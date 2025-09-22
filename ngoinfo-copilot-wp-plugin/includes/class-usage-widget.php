<?php
/**
 * Usage widget for NGOInfo Copilot
 *
 * @package NGOInfo_Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Usage Widget class
 */
class NGOInfo_Copilot_Usage_Widget {

	/**
	 * API Client instance
	 *
	 * @var NGOInfo_Copilot_Api_Client
	 */
	private $api_client;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->api_client = new NGOInfo_Copilot_Api_Client();
		
		// Register shortcode
		add_shortcode( 'ngoinfo_copilot_usage', array( $this, 'render_shortcode' ) );
		
		// Add AJAX handlers for widget refresh
		add_action( 'wp_ajax_ngoinfo_copilot_refresh_usage', array( $this, 'ajax_refresh_usage' ) );
		add_action( 'wp_ajax_nopriv_ngoinfo_copilot_refresh_usage', array( $this, 'ajax_refresh_usage_nopriv' ) );
	}

	/**
	 * Render usage widget shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Widget HTML.
	 */
	public function render_shortcode( $atts = array() ) {
		// Parse attributes
		$atts = shortcode_atts(
			array(
				'theme'        => 'default',
				'show_refresh' => 'true',
				'cache_time'   => '300', // 5 minutes default cache
			),
			$atts,
			'ngoinfo_copilot_usage'
		);

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return $this->render_login_prompt();
		}

		$current_user = wp_get_current_user();
		
		// Get cached usage data or fetch from API
		$usage_data = $this->get_usage_data( $current_user, intval( $atts['cache_time'] ) );

		// Generate widget ID for JavaScript
		$widget_id = 'ngoinfo-usage-' . wp_rand( 1000, 9999 );

		// Enqueue scripts if not already done
		if ( ! wp_script_is( 'ngoinfo-copilot-public', 'enqueued' ) ) {
			wp_enqueue_script(
				'ngoinfo-copilot-public',
				NGOINFO_COPILOT_PLUGIN_URL . 'assets/js/public.js',
				array( 'jquery' ),
				NGOINFO_COPILOT_VERSION,
				true
			);
		}

		// Localize script for AJAX
		wp_localize_script(
			'ngoinfo-copilot-public',
			'ngoinfo_copilot_public',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ngoinfo_copilot_public' ),
			)
		);

		// Render widget
		ob_start();
		$usage_widget = $this; // Pass current instance to template
		require NGOINFO_COPILOT_PLUGIN_DIR . 'public/views/usage-widget.php';
		return ob_get_clean();
	}

	/**
	 * Get usage data with caching
	 *
	 * @param WP_User $user User to get usage for.
	 * @param int     $cache_time Cache time in seconds.
	 * @return array Usage data.
	 */
	private function get_usage_data( $user, $cache_time = 300 ) {
		$cache_key = 'ngoinfo_usage_' . $user->ID;
		$cached_data = get_transient( $cache_key );

		// Return cached data if valid
		if ( false !== $cached_data && isset( $cached_data['timestamp'] ) ) {
			$age = time() - $cached_data['timestamp'];
			if ( $age < $cache_time ) {
				return $cached_data;
			}
		}

		// Fetch fresh data from API
		$api_response = $this->api_client->get_usage_summary( $user );
		
		$usage_data = array(
			'timestamp' => time(),
			'success'   => false,
			'data'      => null,
			'error'     => null,
		);

		if ( false !== $api_response && $api_response['success'] ) {
			$usage_data['success'] = true;
			$usage_data['data'] = $api_response['data'];
		} elseif ( false !== $api_response && isset( $api_response['error'] ) ) {
			$usage_data['error'] = $api_response['error'];
		} else {
			$usage_data['error'] = array(
				'code'    => 'API_UNAVAILABLE',
				'message' => __( 'API service is currently unavailable.', 'ngoinfo-copilot' ),
			);
		}

		// Cache the result
		set_transient( $cache_key, $usage_data, $cache_time );

		return $usage_data;
	}

	/**
	 * Render login prompt for unauthenticated users
	 *
	 * @return string Login prompt HTML.
	 */
	private function render_login_prompt() {
		$login_url = wp_login_url( get_permalink() );
		
		return sprintf(
			'<div class="ngoinfo-usage-widget ngoinfo-login-prompt">
				<div class="usage-header">
					<h4>%1$s</h4>
				</div>
				<div class="usage-content">
					<p>%2$s</p>
					<a href="%3$s" class="usage-login-button">%4$s</a>
				</div>
			</div>',
			esc_html__( 'NGO Copilot Usage', 'ngoinfo-copilot' ),
			esc_html__( 'Please log in to view your proposal generation usage and limits.', 'ngoinfo-copilot' ),
			esc_url( $login_url ),
			esc_html__( 'Log In', 'ngoinfo-copilot' )
		);
	}

	/**
	 * AJAX handler for refreshing usage data
	 */
	public function ajax_refresh_usage() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'ngoinfo_copilot_public' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ngoinfo-copilot' ) ) );
		}

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in to refresh usage data.', 'ngoinfo-copilot' ) ) );
		}

		$current_user = wp_get_current_user();
		
		// Clear cache and get fresh data
		delete_transient( 'ngoinfo_usage_' . $current_user->ID );
		$usage_data = $this->get_usage_data( $current_user, 0 ); // No cache for refresh

		if ( $usage_data['success'] ) {
			wp_send_json_success( $usage_data );
		} else {
			wp_send_json_error( $usage_data );
		}
	}

	/**
	 * AJAX handler for non-logged-in users
	 */
	public function ajax_refresh_usage_nopriv() {
		wp_send_json_error( array( 'message' => __( 'You must be logged in to access usage data.', 'ngoinfo-copilot' ) ) );
	}

	/**
	 * Format usage data for display
	 *
	 * @param array $data Usage data from API.
	 * @return array Formatted data.
	 */
	public function format_usage_data( $data ) {
		if ( empty( $data ) ) {
			return array(
				'plan'          => __( 'Unknown', 'ngoinfo-copilot' ),
				'used'          => 0,
				'monthly_limit' => 0,
				'remaining'     => 0,
				'reset_at'      => '',
				'usage_percent' => 0,
				'status'        => 'unknown',
			);
		}

		$used = isset( $data['used'] ) ? intval( $data['used'] ) : 0;
		$monthly_limit = isset( $data['monthly_limit'] ) ? intval( $data['monthly_limit'] ) : 0;
		$remaining = max( 0, $monthly_limit - $used );
		
		$usage_percent = 0;
		if ( $monthly_limit > 0 ) {
			$usage_percent = min( 100, round( ( $used / $monthly_limit ) * 100 ) );
		}

		// Determine status based on usage
		$status = 'normal';
		if ( $usage_percent >= 100 ) {
			$status = 'limit_reached';
		} elseif ( $usage_percent >= 80 ) {
			$status = 'warning';
		}

		// Format reset date
		$reset_at = '';
		if ( isset( $data['reset_at'] ) ) {
			$reset_timestamp = strtotime( $data['reset_at'] );
			if ( $reset_timestamp ) {
				$reset_at = date_i18n( get_option( 'date_format' ), $reset_timestamp );
			}
		}

		return array(
			'plan'          => isset( $data['plan'] ) ? sanitize_text_field( $data['plan'] ) : __( 'Free Plan', 'ngoinfo-copilot' ),
			'used'          => $used,
			'monthly_limit' => $monthly_limit,
			'remaining'     => $remaining,
			'reset_at'      => $reset_at,
			'usage_percent' => $usage_percent,
			'status'        => $status,
		);
	}
}








