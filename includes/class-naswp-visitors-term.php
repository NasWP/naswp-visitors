<?php

/**
 * API for term visitor counters
 */
final class NasWP_Visitors_Term extends NasWP_Visitors_Base
{
	/**
	 * Get sum of all totals from all terms of given (or all) taxonomies.
	 * @param string|array<string>|null $taxes One or more taxonomies or null for all registered taxonomies.
	 * @return int
	 */
	public static function get_total_sum( $taxes = null ): int
	{
		if ( $taxes === null ) {
			$taxes = apply_filters( 'naswp_visitors_tax', NASWP_VISITORS_TAX_DEFAULT );
		} else if ( is_string( $taxes ) ) {
			$taxes = [ $taxes ];
		}

		$taxesQuoted = array_map( fn( string $tax ) => "'" . $tax . "'", $taxes );
		$taxesIn = implode( ', ', $taxesQuoted );

		global $wpdb;
		$key = NASWP_VISITORS_TOTAL;
		$termmeta = $wpdb->termmeta;
		$termtax = $wpdb->term_taxonomy;

		$sqlLines = [
			"SELECT SUM(`m`.`meta_value`) AS `sum` FROM `$termmeta` AS `m`",
			"INNER JOIN `$termtax` AS `tt` ON `tt`.`term_id` = `m`.`term_id`",
			"WHERE `m`.`meta_key` = '$key' AND `tt`.`taxonomy` IN ($taxesIn)"
		];
		$sql = implode( PHP_EOL, $sqlLines );

		$result = $wpdb->get_col( $sql );
		return ( $result ) ? reset($result) : 0;
	}

	/**
	 * Get sum of yearly totals from all terms of given (or all) taxonomies.
	 *	Count only yearly totals, which are not expired (their last update is in this year).
	 * @param string|array<string>|null $taxes One or more taxonomies or null for all registered taxonomies.
	 * @return int
	 */
	public static function get_yearly_sum( $taxes = null ): int
	{
		return static::get_interval_sum( $taxes, NASWP_VISITORS_YEARLY, strtotime( '-12 months' ) );
	}

	/**
	 * Get sum of monthly totals from all terms of given (or all) taxonomies.
	 *	Count only monthly totals, which are not expired (their last update is in this month).
	 * @param string|array<string>|null $taxes One or more taxonomies or null for all registered taxonomies.
	 * @return int
	 */
	public static function get_monthly_sum( $taxes = null ): int
	{
		return static::get_interval_sum( $taxes, NASWP_VISITORS_MONTHLY, strtotime( '-30 days' ) );
	}

	/**
	 * Get sum of daily totals from all terms of given (or all) taxonomies.
	 *	Count only daily totals, which are not expired (their last update is in this day).
	 * @param string|array<string>|null $taxes One or more taxonomies or null for all registered taxonomies.
	 * @return int
	 */
	public static function get_daily_sum( $taxes = null ): int
	{
		return static::get_interval_sum( $taxes, NASWP_VISITORS_DAILY, strtotime( '-24 hours' ) );
	}

	/**
	 * Get sum of meta values of given key for all terms of given (or all) taxonomies.
	 *	Count only meta values, which are not expired (their last update is in given time interval).
	 * @param string|array<string>|null $taxes One or more taxonomies or null for all registered taxonomies.
	 * @param string $metaKey NASWP_VISITORS_YEARLY | NASWP_VISITORS_MONTHLY | NASWP_VISITORS_DAILY | NASWP_VISITORS_TOTAL
	 * @param int $timeFrom Timestamp of time from.
	 * @return int
	 */
	protected static function get_interval_sum( $taxes = null, string $metaKey, int $timeFrom ): int
	{
		if ( $taxes === null ) {
			$taxes = apply_filters( 'naswp_visitors_tax', NASWP_VISITORS_TAX_DEFAULT );
		} else if ( is_string( $taxes ) ) {
			$taxes = [ $taxes ];
		}

		$taxesQuoted = array_map( fn( string $tax ) => "'" . $tax . "'", $taxes );
		$taxesIn = implode( ', ', $taxesQuoted );

		global $wpdb;
		$termmeta = $wpdb->termmeta;
		$termtax = $wpdb->term_taxonomy;
		$updateMeta = NASWP_VISITORS_LAST_UPDATE;

		$sqlLines = [
			"SELECT SUM(`m`.`meta_value`) AS `sum` FROM `$termmeta` AS `m`",
			"INNER JOIN `$termtax` AS `tt` ON `tt`.`term_id` = `m`.`term_id`",
			"INNER JOIN `$termmeta` AS `lu` ON `tt`.`term_id` = `lu`.`term_id` AND `lu`.`meta_key` = '$updateMeta'",
			"WHERE `m`.`meta_key` = '$metaKey' AND `tt`.`taxonomy` IN ($taxesIn) AND `lu`.`meta_value` >= $timeFrom"
		];
		$sql = implode( PHP_EOL, $sqlLines );

		$result = $wpdb->get_col( $sql );
		return ( $result ) ? reset($result) : 0;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_meta( string $key, $default = null )
	{
		$value = get_term_meta( $this->id, $key, true );
		return ($value === '') ? $default : $value;
	}

	/**
	 * @inheritDoc
	 */
	protected function update_meta( string $key, $value ): void
	{
		update_term_meta( $this->id, $key, $value );
	}
}