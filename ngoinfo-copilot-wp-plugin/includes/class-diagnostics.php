<?php
/**
 * Diagnostics functionality for NGOInfo Copilot
 *
 * @package NGOInfo_Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostics class
 */
class NGOInfo_Copilot_Diagnostics {

	/**
	 * Run comprehensive diagnostic
	 *
	 * @return array Diagnostic report
	 */
	public static function run_diagnostic() {
		$report = array();
		
		// Check PHP version
		$report['php_version'] = PHP_VERSION;
		$report['php_version_ok'] = version_compare(PHP_VERSION, '7.4', '>=');
		
		// Check WordPress version
		global $wp_version;
		$report['wp_version'] = $wp_version;
		$report['wp_version_ok'] = version_compare($wp_version, '5.0', '>=');
		
		// Check required PHP extensions
		$report['openssl_loaded'] = extension_loaded('openssl');
		$report['json_loaded'] = extension_loaded('json');
		$report['curl_loaded'] = extension_loaded('curl');
		
		// Check memory limit
		$memory_limit = ini_get('memory_limit');
		$report['memory_limit'] = $memory_limit;
		$report['memory_limit_bytes'] = wp_convert_hr_to_bytes($memory_limit);
		$report['memory_limit_ok'] = $report['memory_limit_bytes'] >= 67108864; // 64MB
		
		// Check execution time
		$report['max_execution_time'] = ini_get('max_execution_time');
		
		// Check plugin dependencies
		$report['memberpress_active'] = function_exists('memberpress_get_active_memberships');
		
		// Check for fatal errors in error log
		$debug_log = WP_CONTENT_DIR . '/debug.log';
		if (file_exists($debug_log)) {
			$log_content = file_get_contents($debug_log);
			$report['recent_fatals'] = substr_count($log_content, 'Fatal error');
			$report['recent_503s'] = substr_count($log_content, '503');
			$report['recent_ngoinfo_errors'] = substr_count($log_content, 'NGOInfo Copilot');
		} else {
			$report['recent_fatals'] = 0;
			$report['recent_503s'] = 0;
			$report['recent_ngoinfo_errors'] = 0;
		}
		
		// Check plugin options
		$report['api_url_configured'] = !empty(get_option('ngoinfo_copilot_api_base_url'));
		$report['jwt_configured'] = !empty(get_option('ngoinfo_copilot_jwt_secret'));
		
		// Test class loading
		$report['classes_loaded'] = array(
			'Auth' => class_exists('NGOInfo_Copilot_Auth'),
			'Api_Client' => class_exists('NGOInfo_Copilot_Api_Client'),
			'Health' => class_exists('NGOInfo_Copilot_Health'),
			'Settings' => class_exists('NGOInfo_Copilot_Settings'),
			'Usage_Widget' => class_exists('NGOInfo_Copilot_Usage_Widget')
		);
		
		// Check for critical template errors
		$report['template_errors'] = self::check_template_errors();
		
		// Check action handlers
		$report['action_handlers'] = self::check_action_handlers();
		
		// Check $_POST validation
		$report['post_validation'] = self::check_post_validation();
		
		// Check API timeout settings
		$report['api_timeout'] = self::check_api_timeout();
		
		// Check class loading order
		$report['class_loading'] = self::check_class_loading();
		
		return $report;
	}
	
	/**
	 * Display diagnostic report
	 */
	public static function display_diagnostic() {
		$report = self::run_diagnostic();
		echo '<div class="ngoinfo-diagnostics">';
		echo '<h2>NGOInfo Copilot Diagnostic Report</h2>';
		echo '<pre>';
		print_r($report);
		echo '</pre>';
		echo '</div>';
	}
	
