<?php

/** 
 * registration.php
 *
 * Contains the registration model, instance. Stored in $ep_models['registration']
 *
 * @author Kunal Bhalla
 *
 * @since 0.1
 */

class ep_registration_model {

	/**
	 * Map registration editing capabilities to parent post capabilities.
	 *
	 * As registration editing is tied to the parent post, maps accordingly.
	 *
	 * @param Array $caps The current required caps array
	 * @param Array $cap The cap being mapped
	 * @param int $userid
	 * @param Array $args Extra arguments (Generally just the postid)
	 *
	 * @since 0.1
	 */
	function map_capabilities( $caps, $cap, $userid, $args ) {
		global $post;

		if ( isset( $post ) ) $currentid = $post->ID; else $currentid = 0;
		if ( isset( $args[0] ) && $args[0] == (int) $args[0] ) $currentid = $args[0];

		$currentpost = get_post( $currentid );

		if ( isset( $currentpost ) && $currentpost->post_type == 'ep_reg' ) {
			$currentpost = get_post( $currentpost->post_parent );

			if( isset( $currentpost ) and $currentpost->post_type == 'ep_event' ) {
				switch( $cap ) {
					case 'edit_reg':
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

					case 'delete_reg':
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
					case 'read_reg':
						$caps = Array();

						if ( $currentpost->post_status == 'private' && $currentpost->post_author != $userid )
							$caps[] = 'read_private_events';
			
						break;
				}
			}
		}

		switch( $cap ) {
			case 'edit_regs':
			case 'edit_others_regs':
			case 'edit_published_regs':
			case 'publish_regs':
			case 'delete_regs':
			case 'delete_others_regs':
			case 'delete_published_regs':
			case 'delete_private_regs':
			case 'edit_private_regs':
			case 'read_regs':
			case 'read_private_regs':
				$caps = Array();
				$caps[] = preg_replace( '/regs$/', 'events', $cap );
		}

		return apply_filters( 'ep_reg_mapcaps', $caps, $cap, $userid, $args );
	}

	/**
	 * Returns arguments for registering this post type.
	 *
	 * @since 0.1
	 */
	function register_reg_type() {

		$labels = Array(
			'name' 			=> __( 'Registrations', 'eventpress' ),
			'singular_name' 	=> __( 'Registration', 'eventpress' ),
			'add_new' 		=> _x( 'Create New', 'Event', 'eventpress' ),
			'add_new_item' 		=> __( 'Register', 'eventpress' ),
			'edit_item' 		=> __( 'Edit Registration Details', 'eventpress' ),
			'edit' 			=> _x( 'Edit', 'Event', 'eventpress' ),
			'new_item' 		=> __( 'New Registration', 'eventpress' ),
			'view_item' 		=> __( 'View Registrations', 'eventpress' ),
			'search_items' 		=> __( 'Search Registrations', 'eventpress' ),
			'not_found' 		=> __( 'No registration found', 'eventpress' ),
			'not_found_in_trash' 	=> __( 'No registrations found in trash', 'eventpress' )
		);

		$supports = Array( 'author', 'custom-fields' );

		global $ep_controllers;
		$args = Array(
			'labels'		=> $labels,
			'description' 		=> __( 'Registration details for events', 'eventpress' ),
			'public' 		=> true,
			'show_ui'		=> true,
			'capability_type'	=> 'reg',
			'supports'		=> $supports,
			'menu_position' 	=> 30,
			'rewrite'		=> false,
			'hierarchical' 		=> true,  
			'register_meta_box_cb'	=> Array( &$ep_controllers['wp'], 'register_reg_meta_boxes' ),
		);

		return apply_filters( 'ep_reg_post_type', $args );
	}

	/**
	 * Add extra capabilities for the reg post type.
	 *
	 * @since 0.1
	 */
	function extend_capabilities() {
		//There has to be a better way to do this?
		global $wp_roles;

		$extend_caps = Array(
			'administrator' => Array( 
				//'edit_reg',
				'edit_regs',
				'edit_others_regs',
				'publish_regs',
				'read_regs',
				//'delete_reg',
				'read_private_regs'
			),
			'subscriber' => Array(
				'read_regs',
				'publish_regs',
				'edit_regs'
			)
		);
		return apply_filters( 'ep_reg_caps', $extend_caps );

	}

