<?php
/**
 * Blocks (days off / unavailable ranges) admin page.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Admin_Blocks {

	private const PAGE = 'fwh-sb-blocks';

	public function init(): void {
		add_action( 'admin_post_fwh_sb_add_block', array( $this, 'handle_add' ) );
		add_action( 'admin_post_fwh_sb_delete_block', array( $this, 'handle_delete' ) );
	}

	private function guard( string $action ): void {
		if ( ! current_user_can( 'fwh_sb_manage' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'fwh-speaking-booking' ), 403 );
		}
		check_admin_referer( $action );
	}

	public function handle_add(): void {
		$this->guard( 'fwh_sb_add_block' );
		$tid        = isset( $_POST['trainer_id'] ) ? absint( $_POST['trainer_id'] ) : 0;
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$start_time = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '';
		$end_time   = isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : '';
		$note       = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : '';

		if ( '' === $end_date ) {
			$end_date = $start_date;
		}
		if ( ! Time::valid_date( $start_date ) || ! Time::valid_date( $end_date ) ) {
			$this->fail( __( 'Please enter valid dates.', 'fwh-speaking-booking' ) );
		}
		if ( $tid && ! Repo::trainer( $tid ) ) {
			$this->fail( __( 'Trainer not found.', 'fwh-speaking-booking' ) );
		}
		$timed = '' !== $start_time || '' !== $end_time;
		if ( $timed ) {
			if ( ! Time::valid_time( $start_time ) || ! Time::valid_time( $end_time ) ) {
				$this->fail( __( 'For a time range enter both a valid start and end time.', 'fwh-speaking-booking' ) );
			}
			$start_utc = Time::toronto_to_utc( $start_date, $start_time );
			$end_utc   = Time::toronto_to_utc( $end_date, $end_time );
		} else {
			// Whole days: midnight Toronto on the first day to midnight after the last day.
			$next      = ( new \DateTimeImmutable( $end_date, Time::utc() ) )->modify( '+1 day' )->format( 'Y-m-d' );
			$start_utc = Time::toronto_to_utc( $start_date, '00:00' );
			$end_utc   = Time::toronto_to_utc( $next, '00:00' );
		}
		if ( ! $start_utc || ! $end_utc || $end_utc <= $start_utc ) {
			$this->fail( __( 'The end must be after the start.', 'fwh-speaking-booking' ) );
		}

		Repo::add_block( $tid ? $tid : null, $start_utc, $end_utc, $note, get_current_user_id() );
		Notice::add( 'success', __( 'Block added.', 'fwh-speaking-booking' ) );
		Notice::redirect( self::PAGE );
	}

	private function fail( string $msg ): void {
		Notice::add( 'error', $msg );
		Notice::redirect( self::PAGE );
	}

	public function handle_delete(): void {
		$this->guard( 'fwh_sb_delete_block' );
		$id = isset( $_POST['block_id'] ) ? absint( $_POST['block_id'] ) : 0;
		if ( $id ) {
			Repo::delete_block( $id );
			Notice::add( 'success', __( 'Block removed.', 'fwh-speaking-booking' ) );
		}
		Notice::redirect( self::PAGE );
	}

	public function render(): void {
		if ( ! current_user_can( 'fwh_sb_manage' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'fwh-speaking-booking' ) );
		}
		echo '<div class="wrap"><h1>' . esc_html__( 'Blocks (days off)', 'fwh-speaking-booking' ) . '</h1>';
		Notice::render();
		echo '<p class="description">' . esc_html__( 'All dates and times are Toronto time. Leave the times blank to block whole days.', 'fwh-speaking-booking' ) . '</p>';

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><table class="form-table" role="presentation">';
		wp_nonce_field( 'fwh_sb_add_block' );
		echo '<input type="hidden" name="action" value="fwh_sb_add_block">';
		echo '<tr><th><label for="fwh_sb_b_tr">' . esc_html__( 'Applies to', 'fwh-speaking-booking' ) . '</label></th><td><select id="fwh_sb_b_tr" name="trainer_id"><option value="0">' . esc_html__( 'All trainers', 'fwh-speaking-booking' ) . '</option>';
		foreach ( Repo::trainers() as $t ) {
			printf( '<option value="%d">%s</option>', (int) $t->id, esc_html( $t->name ) );
		}
		echo '</select></td></tr>';
		echo '<tr><th><label for="fwh_sb_b_sd">' . esc_html__( 'From date', 'fwh-speaking-booking' ) . '</label></th><td><input id="fwh_sb_b_sd" type="date" name="start_date" required> <input type="time" name="start_time" aria-label="' . esc_attr__( 'From time', 'fwh-speaking-booking' ) . '"></td></tr>';
		echo '<tr><th><label for="fwh_sb_b_ed">' . esc_html__( 'To date', 'fwh-speaking-booking' ) . '</label></th><td><input id="fwh_sb_b_ed" type="date" name="end_date"> <input type="time" name="end_time" aria-label="' . esc_attr__( 'To time', 'fwh-speaking-booking' ) . '"></td></tr>';
		echo '<tr><th><label for="fwh_sb_b_n">' . esc_html__( 'Note', 'fwh-speaking-booking' ) . '</label></th><td><input id="fwh_sb_b_n" name="note" class="regular-text" maxlength="255"></td></tr>';
		echo '</table>';
		submit_button( __( 'Add block', 'fwh-speaking-booking' ) );
		echo '</form>';

		echo '<h2>' . esc_html__( 'Existing blocks', 'fwh-speaking-booking' ) . '</h2><table class="widefat striped"><thead><tr><th>Applies to</th><th>From (Toronto)</th><th>To (Toronto)</th><th>Note</th><th></th></tr></thead><tbody>';
		$blocks = Repo::blocks();
		if ( ! $blocks ) {
			echo '<tr><td colspan="5">' . esc_html__( 'No blocks.', 'fwh-speaking-booking' ) . '</td></tr>';
		}
		foreach ( $blocks as $b ) {
			echo '<tr><td>' . ( null === $b->trainer_id ? esc_html__( 'All trainers', 'fwh-speaking-booking' ) : esc_html( (string) $b->trainer_name ) ) . '</td>';
			echo '<td>' . esc_html( Time::utc_to_toronto( $b->start_utc ) ) . '</td><td>' . esc_html( Time::utc_to_toronto( $b->end_utc ) ) . '</td><td>' . esc_html( $b->note ) . '</td><td>';
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'' . esc_js( __( 'Remove this block?', 'fwh-speaking-booking' ) ) . '\')">';
			wp_nonce_field( 'fwh_sb_delete_block' );
			echo '<input type="hidden" name="action" value="fwh_sb_delete_block"><input type="hidden" name="block_id" value="' . esc_attr( (string) $b->id ) . '"><button class="button-link button-link-delete">' . esc_html__( 'Remove', 'fwh-speaking-booking' ) . '</button></form></td></tr>';
		}
		echo '</tbody></table></div>';
	}
}
