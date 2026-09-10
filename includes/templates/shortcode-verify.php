<?php
/**
 * Shortcode: [ticket_verify] template
 */
$verified = isset( $_GET['verify'] ) && $_GET['verify'] === '1';
$ticket_id = isset( $_GET['ticket_id'] ) ? sanitize_text_field( $_GET['ticket_id'] ) : '';
$download = isset( $_GET['download'] ) && $_GET['download'] === 'pdf';

if ( $verified && $ticket_id ) {
	$ticket = Woo_Event_Tickets_Tickets::get_by_ticket_id( $ticket_id );
	
	if ( $ticket ) {
		$ticket_type = Woo_Event_Tickets_Ticket_Types::get( $ticket['ticket_type_id'] );
		$event = Woo_Event_Tickets_Events::get( $ticket['event_id'] );
		$qr_token = Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'qr_tokens' ) . " WHERE ticket_id = %d", [ $ticket['id'] ] );
		$attendee = Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'attendees' ) . " WHERE ticket_id = %d", [ $ticket['id'] ] );

		if ( $download && $ticket['pdf_generated'] ) {
			$pdf_path = WET_PLUGIN_DIR . "uploads/tickets/{$ticket['ticket_id']}.pdf";
			if ( file_exists( $pdf_path ) ) {
				header( 'Content-Type: application/pdf' );
				header( 'Content-Disposition: attachment; filename="' . $ticket['ticket_id'] . '.pdf"' );
				readfile( $pdf_path );
				exit;
			}
		}

		if ( $ticket && $ticket_type && $event ) :
			$attendee_name = $attendee['full_name'] ?? $ticket['customer_name'];
			$allowed_people = $ticket_type['allowed_people'] ?? 1;
		?>
		<div class="wet-verify-ticket" style="--wet-primary: <?php echo esc_attr( $event['primary_color'] ?? '#0066cc' ); ?>">
			<div class="wet-verify-header">
				<h1><?php echo esc_html( $event['name'] ); ?></h1>
				<span class="wet-verify-status wet-status-<?php echo esc_attr( $ticket['status'] ); ?>">
					<?php echo esc_html( ucfirst( $ticket['status'] ) ); ?>
				</span>
			</div>
			
			<div class="wet-verify-content">
				<div class="wet-verify-main">
					<div class="wet-verify-ticket-id"><?php echo esc_html( $ticket['ticket_id'] ); ?></div>
					<div class="wet-verify-type"><?php echo esc_html( $ticket_type['name'] ); ?></div>
					
					<div class="wet-verify-qr">
						<?php if ( $qr_token && $qr_token['verification_url'] ) : ?>
							<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=<?php echo urlencode( $qr_token['verification_url'] ); ?>&choe=UTF-8" alt="QR Code" class="wet-qr-image">
						<?php endif; ?>
						<p class="wet-qr-caption"><?php esc_html_e( 'Present this QR code at the entrance', 'wootickets' ); ?></p>
					</div>
				</div>
				
				<div class="wet-verify-details">
					<div class="wet-detail">
						<span class="wet-detail-label"><?php esc_html_e( 'Attendee', 'wootickets' ); ?></span>
						<span class="wet-detail-value"><?php echo esc_html( $attendee_name ); ?></span>
					</div>
					<div class="wet-detail">
						<span class="wet-detail-label"><?php esc_html_e( 'Attendees Allowed', 'wootickets' ); ?></span>
						<span class="wet-detail-value"><?php echo intval( $allowed_people ); ?></span>
					</div>
					<div class="wet-detail">
						<span class="wet-detail-label"><?php esc_html_e( 'Event Date', 'wootickets' ); ?></span>
						<span class="wet-detail-value"><?php echo date_i18n( 'F j, Y', strtotime( $event['start_datetime'] ) ); ?></span>
					</div>
					<div class="wet-detail">
						<span class="wet-detail-label"><?php esc_html_e( 'Event Time', 'wootickets' ); ?></span>
						<span class="wet-detail-value"><?php echo date_i18n( 'g:i A', strtotime( $event['start_datetime'] ) ); ?></span>
					</div>
					<div class="wet-detail">
						<span class="wet-detail-label"><?php esc_html_e( 'Venue', 'wootickets' ); ?></span>
						<span class="wet-detail-value"><?php echo esc_html( $event['venue'] ); ?><?php if ( $event['address'] ) : ?>, <?php echo esc_html( $event['address'] ); ?><?php endif; ?></span>
					</div>
					<div class="wet-detail">
						<span class="wet-detail-label"><?php esc_html_e( 'Order', 'wootickets' ); ?></span>
						<span class="wet-detail-value">#<?php echo $ticket['order_id']; ?></span>
					</div>
					<?php if ( $ticket['check_in_date'] ) : ?>
					<div class="wet-detail">
						<span class="wet-detail-label"><?php esc_html_e( 'Checked In', 'wootickets' ); ?></span>
						<span class="wet-detail-value"><?php echo date_i18n( 'M j, Y H:i', strtotime( $ticket['check_in_date'] ) ); ?></span>
					</div>
					<?php endif; ?>
				</div>
				
				<div class="wet-verify-actions">
					<a href="<?php echo esc_url( add_query_arg( [ 'ticket_id' => $ticket['ticket_id'], 'download' => 'pdf' ] ) ); ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'Download PDF', 'wootickets' ); ?></a>
					<a href="<?php echo esc_url( remove_query_arg( [ 'verify', 'ticket_id', 'download' ] ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Back to My Tickets', 'wootickets' ); ?></a>
				</div>
			</div>
		</div>
		<?php
		else :
			?>
			<div class="wet-verify-not-found">
				<span class="dashicons dashicons-warning"></span>
				<h2><?php esc_html_e( 'Ticket Not Found', 'wootickets' ); ?></h2>
				<p><?php esc_html_e( 'The ticket you\'re looking for doesn\'t exist.', 'wootickets' ); ?></p>
			</div>
			<?php
		endif;
	} else {
		?>
		<div class="wet-verify-form">
			<h2><?php esc_html_e( 'Verify Your Ticket', 'wootickets' ); ?></h2>
			<p><?php esc_html_e( 'Enter your Ticket ID or scan the QR code to verify your ticket.', 'wootickets' ); ?></p>
			<form method="get" class="wet-verify-form">
				<input type="hidden" name="verify" value="1">
				<input type="text" name="ticket_id" placeholder="<?php esc_attr_e( 'Ticket ID (e.g., EVT-2026-000001)', 'wootickets' ); ?>" class="regular-text" required>
				<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Verify', 'wootickets' ); ?></button>
			</form>
		</div>
		<?php
	}
} else {
	?>
	<div class="wet-verify-form">
		<h2><?php esc_html_e( 'Verify Your Ticket', 'wootickets' ); ?></h2>
		<p><?php esc_html_e( 'Enter your Ticket ID or scan the QR code to verify your ticket.', 'wootickets' ); ?></p>
		<form method="get" class="wet-verify-form">
			<input type="hidden" name="verify" value="1">
			<input type="text" name="ticket_id" placeholder="<?php esc_attr_e( 'Ticket ID (e.g., EVT-2026-000001)', 'wootickets' ); ?>" class="regular-text" required>
			<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Verify', 'wootickets' ); ?></button>
		</form>
	</div>
	<?php
}
?>