	/**
	 * Modify the post creation messages in the admin.
	 *
	 * @param string array $messages Existing array of messages, grouped by post type
	 *
	 * @since 0.1
	 */
	function update_messages( $messages ) {
		global $post_ID, $post;

		$messages['ep_reg'] = Array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => sprintf( __('Registration updated. <a href="%s">View registration</a>', 'eventpress' ), esc_url( get_permalink($post_ID) ) ),
			 2 => __('Custom field updated.', 'eventpress' ),
			 3 => __('Custom field deleted.', 'eventpress' ),
			 4 => __('Registration updated.', 'eventpress' ),
			 5 => isset($_GET['revision']) ? sprintf( __('Registration restored to revision from %s', 'eventpress' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __('Registration published. <a href="%s">View registration</a>', 'eventpress' ), esc_url( get_permalink($post_ID) ) ),
			 7 => __('Registration saved.', 'eventpress' ),
			 8 => sprintf( __('Registration submitted. <a target="_blank" href="%s">Preview registration</a>', 'eventpress' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			 9 => sprintf( __('Registration scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview registration</a>', 'eventpress' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Registration draft updated. <a target="_blank" href="%s">Preview registration</a>', 'eventpress' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return $messages;
	}

	/** 
	 * Returns the arguments for registering the custom post statuses for registration.
	 *
	 * @since 0.1
	 */
	function register_registration_statuses() {
		return Array(
			'reg_approved' => Array(
				'label' => _x( 'Approved', 'registration', 'eventpress' ),
				'label_count' => _n_noop( 'Approved <span class="count">(%s)</span>', 'Approved <span class="count">(%s)</span>', 'eventpress' ),
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'public' => true
			),
			'reg_pending' => Array(
				'label' => _x( 'Pending Approval', 'registration', 'eventpress' ),
				'label_count' => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'eventpress' ),
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'public' => true
			),
			'reg_cancelled' => Array(
				'label' => _x( 'Cancelled', 'registration', 'eventpress' ),
				'label_count' => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'eventpress' ),
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'public' => true
			)
		);
	}

	/**
	 * Register a user for an event.
	 *
	 * Accepts arguments:
	 *	userid Which user
	 *	eventid which event
	 *	approved Approved registration?
	 *
	 * @param Array $args  
	 *
	 * @since 0.1
	 */
	function register( $args ) {
		global $ep_models;
		
		$defaults = Array( 
			'userid' => 0,
			'eventid' => 0,
			'approved' => false,
			'data' => Array()
		);
		extract( wp_parse_args( $args, $defaults ) );

		
		if ( $ep_models['events']->is_moderated( $eventid ) && !$approved ) 
			$post_status = 'reg_pending';
		else 
			$post_status = 'reg_approved';

		$post_status = apply_filters( 'ep_reg_status', $post_status, $args );
		$postid = wp_insert_post(
			Array(
				'post_status' 	=> $post_status,
				'post_type'	=> 'ep_reg',
				'post_author'	=> $userid,
				'ping_status'	=> false,
				'post_parent'	=> $eventid,
				'menu_order'	=> 0,
				'to_ping'	=> false,
				'post_content'	=> '',
				'post_title'	=> "$userid-$eventid"
			),
		true);

		//Save the registration metadata as metadata -- helps in sorting, UI elements

		/** @todo While I get UI elements, and sorting capability, there can be a 
		          performance enhancement here by saving this in content (saves an extra join).
			  At the very least, use an alternative to speed things up by adding post meta data
			  using a single query instead of multiple entries. */

		$date = apply_filters( 'ep_reg_data', $data, $args, $postid );
		foreach( $data as $key => $val )
			update_post_meta( $postid, $key, $val );

		return $postid;
	}

	/**
	 * Unregister a user.
	 *
	 * Accepts arguments:
	 *	userid Which user
	 *	eventid which event
	 *	approved Approved registration?
	 *
	 * @param Array $args  
	 *
	 * @since 0.1
	 */
	function unregister( $args ) {
		global $ep_models;
		
		$defaults = Array( 
			'userid' => 0,
			'eventid' => 0,
			'data' => Array()
		);
		extract( wp_parse_args( $args, $defaults ) );

		$posts = query_posts( Array( 'post_parent' => $eventid, 'post_type' => 'ep_reg', 'post_author' => $userid ) );

		if( isset( $posts[0] ) ) {
			wp_delete_post( $posts[0]->ID );
			return true;
		}

		return false;
	}

	/**
	 * The Registration loop inner functions and variables.
	 * Only meant for use within an external WordPress loop.
	 *
	 * @todo Move to kb_loop instead.
	 *
	 * @since 0.1
	 */
	var $registrants;
	var $registrant;
	var $counter;
	var $count;
	var $in_the_loop;

	function get_avail_registrations() {
		global $post, $wpdb, $current_user;

		get_currentuserinfo();

		$query = "SELECT * FROM $wpdb->posts WHERE post_parent = %d AND ( post_status = 'reg_approved' OR ( post_author = %d AND post_status != 'trash' ) ) AND post_type = 'ep_reg' ORDER BY post_date";
		$this->registrants = $wpdb->get_results( $wpdb->prepare( $query, $post->ID, $current_user->ID ) );

		// Basic sanitation before caching.
		foreach ($this->registrants as $key=>$row)
			$this->registrants[$key] = sanitize_post( $this->registrants[$key], 'raw' );

		// Cache this data. Probably not useful, but might as well, just in case.
		update_post_caches( $this->registrants, 'ep_reg', true, true );	

		$author_list = Array();
		foreach( $this->registrants as $row ) 
			$author_list[] = $row->post_author;

		// Cache the users using a single query instead of multiple queries.
		cache_users( $author_list );

		$this->count = count( $this->registrants );

		global $ep_registrants, $ep_registrants_count;

		$ep_registrants = $this->registrants;
		$ep_registrants_count = $this->count;

		$this->counter = -1;

		return $this->registrants;
	}

	function rewind() {
		$this->counter = -1;
		
		global $ep_registrants_count;
		$ep_registrants_count = 0;
	}

	function next_registrant() {
		$this->counter++;
		$this->in_the_loop = true;

		$this->registrant = $this->registrants[$this->counter];
		return $this->registrants[$this->counter];
	}

	function the_registrant() {
		global $ep_registrant;

		$ep_registrant = $this->next_registrant();
		$this->setup_registrant_data( $ep_registrant );
	}

	function setup_registrant_data( $reg ) {
		global 	$ep_registrant_name, 
			$ep_registrant_email, 
			$ep_registrant_url, 
			$ep_registrant_nicename,
			$ep_reg_id,
			$ep_registrant_id;

		$userdata = get_userdata( $reg->post_author );

		$ep_registrant_name = $userdata->display_name;
		$ep_registrant_email = $userdata->user_email;
		$ep_registrant_nicename = $userdata->user_nicename;
		$ep_registrant_url = $userdata->user_url;
		$ep_registrant_id = $reg->post_author;

		$ep_reg_id = $reg->ID;
	}

	function have_registrants() {
		if( !isset( $this->in_the_loop ) || $this->in_the_loop == false ) 
			$this->get_avail_registrations();

		if( $this->counter < $this->count - 1)
			return true;
		else {
			$this->in_the_loop = false;
			return false;
		}
	}

	function get_link( $regid ) {
		global $ep_registrant;
			
		if ( ( !isset( $regid ) || $regid == 0 ) ) {
			if ( !isset( $ep_registrant ) || $ep_registrant->ID == 0 )
				return;
			else $reg = $ep_registrant;
		} else 
			$reg = get_post( $regid );
		
		return get_permalink( $reg->post_parent ) . "#ep_register-" . $reg->ID;
	}

	/**
	 * Delete the associated registrations when deleting an event.
	 *
	 * @param int $eventid The deleted event
	 *
	 * @since 0.1
	 */
	function delete( $eventid ) {
		if ( $eventid == 0 ) return;

		$event = get_post( $eventid );

		if ( 'ep_event' == $event->post_type ) {
			$children = get_children( Array( 'post_type' => 'ep_reg', 'post_parent' => $eventid ) );

			foreach( $children as $child ) 
				wp_delete_post( $child->ID, true );
		}
	}
	
	/**
	 * Untrash registrations along with an event being un-trashed.
	 *
	 * @param int $eventid The event
	 *
	 * @since 0.1
	 */
	function untrash( $eventid ) {
		if ( $eventid == 0 ) return;

		$event = get_post( $eventid );

		if ( 'ep_event' == $event->post_type ) {
			$children = get_children( Array( 'post_type' => 'ep_reg', 'post_parent' => $eventid, 'post_status' => 'trash' ) );
				
			$trash_time = get_post_meta( $eventid, '_wp_trash_meta_time', true );

			foreach( $children as $child ) {
				$child_trash_time = get_post_meta( $child->ID, '_wp_trash_meta_time', true );

				//Only untrash items that were trashed with the event, and not those that were trashed by the user.
				if ( (int) $child_trash_time >= (int) $trash_time )
					wp_untrash_post( $child->ID );
			}
		}
	}

	/**
	 * Trash registrations along with an event being trashed.
	 *
	 * @param int $eventid The event
	 *
	 * @since 0.1
	 */
	function trash( $eventid ) {
		if ( $eventid == 0 ) return;

		$event = get_post( $eventid );

		if ( 'ep_event' == $event->post_type ) {
		/**
		 * Getting the children with a single query as trash post will then be able to get all children directly from cache.
		 */
		$children = get_children( Array( 'post_type' => 'ep_reg', 'post_parent' => $eventid ) );

		foreach ($children as $child)
			wp_trash_post( $child->ID );
		}
	}

	/**
	 * Send invites to a user.
	 *
	 * Invites sent by the event creator are automatically approved registrations.
	 * Invites sent by anyone else land up for moderation as registrations.
	 *
	 * @param int $to ID of user the invite is being sent to
	 * @param int $from ID of the user who is sending the invite
	 * @param int $eventid ID of the event the invite is for
	 *
	 * @since 0.1
	 */
	function send_invites( $to, $from, $eventid ) {
		$event = get_post( $eventid );

		$approved = false;
		if ( $from == $event->post_author ) 
			$approved = true;
		
		$existing_regs = get_posts( Array( 'post_type' => 'ep_reg' ) );

		$auth_map = Array();
		foreach( $existing_regs as $ex_reg ) 
			$auth_map[ $ex_reg->post_author ] = $ex_reg; //As there's only one registration per person, I can do this

		$regs = Array();

		foreach( $to as $invited ) {
			if ( isset( $auth_map[$invited] ) ) {
				if ( $auth_map[$invited]->post_status != 'reg_approved' && $approved ) {
					$this->approve( $auth_map[$invited]->ID );
					add_post_meta( $auth_map[$invited]->ID, '_ep_invited_by', $from );
				}
			} else {
				$reg = $this->register( Array(
					'userid' => $invited,
					'eventid' => $eventid,
					'approved' => $approved
				) );
				add_post_meta( $reg, '_ep_invited_by', $from );
				add_post_meta( $reg, '_ep_invite_confirmed', 'unset' );
			}
		}

		if ( $reg )
			return true;

		return false;
	}

	/**
	 * Accept an invite.
	 *
	 * @param $postid The registration id.
	 *
	 * @since 0.1
	 */	
	function approve( $postid ) {
		$post->ID = $postid;
		$post->post_status =  'reg_approved';
		wp_update_post( $post );
		do_action( 'ep_reg_approve', $postid );
	}

	/**
	 * Cancel an invite.
	 *
	 * @param $postid The registration id.
	 *
	 * @since 0.1
	 */	
	function cancel( $postid ) {
		$post->ID = $postid;
		$post->post_status =  'reg_cancelled';
		wp_update_post( $post );
		do_action( 'ep_reg_cancel', $postid );
	}

	/**
	 * Get the list of ids a user can invite for a given event.
	 *
	 * @param $postid The event id
	 * @param $userid The user
	 *
	 * @since 0.1
	 */
	function get_inviteable_ids( $postid = 0, $userid = 0 ) {
		global $bp, $wpdb, $post;

		if ( !$userid ) $userid = bp_loggedin_user_id();
		if ( !$postid ) $postid = $post->ID;
		
		$current_post = get_post( $postid );

		//Run a query which searches for all available members, and cuts out all those who
		// if current user is admin-> have approved or cancelled registrations
		// if current user is not admin-> have _any_ registrations
		$for_admin = '';

		if ( isset( $current_post ) && $current_post->post_author == $userid )
			$for_admin = "AND p.post_status = 'reg_approved'";

		$query = <<<QUERY
			SELECT 	DISTINCT(u.ID),
				u.user_email,
				u.user_nicename,
				u.display_name,
				pd.value	
			FROM $wpdb->users u 
			LEFT
				JOIN {$bp->profile->table_name_data} pd 
				ON u.ID = pd.user_id
			LEFT 
				JOIN (
					SELECT DISTINCT( p.post_author ) ID
					FROM $wpdb->posts p
					WHERE
						p.post_type = 'ep_reg'
						AND p.post_parent = $postid
						$for_admin
				) pa
				ON u.ID = pa.ID
			WHERE 
				u.user_status = 0
				AND pa.ID IS NULL
				AND pd.field_id = 1
			ORDER BY pd.value ASC
QUERY;
		$data = $wpdb->get_results( $query );
		return apply_filters( 'ep_inviteable_ids', $data );
	}

	/** @ToDo Investigate why query_posts works, and get_posts doesn't. */
	/**
	 * Get the invites for the current user.
	 *
	 * Runs a query to get invite based, unaccepted registrations for the signed in user.
	 * Returns a list of their parent events.
	 *
	 * @since 0.1
	 */
	function invites() {
		$query = new WP_Query();
		
		$invitations = $query->query( Array(
			'post_type' => 'ep_reg',
			'orderby' => 'modified',
			'meta_key' => '_ep_invite_confirmed',
			'meta_value' => 'unset',
			'author' => bp_loggedin_user_id()
		) );

		$events = Array();
		foreach( $invitations as $invite )
			$events[] = $invite->post_parent;

		return apply_filters( 'ep_invites', $events );
	}

}

//Create an instance of this model
global $ep_models; $ep_models['registration'] = new ep_registration_model();
