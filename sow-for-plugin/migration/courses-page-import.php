<?php
/**
 * Courses page import for the Harman site (run with WP-CLI).
 *
 * Creates or updates the "Courses" page with the content in courses-page.json (same folder):
 * the four Comparison Table sections, their buttons, spacing and card illustrations.
 *
 *   Dry run (changes nothing):   wp eval-file courses-page-import.php --user=<admin login>
 *   Apply:                       FWH_APPLY=1 wp eval-file courses-page-import.php --user=<admin login>
 *   Undo (restore a backup):     FWH_RESTORE=/path/to/courses-backup-....json wp eval-file courses-page-import.php --user=<admin login>
 *
 * Optional: FWH_SLUG=some-other-slug (default "courses"); FWH_IMAGE_DIR=/folder/with/the/svg/files (default: the theme's assets/images/cards).
 *
 * What it does NOT touch: other pages, menus, Yoast settings, the ACF field group itself (import that first),
 * users, orders, the booking plugin data.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$apply   = '1' === getenv( 'FWH_APPLY' );
$restore = getenv( 'FWH_RESTORE' );
$slug    = getenv( 'FWH_SLUG' ) ?: 'courses';
$say     = static function ( $m ) { WP_CLI::log( $m ); };
$fail    = static function ( $m ) { WP_CLI::error( $m ); };

global $wpdb;

/* ---------- restore mode ---------- */
if ( $restore ) {
	if ( ! is_readable( $restore ) ) { $fail( "Cannot read $restore" ); }
	$b = json_decode( file_get_contents( $restore ), true );
	if ( ! is_array( $b ) || empty( $b['post_id'] ) || ! isset( $b['meta'] ) ) { $fail( 'That is not a backup file made by this script.' ); }
	$pid = (int) $b['post_id'];
	if ( ! get_post( $pid ) ) { $fail( "Page $pid no longer exists." ); }
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND (meta_key LIKE 'page\\_section%%' OR meta_key LIKE '\\_page\\_section%%')", $pid ) );
	foreach ( $b['meta'] as $row ) {
		$wpdb->insert( $wpdb->postmeta, array( 'post_id' => $pid, 'meta_key' => $row['k'], 'meta_value' => $row['v'] ) );
	}
	clean_post_cache( $pid );
	WP_CLI::success( 'Restored ' . count( $b['meta'] ) . " page_section values on page $pid (" . get_permalink( $pid ) . '). Clear your cache.' );
	return;
}

/* ---------- read the content ---------- */
$file = __DIR__ . '/courses-page.json';
if ( ! is_readable( $file ) ) { $fail( "courses-page.json must sit next to this script ($file)." ); }
$data = json_decode( file_get_contents( $file ), true );
if ( ! is_array( $data ) || empty( $data['sections'] ) ) { $fail( 'courses-page.json is not valid.' ); }

/* ---------- checks ---------- */
$say( '== Checks' );
if ( ! function_exists( 'acf_get_field' ) || ! function_exists( 'update_field' ) ) { $fail( 'ACF Pro is not active on this site.' ); }
$flex = acf_get_field( $data['acf_field_key'] );
if ( ! $flex || 'flexible_content' !== $flex['type'] ) { $fail( 'The ACF "Page Section" field (' . $data['acf_field_key'] . ') is missing. Import the ACF field group from staging first (ACF, Tools, Import).' ); }
$layout_ok = false;
foreach ( (array) $flex['layouts'] as $l ) { if ( 'comparison_table' === $l['name'] ) { $layout_ok = true; } }
if ( ! $layout_ok ) { $fail( 'The "Comparison Table" layout is missing from the ACF field group.' ); }
$say( '  OK  ACF "Page Section" field and Comparison Table layout found' );
if ( acf_get_field( 'field_fwh_cmp_space01' ) ) { $say( '  OK  "Section spacing" field found' ); } else { WP_CLI::warning( 'The "Section spacing" field is missing, so spacing options will be ignored. Re-import the ACF field group from staging.' ); }

$dir = trailingslashit( getenv( 'FWH_IMAGE_DIR' ) ?: get_template_directory() . '/assets/images/cards' );
$need = array();
foreach ( $data['sections'] as $s ) { foreach ( array( 'table_1_icon', 'table_2_icon' ) as $k ) { if ( ! empty( $s[ $k ]['file'] ) ) { $need[ $s[ $k ]['file'] ] = true; } } }
$ids = array();
foreach ( array_keys( $need ) as $f ) {
	if ( ! is_readable( $dir . $f ) ) { $fail( "Image file missing in the theme: assets/images/cards/$f. Deploy the theme first." ); }
	$found = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND (meta_value = %s OR meta_value LIKE %s) ORDER BY post_id DESC LIMIT 1", $f, '%/' . $wpdb->esc_like( $f ) ) );
	$ids[ $f ] = $found ? (int) $found : 0;
	$say( sprintf( '  %-4s image %-32s %s', $found ? 'OK' : 'ADD', $f, $found ? "already in Media Library (#$found)" : 'will be added to the Media Library' ) );
}

