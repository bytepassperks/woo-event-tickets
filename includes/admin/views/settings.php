<?php
/**
 * Settings view
 */
$order_statuses = [ 'pending' => __( 'Pending', 'wootickets' ), 'processing' => __( 'Processing', 'wootickets' ), 'completed' => __( 'Completed', 'wootickets' ), 'on-hold' => __( 'On Hold', 'wootickets' ), 'cancelled' => __( 'Cancelled', 'wootickets' ), 'refunded' => __( 'Refunded', 'wootickets' ), 'failed' => __( 'Failed', 'wootickets' ) ];
$selected_statuses = get_option( 'wet_ticket_generation_statuses', [ 'processing', 'completed' ] );
?>
<div class="wrap wet-settings-page">
	<h1><?php esc_html_e( 'Event Tickets Settings', 'wootickets' ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( 'wet_settings_group' ); ?>
		<?php do_settings_sections( 'wet_settings' ); ?>

		<!-- General Settings -->
		<h2><?php esc_html_e( 'General', 'wootickets' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="wet_currency"><?php esc_html_e( 'Currency', 'wootickets' ); ?></label></th>
				<td>
					<input type="text" name="wet_currency" id="wet_currency" value="<?php echo esc_attr( get_option( 'wet_currency', 'INR' ) ); ?>" class="small-text">
					<p class="description"><?php esc_html_e( 'ISO 4217 currency code (e.g., USD, EUR, INR).', 'wootickets' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="wet_ticket_id_format"><?php esc_html_e( 'Ticket ID Format', 'wootickets' ); ?></label></th>
				<td>
					<input type="text" name="wet_ticket_id_format" id="wet_ticket_id_format" value="<?php echo esc_attr( get_option( 'wet_ticket_id_format', '{EVENT}-{YEAR}-{SEQ}' ) ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Available placeholders: {EVENT}, {YEAR}, {SEQ}, {RANDOM}. Example: GRB-2026-000123', 'wootickets' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="wet_timezone"><?php esc_html_e( 'Timezone', 'wootickets' ); ?></label></th>
				<td>
					<select name="wet_timezone" id="wet_timezone" class="regular-text">
						<?php foreach ( DateTimeZone::listIdentifiers() as $tz ) : ?>
							<option value="<?php echo esc_attr( $tz ); ?>" <?php selected( get_option( 'wet_timezone', get_option( 'timezone_string', 'UTC' ) ), $tz ); ?>><?php echo esc_html( $tz ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>

		<!-- WooCommerce Settings -->
		<h2><?php esc_html_e( 'WooCommerce Integration', 'wootickets' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Ticket Generation Order Statuses', 'wootickets' ); ?></th>
				<td>
					<fieldset>
						<?php foreach ( $order_statuses as $key => $label ) : ?>
							<label style="display: block; margin: 5px 0;">
								<input type="checkbox" name="wet_ticket_generation_statuses[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $selected_statuses ), true ); ?>> <?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
						<p class="description"><?php esc_html_e( 'Tickets will be generated when order reaches any of these statuses.', 'wootickets' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Refund Behavior', 'wootickets' ); ?></th>
				<td>
					<label><input type="checkbox" name="wet_refund_cancels_tickets" value="yes" <?php checked( get_option( 'wet_refund_cancels_tickets', 'yes' ), 'yes' ); ?>> <?php esc_html_e( 'Automatically cancel tickets when order is refunded', 'wootickets' ); ?></label>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Cancellation Behavior', 'wootickets' ); ?></th>
				<td>
					<label><input type="checkbox" name="wet_cancel_invalidates_tickets" value="yes" <?php checked( get_option( 'wet_cancel_invalidates_tickets', 'yes' ), 'yes' ); ?>> <?php esc_html_e( 'Invalidate tickets when order is cancelled', 'wootickets' ); ?></label>
				</td>
			</tr>
		</table>

		<!-- QR Code Settings -->
		<h2><?php esc_html_e( 'QR Code', 'wootickets' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'QR Token Expiration', 'wootickets' ); ?></th>
				<td>
					<input type="number" name="wet_qr_expiration_days" value="<?php echo esc_attr( get_option( 'wet_qr_expiration_days', 365 ) ); ?>" class="small-text" min="1">
					<p class="description"><?php esc_html_e( 'Days after which QR codes expire (0 for never).', 'wootickets' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Allow QR Regeneration', 'wootickets' ); ?></th>
				<td>
					<label><input type="checkbox" name="wet_allow_qr_regeneration" value="yes" <?php checked( get_option( 'wet_allow_qr_regeneration', 'yes' ), 'yes' ); ?>> <?php esc_html_e( 'Allow admins to regenerate QR codes (invalidates previous)', 'wootickets' ); ?></label>
				</td>
			</tr>
		</table>

		<!-- Email Settings -->
		<h2><?php esc_html_e( 'Email', 'wootickets' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="wet_email_sender_name"><?php esc_html_e( 'Sender Name', 'wootickets' ); ?></label></th>
				<td><input type="text" name="wet_email_sender_name" id="wet_email_sender_name" value="<?php echo esc_attr( get_option( 'wet_email_sender_name', get_bloginfo( 'name' ) ) ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="wet_email_sender_email"><?php esc_html_e( 'Sender Email', 'wootickets' ); ?></label></th>
				<td><input type="email" name="wet_email_sender_email" id="wet_email_sender_email" value="<?php echo esc_attr( get_option( 'wet_email_sender_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="wet_email_subject"><?php esc_html_e( 'Default Subject', 'wootickets' ); ?></label></th>
				<td><input type="text" name="wet_email_subject" id="wet_email_subject" value="<?php echo esc_attr( get_option( 'wet_email_subject', 'Your Ticket is Confirmed – {EVENT}' ) ); ?>" class="large-text"><p class="description"><?php esc_html_e( 'Available placeholders: {EVENT}, {CUSTOMER}, {TICKET_ID}, {TICKET_TYPE}', 'wootickets' ); ?></p></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'PDF Attachment', 'wootickets' ); ?></th>
				<td>
					<label><input type="checkbox" name="wet_pdf_attachment" value="yes" <?php checked( get_option( 'wet_pdf_attachment', 'yes' ), 'yes' ); ?>> <?php esc_html_e( 'Attach PDF ticket to confirmation email', 'wootickets' ); ?></label>
				</td>
			</tr>
		</table>

		<!-- Scanner Settings -->
		<h2><?php esc_html_e( 'Scanner', 'wootickets' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Sound Effects', 'wootickets' ); ?></th>
				<td><label><input type="checkbox" name="wet_scanner_sound" value="yes" <?php checked( get_option( 'wet_scanner_sound', 'yes' ), 'yes' ); ?>> <?php esc_html_e( 'Play sound on scan result', 'wootickets' ); ?></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Vibration', 'wootickets' ); ?></th>
				<td><label><input type="checkbox" name="wet_scanner_vibration" value="yes" <?php checked( get_option( 'wet_scanner_vibration', 'yes' ), 'yes' ); ?>> <?php esc_html_e( 'Vibrate on scan (mobile devices)', 'wootickets' ); ?></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Auto Next Scan', 'wootickets' ); ?></th>
				<td><label><input type="checkbox" name="wet_scanner_auto_next" value="yes" <?php checked( get_option( 'wet_scanner_auto_next', 'no' ), 'yes' ); ?>> <?php esc_html_e( 'Automatically start next scan after result', 'wootickets' ); ?></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Manual Verification', 'wootickets' ); ?></th>
				<td><label><input type="checkbox" name="wet_scanner_manual" value="yes" <?php checked( get_option( 'wet_scanner_manual', 'yes' ), 'yes' ); ?>> <?php esc_html_e( 'Allow manual ticket ID/token entry', 'wootickets' ); ?></label></td>
			</tr>
		</table>

		<!-- Security Settings -->
		<h2><?php esc_html_e( 'Security', 'wootickets' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Allow Late Check-in', 'wootickets' ); ?></th>
				<td><label><input type="checkbox" name="wet_allow_late_checkin" value="yes" <?php checked( get_option( 'wet_allow_late_checkin', 'no' ), 'yes' ); ?>> <?php esc_html_e( 'Allow check-in after event end time', 'wootickets' ); ?></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Rate Limit (requests/minute)', 'wootickets' ); ?></th>
				<td><input type="number" name="wet_rate_limit" value="<?php echo esc_attr( get_option( 'wet_rate_limit', 60 ) ); ?>" class="small-text" min="1"><p class="description"><?php esc_html_e( 'Maximum verification requests per minute per IP.', 'wootickets' ); ?></p></td>
			</tr>
		</table>

		<!-- PDF Settings -->
		<h2><?php esc_html_e( 'PDF Ticket', 'wootickets' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'PDF Library', 'wootickets' ); ?></th>
				<td>
					<?php 
					$has_tcpdf = class_exists( 'TCPDF' );
					$has_dompdf = class_exists( 'Dompdf\Dompdf' );
					?>
					<label><input type="radio" name="wet_pdf_library" value="tcpdf" <?php checked( get_option( 'wet_pdf_library', $has_tcpdf ? 'tcpdf' : ( $has_dompdf ? 'dompdf' : 'tcpdf' ) ), 'tcpdf' ); ?> <?php echo $has_tcpdf ? '' : '(not installed) '; ?>>TCPDF</label><br>
					<label><input type="radio" name="wet_pdf_library" value="dompdf" <?php checked( get_option( 'wet_pdf_library', $has_tcpdf ? 'tcpdf' : ( $has_dompdf ? 'dompdf' : 'tcpdf' ) ), 'dompdf' ); ?> <?php echo $has_dompdf ? '' : '(not installed) '; ?>>Dompdf</label>
					<p class="description"><?php esc_html_e( 'Install TCPDF or Dompdf via Composer for PDF generation.', 'wootickets' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Save Settings', 'wootickets' ), 'primary', 'wet_save_settings' ); ?>
	</form>
</div>