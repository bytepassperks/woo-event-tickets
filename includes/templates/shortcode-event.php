<?php
/**
 * Shortcode: [ticket_event] template
 * @var array $event
 * @var array $ticket_types
 * @var array $atts
 */
$show_description = $atts['show_description'] === 'yes';
$show_ticket_types = $atts['show_ticket_types'] === 'yes';
$primary_color = $event['primary_color'] ?? '#0066cc';
$secondary_color = $event['secondary_color'] ?? '#004499';
$stats = Woo_Event_Tickets_Events::get_stats( $event['id'] );
$is_upcoming = strtotime( $event['start_datetime'] ) > current_time( 'timestamp' );
$is_ongoing = strtotime( $event['start_datetime'] ) <= current_time( 'timestamp' ) && strtotime( $event['end_datetime'] ) >= current_time( 'timestamp' );
$status_class = $is_upcoming ? 'upcoming' : ( $is_ongoing ? 'ongoing' : 'past' );
?>
<div class="wet-single-event" style="--wet-primary: <?php echo esc_attr( $primary_color ); ?>; --wet-secondary: <?php echo esc_attr( $secondary_color ); ?>;">
	<!-- Hero Section -->
	<div class="wet-event-hero">
		<?php if ( $event['banner_url'] ) : ?>
			<img src="<?php echo esc_url( $event['banner_url'] ); ?>" alt="<?php echo esc_attr( $event['name'] ); ?>" class="wet-hero-image">
		<?php else : ?>
			<div class="wet-hero-gradient"></div>
		<?php endif; ?>
		
		<div class="wet-hero-content">
			<span class="wet-event-status wet-status-<?php echo esc_attr( $status_class ); ?>">
				<?php echo esc_html( ucfirst( $status_class ) ); ?>
			</span>
			<h1 class="wet-event-title"><?php echo esc_html( $event['name'] ); ?></h1>
			
			<div class="wet-event-meta-grid">
				<div class="wet-meta-item">
					<span class="dashicons dashicons-calendar-alt"></span>
					<div>
						<span class="wet-meta-label"><?php esc_html_e( 'Date', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo date_i18n( 'F j, Y', strtotime( $event['start_datetime'] ) ); ?></span>
					</div>
				</div>
				<div class="wet-meta-item">
					<span class="dashicons dashicons-clock"></span>
					<div>
						<span class="wet-meta-label"><?php esc_html_e( 'Time', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo date_i18n( 'g:i A', strtotime( $event['start_datetime'] ) ); ?></span>
					</div>
				</div>
				<div class="wet-meta-item">
					<span class="dashicons dashicons-location-alt"></span>
					<div>
						<span class="wet-meta-label"><?php esc_html_e( 'Venue', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo esc_html( $event['venue'] ); ?><?php if ( $event['city'] ) : ?>, <?php echo esc_html( $event['city'] ); ?><?php endif; ?></span>
					</div>
				</div>
				<div class="wet-meta-item">
					<span class="dashicons dashicons-tickets"></span>
					<div>
						<span class="wet-meta-label"><?php esc_html_e( 'Tickets Sold', 'wootickets' ); ?></span>
						<span class="wet-meta-value"><?php echo number_format( $stats['total_sold'] ); ?> / <?php echo number_format( $stats['total_tickets'] ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="wet-event-body">
		<?php if ( $show_description && $event['description'] ) : ?>
			<div class="wet-event-description">
				<h2><?php esc_html_e( 'About This Event', 'wootickets' ); ?></h2>
				<div class="wet-description-content"><?php echo wp_kses_post( $event['description'] ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( $show_ticket_types && ! empty( $ticket_types ) ) : ?>
			<div class="wet-ticket-types-section">
				<h2><?php esc_html_e( 'Available Tickets', 'wootickets' ); ?></h2>
				<div class="wet-ticket-types-grid">
					<?php foreach ( $ticket_types as $type ) : 
						$display_price = Woo_Event_Tickets_Ticket_Types::get_display_price( $type );
						$remaining = Woo_Event_Tickets_Ticket_Types::get_remaining_quantity( $type['id'] );
						$product = $type['woocommerce_product_id'] ? wc_get_product( $type['woocommerce_product_id'] ) : null;
						$product_url = $product ? $product->get_permalink() : '#';
					?>
					<article class="wet-ticket-card <?php echo $remaining <= 0 ? 'sold-out' : ''; ?>">
						<div class="wet-ticket-header" style="border-left-color: <?php echo esc_attr( $type['color'] ); ?>;">
							<h3><?php echo esc_html( $type['name'] ); ?></h3>
							<?php if ( $type['category'] ) : ?>
								<span class="wet-ticket-category"><?php echo esc_html( ucfirst( $type['category'] ) ); ?></span>
							<?php endif; ?>
						</div>
						
						<?php if ( $type['description'] ) : ?>
							<p class="wet-ticket-desc"><?php echo esc_html( wp_trim_words( $type['description'], 25 ) ); ?></p>
						<?php endif; ?>
						
						<div class="wet-ticket-price">
							<?php if ( $display_price['is_early_bird'] ) : ?>
								<span class="wet-price-current"><?php echo wc_price( $display_price['price'] ); ?></span>
								<span class="wet-price-original"><?php echo wc_price( $display_price['original_price'] ); ?></span>
								<span class="wet-early-bird-tag"><?php esc_html_e( 'Early Bird', 'wootickets' ); ?></span>
								<?php if ( $display_price['early_bird_remaining'] > 0 ) : ?>
									<span class="wet-early-bird-count"><?php printf( esc_html__( '%d left', 'wootickets' ), $display_price['early_bird_remaining'] ); ?></span>
								<?php endif; ?>
							<?php elseif ( ! empty( $display_price['is_sale'] ) ) : ?>
								<del class="wet-price-original"><?php echo wc_price( $display_price['original_price'] ); ?></del>
								<span class="wet-price-current"><?php echo wc_price( $display_price['price'] ); ?></span>
							<?php else : ?>
								<span class="wet-price-current"><?php echo wc_price( $display_price['price'] ); ?></span>
							<?php endif; ?>
						</div>
						
						<div class="wet-ticket-availability">
							<span class="wet-remaining"><?php echo number_format( $remaining ); ?> <?php esc_html_e( 'remaining', 'wootickets' ); ?></span>
						</div>
						
						<?php if ( $type['features'] ) : ?>
							<ul class="wet-ticket-features">
								<?php 
								$features = preg_split( '/\r\n|\r|\n/', $type['features'] );
								foreach ( array_slice( $features, 0, 4 ) as $feature ) :
									$feature = trim( preg_replace( '/^[\-\*\•]\s*/', '', $feature ) );
									if ( $feature ) :
								?>
								<li><?php echo esc_html( $feature ); ?></li>
								<?php endif; endforeach; ?>
							</ul>
						<?php endif; ?>
						
						<div class="wet-ticket-actions">
							<?php if ( $remaining > 0 ) : ?>
								<a href="<?php echo esc_url( $product_url ); ?>" class="button button-primary wet-btn-buy" data-ticket-type="<?php echo esc_attr( $type['id'] ); ?>"><?php esc_html_e( 'Buy Now', 'wootickets' ); ?></a>
							<?php else : ?>
								<button class="button button-secondary" disabled><?php esc_html_e( 'Sold Out', 'wootickets' ); ?></button>
							<?php endif; ?>
						</div>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $event['organizer_name'] || $event['organizer_email'] || $event['organizer_phone'] ) : ?>
			<div class="wet-event-organizer">
				<h2><?php esc_html_e( 'Organizer', 'wootickets' ); ?></h2>
				<div class="wet-organizer-info">
					<?php if ( $event['organizer_name'] ) : ?>
						<p><strong><?php echo esc_html( $event['organizer_name'] ); ?></strong></p>
					<?php endif; ?>
					<?php if ( $event['organizer_email'] ) : ?>
						<p><a href="mailto:<?php echo esc_attr( $event['organizer_email'] ); ?>"><?php echo esc_html( $event['organizer_email'] ); ?></a></p>
					<?php endif; ?>
					<?php if ( $event['organizer_phone'] ) : ?>
						<p><a href="tel:<?php echo esc_attr( $event['organizer_phone'] ); ?>"><?php echo esc_html( $event['organizer_phone'] ); ?></a></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<style>
.wet-single-event { max-width: 900px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
.wet-event-hero { position: relative; border-radius: 16px 16px 0 0; overflow: hidden; min-height: 300px; }
.wet-hero-image { width: 100%; height: 300px; object-fit: cover; }
.wet-hero-gradient { width: 100%; height: 300px; background: linear-gradient(135deg, var(--wet-primary), var(--wet-secondary)); }
.wet-hero-content { position: absolute; bottom: 0; left: 0; right: 0; padding: 40px 30px 30px; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: #fff; }
.wet-event-status { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 16px; }
.wet-event-status.upcoming { background: var(--wet-primary); }
.wet-event-status.ongoing { background: #46b450; animation: pulse 2s infinite; }
.wet-event-status.past { background: #999; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
.wet-event-title { font-size: 36px; font-weight: 800; margin: 0 0 24px; text-shadow: 0 2px 8px rgba(0,0,0,0.3); }
.wet-event-meta-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.wet-meta-item { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.1); padding: 16px; border-radius: 12px; backdrop-filter: blur(10px); }
.wet-meta-item .dashicons { font-size: 24px; width: 24px; height: 24px; opacity: 0.9; }
.wet-meta-label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; }
.wet-meta-value { font-size: 16px; font-weight: 600; }
.wet-event-body { background: #fff; padding: 40px 30px; }
.wet-event-body h2 { font-size: 24px; font-weight: 700; margin: 0 0 24px; color: #1d2327; padding-bottom: 12px; border-bottom: 2px solid var(--wet-primary); }
.wet-description-content { color: #444; line-height: 1.8; font-size: 16px; }
.wet-ticket-types-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
.wet-ticket-card { background: #fafafa; border-radius: 12px; padding: 24px; border: 1px solid #e8e8e8; transition: all 0.3s; position: relative; }
.wet-ticket-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); border-color: var(--wet-primary); }
.wet-ticket-card.sold-out { opacity: 0.6; background: #f5f5f5; }
.wet-ticket-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-left: 16px; border-left: 4px solid var(--wet-primary); }
.wet-ticket-header h3 { margin: 0; font-size: 18px; font-weight: 700; }
.wet-ticket-category { background: var(--wet-primary); color: #fff; padding: 2px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.wet-ticket-desc { color: #646970; font-size: 14px; margin: 0 0 16px; }
.wet-ticket-price { margin: 16px 0; display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
.wet-price-current { font-size: 28px; font-weight: 800; color: var(--wet-primary); }
.wet-price-original { font-size: 16px; color: #999; text-decoration: line-through; }
.wet-early-bird-tag { background: #46b450; color: #fff; padding: 4px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.wet-early-bird-count { background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 10px; font-size: 12px; font-weight: 600; }
.wet-ticket-availability { margin: 12px 0; }
.wet-remaining { font-size: 13px; color: #46b450; font-weight: 600; }
.wet-ticket-features { list-style: none; padding: 0; margin: 16px 0 0; }
.wet-ticket-features li { padding: 8px 0; font-size: 13px; color: #444; border-bottom: 1px solid #f0f0f0; position: relative; padding-left: 20px; }
.wet-ticket-features li::before { content: '✓'; position: absolute; left: 0; color: var(--wet-primary); font-weight: bold; }
.wet-ticket-features li:last-child { border-bottom: none; }
.wet-ticket-actions { margin-top: 20px; padding-top: 16px; border-top: 1px solid #f0f0f0; }
.wet-btn-buy { width: 100%; padding: 14px; font-size: 15px; font-weight: 600; }
.wet-event-organizer { margin-top: 40px; padding-top: 24px; border-top: 1px solid #e8e8e8; }
.wet-organizer-info { color: #646970; line-height: 1.8; }

@media (max-width: 768px) {
	.wet-event-meta-grid { grid-template-columns: repeat(2, 1fr); }
	.wet-hero-content { padding: 24px; }
	.wet-event-title { font-size: 28px; }
	.wet-event-body { padding: 24px; }
}
</style>