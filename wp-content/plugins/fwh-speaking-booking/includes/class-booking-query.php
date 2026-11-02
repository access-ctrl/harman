<?php
/**
 * Filtered booking lists for the coordinator and trainer screens.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Booking_Query {

	/**
	 * Keys: statuses (string[]), trainer_id, from_utc, to_utc (start_utc range, to exclusive),
	 * when ('upcoming'|'past'), order ('asc'|'desc'), limit, offset.
	 *
	 * @return array{rows: object[], total: int}
	 */
	public static function search( array $a ): array {
		global $wpdb;
		$b     = Schema::table( 'bookings' );
		$t     = Schema::table( 'trainers' );
		$where = array( '1=1' );
		$args  = array();

		if ( ! empty( $a['statuses'] ) ) {
			$where[] = 'b.status IN (' . implode( ',', array_fill( 0, count( $a['statuses'] ), '%s' ) ) . ')';
			$args    = array_merge( $args, array_values( $a['statuses'] ) );
		}
		if ( ! empty( $a['trainer_id'] ) ) {
			$where[] = 'b.trainer_id = %d';
			$args[]  = (int) $a['trainer_id'];
		}
		if ( ! empty( $a['from_utc'] ) ) {
			$where[] = 'b.start_utc >= %s';
			$args[]  = $a['from_utc'];
		}
		if ( ! empty( $a['to_utc'] ) ) {
			$where[] = 'b.start_utc < %s';
			$args[]  = $a['to_utc'];
		}
		if ( 'upcoming' === ( $a['when'] ?? '' ) ) {
			$where[] = 'b.end_utc >= %s';
			$args[]  = Time::now_utc();
		} elseif ( 'past' === ( $a['when'] ?? '' ) ) {
			$where[] = 'b.end_utc < %s';
			$args[]  = Time::now_utc();
		}
		$where_sql = implode( ' AND ', $where );
		$dir       = 'desc' === ( $a['order'] ?? 'asc' ) ? 'DESC' : 'ASC';
		$limit     = max( 1, min( 500, (int) ( $a['limit'] ?? 50 ) ) );
		$offset    = max( 0, (int) ( $a['offset'] ?? 0 ) );

		$count_sql = "SELECT COUNT(*) FROM {$b} b WHERE {$where_sql}";
		$total     = (int) ( $args ? $wpdb->get_var( $wpdb->prepare( $count_sql, $args ) ) : $wpdb->get_var( $count_sql ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared

		$sql  = "SELECT b.*, t.name AS trainer_name FROM {$b} b LEFT JOIN {$t} t ON t.id = b.trainer_id WHERE {$where_sql} ORDER BY b.start_utc {$dir}, b.id {$dir} LIMIT %d OFFSET %d";
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $args, array( $limit, $offset ) ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared

		return array( 'rows' => $rows, 'total' => $total );
	}
}
