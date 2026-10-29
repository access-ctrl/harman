<?php
/**
 * Trainers admin page: list, add/edit, weekly hours, deactivate, create login.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Admin_Trainers {

	private const PAGE = 'fwh-sb-trainers';

	private const DAYS = array(
		1 => 'Monday',
		2 => 'Tuesday',
		3 => 'Wednesday',
		4 => 'Thursday',
		5 => 'Friday',
		6 => 'Saturday',
	);

	public function init(): void {
		add_action( 'admin_post_fwh_sb_save_trainer', array( $this, 'handle_save' ) );
		add_action( 'admin_post_fwh_sb_toggle_trainer', array( $this, 'handle_toggle' ) );
		add_action( 'admin_post_fwh_sb_create_login', array( $this, 'handle_create_login' ) );
	}

	private function guard( string $action ): void {
		if ( ! current_user_can( 'fwh_sb_manage' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'fwh-speaking-booking' ), 403 );
		}
		check_admin_referer( $action );
	}

	public function handle_save(): void {
		$this->guard( 'fwh_sb_save_trainer' );
		$id    = isset( $_POST['trainer_id'] ) ? absint( $_POST['trainer_id'] ) : 0;
		$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$gmail = isset( $_POST['google_email'] ) ? sanitize_email( wp_unslash( $_POST['google_email'] ) ) : '';
		$back  = array( 'action' => 'edit', 'trainer' => $id );

		if ( '' === $name || ! is_email( $email ) || ( '' !== $gmail && ! is_email( $gmail ) ) ) {
			Notice::add( 'error', __( 'Name and a valid email are required; Google email must be valid if given.', 'fwh-speaking-booking' ) );
			Notice::redirect( self::PAGE, $id ? $back : array( 'action' => 'new' ) );
		}
		if ( $id && ! Repo::trainer( $id ) ) {
			Notice::add( 'error', __( 'Trainer not found.', 'fwh-speaking-booking' ) );
			Notice::redirect( self::PAGE );
		}

		$rows = $this->parse_hours();
		if ( is_string( $rows ) ) {
			Notice::add( 'error', $rows );
			Notice::redirect( self::PAGE, $id ? $back : array( 'action' => 'new' ) );
		}

		$id = Repo::save_trainer(
			$id,
			array(
				'name'         => $name,
				'email'        => $email,
				'google_email' => $gmail,
				'active'       => ! empty( $_POST['active'] ),
			)
		);
		if ( ! $id ) {
			Notice::add( 'error', __( 'Could not save the trainer.', 'fwh-speaking-booking' ) );
			Notice::redirect( self::PAGE );
		}
		Repo::replace_hours( $id, $rows );
		Notice::add( 'success', __( 'Trainer saved.', 'fwh-speaking-booking' ) );
		Notice::redirect( self::PAGE, array( 'action' => 'edit', 'trainer' => $id ) );
	}

	/**
	 * Validate submitted hour ranges. Returns rows or an error string.
	 *
	 * @return array|string
	 */
	private function parse_hours() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in guard().
		$days   = isset( $_POST['hours_day'] ) ? (array) wp_unslash( $_POST['hours_day'] ) : array();
		$starts = isset( $_POST['hours_start'] ) ? (array) wp_unslash( $_POST['hours_start'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$ends   = isset( $_POST['hours_end'] ) ? (array) wp_unslash( $_POST['hours_end'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$rows   = array();
		foreach ( $days as $i => $day ) {
			$day   = (int) $day;
			$start = isset( $starts[ $i ] ) ? sanitize_text_field( $starts[ $i ] ) : '';
			$end   = isset( $ends[ $i ] ) ? sanitize_text_field( $ends[ $i ] ) : '';
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

	public function handle_toggle(): void {
		$this->guard( 'fwh_sb_toggle_trainer' );
		$id = isset( $_POST['trainer_id'] ) ? absint( $_POST['trainer_id'] ) : 0;
		if ( $id && Repo::trainer( $id ) ) {
			$active = ! empty( $_POST['make_active'] );
			Repo::set_active( $id, $active );
			Notice::add( 'success', $active ? __( 'Trainer activated.', 'fwh-speaking-booking' ) : __( 'Trainer deactivated.', 'fwh-speaking-booking' ) );
		}
		Notice::redirect( self::PAGE );
	}

	public function handle_create_login(): void {
		$this->guard( 'fwh_sb_create_login' );
		$id      = isset( $_POST['trainer_id'] ) ? absint( $_POST['trainer_id'] ) : 0;
		$trainer = $id ? Repo::trainer( $id ) : null;
		$back    = array( 'action' => 'edit', 'trainer' => $id );
		if ( ! $trainer ) {
			Notice::add( 'error', __( 'Trainer not found.', 'fwh-speaking-booking' ) );
			Notice::redirect( self::PAGE );
		}
		if ( $trainer->user_id && get_userdata( (int) $trainer->user_id ) ) {
			Notice::add( 'error', __( 'This trainer already has a login.', 'fwh-speaking-booking' ) );
			Notice::redirect( self::PAGE, $back );
		}
		if ( email_exists( $trainer->email ) || username_exists( $trainer->email ) ) {
			Notice::add( 'error', __( 'A WordPress user with this email already exists. Use a different trainer email.', 'fwh-speaking-booking' ) );
			Notice::redirect( self::PAGE, $back );
		}
		$user_id = wp_insert_user(
			array(
				'user_login'   => $trainer->email,
				'user_email'   => $trainer->email,
				'display_name' => $trainer->name,
				'first_name'   => $trainer->name,
				'user_pass'    => wp_generate_password( 24, true, true ),
				'role'         => 'fwh_sb_trainer',
			)
		);
		if ( is_wp_error( $user_id ) ) {
			error_log( '[fwh-sb] create login failed: ' . $user_id->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			Notice::add( 'error', __( 'Could not create the login.', 'fwh-speaking-booking' ) );
			Notice::redirect( self::PAGE, $back );
		}
		Repo::link_user( $id, (int) $user_id );
		wp_new_user_notification( (int) $user_id, null, 'user' );
		Notice::add( 'success', __( 'Login created. The trainer has been emailed a link to set their password.', 'fwh-speaking-booking' ) );
		Notice::redirect( self::PAGE, $back );
	}

	public function render(): void {
		if ( ! current_user_can( 'fwh_sb_manage' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'fwh-speaking-booking' ) );
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only view routing.
		$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
		$id     = isset( $_GET['trainer'] ) ? absint( $_GET['trainer'] ) : 0;
		// phpcs:enable
		echo '<div class="wrap">';
		Notice::render();
		if ( 'new' === $action ) {
			$this->render_form( null );
		} elseif ( 'edit' === $action && $id && Repo::trainer( $id ) ) {
			$this->render_form( Repo::trainer( $id ) );
		} else {
			$this->render_list();
		}
		echo '</div>';
	}

	private function render_list(): void {
		$new = add_query_arg( array( 'page' => self::PAGE, 'action' => 'new' ), admin_url( 'admin.php' ) );
		echo '<h1 class="wp-heading-inline">' . esc_html__( 'Trainers', 'fwh-speaking-booking' ) . '</h1> <a href="' . esc_url( $new ) . '" class="page-title-action">' . esc_html__( 'Add trainer', 'fwh-speaking-booking' ) . '</a><hr class="wp-header-end">';
		echo '<table class="widefat striped"><thead><tr><th>Name</th><th>Email</th><th>Google email</th><th>Login</th><th>Status</th><th></th></tr></thead><tbody>';
		$trainers = Repo::trainers();
		if ( ! $trainers ) {
			echo '<tr><td colspan="6">' . esc_html__( 'No trainers yet.', 'fwh-speaking-booking' ) . '</td></tr>';
		}
		foreach ( $trainers as $t ) {
			$edit = add_query_arg( array( 'page' => self::PAGE, 'action' => 'edit', 'trainer' => $t->id ), admin_url( 'admin.php' ) );
			echo '<tr><td>' . esc_html( $t->name ) . '</td><td>' . esc_html( $t->email ) . '</td><td>' . esc_html( $t->google_email ) . '</td>';
			echo '<td>' . ( $t->user_id ? esc_html__( 'Yes', 'fwh-speaking-booking' ) : '&mdash;' ) . '</td>';
			echo '<td>' . ( $t->active ? esc_html__( 'Active', 'fwh-speaking-booking' ) : esc_html__( 'Inactive', 'fwh-speaking-booking' ) ) . '</td><td>';
			echo '<a href="' . esc_url( $edit ) . '">' . esc_html__( 'Edit', 'fwh-speaking-booking' ) . '</a> ';
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">';
			wp_nonce_field( 'fwh_sb_toggle_trainer' );
			echo '<input type="hidden" name="action" value="fwh_sb_toggle_trainer"><input type="hidden" name="trainer_id" value="' . esc_attr( (string) $t->id ) . '">';
			if ( ! $t->active ) {
				echo '<input type="hidden" name="make_active" value="1">';
			}
			echo '<button class="button-link">' . ( $t->active ? esc_html__( 'Deactivate', 'fwh-speaking-booking' ) : esc_html__( 'Activate', 'fwh-speaking-booking' ) ) . '</button></form>';
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}

	private function render_form( ?object $t ): void {
		$id    = $t ? (int) $t->id : 0;
		$hours = $t ? Repo::hours( $id ) : array();
		$back  = add_query_arg( array( 'page' => self::PAGE ), admin_url( 'admin.php' ) );
		echo '<h1>' . ( $t ? esc_html__( 'Edit trainer', 'fwh-speaking-booking' ) : esc_html__( 'Add trainer', 'fwh-speaking-booking' ) ) . '</h1>';
		echo '<p><a href="' . esc_url( $back ) . '">&larr; ' . esc_html__( 'All trainers', 'fwh-speaking-booking' ) . '</a></p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'fwh_sb_save_trainer' );
		echo '<input type="hidden" name="action" value="fwh_sb_save_trainer"><input type="hidden" name="trainer_id" value="' . esc_attr( (string) $id ) . '">';
		echo '<table class="form-table" role="presentation">';
		printf( '<tr><th><label for="fwh_sb_name">%s</label></th><td><input id="fwh_sb_name" name="name" class="regular-text" required value="%s"></td></tr>', esc_html__( 'Name', 'fwh-speaking-booking' ), esc_attr( $t->name ?? '' ) );
		printf( '<tr><th><label for="fwh_sb_email">%s</label></th><td><input id="fwh_sb_email" type="email" name="email" class="regular-text" required value="%s"></td></tr>', esc_html__( 'Email', 'fwh-speaking-booking' ), esc_attr( $t->email ?? '' ) );
		printf( '<tr><th><label for="fwh_sb_gemail">%s</label></th><td><input id="fwh_sb_gemail" type="email" name="google_email" class="regular-text" value="%s"></td></tr>', esc_html__( 'Google account email', 'fwh-speaking-booking' ), esc_attr( $t->google_email ?? '' ) );
		printf( '<tr><th>%s</th><td><label><input type="checkbox" name="active" value="1" %s> %s</label></td></tr>', esc_html__( 'Active', 'fwh-speaking-booking' ), checked( ! $t || $t->active, true, false ), esc_html__( 'Available for bookings', 'fwh-speaking-booking' ) );
		echo '</table>';

		echo '<h2>' . esc_html__( 'Weekly hours (Toronto time, Mon-Sat)', 'fwh-speaking-booking' ) . '</h2>';
		echo '<p class="description">' . esc_html__( 'Add several ranges per day if needed. Leave start and end blank to remove a row. Sunday is always closed.', 'fwh-speaking-booking' ) . '</p>';
		echo '<table class="widefat" style="max-width:520px" id="fwh-sb-hours"><thead><tr><th>Day</th><th>Start</th><th>End</th></tr></thead><tbody>';
		$rows = array();
		foreach ( $hours as $h ) {
			$rows[] = array( (int) $h->weekday, substr( $h->start_time, 0, 5 ), substr( $h->end_time, 0, 5 ) );
		}
		for ( $i = 0; $i < 4; $i++ ) { // Spare blank rows.
			$rows[] = array( 1, '', '' );
		}
		foreach ( $rows as $r ) {
			echo '<tr><td><select name="hours_day[]">';
			foreach ( self::DAYS as $num => $label ) {
				printf( '<option value="%d" %s>%s</option>', (int) $num, selected( $r[0], $num, false ), esc_html( $label ) );
			}
			printf( '</select></td><td><input type="time" name="hours_start[]" value="%s"></td><td><input type="time" name="hours_end[]" value="%s"></td></tr>', esc_attr( $r[1] ), esc_attr( $r[2] ) );
		}
		echo '</tbody></table>';
		submit_button( $t ? __( 'Save trainer', 'fwh-speaking-booking' ) : __( 'Add trainer', 'fwh-speaking-booking' ) );
		echo '</form>';

		if ( $t ) {
			echo '<h2>' . esc_html__( 'Google Calendar and Meet', 'fwh-speaking-booking' ) . '</h2>';
			Admin_Google::connection_box( $id );
			echo '<h2>' . esc_html__( 'Login', 'fwh-speaking-booking' ) . '</h2>';
			if ( $t->user_id && get_userdata( (int) $t->user_id ) ) {
				echo '<p>' . esc_html__( 'This trainer has a WordPress login.', 'fwh-speaking-booking' ) . '</p>';
			} else {
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
				wp_nonce_field( 'fwh_sb_create_login' );
				echo '<input type="hidden" name="action" value="fwh_sb_create_login"><input type="hidden" name="trainer_id" value="' . esc_attr( (string) $id ) . '">';
				submit_button( __( 'Create login', 'fwh-speaking-booking' ), 'secondary' );
				echo '<p class="description">' . esc_html__( 'Creates a Speaking Trainer user and emails a set-password link.', 'fwh-speaking-booking' ) . '</p></form>';
			}
		}
	}
}
