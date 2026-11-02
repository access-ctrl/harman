<?php
/**
 * Activation handler. Creates tables, roles and default settings.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Activator {

	public static function activate(): void {
		Schema::install();
		Roles::install();
		Settings::install_defaults();
	}
}
