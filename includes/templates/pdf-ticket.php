<?php
/**
 * PDF ticket template
 * @var array $ticket
 * @var array $ticket_type
 * @var array $event
 * @var array $attendee
 * @var string $qr_image (base64 or URL)
 */
$primary_color = $event['primary_color'] ?? '#0066cc';
$secondary_color = $event['secondary_color'] ?? '#004499';
$logo_url = $event['logo_url'] ?? '';
$banner_url = $event['banner_url'] ?? '';
$footer_text = $event['footer_text'] ?? '';
$organizer_name = $event['organizer_name'] ?? '';
$organizer_email = $event['organizer_email'] ?? '';
$organizer_phone = $event['organizer_phone'] ?? '';

$attendee_name = $attendee['full_name'] ?? $ticket['customer_name'];
$attendee_email = $attendee['email'] ?? $ticket['customer_email'];
$attendee_phone = $attendee['phone'] ?? $ticket['customer_phone'];
$features = $ticket_type['features'] ?? '';
$terms = $ticket_type['terms_conditions'] ?? '';
$allowed_people = $ticket_type['allowed_people'] ?? 1;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body { margin: 0; padding: 0; font-family: 'Helvetica', 'Arial', sans-serif; color: #1d2327; }
        .ticket { width: 100%; max-width: 700px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, <?php echo esc_attr( $primary_color ); ?> 0%, <?php echo esc_attr( $secondary_color ); ?> 100%); padding: 40px 30px; text-align: center; color: #fff; position: relative; }
        .header::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></svg>') center/cover; opacity: 0.5; }
        .logo { max-width: 180px; height: auto; margin-bottom: 15px; position: relative; z-index: 1; }
        .event-name { font-size: 32px; font-weight: 700; margin: 0 0 10px; position: relative; z-index: 1; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .ticket-type-badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 8px 20px; border-radius: 30px; font-size: 16px; font-weight: 600; position: relative; z-index: 1; }
        .content { padding: 40px 30px; }
        .ticket-id { font-family: 'Courier New', monospace; font-size: 20px; font-weight: 700; letter-spacing: 2px; color: <?php echo esc_attr( $primary_color ); ?>; text-align: center; margin-bottom: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 2px dashed <?php echo esc_attr( $primary_color ); ?>; }
        .info-grid { display: table; width: 100%; margin-bottom: 30px; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; width: 50%; padding: 15px 10px; vertical-align: top; }
        .info-label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #666; margin-bottom: 5px; font-weight: 600; }
        .info-value { font-size: 16px; font-weight: 600; color: #1d2327; }
        .qr-section { text-align: center; margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 12px; }
        .qr-image { max-width: 250px; height: auto; border: 1px solid #e0e0e0; padding: 10px; background: #fff; border-radius: 8px; }
        .qr-caption { margin-top: 10px; font-size: 12px; color: #999; }
        .features { margin: 30px 0; }
        .features-title { font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: #666; margin-bottom: 15px; font-weight: 600; }
        .features-list { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .features-list li { padding: 10px 15px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; gap: 10px; font-size: 14px; }
        .features-list li::before { content: '✓'; color: <?php echo esc_attr( $primary_color ); ?>; font-weight: bold; }
        .terms { border-top: 1px solid #e0e0e0; padding-top: 20px; font-size: 12px; color: #666; line-height: 1.6; }
        .terms strong { color: #1d2327; }
        .footer { text-align: center; padding: 20px 30px; background: #f8f9fa; border-top: 1px solid #e0e0e0; font-size: 11px; color: #999; }
        .organizer-info { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Header -->
        <div class="header">
            <?php if ( $logo_url ) : ?>
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $event['name'] ); ?>" class="logo">
            <?php endif; ?>
            <h1 class="event-name"><?php echo esc_html( $event['name'] ); ?></h1>
            <span class="ticket-type-badge"><?php echo esc_html( $ticket_type['name'] ); ?></span>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Ticket ID -->
            <div class="ticket-id"><?php echo esc_html( $ticket['ticket_id'] ); ?></div>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label"><?php esc_html_e( 'Event Date', 'wootickets' ); ?></span>
                        <span class="info-value"><?php echo date_i18n( 'F j, Y', strtotime( $event['start_datetime'] ) ); ?></span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label"><?php esc_html_e( 'Event Time', 'wootickets' ); ?></span>
                        <span class="info-value"><?php echo date_i18n( 'g:i A', strtotime( $event['start_datetime'] ) ); ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label"><?php esc_html_e( 'Venue', 'wootickets' ); ?></span>
                        <span class="info-value"><?php echo esc_html( $event['venue'] ); ?><?php if ( $event['address'] ) : ?>, <?php echo esc_html( $event['address'] ); ?><?php endif; ?></span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label"><?php esc_html_e( 'Attendee', 'wootickets' ); ?></span>
                        <span class="info-value"><?php echo esc_html( $attendee_name ); ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label"><?php esc_html_e( 'Email', 'wootickets' ); ?></span>
                        <span class="info-value"><?php echo esc_html( $attendee_email ); ?></span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label"><?php esc_html_e( 'Phone', 'wootickets' ); ?></span>
                        <span class="info-value"><?php echo esc_html( $attendee_phone ); ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label"><?php esc_html_e( 'Attendees Allowed', 'wootickets' ); ?></span>
                        <span class="info-value"><?php echo intval( $allowed_people ); ?></span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label"><?php esc_html_e( 'Order #', 'wootickets' ); ?></span>
                        <span class="info-value">#<?php echo $ticket['order_id']; ?></span>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            <div class="qr-section">
                <?php if ( $qr_image ) : ?>
                    <img src="<?php echo esc_url( $qr_image ); ?>" alt="QR Code" class="qr-image">
                    <div class="qr-caption"><?php esc_html_e( 'Scan at entrance for verification', 'wootickets' ); ?></div>
                <?php endif; ?>
            </div>

            <!-- Features -->
            <?php if ( $features ) : ?>
            <div class="features">
                <div class="features-title"><?php esc_html_e( 'Includes', 'wootickets' ); ?></div>
                <ul class="features-list">
                    <?php 
                    $feature_lines = preg_split( '/\r\n|\r|\n/', $features );
                    foreach ( $feature_lines as $line ) :
                        $line = trim( $line );
                        if ( $line ) :
                            $line = preg_replace( '/^[\-\*\•]\s*/', '', $line );
                    ?>
                    <li><?php echo esc_html( $line ); ?></li>
                    <?php endif; endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Terms & Conditions -->
            <?php if ( $terms ) : ?>
            <div class="terms">
                <strong><?php esc_html_e( 'Terms & Conditions:', 'wootickets' ); ?></strong>
                <?php echo wp_kses_post( wpautop( $terms ) ); ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <?php if ( $footer_text ) : ?>
                <p><?php echo esc_html( $footer_text ); ?></p>
            <?php endif; ?>
            <div class="organizer-info">
                <?php if ( $organizer_name ) : ?>
                    <p><?php echo esc_html( $organizer_name ); ?></p>
                <?php endif; ?>
                <?php if ( $organizer_email ) : ?>
                    <p><a href="mailto:<?php echo esc_attr( $organizer_email ); ?>" style="color: #999;"><?php echo esc_html( $organizer_email ); ?></a></p>
                <?php endif; ?>
                <?php if ( $organizer_phone ) : ?>
                    <p><?php echo esc_html( $organizer_phone ); ?></p>
                <?php endif; ?>
            </div>
            <p><?php echo esc_html( $event['name'] ); ?> &mdash; <?php esc_html_e( 'Powered by Woo Event Tickets', 'wootickets' ); ?></p>
        </div>
    </div>
</body>
</html>