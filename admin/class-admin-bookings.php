<?php
/**
 * Coordinator / administrator Bookings page: tabs, filters, cancel.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Admin_Bookings {

	public const PAGE     = 'fwh-sb-bookings';
	private const PER_PAGE = 50;

	public function init(): void {
		add_action( 'admin_post_fwh_sb_cancel_booking', array( $this, 'handle_cancel' ) );
	}

	private function allowed(): bool {
		return current_user_can( 'fwh_sb_view_bookings' ) || current_user_can( 'fwh_sb_manage' );
	}

	public function handle_cancel(): void {
		if ( ! $this->allowed() ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'fwh-speaking-booking' ), 403 );
		}
		check_admin_referer( 'fwh_sb_cancel_booking' );
		$id  = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
		$res = $id ? Bookings::cancel( $id ) : 'not_cancellable';
		if ( 'cancelled' === $res ) {
			Notice::add( 'success', __( 'Booking cancelled and the time slot is free again.', 'fwh-speaking-booking' ) );
		} elseif ( 'cancelled_event_failed' === $res ) {
			Notice::add( 'error', __( 'Booking cancelled and the slot is free, but the Google Calendar event could not be deleted. Please remove it from the trainer\'s calendar by hand.', 'fwh-speaking-booking' ) );
		} else {
			Notice::add( 'error', __( 'That booking cannot be cancelled.', 'fwh-speaking-booking' ) );
		}
		$back = wp_get_referer();
		wp_safe_redirect( $back ? $back : add_query_arg( 'page', self::PAGE, admin_url( 'admin.php' ) ) );
		exit;
	}

	public function render(): void {
		if ( ! $this->allowed() ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'fwh-speaking-booking' ) );
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters.
		$tab     = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'upcoming';
		$trainer = isset( $_GET['trainer'] ) ? absint( $_GET['trainer'] ) : 0;
		$from    = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
		$to      = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
		$paged   = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		// phpcs:enable
		$tabs = array(
			'upcoming'  => __( 'Upcoming', 'fwh-speaking-booking' ),
			'past'      => __( 'Past', 'fwh-speaking-booking' ),
			'held'      => __( 'Held / Expired', 'fwh-speaking-booking' ),
			'cancelled' => __( 'Cancelled', 'fwh-speaking-booking' ),
		);
		$tab  = isset( $tabs[ $tab ] ) ? $tab : 'upcoming';
		$from = Time::valid_date( $from ) ? $from : '';
		$to   = Time::valid_date( $to ) ? $to : '';

		$q = array(
			'trainer_id' => $trainer,
			'limit'      => self::PER_PAGE,
			'offset'     => ( $paged - 1 ) * self::PER_PAGE,
			'order'      => 'asc',
		);
		if ( 'upcoming' === $tab ) {
			$q['statuses'] = array( 'paid' );
			$q['when']     = 'upcoming';
		} elseif ( 'past' === $tab ) {
			$q['statuses'] = array( 'paid' );
			$q['when']     = 'past';
			$q['order']    = 'desc';
		} elseif ( 'held' === $tab ) {
			$q['statuses'] = array( 'held', 'expired' );
			$q['order']    = 'desc';
		} else {
			$q['statuses'] = array( 'cancelled' );
			$q['order']    = 'desc';
		}
		if ( $from ) {
			$q['from_utc'] = Time::toronto_to_utc( $from, '00:00' );
		}
		if ( $to ) {
			$q['to_utc'] = Time::toronto_to_utc( ( new \DateTimeImmutable( $to, Time::utc() ) )->modify( '+1 day' )->format( 'Y-m-d' ), '00:00' );
		}
		$res = Booking_Query::search( $q );

		echo '<div class="wrap fwh-sb-admin"><h1>' . esc_html__( 'Bookings', 'fwh-speaking-booking' ) . '</h1>';
		Notice::render();

		$filters = array_filter( array( 'trainer' => $trainer, 'from' => $from, 'to' => $to ) );
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $k => $label ) {
			printf( '<a class="nav-tab %s" href="%s">%s</a>', $k === $tab ? 'nav-tab-active' : '', esc_url( add_query_arg( array_merge( array( 'page' => self::PAGE, 'tab' => $k ), $filters ), admin_url( 'admin.php' ) ) ), esc_html( $label ) );
		}
		echo '</h2>';

		echo '<form method="get" class="fwh-sb-filters"><input type="hidden" name="page" value="' . esc_attr( self::PAGE ) . '"><input type="hidden" name="tab" value="' . esc_attr( $tab ) . '">';
		echo '<label>' . esc_html__( 'Trainer', 'fwh-speaking-booking' ) . ' <select name="trainer"><option value="0">' . esc_html__( 'All', 'fwh-speaking-booking' ) . '</option>';
		foreach ( Repo::trainers() as $t ) {
			printf( '<option value="%d" %s>%s</option>', (int) $t->id, selected( $trainer, (int) $t->id, false ), esc_html( $t->name ) );
		}
		echo '</select></label> <label>' . esc_html__( 'From', 'fwh-speaking-booking' ) . ' <input type="date" name="from" value="' . esc_attr( $from ) . '"></label> <label>' . esc_html__( 'To', 'fwh-speaking-booking' ) . ' <input type="date" name="to" value="' . esc_attr( $to ) . '"></label> ';
		submit_button( __( 'Filter', 'fwh-speaking-booking' ), 'secondary', '', false );
		echo '</form>';

		echo '<table class="widefat striped fwh-sb-bookings"><thead><tr>';
		foreach ( array( 'Ref', 'Student', 'Email', 'Phone', 'Trainer', 'Toronto time', 'Status', 'Meet link', 'Meet status', '' ) as $h ) {
			echo '<th>' . esc_html( $h ) . '</th>';
		}
		echo '</tr></thead><tbody>';
		if ( ! $res['rows'] ) {
			echo '<tr><td colspan="10">' . esc_html__( 'No bookings found.', 'fwh-speaking-booking' ) . '</td></tr>';
		}
		foreach ( $res['rows'] as $b ) {
			echo '<tr><td>' . esc_html( $b->ref ) . '</td><td>' . esc_html( $b->student_name ) . '</td><td>' . esc_html( $b->student_email ) . '</td><td>' . esc_html( $b->student_phone ) . '</td><td>' . esc_html( (string) $b->trainer_name ) . '</td>';
			echo '<td>' . esc_html( Time::utc_to_toronto( $b->start_utc, 'D j M Y, H:i' ) ) . '</td><td>' . esc_html( ucfirst( $b->status ) ) . '</td><td>';
			echo $b->meet_link ? '<a target="_blank" rel="noopener noreferrer" href="' . esc_url( $b->meet_link ) . '">' . esc_html__( 'Open', 'fwh-speaking-booking' ) . '</a>' : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			echo '</td><td>' . esc_html( $b->meet_status );
			if ( 'paid' === $b->status && 'ok' !== $b->meet_status ) {
				echo ' ' . $this->action_form( 'fwh_sb_retry_meet', 'fwh_sb_retry_meet', (int) $b->id, __( 'Retry Meet link', 'fwh-speaking-booking' ), '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in action_form().
			}
			echo '</td><td>';
			if ( in_array( $b->status, array( 'paid', 'held' ), true ) ) {
				echo $this->action_form( 'fwh_sb_cancel_booking', 'fwh_sb_cancel_booking', (int) $b->id, __( 'Cancel', 'fwh-speaking-booking' ), __( 'Cancel this booking? The calendar event will be deleted and the time slot freed. No email is sent.', 'fwh-speaking-booking' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in action_form().
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';

		$pages = (int) ceil( $res['total'] / self::PER_PAGE );
		if ( $pages > 1 ) {
			echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(
				paginate_links(
					array(
						'base'    => add_query_arg( 'paged', '%#%' ),
						'format'  => '',
						'current' => $paged,
						'total'   => $pages,
					)
				)
			) . '</div></div>';
		}
		echo '</div>';
	}

	private function action_form( string $action, string $nonce, int $id, string $label, string $confirm ): string {
		$onsubmit = '' === $confirm ? '' : ' onsubmit="return confirm(\'' . esc_js( $confirm ) . '\')"';
		$html     = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline"' . $onsubmit . '>';
		$html    .= wp_nonce_field( $nonce, '_wpnonce', true, false );
		$html    .= '<input type="hidden" name="action" value="' . esc_attr( $action ) . '"><input type="hidden" name="booking_id" value="' . esc_attr( (string) $id ) . '">';
		$html    .= '<button class="button-link' . ( 'fwh_sb_cancel_booking' === $action ? ' button-link-delete' : '' ) . '">' . esc_html( $label ) . '</button></form>';
		return $html;
	}
}
