<?php

/**
 * Add daily, monthly, yearly and total visits columns to admin tables.
 */
class NasWP_Visitors_Admin_MetaBox
{
	/**
	 * Hook actions.
	 */
	public static function hook(): void
	{
		$self = new self();
		add_action( 'add_meta_boxes', [ $self, 'add_post_meta_box' ] );
		add_action( 'save_post', [ $self, 'save_post' ] );

		$taxonomies = apply_filters( 'naswp_visitors_tax', NASWP_VISITORS_TAX_DEFAULT );
		foreach ($taxonomies as $tax) {
			add_action( "{$tax}_edit_form", [ $self, 'add_term_meta_box' ] );
			add_action( "saved_{$tax}", [ $self, 'save_term' ] );
		}
	}

	/**
	 * Add meta box to defined taxonomy term.
	 * @param WP_Term $term
	 */
	public function add_term_meta_box( WP_Term $term ): void
	{
		?>
		<hr />
		<?php
		$this->render_meta_box_content( new NasWP_Visitors_Term( $term->term_id ) );
		?>
		<br />
		<hr />
		<?php
	}

	/**
	 * Add meta box to defined post types.
	 * @param string $postType
	 */
	public function add_post_meta_box( string $postType ): void
	{
		$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		if ( !in_array( $postType, $cpts ) ) return;
		add_meta_box(
			'naswp_visitors',
			__( 'Visitors', 'visitors-naswp' ),
			// [ $this, 'render_meta_box_content' ],
			fn( WP_Post $post ) => $this->render_meta_box_content( new NasWP_Visitors_Post( $post->ID ) ),
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
	public function render_meta_box_content( NasWP_Visitors_Base $model ): void
	{
		$type = $model instanceof NasWP_Visitors_Post ? __( 'post', 'visitors-naswp' ) : __( 'term', 'visitors-naswp' );
		?>
		<div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:20px">
			<?php
			$this->render_float_table( $model );
			// $this->render_views_table( $model, __( 'Daily views', 'visitors-naswp' ), $model->get_daily_data(), 'D, j. F Y' );
			// $this->render_views_table( $model, __( 'Monthly views', 'visitors-naswp' ), $model->get_monthly_data(), 'F Y', );
			// $this->render_views_table( $model, __( 'Yearly views', 'visitors-naswp' ), $model->get_yearly_data(), 'Y', );
			?>
		</div>
		<div style="display:flex;flex-wrap:wrap;align-items:center;gap:30px">
			<label>
				<br>
				<input type="checkbox" name="naswp_reset" value="1">
				<?php echo sprintf( __( 'Reset all data for this %s on next update. Refresh page after save.', 'visitors-naswp' ), $type ) ?>
			</label>

			<div class="form-field form-required term-name-wrap">
				<label for="naswp_total"><?php _e( 'Total views number', 'visitors-naswp' ) ?></label>
				<input name="naswp_total" id="naswp_total" type="number" value="<?php echo $model->get_total() ?>" size="40" aria-required="true">
			</div>
		</div>
		<?php
	}

	/**
	 * Render table of total views.
	 * @param NasWP_Visitors_Base $model
	 */
	private function render_float_table( NasWP_Visitors_Base $model ): void
	{
		?>
		<table style="flex-shrink:0">
			<thead>
				<tr>
					<th colspan="2" style="text-align:left"><?php _e( 'Statistics', 'visitors-naswp' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php _e( 'Total views', 'visitors-naswp' ) ?>:</td>
					<th><?php echo $model->get_total() ?>x</th>
				</tr>
				<tr>
					<td><?php _e( 'Views in past 24 hours', 'visitors-naswp' ) ?>:</td>
					<th><?php echo $model->get_daily() ?>x</th>
				</tr>
				<tr>
					<td><?php _e( 'Views in past 30 days', 'visitors-naswp' ) ?>:</td>
					<th><?php echo $model->get_monthly() ?>x</th>
				</tr>
				<tr>
					<td><?php _e( 'Views in past 12 months', 'visitors-naswp' ) ?>:</td>
					<th><?php echo $model->get_yearly() ?>x</th>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render views per time interval in table.
	 * @param NasWP_Visitors_Base $model
	 * @param string $title
	 * @param array|array[] $views
	 * @param string $dateFormat
	 * @internal
	 */
	private function render_views_table( NasWP_Visitors_Base $model, string $title, array $views, string $dateFormat ): void
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
	 * Save post metabox data.
	 * @param int $postId
	 * @return int
	 */
	public function save_post( int $postId ): int
	{
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $postId;
		}

		$p = get_post( $postId );
		$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		if ( !$p || in_array( $p->post_type, $cpts ) ) return $postId;

		$model = new NasWP_Visitors_Post( $postId );
		if ( isset( $_POST['naswp_reset'] ) && $_POST['naswp_reset'] ) {
			$model->reset_data();
		}

		$total = intval( filter_var( $_POST['naswp_total'], FILTER_SANITIZE_NUMBER_INT ) );
		$model->update_total( $total );

		if ( $model->get_last_update() === 0 ) {
			$model->reset_last_update();
		}

		return $postId;
	}

	/**
	 * Save term metabox data.
	 * @param int $termId
	 */
	public function save_term( int $termId ): void
	{
		$model = new NasWP_Visitors_Term( $termId );
		if ( isset( $_POST['naswp_reset'] ) && $_POST['naswp_reset'] ) {
			$model->reset_data();
		}

		$total = filter_var( $_POST['naswp_total'], FILTER_SANITIZE_NUMBER_INT );
		$model->update_total( $total );

		if ( $model->get_last_update() === 0 ) {
			$model->reset_last_update();
		}
	}
}