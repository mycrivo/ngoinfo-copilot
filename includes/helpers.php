<?php
/**
 * Helper functions for NGOInfo Copilot plugin
 *
 * @package NGOInfo\Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get plugin option with fallback
 *
 * @param string $option_name Option name (without plugin prefix).
 * @param mixed  $default     Default value if option doesn't exist.
 * @return mixed Option value or default.
 */
function ngoinfo_copilot_get_option( $option_name, $default = '' ) {
	return get_option( "ngoinfo_copilot_{$option_name}", $default );
}

/**
 * Update plugin option
 *
 * @param string $option_name Option name (without plugin prefix).
 * @param mixed  $value       Option value.
 * @return bool True if option was updated, false otherwise.
 */
function ngoinfo_copilot_update_option( $option_name, $value ) {
	return update_option( "ngoinfo_copilot_{$option_name}", $value );
}

/**
 * Encrypt a value using OpenSSL
 *
 * @param string $value Value to encrypt.
 * @param string $key   Encryption key.
 * @return string Encrypted value (base64 encoded).
 */
function ngoinfo_copilot_encrypt( $value, $key = '' ) {
	if ( empty( $key ) ) {
		$key = wp_salt( 'nonce' );
	}

	$cipher = 'AES-256-CBC';
	$ivlen  = openssl_cipher_iv_length( $cipher );
	$iv     = openssl_random_pseudo_bytes( $ivlen );

	$encrypted = openssl_encrypt( $value, $cipher, $key, 0, $iv );
	
	if ( false === $encrypted ) {
		return false;
	}

	// Combine IV and encrypted data
	return base64_encode( $iv . $encrypted );
}

/**
 * Decrypt a value using OpenSSL
 *
 * @param string $encrypted_value Encrypted value (base64 encoded).
 * @param string $key             Encryption key.
 * @return string|false Decrypted value or false on failure.
 */
function ngoinfo_copilot_decrypt( $encrypted_value, $key = '' ) {
	if ( empty( $key ) ) {
		$key = wp_salt( 'nonce' );
	}

	$data = base64_decode( $encrypted_value );
	if ( false === $data ) {
		return false;
	}

	$cipher = 'AES-256-CBC';
	$ivlen  = openssl_cipher_iv_length( $cipher );
	
	if ( strlen( $data ) <= $ivlen ) {
		return false;
	}

	$iv        = substr( $data, 0, $ivlen );
	$encrypted = substr( $data, $ivlen );

	return openssl_decrypt( $encrypted, $cipher, $key, 0, $iv );
}

/**
 * Sanitize API URL
 *
 * @param string $url URL to sanitize.
 * @return string Sanitized URL.
 */
function ngoinfo_copilot_sanitize_url( $url ) {
	$url = esc_url_raw( $url );
	
	// Ensure HTTPS for production
	if ( 'production' === ngoinfo_copilot_get_option( 'environment' ) ) {
		$url = str_replace( 'http://', 'https://', $url );
	}

	// Remove trailing slash
	return rtrim( $url, '/' );
}

/**
 * Check if user can manage plugin settings
 *
 * @return bool True if user has permission, false otherwise.
 */
function ngoinfo_copilot_user_can_manage() {
	return current_user_can( 'manage_options' );
}

/**
 * Display admin notice
 *
 * @param string $message Notice message.
 * @param string $type    Notice type (success, error, warning, info).
 * @param bool   $dismissible Whether notice is dismissible.
 */
function ngoinfo_copilot_admin_notice( $message, $type = 'info', $dismissible = true ) {
	$classes = array( 'notice', "notice-{$type}" );
	
	if ( $dismissible ) {
		$classes[] = 'is-dismissible';
	}

	printf(
		'<div class="%s"><p>%s</p></div>',
		esc_attr( implode( ' ', $classes ) ),
		wp_kses_post( $message )
	);
}

/**
 * Log message to WordPress debug log
 *
 * @param string $message Message to log.
 * @param string $level   Log level (info, warning, error).
 */
function ngoinfo_copilot_log( $message, $level = 'info' ) {
	if ( ! WP_DEBUG_LOG ) {
		return;
	}

	$timestamp = gmdate( 'Y-m-d H:i:s' );
	$log_entry = sprintf(
		'[%s] NGOInfo Copilot [%s]: %s',
		$timestamp,
		strtoupper( $level ),
		$message
	);

	error_log( $log_entry );
}

/**
 * Redact sensitive information from log messages
 *
 * @param string $message Message to redact.
 * @return string Redacted message.
 */
function ngoinfo_copilot_redact_sensitive( $message ) {
	$sensitive_patterns = array(
		'/secret["\']?\s*[:\=]\s*["\']?[^"\'\s]+/i' => 'secret: [REDACTED]',
		'/token["\']?\s*[:\=]\s*["\']?[^"\'\s]+/i'  => 'token: [REDACTED]',
		'/password["\']?\s*[:\=]\s*["\']?[^"\'\s]+/i' => 'password: [REDACTED]',
		'/key["\']?\s*[:\=]\s*["\']?[^"\'\s]+/i'    => 'key: [REDACTED]',
		'/Bearer\s+[A-Za-z0-9\-\._~\+\/]+=*/i'     => 'Bearer [REDACTED]',
	);

	return preg_replace( array_keys( $sensitive_patterns ), array_values( $sensitive_patterns ), $message );
}

/**
 * Validate JWT secret strength
 *
 * @param string $secret Secret to validate.
 * @return bool True if secret is strong enough, false otherwise.
 */
function ngoinfo_copilot_validate_jwt_secret( $secret ) {
	// Minimum 32 characters
	if ( strlen( $secret ) < 32 ) {
		return false;
	}

	// Should contain mixed case, numbers, and special characters
	$has_lowercase = preg_match( '/[a-z]/', $secret );
	$has_uppercase = preg_match( '/[A-Z]/', $secret );
	$has_numbers   = preg_match( '/[0-9]/', $secret );
	$has_special   = preg_match( '/[^a-zA-Z0-9]/', $secret );

	return $has_lowercase && $has_uppercase && $has_numbers && $has_special;
}

/**
 * Debug logging function for NGOInfo Copilot
 *
 * @param string $message Debug message.
 * @param array  $context Additional context data.
 */
function ngoinfo_copilot_debug( $message, $context = array() ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$log = '[NGOINFO DEBUG] ' . $message;
		if ( ! empty( $context ) ) {
			$log .= ' | ' . wp_json_encode( $context );
		}
		error_log( $log );
	}
}

/**
 * Minimal logging function for NGOInfo Copilot
 *
 * @param mixed $m Message or data to log.
 */
function ngoinfo_log( $m ) {
	if ( function_exists( 'error_log' ) ) {
		error_log( '[NGOINFO] ' . ( is_scalar( $m ) ? $m : wp_json_encode( $m ) ) );
	}
}

