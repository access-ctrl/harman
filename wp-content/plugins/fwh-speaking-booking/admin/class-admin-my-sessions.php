<?php
/**
 * Trainer portal: My Sessions (own paid bookings only), list and week/month calendar.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Admin_My_Sessions {

	public function render(): void {
		if ( ! current_user_can( 'fwh_sb_trainer' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'fwh-speaking-booking' ) );
		}
		echo '<div class="wrap fwh-sb-admin"><h1>' . esc_html__( 'My Sessions', 'fwh-speaking-booking' ) . '</h1>';
		Notice::render();
		$trainer = Trainer_Access::current_trainer();
		if ( ! $trainer ) {
			echo '<p>' . esc_html__( 'Your account is not linked to a trainer profile yet. Please contact the coordinator.', 'fwh-speaking-booking' ) . '</p></div>';
			return;
		}
		$tid = (int) $trainer->id;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only view routing.
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'upcoming';
		$view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'list';
		$ref  = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';
		// phpcs:enable
		$tab  = in_array( $tab, array( 'upcoming', 'past' ), true ) ? $tab : 'upcoming';
		$view = in_array( $view, array( 'list', 'week', 'month' ), true ) ? $view : 'list';

		$base = array( 'page' => Trainer_Access::SESSIONS );
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( array( 'upcoming' => __( 'Upcoming', 'fwh-speaking-booking' ), 'past' => __( 'Past', 'fwh-speaking-booking' ) ) as $k => $label ) {
			printf( '<a class="nav-tab %s" href="%s">%s</a>', $k === $tab && 'list' === $view ? 'nav-tab-active' : '', esc_url( add_query_arg( array_merge( $base, array( 'tab' => $k ) ), admin_url( 'admin.php' ) ) ), esc_html( $label ) );
		}
		foreach ( array( 'week' => __( 'Week calendar', 'fwh-speaking-booking' ), 'month' => __( 'Month calendar', 'fwh-speaking-booking' ) ) as $k => $label ) {
			printf( '<a class="nav-tab %s" href="%s">%s</a>', $k === $view ? 'nav-tab-active' : '', esc_url( add_query_arg( array_merge( $base, array( 'view' => $k ) ), admin_url( 'admin.php' ) ) ), esc_html( $label ) );
		}
		echo '</h2>';

		if ( 'list' === $view ) {
			$this->render_list( $tid, $tab );
		} else {
			$this->render_calendar( $tid, $view, $ref );
		}
		echo '</div>';
	}

	private function join_button( object $b ): string {
		if ( 'ok' === $b->meet_status && $b->meet_link ) {
			return '<a class="button button-primary" target="_blank" rel="noopener noreferrer" href="' . esc_url( $b->meet_link ) . '">' . esc_html__( 'Join Meeting', 'fwh-speaking-booking' ) . '</a>';
		}
		return '<em>' . esc_html__( 'Link being prepared', 'fwh-speaking-booking' ) . '</em>';
	}

	private function render_list( int $tid, string $tab ): void {
		$res = Booking_Query::search(
			array(
				'statuses'   => array( 'paid' ),
				'trainer_id' => $tid,
				'when'       => $tab,
				'order'      => 'upcoming' === $tab ? 'asc' : 'desc',
				'limit'      => 200,
			)
		);
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'When (Toronto)', 'fwh-speaking-booking' ) . '</th><th>' . esc_html__( 'Booking', 'fwh-speaking-booking' ) . '</th><th>' . esc_html__( 'Meeting', 'fwh-speaking-booking' ) . '</th></tr></thead><tbody>';
		if ( ! $res['rows'] ) {
			echo '<tr><td colspan="3">' . esc_html__( 'No sessions.', 'fwh-speaking-booking' ) . '</td></tr>';
		}
		foreach ( $res['rows'] as $b ) {
			echo '<tr><td>' . esc_html( Time::utc_to_toronto( $b->start_utc, 'D j M Y, H:i' ) ) . '</td><td>' . esc_html( $b->ref ) . '</td><td>' . $this->join_button( $b ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in join_button().
		}
		echo '</tbody></table>';
	}

	private function render_calendar( int $tid, string $view, string $ref ): void {
		$today = ( new \DateTimeImmutable( 'now', Time::toronto() ) )->format( 'Y-m-d' );
		$ref   = Time::valid_date( $ref ) ? $ref : $today;
		$d     = new \DateTimeImmutable( $ref, Time::utc() );

		if ( 'week' === $view ) {
			$first = $d->modify( 'monday this week' );
			if ( '7' === $d->format( 'N' ) ) {
				$first = $d->modify( 'monday last week' );
			}
			$last = $first->modify( '+5 days' );
			$prev = $first->modify( '-7 days' )->format( 'Y-m-d' );
			$next = $first->modify( '+7 days' )->format( 'Y-m-d' );
			$title = $first->format( 'j M' ) . ' - ' . $last->format( 'j M Y' );
		} else {
			$first = $d->modify( 'first day of this month' );
			$last  = $d->modify( 'last day of this month' );
			$prev  = $first->modify( '-1 month' )->format( 'Y-m-d' );
			$next  = $first->modify( '+1 month' )->format( 'Y-m-d' );
			$title = $first->format( 'F Y' );
		}

		$res = Booking_Query::search(
			array(
				'statuses'   => array( 'paid' ),
				'trainer_id' => $tid,
				'from_utc'   => Time::toronto_to_utc( $first->format( 'Y-m-d' ), '00:00' ),
				'to_utc'     => Time::toronto_to_utc( $last->modify( '+1 day' )->format( 'Y-m-d' ), '00:00' ),
				'limit'      => 500,
			)
		);
		$by_day = array();
		foreach ( $res['rows'] as $b ) {
			$by_day[ Time::utc_to_toronto( $b->start_utc, 'Y-m-d' ) ][] = $b;
		}

		$nav = fn( string $r ): string => add_query_arg( array( 'page' => Trainer_Access::SESSIONS, 'view' => $view, 'ref' => $r ), admin_url( 'admin.php' ) );
		echo '<p class="fwh-sb-calnav"><a class="button" href="' . esc_url( $nav( $prev ) ) . '">&larr;</a> <strong>' . esc_html( $title ) . '</strong> <a class="button" href="' . esc_url( $nav( $next ) ) . '">&rarr;</a> <a class="button" href="' . esc_url( $nav( $today ) ) . '">' . esc_html__( 'Today', 'fwh-speaking-booking' ) . '</a></p>';

		echo '<div class="fwh-sb-cal fwh-sb-cal-' . esc_attr( $view ) . '">';
		foreach ( array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ) as $dow ) {
			if ( 'week' === $view && 'Sun' === $dow ) {
				continue;
			}
			echo '<div class="fwh-sb-cal-dow">' . esc_html( $dow ) . '</div>';
		}
		$cursor = $first;
		if ( 'month' === $view ) {
			for ( $i = 0, $pad = (int) $first->format( 'N' ) - 1; $i < $pad; $i++ ) {
				echo '<div class="fwh-sb-cal-cell is-empty"></div>';
			}
		}
		while ( $cursor <= $last ) {
			$key = $cursor->format( 'Y-m-d' );
			echo '<div class="fwh-sb-cal-cell' . ( $key === $today ? ' is-today' : '' ) . '"><span class="fwh-sb-cal-day">' . esc_html( $cursor->format( 'j' ) ) . '</span>';
			foreach ( $by_day[ $key ] ?? array() as $b ) {
				echo '<div class="fwh-sb-cal-event"><strong>' . esc_html( Time::utc_to_toronto( $b->start_utc, 'H:i' ) ) . '</strong> ' . esc_html( $b->ref );
				if ( 'ok' === $b->meet_status && $b->meet_link ) {
					echo ' <a target="_blank" rel="noopener noreferrer" href="' . esc_url( $b->meet_link ) . '">' . esc_html__( 'Join', 'fwh-speaking-booking' ) . '</a>';
				}
				echo '</div>';
			}
			echo '</div>';
			$cursor = $cursor->modify( '+1 day' );
		}
		echo '</div>';
	}
}
