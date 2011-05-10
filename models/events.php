<?php
/**
 * Contains the class namespacing the events model.
 *
 * For both WordPress and BuddyPress.
 *
 * @author Kunal Bhalla
 * @since 0.1
 */

/**
 * The events model
 * 
 * Handles all data related functions for events, as well
 * as the arguments for event registration.
 * The arguments were moved into the model as these would be
 * obtained from the specified options and it didn't make sense
 * to put all that code in the controller.
 *
 * @since 0.1
 */
class ep_event_model {

	/**
	 * Map user capabilities for events.
	 *
	 * Performs standard meta capabilitiy mapping, similar to post.
	 *
	 * @since 0.1
	 *
	 * @param $caps The capabilities assigned by the map_meta_caps function
	 * @param $cap The capability to be map
	 * @param $userid The user for whom capabilities are being mapped.
	 * @param $args Generally an array containing the postid. Can be extended.
	 * @return The required capabilities.
	 */
	function map_capabilities( $caps, $cap, $userid, $args ) {
		global $post;

		if ( isset( $post ) ) $currentid = $post->ID; else $currentid = 0;
		if ( isset( $args[0] ) && $args[0] == (int) $args[0] ) $currentid = $args[0];

		$currentpost = get_post( $currentid );

		if ( isset( $currentpost ) && $currentpost->post_type == 'ep_event' ) {
			switch( $cap ) {
				case 'edit_event':
					$caps = Array();
					
					if ( $currentpost->post_author == $userid )
						$caps[] = 'edit_events';
					else 
						$caps[] = 'edit_others_events';

					if ( $currentpost->post_status == 'publish' )
						$caps[] = 'edit_published_events';
					else if ( $currentpost->post_status == 'private' )
						$caps[] = 'edit_private_events';

					break;

				case 'delete_event':
					$caps = Array();

					if ( $currentpost->post_author == $userid )
						$caps[] = 'delete_events';
					else
						$caps[] = 'delete_others_events';

					if ( $currentpost->post_status == 'publish' )
						$caps[] = 'delete_published_events';
					else if ( $currentpost->post_status == 'private' )
						$caps[] = 'delete_private_events';

					break;
				case 'read_event':
					$caps = Array();

					if ( $currentpost->post_status == 'private' && $currentpost->post_author != $userid )
						$caps[] = 'read_private_events';
					else
						$caps[] = 'read';
		
					break;
			}
		}

		$caps = apply_filters( 'ep_event_mapcaps', $caps, $cap, $userid, $args );
		return $caps;
	}

	/**
	 * Return arguments for registering event tags.
	 *
	 * @since 0.1
	 *
	 * @return Array arguments
	 */
	function register_tags() {
		$label_tags = Array(
			'name'			=> _x( 'Event Tags', 'Taxonomy General Name' ),
			'singular_name'		=> _x( 'Event Tags', 'Taxonomy Singular Name' ),
			'search_items'		=> __( 'Search Event Tags' ),
			'popular_items'		=> __( 'Popular Events' ),
			'all_items'		=> __( 'All Event Tags' ),
			'parent_item'		=> __( 'Parent Event Tag' ),
			'parent_item_colon'	=> __( 'Parent Event Tag:' ),
			'edit_item'		=> __( 'Edit Event Tag' ),
			'update_item'		=> __( 'Update Event Tag' ),
			'add_new_item'		=> __( 'Add New Event Tag' ),
			'new_item_name'		=> __( 'New Event Tag Name' )
		);
		$arg_tags = Array(
			'labels'		=> $label_tags,
			'show_ui'		=> true,
			'public'		=> true,
			'show_tagcloud'		=> false,
			'hierarchical'		=> false
		);

		$arg_tags = apply_filters( 'ep_tag_args', $arg_tags );
		return $arg_tags;
	}

