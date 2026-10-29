<?php
/**
 * Detects changes that would strand an existing paid booking.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Conflicts {

	/** @return object[] Upcoming paid bookings for the trainer. */
	private static function upcoming( int $trainer_id ): array {
		global $wpdb;
		$t = Schema::table( 'bookings' );
		return $wpdb->get_results( $wpdb->prepare( "SELECT ref, student_name, start_utc, end_utc FROM {$t} WHERE trainer_id = %d AND status = 'paid' AND end_utc > %s ORDER BY start_utc", $trainer_id, Time::now_utc() ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Paid bookings that would fall outside the proposed weekly hours.
	 *
	 * @param array $rows [ [weekday, 'HH:MM', 'HH:MM'], ... ]
	 * @return object[]
	 */
	public static function hours( int $trainer_id, array $rows ): array {
		$bad = array();
		foreach ( self::upcoming( $trainer_id ) as $b ) {
			$start = new \DateTimeImmutable( $b->start_utc, Time::utc() );
			$end   = new \DateTimeImmutable( $b->end_utc, Time::utc() );
			$s     = $start->setTimezone( Time::toronto() );
			$e     = $end->setTimezone( Time::toronto() );
			$day   = (int) $s->format( 'N' );
			$ok    = false;
			if ( $s->format( 'Y-m-d' ) === $e->format( 'Y-m-d' ) ) {
				foreach ( $rows as $r ) {
					if ( $r[0] === $day && $r[1] <= $s->format( 'H:i' ) && $e->format( 'H:i' ) <= $r[2] ) {
						$ok = true;
						break;
					}
				}
			}
			if ( ! $ok ) {
				$bad[] = $b;
			}
		}
		return $bad;
	}

	/** Paid bookings that overlap the proposed block. */
	public static function block( int $trainer_id, string $start_utc, string $end_utc ): array {
		return array_values( array_filter( self::upcoming( $trainer_id ), static fn( $b ) => $b->start_utc < $end_utc && $b->end_utc > $start_utc ) );
	}

	/** Human message naming the conflicting bookings. */
	public static function message( array $bookings, string $what ): string {
		$names = array();
		foreach ( array_slice( $bookings, 0, 3 ) as $b ) {
			$names[] = sprintf( '%s (%s, %s)', $b->ref, $b->student_name, Time::utc_to_toronto( $b->start_utc, 'D j M, H:i' ) );
		}
		$more = count( $bookings ) > 3 ? sprintf( /* translators: %d: number */ __( ' and %d more', 'fwh-speaking-booking' ), count( $bookings ) - 3 ) : '';
		return sprintf(
			/* translators: 1: what is being changed, 2: booking list */
			__( 'Not saved: %1$s would conflict with a paid booking: %2$s%3$s. Ask the coordinator to cancel it first.', 'fwh-speaking-booking' ),
			$what,
			implode( '; ', $names ),
			$more
		);
	}
}
