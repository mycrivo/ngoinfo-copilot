<?php
/**
 * Health check functionality for NGOInfo Copilot
 *
 * @package NGOInfo_Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Health class
 */
class NGOInfo_Copilot_Health {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_ngoinfo_copilot_health_check', array( $this, 'ajax_health_check' ) );
		add_action( 'wp_ajax_ngoinfo_copilot_jwt_diagnostics', array( $this, 'ajax_jwt_diagnostics' ) );
		add_action( 'admin_post_ngoinfo_copilot_healthcheck', array( $this, 'admin_post_health_check' ) );
	}

	/**
	 * Admin post health check handler
	 */
	public function admin_post_health_check() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['ngoinfo_copilot_health_nonce'] ?? '', 'ngoinfo_copilot_health_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'ngoinfo-copilot' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'ngoinfo-copilot' ) );
		}

		// Run health check
		$result = $this->run_health_check_with_jwt();

		// Store result in transient for display
		$user_id = get_current_user_id();
		set_transient( "ngoinfo_health_result_{$user_id}", $result, 120 ); // 2 minutes TTL

		// Redirect back to health tab
		$redirect_url = admin_url( 'options-general.php?page=ngoinfo-copilot&tab=health' );
		wp_safe_redirect( $redirect_url );
		exit;
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
	 * AJAX JWT diagnostics handler
	 */
	public function ajax_jwt_diagnostics() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'ngoinfo_copilot_admin' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ngoinfo-copilot' ) ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'ngoinfo-copilot' ) ) );
		}

		// Get API base URL
		$base_url = ngoinfo_copilot_get_option( 'api_base_url' );
		if ( empty( $base_url ) ) {
			wp_send_json_error( array( 'message' => __( 'API Base URL is not set.', 'ngoinfo-copilot' ) ) );
		}

		// Build diagnostics endpoint URL
		$path = apply_filters( 'ngoinfo_copilot_diag_path', '/api/auth/echo-claims' );
		$url = rtrim( $base_url, '/' ) . $path;

		// Load Auth class if not available
		if ( ! class_exists( 'NGOInfo_Copilot_Auth' ) ) {
			require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-auth.php';
		}

		$auth = new NGOInfo_Copilot_Auth();
		$current_user = wp_get_current_user();

		// Create JWT token
		$token_result = $auth->create_bearer_token( $current_user->ID );
		if ( is_wp_error( $token_result ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create JWT token. Please check your JWT configuration.', 'ngoinfo-copilot' ) ) );
		}

		$jwt = $token_result['token'];

		$start_time = microtime( true );

		// Make request to diagnostics endpoint
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
				'headers' => array(
					'Authorization' => 'Bearer ' . $jwt,
					'Accept'        => 'application/json',
					'User-Agent'    => 'NGOInfo-Copilot-WP/' . NGOINFO_COPILOT_VERSION,
				),
			)
		);

		$end_time = microtime( true );
		$duration = round( ( $end_time - $start_time ) * 1000 );

		// Log minimal diagnostic event
		ngoinfo_copilot_log( '[Diagnostics] Request to ' . $url . ' completed in ' . $duration . 'ms' );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			ngoinfo_copilot_log( '[Diagnostics] Request failed: ' . ngoinfo_copilot_redact_sensitive( $error_message ) );
			wp_send_json_error( array( 
				'message' => sprintf( __( 'Request failed: %s', 'ngoinfo-copilot' ), $error_message ),
				'status_code' => 0,
				'duration_ms' => $duration,
			) );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( 404 === $status_code ) {
			wp_send_json_error( array( 
				'message' => __( 'Diagnostics endpoint not found on API (404). Ask backend to enable /api/auth/echo-claims or update the filter ngoinfo_copilot_diag_path.', 'ngoinfo-copilot' ),
				'status_code' => $status_code,
				'duration_ms' => $duration,
			) );
		}

		if ( 200 === $status_code ) {
			$body_decoded = json_decode( $response_body, true );
			wp_send_json_success( array(
				'status_code' => $status_code,
				'duration_ms' => $duration,
				'body_decoded' => $body_decoded,
				'note' => __( 'JWT sent successfully; these are server-decoded claims/identity.', 'ngoinfo-copilot' ),
			) );
		}

		// Handle other status codes
		$body_excerpt = wp_trim_words( $response_body, 20 );
		wp_send_json_error( array( 
			'message' => sprintf( __( 'Unexpected response (HTTP %d): %s. Check JWT configuration (iss, aud, exp).', 'ngoinfo-copilot' ), $status_code, $body_excerpt ),
			'status_code' => $status_code,
			'duration_ms' => $duration,
		) );
	}

	/**
	 * Run health check with JWT authentication
	 *
	 * @return array Health check results.
	 */
	public function run_health_check_with_jwt() {
		$api_base_url = ngoinfo_copilot_get_option( 'api_base_url' );
		
		if ( empty( $api_base_url ) ) {
			return $this->create_error_result( 'API Base URL not configured.', 0, 'Configuration error' );
		}

		// Check if JWT secret is configured
		$jwt_secret = ngoinfo_copilot_get_option( 'jwt_secret' );
		if ( empty( $jwt_secret ) ) {
			return $this->create_error_result( 'JWT secret not configured.', 0, 'Configuration error' );
		}

		// Load Auth class if not available
		if ( ! class_exists( 'NGOInfo_Copilot_Auth' ) ) {
			require_once NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-auth.php';
		}

		$auth = new NGOInfo_Copilot_Auth();
		$current_user = wp_get_current_user();

		// Mint JWT token
		$jwt_token = $auth->mint_user_jwt( $current_user );
		if ( false === $jwt_token ) {
			return $this->create_error_result( 'Failed to generate JWT token.', 0, 'Authentication error' );
		}

		$start_time = microtime( true );
		
		// Build health URL safely
		$health_url = rtrim( $api_base_url, '/' ) . '/healthcheck';
		
		$response = wp_remote_get(
			$health_url,
			array(
				'timeout'     => 8,
				'redirection' => 2,
				'httpversion' => '1.1',
				'sslverify'   => true,
				'headers'     => array(
					'Authorization' => 'Bearer ' . $jwt_token,
					'Accept'        => 'application/json',
					'User-Agent'    => 'NGOInfo-Copilot-WP/' . NGOINFO_COPILOT_VERSION,
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

			return $this->create_error_result( 
				sprintf( 'Connection failed: %s', $error_message ), 
				0, 
				$error_message, 
				$duration 
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$parsed_response = json_decode( $response_body, true );

		// Store health check results
		$timestamp = current_time( 'mysql' );
		ngoinfo_copilot_update_option( 'last_health_check', $timestamp );

		if ( 200 === $status_code ) {
			// Clear any previous errors
			ngoinfo_copilot_update_option( 'last_error', '' );

			return array(
				'success'     => true,
				'message'     => 'Health check successful!',
				'status_code' => $status_code,
				'response'    => $parsed_response,
				'duration'    => $duration,
				'timestamp'   => $timestamp,
				'error'       => null,
			);
		} else {
			$normalized_error = $this->normalize_backend_error( $status_code, $parsed_response );
			
			// Store error info
			ngoinfo_copilot_update_option( 'last_error', $normalized_error['message'] );

			// Log error with request ID if available
			$log_message = 'Health check returned error: ' . $normalized_error['message'];
			if ( ! empty( $normalized_error['request_id'] ) ) {
				$log_message .= ' (Request ID: ' . $normalized_error['request_id'] . ')';
			}
			ngoinfo_copilot_log( $log_message, 'error' );

			return array(
				'success'     => false,
				'message'     => sprintf( 'Health check failed: %s', $normalized_error['message'] ),
				'status_code' => $status_code,
				'response'    => $parsed_response,
				'duration'    => $duration,
				'timestamp'   => $timestamp,
				'error'       => $normalized_error,
			);
		}
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
				'timeout'     => 8,
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

			// Log error with request ID if available
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

	/**
	 * Get last health check result from transient
	 *
	 * @return array|false Health check result or false if not found.
	 */
	public function get_last_health_result() {
		$user_id = get_current_user_id();
		return get_transient( "ngoinfo_health_result_{$user_id}" );
	}

	/**
	 * Create error result array
	 *
	 * @param string $message Error message.
	 * @param int    $status_code HTTP status code.
	 * @param string $error Error details.
	 * @param int    $duration Duration in milliseconds.
	 * @return array Error result.
	 */
	private function create_error_result( $message, $status_code = 0, $error = '', $duration = 0 ) {
		return array(
			'success'     => false,
			'message'     => $message,
			'status_code' => $status_code,
			'response'    => null,
			'duration'    => $duration,
			'timestamp'   => current_time( 'mysql' ),
			'error'       => $error,
		);
	}

	/**
	 * Normalize backend error response
	 *
	 * @param int   $status_code HTTP status code.
	 * @param array $parsed_response Parsed JSON response.
	 * @return array Normalized error.
	 */
	private function normalize_backend_error( $status_code, $parsed_response ) {
		// If response has standard error format from backend
		if ( is_array( $parsed_response ) && isset( $parsed_response['code'], $parsed_response['message'] ) ) {
			return array(
				'code'       => sanitize_text_field( $parsed_response['code'] ),
				'message'    => sanitize_text_field( $parsed_response['message'] ),
				'request_id' => isset( $parsed_response['request_id'] ) ? sanitize_text_field( $parsed_response['request_id'] ) : null,
			);
		}

		// Fallback to standard HTTP status messages
		$default_messages = array(
			400 => 'Bad Request',
			401 => 'Unauthorized - Check JWT secret configuration',
			403 => 'Forbidden',
			404 => 'Not Found - Check API Base URL',
			422 => 'Validation Error',
			429 => 'Rate Limit Exceeded',
			500 => 'Internal Server Error',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
		);

		$message = isset( $default_messages[ $status_code ] ) 
			? $default_messages[ $status_code ]
			: sprintf( 'HTTP Error %d', $status_code );

		return array(
			'code'       => 'HTTP_' . $status_code,
			'message'    => $message,
			'request_id' => null,
		);
	}
}