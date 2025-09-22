/**
 * Public JavaScript for NGOInfo Copilot
 */

jQuery(document).ready(function($) {
	'use strict';

	// Usage Widget functionality
	var UsageWidget = {
		init: function() {
			this.bindEvents();
			this.initializeWidgets();
		},

		bindEvents: function() {
			$(document).on('click', '.usage-refresh-btn, .usage-retry-btn', this.refreshUsage.bind(this));
		},

		initializeWidgets: function() {
			// Initialize any widgets that need setup
			$('.ngoinfo-usage-widget').each(function() {
				var $widget = $(this);
				var widgetId = $widget.attr('id');
				
				// Store widget reference for AJAX updates
				if (widgetId) {
					UsageWidget.widgets = UsageWidget.widgets || {};
					UsageWidget.widgets[widgetId] = $widget;
				}
			});
		},

		refreshUsage: function(e) {
			e.preventDefault();
			
			var $button = $(e.currentTarget);
			var $widget = $button.closest('.ngoinfo-usage-widget');
			var $loading = $widget.find('.usage-loading');
			var $content = $widget.find('.usage-content');
			
			// Show loading state
			this.showLoading($widget, $button);
			
			// Make AJAX request
			$.ajax({
				url: ngoinfo_copilot_public.ajax_url,
				type: 'POST',
				data: {
					action: 'ngoinfo_copilot_refresh_usage',
					nonce: ngoinfo_copilot_public.nonce
				},
				timeout: 30000,
				success: this.handleRefreshSuccess.bind(this, $widget, $button),
				error: this.handleRefreshError.bind(this, $widget, $button)
			});
		},

		showLoading: function($widget, $button) {
			var $loading = $widget.find('.usage-loading');
			var $content = $widget.find('.usage-content');
			
			// Disable button
			$button.prop('disabled', true);
			$button.find('.dashicons').addClass('spin');
			
			// Show loading overlay
			$loading.show();
			$content.hide();
		},

		hideLoading: function($widget, $button) {
			var $loading = $widget.find('.usage-loading');
			var $content = $widget.find('.usage-content');
			
			// Re-enable button
			$button.prop('disabled', false);
			$button.find('.dashicons').removeClass('spin');
			
			// Hide loading overlay
			$loading.hide();
			$content.show();
		},

		handleRefreshSuccess: function($widget, $button, response) {
			if (response.success && response.data) {
				// Update widget with new data
				this.updateWidgetContent($widget, response.data);
			} else {
				// Show error from API
				var errorMessage = response.data?.error?.message || 'Failed to refresh usage data.';
				this.showError($widget, errorMessage, response.data?.error?.request_id);
			}
			
			this.hideLoading($widget, $button);
		},

		handleRefreshError: function($widget, $button, xhr, status, error) {
			var errorMessage = 'Failed to refresh usage data. Please try again.';
			
			if (status === 'timeout') {
				errorMessage = 'Request timed out. Please try again.';
			} else if (xhr.status === 0) {
				errorMessage = 'Network error. Please check your connection.';
			} else if (xhr.status === 401) {
				errorMessage = 'You must be logged in to view usage data.';
			}
			
			this.showError($widget, errorMessage);
			this.hideLoading($widget, $button);
		},

		updateWidgetContent: function($widget, data) {
			if (!data || !data.data) {
				this.showError($widget, 'Invalid usage data received.');
				return;
			}

			var usageData = data.data;
			var formattedData = this.formatUsageData(usageData);
			
			// Update usage stats
			this.updateUsageStats($widget, formattedData);
			
			// Update status classes
			$widget.removeClass('status-normal status-warning status-limit_reached');
			$widget.addClass('status-' + formattedData.status);
			
			// Show success content
			$widget.find('.usage-content').html(this.buildUsageHTML(formattedData));
		},

		formatUsageData: function(data) {
			var used = parseInt(data.used) || 0;
			var monthlyLimit = parseInt(data.monthly_limit) || 0;
			var remaining = Math.max(0, monthlyLimit - used);
			var usagePercent = monthlyLimit > 0 ? Math.min(100, Math.round((used / monthlyLimit) * 100)) : 0;
			
			var status = 'normal';
			if (usagePercent >= 100) {
				status = 'limit_reached';
			} else if (usagePercent >= 80) {
				status = 'warning';
			}
			
			var resetAt = '';
			if (data.reset_at) {
				var resetDate = new Date(data.reset_at);
				resetAt = resetDate.toLocaleDateString();
			}
			
			return {
				plan: data.plan || 'Free Plan',
				used: used,
				monthly_limit: monthlyLimit,
				remaining: remaining,
				usage_percent: usagePercent,
				reset_at: resetAt,
				status: status
			};
		},

		updateUsageStats: function($widget, data) {
			// Update usage bar
			$widget.find('.usage-fill').css('width', data.usage_percent + '%');
			
			// Update text content
			$widget.find('.usage-text').text(data.used + ' of ' + data.monthly_limit + ' proposals used');
			$widget.find('.remaining-count').text(data.remaining);
			$widget.find('.reset-date').text(data.reset_at);
			$widget.find('.plan-name').text(data.plan);
		},

		buildUsageHTML: function(data) {
			var html = '<div class="usage-display">';
			
			// Plan info
			html += '<div class="usage-plan">';
			html += '<span class="plan-label">Plan:</span>';
			html += '<span class="plan-name">' + this.escapeHtml(data.plan) + '</span>';
			html += '</div>';
			
			// Usage stats
			html += '<div class="usage-stats">';
			html += '<div class="usage-bar-container">';
			html += '<div class="usage-bar">';
			html += '<div class="usage-fill" style="width: ' + data.usage_percent + '%;"></div>';
			html += '</div>';
			html += '<div class="usage-text">' + data.used + ' of ' + data.monthly_limit + ' proposals used</div>';
			html += '</div>';
			
			// Usage details
			html += '<div class="usage-details">';
			html += '<div class="usage-remaining">';
			if (data.remaining > 0) {
				html += '<span class="remaining-count">' + data.remaining + '</span>';
				html += '<span class="remaining-label">remaining</span>';
			} else {
				html += '<span class="limit-reached">Monthly limit reached</span>';
			}
			html += '</div>';
			
			if (data.reset_at) {
				html += '<div class="usage-reset">';
				html += '<span class="reset-label">Resets:</span>';
				html += '<span class="reset-date">' + data.reset_at + '</span>';
				html += '</div>';
			}
			html += '</div>';
			html += '</div>';
			
			// Warning messages
			if (data.status === 'warning') {
				html += '<div class="usage-warning">';
				html += '<span class="dashicons dashicons-warning"></span>';
				html += "You're approaching your monthly limit.";
				html += '</div>';
			} else if (data.status === 'limit_reached') {
				html += '<div class="usage-limit-reached">';
				html += '<span class="dashicons dashicons-dismiss"></span>';
				html += 'Monthly limit reached. Upgrade for more proposals.';
				html += '</div>';
			}
			
			html += '</div>';
			return html;
		},

		showError: function($widget, message, requestId) {
			var html = '<div class="usage-error">';
			html += '<div class="error-icon"><span class="dashicons dashicons-warning"></span></div>';
			html += '<div class="error-content">';
			html += '<p class="error-message">' + this.escapeHtml(message) + '</p>';
			
			if (requestId) {
				html += '<p class="error-request-id"><small>Request ID: ' + this.escapeHtml(requestId) + '</small></p>';
			}
			
			html += '<button type="button" class="usage-retry-btn">Try Again</button>';
			html += '</div>';
			html += '</div>';
			
			$widget.find('.usage-content').html(html);
		},

		escapeHtml: function(text) {
			var map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
		}
	};

	// Auto-refresh functionality
	var AutoRefresh = {
		init: function() {
			this.startAutoRefresh();
		},

		startAutoRefresh: function() {
			// Auto-refresh usage widgets every 5 minutes
			setInterval(function() {
				$('.ngoinfo-usage-widget').each(function() {
					var $widget = $(this);
					var $refreshBtn = $widget.find('.usage-refresh-btn');
					
					// Only auto-refresh if widget is visible and has data
					if ($widget.is(':visible') && $widget.find('.usage-display').length > 0) {
						$refreshBtn.trigger('click');
					}
				});
			}, 5 * 60 * 1000); // 5 minutes
		}
	};

	// Error handling for AJAX requests
	$(document).ajaxError(function(event, xhr, settings, error) {
		// Only handle our plugin's AJAX requests
		if (settings.data && typeof settings.data === 'string') {
			if (settings.data.indexOf('ngoinfo_copilot') !== -1) {
				console.error('NGOInfo Copilot AJAX Error:', {
					url: settings.url,
					status: xhr.status,
					error: error,
					response: xhr.responseText
				});
			}
		}
	});

	// Initialize components
	UsageWidget.init();
	AutoRefresh.init();

	// Accessibility enhancements
	$(document).on('keydown', '.usage-refresh-btn, .usage-retry-btn', function(e) {
		// Allow Enter key to trigger button click
		if (e.keyCode === 13) {
			$(this).trigger('click');
		}
	});

	// Touch device enhancements
	if ('ontouchstart' in window) {
		$('.ngoinfo-usage-widget').addClass('touch-device');
	}
});








