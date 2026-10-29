<?php
/**
 * Per-user admin notices carried across a redirect.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Notice {

	public static function add( string $type, string $message ): void {
		set_transient( 'fwh_sb_notice_' . get_current_user_id(), array( $type, $message ), 120 );
	}

	public static function render(): void {
		$key  = 'fwh_sb_notice_' . get_current_user_id();
		$data = get_transient( $key );
		if ( ! is_array( $data ) ) {
			return;
		}
		delete_transient( $key );
		$class = 'error' === $data[0] ? 'notice-error' : 'notice-success';
		printf( '<div class="notice %s is-dismissible"><p>%s</p></div>', esc_attr( $class ), esc_html( $data[1] ) );
	}

	public static function redirect( string $page, array $args = array() ): void {
		wp_safe_redirect( add_query_arg( array_merge( array( 'page' => $page ), $args ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
