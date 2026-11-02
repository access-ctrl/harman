<?php
/**
 * Plugin Name: FWH Speaking Booking
 * Description: 20-minute 1-to-1 French speaking session booking for frenchwithharman.com.
 * Version: 1.0.0
 * Requires PHP: 8.0
 * Requires at least: 6.0
 * Text Domain: fwh-speaking-booking
 *
 * @package FWH\SpeakingBooking
 */

defined( 'ABSPATH' ) || exit;

define( 'FWH_SB_VERSION', '1.0.0' );
define( 'FWH_SB_FILE', __FILE__ );
define( 'FWH_SB_DIR', plugin_dir_path( __FILE__ ) );
define( 'FWH_SB_URL', plugin_dir_url( __FILE__ ) );

require_once FWH_SB_DIR . 'includes/class-autoloader.php';
\FWH\SpeakingBooking\Autoloader::register();

register_activation_hook( __FILE__, array( \FWH\SpeakingBooking\Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \FWH\SpeakingBooking\Deactivator::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		\FWH\SpeakingBooking\Plugin::instance()->init();
	}
);
