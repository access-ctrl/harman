<?php
/**
 * Public, rate-limited slots route.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Rest_Slots {

	private const MAX_DAYS     = 31;
	private const RATE_LIMIT   = 120; // Requests per window.
	private const RATE_WINDOW  = 60;  // Seconds.

	public function init(): void {
		add_action( 'rest_api_init', array( $this, 'register' ) );
	}

	public function register(): void {
		register_rest_route(
			'fwh-sb/v1',
			'/slots',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'handle' ),
				'permission_callback' => '__return_true', // Public by design: only free UTC start times are returned.
				'args'                => array(
					'from' => array(
						'required'          => true,
						'validate_callback' => static fn( $v ) => is_string( $v ) && Time::valid_date( $v ),
					),
					'to'   => array(
						'required'          => true,
						'validate_callback' => static fn( $v ) => is_string( $v ) && Time::valid_date( $v ),
					),
				),
			)
		);
	}

	public function handle( \WP_REST_Request $request ) {
		nocache_headers();

		if ( ! Rate_Limit::allow( 'slots', self::RATE_LIMIT, self::RATE_WINDOW ) ) {
			return new \WP_Error( 'fwh_sb_rate_limited', __( 'Too many requests. Please wait a moment.', 'fwh-speaking-booking' ), array( 'status' => 429 ) );
		}

		$from = (string) $request['from'];
		$to   = (string) $request['to'];
		if ( $to < $from ) {
			return new \WP_Error( 'fwh_sb_bad_range', __( 'Invalid date range.', 'fwh-speaking-booking' ), array( 'status' => 400 ) );
		}
		$days = ( new \DateTimeImmutable( $from, Time::utc() ) )->diff( new \DateTimeImmutable( $to, Time::utc() ) )->days;
		if ( $days >= self::MAX_DAYS ) {
			return new \WP_Error( 'fwh_sb_range_too_large', __( 'Please request at most 31 days at a time.', 'fwh-speaking-booking' ), array( 'status' => 400 ) );
		}

		$response = new \WP_REST_Response(
			array(
				'timezone' => 'America/Toronto',
				'days'     => (object) Availability::public_slots( $from, $to ),
			)
		);
		$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );
		return $response;
	}
}
