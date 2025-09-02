<?php
/**
 * JWT authentication for NGOInfo Copilot
 *
 * @package NGOInfo\Copilot
 */

namespace NGOInfo\Copilot;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auth class for JWT token management
 */
class Auth {

	/**
	 * JWT algorithm
	 *
	 * @var string
	 */
	private const ALGORITHM = 'HS256';

	/**
	 * Mint JWT token for user
	 *
	 * @param \WP_User $user User object.
	 * @param array    $claims_extra Additional claims to include.
	 * @return string|false JWT token or false on failure.
	 */
	public function mint_user_jwt( $user, $claims_extra = array() ) {
		if ( ! $user instanceof \WP_User ) {
			ngoinfo_copilot_log( 'Invalid user object provided to mint_user_jwt', 'error' );
			return false;
		}

		// Get JWT configuration
		$issuer   = ngoinfo_copilot_get_option( 'jwt_issuer', 'ngoinfo-wp' );
		$audience = ngoinfo_copilot_get_option( 'jwt_audience', 'ngoinfo-copilot' );
		$expiry   = ngoinfo_copilot_get_option( 'jwt_expiry', 15 );
		$secret   = $this->get_jwt_secret();

		if ( false === $secret ) {
			ngoinfo_copilot_log( 'JWT secret not configured or decryption failed', 'error' );
			return false;
		}

		// Create JWT claims
		$current_time = time();
		$claims = array_merge(
			array(
				'sub'        => (string) $user->ID,
				'email'      => $user->user_email,
				'plan_tier'  => $this->get_user_plan_tier( $user ),
				'iat'        => $current_time,
				'exp'        => $current_time + ( $expiry * 60 ),
				'iss'        => $issuer,
				'aud'        => $audience,
				'nonce'      => wp_create_nonce( 'ngoinfo_copilot_' . $user->ID ),
			),
			$claims_extra
		);

		// Debug: Log JWT claims
		ngoinfo_copilot_debug( 'Minted JWT claims', $claims );

		// Generate JWT
		$jwt = $this->create_jwt( $claims, $secret );
		
		// Debug: Log JWT length (not the raw JWT for security)
		if ( false !== $jwt ) {
			ngoinfo_copilot_debug( 'JWT length', array( 'len' => strlen( $jwt ) ) );
		}
		
		// Log auth.build_claims and token prefix
		ngoinfo_log( 'auth.build_claims' );
		ngoinfo_log( array(
			'email'   => $user->user_email,
			'user_id' => $user->ID,
			'iss'     => $issuer,
			'aud'     => $audience,
			'now'     => $current_time,
			'exp'     => $current_time + ( $expiry * 60 ),
		) );
		
		if ( false !== $jwt ) {
			ngoinfo_log( 'auth.token_prefix' );
			ngoinfo_log( substr( $jwt, 0, 12 ) . '...' );
		}
		
		return $jwt;
	}

	/**
	 * Create JWT token
	 *
	 * @param array  $payload JWT payload.
	 * @param string $secret Signing secret.
	 * @return string|false JWT token or false on failure.
	 */
	private function create_jwt( $payload, $secret ) {
		// Create header
		$header = array(
			'typ' => 'JWT',
			'alg' => self::ALGORITHM,
		);

		// Encode components
		$header_encoded  = $this->base64url_encode( wp_json_encode( $header ) );
		$payload_encoded = $this->base64url_encode( wp_json_encode( $payload ) );

		// Create signature
		$signature_input = $header_encoded . '.' . $payload_encoded;
		$signature       = $this->create_signature( $signature_input, $secret );

		if ( false === $signature ) {
			return false;
		}

		return $header_encoded . '.' . $payload_encoded . '.' . $signature;
	}

	/**
	 * Create HMAC signature
	 *
	 * @param string $input Input string to sign.
	 * @param string $secret Signing secret.
	 * @return string|false Base64URL encoded signature or false on failure.
	 */
	private function create_signature( $input, $secret ) {
		if ( ! function_exists( 'hash_hmac' ) ) {
			ngoinfo_copilot_log( 'hash_hmac function not available for JWT signing', 'error' );
			return false;
		}

		$signature = hash_hmac( 'sha256', $input, $secret, true );
		
		if ( false === $signature ) {
			ngoinfo_copilot_log( 'Failed to create JWT signature', 'error' );
			return false;
		}

		return $this->base64url_encode( $signature );
	}

