<?php
/**
 * Ticket types management class
 */
class Woo_Event_Tickets_Ticket_Types {

	/**
	 * Create a ticket type
	 * @param array $data
	 * @return int|false
	 */
	public static function create( $data ) {
		$defaults = [
			'event_id' => 0,
			'name' => '',
			'slug' => '',
			'description' => '',
			'image_url' => '',
			'regular_price' => 0.00,
			'sale_price' => null,
			'early_bird_price' => null,
			'early_bird_quantity' => 0,
			'early_bird_start' => null,
			'early_bird_end' => null,
			'total_quantity' => 0,
			'remaining_quantity' => 0,
			'max_per_customer' => 10,
			'min_per_order' => 1,
			'validity_days' => 0,
			'event_date' => null,
			'event_time' => null,
			'venue' => '',
			'address' => '',
			'allowed_people' => 1,
			'features' => '',
			'terms_conditions' => '',
			'refund_policy' => '',
			'category' => 'general',
			'color' => '#0066cc',
			'custom_fields' => '',
			'woocommerce_product_id' => null,
			'status' => 'active',
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		];

		$data = wp_parse_args( $data, $defaults );
		$data['slug'] = $data['slug'] ?: sanitize_title( $data['name'] );
		$data['remaining_quantity'] = $data['total_quantity'];

		// Create WooCommerce product if needed
		if ( ! $data['woocommerce_product_id'] && class_exists( 'WooCommerce' ) ) {
			$product_id = self::create_woocommerce_product( $data );
			if ( $product_id ) {
				$data['woocommerce_product_id'] = $product_id;
			}
		}

		return Woo_Event_Tickets_DB::insert( 'ticket_types', $data );
	}

	/**
	 * Update a ticket type
	 * @param int $id
	 * @param array $data
	 * @return int|false
	 */
	public static function update( $id, $data ) {
		$data['updated_at'] = current_time( 'mysql' );

		// Update WooCommerce product
		$existing = self::get( $id );
		if ( $existing && $existing['woocommerce_product_id'] && class_exists( 'WooCommerce' ) ) {
			self::update_woocommerce_product( $existing['woocommerce_product_id'], array_merge( (array) $existing, $data ) );
		}

		return Woo_Event_Tickets_DB::update( 'ticket_types', $data, [ 'id' => $id ] );
	}

