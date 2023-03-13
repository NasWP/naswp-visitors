<?php

/**
 * Add daily, monthly, yearly and total visits columns to admin tables.
 */
class NasWP_Visitors_Admin_Table
{

	/**
	 * Hook to admin tables of defined post types.
	 */
	public static function hook()
	{
		$self = new self();
		$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		foreach ( $cpts as $cpt ) {
			add_filter( 'manage_' . $cpt . '_posts_columns', [ $self, 'visitorsColumns' ] );
			add_action( 'manage_' . $cpt . '_posts_custom_column', [ $self, 'populateColumns' ], 10, 2 );
			add_filter( 'manage_edit-' . $cpt . '_sortable_columns', [ $self, 'sortableColumns' ] );
		}

		// add_action( 'pre_get_posts', [ $self, 'orderAdminQuery' ] );
	}

	/**
	 * Add columns for all time intervals.
	 * @param array|string[] $columns
	 * @return array|string[]
	 * @internal
	 */
	public function visitorsColumns( array $columns ): array
	{
		$columns[NASWP_VISITORS_TOTAL] = __( 'Total views', 'visitors-naswp' );
		$columns[NASWP_VISITORS_DAILY] = __( 'Daily views', 'visitors-naswp' );
		$columns[NASWP_VISITORS_MONTHLY] = __( 'Monthly views', 'visitors-naswp' );
		$columns[NASWP_VISITORS_YEARLY] = __( 'Yearly views', 'visitors-naswp' );

		return $columns;
	}

	/**
	 * Print number of views in given $column for given $postId
	 * @param string $column
	 * @param int $postId
	 * @internal
	 */
	public function populateColumns( string $column, int $postId )
	{
		if ( in_array( $column, NASWP_VISITORS_COLUMNS ) ) {
			$visitors = new NasWP_Visitors_Post($postId);
			switch ( $column ) {
				case NASWP_VISITORS_DAILY:
					$count = $visitors->get_daily();
					break;
				case NASWP_VISITORS_MONTHLY:
					$count = $visitors->get_monthly();
					break;
				case NASWP_VISITORS_YEARLY:
					$count = $visitors->get_yearly();
					break;
				case NASWP_VISITORS_TOTAL:
					$count = $visitors->get_total();
					break;
			}
			echo esc_html( intval( $count ) . 'x' );
		}
	}

	/**
	 * Add all views columns as sortable
	 * @param array|string[] $columns
	 * @return array|string[]
	 * @internal
	 */
	public function sortableColumns( array $columns ): array
	{
		$sortable = array_combine( NASWP_VISITORS_COLUMNS, NASWP_VISITORS_COLUMNS );
		$columns = array_merge( $columns, $sortable );

		return $columns;
	}
}