	/**
	 * Return arguments for registering event categories.
	 *
	 * @since 0.1
	 *
	 * @return Array arguments
	 */
	function register_cats() {
		$label_cats = Array(
			'name'			=> _x( 'Event Categories', 'Taxonomy General Name' ),
			'singular_name'		=> _x( 'Event Categories', 'Taxonomy Singular Name' ),
			'search_items'		=> __( 'Search Event Categories' ),
			'popular_items'		=> __( 'Popular Events' ),
			'all_items'		=> __( 'All Event Categories' ),
			'parent_item'		=> __( 'Parent Event Category' ),
			'parent_item_colon'	=> __( 'Parent Event Category:' ),
			'edit_item'		=> __( 'Edit Event Category' ),
			'update_item'		=> __( 'Update Event Category' ),
			'add_new_item'		=> __( 'Add New Event Category' ),
			'new_item_name'		=> __( 'New Event Category Name' )
		);
		$arg_cats = Array(
			'labels'		=> $label_cats,
			'show_ui'		=> true,
			'public'		=> true,
			'show_tagcloud'		=> false,
			'hierarchical'		=> true
		);
		$arg_cats = apply_filters( 'ep_cat_args', $arg_cats );
		return $arg_cats;
	}

	/**
	 * Returns arguments for registering the post type.
	 *
	 * @since 0.1
	 *
	 * @return Array arguments
	 */
	function register_event_type() {
		$labels = Array(
			'name' 			=> __( 'Events', 'eventpress' ),
			'singular_name' 	=> __( 'Event' , 'eventpress' ),
			'add_new' 		=> _x( 'Create New', 'Event' , 'eventpress' ),
			'add_new_item' 		=> __( 'Create New Event' , 'eventpress' ),
			'edit_item' 		=> __( 'Edit Event' , 'eventpress' ),
			'edit' 			=> _x( 'Edit', 'Event' , 'eventpress' ),
			'new_item' 		=> __( 'New Event' , 'eventpress' ),
			'view_item' 		=> __( 'View Event' , 'eventpress' ),
			'search_items' 		=> __( 'Search Events' , 'eventpress' ),
			'not_found' 		=> __( 'No events found' , 'eventpress' ),
			'not_found_in_trash' 	=> __( 'No events found in trash' , 'eventpress' )
		);

		$supports = Array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions', 'comments', 'custom-fields' );

		$taxonomies = Array( 'event_category', 'event_tag' );

		global $ep_controllers;
		$args = Array(
			'labels'		=> $labels,
			'description' 		=> __( 'Create Events &mdash; people can RSVP, comment, fix venue, etc.' ),
			'public' 		=> true,
			'show_ui'		=> true,
			'capability_type'	=> 'event',
			'supports'		=> $supports,
			'menu_position' 	=> 30,
			'taxonomies'		=> $taxonomies,
			'register_meta_box_cb'	=> Array( &$ep_controllers['wp'], 'register_meta_boxes' ),
			'rewrite'		=> Array( 'slug' => 'event', 'with_front' => true )
		);

		$args = apply_filters( 'ep_event_post_type', $args );
		return $args;
	}

	/**
	 * Returns arguments for registering the post type
	 * using BuddyPress Custom Posts
	 *
	 * @since 0.1
	 *
	 * @return Array arguments
	 */
	function register_event_type_bp() {
		global $ep_views;

		$labels = Array(
			'my_posts' 		=> __( 'My Events (%s)' , 'eventpress' ),
			'posts_directory'	=> __( 'Events Directory' , 'eventpress' ),
			'name'			=> __( 'Events' , 'eventpress' ),
			'all_posts'		=> __( 'All Events (%s)' , 'eventpress' ),
			'type_creator'		=> __( 'Event Creator' , 'eventpress' ),
			'activity_tab'		=> __( 'Events' , 'eventpress' ),
			'show_created'		=> __( 'Show New Events' , 'eventpress' ),
			'my_posts_public_activity' => __( 'My Events - Public Activity' , 'eventpress' )
		);

		$activity = Array(
			'create_posts' => true,
			'edit_posts' => true
		);

		$args = Array(
			'id'		=> 'ep_event',
			'nav'		=> true,
			'theme_nav'	=> true,
			'labels'	=> $labels,
			'format_notifications' => Array( &$ep_views['template'], 'activity_notifications' ),
			'theme_dir'	=> EP_THEMES_DIR . '/bp',
			'activity'	=> $activity,
			'forum'		=> true
		);

		$args = apply_filters( 'ep_event_post_type_bp', $args );
		return $args;
	}

