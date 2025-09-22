<?php
/**
 * JWT Helper for NGOInfo Copilot
 *
 * @package NGOInfo_Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JWT Helper class
 */
class NGOInfo_Copilot_JWT_Helper {

	/**
	 * Encode JWT token with HS256 algorithm
	 *
	 * @param array  $claims JWT claims.
	 * @param string $secret Secret key for signing.
	 * @return string JWT token.
	 */
	public static function encode( $claims, $secret ) {
		// Create header
		$header = array(
			'alg' => 'HS256',
			'typ' => 'JWT',
		);

		// Encode header and payload
		$header_encoded = self::base64url_encode( wp_json_encode( $header ) );
		$payload_encoded = self::base64url_encode( wp_json_encode( $claims ) );

		// Create signature
		$signature_data = $header_encoded . '.' . $payload_encoded;
		$signature = hash_hmac( 'sha256', $signature_data, $secret, true );
		$signature_encoded = self::base64url_encode( $signature );

		// Return complete JWT
		return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
	}

	/**
	 * Base64URL encode (without padding)
	 *
	 * @param string $data Data to encode.
	 * @return string Base64URL encoded string.
	 */
	private static function base64url_encode( $data ) {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	/**
	 * Base64URL decode
	 *
	 * @param string $data Data to decode.
	 * @return string Decoded string.
	 */
	private static function base64url_decode( $data ) {
		// Add padding if needed
		$remainder = strlen( $data ) % 4;
		if ( $remainder ) {
			$pad_length = 4 - $remainder;
			$data .= str_repeat( '=', $pad_length );
		}

		return base64_decode( strtr( $data, '-_', '+/' ) );
	}

	/**
	 * Get current timestamp
	 * Can be overridden for testing
	 *
	 * @return int Current timestamp.
	 */
	public static function now() {
		return time();
	}

	/**
	 * Create JWT claims for Grantpilot API
	 *
	 * @param WP_User $user WordPress user.
	 * @return array JWT claims.
	 */
	public static function create_grantpilot_claims( $user ) {
		$now = self::now();
		
		return array(
			'sub'   => (string) $user->ID,
			'email' => $user->user_email,
			'plan'  => 'grantpilot',
			'iss'   => NGOInfo_Copilot_Settings::get_jwt_iss(),
			'aud'   => NGOInfo_Copilot_Settings::get_jwt_aud(),
			'iat'   => $now,
			'exp'   => $now + 600, // 10 minutes
		);
	}

	/**
	 * Mint JWT token for Grantpilot API
	 *
	 * @param WP_User $user WordPress user.
	 * @return string|false JWT token or false on failure.
	 */
	public static function mint_grantpilot_token( $user ) {
		$secret = NGOInfo_Copilot_Settings::get_jwt_secret();
		if ( empty( $secret ) ) {
			ngoinfo_copilot_log( 'JWT secret not configured', 'error' );
			return false;
		}

		$claims = self::create_grantpilot_claims( $user );
		return self::encode( $claims, $secret );
	}
}
