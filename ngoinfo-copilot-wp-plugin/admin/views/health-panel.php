<?php
/**
 * Health panel template
 *
 * @package NGOInfo\Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$health = new NGOInfo\Copilot\Health();
$status = $health->get_health_status();
?>

<div class="ngoinfo-health-panel">
	<div class="health-header">
		<h3><?php esc_html_e( 'API Health Monitor', 'ngoinfo-copilot' ); ?></h3>
		<div class="health-actions">
			<button type="button" id="run-health-check" class="button button-primary">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Run Health Check', 'ngoinfo-copilot' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=ngoinfo_copilot_health_check' ) ); ?>" class="button button-secondary">
				<span class="dashicons dashicons-admin-tools"></span>
				<?php esc_html_e( 'Admin Health Check', 'ngoinfo-copilot' ); ?>
			</a>
		</div>
	</div>

	<!-- Current Status -->
	<div class="health-status-card">
		<h4><?php esc_html_e( 'Current Status', 'ngoinfo-copilot' ); ?></h4>
		<div class="status-indicator status-<?php echo esc_attr( $status['status'] ); ?>">
			<?php
			switch ( $status['status'] ) {
				case 'healthy':
					echo '<span class="dashicons dashicons-yes-alt"></span>';
					esc_html_e( 'Healthy', 'ngoinfo-copilot' );
					break;
				case 'error':
					echo '<span class="dashicons dashicons-warning"></span>';
					esc_html_e( 'Error', 'ngoinfo-copilot' );
					break;
				default:
					echo '<span class="dashicons dashicons-marker"></span>';
					esc_html_e( 'Unknown', 'ngoinfo-copilot' );
					break;
			}
			?>
		</div>

		<div class="status-details">
			<div class="status-row">
				<strong><?php esc_html_e( 'API URL:', 'ngoinfo-copilot' ); ?></strong>
				<code><?php echo esc_html( $status['api_base_url'] ?: __( 'Not configured', 'ngoinfo-copilot' ) ); ?></code>
			</div>
			<?php if ( $status['last_check'] ) : ?>
			<div class="status-row">
				<strong><?php esc_html_e( 'Last Check:', 'ngoinfo-copilot' ); ?></strong>
				<span><?php echo esc_html( mysql2date( 'F j, Y g:i a', $status['last_check'] ) ); ?></span>
			</div>
			<?php endif; ?>
			<?php if ( $status['last_error'] ) : ?>
			<div class="status-row error">
				<strong><?php esc_html_e( 'Last Error:', 'ngoinfo-copilot' ); ?></strong>
				<span><?php echo esc_html( $status['last_error'] ); ?></span>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Health Check Results -->
	<div id="health-check-results" class="health-results" style="display: none;">
		<h4><?php esc_html_e( 'Health Check Results', 'ngoinfo-copilot' ); ?></h4>
		<div id="health-check-content"></div>
	</div>

	<!-- Configuration Check -->
	<div class="health-config-card">
		<h4><?php esc_html_e( 'Configuration Check', 'ngoinfo-copilot' ); ?></h4>
		<div class="config-checks">
			<div class="config-check <?php echo $status['has_api_config'] ? 'check-pass' : 'check-fail'; ?>">
				<span class="dashicons dashicons-<?php echo $status['has_api_config'] ? 'yes-alt' : 'warning'; ?>"></span>
				<?php esc_html_e( 'API Base URL configured', 'ngoinfo-copilot' ); ?>
			</div>
			<div class="config-check <?php echo $status['has_jwt_secret'] ? 'check-pass' : 'check-fail'; ?>">
				<span class="dashicons dashicons-<?php echo $status['has_jwt_secret'] ? 'yes-alt' : 'warning'; ?>"></span>
				<?php esc_html_e( 'JWT signing secret configured', 'ngoinfo-copilot' ); ?>
			</div>
		</div>
		<?php if ( ! $status['has_api_config'] || ! $status['has_jwt_secret'] ) : ?>
		<p class="config-warning">
			<span class="dashicons dashicons-info"></span>
			<?php esc_html_e( 'Complete the configuration in the Settings tab before running health checks.', 'ngoinfo-copilot' ); ?>
		</p>
		<?php endif; ?>
	</div>

	<!-- CORS Information -->
	<div class="health-cors-card">
		<h4><?php esc_html_e( 'CORS Requirements', 'ngoinfo-copilot' ); ?></h4>
		<p><?php esc_html_e( 'The backend must allow your WordPress site\'s origin for CORS requests. This was configured in Phase 0 of the backend hardening.', 'ngoinfo-copilot' ); ?></p>
		<p><?php esc_html_e( 'If you experience CORS errors during health checks, verify your domain is included in the backend CORS_ALLOWED_ORIGINS configuration.', 'ngoinfo-copilot' ); ?></p>
		
		<div class="cors-domains">
			<strong><?php esc_html_e( 'Expected allowed origins:', 'ngoinfo-copilot' ); ?></strong>
			<ul>
				<li>Production: https://ngoinfo.org, https://www.ngoinfo.org</li>
				<li>Staging: https://staging.ngoinfo.org</li>
				<li>Development: http://localhost:3000, http://localhost:8000</li>
			</ul>
		</div>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#run-health-check').on('click', function() {
		var $button = $(this);
		var $results = $('#health-check-results');
		var $content = $('#health-check-content');
		
		// Disable button and show loading
		$button.prop('disabled', true);
		$button.find('.dashicons').addClass('spin');
		$button.find('span:not(.dashicons)').text('<?php esc_html_e( 'Checking...', 'ngoinfo-copilot' ); ?>');
		
		// Clear previous results
		$results.hide();
		$content.empty();
		
		// Make AJAX request
		$.post(ajaxurl, {
			action: 'ngoinfo_copilot_health_check',
			nonce: ngoinfo_copilot_admin.nonce
		}, function(response) {
			// Re-enable button
			$button.prop('disabled', false);
			$button.find('.dashicons').removeClass('spin');
			$button.find('span:not(.dashicons)').text('<?php esc_html_e( 'Run Health Check', 'ngoinfo-copilot' ); ?>');
			
			// Show results
			$results.show();
			
			if (response.success) {
				$content.html(
					'<div class="health-result success">' +
					'<h5><span class="dashicons dashicons-yes-alt"></span>' + response.data.message + '</h5>' +
					'<div class="result-details">' +
					'<div class="result-row"><strong><?php esc_html_e( 'Status Code:', 'ngoinfo-copilot' ); ?></strong> ' + response.data.status_code + '</div>' +
					'<div class="result-row"><strong><?php esc_html_e( 'Response Time:', 'ngoinfo-copilot' ); ?></strong> ' + response.data.duration + 'ms</div>' +
					(response.data.response ? '<div class="result-row"><strong><?php esc_html_e( 'Response:', 'ngoinfo-copilot' ); ?></strong><br><pre>' + JSON.stringify(response.data.response, null, 2) + '</pre></div>' : '') +
					'</div>' +
					'</div>'
				);
			} else {
				$content.html(
					'<div class="health-result error">' +
					'<h5><span class="dashicons dashicons-warning"></span>' + response.data.message + '</h5>' +
					'<div class="result-details">' +
					(response.data.status_code ? '<div class="result-row"><strong><?php esc_html_e( 'Status Code:', 'ngoinfo-copilot' ); ?></strong> ' + response.data.status_code + '</div>' : '') +
					'<div class="result-row"><strong><?php esc_html_e( 'Duration:', 'ngoinfo-copilot' ); ?></strong> ' + response.data.duration + 'ms</div>' +
					(response.data.error ? '<div class="result-row"><strong><?php esc_html_e( 'Error:', 'ngoinfo-copilot' ); ?></strong> ' + response.data.error + '</div>' : '') +
					'</div>' +
					'</div>'
				);
			}
		}).fail(function() {
			// Re-enable button
			$button.prop('disabled', false);
			$button.find('.dashicons').removeClass('spin');
			$button.find('span:not(.dashicons)').text('<?php esc_html_e( 'Run Health Check', 'ngoinfo-copilot' ); ?>');
			
			$results.show();
			$content.html('<div class="health-result error"><h5><span class="dashicons dashicons-warning"></span><?php esc_html_e( 'Request failed. Please try again.', 'ngoinfo-copilot' ); ?></h5></div>');
		});
	});
});
</script>

<style>
.ngoinfo-health-panel {
	max-width: 800px;
}

.health-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20px;
}

.health-header h3 {
	margin: 0;
}

.health-actions {
	display: flex;
	gap: 10px;
}

.health-status-card, .health-results, .health-config-card, .health-cors-card {
	background: #fff;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
	padding: 20px;
	margin-bottom: 20px;
}

.health-status-card h4, .health-results h4, .health-config-card h4, .health-cors-card h4 {
	margin-top: 0;
	margin-bottom: 15px;
}

.status-indicator {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 15px;
}

.status-indicator.status-healthy {
	color: #00a32a;
}

.status-indicator.status-error {
	color: #d63638;
}

.status-indicator.status-unknown {
	color: #8c8f94;
}

.status-details {
	border-top: 1px solid #f0f0f1;
	padding-top: 15px;
}

.status-row {
	display: flex;
	margin-bottom: 8px;
}

.status-row strong {
	min-width: 120px;
	margin-right: 10px;
}

.status-row.error {
	color: #d63638;
}

.config-checks {
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.config-check {
	display: flex;
	align-items: center;
	gap: 8px;
}

.config-check.check-pass {
	color: #00a32a;
}

.config-check.check-fail {
	color: #d63638;
}

.config-warning {
	margin-top: 15px;
	padding: 10px;
	background: #fff3cd;
	border: 1px solid #ffeaa7;
	border-radius: 4px;
	color: #664d03;
}

.health-result {
	padding: 15px;
	border-radius: 4px;
	margin-bottom: 10px;
}

.health-result.success {
	background: #d1ecf1;
	border: 1px solid #bee5eb;
	color: #0c5460;
}

.health-result.error {
	background: #f8d7da;
	border: 1px solid #f5c2c7;
	color: #721c24;
}

.health-result h5 {
	margin: 0 0 10px 0;
	display: flex;
	align-items: center;
	gap: 8px;
}

.result-details {
	font-size: 14px;
}

.result-row {
	margin-bottom: 5px;
}

.result-row pre {
	background: rgba(0,0,0,0.1);
	padding: 10px;
	border-radius: 4px;
	margin-top: 5px;
	overflow-x: auto;
	font-size: 12px;
}

.cors-domains {
	margin-top: 15px;
}

.cors-domains ul {
	margin-left: 20px;
	font-family: monospace;
	font-size: 14px;
}

.spin {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	from { transform: rotate(0deg); }
	to { transform: rotate(360deg); }
}
</style>