	/**
	 * Returns capabilities to be granted to an event creator.
	 *
	 * @since 0.1
	 *
	 * @return Array of capabilities for event creator.
	 */
	function create_role() {
		$role_caps = Array(
				'edit_event' => true,
				'read_event' => true,
				'delete_event' => true,
				'edit_events' => true,
				'edit_published_events' => true,
				'publish_events' => true,
				'delete_events' => true,
				'delete_published_events' => true,
				'read_events' => true,
				'read' => true
		); 
		return apply_filters( 'ep_role_caps', $role_caps );
	}

	/**
	 * Returns arguments to extend user capabilities to support event editing.
	 *
	 * Grants capabilities according to the post capabilities,
	 * to the administrator, author, etc. However, also granting
	 * the core capabilities edit_event, read_event, delete_event,
	 * etc. as for some reason wp-admin tries to check against these
	 * too.
	 *
	 * @uses $wp_roles
	 *
	 * @return Array Arguments
	 */
	function extend_capabilities() {
		global $wp_roles;

		$extend_caps = Array(
			'administrator' => Array( 
				'edit_event',
				'read_event',
				'delete_event',
				'edit_events',
				'edit_others_events',
				'edit_published_events',
				'publish_events',
				'delete_events',
				'delete_others_events',
				'delete_published_events',
				'delete_private_events',
				'edit_private_events',
				'read_events',
				'read_private_events'
			),
			'editor' => Array( 
				'edit_event',
				'read_event',
				'delete_event',
				'edit_events',
				'edit_others_events',
				'edit_published_events',
				'publish_events',
				'delete_events',
				'delete_others_events',
				'delete_published_events',
				'delete_private_events',
				'edit_private_events',
				'read_events',
				'read_private_events'
			),
			'author' => Array(
				'edit_event',
				'read_event',
				'delete_event',
				'edit_events',
				'edit_published_events',
				'publish_events',
				'delete_events',
				'delete_published_events',
				'read_events'
			),
			'contributor' => Array(
				'edit_event',
				'read_event',
				'edit_events',
				'read_events'
			),
			'subscriber' => Array(
				'read_event',
				'read_events'
			)
		);

		return apply_filters( 'ep_extend_caps', $extend_caps );
	}

