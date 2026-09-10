<?php
/**
 * Admin pages
 */
class Woo_Event_Tickets_Admin {

	/**
	 * Admin page: Dashboard
	 */
	public static function admin_page_dashboard() {
		$events = Woo_Event_Tickets_Events::get_all( [ 'status' => 'published', 'per_page' => 5 ] );
		$total_stats = self::get_total_stats();

		include WET_PLUGIN_DIR . 'includes/admin/views/dashboard.php';
	}

	/**
	 * Admin page: Events
	 */
	public static function admin_page_events() {
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
		$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;

		switch ( $action ) {
			case 'edit':
			case 'add':
				self::render_event_form( $event_id );
				break;
			case 'delete':
				self::delete_event( $event_id );
				break;
			default:
				self::render_events_list();
		}
	}

	/**
	 * Admin page: Ticket Types
	 */
	public static function admin_page_ticket_types() {
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
		$type_id = isset( $_GET['type_id'] ) ? intval( $_GET['type_id'] ) : 0;
		$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;

		switch ( $action ) {
			case 'edit':
			case 'add':
				self::render_ticket_type_form( $type_id, $event_id );
				break;
			case 'delete':
				self::delete_ticket_type( $type_id );
				break;
			default:
				self::render_ticket_types_list( $event_id );
		}
	}

	/**
	 * Admin page: Tickets
	 */
	public static function admin_page_tickets() {
		$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
		$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		$page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

		$args = [
			'event_id' => $event_id,
			'status' => $status,
			'page' => $page,
			'per_page' => 25,
			'search' => $search,
		];

		$tickets = Woo_Event_Tickets_Tickets::get_by_event( $event_id, $args );
		$events = Woo_Event_Tickets_Events::get_dropdown_options();

		include WET_PLUGIN_DIR . 'includes/admin/views/tickets.php';
	}

	/**
	 * Admin page: Scanner
	 */
	public static function admin_page_scanner() {
		if ( ! current_user_can( 'manage_woo_event_tickets' ) ) {
			wp_die( __( 'Permission denied', 'wootickets' ) );
		}

		$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
		$events = Woo_Event_Tickets_Events::get_all( [ 'status' => 'published' ] );

		// Handle AJAX check-in
		if ( isset( $_POST['wet_check_in'] ) ) {
			check_admin_referer( 'wet_check_in_nonce' );
			
			$secure_token = sanitize_text_field( $_POST['secure_token'] );
			$ticket_id = sanitize_text_field( $_POST['ticket_id'] );

			$result = Woo_Event_Tickets_Checkin::process_check_in( $secure_token, $ticket_id );
			wp_send_json( $result );
		}

		echo Woo_Event_Tickets_Scanner::get_scanner_page( $event_id );
	}

	/**
	 * Admin page: Check-ins
	 */
	public static function admin_page_checkins() {
		$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
		$result = isset( $_GET['result'] ) ? sanitize_text_field( $_GET['result'] ) : '';
		$page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;

		$checkins = Woo_Event_Tickets_Checkin::get_recent( $event_id, 50 );
		$stats = $event_id ? Woo_Event_Tickets_Checkin::get_stats( $event_id ) : [];
		$events = Woo_Event_Tickets_Events::get_dropdown_options();

		include WET_PLUGIN_DIR . 'includes/admin/views/checkins.php';
	}

	/**
	 * Admin page: Staff
	 */
	public static function admin_page_staff() {
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
		$staff_id = isset( $_GET['staff_id'] ) ? intval( $_GET['staff_id'] ) : 0;
		$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;

		switch ( $action ) {
			case 'add':
				self::render_staff_form( 0, $event_id );
				break;
			case 'edit':
				self::render_staff_form( $staff_id );
				break;
			case 'delete':
				self::delete_staff( $staff_id );
				break;
			default:
				self::render_staff_list( $event_id );
		}
	}

	/**
	 * Admin page: Reports
	 */
	public static function admin_page_reports() {
		$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : date( 'Y-m-01' );
		$date_to = isset( $_GET['date_to'] ) ? sanitize_text_field( $_GET['date_to'] ) : date( 'Y-m-d' );
		$report_type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : 'sales';

		$events = Woo_Event_Tickets_Events::get_dropdown_options();

		switch ( $report_type ) {
			case 'checkins':
				$report_data = self::get_checkin_report( $event_id, $date_from, $date_to );
				break;
			case 'customers':
				$report_data = self::get_customer_report( $event_id, $date_from, $date_to );
				break;
			case 'revenue':
				$report_data = self::get_revenue_report( $event_id, $date_from, $date_to );
				break;
			default:
				$report_data = self::get_sales_report( $event_id, $date_from, $date_to );
		}

		include WET_PLUGIN_DIR . 'includes/admin/views/reports.php';
	}

