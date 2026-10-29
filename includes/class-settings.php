<?php
/**
 * Plugin settings stored in one option (autoload off).
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Settings {

	public const OPTION = 'fwh_sb_settings';

	/** Keys holding secrets: masked in the UI, blank input keeps the stored value. */
	public const SECRET_KEYS = array(
		'stripe_test_sk',
		'stripe_test_whsec',
		'stripe_live_sk',
		'stripe_live_whsec',
		'google_client_secret',
	);

	public static function defaults(): array {
		return array(
			'session_name'         => 'French Speaking Session',
			'duration'             => 20,
			'price'                => '',
			'currency'             => 'cad',
			'gap_minutes'          => 0,
			'booking_window_days'  => 30,
			'min_notice_hours'     => 2,
			'coordinator_email'    => '',
			'contact_email'        => '',
			'stripe_mode'          => 'test',
			'stripe_test_pk'       => '',
			'stripe_test_sk'       => '',
			'stripe_test_whsec'    => '',
			'stripe_live_pk'       => '',
			'stripe_live_sk'       => '',
			'stripe_live_whsec'    => '',
			'google_client_id'     => '',
			'google_client_secret' => '',
		);
	}

	public static function install_defaults(): void {
		// add_option with autoload off; no-op if it already exists.
		add_option( self::OPTION, self::defaults(), '', false );
	}

	public static function all(): array {
		$stored = get_option( self::OPTION, array() );
		return array_merge( self::defaults(), is_array( $stored ) ? $stored : array() );
	}

	public static function get( string $key ) {
		$all = self::all();
		return $all[ $key ] ?? null;
	}

	/** Price in minor currency units (e.g. cents). */
	public static function price_minor(): int {
		return (int) round( (float) self::get( 'price' ) * 100 );
	}

	public static function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : array();
		$old   = self::all();
		$out   = $old;

		$text = array( 'session_name', 'stripe_test_pk', 'stripe_live_pk', 'google_client_id' );
		foreach ( $text as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = sanitize_text_field( wp_unslash( $input[ $k ] ) );
			}
		}
		foreach ( self::SECRET_KEYS as $k ) {
			$v = isset( $input[ $k ] ) ? trim( sanitize_text_field( wp_unslash( $input[ $k ] ) ) ) : '';
			if ( '' !== $v ) {
				$out[ $k ] = $v;
			}
		}
		$ints = array(
			'duration'            => array( 5, 240 ),
			'gap_minutes'         => array( 0, 240 ),
			'booking_window_days' => array( 1, 365 ),
			'min_notice_hours'    => array( 0, 720 ),
		);
		foreach ( $ints as $k => $range ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = max( $range[0], min( $range[1], (int) $input[ $k ] ) );
			}
		}
		if ( isset( $input['price'] ) ) {
			$price        = preg_replace( '/[^0-9.]/', '', wp_unslash( $input['price'] ) );
			$out['price'] = '' === $price ? '' : number_format( (float) $price, 2, '.', '' );
		}
		if ( isset( $input['currency'] ) ) {
			$cur = strtolower( preg_replace( '/[^A-Za-z]/', '', wp_unslash( $input['currency'] ) ) );
			if ( 3 === strlen( $cur ) ) {
				$out['currency'] = $cur;
			} else {
				add_settings_error( self::OPTION, 'currency', __( 'Currency must be a 3-letter code such as CAD.', 'fwh-speaking-booking' ) );
			}
		}
		foreach ( array( 'coordinator_email', 'contact_email' ) as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$email = sanitize_email( wp_unslash( $input[ $k ] ) );
				if ( '' !== $email && ! is_email( $email ) ) {
					add_settings_error( self::OPTION, $k, __( 'Please enter a valid email address.', 'fwh-speaking-booking' ) );
				} else {
					$out[ $k ] = $email;
				}
			}
		}
		if ( isset( $input['stripe_mode'] ) ) {
			$out['stripe_mode'] = 'live' === $input['stripe_mode'] ? 'live' : 'test';
		}
		return $out;
	}
}
