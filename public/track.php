<?php

/**
 * Standalone visit tracking endpoint.
 *
 * Boots WordPress with SHORTINIT so no plugins, theme or most of core load -
 * a tracking hit through admin-ajax.php costs a full WordPress boot, this
 * route only wpdb + object cache + the meta API. On any failure it responds
 * with an uncacheable error status and visitors.js falls back to the legacy
 * admin-ajax endpoint.
 */

// Direct access only - never as part of a regular WordPress load
if ( defined( 'WPINC' ) ) exit;

// Page cache drop-ins (Batcache, WP Super Cache, W3TC...) load even under
// SHORTINIT and must not serve this response to other visitors.
define( 'DONOTCACHEPAGE', true );

define( 'SHORTINIT', true );

// WP_DEBUG notices must not break our headers
ob_start();

/**
 * Respond with an uncacheable status and stop.
 * @param int $status
 */
function naswp_track_fail( int $status ): void
{
	while ( ob_get_level() ) ob_end_clean();
	if ( !headers_sent() ) {
		http_response_code( $status );
		header( 'Cache-Control: no-store' );
	}
	exit();
}

/**
 * Send headers shared by the 200 and 304 responses. 'private' allows browser
 * caching (repeat views within a day make no request at all) but keeps shared
 * caches (CDN, nginx fastcgi_cache...) from swallowing other visitors' hits.
 * @param int $lastMod
 */
function naswp_track_send_cache_headers( int $lastMod ): void
{
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', $lastMod ) );
	header( 'Cache-Control: private, max-age=86400' );
	header( 'X-Accel-Expires: 0' );
	header( 'X-Robots-Tag: noindex' );
}

/**
 * Locate wp-load.php. Climbs from the path the web server resolved (works for
 * symlinked plugin directories), then from the real path, then probes common
 * document root layouts.
 * @return string|null
 */
function naswp_track_find_wp_load(): ?string
{
	$starts = [];
	if ( !empty( $_SERVER['SCRIPT_FILENAME'] ) ) $starts[] = dirname( $_SERVER['SCRIPT_FILENAME'] );
	$starts[] = __DIR__;

	foreach ( $starts as $dir ) {
		for ( $i = 0; $i < 7; $i++ ) {
			if ( file_exists( $dir . '/wp-load.php' ) ) return $dir . '/wp-load.php';
			$parent = dirname( $dir );
			if ( $parent === $dir ) break;
			$dir = $parent;
		}
	}

	if ( !empty( $_SERVER['DOCUMENT_ROOT'] ) ) {
		foreach ( [ '/wp-load.php', '/wp/wp-load.php' ] as $candidate ) {
			if ( file_exists( $_SERVER['DOCUMENT_ROOT'] . $candidate ) ) return $_SERVER['DOCUMENT_ROOT'] . $candidate;
		}
	}

	return null;
}

if ( !isset( $_SERVER['REQUEST_METHOD'] ) || $_SERVER['REQUEST_METHOD'] !== 'GET' ) naswp_track_fail( 405 );

$naswp_track_wp_load = naswp_track_find_wp_load();
if ( $naswp_track_wp_load === null ) naswp_track_fail( 500 );
require_once( $naswp_track_wp_load );

// Subdirectory multisite would resolve this URL to the main site and write
// into a wrong table prefix - let the JS fall back to admin-ajax instead.
if ( is_multisite() ) naswp_track_fail( 500 );

// Ancient WordPress without the meta API this early in the boot
if ( !function_exists( 'get_metadata' ) || !function_exists( 'update_metadata' ) || !function_exists( 'get_option' ) ) naswp_track_fail( 500 );

// SHORTINIT loads neither post.php nor taxonomy.php, but core meta updates
// reach into both: get_object_subtype() calls get_post_type()/get_term() and
// default-filters.php hooks the *_last_changed and term meta support
// callbacks. Provide minimal stand-ins so the meta API can run.
if ( !function_exists( 'get_post_type' ) ) {
	function get_post_type( $post = null ) { return false; }
}
if ( !function_exists( 'get_term' ) ) {
	function get_term( $term = null, $taxonomy = '', $output = 'OBJECT', $filter = 'raw' ) { return null; }
}
if ( !function_exists( 'wp_cache_set_posts_last_changed' ) ) {
	function wp_cache_set_posts_last_changed() { wp_cache_set_last_changed( 'posts' ); }
}
if ( !function_exists( 'wp_cache_set_terms_last_changed' ) ) {
	function wp_cache_set_terms_last_changed() { wp_cache_set_last_changed( 'terms' ); }
}
if ( !function_exists( 'wp_check_term_meta_support_prefilter' ) ) {
	function wp_check_term_meta_support_prefilter( $check ) { return $check; }
}

require_once( __DIR__ . '/../includes/constants.php' );
require_once( __DIR__ . '/../includes/class-naswp-visitors-token.php' );
require_once( __DIR__ . '/../includes/class-naswp-visitors-base.php' );
require_once( __DIR__ . '/../includes/class-naswp-visitors-post-light.php' );
require_once( __DIR__ . '/../includes/class-naswp-visitors-term-light.php' );

// Set last modified timestamp to constant (same value as the legacy endpoint)
$naswp_track_last_mod = 1600000000;

// The script has been loaded with the same modified date - do not track
$naswp_track_ims = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
if ( $naswp_track_ims && strtotime( $naswp_track_ims ) === $naswp_track_last_mod ) {
	while ( ob_get_level() ) ob_end_clean();
	http_response_code( 304 );
	naswp_track_send_cache_headers( $naswp_track_last_mod );
	exit();
}

// Check token
$naswp_track_id = isset( $_GET['ID'] ) ? $_GET['ID'] : null;
$naswp_track_type = isset( $_GET['type'] ) ? $_GET['type'] : null;
$naswp_track_d = isset( $_GET['d'] ) ? $_GET['d'] : null;
$naswp_track_token = isset( $_GET['token'] ) ? $_GET['token'] : null;
if ( !NasWP_Visitors_Token::verify( $naswp_track_id, $naswp_track_type, $naswp_track_d, $naswp_track_token ) ) naswp_track_fail( 403 );

// Track visit of post or term
try {
	$naswp_track_model = ( $naswp_track_type === 'post' )
		? new NasWP_Visitors_Post_Light( intval( $naswp_track_id ) )
		: new NasWP_Visitors_Term_Light( intval( $naswp_track_id ) );
	$naswp_track_model->track_visit();
} catch ( \Throwable $e ) {
	naswp_track_fail( 500 );
}

while ( ob_get_level() ) ob_end_clean();
naswp_track_send_cache_headers( $naswp_track_last_mod );
header( 'X-NasWP-Tracked: 1' );
exit();
