<?php
/**
 * Public REST routes: book, booking-status and the Stripe webhook.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Rest_Booking {

	private const NS = 'fwh-sb/v1';

	public function init(): void {
		add_action( 'rest_api_init', array( $this, 'register' ) );
	}

	public function register(): void {
		register_rest_route(
			self::NS,
			'/book',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'book' ),
				'permission_callback' => '__return_true', // Public by design: protected by nonce, honeypot and rate limit.
			)
		);
		register_rest_route(
			self::NS,
			'/booking-status',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'status' ),
				'permission_callback' => '__return_true', // Public by design: ref and session_id must both match.
				'args'                => array(
					'ref'        => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'session_id' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/stripe-webhook',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'webhook' ),
				'permission_callback' => '__return_true', // Public by design: authenticated by Stripe signature on the raw body.
			)
		);
	}

	private function error( string $code, string $message, int $status ): \WP_Error {
		return new \WP_Error( $code, $message, array( 'status' => $status ) );
	}

	public function book( \WP_REST_Request $request ) {
		nocache_headers();

		if ( ! wp_verify_nonce( (string) $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			return $this->error( 'fwh_sb_nonce', __( 'Your session expired. Please refresh the page and try again.', 'fwh-speaking-booking' ), 403 );
		}
		if ( ! Rate_Limit::allow( 'book', 10, 600 ) ) {
			return $this->error( 'fwh_sb_rate', __( 'Too many attempts. Please wait a few minutes.', 'fwh-speaking-booking' ), 429 );
		}
		$p = $request->get_json_params();
		$p = is_array( $p ) ? $p : array();
		$s = static fn( string $k ): string => isset( $p[ $k ] ) && is_string( $p[ $k ] ) ? trim( sanitize_text_field( $p[ $k ] ) ) : '';

		if ( '' !== $s( 'website' ) ) { // Honeypot.
			return $this->error( 'fwh_sb_invalid', __( 'Invalid request.', 'fwh-speaking-booking' ), 400 );
		}
		$name  = $s( 'name' );
		$email = sanitize_email( $s( 'email' ) );
		$phone = $s( 'phone' );
		$tz    = $s( 'student_tz' );
		$start = $s( 'start' );

		if ( '' === $name || strlen( $name ) > 190 ) {
			return $this->error( 'fwh_sb_invalid', __( 'Please enter your name.', 'fwh-speaking-booking' ), 400 );
		}
		if ( ! is_email( $email ) || strlen( $email ) > 190 ) {
			return $this->error( 'fwh_sb_invalid', __( 'Please enter a valid email address.', 'fwh-speaking-booking' ), 400 );
		}
		if ( ! preg_match( '/^\+\d[\d\s\-()]{6,19}$/', $phone ) ) {
			return $this->error( 'fwh_sb_invalid', __( 'Please enter your phone number starting with the country code.', 'fwh-speaking-booking' ), 400 );
		}
		if ( ! in_array( $tz, timezone_identifiers_list(), true ) ) {
			return $this->error( 'fwh_sb_invalid', __( 'Unrecognised time zone.', 'fwh-speaking-booking' ), 400 );
		}
		$dt = \DateTimeImmutable::createFromFormat( '!Y-m-d\TH:i:s\Z', $start, Time::utc() );
		if ( ! $dt || $dt->format( 'Y-m-d\TH:i:s\Z' ) !== $start ) {
			return $this->error( 'fwh_sb_invalid', __( 'Invalid time selected.', 'fwh-speaking-booking' ), 400 );
		}
		$page_url = $this->safe_page_url( $s( 'page_url' ) );
		if ( '' === $page_url ) {
			return $this->error( 'fwh_sb_invalid', __( 'Invalid request.', 'fwh-speaking-booking' ), 400 );
		}

		$res = Bookings::place(
			array(
				'start_utc' => $dt->format( Time::FORMAT ),
				'name'      => $name,
				'email'     => $email,
				'phone'     => $phone,
				'tz'        => $tz,
				'page_url'  => $page_url,
			)
		);
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		return new \WP_REST_Response( $res );
	}

	/** Accept only an http(s) URL on this site, stripped of query string and fragment. */
	private function safe_page_url( string $url ): string {
		$url = esc_url_raw( $url, array( 'http', 'https' ) );
		$h   = wp_parse_url( $url );
		$me  = wp_parse_url( home_url() );
		if ( empty( $h['host'] ) || empty( $me['host'] ) || strtolower( $h['host'] ) !== strtolower( $me['host'] ) ) {
			return '';
		}
		return ( $h['scheme'] ?? 'https' ) . '://' . $h['host'] . ( isset( $h['port'] ) ? ':' . $h['port'] : '' ) . ( $h['path'] ?? '/' );
	}

	public function status( \WP_REST_Request $request ) {
		nocache_headers();
		if ( ! Rate_Limit::allow( 'status', 120, 60 ) ) {
			return $this->error( 'fwh_sb_rate', __( 'Too many requests.', 'fwh-speaking-booking' ), 429 );
		}
		$b = Bookings::get_by_ref( (string) $request['ref'] );
		if ( ! $b || empty( $b->stripe_session_id ) || ! hash_equals( $b->stripe_session_id, (string) $request['session_id'] ) ) {
			return $this->error( 'fwh_sb_not_found', __( 'Booking not found.', 'fwh-speaking-booking' ), 404 );
		}
		// If the webhook has not arrived, check Stripe directly (at most every 10 seconds per booking).
		if ( 'held' === $b->status ) {
			$key = 'fwh_sb_sync_' . $b->id;
			if ( false === get_transient( $key ) ) {
				set_transient( $key, 1, 10 );
				Bookings::sync_with_stripe( $b );
				$b = Bookings::get( (int) $b->id );
			}
		}
		$out = array( 'status' => $b->status );
		if ( 'paid' === $b->status ) {
			$out['start']      = Availability::to_iso( $b->start_utc );
			$out['student_tz'] = $b->student_tz;
		}
		return new \WP_REST_Response( $out );
	}

	public function webhook( \WP_REST_Request $request ) {
		global $wpdb;
		$raw = $request->get_body();
		$sig = (string) $request->get_header( 'stripe_signature' );
		if ( ! Stripe_Client::verify_signature( $raw, $sig, Stripe_Client::webhook_secret() ) ) {
			return $this->error( 'fwh_sb_signature', 'Invalid signature.', 400 );
		}
		$event = json_decode( $raw, true );
		if ( ! is_array( $event ) || empty( $event['id'] ) || empty( $event['type'] ) ) {
			return $this->error( 'fwh_sb_payload', 'Invalid payload.', 400 );
		}
		$id   = (string) $event['id'];
		$type = (string) $event['type'];

		$table = Schema::table( 'stripe_events' );
		$new   = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$table} (event_id, type, received_utc) VALUES (%s, %s, %s)", $id, $type, Time::now_utc() ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( 1 !== (int) $new ) {
			return new \WP_REST_Response( array( 'received' => true, 'duplicate' => true ) );
		}

		try {
			$object = $event['data']['object'] ?? array();
			if ( 'checkout.session.completed' === $type && is_array( $object ) && 'paid' === ( $object['payment_status'] ?? '' ) ) {
				Bookings::apply_paid_session( $object );
			} elseif ( 'checkout.session.expired' === $type && is_array( $object ) ) {
				$b = Bookings::get_by_ref( (string) ( $object['client_reference_id'] ?? '' ) );
				if ( $b && 'held' === $b->status && ( $object['id'] ?? '' ) === $b->stripe_session_id ) {
					Bookings::expire_held( (int) $b->id );
				}
			}
		} catch ( \Throwable $e ) {
			// Let Stripe retry: forget the event so it is processed next time.
			$wpdb->delete( $table, array( 'event_id' => $id ), array( '%s' ) );
			error_log( '[fwh-sb] Webhook processing failed: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return $this->error( 'fwh_sb_error', 'Processing failed.', 500 );
		}
		return new \WP_REST_Response( array( 'received' => true ) );
	}
}
