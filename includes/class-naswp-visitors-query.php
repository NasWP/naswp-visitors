<?php

/**
 * WP query tweaks
 */
class NasWP_Visitors_Query
{

	/** @var string */
	private $visitAlias = '_visit_count';

	/** @var string */
	private $updateAlias = '_visit_last_update';

	/**
	 * Hook to WP query.
	 */
	public static function hook()
	{
		$self = new self();
		// add_action( 'pre_get_posts', [ $self, 'order_query' ] );

		add_filter( 'posts_join_paged', [ $self, 'join_query' ], 10, 2 );
		add_filter( 'posts_orderby', [ $self, 'orderby_query' ], 10, 2 );
	}

	/**
	 * Join last update and visitors postmeta.
	 * @param string $sqlJoin
	 * @param WP_Query $query
	 * @return string
	 */
	public function join_query( string $sqlJoin, WP_Query $query ): string
	{
		$orderby = $query->get( 'orderby' );
		if ( !in_array( $orderby, NASWP_VISITORS_COLUMNS ) ) return $sqlJoin;

		global $wpdb;
		$posts = $wpdb->posts;
		$postmeta = $wpdb->postmeta;
		$visitKey = $orderby;
		$updateKey = NASWP_VISITORS_LAST_UPDATE;

		$sqlJoin .= PHP_EOL . "LEFT JOIN `$postmeta` AS `$this->visitAlias` ON `$this->visitAlias`.`post_id` = `$posts`.`ID` AND `$this->visitAlias`.`meta_key` = '$visitKey'";

		if ( self::getLastUpdateLimit( $orderby ) ) {
			$sqlJoin .= PHP_EOL . "LEFT JOIN `$postmeta` AS `$this->updateAlias` ON `$this->updateAlias`.`post_id` = `$posts`.`ID` AND `$this->updateAlias`.`meta_key` = '$updateKey'";
		}

		return $sqlJoin;
	}

	/**
	 * Change ORDER BY SQL.
	 * 	BEWARE: Works only with simple order by one of visitors columns. Orderby arrays aren't supported.
	 * @param string $sqlOrderby
	 * @param WP_Query $query
	 * @return string
	 */
	public function orderby_query( string $sqlOrderby, WP_Query $query ): string
	{
		$orderby = $query->get( 'orderby' );
		if ( !in_array( $orderby, NASWP_VISITORS_COLUMNS ) ) return $orderby;
		$order = $query->get( 'order' );

		$updateLimit = static::getLastUpdateLimit($orderby);

		$visitCol = "CAST(`$this->visitAlias`.`meta_value` AS SIGNED)";
		if ( $updateLimit ) {
			$lastUpdateCol = "CAST(`$this->updateAlias`.`meta_value` AS SIGNED)";
			$newOrderby = "IF($lastUpdateCol >= $updateLimit, $visitCol, 0) $order";
		} else {
			$newOrderby = "$visitCol $order";
		}

		return $newOrderby;
	}

	/**
	 * Create limit of last update timestamp for given visit counter type.
	 *	Example:
	 * 		For NASWP_VISITORS_MONTHLY the limit is last 30 days.
	 * 		For NASWP_VISITORS_DAILY the limit is last 24 hours.
	 * @param string $forColumn
	 * @return int|NULL
	 */
	public static function getLastUpdateLimit(string $forColumn, int $now = null): ?int
	{
		if ( $now !== null ) $now = time();
		switch ( $forColumn ) {
			case NASWP_VISITORS_DAILY:
				return strtotime( '-24 hours', $now );
			case NASWP_VISITORS_MONTHLY:
				return strtotime( '-30 days', $now );
			case NASWP_VISITORS_YEARLY:
				return strtotime( '-12 months', $now );
			default:
				return null;
		}
	}

	/**
	 * Helper function to output SQL query to debug.
	 */
	private function showSql()
	{
		add_filter( 'posts_request', function( $sql ) {
			echo '<pre>';
			print_r( $sql );
			exit();
		} );
	}
}