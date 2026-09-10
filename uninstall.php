<?php
/**
 * Plugin activation, deactivation, and uninstall hooks
 */

// Activation hook
register_activation_hook( __FILE__, 'wet_activate' );

function wet_activate() {
	// Create database tables
	$installer = new Woo_Event_Tickets_Installer();
	$installer->create_tables();

	// Register capabilities
	Woo_Event_Tickets_Staff::register_capabilities();

	// Flush rewrite rules for My Account endpoint
	flush_rewrite_rules();

	// Schedule early bird check cron
	if ( ! wp_next_scheduled( 'wet_early_bird_check' ) ) {
		wp_schedule_event( time(), 'hourly', 'wet_early_bird_check' );
	}

	// Create upload directory
	$upload_dir = WET_PLUGIN_DIR . 'uploads/tickets';
	if ( ! file_exists( $upload_dir ) ) {
		wp_mkdir_p( $upload_dir );
	}

	// Set default options
	add_option( 'wet_db_version', WET_VERSION );
	add_option( 'wet_ticket_generation_statuses', [ 'processing', 'completed' ] );
	add_option( 'wet_currency', 'INR' );
	add_option( 'wet_ticket_id_format', '{EVENT}-{YEAR}-{SEQ}' );
	add_option( 'wet_timezone', get_option( 'timezone_string', 'UTC' ) );
	add_option( 'wet_email_sender_name', get_bloginfo( 'name' ) );
	add_option( 'wet_email_sender_email', get_option( 'admin_email' ) );
	add_option( 'wet_email_subject', 'Your Ticket is Confirmed – {EVENT}' );
	add_option( 'wet_pdf_attachment', 'yes' );
	add_option( 'wet_scanner_sound', 'yes' );
	add_option( 'wet_scanner_vibration', 'yes' );
	add_option( 'wet_scanner_auto_next', 'no' );
	add_option( 'wet_scanner_manual', 'yes' );
	add_option( 'wet_allow_late_checkin', 'no' );
	add_option( 'wet_rate_limit', 60 );
	add_option( 'wet_qr_expiration_days', 365 );
	add_option( 'wet_allow_qr_regeneration', 'yes' );
	add_option( 'wet_refund_cancels_tickets', 'yes' );
	add_option( 'wet_cancel_invalidates_tickets', 'yes' );
	add_option( 'wet_pdf_library', class_exists( 'TCPDF' ) ? 'tcpdf' : ( class_exists( 'Dompdf\Dompdf' ) ? 'dompdf' : 'tcpdf' ) );
}

// Deactivation hook
register_deactivation_hook( __FILE__, 'wet_deactivate' );

function wet_deactivate() {
	// Clear scheduled cron
	wp_clear_scheduled_hook( 'wet_early_bird_check' );

	// Flush rewrite rules
	flush_rewrite_rules();
}

// Uninstall hook (handled in uninstall.php)
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

/**
 * Uninstall - clean up plugin data
 * This runs when the plugin is deleted
 */

// Delete custom database tables
global $wpdb;
$tables = [
	$wpdb->prefix . 'wet_events',
	$wpdb->prefix . 'wet_ticket_types',
	$wpdb->prefix . 'wet_tickets',
	$wpdb->prefix . 'wet_qr_tokens',
	$wpdb->prefix . 'wet_checkins',
	$wpdb->prefix . 'wet_staff',
	$wpdb->prefix . 'wet_attendees',
	$wpdb->prefix . 'wet_attendee_fields',
	$wpdb->prefix . 'wet_logs',
	$wpdb->prefix . 'wet_settings',
];

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

// Delete options
$options = [
	'wet_db_version',
	'wet_ticket_generation_statuses',
	'wet_currency',
	'wet_ticket_id_format',
	'wet_timezone',
	'wet_email_sender_name',
	'wet_email_sender_email',
	'wet_email_subject',
	'wet_pdf_attachment',
	'wet_scanner_sound',
	'wet_scanner_vibration',
	'wet_scanner_auto_next',
	'wet_scanner_manual',
	'wet_allow_late_checkin',
	'wet_rate_limit',
	'wet_qr_expiration_days',
	'wet_allow_qr_regeneration',
	'wet_refund_cancels_tickets',
	'wet_cancel_invalidates_tickets',
	'wet_pdf_library',
];

foreach ( $options as $option ) {
	delete_option( $option );
}

// Delete post meta
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_wet_%'" );

// Remove custom roles
remove_role( 'wet_event_manager' );
remove_role( 'wet_scanner_staff' );

// Remove capabilities from administrator
$admin = get_role( 'administrator' );
if ( $admin ) {
	$admin->remove_cap( 'manage_woo_event_tickets' );
	$admin->remove_cap( 'view_woo_event_tickets_reports' );
	$admin->remove_cap( 'manage_woo_event_tickets_staff' );
	$admin->remove_cap( 'manage_woo_event_tickets_settings' );
}