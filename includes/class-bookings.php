<?php
/**
 * Booking lifecycle: place a hold, create Stripe Checkout, confirm payment, expire.
 *
 * A booking becomes paid only via mark_paid(), which is reached only from the
 * signature-verified webhook or a server-side Stripe API check.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Bookings {

	public const HOLD_MINUTES    = 35;
	// Stripe requires expires_at to be at least 30 minutes after creation, so we ask for 31
	// to survive request latency. It is still shorter than the 35 minute hold.
	public const CHECKOUT_SECONDS = 1860;

	private static function t(): string {
		return Schema::table( 'bookings' );
	}

	public static function get( int $id ): ?object {
		global $wpdb;
		$t = self::t();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	public static function get_by_ref( string $ref ): ?object {
		global $wpdb;
		$t = self::t();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE ref = %s", $ref ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	private static function update( int $id, array $data ): void {
		global $wpdb;
		$data['updated_utc'] = Time::now_utc();
		$wpdb->update( self::t(), $data, array( 'id' => $id ) );
	}

	/** HMAC token that authorises the Stripe "cancel" return for one booking. */
	public static function cancel_token( string $ref ): string {
		return substr( hash_hmac( 'sha256', 'cancel|' . $ref, wp_salt( 'auth' ) ), 0, 24 );
	}

	/**
	 * Place a held booking and create the Stripe Checkout Session.
	 *
	 * @param array $d start_utc (Y-m-d H:i:s), name, email, phone, tz, page_url.
	 * @return array|\WP_Error [ 'checkout_url' => string, 'ref' => string ]
	 */
	public static function place( array $d ) {
		global $wpdb;

		$amount = Settings::price_minor();
		if ( $amount <= 0 || ! Stripe_Client::configured() ) {
			error_log( '[fwh-sb] Booking attempted but price or Stripe key is not configured.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return new \WP_Error( 'fwh_sb_unavailable', __( 'Booking is temporarily unavailable. Please try again later.', 'fwh-speaking-booking' ), array( 'status' => 503 ) );
		}

		$taken = new \WP_Error( 'fwh_sb_taken', __( 'That time was just taken, please pick another', 'fwh-speaking-booking' ), array( 'status' => 409 ) );
		$free  = Availability::trainers_free_at( $d['start_utc'] );
		if ( ! $free ) {
			return $taken;
		}

		$duration = (int) Settings::get( 'duration' );
		$end_utc  = ( new \DateTimeImmutable( $d['start_utc'], Time::utc() ) )->modify( "+{$duration} minutes" )->format( Time::FORMAT );
		$now      = Time::now_utc();
		$expires  = ( new \DateTimeImmutable( $now, Time::utc() ) )->modify( '+' . self::HOLD_MINUTES . ' minutes' )->format( Time::FORMAT );
		$currency = strtolower( (string) Settings::get( 'currency' ) );

		$booking_id = 0;
		foreach ( self::order_trainers( $free, $d['start_utc'] ) as $tid ) {
			$lock = 'fwh_sb_trainer_' . $tid;
			if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 5)', $lock ) ) ) {
				continue;
			}
			try {
				if ( in_array( $tid, Availability::trainers_free_at( $d['start_utc'], $tid ), true ) ) {
					$ok = $wpdb->insert(
						self::t(),
						array(
							'ref'              => 'TMP' . wp_generate_password( 14, false ),
							'trainer_id'       => $tid,
							'start_utc'        => $d['start_utc'],
							'end_utc'          => $end_utc,
							'status'           => 'held',
							'hold_expires_utc' => $expires,
							'student_name'     => $d['name'],
							'student_email'    => $d['email'],
							'student_phone'    => $d['phone'],
							'student_tz'       => $d['tz'],
							'amount'           => $amount,
							'currency'         => $currency,
							'created_utc'      => $now,
							'updated_utc'      => $now,
						)
					);
					if ( $ok ) {
						$booking_id = (int) $wpdb->insert_id;
					}
				}
			} finally {
				$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
			}
			if ( $booking_id ) {
				break;
			}
		}
		if ( ! $booking_id ) {
			return $taken;
		}

		$ref = sprintf( 'FWH-%06d', $booking_id );
		self::update( $booking_id, array( 'ref' => $ref ) );

		$sep     = false === strpos( $d['page_url'], '?' ) ? '?' : '&';
		$success = $d['page_url'] . $sep . 'fwh_sb_return=success&ref=' . rawurlencode( $ref ) . '&session_id={CHECKOUT_SESSION_ID}';
		$cancel  = $d['page_url'] . $sep . 'fwh_sb_return=cancel&ref=' . rawurlencode( $ref ) . '&tok=' . self::cancel_token( $ref );

		$session = Stripe_Client::create_session(
			array(
				'mode'                 => 'payment',
				'payment_method_types' => array( 'card' ),
				'line_items'           => array(
					array(
						'quantity'   => 1,
						'price_data' => array(
							'currency'     => $currency,
							'unit_amount'  => $amount,
							'product_data' => array( 'name' => (string) Settings::get( 'session_name' ) ),
						),
					),
				),
				'customer_email'       => $d['email'],
				'client_reference_id'  => $ref,
				'metadata'             => array( 'booking_id' => (string) $booking_id, 'ref' => $ref ),
				'expires_at'           => time() + self::CHECKOUT_SECONDS,
				'success_url'          => $success,
				'cancel_url'           => $cancel,
			),
			$ref
		);
		if ( is_wp_error( $session ) || empty( $session['id'] ) || empty( $session['url'] ) ) {
			self::expire_held( $booking_id ); // Free the slot at once.
			return new \WP_Error( 'fwh_sb_payment', __( 'We could not start the payment. Please try again.', 'fwh-speaking-booking' ), array( 'status' => 502 ) );
		}
		self::update( $booking_id, array( 'stripe_session_id' => (string) $session['id'] ) );

		return array(
			'checkout_url' => (string) $session['url'],
			'ref'          => $ref,
		);
	}

	/**
	 * Order candidate trainers by fewest paid + live-held bookings in the Toronto ISO week of the slot, then lowest ID.
	 *
	 * @param int[] $ids
	 * @return int[]
	 */
	private static function order_trainers( array $ids, string $start_utc ): array {
		global $wpdb;
		$local      = new \DateTimeImmutable( $start_utc, Time::utc() );
		$local      = $local->setTimezone( Time::toronto() );
		$week_start = $local->modify( 'monday this week' )->setTime( 0, 0 );
		if ( $local->format( 'N' ) === '7' ) {
			$week_start = $local->modify( 'monday last week' )->setTime( 0, 0 );
		}
		$from = $week_start->setTimezone( Time::utc() )->format( Time::FORMAT );
		$to   = $week_start->modify( '+7 days' )->setTimezone( Time::utc() )->format( Time::FORMAT );
		$in   = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$t    = self::t();
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT trainer_id, COUNT(*) AS n FROM {$t} WHERE trainer_id IN ({$in}) AND start_utc >= %s AND start_utc < %s AND (status = 'paid' OR (status = 'held' AND hold_expires_utc > %s)) GROUP BY trainer_id", array_merge( $ids, array( $from, $to, Time::now_utc() ) ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$counts = array_fill_keys( $ids, 0 );
		foreach ( $rows as $r ) {
			$counts[ (int) $r->trainer_id ] = (int) $r->n;
		}
		usort( $ids, static fn( $a, $b ) => array( $counts[ $a ], $a ) <=> array( $counts[ $b ], $b ) );
		return $ids;
	}

	public static function expire_held( int $id ): bool {
		global $wpdb;
		$t = self::t();
		return 1 === (int) $wpdb->query( $wpdb->prepare( "UPDATE {$t} SET status = 'expired', updated_utc = %s WHERE id = %d AND status = 'held'", Time::now_utc(), $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Mark a booking paid. Idempotent: fires fwh_sb_booking_paid at most once.
	 *
	 * @return string paid | already | paid_late | conflict | missing
	 */
	public static function mark_paid( int $id, string $payment_intent ): string {
		global $wpdb;
		$b = self::get( $id );
		if ( ! $b ) {
			return 'missing';
		}
		if ( 'paid' === $b->status ) {
			return 'already';
		}
		$t   = self::t();
		$now = Time::now_utc();

		// Normal path: still held and the hold has not lapsed (so nobody else could have taken the slot).
		$rows = (int) $wpdb->query( $wpdb->prepare( "UPDATE {$t} SET status = 'paid', stripe_payment_intent = %s, updated_utc = %s WHERE id = %d AND status = 'held' AND hold_expires_utc > %s", $payment_intent, $now, $id, $now ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( 1 === $rows ) {
			do_action( 'fwh_sb_booking_paid', $id );
			return 'paid';
		}

		$b = self::get( $id );
		if ( 'paid' === $b->status ) {
			return 'already';
		}

		// Late path: hold lapsed or booking expired/cancelled. Confirm only if the trainer is still free.
		$lock = 'fwh_sb_trainer_' . (int) $b->trainer_id;
		$wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 5)', $lock ) );
		try {
			$free = $b->start_utc > $now && in_array( (int) $b->trainer_id, Availability::trainers_free_at( $b->start_utc, (int) $b->trainer_id, true ), true );
			if ( $free ) {
				$rows = (int) $wpdb->query( $wpdb->prepare( "UPDATE {$t} SET status = 'paid', stripe_payment_intent = %s, updated_utc = %s WHERE id = %d AND status IN ('held','expired','cancelled')", $payment_intent, $now, $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		} finally {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
		}
		if ( ! empty( $free ) && 1 === $rows ) {
			do_action( 'fwh_sb_booking_paid', $id );
			return 'paid_late';
		}

		self::update( $id, array( 'stripe_payment_intent' => $payment_intent ) );
		error_log( '[fwh-sb] Paid booking ' . $b->ref . ' could not be confirmed: slot no longer free.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		Notify::coordinator(
			'Refund needed for ' . $b->ref,
			"A payment arrived for booking {$b->ref} but the slot is no longer available.\n\nStudent: {$b->student_name} <{$b->student_email}>\nPayment intent: {$payment_intent}\n\nPlease refund this payment in the Stripe dashboard."
		);
		return 'conflict';
	}

	/** Does a Stripe Checkout Session match the booking's amount, currency and reference? */
	public static function session_matches( object $b, array $session ): bool {
		return isset( $session['client_reference_id'], $session['amount_total'], $session['currency'] )
			&& $session['client_reference_id'] === $b->ref
			&& (int) $session['amount_total'] === (int) $b->amount
			&& strtolower( (string) $session['currency'] ) === strtolower( $b->currency );
	}

	/**
	 * Confirm a Stripe-reported paid session against the booking and mark it paid.
	 */
	public static function apply_paid_session( array $session ): string {
		$ref = isset( $session['client_reference_id'] ) ? (string) $session['client_reference_id'] : '';
		$b   = '' !== $ref ? self::get_by_ref( $ref ) : null;
		if ( ! $b ) {
			error_log( '[fwh-sb] Paid session for unknown booking reference.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return 'missing';
		}
		if ( empty( $b->stripe_session_id ) || ( $session['id'] ?? '' ) !== $b->stripe_session_id || ! self::session_matches( $b, $session ) ) {
			error_log( '[fwh-sb] Paid session does not match booking ' . $b->ref . ' (session, amount or currency).' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			Notify::coordinator( 'Payment mismatch for ' . $b->ref, "A paid Checkout Session did not match booking {$b->ref} (session, amount or currency). Please check Stripe." );
			return 'mismatch';
		}
		$pi = isset( $session['payment_intent'] ) && is_string( $session['payment_intent'] ) ? $session['payment_intent'] : '';
		return self::mark_paid( (int) $b->id, $pi );
	}

	/**
	 * Ask Stripe for the session's real state and act on it.
	 *
	 * @return string paid | expired | pending | error
	 */
	public static function sync_with_stripe( object $b ): string {
		if ( 'paid' === $b->status ) {
			return 'paid';
		}
		if ( empty( $b->stripe_session_id ) ) {
			self::expire_held( (int) $b->id );
			return 'expired';
		}
		$session = Stripe_Client::retrieve_session( $b->stripe_session_id );
		if ( is_wp_error( $session ) ) {
			return 'error';
		}
		if ( 'paid' === ( $session['payment_status'] ?? '' ) ) {
			$res = self::apply_paid_session( $session );
			return in_array( $res, array( 'paid', 'already', 'paid_late' ), true ) ? 'paid' : 'error';
		}
		$lapsed = $b->hold_expires_utc && $b->hold_expires_utc <= Time::now_utc();
		if ( 'open' === ( $session['status'] ?? '' ) ) {
			if ( ! $lapsed ) {
				return 'pending';
			}
			Stripe_Client::expire_session( $b->stripe_session_id ); // Stop a late payment before releasing the slot.
		}
		self::expire_held( (int) $b->id );
		return 'expired';
	}

	/** Customer clicked "back" on the Stripe page: release the slot at once if it is unpaid. */
	public static function cancel_from_return( string $ref, string $token ): void {
		if ( ! hash_equals( self::cancel_token( $ref ), $token ) ) {
			return;
		}
		$b = self::get_by_ref( $ref );
		if ( ! $b || 'held' !== $b->status ) {
			return;
		}
		if ( ! empty( $b->stripe_session_id ) ) {
			$res = Stripe_Client::expire_session( $b->stripe_session_id );
			if ( is_wp_error( $res ) ) {
				// Could not expire (maybe already paid): trust Stripe's real state instead.
				self::sync_with_stripe( $b );
				return;
			}
		}
		self::expire_held( (int) $b->id );
	}

	/**
	 * Coordinator cancel: frees the slot and removes the calendar event. Sends no emails.
	 *
	 * @return string cancelled | cancelled_event_failed | not_cancellable
	 */
	public static function cancel( int $id ): string {
		global $wpdb;
		$b = self::get( $id );
		if ( ! $b || ! in_array( $b->status, array( 'paid', 'held' ), true ) ) {
			return 'not_cancellable';
		}
		$t    = self::t();
		$rows = (int) $wpdb->query( $wpdb->prepare( "UPDATE {$t} SET status = 'cancelled', updated_utc = %s WHERE id = %d AND status IN ('paid','held')", Time::now_utc(), $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( 1 !== $rows ) {
			return 'not_cancellable';
		}
		wp_clear_scheduled_hook( Meet_Service::RETRY_HOOK, array( $id ) );

		// A still-open Checkout Session must not be payable after cancellation.
		if ( 'held' === $b->status && ! empty( $b->stripe_session_id ) ) {
			Stripe_Client::expire_session( $b->stripe_session_id );
		}

		if ( ! empty( $b->google_event_id ) ) {
			// Delete with the connection that created the event.
			$conn = 'fallback' === $b->google_calendar_owner ? 0 : (int) $b->trainer_id;
			$res  = Google_Client::delete_event( $conn, $b->google_event_id );
			if ( is_wp_error( $res ) ) {
				$data = $res->get_error_data();
				$http = is_array( $data ) ? (int) ( $data['http'] ?? 0 ) : 0;
				if ( ! in_array( $http, array( 404, 410 ), true ) ) { // Already gone counts as deleted.
					error_log( '[fwh-sb] Could not delete calendar event for ' . $b->ref ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					return 'cancelled_event_failed';
				}
			}
		}
		return 'cancelled';
	}

	/** Cron: settle held bookings whose hold lapsed. */
	public static function sweep_holds(): void {
		global $wpdb;
		$t    = self::t();
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t} WHERE status = 'held' AND hold_expires_utc <= %s ORDER BY id LIMIT 50", Time::now_utc() ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $rows as $b ) {
			$result = self::sync_with_stripe( $b );
			if ( 'error' === $result && $b->hold_expires_utc < gmdate( Time::FORMAT, time() - DAY_IN_SECONDS ) ) {
				self::expire_held( (int) $b->id ); // Stripe unreachable for a day: do not hold the slot forever.
			}
		}
	}
}
