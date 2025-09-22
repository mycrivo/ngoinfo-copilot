<?php
/**
 * Plugin Name: NGOInfo Copilot
 * Plugin URI: https://ngoinfo.org
 * Description: AI-powered proposal generation for NGOs. Integrates with NGOInfo Copilot backend API for secure authentication, usage tracking, and intelligent proposal creation.
 * Version: 0.1.0
 * Author: NGOInfo Copilot Team
 * Author URI: https://ngoinfo.org
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: ngoinfo-copilot
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package NGOInfo_Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'NGOINFO_COPILOT_VERSION', '0.1.0' );
define( 'NGOINFO_COPILOT_PLUGIN_FILE', __FILE__ );
define( 'NGOINFO_COPILOT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NGOINFO_COPILOT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NGOINFO_COPILOT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
class NGOInfo_Copilot {

	/**
	 * Plugin instance
	 *
	 * @var NGOInfo_Copilot
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return NGOInfo_Copilot
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize plugin
	 */
	private function init() {
		// Load required files
		$this->load_dependencies();

		// Load text domain
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Initialize components
		add_action( 'init', array( $this, 'init_components' ) );

		// Activation and deactivation hooks
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Load plugin dependencies
	 */
	private function load_dependencies() {
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/helpers.php';
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-settings.php';
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-health.php';
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-auth.php';
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-api-client.php';
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-usage-widget.php';
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-diagnostics.php';
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-jwt-helper.php';
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-generator-service.php';
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-ajax-controller.php';
	}

	/**
	 * Load plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'ngoinfo-copilot',
			false,
			dirname( NGOINFO_COPILOT_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Initialize plugin components
	 */
	public function init_components() {
		// Initialize settings
		new NGOInfo_Copilot_Settings();

		// Initialize health panel
		new NGOInfo_Copilot_Health();

		// Initialize usage widget
		new NGOInfo_Copilot_Usage_Widget();

		// Initialize AJAX controller
		new NGOInfo_Copilot_Ajax_Controller();

		// Register shortcodes
		add_shortcode( 'ngoinfo_copilot_generate', array( $this, 'render_generate_shortcode' ) );

		// Load admin assets
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		}

		// Load public assets conditionally
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		// Only load on our settings pages
		if ( 'settings_page_ngoinfo-copilot' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'ngoinfo-copilot-admin',
			NGOINFO_COPILOT_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			NGOINFO_COPILOT_VERSION
		);

		wp_enqueue_script(
			'ngoinfo-copilot-admin',
			NGOINFO_COPILOT_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			NGOINFO_COPILOT_VERSION,
			true
		);

		// Localize script for AJAX
		wp_localize_script(
			'ngoinfo-copilot-admin',
			'ngoinfo_copilot_admin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ngoinfo_copilot_admin' ),
				'strings'  => array(
					'checking'  => __( 'Checking...', 'ngoinfo-copilot' ),
					'run_check' => __( 'Run Health Check', 'ngoinfo-copilot' ),
				),
			)
		);
	}

	/**
	 * Enqueue public scripts and styles
	 */
	public function enqueue_public_scripts() {
		// Only enqueue if the current post contains the shortcode or is the grantpilot page
		if ( $this->should_enqueue_generator_assets() ) {
			NGOInfo_Copilot_Generator_Service::enqueue_assets();
		}

		// Always enqueue basic public styles
		wp_enqueue_style(
			'ngoinfo-copilot-public',
			NGOINFO_COPILOT_PLUGIN_URL . 'assets/css/public.css',
			array(),
			NGOINFO_COPILOT_VERSION
		);

		wp_enqueue_script(
			'ngoinfo-copilot-public',
			NGOINFO_COPILOT_PLUGIN_URL . 'assets/js/public.js',
			array( 'jquery' ),
			NGOINFO_COPILOT_VERSION,
			true
		);
	}

	/**
	 * Check if generator assets should be enqueued
	 *
	 * @return bool True if assets should be enqueued.
	 */
	private function should_enqueue_generator_assets() {
		global $post;

		// Check if current post contains the shortcode
		if ( $post && has_shortcode( $post->post_content, 'ngoinfo_copilot_generate' ) ) {
			return true;
		}

		// Check if current page is 'grantpilot'
		if ( is_page( 'grantpilot' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Render the generate shortcode
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string Shortcode output.
	 */
	public function render_generate_shortcode( $atts, $content = '' ) {
		return NGOInfo_Copilot_Generator_Service::render_form();
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		// Create default options
		$default_options = array(
			'api_base_url'           => '',
			'jwt_iss'                => 'ngoinfo-wp',
			'jwt_aud'                => 'grantpilot-api',
			'jwt_secret'             => '',
			'memberpress_free_ids'   => '2268',
			'memberpress_growth_ids' => '2259,2271',
			'memberpress_impact_ids' => '2272,2273',
			'http_timeout'           => 60,
			'cooldown_secs'          => 60,
			'environment'            => 'staging',
			'last_health_check'      => '',
			'last_error'             => '',
		);

		foreach ( $default_options as $option => $value ) {
			if ( false === get_option( "ngoinfo_copilot_{$option}" ) ) {
				add_option( "ngoinfo_copilot_{$option}", $value );
			}
		}

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}

// Initialize plugin
NGOInfo_Copilot::get_instance();








