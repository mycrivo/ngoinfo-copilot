<?php
/**
 * Health panel template
 *
 * @package NGOInfo_Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$health = new NGOInfo_Copilot_Health();
$status = $health->get_health_status();
$last_result = $health->get_last_health_result();
?>

<div class="ngoinfo-health-panel">
	<div class="health-header">
		<h3><?php esc_html_e( 'API Health Monitor', 'ngoinfo-copilot' ); ?></h3>
	</div>

	<?php if ( ! empty( $last_result ) && ! empty( $last_result['message'] ) ) : ?>
		<div class="notice notice-info" style="margin-top:10px;">
			<p>
				<strong><?php esc_html_e( 'Last Check:', 'ngoinfo-copilot' ); ?></strong>
				<?php echo esc_html( $last_result['message'] ); ?>
				<?php if ( ! empty( $last_result['status_code'] ) ) : ?>
					(<?php echo esc_html( $last_result['status_code'] ); ?>)
				<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>

	<div class="health-actions" style="margin: 12px 0 20px;">
		<button id="run-health-check" class="button button-primary">
			<?php esc_html_e( 'Run Health Check', 'ngoinfo-copilot' ); ?>
		</button>
		<button id="run-jwt-diagnostics" class="button" style="margin-left:8px;">
			<?php esc_html_e( 'Run JWT Diagnostics', 'ngoinfo-copilot' ); ?>
		</button>
	</div>

	<div id="health-check-results" style="display:none;">
		<div class="result-content"></div>
	</div>

	<div id="jwt-diagnostics-results" style="display:none; margin-top:14px;">
		<div class="result-content"></div>
	</div>

	<p class="health-note">
		<em><?php esc_html_e( 'This check runs server-side from WordPress; it avoids browser CORS.', 'ngoinfo-copilot' ); ?></em>
	</p>

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
	<?php if ( $last_result ) : ?>
	<div class="health-results">
		<h4><?php esc_html_e( 'Latest Health Check Results', 'ngoinfo-copilot' ); ?></h4>
		<div class="health-check-content">
			<?php if ( $last_result['success'] ) : ?>
				<div class="health-result success">
					<h5>
						<span class="dashicons dashicons-yes-alt"></span>
						<?php echo esc_html( $last_result['message'] ); ?>
					</h5>
					<div class="result-details">
						<div class="result-row">
							<strong><?php esc_html_e( 'Status Code:', 'ngoinfo-copilot' ); ?></strong> 
							<?php echo esc_html( $last_result['status_code'] ); ?>
						</div>
						<div class="result-row">
							<strong><?php esc_html_e( 'Response Time:', 'ngoinfo-copilot' ); ?></strong> 
							<?php echo esc_html( $last_result['duration'] ); ?>ms
						</div>
						<?php if ( isset( $last_result['timestamp'] ) ) : ?>
						<div class="result-row">
							<strong><?php esc_html_e( 'Timestamp:', 'ngoinfo-copilot' ); ?></strong> 
							<?php echo esc_html( mysql2date( 'F j, Y g:i a', $last_result['timestamp'] ) ); ?>
						</div>
						<?php endif; ?>
						<?php if ( ! empty( $last_result['response'] ) ) : ?>
						<div class="result-row">
							<strong><?php esc_html_e( 'Response:', 'ngoinfo-copilot' ); ?></strong><br>
							<div class="response-data">
								<?php if ( isset( $last_result['response']['status'] ) && isset( $last_result['response']['db'] ) ) : ?>
									<div class="status-badge status-<?php echo esc_attr( $last_result['response']['status'] ); ?>">
										<?php 
										if ( 'ok' === $last_result['response']['status'] && 'ok' === $last_result['response']['db'] ) {
											echo '<span class="dashicons dashicons-yes-alt"></span> OK';
										} elseif ( 'degraded' === $last_result['response']['status'] ) {
											echo '<span class="dashicons dashicons-warning"></span> Degraded';
										} else {
											echo '<span class="dashicons dashicons-dismiss"></span> Down';
										}
										?>
									</div>
									<div class="db-status">
										<strong><?php esc_html_e( 'Database:', 'ngoinfo-copilot' ); ?></strong>
										<?php echo 'ok' === $last_result['response']['db'] ? '✓ OK' : '✗ Error'; ?>
									</div>
								<?php endif; ?>
								<?php if ( isset( $last_result['response']['version'] ) ) : ?>
									<div class="version-info">
										<strong><?php esc_html_e( 'Version:', 'ngoinfo-copilot' ); ?></strong>
										<?php echo esc_html( $last_result['response']['version'] ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
				<div class="health-result error">
					<h5>
						<span class="dashicons dashicons-warning"></span>
						<?php echo esc_html( $last_result['message'] ); ?>
					</h5>
					<div class="result-details">
						<?php if ( $last_result['status_code'] ) : ?>
						<div class="result-row">
							<strong><?php esc_html_e( 'Status Code:', 'ngoinfo-copilot' ); ?></strong> 
							<?php echo esc_html( $last_result['status_code'] ); ?>
						</div>
						<?php endif; ?>
						<div class="result-row">
							<strong><?php esc_html_e( 'Duration:', 'ngoinfo-copilot' ); ?></strong> 
							<?php echo esc_html( $last_result['duration'] ); ?>ms
						</div>
						<?php if ( isset( $last_result['timestamp'] ) ) : ?>
						<div class="result-row">
							<strong><?php esc_html_e( 'Timestamp:', 'ngoinfo-copilot' ); ?></strong> 
							<?php echo esc_html( mysql2date( 'F j, Y g:i a', $last_result['timestamp'] ) ); ?>
						</div>
						<?php endif; ?>
						<?php if ( ! empty( $last_result['error'] ) ) : ?>
						<div class="result-row">
							<strong><?php esc_html_e( 'Error:', 'ngoinfo-copilot' ); ?></strong> 
							<?php 
							if ( is_array( $last_result['error'] ) ) {
								echo esc_html( $last_result['error']['message'] );
								if ( ! empty( $last_result['error']['request_id'] ) ) {
									echo '<br><small>Request ID: ' . esc_html( $last_result['error']['request_id'] ) . '</small>';
								}
							} else {
								echo esc_html( $last_result['error'] );
							}
							?>
						</div>
						<?php endif; ?>
						
						<?php if ( $last_result['status_code'] === 401 ) : ?>
						<div class="result-row troubleshooting">
							<strong><?php esc_html_e( 'Troubleshooting:', 'ngoinfo-copilot' ); ?></strong>
							<span style="color: #d63638;"><?php esc_html_e( 'JWT secret mismatch. Verify the secret matches your backend configuration.', 'ngoinfo-copilot' ); ?></span>
						</div>
						<?php elseif ( $last_result['status_code'] === 404 ) : ?>
						<div class="result-row troubleshooting">
							<strong><?php esc_html_e( 'Troubleshooting:', 'ngoinfo-copilot' ); ?></strong>
							<span style="color: #d63638;"><?php esc_html_e( 'Check API Base URL. Ensure it points to your backend with /healthcheck endpoint.', 'ngoinfo-copilot' ); ?></span>
						</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

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



<style>
.ngoinfo-health-panel {
	max-width: 800px;
}

.health-note {
	font-style: italic;
	color: #666;
	margin: 10px 0;
}

.response-data {
	margin-top: 10px;
	padding: 10px;
	background: #f9f9f9;
	border-radius: 4px;
}

.response-data .status-badge {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	padding: 5px 10px;
	border-radius: 4px;
	font-weight: 600;
	margin-bottom: 8px;
}

.response-data .status-badge.status-ok {
	background: #d1ecf1;
	color: #0c5460;
}

.response-data .status-badge.status-degraded {
	background: #fff3cd;
	color: #664d03;
}

.response-data .db-status,
.response-data .version-info {
	margin: 5px 0;
	font-size: 14px;
}

.result-row.troubleshooting {
	margin-top: 10px;
	padding: 10px;
	background: #fff3cd;
	border: 1px solid #ffeaa7;
	border-radius: 4px;
}

.health-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20px;
}

.health-status-card, .health-results, .health-config-card, .health-cors-card {
	background: #fff;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
	padding: 20px;
	margin-bottom: 20px;
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

.config-check {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 10px;
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
