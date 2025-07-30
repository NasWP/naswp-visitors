<?php

/**
 * Admin widget for all CPTs, which supports visitors.
 */
final class NasWP_Visitors_Admin_Widget_Cpt extends NasWP_Visitors_Admin_Widget
{
	/**
	 * Instantiate with CPT dependency.
	 * @param string $cpt
	 */
	public function __construct( private string $cpt )
	{ }

	public function getCptLabel(): string
	{
		return $this->getPostType()->labels->name;
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): string
	{
		return sprintf( __( 'Visitors - %s', 'naswp-visitors' ), $this->getCptLabel() );
	}

	public function render(): void
	{
		$total = NasWP_Visitors_Post::get_total_sum( $this->cpt );
		$yearly = NasWP_Visitors_Post::get_yearly_sum( $this->cpt );
		$monthly = NasWP_Visitors_Post::get_monthly_sum( $this->cpt );
		$daily = NasWP_Visitors_Post::get_daily_sum( $this->cpt );
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
	 * Get post type object.
	 * @return WP_Post_Type
	 */
	protected function getPostType(): WP_Post_Type
	{
		return get_post_type_object( $this->cpt );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string
	{
		return strtolower( mb_strtolower( static::class ) . '-' . $this->cpt );
	}

	/**
	 * @inheritDoc
	 */
	public static function hook(): void
	{
		$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		foreach ( $cpts as $cpt ) {
			$self = new static( $cpt );
			add_action( 'wp_dashboard_setup', [ $self, 'register' ] );
		}
	}
}