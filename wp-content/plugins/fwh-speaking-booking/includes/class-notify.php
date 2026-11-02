<?php
/**
 * Operational alerts to the coordinator.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Notify {

	public static function coordinator( string $subject, string $message ): void {
		$to = (string) Settings::get( 'coordinator_email' );
		if ( ! is_email( $to ) ) {
			$to = (string) get_option( 'admin_email' );
		}
		wp_mail( $to, '[FWH Booking] ' . $subject, $message );
	}
}
