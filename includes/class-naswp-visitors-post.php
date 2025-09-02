<?php

/**
 * API for post visitor counters
 */
final class NasWP_Visitors_Post extends NasWP_Visitors_Base
{
	/**
	 * Get sum of all totals from all posts of given (or all) post types.
	 * @param string|array<string>|null $cpts One or more CPT or null for all registered CPTs.
	 * @return int
	 */
	public static function get_total_sum( $cpts = null ): int
	{
		if ( $cpts === null ) {
			$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		} else if ( is_string( $cpts ) ) {
			$cpts = [ $cpts ];
		}

		$cptsQuoted = array_map( fn( string $cpt ) => "'" . $cpt . "'", $cpts );
		$cptsIn = implode( ', ', $cptsQuoted );

		global $wpdb;
		$key = NASWP_VISITORS_TOTAL;
		$posts = $wpdb->posts;
		$postmeta = $wpdb->postmeta;

		$sqlLines = [
			"SELECT SUM(`m`.`meta_value`) AS `sum` FROM `$postmeta` AS `m`",
			"INNER JOIN `$posts` AS `p` ON `p`.`ID` = `m`.`post_id`",
			"WHERE `m`.`meta_key` = '$key' AND `p`.`post_type` IN ($cptsIn)"
		];
		$sql = implode( PHP_EOL, $sqlLines );

		$result = $wpdb->get_col( $sql );
		return ( $result ) ? intval( reset($result) ) : 0;
	}

	/**
	 * Get sum of yearly totals from all posts of given (or all) post types.
	 *	Count only yearly totals, which are not expired (their last update is in this year).
	 * @param string|array<string>|null $cpts One or more CPT or null for all registered CPTs.
	 * @return int
	 */
	public static function get_yearly_sum( $cpts = null ): int
	{
		return static::get_interval_sum( $cpts, NASWP_VISITORS_YEARLY, strtotime( '-12 months' ) );
	}

	/**
	 * Get sum of monthly totals from all posts of given (or all) post types.
	 *	Count only monthly totals, which are not expired (their last update is in this month).
	 * @param string|array<string>|null $cpts One or more CPT or null for all registered CPTs.
	 * @return int
	 */
	public static function get_monthly_sum( $cpts = null ): int
	{
		return static::get_interval_sum( $cpts, NASWP_VISITORS_MONTHLY, strtotime( '-30 days' ) );
	}

	/**
	 * Get sum of daily totals from all posts of given (or all) post types.
	 *	Count only daily totals, which are not expired (their last update is in this day).
	 * @param string|array<string>|null $cpts One or more CPT or null for all registered CPTs.
	 * @return int
	 */
	public static function get_daily_sum( $cpts = null ): int
	{
		return static::get_interval_sum( $cpts, NASWP_VISITORS_DAILY, strtotime( '-24 hours' ) );
	}

	/**
	 * Get sum of meta values of given key for all posts of given (or all) post types.
	 *	Count only meta values, which are not expired (their last update is in given time interval).
	 * @param string|array<string>|null $cpts One or more CPT or null for all registered CPTs.
	 * @param string $metaKey NASWP_VISITORS_YEARLY | NASWP_VISITORS_MONTHLY | NASWP_VISITORS_DAILY | NASWP_VISITORS_TOTAL
	 * @param int $timeFrom Timestamp of time from.
	 * @return int
	 */
	protected static function get_interval_sum( $cpts = null, string $metaKey, int $timeFrom ): int
	{
		if ( $cpts === null ) {
			$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		} else if ( is_string( $cpts ) ) {
			$cpts = [ $cpts ];
		}

		$cptsQuoted = array_map( fn( string $cpt ) => "'" . $cpt . "'", $cpts );
		$cptsIn = implode( ', ', $cptsQuoted );

		global $wpdb;
		$posts = $wpdb->posts;
		$postmeta = $wpdb->postmeta;
		$updateMeta = NASWP_VISITORS_LAST_UPDATE;

		$sqlLines = [
			"SELECT SUM(`m`.`meta_value`) AS `sum` FROM `$postmeta` AS `m`",
			"INNER JOIN `$posts` AS `p` ON `p`.`ID` = `m`.`post_id`",
			"INNER JOIN `$postmeta` AS `lu` ON `p`.`ID` = `lu`.`post_id` AND `lu`.`meta_key` = '$updateMeta'",
			"WHERE `m`.`meta_key` = '$metaKey' AND `p`.`post_type` IN ($cptsIn) AND `lu`.`meta_value` >= $timeFrom"
		];
		$sql = implode( PHP_EOL, $sqlLines );

		$result = $wpdb->get_col( $sql );
		return ( $result ) ? intval( reset($result) ) : 0;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_meta( string $key, $default = null )
	{
		$value = get_post_meta( $this->id, $key, true );
		return ($value === '') ? $default : $value;
	}

	/**
	 * @inheritDoc
	 */
	protected function update_meta( string $key, $value ): void
	{
		update_post_meta( $this->id, $key, $value );
	}
}