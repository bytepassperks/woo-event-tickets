# Woo Event Tickets

**Production-ready WordPress + WooCommerce Event Ticketing & QR Verification Plugin**

A comprehensive event ticketing system built for WordPress and WooCommerce that handles everything from ticket creation to QR code scanning at event entrances.

## Features

### Core Ticketing
- **Multiple Events** - Create and manage unlimited events
- **Flexible Ticket Types** - Unlimited ticket types per event (Regular, VIP, Early Bird, Group/Family)
- **Early Bird Pricing** - Automatic price switching based on quantity and date
- **Inventory Management** - Real-time stock tracking with overselling prevention
- **Group/Family Tickets** - Single QR code for multiple attendees with partial check-in support

### WooCommerce Integration
- **Automatic Product Creation** - Ticket types automatically create/sync WooCommerce products
- **Order Status Triggers** - Configurable order statuses that trigger ticket generation
- **Payment Gateway Agnostic** - Works with all WooCommerce payment gateways
- **HPOS Compatible** - Fully compatible with WooCommerce High-Performance Order Storage
- **Refund/Cancellation Handling** - Automatic ticket invalidation on refund/cancel

### Unique Ticket System
- **Cryptographically Secure** - Each ticket gets a unique 256-bit verification token
- **Unique Ticket IDs** - Human-readable format: `EVT-2026-000123`
- **QR Code Generation** - Automatic QR code for every ticket
- **One-Time Verification** - Server-side atomic check-in prevents duplicate scans
- **Concurrent Scan Protection** - Database-level locking prevents race conditions

### Check-in System
- **Mobile-Optimized Scanner** - Works on phones/tablets with camera access
- **Multiple Scanners** - Unlimited staff devices scanning simultaneously
- **Manual Verification** - Fallback for damaged QR codes
- **Real-time Results** - Instant visual feedback (green/red) with details
- **Offline-Ready** - Designed for future PWA offline support

### Customer Experience
- **My Tickets Page** - Integrated with WooCommerce My Account
- **Email Delivery** - Automatic ticket emails with QR codes
- **PDF Tickets** - Professional branded PDF tickets for download/print
- **Public Verification** - Customers can verify their own tickets
- **Multi-language Ready** - Full i18n support

### Admin Features
- **Dashboard** - Real-time stats: sold, checked-in, revenue, check-in rate
- **Event Management** - Complete event lifecycle management
- **Ticket Management** - Search, filter, bulk actions, CSV export
- **Staff Roles** - Scanner, Manager, Admin with granular permissions
- **Reports** - Sales, check-in, customer, and revenue reports
- **Check-in Analytics** - Hourly charts, staff performance tracking
- **Audit Logs** - Complete history of all ticket operations

### Security
- **No Sensitive Data in QR** - Only secure verification tokens
- **Atomic Operations** - Database transactions prevent race conditions
- **Capability System** - WordPress role-based permissions
- **Rate Limiting** - Configurable API rate limits
- **Nonce Protection** - All AJAX endpoints secured
- **SQL Injection Prevention** - Prepared statements throughout

## Requirements

- WordPress 6.5+
- WooCommerce 8.0+
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate through the 'Plugins' menu in WordPress
3. Go to **Event Tickets → Settings** to configure
4. Create your first event at **Event Tickets → Events**
5. Add ticket types at **Event Tickets → Ticket Types**
6. Tickets are automatically generated when orders are paid

## Quick Start

### 1. Configure Settings
Navigate to **Event Tickets → Settings** and set:
- Currency
- Ticket generation order statuses (default: Processing, Completed)
- Email sender details
- Scanner preferences (sound, vibration, auto-next)

### 2. Create an Event
Go to **Event Tickets → Events → Add New** and fill in:
- Event name, date/time, venue
- Branding (colors, logo, banner)
- Organizer details

### 3. Create Ticket Types
Go to **Event Tickets → Ticket Types → Add New**:
- Select your event
- Set name, price, quantity
- Configure early bird pricing (optional)
- Set allowed attendees per ticket (for group tickets)

### 4. Sell Tickets
- Ticket types automatically create WooCommerce products
- Customers purchase through standard WooCommerce checkout
- Tickets auto-generated on payment completion
- QR codes emailed automatically

### 5. Event Day Scanning
- Staff go to **Event Tickets → Scanner**
- Select the event
- Click "Start Camera" and scan QR codes
- Instant green/red feedback with attendee details

## Shortcodes

```php
// Display all events
[ticket_events columns="3" show_date="yes" show_venue="yes"]

// Display single event
[ticket_event id="123"]

// Display ticket types for event
[ticket_types event_id="123"]

// Customer's tickets (My Account)
[my_tickets]

// Public ticket verification
[ticket_verify]

// Staff scanner (requires permission)
[ticket_scanner event_id="123"]
```

## Database Tables

The plugin creates the following tables:
- `wp_wet_events` - Events
- `wp_wet_ticket_types` - Ticket types
- `wp_wet_tickets` - Individual tickets
- `wp_wet_qr_tokens` - QR verification tokens
- `wp_wet_checkins` - Check-in logs
- `wp_wet_staff` - Staff assignments
- `wp_wet_attendees` - Attendee information
- `wp_wet_attendee_fields` - Custom attendee fields
- `wp_wet_logs` - Audit logs
- `wp_wet_settings` - Plugin settings

