<?php

/**
 * Post visitor counter for the standalone SHORTINIT tracking endpoint.
 *
 * Uses get_metadata()/update_metadata() directly, because the
 * get_post_meta()/update_post_meta() wrappers live in wp-includes/post.php,
 * which is not loaded under SHORTINIT.
 */
final class NasWP_Visitors_Post_Light extends NasWP_Visitors_Base
{
	/**
	 * @inheritDoc
	 */
	protected function get_meta( string $key, $default = null )
	{
		$value = get_metadata( 'post', $this->id, $key, true );
		return ($value === '') ? $default : $value;
	}

	/**
	 * @inheritDoc
	 */
	protected function update_meta( string $key, $value ): void
	{
		update_metadata( 'post', $this->id, $key, $value );
	}
}
