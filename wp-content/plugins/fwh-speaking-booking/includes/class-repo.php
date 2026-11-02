<?php
/**
 * Data access for trainers, weekly hours and blocks.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Repo {

	public static function trainers(): array {
		global $wpdb;
		$t = Schema::table( 'trainers' );
		return $wpdb->get_results( "SELECT * FROM {$t} ORDER BY active DESC, name ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	public static function trainer( int $id ): ?object {
		global $wpdb;
		$t = Schema::table( 'trainers' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	public static function trainer_by_user( int $user_id ): ?object {
		global $wpdb;
		$t = Schema::table( 'trainers' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE user_id = %d", $user_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Insert or update a trainer. Returns the trainer ID or 0 on failure.
	 */
	public static function save_trainer( int $id, array $data ): int {
		global $wpdb;
		$t      = Schema::table( 'trainers' );
		$fields = array(
			'name'         => $data['name'],
			'email'        => $data['email'],
			'google_email' => $data['google_email'],
			'active'       => (int) $data['active'],
		);
		if ( $id > 0 ) {
			$wpdb->update( $t, $fields, array( 'id' => $id ), array( '%s', '%s', '%s', '%d' ), array( '%d' ) );
			return $id;
		}
		$fields['created_utc'] = Time::now_utc();
		$wpdb->insert( $t, $fields, array( '%s', '%s', '%s', '%d', '%s' ) );
		return (int) $wpdb->insert_id;
	}

	public static function link_user( int $trainer_id, int $user_id ): void {
		global $wpdb;
		$wpdb->update( Schema::table( 'trainers' ), array( 'user_id' => $user_id ), array( 'id' => $trainer_id ), array( '%d' ), array( '%d' ) );
	}

	public static function set_active( int $id, bool $active ): void {
		global $wpdb;
		$wpdb->update( Schema::table( 'trainers' ), array( 'active' => $active ? 1 : 0 ), array( 'id' => $id ), array( '%d' ), array( '%d' ) );
	}

	/** @return object[] rows with weekday, start_time, end_time. */
	public static function hours( int $trainer_id ): array {
		global $wpdb;
		$t = Schema::table( 'trainer_hours' );
		return $wpdb->get_results( $wpdb->prepare( "SELECT weekday, start_time, end_time FROM {$t} WHERE trainer_id = %d ORDER BY weekday, start_time", $trainer_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Replace a trainer's weekly ranges. $rows: [ [weekday, 'HH:MM', 'HH:MM'], ... ] already validated.
	 */
	public static function replace_hours( int $trainer_id, array $rows ): void {
		global $wpdb;
		$t = Schema::table( 'trainer_hours' );
		$wpdb->query( 'START TRANSACTION' );
		$wpdb->delete( $t, array( 'trainer_id' => $trainer_id ), array( '%d' ) );
		foreach ( $rows as $r ) {
			$wpdb->insert(
				$t,
				array(
					'trainer_id' => $trainer_id,
					'weekday'    => $r[0],
					'start_time' => $r[1] . ':00',
					'end_time'   => $r[2] . ':00',
				),
				array( '%d', '%d', '%s', '%s' )
			);
		}
		$wpdb->query( 'COMMIT' );
	}

	public static function blocks(): array {
		global $wpdb;
		$b = Schema::table( 'blocks' );
		$t = Schema::table( 'trainers' );
		return $wpdb->get_results( "SELECT b.*, t.name AS trainer_name FROM {$b} b LEFT JOIN {$t} t ON t.id = b.trainer_id ORDER BY b.end_utc DESC LIMIT 500" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	public static function add_block( ?int $trainer_id, string $start_utc, string $end_utc, string $note, int $user_id ): int {
		global $wpdb;
		$wpdb->insert(
			Schema::table( 'blocks' ),
			array(
				'trainer_id' => $trainer_id,
				'start_utc'  => $start_utc,
				'end_utc'    => $end_utc,
				'note'       => $note,
				'created_by' => $user_id,
			),
			array( '%d', '%s', '%s', '%s', '%d' )
		);
		return (int) $wpdb->insert_id;
	}

	public static function delete_block( int $id ): void {
		global $wpdb;
		$wpdb->delete( Schema::table( 'blocks' ), array( 'id' => $id ), array( '%d' ) );
	}
}
