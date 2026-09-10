<?php
/**
 * Event form view
 */
$is_edit = ! empty( $event );
$action = $is_edit ? 'edit' : 'add';
?>
<div class="wrap wet-event-form">
	<div class="wet-page-header">
		<h1><?php echo $is_edit ? esc_html__( 'Edit Event', 'wootickets' ) : esc_html__( 'Add New Event', 'wootickets' ); ?></h1>
		<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-events' ); ?>" class="button"><?php esc_html_e( 'Back to Events', 'wootickets' ); ?></a>
	</div>

	<form method="post" id="wet-event-form">
		<?php wp_nonce_field( 'wet_event_nonce' ); ?>
		<input type="hidden" name="wet_save_event" value="1">

		<div class="wet-form-tabs">
			<nav class="wet-tab-nav">
				<button type="button" class="wet-tab-btn active" data-tab="general"><?php esc_html_e( 'General', 'wootickets' ); ?></button>
				<button type="button" class="wet-tab-btn" data-tab="schedule"><?php esc_html_e( 'Schedule', 'wootickets' ); ?></button>
				<button type="button" class="wet-tab-btn" data-tab="venue"><?php esc_html_e( 'Venue', 'wootickets' ); ?></button>
				<button type="button" class="wet-tab-btn" data-tab="branding"><?php esc_html_e( 'Branding', 'wootickets' ); ?></button>
				<button type="button" class="wet-tab-btn" data-tab="organizer"><?php esc_html_e( 'Organizer', 'wootickets' ); ?></button>
			</nav>

			<div class="wet-tab-panels">
				<!-- General Tab -->
				<div class="wet-tab-panel active" id="tab-general">
					<table class="form-table">
						<tr>
							<th><label for="name"><?php esc_html_e( 'Event Name', 'wootickets' ); ?> <span class="required">*</span></label></th>
							<td>
								<input type="text" name="name" id="name" value="<?php echo esc_attr( $event['name'] ?? '' ); ?>" class="regular-text" required>
							</td>
						</tr>
						<tr>
							<th><label for="slug"><?php esc_html_e( 'Slug', 'wootickets' ); ?></label></th>
							<td>
								<input type="text" name="slug" id="slug" value="<?php echo esc_attr( $event['slug'] ?? '' ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'URL-friendly name. Auto-generated if left empty.', 'wootickets' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="description"><?php esc_html_e( 'Description', 'wootickets' ); ?></label></th>
							<td>
								<?php wp_editor( $event['description'] ?? '', 'description', [ 'textarea_name' => 'description', 'media_buttons' => true ] ); ?>
							</td>
						</tr>
						<tr>
							<th><label for="banner_url"><?php esc_html_e( 'Banner Image URL', 'wootickets' ); ?></label></th>
							<td>
								<input type="url" name="banner_url" id="banner_url" value="<?php echo esc_attr( $event['banner_url'] ?? '' ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'Full URL to banner image (recommended 1920x600px).', 'wootickets' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="logo_url"><?php esc_html_e( 'Logo Image URL', 'wootickets' ); ?></label></th>
							<td>
								<input type="url" name="logo_url" id="logo_url" value="<?php echo esc_attr( $event['logo_url'] ?? '' ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'Full URL to logo image (recommended 400x400px).', 'wootickets' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<!-- Schedule Tab -->
				<div class="wet-tab-panel" id="tab-schedule">
					<table class="form-table">
						<tr>
							<th><label for="start_datetime"><?php esc_html_e( 'Start Date & Time', 'wootickets' ); ?> <span class="required">*</span></label></th>
							<td>
								<input type="datetime-local" name="start_datetime" id="start_datetime" value="<?php echo $event['start_datetime'] ? date( 'Y-m-d\TH:i', strtotime( $event['start_datetime'] ) ) : ''; ?>" class="regular-text" required>
							</td>
						</tr>
						<tr>
							<th><label for="end_datetime"><?php esc_html_e( 'End Date & Time', 'wootickets' ); ?> <span class="required">*</span></label></th>
							<td>
								<input type="datetime-local" name="end_datetime" id="end_datetime" value="<?php echo $event['end_datetime'] ? date( 'Y-m-d\TH:i', strtotime( $event['end_datetime'] ) ) : ''; ?>" class="regular-text" required>
							</td>
						</tr>
						<tr>
							<th><label for="timezone"><?php esc_html_e( 'Timezone', 'wootickets' ); ?></label></th>
							<td>
								<select name="timezone" id="timezone" class="regular-text">
									<?php foreach ( DateTimeZone::listIdentifiers() as $tz ) : ?>
										<option value="<?php echo esc_attr( $tz ); ?>" <?php selected( $event['timezone'] ?? get_option( 'timezone_string', 'UTC' ), $tz ); ?>><?php echo esc_html( $tz ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="capacity"><?php esc_html_e( 'Venue Capacity (Attendees)', 'wootickets' ); ?></label></th>
							<td>
								<input type="number" name="capacity" id="capacity" value="<?php echo esc_attr( $event['capacity'] ?? 0 ); ?>" class="small-text" min="0">
								<p class="description"><?php esc_html_e( 'Maximum number of attendees. Set to 0 for unlimited.', 'wootickets' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="status"><?php esc_html_e( 'Status', 'wootickets' ); ?></label></th>
							<td>
								<select name="status" id="status" class="regular-text">
									<option value="draft" <?php selected( $event['status'] ?? 'draft', 'draft' ); ?>><?php esc_html_e( 'Draft', 'wootickets' ); ?></option>
									<option value="published" <?php selected( $event['status'] ?? 'draft', 'published' ); ?>><?php esc_html_e( 'Published', 'wootickets' ); ?></option>
									<option value="completed" <?php selected( $event['status'] ?? 'draft', 'completed' ); ?>><?php esc_html_e( 'Completed', 'wootickets' ); ?></option>
									<option value="cancelled" <?php selected( $event['status'] ?? 'draft', 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'wootickets' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
				</div>

				<!-- Venue Tab -->
				<div class="wet-tab-panel" id="tab-venue">
					<table class="form-table">
						<tr>
							<th><label for="venue"><?php esc_html_e( 'Venue Name', 'wootickets' ); ?></label></th>
							<td><input type="text" name="venue" id="venue" value="<?php echo esc_attr( $event['venue'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="address"><?php esc_html_e( 'Address', 'wootickets' ); ?></label></th>
							<td><textarea name="address" id="address" class="large-text" rows="3"><?php echo esc_textarea( $event['address'] ?? '' ); ?></textarea></td>
						</tr>
						<tr>
							<th><label for="city"><?php esc_html_e( 'City', 'wootickets' ); ?></label></th>
							<td><input type="text" name="city" id="city" value="<?php echo esc_attr( $event['city'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="state"><?php esc_html_e( 'State/Province', 'wootickets' ); ?></label></th>
							<td><input type="text" name="state" id="state" value="<?php echo esc_attr( $event['state'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="country"><?php esc_html_e( 'Country', 'wootickets' ); ?></label></th>
							<td><input type="text" name="country" id="country" value="<?php echo esc_attr( $event['country'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="postal_code"><?php esc_html_e( 'Postal Code', 'wootickets' ); ?></label></th>
							<td><input type="text" name="postal_code" id="postal_code" value="<?php echo esc_attr( $event['postal_code'] ?? '' ); ?>" class="small-text"></td>
						</tr>
					</table>
				</div>

				<!-- Branding Tab -->
				<div class="wet-tab-panel" id="tab-branding">
					<table class="form-table">
						<tr>
							<th><label for="primary_color"><?php esc_html_e( 'Primary Color', 'wootickets' ); ?></label></th>
							<td>
								<input type="color" name="primary_color" id="primary_color" value="<?php echo esc_attr( $event['primary_color'] ?? '#0066cc' ); ?>">
								<input type="text" name="primary_color_text" value="<?php echo esc_attr( $event['primary_color'] ?? '#0066cc' ); ?>" class="small-text" style="margin-left: 10px;">
							</td>
						</tr>
						<tr>
							<th><label for="secondary_color"><?php esc_html_e( 'Secondary Color', 'wootickets' ); ?></label></th>
							<td>
								<input type="color" name="secondary_color" id="secondary_color" value="<?php echo esc_attr( $event['secondary_color'] ?? '#004499' ); ?>">
								<input type="text" name="secondary_color_text" value="<?php echo esc_attr( $event['secondary_color'] ?? '#004499' ); ?>" class="small-text" style="margin-left: 10px;">
							</td>
						</tr>
						<tr>
							<th><label for="footer_text"><?php esc_html_e( 'Footer Text', 'wootickets' ); ?></label></th>
							<td><textarea name="footer_text" id="footer_text" class="large-text" rows="3"><?php echo esc_textarea( $event['footer_text'] ?? '' ); ?></textarea></td>
						</tr>
					</table>
				</div>

				<!-- Organizer Tab -->
				<div class="wet-tab-panel" id="tab-organizer">
					<table class="form-table">
						<tr>
							<th><label for="organizer_name"><?php esc_html_e( 'Organizer Name', 'wootickets' ); ?></label></th>
							<td><input type="text" name="organizer_name" id="organizer_name" value="<?php echo esc_attr( $event['organizer_name'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="organizer_email"><?php esc_html_e( 'Organizer Email', 'wootickets' ); ?></label></th>
							<td><input type="email" name="organizer_email" id="organizer_email" value="<?php echo esc_attr( $event['organizer_email'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="organizer_phone"><?php esc_html_e( 'Organizer Phone', 'wootickets' ); ?></label></th>
							<td><input type="tel" name="organizer_phone" id="organizer_phone" value="<?php echo esc_attr( $event['organizer_phone'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="website_url"><?php esc_html_e( 'Website URL', 'wootickets' ); ?></label></th>
							<td><input type="url" name="website_url" id="website_url" value="<?php echo esc_attr( $event['website_url'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="social_links"><?php esc_html_e( 'Social Links (JSON)', 'wootickets' ); ?></label></th>
							<td>
								<textarea name="social_links" id="social_links" class="large-text code" rows="5"><?php echo esc_textarea( $event['social_links'] ?? '{"facebook":"","twitter":"","instagram":"","linkedin":""}' ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Enter as JSON object with keys: facebook, twitter, instagram, linkedin, youtube.', 'wootickets' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<div class="wet-form-actions">
			<button type="submit" class="button button-primary button-large"><?php echo $is_edit ? esc_html__( 'Update Event', 'wootickets' ) : esc_html__( 'Create Event', 'wootickets' ); ?></button>
			<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-events' ); ?>" class="button button-large"><?php esc_html_e( 'Cancel', 'wootickets' ); ?></a>
		</div>
	</form>
</div>

<style>
.wet-form-tabs { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.wet-tab-nav { display: flex; border-bottom: 1px solid #e0e0e0; background: #fafafa; }
.wet-tab-btn { padding: 12px 20px; border: none; background: transparent; cursor: pointer; font-size: 14px; color: #646970; border-bottom: 3px solid transparent; transition: all 0.2s; }
.wet-tab-btn:hover { color: #1d2327; background: #f0f0f1; }
.wet-tab-btn.active { color: #007cba; border-bottom-color: #007cba; }
.wet-tab-panel { display: none; padding: 20px; }
.wet-tab-panel.active { display: block; }
.wet-form-actions { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0; display: flex; gap: 10px; }
</style>

<script>
jQuery(function($) {
	$('.wet-tab-btn').on('click', function() {
		var tab = $(this).data('tab');
		$('.wet-tab-btn').removeClass('active');
		$(this).addClass('active');
		$('.wet-tab-panel').removeClass('active');
		$('#tab-' + tab).addClass('active');
	});

	// Sync color picker with text input
	$('#primary_color, #secondary_color').on('input', function() {
		$('#' + this.id + '_text').val(this.value);
	});
	$('#primary_color_text, #secondary_color_text').on('input', function() {
		var colorId = this.id.replace('_text', '');
		if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
			$('#' + colorId).val(this.value);
		}
	});
});
</script>