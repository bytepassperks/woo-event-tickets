<?php
/**
 * Events list view
 */
?>
<div class="wrap wet-events-list">
	<div class="wet-page-header">
		<h1><?php esc_html_e( 'Events', 'wootickets' ); ?></h1>
		<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-events&action=add' ); ?>" class="button button-primary"><?php esc_html_e( 'Add New Event', 'wootickets' ); ?></a>
	</div>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Event saved successfully.', 'wootickets' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['created'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Event created successfully.', 'wootickets' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Event deleted successfully.', 'wootickets' ); ?></p></div>
	<?php endif; ?>

	<div class="wet-filters">
		<form method="get">
			<input type="hidden" name="page" value="woo-event-tickets-events">
			<select name="status" id="wet-filter-status">
				<option value=""><?php esc_html_e( 'All Statuses', 'wootickets' ); ?></option>
				<option value="draft" <?php selected( $_GET['status'] ?? '', 'draft' ); ?>><?php esc_html_e( 'Draft', 'wootickets' ); ?></option>
				<option value="published" <?php selected( $_GET['status'] ?? '', 'published' ); ?>><?php esc_html_e( 'Published', 'wootickets' ); ?></option>
				<option value="completed" <?php selected( $_GET['status'] ?? '', 'completed' ); ?>><?php esc_html_e( 'Completed', 'wootickets' ); ?></option>
				<option value="cancelled" <?php selected( $_GET['status'] ?? '', 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'wootickets' ); ?></option>
			</select>
			<input type="search" name="s" value="<?php echo esc_attr( $_GET['s'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Search events...', 'wootickets' ); ?>">
			<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'wootickets' ); ?>">
		</form>
	</div>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th width="10%"><?php esc_html_e( 'Banner', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Event', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Date & Time', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Venue', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Status', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Capacity', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Tickets Sold', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Revenue', 'wootickets' ); ?></th>
				<th width="15%"><?php esc_html_e( 'Actions', 'wootickets' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $events['items'] ) ) : ?>
				<?php foreach ( $events['items'] as $event ) : 
					$stats = Woo_Event_Tickets_Events::get_stats( $event['id'] );
					$status_class = 'wet-status-' . $event['status'];
				?>
				<tr>
					<td>
						<?php if ( $event['banner_url'] ) : ?>
							<img src="<?php echo esc_url( $event['banner_url'] ); ?>" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
						<?php else : ?>
							<span class="dashicons dashicons-calendar" style="font-size: 24px; color: #ccc;"></span>
						<?php endif; ?>
					</td>
					<td>
						<strong><?php echo esc_html( $event['name'] ); ?></strong>
						<?php if ( $event['slug'] ) : ?>
							<br><small><?php echo esc_html( $event['slug'] ); ?></small>
						<?php endif; ?>
					</td>
					<td>
						<?php echo date_i18n( 'M j, Y', strtotime( $event['start_datetime'] ) ); ?> @ <?php echo date_i18n( 'g:i A', strtotime( $event['start_datetime'] ) ); ?>
						<?php if ( $event['end_datetime'] ) : ?>
							<br><small><?php echo date_i18n( 'M j, Y', strtotime( $event['end_datetime'] ) ); ?> @ <?php echo date_i18n( 'g:i A', strtotime( $event['end_datetime'] ) ); ?></small>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $event['venue'] ); ?><?php if ( $event['address'] ) : ?><br><small><?php echo esc_html( $event['address'] ); ?></small><?php endif; ?></td>
					<td><span class="wet-status-badge <?php echo $status_class; ?>"><?php echo esc_html( ucfirst( $event['status'] ) ); ?></span></td>
					<td><?php echo $event['capacity'] ? number_format( $event['capacity'] ) : '—'; ?></td>
					<td><?php echo number_format( $stats['total_sold'] ); ?></td>
					<td><?php echo wc_price( $stats['total_revenue'] ); ?></td>
					<td>
						<div class="wet-action-links">
							<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-events&action=edit&event_id=' . $event['id'] ); ?>"><?php esc_html_e( 'Edit', 'wootickets' ); ?></a>
							<span class="separator">|</span>
							<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types&event_id=' . $event['id'] ); ?>"><?php esc_html_e( 'Tickets', 'wootickets' ); ?></a>
							<span class="separator">|</span>
							<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-tickets&event_id=' . $event['id'] ); ?>"><?php esc_html_e( 'Attendees', 'wootickets' ); ?></a>
							<?php if ( $event['status'] === 'published' ) : ?>
							<span class="separator">|</span>
							<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-scanner&event_id=' . $event['id'] ); ?>" target="_blank"><?php esc_html_e( 'Scanner', 'wootickets' ); ?></a>
							<?php endif; ?>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="9" class="wet-empty-state">
						<span class="dashicons dashicons-calendar-alt"></span>
						<p><?php esc_html_e( 'No events found.', 'wootickets' ); ?></p>
						<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-events&action=add' ); ?>" class="button button-primary"><?php esc_html_e( 'Create Your First Event', 'wootickets' ); ?></a>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $events['total_pages'] > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<?php
				$base_url = admin_url( 'admin.php?page=woo-event-tickets-events' );
				echo paginate_links( [
					'base' => $base_url . '&paged=%#%',
					'format' => '',
					'current' => $events['page'],
					'total' => $events['total_pages'],
					'prev_text' => '«',
					'next_text' => '»',
				] );
				?>
			</div>
		</div>
	<?php endif; ?>
</div>

<style>
.wet-page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.wet-filters { background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.wet-filters select, .wet-filters input[type="search"] { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
.wet-empty-state { text-align: center; padding: 60px 20px; color: #646970; }
.wet-empty-state .dashicons { font-size: 48px; margin-bottom: 15px; color: #ccc; }
.wet-empty-state p { margin-bottom: 20px; }
.wet-status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.wet-status-draft { background: #fff3cd; color: #856404; }
.wet-status-published { background: #d4edda; color: #155724; }
.wet-status-completed { background: #cce5ff; color: #004085; }
.wet-status-cancelled { background: #f8d7da; color: #721c24; }
.wet-action-links { display: flex; flex-wrap: wrap; gap: 5px; }
.wet-action-links .separator { color: #ccc; }
</style>