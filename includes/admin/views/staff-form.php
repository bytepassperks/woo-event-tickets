<?php
/**
 * Staff form view
 */
$is_edit = ! empty( $staff );
$event = $event_id ? Woo_Event_Tickets_Events::get( $event_id ) : null;
$events = Woo_Event_Tickets_Events::get_dropdown_options();
$available_users = Woo_Event_Tickets_Staff::get_available_users();
$roles = [ 'scanner' => __( 'Scanner', 'wootickets' ), 'manager' => __( 'Manager', 'wootickets' ), 'admin' => __( 'Admin', 'wootickets' ) ];
?>
<div class="wrap wet-staff-form">
	<div class="wet-page-header">
		<h1><?php echo $is_edit ? esc_html__( 'Edit Staff Member', 'wootickets' ) : esc_html__( 'Add Staff Member', 'wootickets' ); ?></h1>
		<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-staff' ); ?>" class="button"><?php esc_html_e( 'Back to Staff', 'wootickets' ); ?></a>
	</div>

	<form method="post" id="wet-staff-form">
		<?php wp_nonce_field( 'wet_staff_nonce' ); ?>
		<input type="hidden" name="wet_save_staff" value="1">

		<table class="form-table">
			<tr>
				<th><label for="event_id"><?php esc_html_e( 'Event', 'wootickets' ); ?> <span class="required">*</span></label></th>
				<td>
					<select name="event_id" id="event_id" class="regular-text" <?php echo $is_edit ? 'disabled' : ''; ?> required>
						<?php foreach ( $events as $id => $name ) : ?>
							<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $staff['event_id'] ?? $event_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="user_id"><?php esc_html_e( 'User', 'wootickets' ); ?> <span class="required">*</span></label></th>
				<td>
					<select name="user_id" id="user_id" class="regular-text" <?php echo $is_edit ? 'disabled' : ''; ?> required>
						<option value=""><?php esc_html_e( 'Select a user', 'wootickets' ); ?></option>
						<?php foreach ( $available_users as $user ) : ?>
							<option value="<?php echo esc_attr( $user['ID'] ); ?>" <?php selected( $staff['user_id'] ?? 0, $user['ID'] ); ?>>
								<?php echo esc_html( $user['display_name'] . ' (' . $user['user_email'] . ')' ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Only users without existing staff assignments are shown.', 'wootickets' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="role"><?php esc_html_e( 'Role', 'wootickets' ); ?></label></th>
				<td>
					<select name="role" id="role" class="regular-text">
						<?php foreach ( $roles as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $staff['role'] ?? 'scanner', $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<strong><?php esc_html_e( 'Scanner:', 'wootickets' ); ?></strong> <?php esc_html_e( 'Can scan tickets only.', 'wootickets' ); ?><br>
						<strong><?php esc_html_e( 'Manager:', 'wootickets' ); ?></strong> <?php esc_html_e( 'Can scan, view reports, manage staff.', 'wootickets' ); ?><br>
						<strong><?php esc_html_e( 'Admin:', 'wootickets' ); ?></strong> <?php esc_html_e( 'Full access to event management.', 'wootickets' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th><label for="permissions"><?php esc_html_e( 'Custom Permissions (JSON)', 'wootickets' ); ?></label></th>
				<td>
					<textarea name="permissions" id="permissions" class="large-text code" rows="5"><?php echo esc_textarea( $staff['permissions'] ?? '{}' ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Optional custom permissions as JSON. Example: {"can_export":true,"can_manual_checkin":false}', 'wootickets' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Active', 'wootickets' ); ?></th>
				<td>
					<label><input type="checkbox" name="active" value="1" <?php checked( $staff['active'] ?? 1, 1 ); ?>> <?php esc_html_e( 'Yes, this staff member is active', 'wootickets' ); ?></label>
				</td>
			</tr>
		</table>

		<div class="wet-form-actions">
			<button type="submit" class="button button-primary button-large"><?php echo $is_edit ? esc_html__( 'Update Staff', 'wootickets' ) : esc_html__( 'Add Staff Member', 'wootickets' ); ?></button>
			<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-staff' ); ?>" class="button button-large"><?php esc_html_e( 'Cancel', 'wootickets' ); ?></a>
		</div>
	</form>
</div>