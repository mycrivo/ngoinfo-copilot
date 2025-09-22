/**
 * NGOInfo Copilot Generator Frontend JavaScript
 *
 * @package NGOInfo_Copilot
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize generator form
        initGeneratorForm();
    });

    /**
     * Initialize the generator form
     */
    function initGeneratorForm() {
        const $form = $('#ngoinfo-copilot-generate-form');
        if (!$form.length) {
            return;
        }

        $form.on('submit', handleFormSubmit);
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('#generate-btn');
        const $btnText = $submitBtn.find('.btn-text');
        const $btnSpinner = $submitBtn.find('.btn-spinner');
        const $statusMessage = $('#status-message');

        // Disable form and show loading state
        $form.find('input, button').prop('disabled', true);
        $btnText.hide();
        $btnSpinner.show();
        $statusMessage.hide();

        // Prepare form data
        const formData = {
            action: 'ngoinfo_copilot_generate',
            nonce: NGOInfoCopilotGen.nonce,
            donor: $form.find('#donor').val(),
            theme: $form.find('#theme').val(),
            country: $form.find('#country').val(),
            title: $form.find('#title').val(),
            budget: $form.find('#budget').val(),
            duration: $form.find('#duration').val()
        };

        // Make AJAX request
        $.ajax({
            url: NGOInfoCopilotGen.ajaxUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 120000, // 2 minutes timeout
            success: function(response) {
                handleSuccess(response);
            },
            error: function(xhr, status, error) {
                handleError(xhr, status, error);
            },
            complete: function() {
                // Re-enable form
                $form.find('input, button').prop('disabled', false);
                $btnText.show();
                $btnSpinner.hide();
            }
        });
    }

    /**
     * Handle successful response
     */
    function handleSuccess(response) {
        const $statusMessage = $('#status-message');
        
        if (response.success) {
            // Show success message
            $statusMessage.removeClass('error').addClass('success').html(
                '<div class="success-content">' +
                    '<h4>' + NGOInfoCopilotGen.msgs.success + '</h4>' +
                    '<div class="proposal-preview">' +
                        '<p><strong>Proposal ID:</strong> ' + escapeHtml(response.data.proposal_id) + '</p>' +
                        (response.data.preview ? '<div class="preview-content">' + escapeHtml(response.data.preview) + '</div>' : '') +
                    '</div>' +
                '</div>'
            ).show();

            // Scroll to message
            $statusMessage[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            // Clear form
            $('#ngoinfo-copilot-generate-form')[0].reset();
        } else {
            // Show error message
            const errorMsg = response.data && response.data.msg ? response.data.msg : NGOInfoCopilotGen.msgs.error;
            $statusMessage.removeClass('success').addClass('error').html(
                '<div class="error-content">' +
                    '<h4>Error</h4>' +
                    '<p>' + escapeHtml(errorMsg) + '</p>' +
                '</div>'
            ).show();

            // Scroll to message
            $statusMessage[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    /**
     * Handle AJAX error
     */
    function handleError(xhr, status, error) {
        const $statusMessage = $('#status-message');
        let errorMsg = NGOInfoCopilotGen.msgs.error;

        // Try to parse error response
        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.msg) {
            errorMsg = xhr.responseJSON.data.msg;
        } else if (xhr.responseText) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.data && response.data.msg) {
                    errorMsg = response.data.msg;
                }
            } catch (e) {
                // Use default error message
            }
        }

        // Handle specific error types
        if (status === 'timeout') {
            errorMsg = 'Request timed out. Please try again.';
        } else if (status === 'abort') {
            errorMsg = 'Request was cancelled.';
        }

        $statusMessage.removeClass('success').addClass('error').html(
            '<div class="error-content">' +
                '<h4>Error</h4>' +
                '<p>' + escapeHtml(errorMsg) + '</p>' +
            '</div>'
        ).show();

        // Scroll to message
        $statusMessage[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

})(jQuery);
