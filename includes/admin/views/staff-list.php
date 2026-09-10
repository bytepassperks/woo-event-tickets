<?php
/**
 * Staff list view
 */
$event = $event_id ? Woo_Event_Tickets_Events::get( $event_id ) : null;
$events = Woo_Event_Tickets_Events::get_dropdown_options();
$roles = [ 'scanner' => __( 'Scanner', 'wootickets' ), 'manager' => __( 'Manager', 'wootickets' ), 'admin' => __( 'Admin', 'wootickets' ) ];
?>
<div class="wrap wet-staff-list">
	<div class="wet-page-header">
		<h1><?php echo $event ? esc_html( $event['name'] ) . ' – ' : ''; esc_html_e( 'Event Staff', 'wootickets' ); ?></h1>
		<div class="wet-header-actions">
			<?php if ( $event ) : ?>
				<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-staff&action=add&event_id=' . $event['id'] ); ?>" class="button button-primary"><?php esc_html_e( 'Add Staff', 'wootickets' ); ?></a>
			<?php else : ?>
				<select name="event_filter" id="wet-staff-event-filter" class="regular-text" style="margin-right: 10px;">
					<option value=""><?php esc_html_e( 'Filter by Event', 'wootickets' ); ?></option>
					<?php foreach ( $events as $id => $name ) : ?>
						<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $event_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
				<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-staff&action=add' ); ?>" class="button button-primary"><?php esc_html_e( 'Add Staff', 'wootickets' ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Staff member saved successfully.', 'wootickets' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Staff member deleted successfully.', 'wootickets' ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! $event && ! $event_id ) : ?>
		<div class="wet-no-event-selected">
			<span class="dashicons dashicons-filter"></span>
			<p><?php esc_html_e( 'Please select an event from the dropdown to view its staff.', 'wootickets' ); ?></p>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Email', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Role', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Event', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Status', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Added', 'wootickets' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wootickets' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $staff ) ) : ?>
					<?php foreach ( $staff as $s ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $s['display_name'] ); ?></strong>
							<br><small><?php echo esc_html( $s['user_login'] ); ?></small>
						</td>
						<td><?php echo esc_html( $s['user_email'] ); ?></td>
						<td><span class="wet-role-badge wet-role-<?php echo esc_attr( $s['role'] ); ?>"><?php echo esc_html( $roles[ $s['role'] ] ?? $s['role'] ); ?></span></td>
						<td><?php echo esc_html( $s['event_name'] ); ?></td>
						<td><span class="wet-status-badge <?php echo $s['active'] ? 'wet-status-active' : 'wet-status-inactive'; ?>"><?php echo $s['active'] ? esc_html__( 'Active', 'wootickets' ) : esc_html__( 'Inactive', 'wootickets' ); ?></span></td>
						<td><?php echo date_i18n( 'M j, Y', strtotime( $s['created_at'] ) ); ?></td>
						<td>
							<div class="wet-action-links">
								<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-staff&action=edit&staff_id=' . $s['id'] ); ?>"><?php esc_html_e( 'Edit', 'wootickets' ); ?></a>
								<span class="separator">|</span>
								<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-staff&action=delete&staff_id=' . $s['id'] ); ?>" class="wet-delete-staff" data-nonce="<?php echo wp_create_nonce( 'wet_delete_staff' ); ?>"><?php esc_html_e( 'Delete', 'wootickets' ); ?></a>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="7" class="wet-empty-state">
							<span class="dashicons dashicons-groups"></span>
							<p><?php esc_html_e( 'No staff members assigned.', 'wootickets' ); ?></p>
							<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-staff&action=add' . ( $event_id ? '&event_id=' . $event_id : '' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add Staff', 'wootickets' ); ?></a>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<style>
.wet-role-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.wet-role-scanner { background: #e8e8e8; color: #646970; }
.wet-role-manager { background: #cce5ff; color: #004085; }
.wet-role-admin { background: #fff3cd; color: #856404; }
</style>

<script>
jQuery(function($) {
	$('#wet-staff-event-filter').on('change', function() {
		var eventId = $(this).val();
		if (eventId) {
			window.location.href = '<?php echo admin_url( 'admin.php?page=woo-event-tickets-staff&event_id=' ); ?>' + eventId;
		} else {
			window.location.href = '<?php echo admin_url( 'admin.php?page=woo-event-tickets-staff' ); ?>';
		}
	});

	$('.wet-delete-staff').on('click', function(e) {
		if (!confirm('<?php esc_js( __( 'Are you sure you want to delete this staff member?', 'wootickets' ) ); ?>')) {
			e.preventDefault();
		}
	});
});
</script>