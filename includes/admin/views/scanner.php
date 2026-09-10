<?php
/**
 * Scanner view
 */
$settings = Woo_Event_Tickets_Scanner::get_settings();
$events = Woo_Event_Tickets_Events::get_all( [ 'status' => 'published' ] );
$event = $event_id ? Woo_Event_Tickets_Events::get( $event_id ) : null;
$event_day_stats = $event_id ? Woo_Event_Tickets_Scanner::get_event_day_stats( $event_id ) : null;
?>
<div class="wrap wet-scanner-page">
	<div class="wet-scanner-header">
		<h1><?php esc_html_e( 'Ticket Scanner', 'wootickets' ); ?></h1>
		<div class="wet-scanner-event-selector">
			<select id="wet-scanner-event" class="regular-text">
				<option value=""><?php esc_html_e( 'Select Event', 'wootickets' ); ?></option>
				<?php foreach ( $events as $e ) : ?>
					<option value="<?php echo esc_attr( $e['id'] ); ?>" <?php selected( $event_id, $e['id'] ); ?>><?php echo esc_html( $e['name'] ); ?> (<?php echo date_i18n( 'M j, Y', strtotime( $e['start_datetime'] ) ); ?>)</option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<?php if ( $event_day_stats ) : ?>
	<div class="wet-event-day-stats">
		<div class="wet-stat">
			<span class="wet-stat-value"><?php echo number_format( $event_day_stats['checked_in'] ); ?></span>
			<span class="wet-stat-label"><?php esc_html_e( 'Checked In Today', 'wootickets' ); ?></span>
		</div>
		<div class="wet-stat">
			<span class="wet-stat-value"><?php echo number_format( $event_day_stats['total_sold'] ); ?></span>
			<span class="wet-stat-label"><?php esc_html_e( 'Total Sold', 'wootickets' ); ?></span>
		</div>
		<div class="wet-stat">
			<span class="wet-stat-value"><?php echo number_format( $event_day_stats['remaining'] ); ?></span>
			<span class="wet-stat-label"><?php esc_html_e( 'Remaining', 'wootickets' ); ?></span>
		</div>
		<div class="wet-stat">
			<span class="wet-stat-value"><?php echo $event_day_stats['total_sold'] > 0 ? round( ( $event_day_stats['checked_in'] / $event_day_stats['total_sold'] ) * 100 ) : 0; ?>%</span>
			<span class="wet-stat-label"><?php esc_html_e( 'Check-in Rate', 'wootickets' ); ?></span>
		</div>
	</div>
	<?php endif; ?>

	<div class="wet-scanner-container" id="wet-scanner-container">
		<?php if ( ! $event_id ) : ?>
			<div class="wet-scanner-placeholder">
				<span class="dashicons dashicons-qrcode"></span>
				<h2><?php esc_html_e( 'Select an event to start scanning', 'wootickets' ); ?></h2>
				<p><?php esc_html_e( 'Choose an event from the dropdown above to begin scanning tickets.', 'wootickets' ); ?></p>
			</div>
		<?php else : ?>
			<div class="wet-scanner-main">
				<div class="wet-scanner-video-wrapper">
					<video id="wet-scanner-video" playsinline></video>
					<canvas id="wet-scanner-canvas" style="display: none;"></canvas>
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
					<button type="button" id="wet-scanner-start" class="button button-primary button-large" <?php echo $settings['sound_enabled'] ? '' : 'data-sound="off"'; ?>>
						<span class="dashicons dashicons-video-alt3"></span> <?php esc_html_e( 'Start Camera', 'wootickets' ); ?>
					</button>
					<button type="button" id="wet-scanner-stop" class="button button-large" style="display: none;">
						<span class="dashicons dashicons-no"></span> <?php esc_html_e( 'Stop Camera', 'wootickets' ); ?>
					</button>
					<button type="button" id="wet-scanner-switch" class="button button-large" style="display: none;">
						<span class="dashicons dashicons-rotate"></span> <?php esc_html_e( 'Switch Camera', 'wootickets' ); ?>
					</button>
				</div>

				<div class="wet-scanner-manual" style="display: none;">
					<h3><?php esc_html_e( 'Manual Verification', 'wootickets' ); ?></h3>
					<div class="wet-manual-form">
						<input type="text" id="wet-manual-ticket-id" placeholder="<?php esc_attr_e( 'Ticket ID (e.g., EVT-2026-000001)', 'wootickets' ); ?>" class="regular-text">
						<input type="text" id="wet-manual-token" placeholder="<?php esc_attr_e( 'Secure Token (from QR)', 'wootickets' ); ?>" class="regular-text">
						<button type="button" id="wet-manual-verify" class="button button-primary"><?php esc_html_e( 'Verify', 'wootickets' ); ?></button>
					</div>
				</div>

				<div id="wet-scanner-result" class="wet-scanner-result" style="display: none;"></div>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $event_id ) : ?>
	<div class="wet-recent-scans">
		<h2><?php esc_html_e( 'Recent Scans', 'wootickets' ); ?></h2>
		<div id="wet-recent-scans-list">
			<?php
			$recent = Woo_Event_Tickets_Checkin::get_recent( $event_id, 10 );
			if ( ! empty( $recent ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Time', 'wootickets' ); ?></th>
							<th><?php esc_html_e( 'Ticket', 'wootickets' ); ?></th>
							<th><?php esc_html_e( 'Type', 'wootickets' ); ?></th>
							<th><?php esc_html_e( 'Result', 'wootickets' ); ?></th>
							<th><?php esc_html_e( 'Staff', 'wootickets' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent as $scan ) : ?>
						<tr class="wet-scan-<?php echo esc_attr( $scan['result'] ); ?>">
							<td><?php echo date_i18n( 'H:i:s', strtotime( $scan['scanned_at'] ) ); ?></td>
							<td><?php echo esc_html( $scan['ticket_id'] ); ?></td>
							<td><?php echo esc_html( $scan['ticket_type_name'] ); ?></td>
							<td><span class="wet-status-badge wet-status-<?php echo esc_attr( $scan['result'] ); ?>"><?php echo esc_html( ucfirst( $scan['result'] ) ); ?></span></td>
							<td><?php echo esc_html( $scan['staff_name'] ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No scans yet.', 'wootickets' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
</div>

<style>
.wet-scanner-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.wet-scanner-event-selector select { min-width: 300px; }
.wet-event-day-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
.wet-event-day-stats .wet-stat { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-align: center; }
.wet-event-day-stats .wet-stat-value { display: block; font-size: 32px; font-weight: 700; color: #007cba; }
.wet-event-day-stats .wet-stat-label { font-size: 13px; color: #646970; }

.wet-scanner-placeholder { text-align: center; padding: 80px 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); color: #646970; }
.wet-scanner-placeholder .dashicons { font-size: 64px; margin-bottom: 20px; color: #ccc; }

.wet-scanner-main { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 20px; }
.wet-scanner-video-wrapper { position: relative; width: 100%; max-width: 600px; margin: 0 auto 20px; background: #000; border-radius: 8px; overflow: hidden; }
.wet-scanner-video-wrapper video { width: 100%; display: block; }
.wet-scanner-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; }
.wet-scanner-frame { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 280px; height: 280px; border: 2px solid #007cba; border-radius: 12px; }
.wet-scanner-corner { position: absolute; width: 20px; height: 20px; border: 3px solid #007cba; }
.wet-scanner-corner.tl { top: -3px; left: -3px; border-right: none; border-bottom: none; }
.wet-scanner-corner.tr { top: -3px; right: -3px; border-left: none; border-bottom: none; }
.wet-scanner-corner.bl { bottom: -3px; left: -3px; border-right: none; border-top: none; }
.wet-scanner-corner.br { bottom: -3px; right: -3px; border-left: none; border-top: none; }
.wet-scanner-line { position: absolute; top: 50%; left: 10px; right: 10px; height: 2px; background: linear-gradient(90deg, transparent, #007cba, transparent); animation: wetScanLine 2s infinite; }
@keyframes wetScanLine { 0% { top: 10px; opacity: 0; } 50% { opacity: 1; } 100% { top: calc(100% - 10px); opacity: 0; } }

.wet-scanner-controls { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 20px; }
.wet-manual-form { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.wet-manual-form input { min-width: 250px; }

.wet-scanner-result { margin-top: 20px; padding: 30px; border-radius: 12px; text-align: center; animation: wetResultPop 0.3s ease-out; }
@keyframes wetResultPop { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.wet-scanner-result.valid { background: #d4edda; border: 2px solid #46b450; }
.wet-scanner-result.invalid { background: #f8d7da; border: 2px solid #dc3232; }
.wet-scanner-result .wet-result-icon { font-size: 64px; margin-bottom: 15px; }
.wet-scanner-result.valid .wet-result-icon { color: #46b450; }
.wet-scanner-result.invalid .wet-result-icon { color: #dc3232; }
.wet-scanner-result h3 { margin: 0 0 10px; font-size: 24px; }
.wet-scanner-result .wet-result-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 20px; text-align: left; }
.wet-scanner-result .wet-result-detail { padding: 10px; background: rgba(255,255,255,0.3); border-radius: 8px; }
.wet-scanner-result .wet-result-label { font-size: 12px; text-transform: uppercase; color: inherit; opacity: 0.8; }
.wet-scanner-result .wet-result-value { font-size: 16px; font-weight: 600; }
.wet-scanner-result .wet-result-actions { margin-top: 20px; }
.wet-scanner-result .wet-result-actions button { margin: 0 5px; }

.wet-recent-scans { margin-top: 30px; }
.wet-scan-valid { background: #f0fff4; }
.wet-scan-already_used, .wet-scan-invalid, .wet-scan-cancelled, .wet-scan-refunded, .wet-scan-expired, .wet-scan-blocked { background: #fff5f5; }
</style>

<script>
var wetScanner = {
    video: null,
    canvas: null,
    ctx: null,
    stream: null,
    scanning: false,
    facingMode: 'environment',
    lastScanTime: 0,
    scanCooldown: 2000,

    init: function() {
        this.video = document.getElementById('wet-scanner-video');
        this.canvas = document.getElementById('wet-scanner-canvas');
        this.ctx = this.canvas.getContext('2d');

        document.getElementById('wet-scanner-start').addEventListener('click', () => this.startCamera());
        document.getElementById('wet-scanner-stop').addEventListener('click', () => this.stopCamera());
        document.getElementById('wet-scanner-switch').addEventListener('click', () => this.switchCamera());
        document.getElementById('wet-manual-verify').addEventListener('click', () => this.manualVerify());

        // Load html5-qrcode library if available
        if (typeof Html5QrcodeScanner !== 'undefined') {
            this.initHtml5Qrcode();
        } else {
            this.loadQrLibrary();
        }
    },

    loadQrLibrary: function() {
        // Load html5-qrcode from CDN
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
            // Fallback to native getUserMedia
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
                alert('<?php esc_js( __( 'Camera access denied or not available', 'wootickets' ) ); ?>');
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

        this.canvas.width = this.video.videoWidth;
        this.canvas.height = this.video.videoHeight;
        this.ctx.drawImage(this.video, 0, 0);

        var imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
        var code = jsQR(imageData.data, imageData.width, imageData.height);

        if (code) {
            this.lastScanTime = now;
            this.onScanSuccess(code.data);
        }
    },

    onScanSuccess: function(decodedText) {
        if (!this.scanning) return;
        this.stopCamera();
        this.processToken(decodedText);
    },

    onScanFailure: function(err) {
        // Ignore scan failures (happens frequently)
    },

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
            // Look up token by ticket ID
            this.lookupByTicketId(ticketId);
        }
    },

    processToken: function(token) {
        var self = this;
        var resultDiv = document.getElementById('wet-scanner-result');
        
        // Show loading
        resultDiv.style.display = 'block';
        resultDiv.className = 'wet-scanner-result';
        resultDiv.innerHTML = '<div class="wet-result-icon dashicons dashicons-update" style="animation: spin 1s linear infinite;"></div><h3><?php esc_html_e( 'Verifying...', 'wootickets' ); ?></h3>';

        $.post(ajaxurl, {
            action: 'wet_check_in',
            secure_token: token,
            ticket_id: '',
            _ajax_nonce: '<?php echo wp_create_nonce( 'wet_ajax_nonce' ); ?>'
        }, function(response) {
            self.showResult(response);
        }).fail(function() {
            self.showResult({ success: false, status: 'error', message: '<?php esc_js( __( 'Verification failed. Please try again.', 'wootickets' ) ); ?>' });
        });
    },

    lookupByTicketId: function(ticketId) {
        var self = this;
        $.post(ajaxurl, {
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
                '<h3><?php esc_html_e( 'VALID TICKET', 'wootickets' ); ?></h3>' +
                '<div class="wet-result-details">' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_html_e( 'Ticket ID', 'wootickets' ); ?></div><div class="wet-result-value">' + ticket.ticket_id + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_html_e( 'Type', 'wootickets' ); ?></div><div class="wet-result-value">' + ticket.ticket_type + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_html_e( 'Attendee', 'wootickets' ); ?></div><div class="wet-result-value">' + ticket.customer_name + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_html_e( 'People Allowed', 'wootickets' ); ?></div><div class="wet-result-value">' + ticket.people_allowed + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_html_e( 'Checked In', 'wootickets' ); ?></div><div class="wet-result-value">' + ticket.checked_in_at + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_html_e( 'Staff', 'wootickets' ); ?></div><div class="wet-result-value">' + ticket.checked_in_by + '</div></div>' +
                '</div>' +
                '<div class="wet-result-actions">' +
                    '<button type="button" class="button button-primary" onclick="wetScanner.startCamera()"><?php esc_html_e( 'Scan Next', 'wootickets' ); ?></button>' +
                '</div>';

            // Play success sound
            this.playSound('success');
            
            // Vibrate if supported
            if (navigator.vibrate && <?php echo $settings['vibration_enabled'] ? 'true' : 'false'; ?>) {
                navigator.vibrate([100, 50, 100]);
            }

            // Auto refresh recent scans
            this.refreshRecentScans();

        } else {
            resultDiv.className = 'wet-scanner-result invalid';
            var icon = response.data.status === 'already_used' ? 'dashicons dashicons-warning' : 'dashicons dashicons-no';
            var title = response.data.status === 'already_used' ? '<?php esc_html_e( 'ALREADY USED', 'wootickets' ); ?>' : '<?php esc_html_e( 'ENTRY DENIED', 'wootickets' ); ?>';
            
            var details = '';
            if (response.data.ticket) {
                var t = response.data.ticket;
                details = '<div class="wet-result-details">' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_html_e( 'Ticket ID', 'wootickets' ); ?></div><div class="wet-result-value">' + t.ticket_id + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_html_e( 'First Used', 'wootickets' ); ?></div><div class="wet-result-value">' + t.first_used + '</div></div>' +
                    '<div class="wet-result-detail"><div class="wet-result-label"><?php esc_html_e( 'Checked In By', 'wootickets' ); ?></div><div class="wet-result-value">' + t.checked_in_by + '</div></div>' +
                '</div>';
            }

            resultDiv.innerHTML = 
                '<div class="wet-result-icon ' + icon + '"></div>' +
                '<h3>' + title + '</h3>' +
                '<p>' + response.data.message + '</p>' +
                details +
                '<div class="wet-result-actions">' +
                    '<button type="button" class="button button-primary" onclick="wetScanner.startCamera()"><?php esc_html_e( 'Scan Next', 'wootickets' ); ?></button>' +
                '</div>';

            // Play error sound
            this.playSound('error');
            
            // Vibrate if supported
            if (navigator.vibrate && <?php echo $settings['vibration_enabled'] ? 'true' : 'false'; ?>) {
                navigator.vibrate([200, 100, 200]);
            }
        }
    },

    playSound: function(type) {
        if (!<?php echo $settings['sound_enabled'] ? 'true' : 'false'; ?>) return;
        
        var audio = new Audio();
        if (type === 'success') {
            audio.src = '<?php echo WET_PLUGIN_URL; ?>assets/sounds/success.mp3';
        } else {
            audio.src = '<?php echo WET_PLUGIN_URL; ?>assets/sounds/error.mp3';
        }
        audio.play().catch(() => {});
    },

    refreshRecentScans: function() {
        var eventId = document.getElementById('wet-scanner-event').value;
        if (!eventId) return;

        $.post(ajaxurl, {
            action: 'wet_get_recent_scans',
            event_id: eventId,
            nonce: '<?php echo wp_create_nonce( 'wet_ajax_nonce' ); ?>'
        }, function(response) {
            if (response.success) {
                document.getElementById('wet-recent-scans-list').innerHTML = response.data.html;
            }
        });
    }
};

// Initialize on load
jQuery(function($) {
    wetScanner.init();

    // Event selector change
    $('#wet-scanner-event').on('change', function() {
        var eventId = $(this).val();
        if (eventId) {
            window.location.href = '<?php echo admin_url( 'admin.php?page=woo-event-tickets-scanner&event_id=' ); ?>' + eventId;
        }
    });

    // Load jsQR library for native scanning fallback
    if (typeof jsQR === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js';
        document.head.appendChild(script);
    }
});

// CSS animation for spinner
var style = document.createElement('style');
style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>