	/**
	 * Admin page: Settings
	 */
	public static function admin_page_settings() {
		if ( isset( $_POST['wet_save_settings'] ) ) {
			check_admin_referer( 'wet_settings_nonce' );
			self::save_settings();
			add_settings_error( 'wet_settings', 'wet_settings_saved', __( 'Settings saved.', 'wootickets' ), 'updated' );
		}

		settings_errors( 'wet_settings' );
		include WET_PLUGIN_DIR . 'includes/admin/views/settings.php';
	}

	/**
	 * Get total stats across all events
	 * @return array
	 */
	public static function get_total_stats() {
		global $wpdb;
		$tickets = Woo_Event_Tickets_DB::table( 'tickets' );
		$checkins = Woo_Event_Tickets_DB::table( 'checkins' );
		$events = Woo_Event_Tickets_DB::table( 'events' );

		$total_sold = $wpdb->get_var( "SELECT SUM(quantity) FROM {$tickets} WHERE status IN ('active','used')" ) ?: 0;
		$checked_in = $wpdb->get_var( "SELECT COUNT(*) FROM {$checkins} WHERE result = 'valid'" ) ?: 0;
		$total_revenue = $wpdb->get_var( "SELECT SUM(price_paid * quantity) FROM {$tickets} WHERE status IN ('active','used')" ) ?: 0;
		$active_events = $wpdb->get_var( "SELECT COUNT(*) FROM {$events} WHERE status = 'published'" ) ?: 0;

		return [
			'total_sold' => intval( $total_sold ),
			'checked_in' => intval( $checked_in ),
			'total_revenue' => floatval( $total_revenue ),
			'active_events' => intval( $active_events ),
		];
	}

	/**
	 * Render events list
	 */
	public static function render_events_list() {
		$page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$events = Woo_Event_Tickets_Events::get_all( [ 'per_page' => 20, 'page' => $page ] );

		include WET_PLUGIN_DIR . 'includes/admin/views/events-list.php';
	}

	/**
	 * Render event form
	 * @param int $event_id
	 */
	public static function render_event_form( $event_id = 0 ) {
		$event = $event_id ? Woo_Event_Tickets_Events::get( $event_id ) : [];

		if ( isset( $_POST['wet_save_event'] ) ) {
			check_admin_referer( 'wet_event_nonce' );
			self::save_event( $event_id );
		}

		include WET_PLUGIN_DIR . 'includes/admin/views/event-form.php';
	}

	/**
	 * Save event
	 * @param int $event_id
	 */
	public static function save_event( $event_id = 0 ) {
		$data = [
			'name' => sanitize_text_field( $_POST['name'] ),
			'slug' => sanitize_title( $_POST['slug'] ),
			'description' => wp_kses_post( $_POST['description'] ),
			'banner_url' => esc_url_raw( $_POST['banner_url'] ),
			'logo_url' => esc_url_raw( $_POST['logo_url'] ),
			'start_datetime' => sanitize_text_field( $_POST['start_datetime'] ),
			'end_datetime' => sanitize_text_field( $_POST['end_datetime'] ),
			'venue' => sanitize_text_field( $_POST['venue'] ),
			'address' => sanitize_textarea_field( $_POST['address'] ),
			'city' => sanitize_text_field( $_POST['city'] ),
			'state' => sanitize_text_field( $_POST['state'] ),
			'country' => sanitize_text_field( $_POST['country'] ),
			'postal_code' => sanitize_text_field( $_POST['postal_code'] ),
			'capacity' => intval( $_POST['capacity'] ),
			'status' => sanitize_text_field( $_POST['status'] ),
			'timezone' => sanitize_text_field( $_POST['timezone'] ),
			'primary_color' => sanitize_hex_color( $_POST['primary_color'] ),
			'secondary_color' => sanitize_hex_color( $_POST['secondary_color'] ),
			'footer_text' => sanitize_textarea_field( $_POST['footer_text'] ),
			'organizer_name' => sanitize_text_field( $_POST['organizer_name'] ),
			'organizer_email' => sanitize_email( $_POST['organizer_email'] ),
			'organizer_phone' => sanitize_text_field( $_POST['organizer_phone'] ),
			'website_url' => esc_url_raw( $_POST['website_url'] ),
			'social_links' => sanitize_textarea_field( $_POST['social_links'] ),
		];

		if ( $event_id ) {
			Woo_Event_Tickets_Events::update( $event_id, $data );
			$redirect = add_query_arg( [ 'event_id' => $event_id, 'saved' => 1 ], admin_url( 'admin.php?page=woo-event-tickets-events' ) );
		} else {
			$event_id = Woo_Event_Tickets_Events::create( $data );
			$redirect = add_query_arg( [ 'event_id' => $event_id, 'created' => 1 ], admin_url( 'admin.php?page=woo-event-tickets-events' ) );
		}

		wp_redirect( $redirect );
		exit;
	}

