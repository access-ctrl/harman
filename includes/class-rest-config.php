<?php
/**
 * Public widget configuration, fetched fresh when the top bar modal opens so that cached
 * pages never serve a stale nonce or booking window.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Rest_Config {

	public function init(): void {
		add_action( 'rest_api_init', array( $this, 'register' ) );
	}

	public function register(): void {
		register_rest_route(
			'fwh-sb/v1',
			'/config',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'handle' ),
				'permission_callback' => '__return_true', // Public by design: session name, price, dates and a REST nonce only.
			)
		);
	}

	public function handle() {
		nocache_headers();
		if ( ! Rate_Limit::allow( 'config', 60, 60 ) ) {
			return new \WP_Error( 'fwh_sb_rate', __( 'Too many requests.', 'fwh-speaking-booking' ), array( 'status' => 429 ) );
		}
		// This route carries no nonce, so WordPress treats the caller as logged out. A logged-in visitor's
		// later requests are checked against their account, so mint the nonce for that account or it is rejected.
		$uid = (int) wp_validate_logged_in_cookie( false );
		if ( $uid ) {
			wp_set_current_user( $uid );
		}
		$response = new \WP_REST_Response( Shortcode::config() );
		$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );
		return $response;
	}
}
