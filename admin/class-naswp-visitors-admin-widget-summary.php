<?php

/**
 * Overview admin widget
 */
final class NasWP_Visitors_Admin_Widget_Summary extends NasWP_Visitors_Admin_Widget
{
	/**
	 * @inheritDoc
	 */
	public function getTitle(): string
	{
		return  __( 'Visitors - Summary', 'naswp-visitors' );
	}

	/**
	 * @inheritDoc
	 */
	public function render(): void
	{
		$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		$taxonomies = apply_filters( 'naswp_visitors_tax', NASWP_VISITORS_TAX_DEFAULT );
		$top10 = get_posts( [
			'post_type' => $cpts,
			'meta_key' => NASWP_VISITORS_TOTAL,
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'posts_per_page' => 10,
		] );
		$total = NasWP_Visitors_Post::get_total_sum() + NasWP_Visitors_Term::get_total_sum();
		?>
		<div class="naswp-visitors-widget">
			<div class="naswp-visitors-widget__tabs">
				<?php // TAB 1 ?>
				<input class="naswp-visitors-widget__tab-radio" name="naswp-summary" tabindex="1" type="radio" id="tabSummary1" checked="checked">

				<label class="naswp-visitors-widget__tab-label" for="tabSummary1"><?php _e( 'Statistics', 'naswp-visitors' ); ?></label>

				<div class="naswp-visitors-widget__tab-content" tabindex="1">
					<table>
						<tbody>
							<tr>
								<td class="naswp-visitors-widget__bold"><?php _e( 'Total number of visitors', 'naswp-visitors' ); ?></td>

								<td class="naswp-visitors-widget__align-right"><?php echo number_format( $total, 0, "", " " ); ?></td>
							</tr>

							<?php
							foreach ( $cpts as $cpt ) {
								$cptWidget = new NasWP_Visitors_Admin_Widget_Cpt( $cpt );
								?>
								<tr>
									<td class="naswp-visitors-widget__bold"><?php echo $cptWidget->getCptLabel(); ?></td>

									<td class="naswp-visitors-widget__align-right"><?php echo number_format( NasWP_Visitors_Post::get_total_sum( [ $cpt ] ), 0, "", " " ); ?></td>
								</tr>
							<?php } ?>

							<?php foreach ( $taxonomies as $tax ) {
								$taxWidget = new NasWP_Visitors_Admin_Widget_Tax( $tax );
								?>
								<tr>
									<td class="naswp-visitors-widget__bold"><?php echo $taxWidget->getTaxLabel(); ?></td>

									<td class="naswp-visitors-widget__align-right"><?php echo number_format( NasWP_Visitors_Term::get_total_sum( [ $tax ] ), 0, "", " " ); ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>

				<?php // TAB 2 ?>
				<input class="naswp-visitors-widget__tab-radio" name="naswp-summary" tabindex="1" type="radio" id="tabSummary2">

				<label class="naswp-visitors-widget__tab-label" for="tabSummary2"><?php _e( 'TOP10', 'naswp-visitors' ); ?></label>

				<div class="naswp-visitors-widget__tab-content" tabindex="1">
					<ol>

						<?php foreach ( $top10 as $p ) { ?>
							<li>
								<a href="<?php echo get_permalink( $p->ID ) ?>" target="_blank" rel="noopener noreferrer"><?php echo apply_filters( 'the_title', $p->post_title ) ?></a>
								(<?php echo number_format( get_post_meta( $p->ID, NASWP_VISITORS_TOTAL, true ), 0, "", " " ) ?>)
							</li>
						<?php } ?>

					</ol>
				</div>
			</div>

		</div>
		<?php
	}
}