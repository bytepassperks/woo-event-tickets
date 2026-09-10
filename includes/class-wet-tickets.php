<?php
/**
 * Tickets management class
 */
class Woo_Event_Tickets_Tickets {

	/**
	 * Generate unique ticket ID
	 * @param int $event_id
	 * @return string
	 */
	public static function generate_ticket_id( $event_id ) {
		$event = Woo_Event_Tickets_Events::get( $event_id );
		$prefix = $event ? strtoupper( substr( preg_replace( '/[^A-Z]/', '', $event['name'] ), 0, 3 ) ) : 'EVT';
		$year = date( 'Y' );
		
		// Get next sequence number
		global $wpdb;
		$tickets_table = Woo_Event_Tickets_DB::table( 'tickets' );
		$sequence = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) + 1 FROM {$tickets_table} WHERE event_id = %d AND ticket_id LIKE %s",
			$event_id,
			$prefix . '-' . $year . '-%'
		) );

		return sprintf( '%s-%s-%06d', $prefix, $year, intval( $sequence ?: 1 ) );
	}

	/**
	 * Generate secure random token
	 * @return string
	 */
	public static function generate_secure_token() {
		return bin2hex( random_bytes( 32 ) );
	}

	/**
	 * Create ticket from WooCommerce order
	 * @param WC_Order $order
	 * @param WC_Order_Item_Product $item
	 * @return int|false
	 */
	public static function create_from_order( $order, $item ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		$product_id = $item->get_product_id();
		$ticket_type = Woo_Event_Tickets_Ticket_Types::get_by_product_id( $product_id );
		
		if ( ! $ticket_type ) {
			// Maybe it's a variation
			$variation_id = $item->get_variation_id();
			if ( $variation_id ) {
				$ticket_type = Woo_Event_Tickets_Ticket_Types::get_by_product_id( $variation_id );
			}
		}

		if ( ! $ticket_type ) {
			self::log( 0, 0, 'ticket_creation_failed', sprintf( 'No ticket type found for product %d', $product_id ) );
			return false;
		}

		$event_id = $ticket_type['event_id'];
		$quantity = $item->get_quantity();
		$price = $item->get_total() / $quantity;
		$customer_id = $order->get_customer_id();
		$customer_email = $order->get_billing_email();
		$customer_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$customer_phone = $order->get_billing_phone();

		$created_tickets = [];

		// Generate individual tickets for each quantity
		for ( $i = 0; $i < $quantity; $i++ ) {
			$ticket_id = self::generate_ticket_id( $event_id );
			$secure_token = self::generate_secure_token();

			$ticket_data = [
				'event_id' => $event_id,
				'ticket_type_id' => $ticket_type['id'],
				'order_id' => $order->get_id(),
				'order_item_id' => $item->get_id(),
				'ticket_id' => $ticket_id,
				'customer_id' => $customer_id,
				'customer_email' => $customer_email,
				'customer_name' => $customer_name,
				'customer_phone' => $customer_phone,
				'quantity' => 1,
				'price_paid' => $price,
				'currency' => $order->get_currency(),
				'status' => 'active',
				'purchase_date' => current_time( 'mysql' ),
				'email_status' => 'pending',
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			];

			$ticket_db_id = Woo_Event_Tickets_DB::insert( 'tickets', $ticket_data );
			
			if ( $ticket_db_id ) {
				// Create QR token
				self::create_qr_token( $ticket_db_id, $secure_token, $event_id );

				// Create attendee record
				self::create_attendee( $ticket_db_id, $ticket_type['id'], $event_id, [
					'full_name' => $customer_name,
					'email' => $customer_email,
					'phone' => $customer_phone,
				] );

				$created_tickets[] = $ticket_db_id;
			}
		}

		// Update ticket type remaining quantity
		Woo_Event_Tickets_Ticket_Types::update_remaining_quantity( $ticket_type['id'] );

		// Send emails and generate PDFs
		foreach ( $created_tickets as $ticket_db_id ) {
			self::send_ticket_email( $ticket_db_id );
			self::generate_pdf_ticket( $ticket_db_id );
		}

		self::log( $event_id, $created_tickets[0] ?? 0, 'tickets_created', sprintf( 'Created %d tickets for order %s', count( $created_tickets ), $order->get_order_number() ) );

		return $created_tickets[0] ?? false;
	}

	/**
	 * Create QR token for ticket
	 * @param int $ticket_db_id
	 * @param string $secure_token
	 * @param int $event_id
	 * @return int|false
	 */
	public static function create_qr_token( $ticket_db_id, $secure_token, $event_id ) {
		$verification_url = home_url( "/ticket/verify/{$secure_token}" );

		$qr_data = [
			'ticket_id' => $ticket_db_id,
			'secure_token' => $secure_token,
			'verification_url' => $verification_url,
			'status' => 'active',
			'generated_at' => current_time( 'mysql' ),
		];

		return Woo_Event_Tickets_DB::insert( 'qr_tokens', $qr_data );
	}

	/**
	 * Create attendee record
	 * @param int $ticket_db_id
	 * @param int $ticket_type_id
	 * @param int $event_id
	 * @param array $data
	 * @return int|false
	 */
	public static function create_attendee( $ticket_db_id, $ticket_type_id, $event_id, $data ) {
		$defaults = [
			'full_name' => '',
			'email' => '',
			'phone' => '',
			'custom_data' => '',
		];
		$data = wp_parse_args( $data, $defaults );

		$attendee_data = [
			'ticket_id' => $ticket_db_id,
			'ticket_type_id' => $ticket_type_id,
			'event_id' => $event_id,
			'full_name' => $data['full_name'],
			'email' => $data['email'],
			'phone' => $data['phone'],
			'custom_data' => json_encode( $data['custom_data'] ?? [] ),
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		];

		return Woo_Event_Tickets_DB::insert( 'attendees', $attendee_data );
	}

	/**
	 * Get ticket by ID
	 * @param int $id
	 * @return array|null
	 */
	public static function get( $id ) {
		return Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'tickets' ) . " WHERE id = %d", [ $id ] );
	}

	/**
	 * Get ticket by ticket_id string
	 * @param string $ticket_id
	 * @return array|null
	 */
	public static function get_by_ticket_id( $ticket_id ) {
		return Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'tickets' ) . " WHERE ticket_id = %s", [ $ticket_id ] );
	}

	/**
	 * Get tickets by order
	 * @param int $order_id
	 * @return array
	 */
	public static function get_by_order( $order_id ) {
		return Woo_Event_Tickets_DB::get_results( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'tickets' ) . " WHERE order_id = %d ORDER BY created_at ASC", [ $order_id ] );
	}

	/**
	 * Get tickets by event
	 * @param int $event_id
	 * @param array $args
	 * @return array
	 */
	public static function get_by_event( $event_id, $args = [] ) {
		$defaults = [
			'status' => '',
			'ticket_type_id' => '',
			'orderby' => 'created_at',
			'order' => 'DESC',
			'per_page' => 20,
			'page' => 1,
			'search' => '',
		];
		$args = wp_parse_args( $args, $defaults );

		$where = [ 'event_id = %d' ];
		$where_args = [ $event_id ];

		if ( $args['status'] ) {
			$where[] = 'status = %s';
			$where_args[] = $args['status'];
		}

		if ( $args['ticket_type_id'] ) {
			$where[] = 'ticket_type_id = %d';
			$where_args[] = $args['ticket_type_id'];
		}

		if ( $args['search'] ) {
			$where[] = '(ticket_id LIKE %s OR customer_name LIKE %s OR customer_email LIKE %s OR customer_phone LIKE %s)';
			$search_term = '%' . $args['search'] . '%';
			$where_args[] = $search_term;
			$where_args[] = $search_term;
			$where_args[] = $search_term;
			$where_args[] = $search_term;
		}

		return Woo_Event_Tickets_DB::get_paginated( Woo_Event_Tickets_DB::table( 'tickets' ), [
			'where' => implode( ' AND ', $where ),
			'where_args' => $where_args,
			'orderby' => $args['orderby'],
			'order' => $args['order'],
			'per_page' => $args['per_page'],
			'page' => $args['page'],
		] );
	}

	/**
	 * Update ticket status
	 * @param int $ticket_db_id
	 * @param string $status
	 * @return int|false
	 */
	public static function update_status( $ticket_db_id, $status ) {
		$allowed = [ 'pending', 'active', 'used', 'cancelled', 'refunded', 'expired', 'blocked' ];
		if ( ! in_array( $status, $allowed, true ) ) {
			return false;
		}

		$data = [
			'status' => $status,
			'updated_at' => current_time( 'mysql' ),
		];

		if ( $status === 'used' ) {
			$data['check_in_date'] = current_time( 'mysql' );
			$data['checked_in_by'] = get_current_user_id();
		}

		return Woo_Event_Tickets_DB::update( 'tickets', $data, [ 'id' => $ticket_db_id ] );
	}

	/**
	 * Send ticket email
	 * @param int $ticket_db_id
	 * @return bool
	 */
	public static function send_ticket_email( $ticket_db_id ) {
		$ticket = self::get( $ticket_db_id );
		if ( ! $ticket ) {
			return false;
		}

		$ticket_type = Woo_Event_Tickets_Ticket_Types::get( $ticket['ticket_type_id'] );
		$event = Woo_Event_Tickets_Events::get( $ticket['event_id'] );

		if ( ! $ticket_type || ! $event ) {
			return false;
		}

		$qr_token = Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'qr_tokens' ) . " WHERE ticket_id = %d", [ $ticket_db_id ] );

		$subject = apply_filters( 'wet_email_subject', sprintf( __( 'Your Ticket is Confirmed – %s', 'wootickets' ), $event['name'] ), $ticket, $ticket_type, $event );

		$message = self::get_email_template( 'ticket_confirmation', [
			'customer_name' => $ticket['customer_name'],
			'event_name' => $event['name'],
			'ticket_type' => $ticket_type['name'],
			'ticket_id' => $ticket['ticket_id'],
			'event_date' => date_i18n( 'F j, Y', strtotime( $event['start_datetime'] ) ),
			'event_time' => date_i18n( 'g:i A', strtotime( $event['start_datetime'] ) ),
			'venue' => $event['venue'],
			'address' => $event['address'],
			'qr_url' => $qr_token['verification_url'] ?? '',
			'order_number' => '',
			'allowed_people' => $ticket_type['allowed_people'],
		] );

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		$sent = wp_mail( $ticket['customer_email'], $subject, $message, $headers );

		Woo_Event_Tickets_DB::update( 'tickets', [
			'email_sent' => $sent ? 1 : 0,
			'email_status' => $sent ? 'sent' : 'failed',
			'updated_at' => current_time( 'mysql' ),
		], [ 'id' => $ticket_db_id ] );

		if ( ! $sent ) {
			self::log( $ticket['event_id'], $ticket_db_id, 'email_failed', 'Failed to send ticket email' );
		}

		return $sent;
	}

	/**
	 * Get email template
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public static function get_email_template( $template, $data ) {
		ob_start();
		include WET_PLUGIN_DIR . "includes/emails/{$template}.php";
		return ob_get_clean();
	}

	/**
	 * Generate PDF ticket
	 * @param int $ticket_db_id
	 * @return string|false
	 */
	public static function generate_pdf_ticket( $ticket_db_id ) {
		$ticket = self::get( $ticket_db_id );
		if ( ! $ticket ) {
			return false;
		}

		// Check if TCPDF or Dompdf is available
		if ( ! class_exists( 'TCPDF' ) && ! class_exists( 'Dompdf\Dompdf' ) ) {
			self::log( $ticket['event_id'], $ticket_db_id, 'pdf_failed', 'No PDF library available' );
			return false;
		}

		$ticket_type = Woo_Event_Tickets_Ticket_Types::get( $ticket['ticket_type_id'] );
		$event = Woo_Event_Tickets_Events::get( $ticket['event_id'] );
		$qr_token = Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'qr_tokens' ) . " WHERE ticket_id = %d", [ $ticket_db_id ] );
		$attendee = Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'attendees' ) . " WHERE ticket_id = %d", [ $ticket_db_id ] );

		if ( ! $ticket_type || ! $event ) {
			return false;
		}

		// Generate QR code image
		$qr_image = self::generate_qr_code_image( $qr_token['verification_url'] ?? '' );

		$pdf_path = WET_PLUGIN_DIR . "uploads/tickets/{$ticket['ticket_id']}.pdf";
		
		// Create directory if not exists
		$dir = dirname( $pdf_path );
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		try {
			if ( class_exists( 'TCPDF' ) ) {
				$pdf = new TCPDF( 'P', 'mm', 'A4', true, 'UTF-8', false );
				$pdf->SetCreator( 'Woo Event Tickets' );
				$pdf->SetAuthor( $event['organizer_name'] );
				$pdf->SetTitle( $ticket['ticket_id'] );
				$pdf->SetMargins( 15, 15, 15 );
				$pdf->AddPage();

				// Build HTML content
				$html = self::get_pdf_template( $ticket, $ticket_type, $event, $attendee, $qr_image );
				$pdf->writeHTML( $html, true, false, true, false, '' );
				$pdf->Output( $pdf_path, 'F' );
			} else {
				$dompdf = new Dompdf\Dompdf();
				$html = self::get_pdf_template( $ticket, $ticket_type, $event, $attendee, $qr_image );
				$dompdf->loadHtml( $html );
				$dompdf->setPaper( 'A4', 'portrait' );
				$dompdf->render();
				file_put_contents( $pdf_path, $dompdf->output() );
			}

			Woo_Event_Tickets_DB::update( 'tickets', [
				'pdf_generated' => 1,
				'updated_at' => current_time( 'mysql' ),
			], [ 'id' => $ticket_db_id ] );

			return $pdf_path;
		} catch ( Exception $e ) {
			self::log( $ticket['event_id'], $ticket_db_id, 'pdf_failed', $e->getMessage() );
			return false;
		}
	}

	/**
	 * Generate QR code image
	 * @param string $data
	 * @return string (base64 encoded)
	 */
	public static function generate_qr_code_image( $data ) {
		// Simple QR generation using Google Charts API or similar
		// In production, use a PHP QR library
		$url = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode( $data ) . '&choe=UTF-8';
		return $url;
	}

	/**
	 * Get PDF template
	 * @param array $ticket
	 * @param array $ticket_type
	 * @param array $event
	 * @param array $attendee
	 * @param string $qr_image
	 * @return string
	 */
	public static function get_pdf_template( $ticket, $ticket_type, $event, $attendee, $qr_image ) {
		ob_start();
		include WET_PLUGIN_DIR . 'includes/templates/pdf-ticket.php';
		return ob_get_clean();
	}

	/**
	 * Resend ticket email
	 * @param int $ticket_db_id
	 * @return bool
	 */
	public static function resend_email( $ticket_db_id ) {
		$ticket = self::get( $ticket_db_id );
		if ( ! $ticket ) {
			return false;
		}

		Woo_Event_Tickets_DB::update( 'tickets', [
			'email_status' => 'pending',
			'updated_at' => current_time( 'mysql' ),
		], [ 'id' => $ticket_db_id ] );

		return self::send_ticket_email( $ticket_db_id );
	}

	/**
	 * Regenerate QR code
	 * @param int $ticket_db_id
	 * @return array|false
	 */
	public static function regenerate_qr( $ticket_db_id ) {
		$ticket = self::get( $ticket_db_id );
		if ( ! $ticket ) {
			return false;
		}

		// Invalidate old QR
		Woo_Event_Tickets_DB::update( 'qr_tokens', [
			'status' => 'cancelled',
			'last_regenerated_at' => current_time( 'mysql' ),
		], [ 'ticket_id' => $ticket_db_id ] );

		// Create new QR
		$new_token = self::generate_secure_token();
		$qr_id = self::create_qr_token( $ticket_db_id, $new_token, $ticket['event_id'] );

		// Regenerate PDF
		self::generate_pdf_ticket( $ticket_db_id );

		self::log( $ticket['event_id'], $ticket_db_id, 'qr_regenerated', 'QR code regenerated' );

		return [
			'qr_id' => $qr_id,
			'secure_token' => $new_token,
			'verification_url' => home_url( "/ticket/verify/{$new_token}" ),
		];
	}

	/**
	 * Log action
	 * @param int $event_id
	 * @param int $ticket_id
	 * @param string $action
	 * @param string $description
	 */
	public static function log( $event_id, $ticket_id, $action, $description ) {
		Woo_Event_Tickets_DB::insert( 'logs', [
			'event_id' => $event_id,
			'ticket_id' => $ticket_id,
			'action' => $action,
			'description' => $description,
			'user_id' => get_current_user_id(),
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'created_at' => current_time( 'mysql' ),
		] );
	}
}