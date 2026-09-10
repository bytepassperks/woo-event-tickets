<?php
/**
 * Early bird pricing management
 */
class Woo_Event_Tickets_Early_Bird {

	/**
	 * Check and update early bird status for an event
	 * @param array $event
	 */
	public static function check_event( $event ) {
		$ticket_types = Woo_Event_Tickets_Ticket_Types::get_by_event( $event['id'] );
		$now = current_time( 'mysql' );

		foreach ( $ticket_types as $type ) {
			if ( ! $type['early_bird_price'] || ! $type['early_bird_start'] || ! $type['early_bird_end'] || $type['early_bird_quantity'] <= 0 ) {
				continue;
			}

			$is_active = strtotime( $now ) >= strtotime( $type['early_bird_start'] )
				&& strtotime( $now ) <= strtotime( $type['early_bird_end'] );

			// Get sold count for early bird
			$early_bird_sold = self::get_early_bird_sold( $type['id'] );

			$was_active = get_post_meta( $type['woocommerce_product_id'] ?? 0, '_wet_early_bird_active', true ) === 'yes';
			$should_be_active = $is_active && $early_bird_sold < $type['early_bird_quantity'];

			if ( $was_active !== $should_be_active ) {
				update_post_meta( $type['woocommerce_product_id'] ?? 0, '_wet_early_bird_active', $should_be_active ? 'yes' : 'no' );
				
				if ( $should_be_active ) {
					// Switch to early bird price
					self::apply_early_bird_price( $type );
				} else {
					// Switch back to regular price
					self::restore_regular_price( $type );
				}
			}
		}
	}

	/**
	 * Get early bird sold count
	 * @param int $ticket_type_id
	 * @return int
	 */
	public static function get_early_bird_sold( $ticket_type_id ) {
		global $wpdb;
		$tickets = Woo_Event_Tickets_DB::table( 'tickets' );
		$orders = $wpdb->prefix . 'wc_orders';

		// Count tickets sold during early bird period with early bird price
		// This is a simplified version - in reality you'd track this separately
		$type = Woo_Event_Tickets_Ticket_Types::get( $ticket_type_id );
		if ( ! $type || ! $type['early_bird_start'] || ! $type['early_bird_end'] ) {
			return 0;
		}

		return $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(quantity) FROM {$tickets} WHERE ticket_type_id = %d AND purchase_date BETWEEN %s AND %s AND status IN ('active','used')",
			$ticket_type_id,
			$type['early_bird_start'],
			$type['early_bird_end']
		) ) ?: 0;
	}

	/**
	 * Apply early bird price to WooCommerce product
	 * @param array $ticket_type
	 */
	public static function apply_early_bird_price( $ticket_type ) {
		if ( ! class_exists( 'WooCommerce' ) || ! $ticket_type['woocommerce_product_id'] ) {
			return;
		}

		$product = wc_get_product( $ticket_type['woocommerce_product_id'] );
		if ( ! $product ) {
			return;
		}

		$product->set_regular_price( $ticket_type['early_bird_price'] );
		$product->set_sale_price( '' );
		$product->save();

		// Update ticket type remaining with early bird quantity
		Woo_Event_Tickets_DB::update( 'ticket_types', [
			'remaining_quantity' => $ticket_type['early_bird_quantity'] - self::get_early_bird_sold( $ticket_type['id'] ),
			'updated_at' => current_time( 'mysql' ),
		], [ 'id' => $ticket_type['id'] ] );
	}

	/**
	 * Restore regular price to WooCommerce product
	 * @param array $ticket_type
	 */
	public static function restore_regular_price( $ticket_type ) {
		if ( ! class_exists( 'WooCommerce' ) || ! $ticket_type['woocommerce_product_id'] ) {
			return;
		}

		$product = wc_get_product( $ticket_type['woocommerce_product_id'] );
		if ( ! $product ) {
			return;
		}

		$product->set_regular_price( $ticket_type['regular_price'] );
		if ( $ticket_type['sale_price'] && $ticket_type['sale_price'] < $ticket_type['regular_price'] ) {
			$product->set_sale_price( $ticket_type['sale_price'] );
		}
		$product->save();

		// Restore remaining to total remaining
		Woo_Event_Tickets_Ticket_Types::update_remaining_quantity( $ticket_type['id'] );
	}

	/**
	 * Get effective price for display
	 * @param array $ticket_type
	 * @return array
	 */
	public static function get_display_price( $ticket_type ) {
		$now = current_time( 'mysql' );
		$early_bird_sold = self::get_early_bird_sold( $ticket_type['id'] );

		$is_early_bird = $ticket_type['early_bird_price']
			&& $ticket_type['early_bird_start']
			&& $ticket_type['early_bird_end']
			&& $ticket_type['early_bird_quantity'] > 0
			&& strtotime( $now ) >= strtotime( $ticket_type['early_bird_start'] )
			&& strtotime( $now ) <= strtotime( $ticket_type['early_bird_end'] )
			&& $early_bird_sold < $ticket_type['early_bird_quantity'];

		if ( $is_early_bird ) {
			return [
				'price' => $ticket_type['early_bird_price'],
				'original_price' => $ticket_type['regular_price'],
				'is_early_bird' => true,
				'early_bird_remaining' => max( 0, $ticket_type['early_bird_quantity'] - $early_bird_sold ),
			];
		}

		if ( $ticket_type['sale_price'] && $ticket_type['sale_price'] < $ticket_type['regular_price'] ) {
			return [
				'price' => $ticket_type['sale_price'],
				'original_price' => $ticket_type['regular_price'],
				'is_early_bird' => false,
				'is_sale' => true,
			];
		}

		return [
			'price' => $ticket_type['regular_price'],
			'original_price' => $ticket_type['regular_price'],
			'is_early_bird' => false,
		];
	}
}