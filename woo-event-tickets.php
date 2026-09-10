<?php
/**
 * Plugin Name: Woo Event Tickets
 * Plugin URI: https://warpfix.org/woo-event-tickets
 * Description: Production-ready WordPress + WooCommerce Event Ticketing & QR Verification Plugin
 * Version: 1.0.0
 * Author: Hardy Lebsack
 * Author URI: https://warpfix.org
 * Text Domain: wootickets
 * Domain Path: /languages
 * WC Version: 8.0+
 * WP Version: 6.5+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define plugin constants
 */
define( 'WET_VERSION', '1.0.0' );
define( 'WET_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WET_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class
 */
final class Woo_Event_Tickets {

	/**
	 * Plugin instance
	 * @var Woo_Event_Tickets|null
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 * @return Woo_Event_Tickets
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->define_constants();
		$this->load_dependencies();
		$this->register_hooks();
		$this->init();
	}

	/**
	 * Define constants
	 */
	private function define_constants() {
		// Already defined above
	}

	/**
	 * Load dependencies
	 */
	private function load_dependencies() {
		// Autoloader
		spl_autoload_register( [ $this, 'autoload' ] );

		// Load core classes
		require_once WET_PLUGIN_DIR . 'includes/db/class-wet-db.php';
		require_once WET_PLUGIN_DIR . 'includes/db/installer.php';
		require_once WET_PLUGIN_DIR . 'includes/class-wet-events.php';
		require_once WET_PLUGIN_DIR . 'includes/class-wet-ticket-types.php';
		require_once WET_PLUGIN_DIR . 'includes/class-wet-tickets.php';
		require_once WET_PLUGIN_DIR . 'includes/class-wet-checkin.php';
		require_once WET_PLUGIN_DIR . 'includes/class-wet-early-bird.php';
		require_once WET_PLUGIN_DIR . 'includes/class-wet-staff.php';
		require_once WET_PLUGIN_DIR . 'includes/class-wet-scanner.php';
		require_once WET_PLUGIN_DIR . 'includes/integrations/class-wet-woocommerce.php';
		require_once WET_PLUGIN_DIR . 'includes/shortcodes/class-wet-shortcodes.php';
		require_once WET_PLUGIN_DIR . 'includes/admin/class-wet-admin.php';
		require_once WET_PLUGIN_DIR . 'includes/widgets.php';
		require_once WET_PLUGIN_DIR . 'includes/api/endpoints.php';
	}

	/**
	 * Autoloader
	 * @param string $class_name
	 */
	public function autoload( $class_name ) {
		// Check if class is in our namespace
		if ( strpos( $class_name, 'Woo_Event_Tickets' ) !== 0 ) {
			return;
		}

		$class_file = WET_PLUGIN_DIR . 'includes/' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';
		
		// Try different naming conventions
		$possible_files = [
			$class_file,
			WET_PLUGIN_DIR . 'includes/' . str_replace( '\\', '/', lcfirst( $class_name ) ) . '.php',
			WET_PLUGIN_DIR . 'includes/class-' . strtolower( str_replace( 'Woo_Event_Tickets_', '', $class_name ) ) . '.php',
		];

		foreach ( $possible_files as $file ) {
			if ( file_exists( $file ) ) {
				require_once $file;
				return;
			}
		}
	}

	/**
	 * Register hooks
	 */
	private function register_hooks() {
		// Activation/Deactivation
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

		// Admin
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		// Frontend
		add_shortcode( 'ticket_events', [ 'Woo_Event_Tickets_Shortcodes', 'shortcode_ticket_events' ] );
		add_shortcode( 'ticket_event', [ 'Woo_Event_Tickets_Shortcodes', 'shortcode_ticket_event' ] );
		add_shortcode( 'ticket_types', [ 'Woo_Event_Tickets_Shortcodes', 'shortcode_ticket_types' ] );
		add_shortcode( 'my_tickets', [ 'Woo_Event_Tickets_Shortcodes', 'shortcode_my_tickets' ] );
		add_shortcode( 'ticket_verify', [ 'Woo_Event_Tickets_Shortcodes', 'shortcode_ticket_verify' ] );
		add_shortcode( 'ticket_scanner', [ 'Woo_Event_Tickets_Shortcodes', 'shortcode_ticket_scanner' ] );

		// REST API
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		// AJAX
		add_action( 'wp_ajax_wet_check_in', [ $this, 'ajax_check_in' ] );
		add_action( 'wp_ajax_nopriv_wet_check_in', [ $this, 'ajax_check_in' ] );

		// Widgets
		add_action( 'widgets_init', [ $this, 'register_widgets' ] );

		// Cron
		add_action( 'wet_early_bird_check', [ 'Woo_Event_Tickets_Early_Bird', 'check_all_events' ] );

		// Init
		add_action( 'init', [ $this, 'on_init' ] );
	}

	/**
	 * Initialize plugin
	 */
	public function init() {
		// Load text domain
		load_plugin_textdomain( 'wootickets', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * On init - register capabilities, rewrite rules
	 */
	public function on_init() {
		// Register capabilities
		Woo_Event_Tickets_Staff::register_capabilities();

		// Add rewrite endpoint for ticket verification
		add_rewrite_endpoint( 'ticket-verify', EP_ROOT | EP_PAGES );
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		$installer = new Woo_Event_Tickets_Installer();
		$installer->create_tables();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Schedule early bird check cron
		if ( ! wp_next_scheduled( 'wet_early_bird_check' ) ) {
			wp_schedule_event( time(), 'hourly', 'wet_early_bird_check' );
		}

		// Create upload directory
		$upload_dir = WET_PLUGIN_DIR . 'uploads/tickets';
		if ( ! file_exists( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		wp_clear_scheduled_hook( 'wet_early_bird_check' );
		flush_rewrite_rules();
	}

	/**
	 * Admin menu
	 */
	public function admin_menu() {
		$capability = 'manage_woo_event_tickets';

		add_menu_page(
			__( 'Event Tickets', 'wootickets' ),
			__( 'Event Tickets', 'wootickets' ),
			$capability,
			'woo-event-tickets',
			[ $this, 'admin_page_index' ],
			'dashicons-tag',
			20
		);

		// Submenus
		$submenus = [
			'dashboard' => [ __( 'Dashboard', 'wootickets' ), 'admin_page_dashboard' ],
			'events' => [ __( 'Events', 'wootickets' ), 'admin_page_events' ],
			'ticket-types' => [ __( 'Ticket Types', 'wootickets' ), 'admin_page_ticket_types' ],
			'tickets' => [ __( 'Tickets', 'wootickets' ), 'admin_page_tickets' ],
			'scanner' => [ __( 'Scanner', 'wootickets' ), 'admin_page_scanner' ],
			'checkins' => [ __( 'Check-ins', 'wootickets' ), 'admin_page_checkins' ],
			'staff' => [ __( 'Staff', 'wootickets' ), 'admin_page_staff' ],
			'reports' => [ __( 'Reports', 'wootickets' ), 'admin_page_reports' ],
			'settings' => [ __( 'Settings', 'wootickets' ), 'admin_page_settings' ],
		];

		foreach ( $submenus as $slug => $data ) {
			add_submenu_page(
				'woo-event-tickets',
				$data[0],
				$data[0],
				$capability,
				'woo-event-tickets-' . $slug,
				[ 'Woo_Event_Tickets_Admin', $data[1] ]
			);
		}
	}

	/**
	 * Admin index page (redirect to dashboard)
	 */
	public function admin_page_index() {
		wp_redirect( admin_url( 'admin.php?page=woo-event-tickets-dashboard' ) );
		exit;
	}

	/**
	 * Admin enqueue scripts
	 */
	public function admin_enqueue_scripts( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'woo-event-tickets' ) === false ) {
			return;
		}

		wp_enqueue_style( 'woo-event-tickets-admin', WET_PLUGIN_URL . 'assets/css/admin.css', [], WET_VERSION );
		wp_enqueue_script( 'woo-event-tickets-admin', WET_PLUGIN_URL . 'assets/js/admin.js', [ 'jquery' ], WET_VERSION, true );

		// Load Select2
		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', [ 'jquery' ], '4.0.13', true );
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], '4.0.13' );

		// Load SweetAlert2
		wp_enqueue_script( 'sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], '11.0.17', true );
		wp_enqueue_style( 'sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', [], '11.0.17' );

		// Localize script
		wp_localize_script( 'woo-event-tickets-admin', 'wet_admin_obj', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wet_ajax_nonce' ),
			'version' => WET_VERSION,
			'i18n' => [
				'select_action' => __( 'Please select an action', 'wootickets' ),
				'no_items_selected' => __( 'No items selected', 'wootickets' ),
				'confirm_delete' => __( 'Are you sure you want to delete this item?', 'wootickets' ),
				'confirm_cancel' => __( 'Are you sure you want to cancel the selected tickets? This cannot be undone.', 'wootickets' ),
				'confirm_regenerate' => __( 'Are you sure you want to regenerate QR codes for selected tickets? Previous QR codes will be invalidated.', 'wootickets' ),
				'fill_required' => __( 'Please fill in all required fields', 'wootickets' ),
				'sending' => __( 'Sending...', 'wootickets' ),
				'sent' => __( 'Sent!', 'wootickets' ),
				'resend' => __( 'Resend', 'wootickets' ),
				'ticket_resent' => __( 'Ticket resent successfully', 'wootickets' ),
				'syncing' => __( 'Syncing...', 'wootickets' ),
				'synced' => __( 'Synced successfully!', 'wootickets' ),
				'sync' => __( 'Sync with WooCommerce', 'wootickets' ),
				'copied' => __( 'Copied to clipboard!', 'wootickets' ),
			],
		] );
	}

	/**
	 * Register REST API routes
	 */
	public function register_rest_routes() {
		// Public verification endpoint
		register_rest_route( 'woo-tickets/v1', '/verify/(?P<token>[a-z0-9_-]+)', [
			'methods' => 'GET',
			'callback' => 'wet_rest_verify_ticket',
			'permission_callback' => '__return_true',
			'args' => [
				'token' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
			],
		] );

		// Check-in (requires auth)
		register_rest_route( 'woo-tickets/v1', '/check-in', [
			'methods' => 'POST',
			'callback' => 'wet_rest_check_in',
			'permission_callback' => [ $this, 'rest_check_in_permission' ],
			'args' => [
				'secure_token' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				'ticket_id' => [ 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ],
			],
		] );

		// Scanner status
		register_rest_route( 'woo-tickets/v1', '/scanner-status', [
			'methods' => 'GET',
			'callback' => 'wet_rest_scanner_status',
			'permission_callback' => [ $this, 'rest_scanner_permission' ],
		] );

		// Lookup token
		register_rest_route( 'woo-tickets/v1', '/lookup-token', [
			'methods' => 'POST',
			'callback' => 'wet_rest_lookup_token',
			'permission_callback' => [ $this, 'rest_check_in_permission' ],
			'args' => [
				'ticket_id' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
			],
		] );

		// Recent scans
		register_rest_route( 'woo-tickets/v1', '/recent-scans/(?P<event_id>\d+)', [
			'methods' => 'GET',
			'callback' => 'wet_rest_recent_scans',
			'permission_callback' => [ $this, 'rest_scanner_permission' ],
			'args' => [
				'event_id' => [ 'required' => true, 'validate_callback' => 'is_numeric' ],
				'limit' => [ 'required' => false, 'default' => 10, 'sanitize_callback' => 'absint' ],
			],
		] );
	}

	/**
	 * REST check-in permission
	 */
	public function rest_check_in_permission( $request ) {
		return current_user_can( 'manage_woo_event_tickets' );
	}

	/**
	 * REST scanner permission
	 */
	public function rest_scanner_permission( $request ) {
		return current_user_can( 'manage_woo_event_tickets' );
	}

	/**
	 * AJAX check-in handler
	 */
	public function ajax_check_in() {
		check_ajax_referer( 'wet_ajax_nonce', 'security' );

		if ( ! current_user_can( 'manage_woo_event_tickets' ) ) {
			wp_die( __( 'Permission denied', 'wootickets' ) );
		}

		$secure_token = isset( $_POST['secure_token'] ) ? sanitize_text_field( $_POST['secure_token'] ) : '';
		$ticket_id = isset( $_POST['ticket_id'] ) ? sanitize_text_field( $_POST['ticket_id'] ) : '';

		if ( empty( $secure_token ) ) {
			wp_send_json_error( [ 'message' => __( 'Missing secure token', 'wootickets' ) ] );
		}

		$result = Woo_Event_Tickets_Checkin::process_check_in( $secure_token, $ticket_id );
		wp_send_json( $result );
	}

	/**
	 * Register widgets
	 */
	public function register_widgets() {
		register_widget( 'Woo_Event_Tickets_Recent_Checkins_Widget' );
		register_widget( 'Woo_Event_Tickets_Revenue_Widget' );
	}
}

// Initialize the plugin
Woo_Event_Tickets::get_instance();

// Include the uninstall file
if ( file_exists( WET_PLUGIN_DIR . 'uninstall.php' ) ) {
	require_once WET_PLUGIN_DIR . 'uninstall.php';
}