<?php
/**
 * Plugin Name: Visitors
 * Plugin URI: https://github.com/NasWP/naswp-visitors
 * Description: Plugin for tracking site traffic without Cookies, localStorage or sessionStorage.
 * Version: 1.1.0
 * Author: NášWP.cz
 * Author URI: https://naswp.cz/
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: naswp-visitors
 * Domain Path: /languages
 */

// No direct access
if ( !defined( 'WPINC' ) ) {
	die;
}

// GitHub updater inspired by https://github.com/rayman813/smashing-updater-plugin
if ( !class_exists( 'Smashing_Updater' ) ){
	include_once( plugin_dir_path( __FILE__ ) . 'admin/class-sm-github-updater.php' );
}

$updater = new Smashing_Updater( __FILE__ );
$updater->set_username( 'NasWP' );
$updater->set_repository( 'naswp-visitors' );
$updater->initialize();

// Adds textdomain for translations.
add_action( 'admin_init', 'naswp_visitors_textdomain' );

function naswp_visitors_textdomain() {
	load_plugin_textdomain( 'naswp-visitors', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// Constants shared with the standalone tracking endpoint (public/track.php)
require_once( __DIR__ . '/includes/constants.php' );

// AJAX-related config
if ( !defined( 'NASWP_VISITORS_URL' ) ) {
	define( 'NASWP_VISITORS_URL', plugin_dir_url( __FILE__ ) );
}

// Visitor counter models
require_once( __DIR__ . '/includes/class-naswp-visitors-token.php' );
require_once( __DIR__ . '/includes/class-naswp-visitors-base.php' );
require_once( __DIR__ . '/includes/class-naswp-visitors-post.php' );
require_once( __DIR__ . '/includes/class-naswp-visitors-term.php' );

// WP query tweaks
require_once( __DIR__ . '/includes/class-naswp-visitors-query.php' );
NasWP_Visitors_Query::hook();

// Visits in admin tables
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-table.php' );
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-table-post.php' );
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-table-term.php' );
add_action( 'admin_init', ['NasWP_Visitors_Admin_Table_Post', 'hook'] );
add_action( 'admin_init', ['NasWP_Visitors_Admin_Table_Term', 'hook'] );

// Admin widgets
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-widget.php' );
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-widget-summary.php' );
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-widget-total.php' );
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-widget-cpt.php' );
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-widget-tax.php' );
add_action( 'admin_init', fn() => NasWP_Visitors_Admin_Widget_Summary::hook() );
add_action( 'admin_init', fn() => NasWP_Visitors_Admin_Widget_Total::hook() );
add_action( 'admin_init', fn() => NasWP_Visitors_Admin_Widget_Cpt::hook() );
add_action( 'admin_init', fn() => NasWP_Visitors_Admin_Widget_Tax::hook() );

// Admin meta boxes to show and edit visits
require_once( __DIR__ . '/admin/class-naswp-visitors-admin-metabox.php' );
add_action( 'admin_init', fn() => NasWP_Visitors_Admin_MetaBox::hook() );

// Initialize tracking on front-end
require_once( __DIR__ . '/public/tracking.php' );
require_once( __DIR__ . '/public/tracking-ajax.php' );
