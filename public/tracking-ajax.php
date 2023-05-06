<?php
/**
 * AJAX action to measure unique visit hits.
 */
function naswp_track_user() {
	// Required query parameters missing
	if ( !isset( $_GET['nonce'] ) || !isset( $_GET['ID'] ) || !isset( $_GET['type'] ) ) {
		exit( 'Forbidden' );
	}

	// Set last modified timestamp to constant
	$lastMod = 1600000000;
	$ifModifiedSince = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
	header( "Last-Modified: " . gmdate( "D, d M Y H:i:s \G\M\T", $lastMod ) );
	header( "Cache-Control: public" );

	// The script has been loaded with the same modified date - do not track
	if ( $ifModifiedSince && strtotime( $ifModifiedSince ) === $lastMod ) {
		header( "HTTP/1.1 304 Not Modified" );
		exit();
	}

	// Check nonce
	$verified = isset( $_GET['nonce'] ) && $_GET['nonce'] && wp_verify_nonce( $_GET['nonce'], NASWP_VISITORS_NONCE );
	if ( !$verified ) wp_send_json_error( 'Error: Nonce invalid.' );

	// Get post or term ID
	$objId = ( isset( $_GET['ID'] ) && is_numeric( $_GET['ID'] ) ) ? intval( $_GET['ID'] ) : null;
	if ( !$objId ) wp_send_json_error( 'Error: Object ID invalid.' );

	// Get entity type
	$objType = ( isset( $_GET['type'] ) && in_array( $_GET['type'], [ 'post', 'tax' ] ) ) ? trim( $_GET['type'] ) : null;
	if ( !$objType ) wp_send_json_error( 'Error: Object type invalid.' );

	// Track visit of post or term
	$ref = null;
	$model = ( $objType === 'post' ) ? new NasWP_Visitors_Post( $objId, $ref ) : new NasWP_Visitors_Term( $objId, $ref );
	$model->track_visit();

	// BEWARE: DO NOT USE wp_send_json_success(), it will ruin our headers
	// wp_send_json_success( 'Visitors incremented.' );
	exit();
};
add_action( 'wp_ajax_naswp_track_user', 'naswp_track_user' );
add_action( 'wp_ajax_nopriv_naswp_track_user', 'naswp_track_user' );
