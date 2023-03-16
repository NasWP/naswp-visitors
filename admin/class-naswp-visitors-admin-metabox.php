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

		// Add meta post box JS
		add_action( 'admin_enqueue_scripts', [ $self, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_naswp_visitors_save', [ $self, 'save_post_ajax' ] );

		$taxonomies = apply_filters( 'naswp_visitors_tax', NASWP_VISITORS_TAX_DEFAULT );
		foreach ($taxonomies as $tax) {
			add_action( "{$tax}_edit_form", [ $self, 'add_term_meta_box' ] );
			add_action( "saved_{$tax}", [ $self, 'save_term' ] );
		}
	}

	/**
	 * Enqueue admin JS to save metabox values by AJAX.
	 */
	public function enqueue_scripts(): void
	{
		$cpts = apply_filters( 'naswp_visitors_cpt', NASWP_VISITORS_CPT_DEFAULT );
		$screen = get_current_screen();
		if ( $screen->base !== 'post' || !in_array( $screen->post_type, $cpts ) ) return;

		wp_enqueue_script( 'naswp_visitors_metabox', NASWP_VISITORS_URL . '/admin/meta_box.js', [], false, true );
		wp_localize_script( 'naswp_visitors_metabox', 'naswp_visitors', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'ID' => get_the_ID()
		] );
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
			__( 'Visitors', 'naswp-visitors' ),
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
		?>
		<div id="naswpVisitorsTable" style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px">
			<?php
			$this->render_float_table( $model );
			// $this->render_views_table( $model, __( 'Daily visits', 'naswp-visitors' ), $model->get_daily_data(), 'D, j. F Y' );
			// $this->render_views_table( $model, __( 'Monthly visits', 'naswp-visitors' ), $model->get_monthly_data(), 'F Y', );
			// $this->render_views_table( $model, __( 'Yearly visits', 'naswp-visitors' ), $model->get_yearly_data(), 'Y', );
			?>
		</div>
		<div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px">
			<label>
				<input type="checkbox" name="naswp_reset" value="1">
				<?php echo __( 'Reset all visitors data.', 'naswp-visitors' ); ?>
			</label>

			<div class="form-field form-required term-name-wrap">
				<label for="naswp_total"><?php _e( 'Adjustment of the total number of visits:', 'naswp-visitors' ) ?></label>
				<input name="naswp_total" id="naswp_total" type="number" value="<?php echo $model->get_total() ?>" size="40" aria-required="true">
			</div>

			<?php if ( $model instanceof NasWP_Visitors_Post ) { ?>
				<button type="button" name="naswp_save" class="button button-primary" data-loading-caption="<?php _e( 'Saving...', 'naswp-visitors' ) ?>">
					<?php _e( 'Save', 'naswp-visitors' ) ?>
				</button>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Render table of total visits.
	 * @param NasWP_Visitors_Base $model
	 */
	private function render_float_table( NasWP_Visitors_Base $model ): void
	{
		?>
		<table style="flex-shrink:0">
			<thead>
				<tr>
					<th colspan="2" style="text-align:left"><?php _e( 'Statistics', 'naswp-visitors' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php _e( 'Total visits', 'naswp-visitors' ) ?>:</td>
					<th><?php echo $model->get_total() ?>x</th>
				</tr>
				<tr>
					<td><?php _e( 'Visits in 24 hours', 'naswp-visitors' ) ?>:</td>
					<th><?php echo $model->get_daily() ?>x</th>
				</tr>
				<tr>
					<td><?php _e( 'Visits in 30 days', 'naswp-visitors' ) ?>:</td>
					<th><?php echo $model->get_monthly() ?>x</th>
				</tr>
				<tr>
					<td><?php _e( 'Visits in 12 months', 'naswp-visitors' ) ?>:</td>
					<th><?php echo $model->get_yearly() ?>x</th>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render visits per time interval in table.
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
	 * Called by AJAX to save metabox values.
	 */
	public function save_post_ajax(): void
	{
		$id = $_POST['id'];
		$this->save_post( intval( $id ) );
		$model = new NasWP_Visitors_Post( $id );

		ob_start();
		$this->render_float_table( $model );
		$table = ob_get_clean();

		wp_send_json( [
			'data' => $_POST,
			'table' => $table
		] );
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
		if ( !$p || !in_array( $p->post_type, $cpts ) ) return $postId;

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

		if ( isset( $_POST['naswp_total'] ) ) {
			$total = filter_var( $_POST['naswp_total'], FILTER_SANITIZE_NUMBER_INT );
			$model->update_total( $total );
		}

		if ( $model->get_last_update() === 0 ) {
			$model->reset_last_update();
		}
	}
}