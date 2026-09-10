<?php
/**
 * Frontend shortcodes
 */
class Woo_Event_Tickets_Shortcodes {

	/**
	 * Initialize shortcodes
	 */
	public static function init() {
		add_shortcode( 'ticket_events', [ __CLASS__, 'shortcode_ticket_events' ] );
		add_shortcode( 'ticket_event', [ __CLASS__, 'shortcode_ticket_event' ] );
		add_shortcode( 'ticket_types', [ __CLASS__, 'shortcode_ticket_types' ] );
		add_shortcode( 'my_tickets', [ __CLASS__, 'shortcode_my_tickets' ] );
		add_shortcode( 'ticket_verify', [ __CLASS__, 'shortcode_ticket_verify' ] );
		add_shortcode( 'ticket_scanner', [ __CLASS__, 'shortcode_ticket_scanner' ] );
	}

	/**
	 * Shortcode: [ticket_events]
	 * @param array $atts
	 * @return string
	 */
	public static function shortcode_ticket_events( $atts ) {
		$atts = shortcode_atts( [
			'status' => 'published',
			'limit' => -1,
			'columns' => 3,
			'show_date' => 'yes',
			'show_venue' => 'yes',
		], $atts );

		$events = Woo_Event_Tickets_Events::get_all( [
			'status' => $atts['status'],
			'orderby' => 'start_datetime',
			'order' => 'ASC',
			'per_page' => intval( $atts['limit'] ),
		] );

		if ( empty( $events ) ) {
			return '<p class="wet-no-events">' . esc_html__( 'No events found.', 'wootickets' ) . '</p>';
		}

		ob_start();
		include WET_PLUGIN_DIR . 'includes/templates/shortcode-events.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode: [ticket_event id="123"]
	 * @param array $atts
	 * @return string
	 */
	public static function shortcode_ticket_event( $atts ) {
		$atts = shortcode_atts( [
			'id' => 0,
			'slug' => '',
			'show_description' => 'yes',
			'show_ticket_types' => 'yes',
		], $atts );

		$event = $atts['id'] ? Woo_Event_Tickets_Events::get( intval( $atts['id'] ) ) : null;
		if ( ! $event && $atts['slug'] ) {
			$event = Woo_Event_Tickets_Events::get_by_slug( $atts['slug'] );
		}

		if ( ! $event ) {
			return '<p class="wet-no-event">' . esc_html__( 'Event not found.', 'wootickets' ) . '</p>';
		}

		$ticket_types = Woo_Event_Tickets_Ticket_Types::get_by_event( $event['id'] );

		ob_start();
		include WET_PLUGIN_DIR . 'includes/templates/shortcode-event.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode: [ticket_types event_id="123"]
	 * @param array $atts
	 * @return string
	 */
	public static function shortcode_ticket_types( $atts ) {
		$atts = shortcode_atts( [
			'event_id' => 0,
			'show_features' => 'yes',
			'show_early_bird' => 'yes',
		], $atts );

		if ( ! $atts['event_id'] ) {
			return '<p class="wet-error">' . esc_html__( 'Event ID is required.', 'wootickets' ) . '</p>';
		}

		$ticket_types = Woo_Event_Tickets_Ticket_Types::get_by_event( intval( $atts['event_id'] ) );

		if ( empty( $ticket_types ) ) {
			return '<p class="wet-no-tickets">' . esc_html__( 'No ticket types available.', 'wootickets' ) . '</p>';
		}

		ob_start();
		include WET_PLUGIN_DIR . 'includes/templates/shortcode-ticket-types.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode: [my_tickets]
	 * @param array $atts
	 * @return string
	 */
	public static function shortcode_my_tickets( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '<p class="wet-login-required">' . esc_html__( 'Please log in to view your tickets.', 'wootickets' ) . '</p>';
		}

		$customer_id = get_current_user_id();
		$tickets = Woo_Event_Tickets_WooCommerce::get_customer_tickets( $customer_id );

		ob_start();
		include WET_PLUGIN_DIR . 'includes/templates/shortcode-my-tickets.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode: [ticket_verify]
	 * @param array $atts
	 * @return string
	 */
	public static function shortcode_ticket_verify( $atts ) {
		ob_start();
		include WET_PLUGIN_DIR . 'includes/templates/shortcode-verify.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode: [ticket_scanner]
	 * @param array $atts
	 * @return string
	 */
	public static function shortcode_ticket_scanner( $atts ) {
		if ( ! Woo_Event_Tickets_Scanner::validate_access() ) {
			return '<p class="wet-access-denied">' . esc_html__( 'Access denied. Staff only.', 'wootickets' ) . '</p>';
		}

		$atts = shortcode_atts( [
			'event_id' => 0,
		], $atts );

		return Woo_Event_Tickets_Scanner::get_scanner_page( intval( $atts['event_id'] ) );
	}
}

// Initialize shortcodes
Woo_Event_Tickets_Shortcodes::init();