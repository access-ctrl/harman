<?php
/**
 * Weekly-hours form: validation and rendering, shared by admin and trainer screens.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Hours_Input {

	public const DAYS = array(
		1 => 'Monday',
		2 => 'Tuesday',
		3 => 'Wednesday',
		4 => 'Thursday',
		5 => 'Friday',
		6 => 'Saturday',
	);

	/**
	 * Validate submitted ranges. Returns [ [weekday, 'HH:MM', 'HH:MM'], ... ] or an error string.
	 *
	 * @param array $days   Raw (unslashed) weekday values.
	 * @param array $starts Raw start times.
	 * @param array $ends   Raw end times.
	 * @return array|string
	 */
	public static function parse( array $days, array $starts, array $ends ) {
		$rows = array();
		foreach ( $days as $i => $day ) {
			if ( ! is_scalar( $day ) || ( isset( $starts[ $i ] ) && ! is_scalar( $starts[ $i ] ) ) || ( isset( $ends[ $i ] ) && ! is_scalar( $ends[ $i ] ) ) ) {
				return __( 'Invalid hours submitted.', 'fwh-speaking-booking' );
			}
			$day   = (int) $day;
			$start = isset( $starts[ $i ] ) ? sanitize_text_field( (string) $starts[ $i ] ) : '';
			$end   = isset( $ends[ $i ] ) ? sanitize_text_field( (string) $ends[ $i ] ) : '';
			if ( '' === $start && '' === $end ) {
				continue;
			}
			if ( ! isset( self::DAYS[ $day ] ) || ! Time::valid_time( $start ) || ! Time::valid_time( $end ) ) {
				return __( 'Each hour range needs a weekday (Mon-Sat) and valid start and end times.', 'fwh-speaking-booking' );
			}
			if ( $start >= $end ) {
				return sprintf(
					/* translators: %s: weekday name */
					__( '%s: the end time must be after the start time.', 'fwh-speaking-booking' ),
					self::DAYS[ $day ]
				);
			}
			$rows[] = array( $day, $start, $end );
		}
		usort( $rows, static fn( $a, $b ) => array( $a[0], $a[1] ) <=> array( $b[0], $b[1] ) );
		for ( $i = 1, $n = count( $rows ); $i < $n; $i++ ) {
			if ( $rows[ $i ][0] === $rows[ $i - 1 ][0] && $rows[ $i ][1] < $rows[ $i - 1 ][2] ) {
				return sprintf(
					/* translators: %s: weekday name */
					__( '%s: hour ranges overlap.', 'fwh-speaking-booking' ),
					self::DAYS[ $rows[ $i ][0] ]
				);
			}
		}
		return $rows;
	}

	/** Read and validate the ranges from the current POST (nonce must already be verified). */
	public static function parse_post() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- callers verify the nonce first.
		$days   = isset( $_POST['hours_day'] ) ? (array) wp_unslash( $_POST['hours_day'] ) : array();
		$starts = isset( $_POST['hours_start'] ) ? (array) wp_unslash( $_POST['hours_start'] ) : array();
		$ends   = isset( $_POST['hours_end'] ) ? (array) wp_unslash( $_POST['hours_end'] ) : array();
		// phpcs:enable
		return self::parse( $days, $starts, $ends );
	}

	/** Render the editable ranges table (existing rows plus spare blank rows). */
	public static function render_table( array $hours ): void {
		$rows = array();
		foreach ( $hours as $h ) {
			$rows[] = array( (int) $h->weekday, substr( $h->start_time, 0, 5 ), substr( $h->end_time, 0, 5 ) );
		}
		$used = array_unique( array_column( $rows, 0 ) );
		foreach ( array_keys( self::DAYS ) as $day ) { // A blank row for each day that has no hours yet, so it can be filled in.
			if ( ! in_array( $day, $used, true ) ) {
				$rows[] = array( $day, '', '' );
			}
		}

		echo '<p class="fwh-sb-samehours"><label><input type="checkbox" id="fwh-sb-same"> ' . esc_html__( 'Use the first row\'s start and end time for every day', 'fwh-speaking-booking' ) . '</label></p>';
		echo '<table class="widefat" style="max-width:620px" id="fwh-sb-hours"><thead><tr><th>Day</th><th>Start</th><th>End</th><th></th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			echo '<tr><td><select name="hours_day[]">';
			foreach ( self::DAYS as $num => $label ) {
				printf( '<option value="%d" %s>%s</option>', (int) $num, selected( $r[0], $num, false ), esc_html( $label ) );
			}
			printf( '</select></td><td><input type="time" name="hours_start[]" value="%s"></td><td><input type="time" name="hours_end[]" value="%s"></td><td><button type="button" class="button-link button-link-delete fwh-sb-rm" aria-label="%s">%s</button></td></tr>', esc_attr( $r[1] ), esc_attr( $r[2] ), esc_attr__( 'Remove this time range', 'fwh-speaking-booking' ), esc_html__( 'Remove', 'fwh-speaking-booking' ) );
		}
		echo '</tbody></table>';
		echo '<p><button type="button" class="button-link" id="fwh-sb-addrow">+ ' . esc_html__( 'Add another range', 'fwh-speaking-booking' ) . '</button></p>';
		?>
<script>
(function () {
	var tbl = document.getElementById('fwh-sb-hours');
	var same = document.getElementById('fwh-sb-same');
	var add = document.getElementById('fwh-sb-addrow');
	if (!tbl || !same || !add) { return; }
	var body = tbl.tBodies[0];
	function rows() { return Array.prototype.slice.call(body.rows); }
	function times(r) { return r.querySelectorAll('input[type="time"]'); }
	// Copy the first row's times onto the first row of every other day. Extra ranges on a day are left as they are.
	function apply() {
		if (!same.checked) { return; }
		var all = rows(), f = times(all[0]), seen = {};
		seen[all[0].querySelector('select').value] = true;
		all.slice(1).forEach(function (r) {
			var day = r.querySelector('select').value;
			if (seen[day]) { return; }
			seen[day] = true;
			var t = times(r);
			t[0].value = f[0].value;
			t[1].value = f[1].value;
		});
	}
	same.addEventListener('change', apply);
	body.addEventListener('input', function (e) { if (same.checked && rows()[0].contains(e.target)) { apply(); } });
	// Remove: an extra range on a day is deleted; the only row for a day is cleared so that day is off (and can be filled again).
	body.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('.fwh-sb-rm') : null;
		if (!btn) { return; }
		var tr = btn.closest('tr'), day = tr.querySelector('select').value;
		var sameDay = rows().filter(function (r) { return r.querySelector('select').value === day; });
		if (sameDay.length > 1) {
			tr.parentNode.removeChild(tr);
		} else {
			Array.prototype.forEach.call(times(tr), function (i) { i.value = ''; });
		}
		same.checked = false; // The days are no longer all the same, so stop syncing them.
	});
	add.addEventListener('click', function () {
		var clone = rows()[rows().length - 1].cloneNode(true);
		Array.prototype.forEach.call(clone.querySelectorAll('input'), function (i) { i.value = ''; });
		clone.querySelector('select').value = '1';
		body.appendChild(clone);
	});
})();
</script>
		<?php
	}
}
