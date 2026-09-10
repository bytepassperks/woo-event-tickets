<?php
/**
 * Widgets for Woo Event Tickets
 */

// Recent Check-ins Widget
class Woo_Event_Tickets_Recent_Checkins_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'wet_recent_checkins', __( 'WET: Recent Check-ins', 'wootickets' ), [
			'description' => __( 'Display recent check-ins for an event.', 'wootickets' ),
		] );
	}

	public function widget( $args, $instance ) {
		$event_id = ! empty( $instance['event_id'] ) ? intval( $instance['event_id'] ) : 0;
		$limit = ! empty( $instance['limit'] ) ? intval( $instance['limit'] ) : 5;
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Recent Check-ins', 'wootickets' );

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		$checkins = Woo_Event_Tickets_Checkin::get_recent( $event_id, $limit );

		if ( ! empty( $checkins ) ) {
			echo '<ul class="wet-widget-checkins">';
			foreach ( $checkins as $checkin ) {
				$status_class = 'wet-status-' . $checkin['result'];
				echo '<li>';
				echo '<span class="wet-checkin-time">' . date_i18n( 'H:i', strtotime( $checkin['scanned_at'] ) ) . '</span> ';
				echo '<span class="wet-checkin-ticket">' . esc_html( $checkin['ticket_id'] ) . '</span> ';
				echo '<span class="wet-status-badge ' . $status_class . '">' . esc_html( ucfirst( $checkin['result'] ) ) . '</span>';
				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo '<p class="wet-no-data">' . esc_html__( 'No check-ins yet.', 'wootickets' ) . '</p>';
		}

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$event_id = ! empty( $instance['event_id'] ) ? $instance['event_id'] : 0;
		$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : 5;
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$events = Woo_Event_Tickets_Events::get_dropdown_options();
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'wootickets' ); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'event_id' ); ?>"><?php esc_html_e( 'Event:', 'wootickets' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'event_id' ); ?>" name="<?php echo $this->get_field_name( 'event_id' ); ?>" class="widefat">
				<option value="0"><?php esc_html_e( 'All Events', 'wootickets' ); ?></option>
				<?php foreach ( $events as $id => $name ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $event_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php esc_html_e( 'Number of items:', 'wootickets' ); ?></label>
			<input type="number" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo esc_attr( $limit ); ?>" class="small-text" min="1" max="20">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = [];
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['event_id'] = intval( $new_instance['event_id'] );
		$instance['limit'] = intval( $new_instance['limit'] );
		return $instance;
	}
}

// Revenue Widget
class Woo_Event_Tickets_Revenue_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'wet_revenue', __( 'WET: Event Revenue', 'wootickets' ), [
			'description' => __( 'Display revenue summary for an event.', 'wootickets' ),
		] );
	}

	public function widget( $args, $instance ) {
		$event_id = ! empty( $instance['event_id'] ) ? intval( $instance['event_id'] ) : 0;
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Event Revenue', 'wootickets' );

		if ( ! $event_id ) {
			return;
		}

		$stats = Woo_Event_Tickets_Events::get_stats( $event_id );

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		echo '<div class="wet-widget-revenue">';
		echo '<div class="wet-revenue-item">';
		echo '<span class="wet-revenue-label">' . esc_html__( 'Total Sold', 'wootickets' ) . '</span>';
		echo '<span class="wet-revenue-value">' . number_format( $stats['total_sold'] ) . '</span>';
		echo '</div>';
		echo '<div class="wet-revenue-item">';
		echo '<span class="wet-revenue-label">' . esc_html__( 'Checked In', 'wootickets' ) . '</span>';
		echo '<span class="wet-revenue-value" style="color: #46b450;">' . number_format( $stats['checked_in'] ) . '</span>';
		echo '</div>';
		echo '<div class="wet-revenue-item">';
		echo '<span class="wet-revenue-label">' . esc_html__( 'Remaining', 'wootickets' ) . '</span>';
		echo '<span class="wet-revenue-value">' . number_format( $stats['remaining'] ) . '</span>';
		echo '</div>';
		echo '<div class="wet-revenue-item">';
		echo '<span class="wet-revenue-label">' . esc_html__( 'Revenue', 'wootickets' ) . '</span>';
		echo '<span class="wet-revenue-value" style="color: #0066cc;">' . wc_price( $stats['total_revenue'] ) . '</span>';
		echo '</div>';
		echo '</div>';

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$event_id = ! empty( $instance['event_id'] ) ? $instance['event_id'] : 0;
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$events = Woo_Event_Tickets_Events::get_dropdown_options();
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'wootickets' ); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'event_id' ); ?>"><?php esc_html_e( 'Event:', 'wootickets' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'event_id' ); ?>" name="<?php echo $this->get_field_name( 'event_id' ); ?>" class="widefat">
				<option value="0"><?php esc_html_e( 'Select Event', 'wootickets' ); ?></option>
				<?php foreach ( $events as $id => $name ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $event_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = [];
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['event_id'] = intval( $new_instance['event_id'] );
		return $instance;
	}
}