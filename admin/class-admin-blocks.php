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
		$tid   = isset( $_POST['trainer_id'] ) ? absint( $_POST['trainer_id'] ) : 0;
		$note  = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : '';
		$range = Block_Input::parse_post();
		if ( is_string( $range ) ) {
			$this->fail( $range );
		}
		if ( $tid && ! Repo::trainer( $tid ) ) {
			$this->fail( __( 'Trainer not found.', 'fwh-speaking-booking' ) );
		}
		list( $start_utc, $end_utc ) = $range;

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
		Block_Input::render_fields();
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