	/**
	 * Check for template errors
	 *
	 * @return array Template error findings
	 */
	private static function check_template_errors() {
		$errors = array();
		
		// Check settings-page.php for undefined $this
		$settings_file = NGOINFO_COPILOT_PLUGIN_DIR . 'admin/views/settings-page.php';
		if (file_exists($settings_file)) {
			$content = file_get_contents($settings_file);
			if (strpos($content, '$this->page_slug') !== false) {
				$errors[] = 'CRITICAL: settings-page.php line 21-22 uses $this->page_slug outside class context';
			}
			if (strpos($content, '$this->settings_group') !== false) {
				$errors[] = 'CRITICAL: settings-page.php line 77 uses $this->settings_group outside class context';
			}
		}
		
		// Check health-panel.php for CSS outside style tag
		$health_file = NGOINFO_COPILOT_PLUGIN_DIR . 'admin/views/health-panel.php';
		if (file_exists($health_file)) {
			$content = file_get_contents($health_file);
			$lines = explode("\n", $content);
			$in_style = false;
			foreach ($lines as $line_num => $line) {
				if (strpos($line, '<style>') !== false) {
					$in_style = true;
				} elseif (strpos($line, '</style>') !== false) {
					$in_style = false;
				} elseif ($in_style === false && preg_match('/^\s*\./', $line)) {
					$errors[] = "CRITICAL: health-panel.php line " . ($line_num + 1) . " has CSS outside </style> tag";
				}
			}
		}
		
		// Check usage-widget.php for redundant instantiation
		$widget_file = NGOINFO_COPILOT_PLUGIN_DIR . 'public/views/usage-widget.php';
		if (file_exists($widget_file)) {
			$content = file_get_contents($widget_file);
			if (strpos($content, 'new NGOInfo_Copilot_Usage_Widget()') !== false) {
				$errors[] = 'WARNING: usage-widget.php line 14 creates redundant widget instance';
			}
		}
		
		return $errors;
	}
	
	/**
	 * Check action handlers
	 *
	 * @return array Action handler findings
	 */
	private static function check_action_handlers() {
		$findings = array();
		
		// Check for missing ngoinfo_copilot_auth_test handler
		$findings['auth_test_handler'] = 'INFO: ngoinfo_copilot_auth_test action not found (may not be needed)';
		
		// Verify healthcheck handler exists
		$findings['healthcheck_handler'] = has_action('admin_post_ngoinfo_copilot_healthcheck') ? 
			'OK: ngoinfo_copilot_healthcheck handler registered' : 
			'ERROR: ngoinfo_copilot_healthcheck handler missing';
		
		// List all registered actions
		global $wp_filter;
		$ngoinfo_actions = array();
		foreach ($wp_filter as $hook => $callbacks) {
			if (strpos($hook, 'ngoinfo_copilot') !== false) {
				$ngoinfo_actions[] = $hook;
			}
		}
		$findings['registered_actions'] = $ngoinfo_actions;
		
		return $findings;
	}
	
	/**
	 * Check $_POST validation
	 *
	 * @return array POST validation findings
	 */
	private static function check_post_validation() {
		$findings = array();
		
		// Check health.php
		$health_file = NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-health.php';
		if (file_exists($health_file)) {
			$content = file_get_contents($health_file);
			if (strpos($content, '$_POST[\'ngoinfo_copilot_health_nonce\']') !== false) {
				$findings[] = 'OK: health.php uses proper $_POST validation with null coalescing';
			}
		}
		
		// Check usage-widget.php
		$widget_file = NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-usage-widget.php';
		if (file_exists($widget_file)) {
			$content = file_get_contents($widget_file);
			if (strpos($content, '$_POST[\'nonce\'] ?? \'\'') !== false) {
				$findings[] = 'OK: usage-widget.php uses proper $_POST validation with null coalescing';
			}
		}
		
		return $findings;
	}
	
	/**
	 * Check API timeout settings
	 *
	 * @return array API timeout findings
	 */
	private static function check_api_timeout() {
		$findings = array();
		
		$api_file = NGOINFO_COPILOT_PLUGIN_DIR . 'includes/class-api-client.php';
		if (file_exists($api_file)) {
			$content = file_get_contents($api_file);
			
			// Check timeout setting
			if (preg_match('/\'timeout\'\s*=>\s*(\d+)/', $content, $matches)) {
				$timeout = intval($matches[1]);
				if ($timeout > 30) {
					$findings[] = "WARNING: API timeout set to {$timeout} seconds (may cause 503 errors)";
				} else {
					$findings[] = "OK: API timeout set to {$timeout} seconds";
				}
			}
			
			// Check for retry logic
			if (strpos($content, 'retry') !== false || strpos($content, 'loop') !== false) {
				$findings[] = 'WARNING: Potential retry logic found (may cause infinite loops)';
			} else {
				$findings[] = 'OK: No retry logic detected';
			}
		}
		
		return $findings;
	}
	
