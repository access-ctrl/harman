<?php
/**
 * Deactivation handler. Data is never dropped on deactivate.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Deactivator {

	public static function deactivate(): void {
		// Nothing to clean up yet.
	}
}
