<?php
/**
 * Check-ins view
 */
$event = $event_id ? Woo_Event_Tickets_Events::get( $event_id ) : null;
$events = Woo_Event_Tickets_Events::get_dropdown_options();
$results = [ 'valid', 'already_used', 'invalid', 'cancelled', 'refunded', 'expired', 'blocked' ];
?>
<div class="wrap wet-checkins-list">
	<div class="wet-page-header">
		<h1><?php echo $event ? esc_html( $event['name'] ) . ' – ' : ''; esc_html_e( 'Check-ins', 'wootickets' ); ?></h1>
	</div>

	<div class="wet-filters">
		<form method="get" class="wet-filter-form">
			<input type="hidden" name="page" value="woo-event-tickets-checkins">
			
			<select name="event_id" id="wet-filter-event">
				<option value=""><?php esc_html_e( 'All Events', 'wootickets' ); ?></option>
				<?php foreach ( $events as $id => $name ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $event_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>

			<select name="result" id="wet-filter-result">
				<option value=""><?php esc_html_e( 'All Results', 'wootickets' ); ?></option>
				<?php foreach ( $results as $result ) : ?>
					<option value="<?php echo $result; ?>" <?php selected( $result, $result ); ?>><?php echo esc_html( ucfirst( str_replace( '_', ' ', $result ) ) ); ?></option>
				<?php endforeach; ?>
			</select>

			<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'wootickets' ); ?>">
			<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-checkins' ); ?>" class="button"><?php esc_html_e( 'Reset', 'wootickets' ); ?></a>
		</form>
	</div>

	<?php if ( $event_id && ! empty( $stats ) ) : ?>
	<div class="wet-checkin-stats">
		<div class="wet-stat-card">
			<span class="wet-stat-value"><?php echo number_format( $stats['total_scans'] ); ?></span>
			<span class="wet-stat-label"><?php esc_html_e( 'Total Scans', 'wootickets' ); ?></span>
		</div>
		<div class="wet-stat-card wet-stat-success">
			<span class="wet-stat-value"><?php echo number_format( $stats['valid'] ); ?></span>
			<span class="wet-stat-label"><?php esc_html_e( 'Valid Check-ins', 'wootickets' ); ?></span>
		</div>
		<div class="wet-stat-card wet-stat-danger">
			<span class="wet-stat-value"><?php echo number_format( $stats['rejected'] ); ?></span>
			<span class="wet-stat-label"><?php esc_html_e( 'Rejected', 'wootickets' ); ?></span>
		</div>
	</div>

	<div class="wet-staff-performance">
		<h2><?php esc_html_e( 'Staff Performance', 'wootickets' ); ?></h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Staff', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Total Scans', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Valid', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Rejected', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'First Scan', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Last Scan', 'wootickets' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $stats['by_staff'] as $staff ) : ?>
				<tr>
					<td><?php echo esc_html( $staff['staff_name'] ); ?></td>
					<td><?php echo number_format( $staff['total_scans'] ); ?></td>
					<td style="color: #46b450;"><?php echo number_format( $staff['valid_scans'] ); ?></td>
					<td style="color: #dc3232;"><?php echo number_format( $staff['rejected_scans'] ); ?></td>
					<td><?php echo $staff['first_scan'] ? date_i18n( 'H:i:s', strtotime( $staff['first_scan'] ) ) : '—'; ?></td>
					<td><?php echo $staff['last_scan'] ? date_i18n( 'H:i:s', strtotime( $staff['last_scan'] ) ) : '—'; ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Time', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Ticket ID', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Ticket Type', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Result', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'Staff', 'wootickets' ); ?></th>
				<th><?php esc_html_e( 'IP Address', 'wootickets' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $checkins ) ) : ?>
				<?php foreach ( $checkins as $checkin ) : ?>
				<tr class="wet-checkin-<?php echo esc_attr( $checkin['result'] ); ?>">
					<td><?php echo date_i18n( 'M j, Y H:i:s', strtotime( $checkin['scanned_at'] ) ); ?></td>
					<td><?php echo esc_html( $checkin['ticket_id'] ); ?></td>
					<td><?php echo esc_html( $checkin['ticket_type_name'] ); ?></td>
					<td><span class="wet-status-badge wet-status-<?php echo esc_attr( $checkin['result'] ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $checkin['result'] ) ) ); ?></span></td>
					<td><?php echo esc_html( $checkin['staff_name'] ); ?></td>
					<td><?php echo esc_html( $checkin['ip_address'] ); ?></td>
				</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="6" class="wet-empty-state">
						<span class="dashicons dashicons-qrcode"></span>
						<p><?php esc_html_e( 'No check-ins found.', 'wootickets' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $event_id ) : ?>
	<div class="wet-hourly-chart">
		<h2><?php esc_html_e( 'Check-ins by Hour', 'wootickets' ); ?></h2>
		<div class="wet-chart-bars">
			<?php 
			$max_count = 0;
			foreach ( $stats['by_hour'] as $h ) { $max_count = max( $max_count, $h['count'] ); }
			?>
			<?php foreach ( $stats['by_hour'] as $hour ) : 
				$height = $max_count > 0 ? ( $hour['count'] / $max_count ) * 100 : 0;
			?>
				<div class="wet-chart-bar" title="<?php printf( esc_html__( '%d check-ins at %d:00', 'wootickets' ), $hour['count'], $hour['hour'] ); ?>">
					<div class="wet-chart-bar-fill" style="height: <?php echo $height; ?>%;"></div>
					<div class="wet-chart-bar-label"><?php echo sprintf( '%02d:00', $hour['hour'] ); ?></div>
					<div class="wet-chart-bar-value"><?php echo $hour['count']; ?></div>
				</div>
			<?php endforeach; ?>
			<?php if ( empty( $stats['by_hour'] ) ) : ?>
				<p><?php esc_html_e( 'No check-in data available.', 'wootickets' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
</div>

<style>
.wet-checkin-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
.wet-staff-performance { margin-bottom: 30px; }
.wet-hourly-chart { margin-top: 30px; }
.wet-chart-bars { display: flex; align-items: flex-end; gap: 8px; height: 200px; padding: 20px 0; }
.wet-chart-bar { flex: 1; display: flex; flex-direction: column; align-items: center; position: relative; }
.wet-chart-bar-fill { width: 100%; background: #007cba; border-radius: 4px 4px 0 0; transition: height 0.3s; position: absolute; bottom: 0; left: 0; right: 0; }
.wet-chart-bar-fill:hover { background: #005a9c; }
.wet-chart-bar-label { position: relative; z-index: 1; font-size: 11px; color: #646970; margin-top: 5px; }
.wet-chart-bar-value { position: relative; z-index: 1; font-size: 12px; font-weight: 600; margin-bottom: 5px; color: #007cba; }
.wet-checkin-valid { background: #f0fff4; }
.wet-checkin-already_used, .wet-checkin-invalid, .wet-checkin-cancelled, .wet-checkin-refunded, .wet-checkin-expired, .wet-checkin-blocked { background: #fff5f5; }
</style>

<script>
jQuery(function($) {
	$('#wet-filter-event').on('change', function() {
		this.form.submit();
	});
	$('#wet-filter-result').on('change', function() {
		this.form.submit();
	});
});
</script>