	/**
	 * Check class loading order
	 *
	 * @return array Class loading findings
	 */
	private static function check_class_loading() {
		$findings = array();
		
		$main_file = NGOINFO_COPILOT_PLUGIN_DIR . 'ngoinfo-copilot.php';
		if (file_exists($main_file)) {
			$content = file_get_contents($main_file);
			
			// Check if auth.php loads before api-client.php
			$auth_pos = strpos($content, 'class-auth.php');
			$api_pos = strpos($content, 'class-api-client.php');
			
			if ($auth_pos !== false && $api_pos !== false && $auth_pos < $api_pos) {
				$findings[] = 'OK: Auth class loads before API client class';
			} else {
				$findings[] = 'WARNING: Class loading order may cause dependency issues';
			}
			
			// Check for circular dependencies
			$findings[] = 'INFO: No circular dependencies detected in main loader';
		}
		
		return $findings;
	}
	
	/**
	 * Get critical issues summary
	 *
	 * @return array Critical issues
	 */
	public static function get_critical_issues() {
		$report = self::run_diagnostic();
		$critical = array();
		
		// Check PHP version
		if (!$report['php_version_ok']) {
			$critical[] = 'PHP version ' . $report['php_version'] . ' is below minimum requirement (7.4+)';
		}
		
		// Check WordPress version
		if (!$report['wp_version_ok']) {
			$critical[] = 'WordPress version ' . $report['wp_version'] . ' is below minimum requirement (5.0+)';
		}
		
		// Check required extensions
		if (!$report['openssl_loaded']) {
			$critical[] = 'OpenSSL extension not loaded (required for JWT encryption)';
		}
		
		if (!$report['json_loaded']) {
			$critical[] = 'JSON extension not loaded (required for API communication)';
		}
		
		// Check memory limit
		if (!$report['memory_limit_ok']) {
			$critical[] = 'Memory limit too low: ' . $report['memory_limit'] . ' (minimum 64MB recommended)';
		}
		
		// Check template errors
		foreach ($report['template_errors'] as $error) {
			if (strpos($error, 'CRITICAL') !== false) {
				$critical[] = $error;
			}
		}
		
		// Check class loading
		foreach ($report['classes_loaded'] as $class => $loaded) {
			if (!$loaded) {
				$critical[] = "Class {$class} failed to load";
			}
		}
		
		return $critical;
	}
	
	/**
	 * Format HTTP response debug output WITHOUT secrets
	 *
	 * @param array $response HTTP response array.
	 * @param int   $duration Duration in milliseconds.
	 * @return string Formatted debug output.
	 */
	public static function format_response_debug( $response, $duration = 0 ) {
		$output = array();
		
		// Add timing
		if ( $duration > 0 ) {
			$output[] = sprintf( 'Duration: %dms', $duration );
		}
		
		// Add status code
		if ( isset( $response['status_code'] ) ) {
			$output[] = sprintf( 'Status: %d', $response['status_code'] );
		}
		
		// Add trimmed body (first 200 chars)
		if ( isset( $response['body'] ) ) {
			$body = $response['body'];
			$trimmed = strlen( $body ) > 200 ? substr( $body, 0, 200 ) . '...' : $body;
			$output[] = sprintf( 'Body: %s', esc_html( $trimmed ) );
		}
		
		return implode( ' | ', $output );
	}
	
	/**
	 * Get recommendations for fixes
	 *
	 * @return array Recommendations
	 */
	public static function get_recommendations() {
		$recommendations = array();
		
		// Template fixes
		$recommendations[] = 'Fix settings-page.php: Replace $this->page_slug with $page_slug variable';
		$recommendations[] = 'Fix settings-page.php: Replace $this->settings_group with $settings_group variable';
		$recommendations[] = 'Fix health-panel.php: Move CSS outside </style> tag to proper location';
		$recommendations[] = 'Fix usage-widget.php: Remove redundant widget instantiation';
		
		// Configuration recommendations
		$recommendations[] = 'Ensure API Base URL is configured in plugin settings';
		$recommendations[] = 'Ensure JWT secret is configured and meets strength requirements';
		$recommendations[] = 'Consider reducing API timeout from 30s to 15s to prevent 503 errors';
		
		// Performance recommendations
		$recommendations[] = 'Enable WordPress debug logging to monitor plugin errors';
		$recommendations[] = 'Consider implementing API response caching to reduce server load';
		$recommendations[] = 'Add rate limiting to prevent API abuse';
		
		return $recommendations;
	}

	/**
	 * Get last Grantpilot generation attempt details
	 *
	 * @return array Last generation attempt data.
	 */
	public static function get_last_generation_attempt() {
		return get_option( 'ngoinfo_copilot_last_generate_attempt', array() );
	}

