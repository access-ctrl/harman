<?php
/**
 * Confirmation emails: exactly one to the student, one to the trainer (coordinator in CC).
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Mailer {

	private static function render( string $template, array $vars ): string {
		$file = FWH_SB_DIR . 'templates/' . $template . '.php';
		if ( ! is_readable( $file ) ) {
			return '';
		}
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- template variables, controlled by this class.
		extract( $vars, EXTR_SKIP );
		ob_start();
		include $file;
		return (string) ob_get_clean();
	}

	public static function send_confirmations( int $booking_id ): void {
		$b = Bookings::get( $booking_id );
		if ( ! $b || empty( $b->meet_link ) || 'ok' !== $b->meet_status ) {
			return; // Never email without a Meet link.
		}
		$trainer     = Repo::trainer( (int) $b->trainer_id );
		$coordinator = (string) Settings::get( 'coordinator_email' );
		$contact     = (string) Settings::get( 'contact_email' );
		$headers     = array( 'Content-Type: text/html; charset=UTF-8' );

		// Student: time in their own zone.
		try {
			$zone = new \DateTimeZone( $b->student_tz );
		} catch ( \Exception $e ) {
			$zone = Time::toronto();
		}
		$local = ( new \DateTimeImmutable( $b->start_utc, Time::utc() ) )->setTimezone( $zone );
		$html  = self::render(
			'email-student',
			array(
				'b'        => $b,
				'when'     => $local->format( 'l, j F Y \a\t g:i A' ),
				'zone'     => $zone->getName() . ' (' . $local->format( 'T' ) . ')',
				'duration' => (int) Settings::get( 'duration' ),
				'contact'  => $contact,
			)
		);
		wp_mail( $b->student_email, 'Your French speaking session is confirmed (' . $b->ref . ')', $html, $headers );

		// Trainer, coordinator in CC.
		if ( $trainer && is_email( $trainer->email ) ) {
			$th = $headers;
			if ( is_email( $coordinator ) ) {
				$th[] = 'Cc: ' . $coordinator;
			}
			$html = self::render(
				'email-trainer',
				array(
					'b'    => $b,
					'when' => Time::utc_to_toronto( $b->start_utc, 'l, j F Y \a\t g:i A T' ),
				)
			);
			wp_mail( $trainer->email, 'New speaking session: ' . $b->student_name . ' (' . $b->ref . ')', $html, $th );
		}
	}
}
