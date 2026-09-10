<?php
/**
 * Shortcode: [my_tickets] template
 * @var array $tickets
 */
if ( empty( $tickets ) ) : ?>
	<div class="wet-my-tickets-empty">
		<span class="dashicons dashicons-tickets"></span>
		<h3><?php esc_html_e( 'No Tickets Yet', 'wootickets' ); ?></h3>
		<p><?php esc_html_e( 'You haven\'t purchased any tickets yet.', 'wootickets' ); ?></p>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Browse Events', 'wootickets' ); ?></a>
	</div>
<?php else : ?>
	<div class="wet-my-tickets">
		<h2><?php esc_html_e( 'My Tickets', 'wootickets' ); ?></h2>
		
		<?php foreach ( $tickets as $ticket ) : 
			$status_class = 'wet-status-' . $ticket['status'];
			$can_view = in_array( $ticket['status'], [ 'active', 'used' ] );
		?>
		<article class="wet-my-ticket-card <?php echo esc_attr( $status_class ); ?>">
			<div class="wet-ticket-visual">
				<?php if ( $ticket['event_name'] ) : ?>
					<div class="wet-ticket-event-badge"><?php echo esc_html( $ticket['event_name'] ); ?></div>
				<?php endif; ?>
				
				<div class="wet-ticket-qr-placeholder">
					<?php if ( $can_view ) : ?>
						<span class="dashicons dashicons-qrcode"></span>
					<?php else : ?>
						<span class="dashicons dashicons-lock"></span>
					<?php endif; ?>
				</div>
				
				<div class="wet-ticket-status-badge <?php echo esc_attr( $status_class ); ?>">
					<?php echo esc_html( ucfirst( $ticket['status'] ) ); ?>
				</div>
			</div>
			
			<div class="wet-ticket-info">
				<h3 class="wet-ticket-title"><?php echo esc_html( $ticket['ticket_type_name'] ); ?></h3>
				
				<div class="wet-ticket-meta">
					<div class="wet-meta-row">
						<span class="wet-meta-label"><?php esc_html_e( 'Ticket ID', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo esc_html( $ticket['ticket_id'] ); ?></span>
					</div>
					<div class="wet-meta-row">
						<span class="wet-meta-label"><?php esc_html_e( 'Event', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo esc_html( $ticket['event_name'] ); ?></span>
					</div>
					<div class="wet-meta-row">
						<span class="wet-meta-label"><?php esc_html_e( 'Date', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo date_i18n( 'F j, Y', strtotime( $ticket['start_datetime'] ) ); ?></span>
					</div>
					<div class="wet-meta-row">
						<span class="wet-meta-label"><?php esc_html_e( 'Time', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo date_i18n( 'g:i A', strtotime( $ticket['start_datetime'] ) ); ?></span>
					</div>
					<div class="wet-meta-row">
						<span class="wet-meta-label"><?php esc_html_e( 'Venue', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo esc_html( $ticket['venue'] ); ?><?php if ( $ticket['address'] ) : ?>, <?php echo esc_html( $ticket['address'] ); ?><?php endif; ?></span>
					</div>
					<div class="wet-meta-row">
						<span class="wet-meta-label"><?php esc_html_e( 'Attendees', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo intval( $ticket['allowed_people'] ); ?></span>
					</div>
					<?php if ( $ticket['check_in_date'] ) : ?>
					<div class="wet-meta-row">
						<span class="wet-meta-label"><?php esc_html_e( 'Checked In', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo date_i18n( 'M j, Y H:i', strtotime( $ticket['check_in_date'] ) ); ?></span>
					</div>
					<?php endif; ?>
				</div>
				
				<div class="wet-ticket-actions">
					<?php if ( $can_view ) : ?>
						<a href="<?php echo esc_url( add_query_arg( [ 'ticket_id' => $ticket['ticket_id'], 'verify' => '1' ], home_url( '/ticket/verify/' ) ) ); ?>" target="_blank" class="button button-primary"><?php esc_html_e( 'View Ticket', 'wootickets' ); ?></a>
						<a href="<?php echo esc_url( add_query_arg( [ 'ticket_id' => $ticket['ticket_id'], 'download' => 'pdf' ], home_url( '/ticket/verify/' ) ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Download PDF', 'wootickets' ); ?></a>
					<?php endif; ?>
					<?php if ( $ticket['status'] === 'cancelled' || $ticket['status'] === 'refunded' ) : ?>
						<span class="wet-refund-notice"><?php echo $ticket['status'] === 'refunded' ? esc_html__( 'Refunded', 'wootickets' ) : esc_html__( 'Cancelled', 'wootickets' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<style>
.wet-my-tickets-empty { text-align: center; padding: 60px 20px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.wet-my-tickets-empty .dashicons { font-size: 64px; color: #ccc; margin-bottom: 20px; }
.wet-my-tickets-empty h3 { margin: 0 0 10px; font-size: 24px; color: #1d2327; }
.wet-my-tickets-empty p { color: #646970; margin-bottom: 24px; }
.wet-my-tickets { max-width: 800px; margin: 0 auto; }
.wet-my-tickets > h2 { font-size: 28px; font-weight: 700; margin: 0 0 24px; color: #1d2327; }
.wet-my-ticket-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 24px; display: flex; border-left: 4px solid #0066cc; }
.wet-my-ticket-card.wet-status-used { border-left-color: #46b450; }
.wet-my-ticket-card.wet-status-cancelled, .wet-my-ticket-card.wet-status-refunded { border-left-color: #dc3232; opacity: 0.7; }
.wet-my-ticket-card.wet-status-pending { border-left-color: #ffb900; }
.wet-ticket-visual { width: 180px; background: linear-gradient(135deg, #0066cc, #004499); position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px; color: #fff; }
.wet-ticket-event-badge { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; margin-bottom: 16px; text-align: center; }
.wet-ticket-qr-placeholder { width: 80px; height: 80px; background: rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
.wet-ticket-qr-placeholder .dashicons { font-size: 36px; width: 36px; height: 36px; opacity: 0.9; }
.wet-ticket-status-badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; background: rgba(255,255,255,0.2); }
.wet-ticket-info { flex: 1; padding: 24px; }
.wet-ticket-title { margin: 0 0 20px; font-size: 22px; font-weight: 700; color: #1d2327; }
.wet-ticket-meta { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px; }
.wet-meta-row { display: flex; flex-direction: column; gap: 4px; }
.wet-meta-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #999; font-weight: 600; }
.wet-meta-value { font-size: 14px; color: #1d2327; font-weight: 500; }
.wet-ticket-actions { display: flex; gap: 12px; flex-wrap: wrap; padding-top: 16px; border-top: 1px solid #f0f0f0; }
.wet-ticket-actions .button { flex: 1; min-width: 140px; text-align: center; }
.wet-refund-notice { padding: 8px 16px; background: #fff5f5; color: #dc3232; border-radius: 8px; font-weight: 600; font-size: 13px; }

@media (max-width: 600px) {
	.wet-my-ticket-card { flex-direction: column; }
	.wet-ticket-visual { width: 100%; flex-direction: row; padding: 16px; }
	.wet-ticket-qr-placeholder { width: 60px; height: 60px; margin: 0 16px 0 0; }
	.wet-ticket-event-badge { margin-bottom: 0; }
	.wet-ticket-meta { grid-template-columns: 1fr; }
}
</style>