<?php
/**
 * Google connection screens: connect start, OAuth callback, status and failed-Meet retry.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Admin_Google {

	public function init(): void {
		add_action( 'admin_post_fwh_sb_google_connect', array( $this, 'handle_connect' ) );
		add_action( 'admin_post_fwh_sb_google_callback', array( $this, 'handle_callback' ) );
		add_action( 'admin_post_fwh_sb_google_disconnect', array( $this, 'handle_disconnect' ) );
		add_action( 'admin_post_fwh_sb_retry_meet', array( $this, 'handle_retry' ) );
		add_action( 'admin_notices', array( $this, 'failed_notice' ) );
	}

	/** May the current user manage this Google connection (0 = fallback)? */
	private function can_manage( int $trainer_id ): bool {
		if ( current_user_can( 'fwh_sb_manage' ) ) {
			return true;
		}
		if ( 0 === $trainer_id || ! current_user_can( 'fwh_sb_trainer' ) ) {
			return false;
		}
		$own = Repo::trainer_by_user( get_current_user_id() );
		return $own && (int) $own->id === $trainer_id;
	}

	private function return_page( int $trainer_id ): array {
		if ( 0 === $trainer_id ) {
			return array( 'fwh-sb', array() );
		}
		if ( current_user_can( 'fwh_sb_manage' ) ) {
			return array( 'fwh-sb-trainers', array( 'action' => 'edit', 'trainer' => $trainer_id ) );
		}
		return array( 'fwh-sb-my-schedule', array() );
	}

	public function handle_connect(): void {
		check_admin_referer( 'fwh_sb_google_connect' );
		$tid = isset( $_POST['trainer_id'] ) ? absint( $_POST['trainer_id'] ) : 0;
		if ( ! $this->can_manage( $tid ) || ( $tid && ! Repo::trainer( $tid ) ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'fwh-speaking-booking' ), 403 );
		}
		if ( ! Google_Client::configured() ) {
			Notice::add( 'error', __( 'Enter the Google OAuth client ID and secret in Settings first.', 'fwh-speaking-booking' ) );
			list( $page, $args ) = $this->return_page( $tid );
			Notice::redirect( $page, $args );
		}
		wp_redirect( Google_Client::auth_url( $tid, get_current_user_id() ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- external Google URL.
		exit;
	}

	public function handle_callback(): void {
		// The state token (one-time, bound to this user) authenticates this request.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
		$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		$error = isset( $_GET['error'] );
		// phpcs:enable
		$data = '' !== $state ? Google_Client::consume_state( $state ) : null;
		if ( ! $data || (int) $data[0] !== get_current_user_id() || ! $this->can_manage( (int) $data[1] ) ) {
			wp_die( esc_html__( 'This connection link is invalid or has expired. Please try again.', 'fwh-speaking-booking' ), 403 );
		}
		$tid = (int) $data[1];
		list( $page, $args ) = $this->return_page( $tid );

		if ( $error || '' === $code ) {
			Notice::add( 'error', __( 'Google connection was cancelled.', 'fwh-speaking-booking' ) );
			Notice::redirect( $page, $args );
		}
		$tokens = Google_Client::exchange_code( $code );
		if ( is_wp_error( $tokens ) || empty( $tokens['refresh_token'] ) ) {
			Notice::add( 'error', __( 'Google did not return a refresh token. Remove the app from your Google account permissions and try again.', 'fwh-speaking-booking' ) );
			Notice::redirect( $page, $args );
		}
		$trainer = $tid ? Repo::trainer( $tid ) : null;
		Google_Tokens::save( $tid, (string) $tokens['refresh_token'], $trainer ? (string) $trainer->google_email : '' );
		Notice::add( 'success', __( 'Google account connected.', 'fwh-speaking-booking' ) );
		Notice::redirect( $page, $args );
	}

	public function handle_disconnect(): void {
		check_admin_referer( 'fwh_sb_google_disconnect' );
		$tid = isset( $_POST['trainer_id'] ) ? absint( $_POST['trainer_id'] ) : 0;
		if ( ! $this->can_manage( $tid ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'fwh-speaking-booking' ), 403 );
		}
		Google_Tokens::delete( $tid );
		Notice::add( 'success', __( 'Google account disconnected.', 'fwh-speaking-booking' ) );
		list( $page, $args ) = $this->return_page( $tid );
		Notice::redirect( $page, $args );
	}

	public function handle_retry(): void {
		check_admin_referer( 'fwh_sb_retry_meet' );
		if ( ! current_user_can( 'fwh_sb_manage' ) && ! current_user_can( 'fwh_sb_view_bookings' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'fwh-speaking-booking' ), 403 );
		}
		$id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
		Meet_Service::manual_retry( $id );
		$b = $id ? Bookings::get( $id ) : null;
		Notice::add( $b && 'ok' === $b->meet_status ? 'success' : 'error', $b && 'ok' === $b->meet_status ? __( 'Meet link created and emails sent.', 'fwh-speaking-booking' ) : __( 'Still could not create the Meet link. Check the Google connection and try again.', 'fwh-speaking-booking' ) );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
		exit;
	}

	/** Connection status plus Connect/Reconnect (and Disconnect) buttons. */
	public static function connection_box( int $trainer_id ): void {
		$connected = Google_Tokens::has( $trainer_id );
		$since     = $connected ? Google_Tokens::connected_utc( $trainer_id ) : null;
		echo '<p><strong>' . ( $connected ? esc_html__( 'Connected', 'fwh-speaking-booking' ) : esc_html__( 'Not connected', 'fwh-speaking-booking' ) ) . '</strong>';
		if ( $since ) {
			echo ' &middot; ' . esc_html( Time::utc_to_toronto( $since, 'j M Y H:i' ) ) . ' Toronto';
		}
		echo '</p>';
		if ( ! Google_Client::configured() ) {
			echo '<p class="description">' . esc_html__( 'Add the Google OAuth client ID and secret in Settings to enable connecting.', 'fwh-speaking-booking' ) . '</p>';
			return;
		}
		foreach ( array( 'connect' => ( $connected ? __( 'Reconnect Google', 'fwh-speaking-booking' ) : __( 'Connect Google', 'fwh-speaking-booking' ) ), 'disconnect' => __( 'Disconnect', 'fwh-speaking-booking' ) ) as $act => $label ) {
			if ( 'disconnect' === $act && ! $connected ) {
				continue;
			}
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin-right:8px">';
			wp_nonce_field( 'fwh_sb_google_' . $act );
			echo '<input type="hidden" name="action" value="fwh_sb_google_' . esc_attr( $act ) . '"><input type="hidden" name="trainer_id" value="' . esc_attr( (string) $trainer_id ) . '">';
			submit_button( $label, 'connect' === $act ? 'primary' : 'secondary', '', false );
			echo '</form>';
		}
	}

	/** Admin notice for paid bookings whose Meet link failed, with a Retry button. */
	public function failed_notice(): void {
		global $wpdb;
		if ( ! current_user_can( 'fwh_sb_manage' ) && ! current_user_can( 'fwh_sb_view_bookings' ) ) {
			return;
		}
		$t    = Schema::table( 'bookings' );
		$rows = $wpdb->get_results( "SELECT id, ref, student_name, start_utc FROM {$t} WHERE status = 'paid' AND meet_status = 'failed' ORDER BY start_utc LIMIT 10" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $rows as $r ) {
			echo '<div class="notice notice-error"><p>' . esc_html(
				sprintf(
					/* translators: 1: booking ref, 2: student, 3: date */
					__( 'Speaking Booking %1$s (%2$s, %3$s Toronto): the Meet link could not be created and the student has not been emailed.', 'fwh-speaking-booking' ),
					$r->ref,
					$r->student_name,
					Time::utc_to_toronto( $r->start_utc )
				)
			) . '</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			wp_nonce_field( 'fwh_sb_retry_meet' );
			echo '<input type="hidden" name="action" value="fwh_sb_retry_meet"><input type="hidden" name="booking_id" value="' . esc_attr( (string) $r->id ) . '">';
			submit_button( __( 'Retry Meet link', 'fwh-speaking-booking' ), 'secondary', '', false );
			echo '</form><p></p></div>';
		}
	}
}
