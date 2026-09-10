<?php
/**
 * QR Scanner class
 */
class Woo_Event_Tickets_Scanner {

	/**
	 * Get scanner page content
	 * @param int $event_id
	 * @return string
	 */
	public static function get_scanner_page( $event_id = 0 ) {
		$event = $event_id ? Woo_Event_Tickets_Events::get( $event_id ) : null;
		$events = Woo_Event_Tickets_Events::get_all( [ 'status' => 'published' ] );

		ob_start();
		include WET_PLUGIN_DIR . 'includes/templates/scanner.php';
		return ob_get_clean();
	}

	/**
	 * Get scanner stats for event day
	 * @param int $event_id
	 * @return array
	 */
	public static function get_event_day_stats( $event_id ) {
		$checkin_stats = Woo_Event_Tickets_Checkin::get_stats( $event_id );
		$event_stats = Woo_Event_Tickets_Events::get_stats( $event_id );

		return array_merge( $event_stats, $checkin_stats );
	}

	/**
	 * Get recent scans for display
	 * @param int $event_id
	 * @param int $limit
	 * @return array
	 */
	public static function get_recent_scans( $event_id, $limit = 10 ) {
		return Woo_Event_Tickets_Checkin::get_recent( $event_id, $limit );
	}

	/**
	 * Validate scanner access
	 * @return bool
	 */
	public static function validate_access() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user_id = get_current_user_id();
		return Woo_Event_Tickets_Staff::is_staff_for_event( $user_id );
	}

	/**
	 * Get scanner settings
	 * @return array
	 */
	public static function get_settings() {
		return [
			'sound_enabled' => get_option( 'wet_scanner_sound', 'yes' ) === 'yes',
			'vibration_enabled' => get_option( 'wet_scanner_vibration', 'yes' ) === 'yes',
			'auto_next' => get_option( 'wet_scanner_auto_next', 'yes' ) === 'yes',
			'manual_verification' => get_option( 'wet_scanner_manual', 'yes' ) === 'yes',
			'success_sound' => get_option( 'wet_scanner_success_sound', 'success' ),
			'error_sound' => get_option( 'wet_scanner_error_sound', 'error' ),
		];
	}
}