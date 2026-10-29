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

	public function init(): void {
		$this->settings = new Admin_Settings();
		$this->trainers = new Admin_Trainers();
		$this->blocks   = new Admin_Blocks();
		$this->settings->init();
		$this->trainers->init();
		$this->blocks->init();
		add_action( 'admin_menu', array( $this, 'register' ) );
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
	}
}
