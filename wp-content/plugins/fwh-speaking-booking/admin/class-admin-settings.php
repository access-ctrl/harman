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
			'<input id="%1$s" name="%2$s" type="%3$s" value="%4$s" placeholder="%5$s" class="regular-text" autocomplete="%6$s" %7$s>',
			esc_attr( 'fwh_sb_' . $key ),
			esc_attr( $name ),
			esc_attr( $secret ? 'password' : $type ),
			esc_attr( $value ),
			esc_attr( $ph ),
			$secret ? 'new-password' : 'off', // new-password stops browsers filling saved passwords into key fields.
			$secret ? 'data-lpignore="true" data-1p-ignore="true"' : ''
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
		Mailer::failure_notice();
		$mode = Stripe_Client::mode();
		if ( ! Stripe_Client::configured() ) {
			echo '<div class="notice notice-warning"><p>' . esc_html(
				sprintf(
					/* translators: %s: test or live */
					__( 'Payments are not ready: the Stripe %s secret key is missing or is not a valid %s-mode key. Bookings will fail until it is fixed.', 'fwh-speaking-booking' ),
					$mode,
					$mode
				)
			) . '</p></div>';
		}
		if ( ! in_array( strtolower( (string) Settings::get( 'currency' ) ), Settings::CURRENCIES, true ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'The saved currency is not one Stripe supports (India is INR, not IND). Please correct it.', 'fwh-speaking-booking' ) . '</p></div>';
		}
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
		$this->field( 'contact_whatsapp', __( 'Student contact WhatsApp number', 'fwh-speaking-booking' ), 'text', __( 'With country code, e.g. +14165550123. Shown in the student confirmation email.', 'fwh-speaking-booking' ) );
		$this->field( 'duration_label', __( 'Session length shown to students', 'fwh-speaking-booking' ), 'text', __( 'e.g. 15-20 minutes. Leave blank to show the duration above. The real slot length stays as set above.', 'fwh-speaking-booking' ) );
		echo '</table>';

		echo '<h2>' . esc_html__( 'Top bar', 'fwh-speaking-booking' ) . '</h2><table class="form-table" role="presentation"><tr><th scope="row">' . esc_html__( 'Show top bar', 'fwh-speaking-booking' ) . '</th><td>';
		printf(
			'<input type="hidden" name="%1$s[topbar_enabled]" value="0"><label><input type="checkbox" name="%1$s[topbar_enabled]" value="1" %2$s> %3$s</label><p class="description">%4$s</p>',
			esc_attr( Settings::OPTION ),
			checked( (int) $s['topbar_enabled'], 1, false ),
			esc_html__( 'Show a strip at the top of every page with a button that opens the booking form', 'fwh-speaking-booking' ),
			esc_html__( 'Needs the theme to call wp_body_open() right after the opening body tag (the Harman theme does).', 'fwh-speaking-booking' )
		);
		echo '</td></tr>';
		$this->field( 'topbar_text', __( 'Strip text', 'fwh-speaking-booking' ) );
		$this->field( 'topbar_button', __( 'Button label', 'fwh-speaking-booking' ) );
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

		echo '<p>' . esc_html__( 'Redirect URI to add in Google Cloud (Authorized redirect URIs):', 'fwh-speaking-booking' ) . ' <code>' . esc_html( Google_Client::redirect_uri() ) . '</code></p>';

		submit_button();
		echo '</form>';

		echo '<h2>' . esc_html__( 'Fallback Google account', 'fwh-speaking-booking' ) . '</h2>';
		echo '<p class="description">' . esc_html__( 'Used to create the Meet event when a trainer has not connected their own account. Save the client ID and secret above first.', 'fwh-speaking-booking' ) . '</p>';
		Admin_Google::connection_box( 0 );
		echo '</div>';
	}
}
