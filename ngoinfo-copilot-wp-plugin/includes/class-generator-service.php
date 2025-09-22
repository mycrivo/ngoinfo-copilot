<?php
/**
 * Generator Service for NGOInfo Copilot Grantpilot
 *
 * @package NGOInfo_Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generator Service class
 */
class NGOInfo_Copilot_Generator_Service {

	/**
	 * Render the Grantpilot generation form
	 *
	 * @return string HTML form.
	 */
	public static function render_form() {
		// Check if settings are configured
		$api_base_url = NGOInfo_Copilot_Settings::get_api_base_url();
		$jwt_secret = NGOInfo_Copilot_Settings::get_jwt_secret();
		
		if ( empty( $api_base_url ) || empty( $jwt_secret ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return '<div class="ngoinfo-copilot-warning"><p><strong>Admin Notice:</strong> Grantpilot settings are not configured. Please configure API Base URL and JWT Secret in plugin settings.</p></div>';
			} else {
				return '<div class="ngoinfo-copilot-warning"><p>Service temporarily unavailable. Please try again later.</p></div>';
			}
		}

		// Check if user can generate
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return '<div class="ngoinfo-copilot-warning"><p>Please log in to use Grantpilot.</p></div>';
		}

		if ( ! self::user_can_generate( $user_id ) ) {
			return '<div class="ngoinfo-copilot-warning"><p>Grantpilot membership required. Please upgrade your membership to access this feature.</p></div>';
		}

		// Check rate limiting
		if ( ! self::rate_limit_check( $user_id ) ) {
			$cooldown = NGOInfo_Copilot_Settings::get_cooldown_secs();
			return '<div class="ngoinfo-copilot-warning"><p>Please wait ' . esc_html( $cooldown ) . ' seconds before generating another proposal.</p></div>';
		}

		ob_start();
		?>
		<div class="ngoinfo-copilot-generator-form">
			<form id="ngoinfo-copilot-generate-form" method="post">
				<?php wp_nonce_field( 'ngoinfo_copilot_generate', 'ngoinfo_copilot_generate_nonce' ); ?>
				<input type="hidden" name="action" value="ngoinfo_copilot_generate" />
				
				<div class="form-group">
					<label for="donor">Donor Organization *</label>
					<input type="text" id="donor" name="donor" required maxlength="200" />
				</div>

				<div class="form-group">
					<label for="theme">Theme/Focus Area *</label>
					<input type="text" id="theme" name="theme" required maxlength="200" />
				</div>

				<div class="form-group">
					<label for="country">Country *</label>
					<input type="text" id="country" name="country" required maxlength="200" />
				</div>

				<div class="form-group">
					<label for="title">Project Title *</label>
					<input type="text" id="title" name="title" required maxlength="200" />
				</div>

				<div class="form-group">
					<label for="budget">Budget (USD) *</label>
					<input type="number" id="budget" name="budget" required min="0" step="0.01" />
				</div>

				<div class="form-group">
					<label for="duration">Duration (months) *</label>
					<input type="number" id="duration" name="duration" required min="1" max="60" />
				</div>

				<div class="form-group">
					<button type="submit" id="generate-btn" class="btn-primary">
						<span class="btn-text">Generate Proposal</span>
						<span class="btn-spinner" style="display: none;">Generating...</span>
					</button>
				</div>

				<div id="status-message" class="status-message" style="display: none;"></div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Enqueue assets for the generator form
	 */
	public static function enqueue_assets() {
		wp_enqueue_script(
			'ngoinfo-copilot-generator',
			NGOINFO_COPILOT_PLUGIN_URL . 'assets/js/generator.js',
			array( 'jquery' ),
			NGOINFO_COPILOT_VERSION,
			true
		);

		wp_localize_script(
			'ngoinfo-copilot-generator',
			'NGOInfoCopilotGen',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ngoinfo_copilot_generate' ),
				'msgs'    => array(
					'generating' => __( 'Generating proposal...', 'ngoinfo-copilot' ),
					'success'    => __( 'Proposal generated successfully!', 'ngoinfo-copilot' ),
					'error'      => __( 'An error occurred. Please try again.', 'ngoinfo-copilot' ),
					'auth'       => __( 'Please log in to use Grantpilot.', 'ngoinfo-copilot' ),
					'plan'       => __( 'Grantpilot plan required.', 'ngoinfo-copilot' ),
					'rate'       => __( 'Please wait a moment before trying again.', 'ngoinfo-copilot' ),
					'api'        => __( 'Service error. Please try again later.', 'ngoinfo-copilot' ),
				),
			)
		);

