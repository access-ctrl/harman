<?php
/**
 * Time helpers. All storage is UTC; Toronto time is always explicit.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Time {

	public const FORMAT = 'Y-m-d H:i:s';

	public static function toronto(): \DateTimeZone {
		return new \DateTimeZone( 'America/Toronto' );
	}

	public static function utc(): \DateTimeZone {
		return new \DateTimeZone( 'UTC' );
	}

	public static function now_utc(): string {
		return ( new \DateTimeImmutable( 'now', self::utc() ) )->format( self::FORMAT );
	}

	/**
	 * Convert a Toronto local date (Y-m-d) and time (H:i) to a UTC DATETIME string.
	 */
	public static function toronto_to_utc( string $date, string $time = '00:00' ): ?string {
		$local = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $time, self::toronto() );
		if ( ! $local ) {
			return null;
		}
		return $local->setTimezone( self::utc() )->format( self::FORMAT );
	}

	/**
	 * Format a UTC DATETIME string in Toronto time.
	 */
	public static function utc_to_toronto( string $utc, string $format = 'D j M Y, H:i' ): string {
		$dt = new \DateTimeImmutable( $utc, self::utc() );
		return $dt->setTimezone( self::toronto() )->format( $format );
	}

	public static function valid_date( string $date ): bool {
		$dt = \DateTimeImmutable::createFromFormat( '!Y-m-d', $date );
		return $dt && $dt->format( 'Y-m-d' ) === $date;
	}

	public static function valid_time( string $time ): bool {
		return (bool) preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $time );
	}
}
