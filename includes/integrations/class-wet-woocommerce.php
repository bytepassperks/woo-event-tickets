<?php
/**
 * WooCommerce integration
 */
class Woo_Event_Tickets_WooCommerce {

	/**
	 * Initialize WooCommerce integration
	 */
	public static function init() {
		// Only run if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Order status changes
		add_action( 'woocommerce_order_status_changed', [ __CLASS__, 'on_order_status_changed' ], 20, 3 );
		add_action( 'woocommerce_order_payment_complete', [ __CLASS__, 'on_order_payment_complete' ], 20, 1 );
		add_action( 'woocommerce_order_cancelled', [ __CLASS__, 'on_order_cancelled' ], 20, 1 );
		add_action( 'woocommerce_order_refunded', [ __CLASS__, 'on_order_refunded' ], 20, 1 );
		add_action( 'woocommerce_order_partially_refunded', [ __CLASS__, 'on_order_refunded' ], 20, 1 );

		// Product save
		add_action( 'woocommerce_process_product_meta_simple', [ __CLASS__, 'save_product_meta' ], 10, 2 );

		// Admin order item actions
		add_action( 'woocommerce_order_item_add_action_buttons', [ __CLASS__, 'order_item_actions' ], 10, 1 );

		// My Account endpoint
		add_action( 'init', [ __CLASS__, 'add_my_account_endpoint' ] );
		add_filter( 'woocommerce_account_menu_items', [ __CLASS__, 'add_my_account_menu' ] );
		add_action( 'woocommerce_account_my-tickets_endpoint', [ __CLASS__, 'my_account_tickets_content' ] );

		// Email hooks
		add_filter( 'woocommerce_email_attachments', [ __CLASS__, 'attach_ticket_pdf' ], 10, 4 );

		// HPOS compatibility
		if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ __CLASS__, 'modify_orders_query' ], 10, 2 );
		}
	}

	/**
	 * Handle order status change
	 * @param int $order_id
	 * @param string $old_status
	 * @param string $new_status
	 * @param WC_Order $order
	 */
	public static function on_order_status_changed( $order_id, $old_status, $new_status, $order = null ) {
		if ( ! $order ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		// Get configured statuses that trigger ticket generation
		$trigger_statuses = get_option( 'wet_ticket_generation_statuses', [ 'processing', 'completed' ] );

		if ( in_array( $new_status, $trigger_statuses, true ) ) {
			self::generate_tickets_for_order( $order );
		}

		// Handle cancellation/refund
		if ( in_array( $new_status, [ 'cancelled', 'refunded', 'failed' ], true ) ) {
			self::handle_order_cancellation( $order, $new_status );
		}
	}

	/**
	 * Handle order payment complete
	 * @param int $order_id
	 */
	public static function on_order_payment_complete( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			self::generate_tickets_for_order( $order );
		}
	}

	/**
	 * Handle order cancellation
	 * @param int $order_id
	 */
	public static function on_order_cancelled( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			self::handle_order_cancellation( $order, 'cancelled' );
		}
	}

	/**
	 * Handle order refund
	 * @param int $order_id
	 */
	public static function on_order_refunded( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			self::handle_order_cancellation( $order, 'refunded' );
		}
	}

	/**
	 * Generate tickets for order
	 * @param WC_Order $order
	 */
	public static function generate_tickets_for_order( $order ) {
		// Check if tickets already generated (idempotent)
		$existing = get_post_meta( $order->get_id(), '_wet_tickets_generated', true );
		if ( $existing === 'yes' ) {
			return;
		}

		$items = $order->get_items();
		$generated = false;

		foreach ( $items as $item ) {
			if ( $item->get_type() !== 'line_item' ) {
				continue;
			}

			$result = Woo_Event_Tickets_Tickets::create_from_order( $order, $item );
			if ( $result ) {
				$generated = true;
			}
		}

		if ( $generated ) {
			update_post_meta( $order->get_id(), '_wet_tickets_generated', 'yes' );
			update_post_meta( $order->get_id(), '_wet_tickets_generated_at', current_time( 'mysql' ) );
		}
	}

	/**
	 * Handle order cancellation/refund
	 * @param WC_Order $order
	 * @param string $new_status
	 */
	public static function handle_order_cancellation( $order, $new_status ) {
		$tickets = Woo_Event_Tickets_Tickets::get_by_order( $order->get_id() );

		foreach ( $tickets as $ticket ) {
			if ( $ticket['status'] === 'active' || $ticket['status'] === 'pending' ) {
				$status = $new_status === 'refunded' ? 'refunded' : 'cancelled';
				
				Woo_Event_Tickets_Tickets::update_status( $ticket['id'], $status );

				// Invalidate QR
				Woo_Event_Tickets_DB::update( 'qr_tokens', [
					'status' => $status,
				], [ 'ticket_id' => $ticket['id'] ] );

				// Update ticket type remaining
				Woo_Event_Tickets_Ticket_Types::update_remaining_quantity( $ticket['ticket_type_id'] );
			}
		}

		// If refund is reversed, we could regenerate (configurable)
		if ( $new_status === 'refunded' ) {
			update_post_meta( $order->get_id(), '_wet_tickets_refunded', 'yes' );
		}
	}

	/**
	 * Save product meta
	 * @param int $post_id
	 * @param WC_Product $product
	 */
	public static function save_product_meta( $post_id, $product ) {
		// Save ticket type ID if this is a ticket product
		if ( isset( $_POST['_wet_ticket_type_id'] ) ) {
			update_post_meta( $post_id, '_wet_ticket_type_id', sanitize_text_field( $_POST['_wet_ticket_type_id'] ) );
		}
		if ( isset( $_POST['_wet_event_id'] ) ) {
			update_post_meta( $post_id, '_wet_event_id', sanitize_text_field( $_POST['_wet_event_id'] ) );
		}
	}

	/**
	 * Add custom actions to order items
	 * @param WC_Order $order
	 */
	public static function order_item_actions( $order ) {
		$tickets = Woo_Event_Tickets_Tickets::get_by_order( $order->get_id() );
		if ( empty( $tickets ) ) {
			return;
		}

		?>
		<div class="wet-order-actions">
			<button type="button" class="button wet-resend-tickets" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
				<?php esc_html_e( 'Resend Tickets', 'wootickets' ); ?>
			</button>
			<button type="button" class="button wet-regenerate-qr" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
				<?php esc_html_e( 'Regenerate QR Codes', 'wootickets' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Add My Account endpoint
	 */
	public static function add_my_account_endpoint() {
		add_rewrite_endpoint( 'my-tickets', EP_ROOT | EP_PAGES );
	}

	/**
	 * Add My Account menu item
	 * @param array $items
	 * @return array
	 */
	public static function add_my_account_menu( $items ) {
		$new_items = [];
		foreach ( $items as $key => $value ) {
			$new_items[ $key ] = $value;
			if ( $key === 'orders' ) {
				$new_items['my-tickets'] = __( 'My Tickets', 'wootickets' );
			}
		}
		return $new_items;
	}

	/**
	 * My Account tickets content
	 */
	public static function my_account_tickets_content() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$customer_id = get_current_user_id();
		$tickets = self::get_customer_tickets( $customer_id );

		wc_get_template( 'myaccount/my-tickets.php', [
			'tickets' => $tickets,
		], 'wootickets', WET_PLUGIN_DIR . 'templates/' );
	}

	/**
	 * Get customer tickets
	 * @param int $customer_id
	 * @return array
	 */
	public static function get_customer_tickets( $customer_id ) {
		global $wpdb;
		$tickets_table = Woo_Event_Tickets_DB::table( 'tickets' );
		$events_table = Woo_Event_Tickets_DB::table( 'events' );
		$types_table = Woo_Event_Tickets_DB::table( 'ticket_types' );

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT t.*, e.name as event_name, e.start_datetime, e.end_datetime, e.venue, e.address, tt.name as ticket_type_name, tt.allowed_people
			FROM {$tickets_table} t
			LEFT JOIN {$events_table} e ON t.event_id = e.id
			LEFT JOIN {$types_table} tt ON t.ticket_type_id = tt.id
			WHERE t.customer_id = %d
			ORDER BY t.created_at DESC",
			$customer_id
		), ARRAY_A );
	}

	/**
	 * Attach ticket PDF to WooCommerce emails
	 * @param array $attachments
	 * @param int $email_id
	 * @param WC_Order $order
	 * @param WC_Email $email
	 * @return array
	 */
	public static function attach_ticket_pdf( $attachments, $email_id, $order, $email ) {
		// Only attach to customer completed order email
		if ( $email_id !== 'customer_completed_order' ) {
			return $attachments;
		}

		$tickets = Woo_Event_Tickets_Tickets::get_by_order( $order->get_id() );
		foreach ( $tickets as $ticket ) {
			$pdf_path = WET_PLUGIN_DIR . "uploads/tickets/{$ticket['ticket_id']}.pdf";
			if ( file_exists( $pdf_path ) ) {
				$attachments[] = $pdf_path;
			}
		}

		return $attachments;
	}

	/**
	 * Modify orders query for HPOS
	 * @param array $query
	 * @param array $args
	 * @return array
	 */
	public static function modify_orders_query( $query, $args ) {
		// Add any custom query modifications for HPOS
		return $query;
	}

	/**
	 * Check if product is a ticket product
	 * @param int $product_id
	 * @return bool
	 */
	public static function is_ticket_product( $product_id ) {
		$ticket_type = Woo_Event_Tickets_Ticket_Types::get_by_product_id( $product_id );
		return $ticket_type !== null;
	}

	/**
	 * Get ticket type for product
	 * @param int $product_id
	 * @return array|null
	 */
	public static function get_ticket_type_for_product( $product_id ) {
		return Woo_Event_Tickets_Ticket_Types::get_by_product_id( $product_id );
	}

	/**
	 * Sync ticket type with product
	 * @param int $ticket_type_id
	 * @return bool
	 */
	public static function sync_ticket_type( $ticket_type_id ) {
		$type = Woo_Event_Tickets_Ticket_Types::get( $ticket_type_id );
		if ( ! $type || ! $type['woocommerce_product_id'] ) {
			return false;
		}

		return Woo_Event_Tickets_Ticket_Types::update_woocommerce_product( $type['woocommerce_product_id'], (array) $type );
	}
}

// Initialize WooCommerce integration
add_action( 'woocommerce_init', [ 'Woo_Event_Tickets_WooCommerce', 'init' ] );