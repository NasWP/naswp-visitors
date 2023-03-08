<?php
/**
 * Tests
 *
 * To run these tests:
 * 1. Install plugin to any local WordPress instance
 * 2. Create some post with enabled views tracking, note it's ID
 * 3. Set post ID to constant below
 * 4. Run this file in CLI:
 * 		php test/index.php
 */


define('NASWP_TEST_POST_ID', 1);

// Wordpress
require_once( '/Users/fronty/www/dootheme/wp-load.php' ); // Use absolute path for softlinked plugin
// require_once( __DIR__ . '/../../../../wp-load.php' );

// Functions
require_once( __DIR__ . '/includes.php' );

// Tests
require_once( __DIR__ . '/model.php' );