	/**
	 * Get ticket type by ID
	 * @param int $id
	 * @return array|null
	 */
	public static function get( $id ) {
		return Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'ticket_types' ) . " WHERE id = %d", [ $id ] );
	}

	/**
	 * Get ticket types by event
	 * @param int $event_id
	 * @param array $args
	 * @return array
	 */
	public static function get_by_event( $event_id, $args = [] ) {
		$defaults = [
			'status' => 'active',
			'orderby' => 'created_at',
			'order' => 'ASC',
		];
		$args = wp_parse_args( $args, $defaults );

		$where = [ 'event_id = %d' ];
		$where_args = [ $event_id ];

		if ( $args['status'] ) {
			$where[] = 'status = %s';
			$where_args[] = $args['status'];
		}

		$query = "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'ticket_types' ) . " WHERE " . implode( ' AND ', $where ) . " ORDER BY {$args['orderby']} {$args['order']}";
		return Woo_Event_Tickets_DB::get_results( $query, $where_args );
	}

	/**
	 * Get ticket type by WooCommerce product ID
	 * @param int $product_id
	 * @return array|null
	 */
	public static function get_by_product_id( $product_id ) {
		return Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'ticket_types' ) . " WHERE woocommerce_product_id = %d", [ $product_id ] );
	}

	/**
	 * Delete ticket type
	 * @param int $id
	 * @return int|false
	 */
	public static function delete( $id ) {
		$type = self::get( $id );
		if ( $type && $type['woocommerce_product_id'] ) {
			wp_delete_post( $type['woocommerce_product_id'], true );
		}
		return Woo_Event_Tickets_DB::delete( 'ticket_types', [ 'id' => $id ] );
	}

	/**
	 * Create WooCommerce product
	 * @param array $data
	 * @return int|false
	 */
	public static function create_woocommerce_product( $data ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		$product = new WC_Product_Simple();
		$product->set_name( $data['name'] );
		$product->set_slug( $data['slug'] );
		$product->set_description( $data['description'] );
		$product->set_short_description( $data['description'] );
		$product->set_regular_price( $data['regular_price'] );
		
		if ( $data['sale_price'] ) {
			$product->set_sale_price( $data['sale_price'] );
		}

		if ( $data['total_quantity'] > 0 ) {
			$product->set_manage_stock( true );
			$product->set_stock_quantity( $data['total_quantity'] );
			$product->set_stock_status( 'instock' );
		}

		$product->set_sold_individually( false );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_virtual( true );
		$product->set_downloadable( false );

		// Store ticket type ID as meta
		$product->save();
		$product_id = $product->get_id();

		if ( $product_id ) {
			update_post_meta( $product_id, '_wet_ticket_type_id', $data['id'] ?? 0 );
			update_post_meta( $product_id, '_wet_event_id', $data['event_id'] ?? 0 );
		}

		return $product_id;
	}

	/**
	 * Update WooCommerce product
	 * @param int $product_id
	 * @param array $data
	 * @return bool
	 */
	public static function update_woocommerce_product( $product_id, $data ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return false;
		}

		$product->set_name( $data['name'] );
		$product->set_slug( $data['slug'] );
		$product->set_description( $data['description'] );
		$product->set_short_description( $data['description'] );
		$product->set_regular_price( $data['regular_price'] );
		
		if ( $data['sale_price'] ) {
			$product->set_sale_price( $data['sale_price'] );
		} else {
			$product->set_sale_price( '' );
		}

		if ( $data['total_quantity'] > 0 ) {
			$product->set_manage_stock( true );
			$product->set_stock_quantity( $data['total_quantity'] );
			$product->set_stock_status( 'instock' );
		} else {
			$product->set_manage_stock( false );
		}

		$product->save();
		return true;
	}

	/**
	 * Get effective price (considers early bird)
	 * @param array $ticket_type
	 * @param int $quantity_sold
	 * @return float
	 */
	public static function get_effective_price( $ticket_type, $quantity_sold = 0 ) {
		$now = current_time( 'mysql' );

		// Check early bird
		if ( $ticket_type['early_bird_price'] 
			&& $ticket_type['early_bird_start'] 
			&& $ticket_type['early_bird_end']
			&& $ticket_type['early_bird_quantity'] > 0
			&& strtotime( $now ) >= strtotime( $ticket_type['early_bird_start'] )
			&& strtotime( $now ) <= strtotime( $ticket_type['early_bird_end'] )
			&& $quantity_sold < $ticket_type['early_bird_quantity'] ) {
			return floatval( $ticket_type['early_bird_price'] );
		}

		// Check sale price
		if ( $ticket_type['sale_price'] && $ticket_type['sale_price'] < $ticket_type['regular_price'] ) {
			return floatval( $ticket_type['sale_price'] );
		}

		return floatval( $ticket_type['regular_price'] );
	}

	/**
	 * Check if early bird is active
	 * @param array $ticket_type
	 * @param int $quantity_sold
	 * @return bool
	 */
	public static function is_early_bird_active( $ticket_type, $quantity_sold = 0 ) {
		$now = current_time( 'mysql' );

		return $ticket_type['early_bird_price']
			&& $ticket_type['early_bird_start']
			&& $ticket_type['early_bird_end']
			&& $ticket_type['early_bird_quantity'] > 0
			&& strtotime( $now ) >= strtotime( $ticket_type['early_bird_start'] )
			&& strtotime( $now ) <= strtotime( $ticket_type['early_bird_end'] )
			&& $quantity_sold < $ticket_type['early_bird_quantity'];
	}

	/**
	 * Get remaining quantity
	 * @param int $ticket_type_id
	 * @return int
	 */
	public static function get_remaining_quantity( $ticket_type_id ) {
		global $wpdb;
		$tickets = Woo_Event_Tickets_DB::table( 'tickets' );

		$sold = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(quantity) FROM {$tickets} WHERE ticket_type_id = %d AND status IN ('active','used')",
			$ticket_type_id
		) );

		$type = self::get( $ticket_type_id );
		if ( ! $type ) {
			return 0;
		}

		return max( 0, intval( $type['total_quantity'] ) - intval( $sold ?: 0 ) );
	}

	/**
	 * Update remaining quantity
	 * @param int $ticket_type_id
	 * @return int|false
	 */
	public static function update_remaining_quantity( $ticket_type_id ) {
		$remaining = self::get_remaining_quantity( $ticket_type_id );
		return self::update( $ticket_type_id, [ 'remaining_quantity' => $remaining ] );
	}
}