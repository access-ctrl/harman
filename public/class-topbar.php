<?php
/**
 * Top-of-page strip with a button that opens the booking form in a modal. No page needed.
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Topbar {

	public function init(): void {
		add_action( 'template_redirect', array( $this, 'handle_return' ) );
		add_action( 'wp_body_open', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	private function active(): bool {
		if ( is_admin() || is_feed() || ! Settings::get( 'topbar_enabled' ) ) {
			return false;
		}
		// The shortcode page already has the full widget; avoid a second copy.
		$post = is_singular() ? get_post() : null;
		return ! ( $post && has_shortcode( $post->post_content, Shortcode::TAG ) );
	}

	/** Back from Stripe: release the slot if the customer cancelled, and keep this response uncached. */
	public function handle_return(): void {
		if ( ! $this->active() || ! isset( $_GET['fwh_sb_return'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the HMAC token authorises the cancel.
			return;
		}
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
		Shortcode::handle_cancel_return();
	}

	public function enqueue(): void {
		if ( ! $this->active() ) {
			return;
		}
		$css = FWH_SB_DIR . 'assets/topbar.css';
		$js  = FWH_SB_DIR . 'assets/topbar.js';
		wp_enqueue_style( 'fwh-sb-topbar', FWH_SB_URL . 'assets/topbar.css', array(), (string) filemtime( $css ) );
		wp_enqueue_script( 'fwh-sb-topbar', FWH_SB_URL . 'assets/topbar.js', array(), (string) filemtime( $js ), true );
		wp_add_inline_script(
			'fwh-sb-topbar',
			'window.fwhSbTopbar=' . wp_json_encode(
				array(
					'restUrl' => esc_url_raw( rest_url( 'fwh-sb/v1/' ) ),
					'css'     => FWH_SB_URL . 'assets/booking.css?ver=' . filemtime( FWH_SB_DIR . 'assets/booking.css' ),
					'js'      => FWH_SB_URL . 'assets/booking.js?ver=' . filemtime( FWH_SB_DIR . 'assets/booking.js' ),
				)
			) . ';',
			'before'
		);
	}

	public function render(): void {
		if ( ! $this->active() ) {
			return;
		}
		$text   = (string) Settings::get( 'topbar_text' );
		$button = (string) Settings::get( 'topbar_button' );
		if ( '' === $button ) {
			$button = __( 'Book a speaking session', 'fwh-speaking-booking' );
		}
		?>
		<div class="fwh-sb-topbar" role="region" aria-label="<?php esc_attr_e( 'Speaking session booking', 'fwh-speaking-booking' ); ?>">
			<div class="fwh-sb-topbar__inner">
				<?php if ( '' !== $text ) : ?>
					<p class="fwh-sb-topbar__text"><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>
				<button type="button" class="fwh-sb-topbar__btn" aria-haspopup="dialog" aria-controls="fwh-sb-modal"><?php echo esc_html( $button ); ?></button>
			</div>
		</div>
		<div class="fwh-sb-modal" id="fwh-sb-modal" role="dialog" aria-modal="true" aria-labelledby="fwh-sb-modal-title" hidden>
			<div class="fwh-sb-modal__backdrop" data-fwh-close></div>
			<div class="fwh-sb-modal__panel" tabindex="-1">
				<div class="fwh-sb-modal__head">
					<span class="fwh-sb-modal__title" id="fwh-sb-modal-title"><?php echo esc_html( (string) Settings::get( 'session_name' ) ); ?></span>
					<button type="button" class="fwh-sb-modal__close" data-fwh-close aria-label="<?php esc_attr_e( 'Close', 'fwh-speaking-booking' ); ?>">&times;</button>
				</div>
				<div class="fwh-sb-wrap" id="fwh-sb-app">
					<div class="fwh-sb-body" aria-live="polite"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