<style>
.wet-verify-form { max-width: 400px; margin: 0 auto; text-align: center; padding: 40px 20px; }
.wet-verify-form h2 { margin-bottom: 10px; color: #1d2327; }
.wet-verify-form p { color: #646970; margin-bottom: 24px; }
.wet-verify-form input { width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; margin-bottom: 16px; }
.wet-verify-form input:focus { border-color: #0066cc; outline: none; }
.wet-verify-form .button-large { width: 100%; padding: 16px; font-size: 16px; }

.wet-verify-not-found { text-align: center; padding: 60px 20px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.wet-verify-not-found .dashicons { font-size: 64px; color: #dc3232; margin-bottom: 20px; }

.wet-verify-ticket { max-width: 500px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
.wet-verify-header { background: var(--wet-primary); color: #fff; padding: 30px; text-align: center; position: relative; }
.wet-verify-header::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></svg>') center/cover; }
.wet-verify-header h1 { margin: 0 0 12px; font-size: 28px; font-weight: 700; position: relative; z-index: 1; }
.wet-verify-status { display: inline-block; padding: 8px 20px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; background: rgba(255,255,255,0.2); position: relative; z-index: 1; }
.wet-verify-content { padding: 30px; }
.wet-verify-main { text-align: center; margin-bottom: 30px; }
.wet-verify-ticket-id { font-family: 'Courier New', monospace; font-size: 20px; font-weight: 700; letter-spacing: 2px; color: var(--wet-primary); margin-bottom: 8px; padding: 12px; background: #f8f9fa; border-radius: 8px; border: 2px dashed var(--wet-primary); }
.wet-verify-type { font-size: 18px; font-weight: 600; color: #646970; margin-bottom: 24px; }
.wet-verify-qr { margin: 24px 0; }
.wet-qr-image { max-width: 250px; border: 1px solid #e0e0e0; padding: 10px; background: #fff; border-radius: 8px; }
.wet-qr-caption { margin: 12px 0 0; font-size: 13px; color: #999; }
.wet-verify-details { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 24px; padding: 20px; background: #f8f9fa; border-radius: 12px; }
.wet-detail { display: flex; flex-direction: column; gap: 4px; }
.wet-detail-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #999; font-weight: 600; }
.wet-detail-value { font-size: 14px; font-weight: 500; color: #1d2327; }
.wet-verify-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; padding-top: 20px; border-top: 1px solid #f0f0f0; }
.wet-verify-actions .button { min-width: 160px; }

@media (max-width: 480px) {
	.wet-verify-details { grid-template-columns: 1fr; }
	.wet-verify-actions { flex-direction: column; }
	.wet-verify-actions .button { width: 100%; }
}
</style>