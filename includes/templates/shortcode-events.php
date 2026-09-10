<?php
/**
 * Shortcode: [ticket_events] template
 * @var array $events
 * @var array $atts
*/
$columns = intval( $atts['columns'] );
$show_date = $atts['show_date'] === 'yes';
$show_venue = $atts['show_venue'] === 'yes';
?>
<div class="wet-events-grid wet-columns-<?php echo esc_attr( $columns ); ?>">
	<?php foreach ( $events as $event ) : 
		$stats = Woo_Event_Tickets_Events::get_stats( $event['id'] );
		$is_upcoming = strtotime( $event['start_datetime'] ) > current_time( 'timestamp' );
		$is_ongoing = strtotime( $event['start_datetime'] ) <= current_time( 'timestamp' ) && strtotime( $event['end_datetime'] ) >= current_time( 'timestamp' );
		$status_class = $is_upcoming ? 'upcoming' : ( $is_ongoing ? 'ongoing' : 'past' );
	?>
	<article class="wet-event-card wet-status-<?php echo esc_attr( $status_class ); ?>">
		<?php if ( $event['banner_url'] ) : ?>
			<div class="wet-event-banner" style="background-image: url('<?php echo esc_url( $event['banner_url'] ); ?>');"></div>
		<?php else : ?>
			<div class="wet-event-banner" style="background: linear-gradient(135deg, <?php echo esc_attr( $event['primary_color'] ); ?>, <?php echo esc_attr( $event['secondary_color'] ); ?>);"></div>
		<?php endif; ?>
		
		<div class="wet-event-content">
			<div class="wet-event-meta">
				<span class="wet-event-status wet-status-<?php echo esc_attr( $status_class ); ?>">
					<?php echo esc_html( ucfirst( $status_class ) ); ?>
				</span>
			</div>
			
			<h3 class="wet-event-title"><?php echo esc_html( $event['name'] ); ?></h3>
			
			<?php if ( $show_date ) : ?>
				<div class="wet-event-date">
					<span class="dashicons dashicons-calendar-alt"></span>
					<?php echo date_i18n( 'F j, Y', strtotime( $event['start_datetime'] ) ); ?>
					<?php if ( $event['end_datetime'] && date( 'Y-m-d', strtotime( $event['start_datetime'] ) ) !== date( 'Y-m-d', strtotime( $event['end_datetime'] ) ) ) : ?>
						– <?php echo date_i18n( 'F j, Y', strtotime( $event['end_datetime'] ) ); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			
			<?php if ( $show_venue && $event['venue'] ) : ?>
				<div class="wet-event-venue">
					<span class="dashicons dashicons-location-alt"></span>
					<?php echo esc_html( $event['venue'] ); ?>
					<?php if ( $event['city'] ) : ?>, <?php echo esc_html( $event['city'] ); ?><?php endif; ?>
				</div>
			<?php endif; ?>
			
			<?php if ( $event['description'] ) : ?>
				<div class="wet-event-excerpt"><?php echo wp_trim_words( $event['description'], 30 ); ?></div>
			<?php endif; ?>
			
			<div class="wet-event-stats">
				<span class="wet-stat"><span class="wet-stat-value"><?php echo number_format( $stats['total_sold'] ); ?></span> <?php esc_html_e( 'Sold', 'wootickets' ); ?></span>
				<span class="wet-stat"><span class="wet-stat-value"><?php echo number_format( $stats['remaining'] ); ?></span> <?php esc_html_e( 'Left', 'wootickets' ); ?></span>
			</div>
			
			<a href="<?php echo esc_url( add_query_arg( 'event_id', $event['id'], get_permalink() ) ); ?>" class="wet-event-btn"><?php esc_html_e( 'View Tickets', 'wootickets' ); ?></a>
		</div>
	</article>
	<?php endforeach; ?>
</div>

<style>
.wet-events-grid { display: grid; grid-template-columns: repeat(<?php echo esc_attr( $columns ); ?>, 1fr); gap: 24px; }
.wet-event-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.3s, box-shadow 0.3s; position: relative; }
.wet-event-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.12); }
.wet-event-banner { height: 180px; background-size: cover; background-position: center; position: relative; }
.wet-event-banner::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 60px; background: linear-gradient(transparent, rgba(0,0,0,0.7)); }
.wet-event-content { padding: 24px; position: relative; }
.wet-event-meta { position: absolute; top: 16px; right: 16px; z-index: 2; }
.wet-event-status { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; color: #fff; }
.wet-event-status.upcoming { background: #0066cc; }
.wet-event-status.ongoing { background: #46b450; animation: pulse 2s infinite; }
.wet-event-status.past { background: #999; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
.wet-event-title { margin: 0 0 12px; font-size: 20px; font-weight: 700; color: #1d2327; }
.wet-event-date, .wet-event-venue { display: flex; align-items: center; gap: 6px; font-size: 14px; color: #646970; margin-bottom: 8px; }
.wet-event-date .dashicons, .wet-event-venue .dashicons { font-size: 16px; width: 16px; height: 16px; }
.wet-event-excerpt { color: #646970; font-size: 14px; line-height: 1.6; margin-bottom: 16px; }
.wet-event-stats { display: flex; gap: 24px; margin-bottom: 20px; padding-top: 16px; border-top: 1px solid #f0f0f0; }
.wet-stat { display: flex; flex-direction: column; }
.wet-stat-value { font-size: 18px; font-weight: 700; color: #1d2327; }
.wet-event-btn { display: block; text-align: center; background: #0066cc; color: #fff; padding: 14px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.2s; }
.wet-event-btn:hover { background: #0052a3; }

@media (max-width: 768px) {
	.wet-events-grid { grid-template-columns: 1fr; }
}
</style>