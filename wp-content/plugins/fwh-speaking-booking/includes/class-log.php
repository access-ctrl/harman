<?php
/**
 * Error logging that never writes secrets.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Log {

	/** Replace anything shaped like a Stripe/Google credential before it reaches the log. */
	public static function redact( string $text ): string {
		$text = preg_replace( '/\b(?:sk|rk|pk)_[A-Za-z0-9_*]+/', '[redacted]', $text );
		$text = preg_replace( '/\bwhsec_[A-Za-z0-9+\/=_*-]+/', '[redacted]', (string) $text ); // Webhook secrets may contain + / = -.
		$text = preg_replace( '/\bGOCSPX-[A-Za-z0-9_-]+/', '[redacted]', (string) $text );
		$text = preg_replace( '/\bya29\.[A-Za-z0-9._-]+/', '[redacted]', (string) $text );
		$text = preg_replace( '/\b1\/\/[A-Za-z0-9._-]{20,}/', '[redacted]', (string) $text );
		return substr( (string) $text, 0, 500 );
	}

	public static function error( string $message ): void {
		error_log( '[fwh-sb] ' . self::redact( $message ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}
