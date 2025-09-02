<?php
/**
 * Settings page template
 *
 * @package NGOInfo\Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_info = ( new NGOInfo\Copilot\Settings() )->get_status_info();
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a href="?page=<?php echo esc_attr( $this->page_slug ); ?>&tab=settings" 
		   class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Settings', 'ngoinfo-copilot' ); ?>
		</a>
		<a href="?page=<?php echo esc_attr( $this->page_slug ); ?>&tab=health" 
		   class="nav-tab <?php echo 'health' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Health', 'ngoinfo-copilot' ); ?>
		</a>
	</h2>

	<?php if ( 'settings' === $active_tab ) : ?>
		
		<!-- Status Card -->
		<div class="ngoinfo-status-card">
			<h3><?php esc_html_e( 'Connection Status', 'ngoinfo-copilot' ); ?></h3>
			<div class="status-grid">
				<div class="status-item">
					<strong><?php esc_html_e( 'Environment:', 'ngoinfo-copilot' ); ?></strong>
					<span class="status-badge status-<?php echo esc_attr( $status_info['environment'] ); ?>">
						<?php echo esc_html( ucfirst( $status_info['environment'] ) ); ?>
					</span>
				</div>
				<div class="status-item">
					<strong><?php esc_html_e( 'API Base URL:', 'ngoinfo-copilot' ); ?></strong>
					<code><?php echo esc_html( $status_info['api_base_url'] ?: __( 'Not configured', 'ngoinfo-copilot' ) ); ?></code>
				</div>
				<div class="status-item">
					<strong><?php esc_html_e( 'JWT Secret:', 'ngoinfo-copilot' ); ?></strong>
					<?php if ( $status_info['has_jwt_secret'] ) : ?>
						<span class="dashicons dashicons-yes-alt" style="color: green;"></span>
						<span><?php esc_html_e( 'Configured', 'ngoinfo-copilot' ); ?></span>
					<?php else : ?>
						<span class="dashicons dashicons-warning" style="color: orange;"></span>
						<span><?php esc_html_e( 'Not configured', 'ngoinfo-copilot' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="status-item">
					<strong><?php esc_html_e( 'Last Health Check:', 'ngoinfo-copilot' ); ?></strong>
					<?php if ( $status_info['last_health_check'] ) : ?>
						<span><?php echo esc_html( $status_info['last_health_check'] ); ?></span>
					<?php else : ?>
						<span><?php esc_html_e( 'Never', 'ngoinfo-copilot' ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $status_info['last_error'] ) : ?>
				<div class="status-item error">
					<strong><?php esc_html_e( 'Last Error:', 'ngoinfo-copilot' ); ?></strong>
					<span><?php echo esc_html( $status_info['last_error'] ); ?></span>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Settings Form -->
		<form method="post" action="options.php">
			<?php
			settings_fields( $this->settings_group );
			do_settings_sections( $this->page_slug );
			submit_button();
			?>
		</form>

		<!-- Auth Self-Test -->
		<div class="ngoinfo-auth-test-card">
			<h3><?php esc_html_e( 'Authentication Self-Test', 'ngoinfo-copilot' ); ?></h3>
			<p><?php esc_html_e( 'Test JWT generation and API authentication to verify your configuration.', 'ngoinfo-copilot' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=ngoinfo_copilot_auth_test' ) ); ?>" class="button button-secondary">
				<span class="dashicons dashicons-admin-users"></span>
				<?php esc_html_e( 'Run Auth Self-Test', 'ngoinfo-copilot' ); ?>
			</a>
		</div>

		<!-- CORS Information -->
		<div class="ngoinfo-info-card">
			<h3><?php esc_html_e( 'CORS Requirements', 'ngoinfo-copilot' ); ?></h3>
			<p><?php esc_html_e( 'The backend API must allow your WordPress site\'s origin for CORS requests. This was configured in Phase 0 of the backend hardening:', 'ngoinfo-copilot' ); ?></p>
			<ul>
				<li><strong><?php esc_html_e( 'Production:', 'ngoinfo-copilot' ); ?></strong> https://ngoinfo.org, https://www.ngoinfo.org</li>
				<li><strong><?php esc_html_e( 'Staging:', 'ngoinfo-copilot' ); ?></strong> https://staging.ngoinfo.org</li>
			</ul>
			<p><?php esc_html_e( 'If you experience CORS errors, verify your domain is included in the backend CORS_ALLOWED_ORIGINS configuration.', 'ngoinfo-copilot' ); ?></p>
		</div>

	<?php elseif ( 'health' === $active_tab ) : ?>
		
		<?php
		// Include health panel
		require_once NGOINFO_COPILOT_PLUGIN_DIR . 'admin/views/health-panel.php';
		?>

	<?php endif; ?>
</div>

<style>
.ngoinfo-status-card, .ngoinfo-info-card, .ngoinfo-auth-test-card {
	background: #fff;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
	padding: 20px;
	margin: 20px 0;
}

.ngoinfo-status-card h3, .ngoinfo-info-card h3, .ngoinfo-auth-test-card h3 {
	margin-top: 0;
}

.status-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 15px;
}

.status-item {
	display: flex;
	align-items: center;
	gap: 8px;
}

.status-item.error {
	color: #d63638;
}

.status-badge {
	padding: 2px 8px;
	border-radius: 4px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
}

.status-staging {
	background: #fff3cd;
	color: #664d03;
}

.status-production {
	background: #d1ecf1;
	color: #0c5460;
}

.ngoinfo-info-card ul {
	margin-left: 20px;
}
</style>

