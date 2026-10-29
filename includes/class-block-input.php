<?php
/**
 * Block (day off) input: Toronto dates/times to a UTC range.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Block_Input {

	/**
	 * @return array|string [ start_utc, end_utc ] or an error message.
	 */
	public static function parse( string $start_date, string $end_date, string $start_time, string $end_time ) {
		if ( '' === $end_date ) {
			$end_date = $start_date;
		}
		if ( ! Time::valid_date( $start_date ) || ! Time::valid_date( $end_date ) ) {
			return __( 'Please enter valid dates.', 'fwh-speaking-booking' );
		}
		if ( '' !== $start_time || '' !== $end_time ) {
			if ( ! Time::valid_time( $start_time ) || ! Time::valid_time( $end_time ) ) {
				return __( 'For a time range enter both a valid start and end time.', 'fwh-speaking-booking' );
			}
			$start_utc = Time::toronto_to_utc( $start_date, $start_time );
			$end_utc   = Time::toronto_to_utc( $end_date, $end_time );
		} else {
			// Whole days: midnight Toronto on the first day to midnight after the last day.
			$next      = ( new \DateTimeImmutable( $end_date, Time::utc() ) )->modify( '+1 day' )->format( 'Y-m-d' );
			$start_utc = Time::toronto_to_utc( $start_date, '00:00' );
			$end_utc   = Time::toronto_to_utc( $next, '00:00' );
		}
		if ( ! $start_utc || ! $end_utc || $end_utc <= $start_utc ) {
			return __( 'The end must be after the start.', 'fwh-speaking-booking' );
		}
		return array( $start_utc, $end_utc );
	}

	/** Parse from the current POST (nonce must already be verified). */
	public static function parse_post() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- callers verify the nonce first.
		$f = static fn( string $k ): string => isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : '';
		$r = self::parse( $f( 'start_date' ), $f( 'end_date' ), $f( 'start_time' ), $f( 'end_time' ) );
		// phpcs:enable
		return $r;
	}

	/** Date/time input fields shared by both block forms. */
	public static function render_fields(): void {
		echo '<tr><th><label for="fwh_sb_b_sd">' . esc_html__( 'From date', 'fwh-speaking-booking' ) . '</label></th><td><input id="fwh_sb_b_sd" type="date" name="start_date" required> <input type="time" name="start_time" aria-label="' . esc_attr__( 'From time', 'fwh-speaking-booking' ) . '"></td></tr>';
		echo '<tr><th><label for="fwh_sb_b_ed">' . esc_html__( 'To date', 'fwh-speaking-booking' ) . '</label></th><td><input id="fwh_sb_b_ed" type="date" name="end_date"> <input type="time" name="end_time" aria-label="' . esc_attr__( 'To time', 'fwh-speaking-booking' ) . '"></td></tr>';
		echo '<tr><th><label for="fwh_sb_b_n">' . esc_html__( 'Note', 'fwh-speaking-booking' ) . '</label></th><td><input id="fwh_sb_b_n" name="note" class="regular-text" maxlength="255"></td></tr>';
	}
}
