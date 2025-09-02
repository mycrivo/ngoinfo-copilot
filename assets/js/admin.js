/**
 * Admin JavaScript for NGOInfo Copilot
 */

jQuery(document).ready(function($) {
	'use strict';

	// Health check functionality is handled in the health-panel.php template
	// This file is reserved for additional admin JavaScript functionality

	// Form validation for settings
	var $settingsForm = $('form[action="options.php"]');
	if ($settingsForm.length) {
		$settingsForm.on('submit', function(e) {
			var isValid = true;
			var errors = [];

			// Validate API Base URL
			var $apiUrl = $('#api_base_url');
			if ($apiUrl.length && $apiUrl.val()) {
				var urlPattern = /^https?:\/\/.+/;
				if (!urlPattern.test($apiUrl.val())) {
					errors.push('API Base URL must be a valid HTTP or HTTPS URL.');
					$apiUrl.addClass('error');
					isValid = false;
				} else {
					$apiUrl.removeClass('error');
				}
			}

			// Validate JWT secret strength (client-side check)
			var $jwtSecret = $('#jwt_secret');
			if ($jwtSecret.length && $jwtSecret.val()) {
				var secret = $jwtSecret.val();
				if (secret.length < 32) {
					errors.push('JWT secret must be at least 32 characters long.');
					$jwtSecret.addClass('error');
					isValid = false;
				} else if (!/[a-z]/.test(secret) || !/[A-Z]/.test(secret) || !/[0-9]/.test(secret) || !/[^a-zA-Z0-9]/.test(secret)) {
					errors.push('JWT secret must contain lowercase letters, uppercase letters, numbers, and special characters.');
					$jwtSecret.addClass('error');
					isValid = false;
				} else {
					$jwtSecret.removeClass('error');
				}
			}

			// Display errors if any
			if (!isValid) {
				e.preventDefault();
				
				// Remove existing error notices
				$('.ngoinfo-validation-error').remove();
				
				// Add error notice
				var errorHtml = '<div class="notice notice-error ngoinfo-validation-error"><p><strong>Please correct the following errors:</strong></p><ul>';
				errors.forEach(function(error) {
					errorHtml += '<li>' + error + '</li>';
				});
				errorHtml += '</ul></div>';
				
				$('h1').after(errorHtml);
				
				// Scroll to top
				$('html, body').animate({
					scrollTop: 0
				}, 300);
			}
		});

		// Remove error styling on input
		$('#api_base_url, #jwt_secret').on('input', function() {
			$(this).removeClass('error');
		});
	}

	// Environment-based URL suggestions
	var $environment = $('#environment');
	var $apiUrl = $('#api_base_url');
	
	if ($environment.length && $apiUrl.length) {
		$environment.on('change', function() {
			var env = $(this).val();
			var currentUrl = $apiUrl.val();
			
			// Only suggest if URL is empty or looks like our default URLs
			if (!currentUrl || currentUrl.includes('api.ngoinfo.org') || currentUrl.includes('staging-api.ngoinfo.org')) {
				if (env === 'staging') {
					$apiUrl.val('https://staging-api.ngoinfo.org');
				} else if (env === 'production') {
					$apiUrl.val('https://api.ngoinfo.org');
				}
			}
		});
	}

	// JWT secret generator (optional enhancement)
	function generateSecureSecret(length) {
		length = length || 64;
		var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
		var secret = '';
		
		for (var i = 0; i < length; i++) {
			secret += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		
		return secret;
	}

	// Add secret generator button (if not already configured)
	var $jwtSecretField = $('#jwt_secret');
	if ($jwtSecretField.length && !$jwtSecretField.siblings('.generate-secret-btn').length) {
		var hasSecret = $jwtSecretField.siblings('.description').find('.dashicons-yes-alt').length > 0;
		
		if (!hasSecret) {
			$jwtSecretField.after(
				'<button type="button" class="button button-secondary generate-secret-btn" style="margin-left: 10px;">Generate Secure Secret</button>'
			);
			
			$('.generate-secret-btn').on('click', function(e) {
				e.preventDefault();
				var secret = generateSecureSecret(64);
				$jwtSecretField.val(secret);
				$jwtSecretField.removeClass('error');
				
				// Show confirmation
				$(this).text('Generated!').prop('disabled', true);
				setTimeout(function() {
					$('.generate-secret-btn').text('Generate Secure Secret').prop('disabled', false);
				}, 2000);
			});
		}
	}

	// Tab switching animation
	$('.nav-tab').on('click', function() {
		var $this = $(this);
		if (!$this.hasClass('nav-tab-active')) {
			// Add loading animation
			$this.append(' <span class="dashicons dashicons-update spin" style="font-size: 14px;"></span>');
		}
	});

	// Auto-save indication for forms
	var formChanged = false;
	$settingsForm.find('input, select, textarea').on('change input', function() {
		if (!formChanged) {
			formChanged = true;
			$('.submit .button-primary').text($('.submit .button-primary').text() + ' *');
		}
	});

	// Add tooltips for help text
	$('.description').each(function() {
		var $desc = $(this);
		var $field = $desc.siblings('input, select, textarea');
		
		if ($field.length) {
			$field.attr('title', $desc.text());
		}
	});

	// Settings page specific enhancements
	if ($('body').hasClass('settings_page_ngoinfo-copilot')) {
		// Add visual feedback for required fields
		$('input[required], select[required]').each(function() {
			var $field = $(this);
			var $label = $('label[for="' + $field.attr('id') + '"]');
			
			if ($label.length && $label.text().indexOf('*') === -1) {
				$label.html($label.html() + ' <span style="color: #d63638;">*</span>');
			}
		});
	}

	// Console log for debugging (only in development)
	if (window.location.hostname === 'localhost' || window.location.hostname.includes('staging')) {
		console.log('NGOInfo Copilot Admin JS loaded');
		
		// Expose some utilities for debugging
		window.ngoinfoDebug = {
			generateSecret: generateSecureSecret,
			version: 'NGOInfo Copilot WordPress Plugin v0.1.0'
		};
	}
});