	/**
	 * Delete event
	 * @param int $event_id
	 */
	public static function delete_event( $event_id ) {
		check_admin_referer( 'wet_delete_event' );

		// Delete related data
		Woo_Event_Tickets_Ticket_Types::get_by_event( $event_id );
		// TODO: Delete ticket types, tickets, etc.

		Woo_Event_Tickets_Events::delete( $event_id );

		wp_redirect( admin_url( 'admin.php?page=woo-event-tickets-events&deleted=1' ) );
		exit;
	}

	/**
	 * Render ticket types list
	 * @param int $event_id
	 */
	public static function render_ticket_types_list( $event_id = 0 ) {
		$events = Woo_Event_Tickets_Events::get_dropdown_options();
		$event = $event_id ? Woo_Event_Tickets_Events::get( $event_id ) : null;

		if ( $event_id ) {
			$ticket_types = Woo_Event_Tickets_Ticket_Types::get_by_event( $event_id );
		} else {
			$ticket_types = [];
		}

		include WET_PLUGIN_DIR . 'includes/admin/views/ticket-types-list.php';
	}

	/**
	 * Render ticket type form
	 * @param int $type_id
	 * @param int $event_id
	 */
	public static function render_ticket_type_form( $type_id = 0, $event_id = 0 ) {
		$type = $type_id ? Woo_Event_Tickets_Ticket_Types::get( $type_id ) : [];
		$events = Woo_Event_Tickets_Events::get_dropdown_options();

		if ( isset( $_POST['wet_save_ticket_type'] ) ) {
			check_admin_referer( 'wet_ticket_type_nonce' );
			self::save_ticket_type( $type_id );
		}

		include WET_PLUGIN_DIR . 'includes/admin/views/ticket-type-form.php';
	}

	/**
	 * Save ticket type
	 * @param int $type_id
	 */
	public static function save_ticket_type( $type_id = 0 ) {
		$event_id = intval( $_POST['event_id'] );
		$event = Woo_Event_Tickets_Events::get( $event_id );

		$data = [
			'event_id' => $event_id,
			'name' => sanitize_text_field( $_POST['name'] ),
			'slug' => sanitize_title( $_POST['slug'] ),
			'description' => wp_kses_post( $_POST['description'] ),
			'image_url' => esc_url_raw( $_POST['image_url'] ),
			'regular_price' => floatval( $_POST['regular_price'] ),
			'sale_price' => $_POST['sale_price'] ? floatval( $_POST['sale_price'] ) : null,
			'early_bird_price' => $_POST['early_bird_price'] ? floatval( $_POST['early_bird_price'] ) : null,
			'early_bird_quantity' => intval( $_POST['early_bird_quantity'] ),
			'early_bird_start' => $_POST['early_bird_start'] ? sanitize_text_field( $_POST['early_bird_start'] ) : null,
			'early_bird_end' => $_POST['early_bird_end'] ? sanitize_text_field( $_POST['early_bird_end'] ) : null,
			'total_quantity' => intval( $_POST['total_quantity'] ),
			'max_per_customer' => intval( $_POST['max_per_customer'] ),
			'min_per_order' => intval( $_POST['min_per_order'] ),
			'validity_days' => intval( $_POST['validity_days'] ),
			'event_date' => $_POST['event_date'] ? sanitize_text_field( $_POST['event_date'] ) : null,
			'event_time' => $_POST['event_time'] ? sanitize_text_field( $_POST['event_time'] ) : null,
			'venue' => sanitize_text_field( $_POST['venue'] ),
			'address' => sanitize_textarea_field( $_POST['address'] ),
			'allowed_people' => intval( $_POST['allowed_people'] ),
			'features' => wp_kses_post( $_POST['features'] ),
			'terms_conditions' => wp_kses_post( $_POST['terms_conditions'] ),
			'refund_policy' => wp_kses_post( $_POST['refund_policy'] ),
			'category' => sanitize_text_field( $_POST['category'] ),
			'color' => sanitize_hex_color( $_POST['color'] ),
			'custom_fields' => sanitize_textarea_field( $_POST['custom_fields'] ),
			'status' => sanitize_text_field( $_POST['status'] ),
		];

		if ( $type_id ) {
			Woo_Event_Tickets_Ticket_Types::update( $type_id, $data );
			$redirect = add_query_arg( [ 'type_id' => $type_id, 'saved' => 1 ], admin_url( 'admin.php?page=woo-event-tickets-ticket-types' ) );
		} else {
			$type_id = Woo_Event_Tickets_Ticket_Types::create( $data );
			$redirect = add_query_arg( [ 'type_id' => $type_id, 'created' => 1 ], admin_url( 'admin.php?page=woo-event-tickets-ticket-types' ) );
		}

		wp_redirect( $redirect );
		exit;
	}

