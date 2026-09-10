<?php
/**
 * Reports view
 */
$events = Woo_Event_Tickets_Events::get_dropdown_options();
$report_types = [
	'sales' => __( 'Sales Report', 'wootickets' ),
	'checkins' => __( 'Check-in Report', 'wootickets' ),
	'customers' => __( 'Customer Report', 'wootickets' ),
	'revenue' => __( 'Revenue Report', 'wootickets' ),
];
?>
<div class="wrap wet-reports-page">
	<div class="wet-page-header">
		<h1><?php esc_html_e( 'Reports', 'wootickets' ); ?></h1>
	</div>

	<div class="wet-reports-tabs">
		<nav class="wet-tab-nav">
			<?php foreach ( $report_types as $key => $label ) : ?>
				<button type="button" class="wet-tab-btn <?php echo $report_type === $key ? 'active' : ''; ?>" data-tab="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</nav>
	</div>

	<form method="get" class="wet-report-filters">
		<input type="hidden" name="page" value="woo-event-tickets-reports">
		<input type="hidden" name="type" value="<?php echo esc_attr( $report_type ); ?>">

		<select name="event_id" class="regular-text">
			<option value=""><?php esc_html_e( 'All Events', 'wootickets' ); ?></option>
			<?php foreach ( $events as $id => $name ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $event_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>

		<input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" class="regular-text">
		<span>to</span>
		<input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" class="regular-text">

		<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Generate Report', 'wootickets' ); ?>">
		<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-reports&type=' . $report_type . '&event_id=' . $event_id . '&date_from=' . $date_from . '&date_to=' . $date_to . '&export=csv' ); ?>" class="button"><?php esc_html_e( 'Export CSV', 'wootickets' ); ?></a>
	</form>

	<div class="wet-report-content">
		<?php if ( $report_type === 'sales' ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Ticket ID', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Event', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Ticket Type', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Customer', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Email', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Quantity', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Price', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Total', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Date', 'wootickets' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $report_data as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row['ticket_id'] ); ?></td>
						<td><?php echo esc_html( $row['event_name'] ); ?></td>
						<td><?php echo esc_html( $row['ticket_type_name'] ); ?></td>
						<td><?php echo esc_html( $row['customer_name'] ); ?></td>
						<td><?php echo esc_html( $row['customer_email'] ); ?></td>
						<td><?php echo number_format( $row['quantity'] ); ?></td>
						<td><?php echo wc_price( $row['price_paid'] ); ?></td>
						<td><?php echo wc_price( $row['price_paid'] * $row['quantity'] ); ?></td>
						<td><span class="wet-status-badge wet-status-<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( ucfirst( $row['status'] ) ); ?></span></td>
						<td><?php echo date_i18n( 'M j, Y H:i', strtotime( $row['created_at'] ) ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php elseif ( $report_type === 'checkins' ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Time', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Ticket ID', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Ticket Type', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Result', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Staff', 'wootickets' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $report_data as $row ) : ?>
					<tr>
						<td><?php echo date_i18n( 'M j, Y H:i:s', strtotime( $row['scanned_at'] ) ); ?></td>
						<td><?php echo esc_html( $row['ticket_id'] ); ?></td>
						<td><?php echo esc_html( $row['ticket_type_name'] ); ?></td>
						<td><span class="wet-status-badge wet-status-<?php echo esc_attr( $row['result'] ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $row['result'] ) ) ); ?></span></td>
						<td><?php echo esc_html( $row['staff_name'] ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php elseif ( $report_type === 'customers' ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Customer', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Email', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Tickets', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Total Qty', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Total Spent', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Last Purchase', 'wootickets' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $report_data as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row['customer_name'] ); ?></td>
						<td><?php echo esc_html( $row['customer_email'] ); ?></td>
						<td><?php echo esc_html( $row['customer_phone'] ); ?></td>
						<td><?php echo number_format( $row['ticket_count'] ); ?></td>
						<td><?php echo number_format( $row['total_quantity'] ); ?></td>
						<td><?php echo wc_price( $row['total_spent'] ); ?></td>
						<td><?php echo date_i18n( 'M j, Y', strtotime( $row['last_purchase'] ) ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php elseif ( $report_type === 'revenue' ) : ?>
			<div class="wet-revenue-summary">
				<?php 
				$total_revenue = 0;
				$total_tickets = 0;
				foreach ( $report_data as $row ) {
					$total_revenue += $row['revenue'];
					$total_tickets += $row['quantity'];
				}
				?>
				<div class="wet-stat-card"><span class="wet-stat-value"><?php echo wc_price( $total_revenue ); ?></span><span class="wet-stat-label"><?php esc_html_e( 'Total Revenue', 'wootickets' ); ?></span></div>
				<div class="wet-stat-card"><span class="wet-stat-value"><?php echo number_format( $total_tickets ); ?></span><span class="wet-stat-label"><?php esc_html_e( 'Tickets Sold', 'wootickets' ); ?></span></div>
			</div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Tickets Sold', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Quantity', 'wootickets' ); ?></th>
						<th><?php esc_html_e( 'Revenue', 'wootickets' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $report_data as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row['date'] ); ?></td>
						<td><?php echo number_format( $row['tickets_sold'] ); ?></td>
						<td><?php echo number_format( $row['quantity'] ); ?></td>
						<td><?php echo wc_price( $row['revenue'] ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

<style>
.wet-report-filters { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 20px; padding: 15px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.wet-report-filters select, .wet-report-filters input[type="date"] { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
.wet-revenue-summary { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px; }
.wet-stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-align: center; }
.wet-stat-card .wet-stat-value { display: block; font-size: 28px; font-weight: 600; color: #007cba; }
.wet-stat-card .wet-stat-label { font-size: 13px; color: #646970; }
</style>

<script>
jQuery(function($) {
	$('.wet-tab-btn').on('click', function() {
		var tab = $(this).data('tab');
		window.location.href = '<?php echo admin_url( 'admin.php?page=woo-event-tickets-reports&type=' ); ?>' + tab + 
			'&event_id=<?php echo esc_js( $event_id ); ?>' + 
			'&date_from=<?php echo esc_js( $date_from ); ?>' + 
			'&date_to=<?php echo esc_js( $date_to ); ?>';
	});
});
</script>