<?php
/**
 * Google OAuth 2.0 and Calendar API over wp_remote_*. Secrets are never logged.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Google_Client {

	public const SCOPE = 'https://www.googleapis.com/auth/calendar.events';

	private const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
	private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
	private const CAL_URL   = 'https://www.googleapis.com/calendar/v3/';

	public static function redirect_uri(): string {
		return admin_url( 'admin-post.php?action=fwh_sb_google_callback' );
	}

	public static function configured(): bool {
		return '' !== (string) Settings::get( 'google_client_id' ) && '' !== (string) Settings::get( 'google_client_secret' );
	}

	/** One-time state token bound to the user and the connection being made. */
	public static function auth_url( int $trainer_id, int $user_id ): string {
		$state = wp_generate_password( 32, false );
		set_transient( 'fwh_sb_gstate_' . $state, array( $user_id, $trainer_id ), 10 * MINUTE_IN_SECONDS );
		return self::AUTH_URL . '?' . http_build_query(
			array(
				'client_id'     => (string) Settings::get( 'google_client_id' ),
				'redirect_uri'  => self::redirect_uri(),
				'response_type' => 'code',
				'scope'         => self::SCOPE,
				'access_type'   => 'offline',
				'prompt'        => 'consent',
				'state'         => $state,
			),
			'',
			'&',
			PHP_QUERY_RFC3986
		);
	}

	/** @return array|null [ user_id, trainer_id ] if the state is valid (consumed once). */
	public static function consume_state( string $state ): ?array {
		$data = get_transient( 'fwh_sb_gstate_' . $state );
		delete_transient( 'fwh_sb_gstate_' . $state );
		return is_array( $data ) ? $data : null;
	}

	/** @return array|\WP_Error Decoded JSON. */
	private static function http( string $method, string $url, array $args = array() ) {
		$args['method']  = $method;
		$args['timeout'] = 20;
		$res             = wp_remote_request( $url, $args );
		if ( is_wp_error( $res ) ) {
			error_log( '[fwh-sb] Google request failed: ' . $res->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return new \WP_Error( 'fwh_sb_google_http', 'Google request failed.' );
		}
		$code = (int) wp_remote_retrieve_response_code( $res );
		$raw  = wp_remote_retrieve_body( $res );
		$data = '' === $raw ? array() : json_decode( $raw, true );
		if ( $code >= 400 || ! is_array( $data ) ) {
			$err = is_array( $data ) && isset( $data['error'] ) ? ( is_array( $data['error'] ) ? (string) ( $data['error']['message'] ?? 'error' ) : (string) $data['error'] ) : 'error';
			error_log( '[fwh-sb] Google API ' . $code . ' on ' . $method . ' ' . wp_parse_url( $url, PHP_URL_PATH ) . ': ' . $err ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return new \WP_Error( 'fwh_sb_google_api', 'Google API error.', array( 'http' => $code, 'error' => is_array( $data ) && isset( $data['error'] ) && is_string( $data['error'] ) ? $data['error'] : '' ) );
		}
		return $data;
	}

	/** @return array|\WP_Error Token response (contains refresh_token on first consent). */
	public static function exchange_code( string $code ) {
		return self::http(
			'POST',
			self::TOKEN_URL,
			array(
				'body' => array(
					'code'          => $code,
					'client_id'     => (string) Settings::get( 'google_client_id' ),
					'client_secret' => (string) Settings::get( 'google_client_secret' ),
					'redirect_uri'  => self::redirect_uri(),
					'grant_type'    => 'authorization_code',
				),
			)
		);
	}

	/**
	 * Short-lived access token for a stored connection. On invalid_grant the connection
	 * is removed and the coordinator is told.
	 *
	 * @return string|\WP_Error
	 */
	public static function access_token( int $trainer_id ) {
		$cached = get_transient( 'fwh_sb_gat_' . $trainer_id );
		if ( is_string( $cached ) && '' !== $cached ) {
			return $cached;
		}
		$refresh = Google_Tokens::get( $trainer_id );
		if ( '' === $refresh ) {
			return new \WP_Error( 'fwh_sb_google_disconnected', 'Google is not connected.' );
		}
		$res = self::http(
			'POST',
			self::TOKEN_URL,
			array(
				'body' => array(
					'refresh_token' => $refresh,
					'client_id'     => (string) Settings::get( 'google_client_id' ),
					'client_secret' => (string) Settings::get( 'google_client_secret' ),
					'grant_type'    => 'refresh_token',
				),
			)
		);
		if ( is_wp_error( $res ) ) {
			$data = $res->get_error_data();
			if ( is_array( $data ) && 'invalid_grant' === ( $data['error'] ?? '' ) ) {
				Google_Tokens::delete( $trainer_id );
				$who = 0 === $trainer_id ? 'the fallback account' : 'trainer #' . $trainer_id;
				Notify::coordinator( 'Google connection lost', "Google rejected the saved connection for {$who} (invalid_grant). It has been marked disconnected and must be reconnected in wp-admin." );
				return new \WP_Error( 'fwh_sb_google_disconnected', 'Google connection was revoked.' );
			}
			return $res;
		}
		if ( empty( $res['access_token'] ) ) {
			return new \WP_Error( 'fwh_sb_google_token', 'No access token returned.' );
		}
		set_transient( 'fwh_sb_gat_' . $trainer_id, $res['access_token'], max( 60, (int) ( $res['expires_in'] ?? 3600 ) - 120 ) );
		return (string) $res['access_token'];
	}

	/** @return array|\WP_Error */
	private static function calendar( int $trainer_id, string $method, string $path, ?array $json = null ) {
		$token = self::access_token( $trainer_id );
		if ( is_wp_error( $token ) ) {
			return $token;
		}
		$args = array( 'headers' => array( 'Authorization' => 'Bearer ' . $token ) );
		if ( null !== $json ) {
			$args['headers']['Content-Type'] = 'application/json';
			$args['body']                    = wp_json_encode( $json );
		}
		return self::http( $method, self::CAL_URL . $path, $args );
	}

	public static function insert_event( int $trainer_id, array $event ) {
		return self::calendar( $trainer_id, 'POST', 'calendars/primary/events?conferenceDataVersion=1&sendUpdates=none', $event );
	}

	public static function get_event( int $trainer_id, string $event_id ) {
		return self::calendar( $trainer_id, 'GET', 'calendars/primary/events/' . rawurlencode( $event_id ) . '?conferenceDataVersion=1' );
	}

	public static function delete_event( int $trainer_id, string $event_id ) {
		return self::calendar( $trainer_id, 'DELETE', 'calendars/primary/events/' . rawurlencode( $event_id ) . '?sendUpdates=none' );
	}

	/** Extract the Meet URL from an event, or '' if it is not ready. */
	public static function meet_link( array $event ): string {
		foreach ( $event['conferenceData']['entryPoints'] ?? array() as $ep ) {
			if ( 'video' === ( $ep['entryPointType'] ?? '' ) && ! empty( $ep['uri'] ) ) {
				return (string) $ep['uri'];
			}
		}
		return ! empty( $event['hangoutLink'] ) ? (string) $event['hangoutLink'] : '';
	}
}
