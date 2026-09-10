<?php
/**
 * Events management class
 */
class Woo_Event_Tickets_Events {

	/**
	 * Create an event
	 * @param array $data
	 * @return int|false
	 */
	public static function create( $data ) {
		$defaults = [
			'name' => '',
			'slug' => '',
			'description' => '',
			'banner_url' => '',
			'logo_url' => '',
			'start_datetime' => current_time( 'mysql' ),
			'end_datetime' => current_time( 'mysql', true ),
			'venue' => '',
			'address' => '',
			'city' => '',
			'state' => '',
			'country' => '',
			'postal_code' => '',
			'capacity' => 0,
			'status' => 'draft',
			'timezone' => get_option( 'timezone_string', 'UTC' ),
			'primary_color' => '#0066cc',
			'secondary_color' => '#004499',
			'footer_text' => '',
			'organizer_name' => '',
			'organizer_email' => '',
			'organizer_phone' => '',
			'website_url' => '',
			'social_links' => '',
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		];

		$data = wp_parse_args( $data, $defaults );
		$data['slug'] = $data['slug'] ?: sanitize_title( $data['name'] );

		return Woo_Event_Tickets_DB::insert( 'events', $data );
	}

	/**
	 * Update an event
	 * @param int $id
	 * @param array $data
	 * @return int|false
	 */
	public static function update( $id, $data ) {
		$data['updated_at'] = current_time( 'mysql' );
		return Woo_Event_Tickets_DB::update( 'events', $data, [ 'id' => $id ] );
	}

	/**
	 * Get event by ID
	 * @param int $id
	 * @return array|null
	 */
	public static function get( $id ) {
		return Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'events' ) . " WHERE id = %d", [ $id ] );
	}

	/**
	 * Get event by slug
	 * @param string $slug
	 * @return array|null
	 */
	public static function get_by_slug( $slug ) {
		return Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'events' ) . " WHERE slug = %s", [ $slug ] );
	}

	/**
	 * Get all events
	 * @param array $args
	 * @return array
	 */
	public static function get_all( $args = [] ) {
		$defaults = [
			'status' => '',
			'orderby' => 'start_datetime',
			'order' => 'ASC',
			'per_page' => -1,
			'page' => 1,
		];
		$args = wp_parse_args( $args, $defaults );

		$where = [];
		$where_args = [];

		if ( $args['status'] ) {
			$where[] = 'status = %s';
			$where_args[] = $args['status'];
		}

		$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

		if ( $args['per_page'] > 0 ) {
			return Woo_Event_Tickets_DB::get_paginated( Woo_Event_Tickets_DB::table( 'events' ), [
				'where' => $where_sql,
				'where_args' => $where_args,
				'orderby' => $args['orderby'],
				'order' => $args['order'],
				'per_page' => $args['per_page'],
				'page' => $args['page'],
			] );
		}

		$query = "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'events' ) . " {$where_sql} ORDER BY {$args['orderby']} {$args['order']}";
		return Woo_Event_Tickets_DB::get_results( $query, $where_args );
	}

	/**
	 * Delete event
	 * @param int $id
	 * @return int|false
	 */
	public static function delete( $id ) {
		return Woo_Event_Tickets_DB::delete( 'events', [ 'id' => $id ] );
	}

	/**
	 * Get event statistics
	 * @param int $event_id
	 * @return array
	 */
	public static function get_stats( $event_id ) {
		global $wpdb;

		$ticket_types = Woo_Event_Tickets_DB::table( 'ticket_types' );
		$tickets = Woo_Event_Tickets_DB::table( 'tickets' );
		$checkins = Woo_Event_Tickets_DB::table( 'checkins' );

		$total_sold = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(quantity) FROM {$tickets} WHERE event_id = %d AND status IN ('active','used')",
			$event_id
		) );

		$checked_in = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$checkins} WHERE event_id = %d AND result = 'valid'",
			$event_id
		) );

		$total_tickets = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(total_quantity) FROM {$ticket_types} WHERE event_id = %d",
			$event_id
		) );

		$total_revenue = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(price_paid * quantity) FROM {$tickets} WHERE event_id = %d AND status IN ('active','used')",
			$event_id
		) );

		$today_checkins = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$checkins} WHERE event_id = %d AND DATE(scanned_at) = CURDATE() AND result = 'valid'",
			$event_id
		) );

		return [
			'total_sold' => intval( $total_sold ?: 0 ),
			'checked_in' => intval( $checked_in ?: 0 ),
			'total_tickets' => intval( $total_tickets ?: 0 ),
			'remaining' => max( 0, intval( $total_tickets ?: 0 ) - intval( $total_sold ?: 0 ) ),
			'total_revenue' => floatval( $total_revenue ?: 0 ),
			'today_checkins' => intval( $today_checkins ?: 0 ),
		];
	}

	/**
	 * Get events for dropdown
	 * @return array
	 */
	public static function get_dropdown_options() {
		$events = self::get_all( [ 'status' => 'published', 'orderby' => 'start_datetime', 'order' => 'ASC' ] );
		$options = [];
		foreach ( $events as $event ) {
			$options[ $event['id'] ] = $event['name'] . ' (' . date_i18n( 'M j, Y', strtotime( $event['start_datetime'] ) ) . ')';
		}
		return $options;
	}
}