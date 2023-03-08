<?php
/**
 * Measure unique visit hits.
 */


if ( !isset( $_GET['nonce'] ) || !isset( $_GET['ID'] ) || !isset( $_GET['path'] ) ) {
	exit( 'Forbidden' );
}

// $id = random_int(100000000, 999999999);
$lastMod = 1600000000;
$ifModifiedSince= isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;

header( "Last-Modified: " . gmdate( "D, d M Y H:i:s \G\M\T", $lastMod ) );
header( "Cache-Control: public" );

if ( $ifModifiedSince && strtotime( $ifModifiedSince ) === $lastMod ) {
	header( "HTTP/1.1 304 Not Modified" );
	exit();
}

// Verify cached version - $id is the same after 2nd reload
// echo $id;

require_once( $_GET['path'] . 'wp-load.php' );

$verified = isset( $_GET['nonce'] ) && $_GET['nonce'] && wp_verify_nonce( $_GET['nonce'], NASWP_VISITORS_NONCE );
if ( !$verified ) wp_send_json_error( 'Error: Nonce invalid.' );

$postId = ( isset( $_GET['ID'] ) && is_numeric( $_GET['ID'] ) ) ? $_GET['ID'] : null;
if ( !$postId ) wp_send_json_error( 'Error: Post ID invalid.' );


// $ref = strtotime('19.11.2021');
$ref = null;
$model = new NasWP_Visitors_Post($postId, $ref);
$model->track_visit();


wp_send_json_success( 'Visitors incremented.' );
