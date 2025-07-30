<?php

/**
 * Admin widget for all taxonomies, which supports visitors.
 */
final class NasWP_Visitors_Admin_Widget_Tax extends NasWP_Visitors_Admin_Widget
{

	/**
	 * Instantiate with taxonomy dependency.
	 * @param string $tax
	 */
	public function __construct( private string $tax )
	{ }

	public function getTaxLabel(): string
	{
		$tax = $this->getTaxonomy();
		$cpts = array_map( fn( $cpt ) => get_post_type_object( $cpt )->labels->name, $tax->object_type );
		return $tax->labels->name . ' (' . implode( ', ', $cpts ) . ')';
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): string
	{
		return sprintf( __( 'Visitors - %s', 'naswp-visitors' ), $this->getTaxLabel() );
	}

	/**
	 * @inheritDoc
	 */
	public function render(): void
	{
		$total = NasWP_Visitors_Term::get_total_sum( $this->tax );
		$yearly = NasWP_Visitors_Term::get_yearly_sum( $this->tax );
		$monthly = NasWP_Visitors_Term::get_monthly_sum( $this->tax );
		$daily = NasWP_Visitors_Term::get_daily_sum( $this->tax );
		?>
		<div class="naswp-visitors-widget">
			<table>
				<tbody>
					<tr>
						<td class="naswp-visitors-widget__bold"><?php _e( 'Total visits', 'naswp-visitors' ); ?></td>

						<td class="naswp-visitors-widget__align-right"><?php echo number_format( $total, 0, "", " " ); ?></td>
					</tr>

					<tr>
						<td class="naswp-visitors-widget__bold"><?php _e( 'In 12 months', 'naswp-visitors' ); ?></td>

						<td class="naswp-visitors-widget__align-right"><?php echo number_format( $yearly, 0, "", " " ); ?></td>
					</tr>

					<tr>
						<td class="naswp-visitors-widget__bold"><?php _e( 'In 30 days', 'naswp-visitors' ); ?></td>

						<td class="naswp-visitors-widget__align-right"><?php echo number_format( $monthly, 0, "", " " ); ?></td>
					</tr>

					<tr>
						<td class="naswp-visitors-widget__bold"><?php _e( 'In 24 hours', 'naswp-visitors' ); ?></td>

						<td class="naswp-visitors-widget__align-right"><?php echo number_format( $daily, 0, "", " " ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Get taxonomy object.
	 * @return WP_Taxonomy
	 */
	protected function getTaxonomy(): WP_Taxonomy
	{
		return get_taxonomy( $this->tax );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string
	{
		return strtolower( mb_strtolower( static::class ) . '-' . $this->tax );
	}

	/**
	 * @inheritDoc
	 */
	public static function hook(): void
	{
		$taxonomies = apply_filters( 'naswp_visitors_tax', NASWP_VISITORS_TAX_DEFAULT );
		foreach ( $taxonomies as $tax ) {
			$self = new static( $tax );
			add_action( 'wp_dashboard_setup', [ $self, 'register' ] );
		}
	}
}