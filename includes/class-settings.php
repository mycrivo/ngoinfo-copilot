<?php
/**
 * Settings management for NGOInfo Copilot
 *
 * @package NGOInfo\Copilot
 */

namespace NGOInfo\Copilot;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class
 */
class Settings {

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

		// JWT Issuer
		add_settings_field(
			'jwt_issuer',
			__( 'JWT Issuer (iss)', 'ngoinfo-copilot' ),
			array( $this, 'render_jwt_issuer_field' ),
			$this->page_slug,
			'jwt_config'
		);

		// JWT Audience
		add_settings_field(
			'jwt_audience',
			__( 'JWT Audience (aud)', 'ngoinfo-copilot' ),
			array( $this, 'render_jwt_audience_field' ),
			$this->page_slug,
			'jwt_config'
		);

		// JWT Expiry
		add_settings_field(
			'jwt_expiry',
			__( 'JWT Expiry (minutes)', 'ngoinfo-copilot' ),
			array( $this, 'render_jwt_expiry_field' ),
			$this->page_slug,
			'jwt_config'
		);

		// JWT Secret
		add_settings_field(
			'jwt_secret',
			__( 'JWT Signing Secret', 'ngoinfo-copilot' ),
			array( $this, 'render_jwt_secret_field' ),
			$this->page_slug,
			'jwt_config'
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
	 * Render JWT issuer field
	 */
	public function render_jwt_issuer_field() {
		$value = ngoinfo_copilot_get_option( 'jwt_issuer', 'ngoinfo-wp' );
		?>
		<input type="text" 
			   id="jwt_issuer" 
			   name="ngoinfo_copilot_settings[jwt_issuer]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="regular-text" />
		<p class="description">
			<?php esc_html_e( 'JWT issuer claim (iss). Leave default unless instructed otherwise.', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Render JWT audience field
	 */
	public function render_jwt_audience_field() {
		$value = ngoinfo_copilot_get_option( 'jwt_audience', 'ngoinfo-copilot' );
		?>
		<input type="text" 
			   id="jwt_audience" 
			   name="ngoinfo_copilot_settings[jwt_audience]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="regular-text" />
		<p class="description">
			<?php esc_html_e( 'JWT audience claim (aud). Leave default unless instructed otherwise.', 'ngoinfo-copilot' ); ?>
		</p>
		<?php
	}

	/**
	 * Render JWT expiry field
	 */
	public function render_jwt_expiry_field() {
		$value = ngoinfo_copilot_get_option( 'jwt_expiry', 15 );
		?>
		<input type="number" 
			   id="jwt_expiry" 
			   name="ngoinfo_copilot_settings[jwt_expiry]" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   min="1" 
			   max="1440" 
			   class="small-text" />
		<p class="description">
			<?php esc_html_e( 'JWT token expiry time in minutes. Recommended: 15 minutes.', 'ngoinfo-copilot' ); ?>
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
		if ( isset( $input['jwt_issuer'] ) ) {
			$sanitized['jwt_issuer'] = sanitize_text_field( $input['jwt_issuer'] );
		}

		// Sanitize JWT audience
		if ( isset( $input['jwt_audience'] ) ) {
			$sanitized['jwt_audience'] = sanitize_text_field( $input['jwt_audience'] );
		}

		// Sanitize JWT expiry
		if ( isset( $input['jwt_expiry'] ) ) {
			$expiry = intval( $input['jwt_expiry'] );
			$sanitized['jwt_expiry'] = max( 1, min( 1440, $expiry ) );
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
}