	/**
	 * Returns the messages to over-ride the pre-set post 
	 * submission messages.
	 *
	 * @since 0.1
	 *
	 * @uses $post_ID
	 * @uses $post
	 *
	 * @return Array of messages
	 */
	function update_messages( $messages ) {
		global $post_ID, $post;

		$messages['ep_event'] = Array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => sprintf( __('Event updated. <a href="%s">View event</a>', 'eventpress' ), esc_url( get_permalink($post_ID) ) ),
			 2 => __('Custom field updated.', 'eventpress'),
			 3 => __('Custom field deleted.', 'eventpress'),
			 4 => __('Event updated.', 'eventpress'),
			 5 => isset($_GET['revision']) ? sprintf( __('Event restored to revision from %s', 'eventpress'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __('Event published. <a href="%s">View event</a>', 'eventpress'), esc_url( get_permalink($post_ID) ) ),
			 7 => __('Event saved.', 'eventpress'),
			 8 => sprintf( __('Event submitted. <a target="_blank" href="%s">Preview event</a>', 'eventpress'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			 9 => sprintf( __('Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>', 'eventpress'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Event draft updated. <a target="_blank" href="%s">Preview event</a>', 'eventpress'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return apply_filters( 'ep_admin_messages', $messages );
	}

	/**
	 * Check if a given event is moderated.
	 *
	 * @param $id The event id
	 * @return bool whether it is or not
	 */
	function is_moderated( $id ) {
		$is_moderated = true;

		if( get_post_meta( $id, '_ep_confirmreg', true ) == 'autoreg' )
			$is_moderated = false;

		return apply_filters( 'ep_is_moderated', $is_moderated, $id );
	}

	/**
	 * Returns the number of people who have registered for an event.
	 *
	 * Runs a query to count how many people have registered for an
	 * event and stores in a cache. ToDo Convert to a meta value that is
	 * updated with each registration instead to avoid a potentially 
	 * expensive query.
	 *
	 * @param $id The event id
	 * @return Array The number of registrations grouped by status
	 */
	function get_registration_number( $id ) {
		global $wpdb;
		$cache_key = $id;

		$count = wp_cache_get( $cache_key, 'ep_reg_counts' );
		if ( false != $count ) return $count;

		$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type='ep_reg' AND post_parent=%d GROUP BY post_status;";
		$count = $wpdb->get_results( $wpdb->prepare( $query, $id ), ARRAY_A );

		$stats = array();
		foreach ( get_post_stati() as $state )
			$stats[$state] = 0;

		foreach ( (array) $count as $row )
			$stats[$row['post_status']] = $row['num_posts'];

		$stats = (object) $stats;
		wp_cache_set($cache_key, $stats, 'ep_reg_counts');

		return apply_filters( 'ep_reg_count', $stats, $id );
	}

	/**
	 * Gets the registration status for the event.
	 *
	 * Based on registration starting data, end date, event end
	 * date, etc. returns whether registration is currently open
	 * or not, and why.
	 *
	 * @since 0.1
	 *
	 * @param $id eventid
	 * @return The current registration status
	 */
	function registration_status( $id = 0 ) {
		global $post;

		if ( !isset( $post ) && 0 == $id )
			return false;
		else if ( 0 == $id ) 
			$eventid = $post->ID;
		else 
			$eventid = $id;

		$startreg = (int) get_post_meta( $eventid, "_ep_startreg", true );
		$stopreg = (int) get_post_meta( $eventid, "_ep_stopreg", true );
		$end = (int) get_post_meta( $eventid, "_ep_end", true );

		if( $stopreg && time() > $stopreg )
			return Array(
				'status' => 'past',
				'stopreg' => $stopreg
			);
		else if ( $startreg && time() < $startreg )
			return Array(
				'status' => 'future',
				'startreg' => $startreg
			);
		else if ( $end && time() > $end )
			return Array(
				'status' => 'over',
				'end'	=> $end
			);
		else
			return Array( 
				'status' => 'open'
			);
	}

	/**
	 * Filter function for adding the custom part to the query.
	 *
	 * @since 0.1
	 *
	 * @uses $ep_month_query
	 * @uses $wpdb
	 * @uses $ep_year_query
	 */
	function _filter_fun( $args ) {
		global $ep_month_query, $ep_year_query, $wpdb;
		$args .= " AND MONTH( FROM_UNIXTIME( {$wpdb->postmeta}.meta_value ) ) = $ep_month_query AND YEAR( FROM_UNIXTIME( {$wpdb->postmeta}.meta_value ) ) = $ep_year_query";
		return $args;
	}

	/**
	 * Query by event's starting day month.
	 *
	 * Filters the query to perform some magic.
	 *
	 * @since 0.1
	 *
	 * @uses $ep_month_query Stores month being queried for
	 */
	function query_month( $month, $year ) {
		global $ep_month_query, $ep_year_query;
		$ep_month_query = $month;
		$ep_year_query = $year;

		add_filter( 'posts_where', Array( &$this, '_filter_fun' ) );
		query_posts( 'post_type=ep_event&meta_key=_ep_start&orderby=meta_value&order=ASC&nopaging=true' );
		remove_filter( 'posts_where', Array( &$this, '_filter_fun' ) );
	} 
}

global $ep_models; 

//Initiates the events model.
$ep_models['events'] = new ep_event_model();
