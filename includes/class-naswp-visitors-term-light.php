<?php

/**
 * Term visitor counter for the standalone SHORTINIT tracking endpoint.
 *
 * Uses get_metadata()/update_metadata() directly, because the
 * get_term_meta()/update_term_meta() wrappers live in wp-includes/taxonomy.php,
 * which is not loaded under SHORTINIT.
 */
final class NasWP_Visitors_Term_Light extends NasWP_Visitors_Base
{
	/**
	 * @inheritDoc
	 */
	protected function get_meta( string $key, $default = null )
	{
		$value = get_metadata( 'term', $this->id, $key, true );
		return ($value === '') ? $default : $value;
	}

	/**
	 * @inheritDoc
	 */
	protected function update_meta( string $key, $value ): void
	{
		update_metadata( 'term', $this->id, $key, $value );
	}
}
