<?php
/**
 * Availability engine. All maths in UTC; Toronto time is always explicit.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Availability {

	/**
	 * Compute free slots for Toronto dates $from..$to (Y-m-d, inclusive).
	 *
	 * Options:
	 *  - trainer_id   (int)  limit to one trainer (even if inactive).
	 *  - ignore_limits (bool) skip booking window and minimum notice (diagnostics).
	 *
	 * @return array<string, array<string, int[]>> Toronto date => [ UTC 'Y-m-d H:i:s' start => free trainer IDs ].
	 */
	public static function compute( string $from, string $to, array $opts = array() ): array {
		global $wpdb;

		if ( ! Time::valid_date( $from ) || ! Time::valid_date( $to ) || $to < $from ) {
			return array();
		}
		$duration = max( 1, (int) Settings::get( 'duration' ) );
		$gap      = max( 0, (int) Settings::get( 'gap_minutes' ) );
		$window   = (int) Settings::get( 'booking_window_days' );
		$notice   = (int) Settings::get( 'min_notice_hours' );
		$limits   = empty( $opts['ignore_limits'] );
		$utc      = Time::utc();
		$now      = new \DateTimeImmutable( 'now', $utc );

		$trainer_filter = isset( $opts['trainer_id'] ) ? (int) $opts['trainer_id'] : 0;
		$tt             = Schema::table( 'trainers' );
		if ( $trainer_filter ) {
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$tt} WHERE id = %d", $trainer_filter ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		} else {
			$ids = $wpdb->get_col( "SELECT id FROM {$tt} WHERE active = 1" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
		$ids = array_map( 'intval', $ids );
		if ( ! $ids ) {
			return array();
		}
		$in = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// Weekly hours: trainer => weekday => [ [start, end], ... ].
		$th    = Schema::table( 'trainer_hours' );
		$hours = array();
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT trainer_id, weekday, start_time, end_time FROM {$th} WHERE trainer_id IN ({$in}) ORDER BY start_time", $ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		foreach ( $rows as $r ) {
			$hours[ (int) $r->trainer_id ][ (int) $r->weekday ][] = array( substr( $r->start_time, 0, 5 ), substr( $r->end_time, 0, 5 ) );
		}

		// Preload blocks and bookings for the whole span once.
		$span_start = Time::toronto_to_utc( $from, '00:00' );
		$next       = ( new \DateTimeImmutable( $to, $utc ) )->modify( '+1 day' )->format( 'Y-m-d' );
		$span_end   = Time::toronto_to_utc( $next, '00:00' );
		$pad_start  = ( new \DateTimeImmutable( $span_start, $utc ) )->modify( "-{$gap} minutes" )->format( Time::FORMAT );
		$pad_end    = ( new \DateTimeImmutable( $span_end, $utc ) )->modify( "+{$gap} minutes" )->format( Time::FORMAT );

		$bt     = Schema::table( 'blocks' );
		$blocks = $wpdb->get_results( $wpdb->prepare( "SELECT trainer_id, start_utc, end_utc FROM {$bt} WHERE start_utc < %s AND end_utc > %s AND (trainer_id IS NULL OR trainer_id IN ({$in}))", array_merge( array( $span_end, $span_start ), $ids ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared

		$bk       = Schema::table( 'bookings' );
		$bookings = $wpdb->get_results( $wpdb->prepare( "SELECT trainer_id, start_utc, end_utc FROM {$bk} WHERE start_utc < %s AND end_utc > %s AND trainer_id IN ({$in}) AND (status = 'paid' OR (status = 'held' AND hold_expires_utc > %s))", array_merge( array( $pad_end, $pad_start ), $ids, array( $now->format( Time::FORMAT ) ) ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared

		$today_local = $now->setTimezone( Time::toronto() )->format( 'Y-m-d' );
		$last_local  = ( new \DateTimeImmutable( $today_local, $utc ) )->modify( "+{$window} days" )->format( 'Y-m-d' );
		$earliest    = $now->modify( "+{$notice} hours" );

		$out  = array();
		$date = $from;
		while ( $date <= $to ) {
			$day = new \DateTimeImmutable( $date, $utc );
			$cur = $date;
			$date = $day->modify( '+1 day' )->format( 'Y-m-d' );

			$weekday = (int) $day->format( 'N' ); // 1=Mon..7=Sun.
			if ( 7 === $weekday ) {
				continue; // Sunday is always closed.
			}
			if ( $limits && ( $cur < $today_local || $cur > $last_local ) ) {
				continue;
			}

			foreach ( $ids as $tid ) {
				foreach ( $hours[ $tid ][ $weekday ] ?? array() as $range ) {
					$range_start = new \DateTimeImmutable( Time::toronto_to_utc( $cur, $range[0] ), $utc );
					$range_end   = new \DateTimeImmutable( Time::toronto_to_utc( $cur, $range[1] ), $utc );
					$step        = $duration + $gap;
					for ( $s = $range_start; ; $s = $s->modify( "+{$step} minutes" ) ) {
						$e = $s->modify( "+{$duration} minutes" );
						if ( $e > $range_end ) {
							break;
						}
						if ( $limits && $s < $earliest ) {
							continue;
						}
						$s_str = $s->format( Time::FORMAT );
						$e_str = $e->format( Time::FORMAT );
						if ( self::is_free( $tid, $s_str, $e_str, $gap, $blocks, $bookings ) ) {
							$out[ $cur ][ $s_str ][] = $tid;
						}
					}
				}
			}
			if ( ! empty( $out[ $cur ] ) ) {
				ksort( $out[ $cur ] );
			}
		}
		return $out;
	}

	private static function is_free( int $tid, string $s, string $e, int $gap, array $blocks, array $bookings ): bool {
		foreach ( $blocks as $b ) {
			if ( ( null === $b->trainer_id || (int) $b->trainer_id === $tid ) && $b->start_utc < $e && $b->end_utc > $s ) {
				return false;
			}
		}
		$utc   = Time::utc();
		$s_gap = ( new \DateTimeImmutable( $s, $utc ) )->modify( "-{$gap} minutes" )->format( Time::FORMAT );
		$e_gap = ( new \DateTimeImmutable( $e, $utc ) )->modify( "+{$gap} minutes" )->format( Time::FORMAT );
		foreach ( $bookings as $b ) {
			if ( (int) $b->trainer_id === $tid && $b->start_utc < $e_gap && $b->end_utc > $s_gap ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Public view: Toronto date => list of UTC ISO start times. No trainer data.
	 *
	 * @return array<string, string[]>
	 */
	public static function public_slots( string $from, string $to ): array {
		$days = array();
		foreach ( self::compute( $from, $to ) as $date => $slots ) {
			$days[ $date ] = array_map( array( self::class, 'to_iso' ), array_keys( $slots ) );
		}
		return $days;
	}

	/**
	 * Trainer IDs free at an exact UTC start ('Y-m-d H:i:s'). Booking window and minimum
	 * notice apply unless $ignore_limits (used when a late payment is reconciled).
	 *
	 * @return int[]
	 */
	public static function trainers_free_at( string $start_utc, int $trainer_id = 0, bool $ignore_limits = false ): array {
		$date = Time::utc_to_toronto( $start_utc, 'Y-m-d' );
		$opts = array( 'ignore_limits' => $ignore_limits );
		if ( $trainer_id ) {
			$opts['trainer_id'] = $trainer_id;
		}
		$all  = self::compute( $date, $date, $opts );
		return $all[ $date ][ $start_utc ] ?? array();
	}

	public static function to_iso( string $utc ): string {
		return ( new \DateTimeImmutable( $utc, Time::utc() ) )->format( 'Y-m-d\TH:i:s\Z' );
	}
}
