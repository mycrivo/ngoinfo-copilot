<?php
/**
 * AJAX Controller for NGOInfo Copilot Grantpilot
 *
 * @package NGOInfo_Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Controller class
 */
class NGOInfo_Copilot_Ajax_Controller {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_ngoinfo_copilot_generate', array( $this, 'handle_generate' ) );
		add_action( 'wp_ajax_nopriv_ngoinfo_copilot_generate', array( $this, 'reject_guest' ) );
	}

	/**
	 * Reject guest users
	 */
	public function reject_guest() {
		wp_send_json_error( array(
			'code' => 'auth',
			'msg'  => __( 'Please log in to use Grantpilot.', 'ngoinfo-copilot' ),
		) );
	}

	/**
	 * Handle proposal generation request
	 */
	public function handle_generate() {
		// Verify nonce
		if ( ! check_ajax_referer( 'ngoinfo_copilot_generate', 'nonce', false ) ) {
			wp_send_json_error( array(
				'code' => 'nonce',
				'msg'  => __( 'Security check failed. Please refresh and try again.', 'ngoinfo-copilot' ),
			) );
		}

		// Check if user is logged in
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( array(
				'code' => 'auth',
				'msg'  => __( 'Please log in to use Grantpilot.', 'ngoinfo-copilot' ),
			) );
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			wp_send_json_error( array(
				'code' => 'auth',
				'msg'  => __( 'User not found.', 'ngoinfo-copilot' ),
			) );
		}

		// Validate MemberPress membership
		if ( ! NGOInfo_Copilot_Generator_Service::user_can_generate( $user_id ) ) {
			wp_send_json_error( array(
				'code' => 'plan',
				'msg'  => __( 'Grantpilot membership required.', 'ngoinfo-copilot' ),
			) );
		}

		// Validate and sanitize inputs
		$validation_result = $this->validate_inputs();
		if ( is_wp_error( $validation_result ) ) {
			wp_send_json_error( array(
				'code' => 'validation',
				'msg'  => $validation_result->get_error_message(),
			) );
		}

		$validated_data = $validation_result;

		// Check rate limiting
		if ( ! NGOInfo_Copilot_Generator_Service::rate_limit_check( $user_id ) ) {
			wp_send_json_error( array(
				'code' => 'rate',
				'msg'  => __( 'Please wait a moment before trying again.', 'ngoinfo-copilot' ),
			) );
		}

		// Mint JWT token
		$jwt_token = NGOInfo_Copilot_Generator_Service::mint_jwt( $user );
		if ( ! $jwt_token ) {
			wp_send_json_error( array(
				'code' => 'auth',
				'msg'  => __( 'Authentication failed. Please contact support.', 'ngoinfo-copilot' ),
			) );
		}

		// Prepare API request
		$api_base_url = NGOInfo_Copilot_Settings::get_api_base_url();
		$url = rtrim( $api_base_url, '/' ) . '/api/proposals/generate';
		
		$headers = array(
			'Authorization' => 'Bearer ' . $jwt_token,
			'Content-Type'  => 'application/json',
		);

		$timeout = NGOInfo_Copilot_Settings::get_http_timeout();

		// Log request for diagnostics
		$this->log_request_start( $user_id, $url, $validated_data );

		// Make API request
		$api_client = new NGOInfo_Copilot_Api_Client();
		$response = $api_client->post_json( $url, $headers, $validated_data, $timeout );

		// Log response for diagnostics
		$this->log_request_end( $user_id, $response );

		// Handle response
		if ( $response['success'] ) {
			// Mark rate limit
			NGOInfo_Copilot_Generator_Service::rate_limit_mark( $user_id );

			// Add to history
			$history_data = array_merge( $validated_data, array(
				'proposal_id' => $response['data']['proposal_id'] ?? 'unknown',
			) );
			NGOInfo_Copilot_Generator_Service::add_to_history( $user_id, $history_data );

			// Return success response
			wp_send_json_success( array(
				'proposal_id' => $response['data']['proposal_id'] ?? 'unknown',
				'preview'     => $response['data']['preview'] ?? '',
				'meta'        => $response['data']['meta'] ?? array(),
			) );
		} else {
			// Handle API errors
			$error_code = $response['error']['code'] ?? 'unknown';
			$error_message = $response['error']['message'] ?? __( 'Unknown error occurred.', 'ngoinfo-copilot' );

			// Map API error codes to user-friendly messages
			$user_message = $this->map_error_code( $error_code, $error_message );

			wp_send_json_error( array(
				'code' => $error_code,
				'msg'  => $user_message,
			) );
		}
	}

	/**
	 * Validate and sanitize form inputs
	 *
	 * @return array|WP_Error Validated data or error.
	 */
	private function validate_inputs() {
		$required_fields = array( 'donor', 'theme', 'country', 'title', 'budget', 'duration' );
		$validated = array();

		foreach ( $required_fields as $field ) {
			$value = sanitize_text_field( $_POST[ $field ] ?? '' );
			
			if ( empty( $value ) ) {
				return new WP_Error( 'missing_field', sprintf( __( 'Field "%s" is required.', 'ngoinfo-copilot' ), $field ) );
			}

			// Field-specific validation
			switch ( $field ) {
				case 'donor':
				case 'theme':
				case 'country':
				case 'title':
					if ( strlen( $value ) < 2 || strlen( $value ) > 200 ) {
						return new WP_Error( 'invalid_length', sprintf( __( 'Field "%s" must be between 2 and 200 characters.', 'ngoinfo-copilot' ), $field ) );
					}
					$validated[ $field ] = $value;
					break;

				case 'budget':
					$budget = floatval( $value );
					if ( $budget < 0 ) {
						return new WP_Error( 'invalid_budget', __( 'Budget must be a positive number.', 'ngoinfo-copilot' ) );
					}
					$validated[ $field ] = $budget;
					break;

				case 'duration':
					$duration = intval( $value );
					if ( $duration < 1 || $duration > 60 ) {
						return new WP_Error( 'invalid_duration', __( 'Duration must be between 1 and 60 months.', 'ngoinfo-copilot' ) );
					}
					$validated[ $field ] = $duration;
					break;
			}
		}

		return $validated;
	}

	/**
	 * Map API error codes to user-friendly messages
	 *
	 * @param string $error_code API error code.
	 * @param string $error_message Original error message.
	 * @return string User-friendly message.
	 */
	private function map_error_code( $error_code, $error_message ) {
		switch ( $error_code ) {
			case 'HTTP_401':
			case 'UNAUTHORIZED':
				return __( 'Authentication failed. Please contact support.', 'ngoinfo-copilot' );

			case 'HTTP_403':
			case 'FORBIDDEN':
				return __( 'Your plan does not allow this action.', 'ngoinfo-copilot' );

			case 'HTTP_429':
			case 'RATE_LIMITED':
				return __( 'Rate limit exceeded. Please try again later.', 'ngoinfo-copilot' );

			case 'HTTP_500':
			case 'HTTP_502':
			case 'HTTP_503':
			case 'HTTP_504':
			case 'INTERNAL_ERROR':
			case 'SERVICE_UNAVAILABLE':
				return __( 'Service error. Please try again later.', 'ngoinfo-copilot' );

			default:
				return $error_message;
		}
	}

	/**
	 * Log request start for diagnostics
	 *
	 * @param int    $user_id User ID.
	 * @param string $url Request URL.
	 * @param array  $data Request data.
	 */
	private function log_request_start( $user_id, $url, $data ) {
		$log_data = array(
			'timestamp'        => time(),
			'user_id'          => $user_id,
			'memberpress_check' => 'passed',
			'url'              => $url,
			'request_data'     => substr( wp_json_encode( $data ), 0, 120 ),
		);

		update_option( 'ngoinfo_copilot_last_generate_attempt', $log_data );
	}

	/**
	 * Log request end for diagnostics
	 *
	 * @param int   $user_id User ID.
	 * @param array $response API response.
	 */
	private function log_request_end( $user_id, $response ) {
		$existing_log = get_option( 'ngoinfo_copilot_last_generate_attempt', array() );
		
		$log_data = array_merge( $existing_log, array(
			'http_status'    => $response['status_code'] ?? 0,
			'response_body'   => substr( $response['raw_body'] ?? '', 0, 200 ),
			'error_code'      => $response['error']['code'] ?? null,
			'error_message'   => $response['error']['message'] ?? null,
			'success'         => $response['success'] ?? false,
		) );

		update_option( 'ngoinfo_copilot_last_generate_attempt', $log_data );
	}
}
