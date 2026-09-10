<?php
/**
 * Database abstraction layer for Woo Event Tickets
 */
class Woo_Event_Tickets_DB {

	/**
	 * Get table names
	 * @return array
	 */
	public static function tables() {
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
	 * Get table name
	 * @param string $table
	 * @return string
	 */
	public static function table( $table ) {
		$tables = self::tables();
		return isset( $tables[ $table ] ) ? $tables[ $table ] : $table;
	}

	/**
	 * Insert row
	 * @param string $table
	 * @param array $data
	 * @param array $format
	 * @return int|false
	 */
	public static function insert( $table, $data, $format = null ) {
		global $wpdb;
		return $wpdb->insert( self::table( $table ), $data, $format );
	}

	/**
	 * Update row
	 * @param string $table
	 * @param array $data
	 * @param array $where
	 * @param array $format
	 * @param array $where_format
	 * @return int|false
	 */
	public static function update( $table, $data, $where, $format = null, $where_format = null ) {
		global $wpdb;
		return $wpdb->update( self::table( $table ), $data, $where, $format, $where_format );
	}

	/**
	 * Delete row
	 * @param string $table
	 * @param array $where
	 * @param array $where_format
	 * @return int|false
	 */
	public static function delete( $table, $where, $where_format = null ) {
		global $wpdb;
		return $wpdb->delete( self::table( $table ), $where, $where_format );
	}

	/**
	 * Get row
	 * @param string $query
	 * @param array $params
	 * @return object|null
	 */
	public static function get_row( $query, $params = [] ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( $query, ...array_values( $params ) ) );
	}

	/**
	 * Get results
	 * @param string $query
	 * @param array $params
	 * @return array
	 */
	public static function get_results( $query, $params = [] ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( $query, ...array_values( $params ) ), ARRAY_A );
	}

	/**
	 * Get var
	 * @param string $query
	 * @param array $params
	 * @return mixed
	 */
	public static function get_var( $query, $params = [] ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( $query, ...array_values( $params ) ) );
	}

	/**
	 * Query
	 * @param string $query
	 * @param array $params
	 * @return int|false
	 */
	public static function query( $query, $params = [] ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( $query, ...array_values( $params ) ) );
	}

	/**
	 * Get results with pagination
	 * @param string $table
	 * @param array $args
	 * @return array
	 */
	public static function get_paginated( $table, $args = [] ) {
		global $wpdb;

		$defaults = [
			'where' => '',
			'where_args' => [],
			'orderby' => 'id',
			'order' => 'DESC',
			'per_page' => 20,
			'page' => 1,
			'fields' => '*',
		];

		$args = wp_parse_args( $args, $defaults );
		$offset = ( $args['page'] - 1 ) * $args['per_page' ];

		$where_sql = $args['where'] ? 'WHERE ' . $args['where'] : '';
		$order_sql = 'ORDER BY ' . esc_sql( $args['orderby'] ) . ' ' . esc_sql( $args['order'] );
		$limit_sql = 'LIMIT ' . intval( $args['per_page'] ) . ' OFFSET ' . intval( $offset );

		$count_query = "SELECT COUNT(*) FROM {$table} {$where_sql}";
		$total = $wpdb->get_var( $wpdb->prepare( $count_query, ...array_values( $args['where_args'] ) ) );

		$query = "SELECT {$args['fields']} FROM {$table} {$where_sql} {$order_sql} {$limit_sql}";
		$results = $wpdb->get_results( $wpdb->prepare( $query, ...array_values( $args['where_args'] ) ), ARRAY_A );

		return [
			'items' => $results,
			'total' => intval( $total ),
			'page' => $args['page'],
			'per_page' => $args['per_page'],
			'total_pages' => ceil( $total / $args['per_page'] ),
		];
	}
}