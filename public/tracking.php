<?php
/**
 * Enqueue tracking JS if not logged in and visiting matching CPT
 */
add_action( 'wp', function() {
	if ( is_admin() ) return;

	// Check CPT and taxonomy
	$obj = get_queried_object();
	$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
	$taxonomies = apply_filters( 'naswp_visitors_tax', NASWP_VISITORS_TAX_DEFAULT );
	if (
		!( $obj instanceof WP_Post && in_array( $obj->post_type, $cpts ) ) &&
		!( $obj instanceof WP_Term && in_array( $obj->taxonomy, $taxonomies) )
	) {
		return;
	}

	// Check user
	if ( is_user_logged_in() ) {
		$roles = apply_filters( 'naswp_visitors_roles', NASWP_VISITORS_USE_WITH_ROLES_DEFAULT );
		$user = wp_get_current_user();
		if ( empty( array_intersect( (array)$user->roles, $roles ) ) ) return;
	}

	// Init AJAX counter
	wp_enqueue_script( 'naswp_track_visitors', plugin_dir_url(__FILE__) . 'js/visitors.js', [], false, true );
	wp_localize_script( 'naswp_track_visitors', 'NASWP_VISITORS', [
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( NASWP_VISITORS_NONCE ),
		'id' => ($obj instanceof WP_Post) ? $obj->ID : $obj->term_id,
		'type' => ($obj instanceof WP_Post) ? 'post' : 'tax'
	] );


	// $model = new NasWP_Visitors_Post($obj->ID);
	// echo '<pre>';
	// print_r($model->get_data());
	// exit();
} );
