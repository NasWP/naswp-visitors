<?php

/**
 * Stateless HMAC token securing the tracking endpoint.
 *
 * Replaces WP nonce on the tracking route: it is verifiable under SHORTINIT
 * (pluggable.php with wp_verify_nonce() is not loaded there) and does not
 * expire with long-lived page caches. The date part rotates tracking URLs
 * daily, which keeps the "counted once per browser per page per day"
 * semantics of the original nonce-rotated URLs.
 */
final class NasWP_Visitors_Token
{
	private const SALT_PLACEHOLDER = 'put your unique phrase here';
	private const SECRET_OPTION = 'naswp_visitors_secret';


	/**
	 * Create token for given object ID, entity type and Ymd datestamp.
	 * @param int $id
	 * @param string $type 'post' | 'tax'
	 * @param string $d Datestamp in 'Ymd' format.
	 * @return string
	 */
	public static function create( int $id, string $type, string $d ): string
	{
		return hash_hmac( 'sha256', $id . '|' . $type . '|' . $d, self::secret() );
	}

	/**
	 * Verify raw request parameters against the token.
	 * @param mixed $id
	 * @param mixed $type
	 * @param mixed $d
	 * @param mixed $token
	 * @return bool
	 */
	public static function verify( $id, $type, $d, $token ): bool
	{
		if ( !is_string( $token ) || $token === '' ) return false;
		if ( !is_numeric( $id ) || intval( $id ) <= 0 ) return false;
		if ( !is_string( $type ) || !in_array( $type, [ 'post', 'tax' ], true ) ) return false;
		if ( !is_string( $d ) || !preg_match( '/^\d{8}$/', $d ) ) return false;
		return hash_equals( self::create( intval( $id ), $type, $d ), $token );
	}

	/**
	 * Get site-specific secret. Prefers wp-config.php salts, falls back to an
	 * autoloaded option (readable under SHORTINIT via get_option()).
	 * @return string
	 */
	public static function secret(): string
	{
		if (
			defined( 'NONCE_KEY' ) && defined( 'NONCE_SALT' ) &&
			strpos( NONCE_KEY, self::SALT_PLACEHOLDER ) === false &&
			strpos( NONCE_SALT, self::SALT_PLACEHOLDER ) === false
		) {
			return NONCE_KEY . NONCE_SALT;
		}

		$secret = get_option( self::SECRET_OPTION );
		if ( !is_string( $secret ) || $secret === '' ) {
			$secret = bin2hex( random_bytes( 32 ) );
			add_option( self::SECRET_OPTION, $secret );
		}
		return $secret;
	}
}
