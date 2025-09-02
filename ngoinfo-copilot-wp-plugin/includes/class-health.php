<?php
/**
 * Health check functionality for NGOInfo Copilot
 *
 * @package NGOInfo\Copilot
 */

namespace NGOInfo\Copilot;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Health class
 */
class Health {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_ngoinfo_copilot_health_check', array( $this, 'ajax_health_check' ) );
		add_action( 'admin_post_ngoinfo_copilot_health_check', array( $this, 'admin_health_check' ) );
		add_action( 'admin_post_ngoinfo_copilot_auth_test', array( $this, 'admin_auth_test' ) );
	}

	/**
	 * AJAX health check handler
	 */
	public function ajax_health_check() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'ngoinfo_copilot_admin' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'ngoinfo-copilot' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'ngoinfo-copilot' ) );
		}

		$result = $this->run_health_check();

		wp_send_json( $result );
	}

	/**
	 * Admin health check handler
	 */
	public function admin_health_check() {
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'ngoinfo-copilot' ) );
		}

		$api_client = new Api_Client();
		$current_user = wp_get_current_user();
		
		// Test GET /health (no auth)
		$health_result = $this->run_health_check();
		
		// Test GET /api/usage (with auth)
		$usage_result = $api_client->get_usage_summary( $current_user );
		
		// Build admin notice
		$notice_type = 'info';
		$notice_message = '<h4>NGOInfo Copilot Health Check Results</h4>';
		
		// Health endpoint results
		$notice_message .= '<h5>Health Endpoint (/healthcheck - no auth):</h5>';
		$notice_message .= '<p><strong>Status Code:</strong> ' . esc_html( $health_result['status_code'] ) . '</p>';
		if ( $health_result['success'] ) {
			$notice_message .= '<p><strong>Response:</strong> ' . esc_html( substr( wp_json_encode( $health_result['response'] ), 0, 200 ) ) . '</p>';
		} else {
			$notice_message .= '<p><strong>Error:</strong> ' . esc_html( $health_result['error'] ) . '</p>';
		}
		
		// Usage endpoint results
		$notice_message .= '<h5>Usage Endpoint (/api/usage):</h5>';
		if ( false !== $usage_result ) {
			$notice_message .= '<p><strong>Status Code:</strong> ' . esc_html( $usage_result['status_code'] ) . '</p>';
			if ( $usage_result['success'] ) {
				$notice_message .= '<p><strong>Response:</strong> ' . esc_html( substr( wp_json_encode( $usage_result['data'] ), 0, 200 ) ) . '</p>';
			} else {
				$notice_message .= '<p><strong>Error:</strong> ' . esc_html( $usage_result['error']['message'] ) . '</p>';
			}
		} else {
			$notice_message .= '<p><strong>Error:</strong> API request failed</p>';
		}
		
		// Set notice type based on results
		if ( $health_result['success'] && ( false !== $usage_result && $usage_result['success'] ) ) {
			$notice_type = 'success';
		} elseif ( ! $health_result['success'] || ( false === $usage_result || ! $usage_result['success'] ) ) {
			$notice_type = 'error';
		}
		
		// Add admin notice
		ngoinfo_copilot_admin_notice( $notice_message, $notice_type, false );
		
		// Redirect back to admin
		wp_redirect( admin_url( 'admin.php?page=ngoinfo-copilot-settings' ) );
		exit;
	}

	/**
	 * Admin auth test handler
	 */
	public function admin_auth_test() {
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'ngoinfo-copilot' ) );
		}

		$current_user = wp_get_current_user();
		$auth = new Auth();
		$api_client = new Api_Client();
		
		// Build JWT exactly as used in normal requests
		$jwt = $auth->mint_user_jwt( $current_user );
		
		// Get JWT claims for display (without secret)
		$issuer = ngoinfo_copilot_get_option( 'jwt_issuer', 'ngoinfo-wp' );
		$audience = ngoinfo_copilot_get_option( 'jwt_audience', 'ngoinfo-copilot' );
		$expiry = ngoinfo_copilot_get_option( 'jwt_expiry', 15 );
		$current_time = time();
		
		$claims = array(
			'email' => $current_user->user_email,
			'iss'   => $issuer,
			'aud'   => $audience,
			'iat'   => $current_time,
			'exp'   => $current_time + ( $expiry * 60 ),
			'now'   => $current_time,
		);
		
		// Log auth claims
		ngoinfo_log( 'auth.claims' );
		ngoinfo_log( $claims );
		
		// Test GET /health (no auth)
		$health_result = $this->run_health_check();
		
		// Test GET /api/usage/summary with auth
		$usage_result = $api_client->get_usage_summary( $current_user );
		
		// Build admin notice
		$notice_type = 'info';
		$notice_message = '<h4>NGOInfo Copilot Auth Self-Test Results</h4>';
		
		// JWT Information
		$notice_message .= '<h5>JWT Token Information:</h5>';
		$notice_message .= '<p><strong>Claims:</strong> ' . esc_html( wp_json_encode( $claims ) ) . '</p>';
		$notice_message .= '<p><strong>Token Length:</strong> ' . esc_html( strlen( $jwt ) ) . ' characters</p>';
		$notice_message .= '<p><strong>Token Prefix:</strong> ' . esc_html( substr( $jwt, 0, 12 ) ) . '...</p>';
		
		// Health endpoint results
		$notice_message .= '<h5>Health Endpoint (/healthcheck - no auth):</h5>';
		$notice_message .= '<p><strong>Status Code:</strong> ' . esc_html( $health_result['status_code'] ) . '</p>';
		if ( $health_result['success'] ) {
			$notice_message .= '<p><strong>Response:</strong> ' . esc_html( substr( wp_json_encode( $health_result['response'] ), 0, 200 ) ) . '</p>';
		} else {
			$notice_message .= '<p><strong>Error:</strong> ' . esc_html( $health_result['error'] ) . '</p>';
		}
		
		// Usage endpoint results
		$notice_message .= '<h5>Usage Endpoint (/api/usage/summary):</h5>';
		if ( false !== $usage_result ) {
			$api_base_url = ngoinfo_copilot_get_option( 'api_base_url' );
			$final_url = rtrim( $api_base_url, '/' ) . '/api/usage/summary';
			$notice_message .= '<p><strong>Final URL:</strong> ' . esc_html( $final_url ) . '</p>';
			$notice_message .= '<p><strong>Status Code:</strong> ' . esc_html( $usage_result['status_code'] ) . '</p>';
			$notice_message .= '<p><strong>Authorization Header:</strong> ' . ( isset( $usage_result['has_auth'] ) ? 'Present' : 'Not present' ) . '</p>';
			if ( $usage_result['success'] ) {
				$notice_message .= '<p><strong>Response:</strong> ' . esc_html( substr( wp_json_encode( $usage_result['data'] ), 0, 200 ) ) . '</p>';
			} else {
				$notice_message .= '<p><strong>Error:</strong> ' . esc_html( $usage_result['error']['message'] ) . '</p>';
			}
		} else {
			$notice_message .= '<p><strong>Error:</strong> API request failed</p>';
		}
		
		// Set notice type based on results
		if ( $health_result['success'] && ( false !== $usage_result && $usage_result['success'] ) ) {
			$notice_type = 'success';
		} elseif ( ! $health_result['success'] || ( false === $usage_result || ! $usage_result['success'] ) ) {
			$notice_type = 'error';
		}
		
		// Add admin notice
		ngoinfo_copilot_admin_notice( $notice_message, $notice_type, false );
		
		// Redirect back to admin
		wp_redirect( admin_url( 'admin.php?page=ngoinfo-copilot-settings' ) );
		exit;
	}

	/**
	 * Run health check
	 *
	 * @return array Health check results.
	 */
	public function run_health_check() {
		$api_base_url = ngoinfo_copilot_get_option( 'api_base_url' );
		
		if ( empty( $api_base_url ) ) {
			return array(
				'success'     => false,
				'message'     => __( 'API Base URL not configured.', 'ngoinfo-copilot' ),
				'status_code' => 0,
				'response'    => null,
				'duration'    => 0,
				'error'       => 'Configuration error',
			);
		}

		$start_time = microtime( true );
		
		$response = wp_remote_get(
			rtrim( $api_base_url, '/' ) . '/healthcheck',
			array(
				'timeout'     => 30,
				'redirection' => 0,
				'httpversion' => '1.1',
				'headers'     => array(
					'User-Agent' => 'NGOInfo-Copilot-WP/' . NGOINFO_COPILOT_VERSION,
					'Accept'     => 'application/json',
				),
			)
		);

		$end_time = microtime( true );
		$duration = round( ( $end_time - $start_time ) * 1000 ); // Convert to milliseconds

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			
			// Store error info
			ngoinfo_copilot_update_option( 'last_error', $error_message );
			ngoinfo_copilot_update_option( 'last_health_check', '' );

			// Log error (with sensitive data redacted)
			ngoinfo_copilot_log( 'Health check failed: ' . ngoinfo_copilot_redact_sensitive( $error_message ), 'error' );

			return array(
				'success'     => false,
				'message'     => sprintf( __( 'Connection failed: %s', 'ngoinfo-copilot' ), $error_message ),
				'status_code' => 0,
				'response'    => null,
				'duration'    => $duration,
				'error'       => $error_message,
			);
		}

		$status_code    = wp_remote_retrieve_response_code( $response );
		$response_body  = wp_remote_retrieve_body( $response );
		$parsed_response = json_decode( $response_body, true );

		// Log health response
		ngoinfo_copilot_log( 'health.response', array(
			'status' => $status_code,
			'body'   => substr( $response_body, 0, 120 ),
		) );

		// Store health check results
		$timestamp = current_time( 'mysql' );
		ngoinfo_copilot_update_option( 'last_health_check', $timestamp );

		if ( 200 === $status_code ) {
			// Clear any previous errors
			ngoinfo_copilot_update_option( 'last_error', '' );

			return array(
				'success'     => true,
				'message'     => __( 'Health check successful!', 'ngoinfo-copilot' ),
				'status_code' => $status_code,
				'response'    => $parsed_response,
				'duration'    => $duration,
				'error'       => null,
			);
		} else {
			$error_message = sprintf( 'HTTP %d: %s', $status_code, wp_remote_retrieve_response_message( $response ) );
			
			// Extract request ID if available
			$request_id = '';
			if ( $parsed_response && isset( $parsed_response['request_id'] ) ) {
				$request_id = $parsed_response['request_id'];
				$error_message .= ' (Request ID: ' . $request_id . ')';
			}

			// Store error info
			ngoinfo_copilot_update_option( 'last_error', $error_message );

			// Log error
			ngoinfo_copilot_log( 'Health check returned error: ' . $error_message, 'error' );

			return array(
				'success'     => false,
				'message'     => sprintf( __( 'Health check failed: %s', 'ngoinfo-copilot' ), $error_message ),
				'status_code' => $status_code,
				'response'    => $parsed_response,
				'duration'    => $duration,
				'error'       => $error_message,
			);
		}
	}

	/**
	 * Get health status display data
	 *
	 * @return array Health status information.
	 */
	public function get_health_status() {
		$last_check = ngoinfo_copilot_get_option( 'last_health_check' );
		$last_error = ngoinfo_copilot_get_option( 'last_error' );
		$api_base_url = ngoinfo_copilot_get_option( 'api_base_url' );

		$status = 'unknown';
		if ( ! empty( $last_check ) && empty( $last_error ) ) {
			$status = 'healthy';
		} elseif ( ! empty( $last_error ) ) {
			$status = 'error';
		}

		return array(
			'status'          => $status,
			'last_check'      => $last_check,
			'last_error'      => $last_error,
			'api_base_url'    => $api_base_url,
			'has_api_config'  => ! empty( $api_base_url ),
			'has_jwt_secret'  => ! empty( ngoinfo_copilot_get_option( 'jwt_secret' ) ),
		);
	}

	/**
	 * Format health check response for display
	 *
	 * @param array $response Health check response.
	 * @return string Formatted response.
	 */
	public function format_response( $response ) {
		if ( empty( $response ) ) {
			return __( 'No response data', 'ngoinfo-copilot' );
		}

		$formatted = array();

		// Add main status
		if ( isset( $response['status'] ) ) {
			$formatted[] = sprintf( '<strong>Status:</strong> %s', esc_html( $response['status'] ) );
		}

		// Add database status
		if ( isset( $response['db'] ) ) {
			$db_icon = 'ok' === $response['db'] ? '✓' : '✗';
			$formatted[] = sprintf( '<strong>Database:</strong> %s %s', $db_icon, esc_html( $response['db'] ) );
		}

		// Add service info
		if ( isset( $response['service'] ) ) {
			$formatted[] = sprintf( '<strong>Service:</strong> %s', esc_html( $response['service'] ) );
		}

		// Add version
		if ( isset( $response['version'] ) ) {
			$formatted[] = sprintf( '<strong>Version:</strong> %s', esc_html( $response['version'] ) );
		}

		// Add timestamp
		if ( isset( $response['timestamp'] ) ) {
			$formatted[] = sprintf( '<strong>Timestamp:</strong> %s', esc_html( $response['timestamp'] ) );
		}

		return implode( '<br>', $formatted );
	}
}

