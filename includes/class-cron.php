<?php
/**
 * Five-minute cron that settles lapsed holds. Real server cron should hit wp-cron.php.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Cron {

	public const HOOK     = 'fwh_sb_sweep_holds';
	public const SCHEDULE = 'fwh_sb_five_minutes';

	public function init(): void {
		add_filter( 'cron_schedules', array( $this, 'schedules' ) );
		add_action( self::HOOK, array( Bookings::class, 'sweep_holds' ) );
		add_action( 'init', array( self::class, 'ensure_scheduled' ) );
	}

	public function schedules( array $schedules ): array {
		$schedules[ self::SCHEDULE ] = array(
			'interval' => 300,
			'display'  => __( 'Every 5 minutes (FWH Speaking Booking)', 'fwh-speaking-booking' ),
		);
		return $schedules;
	}

	public static function ensure_scheduled(): void {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + 60, self::SCHEDULE, self::HOOK );
		}
	}

	public static function clear(): void {
		wp_clear_scheduled_hook( self::HOOK );
	}
}
