<?php
/**
 * Ticket confirmation email template
 * @var array $data
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $data['event_name'] ); ?> - Ticket Confirmation</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <!-- Header -->
        <tr>
            <td style="text-align: center; padding: 30px 20px; background: linear-gradient(135deg, #0066cc 0%, #004499 100%); border-radius: 12px 12px 0 0;">
                <?php if ( ! empty( $data['event_logo'] ) ) : ?>
                    <img src="<?php echo esc_url( $data['event_logo'] ); ?>" alt="<?php echo esc_attr( $data['event_name'] ); ?>" style="max-width: 150px; height: auto; margin-bottom: 15px;">
                <?php else : ?>
                    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;"><?php echo esc_html( $data['event_name'] ); ?></h1>
                <?php endif; ?>
                <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 16px;"><?php esc_html_e( 'Your Ticket is Confirmed', 'wootickets' ); ?></p>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="background: #ffffff; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <p style="font-size: 16px; color: #1d2327; margin-top: 0;">Hi <?php echo esc_html( $data['customer_name'] ); ?>,</p>
                
                <p style="font-size: 16px; color: #444; line-height: 1.6;">Thank you for your purchase! Your ticket for <strong><?php echo esc_html( $data['event_name'] ); ?></strong> has been confirmed.</p>

                <!-- Ticket Card -->
                <div style="background: #f8f9fa; border-radius: 12px; padding: 24px; margin: 24px 0; border-left: 4px solid #0066cc;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="width: 50%; padding-right: 15px;">
                                <p style="margin: 0 0 8px; color: #666; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e( 'Event', 'wootickets' ); ?></p>
                                <p style="margin: 0; font-size: 18px; font-weight: 600; color: #1d2327;"><?php echo esc_html( $data['event_name'] ); ?></p>
                            </td>
                            <td style="width: 50%; padding-left: 15px; text-align: right;">
                                <p style="margin: 0 0 8px; color: #666; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e( 'Ticket Type', 'wootickets' ); ?></p>
                                <p style="margin: 0; font-size: 18px; font-weight: 600; color: #0066cc;"><?php echo esc_html( $data['ticket_type'] ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 20px; padding-right: 15px;">
                                <p style="margin: 0 0 8px; color: #666; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e( 'Date & Time', 'wootickets' ); ?></p>
                                <p style="margin: 0; font-size: 16px; color: #1d2327;"><?php echo esc_html( $data['event_date'] ); ?> at <?php echo esc_html( $data['event_time'] ); ?></p>
                            </td>
                            <td style="padding-top: 20px; padding-left: 15px; text-align: right;">
                                <p style="margin: 0 0 8px; color: #666; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e( 'Ticket ID', 'wootickets' ); ?></p>
                                <p style="margin: 0; font-size: 16px; font-family: monospace; color: #1d2327;"><?php echo esc_html( $data['ticket_id'] ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 20px; padding-right: 15px;">
                                <p style="margin: 0 0 8px; color: #666; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e( 'Venue', 'wootickets' ); ?></p>
                                <p style="margin: 0; font-size: 16px; color: #1d2327;"><?php echo esc_html( $data['venue'] ); ?><?php if ( $data['address'] ) : ?>, <?php echo esc_html( $data['address'] ); ?><?php endif; ?></p>
                            </td>
                            <td style="padding-top: 20px; padding-left: 15px; text-align: right;">
                                <p style="margin: 0 0 8px; color: #666; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e( 'Attendees', 'wootickets' ); ?></p>
                                <p style="margin: 0; font-size: 16px; font-weight: 600; color: #1d2327;"><?php echo intval( $data['allowed_people'] ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- QR Code -->
                <?php if ( $data['qr_url'] ) : ?>
                <div style="text-align: center; margin: 30px 0;">
                    <p style="margin: 0 0 15px; color: #666; font-size: 14px;"><?php esc_html_e( 'Show this QR code at the event entrance', 'wootickets' ); ?></p>
                    <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=<?php echo urlencode( $data['qr_url'] ); ?>&choe=UTF-8" alt="QR Code" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 10px; background: #fff;">
                    <p style="margin: 15px 0 0; color: #999; font-size: 12px;"><?php esc_html_e( 'QR Code for verification', 'wootickets' ); ?></p>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div style="text-align: center; margin: 30px 0;">
                    <a href="<?php echo esc_url( $data['qr_url'] ); ?>" style="display: inline-block; background: #0066cc; color: #ffffff; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; margin-right: 10px;"><?php esc_html_e( 'View Ticket', 'wootickets' ); ?></a>
                    <a href="<?php echo esc_url( $data['qr_url'] ); ?>.pdf" style="display: inline-block; background: #ffffff; color: #0066cc; border: 2px solid #0066cc; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px;"><?php esc_html_e( 'Download PDF', 'wootickets' ); ?></a>
                </div>

                <!-- Important Info -->
                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin: 30px 0;">
                    <h3 style="margin: 0 0 10px; color: #856404; font-size: 16px;"><?php esc_html_e( 'Important Information', 'wootickets' ); ?></h3>
                    <ul style="margin: 0; padding-left: 20px; color: #856404; font-size: 14px; line-height: 1.8;">
                        <li><?php esc_html_e( 'Bring a valid photo ID along with this ticket.', 'wootickets' ); ?></li>
                        <li><?php esc_html_e( 'This QR code can only be used once. Screenshots may not work.', 'wootickets' ); ?></li>
                        <li><?php esc_html_e( 'Arrive at least 30 minutes before the event start time.', 'wootickets' ); ?></li>
                        <li><?php esc_html_e( 'Tickets are non-transferable unless authorized by the organizer.', 'wootickets' ); ?></li>
                    </ul>
                </div>

                <!-- Footer -->
                <p style="color: #666; font-size: 14px; line-height: 1.6;">If you have any questions, please contact us at <a href="mailto:<?php echo esc_attr( $data['organizer_email'] ?? '' ); ?>" style="color: #0066cc;"><?php echo esc_html( $data['organizer_email'] ?? '' ); ?></a>.</p>
                
                <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                
                <p style="color: #999; font-size: 12px; margin: 0;"><?php echo esc_html( $data['event_name'] ); ?> &mdash; <?php esc_html_e( 'Powered by Woo Event Tickets', 'wootickets' ); ?></p>
            </td>
        </tr>
    </table>
</body>
</html>