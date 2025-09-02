/**
 * Public JavaScript for NGOInfo Copilot
 */

jQuery(document).ready(function($) {
	'use strict';

	// Usage widget functionality is mostly handled in the widget template
	// This file provides additional public-facing JavaScript functionality

	// Generic AJAX error handler
	function handleAjaxError(response, $container) {
		var errorMessage = 'An unexpected error occurred. Please try again.';
		
		if (response && response.data && response.data.message) {
			errorMessage = response.data.message;
		}
		
		if ($container) {
			$container.html(
				'<div class="ngoinfo-error">' +
				'<span class="dashicons dashicons-warning"></span>' +
				'<span>' + errorMessage + '</span>' +
				'</div>'
			);
		}
	}

	// Auto-refresh usage widgets periodically (optional)
	function autoRefreshUsageWidgets() {
		$('.ngoinfo-usage-widget').each(function() {
			var $widget = $(this);
			var $refreshBtn = $widget.find('.usage-refresh-btn');
			
			// Only auto-refresh if widget has refresh button and is not currently loading
			if ($refreshBtn.length && !$refreshBtn.prop('disabled') && !$widget.find('.usage-loading').is(':visible')) {
				// Auto-refresh every 5 minutes
				setTimeout(function() {
					if ($refreshBtn.is(':visible')) {
						$refreshBtn.trigger('click');
					}
				}, 300000); // 5 minutes
			}
		});
	}

	// Initialize auto-refresh (only if user is logged in and widgets are present)
	if ($('.ngoinfo-usage-widget').length && $('body').hasClass('logged-in')) {
		autoRefreshUsageWidgets();
	}

	// Shortcode attribute handling
	$('.ngoinfo-usage-widget[data-auto-refresh]').each(function() {
		var $widget = $(this);
		var autoRefresh = $widget.data('auto-refresh');
		var refreshInterval = parseInt($widget.data('refresh-interval')) || 300000; // Default 5 minutes
		
		if (autoRefresh === 'true' || autoRefresh === true) {
			setInterval(function() {
				var $refreshBtn = $widget.find('.usage-refresh-btn');
				if ($refreshBtn.length && !$refreshBtn.prop('disabled')) {
					$refreshBtn.trigger('click');
				}
			}, refreshInterval);
		}
	});

	// Smooth animations for widget state changes
	$('.ngoinfo-usage-widget').on('click', '.usage-refresh-btn, .usage-retry-btn', function() {
		var $widget = $(this).closest('.ngoinfo-usage-widget');
		
		// Add subtle animation
		$widget.addClass('refreshing');
		
		// Remove animation class after refresh completes
		setTimeout(function() {
			$widget.removeClass('refreshing');
		}, 2000);
	});

	// Accessibility enhancements
	$('.ngoinfo-usage-widget .usage-refresh-btn').on('keydown', function(e) {
		// Allow space or enter to trigger refresh
		if (e.which === 13 || e.which === 32) {
			e.preventDefault();
			$(this).trigger('click');
		}
	});

	// Handle widget themes dynamically
	$('.ngoinfo-usage-widget[data-theme]').each(function() {
		var $widget = $(this);
		var theme = $widget.data('theme');
		
		if (theme && !$widget.hasClass('theme-' + theme)) {
			$widget.addClass('theme-' + theme);
		}
	});

	// Error reporting helper (for debugging)
	function reportError(error, context) {
		if (window.console && console.error) {
			console.error('NGOInfo Copilot Error:', error, 'Context:', context);
		}
		
		// Could be extended to send errors to backend for logging
		// if (window.ngoinfo_copilot_public && ngoinfo_copilot_public.ajax_url) {
		//     $.post(ngoinfo_copilot_public.ajax_url, {
		//         action: 'ngoinfo_copilot_log_error',
		//         error: error.toString(),
		//         context: context,
		//         nonce: ngoinfo_copilot_public.nonce
		//     });
		// }
	}

	// Global error handler for widget-related errors
	window.addEventListener('error', function(e) {
		if (e.target && $(e.target).closest('.ngoinfo-usage-widget').length) {
			reportError(e.error || e.message, 'usage-widget');
		}
	});

	// Utility functions for external use
	window.ngoinfoWidgets = {
		refreshUsage: function(widgetId) {
			var $widget = widgetId ? $('#' + widgetId) : $('.ngoinfo-usage-widget').first();
			var $refreshBtn = $widget.find('.usage-refresh-btn');
			
			if ($refreshBtn.length) {
				$refreshBtn.trigger('click');
				return true;
			}
			return false;
		},
		
		setTheme: function(widgetId, theme) {
			var $widget = $('#' + widgetId);
			if ($widget.length) {
				$widget.removeClass(function(index, className) {
					return (className.match(/(^|\s)theme-\S+/g) || []).join(' ');
				});
				$widget.addClass('theme-' + theme);
				return true;
			}
			return false;
		}
	};

	// Console log for debugging (only in development)
	if (window.location.hostname === 'localhost' || window.location.hostname.includes('staging')) {
		console.log('NGOInfo Copilot Public JS loaded');
		console.log('Widgets found:', $('.ngoinfo-usage-widget').length);
	}
});


