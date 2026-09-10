<?php
/**
 * Dashboard view
 */
?>
<div class="wrap wet-dashboard">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Event Tickets Dashboard', 'wootickets' ); ?></h1>
	
	<div class="wet-stats-grid">
		<div class="wet-stat-card wet-stat-primary">
			<div class="wet-stat-icon"><span class="dashicons dashicons-tag"></span></div>
			<div class="wet-stat-content">
				<span class="wet-stat-value"><?php echo number_format( $total_stats['total_sold'] ); ?></span>
				<span class="wet-stat-label"><?php esc_html_e( 'Total Tickets Sold', 'wootickets' ); ?></span>
			</div>
		</div>

		<div class="wet-stat-card wet-stat-success">
			<div class="wet-stat-icon"><span class="dashicons dashicons-yes"></span></div>
			<div class="wet-stat-content">
				<span class="wet-stat-value"><?php echo number_format( $total_stats['checked_in'] ); ?></span>
				<span class="wet-stat-label"><?php esc_html_e( 'Checked In', 'wootickets' ); ?></span>
			</div>
		</div>

		<div class="wet-stat-card wet-stat-info">
			<div class="wet-stat-icon"><span class="dashicons dashicons-money-alt"></span></div>
			<div class="wet-stat-content">
				<span class="wet-stat-value"><?php echo wc_price( $total_stats['total_revenue'] ); ?></span>
				<span class="wet-stat-label"><?php esc_html_e( 'Total Revenue', 'wootickets' ); ?></span>
			</div>
		</div>

		<div class="wet-stat-card wet-stat-warning">
			<div class="wet-stat-icon"><span class="dashicons dashicons-calendar"></span></div>
			<div class="wet-stat-content">
				<span class="wet-stat-value"><?php echo number_format( $total_stats['active_events'] ); ?></span>
				<span class="wet-stat-label"><?php esc_html_e( 'Active Events', 'wootickets' ); ?></span>
			</div>
		</div>
	</div>

	<div class="wet-dashboard-row">
		<div class="wet-dashboard-col">
			<div class="wet-card">
				<h2><?php esc_html_e( 'Upcoming Events', 'wootickets' ); ?></h2>
				<?php if ( ! empty( $events['items'] ) ) : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Event', 'wootickets' ); ?></th>
								<th><?php esc_html_e( 'Date', 'wootickets' ); ?></th>
								<th><?php esc_html_e( 'Venue', 'wootickets' ); ?></th>
								<th><?php esc_html_e( 'Sold', 'wootickets' ); ?></th>
								<th><?php esc_html_e( 'Revenue', 'wootickets' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'wootickets' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $events['items'] as $event ) : 
								$stats = Woo_Event_Tickets_Events::get_stats( $event['id'] );
							?>
							<tr>
								<td><strong><?php echo esc_html( $event['name'] ); ?></strong></td>
								<td><?php echo date_i18n( 'M j, Y', strtotime( $event['start_datetime'] ) ); ?></td>
								<td><?php echo esc_html( $event['venue'] ); ?></td>
								<td><?php echo number_format( $stats['total_sold'] ); ?></td>
								<td><?php echo wc_price( $stats['total_revenue'] ); ?></td>
								<td>
									<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-events&action=edit&event_id=' . $event['id'] ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'wootickets' ); ?></a>
									<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types&event_id=' . $event['id'] ); ?>" class="button button-small"><?php esc_html_e( 'Tickets', 'wootickets' ); ?></a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'No upcoming events.', 'wootickets' ); ?></p>
					<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-events&action=add' ); ?>" class="button button-primary"><?php esc_html_e( 'Create Event', 'wootickets' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<div class="wet-dashboard-col">
			<div class="wet-card">
				<h2><?php esc_html_e( 'Quick Actions', 'wootickets' ); ?></h2>
				<div class="wet-quick-actions">
					<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-events&action=add' ); ?>" class="wet-action-btn">
						<span class="dashicons dashicons-plus-alt"></span>
						<span><?php esc_html_e( 'New Event', 'wootickets' ); ?></span>
					</a>
					<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types&action=add' ); ?>" class="wet-action-btn">
						<span class="dashicons dashicons-tickets"></span>
						<span><?php esc_html_e( 'New Ticket Type', 'wootickets' ); ?></span>
					</a>
					<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-scanner' ); ?>" class="wet-action-btn">
						<span class="dashicons dashicons-qrcode"></span>
						<span><?php esc_html_e( 'Open Scanner', 'wootickets' ); ?></span>
					</a>
					<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-staff&action=add' ); ?>" class="wet-action-btn">
						<span class="dashicons dashicons-groups"></span>
						<span><?php esc_html_e( 'Add Staff', 'wootickets' ); ?></span>
					</a>
					<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-reports' ); ?>" class="wet-action-btn">
						<span class="dashicons dashicons-chart-bar"></span>
						<span><?php esc_html_e( 'View Reports', 'wootickets' ); ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.wet-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
.wet-stat-card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 16px; border-left: 4px solid #007cba; }
.wet-stat-card.wet-stat-success { border-left-color: #46b450; }
.wet-stat-card.wet-stat-info { border-left-color: #00a0d2; }
.wet-stat-card.wet-stat-warning { border-left-color: #ffb900; }
.wet-stat-icon { width: 48px; height: 48px; border-radius: 8px; background: #f0f0f1; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #007cba; }
.wet-stat-value { display: block; font-size: 28px; font-weight: 600; color: #1d2327; }
.wet-stat-label { font-size: 13px; color: #646970; }
.wet-dashboard-row { display: grid; grid-template-columns: 1fr 350px; gap: 20px; }
.wet-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 20px; }
.wet-card h2 { margin-top: 0; font-size: 16px; font-weight: 600; }
.wet-quick-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.wet-action-btn { display: flex; align-items: center; gap: 10px; padding: 14px; background: #f6f7f7; border-radius: 8px; text-decoration: none; color: #1d2327; transition: all 0.2s; }
.wet-action-btn:hover { background: #e8e8e8; transform: translateY(-1px); }
.wet-action-btn .dashicons { font-size: 20px; width: 20px; height: 20px; }
</style>