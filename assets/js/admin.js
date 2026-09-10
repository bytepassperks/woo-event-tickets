/**
 * Woo Event Tickets - Admin JavaScript
 */

(function($) {
    'use strict';

    // Global object from wp_localize_script
    var wet_admin = window.wet_admin_obj || {};

    // Initialize on document ready
    $(document).ready(function() {
        initTabs();
        initColorPickers();
        initBulkActions();
        initTooltips();
        initConfirmDialogs();
        initSelect2();
    });

    /**
     * Initialize tab navigation
     */
    function initTabs() {
        $('.wet-tab-btn').on('click', function() {
            var $btn = $(this);
            var tab = $btn.data('tab');
            var $panel = $('#tab-' + tab);

            if (!$panel.length) return;

            $btn.closest('.wet-tab-nav').find('.wet-tab-btn').removeClass('active');
            $btn.addClass('active');

            $panel.closest('.wet-tab-panels').find('.wet-tab-panel').removeClass('active');
            $panel.addClass('active');
        });
    }

    /**
     * Initialize color pickers with text input sync
     */
    function initColorPickers() {
        $('input[type="color"]').each(function() {
            var $color = $(this);
            var $text = $('#' + $color.attr('id') + '_text');

            if ($text.length) {
                $color.on('input', function() {
                    $text.val(this.value);
                });

                $text.on('input', function() {
                    var val = this.value.trim();
                    if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                        $color.val(val);
                    }
                });
            }
        });
    }

    /**
     * Initialize bulk actions
     */
    function initBulkActions() {
        // Check all checkboxes
        $('#wet-check-all').on('change', function() {
            var checked = this.checked;
            $('input[name="ticket_ids[]"]').prop('checked', checked);
        });

        // Enable/disable bulk action button
        $('input[name="ticket_ids[]"]').on('change', function() {
            var anyChecked = $('input[name="ticket_ids[]"]:checked').length > 0;
            $('#wet-bulk-action').prop('disabled', !anyChecked);
            $('.wet-bulk-actions .button').prop('disabled', !anyChecked);
        });

        // Handle bulk action submit
        $('#wet-tickets-form').on('submit', function(e) {
            var action = $('#wet-bulk-action').val();
            var checkedCount = $('input[name="ticket_ids[]"]:checked').length;

            if (!action) {
                e.preventDefault();
                alert(wet_admin.i18n?.select_action || 'Please select an action');
                return;
            }

            if (checkedCount === 0) {
                e.preventDefault();
                alert(wet_admin.i18n?.no_items_selected || 'No items selected');
                return;
            }

            // Confirm dangerous actions
            if (action === 'cancel' || action === 'regenerate_qr') {
                var message = action === 'cancel' 
                    ? (wet_admin.i18n?.confirm_cancel || 'Are you sure you want to cancel the selected tickets? This cannot be undone.')
                    : (wet_admin.i18n?.confirm_regenerate || 'Are you sure you want to regenerate QR codes for selected tickets? Previous QR codes will be invalidated.');
                
                if (!confirm(message)) {
                    e.preventDefault();
                }
            }
        });
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        $('[data-tooltip]').each(function() {
            var $el = $(this);
            var text = $el.data('tooltip');
            
            $el.on('mouseenter', function() {
                var $tooltip = $('<div class="wet-tooltip">' + text + '</div>');
                $('body').append($tooltip);
                
                var offset = $el.offset();
                $tooltip.css({
                    top: offset.top - $tooltip.outerHeight() - 8,
                    left: offset.left + ($el.outerWidth() / 2) - ($tooltip.outerWidth() / 2)
                });
            }).on('mouseleave', function() {
                $('.wet-tooltip').remove();
            });
        });
    }

    /**
     * Initialize confirmation dialogs
     */
    function initConfirmDialogs() {
        $('.wet-delete-staff, .wet-delete-event, .wet-delete-ticket-type').on('click', function(e) {
            var $link = $(this);
            var message = $link.data('confirm') || wet_admin.i18n?.confirm_delete || 'Are you sure you want to delete this item?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    }

    /**
     * Initialize Select2
     */
    function initSelect2() {
        if ($.fn.select2) {
            $('select.wet-select2').select2({
                width: '100%',
                placeholder: $(this).data('placeholder') || ''
            });
        }
    }

    /**
     * Show notification
     */
    window.wetShowNotice = function(message, type) {
        type = type || 'success';
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').first().after($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() { $(this).remove(); });
        }, 5000);
    };

    /**
     * AJAX helper
     */
    window.wetAjax = function(action, data, callback) {
        data = data || {};
        data.action = action;
        data._ajax_nonce = wet_admin.nonce;

        return $.post(wet_admin.ajax_url, data)
            .done(function(response) {
                if (callback) callback(response);
            })
            .fail(function(xhr, status, error) {
                console.error('WET AJAX Error:', error);
                if (callback) callback({ success: false, message: error });
            });
    };

    /**
     * Resend ticket
     */
    $(document).on('click', '.wet-resend-ticket', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var ticketId = $btn.data('ticket-id');
        
        $btn.prop('disabled', true).text(wet_admin.i18n?.sending || 'Sending...');

        wetAjax('wet_resend_ticket', { ticket_id: ticketId }, function(response) {
            if (response.success) {
                $btn.text(wet_admin.i18n?.sent || 'Sent!');
                wetShowNotice(wet_admin.i18n?.ticket_resent || 'Ticket resent successfully');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                $btn.prop('disabled', false).text(wet_admin.i18n?.resend || 'Resend');
                wetShowNotice(response.data?.message || 'Failed to resend', 'error');
            }
        });
    });

    /**
     * Sync ticket type with WooCommerce
     */
    $(document).on('click', '.wet-sync-product', function() {
        var $btn = $(this);
        var typeId = $btn.data('type-id');
        var $result = $btn.next('.wet-sync-result');

        $btn.prop('disabled', true).text(wet_admin.i18n?.syncing || 'Syncing...');
        $result.removeClass('success error').text('');

        wetAjax('wet_sync_ticket_type', { type_id: typeId }, function(response) {
            if (response.success) {
                $result.addClass('success').text(wet_admin.i18n?.synced || 'Synced successfully!');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                $result.addClass('error').text(response.data?.message || 'Sync failed');
            }
            $btn.prop('disabled', false).text(wet_admin.i18n?.sync || 'Sync with WooCommerce');
        });
    });

    /**
     * Event filter dropdown
     */
    $('#wet-event-filter, #wet-staff-event-filter').on('change', function() {
        var eventId = $(this).val();
        var baseUrl = $(this).data('base-url') || '';
        
        if (eventId) {
            window.location.href = baseUrl + eventId;
        } else {
            window.location.href = baseUrl.replace(/&event_id=\d+/, '').replace(/\?event_id=\d+/, '');
        }
    });

    /**
     * Form validation
     */
    $('form#wet-event-form, form#wet-ticket-type-form, form#wet-staff-form').on('submit', function(e) {
        var $form = $(this);
        var valid = true;
        var firstInvalid = null;

        $form.find('[required]').each(function() {
            var $field = $(this);
            if (!$field.val().trim()) {
                valid = false;
                $field.addClass('wet-error');
                if (!firstInvalid) firstInvalid = $field;
            } else {
                $field.removeClass('wet-error');
            }
        });

        if (!valid) {
            e.preventDefault();
            if (firstInvalid) {
                firstInvalid.focus();
                $('html, body').animate({ scrollTop: firstInvalid.offset().top - 100 }, 300);
            }
            wetShowNotice(wet_admin.i18n?.fill_required || 'Please fill in all required fields', 'error');
        }
    });

    // Remove error class on input
    $(document).on('input change', '.wet-error', function() {
        $(this).removeClass('wet-error');
    });

    /**
     * Auto-slug generation
     */
    $('#name').on('blur', function() {
        var $slug = $('#slug');
        if (!$slug.val() || $slug.data('auto')) {
            var slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .substring(0, 200);
            $slug.val(slug).data('auto', true);
        }
    });

    // Mark slug as manual when user types
    $('#slug').on('input', function() {
        $(this).data('auto', false);
    });

    /**
     * Dynamic early bird fields
     */
    $('#early_bird_price').on('input', function() {
        var hasPrice = $(this).val().trim() !== '';
        $('#early_bird_quantity, #early_bird_start, #early_bird_end').prop('disabled', !hasPrice);
    }).trigger('input');

    /**
     * Copy to clipboard
     */
    window.wetCopyToClipboard = function(text) {
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
        wetShowNotice(wet_admin.i18n?.copied || 'Copied to clipboard!');
    };

    // Add copy buttons for ticket IDs
    $(document).on('click', '.wet-copy-ticket-id', function() {
        var ticketId = $(this).data('ticket-id');
        wetCopyToClipboard(ticketId);
    });

    /**
     * Price display calculator
     */
    function updatePriceDisplay() {
        var regularPrice = parseFloat($('#regular_price').val()) || 0;
        var salePrice = parseFloat($('#sale_price').val()) || 0;
        var earlyBirdPrice = parseFloat($('#early_bird_price').val()) || 0;
        var earlyBirdQty = parseInt($('#early_bird_quantity').val()) || 0;
        
        // This would show a preview of effective price
        // Implementation depends on specific requirements
    }

    $('#regular_price, #sale_price, #early_bird_price, #early_bird_quantity').on('input', updatePriceDisplay);

    /**
     * Debounce helper
     */
    window.wetDebounce = function(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    };

    /**
     * Format currency
     */
    window.wetFormatCurrency = function(amount, currency) {
        currency = currency || 'INR';
        var formatter = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2
        });
        return formatter.format(amount);
    };

})(jQuery);