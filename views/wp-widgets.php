<?php

/**
 * Defines widgets for WordPress.
 *
 * @since 0.1
 * @author Kunal Bhalla
 */

/**
 * Shows the 5 latest events.
 *
 * @since 0.1
 */
class EP_Upcoming_Events extends WP_Widget {
	/**
	 * Constructor. Initializes using parent class.
	 *
	 * @since 0.1
	 */
	function EP_Upcoming_events() {
		parent::WP_Widget( 'ep_upcoming_events', __( 'Upcoming Events', 'eventpress' ) );
	}

	function widget( $args, $instance ) {
		global $post;
		extract( $args );

		echo $before_widget;
		echo $before_title;
		echo $widget_name;
		echo $after_title;

		query_posts( Array(
			'post_type' => 'ep_event',
			'orderby' => 'meta_value',
			'meta_key' => '_ep_start',
			'order' => 'DESC',
			'posts_per_page' => 10
		) );

		echo "<ul>";
		while( have_posts() ) {
			the_post();
			echo "<li><a href = '" . get_permalink() . "'>" . $post->post_title . "</a>";
			echo "<p>" . get_the_excerpt() . "</p></li>";
		}	
		echo "</ul>";

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}

/**
 * Shows a randomly generated list of
 * avatars of attendees.
 *
 * @since 0.1
 */
class EP_Calendar_Widget extends WP_Widget {
	/**
	 * Constructor. Initializes using parent class.
	 *
	 * @since 0.1
	 */
	function EP_Calendar_Widget() {
		parent::WP_Widget( 'ep_calendar', __( 'Events Calendar', 'eventpress' ) );

		if( is_active_widget( false, false, 'ep_calendar' ) ) {
			if( defined( 'EP_BP' ) )
				wp_enqueue_style( 'ep_widget_calendar', EP_REL_URL .'/themes/bp/assets/css/widget_calendar'.kb_ext().'.css' );
			else
				wp_enqueue_style( 'ep_widget_calendar', EP_REL_URL .'/themes/wp/assets/css/widget_calendar'.kb_ext().'.css' );
		}
	}

	function widget( $args, $instance ) {
		extract( $args );

		echo $before_widget;
		echo $before_title;
		echo $widget_name;
		echo $after_title;

		ep_calendar();

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}
