<?php
/**
 * JWT authentication for NGOInfo Copilot
 *
 * @package NGOInfo_Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auth class for JWT token management
 */
class NGOInfo_Copilot_Auth {

	/**
	 * JWT algorithm
	 *
	 * @var string
	 */
	private const ALGORITHM = 'HS256';

	/**
	 * Create bearer token for user
	 *
	 * @param int $user_id User ID.
	 * @return array|WP_Error Array with 'token' and 'expires' keys, or WP_Error on failure.
	 */
	public function create_bearer_token( $user_id ) {
		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			return new WP_Error( 'invalid_user', __( 'Invalid user ID.', 'ngoinfo-copilot' ) );
		}

		$jwt_secret = $this->get_jwt_secret();
		if ( false === $jwt_secret ) {
			return new WP_Error( 'jwt_secret_missing', __( 'JWT secret is not configured.', 'ngoinfo-copilot' ) );
		}

		// Get JWT configuration
		$issuer   = ngoinfo_copilot_get_option( 'jwt_issuer', 'ngoinfo-wp' );
		$audience = ngoinfo_copilot_get_option( 'jwt_audience', 'ngoinfo-copilot' );
		$expiry   = ngoinfo_copilot_get_option( 'jwt_expiry', 60 );

		// Create JWT claims
		$current_time = time();
		$claims = array(
			'sub'        => (string) $user->ID,
			'email'      => $user->user_email,
			'iat'        => $current_time,
			'exp'        => $current_time + ( $expiry * 60 ),
			'iss'        => $issuer,
			'aud'        => $audience,
		);

		// Generate JWT
		$token = $this->create_jwt( $claims, $jwt_secret );
		if ( false === $token ) {
			return new WP_Error( 'jwt_creation_failed', __( 'Failed to create JWT token.', 'ngoinfo-copilot' ) );
		}

		return array(
			'token'   => $token,
			'expires' => $current_time + ( $expiry * 60 ),
		);
	}

	/**
	 * Mint JWT token for user
	 *
	 * @param WP_User $user User object.
	 * @param array   $claims_extra Additional claims to include.
	 * @return string|false JWT token or false on failure.
	 */
	public function mint_user_jwt( $user, $claims_extra = array() ) {
		if ( ! $user instanceof WP_User ) {
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

		// Generate JWT
		return $this->create_jwt( $claims, $secret );
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
	 * Get user plan tier based on MemberPress memberships
	 *
	 * @param WP_User $user User object.
	 * @return string Plan tier (FREE, GROWTH, IMPACT).
	 */
	private function get_user_plan_tier( $user ) {
		// Check if MemberPress is active
		if ( ! function_exists( 'memberpress_get_active_memberships' ) ) {
			ngoinfo_copilot_log( 'MemberPress not active, defaulting to FREE tier', 'warning' );
			return 'FREE';
		}

		// Get user's active memberships
		$active_memberships = memberpress_get_active_memberships( $user->ID );
		
		if ( empty( $active_memberships ) ) {
			// Check for Free plan with 24h expiry rule
			if ( $this->has_free_plan_access( $user->ID ) ) {
				return 'FREE';
			}
			return 'FREE'; // Default to FREE for non-members
		}

		// Get MemberPress plan mappings
		$free_ids = $this->parse_membership_ids( NGOInfo_Copilot_Settings::get_memberpress_free_ids() );
		$growth_ids = $this->parse_membership_ids( NGOInfo_Copilot_Settings::get_memberpress_growth_ids() );
		$impact_ids = $this->parse_membership_ids( NGOInfo_Copilot_Settings::get_memberpress_impact_ids() );

		// Check for highest tier membership (Impact > Growth > Free)
		foreach ( $active_memberships as $membership ) {
			$membership_id = $membership->id ?? $membership->ID ?? null;
			
			if ( $membership_id && in_array( $membership_id, $impact_ids, true ) ) {
				return 'IMPACT';
			}
		}

		foreach ( $active_memberships as $membership ) {
			$membership_id = $membership->id ?? $membership->ID ?? null;
			
			if ( $membership_id && in_array( $membership_id, $growth_ids, true ) ) {
				return 'GROWTH';
			}
		}

		foreach ( $active_memberships as $membership ) {
			$membership_id = $membership->id ?? $membership->ID ?? null;
			
			if ( $membership_id && in_array( $membership_id, $free_ids, true ) ) {
				return 'FREE';
			}
		}

		// If user has memberships but none match our mapping, default to FREE
		return 'FREE';
	}

	/**
	 * Parse comma-separated membership IDs into array
	 *
	 * @param string $ids_string Comma-separated IDs.
	 * @return array Array of integer IDs.
	 */
	private function parse_membership_ids( $ids_string ) {
		if ( empty( $ids_string ) ) {
			return array();
		}

		$ids = explode( ',', $ids_string );
		$parsed_ids = array();

		foreach ( $ids as $id ) {
			$clean_id = intval( trim( $id ) );
			if ( $clean_id > 0 ) {
				$parsed_ids[] = $clean_id;
			}
		}

		return $parsed_ids;
	}

	/**
	 * Check if user has Free plan access with 24h expiry rule
	 *
	 * @param int $user_id User ID.
	 * @return bool True if user has free access.
	 */
	private function has_free_plan_access( $user_id ) {
		// Check if user has used free access in last 24 hours
		$last_free_use = get_user_meta( $user_id, '_ngoinfo_copilot_last_free_use', true );
		
		if ( empty( $last_free_use ) ) {
			return true; // First time user, allow free access
		}

		$time_since_last_use = time() - intval( $last_free_use );
		$twenty_four_hours = 24 * 60 * 60; // 24 hours in seconds

		return $time_since_last_use >= $twenty_four_hours;
	}

	/**
	 * Mark free plan usage for 24h expiry tracking
	 *
	 * @param int $user_id User ID.
	 */
	public function mark_free_plan_usage( $user_id ) {
		update_user_meta( $user_id, '_ngoinfo_copilot_last_free_use', time() );
	}

	/**
	 * Add authorization header to HTTP request args
	 *
	 * @param array   $args HTTP request arguments.
	 * @param WP_User $user User for JWT token (optional, uses current user if not provided).
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
}