	/**
	 * Delete ticket type
	 * @param int $type_id
	 */
	public static function delete_ticket_type( $type_id ) {
		check_admin_referer( 'wet_delete_ticket_type' );
		Woo_Event_Tickets_Ticket_Types::delete( $type_id );
		wp_redirect( admin_url( 'admin.php?page=woo-event-tickets-ticket-types&deleted=1' ) );
		exit;
	}

	/**
	 * Render staff list
	 * @param int $event_id
	 */
	public static function render_staff_list( $event_id = 0 ) {
		$events = Woo_Event_Tickets_Events::get_dropdown_options();
		$event = $event_id ? Woo_Event_Tickets_Events::get( $event_id ) : null;

		if ( $event_id ) {
			$staff = Woo_Event_Tickets_Staff::get_by_event( $event_id );
		} else {
			$staff = [];
		}

		include WET_PLUGIN_DIR . 'includes/admin/views/staff-list.php';
	}

	/**
	 * Render staff form
	 * @param int $staff_id
	 * @param int $event_id
	 */
	public static function render_staff_form( $staff_id = 0, $event_id = 0 ) {
		$staff = $staff_id ? Woo_Event_Tickets_Staff::get( $staff_id ) : [];
		$events = Woo_Event_Tickets_Events::get_dropdown_options();
		$available_users = Woo_Event_Tickets_Staff::get_available_users();

		if ( isset( $_POST['wet_save_staff'] ) ) {
			check_admin_referer( 'wet_staff_nonce' );
			self::save_staff( $staff_id );
		}

		include WET_PLUGIN_DIR . 'includes/admin/views/staff-form.php';
	}

	/**
	 * Save staff
	 * @param int $staff_id
	 */
	public static function save_staff( $staff_id = 0 ) {
		$data = [
			'event_id' => intval( $_POST['event_id'] ),
			'user_id' => intval( $_POST['user_id'] ),
			'role' => sanitize_text_field( $_POST['role'] ),
			'permissions' => sanitize_textarea_field( $_POST['permissions'] ),
			'active' => isset( $_POST['active'] ) ? 1 : 0,
		];

		if ( $staff_id ) {
			Woo_Event_Tickets_Staff::update( $staff_id, $data );
		} else {
			Woo_Event_Tickets_Staff::create( $data );
		}

		wp_redirect( admin_url( 'admin.php?page=woo-event-tickets-staff&saved=1' ) );
		exit;
	}

	/**
	 * Delete staff
	 * @param int $staff_id
	 */
	public static function delete_staff( $staff_id ) {
		check_admin_referer( 'wet_delete_staff' );
		Woo_Event_Tickets_Staff::delete( $staff_id );
		wp_redirect( admin_url( 'admin.php?page=woo-event-tickets-staff&deleted=1' ) );
		exit;
	}

