<?php
/**
 * Staff management
 */
class Woo_Event_Tickets_Staff {

	/**
	 * Register custom capabilities
	 */
	public static function register_capabilities() {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'manage_woo_event_tickets' );
			$admin->add_cap( 'view_woo_event_tickets_reports' );
			$admin->add_cap( 'manage_woo_event_tickets_staff' );
			$admin->add_cap( 'manage_woo_event_tickets_settings' );
		}

		// Event Manager role
		if ( ! get_role( 'wet_event_manager' ) ) {
			add_role( 'wet_event_manager', __( 'Event Manager', 'wootickets' ), [
				'read' => true,
				'manage_woo_event_tickets' => true,
				'view_woo_event_tickets_reports' => true,
				'manage_woo_event_tickets_staff' => true,
			] );
		}

		// Scanner Staff role
		if ( ! get_role( 'wet_scanner_staff' ) ) {
			add_role( 'wet_scanner_staff', __( 'Event Scanner Staff', 'wootickets' ), [
				'read' => true,
				'manage_woo_event_tickets' => true,
			] );
		}
	}

	/**
	 * Create staff member
	 * @param array $data
	 * @return int|false
	 */
	public static function create( $data ) {
		$defaults = [
			'event_id' => 0,
			'user_id' => 0,
			'role' => 'scanner',
			'permissions' => '',
			'active' => 1,
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		];

		$data = wp_parse_args( $data, $defaults );

		// Assign WordPress role
		$user = get_userdata( $data['user_id'] );
		if ( $user ) {
			$wp_role = $data['role'] === 'admin' ? 'wet_event_manager' : 'wet_scanner_staff';
			$user->add_role( $wp_role );
		}

		return Woo_Event_Tickets_DB::insert( 'staff', $data );
	}

	/**
	 * Update staff
	 * @param int $id
	 * @param array $data
	 * @return int|false
	 */
	public static function update( $id, $data ) {
		$data['updated_at'] = current_time( 'mysql' );

		$existing = self::get( $id );
		if ( $existing && isset( $data['role'] ) && $data['role'] !== $existing['role'] ) {
			$user = get_userdata( $existing['user_id'] );
			if ( $user ) {
				// Remove old role
				$user->remove_role( $existing['role'] === 'admin' ? 'wet_event_manager' : 'wet_scanner_staff' );
				// Add new role
				$wp_role = $data['role'] === 'admin' ? 'wet_event_manager' : 'wet_scanner_staff';
				$user->add_role( $wp_role );
			}
		}

		return Woo_Event_Tickets_DB::update( 'staff', $data, [ 'id' => $id ] );
	}

	/**
	 * Get staff by ID
	 * @param int $id
	 * @return array|null
	 */
	public static function get( $id ) {
		return Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'staff' ) . " WHERE id = %d", [ $id ] );
	}

	/**
	 * Get staff by event
	 * @param int $event_id
	 * @return array
	 */
	public static function get_by_event( $event_id ) {
		return Woo_Event_Tickets_DB::get_results( "SELECT s.*, u.user_login, u.user_email, u.display_name FROM " . Woo_Event_Tickets_DB::table( 'staff' ) . " s LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID WHERE s.event_id = %d ORDER BY s.created_at DESC", [ $event_id ] );
	}

	/**
	 * Get staff by user
	 * @param int $user_id
	 * @return array
	 */
	public static function get_by_user( $user_id ) {
		return Woo_Event_Tickets_DB::get_results( "SELECT s.*, e.name as event_name FROM " . Woo_Event_Tickets_DB::table( 'staff' ) . " s LEFT JOIN " . Woo_Event_Tickets_DB::table( 'events' ) . " e ON s.event_id = e.id WHERE s.user_id = %d ORDER BY s.created_at DESC", [ $user_id ] );
	}

	/**
	 * Delete staff
	 * @param int $id
	 * @return int|false
	 */
	public static function delete( $id ) {
		$staff = self::get( $id );
		if ( $staff ) {
			$user = get_userdata( $staff['user_id'] );
			if ( $user ) {
				$user->remove_role( 'wet_event_manager' );
				$user->remove_role( 'wet_scanner_staff' );
			}
		}
		return Woo_Event_Tickets_DB::delete( 'staff', [ 'id' => $id ] );
	}

	/**
	 * Check if user is staff for event
	 * @param int $user_id
	 * @param int $event_id
	 * @return bool
	 */
	public static function is_staff_for_event( $user_id, $event_id = 0 ) {
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		$where = [ 'user_id = %d', 'active = 1' ];
		$args = [ $user_id, 1 ];

		if ( $event_id ) {
			$where[] = 'event_id = %d';
			$args[] = $event_id;
		}

		$staff = Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'staff' ) . " WHERE " . implode( ' AND ', $where ), $args );
		return $staff !== null;
	}

	/**
	 * Get staff check-in stats
	 * @param int $event_id
	 * @return array
	 */
	public static function get_staff_stats( $event_id ) {
		global $wpdb;
		$checkins = Woo_Event_Tickets_DB::table( 'checkins' );
		$staff = Woo_Event_Tickets_DB::table( 'staff' );

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT s.id, s.user_id, u.display_name, s.role,
				COUNT(c.id) as total_scans,
				SUM(CASE WHEN c.result = 'valid' THEN 1 ELSE 0 END) as valid_scans,
				SUM(CASE WHEN c.result != 'valid' THEN 1 ELSE 0 END) as rejected_scans,
				MIN(c.scanned_at) as first_scan,
				MAX(c.scanned_at) as last_scan
			FROM {$staff} s
			LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
			LEFT JOIN {$checkins} c ON s.user_id = c.staff_id AND c.event_id = %d
			WHERE s.event_id = %d
			GROUP BY s.id
			ORDER BY valid_scans DESC",
			$event_id,
			$event_id
		), ARRAY_A );
	}

	/**
	 * Get available users for staff assignment
	 * @return array
	 */
	public static function get_available_users() {
		global $wpdb;
		return $wpdb->get_results( "SELECT ID, user_login, user_email, display_name FROM {$wpdb->users} WHERE ID NOT IN (SELECT user_id FROM " . Woo_Event_Tickets_DB::table( 'staff' ) . " WHERE active = 1) ORDER BY display_name", ARRAY_A );
	}
}

// Register capabilities on init
add_action( 'init', [ 'Woo_Event_Tickets_Staff', 'register_capabilities' ] );