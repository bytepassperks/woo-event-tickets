<?php
/**
 * Shortcode: [ticket_scanner] template (public version for staff)
 */
if ( ! Woo_Event_Tickets_Scanner::validate_access() ) : ?>
	<div class="wet-scanner-access-denied">
		<span class="dashicons dashicons-lock"></span>
		<h2><?php esc_html_e( 'Access Denied', 'wootickets' ); ?></h2>
		<p><?php esc_html_e( 'This page is for authorized staff only.', 'wootickets' ); ?></p>
	</div>
<?php else : 
	$settings = Woo_Event_Tickets_Scanner::get_settings();
	$events = Woo_Event_Tickets_Events::get_all( [ 'status' => 'published' ] );
	$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
	$event = $event_id ? Woo_Event_Tickets_Events::get( $event_id ) : null;
?>
<div class="wet-public-scanner">
	<div class="wet-scanner-header">
		<h1><?php esc_html_e( 'Ticket Scanner', 'wootickets' ); ?></h1>
		<?php if ( count( $events ) > 1 ) : ?>
		<select id="wet-scanner-event-select" class="wet-event-select">
			<option value=""><?php esc_html_e( 'Select Event', 'wootickets' ); ?></option>
			<?php foreach ( $events as $e ) : ?>
				<option value="<?php echo esc_attr( $e['id'] ); ?>" <?php selected( $event_id, $e['id'] ); ?>><?php echo esc_html( $e['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php endif; ?>
	</div>

	<?php if ( ! $event_id ) : ?>
		<div class="wet-scanner-placeholder">
			<span class="dashicons dashicons-qrcode"></span>
			<h2><?php esc_html_e( 'Select an event to start scanning', 'wootickets' ); ?></h2>
		</div>
	<?php else : ?>
		<div class="wet-scanner-main">
			<div class="wet-scanner-video-wrapper">
				<video id="wet-scanner-video" playsinline></video>
				<div class="wet-scanner-overlay">
					<div class="wet-scanner-frame">
						<div class="wet-scanner-corner tl"></div>
						<div class="wet-scanner-corner tr"></div>
						<div class="wet-scanner-corner bl"></div>
						<div class="wet-scanner-corner br"></div>
						<div class="wet-scanner-line"></div>
					</div>
				</div>
			</div>

			<div class="wet-scanner-controls">
				<button type="button" id="wet-scanner-start" class="button button-primary button-large"><?php esc_html_e( 'Start Camera', 'wootickets' ); ?></button>
				<button type="button" id="wet-scanner-stop" class="button button-large" style="display: none;"><?php esc_html_e( 'Stop Camera', 'wootickets' ); ?></button>
				<button type="button" id="wet-scanner-switch" class="button button-large" style="display: none;"><?php esc_html_e( 'Switch Camera', 'wootickets' ); ?></button>
			</div>

			<div class="wet-scanner-manual">
				<h3><?php esc_html_e( 'Manual Verification', 'wootickets' ); ?></h3>
				<div class="wet-manual-form">
					<input type="text" id="wet-manual-ticket-id" placeholder="<?php esc_attr_e( 'Ticket ID', 'wootickets' ); ?>" class="regular-text">
					<input type="text" id="wet-manual-token" placeholder="<?php esc_attr_e( 'Secure Token', 'wootickets' ); ?>" class="regular-text">
					<button type="button" id="wet-manual-verify" class="button button-primary"><?php esc_html_e( 'Verify', 'wootickets' ); ?></button>
				</div>
			</div>

			<div id="wet-scanner-result" class="wet-scanner-result" style="display: none;"></div>
		</div>
	<?php endif; ?>
</div>
<?php endif; ?>

<style>
.wet-scanner-access-denied { text-align: center; padding: 60px 20px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); color: #dc3232; }
.wet-scanner-access-denied .dashicons { font-size: 64px; margin-bottom: 20px; }
.wet-public-scanner { max-width: 600px; margin: 0 auto; }
.wet-scanner-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.wet-event-select { min-width: 250px; padding: 10px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; }
.wet-scanner-placeholder { text-align: center; padding: 80px 20px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); color: #646970; }
.wet-scanner-placeholder .dashicons { font-size: 64px; margin-bottom: 20px; color: #ccc; }
.wet-scanner-main { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 24px; }
.wet-scanner-video-wrapper { position: relative; width: 100%; background: #000; border-radius: 12px; overflow: hidden; aspect-ratio: 4/3; }
.wet-scanner-video-wrapper video { width: 100%; height: 100%; object-fit: cover; }
.wet-scanner-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; }
.wet-scanner-frame { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 280px; height: 280px; border: 2px solid var(--wet-primary, #0066cc); border-radius: 12px; }
.wet-scanner-corner { position: absolute; width: 20px; height: 20px; border: 3px solid var(--wet-primary, #0066cc); }
.wet-scanner-corner.tl { top: -3px; left: -3px; border-right: none; border-bottom: none; }
.wet-scanner-corner.tr { top: -3px; right: -3px; border-left: none; border-bottom: none; }
.wet-scanner-corner.bl { bottom: -3px; left: -3px; border-right: none; border-top: none; }
.wet-scanner-corner.br { bottom: -3px; right: -3px; border-left: none; border-top: none; }
.wet-scanner-line { position: absolute; top: 50%; left: 10px; right: 10px; height: 2px; background: linear-gradient(90deg, transparent, var(--wet-primary, #0066cc), transparent); animation: wetScanLine 2s infinite; }
@keyframes wetScanLine { 0% { top: 10px; opacity: 0; } 50% { opacity: 1; } 100% { top: calc(100% - 10px); opacity: 0; } }
.wet-scanner-controls { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin: 20px 0; }
.wet-manual-form { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.wet-manual-form input { min-width: 200px; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; }
.wet-scanner-result { margin-top: 24px; padding: 30px; border-radius: 12px; text-align: center; animation: wetResultPop 0.3s ease-out; }
@keyframes wetResultPop { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.wet-scanner-result.valid { background: #d4edda; border: 2px solid #46b450; }
.wet-scanner-result.invalid { background: #f8d7da; border: 2px solid #dc3232; }
.wet-scanner-result .wet-result-icon { font-size: 64px; margin-bottom: 15px; }
.wet-scanner-result.valid .wet-result-icon { color: #46b450; }
.wet-scanner-result.invalid .wet-result-icon { color: #dc3232; }
.wet-scanner-result h3 { margin: 0 0 10px; font-size: 24px; }
.wet-scanner-result .wet-result-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; margin-top: 20px; text-align: left; }
.wet-scanner-result .wet-result-detail { padding: 10px; background: rgba(255,255,255,0.3); border-radius: 8px; }
.wet-scanner-result .wet-result-label { font-size: 12px; text-transform: uppercase; opacity: 0.8; }
.wet-scanner-result .wet-result-value { font-size: 16px; font-weight: 600; }
.wet-scanner-result .wet-result-actions { margin-top: 20px; }
.wet-scanner-result .wet-result-actions button { margin: 0 5px; }
</style>

<script>
// Scanner JavaScript (same as admin but with public AJAX)
var wetPublicScanner = {
    video: null,
    scanning: false,
    facingMode: 'environment',
    lastScanTime: 0,
    scanCooldown: 2000,
    html5QrcodeScanner: null,

    init: function() {
        this.video = document.getElementById('wet-scanner-video');
        
        document.getElementById('wet-scanner-start').addEventListener('click', () => this.startCamera());
        document.getElementById('wet-scanner-stop').addEventListener('click', () => this.stopCamera());
        document.getElementById('wet-scanner-switch').addEventListener('click', () => this.switchCamera());
        document.getElementById('wet-manual-verify').addEventListener('click', () => this.manualVerify());

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
                alert('<?php esc_js( __( 'Camera access denied', 'wootickets' ) ); ?>');
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
            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-block';
            switchBtn.style.display = 'inline-block';
        } else {
            startBtn.style.display = 'inline-block';
            stopBtn.style.display = 'none';
            switchBtn.style.display = 'none';
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
        // Note: For production, use jsQR library or similar
        // This is a simplified version
    },

    onScanSuccess: function(decodedText) {
        if (!this.scanning) return;
        this.stopCamera();
        this.processToken(decodedText);
    },

    onScanFailure: function(err) {},

    manualVerify: function() {
        var ticketId = document.getElementById('wet-manual-ticket-id').value.trim();
        var token = document.getElementById('wet-manual-token').value.trim();
        if (!token && !ticketId) {
            alert('<?php esc_js( __( 'Please enter a ticket ID or secure token', 'wootickets' ) ); ?>');
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
        resultDiv.innerHTML = '<div class="wet-result-icon dashicons dashicons-update" style="animation: spin 1s linear infinite;"></div><h3><?php esc_js( __( 'Verifying...', 'wootickets' ) ); ?></h3>';

        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
        jQuery.post(ajaxurl, {
            action: 'wet_check_in',
            secure_token: token,
            ticket_id: '',
            _ajax_nonce: '<?php echo wp_create_nonce( 'wet_ajax_nonce' ); ?>'
        }, function(response) {
            self.showResult(response);
        }).fail(function() {
            self.showResult({ success: false, status: 'error', message: '<?php esc_js( __( 'Verification failed', 'wootickets' ) ); ?>' });
        });
    },

    lookupByTicketId: function(ticketId) {
        var self = this;
        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
        jQuery.post(ajaxurl, {
            action: 'wet_lookup_token',
            ticket_id: ticketId,
            nonce: '<?php echo wp_create_nonce( 'wet_ajax_nonce' ); ?>'
        }, function(response) {
            if (response.success && response.data.token) {
                self.processToken(response.data.token);
            } else {
                self.showResult({ success: false, status: 'invalid', message: '<?php esc_js( __( 'Ticket not found', 'wootickets' ) ); ?>' });
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
                '<h3><?php esc_js( __( 'VALID TICKET', 'wootickets' ) ); ?></h3>' +
                '<div class="wet-result-details">' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_js( __( 'Ticket ID', 'wootickets' ) ); ?></div><div class="wet-result-value">' + ticket.ticket_id + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_js( __( 'Type', 'wootickets' ) ); ?></div><div class="wet-result-value">' + ticket.ticket_type + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_js( __( 'Attendee', 'wootickets' ) ); ?></div><div class="wet-result-value">' + ticket.customer_name + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_js( __( 'People', 'wootickets' ) ); ?></div><div class="wet-result-value">' + ticket.people_allowed + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_js( __( 'Checked In', 'wootickets' ) ); ?></div><div class="wet-result-value">' + ticket.checked_in_at + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_js( __( 'Staff', 'wootickets' ) ); ?></div><div class="wet-result-value">' + ticket.checked_in_by + '</div></div>' +
                '</div>' +
                '<div class="wet-result-actions"><button class="button button-primary" onclick="wetPublicScanner.startCamera()"><?php esc_js( __( 'Scan Next', 'wootickets' ) ); ?></button></div>';
        } else {
            resultDiv.className = 'wet-scanner-result invalid';
            var icon = response.data.status === 'already_used' ? 'dashicons dashicons-warning' : 'dashicons dashicons-no';
            var title = response.data.status === 'already_used' ? '<?php esc_js( __( 'ALREADY USED', 'wootickets' ) ); ?>' : '<?php esc_js( __( 'ENTRY DENIED', 'wootickets' ) ); ?>';
            var details = '';
            if (response.data.ticket) {
                var t = response.data.ticket;
                details = '<div class="wet-result-details">' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_js( __( 'Ticket ID', 'wootickets' ) ); ?></div><div class="wet-result-value">' + t.ticket_id + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_js( __( 'First Used', 'wootickets' ) ); ?></div><div class="wet-result-value">' + t.first_used + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_js( __( 'Checked In By', 'wootickets' ) ); ?></div><div class="wet-result-value">' + t.checked_in_by + '</div></div>' +
                '</div>';
            }
            resultDiv.innerHTML = 
                '<div class="wet-result-icon ' + icon + '"></div>' +
                '<h3>' + title + '</h3>' +
                '<p>' + response.data.message + '</p>' +
                details +
                '<div class="wet-result-actions"><button class="button button-primary" onclick="wetPublicScanner.startCamera()"><?php esc_js( __( 'Scan Next', 'wootickets' ) ); ?></button></div>';
        }
    }
};

jQuery(function($) {
    wetPublicScanner.init();
    
    $('#wet-scanner-event-select').on('change', function() {
        var eventId = $(this).val();
        if (eventId) {
            window.location.href = '<?php echo esc_js( add_query_arg( 'event_id', '', get_permalink() ) ); ?>' + eventId;
        }
    });

    // Load libraries
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
});

// Spinner animation
var style = document.createElement('style');
style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>