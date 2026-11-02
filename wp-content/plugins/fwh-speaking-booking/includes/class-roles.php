<?php
/**
 * Roles and capabilities.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Roles {

	public static function install(): void {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'fwh_sb_manage' );
			$admin->add_cap( 'fwh_sb_view_bookings' );
		}
		if ( ! get_role( 'fwh_sb_coordinator' ) ) {
			add_role(
				'fwh_sb_coordinator',
				__( 'Speaking Coordinator', 'fwh-speaking-booking' ),
				array(
					'read'                 => true,
					'fwh_sb_view_bookings' => true,
				)
			);
		}
		if ( ! get_role( 'fwh_sb_trainer' ) ) {
			add_role(
				'fwh_sb_trainer',
				__( 'Speaking Trainer', 'fwh-speaking-booking' ),
				array(
					'read'          => true,
					'fwh_sb_trainer' => true,
				)
			);
		}
	}
}