$existing = get_page_by_path( $slug, OBJECT, 'page' );
$say( '' );
$say( '== Plan' );
$say( $existing ? "  Page \"$slug\" exists (#{$existing->ID}, {$existing->post_status}). Its sections will be REPLACED by the ones below. A backup is saved first." : "  Page \"$slug\" does not exist. It will be CREATED and published." );
$strip = static function ( $s ) { return trim( html_entity_decode( wp_strip_all_tags( (string) $s ) ) ); };
foreach ( $data['sections'] as $i => $s ) {
	foreach ( array( 1, 2 ) as $c ) {
		$t = $strip( $s[ "table_{$c}_title" ] ?? '' );
		if ( '' === $t ) { continue; }
		$say( sprintf( '  section %d card %d  %-40s %-30s button: %s  spacing: %s', $i + 1, $c, $t, $s[ "table_{$c}_icon" ]['file'] ?? '-', $s[ "table_{$c}_button" ]['url'] ?? '-', $s['space'] ?? '-' ) );
	}
}
if ( ! $apply ) {
	$say( '' );
	WP_CLI::success( 'Dry run only: nothing was changed. To apply, run again with FWH_APPLY=1 in front of the command.' );
	return;
}

/* ---------- apply ---------- */
$say( '' );
$say( '== Applying' );
// 1. images
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
add_filter( 'upload_mimes', static function ( $m ) { $m['svg'] = 'image/svg+xml'; return $m; } );
add_filter( 'wp_check_filetype_and_ext', static function ( $r, $f, $name ) { if ( preg_match( '/\.svg$/i', $name ) ) { $r['ext'] = 'svg'; $r['type'] = 'image/svg+xml'; $r['proper_filename'] = $name; } return $r; }, 10, 3 );
foreach ( $ids as $f => $id ) {
	if ( $id ) { continue; }
	$tmp = wp_tempnam( $f ); copy( $dir . $f, $tmp );
	$new = media_handle_sideload( array( 'name' => $f, 'tmp_name' => $tmp ), 0, 'Card: ' . preg_replace( '/\.svg$/', '', $f ) );
	if ( is_wp_error( $new ) ) { @unlink( $tmp ); $fail( "Could not add $f to the Media Library: " . $new->get_error_message() . ' (run the command with --user=<admin login>)' ); }
	$ids[ $f ] = (int) $new; $say( "  added $f to the Media Library (#$new)" );
}
// 2. page
if ( $existing ) {
	$pid  = $existing->ID;
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND (meta_key LIKE 'page\\_section%%' OR meta_key LIKE '\\_page\\_section%%')", $pid ), ARRAY_A );
	$bk   = array( 'made' => gmdate( 'c' ), 'site' => home_url(), 'post_id' => $pid, 'slug' => $slug, 'meta' => array_map( static fn( $r ) => array( 'k' => $r['meta_key'], 'v' => $r['meta_value'] ), $rows ) );
	$home = getenv( 'HOME' ) ?: sys_get_temp_dir();
	$path = rtrim( $home, '/' ) . '/courses-backup-' . $slug . '-' . gmdate( 'Ymd-His' ) . '.json';
	file_put_contents( $path, wp_json_encode( $bk ) ); chmod( $path, 0600 );
	$say( "  backup of the current sections saved: $path  (" . count( $rows ) . ' values)' );
} else {
	$pid = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $data['post']['post_title'], 'post_name' => $slug, 'post_content' => (string) $data['post']['post_content'] ), true );
	if ( is_wp_error( $pid ) ) { $fail( 'Could not create the page: ' . $pid->get_error_message() ); }
	$say( "  created page #$pid" );
}
// 3. sections
$rows = array();
foreach ( $data['sections'] as $s ) {
	$r = array( 'acf_fc_layout' => $s['acf_fc_layout'] );
	foreach ( $s as $k => $v ) {
		if ( 'acf_fc_layout' === $k ) { continue; }
		if ( preg_match( '/_icon$/', $k ) ) { $r[ $k ] = ! empty( $v['file'] ) ? $ids[ $v['file'] ] : ''; continue; }
		if ( preg_match( '/_animated_video$/', $k ) ) { $r[ $k ] = ''; continue; }
		$r[ $k ] = $v;
	}
	$rows[] = $r;
}
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND (meta_key LIKE 'page\\_section%%' OR meta_key LIKE '\\_page\\_section%%')", $pid ) );
update_field( $data['acf_field_key'], $rows, $pid );
clean_post_cache( $pid );
// 4. verify what was saved
$saved = get_field( 'page_section', $pid );
$okc = is_array( $saved ) && count( $saved ) === count( $data['sections'] );
foreach ( (array) $saved as $i => $s ) {
	foreach ( array( 1, 2 ) as $c ) {
		$want = $strip( $data['sections'][ $i ][ "table_{$c}_title" ] ?? '' ); $got = $strip( $s[ "table_{$c}_title" ] ?? '' );
		$wf = $data['sections'][ $i ][ "table_{$c}_icon" ]['file'] ?? ''; $gf = ! empty( $s[ "table_{$c}_icon" ] ) ? basename( parse_url( $s[ "table_{$c}_icon" ], PHP_URL_PATH ) ) : '';
		if ( $want !== $got || $wf !== $gf ) { $okc = false; $say( "  MISMATCH section " . ( $i + 1 ) . " card $c: wanted \"$want\" / $wf, got \"$got\" / $gf" ); }
	}
}
$say( '' );
if ( ! $okc ) { WP_CLI::error( 'The saved page does not match the content file. Nothing else was changed. Restore with FWH_RESTORE if needed.' ); }
WP_CLI::success( 'Courses page saved with ' . count( $saved ) . ' sections: ' . get_permalink( $pid ) . '  Clear your site and Cloudflare caches, then check the page.' );
