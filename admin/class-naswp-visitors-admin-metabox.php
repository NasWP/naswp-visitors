<?php

/**
 * Add daily, monthly, yearly and total visits columns to admin tables.
 */
class NasWP_Visitors_Admin_MetaBox
{
	/**
	 * Hook actions.
	 */
	public static function hook()
	{
		$self = new self();
		add_action( 'add_meta_boxes', array( $self, 'add_meta_box' ) );
		add_action( 'save_post', array( $self, 'save' ) );
	}

	/**
	 * Add meta box to defined post types.
	 * @param string $postType
	 */
	public function add_meta_box( string $postType )
	{
		$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		if ( !in_array( $postType, $cpts ) ) return;
		add_meta_box(
			'naswp_visitors',
			__( 'Visitors', 'naswp_visitors' ),
			[ $this, 'render_meta_box_content' ],
			$postType,
			'side',
			'high'
		);
	}

	/**
	 * Render meta box.
	 * @param WP_Post $post
	 * @internal
	 */
	public function render_meta_box_content( WP_Post $post )
	{
		$model = new NasWP_Visitors_Post($post->ID);
		?>
		<div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:20px">
			<?php
			$this->render_float_table( $model );
			$this->render_views_table( $model, __( 'Daily views', 'naswp_visitors' ), $model->get_daily_data(), 'D, j. F Y' );
			$this->render_views_table( $model, __( 'Monthly views', 'naswp_visitors' ), $model->get_monthly_data(), 'F Y', );
			$this->render_views_table( $model, __( 'Yearly views', 'naswp_visitors' ), $model->get_yearly_data(), 'Y', );
			?>
		</div>
		<div style="display:flex;flex-wrap:wrap;align-items:center;gap:30px">
			<label>
				<br>
				<input type="checkbox" name="naswp_reset" value="1" class="components-checkbox-control__input">
				<?php _e( 'Reset all data for this post on next update. Refresh page after save.', 'naswp_visitors' ) ?>
			</label>

			<div class="form-field form-required term-name-wrap">
				<label for="naswp_total"><?php _e( 'Total views number', 'naswp_visitors' ) ?></label>
				<input name="naswp_total" id="naswp_total" type="number" value="<?php echo $model->get_total() ?>" size="40" aria-required="true">
			</div>

			<?php wp_nonce_field( 'naswp_visitors', 'naswp_visitors_nonce' ) ?>
		</div>
		<?php
	}

	/**
	 * Render table of total views.
	 * @param NasWP_Visitors_Post $model
	 */
	private function render_float_table(NasWP_Visitors_Post $model)
	{
		?>
		<table style="flex-shrink:0">
			<thead>
				<tr>
					<th colspan="2" style="text-align:left"><?php _e( 'Statistics', 'naswp_visitors' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php _e( 'Total views', 'naswp_visitors' ) ?>:</td>
					<th><?php echo $model->get_total() ?>x</th>
				</tr>
				<tr>
					<td><?php _e( 'Views in past 24 hours', 'naswp_visitors' ) ?>:</td>
					<th><?php echo $model->get_daily() ?>x</th>
				</tr>
				<tr>
					<td><?php _e( 'Views in past 30 days', 'naswp_visitors' ) ?>:</td>
					<th><?php echo $model->get_monthly() ?>x</th>
				</tr>
				<tr>
					<td><?php _e( 'Views in past 12 months', 'naswp_visitors' ) ?>:</td>
					<th><?php echo $model->get_yearly() ?>x</th>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render views per time interval in table.
	 * @param NasWP_Visitors_Post $model
	 * @param string $title
	 * @param array|array[] $views
	 * @param string $dateFormat
	 * @internal
	 */
	private function render_views_table( NasWP_Visitors_Post $model, string $title, array $views, string $dateFormat )
	{
		?>
		<table style="flex-shrink:0">
			<thead>
				<tr>
					<th colspan="2" style="text-align:left"><?php echo $title ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($views as $view) { ?>
					<tr>
						<td><?php echo $model->format($dateFormat, $view['lastUpdate']) ?>:</td>
						<th style="text-align:right"><?php echo $view['views'] ?>x</th>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Save metabox data.
	 * @param int $postId
	 */
	public function save( int $postId )
	{

		if ( !isset( $_POST['naswp_visitors_nonce'] ) ) {
			return $postId;
		}
		$nonce = $_POST['naswp_visitors_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'naswp_visitors' ) ) {
			return $postId;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $postId;
		}


		$model = new NasWP_Visitors_Post($postId);
		if ( isset( $_POST['naswp_reset'] ) && $_POST['naswp_reset'] ) {
			$model->reset_data();
		}

		$total = filter_var( $_POST['naswp_total'], FILTER_SANITIZE_NUMBER_INT );
		$model->update_total($total);

		if ( $model->get_last_update() === 0 ) {
			$model->reset_last_update();
		}

		return $postId;
	}
}