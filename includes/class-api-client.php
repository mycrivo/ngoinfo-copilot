<?php
/**
 * API client for NGOInfo Copilot backend
 *
 * @package NGOInfo\Copilot
 */

namespace NGOInfo\Copilot;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Client class
 */
class Api_Client {

	/**
	 * Auth instance
	 *
	 * @var Auth
	 */
	private $auth;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->auth = new Auth();
	}

	/**
	 * Get usage summary from API
	 *
	 * @param \WP_User $user User to get usage for (optional, uses current user).
	 * @return array|false Usage data or false on failure.
	 */
	public function get_usage_summary( $user = null ) {
		return $this->request( 'GET', '/api/usage/summary', array(), $user );
	}

	/**
	 * Make HTTP request to API
	 *
	 * @param string   $method HTTP method (GET, POST, PUT, DELETE).
	 * @param string   $endpoint API endpoint (relative to base URL).
	 * @param array    $data Request data for POST/PUT requests.
	 * @param \WP_User $user User for authentication (optional, uses current user).
	 * @return array|false Response data or false on failure.
	 */
	public function request( $method, $endpoint, $data = array(), $user = null ) {
		$api_base_url = ngoinfo_copilot_get_option( 'api_base_url' );
		
		if ( empty( $api_base_url ) ) {
			ngoinfo_copilot_log( 'API base URL not configured for request: ' . $endpoint, 'error' );
			return false;
		}

		// Build full URL
		$url = rtrim( $api_base_url, '/' ) . '/' . ltrim( $endpoint, '/' );

		// Prepare request arguments
		$args = array(
			'method'      => strtoupper( $method ),
			'timeout'     => 30,
			'redirection' => 0,
			'httpversion' => '1.1',
			'headers'     => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
				'User-Agent'   => 'NGOInfo-Copilot-WP/' . NGOINFO_COPILOT_VERSION,
			),
		);

		// Add request body for POST/PUT
		if ( in_array( $method, array( 'POST', 'PUT' ), true ) && ! empty( $data ) ) {
			$args['body'] = wp_json_encode( $data );
		}

		// Add authentication header
		$args = $this->auth->add_auth_header( $args, $user );

		// Debug: Log API request details
		$headers_redacted = $args['headers'];
		if ( isset( $headers_redacted['Authorization'] ) ) {
			$headers_redacted['Authorization'] = substr( $headers_redacted['Authorization'], 0, 10 ) . '...';
		}
		$request_data = array(
			'url'     => $url,
			'method'  => $method,
			'headers' => $headers_redacted,
		);
		if ( in_array( $method, array( 'POST', 'PUT' ), true ) && ! empty( $data ) ) {
			$request_data['body'] = $data;
		}
		ngoinfo_copilot_debug( 'API Request', $request_data );

		// Log request (with sensitive data redacted)
		$log_url = ngoinfo_copilot_redact_sensitive( $url );
		ngoinfo_copilot_log( "API Request: {$method} {$log_url}", 'info' );

		// Log minimal request info
		ngoinfo_log( array(
			'method'      => $method,
			'url'         => $url,
			'has_auth'    => isset( $args['headers']['Authorization'] ),
			'auth_prefix' => substr( $args['headers']['Authorization'] ?? '', 0, 22 ),
		) );

		// Make request
		$response = wp_remote_request( $url, $args );

		// Debug: Log API response
		if ( ! is_wp_error( $response ) ) {
			$code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );
			ngoinfo_copilot_debug( 'API Response', array( 
				'code' => $code, 
				'body_snippet' => substr( $body, 0, 300 ) 
			) );
			
			// Log minimal response info
			ngoinfo_log( array(
				'status_code'  => $code,
				'body_snippet' => substr( $body, 0, 240 ),
			) );
		}

		// Handle request errors
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			ngoinfo_copilot_log( "API Request failed: {$error_message}", 'error' );
			
			return array(
				'success' => false,
				'error'   => array(
					'code'    => 'REQUEST_FAILED',
					'message' => $error_message,
				),
			);
		}

		// Parse response
		return $this->parse_response( $response, $endpoint );
	}

	/**
	 * Parse HTTP response
	 *
	 * @param array  $response WordPress HTTP response.
	 * @param string $endpoint Endpoint for logging context.
	 * @return array Parsed response data.
	 */
	private function parse_response( $response, $endpoint ) {
		$status_code   = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$content_type  = wp_remote_retrieve_header( $response, 'content-type' );

		// Log response status
		ngoinfo_copilot_log( "API Response: {$status_code} for {$endpoint}", 'info' );

		// Try to decode JSON response
		$parsed_body = null;
		if ( strpos( $content_type, 'application/json' ) !== false ) {
			$parsed_body = json_decode( $response_body, true );
		}

		// Handle successful responses (2xx)
		if ( $status_code >= 200 && $status_code < 300 ) {
			return array(
				'success'     => true,
				'status_code' => $status_code,
				'data'        => $parsed_body,
				'raw_body'    => $response_body,
			);
		}

		// Handle error responses
		$error = $this->normalize_error_response( $status_code, $parsed_body, $response_body );
		
		// Log error with request ID if available
		$log_message = "API Error {$status_code}: {$error['message']}";
		if ( ! empty( $error['request_id'] ) ) {
			$log_message .= " (Request ID: {$error['request_id']})";
		}
		ngoinfo_copilot_log( $log_message, 'error' );

		return array(
			'success'     => false,
			'status_code' => $status_code,
			'error'       => $error,
			'raw_body'    => $response_body,
		);
	}

	/**
	 * Normalize error response to standard format
	 *
	 * @param int    $status_code HTTP status code.
	 * @param array  $parsed_body Parsed JSON response body.
	 * @param string $raw_body Raw response body.
	 * @return array Normalized error.
	 */
	private function normalize_error_response( $status_code, $parsed_body, $raw_body ) {
		// If response has standard error format from backend
		if ( is_array( $parsed_body ) && isset( $parsed_body['code'], $parsed_body['message'] ) ) {
			return array(
				'code'       => sanitize_text_field( $parsed_body['code'] ),
				'message'    => sanitize_text_field( $parsed_body['message'] ),
				'request_id' => isset( $parsed_body['request_id'] ) ? sanitize_text_field( $parsed_body['request_id'] ) : null,
				'details'    => isset( $parsed_body['details'] ) ? $parsed_body['details'] : null,
			);
		}

		// Fallback to standard HTTP status messages
		$default_messages = array(
			400 => __( 'Bad Request', 'ngoinfo-copilot' ),
			401 => __( 'Unauthorized', 'ngoinfo-copilot' ),
			403 => __( 'Forbidden', 'ngoinfo-copilot' ),
			404 => __( 'Not Found', 'ngoinfo-copilot' ),
			422 => __( 'Validation Error', 'ngoinfo-copilot' ),
			429 => __( 'Rate Limit Exceeded', 'ngoinfo-copilot' ),
			500 => __( 'Internal Server Error', 'ngoinfo-copilot' ),
			502 => __( 'Bad Gateway', 'ngoinfo-copilot' ),
			503 => __( 'Service Unavailable', 'ngoinfo-copilot' ),
			504 => __( 'Gateway Timeout', 'ngoinfo-copilot' ),
		);

		$message = isset( $default_messages[ $status_code ] ) 
			? $default_messages[ $status_code ]
			: sprintf( __( 'HTTP Error %d', 'ngoinfo-copilot' ), $status_code );

		// Try to extract request ID from raw response if JSON parsing failed
		$request_id = null;
		if ( is_string( $raw_body ) && preg_match( '/request_id["\']?\s*:\s*["\']?([a-f0-9\-]+)/', $raw_body, $matches ) ) {
			$request_id = $matches[1];
		}

		return array(
			'code'       => 'HTTP_' . $status_code,
			'message'    => $message,
			'request_id' => $request_id,
			'details'    => array(
				'status_code' => $status_code,
				'raw_response' => wp_strip_all_tags( substr( $raw_body, 0, 500 ) ),
			),
		);
	}

	/**
	 * Get API connection status
	 *
	 * @return array Connection status information.
	 */
	public function get_connection_status() {
		$api_base_url = ngoinfo_copilot_get_option( 'api_base_url' );
		$has_jwt_secret = $this->auth->is_jwt_configured();
		
		$status = array(
			'configured'     => ! empty( $api_base_url ) && $has_jwt_secret,
			'api_base_url'   => $api_base_url,
			'has_jwt_secret' => $has_jwt_secret,
			'jwt_status'     => $this->auth->get_jwt_status(),
		);

		return $status;
	}

	/**
	 * Test API connection and authentication
	 *
	 * @param \WP_User $user User to test with (optional, uses current user).
	 * @return array Test results.
	 */
	public function test_connection( $user = null ) {
		$status = $this->get_connection_status();
		
		if ( ! $status['configured'] ) {
			return array(
				'success' => false,
				'message' => __( 'API not configured. Please configure API base URL and JWT secret.', 'ngoinfo-copilot' ),
				'status'  => $status,
			);
		}

		if ( null === $user ) {
			$user = wp_get_current_user();
		}

		if ( ! $user || ! $user->exists() ) {
			return array(
				'success' => false,
				'message' => __( 'No valid user available for authentication test.', 'ngoinfo-copilot' ),
				'status'  => $status,
			);
		}

		// Test JWT generation
		$jwt_test = $this->auth->test_jwt_generation( $user );
		if ( ! $jwt_test['success'] ) {
			return array(
				'success' => false,
				'message' => __( 'JWT generation failed: ', 'ngoinfo-copilot' ) . $jwt_test['message'],
				'status'  => $status,
				'jwt_test' => $jwt_test,
			);
		}

		// Test usage summary endpoint (lightest authenticated endpoint)
		$usage_result = $this->get_usage_summary( $user );
		
		if ( false === $usage_result ) {
			return array(
				'success' => false,
				'message' => __( 'API request failed.', 'ngoinfo-copilot' ),
				'status'  => $status,
				'jwt_test' => $jwt_test,
			);
		}

		if ( ! $usage_result['success'] ) {
			$error_msg = $usage_result['error']['message'];
			if ( ! empty( $usage_result['error']['request_id'] ) ) {
				$error_msg .= ' (Request ID: ' . $usage_result['error']['request_id'] . ')';
			}

			return array(
				'success' => false,
				'message' => __( 'API authentication failed: ', 'ngoinfo-copilot' ) . $error_msg,
				'status'  => $status,
				'jwt_test' => $jwt_test,
				'api_response' => $usage_result,
			);
		}

		return array(
			'success' => true,
			'message' => __( 'API connection and authentication successful.', 'ngoinfo-copilot' ),
			'status'  => $status,
			'jwt_test' => $jwt_test,
			'api_response' => $usage_result,
		);
	}

	/**
	 * Format error for display in admin
	 *
	 * @param array $error Error array from API response.
	 * @return string Formatted error message.
	 */
	public function format_error_for_display( $error ) {
		$message = isset( $error['message'] ) ? $error['message'] : __( 'Unknown error occurred.', 'ngoinfo-copilot' );
		
		if ( ! empty( $error['request_id'] ) ) {
			$message .= sprintf( 
				' <small>(%s: %s)</small>', 
				__( 'Request ID', 'ngoinfo-copilot' ), 
				esc_html( $error['request_id'] )
			);
		}

		return $message;
	}
}

