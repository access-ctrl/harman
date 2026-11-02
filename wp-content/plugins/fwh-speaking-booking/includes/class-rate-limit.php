<?php
/**
 * Per-IP fixed-window rate limiter backed by transients.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Rate_Limit {

	/**
	 * CF-Connecting-IP is used when present because the site sits behind Cloudflare;
	 * spoofing it only lets a client dodge its own limit.
	 */
	public static function ip(): string {
		foreach ( array( 'HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR' ) as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$candidate = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				if ( filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
					return $candidate;
				}
			}
		}
		return '';
	}

	public static function allow( string $bucket, int $limit, int $window ): bool {
		$key   = 'fwh_sb_rl_' . md5( $bucket . '|' . self::ip() );
		$state = get_transient( $key );
		$now   = time();
		if ( ! is_array( $state ) || $state[1] <= $now ) {
			$state = array( 0, $now + $window );
		}
		++$state[0];
		set_transient( $key, $state, max( 1, $state[1] - $now ) );
		return $state[0] <= $limit;
	}
}