## Staff Roles

| Role | Capabilities |
|------|-------------|
| **Scanner** | Scan tickets, view assigned events |
| **Manager** | Scanner + view reports, manage staff |
| **Admin** | Full event management access |

Assign staff at **Event Tickets → Staff**

## Early Bird Pricing

Configure early bird pricing per ticket type:
- Early bird price
- Quantity limit
- Start/end dates

The system automatically:
- Switches to early bird price when active
- Tracks quantity sold
- Reverts to regular price when limit reached
- Handles concurrent purchases safely

## Group/Family Tickets

For tickets allowing multiple people:
1. Set "People Allowed Per Ticket" > 1
2. Choose "Partial Check-in" option:
   - **Enabled**: Family of 4 can check in 2 now, 2 later
   - **Disabled**: First scan consumes entire ticket

## PDF Tickets

Requires either TCPDF or Dompdf (install via Composer):
```bash
composer require tecnickcom/tcpdf
# or
composer require dompdf/dompdf
```

## Customization

### Email Templates
Override `includes/emails/ticket_confirmation.php` in your theme:
```
your-theme/woo-event-tickets/emails/ticket_confirmation.php
```

### PDF Templates
Override `includes/templates/pdf-ticket.php` in your theme:
```
your-theme/woo-event-tickets/templates/pdf-ticket.php
```

### Shortcode Templates
Override any shortcode template in your theme:
```
your-theme/woo-event-tickets/templates/shortcode-events.php
your-theme/woo-event-tickets/templates/shortcode-event.php
your-theme/woo-event-tickets/templates/shortcode-ticket-types.php
your-theme/woo-event-tickets/templates/shortcode-my-tickets.php
your-theme/woo-event-tickets/templates/shortcode-verify.php
your-theme/woo-event-tickets/templates/shortcode-scanner.php
```

## Hooks & Filters

### Actions
```php
// After ticket generation
do_action( 'wet_ticket_generated', $ticket_id, $order_id );

// After successful check-in
do_action( 'wet_ticket_checked_in', $ticket_id, $checkin_id );

// Before ticket email sent
do_action( 'wet_before_ticket_email', $ticket_id );
```

### Filters
```php
// Modify email subject
apply_filters( 'wet_email_subject', $subject, $ticket, $ticket_type, $event );

// Modify ticket ID format
apply_filters( 'wet_ticket_id_format', $format, $event );

// Custom QR token generation
apply_filters( 'wet_generate_secure_token', $token );
```

## API Endpoints

### Public Verification
```
GET /wp-json/woo-tickets/v1/verify/{token}
```
Returns ticket status and limited public info.

### Authenticated Check-in
```
POST /wp-json/woo-tickets/v1/check-in
Body: { "secure_token": "...", "ticket_id": "..." }
```
Requires `manage_woo_event_tickets` capability.

### Scanner Status
```
GET /wp-json/woo-tickets/v1/scanner-status
```
Returns current user's staff events and settings.

## Performance

- **Indexed Database** - Optimized indexes on all query columns
- **Pagination** - All admin lists use efficient pagination
- **Caching Ready** - Compatible with WP Rocket, W3 Total Cache, etc.
- **HPOS Support** - Uses WooCommerce CRUD APIs, no direct post queries
- **Minimal Frontend** - Shortcodes load only necessary assets

## Security Considerations

1. **QR Codes contain only tokens** - No PII, payment info, or emails
2. **Server-side validation** - Never trust client-side verification
3. **Atomic check-in** - SELECT FOR UPDATE prevents double-scans
4. **Role-based access** - Staff cannot access admin settings
5. **Rate limiting** - Configurable per-minute request limits
6. **Audit logging** - All operations logged for compliance

## Troubleshooting

### Tickets not generating
- Check order status is in "Ticket Generation Order Statuses" setting
- Verify product is linked to a ticket type
- Check WooCommerce logs for errors

### QR codes not scanning
- Ensure camera permissions granted
- Try manual verification as fallback
- Check HTTPS (required for camera API)

### PDF not generating
- Install TCPDF or Dompdf via Composer
- Check upload directory permissions
- Verify PHP memory limit

### Early bird not working
- Verify timezone settings match server
- Check early bird dates are in future
- Confirm quantity limit not reached

## Support

- Documentation: https://warpfix.org/docs/woo-event-tickets
- Support: https://warpfix.org/support
- Issues: https://github.com/warpfix/woo-event-tickets/issues

## Changelog

### 1.0.0 (2026-09-10)
- Initial release
- Full event ticketing system
- WooCommerce integration
- QR scanner with atomic check-in
- PDF ticket generation
- Staff roles and permissions
- Early bird pricing
- Multi-event support
- Reporting and analytics

## License

GPL v2 or later

## Credits

Developed by Hardy Lebsack (hardy.lebsack@warpfix.org)

Libraries used:
- html5-qrcode for camera scanning
- jsQR for fallback QR decoding
- TCPDF/Dompdf for PDF generation
- Select2 for admin dropdowns
- SweetAlert2 for modals