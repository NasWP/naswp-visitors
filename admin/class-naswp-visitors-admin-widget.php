<?php

/**
 * Register admin widget
 */
abstract class NasWP_Visitors_Admin_Widget
{
	/**
	 * Get widget title.
	 * @return string
	 */
	abstract public function getTitle(): string;

	/**
	 * Render widget content.
	 */
	abstract public function render(): void;


	/**
	 * Get widget ID for it's registration.
	 * @return string
	 */
	public function getId(): string
	{
		return strtolower( mb_strtolower( static::class ) );
	}

	/**
	 * Register widget along with it's styles.
	 * Use this in 'wp_dashboard_setup' action.
	 * @internal
	 */
	public function register(): void
	{
		wp_enqueue_style( 'naswp_visitors_widget', NASWP_VISITORS_URL . '/admin/widget.css', [], false, 'all' );
		wp_add_dashboard_widget( $this->getId(), $this->getTitle(), [ $this, 'render' ] );
	}

	/**
	 * Register widget instantiation.
	 */
	public static function hook(): void
	{
		add_action( 'wp_dashboard_setup', [ new static(), 'register' ] );
	}
}