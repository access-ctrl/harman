<?php
/**
 * Minimal Stripe REST client over wp_remote_request. Secrets are never logged.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Stripe_Client {

	private const API     = 'https://api.stripe.com/v1/';
	private const VERSION = '2024-06-20';

	public static function mode(): string {
		return 'live' === Settings::get( 'stripe_mode' ) ? 'live' : 'test';
	}

	public static function secret_key(): string {
		return (string) Settings::get( 'stripe_' . self::mode() . '_sk' );
	}

	public static function webhook_secret(): string {
		return (string) Settings::get( 'stripe_' . self::mode() . '_whsec' );
	}

	public static function configured(): bool {
		return '' !== self::secret_key();
	}

	/** @return array|\WP_Error Decoded JSON body. */
	private static function request( string $method, string $path, array $body = array(), string $idempotency_key = '' ) {
		$headers = array(
			'Authorization'  => 'Bearer ' . self::secret_key(),
			'Stripe-Version' => self::VERSION,
		);
		if ( '' !== $idempotency_key ) {
			$headers['Idempotency-Key'] = $idempotency_key;
		}
		$args = array(
			'method'  => $method,
			'timeout' => 20,
			'headers' => $headers,
		);
		if ( 'POST' === $method ) {
			$args['body'] = $body;
		}
		$res = wp_remote_request( self::API . $path, $args );
		if ( is_wp_error( $res ) ) {
			Log::error( 'Stripe request failed: ' . $res->get_error_message() );
			return new \WP_Error( 'fwh_sb_stripe_http', 'Stripe request failed.' );
		}
		$code = (int) wp_remote_retrieve_response_code( $res );
		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( $code >= 400 || ! is_array( $data ) ) {
			$msg = is_array( $data ) && isset( $data['error']['message'] ) ? (string) $data['error']['message'] : 'unknown error';
			Log::error( 'Stripe API ' . $code . ' on ' . $method . ' ' . $path . ': ' . $msg );
			return new \WP_Error( 'fwh_sb_stripe_api', 'Stripe API error.', array( 'http' => $code ) );
		}
		return $data;
	}

	public static function create_session( array $params, string $idempotency_key ) {
		return self::request( 'POST', 'checkout/sessions', $params, $idempotency_key );
	}

	public static function retrieve_session( string $id ) {
		return self::request( 'GET', 'checkout/sessions/' . rawurlencode( $id ) );
	}

	public static function expire_session( string $id ) {
		return self::request( 'POST', 'checkout/sessions/' . rawurlencode( $id ) . '/expire' );
	}

	/**
	 * Verify a Stripe-Signature header against the raw body (HMAC-SHA256, constant-time compare).
	 */
	public static function verify_signature( string $raw_body, string $header, string $secret, int $tolerance = 300 ): bool {
		if ( '' === $secret || '' === $header ) {
			return false;
		}
		$timestamp  = 0;
		$signatures = array();
		foreach ( explode( ',', $header ) as $part ) {
			$kv = explode( '=', trim( $part ), 2 );
			if ( 2 !== count( $kv ) ) {
				continue;
			}
			if ( 't' === $kv[0] ) {
				$timestamp = (int) $kv[1];
			} elseif ( 'v1' === $kv[0] ) {
				$signatures[] = $kv[1];
			}
		}
		if ( ! $timestamp || ! $signatures || abs( time() - $timestamp ) > $tolerance ) {
			return false;
		}
		$expected = hash_hmac( 'sha256', $timestamp . '.' . $raw_body, $secret );
		foreach ( $signatures as $sig ) {
			if ( hash_equals( $expected, $sig ) ) {
				return true;
			}
		}
		return false;
	}
}
