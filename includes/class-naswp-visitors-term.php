<?php

/**
 * API for term visitor counters
 */
final class NasWP_Visitors_Term extends NasWP_Visitors_Base
{
	/**
	 * @inheritDoc
	 */
	protected function get_meta( string $key, $default = null )
	{
		$value = get_term_meta( $this->id, $key, true );
		return ($value === '') ? $default : $value;
	}

	/**
	 * @inheritDoc
	 */
	protected function update_meta( string $key, $value ): void
	{
		update_term_meta( $this->id, $key, $value );
	}
}