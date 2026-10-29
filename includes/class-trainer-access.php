<?php
/**
 * Locks the trainer role to its two screens and resolves the trainer from the logged-in user.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Trainer_Access {

	public const SESSIONS = 'fwh-sb-my-sessions';
	public const SCHEDULE = 'fwh-sb-my-schedule';

	public function init(): void {
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 99, 3 );
		add_action( 'admin_init', array( $this, 'restrict_admin' ) );
		add_action( 'admin_menu', array( $this, 'trim_menu' ), 9999 );
		add_action( 'admin_bar_menu', array( $this, 'trim_admin_bar' ), 9999 );
		add_action( 'admin_head', array( $this, 'hide_chrome' ) );
	}

	/** Trainer role only (not an administrator or coordinator who also happens to be linked). */
	public static function is_trainer_only( ?\WP_User $user = null ): bool {
		$user = $user ?? wp_get_current_user();
		return $user && $user->exists() && $user->has_cap( 'fwh_sb_trainer' ) && ! $user->has_cap( 'fwh_sb_manage' ) && ! $user->has_cap( 'fwh_sb_view_bookings' ) && ! $user->has_cap( 'manage_options' );
	}

	/** Resolved from the current user on every call; never from request input. */
	public static function current_trainer(): ?object {
		return is_user_logged_in() ? Repo::trainer_by_user( get_current_user_id() ) : null;
	}

	public static function sessions_url(): string {
		return admin_url( 'admin.php?page=' . self::SESSIONS );
	}

	public function login_redirect( $redirect_to, $requested, $user ) {
		if ( $user instanceof \WP_User && self::is_trainer_only( $user ) ) {
			return self::sessions_url();
		}
		return $redirect_to;
	}

	public function restrict_admin(): void {
		global $pagenow;
		if ( ! self::is_trainer_only() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}
		if ( 'admin-post.php' === $pagenow ) {
			return; // Our own form handlers; each one checks capability and nonce.
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- routing only.
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		if ( 'admin.php' === $pagenow && in_array( $page, array( self::SESSIONS, self::SCHEDULE ), true ) ) {
			return;
		}
		wp_safe_redirect( self::sessions_url() );
		exit;
	}

	/** Remove every admin menu except the trainer screens. */
	public function trim_menu(): void {
		global $menu, $submenu;
		if ( ! self::is_trainer_only() ) {
			return;
		}
		foreach ( (array) $menu as $key => $item ) {
			if ( ( $item[2] ?? '' ) !== self::SESSIONS ) {
				unset( $menu[ $key ] );
			}
		}
		foreach ( array_keys( (array) $submenu ) as $parent ) {
			if ( self::SESSIONS !== $parent ) {
				unset( $submenu[ $parent ] );
			}
		}
	}

	/** Keep only the account/logout part of the admin bar. */
	public function trim_admin_bar( \WP_Admin_Bar $bar ): void {
		if ( ! self::is_trainer_only() ) {
			return;
		}
		$keep = array( 'top-secondary', 'my-account', 'user-actions', 'user-info', 'logout' );
		foreach ( $bar->get_nodes() ?: array() as $node ) {
			if ( ! in_array( $node->id, $keep, true ) ) {
				$bar->remove_node( $node->id );
			}
		}
		$bar->remove_node( 'edit-profile' );
	}

	public function hide_chrome(): void {
		if ( self::is_trainer_only() ) {
			echo '<style>#wp-admin-bar-user-info a.ab-item{pointer-events:none}.update-nag,#wpfooter{display:none}</style>';
		}
	}
}