	/**
	 * Get sales report
	 */
	public static function get_sales_report( $event_id, $date_from, $date_to ) {
		global $wpdb;
		$tickets = Woo_Event_Tickets_DB::table( 'tickets' );
		$types = Woo_Event_Tickets_DB::table( 'ticket_types' );
		$events = Woo_Event_Tickets_DB::table( 'events' );

		$where = "WHERE t.created_at BETWEEN %s AND %s AND t.status IN ('active','used')";
		$args = [ $date_from . ' 00:00:00', $date_to . ' 23:59:59' ];

		if ( $event_id ) {
			$where .= " AND t.event_id = %d";
			$args[] = $event_id;
		}

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT t.*, tt.name as ticket_type_name, e.name as event_name
			FROM {$tickets} t
			LEFT JOIN {$types} tt ON t.ticket_type_id = tt.id
			LEFT JOIN {$events} e ON t.event_id = e.id
			{$where}
			ORDER BY t.created_at DESC",
			...$args
		), ARRAY_A );
	}

	/**
	 * Get check-in report
	 */
	public static function get_checkin_report( $event_id, $date_from, $date_to ) {
		global $wpdb;
		$checkins = Woo_Event_Tickets_DB::table( 'checkins' );
		$tickets = Woo_Event_Tickets_DB::table( 'tickets' );
		$types = Woo_Event_Tickets_DB::table( 'ticket_types' );

		$where = "WHERE c.scanned_at BETWEEN %s AND %s";
		$args = [ $date_from . ' 00:00:00', $date_to . ' 23:59:59' ];

		if ( $event_id ) {
			$where .= " AND c.event_id = %d";
			$args[] = $event_id;
		}

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT c.*, t.ticket_id, tt.name as ticket_type_name
			FROM {$checkins} c
			LEFT JOIN {$tickets} t ON c.ticket_id = t.id
			LEFT JOIN {$types} tt ON t.ticket_type_id = tt.id
			{$where}
			ORDER BY c.scanned_at DESC",
			...$args
		), ARRAY_A );
	}

	/**
	 * Get customer report
	 */
	public static function get_customer_report( $event_id, $date_from, $date_to ) {
		global $wpdb;
		$tickets = Woo_Event_Tickets_DB::table( 'tickets' );
		$events = Woo_Event_Tickets_DB::table( 'events' );

		$where = "WHERE t.created_at BETWEEN %s AND %s";
		$args = [ $date_from . ' 00:00:00', $date_to . ' 23:59:59' ];

		if ( $event_id ) {
			$where .= " AND t.event_id = %d";
			$args[] = $event_id;
		}

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT t.customer_id, t.customer_email, t.customer_name, t.customer_phone,
				COUNT(*) as ticket_count, SUM(t.quantity) as total_quantity, SUM(t.price_paid * t.quantity) as total_spent,
				MAX(t.created_at) as last_purchase
			FROM {$tickets} t
			{$where}
			GROUP BY t.customer_email
			ORDER BY total_spent DESC",
			...$args
		), ARRAY_A );
	}

	/**
	 * Get revenue report
	 */
	public static function get_revenue_report( $event_id, $date_from, $date_to ) {
		global $wpdb;
		$tickets = Woo_Event_Tickets_DB::table( 'tickets' );
		$events = Woo_Event_Tickets_DB::table( 'events' );

		$where = "WHERE t.created_at BETWEEN %s AND %s AND t.status IN ('active','used')";
		$args = [ $date_from . ' 00:00:00', $date_to . ' 23:59:59' ];

		if ( $event_id ) {
			$where .= " AND t.event_id = %d";
			$args[] = $event_id;
		}

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT DATE(t.created_at) as date, 
				COUNT(*) as tickets_sold, 
				SUM(t.quantity) as quantity, 
				SUM(t.price_paid * t.quantity) as revenue
			FROM {$tickets} t
			{$where}
			GROUP BY DATE(t.created_at)
			ORDER BY date ASC",
			...$args
		), ARRAY_A );
	}

	/**
	 * Save settings
	 */
	public static function save_settings() {
		$settings = [
			'ticket_generation_statuses' => isset( $_POST['ticket_generation_statuses'] ) ? array_map( 'sanitize_text_field', $_POST['ticket_generation_statuses'] ) : [ 'processing', 'completed' ],
			'ticket_id_format' => sanitize_text_field( $_POST['ticket_id_format'] ),
			'email_sender_name' => sanitize_text_field( $_POST['email_sender_name'] ),
			'email_sender_email' => sanitize_email( $_POST['email_sender_email'] ),
			'email_subject' => sanitize_text_field( $_POST['email_subject'] ),
			'pdf_attachment' => isset( $_POST['pdf_attachment'] ) ? 'yes' : 'no',
			'scanner_sound' => isset( $_POST['scanner_sound'] ) ? 'yes' : 'no',
			'scanner_vibration' => isset( $_POST['scanner_vibration'] ) ? 'yes' : 'no',
			'scanner_auto_next' => isset( $_POST['scanner_auto_next'] ) ? 'yes' : 'no',
			'scanner_manual' => isset( $_POST['scanner_manual'] ) ? 'yes' : 'no',
			'allow_late_checkin' => isset( $_POST['allow_late_checkin'] ) ? 'yes' : 'no',
			'currency' => sanitize_text_field( $_POST['currency'] ),
			'timezone' => sanitize_text_field( $_POST['timezone'] ),
		];

		foreach ( $settings as $key => $value ) {
			update_option( 'wet_' . $key, $value );
		}
	}
}