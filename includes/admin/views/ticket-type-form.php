<?php
/**
 * Ticket type form view
 */
$is_edit = ! empty( $type );
$event = Woo_Event_Tickets_Events::get( $event_id );
$events = Woo_Event_Tickets_Events::get_dropdown_options();
$product = $type['woocommerce_product_id'] ?? 0 ? wc_get_product( $type['woocommerce_product_id'] ) : null;
?>
<div class="wrap wet-ticket-type-form">
	<div class="wet-page-header">
		<h1><?php echo $is_edit ? esc_html__( 'Edit Ticket Type', 'wootickets' ) : esc_html__( 'Add New Ticket Type', 'wootickets' ); ?></h1>
		<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types' . ( $event_id ? '&event_id=' . $event_id : '' ) ); ?>" class="button"><?php esc_html_e( 'Back to Ticket Types', 'wootickets' ); ?></a>
	</div>

	<form method="post" id="wet-ticket-type-form">
		<?php wp_nonce_field( 'wet_ticket_type_nonce' ); ?>
		<input type="hidden" name="wet_save_ticket_type" value="1">
		<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">

		<div class="wet-form-tabs">
			<nav class="wet-tab-nav">
				<button type="button" class="wet-tab-btn active" data-tab="general"><?php esc_html_e( 'General', 'wootickets' ); ?></button>
				<button type="button" class="wet-tab-btn" data-tab="pricing"><?php esc_html_e( 'Pricing', 'wootickets' ); ?></button>
				<button type="button" class="wet-tab-btn" data-tab="inventory"><?php esc_html_e( 'Inventory', 'wootickets' ); ?></button>
				<button type="button" class="wet-tab-btn" data-tab="early-bird"><?php esc_html_e( 'Early Bird', 'wootickets' ); ?></button>
				<button type="button" class="wet-tab-btn" data-tab="details"><?php esc_html_e( 'Details', 'wootickets' ); ?></button>
				<button type="button" class="wet-tab-btn" data-tab="woocommerce"><?php esc_html_e( 'WooCommerce', 'wootickets' ); ?></button>
			</nav>

			<div class="wet-tab-panels">
				<!-- General Tab -->
				<div class="wet-tab-panel active" id="tab-general">
					<table class="form-table">
						<tr>
							<th><label for="event_id"><?php esc_html_e( 'Event', 'wootickets' ); ?> <span class="required">*</span></label></th>
							<td>
								<select name="event_id" id="event_id" class="regular-text" <?php echo $is_edit ? 'disabled' : ''; ?> required>
									<?php foreach ( $events as $id => $name ) : ?>
										<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $event_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="name"><?php esc_html_e( 'Ticket Name', 'wootickets' ); ?> <span class="required">*</span></label></th>
							<td><input type="text" name="name" id="name" value="<?php echo esc_attr( $type['name'] ?? '' ); ?>" class="regular-text" required></td>
						</tr>
						<tr>
							<th><label for="slug"><?php esc_html_e( 'Slug', 'wootickets' ); ?></label></th>
							<td><input type="text" name="slug" id="slug" value="<?php echo esc_attr( $type['slug'] ?? '' ); ?>" class="regular-text"><p class="description"><?php esc_html_e( 'URL-friendly name. Auto-generated if left empty.', 'wootickets' ); ?></p></td>
						</tr>
						<tr>
							<th><label for="description"><?php esc_html_e( 'Description', 'wootickets' ); ?></label></th>
							<td><?php wp_editor( $type['description'] ?? '', 'description', [ 'textarea_name' => 'description', 'media_buttons' => true ] ); ?></td>
						</tr>
						<tr>
							<th><label for="image_url"><?php esc_html_e( 'Image URL', 'wootickets' ); ?></label></th>
							<td><input type="url" name="image_url" id="image_url" value="<?php echo esc_attr( $type['image_url'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="category"><?php esc_html_e( 'Category', 'wootickets' ); ?></label></th>
							<td><input type="text" name="category" id="category" value="<?php echo esc_attr( $type['category'] ?? 'general' ); ?>" class="regular-text"><p class="description"><?php esc_html_e( 'e.g., General, VIP, Student, Group', 'wootickets' ); ?></p></td>
						</tr>
						<tr>
							<th><label for="color"><?php esc_html_e( 'Color', 'wootickets' ); ?></label></th>
							<td>
								<input type="color" name="color" id="color" value="<?php echo esc_attr( $type['color'] ?? '#0066cc' ); ?>">
								<input type="text" name="color_text" value="<?php echo esc_attr( $type['color'] ?? '#0066cc' ); ?>" class="small-text" style="margin-left: 10px;">
							</td>
						</tr>
					</table>
				</div>

				<!-- Pricing Tab -->
				<div class="wet-tab-panel" id="tab-pricing">
					<table class="form-table">
						<tr>
							<th><label for="regular_price"><?php esc_html_e( 'Regular Price', 'wootickets' ); ?> <span class="required">*</span></label></th>
							<td><input type="number" step="0.01" name="regular_price" id="regular_price" value="<?php echo esc_attr( $type['regular_price'] ?? 0 ); ?>" class="small-text" required min="0"></td>
						</tr>
						<tr>
							<th><label for="sale_price"><?php esc_html_e( 'Sale Price', 'wootickets' ); ?></label></th>
							<td><input type="number" step="0.01" name="sale_price" id="sale_price" value="<?php echo esc_attr( $type['sale_price'] ?? '' ); ?>" class="small-text" min="0"><p class="description"><?php esc_html_e( 'Optional. Must be less than regular price.', 'wootickets' ); ?></p></td>
						</tr>
					</table>
				</div>

				<!-- Inventory Tab -->
				<div class="wet-tab-panel" id="tab-inventory">
					<table class="form-table">
						<tr>
							<th><label for="total_quantity"><?php esc_html_e( 'Total Quantity', 'wootickets' ); ?> <span class="required">*</span></label></th>
							<td><input type="number" name="total_quantity" id="total_quantity" value="<?php echo esc_attr( $type['total_quantity'] ?? 0 ); ?>" class="small-text" required min="0"></td>
						</tr>
						<tr>
							<th><label for="max_per_customer"><?php esc_html_e( 'Max Tickets Per Customer', 'wootickets' ); ?></label></th>
							<td><input type="number" name="max_per_customer" id="max_per_customer" value="<?php echo esc_attr( $type['max_per_customer'] ?? 10 ); ?>" class="small-text" min="1"></td>
						</tr>
						<tr>
							<th><label for="min_per_order"><?php esc_html_e( 'Min Tickets Per Order', 'wootickets' ); ?></label></th>
							<td><input type="number" name="min_per_order" id="min_per_order" value="<?php echo esc_attr( $type['min_per_order'] ?? 1 ); ?>" class="small-text" min="1"></td>
						</tr>
						<tr>
							<th><label for="allowed_people"><?php esc_html_e( 'People Allowed Per Ticket', 'wootickets' ); ?></label></th>
							<td><input type="number" name="allowed_people" id="allowed_people" value="<?php echo esc_attr( $type['allowed_people'] ?? 1 ); ?>" class="small-text" min="1"><p class="description"><?php esc_html_e( 'For group/family tickets. Each ticket allows this many people.', 'wootickets' ); ?></p></td>
						</tr>
						<tr>
							<th><label for="status"><?php esc_html_e( 'Status', 'wootickets' ); ?></label></th>
							<td>
								<select name="status" id="status" class="regular-text">
									<option value="active" <?php selected( $type['status'] ?? 'active', 'active' ); ?>><?php esc_html_e( 'Active', 'wootickets' ); ?></option>
									<option value="inactive" <?php selected( $type['status'] ?? 'active', 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'wootickets' ); ?></option>
									<option value="sold_out" <?php selected( $type['status'] ?? 'active', 'sold_out' ); ?>><?php esc_html_e( 'Sold Out', 'wootickets' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
				</div>

				<!-- Early Bird Tab -->
				<div class="wet-tab-panel" id="tab-early-bird">
					<table class="form-table">
						<tr>
							<th><label for="early_bird_price"><?php esc_html_e( 'Early Bird Price', 'wootickets' ); ?></label></th>
							<td><input type="number" step="0.01" name="early_bird_price" id="early_bird_price" value="<?php echo esc_attr( $type['early_bird_price'] ?? '' ); ?>" class="small-text" min="0"><p class="description"><?php esc_html_e( 'Price during early bird period. Must be less than regular price.', 'wootickets' ); ?></p></td>
						</tr>
						<tr>
							<th><label for="early_bird_quantity"><?php esc_html_e( 'Early Bird Quantity', 'wootickets' ); ?></label></th>
							<td><input type="number" name="early_bird_quantity" id="early_bird_quantity" value="<?php echo esc_attr( $type['early_bird_quantity'] ?? 0 ); ?>" class="small-text" min="0"><p class="description"><?php esc_html_e( 'Number of tickets available at early bird price.', 'wootickets' ); ?></p></td>
						</tr>
						<tr>
							<th><label for="early_bird_start"><?php esc_html_e( 'Early Bird Start', 'wootickets' ); ?></label></th>
							<td><input type="datetime-local" name="early_bird_start" id="early_bird_start" value="<?php echo $type['early_bird_start'] ? date( 'Y-m-d\TH:i', strtotime( $type['early_bird_start'] ) ) : ''; ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="early_bird_end"><?php esc_html_e( 'Early Bird End', 'wootickets' ); ?></label></th>
							<td><input type="datetime-local" name="early_bird_end" id="early_bird_end" value="<?php echo $type['early_bird_end'] ? date( 'Y-m-d\TH:i', strtotime( $type['early_bird_end'] ) ) : ''; ?>" class="regular-text"></td>
						</tr>
					</table>
				</div>

				<!-- Details Tab -->
				<div class="wet-tab-panel" id="tab-details">
					<table class="form-table">
						<tr>
							<th><label for="event_date"><?php esc_html_e( 'Event Date', 'wootickets' ); ?></label></th>
							<td><input type="date" name="event_date" id="event_date" value="<?php echo esc_attr( $type['event_date'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="event_time"><?php esc_html_e( 'Event Time', 'wootickets' ); ?></label></th>
							<td><input type="time" name="event_time" id="event_time" value="<?php echo esc_attr( $type['event_time'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="venue"><?php esc_html_e( 'Venue', 'wootickets' ); ?></label></th>
							<td><input type="text" name="venue" id="venue" value="<?php echo esc_attr( $type['venue'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="address"><?php esc_html_e( 'Address', 'wootickets' ); ?></label></th>
							<td><textarea name="address" id="address" class="large-text" rows="3"><?php echo esc_textarea( $type['address'] ?? '' ); ?></textarea></td>
						</tr>
						<tr>
							<th><label for="features"><?php esc_html_e( 'Ticket Features', 'wootickets' ); ?></label></th>
							<td><?php wp_editor( $type['features'] ?? '', 'features', [ 'textarea_name' => 'features', 'media_buttons' => false ] ); ?><p class="description"><?php esc_html_e( 'List features one per line or as bullet points.', 'wootickets' ); ?></p></td>
						</tr>
						<tr>
							<th><label for="terms_conditions"><?php esc_html_e( 'Terms & Conditions', 'wootickets' ); ?></label></th>
							<td><?php wp_editor( $type['terms_conditions'] ?? '', 'terms_conditions', [ 'textarea_name' => 'terms_conditions', 'media_buttons' => false ] ); ?></td>
						</tr>
						<tr>
							<th><label for="refund_policy"><?php esc_html_e( 'Refund Policy', 'wootickets' ); ?></label></th>
							<td><?php wp_editor( $type['refund_policy'] ?? '', 'refund_policy', [ 'textarea_name' => 'refund_policy', 'media_buttons' => false ] ); ?></td>
						</tr>
						<tr>
							<th><label for="custom_fields"><?php esc_html_e( 'Custom Fields (JSON)', 'wootickets' ); ?></label></th>
							<td><textarea name="custom_fields" id="custom_fields" class="large-text code" rows="5"><?php echo esc_textarea( $type['custom_fields'] ?? '[]' ); ?></textarea><p class="description"><?php esc_html_e( 'Custom attendee fields as JSON array. Example: [{"key":"age","label":"Age","type":"number","required":true}]', 'wootickets' ); ?></p></td>
						</tr>
					</table>
				</div>

				<!-- WooCommerce Tab -->
				<div class="wet-tab-panel" id="tab-woocommerce">
					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'WooCommerce Product', 'wootickets' ); ?></th>
							<td>
								<?php if ( $product ) : ?>
									<div class="wet-product-info">
										<p><strong><?php echo esc_html( $product->get_name() ); ?></strong> (ID: <?php echo $product->get_id(); ?>)</p>
										<p><a href="<?php echo get_edit_post_link( $product->get_id() ); ?>" target="_blank" class="button button-secondary"><?php esc_html_e( 'Edit Product', 'wootickets' ); ?></a></p>
										<p><a href="<?php echo get_permalink( $product->get_id() ); ?>" target="_blank"><?php esc_html_e( 'View Product', 'wootickets' ); ?></a></p>
									</div>
								<?php else : ?>
									<p><?php esc_html_e( 'No product linked. One will be created when you save.', 'wootickets' ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Sync Status', 'wootickets' ); ?></th>
							<td>
								<button type="button" class="button wet-sync-product" data-type-id="<?php echo esc_attr( $type['id'] ?? 0 ); ?>" <?php echo !$is_edit ? 'disabled' : ''; ?>><?php esc_html_e( 'Sync with WooCommerce', 'wootickets' ); ?></button>
								<span class="wet-sync-result"></span>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<div class="wet-form-actions">
			<button type="submit" class="button button-primary button-large"><?php echo $is_edit ? esc_html__( 'Update Ticket Type', 'wootickets' ) : esc_html__( 'Create Ticket Type', 'wootickets' ); ?></button>
			<a href="<?php echo admin_url( 'admin.php?page=woo-event-tickets-ticket-types' . ( $event_id ? '&event_id=' . $event_id : '' ) ); ?>" class="button button-large"><?php esc_html_e( 'Cancel', 'wootickets' ); ?></a>
		</div>
	</form>
</div>

<style>
.wet-product-info { padding: 15px; background: #f6f7f7; border-radius: 8px; }
.wet-sync-result { margin-left: 10px; }
.wet-sync-result.success { color: #46b450; }
.wet-sync-result.error { color: #dc3232; }
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

	$('#color').on('input', function() {
		$('#color_text').val(this.value);
	});
	$('#color_text').on('input', function() {
		if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
			$('#color').val(this.value);
		}
	});

	$('.wet-sync-product').on('click', function() {
		var btn = $(this);
		var typeId = btn.data('type-id');
		var result = btn.next('.wet-sync-result');

		btn.prop('disabled', true).text('<?php esc_html_e( 'Syncing...', 'wootickets' ); ?>');
		result.removeClass('success error').text('');

		$.post(ajaxurl, {
			action: 'wet_sync_ticket_type',
			type_id: typeId,
			nonce: '<?php echo wp_create_nonce( 'wet_ajax_nonce' ); ?>'
		}, function(response) {
			if (response.success) {
				result.addClass('success').text('<?php esc_html_e( 'Synced successfully!', 'wootickets' ); ?>');
				setTimeout(function() { location.reload(); }, 1500);
			} else {
				result.addClass('error').text(response.data.message || '<?php esc_html_e( 'Sync failed', 'wootickets' ); ?>');
			}
			btn.prop('disabled', false).text('<?php esc_html_e( 'Sync with WooCommerce', 'wootickets' ); ?>');
		});
	});
});
</script>