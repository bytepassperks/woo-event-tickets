<?php
/**
 * Tickets list view
 */
$event = $event_id ? Woo_Event_Tickets_Events::get( $event_id ) : null;
$events = Woo_Event_Tickets_Events::get_dropdown_options();
$statuses = [ 'pending', 'active', 'used', 'cancelled', 'refunded', 'expired', 'blocked' ];
?>
<div class="wrap wet-tickets-list">
	<div class="wet-page-header">
		<h1><?php echo $event ? esc_html( $event['name'] ) . ' – ' : ''; esc_html_e( 'Tickets', 'wootickets' ); ?></h1>
	</div>

	<div class="wet-filters">
		<form method="get" class="wet-filter-form">
			<input type="hidden" name="page" value="woo-event-tickets-tickets">
			
			<select name="event_id" id="wet-filter-event">
				<option value=""><?php esc_html_e( 'All Events', 'wootickets' ); ?></option>
				<?php foreach ( $events as $id => $name ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $event_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>

			<select name="status" id="wet-filter-status">
				<option value=""><?php esc_html_e( 'All Statuses', 'wootickets' ); ?></option>
				<?php foreach ( $statuses as $status ) : ?>
					<option value="<?php echo $status; ?>" <?php selected( $status, $status ); ?>><?php echo esc_html( ucfirst( $status ) ); ?></option>
				<?php endforeach; ?>
			</select>

			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search: Ticket ID, Name, Email, Phone...', 'wootickets' ); ?>" style="min-width: 300px;">

			<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'wootickets' ); ?>">
			<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-tickets' ); ?>" class="button"><?php esc_html_e( 'Reset', 'wootickets' ); ?></a>
		</form>
	</div>

	<?php if ( isset( $_GET['bulk_action'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $_GET['bulk_message'] ?? 'Action completed.' ); ?></p></div>
	<?php endif; ?>

	<form method="post" id="wet-tickets-form">
		<?php wp_nonce_field( 'wet_bulk_tickets' ); ?>
		<input type="hidden" name="wet_bulk_action" value="1">

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th class="check-column"><input type="checkbox" id="wet-check-all"></th>
					<th><?php esc_html_e( 'Ticket ID', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Email', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Phone', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Ticket Type', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Order', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Status', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Purchased', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Check-in', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wootickets' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $tickets['items'] ) ) : ?>
					<?php foreach ( $tickets['items'] as $ticket ) : 
						$status_class = 'wet-status-' . $ticket['status'];
					?>
					<tr>
						<td class="check-column"><input type="checkbox" name="ticket_ids[]" value="<?php echo esc_attr( $ticket['id'] ); ?>"></td>
						<td><strong><?php echo esc_html( $ticket['ticket_id'] ); ?></strong></td>
						<td><?php echo esc_html( $ticket['customer_name'] ); ?></td>
						<td><?php echo esc_html( $ticket['customer_email'] ); ?></td>
						<td><?php echo esc_html( $ticket['customer_phone'] ); ?></td>
						<td><?php echo esc_html( $ticket['ticket_type_name'] ?? '' ); ?></td>
						<td>
							<?php if ( $ticket['order_id'] ) : ?>
								<a href="<?php echo admin_url( 'post.php?post=' . $ticket['order_id'] . '&action=edit' ); ?>" target="_blank">#<?php echo $ticket['order_id']; ?></a>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
						<td><span class="wet-status-badge <?php echo $status_class; ?>"><?php echo esc_html( ucfirst( $ticket['status'] ) ); ?></span></td>
						<td><?php echo date_i18n( 'M j, Y H:i', strtotime( $ticket['purchase_date'] ) ); ?></td>
						<td><?php echo $ticket['check_in_date'] ? date_i18n( 'M j, Y H:i', strtotime( $ticket['check_in_date'] ) ) : '—'; ?></td>
						<td>
							<div class="wet-action-links">
								<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-tickets&action=view&ticket_id=' . $ticket['id'] ); ?>"><?php esc_html_e( 'View', 'wootickets' ); ?></a>
								<?php if ( in_array( $ticket['status'], [ 'active', 'pending' ] ) ) : ?>
								<span class="separator">|</span>
								<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-tickets&action=resend&ticket_id=' . $ticket['id'] ); ?>" class="wet-resend-ticket" data-ticket-id="<?php echo esc_attr( $ticket['id'] ); ?>"><?php esc_html_e( 'Resend', 'wootickets' ); ?></a>
								<?php endif; ?>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="11" class="wet-empty-state">
							<span class="dashicons dashicons-tickets"></span>
							<p><?php esc_html_e( 'No tickets found.', 'wootickets' ); ?></p>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<div class="wet-bulk-actions">
			<select name="bulk_action" id="wet-bulk-action">
				<option value=""><?php esc_html_e( 'Bulk Actions', 'wootickets' ); ?></option>
				<option value="resend"><?php esc_html_e( 'Resend Tickets', 'wootickets' ); ?></option>
				<option value="cancel"><?php esc_html_e( 'Cancel Tickets', 'wootickets' ); ?></option>
				<option value="regenerate_qr"><?php esc_html_e( 'Regenerate QR Codes', 'wootickets' ); ?></option>
				<option value="export"><?php esc_html_e( 'Export CSV', 'wootickets' ); ?></option>
			</select>
			<input type="submit" class="button" value="<?php esc_attr_e( 'Apply', 'wootickets' ); ?>">
		</div>
	</form>

	<?php if ( $tickets['total_pages'] > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<?php
				$base_url = add_query_arg( [ 'paged' => '%#%' ], admin_url( 'admin.php?page=woo-event-tickets-tickets' ) );
				echo paginate_links( [
					'base' => $base_url,
					'format' => '',
					'current' => $tickets['page'],
					'total' => $tickets['total_pages'],
					'prev_text' => '«',
					'next_text' => '»',
				] );
				?>
			</div>
		</div>
	<?php endif; ?>
</div>

<style>
.wet-filter-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.wet-filter-form select, .wet-filter-form input[type="search"] { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
</style>

<script>
jQuery(function($) {
	$('#wet-check-all').on('change', function() {
		$('input[name="ticket_ids[]"]').prop('checked', this.checked);
	});

	$('.wet-resend-ticket').on('click', function(e) {
		e.preventDefault();
		var ticketId = $(this).data('ticket-id');
		var btn = $(this);
		btn.text('<?php esc_html_e( 'Sending...', 'wootickets' ); ?>');

		$.post(ajaxurl, {
			action: 'wet_resend_ticket',
			ticket_id: ticketId,
			nonce: '<?php echo wp_create_nonce( 'wet_ajax_nonce' ); ?>'
		}, function(response) {
			if (response.success) {
				btn.text('<?php esc_html_e( 'Sent!', 'wootickets' ); ?>');
				setTimeout(function() { location.reload(); }, 1500);
			} else {
				btn.text('<?php esc_html_e( 'Resend', 'wootickets' ); ?>');
				alert(response.data.message || '<?php esc_html_e( 'Failed to resend', 'wootickets' ); ?>');
			}
		});
	});
});
</script>