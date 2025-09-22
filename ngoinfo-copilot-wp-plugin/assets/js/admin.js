/**
 * Admin JavaScript for NGOInfo Copilot
 */

jQuery(document).ready(function($) {
	'use strict';

	// Health Check functionality
	var HealthCheck = {
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			$('#run-health-check').on('click', this.runHealthCheck.bind(this));
			$('#run-jwt-diagnostics').on('click', this.runJwtDiagnostics.bind(this));
		},

		runHealthCheck: function() {
			var $button = $('#run-health-check');
			var $results = $('#health-check-results');
			var $content = $results.find('.result-content');
			
			// Disable button and show loading state
			$button.prop('disabled', true);
			$button.find('.dashicons').addClass('spin');
			
			// Safer button text updates
			var buttonText = $button.find('span').length > 0 ? 
				$button.find('span:last-child') : $button;
			buttonText.text(ngoinfo_copilot_admin.strings?.checking || 'Checking...');
			
			// Clear previous results
			$results.hide();
			$content.empty();
			
			// Make AJAX request
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ngoinfo_copilot_health_check',
					nonce: ngoinfo_copilot_admin.nonce
				},
				timeout: 45000, // 45 second timeout
				success: this.handleHealthCheckSuccess.bind(this, $button, $results, $content),
				error: this.handleHealthCheckError.bind(this, $button, $results, $content)
			});
		},

		handleHealthCheckSuccess: function($button, $results, $content, response) {
			this.resetButton($button);
			$results.show();
			
			if (response.success) {
				$content.html(this.buildSuccessHTML(response.data));
			} else {
				$content.html(this.buildErrorHTML(response.data));
			}
		},

		handleHealthCheckError: function($button, $results, $content, xhr, status, error) {
			this.resetButton($button);
			$results.show();
			
			var errorMessage = 'Request failed. Please try again.';
			if (status === 'timeout') {
				errorMessage = 'Request timed out. The API may be slow to respond.';
			} else if (xhr.status === 0) {
				errorMessage = 'Network error. Please check your connection.';
			}
			
			$content.html(
				'<div class="health-result error">' +
				'<h5><span class="dashicons dashicons-warning"></span>' + errorMessage + '</h5>' +
				'</div>'
			);
		},

		resetButton: function($button) {
			$button.prop('disabled', false);
			$button.find('.dashicons').removeClass('spin');
			
			// Safer button text updates
			var buttonText = $button.find('span').length > 0 ? 
				$button.find('span:last-child') : $button;
			buttonText.text(ngoinfo_copilot_admin.strings?.run_check || 'Run Health Check');
		},

		buildSuccessHTML: function(data) {
			var html = '<div class="health-result success">';
			html += '<h5><span class="dashicons dashicons-yes-alt"></span>' + (data.message || 'Health check successful!') + '</h5>';
			html += '<div class="result-details">';
			
			if (data.status_code) {
				html += '<div class="result-row"><strong>Status Code:</strong> ' + data.status_code + '</div>';
			}
			
			if (data.duration !== undefined) {
				html += '<div class="result-row"><strong>Response Time:</strong> ' + data.duration + 'ms</div>';
			}
			
			if (data.response) {
				html += '<div class="result-row"><strong>Response:</strong><br>';
				html += '<pre>' + JSON.stringify(data.response, null, 2) + '</pre></div>';
			}
			
			html += '</div></div>';
			return html;
		},

		buildErrorHTML: function(data) {
			var html = '<div class="health-result error">';
			html += '<h5><span class="dashicons dashicons-warning"></span>' + (data.message || 'Health check failed') + '</h5>';
			html += '<div class="result-details">';
			
			if (data.status_code) {
				html += '<div class="result-row"><strong>Status Code:</strong> ' + data.status_code + '</div>';
			}
			
			if (data.duration !== undefined) {
				html += '<div class="result-row"><strong>Duration:</strong> ' + data.duration + 'ms</div>';
			}
			
			if (data.error) {
				html += '<div class="result-row"><strong>Error:</strong> ' + this.escapeHtml(data.error) + '</div>';
			}
			
			html += '</div></div>';
			return html;
		},

		escapeHtml: function(text) {
			var map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return text.replace(/[&<>"']/g, function(m) { return map[m]; });
		},

		runJwtDiagnostics: function() {
			var $button = $('#run-jwt-diagnostics');
			var $results = $('#jwt-diagnostics-results');
			var $content = $results.find('.result-content');
			
			// Disable button and show loading state
			$button.prop('disabled', true);
			$button.text('Running Diagnostics...');
			
			// Clear previous results
			$results.hide();
			$content.empty();
			
			// Make AJAX request
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ngoinfo_copilot_jwt_diagnostics',
					nonce: ngoinfo_copilot_admin.nonce
				},
				timeout: 20000, // 20 second timeout
				success: this.handleJwtDiagnosticsSuccess.bind(this, $button, $results, $content),
				error: this.handleJwtDiagnosticsError.bind(this, $button, $results, $content)
			});
		},

		handleJwtDiagnosticsSuccess: function($button, $results, $content, response) {
			this.resetJwtButton($button);
			$results.show();
			
			if (response.success) {
				$content.html(this.buildJwtSuccessHTML(response.data));
			} else {
				$content.html(this.buildJwtErrorHTML(response.data));
			}
		},

		handleJwtDiagnosticsError: function($button, $results, $content, xhr, status, error) {
			this.resetJwtButton($button);
			$results.show();
			
			var errorMessage = 'Request failed. Please try again.';
			if (status === 'timeout') {
				errorMessage = 'Request timed out. The API may be slow to respond.';
			} else if (xhr.status === 0) {
				errorMessage = 'Network error. Please check your connection.';
			}
			
			$content.html(
				'<div class="health-result error">' +
				'<h5><span class="dashicons dashicons-warning"></span>' + errorMessage + '</h5>' +
				'</div>'
			);
		},

		resetJwtButton: function($button) {
			$button.prop('disabled', false);
			$button.text('Run JWT Diagnostics');
		},

		buildJwtSuccessHTML: function(data) {
			var html = '<div class="health-result success">';
			html += '<h5><span class="dashicons dashicons-yes-alt"></span>' + (data.note || 'JWT Diagnostics successful!') + '</h5>';
			html += '<div class="result-details">';
			
			if (data.status_code) {
				html += '<div class="result-row"><strong>Status Code:</strong> ' + data.status_code + '</div>';
			}
			
			if (data.duration_ms !== undefined) {
				html += '<div class="result-row"><strong>Response Time:</strong> ' + data.duration_ms + 'ms</div>';
			}
			
			if (data.body_decoded) {
				html += '<div class="result-row"><strong>Decoded Claims:</strong><br>';
				html += '<pre>' + this.prettyJson(data.body_decoded) + '</pre></div>';
			}
			
			html += '</div></div>';
			return html;
		},

		buildJwtErrorHTML: function(data) {
			var html = '<div class="health-result error">';
			html += '<h5><span class="dashicons dashicons-warning"></span>' + (data.message || 'JWT Diagnostics failed') + '</h5>';
			html += '<div class="result-details">';
			
			if (data.status_code) {
				html += '<div class="result-row"><strong>Status Code:</strong> ' + data.status_code + '</div>';
			}
			
			if (data.duration_ms !== undefined) {
				html += '<div class="result-row"><strong>Duration:</strong> ' + data.duration_ms + 'ms</div>';
			}
			
			html += '</div></div>';
			return html;
		},

		prettyJson: function(obj) {
			return JSON.stringify(obj, null, 2);
		}
	};

	// Settings Form enhancements
	var SettingsForm = {
		init: function() {
			this.bindEvents();
			this.checkConfiguration();
		},

		bindEvents: function() {
			$('#api_base_url, #environment').on('change', this.validateApiUrl.bind(this));
			$('#jwt_secret').on('input', this.validateJwtSecret.bind(this));
			$('form').on('submit', this.beforeSubmit.bind(this));
		},

		validateApiUrl: function() {
			var $url = $('#api_base_url');
			var $env = $('#environment');
			var url = $url.val().trim();
			var env = $env.val();
			
			if (!url) return;
			
			// Basic URL validation
			try {
				new URL(url);
			} catch (e) {
				this.showFieldError($url, 'Please enter a valid URL.');
				return;
			}
			
			// HTTPS requirement for production
			if (env === 'production' && !url.startsWith('https://')) {
				this.showFieldError($url, 'Production environment requires HTTPS.');
				return;
			}
			
			this.clearFieldError($url);
		},

		validateJwtSecret: function() {
			var $secret = $('#jwt_secret');
			var secret = $secret.val();
			
			if (!secret) {
				this.clearFieldError($secret);
				return;
			}
			
			// Length check
			if (secret.length < 32) {
				this.showFieldError($secret, 'Secret must be at least 32 characters long.');
				return;
			}
			
			// Complexity check
			var hasLower = /[a-z]/.test(secret);
			var hasUpper = /[A-Z]/.test(secret);
			var hasNumber = /[0-9]/.test(secret);
			var hasSpecial = /[^a-zA-Z0-9]/.test(secret);
			
			if (!hasLower || !hasUpper || !hasNumber || !hasSpecial) {
				this.showFieldError($secret, 'Secret must contain uppercase, lowercase, numbers, and special characters.');
				return;
			}
			
			this.clearFieldError($secret);
		},

		showFieldError: function($field, message) {
			this.clearFieldError($field);
			$field.addClass('error');
			$field.after('<div class="field-error" style="color: #d63638; font-size: 12px; margin-top: 5px;">' + message + '</div>');
		},

		clearFieldError: function($field) {
			$field.removeClass('error');
			$field.siblings('.field-error').remove();
		},

		beforeSubmit: function(e) {
			// Remove any existing error messages
			$('.field-error').remove();
			
			// Validate all fields
			this.validateApiUrl();
			this.validateJwtSecret();
			
			// Check if there are any errors
			if ($('.field-error').length > 0) {
				e.preventDefault();
				$('html, body').animate({
					scrollTop: $('.field-error').first().offset().top - 100
				}, 500);
			}
		},

		checkConfiguration: function() {
			// Show warnings for incomplete configuration
			var hasApiUrl = $('#api_base_url').val().trim() !== '';
			var hasJwtSecret = $('.dashicons-yes-alt').closest('.status-item').length > 0;
			
			if (!hasApiUrl || !hasJwtSecret) {
				var $warning = $('<div class="notice notice-warning"><p><strong>Configuration Incomplete:</strong> Please configure both API Base URL and JWT Secret to use the plugin features.</p></div>');
				$('.wrap h1').after($warning);
			}
		}
	};

	// Initialize components
	HealthCheck.init();
	SettingsForm.init();

	// Add notice dismissal functionality
	$(document).on('click', '.notice-dismiss', function() {
		$(this).closest('.notice').fadeOut();
	});

	// Auto-refresh status after successful health check
	$(document).on('ajaxSuccess', function(event, xhr, settings) {
		if (settings.data && settings.data.indexOf('ngoinfo_copilot_health_check') !== -1) {
			// Reload status card after 1 second
			setTimeout(function() {
				location.reload();
			}, 1000);
		}
	});
});