	/**
	 * Base64URL encode
	 *
	 * @param string $data Data to encode.
	 * @return string Base64URL encoded string.
	 */
	private function base64url_encode( $data ) {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	/**
	 * Get JWT signing secret (decrypted)
	 *
	 * @return string|false Decrypted secret or false on failure.
	 */
	private function get_jwt_secret() {
		$encrypted_secret = ngoinfo_copilot_get_option( 'jwt_secret' );
		
		if ( empty( $encrypted_secret ) ) {
			return false;
		}

		$secret = ngoinfo_copilot_decrypt( $encrypted_secret );
		
		if ( false === $secret ) {
			ngoinfo_copilot_log( 'Failed to decrypt JWT secret', 'error' );
			return false;
		}

		return $secret;
	}

	/**
	 * Get user plan tier
	 *
	 * @param \WP_User $user User object.
	 * @return string Plan tier.
	 */
	private function get_user_plan_tier( $user ) {
		// For now, return a placeholder
		// This will be enhanced in future phases when plan management is implemented
		$plan_tier = get_user_meta( $user->ID, 'ngoinfo_copilot_plan_tier', true );
		
		return ! empty( $plan_tier ) ? $plan_tier : 'free';
	}

	/**
	 * Add authorization header to HTTP request args
	 *
	 * @param array    $args HTTP request arguments.
	 * @param \WP_User $user User for JWT token (optional, uses current user if not provided).
	 * @return array Modified HTTP request arguments.
	 */
	public function add_auth_header( $args, $user = null ) {
		if ( null === $user ) {
			$user = wp_get_current_user();
		}

		if ( ! $user || ! $user->exists() ) {
			ngoinfo_copilot_log( 'No valid user available for JWT token generation', 'warning' );
			return $args;
		}

		$jwt_token = $this->mint_user_jwt( $user );
		
		if ( false === $jwt_token ) {
			ngoinfo_copilot_log( 'Failed to mint JWT token for API request', 'error' );
			return $args;
		}

		// Initialize headers array if not set
		if ( ! isset( $args['headers'] ) ) {
			$args['headers'] = array();
		}

		// Add Authorization header
		$args['headers']['Authorization'] = 'Bearer ' . $jwt_token;

		return $args;
	}

	/**
	 * Validate JWT secret configuration
	 *
	 * @return bool True if JWT secret is properly configured.
	 */
	public function is_jwt_configured() {
		$secret = $this->get_jwt_secret();
		
		if ( false === $secret ) {
			return false;
		}

		return ngoinfo_copilot_validate_jwt_secret( $secret );
	}

	/**
	 * Get JWT configuration status
	 *
	 * @return array Configuration status information.
	 */
	public function get_jwt_status() {
		$has_secret     = ! empty( ngoinfo_copilot_get_option( 'jwt_secret' ) );
		$secret_valid   = $this->is_jwt_configured();
		$issuer         = ngoinfo_copilot_get_option( 'jwt_issuer', 'ngoinfo-wp' );
		$audience       = ngoinfo_copilot_get_option( 'jwt_audience', 'ngoinfo-copilot' );
		$expiry         = ngoinfo_copilot_get_option( 'jwt_expiry', 15 );

		return array(
			'has_secret'   => $has_secret,
			'secret_valid' => $secret_valid,
			'issuer'       => $issuer,
			'audience'     => $audience,
			'expiry'       => $expiry,
			'algorithm'    => self::ALGORITHM,
		);
	}

	/**
	 * Generate secure random secret
	 *
	 * @param int $length Secret length (default: 64).
	 * @return string Random secret.
	 */
	public function generate_random_secret( $length = 64 ) {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
		$secret = '';
		
		for ( $i = 0; $i < $length; $i++ ) {
			$secret .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}
		
		return $secret;
	}

	/**
	 * Test JWT token generation
	 *
	 * @param \WP_User $user User to test with (optional, uses current user).
	 * @return array Test results.
	 */
	public function test_jwt_generation( $user = null ) {
		if ( null === $user ) {
			$user = wp_get_current_user();
		}

		if ( ! $user || ! $user->exists() ) {
			return array(
				'success' => false,
				'message' => __( 'No valid user available for testing.', 'ngoinfo-copilot' ),
			);
		}

		$start_time = microtime( true );
		$jwt_token  = $this->mint_user_jwt( $user );
		$end_time   = microtime( true );

		if ( false === $jwt_token ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to generate JWT token.', 'ngoinfo-copilot' ),
			);
		}

		$token_parts = explode( '.', $jwt_token );
		$header      = json_decode( base64_decode( strtr( $token_parts[0], '-_', '+/' ) ), true );
		$payload     = json_decode( base64_decode( strtr( $token_parts[1], '-_', '+/' ) ), true );

		return array(
			'success'        => true,
			'message'        => __( 'JWT token generated successfully.', 'ngoinfo-copilot' ),
			'token_preview'  => substr( $jwt_token, 0, 50 ) . '...',
			'generation_time' => round( ( $end_time - $start_time ) * 1000, 2 ) . 'ms',
			'header'         => $header,
			'payload'        => array_merge( $payload, array( 'sub' => '[REDACTED]', 'email' => '[REDACTED]' ) ),
		);
	}
}

