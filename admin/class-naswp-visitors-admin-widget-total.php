<?php

/**
 * Total admin widget
 */
final class NasWP_Visitors_Admin_Widget_Total extends NasWP_Visitors_Admin_Widget
{
	/**
	 * @inheritDoc
	 */
	public function getTitle(): string
	{
		return  __( 'Visitors - Total', 'naswp-visitors' );
	}

	/**
	 * @inheritDoc
	 */
	public function render(): void
	{
		$total = NasWP_Visitors_Post::get_total_sum() + NasWP_Visitors_Term::get_total_sum();
		$yearly = NasWP_Visitors_Post::get_yearly_sum() + NasWP_Visitors_Term::get_yearly_sum();
		$monthly = NasWP_Visitors_Post::get_monthly_sum() + NasWP_Visitors_Term::get_monthly_sum();
		$daily = NasWP_Visitors_Post::get_daily_sum() + NasWP_Visitors_Term::get_daily_sum();

		$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		$top5 = get_posts( [
			'post_type' => $cpts,
			'meta_key' => NASWP_VISITORS_TOTAL,
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'posts_per_page' => 5,
		] );
		$top5Yearly = get_posts( [
			'post_type' => $cpts,
			'meta_key' => NASWP_VISITORS_YEARLY,
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'posts_per_page' => 5,
		] );
		$top5Monthly = get_posts( [
			'post_type' => $cpts,
			'meta_key' => NASWP_VISITORS_MONTHLY,
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'posts_per_page' => 5,
		] );
		$top5Daily = get_posts( [
			'post_type' => $cpts,
			'meta_key' => NASWP_VISITORS_DAILY,
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'posts_per_page' => 5,
		] );
		?>
		<div class="naswp-visitors-widget">
			<div class="naswp-visitors-widget__tabs">
				<?php // TAB 1 ?>
				<input class="naswp-visitors-widget__tab-radio" name="naswp-total" tabindex="1" type="radio" id="tabTotal1" checked="checked">

				<label class="naswp-visitors-widget__tab-label" for="tabTotal1"><?php _e( 'Statistics', 'naswp-visitors' ); ?></label>

				<div class="naswp-visitors-widget__tab-content" tabindex="1">
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

				<?php // TAB 2 ?>
				<input class="naswp-visitors-widget__tab-radio" name="naswp-total" tabindex="1" type="radio" id="tabTotal2">

				<label class="naswp-visitors-widget__tab-label" for="tabTotal2"><?php _e( 'TOP5', 'naswp-visitors' ); ?></label>

				<div class="naswp-visitors-widget__tab-content" tabindex="1">
					<h3><?php _e( 'Total visits', 'naswp-visitors' ); ?></h3>

					<ol>
						<?php foreach ( $top5 as $p ) { ?>
							<li>
								<a href="<?php echo get_permalink( $p->ID ) ?>" target="_blank" rel="noopener noreferrer"><?php echo apply_filters( 'the_title', $p->post_title ) ?></a>
								(<?php echo number_format( get_post_meta( $p->ID, NASWP_VISITORS_TOTAL, true ), 0, "", " " ) ?>)
							</li>
						<?php } ?>
					</ol>

					<h3><?php _e( 'In 12 months', 'naswp-visitors' ); ?></h3>

					<ol>
						<?php foreach ( $top5Yearly as $p ) { ?>
							<li>
								<a href="<?php echo get_permalink( $p->ID ) ?>" target="_blank" rel="noopener noreferrer"><?php echo apply_filters( 'the_title', $p->post_title ) ?></a>
								(<?php echo number_format( get_post_meta( $p->ID, NASWP_VISITORS_YEARLY, true ), 0, "", " " ) ?>)
							</li>
						<?php } ?>
					</ol>

					<h3><?php _e( 'In 30 days', 'naswp-visitors' ); ?></h3>

					<ol>
						<?php foreach ( $top5Monthly as $p ) { ?>
							<li>
								<a href="<?php echo get_permalink( $p->ID ) ?>" target="_blank" rel="noopener noreferrer"><?php echo apply_filters( 'the_title', $p->post_title ) ?></a>
								(<?php echo number_format( get_post_meta( $p->ID, NASWP_VISITORS_MONTHLY, true ), 0, "", " " ) ?>)
							</li>
						<?php } ?>
					</ol>

					<h3><?php _e( 'In 24 hours', 'naswp-visitors' ); ?></h3>

					<ol>
						<?php foreach ( $top5Daily as $p ) { ?>
							<li>
								<a href="<?php echo get_permalink( $p->ID ) ?>" target="_blank" rel="noopener noreferrer"><?php echo apply_filters( 'the_title', $p->post_title ) ?></a>
								(<?php echo number_format( get_post_meta( $p->ID, NASWP_VISITORS_DAILY, true ), 0, "", " " ) ?>)
							</li>
						<?php } ?>
					</ol>
				</div>
			</div>
		</div>
		<?php
	}
}