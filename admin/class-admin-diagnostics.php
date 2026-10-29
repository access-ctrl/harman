<?php
/**
 * Admin-only diagnostics: generated slots for a trainer on key dates in several time zones.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Admin_Diagnostics {

	/** Toronto dates around the 1 Nov 2026 and 14 Mar 2027 clock changes. */
	private const DATES = array( '2026-10-30', '2026-11-02', '2027-03-16' );

	private const ZONES = array(
		'America/Toronto' => 'Toronto',
		'Asia/Kolkata'    => 'India',
		'Asia/Dubai'      => 'Dubai',
	);

	public function render(): void {
		if ( ! current_user_can( 'fwh_sb_manage' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'fwh-speaking-booking' ) );
		}
		$trainers = Repo::trainers();
		$selected = isset( $_GET['trainer'] ) ? absint( $_GET['trainer'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view.

		echo '<div class="wrap"><h1>' . esc_html__( 'Availability diagnostics', 'fwh-speaking-booking' ) . '</h1>';
		echo '<p class="description">' . esc_html__( 'Shows generated slots ignoring booking window and minimum notice. Blocks and existing bookings still apply.', 'fwh-speaking-booking' ) . '</p>';
		echo '<form method="get"><input type="hidden" name="page" value="fwh-sb-diagnostics"><label for="fwh_sb_diag_t">' . esc_html__( 'Trainer', 'fwh-speaking-booking' ) . '</label> <select id="fwh_sb_diag_t" name="trainer">';
		foreach ( $trainers as $t ) {
			printf( '<option value="%d" %s>%s</option>', (int) $t->id, selected( $selected, (int) $t->id, false ), esc_html( $t->name ) );
		}
		echo '</select> ';
		submit_button( __( 'Show slots', 'fwh-speaking-booking' ), 'secondary', '', false );
		echo '</form>';

		if ( $selected && Repo::trainer( $selected ) ) {
			foreach ( self::DATES as $date ) {
				$slots = Availability::compute( $date, $date, array( 'trainer_id' => $selected, 'ignore_limits' => true ) );
				$label = ( new \DateTimeImmutable( $date, Time::utc() ) )->format( 'D j M Y' );
				/* translators: %s: date */
				echo '<h2>' . esc_html( sprintf( __( '%s (Toronto date)', 'fwh-speaking-booking' ), $label ) ) . '</h2>';
				if ( empty( $slots[ $date ] ) ) {
					echo '<p>' . esc_html__( 'No slots (day off, Sunday, blocked or fully booked).', 'fwh-speaking-booking' ) . '</p>';
					continue;
				}
				echo '<table class="widefat striped" style="max-width:640px"><thead><tr><th>UTC</th>';
				foreach ( self::ZONES as $zone => $name ) {
					echo '<th>' . esc_html( $name ) . '</th>';
				}
				echo '</tr></thead><tbody>';
				foreach ( array_keys( $slots[ $date ] ) as $utc ) {
					$dt = new \DateTimeImmutable( $utc, Time::utc() );
					echo '<tr><td>' . esc_html( $dt->format( 'H:i' ) ) . '</td>';
					foreach ( array_keys( self::ZONES ) as $zone ) {
						echo '<td>' . esc_html( $dt->setTimezone( new \DateTimeZone( $zone ) )->format( 'D H:i T' ) ) . '</td>';
					}
					echo '</tr>';
				}
				echo '</tbody></table>';
			}
		}
		echo '</div>';
	}
}
