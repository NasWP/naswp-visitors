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
		return ( $result ) ? reset($result) : 0;
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