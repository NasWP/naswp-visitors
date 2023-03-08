<?php

// Enqueue tracking JS if not logged in and visiting matching CPT
add_action( 'wp', function() {
	if ( is_admin() ) return;

	// Check CPT
	$obj = get_queried_object();
	$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
	if ( !$obj instanceof WP_Post || !in_array( $obj->post_type, $cpts ) ) return;

	// Check user
	if ( is_user_logged_in() ) {
		$roles = apply_filters( 'naswp_visitors_roles', NASWP_VISITORS_USE_WITH_ROLES_DEFAULT );
		$user = wp_get_current_user();
		if ( empty( array_intersect( (array)$user->roles, $roles ) ) ) return;
	}

	// Init AJAX counter
	wp_enqueue_script( 'naswp_track_visitors', NASWP_VISITORS_URL . '/public/js/visitors.js', [], false, true );
	wp_localize_script( 'naswp_track_visitors', 'NASWP_VISITORS', [
		'ajaxurl' => NASWP_VISITORS_URL . '/public/tracking-ajax.php',
		// 'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'path' => ABSPATH,
		'nonce' => wp_create_nonce( NASWP_VISITORS_NONCE ),
		'id' => $obj->ID
	] );


	// $model = new NasWP_Visitors_Post($obj->ID);
	// echo '<pre>';
	// print_r($model->get_data());
	// exit();
} );