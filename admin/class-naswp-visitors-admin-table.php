<?php

/**
 * Add daily, monthly, yearly and total visits columns to admin tables.
 */
abstract class NasWP_Visitors_Admin_Table
{

	/**
	 * Get model for given object id (either post or term)
	 * @param int $objectId
	 * @return NasWP_Visitors_Base
	 */
	abstract protected function getModel(int $objectId): NasWP_Visitors_Base;

	/**
	 * Hook to admin tables of defined post types.
	 */
	abstract public static function hook(): void;

	/**
	 * Add columns for all time intervals.
	 * @param array|string[] $columns
	 * @return array|string[]
	 * @internal
	 */
	public function visitorsColumns( array $columns ): array
	{
		$columns[NASWP_VISITORS_TOTAL] = __( 'Total visits', 'naswp-visitors' );
		$columns[NASWP_VISITORS_DAILY] = __( 'In 24 hours', 'naswp-visitors' );
		$columns[NASWP_VISITORS_MONTHLY] = __( 'In 30 days', 'naswp-visitors' );
		$columns[NASWP_VISITORS_YEARLY] = __( 'In 12 months', 'naswp-visitors' );

		return $columns;
	}

	/**
	 * Print number of visits in given $column for given $objectId
	 * @param string $column
	 * @param int $objectId
	 * @internal
	 */
	public function populateColumns( string $column, int $objectId )
	{
		if ( in_array( $column, NASWP_VISITORS_COLUMNS ) ) {
			$visitors = $this->getModel($objectId);
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
	 * Add all visits columns as sortable
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