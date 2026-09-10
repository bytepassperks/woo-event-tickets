<?php
/**
 * Shortcode: [ticket_types] template
 * @var array $ticket_types
 * @var array $atts
 */
$show_features = $atts['show_features'] === 'yes';
$show_early_bird = $atts['show_early_bird'] === 'yes';
?>
<div class="wet-ticket-types-list">
	<div class="wet-ticket-types-grid">
		<?php foreach ( $ticket_types as $type ) : 
			$display_price = Woo_Event_Tickets_Ticket_Types::get_display_price( $type );
			$remaining = Woo_Event_Tickets_Ticket_Types::get_remaining_quantity( $type['id'] );
			$product = $type['woocommerce_product_id'] ? wc_get_product( $type['woocommerce_product_id'] ) : null;
			$product_url = $product ? $product->get_permalink() : '#';
		?>
		<article class="wet-ticket-type-card <?php echo $remaining <= 0 ? 'sold-out' : ''; ?>">
			<?php if ( $type['image_url'] ) : ?>
				<div class="wet-ticket-image" style="background-image: url('<?php echo esc_url( $type['image_url'] ); ?>');"></div>
			<?php endif; ?>
			
			<div class="wet-ticket-content">
				<div class="wet-ticket-header">
					<h3><?php echo esc_html( $type['name'] ); ?></h3>
					<?php if ( $type['category'] ) : ?>
						<span class="wet-ticket-category"><?php echo esc_html( ucfirst( $type['category'] ) ); ?></span>
					<?php endif; ?>
				</div>
				
				<?php if ( $type['description'] ) : ?>
					<p class="wet-ticket-desc"><?php echo esc_html( wp_trim_words( $type['description'], 30 ) ); ?></p>
				<?php endif; ?>
				
				<div class="wet-ticket-price">
					<?php if ( $display_price['is_early_bird'] ) : ?>
						<span class="wet-price-current"><?php echo wc_price( $display_price['price'] ); ?></span>
						<span class="wet-price-original"><?php echo wc_price( $display_price['original_price'] ); ?></span>
						<span class="wet-early-bird-tag"><?php esc_html_e( 'Early Bird', 'wootickets' ); ?></span>
					<?php elseif ( ! empty( $display_price['is_sale'] ) ) : ?>
						<del class="wet-price-original"><?php echo wc_price( $display_price['original_price'] ); ?></del>
						<span class="wet-price-current"><?php echo wc_price( $display_price['price'] ); ?></span>
					<?php else : ?>
						<span class="wet-price-current"><?php echo wc_price( $display_price['price'] ); ?></span>
					<?php endif; ?>
				</div>
				
				<?php if ( $show_early_bird && $type['early_bird_price'] && $display_price['early_bird_remaining'] > 0 ) : ?>
					<div class="wet-early-bird-info">
						<span class="dashicons dashicons-clock"></span>
						<?php printf( esc_html__( '%d tickets left at early bird price', 'wootickets' ), $display_price['early_bird_remaining'] ); ?>
					</div>
				<?php endif; ?>
				
				<div class="wet-ticket-availability">
					<?php if ( $remaining > 0 ) : ?>
						<span class="wet-remaining"><?php echo number_format( $remaining ); ?> <?php esc_html_e( 'remaining', 'wootickets' ); ?></span>
					<?php else : ?>
						<span class="wet-sold-out"><?php esc_html_e( 'Sold Out', 'wootickets' ); ?></span>
					<?php endif; ?>
				</div>
				
				<?php if ( $show_features && $type['features'] ) : ?>
					<ul class="wet-ticket-features">
						<?php 
						$features = preg_split( '/\r\n|\r|\n/', $type['features'] );
						foreach ( array_slice( $features, 0, 5 ) as $feature ) :
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
			</div>
		</article>
		<?php endforeach; ?>
	</div>
</div>

<style>
.wet-ticket-types-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
.wet-ticket-type-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s; border: 1px solid #f0f0f0; }
.wet-ticket-type-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.12); }
.wet-ticket-type-card.sold-out { opacity: 0.7; }
.wet-ticket-image { height: 160px; background-size: cover; background-position: center; position: relative; }
.wet-ticket-image::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 40px; background: linear-gradient(transparent, rgba(0,0,0,0.6)); }
.wet-ticket-content { padding: 24px; position: relative; }
.wet-ticket-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.wet-ticket-header h3 { margin: 0; font-size: 18px; font-weight: 700; color: #1d2327; }
.wet-ticket-category { background: #f0f0f0; color: #646970; padding: 4px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.wet-ticket-desc { color: #646970; font-size: 14px; line-height: 1.6; margin: 0 0 16px; }
.wet-ticket-price { margin: 16px 0; display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
.wet-price-current { font-size: 28px; font-weight: 800; color: #0066cc; }
.wet-price-original { font-size: 16px; color: #999; text-decoration: line-through; }
.wet-early-bird-tag { background: #46b450; color: #fff; padding: 4px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.wet-early-bird-info { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #856404; background: #fff3cd; padding: 8px 12px; border-radius: 8px; margin-bottom: 12px; }
.wet-ticket-availability { margin: 12px 0; }
.wet-remaining { font-size: 13px; color: #46b450; font-weight: 600; }
.wet-sold-out { font-size: 13px; color: #dc3232; font-weight: 600; }
.wet-ticket-features { list-style: none; padding: 0; margin: 16px 0 0; }
.wet-ticket-features li { padding: 8px 0 8px 24px; font-size: 13px; color: #444; border-bottom: 1px solid #f0f0f0; position: relative; }
.wet-ticket-features li::before { content: '✓'; position: absolute; left: 0; color: #0066cc; font-weight: bold; }
.wet-ticket-features li:last-child { border-bottom: none; }
.wet-ticket-actions { margin-top: 20px; padding-top: 16px; border-top: 1px solid #f0f0f0; }
.wet-btn-buy { width: 100%; padding: 14px; font-size: 15px; font-weight: 600; }

@media (max-width: 768px) {
	.wet-ticket-types-grid { grid-template-columns: 1fr; }
}
</style>