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
			'contact_whatsapp'     => '',
			'duration_label'       => '',
			'stripe_mode'          => 'test',
			'stripe_test_pk'       => '',
			'stripe_test_sk'       => '',
			'stripe_test_whsec'    => '',
			'stripe_live_pk'       => '',
			'stripe_live_sk'       => '',
			'stripe_live_whsec'    => '',
			'google_client_id'     => '',
			'google_client_secret' => '',
			'topbar_enabled'       => 0,
			'topbar_text'          => 'Practise your French one-to-one with a trainer.',
			'topbar_button'        => 'Book a speaking session',
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

	/** Currencies Stripe Checkout accepts (lower-case ISO 4217). */
	public const CURRENCIES = array(
		'usd', 'aed', 'afn', 'all', 'amd', 'ang', 'aoa', 'ars', 'aud', 'awg', 'azn', 'bam', 'bbd', 'bdt', 'bgn', 'bif', 'bmd', 'bnd', 'bob', 'brl', 'bsd', 'bwp', 'byn', 'bzd', 'cad', 'cdf', 'chf', 'clp', 'cny', 'cop', 'crc', 'cve', 'czk', 'djf', 'dkk', 'dop', 'dzd', 'egp', 'etb', 'eur', 'fjd', 'fkp', 'gbp', 'gel', 'gip', 'gmd', 'gnf', 'gtq', 'gyd', 'hkd', 'hnl', 'htg', 'huf', 'idr', 'ils', 'inr', 'isk', 'jmd', 'jpy', 'kes', 'kgs', 'khr', 'kmf', 'krw', 'kyd', 'kzt', 'lak', 'lbp', 'lkr', 'lrd', 'lsl', 'mad', 'mdl', 'mga', 'mkd', 'mmk', 'mnt', 'mop', 'mur', 'mvr', 'mwk', 'mxn', 'myr', 'mzn', 'nad', 'ngn', 'nio', 'nok', 'npr', 'nzd', 'pab', 'pen', 'pgk', 'php', 'pkr', 'pln', 'pyg', 'qar', 'ron', 'rsd', 'rub', 'rwf', 'sar', 'sbd', 'scr', 'sek', 'sgd', 'shp', 'sos', 'srd', 'szl', 'thb', 'tjs', 'top', 'try', 'ttd', 'twd', 'tzs', 'uah', 'ugx', 'uyu', 'uzs', 'vnd', 'vuv', 'wst', 'xaf', 'xcd', 'xof', 'xpf', 'yer', 'zar', 'zmw',
	);

	/** Currencies with no minor unit: the amount is sent to Stripe as-is, not multiplied by 100. */
	private const ZERO_DECIMAL = array( 'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf' );

	/** Price in minor currency units (e.g. cents; whole units for zero-decimal currencies). */
	public static function price_minor(): int {
		$factor = in_array( strtolower( (string) self::get( 'currency' ) ), self::ZERO_DECIMAL, true ) ? 1 : 100;
		return (int) round( (float) self::get( 'price' ) * $factor );
	}

	public static function sanitize( $input ): array {
		$input = is_array( $input ) ? array_filter( $input, 'is_scalar' ) : array(); // Ignore array-valued fields.
		$old   = self::all();
		$out   = $old;

		$text = array( 'session_name', 'stripe_test_pk', 'stripe_live_pk', 'google_client_id', 'topbar_text', 'topbar_button' );
		foreach ( $text as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = sanitize_text_field( wp_unslash( $input[ $k ] ) );
			}
		}
		if ( isset( $input['duration_label'] ) ) { // Text shown to students instead of "N minutes", e.g. "15-20 minutes".
			$out['duration_label'] = mb_substr( sanitize_text_field( wp_unslash( $input['duration_label'] ) ), 0, 40 );
		}
		if ( isset( $input['contact_whatsapp'] ) ) {
			$digits = preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $input['contact_whatsapp'] ) ) );
			if ( '' === $digits ) {
				$out['contact_whatsapp'] = '';
			} elseif ( preg_match( '/^\d{8,15}$/', $digits ) ) {
				$out['contact_whatsapp'] = '+' . $digits;
			} else {
				add_settings_error( self::OPTION, 'contact_whatsapp', __( 'Please enter the WhatsApp number with its country code, for example +14165550123.', 'fwh-speaking-booking' ) );
			}
		}
		// Secrets: blank keeps the stored value; anything else must look like the right kind of key.
		$formats = array(
			'stripe_test_sk'    => array( '/^(sk|rk)_test_[A-Za-z0-9]{16,}$/', 'a Stripe test secret key (starts with sk_test_)' ),
			'stripe_live_sk'    => array( '/^(sk|rk)_live_[A-Za-z0-9]{16,}$/', 'a Stripe live secret key (starts with sk_live_)' ),
			'stripe_test_whsec' => array( '/^whsec_[A-Za-z0-9+\/=_-]{16,}$/', 'a Stripe webhook signing secret (starts with whsec_)' ),
			'stripe_live_whsec' => array( '/^whsec_[A-Za-z0-9+\/=_-]{16,}$/', 'a Stripe webhook signing secret (starts with whsec_)' ),
		);
		foreach ( self::SECRET_KEYS as $k ) {
			$v = isset( $input[ $k ] ) ? trim( sanitize_text_field( wp_unslash( $input[ $k ] ) ) ) : '';
			if ( '' === $v ) {
				continue;
			}
			if ( isset( $formats[ $k ] ) && ! preg_match( $formats[ $k ][0], $v ) ) {
				/* translators: %s: description of the expected key */
				add_settings_error( self::OPTION, $k, sprintf( __( 'That value is not %s, so it was not saved. If your browser filled in a saved password, clear the field.', 'fwh-speaking-booking' ), $formats[ $k ][1] ) );
				continue;
			}
			$out[ $k ] = $v;
		}
		foreach ( array( 'stripe_test_pk' => 'pk_test_', 'stripe_live_pk' => 'pk_live_' ) as $k => $prefix ) {
			if ( isset( $out[ $k ] ) && '' !== $out[ $k ] && 0 !== strpos( $out[ $k ], $prefix ) ) {
				add_settings_error( self::OPTION, $k, sprintf( /* translators: %s: prefix */ __( 'The publishable key should start with %s.', 'fwh-speaking-booking' ), $prefix ) );
				$out[ $k ] = $old[ $k ];
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
			if ( in_array( $cur, self::CURRENCIES, true ) ) {
				$out['currency'] = $cur;
			} else {
				add_settings_error( self::OPTION, 'currency', __( 'That is not a currency code Stripe supports (for example CAD, USD, INR - India is INR, not IND). The previous currency was kept.', 'fwh-speaking-booking' ) );
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
		if ( isset( $input['topbar_enabled'] ) ) {
			$out['topbar_enabled'] = (int) (bool) (int) $input['topbar_enabled'];
		}
		if ( isset( $input['stripe_mode'] ) ) {
			$out['stripe_mode'] = 'live' === $input['stripe_mode'] ? 'live' : 'test';
		}
		return $out;
	}
}
