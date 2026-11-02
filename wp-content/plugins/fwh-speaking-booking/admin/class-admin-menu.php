<?php
/**
 * "Speaking Booking" admin menu and page wiring.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Admin_Menu {

	private Admin_Settings $settings;
	private Admin_Trainers $trainers;
	private Admin_Blocks $blocks;
	private Admin_Diagnostics $diagnostics;
	private Admin_Google $google;
	private Admin_Bookings $bookings;
	private Admin_My_Sessions $my_sessions;
	private Admin_My_Schedule $my_schedule;

	public function init(): void {
		$this->settings = new Admin_Settings();
		$this->trainers = new Admin_Trainers();
		$this->blocks   = new Admin_Blocks();
		$this->diagnostics = new Admin_Diagnostics();
		$this->bookings    = new Admin_Bookings();
		$this->my_sessions = new Admin_My_Sessions();
		$this->my_schedule = new Admin_My_Schedule();
		$this->bookings->init();
		$this->my_schedule->init();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		$this->google      = new Admin_Google();
		$this->google->init();
		$this->settings->init();
		$this->trainers->init();
		$this->blocks->init();
		add_action( 'admin_menu', array( $this, 'register' ) );
	}

	public function enqueue( string $hook ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- asset loading only.
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		if ( 0 === strpos( $page, 'fwh-sb' ) ) {
			wp_enqueue_style( 'fwh-sb-admin', FWH_SB_URL . 'assets/admin.css', array(), (string) filemtime( FWH_SB_DIR . 'assets/admin.css' ) );
		}
	}

	public function register(): void {
		add_menu_page(
			__( 'Speaking Booking', 'fwh-speaking-booking' ),
			__( 'Speaking Booking', 'fwh-speaking-booking' ),
			'fwh_sb_manage',
			'fwh-sb',
			array( $this->settings, 'render' ),
			'dashicons-calendar-alt',
			58
		);
		add_submenu_page( 'fwh-sb', __( 'Settings', 'fwh-speaking-booking' ), __( 'Settings', 'fwh-speaking-booking' ), 'fwh_sb_manage', 'fwh-sb', array( $this->settings, 'render' ) );
		add_submenu_page( 'fwh-sb', __( 'Trainers', 'fwh-speaking-booking' ), __( 'Trainers', 'fwh-speaking-booking' ), 'fwh_sb_manage', 'fwh-sb-trainers', array( $this->trainers, 'render' ) );
		add_submenu_page( 'fwh-sb', __( 'Blocks', 'fwh-speaking-booking' ), __( 'Blocks', 'fwh-speaking-booking' ), 'fwh_sb_manage', 'fwh-sb-blocks', array( $this->blocks, 'render' ) );
		add_menu_page( __( 'Bookings', 'fwh-speaking-booking' ), __( 'Bookings', 'fwh-speaking-booking' ), 'fwh_sb_view_bookings', Admin_Bookings::PAGE, array( $this->bookings, 'render' ), 'dashicons-list-view', 57 );
		add_menu_page( __( 'My Sessions', 'fwh-speaking-booking' ), __( 'My Sessions', 'fwh-speaking-booking' ), 'fwh_sb_trainer', Trainer_Access::SESSIONS, array( $this->my_sessions, 'render' ), 'dashicons-calendar-alt', 56 );
		add_submenu_page( Trainer_Access::SESSIONS, __( 'My Sessions', 'fwh-speaking-booking' ), __( 'My Sessions', 'fwh-speaking-booking' ), 'fwh_sb_trainer', Trainer_Access::SESSIONS, array( $this->my_sessions, 'render' ) );
		add_submenu_page( Trainer_Access::SESSIONS, __( 'My Schedule', 'fwh-speaking-booking' ), __( 'My Schedule', 'fwh-speaking-booking' ), 'fwh_sb_trainer', Trainer_Access::SCHEDULE, array( $this->my_schedule, 'render' ) );
		add_submenu_page( 'fwh-sb', __( 'Diagnostics', 'fwh-speaking-booking' ), __( 'Diagnostics', 'fwh-speaking-booking' ), 'fwh_sb_manage', 'fwh-sb-diagnostics', array( $this->diagnostics, 'render' ) );
	}
}
