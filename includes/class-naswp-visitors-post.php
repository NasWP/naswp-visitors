<?php

/**
 * API for visitor counters
 */
class NasWP_Visitors_Post
{

	/** @var int Post id */
	private int $id;

	/** @var int Reference timestamp */
	private int $now;

	/**
	 * Instantiate for post of given ID and given reference timestamp.
	 * @param int $id
	 * @param int|null $now For testing purposes. Current timestamp if null given.
	 */
	public function __construct( int $id, int $now = null )
	{
		if ( $now === null ) $now = intval( date_i18n( 'U' ) );
		$this->id = $id;
		$this->now = $now;
	}


	/* --- VIEWS SETTERS --- */

	public function track_visit()
	{
		$data = $this->get_data();
		$isSameYear = $this->is_same_year();
		$isSameMonth = $this->is_same_month();
		$isSameDay = $this->is_same_day();

		// Yearly views
		$year = $this->format( 'Y' );
		if ( !$isSameYear || !isset( $data['yearly'][$year] ) ) $yearly = 1;
		else $yearly = $data['yearly'][$year]['views'] + 1;
		$data['yearly'][$year] = $this->create_view( $yearly );

		// Monthly views
		$month = $this->format( 'n' );
		$isSameMonth = $this->is_same_month();
		$monthly = ( $isSameMonth ? $data['monthly'][$month]['views'] : 0 ) + 1;
		$data['monthly'][$month] = $this->create_view( $monthly );

		// Daily views
		$day = $this->format( 'j' );
		$daily = ( $isSameDay ? $data['daily'][$day]['views'] : 0 ) + 1;
		$data['daily'][$day] = $this->create_view( $daily );


		// Increase floating year count
		if ( $isSameMonth ) $this->increase_meta( NASWP_VISITORS_YEARLY );
		else {
			$last12Months = $this->sum_monthly_views( $data['monthly'], 12 );
			$this->update_meta( NASWP_VISITORS_YEARLY, $last12Months );
		}

		// Increase floating month count
		if ( $isSameDay ) $this->increase_meta( NASWP_VISITORS_MONTHLY );
		else {
			$last30Days = $this->sum_daily_views( $data['daily'], 30 );
			$this->update_meta( NASWP_VISITORS_MONTHLY, $last30Days );
		}

		// Save daily views
		$this->update_meta( NASWP_VISITORS_DAILY, $daily );

		// Increase total count
		$this->increase_meta( NASWP_VISITORS_TOTAL );

		// Save data structure
		$this->update_meta( NASWP_VISITORS_DATA, $data );

		// Save last update
		$this->update_meta( NASWP_VISITORS_LAST_UPDATE, $this->now );
	}

	/**
	 * Update total views.
	 * @param int $views
	 */
	public function update_total( int $views )
	{
		$this->update_meta( NASWP_VISITORS_TOTAL, $views );
	}

	/**
	 * Reset all stored meta fields.
	 */
	public function reset_data()
	{
		$this->update_meta( NASWP_VISITORS_DATA, $this->create_data() );
		$this->update_meta( NASWP_VISITORS_DAILY, 0 );
		$this->update_meta( NASWP_VISITORS_MONTHLY, 0 );
		$this->update_meta( NASWP_VISITORS_YEARLY, 0 );
		$this->update_meta( NASWP_VISITORS_TOTAL, 0 );
		$this->reset_last_update();
	}

	/**
	 * Reset last update timestamp.
	 * 	Use this to ensure, that last update is allways present in db.
	 */
	public function reset_last_update()
	{
		$this->update_meta( NASWP_VISITORS_LAST_UPDATE, 0 );
	}


	/* --- VIEWS GETTERS --- */

	/**
	 * Get timestamp of last update.
	 * @return int
	 */
	public function get_last_update(): int
	{
		return $this->get_meta( NASWP_VISITORS_LAST_UPDATE, 0 );
	}

	/**
	 * Get total views count.
	 * @return int
	 */
	public function get_total(): int
	{
		return $this->get_meta( NASWP_VISITORS_TOTAL, 0 );
	}

	/**
	 * Get views for current day.
	 * @return int
	 */
	public function get_daily(): int
	{
		$updateLimit = NasWP_Visitors_Query::getLastUpdateLimit( NASWP_VISITORS_DAILY, $this->now );
		if ( $this->get_last_update() < $updateLimit ) return 0;
		return $this->get_meta( NASWP_VISITORS_DAILY, 0 );
	}

