<?php
/**
 * Plugin Name: Visitors counter
 * Plugin URI: https://naswp.cz/
 * Description: Visitor counting plugin without cookies, localStorage or sessionStorage.
 * Version: 0.0.1
 * Author: NášWP.cz
 * Author URI: https://naswp.cz/
 * Text Domain: visitors-naswp
 * Domain Path: /languages
 */

// No direct access
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Which post types to track by default?
// Use filter 'naswp_visitors_cpt' to change it.
if ( !defined( 'NASWP_VISITORS_CPT_DEFAULT' ) ) {
	define( 'NASWP_VISITORS_CPT_DEFAULT', [
		'page',
		'post'
	] );
}


// Which taxonomies to track by default?
// Use filter 'naswp_visitors_tax' to change it.
if ( !defined( 'NASWP_VISITORS_TAX_DEFAULT' ) ) {
	define( 'NASWP_VISITORS_TAX_DEFAULT', [
		'category',
		'tag'
	] );
}


// Visits of all anonymous users are tracked.
// Logged-in users of following default roles will be tracked as well.
// Use filter 'naswp_visitors_roles' to change it.
if ( !defined( 'NASWP_VISITORS_USE_WITH_ROLES_DEFAULT' ) ) {
	define( 'NASWP_VISITORS_USE_WITH_ROLES_DEFAULT', [
		'subscriber'
	] );
}

// Meta keys and admin column names
if ( !defined( 'NASWP_VISITORS_DATA' ) ) {
	define( 'NASWP_VISITORS_DATA', 'naswp_visitors_data' );
}
if ( !defined( 'NASWP_VISITORS_TOTAL' ) ) {
	define( 'NASWP_VISITORS_TOTAL', 'naswp_visitors_total' );
}
if ( !defined( 'NASWP_VISITORS_DAILY' ) ) {
	define( 'NASWP_VISITORS_DAILY', 'naswp_visitors_daily' );
}
if ( !defined( 'NASWP_VISITORS_MONTHLY' ) ) {
	define( 'NASWP_VISITORS_MONTHLY', 'naswp_visitors_monthly' );
}
if ( !defined( 'NASWP_VISITORS_YEARLY' ) ) {
	define( 'NASWP_VISITORS_YEARLY', 'naswp_visitors_yearly' );
}
if ( !defined( 'NASWP_VISITORS_LAST_UPDATE' ) ) {
	define( 'NASWP_VISITORS_LAST_UPDATE', 'naswp_visitors_last_update' );
}
if ( !defined( 'NASWP_VISITORS_COLUMNS' ) ) {
	define( 'NASWP_VISITORS_COLUMNS', [
		NASWP_VISITORS_TOTAL,
		NASWP_VISITORS_DAILY,
		NASWP_VISITORS_MONTHLY,
		NASWP_VISITORS_YEARLY
	] );
}

// AJAX-related config
if ( !defined( 'NASWP_VISITORS_URL' ) ) {
	define( 'NASWP_VISITORS_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'NASWP_VISITORS_NONCE' ) ) {
	define( 'NASWP_VISITORS_NONCE', 'naswp_visitors' );
}

// Visitor counter models
require_once( __DIR__ . '/includes/class-naswp-visitors-base.php' );
require_once( __DIR__ . '/includes/class-naswp-visitors-post.php' );
require_once( __DIR__ . '/includes/class-naswp-visitors-term.php' );

// WP query tweaks
require_once( __DIR__ . '/includes/class-naswp-visitors-query.php' );
NasWP_Visitors_Query::hook();

// Views in admin tables
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-table.php' );
add_action( 'admin_init', ['NasWP_Visitors_Admin_Table', 'hook'] );

// Admin meta boxes to show and edit views
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-metabox.php' );
if ( is_admin() ) NasWP_Visitors_Admin_MetaBox::hook();

// Initialize tracking on front-end
require_once( __DIR__ . '/public/tracking.php' );