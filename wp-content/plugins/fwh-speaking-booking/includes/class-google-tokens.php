<?php
/**
 * Google refresh tokens, encrypted at rest with a key derived from AUTH_KEY.
 * trainer_id 0 is the fallback account.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Google_Tokens {

	private const CIPHER = 'aes-256-gcm';

	private static function key(): string {
		return hash( 'sha256', 'fwh_sb|' . ( defined( 'AUTH_KEY' ) ? AUTH_KEY : wp_salt( 'auth' ) ), true );
	}

	private static function encrypt( string $plain ): string {
		$iv     = random_bytes( 12 );
		$tag    = '';
		$cipher = openssl_encrypt( $plain, self::CIPHER, self::key(), OPENSSL_RAW_DATA, $iv, $tag );
		return base64_encode( $iv . $tag . $cipher ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	private static function decrypt( string $blob ): string {
		$raw = base64_decode( $blob, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( false === $raw || strlen( $raw ) < 29 ) {
			return '';
		}
		$plain = openssl_decrypt( substr( $raw, 28 ), self::CIPHER, self::key(), OPENSSL_RAW_DATA, substr( $raw, 0, 12 ), substr( $raw, 12, 16 ) );
		return false === $plain ? '' : $plain;
	}

	public static function save( int $trainer_id, string $refresh_token, string $google_email = '' ): void {
		global $wpdb;
		$wpdb->replace(
			Schema::table( 'google_tokens' ),
			array(
				'trainer_id'    => $trainer_id,
				'refresh_token' => self::encrypt( $refresh_token ),
				'google_email'  => $google_email,
				'connected_utc' => Time::now_utc(),
			),
			array( '%d', '%s', '%s', '%s' )
		);
		delete_transient( 'fwh_sb_gat_' . $trainer_id );
	}

	public static function get( int $trainer_id ): string {
		global $wpdb;
		$t    = Schema::table( 'google_tokens' );
		$blob = $wpdb->get_var( $wpdb->prepare( "SELECT refresh_token FROM {$t} WHERE trainer_id = %d", $trainer_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $blob ? self::decrypt( (string) $blob ) : '';
	}

	public static function has( int $trainer_id ): bool {
		return '' !== self::get( $trainer_id );
	}

	public static function connected_utc( int $trainer_id ): ?string {
		global $wpdb;
		$t = Schema::table( 'google_tokens' );
		$v = $wpdb->get_var( $wpdb->prepare( "SELECT connected_utc FROM {$t} WHERE trainer_id = %d", $trainer_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $v ? (string) $v : null;
	}

	public static function delete( int $trainer_id ): void {
		global $wpdb;
		$wpdb->delete( Schema::table( 'google_tokens' ), array( 'trainer_id' => $trainer_id ), array( '%d' ) );
		delete_transient( 'fwh_sb_gat_' . $trainer_id );
	}
}