	/**
	 * Get views for last 30 days.
	 * @return int
	 */
	public function get_monthly(): int
	{
		$updateLimit = NasWP_Visitors_Query::getLastUpdateLimit( NASWP_VISITORS_MONTHLY, $this->now );
		if ( $this->get_last_update() < $updateLimit ) return 0;
		return $this->get_meta( NASWP_VISITORS_MONTHLY, 0 );
	}

	/**
	 * Get views for last 12 months.
	 * @return int
	 */
	public function get_yearly(): int
	{
		$updateLimit = NasWP_Visitors_Query::getLastUpdateLimit( NASWP_VISITORS_YEARLY, $this->now );
		if ( $this->get_last_update() < $updateLimit ) return 0;
		return $this->get_meta( NASWP_VISITORS_YEARLY, 0 );
	}

	/**
	 * Get daily views for all or last given days, sorted by last update descendigly.
	 * @param int|null $days Number of days back from reference time. Null for no limit.
	 * @param array|null $daily Get from data if null given.
	 * @return array
	 */
	public function get_daily_data( int $days = null, array $daily = null ): array
	{
		if ( $daily === null ) {
			$data = $this->get_data();
			$daily = $data['daily'];
		}

		// Filter number of days back
		if ( $days !== null ) {
			$to = $this->now;
			$from = strtotime( "-$days days", $to );
			$daily = array_filter( $daily, function( array $view ) use ( $from, $to ) {
				return $view['lastUpdate'] >= $from && $view['lastUpdate'] <= $to;
			} );
		}

		// Filter empty records
		$daily = array_filter( $daily, function( array $view ) {
			return $view['views'] > 0;
		} );

		// Sort by update time
		usort( $daily, function( array $a, array $b ) {
			return $b['lastUpdate'] <=> $a['lastUpdate'];
		} );

		return $daily;
	}

	/**
	 * Get monthly views for all or last given months, sorted by last update descendigly.
	 * @param int|null $months Number of months back from reference time. Null for no limit.
	 * @param array|null $monthly
	 * @return array
	 */
	public function get_monthly_data( int $months = null, array $monthly = null ): array
	{
		if ( $monthly === null ) {
			$data = $this->get_data();
			$monthly = $data['monthly'];
		}

		// Filter number of months back
		if ( $months !== null ) {
			$to = $this->now;
			$from = strtotime( "-$months months", $to );
			$monthly = array_filter( $monthly, function( array $view ) use ( $from, $to ) {
				return $view['lastUpdate'] >= $from && $view['lastUpdate'] <= $to;
			} );
		}

		// Filter empty records
		$monthly = array_filter( $monthly, function( array $view ) {
			return $view['views'] > 0;
		} );

		// Sort by update time
		usort( $monthly, function( array $a, array $b ) {
			return $b['lastUpdate'] <=> $a['lastUpdate'];
		} );

		return $monthly;
	}

	/**
	 * Get yearly views for all or last given years, sorted by last update descendigly.
	 * @param int|null $years Number of years back from reference time. Null for no limit.
	 * @param array|null $yearly Get from data if null given.
	 * @return array
	 */
	public function get_yearly_data( int $years = null, array $yearly = null )
	{
		if ( $yearly === null ) {
			$data = $this->get_data();
			$yearly = $data['yearly'];
		}

		// Filter number of years back
		if ( $years !== null ) {
			$to = $this->now;
			$from = strtotime( "-$years years", $to );
			$yearly = array_filter( $yearly, function( array $view ) use ( $from, $to ) {
				return $view['lastUpdate'] >= $from && $view['lastUpdate'] <= $to;
			} );
		}

		usort( $yearly, function( array $a, array $b ) {
			return $b['lastUpdate'] <=> $a['lastUpdate'];
		} );

		return $yearly;
	}

	/**
	 * Get views data of post (or empty if not created yet).
	 * @return array
	 */
	public function get_data(): array
	{
		return $this->get_meta( NASWP_VISITORS_DATA, $this->create_data() );
	}


	/** --- DATE GETTERS --- */

