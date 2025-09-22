<?php
/**
 * Settings management for NGOInfo Copilot
 *
 * @package NGOInfo_Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class
 */
class NGOInfo_Copilot_Settings {

	/**
	 * Settings page slug
	 *
	 * @var string
	 */
	private $page_slug = 'ngoinfo-copilot';

	/**
	 * Settings group
	 *
	 * @var string
	 */
	private $settings_group = 'ngoinfo_copilot_settings';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'NGOInfo Copilot Settings', 'ngoinfo-copilot' ),
			__( 'NGOInfo Copilot', 'ngoinfo-copilot' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		// Register setting group
		register_setting(
			$this->settings_group,
			'ngoinfo_copilot_settings',
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		// API Configuration section
		add_settings_section(
			'api_config',
			__( 'API Configuration', 'ngoinfo-copilot' ),
			array( $this, 'render_api_config_section' ),
			$this->page_slug
		);

		// API Base URL
		add_settings_field(
			'api_base_url',
			__( 'API Base URL', 'ngoinfo-copilot' ),
			array( $this, 'render_api_base_url_field' ),
			$this->page_slug,
			'api_config'
		);

		// Environment
		add_settings_field(
			'environment',
			__( 'Environment', 'ngoinfo-copilot' ),
			array( $this, 'render_environment_field' ),
			$this->page_slug,
			'api_config'
		);

		// JWT Configuration section
		add_settings_section(
			'jwt_config',
			__( 'JWT Configuration', 'ngoinfo-copilot' ),
			array( $this, 'render_jwt_config_section' ),
			$this->page_slug
		);

		// JWT Secret
		add_settings_field(
			'jwt_secret',
			__( 'JWT Signing Secret', 'ngoinfo-copilot' ),
			array( $this, 'render_jwt_secret_field' ),
			$this->page_slug,
			'jwt_config'
		);

		// JWT Issuer
		add_settings_field(
			'jwt_iss',
			__( 'JWT Issuer', 'ngoinfo-copilot' ),
			array( $this, 'render_jwt_iss_field' ),
			$this->page_slug,
			'jwt_config'
		);

		// JWT Audience
		add_settings_field(
			'jwt_aud',
			__( 'JWT Audience', 'ngoinfo-copilot' ),
			array( $this, 'render_jwt_aud_field' ),
			$this->page_slug,
			'jwt_config'
		);

		// Grantpilot Configuration section
		add_settings_section(
			'grantpilot_config',
			__( 'Grantpilot Configuration', 'ngoinfo-copilot' ),
			array( $this, 'render_grantpilot_config_section' ),
			$this->page_slug
		);

		// MemberPress Plan Mapping
		add_settings_field(
			'memberpress_free_ids',
			__( 'Free Plan Membership IDs', 'ngoinfo-copilot' ),
			array( $this, 'render_memberpress_free_ids_field' ),
			$this->page_slug,
			'grantpilot_config'
		);

		add_settings_field(
			'memberpress_growth_ids',
			__( 'Growth Plan Membership IDs', 'ngoinfo-copilot' ),
			array( $this, 'render_memberpress_growth_ids_field' ),
			$this->page_slug,
			'grantpilot_config'
		);

		add_settings_field(
			'memberpress_impact_ids',
			__( 'Impact Plan Membership IDs', 'ngoinfo-copilot' ),
			array( $this, 'render_memberpress_impact_ids_field' ),
			$this->page_slug,
			'grantpilot_config'
		);

		// HTTP Timeout
		add_settings_field(
			'http_timeout',
			__( 'HTTP Timeout (seconds)', 'ngoinfo-copilot' ),
			array( $this, 'render_http_timeout_field' ),
			$this->page_slug,
			'grantpilot_config'
		);

		// Cooldown Seconds
		add_settings_field(
			'cooldown_secs',
			__( 'Rate Limit Cooldown (seconds)', 'ngoinfo-copilot' ),
			array( $this, 'render_cooldown_secs_field' ),
			$this->page_slug,
			'grantpilot_config'
		);
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ngoinfo-copilot' ) );
		}

		// Handle tab switching
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings';
		
		// Pass variables to template
		$page_slug = $this->page_slug;
		$settings_group = $this->settings_group;
		
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Render API config section
	 */
	public function render_api_config_section() {
		echo '<p>' . esc_html__( 'Configure connection to the NGOInfo Copilot backend API.', 'ngoinfo-copilot' ) . '</p>';
	}

	/**
	 * Render JWT config section
	 */
	public function render_jwt_config_section() {
		echo '<p>' . esc_html__( 'Configure JWT token settings for secure API authentication.', 'ngoinfo-copilot' ) . '</p>';
	}

	/**
	 * Render Grantpilot config section
	 */
	public function render_grantpilot_config_section() {
		echo '<p>' . esc_html__( 'Configure Grantpilot generator settings and MemberPress integration.', 'ngoinfo-copilot' ) . '</p>';
	}

	/**
	 * Render API base URL field
	 */
	public function render_api_base_url_field() {
		$value = ngoinfo_copilot_get_option( 'api_base_url' );
		?>
		<input type="url" 
			   id="api_base_url" 
			   name="ngoinfo_copilot_settings[api_base_url]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="regular-text" 
			   required />
		<p class="description">
			<?php esc_html_e( 'Full URL to the NGOInfo Copilot API (e.g., https://api.ngoinfo.org)', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Render environment field
	 */
	public function render_environment_field() {
		$value = ngoinfo_copilot_get_option( 'environment', 'staging' );
		?>
		<select id="environment" name="ngoinfo_copilot_settings[environment]">
			<option value="staging" <?php selected( $value, 'staging' ); ?>>
				<?php esc_html_e( 'Staging', 'ngoinfo-copilot' ); ?>
			</option>
			<option value="production" <?php selected( $value, 'production' ); ?>>
				<?php esc_html_e( 'Production', 'ngoinfo-copilot' ); ?>
			</option>
		</select>
		<p class="description">
			<?php esc_html_e( 'Select the environment that matches your API base URL.', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Render JWT secret field
	 */
	public function render_jwt_secret_field() {
		$has_secret = ! empty( ngoinfo_copilot_get_option( 'jwt_secret' ) );
		?>
		<input type="password" 
			   id="jwt_secret" 
			   name="ngoinfo_copilot_settings[jwt_secret]" 
			   value="" 
			   class="regular-text" 
			   autocomplete="new-password"
			   <?php echo $has_secret ? '' : 'required'; ?> />
		<?php if ( $has_secret ) : ?>
			<p class="description">
				<span class="dashicons dashicons-yes-alt" style="color: green;"></span>
				<?php esc_html_e( 'Secret is configured. Enter new value to change.', 'ngoinfo-copilot' ); ?>
			</p>
		<?php else : ?>
			<p class="description">
				<span class="dashicons dashicons-warning" style="color: orange;"></span>
				<?php esc_html_e( 'Required: Enter a strong secret (32+ characters with mixed case, numbers, and symbols).', 'ngoinfo-copilot' ); ?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render JWT issuer field
	 */
	public function render_jwt_iss_field() {
		$value = ngoinfo_copilot_get_option( 'jwt_iss', 'ngoinfo-wp' );
		?>
		<input type="text" 
			   id="jwt_iss" 
			   name="ngoinfo_copilot_settings[jwt_iss]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="regular-text" 
			   required />
		<p class="description">
			<?php esc_html_e( 'JWT issuer identifier (e.g., ngoinfo-wp)', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Render JWT audience field
	 */
	public function render_jwt_aud_field() {
		$value = ngoinfo_copilot_get_option( 'jwt_aud', 'grantpilot-api' );
		?>
		<input type="text" 
			   id="jwt_aud" 
			   name="ngoinfo_copilot_settings[jwt_aud]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="regular-text" 
			   required />
		<p class="description">
			<?php esc_html_e( 'JWT audience identifier (e.g., grantpilot-api)', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Render MemberPress Free Plan IDs field
	 */
	public function render_memberpress_free_ids_field() {
		$value = ngoinfo_copilot_get_option( 'memberpress_free_ids', '2268' );
		?>
		<input type="text" 
			   id="memberpress_free_ids" 
			   name="ngoinfo_copilot_settings[memberpress_free_ids]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="regular-text" 
			   required />
		<p class="description">
			<?php esc_html_e( 'Comma-separated MemberPress membership IDs for Free plan (e.g., 2268)', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Render MemberPress Growth Plan IDs field
	 */
	public function render_memberpress_growth_ids_field() {
		$value = ngoinfo_copilot_get_option( 'memberpress_growth_ids', '2259,2271' );
		?>
		<input type="text" 
			   id="memberpress_growth_ids" 
			   name="ngoinfo_copilot_settings[memberpress_growth_ids]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="regular-text" 
			   required />
		<p class="description">
			<?php esc_html_e( 'Comma-separated MemberPress membership IDs for Growth plan (e.g., 2259,2271)', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Render MemberPress Impact Plan IDs field
	 */
	public function render_memberpress_impact_ids_field() {
		$value = ngoinfo_copilot_get_option( 'memberpress_impact_ids', '2272,2273' );
		?>
		<input type="text" 
			   id="memberpress_impact_ids" 
			   name="ngoinfo_copilot_settings[memberpress_impact_ids]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="regular-text" 
			   required />
		<p class="description">
			<?php esc_html_e( 'Comma-separated MemberPress membership IDs for Impact plan (e.g., 2272,2273)', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Render HTTP timeout field
	 */
	public function render_http_timeout_field() {
		$value = ngoinfo_copilot_get_option( 'http_timeout', 60 );
		?>
		<input type="number" 
			   id="http_timeout" 
			   name="ngoinfo_copilot_settings[http_timeout]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="small-text" 
			   min="10" 
			   max="300" 
			   required />
		<p class="description">
			<?php esc_html_e( 'HTTP request timeout in seconds (10-300)', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Render cooldown seconds field
	 */
	public function render_cooldown_secs_field() {
		$value = ngoinfo_copilot_get_option( 'cooldown_secs', 60 );
		?>
		<input type="number" 
			   id="cooldown_secs" 
			   name="ngoinfo_copilot_settings[cooldown_secs]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="small-text" 
			   min="30" 
			   max="300" 
			   required />
		<p class="description">
			<?php esc_html_e( 'Rate limit cooldown in seconds (30-300)', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $input Input settings.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// Sanitize API base URL
		if ( isset( $input['api_base_url'] ) ) {
			$sanitized['api_base_url'] = ngoinfo_copilot_sanitize_url( $input['api_base_url'] );
		}

		// Sanitize environment
		if ( isset( $input['environment'] ) ) {
			$environment = sanitize_text_field( $input['environment'] );
			$sanitized['environment'] = in_array( $environment, array( 'staging', 'production' ), true ) ? $environment : 'staging';
		}

		// Sanitize JWT issuer
		if ( isset( $input['jwt_iss'] ) ) {
			$sanitized['jwt_iss'] = sanitize_text_field( $input['jwt_iss'] );
		}

		// Sanitize JWT audience
		if ( isset( $input['jwt_aud'] ) ) {
			$sanitized['jwt_aud'] = sanitize_text_field( $input['jwt_aud'] );
		}

		// Sanitize MemberPress plan mappings
		if ( isset( $input['memberpress_free_ids'] ) ) {
			$sanitized['memberpress_free_ids'] = sanitize_text_field( $input['memberpress_free_ids'] );
		}

		if ( isset( $input['memberpress_growth_ids'] ) ) {
			$sanitized['memberpress_growth_ids'] = sanitize_text_field( $input['memberpress_growth_ids'] );
		}

		if ( isset( $input['memberpress_impact_ids'] ) ) {
			$sanitized['memberpress_impact_ids'] = sanitize_text_field( $input['memberpress_impact_ids'] );
		}

		// Sanitize HTTP timeout
		if ( isset( $input['http_timeout'] ) ) {
			$timeout = intval( $input['http_timeout'] );
			$sanitized['http_timeout'] = max( 10, min( 300, $timeout ) );
		}

		// Sanitize cooldown seconds
		if ( isset( $input['cooldown_secs'] ) ) {
			$cooldown = intval( $input['cooldown_secs'] );
			$sanitized['cooldown_secs'] = max( 30, min( 300, $cooldown ) );
		}

		// Handle JWT secret
		if ( isset( $input['jwt_secret'] ) && ! empty( $input['jwt_secret'] ) ) {
			$secret = $input['jwt_secret'];
			
			// Validate secret strength
			if ( ! ngoinfo_copilot_validate_jwt_secret( $secret ) ) {
				add_settings_error(
					'ngoinfo_copilot_settings',
					'weak_secret',
					__( 'JWT secret must be at least 32 characters long and contain mixed case letters, numbers, and special characters.', 'ngoinfo-copilot' ),
					'error'
				);
			} else {
				// Encrypt and store secret
				$encrypted_secret = ngoinfo_copilot_encrypt( $secret );
				if ( false !== $encrypted_secret ) {
					$sanitized['jwt_secret'] = $encrypted_secret;
				} else {
					add_settings_error(
						'ngoinfo_copilot_settings',
						'encryption_failed',
						__( 'Failed to encrypt JWT secret. Please try again.', 'ngoinfo-copilot' ),
						'error'
					);
				}
			}
		}

		// Update individual options
		foreach ( $sanitized as $key => $value ) {
			ngoinfo_copilot_update_option( $key, $value );
		}

		// Add success message
		if ( empty( get_settings_errors( 'ngoinfo_copilot_settings' ) ) ) {
			add_settings_error(
				'ngoinfo_copilot_settings',
				'settings_updated',
				__( 'Settings saved successfully.', 'ngoinfo-copilot' ),
				'success'
			);
		}

		return $sanitized;
	}

	/**
	 * Display admin notices
	 */
	public function display_admin_notices() {
		settings_errors( 'ngoinfo_copilot_settings' );
	}

	/**
	 * Get status information for display
	 *
	 * @return array Status information.
	 */
	public function get_status_info() {
		$api_base_url = ngoinfo_copilot_get_option( 'api_base_url' );
		$environment  = ngoinfo_copilot_get_option( 'environment', 'staging' );
		$last_health  = ngoinfo_copilot_get_option( 'last_health_check' );
		$last_error   = ngoinfo_copilot_get_option( 'last_error' );

		return array(
			'api_base_url'      => $api_base_url,
			'environment'       => $environment,
			'last_health_check' => $last_health,
			'last_error'        => $last_error,
			'has_jwt_secret'    => ! empty( ngoinfo_copilot_get_option( 'jwt_secret' ) ),
		);
	}

	/**
	 * Get API base URL setting
	 *
	 * @return string API base URL.
	 */
	public static function get_api_base_url() {
		return ngoinfo_copilot_get_option( 'api_base_url', '' );
	}

	/**
	 * Get JWT secret setting
	 *
	 * @return string JWT secret.
	 */
	public static function get_jwt_secret() {
		$encrypted_secret = ngoinfo_copilot_get_option( 'jwt_secret' );
		if ( empty( $encrypted_secret ) ) {
			return '';
		}
		return ngoinfo_copilot_decrypt( $encrypted_secret );
	}

	/**
	 * Get JWT issuer setting
	 *
	 * @return string JWT issuer.
	 */
	public static function get_jwt_iss() {
		return ngoinfo_copilot_get_option( 'jwt_iss', 'ngoinfo-wp' );
	}

	/**
	 * Get JWT audience setting
	 *
	 * @return string JWT audience.
	 */
	public static function get_jwt_aud() {
		return ngoinfo_copilot_get_option( 'jwt_aud', 'grantpilot-api' );
	}

	/**
	 * Get MemberPress Free Plan IDs setting
	 *
	 * @return string MemberPress Free Plan IDs.
	 */
	public static function get_memberpress_free_ids() {
		return ngoinfo_copilot_get_option( 'memberpress_free_ids', '2268' );
	}

	/**
	 * Get MemberPress Growth Plan IDs setting
	 *
	 * @return string MemberPress Growth Plan IDs.
	 */
	public static function get_memberpress_growth_ids() {
		return ngoinfo_copilot_get_option( 'memberpress_growth_ids', '2259,2271' );
	}

	/**
	 * Get MemberPress Impact Plan IDs setting
	 *
	 * @return string MemberPress Impact Plan IDs.
	 */
	public static function get_memberpress_impact_ids() {
		return ngoinfo_copilot_get_option( 'memberpress_impact_ids', '2272,2273' );
	}

	/**
	 * Get HTTP timeout setting
	 *
	 * @return int HTTP timeout in seconds.
	 */
	public static function get_http_timeout() {
		return intval( ngoinfo_copilot_get_option( 'http_timeout', 60 ) );
	}

	/**
	 * Get cooldown seconds setting
	 *
	 * @return int Cooldown in seconds.
	 */
	public static function get_cooldown_secs() {
		return intval( ngoinfo_copilot_get_option( 'cooldown_secs', 60 ) );
	}

	/**
	 * Get setting by name with fallback
	 *
	 * @param string $name Setting name.
	 * @param mixed  $default Default value.
	 * @return mixed Setting value.
	 */
	public static function get( $name, $default = '' ) {
		switch ( $name ) {
			case 'api_base_url':
				return self::get_api_base_url();
			case 'jwt_secret':
				return self::get_jwt_secret();
			case 'jwt_iss':
				return self::get_jwt_iss();
			case 'jwt_aud':
				return self::get_jwt_aud();
			case 'memberpress_free_ids':
				return self::get_memberpress_free_ids();
			case 'memberpress_growth_ids':
				return self::get_memberpress_growth_ids();
			case 'memberpress_impact_ids':
				return self::get_memberpress_impact_ids();
			case 'http_timeout':
				return self::get_http_timeout();
			case 'cooldown_secs':
				return self::get_cooldown_secs();
			default:
				return ngoinfo_copilot_get_option( $name, $default );
		}
	}
}








