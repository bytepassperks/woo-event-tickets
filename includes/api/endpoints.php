<?php
/**
 * REST API and AJAX endpoints
 */

// REST API: Verify ticket
add_action( 'rest_api_init', function() {
    register_rest_route( 'woo-tickets/v1', '/verify/(?P<token>[a-z0-9_-]+)', [
        'methods' => 'GET',
        'callback' => 'wet_rest_verify_ticket',
        'permission_callback' => '__return_true', // Public endpoint
        'args' => [
            'token' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ] );

    // REST API: Check-in (requires auth)
    register_rest_route( 'woo-tickets/v1', '/check-in', [
        'methods' => 'POST',
        'callback' => 'wet_rest_check_in',
        'permission_callback' => 'wet_rest_check_in_permission',
        'args' => [
            'secure_token' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
            'ticket_id' => [ 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ],
        ],
    ] );

    // REST API: Scanner status
    register_rest_route( 'woo-tickets/v1', '/scanner-status', [
        'methods' => 'GET',
        'callback' => 'wet_rest_scanner_status',
        'permission_callback' => 'wet_rest_scanner_permission',
    ] );

    // REST API: Lookup token by ticket ID
    register_rest_route( 'woo-tickets/v1', '/lookup-token', [
        'methods' => 'POST',
        'callback' => 'wet_rest_lookup_token',
        'permission_callback' => 'wet_rest_check_in_permission',
        'args' => [
            'ticket_id' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
        ],
    ] );

    // REST API: Get recent scans
    register_rest_route( 'woo-tickets/v1', '/recent-scans/(?P<event_id>\d+)', [
        'methods' => 'GET',
        'callback' => 'wet_rest_recent_scans',
        'permission_callback' => 'wet_rest_scanner_permission',
        'args' => [
            'event_id' => [ 'required' => true, 'validate_callback' => 'is_numeric' ],
            'limit' => [ 'required' => false, 'default' => 10, 'sanitize_callback' => 'absint' ],
        ],
    ] );
} );

/**
 * Public ticket verification endpoint
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function wet_rest_verify_ticket( $request ) {
    $token = $request->get_param( 'token' );
    
    global $wpdb;
    $qr_tokens = $wpdb->prefix . 'wet_qr_tokens';
    $tickets = $wpdb->prefix . 'wet_tickets';
    $events = $wpdb->prefix . 'wet_events';
    $ticket_types = $wpdb->prefix . 'wet_ticket_types';

    $result = $wpdb->get_row( $wpdb->prepare(
        "SELECT qt.*, t.ticket_id as ticket_str, t.event_id, t.ticket_type_id, t.customer_name, t.status as ticket_status,
                e.name as event_name, e.start_datetime, e.venue, e.address,
                tt.name as ticket_type_name, tt.allowed_people
        FROM {$qr_tokens} qt
        INNER JOIN {$tickets} t ON qt.ticket_id = t.id
        INNER JOIN {$events} e ON t.event_id = e.id
        LEFT JOIN {$ticket_types} tt ON t.ticket_type_id = tt.id
        WHERE qt.secure_token = %s",
        $token
    ), ARRAY_A );

    if ( ! $result ) {
        return new WP_REST_Response( [
            'success' => false,
            'status' => 'invalid',
            'message' => __( 'Invalid QR code', 'wootickets' ),
        ], 404 );
    }

    $status_messages = [
        'active' => __( 'Valid ticket', 'wootickets' ),
        'used' => __( 'This ticket has already been used', 'wootickets' ),
        'cancelled' => __( 'This ticket has been cancelled', 'wootickets' ),
        'refunded' => __( 'This ticket has been refunded', 'wootickets' ),
        'expired' => __( 'This ticket has expired', 'wootickets' ),
        'blocked' => __( 'This ticket has been blocked', 'wootickets' ),
    ];

    // Don't reveal sensitive info for invalid tickets
    $public_data = [
        'success' => $result['status'] === 'active',
        'status' => $result['status'],
        'message' => $status_messages[ $result['status'] ] ?? __( 'Invalid ticket', 'wootickets' ),
    ];

    // Only show ticket details for valid tickets
    if ( $result['status'] === 'active' ) {
        $public_data['ticket'] = [
            'ticket_id' => $result['ticket_str'],
            'ticket_type' => $result['ticket_type_name'],
            'customer_name' => $result['customer_name'],
            'event_name' => $result['event_name'],
            'event_date' => date_i18n( 'F j, Y', strtotime( $result['start_datetime'] ) ),
            'event_time' => date_i18n( 'g:i A', strtotime( $result['start_datetime'] ) ),
            'venue' => $result['venue'],
            'address' => $result['address'],
            'people_allowed' => intval( $result['allowed_people'] ),
        ];
    } elseif ( $result['status'] === 'used' ) {
        $public_data['ticket'] = [
            'ticket_id' => $result['ticket_str'],
            'first_used' => $result['used_at'],
        ];
    }

    return new WP_REST_Response( $public_data, $result['status'] === 'active' ? 200 : 400 );
}

/**
 * Check-in endpoint (requires authentication)
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function wet_rest_check_in( $request ) {
    if ( ! wet_rest_check_in_permission( $request ) ) {
        return new WP_Error( 'permission_denied', __( 'Permission denied', 'wootickets' ), [ 'status' => 403 ] );
    }

    $secure_token = $request->get_param( 'secure_token' );
    $ticket_id = $request->get_param( 'ticket_id' );

    $result = Woo_Event_Tickets_Checkin::process_check_in( $secure_token, $ticket_id );

    return new WP_REST_Response( $result, $result['success'] ? 200 : 400 );
}

/**
 * Check-in permission check
 * @param WP_REST_Request $request
 * @return bool
 */
function wet_rest_check_in_permission( $request ) {
    return current_user_can( 'manage_woo_event_tickets' );
}

/**
 * Scanner permission check
 * @param WP_REST_Request $request
 * @return bool
 */
function wet_rest_scanner_permission( $request ) {
    return current_user_can( 'manage_woo_event_tickets' );
}

/**
 * Scanner status endpoint
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function wet_rest_scanner_status( $request ) {
    $current_user = wp_get_current_user();
    $staff_events = Woo_Event_Tickets_Staff::get_by_user( $current_user->ID );

    return new WP_REST_Response( [
        'user' => [
            'id' => $current_user->ID,
            'name' => $current_user->display_name,
            'email' => $current_user->user_email,
        ],
        'staff_events' => $staff_events,
        'settings' => Woo_Event_Tickets_Scanner::get_settings(),
    ] );
}

/**
 * Lookup token by ticket ID
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function wet_rest_lookup_token( $request ) {
    if ( ! wet_rest_check_in_permission( $request ) ) {
        return new WP_Error( 'permission_denied', __( 'Permission denied', 'wootickets' ), [ 'status' => 403 ] );
    }

    $ticket_id = $request->get_param( 'ticket_id' );
    $ticket = Woo_Event_Tickets_Tickets::get_by_ticket_id( $ticket_id );

    if ( ! $ticket ) {
        return new WP_REST_Response( [
            'success' => false,
            'message' => __( 'Ticket not found', 'wootickets' ),
        ], 404 );
    }

    $qr_token = Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'qr_tokens' ) . " WHERE ticket_id = %d AND status = 'active'", [ $ticket['id'] ] );

    if ( ! $qr_token ) {
        return new WP_REST_Response( [
            'success' => false,
            'message' => __( 'No active QR code found for this ticket', 'wootickets' ),
        ], 404 );
    }

    return new WP_REST_Response( [
        'success' => true,
        'data' => [
            'token' => $qr_token['secure_token'],
            'verification_url' => $qr_token['verification_url'],
        ],
    ] );
}

/**
 * Get recent scans for event
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function wet_rest_recent_scans( $request ) {
    if ( ! wet_rest_scanner_permission( $request ) ) {
        return new WP_Error( 'permission_denied', __( 'Permission denied', 'wootickets' ), [ 'status' => 403 ] );
    }

    $event_id = $request->get_param( 'event_id' );
    $limit = $request->get_param( 'limit' );

    $scans = Woo_Event_Tickets_Checkin::get_recent( $event_id, $limit );

    // Render HTML for the scans table
    ob_start();
    if ( ! empty( $scans ) ) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Time', 'wootickets' ); ?></th>
                    <th><?php esc_html_e( 'Ticket', 'wootickets' ); ?></th>
                    <th><?php esc_html_e( 'Type', 'wootickets' ); ?></th>
                    <th><?php esc_html_e( 'Result', 'wootickets' ); ?></th>
                    <th><?php esc_html_e( 'Staff', 'wootickets' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $scans as $scan ) : ?>
                <tr class="wet-scan-<?php echo esc_attr( $scan['result'] ); ?>">
                    <td><?php echo date_i18n( 'H:i:s', strtotime( $scan['scanned_at'] ) ); ?></td>
                    <td><?php echo esc_html( $scan['ticket_id'] ); ?></td>
                    <td><?php echo esc_html( $scan['ticket_type_name'] ); ?></td>
                    <td><span class="wet-status-badge wet-status-<?php echo esc_attr( $scan['result'] ); ?>"><?php echo esc_html( ucfirst( $scan['result'] ) ); ?></span></td>
                    <td><?php echo esc_html( $scan['staff_name'] ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php esc_html_e( 'No scans yet.', 'wootickets' ); ?></p>
    <?php endif;
    $html = ob_get_clean();

    return new WP_REST_Response( [
        'success' => true,
        'data' => [
            'html' => $html,
            'scans' => $scans,
        ],
    ] );
}

// AJAX Handlers
add_action( 'wp_ajax_wet_check_in', 'wet_ajax_check_in' );
add_action( 'wp_ajax_nopriv_wet_check_in', 'wet_ajax_check_in' );

function wet_ajax_check_in() {
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

add_action( 'wp_ajax_wet_resend_ticket', 'wet_ajax_resend_ticket' );
add_action( 'wp_ajax_nopriv_wet_resend_ticket', 'wet_ajax_resend_ticket' );

function wet_ajax_resend_ticket() {
    check_ajax_referer( 'wet_ajax_nonce', 'security' );

    if ( ! current_user_can( 'manage_woo_event_tickets' ) ) {
        wp_die( __( 'Permission denied', 'wootickets' ) );
    }

    $ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;

    if ( ! $ticket_id ) {
        wp_send_json_error( [ 'message' => __( 'Invalid ticket ID', 'wootickets' ) ] );
    }

    $result = Woo_Event_Tickets_Tickets::resend_email( $ticket_id );

    if ( $result ) {
        wp_send_json_success( [ 'message' => __( 'Ticket email resent successfully', 'wootickets' ) ] );
    } else {
        wp_send_json_error( [ 'message' => __( 'Failed to resend ticket email', 'wootickets' ) ] );
    }
}

add_action( 'wp_ajax_wet_lookup_token', 'wet_ajax_lookup_token' );
add_action( 'wp_ajax_nopriv_wet_lookup_token', 'wet_ajax_lookup_token' );

function wet_ajax_lookup_token() {
    check_ajax_referer( 'wet_ajax_nonce', 'security' );

    if ( ! current_user_can( 'manage_woo_event_tickets' ) ) {
        wp_die( __( 'Permission denied', 'wootickets' ) );
    }

    $ticket_id = isset( $_POST['ticket_id'] ) ? sanitize_text_field( $_POST['ticket_id'] ) : '';

    if ( empty( $ticket_id ) ) {
        wp_send_json_error( [ 'message' => __( 'Missing ticket ID', 'wootickets' ) ] );
    }

    $ticket = Woo_Event_Tickets_Tickets::get_by_ticket_id( $ticket_id );

    if ( ! $ticket ) {
        wp_send_json_error( [ 'message' => __( 'Ticket not found', 'wootickets' ) ] );
    }

    $qr_token = Woo_Event_Tickets_DB::get_row( "SELECT * FROM " . Woo_Event_Tickets_DB::table( 'qr_tokens' ) . " WHERE ticket_id = %d AND status = 'active'", [ $ticket['id'] ] );

    if ( ! $qr_token ) {
        wp_send_json_error( [ 'message' => __( 'No active QR code found', 'wootickets' ) ] );
    }

    wp_send_json_success( [
        'token' => $qr_token['secure_token'],
        'verification_url' => $qr_token['verification_url'],
    ] );
}

add_action( 'wp_ajax_wet_sync_ticket_type', 'wet_ajax_sync_ticket_type' );

function wet_ajax_sync_ticket_type() {
    check_ajax_referer( 'wet_ajax_nonce', 'security' );

    if ( ! current_user_can( 'manage_woo_event_tickets' ) ) {
        wp_die( __( 'Permission denied', 'wootickets' ) );
    }

    $type_id = isset( $_POST['type_id'] ) ? intval( $_POST['type_id'] ) : 0;

    if ( ! $type_id ) {
        wp_send_json_error( [ 'message' => __( 'Invalid ticket type ID', 'wootickets' ) ] );
    }

    $result = Woo_Event_Tickets_WooCommerce::sync_ticket_type( $type_id );

    if ( $result ) {
        wp_send_json_success( [ 'message' => __( 'Synced successfully', 'wootickets' ) ] );
    } else {
        wp_send_json_error( [ 'message' => __( 'Sync failed', 'wootickets' ) ] );
    }
}

add_action( 'wp_ajax_wet_get_recent_scans', 'wet_ajax_get_recent_scans' );
add_action( 'wp_ajax_nopriv_wet_get_recent_scans', 'wet_ajax_get_recent_scans' );

function wet_ajax_get_recent_scans() {
    check_ajax_referer( 'wet_ajax_nonce', 'security' );

    if ( ! current_user_can( 'manage_woo_event_tickets' ) ) {
        wp_die( __( 'Permission denied', 'wootickets' ) );
    }

    $event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;

    if ( ! $event_id ) {
        wp_send_json_error( [ 'message' => __( 'Invalid event ID', 'wootickets' ) ] );
    }

    $scans = Woo_Event_Tickets_Checkin::get_recent( $event_id, 10 );

    ob_start();
    if ( ! empty( $scans ) ) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Time', 'wootickets' ); ?></th>
                    <th><?php esc_html_e( 'Ticket', 'wootickets' ); ?></th>
                    <th><?php esc_html_e( 'Type', 'wootickets' ); ?></th>
                    <th><?php esc_html_e( 'Result', 'wootickets' ); ?></th>
                    <th><?php esc_html_e( 'Staff', 'wootickets' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $scans as $scan ) : ?>
                <tr class="wet-scan-<?php echo esc_attr( $scan['result'] ); ?>">
                    <td><?php echo date_i18n( 'H:i:s', strtotime( $scan['scanned_at'] ) ); ?></td>
                    <td><?php echo esc_html( $scan['ticket_id'] ); ?></td>
                    <td><?php echo esc_html( $scan['ticket_type_name'] ); ?></td>
                    <td><span class="wet-status-badge wet-status-<?php echo esc_attr( $scan['result'] ); ?>"><?php echo esc_html( ucfirst( $scan['result'] ) ); ?></span></td>
                    <td><?php echo esc_html( $scan['staff_name'] ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php esc_html_e( 'No scans yet.', 'wootickets' ); ?></p>
    <?php endif;
    $html = ob_get_clean();

    wp_send_json_success( [ 'html' => $html ] );
}