	/**
	 * Compare last daily updated data with reference timestamp.
	 * 	Day, month and year has to be the same.
	 * @return bool
	 */
	private function is_same_day(): bool
	{
		$data = $this->get_data();
		$lastUpdate = $data['daily'][$this->format( 'j' )]['lastUpdate'];
		return (
			$this->format( 'j' ) === $this->format( 'j', $lastUpdate ) &&
			$this->format( 'n' ) === $this->format( 'n', $lastUpdate ) &&
			$this->format( 'Y' ) === $this->format( 'Y', $lastUpdate )
		);
	}

	/**
	 * Compare last monthly updated data with reference timestamp.
	 *	Month and year has to be the same.
	 * @return bool
	 */
	private function is_same_month(): bool
	{
		$data = $this->get_data();
		$lastUpdate = $data['monthly'][$this->format( 'n' )]['lastUpdate'];
		return (
			$this->format( 'n' ) === $this->format( 'n', $lastUpdate ) &&
			$this->format( 'Y' ) === $this->format( 'Y', $lastUpdate )
		);
	}

	/**
	 * Compare last yearly updated data with reference timestamp.
	 * 	Year has to be the same.
	 * @return bool
	 */
	private function is_same_year(): bool
	{
		$data = $this->get_data();
		$year = $this->format( 'Y' );
		$lastUpdate = isset( $data['yearly'][$year] ) ? $data['yearly'][$year]['lastUpdate'] : 0;
		return $year === $this->format( 'Y', $lastUpdate );
	}


	/* --- PRIVATE HELPERS --- */

	/**
	 * Create empty structure of daily, monthly and yearly views.
	 * @return array|array[]
	 */
	private function create_data()
	{
		$view = $this->create_view( 0, 0 );
		return [
			'daily' => array_fill( 1, 31, $view ),
			'monthly' => array_fill( 1, 12, $view ),
			'yearly' => []
		];
	}

	/**
	 * Count total number of all daily views in last days.
	 * @param array $daily
	 * @param int $days
	 * @return int
	 */
	private function sum_daily_views( array $daily, int $days ): int
	{
		$daily = $this->get_daily_data( $days, $daily );
		$sum = 0;
		foreach ( $daily as $view ) {
			$sum += $view['views'];
		}
		return $sum;
	}

	/**
	 * Count total number of all monthy views in last months.
	 * @param array $monthly
	 * @param int $months
	 * @return int
	 */
	private function sum_monthly_views( array $monthly, int $months ): int
	{
		$to = $this->now;
		$from = strtotime( "-$months months", $to );
		$sum = 0;
		foreach ( $monthly as $view ) {
			if ( $view['lastUpdate'] >= $from && $view['lastUpdate'] <= $to ) $sum += $view['views'];
		}
		return $sum;
	}

	/**
	 * Format timestamp.
	 * @param string $format
	 * @param int|null $timestamp Reference timestamp if null given.
	 * @return string
	 *
	 * @see https://wordpress.org/documentation/article/customize-date-and-time-format/
	 * @see https://developer.wordpress.org/reference/functions/date_i18n/
	 */
	public function format( string $format, int $timestamp = null ): string
	{
		if ( $timestamp === null ) $timestamp = $this->now;
		return date_i18n( $format, $timestamp );
	}

	/**
	 * Create number of views with lastUpdate timestamp pair.
	 * @param int $views
	 * @param int|null $timestamp Created from referenced timestamp if null given.
	 * @return array
	 */
	private function create_view( int $views, int $timestamp = null ): array
	{
		if ($timestamp === null) $timestamp = $this->now;
		return [
			'views' => $views,
			'lastUpdate' => $timestamp
		];
	}

	/**
	 * Increase numeric meta field.
	 * @param string $key
	 */
	private function increase_meta( string $key )
	{
		$value = $this->get_meta( $key, 0 );
		$this->update_meta( $key, $value + 1 );
	}


	/* --- EXTENSIBLE META FIELD API --- */

	/**
	 * Get meta field value of given key.
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	protected function get_meta( string $key, $default = null )
	{
		$value = get_post_meta( $this->id, $key, true );
		return ($value === '') ? $default : $value;
	}

	/**
	 * Update new value of meta field of given key.
	 * @param string $key
	 * @param mixed $value
	 */
	protected function update_meta( string $key, $value )
	{
		update_post_meta( $this->id, $key, $value );
	}
}