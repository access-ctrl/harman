<?php
/**
 * Settings page (Settings API).
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Admin_Settings {

	public function init(): void {
		add_action( 'admin_init', array( $this, 'register' ) );
		add_filter( 'option_page_capability_fwh_sb_settings_group', static fn() => 'fwh_sb_manage' );
	}

	public function register(): void {
		register_setting(
			'fwh_sb_settings_group',
			Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( Settings::class, 'sanitize' ),
				'default'           => Settings::defaults(),
			)
		);
	}

	private function field( string $key, string $label, string $type = 'text', string $help = '' ): void {
		$s      = Settings::all();
		$name   = Settings::OPTION . '[' . $key . ']';
		$secret = in_array( $key, Settings::SECRET_KEYS, true );
		$value  = $secret ? '' : (string) $s[ $key ];
		$ph     = ( $secret && '' !== (string) $s[ $key ] ) ? '•••••••• (saved, leave blank to keep)' : '';
		echo '<tr><th scope="row"><label for="' . esc_attr( 'fwh_sb_' . $key ) . '">' . esc_html( $label ) . '</label></th><td>';
		printf(
			'<input id="%1$s" name="%2$s" type="%3$s" value="%4$s" placeholder="%5$s" class="regular-text" autocomplete="off">',
			esc_attr( 'fwh_sb_' . $key ),
			esc_attr( $name ),
			esc_attr( $secret ? 'password' : $type ),
			esc_attr( $value ),
			esc_attr( $ph )
		);
		if ( '' !== $help ) {
			echo '<p class="description">' . esc_html( $help ) . '</p>';
		}
		echo '</td></tr>';
	}

	public function render(): void {
		if ( ! current_user_can( 'fwh_sb_manage' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'fwh-speaking-booking' ) );
		}
		$s = Settings::all();
		echo '<div class="wrap"><h1>' . esc_html__( 'Speaking Booking Settings', 'fwh-speaking-booking' ) . '</h1>';
		settings_errors( Settings::OPTION );
		echo '<form method="post" action="options.php">';
		settings_fields( 'fwh_sb_settings_group' );

		echo '<h2>' . esc_html__( 'Session', 'fwh-speaking-booking' ) . '</h2><table class="form-table" role="presentation">';
		$this->field( 'session_name', __( 'Session name', 'fwh-speaking-booking' ) );
		$this->field( 'duration', __( 'Duration (minutes)', 'fwh-speaking-booking' ), 'number' );
		$this->field( 'price', __( 'Price', 'fwh-speaking-booking' ), 'text', __( 'In major units, e.g. 25.00', 'fwh-speaking-booking' ) );
		$this->field( 'currency', __( 'Currency', 'fwh-speaking-booking' ), 'text', __( '3-letter code, e.g. CAD', 'fwh-speaking-booking' ) );
		$this->field( 'gap_minutes', __( 'Gap between sessions (minutes)', 'fwh-speaking-booking' ), 'number' );
		$this->field( 'booking_window_days', __( 'Booking window (days)', 'fwh-speaking-booking' ), 'number' );
		$this->field( 'min_notice_hours', __( 'Minimum notice (hours)', 'fwh-speaking-booking' ), 'number' );
		$this->field( 'coordinator_email', __( 'Coordinator email', 'fwh-speaking-booking' ), 'email' );
		$this->field( 'contact_email', __( 'Student contact email', 'fwh-speaking-booking' ), 'email' );
		echo '</table>';

		echo '<h2>Stripe</h2><table class="form-table" role="presentation"><tr><th scope="row">' . esc_html__( 'Mode', 'fwh-speaking-booking' ) . '</th><td>';
		foreach ( array( 'test', 'live' ) as $mode ) {
			printf(
				'<label style="margin-right:16px"><input type="radio" name="%s[stripe_mode]" value="%s" %s> %s</label>',
				esc_attr( Settings::OPTION ),
				esc_attr( $mode ),
				checked( $s['stripe_mode'], $mode, false ),
				esc_html( ucfirst( $mode ) )
			);
		}
		echo '</td></tr>';
		foreach ( array( 'test', 'live' ) as $mode ) {
			$label = ucfirst( $mode );
			/* translators: %s: Test or Live */
			$this->field( "stripe_{$mode}_pk", sprintf( __( '%s publishable key', 'fwh-speaking-booking' ), $label ) );
			/* translators: %s: Test or Live */
			$this->field( "stripe_{$mode}_sk", sprintf( __( '%s secret key', 'fwh-speaking-booking' ), $label ) );
			/* translators: %s: Test or Live */
			$this->field( "stripe_{$mode}_whsec", sprintf( __( '%s webhook signing secret', 'fwh-speaking-booking' ), $label ) );
		}
		echo '</table>';
		echo '<p class="description">' . esc_html__( 'Webhook URL:', 'fwh-speaking-booking' ) . ' <code>' . esc_html( rest_url( 'fwh-sb/v1/stripe-webhook' ) ) . '</code></p>';

		echo '<h2>Google</h2><table class="form-table" role="presentation">';
		$this->field( 'google_client_id', __( 'OAuth client ID', 'fwh-speaking-booking' ) );
		$this->field( 'google_client_secret', __( 'OAuth client secret', 'fwh-speaking-booking' ) );
		echo '</table>';

		submit_button();
		echo '</form></div>';
	}
}
