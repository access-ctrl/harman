<?php
/**
 * Creates the Google Calendar event with a Meet link after payment, retries on failure,
 * and sends the confirmation emails only once a Meet link exists.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Meet_Service {

	public const RETRY_HOOK = 'fwh_sb_meet_retry';

	/** Delays (seconds) before retry 1, 2 and 3 after the first attempt. */
	private const RETRY_DELAYS = array( 60, 300, 900 );

	public function init(): void {
		add_action( 'fwh_sb_booking_paid', array( self::class, 'attempt' ) );
		add_action( self::RETRY_HOOK, array( self::class, 'attempt' ) );
	}

	private static function bump_attempts( int $id ): int {
		global $wpdb;
		$t = Schema::table( 'bookings' );
		$wpdb->query( $wpdb->prepare( "UPDATE {$t} SET meet_attempts = meet_attempts + 1, updated_utc = %s WHERE id = %d", Time::now_utc(), $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT meet_attempts FROM {$t} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	private static function save( int $id, array $data ): void {
		global $wpdb;
		$data['updated_utc'] = Time::now_utc();
		$wpdb->update( Schema::table( 'bookings' ), $data, array( 'id' => $id ) );
	}

	/**
	 * One attempt to give a paid booking a Meet link. Safe to call repeatedly.
	 */
	public static function attempt( int $id ): void {
		global $wpdb;
		$lock = 'fwh_sb_meet_' . $id;
		if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', $lock ) ) ) {
			return; // Another process is already working on this booking.
		}
		try {
			$b = Bookings::get( $id );
			if ( ! $b || 'paid' !== $b->status || 'ok' === $b->meet_status ) {
				return;
			}
			$attempts = self::bump_attempts( $id );
			$result   = self::run( $b );
			if ( true === $result ) {
				return;
			}
			self::handle_failure( $b, $attempts, $result );
		} finally {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
		}
	}

	/** @return true|string True on success, otherwise a short reason for the log. */
	private static function run( object $b ) {
		$trainer = Repo::trainer( (int) $b->trainer_id );
		if ( ! $trainer ) {
			return 'trainer missing';
		}

		// Reuse the connection that already owns an event for this booking, else prefer the trainer's own.
		if ( ! empty( $b->google_event_id ) && in_array( $b->google_calendar_owner, array( 'trainer', 'fallback' ), true ) ) {
			$owner = $b->google_calendar_owner;
		} else {
			$owner = Google_Tokens::has( (int) $b->trainer_id ) ? 'trainer' : 'fallback';
		}
		$conn = 'trainer' === $owner ? (int) $b->trainer_id : 0;
		if ( ! Google_Tokens::has( $conn ) ) {
			return 'no Google connection (' . $owner . ')';
		}

		$event_id = (string) $b->google_event_id;
		$event    = null;
		if ( '' === $event_id ) {
			$res = Google_Client::insert_event( $conn, self::event_body( $b, $trainer, 'fallback' === $owner ) );
			if ( is_wp_error( $res ) || empty( $res['id'] ) ) {
				return 'event insert failed';
			}
			$event    = $res;
			$event_id = (string) $res['id'];
			self::save( (int) $b->id, array( 'google_event_id' => $event_id, 'google_calendar_owner' => $owner ) );
		} else {
			$event = Google_Client::get_event( $conn, $event_id );
			if ( is_wp_error( $event ) ) {
				return 'event fetch failed';
			}
		}

		// The Meet room may still be "pending": poll the event up to 5 times.
		$link  = Google_Client::meet_link( (array) $event );
		$tries = 0;
		while ( '' === $link && $tries < 5 ) {
			++$tries;
			$wait = (int) apply_filters( 'fwh_sb_meet_poll_seconds', 2 );
			if ( $wait > 0 ) {
				sleep( $wait );
			}
			$event = Google_Client::get_event( $conn, $event_id );
			if ( is_wp_error( $event ) ) {
				return 'event fetch failed';
			}
			$link = Google_Client::meet_link( (array) $event );
		}
		if ( '' === $link ) {
			return 'Meet link still pending';
		}
		self::finish( $b, $link, $owner, $trainer );
		return true;
	}

	private static function event_body( object $b, object $trainer, bool $fallback ): array {
		// The student is not added as a guest: the trainer must not see the student's email. The student asks to join and the trainer admits them.
		$attendees = array();
		if ( $fallback && is_email( $trainer->google_email ?: $trainer->email ) ) {
			$attendees[] = array( 'email' => $trainer->google_email ?: $trainer->email );
		}
		return array(
			'summary'        => 'French Speaking Session - ' . $b->student_name,
			'description'    => "Booking ref: {$b->ref}",
			'start'          => array( 'dateTime' => Availability::to_iso( $b->start_utc ), 'timeZone' => 'UTC' ),
			'end'            => array( 'dateTime' => Availability::to_iso( $b->end_utc ), 'timeZone' => 'UTC' ),
			'attendees'      => $attendees,
			'conferenceData' => array(
				'createRequest' => array(
					'requestId'             => $b->ref,
					'conferenceSolutionKey' => array( 'type' => 'hangoutsMeet' ),
				),
			),
		);
	}

	private static function finish( object $b, string $link, string $owner, object $trainer ): void {
		global $wpdb;
		$t = Schema::table( 'bookings' );
		// Atomic flip to ok: only the process that wins it sends the emails, so each goes out once.
		$rows = (int) $wpdb->query( $wpdb->prepare( "UPDATE {$t} SET meet_link = %s, meet_status = 'ok', google_calendar_owner = %s, updated_utc = %s WHERE id = %d AND meet_status <> 'ok'", $link, $owner, Time::now_utc(), (int) $b->id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( 1 !== $rows ) {
			return;
		}
		if ( 'fallback' === $owner ) {
			Notify::coordinator(
				'Trainer must connect Google: ' . $trainer->name,
				"Booking {$b->ref} was created on the fallback Google account because {$trainer->name} has not connected their own Google account. Please ask them to connect it (My Schedule > Google connection) so they host their own meetings."
			);
		}
		Mailer::send_confirmations( (int) $b->id );
	}

	private static function handle_failure( object $b, int $attempts, string $reason ): void {
		Log::error( sprintf( 'Meet attempt %d for %s failed: %s', $attempts, $b->ref, $reason ) );
		if ( $attempts <= count( self::RETRY_DELAYS ) ) {
			wp_schedule_single_event( time() + self::RETRY_DELAYS[ $attempts - 1 ], self::RETRY_HOOK, array( (int) $b->id ) );
			return;
		}
		self::save( (int) $b->id, array( 'meet_status' => 'failed' ) );
		Notify::coordinator(
			'Meet link failed for ' . $b->ref,
			"No Meet link could be created for a paid booking after {$attempts} attempts. No email has been sent to the student.\n\nRef: {$b->ref}\nStudent: {$b->student_name} <{$b->student_email}> {$b->student_phone}\nTime (Toronto): " . Time::utc_to_toronto( $b->start_utc ) . "\n\nUse \"Retry Meet link\" in wp-admin once the Google connection is fixed."
		);
	}

	/** Manual retry from the admin: reset the counter and try again now. */
	public static function manual_retry( int $id ): void {
		global $wpdb;
		$b = Bookings::get( $id );
		if ( ! $b || 'paid' !== $b->status || 'ok' === $b->meet_status ) {
			return;
		}
		wp_clear_scheduled_hook( self::RETRY_HOOK, array( $id ) );
		$t = Schema::table( 'bookings' );
		$wpdb->query( $wpdb->prepare( "UPDATE {$t} SET meet_attempts = 0, meet_status = 'none', updated_utc = %s WHERE id = %d", Time::now_utc(), $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		self::attempt( $id );
	}
}
