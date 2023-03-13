<?php

/**
 * Posts table
 */
final class NasWP_Visitors_Admin_Table_Post extends NasWP_Visitors_Admin_Table
{

	/**
	 * @inheritDoc
	 */
	protected function getModel(int $objectId): NasWP_Visitors_Base
	{
		return new NasWP_Visitors_Post($objectId);
	}

	/**
	 * @inheritDoc
	 */
	public static function hook(): void
	{
		$self = new static();
		$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		foreach ( $cpts as $cpt ) {
			add_filter( 'manage_' . $cpt . '_posts_columns', [ $self, 'visitorsColumns' ] );
			add_action( 'manage_' . $cpt . '_posts_custom_column', [ $self, 'populateColumns' ], 10, 2 );
			add_filter( 'manage_edit-' . $cpt . '_sortable_columns', [ $self, 'sortableColumns' ] );
		}
	}
}