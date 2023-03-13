<?php

/**
 * Term table
 */
final class NasWP_Visitors_Admin_Table_Term extends NasWP_Visitors_Admin_Table
{

	/**
	 * @inheritDoc
	 */
	protected function getModel(int $objectId): NasWP_Visitors_Base
	{
		return new NasWP_Visitors_Term($objectId);
	}

	/**
	 * @inheritDoc
	 */
	public static function hook(): void
	{
		$self = new static();
		$taxonomies = apply_filters( 'naswp_visitors_tax', NASWP_VISITORS_TAX_DEFAULT );
		foreach ( $taxonomies as $tax ) {
			add_filter( 'manage_edit-' . $tax . '_columns', [ $self, 'visitorsColumns' ] );
			add_action( 'manage_' . $tax . '_custom_column',
				fn( string $string, string $column, int $termId ) => $self->populateColumns($column, $termId),
				10, 3
			);
			add_filter( 'manage_edit-' . $tax . '_sortable_columns', [ $self, 'sortableColumns' ] );

			// add_action( 'manage_edit-' . $tax . '_columns', function( array $columns ) {
			// 	echo '<pre>';
			// 	print_r($columns);
			// 	exit();
			// });
		}
	}
}