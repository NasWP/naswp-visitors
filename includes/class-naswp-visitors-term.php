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