<?php
	/*
		Plugin Name: Tester
		Plugin URI:  https://www.example.com/
		Description: Tester plugin.
		Version:     1.0.0
		Author:      Adam Laita
		Author URI:  https://www.example.com/
		License:     GPL3
		License URI: https://www.gnu.org/licenses/gpl-3.0.html
		Text Domain: klen
	*/

	if( ! class_exists( 'Smashing_Updater' ) ){
		include_once( plugin_dir_path( __FILE__ ) . 'updater.php' );
	}
	
	$updater = new Smashing_Updater( __FILE__ );
	$updater->set_username( 'adam-laita' );
	$updater->set_repository( 'visitors-naswp' );
	// $updater->authorize( 'abcdefghijk1234567890' );
	$updater->initialize();
