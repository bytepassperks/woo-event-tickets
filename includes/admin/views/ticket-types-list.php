<?php
/**
 * Ticket types list view
 */
?>
<div class="wrap wet-ticket-types-list">
	<div class="wet-page-header">
		<h1>
			<?php if ( $event ) : ?>
				<?php echo esc_html( $event['name'] ); ?> – <?php esc_html_e( 'Ticket Types', 'wootickets' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'All Ticket Types', 'wootickets' ); ?>
			<?php endif; ?>
		</h1>
		<div class="wet-header-actions">
			<?php if ( $event ) : ?>
				<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types&action=add&event_id=' . $event['id'] ); ?>" class="button button-primary"><?php esc_html_e( 'Add Ticket Type', 'wootickets' ); ?></a>
			<?php else : ?>
				<select name="event_filter" id="wet-event-filter" class="regular-text" style="margin-right: 10px;">
					<option value=""><?php esc_html_e( 'Filter by Event', 'wootickets' ); ?></option>
					<?php foreach ( $events as $id => $name ) : ?>
						<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $event_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
				<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types&action=add' ); ?>" class="button button-primary"><?php esc_html_e( 'Add Ticket Type', 'wootickets' ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Ticket type saved successfully.', 'wootickets' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['created'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Ticket type created successfully.', 'wootickets' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Ticket type deleted successfully.', 'wootickets' ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! $event && ! $event_id ) : ?>
		<div class="wet-no-event-selected">
			<span class="dashicons dashicons-filter"></span>
			<p><?php esc_html_e( 'Please select an event from the dropdown to view its ticket types.', 'wootickets' ); ?></p>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Price', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Early Bird', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Quantity', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Remaining', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Sold', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Status', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'WooCommerce', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wootickets' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $ticket_types ) ) : ?>
					<?php foreach ( $ticket_types as $type ) : 
						$display_price = Woo_Event_Tickets_Ticket_Types::get_display_price( $type );
						$remaining = Woo_Event_Tickets_Ticket_Types::get_remaining_quantity( $type['id'] );
						$sold = $type['total_quantity'] - $remaining;
					?>
					<tr>
						<td>
							<strong><?php echo esc_html( $type['name'] ); ?></strong>
							<?php if ( $type['category'] ) : ?>
								<br><span class="wet-category"><?php echo esc_html( ucfirst( $type['category'] ) ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $display_price['is_early_bird'] ) : ?>
								<span class="wet-price-early-bird"><?php echo wc_price( $display_price['price'] ); ?></span>
								<small class="wet-early-bird-badge"><?php esc_html_e( 'Early Bird', 'wootickets' ); ?></small>
								<?php if ( $display_price['early_bird_remaining'] > 0 ) : ?>
									<br><small><?php printf( esc_html__( '%d left at this price', 'wootickets' ), $display_price['early_bird_remaining'] ); ?></small>
								<?php endif; ?>
							<?php elseif ( ! empty( $display_price['is_sale'] ) ) : ?>
								<del><?php echo wc_price( $display_price['original_price'] ); ?></del>
								<ins><?php echo wc_price( $display_price['price'] ); ?></ins>
							<?php else : ?>
								<?php echo wc_price( $display_price['price'] ); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $type['early_bird_price'] ) : ?>
								<?php echo wc_price( $type['early_bird_price'] ); ?>
								<br><small><?php printf( esc_html__( 'Qty: %d', 'wootickets' ), $type['early_bird_quantity'] ); ?></small>
								<?php if ( $type['early_bird_start'] && $type['early_bird_end'] ) : ?>
									<br><small><?php echo date_i18n( 'M j', strtotime( $type['early_bird_start'] ) ); ?> – <?php echo date_i18n( 'M j', strtotime( $type['early_bird_end'] ) ); ?></small>
								<?php endif; ?>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
						<td><?php echo number_format( $type['total_quantity'] ); ?></td>
						<td><span class="wet-remaining"><?php echo number_format( $remaining ); ?></span></td>
						<td><?php echo number_format( $sold ); ?></td>
						<td><span class="wet-status-badge wet-status-<?php echo esc_attr( $type['status'] ); ?>"><?php echo esc_html( ucfirst( $type['status'] ) ); ?></span></td>
						<td>
							<?php if ( $type['woocommerce_product_id'] ) : ?>
								<a href="<?php echo get_edit_post_link( $type['woocommerce_product_id'] ); ?>" target="_blank">ID: <?php echo $type['woocommerce_product_id']; ?></a>
							<?php else : ?>
								<span class="dashicons dashicons-warning" style="color: #ffb900;"></span> <?php esc_html_e( 'Not linked', 'wootickets' ); ?>
							<?php endif; ?>
						</td>
						<td>
							<div class="wet-action-links">
								<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types&action=edit&type_id=' . $type['id'] . '&event_id=' . $type['event_id'] ); ?>"><?php esc_html_e( 'Edit', 'wootickets' ); ?></a>
								<span class="separator">|</span>
								<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-tickets&event_id=' . $type['event_id'] . '&ticket_type_id=' . $type['id'] ); ?>"><?php esc_html_e( 'Tickets', 'wootickets' ); ?></a>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="9" class="wet-empty-state">
							<span class="dashicons dashicons-tickets"></span>
							<p><?php esc_html_e( 'No ticket types found.', 'wootickets' ); ?></p>
							<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types&action=add' . ( $event_id ? '&event_id=' . $event_id : '' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Create Ticket Type', 'wootickets' ); ?></a>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<style>
.wet-header-actions { display: flex; align-items: center; gap: 10px; }
.wet-no-event-selected { text-align: center; padding: 60px 20px; color: #646970; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.wet-no-event-selected .dashicons { font-size: 48px; margin-bottom: 15px; color: #ccc; }
.wet-category { display: inline-block; background: #e8e8e8; padding: 2px 8px; border-radius: 10px; font-size: 11px; color: #646970; }
.wet-price-early-bird { color: #46b450; font-weight: 600; }
.wet-early-bird-badge { display: inline-block; background: #46b450; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 5px; }
.wet-remaining { font-weight: 600; color: #46b450; }
.wet-status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.wet-status-active { background: #d4edda; color: #155724; }
.wet-status-inactive { background: #fff3cd; color: #856404; }
.wet-status-sold_out { background: #f8d7da; color: #721c24; }
</style>

<script>
jQuery(function($) {
	$('#wet-event-filter').on('change', function() {
		var eventId = $(this).val();
		if (eventId) {
			window.location.href = '<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types&event_id=' ); ?>' + eventId;
		} else {
			window.location.href = '<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types' ); ?>';
		}
	});
});
</script>