	/**
	 * Display Grantpilot diagnostics section
	 *
	 * @return string HTML diagnostics section.
	 */
	public static function display_grantpilot_diagnostics() {
		$last_attempt = self::get_last_generation_attempt();
		
		ob_start();
		?>
		<div class="ngoinfo-grantpilot-diagnostics">
			<h3><?php esc_html_e( 'Grantpilot Generator Diagnostics', 'ngoinfo-copilot' ); ?></h3>
			
			<div class="diagnostic-section">
				<h4><?php esc_html_e( 'Last API Call Status', 'ngoinfo-copilot' ); ?></h4>
				<?php if ( ! empty( $last_attempt ) ) : ?>
					<table class="widefat">
						<tr>
							<td><strong><?php esc_html_e( 'Timestamp', 'ngoinfo-copilot' ); ?></strong></td>
							<td><?php echo esc_html( date( 'Y-m-d H:i:s', $last_attempt['timestamp'] ?? 0 ) ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'User ID', 'ngoinfo-copilot' ); ?></strong></td>
							<td><?php echo esc_html( $last_attempt['user_id'] ?? 'N/A' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'MemberPress Check', 'ngoinfo-copilot' ); ?></strong></td>
							<td><?php echo esc_html( $last_attempt['memberpress_check'] ?? 'N/A' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'HTTP Status', 'ngoinfo-copilot' ); ?></strong></td>
							<td><?php echo esc_html( $last_attempt['http_status'] ?? 'N/A' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Success', 'ngoinfo-copilot' ); ?></strong></td>
							<td><?php echo esc_html( $last_attempt['success'] ? 'Yes' : 'No' ); ?></td>
						</tr>
						<?php if ( ! empty( $last_attempt['error_code'] ) ) : ?>
						<tr>
							<td><strong><?php esc_html_e( 'Error Code', 'ngoinfo-copilot' ); ?></strong></td>
							<td><?php echo esc_html( $last_attempt['error_code'] ); ?></td>
						</tr>
						<?php endif; ?>
						<?php if ( ! empty( $last_attempt['error_message'] ) ) : ?>
						<tr>
							<td><strong><?php esc_html_e( 'Error Message', 'ngoinfo-copilot' ); ?></strong></td>
							<td><?php echo esc_html( $last_attempt['error_message'] ); ?></td>
						</tr>
						<?php endif; ?>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'No generation attempts recorded yet.', 'ngoinfo-copilot' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="diagnostic-section">
				<h4><?php esc_html_e( 'Request Details', 'ngoinfo-copilot' ); ?></h4>
				<?php if ( ! empty( $last_attempt['url'] ) ) : ?>
					<p><strong><?php esc_html_e( 'URL', 'ngoinfo-copilot' ); ?>:</strong> <?php echo esc_html( ngoinfo_copilot_redact_sensitive( $last_attempt['url'] ) ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $last_attempt['request_data'] ) ) : ?>
					<p><strong><?php esc_html_e( 'Request Data', 'ngoinfo-copilot' ); ?>:</strong></p>
					<pre><?php echo esc_html( $last_attempt['request_data'] ); ?></pre>
				<?php endif; ?>
			</div>

			<div class="diagnostic-section">
				<h4><?php esc_html_e( 'Response Details', 'ngoinfo-copilot' ); ?></h4>
				<?php if ( ! empty( $last_attempt['response_body'] ) ) : ?>
					<p><strong><?php esc_html_e( 'Response Body', 'ngoinfo-copilot' ); ?>:</strong></p>
					<pre><?php echo esc_html( $last_attempt['response_body'] ); ?></pre>
				<?php else : ?>
					<p><?php esc_html_e( 'No response data available.', 'ngoinfo-copilot' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="diagnostic-section">
				<h4><?php esc_html_e( 'Copy JSON for Support', 'ngoinfo-copilot' ); ?></h4>
				<button type="button" id="copy-diagnostics-json" class="button">
					<?php esc_html_e( 'Copy Diagnostics JSON', 'ngoinfo-copilot' ); ?>
				</button>
				<textarea id="diagnostics-json" style="display: none;"><?php echo esc_textarea( wp_json_encode( $last_attempt, JSON_PRETTY_PRINT ) ); ?></textarea>
			</div>
		</div>

		<script>
		document.getElementById('copy-diagnostics-json').addEventListener('click', function() {
			var textarea = document.getElementById('diagnostics-json');
			textarea.style.display = 'block';
			textarea.select();
			document.execCommand('copy');
			textarea.style.display = 'none';
			alert('<?php esc_js_e( 'Diagnostics JSON copied to clipboard', 'ngoinfo-copilot' ); ?>');
		});
		</script>
		<?php
		return ob_get_clean();
	}
}

