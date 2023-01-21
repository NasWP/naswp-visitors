<?php
	/*
		Plugin Name: Tester
		Plugin URI:  https://www.example.com/
		Description: Tester plugin.
		Version:     1.0.1
		Author:      Adam Laita
		Author URI:  https://www.example.com/
		License:     GPL3
		License URI: https://www.gnu.org/licenses/gpl-3.0.html
		Text Domain: klen
	*/

	add_action( 'init', 'github_plugin_updater_test_init' );
	
	function github_plugin_updater_test_init() {
	
		include_once( plugin_dir_path( __FILE__ ) . 'updater.php' );
	
		define( 'WP_GITHUB_FORCE_UPDATE', true );
	
		if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin
	
			$config = array(
				'slug' => plugin_basename( __FILE__ ),
				'proper_folder_name' => 'visitors-naswp',
				'api_url' => 'https://api.github.com/repos/adam-laita/visitors-naswp',
				'raw_url' => 'https://raw.github.com/adam-laita/visitors-naswp/master',
				'github_url' => 'https://github.com/adam-laita/visitors-naswp',
				'zip_url' => 'https://github.com/adam-laita/visitors-naswp/archive/master.zip',
				'sslverify' => true,
				'requires' => '3.0',
				'tested' => '3.3',
				'readme' => 'README.md',
				'access_token' => '',
			);
	
			new WP_GitHub_Updater( $config );
	
		}
	
	}
