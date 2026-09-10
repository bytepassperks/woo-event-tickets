<?php
/**
 * Check-in system with atomic operations
 */
class Woo_Event_Tickets_Checkin {

	/**
	 * Process check-in with atomic database operation
	 * @param string $secure_token
	 * @param string $ticket_id
	 * @return array
	 */
	public static function process_check_in( $secure_token, $ticket_id = '' ) {
		global $wpdb;

		$qr_tokens = Woo_Event_Tickets_DB::table( 'qr_tokens' );
		$tickets = Woo_Event_Tickets_DB::table( 'tickets' );
		$checkins = Woo_Event_Tickets_DB::table( 'checkins' );
		$events = Woo_Event_Tickets_DB::table( 'events' );
		$ticket_types = Woo_Event_Tickets_DB::table( 'ticket_types' );

		// Get QR token
		$qr_token = $wpdb->get_row( $wpdb->prepare(
			"SELECT qt.*, t.ticket_id as ticket_str, t.event_id, t.ticket_type_id, t.customer_name, t.status as ticket_status
			FROM {$qr_tokens} qt
			INNER JOIN {$tickets} t ON qt.ticket_id = t.id
			WHERE qt.secure_token = %s",
			$secure_token
		), ARRAY_A );

		if ( ! $qr_token ) {
			return [
				'success' => false,
				'status' => 'invalid',
				'message' => __( 'Invalid QR code', 'wootickets' ),
			];
		}

		// Verify ticket ID matches if provided
		if ( $ticket_id && $qr_token['ticket_str'] !== $ticket_id ) {
			return [
				'success' => false,
				'status' => 'invalid',
				'message' => __( 'Ticket ID mismatch', 'wootickets' ),
			];
		}

		// Get event info
		$event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$events} WHERE id = %d", $qr_token['event_id'] ), ARRAY_A );
		$ticket_type = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ticket_types} WHERE id = %d", $qr_token['ticket_type_id'] ), ARRAY_A );

		// Check QR token status
		if ( $qr_token['status'] !== 'active' ) {
			$status_messages = [
				'used' => __( 'This ticket has already been used', 'wootickets' ),
				'cancelled' => __( 'This ticket has been cancelled', 'wootickets' ),
				'refunded' => __( 'This ticket has been refunded', 'wootickets' ),
				'expired' => __( 'This ticket has expired', 'wootickets' ),
				'blocked' => __( 'This ticket has been blocked', 'wootickets' ),
			];

			// Log the rejection
			self::log_check_in( $qr_token['ticket_id'], $qr_token['id'], $qr_token['event_id'], $qr_token['status'], get_current_user_id() );

			return [
				'success' => false,
				'status' => $qr_token['status'],
				'message' => $status_messages[ $qr_token['status'] ] ?? __( 'Invalid ticket', 'wootickets' ),
				'ticket' => [
					'ticket_id' => $qr_token['ticket_str'],
					'ticket_type' => $ticket_type['name'] ?? '',
					'customer_name' => $qr_token['customer_name'],
					'first_used' => $qr_token['used_at'],
				],
			];
		}

		// Check ticket status
		if ( $qr_token['ticket_status'] !== 'active' ) {
			$status_messages = [
				'pending' => __( 'Payment pending', 'wootickets' ),
				'cancelled' => __( 'This ticket has been cancelled', 'wootickets' ),
				'refunded' => __( 'This ticket has been refunded', 'wootickets' ),
				'expired' => __( 'This ticket has expired', 'wootickets' ),
				'blocked' => __( 'This ticket has been blocked', 'wootickets' ),
			];

			return [
				'success' => false,
				'status' => $qr_token['ticket_status'],
				'message' => $status_messages[ $qr_token['ticket_status'] ] ?? __( 'Invalid ticket status', 'wootickets' ),
			];
		}

		// Check event date (optional - configurable)
		if ( $event && $event['end_datetime'] && strtotime( $event['end_datetime'] ) < current_time( 'timestamp' ) ) {
			// Allow late check-in if configured
			$allow_late = get_option( 'wet_allow_late_checkin', 'no' ) === 'yes';
			if ( ! $allow_late ) {
				return [
					'success' => false,
					'status' => 'expired',
					'message' => __( 'Event has ended', 'wootickets' ),
				];
			}
		}

		// ATOMIC CHECK-IN: Use database transaction with row locking
		// This prevents race conditions when two scanners scan simultaneously
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Lock the QR token row
			$locked = $wpdb->get_row( $wpdb->prepare(
				"SELECT id, status FROM {$qr_tokens} WHERE id = %d FOR UPDATE",
				$qr_token['id']
			), ARRAY_A );

			if ( ! $locked || $locked['status'] !== 'active' ) {
				$wpdb->query( 'ROLLBACK' );
				
				$status_messages = [
					'used' => __( 'This ticket has already been used', 'wootickets' ),
					'cancelled' => __( 'This ticket has been cancelled', 'wootickets' ),
					'refunded' => __( 'This ticket has been refunded', 'wootickets' ),
					'expired' => __( 'This ticket has expired', 'wootickets' ),
					'blocked' => __( 'This ticket has been blocked', 'wootickets' ),
				];

				return [
					'success' => false,
					'status' => $locked['status'] ?? 'invalid',
					'message' => $status_messages[ $locked['status'] ] ?? __( 'Invalid ticket', 'wootickets' ),
					'ticket' => [
						'ticket_id' => $qr_token['ticket_str'],
						'ticket_type' => $ticket_type['name'] ?? '',
						'customer_name' => $qr_token['customer_name'],
						'first_used' => $qr_token['used_at'],
					],
				];
			}

			// Update QR token to used (atomic)
			$wpdb->update( $qr_tokens, [
				'status' => 'used',
				'used_at' => current_time( 'mysql' ),
			], [ 'id' => $qr_token['id'] ] );

			// Update ticket status
			$wpdb->update( $tickets, [
				'status' => 'used',
				'check_in_date' => current_time( 'mysql' ),
				'checked_in_by' => get_current_user_id(),
				'updated_at' => current_time( 'mysql' ),
			], [ 'id' => $qr_token['ticket_id'] ] );

			// Insert check-in log
			$wpdb->insert( $checkins, [
				'ticket_id' => $qr_token['ticket_id'],
				'qr_token_id' => $qr_token['id'],
				'event_id' => $qr_token['event_id'],
				'result' => 'valid',
				'staff_id' => get_current_user_id(),
				'staff_name' => wp_get_current_user()->display_name,
				'device_info' => $_SERVER['HTTP_USER_AGENT'] ?? '',
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'scanned_at' => current_time( 'mysql' ),
			] );

			$wpdb->query( 'COMMIT' );

			// Log the successful check-in
			self::log_check_in( $qr_token['ticket_id'], $qr_token['id'], $qr_token['event_id'], 'valid', get_current_user_id() );

			// Update ticket type remaining (if needed)
			Woo_Event_Tickets_Ticket_Types::update_remaining_quantity( $qr_token['ticket_type_id'] );

			return [
				'success' => true,
				'status' => 'valid',
				'message' => __( 'Valid ticket - Checked in successfully', 'wootickets' ),
				'ticket' => [
					'ticket_id' => $qr_token['ticket_str'],
					'ticket_type' => $ticket_type['name'] ?? '',
					'customer_name' => $qr_token['customer_name'],
					'people_allowed' => $ticket_type['allowed_people'] ?? 1,
					'checked_in_at' => current_time( 'mysql' ),
					'checked_in_by' => wp_get_current_user()->display_name,
				],
			];
		} catch ( Exception $e ) {
			$wpdb->query( 'ROLLBACK' );
			self::log_check_in( $qr_token['ticket_id'], $qr_token['id'], $qr_token['event_id'], 'error', get_current_user_id(), $e->getMessage() );
			
			return [
				'success' => false,
				'status' => 'error',
				'message' => __( 'Check-in failed. Please try again.', 'wootickets' ),
			];
		}
	}

	/**
	 * Manual check-in by ticket ID
	 * @param string $ticket_id
	 * @return array
	 */
	public static function manual_check_in( $ticket_id ) {
		$ticket = Woo_Event_Tickets_Tickets::get_by_ticket_id( $ticket_id );
		if ( ! $ticket ) {
			return [
				'success' => false,
				'message' => __( 'Ticket not found', 'wootickets' ),
			];
		}

		$qr_token = Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'qr_tokens' ) . " WHERE ticket_id = %d AND status = 'active'", [ $ticket['id'] ] );
		if ( ! $qr_token ) {
			return [
				'success' => false,
				'message' => __( 'No valid QR code found for this ticket', 'wootickets' ),
			];
		}

		return self::process_check_in( $qr_token['secure_token'], $ticket_id );
	}

	/**
	 * Log check-in attempt
	 * @param int $ticket_db_id
	 * @param int $qr_token_id
	 * @param int $event_id
	 * @param string $result
	 * @param int $staff_id
	 * @param string $notes
	 */
	public static function log_check_in( $ticket_db_id, $qr_token_id, $event_id, $result, $staff_id = 0, $notes = '' ) {
		Woo_Event_Tickets_DB::insert( 'checkins', [
			'ticket_id' => $ticket_db_id,
			'qr_token_id' => $qr_token_id,
			'event_id' => $event_id,
			'result' => $result,
			'staff_id' => $staff_id,
			'staff_name' => $staff_id ? get_userdata( $staff_id )->display_name : 'System',
			'device_info' => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			'scanned_at' => current_time( 'mysql' ),
			'notes' => $notes,
		] );
	}

	/**
	 * Get check-in statistics for event
	 * @param int $event_id
	 * @return array
	 */
	public static function get_stats( $event_id ) {
		global $wpdb;
		$checkins = Woo_Event_Tickets_DB::table( 'checkins' );

		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$checkins} WHERE event_id = %d", $event_id ) );
		$valid = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$checkins} WHERE event_id = %d AND result = 'valid'", $event_id ) );
		$rejected = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$checkins} WHERE event_id = %d AND result != 'valid'", $event_id ) );

		// By hour
		$by_hour = $wpdb->get_results( $wpdb->prepare(
			"SELECT HOUR(scanned_at) as hour, COUNT(*) as count FROM {$checkins} WHERE event_id = %d AND result = 'valid' GROUP BY HOUR(scanned_at) ORDER BY hour",
			$event_id
		), ARRAY_A );

		// By staff
		$by_staff = $wpdb->get_results( $wpdb->prepare(
			"SELECT staff_name, COUNT(*) as total, SUM(CASE WHEN result = 'valid' THEN 1 ELSE 0 END) as valid, SUM(CASE WHEN result != 'valid' THEN 1 ELSE 0 END) as rejected FROM {$checkins} WHERE event_id = %d GROUP BY staff_name ORDER BY total DESC",
			$event_id
		), ARRAY_A );

		return [
			'total_scans' => intval( $total ?: 0 ),
			'valid' => intval( $valid ?: 0 ),
			'rejected' => intval( $rejected ?: 0 ),
			'by_hour' => $by_hour,
			'by_staff' => $by_staff,
		];
	}

	/**
	 * Get recent check-ins
	 * @param int $event_id
	 * @param int $limit
	 * @return array
	 */
	public static function get_recent( $event_id, $limit = 20 ) {
		global $wpdb;
		$checkins = Woo_Event_Tickets_DB::table( 'checkins' );
		$tickets = Woo_Event_Tickets_DB::table( 'tickets' );
		$ticket_types = Woo_Event_Tickets_DB::table( 'ticket_types' );

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT c.*, t.ticket_id, tt.name as ticket_type_name 
			FROM {$checkins} c
			LEFT JOIN {$tickets} t ON c.ticket_id = t.id
			LEFT JOIN {$ticket_types} tt ON t.ticket_type_id = tt.id
			WHERE c.event_id = %d
			ORDER BY c.scanned_at DESC
			LIMIT %d",
			$event_id,
			$limit
		), ARRAY_A );
	}
}