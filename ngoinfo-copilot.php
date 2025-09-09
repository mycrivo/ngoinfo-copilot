<?php
/**
 * Plugin Name: NGOInfo Copilot
 * Plugin URI: https://github.com/mycrivo/ngoinfo-copilot-wp
 * Description: WordPress plugin for NGOInfo Copilot - AI-powered proposal generation for NGOs. Integrates with FastAPI backend for secure JWT authentication, usage tracking, and proposal management.
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
 * @package NGOInfo\Copilot
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
		// Load autoloader
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-autoloader.php';
		NGOInfo\Copilot\Autoloader::register();

		// Load text domain
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Initialize components
		add_action( 'init', array( $this, 'init_components' ) );

		// Activation and deactivation hooks
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
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
		new NGOInfo\Copilot\Settings();

		// Initialize health panel
		new NGOInfo\Copilot\Health();

		// Initialize usage widget
		new NGOInfo\Copilot\Usage_Widget();

		// Load admin assets
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		}

		// Load public assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		// Only load on our settings pages
		if ( strpos( $hook_suffix, 'ngoinfo-copilot' ) === false ) {
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
			)
		);
	}

	/**
	 * Enqueue public scripts and styles
	 */
	public function enqueue_public_scripts() {
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
	 * Plugin activation
	 */
	public function activate() {
		// Create default options
		$default_options = array(
			'api_base_url'      => '',
			'jwt_issuer'        => 'ngoinfo-wp',
			'jwt_audience'      => 'ngoinfo-copilot',
			'jwt_expiry'        => 15,
			'jwt_secret'        => '',
			'environment'       => 'staging',
			'last_health_check' => '',
			'last_error'        => '',
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




