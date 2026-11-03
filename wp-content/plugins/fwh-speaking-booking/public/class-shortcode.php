<?php
/**
 * [fwh_speaking_booking] shortcode and its assets.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Shortcode {

	public const TAG = 'fwh_speaking_booking';

	private bool $enqueued = false;

	public function init(): void {
		add_shortcode( self::TAG, array( $this, 'render' ) );
		add_action( 'template_redirect', array( $this, 'maybe_prepare_page' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
	}

	private function page_has_shortcode(): bool {
		$post = is_singular() ? get_post() : null;
		return $post && has_shortcode( $post->post_content, self::TAG );
	}

	/** Keep the booking page out of every cache layer. */
	public function maybe_prepare_page(): void {
		if ( $this->page_has_shortcode() ) {
			$this->no_cache();
			self::handle_cancel_return();
		}
	}

	/** Customer came back from Stripe's "back" link: free the held slot immediately. */
	public static function handle_cancel_return(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- the HMAC token below authorises this.
		if ( isset( $_GET['fwh_sb_return'], $_GET['ref'], $_GET['tok'] ) && 'cancel' === $_GET['fwh_sb_return'] ) {
			Bookings::cancel_from_return( sanitize_text_field( wp_unslash( $_GET['ref'] ) ), sanitize_text_field( wp_unslash( $_GET['tok'] ) ) );
		}
		// phpcs:enable
	}

	private function no_cache(): void {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		if ( ! headers_sent() ) {
			nocache_headers();
		}
	}

	public function maybe_enqueue(): void {
		if ( $this->page_has_shortcode() ) {
			$this->enqueue();
		}
	}

	private function enqueue(): void {
		if ( $this->enqueued ) {
			return;
		}
		$this->enqueued = true;
		$css = FWH_SB_DIR . 'assets/booking.css';
		$js  = FWH_SB_DIR . 'assets/booking.js';
		wp_enqueue_style( 'fwh-sb-booking', FWH_SB_URL . 'assets/booking.css', array(), (string) filemtime( $css ) );
		wp_enqueue_script( 'fwh-sb-booking', FWH_SB_URL . 'assets/booking.js', array(), (string) filemtime( $js ), true );
		wp_add_inline_script( 'fwh-sb-booking', 'window.fwhSbConfig=' . wp_json_encode( self::config() ) . ';', 'before' );
	}

	/** What students read for the session length: the custom label, else "N minutes". */
	public static function duration_label(): string {
		$label = trim( (string) Settings::get( 'duration_label' ) );
		if ( '' !== $label ) {
			return $label;
		}
		/* translators: %d: minutes */
		return sprintf( __( '%d minutes', 'fwh-speaking-booking' ), (int) Settings::get( 'duration' ) );
	}

	public static function config(): array {
		$today  = new \DateTimeImmutable( 'now', Time::toronto() );
		$window = (int) Settings::get( 'booking_window_days' );
		$last   = $today->modify( "+{$window} days" );
		return array(
			'restUrl'  => esc_url_raw( rest_url( 'fwh-sb/v1/' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'duration' => (int) Settings::get( 'duration' ),
			'durationLabel' => self::duration_label(),
			'price'    => (float) Settings::get( 'price' ),
			'currency' => strtoupper( (string) Settings::get( 'currency' ) ),
			'from'     => $today->format( 'Y-m-d' ),
			'to'       => $last->format( 'Y-m-d' ),
			'timezones' => array_keys( Time::canada_zones() ),
			'tzLabels' => Time::canada_zones(),
			'tzAliases' => Time::canada_aliases(),
			'homeTz'   => 'America/Toronto',
			'contactEmail' => (string) Settings::get( 'contact_email' ),
		);
	}

	/** e.g. "25.00 CAD", or '' when no price is set. */
	public static function price_label(): string {
		$price = (float) Settings::get( 'price' );
		return $price > 0 ? number_format_i18n( $price, 2 ) . ' ' . strtoupper( (string) Settings::get( 'currency' ) ) : '';
	}

	public function render(): string {
		$this->no_cache();
		$this->enqueue();

		$label = self::price_label();

		ob_start();
		?>
		<div class="fwh-sb-wrap" id="fwh-sb-app">
			<div class="fwh-sb-card">
				<h2 class="fwh-sb-title"><?php echo esc_html( (string) Settings::get( 'session_name' ) ); ?></h2>
				<p class="fwh-sb-meta">
					<span><?php echo esc_html( self::duration_label() ); ?></span>
					<?php if ( '' !== $label ) : ?>
						<span><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
				</p>
			</div>
			<div class="fwh-sb-body" aria-live="polite">
				<noscript><p><?php esc_html_e( 'Please enable JavaScript to book a session.', 'fwh-speaking-booking' ); ?></p></noscript>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
