/**
 * Woo Event Tickets - Frontend JavaScript
 */

(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        initTicketBuyButtons();
        initQuantityInputs();
        initSmoothScroll();
    });

    /**
     * Handle ticket buy buttons
     */
    function initTicketBuyButtons() {
        $(document).on('click', '.wet-btn-buy', function(e) {
            var $btn = $(this);
            var ticketTypeId = $btn.data('ticket-type');

            // Add loading state
            $btn.addClass('wet-loading').prop('disabled', true);

            // If it's a link, let it navigate normally
            if ($btn.is('a')) {
                return true;
            }

            // For button elements, redirect to product
            var productUrl = $btn.data('product-url');
            if (productUrl) {
                window.location.href = productUrl;
            }
        });
    }

    /**
     * Quantity input handling for ticket purchase
     */
    function initQuantityInputs() {
        $('.wet-ticket-quantity').on('change', function() {
            var $input = $(this);
            var max = parseInt($input.attr('max')) || 10;
            var min = parseInt($input.attr('min')) || 1;
            var val = parseInt($input.val()) || min;

            if (val > max) $input.val(max);
            if (val < min) $input.val(min);
        });
    }

    /**
     * Smooth scroll for anchor links
     */
    function initSmoothScroll() {
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
    }

    /**
     * Public scanner functionality
     */
    window.wetPublicScanner = {
        video: null,
        scanning: false,
        facingMode: 'environment',
        lastScanTime: 0,
        scanCooldown: 2000,
        html5QrcodeScanner: null,
        stream: null,

        init: function() {
            this.video = document.getElementById('wet-scanner-video');
            
            var startBtn = document.getElementById('wet-scanner-start');
            var stopBtn = document.getElementById('wet-scanner-stop');
            var switchBtn = document.getElementById('wet-scanner-switch');
            var manualBtn = document.getElementById('wet-manual-verify');

            if (startBtn) startBtn.addEventListener('click', () => this.startCamera());
            if (stopBtn) stopBtn.addEventListener('click', () => this.stopCamera());
            if (switchBtn) switchBtn.addEventListener('click', () => this.switchCamera());
            if (manualBtn) manualBtn.addEventListener('click', () => this.manualVerify());

            if (typeof Html5QrcodeScanner !== 'undefined') {
                this.initHtml5Qrcode();
            } else {
                this.loadQrLibrary();
            }
        },

        loadQrLibrary: function() {
            var script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
            script.onload = () => this.initHtml5Qrcode();
            document.head.appendChild(script);
        },

        initHtml5Qrcode: function() {
            this.html5QrcodeScanner = new Html5QrcodeScanner(
                'wet-scanner-video',
                { fps: 10, qrbox: { width: 250, height: 250 } },
                false
            );
        },

        startCamera: function() {
            var self = this;
            if (this.html5QrcodeScanner) {
                this.html5QrcodeScanner.render(this.onScanSuccess.bind(this), this.onScanFailure.bind(this));
                this.scanning = true;
                this.updateUI();
            } else {
                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: this.facingMode }
                }).then(function(stream) {
                    self.stream = stream;
                    self.video.srcObject = stream;
                    self.video.play();
                    self.scanning = true;
                    self.updateUI();
                    self.startScanLoop();
                }).catch(function(err) {
                    alert('Camera access denied or not available');
                    console.error(err);
                });
            }
        },

        stopCamera: function() {
            if (this.html5QrcodeScanner) {
                this.html5QrcodeScanner.clear().catch(() => {});
            }
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            this.video.srcObject = null;
            this.scanning = false;
            this.updateUI();
        },

        switchCamera: function() {
            this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
            this.stopCamera();
            setTimeout(() => this.startCamera(), 500);
        },

        updateUI: function() {
            var startBtn = document.getElementById('wet-scanner-start');
            var stopBtn = document.getElementById('wet-scanner-stop');
            var switchBtn = document.getElementById('wet-scanner-switch');

            if (this.scanning) {
                if (startBtn) startBtn.style.display = 'none';
                if (stopBtn) stopBtn.style.display = 'inline-block';
                if (switchBtn) switchBtn.style.display = 'inline-block';
            } else {
                if (startBtn) startBtn.style.display = 'inline-block';
                if (stopBtn) stopBtn.style.display = 'none';
                if (switchBtn) switchBtn.style.display = 'none';
            }
        },

        startScanLoop: function() {
            if (!this.scanning) return;
            this.scanFrame();
            requestAnimationFrame(() => this.startScanLoop());
        },

        scanFrame: function() {
            if (!this.scanning || this.video.readyState !== this.video.HAVE_ENOUGH_DATA) return;
            var now = Date.now();
            if (now - this.lastScanTime < this.scanCooldown) return;
            // Note: For production, use jsQR library
        },

        onScanSuccess: function(decodedText) {
            if (!this.scanning) return;
            this.stopCamera();
            this.processToken(decodedText);
        },

        onScanFailure: function(err) {},

        manualVerify: function() {
            var ticketId = document.getElementById('wet-manual-ticket-id')?.value?.trim() || '';
            var token = document.getElementById('wet-manual-token')?.value?.trim() || '';

            if (!token && !ticketId) {
                alert('Please enter a ticket ID or secure token');
                return;
            }

            if (token) {
                this.processToken(token);
            } else {
                this.lookupByTicketId(ticketId);
            }
        },

        processToken: function(token) {
            var self = this;
            var resultDiv = document.getElementById('wet-scanner-result');
            
            resultDiv.style.display = 'block';
            resultDiv.className = 'wet-scanner-result';
            resultDiv.innerHTML = '<div class="wet-result-icon dashicons dashicons-update" style="animation: spin 1s linear infinite;"></div><h3>Verifying...</h3>';

            var ajaxurl = '<?php echo admin_url( "admin-ajax.php" ); ?>';
            var nonce = '<?php echo wp_create_nonce( "wet_ajax_nonce" ); ?>';

            $.post(ajaxurl, {
                action: 'wet_check_in',
                secure_token: token,
                ticket_id: '',
                _ajax_nonce: nonce
            }, function(response) {
                self.showResult(response);
            }).fail(function() {
                self.showResult({ success: false, status: 'error', message: 'Verification failed. Please try again.' });
            });
        },

        lookupByTicketId: function(ticketId) {
            var self = this;
            var ajaxurl = '<?php echo admin_url( "admin-ajax.php" ); ?>';
            var nonce = '<?php echo wp_create_nonce( "wet_ajax_nonce" ); ?>';

            $.post(ajaxurl, {
                action: 'wet_lookup_token',
                ticket_id: ticketId,
                nonce: nonce
            }, function(response) {
                if (response.success && response.data.token) {
                    self.processToken(response.data.token);
                } else {
                    self.showResult({ success: false, status: 'invalid', message: 'Ticket not found' });
                }
            });
        },

        showResult: function(response) {
            var resultDiv = document.getElementById('wet-scanner-result');
            
            if (response.success && response.data.status === 'valid') {
                resultDiv.className = 'wet-scanner-result valid';
                var ticket = response.data.ticket;
                var icon = ticket.people_allowed > 1 ? 'dashicons dashicons-groups' : 'dashicons dashicons-tickets';
                
                resultDiv.innerHTML = 
                    '<div class="wet-result-icon ' + icon + '"></div>' +
                    '<h3>VALID TICKET</h3>' +
                    '<div class="wet-result-details">' +
                        '<div class="wet-result-detail"><div class="wet-result-label">Ticket ID</div><div class="wet-result-value">' + ticket.ticket_id + '</div></div>' +
                        '<div class="wet-result-detail"><div class="wet-result-label">Type</div><div class="wet-result-value">' + ticket.ticket_type + '</div></div>' +
                        '<div class="wet-result-detail"><div class="wet-result-label">Attendee</div><div class="wet-result-value">' + ticket.customer_name + '</div></div>' +
                        '<div class="wet-result-detail"><div class="wet-result-label">People Allowed</div><div class="wet-result-value">' + ticket.people_allowed + '</div></div>' +
                        '<div class="wet-result-detail"><div class="wet-result-label">Checked In</div><div class="wet-result-value">' + ticket.checked_in_at + '</div></div>' +
                        '<div class="wet-result-detail"><div class="wet-result-label">Staff</div><div class="wet-result-value">' + ticket.checked_in_by + '</div></div>' +
                    '</div>' +
                    '<div class="wet-result-actions"><button class="button button-primary" onclick="wetPublicScanner.startCamera()">Scan Next</button></div>';
            } else {
                resultDiv.className = 'wet-scanner-result invalid';
                var icon = response.data.status === 'already_used' ? 'dashicons dashicons-warning' : 'dashicons dashicons-no';
                var title = response.data.status === 'already_used' ? 'ALREADY USED' : 'ENTRY DENIED';
                
                var details = '';
                if (response.data.ticket) {
                    var t = response.data.ticket;
                    details = '<div class="wet-result-details">' +
                        '<div class="wet-result-detail"><div class="wet-result-label">Ticket ID</div><div class="wet-result-value">' + t.ticket_id + '</div></div>' +
                        '<div class="wet-result-detail"><div class="wet-result-label">First Used</div><div class="wet-result-value">' + t.first_used + '</div></div>' +
                        '<div class="wet-result-detail"><div class="wet-result-label">Checked In By</div><div class="wet-result-value">' + t.checked_in_by + '</div></div>' +
                    '</div>';
                }

                resultDiv.innerHTML = 
                    '<div class="wet-result-icon ' + icon + '"></div>' +
                    '<h3>' + title + '</h3>' +
                    '<p>' + response.data.message + '</p>' +
                    details +
                    '<div class="wet-result-actions"><button class="button button-primary" onclick="wetPublicScanner.startCamera()">Scan Next</button></div>';
            }
        }
    };

    // Initialize scanner if on page
    if (document.getElementById('wet-scanner-video')) {
        wetPublicScanner.init();
    }

    // Event selector for public scanner
    $('#wet-scanner-event-select').on('change', function() {
        var eventId = $(this).val();
        if (eventId) {
            var baseUrl = window.location.href.split('?')[0];
            window.location.href = baseUrl + '?event_id=' + eventId;
        }
    });

    // Load required libraries
    if (typeof Html5QrcodeScanner === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
        document.head.appendChild(script);
    }
    if (typeof jsQR === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js';
        document.head.appendChild(script);
    }

    // Spinner animation
    var style = document.createElement('style');
    style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
    document.head.appendChild(style);

})(jQuery);