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

		add_filter( 'posts_join_paged', [ $self, 'join_posts_query' ], 10, 2 );
		add_filter( 'posts_orderby', [ $self, 'orderby_posts_query' ], 99, 2 );

		add_filter( 'terms_clauses', [ $self, 'join_terms_query' ], 10, 3 );
		add_filter( 'get_terms_orderby', [ $self, 'orderby_terms_query' ], 99, 2 );
	}

	/**
	 * Join last update and visitors post meta.
	 * @param string $sqlJoin
	 * @param WP_Query $query
	 * @return string
	 */
	public function join_posts_query( string $sqlJoin, WP_Query $query ): string
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

		// $this->showPostSql();
		return $sqlJoin;
	}

	/**
	 * Change ORDER BY SQL of posts query.
	 * 	BEWARE: Works only with simple order by one of visitors columns. Orderby arrays aren't supported.
	 * @param string $sqlOrderby
	 * @param WP_Query $query
	 * @return string
	 */
	public function orderby_posts_query( string $sqlOrderby, WP_Query $query ): string
	{
		$orderby = $query->get( 'orderby' );
		if ( !in_array( $orderby, NASWP_VISITORS_COLUMNS ) ) return $sqlOrderby;
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

	public function join_terms_query( array $clauses, array $taxonomies, array $args ): array
	{
		$sqlJoin = ( isset( $clauses['join'] ) ) ? $clauses['join'] : '';

		global $wpdb;
		$terms = 't';
		$termmeta = $wpdb->termmeta;
		$visitKey = $args['orderby'];
		$updateKey = NASWP_VISITORS_LAST_UPDATE;

		$sqlJoin .= PHP_EOL . "LEFT JOIN `$termmeta` AS `$this->visitAlias` ON `$this->visitAlias`.`term_id` = `$terms`.`term_id` AND `$this->visitAlias`.`meta_key` = '$visitKey'";
		if ( self::getLastUpdateLimit( $visitKey ) ) {
			$sqlJoin .= PHP_EOL . "LEFT JOIN `$termmeta` AS `$this->updateAlias` ON `$this->updateAlias`.`term_id` = `$terms`.`term_id` AND `$this->updateAlias`.`meta_key` = '$updateKey'";
		}

		$clauses['join'] = $sqlJoin;

		// $this->showTermsSql();
		return $clauses;
	}

	/**
	 * Change ORDER BY SQL of terms query.
	 * 	BEWARE: Works only with simple order by one of visitors columns. Orderby arrays aren't supported.
	 * @param string $sqlOrderby
	 * @param WP_Query $query
	 * @return string
	 */
	public function orderby_terms_query( string $oldOrderby, array $args ): string
	{
		$orderby = isset( $args['orderby'] ) ? $args['orderby'] : '';
		if ( !in_array( $orderby, NASWP_VISITORS_COLUMNS ) ) return $orderby;

		$updateLimit = static::getLastUpdateLimit($orderby);
		$visitCol = "CAST(`$this->visitAlias`.`meta_value` AS SIGNED)";
		if ( $updateLimit ) {
			$lastUpdateCol = "CAST(`$this->updateAlias`.`meta_value` AS SIGNED)";
			$newOrderby = "IF($lastUpdateCol >= $updateLimit, $visitCol, 0)";
		} else {
			$newOrderby = "$visitCol";
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
	 * Helper function to output posts SQL query to debug.
	 */
	private function showPostSql(): void
	{
		add_filter( 'posts_request', function( string $sql ) {
			echo '<pre>';
			print_r( $sql );
			exit();
		} );
	}

	/**
	 * Helper function to output terms SQL query to debug.
	 */
	private function showTermsSql(): void
	{
		add_filter( 'terms_pre_query', function( $terms, $query ) {
			echo '<pre>';
			print_r( $query->request );
			exit();
		}, 10, 2 );
	}
}
