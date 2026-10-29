<?php
/**
 * Custom tables, created with dbDelta and versioned by an option.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Schema {

	public const DB_VERSION = '1';

	public static function table( string $name ): string {
		global $wpdb;
		return $wpdb->prefix . 'fwh_sb_' . $name;
	}

	public static function maybe_upgrade(): void {
		if ( get_option( 'fwh_sb_db_version' ) !== self::DB_VERSION ) {
			self::install();
			Roles::install();
			Settings::install_defaults();
		}
	}

	public static function install(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$c = $wpdb->get_charset_collate();

		$t = self::table( 'trainers' );
		dbDelta(
			"CREATE TABLE {$t} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(20) unsigned DEFAULT NULL,
  name varchar(190) NOT NULL,
  email varchar(190) NOT NULL,
  google_email varchar(190) NOT NULL DEFAULT '',
  active tinyint(1) NOT NULL DEFAULT 1,
  created_utc datetime NOT NULL,
  PRIMARY KEY  (id),
  KEY user_id (user_id)
) {$c};"
		);

		$t = self::table( 'trainer_hours' );
		dbDelta(
			"CREATE TABLE {$t} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  trainer_id bigint(20) unsigned NOT NULL,
  weekday tinyint(1) unsigned NOT NULL,
  start_time time NOT NULL,
  end_time time NOT NULL,
  PRIMARY KEY  (id),
  KEY trainer_day (trainer_id,weekday)
) {$c};"
		);

		$t = self::table( 'blocks' );
		dbDelta(
			"CREATE TABLE {$t} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  trainer_id bigint(20) unsigned DEFAULT NULL,
  start_utc datetime NOT NULL,
  end_utc datetime NOT NULL,
  note varchar(255) NOT NULL DEFAULT '',
  created_by bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  KEY trainer_range (trainer_id,start_utc,end_utc)
) {$c};"
		);

		$t = self::table( 'bookings' );
		dbDelta(
			"CREATE TABLE {$t} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  ref varchar(20) NOT NULL,
  trainer_id bigint(20) unsigned NOT NULL,
  start_utc datetime NOT NULL,
  end_utc datetime NOT NULL,
  status varchar(12) NOT NULL DEFAULT 'held',
  hold_expires_utc datetime DEFAULT NULL,
  student_name varchar(190) NOT NULL DEFAULT '',
  student_email varchar(190) NOT NULL DEFAULT '',
  student_phone varchar(40) NOT NULL DEFAULT '',
  student_tz varchar(64) NOT NULL DEFAULT '',
  amount int(11) NOT NULL DEFAULT 0,
  currency varchar(3) NOT NULL DEFAULT '',
  stripe_session_id varchar(255) DEFAULT NULL,
  stripe_payment_intent varchar(255) DEFAULT NULL,
  google_event_id varchar(255) DEFAULT NULL,
  google_calendar_owner varchar(10) DEFAULT NULL,
  meet_link varchar(255) DEFAULT NULL,
  meet_status varchar(8) NOT NULL DEFAULT 'none',
  meet_attempts tinyint(3) unsigned NOT NULL DEFAULT 0,
  created_utc datetime NOT NULL,
  updated_utc datetime NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY ref (ref),
  UNIQUE KEY stripe_session_id (stripe_session_id),
  KEY trainer_start (trainer_id,start_utc),
  KEY status_hold (status,hold_expires_utc)
) {$c};"
		);

		$t = self::table( 'google_tokens' );
		dbDelta(
			"CREATE TABLE {$t} (
  trainer_id bigint(20) unsigned NOT NULL,
  refresh_token text NOT NULL,
  google_email varchar(190) NOT NULL DEFAULT '',
  connected_utc datetime NOT NULL,
  PRIMARY KEY  (trainer_id)
) {$c};"
		);

		$t = self::table( 'stripe_events' );
		dbDelta(
			"CREATE TABLE {$t} (
  event_id varchar(255) NOT NULL,
  type varchar(100) NOT NULL DEFAULT '',
  received_utc datetime NOT NULL,
  PRIMARY KEY  (event_id)
) {$c};"
		);

		update_option( 'fwh_sb_db_version', self::DB_VERSION, false );
	}
}
