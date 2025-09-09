<?php
/**
 * Usage widget public template
 *
 * @package NGOInfo\Copilot
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Format usage data
$formatted_data = $this->format_usage_data( $usage_data['success'] ? $usage_data['data'] : null );

// Generate CSS classes
$widget_classes = array( 'ngoinfo-usage-widget' );
$widget_classes[] = 'theme-' . sanitize_html_class( $atts['theme'] );
$widget_classes[] = 'status-' . $formatted_data['status'];

?>

<div id="<?php echo esc_attr( $widget_id ); ?>" class="<?php echo esc_attr( implode( ' ', $widget_classes ) ); ?>">
	<div class="usage-header">
		<h4><?php esc_html_e( 'NGO Copilot Usage', 'ngoinfo-copilot' ); ?></h4>
		<?php if ( 'true' === $atts['show_refresh'] ) : ?>
		<button type="button" class="usage-refresh-btn" title="<?php esc_attr_e( 'Refresh usage data', 'ngoinfo-copilot' ); ?>">
			<span class="dashicons dashicons-update"></span>
		</button>
		<?php endif; ?>
	</div>

	<div class="usage-content">
		<?php if ( $usage_data['success'] ) : ?>
			
			<!-- Usage Display -->
			<div class="usage-display">
				<div class="usage-plan">
					<span class="plan-label"><?php esc_html_e( 'Plan:', 'ngoinfo-copilot' ); ?></span>
					<span class="plan-name"><?php echo esc_html( $formatted_data['plan'] ); ?></span>
				</div>

				<div class="usage-stats">
					<div class="usage-bar-container">
						<div class="usage-bar">
							<div class="usage-fill" style="width: <?php echo esc_attr( $formatted_data['usage_percent'] ); ?>%;"></div>
						</div>
						<div class="usage-text">
							<?php
							printf(
								/* translators: %1$d: used proposals, %2$d: total limit */
								esc_html__( '%1$d of %2$d proposals used', 'ngoinfo-copilot' ),
								$formatted_data['used'],
								$formatted_data['monthly_limit']
							);
							?>
						</div>
					</div>

					<div class="usage-details">
						<div class="usage-remaining">
							<?php if ( $formatted_data['remaining'] > 0 ) : ?>
								<span class="remaining-count"><?php echo esc_html( $formatted_data['remaining'] ); ?></span>
								<span class="remaining-label"><?php esc_html_e( 'remaining', 'ngoinfo-copilot' ); ?></span>
							<?php else : ?>
								<span class="limit-reached"><?php esc_html_e( 'Monthly limit reached', 'ngoinfo-copilot' ); ?></span>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $formatted_data['reset_at'] ) ) : ?>
						<div class="usage-reset">
							<span class="reset-label"><?php esc_html_e( 'Resets:', 'ngoinfo-copilot' ); ?></span>
							<span class="reset-date"><?php echo esc_html( $formatted_data['reset_at'] ); ?></span>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( 'warning' === $formatted_data['status'] ) : ?>
				<div class="usage-warning">
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'You\'re approaching your monthly limit.', 'ngoinfo-copilot' ); ?>
				</div>
				<?php elseif ( 'limit_reached' === $formatted_data['status'] ) : ?>
				<div class="usage-limit-reached">
					<span class="dashicons dashicons-dismiss"></span>
					<?php esc_html_e( 'Monthly limit reached. Upgrade for more proposals.', 'ngoinfo-copilot' ); ?>
				</div>
				<?php endif; ?>
			</div>

		<?php else : ?>
			
			<!-- Error Display -->
			<div class="usage-error">
				<div class="error-icon">
					<span class="dashicons dashicons-warning"></span>
				</div>
				<div class="error-content">
					<p class="error-message">
						<?php
						if ( ! empty( $usage_data['error']['message'] ) ) {
							echo esc_html( $usage_data['error']['message'] );
						} else {
							esc_html_e( 'Unable to load usage data.', 'ngoinfo-copilot' );
						}
						?>
					</p>
					
					<?php if ( ! empty( $usage_data['error']['request_id'] ) ) : ?>
					<p class="error-request-id">
						<small>
							<?php
							printf(
								/* translators: %s: request ID */
								esc_html__( 'Request ID: %s', 'ngoinfo-copilot' ),
								esc_html( $usage_data['error']['request_id'] )
							);
							?>
						</small>
					</p>
					<?php endif; ?>

					<?php if ( 'true' === $atts['show_refresh'] ) : ?>
					<button type="button" class="usage-retry-btn">
						<?php esc_html_e( 'Try Again', 'ngoinfo-copilot' ); ?>
					</button>
					<?php endif; ?>
				</div>
			</div>

		<?php endif; ?>
	</div>

	<!-- Loading overlay -->
	<div class="usage-loading" style="display: none;">
		<div class="loading-spinner">
			<span class="dashicons dashicons-update spin"></span>
		</div>
		<p><?php esc_html_e( 'Loading usage data...', 'ngoinfo-copilot' ); ?></p>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	var $widget = $('#<?php echo esc_js( $widget_id ); ?>');
	var $refreshBtn = $widget.find('.usage-refresh-btn');
	var $retryBtn = $widget.find('.usage-retry-btn');
	var $loading = $widget.find('.usage-loading');
	var $content = $widget.find('.usage-content');

	function refreshUsage() {
		$loading.show();
		$content.hide();
		$refreshBtn.prop('disabled', true);

		$.post(ngoinfo_copilot_public.ajax_url, {
			action: 'ngoinfo_copilot_refresh_usage',
			nonce: ngoinfo_copilot_public.nonce
		}, function(response) {
			// Reload the widget content
			location.reload();
		}).fail(function() {
			$loading.hide();
			$content.show();
			$refreshBtn.prop('disabled', false);
			
			// Show error message
			$widget.find('.usage-content').html(
				'<div class="usage-error">' +
				'<div class="error-icon"><span class="dashicons dashicons-warning"></span></div>' +
				'<div class="error-content">' +
				'<p class="error-message"><?php esc_html_e( 'Failed to refresh usage data. Please try again.', 'ngoinfo-copilot' ); ?></p>' +
				'</div>' +
				'</div>'
			);
		});
	}

	$refreshBtn.on('click', refreshUsage);
	$retryBtn.on('click', refreshUsage);
});
</script>




