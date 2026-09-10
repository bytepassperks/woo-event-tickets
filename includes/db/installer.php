<?php
/**
 * Database installer for Woo Event Tickets
 */
class Woo_Event_Tickets_Installer {

	/**
	 * Get database tables
	 * @return array
	 */
	public function get_tables() {
		global $wpdb;

		return [
			'events' => $wpdb->prefix . 'wet_events',
			'ticket_types' => $wpdb->prefix . 'wet_ticket_types',
			'tickets' => $wpdb->prefix . 'wet_tickets',
			'qr_tokens' => $wpdb->prefix . 'wet_qr_tokens',
			'checkins' => $wpdb->prefix . 'wet_checkins',
			'staff' => $wpdb->prefix . 'wet_staff',
			'attendees' => $wpdb->prefix . 'wet_attendees',
			'attendee_fields' => $wpdb->prefix . 'wet_attendee_fields',
			'logs' => $wpdb->prefix . 'wet_logs',
			'settings' => $wpdb->prefix . 'wet_settings',
		];
	}

	/**
	 * Create database tables
	 */
	public function create_tables() {
		global $wpdb;

		$tables = $this->get_tables();
		$charset_collate = $wpdb->get_charset_collate();

		// Events table
		$sql = "CREATE TABLE {$tables['events']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			slug varchar(255) NOT NULL,
			description longtext,
			banner_url varchar(500),
			logo_url varchar(500),
			start_datetime datetime NOT NULL,
			end_datetime datetime NOT NULL,
			venue varchar(255),
			address text,
			city varchar(100),
			state varchar(100),
			country varchar(100),
			postal_code varchar(20),
			capacity int(11) DEFAULT 0,
			status enum('draft','published','completed','cancelled') NOT NULL DEFAULT 'draft',
			timezone varchar(50) DEFAULT 'UTC',
			primary_color varchar(7) DEFAULT '#0066cc',
			secondary_color varchar(7) DEFAULT '#004499',
			footer_text text,
			organizer_name varchar(255),
			organizer_email varchar(255),
			organizer_phone varchar(50),
			website_url varchar(500),
			social_links longtext,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug),
			KEY start_datetime (start_datetime),
			KEY status (status)
		) $charset_collate;";

		$wpdb->query( $sql );

		// Ticket types table
		$sql = "CREATE TABLE {$tables['ticket_types']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL,
			slug varchar(255) NOT NULL,
			description longtext,
			image_url varchar(500),
			regular_price decimal(10,2) NOT NULL DEFAULT 0.00,
			sale_price decimal(10,2) DEFAULT NULL,
			early_bird_price decimal(10,2) DEFAULT NULL,
			early_bird_quantity int(11) DEFAULT 0,
			early_bird_start datetime DEFAULT NULL,
			early_bird_end datetime DEFAULT NULL,
			total_quantity int(11) NOT NULL DEFAULT 0,
			remaining_quantity int(11) NOT NULL DEFAULT 0,
			max_per_customer int(11) DEFAULT 10,
			min_per_order int(11) DEFAULT 1,
			validity_days int(11) DEFAULT 0,
			event_date date DEFAULT NULL,
			event_time time DEFAULT NULL,
			venue varchar(255) DEFAULT NULL,
			address text DEFAULT NULL,
			allowed_people int(11) DEFAULT 1,
			features longtext,
			terms_conditions longtext,
			refund_policy longtext,
			category varchar(100) DEFAULT 'general',
			color varchar(7) DEFAULT '#0066cc',
			custom_fields longtext,
			woocommerce_product_id bigint(20) unsigned DEFAULT NULL,
			status enum('active','inactive','sold_out') NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY event_slug (event_id, slug),
			KEY event_id (event_id),
			KEY status (status),
			KEY woocommerce_product_id (woocommerce_product_id)
		) $charset_collate;";

		$wpdb->query( $sql );

		// Tickets table
		$sql = "CREATE TABLE {$tables['tickets']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id bigint(20) unsigned NOT NULL,
			ticket_type_id bigint(20) unsigned NOT NULL,
			order_id bigint(20) unsigned NOT NULL,
			order_item_id bigint(20) unsigned DEFAULT NULL,
			ticket_id varchar(64) NOT NULL,
			customer_id bigint(20) unsigned DEFAULT NULL,
			customer_email varchar(255),
			customer_name varchar(255),
			customer_phone varchar(50),
			quantity int(11) NOT NULL DEFAULT 1,
			price_paid decimal(10,2) NOT NULL DEFAULT 0.00,
			currency varchar(3) DEFAULT 'USD',
			status enum('pending','active','used','cancelled','refunded','expired','blocked') NOT NULL DEFAULT 'pending',
			purchase_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			check_in_date datetime DEFAULT NULL,
			checked_in_by bigint(20) unsigned DEFAULT NULL,
			email_sent tinyint(1) DEFAULT 0,
			email_status enum('sent','failed','pending') DEFAULT 'pending',
			pdf_generated tinyint(1) DEFAULT 0,
			transfer_count int(11) DEFAULT 0,
			last_transfer_date datetime DEFAULT NULL,
			notes longtext,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY ticket_id (ticket_id),
			KEY event_id (event_id),
			KEY ticket_type_id (ticket_type_id),
			KEY order_id (order_id),
			KEY customer_email (customer_email),
			KEY status (status),
			KEY purchase_date (purchase_date)
		) $charset_collate;";

		$wpdb->query( $sql );

		// QR tokens table
		$sql = "CREATE TABLE {$tables['qr_tokens']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			ticket_id bigint(20) unsigned NOT NULL,
			secure_token varchar(128) NOT NULL,
			verification_url varchar(500) NOT NULL,
			qr_image_url varchar(500) DEFAULT NULL,
			status enum('active','used','cancelled','refunded','expired','blocked') NOT NULL DEFAULT 'active',
			generated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			used_at datetime DEFAULT NULL,
			regenerated_count int(11) DEFAULT 0,
			last_regenerated_at datetime DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY secure_token (secure_token),
			KEY ticket_id (ticket_id),
			KEY status (status),
			KEY generated_at (generated_at)
		) $charset_collate;";

		$wpdb->query( $sql );

		// Check-ins table
		$sql = "CREATE TABLE {$tables['checkins']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			ticket_id bigint(20) unsigned NOT NULL,
			qr_token_id bigint(20) unsigned NOT NULL,
			event_id bigint(20) unsigned NOT NULL,
			result enum('valid','already_used','invalid','cancelled','refunded','expired','blocked') NOT NULL,
			staff_id bigint(20) unsigned DEFAULT NULL,
			staff_name varchar(255) DEFAULT NULL,
			device_info text,
			ip_address varchar(45),
			scanned_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			notes longtext,
			PRIMARY KEY (id),
			KEY ticket_id (ticket_id),
			KEY qr_token_id (qr_token_id),
			KEY event_id (event_id),
			KEY staff_id (staff_id),
			KEY result (result),
			KEY scanned_at (scanned_at)
		) $charset_collate;";

		$wpdb->query( $sql );

		// Staff table
		$sql = "CREATE TABLE {$tables['staff']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id bigint(20) unsigned DEFAULT NULL,
			user_id bigint(20) unsigned NOT NULL,
			role enum('scanner','manager','admin') NOT NULL DEFAULT 'scanner',
			permissions longtext,
			active tinyint(1) DEFAULT 1,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY event_user (event_id, user_id),
			KEY user_id (user_id),
			KEY event_id (event_id),
			KEY role (role)
		) $charset_collate;";

		$wpdb->query( $sql );

		// Attendees table
		$sql = "CREATE TABLE {$tables['attendees']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			ticket_id bigint(20) unsigned NOT NULL,
			ticket_type_id bigint(20) unsigned NOT NULL,
			event_id bigint(20) unsigned NOT NULL,
			full_name varchar(255),
			email varchar(255),
			phone varchar(50),
			custom_data longtext,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY ticket_id (ticket_id),
			KEY ticket_type_id (ticket_type_id),
			KEY event_id (event_id),
			KEY email (email)
		) $charset_collate;";

		$wpdb->query( $sql );

		// Attendee fields table
		$sql = "CREATE TABLE {$tables['attendee_fields']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id bigint(20) unsigned NOT NULL,
			field_key varchar(100) NOT NULL,
			field_label varchar(255) NOT NULL,
			field_type enum('text','email','phone','select','radio','checkbox','textarea','number','date') NOT NULL DEFAULT 'text',
			field_options longtext,
			is_required tinyint(1) DEFAULT 0,
			is_visible_to_staff tinyint(1) DEFAULT 1,
			display_order int(11) DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY event_id (event_id),
			KEY field_key (field_key)
		) $charset_collate;";

		$wpdb->query( $sql );

		// Logs table
		$sql = "CREATE TABLE {$tables['logs']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id bigint(20) unsigned DEFAULT NULL,
			ticket_id bigint(20) unsigned DEFAULT NULL,
			action varchar(100) NOT NULL,
			description longtext,
			user_id bigint(20) unsigned DEFAULT NULL,
			ip_address varchar(45),
			user_agent text,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY event_id (event_id),
			KEY ticket_id (ticket_id),
			KEY action (action),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) $charset_collate;";

		$wpdb->query( $sql );

		// Settings table
		$sql = "CREATE TABLE {$tables['settings']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			setting_key varchar(100) NOT NULL,
			setting_value longtext,
			setting_group varchar(50) NOT NULL DEFAULT 'general',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY setting_key (setting_key),
			KEY setting_group (setting_group)
		) $charset_collate;";

		$wpdb->query( $sql );

		// Update version option
		update_option( 'wet_db_version', WET_VERSION );
	}
}

/**
 * Include this file
 */
require_once WET_PLUGIN_DIR . 'includes/db/installer.php';