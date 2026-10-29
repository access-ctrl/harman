<?php
/**
 * Plugin bootstrap.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Plugin {

	private static ?Plugin $instance = null;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		Schema::maybe_upgrade();
		( new Rest_Slots() )->init();
		( new Rest_Booking() )->init();
		( new Cron() )->init();
		( new Meet_Service() )->init();
		( new Trainer_Access() )->init();
		( new Shortcode() )->init();
		if ( is_admin() ) {
			( new Admin_Menu() )->init();
		}
	}
}
