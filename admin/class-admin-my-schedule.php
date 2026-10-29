<?php
/**
 * Trainer portal: My Schedule (own hours, own blocks, Google connection).
 * The trainer is always resolved from the logged-in user, never from request data.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Admin_My_Schedule {

	public function init(): void {
		add_action( 'admin_post_fwh_sb_my_save_hours', array( $this, 'handle_hours' ) );
		add_action( 'admin_post_fwh_sb_my_add_block', array( $this, 'handle_add_block' ) );
		add_action( 'admin_post_fwh_sb_my_delete_block', array( $this, 'handle_delete_block' ) );
	}

	/** Capability, nonce and trainer resolution in one place. */
	private function trainer_or_die( string $nonce_action ): object {
		if ( ! current_user_can( 'fwh_sb_trainer' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'fwh-speaking-booking' ), 403 );
		}
		check_admin_referer( $nonce_action );
		$trainer = Trainer_Access::current_trainer();
		if ( ! $trainer ) {
			wp_die( esc_html__( 'Your account is not linked to a trainer profile.', 'fwh-speaking-booking' ), 403 );
		}
		return $trainer;
	}

	public function handle_hours(): void {
		$trainer = $this->trainer_or_die( 'fwh_sb_my_save_hours' );
		$rows    = Hours_Input::parse_post();
		if ( is_string( $rows ) ) {
			Notice::add( 'error', $rows );
			Notice::redirect( Trainer_Access::SCHEDULE );
		}
		$conflicts = Conflicts::hours( (int) $trainer->id, $rows );
		if ( $conflicts ) {
			Notice::add( 'error', Conflicts::message( $conflicts, __( 'these hours', 'fwh-speaking-booking' ) ) );
			Notice::redirect( Trainer_Access::SCHEDULE );
		}
		Repo::replace_hours( (int) $trainer->id, $rows );
		Notice::add( 'success', __( 'Your weekly hours were saved.', 'fwh-speaking-booking' ) );
		Notice::redirect( Trainer_Access::SCHEDULE );
	}

	public function handle_add_block(): void {
		$trainer = $this->trainer_or_die( 'fwh_sb_my_add_block' );
		$range   = Block_Input::parse_post();
		if ( is_string( $range ) ) {
			Notice::add( 'error', $range );
			Notice::redirect( Trainer_Access::SCHEDULE );
		}
		$conflicts = Conflicts::block( (int) $trainer->id, $range[0], $range[1] );
		if ( $conflicts ) {
			Notice::add( 'error', Conflicts::message( $conflicts, __( 'this day off', 'fwh-speaking-booking' ) ) );
			Notice::redirect( Trainer_Access::SCHEDULE );
		}
		$note = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : '';
		Repo::add_block( (int) $trainer->id, $range[0], $range[1], $note, get_current_user_id() );
		Notice::add( 'success', __( 'Day off added.', 'fwh-speaking-booking' ) );
		Notice::redirect( Trainer_Access::SCHEDULE );
	}

	public function handle_delete_block(): void {
		$trainer = $this->trainer_or_die( 'fwh_sb_my_delete_block' );
		$id      = isset( $_POST['block_id'] ) ? absint( $_POST['block_id'] ) : 0;
		if ( $id ) {
			global $wpdb;
			// Scoped to this trainer: a trainer can never delete someone else's or a global block.
			$wpdb->delete( Schema::table( 'blocks' ), array( 'id' => $id, 'trainer_id' => (int) $trainer->id ), array( '%d', '%d' ) );
			Notice::add( 'success', __( 'Day off removed.', 'fwh-speaking-booking' ) );
		}
		Notice::redirect( Trainer_Access::SCHEDULE );
	}

	public function render(): void {
		if ( ! current_user_can( 'fwh_sb_trainer' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'fwh-speaking-booking' ) );
		}
		echo '<div class="wrap fwh-sb-admin"><h1>' . esc_html__( 'My Schedule', 'fwh-speaking-booking' ) . '</h1>';
		Notice::render();
		$trainer = Trainer_Access::current_trainer();
		if ( ! $trainer ) {
			echo '<p>' . esc_html__( 'Your account is not linked to a trainer profile yet. Please contact the coordinator.', 'fwh-speaking-booking' ) . '</p></div>';
			return;
		}
		$tid = (int) $trainer->id;

		echo '<h2>' . esc_html__( 'Weekly hours (Toronto time, Mon-Sat)', 'fwh-speaking-booking' ) . '</h2>';
		echo '<p class="description">' . esc_html__( 'Add several ranges per day if needed. Leave start and end blank to remove a row. Sunday is always closed.', 'fwh-speaking-booking' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'fwh_sb_my_save_hours' );
		echo '<input type="hidden" name="action" value="fwh_sb_my_save_hours">';
		Hours_Input::render_table( Repo::hours( $tid ) );
		submit_button( __( 'Save hours', 'fwh-speaking-booking' ) );
		echo '</form>';

		echo '<h2>' . esc_html__( 'Days off', 'fwh-speaking-booking' ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><table class="form-table" role="presentation">';
		wp_nonce_field( 'fwh_sb_my_add_block' );
		echo '<input type="hidden" name="action" value="fwh_sb_my_add_block">';
		Block_Input::render_fields();
		echo '</table>';
		echo '<p class="description">' . esc_html__( 'Toronto time. Leave the times blank to block whole days.', 'fwh-speaking-booking' ) . '</p>';
		submit_button( __( 'Add day off', 'fwh-speaking-booking' ) );
		echo '</form>';

		global $wpdb;
		$bt     = Schema::table( 'blocks' );
		$blocks = $wpdb->get_results( $wpdb->prepare( "SELECT id, start_utc, end_utc, note FROM {$bt} WHERE trainer_id = %d AND end_utc > %s ORDER BY start_utc", $tid, Time::now_utc() ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		echo '<table class="widefat striped" style="max-width:720px"><thead><tr><th>From (Toronto)</th><th>To (Toronto)</th><th>Note</th><th></th></tr></thead><tbody>';
		if ( ! $blocks ) {
			echo '<tr><td colspan="4">' . esc_html__( 'No upcoming days off.', 'fwh-speaking-booking' ) . '</td></tr>';
		}
		foreach ( $blocks as $b ) {
			echo '<tr><td>' . esc_html( Time::utc_to_toronto( $b->start_utc ) ) . '</td><td>' . esc_html( Time::utc_to_toronto( $b->end_utc ) ) . '</td><td>' . esc_html( $b->note ) . '</td><td>';
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'' . esc_js( __( 'Remove this day off?', 'fwh-speaking-booking' ) ) . '\')">';
			wp_nonce_field( 'fwh_sb_my_delete_block' );
			echo '<input type="hidden" name="action" value="fwh_sb_my_delete_block"><input type="hidden" name="block_id" value="' . esc_attr( (string) $b->id ) . '"><button class="button-link button-link-delete">' . esc_html__( 'Remove', 'fwh-speaking-booking' ) . '</button></form></td></tr>';
		}
		echo '</tbody></table>';

		echo '<h2>' . esc_html__( 'Google Calendar and Meet', 'fwh-speaking-booking' ) . '</h2>';
		Admin_Google::connection_box( $tid );
		echo '</div>';
	}
}