		wp_enqueue_style(
			'ngoinfo-copilot-generator',
			NGOINFO_COPILOT_PLUGIN_URL . 'assets/css/generator.css',
			array(),
			NGOINFO_COPILOT_VERSION
		);
	}

	/**
	 * Check if user can generate proposals
	 *
	 * @param int $user_id User ID.
	 * @return bool True if user can generate.
	 */
	public static function user_can_generate( $user_id ) {
		// Check if MemberPress is active
		if ( ! function_exists( 'memberpress_get_active_memberships' ) ) {
			ngoinfo_copilot_log( 'MemberPress not active', 'error' );
			return false;
		}

		// Get user's plan tier using the auth class
		$auth = new NGOInfo_Copilot_Auth();
		$user = get_user_by( 'ID', $user_id );
		
		if ( ! $user ) {
			ngoinfo_copilot_log( "Invalid user ID: {$user_id}", 'error' );
			return false;
		}

		// Use reflection to access private method
		$reflection = new ReflectionClass( $auth );
		$method = $reflection->getMethod( 'get_user_plan_tier' );
		$method->setAccessible( true );
		$plan_tier = $method->invoke( $auth, $user );

		// Allow generation for all tiers (FREE, GROWTH, IMPACT)
		$allowed_tiers = array( 'FREE', 'GROWTH', 'IMPACT' );
		
		if ( ! in_array( $plan_tier, $allowed_tiers, true ) ) {
			ngoinfo_copilot_log( "User {$user_id} has invalid plan tier: {$plan_tier}", 'info' );
			return false;
		}

		// Check rate limiting
		if ( ! self::rate_limit_check( $user_id ) ) {
			ngoinfo_copilot_log( "User {$user_id} rate limited", 'info' );
			return false;
		}

		// For FREE tier, check 24h expiry rule
		if ( 'FREE' === $plan_tier ) {
			$has_free_access_method = $reflection->getMethod( 'has_free_plan_access' );
			$has_free_access_method->setAccessible( true );
			$has_free_access = $has_free_access_method->invoke( $auth, $user_id );
			
			if ( ! $has_free_access ) {
				ngoinfo_copilot_log( "User {$user_id} FREE tier access expired", 'info' );
				return false;
			}
		}

		return true;
	}

	/**
	 * Check rate limiting for user
	 *
	 * @param int $user_id User ID.
	 * @return bool True if user can proceed.
	 */
	public static function rate_limit_check( $user_id ) {
		$last_gen = get_user_meta( $user_id, '_ngoinfo_copilot_last_gen_at', true );
		
		if ( empty( $last_gen ) ) {
			return true;
		}

		$cooldown = NGOInfo_Copilot_Settings::get_cooldown_secs();
		$time_since_last = time() - intval( $last_gen );

		return $time_since_last >= $cooldown;
	}

	/**
	 * Mark rate limit for user
	 *
	 * @param int $user_id User ID.
	 */
	public static function rate_limit_mark( $user_id ) {
		update_user_meta( $user_id, '_ngoinfo_copilot_last_gen_at', time() );
	}

	/**
	 * Mint JWT token for user
	 *
	 * @param WP_User $user WordPress user.
	 * @return string|false JWT token or false on failure.
	 */
	public static function mint_jwt( $user ) {
		return NGOInfo_Copilot_JWT_Helper::mint_grantpilot_token( $user );
	}

	/**
	 * Add generation to user history
	 *
	 * @param int   $user_id User ID.
	 * @param array $data Generation data.
	 */
	public static function add_to_history( $user_id, $data ) {
		$history = get_user_meta( $user_id, '_ngoinfo_copilot_history', true );
		if ( ! is_array( $history ) ) {
			$history = array();
		}

		// Add new entry
		$entry = array_merge( $data, array( 'ts' => time() ) );
		array_unshift( $history, $entry );

		// Keep only last 20 entries
		$history = array_slice( $history, 0, 20 );

		update_user_meta( $user_id, '_ngoinfo_copilot_history', $history );
	}

	/**
	 * Get user generation history
	 *
	 * @param int $user_id User ID.
	 * @return array User history.
	 */
	public static function get_user_history( $user_id ) {
		$history = get_user_meta( $user_id, '_ngoinfo_copilot_history', true );
		return is_array( $history ) ? $history : array();
	}
}
