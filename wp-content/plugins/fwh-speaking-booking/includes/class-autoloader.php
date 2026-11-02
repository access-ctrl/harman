<?php
/**
 * PSR-style autoloader mapping FWH\SpeakingBooking\Foo_Bar to includes/class-foo-bar.php (then /admin and /public).
 *
 * @package FWH\SpeakingBooking
 */

namespace FWH\SpeakingBooking;

defined( 'ABSPATH' ) || exit;

class Autoloader {

	private const PREFIX = 'FWH\\SpeakingBooking\\';

	public static function register(): void {
		spl_autoload_register( array( self::class, 'load' ) );
	}

	public static function load( string $class_name ): void {
		if ( 0 !== strpos( $class_name, self::PREFIX ) ) {
			return;
		}
		$relative = substr( $class_name, strlen( self::PREFIX ) );
		$file     = 'class-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';
		$path     = FWH_SB_DIR . 'includes/' . $file;
		if ( is_readable( $path ) ) {
			require_once $path;
			return;
		}
		foreach ( array( 'admin', 'public' ) as $dir ) {
			$path = FWH_SB_DIR . $dir . '/' . $file;
			if ( is_readable( $path ) ) {
				require_once $path;
				return;
			}
		}
	}
}
