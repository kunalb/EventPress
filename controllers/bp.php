<?php
/**
 * The controller that adds functions specific
 * to BuddyPress. Only included when bp_loaded is
 * called.
 *
 * @author Kunal Bhalla
 * @package EventPress
 */

/**
 * The BuddyPress controller class.
 *
 * Handles all the action creation, etc. Hooks into
 * the BuddyPress Custom Post controller to add the 
 * extra pages -- registered users, invites, etc.
 *
 * @since 0.1
 */
class ep_BP {

	/**
	 * Constructor. Adds all actions and filters.
	 *
	 * Hooks into all the required places -- can only
	 * hook into the actions/filters after bp_loaded,
	 * though.
	 *
	 * @since 0.1
	 *
	 * @uses $ep_models
	 * @uses $ep_views
	 * @uses $ep_controllers
	 * @uses $bp
	 */
	function ep_BP() {
		global $ep_models, $ep_controllers, $bp, $ep_views;

		//Retrieve the arguments for registering the custom component
		$bp_args = $ep_models['events']->register_event_type_bp();
		//Register the event type as a custom component in buddypress
		bpcp_register_post_type($bp_args);

		//Add the meta details before the home page
		add_action( 'bpcp_ep_event_single_before_home', Array( &$ep_views['template'], 'bp_before_home' ) );

		//Add the meta details to the header for all but the home page
		add_action( 'bpcp_ep_event_single_before_header', Array( &$ep_views['template'], 'bp_header_information' ) );

		//Enqueue the scripts for maps 
		add_action( 'bpcp_ep_event_controller_single_home', Array( &$ep_views['template'], 'bp_map_script' ) );

		//Enqueue the custom styling
		add_action( 'bpcp_ep_event_controller', Array( &$ep_views['template'], 'bp_style' ) );

		//Add the all important register button
		add_action( 'bpcp_ep_event_single_item_actions', Array( &$ep_views['template'], 'bp_register_button' ) );

		//Register users controller
		add_action( 'bpcp_ep_event_controller_single_register', Array( &$this, 'register_user' ) ); 

		//Unregister users controller
		add_action( 'bpcp_ep_event_controller_single_unregister', Array( &$this, 'unregister_user' ) ); 

		//Register the member directory and invites directory for the event page
		add_action( 'bpcp_ep_event_single_subnav', Array( &$this, 'member_subnav' ) );

		//Send invites
		add_action( 'bpcp_ep_event_controller_single_send-invites', Array( &$this, 'send_invites' ) );

		//Add the menu for accepting invites
		add_action( 'bpcp_ep_event_general_nav', Array( &$this, 'invites_nav' ) );

		//Add the query for showing events which have invited the user
		add_action( 'bpcp_ep_event_member_page', Array( &$this, 'invites_query' ) );

		//Add meta data to the loop
		add_action( 'bpcp_ep_event_loop_meta', Array( &$ep_views['template'], 'bp_loop_meta' ) );

		//Add register button to the loop
		add_action( 'bpcp_ep_event_loop_action_meta', Array( &$ep_views['template'], 'bp_register_button' ) );

		//Add the metaboxes for the edit/create page
		add_action( 'bpcp_edit_add_metaboxes', Array( &$ep_controllers['wp'], 'register_meta_boxes' ) );
		add_action( 'bpcp_edit_add_metaboxes', Array( &$ep_views['admin'], 'bp_edit_resources' ) );

		//Modify the page templates array
		remove_filter( 'page_template', Array( $ep_controllers['wp'], 'events_pages' ) );
		add_filter( 'page_template', Array( &$this, 'events_pages' ) );

		//Add start registration based sorting to the directory
		add_action( 'bpcp_ep_event_directory_options', Array( $ep_views['template'], 'bp_sort_options' ) );
		add_action( 'bpcp_ep_event_controller_directory', Array( $this, 'extra_filters' ) );
		add_action( 'bpcp_ep_event_ajax_directory', Array( $this, 'extra_filters' ) );
	}

	/**
	 * Actually filter the cookie query based on $_POST
	 *
	 * @since 0.1
	 */
	function _extra_filters( $query ) {
		switch ( $_POST[ 'filter' ] ) {
			case 'ep-event-start': 
				$query[ 'orderby' ] = 'meta_value';
				$query[ 'meta_key' ] = '_ep_start';
				$query[ 'order' ] = 'ASC';
				break;
			case 'ep-event-end': 
				$query[ 'orderby' ] = 'meta_value';
				$query[ 'meta_key' ] = '_ep_end';
				$query[ 'order' ] = 'ASC';
				break;
			case 'ep-reg-start': 
				$query[ 'orderby' ] = 'meta_value';
				$query[ 'meta_key' ] = '_ep_startreg';
				$query[ 'order' ] = 'ASC';
				break;
			case 'ep-reg-end':
				$query[ 'orderby' ] = 'meta_value';
				$query[ 'meta_key' ] = '_ep_stopreg';
				$query[ 'order' ] = 'ASC';
				break;
		}

		$query = apply_filters( 'ep_cookie_query', $query );

		return $query;
	}

	/**
	 * Adds filters for cookie_query based on what's going on.
	 *
	 * @since 0.1
	 */
	function extra_filters() {
		add_filter( 'bpcp_cookie_query', Array( &$this, '_extra_filters' ) );
	}

	/**
	 * Function called when a user tries to unregister in BuddyPress.
	 *
	 * This is more or less a BuddyPress-ized replica of register.php from
	 * the WordPress version. 
	 * Does not generate any activity at the moment.
	 *
	 * @since 0.1
	 *
	 * @uses $post
	 * @uses $ep_models
	 */
	function unregister_user() {
		global $post, $ep_models, $ep_views;
		
		$userid = bp_loggedin_user_id();
		$eventid = $post->ID;

		if ( $eventid == null || $eventid == 0 )
			bp_core_add_message( __("This event doesn't exist.", "eventpress") );
	
		if ( $userid == 0 ) {
			bp_core_add_message( __("You must be logged in to unregister.", "eventpress") );
			bp_core_redirect( wp_get_referer() );
		}
	
		$event = $ep_models['events']->registration_status( $eventid );

		if ( $event['status'] != 'open' ) {
			bp_core_add_message( __( "Cannot unregister for this event." ) );
			bp_core_redirect( wp_get_referer() );
		}


		do_action( 'ep_bp_user_unregister' );
		$result = $ep_models['registration']->unregister( Array( 'userid' => $userid, 'eventid' => $eventid ) );

		if( $result ) {
			do_action( 'ep_bp_user_unregister_success' );
			bp_core_add_message( __("You have been succesfully unregistered.", "eventpress") );
		} else {
			do_action( 'ep_bp_user_unregister_failure' );
			bp_core_add_message( __("You could not be unregistered.", "eventpress") );
		}

		bp_core_redirect( wp_get_referer() );
	}

	/**
	 * Function called when a user tries to register in BuddyPress.
	 *
	 * First shows the custom registration form for this event, if one is set. Then attempts to register.
	 *
	 * This is more or less a BuddyPress-ized replica of register.php from
	 * the WordPress version. Also generates the activity for user
	 * registration.
	 *
	 * @since 0.1
	 *
	 * @uses $post
	 * @uses $ep_models
	 */
	function register_user() {
		global $post, $ep_models, $ep_views;
		
		$userid = bp_loggedin_user_id();
		$eventid = $post->ID;

		if ( $eventid == null || $eventid == 0 )
			bp_core_add_message( __("This event doesn't exist.") );
	
		if ( $userid == 0 ) {
			bp_core_add_message( __("You must be logged in to register.") );
			bp_core_redirect( wp_get_referer() );
		}
	
		$event = $ep_models['events']->registration_status( $eventid );

		if ( $event['status'] != 'open' ) {
			bp_core_add_message( __( "Cannot register for this event." ) );
			bp_core_redirect( wp_get_referer() );
		}

		//Already registered for this event.
		$existing_registrations = get_children( Array(
			'post_parent' => $eventid,
			'post_author'=> $userid,
			'post_type' => 'ep_reg'
		) );
		foreach( $existing_registrations as $reg )
			if ( $reg->post_author == $userid ) {
				bp_core_add_message( __( "You've already registered for this event.", 'eventpress' ) );
				bp_core_redirect( wp_get_referer() );
			}

		do_action( 'ep_bp_user_register' );

		//Get the post's registration form and don't display an extra screen.
		$regform = get_post_meta( $eventid, '_ep_regform', true );

		if ( !isset( $_POST[ 'ep-reg-form-filled' ] ) && $regform ) {
			//User can register and a form exists -- show the registration form
			$ep_views['template']->bp_registration_form();
			do_action( 'ep_bp_user_regform' );
		} else if ( wp_verify_nonce( $_POST[ 'ep-regform-nonce' ], 'regform' ) || !$regform ) {
			//Form filled, or there was none

			$regid = $ep_models['registration']->register( Array( 'userid' => $userid, 'eventid' => $eventid ) );

			$action = sprintf( __( '%s just registered for the event <a href = "%s">%s</a>.', 'eventpress' ), bp_core_get_userlink( $userid ) , get_permalink( $eventid ), $post->post_title );
			$action = apply_filters( 'ep_bp_registration_action', $action );

			if( function_exists( 'bp_activity_add' ) )
				bp_activity_add( Array(
					'action' => $action,
					'component' => 'ep_event',
					'content' => '',
					'type' => 'event_registration',
					'user_id' => $userid,
					'item_id' => $post->ID,
					'secondary_item_id' => $regid
				) );

			do_action( 'ep_bp_register_save' );
			bp_core_redirect( wp_get_referer() );
		} else {
			do_action( 'ep_bp_invalid_register' );
			bp_core_add_message( __( "Something went wrong. Please try again.", 'eventpress' ) );
			bp_core_redirect( wp_get_referer() );
		}
	}

	/**
	 * Add the extra sub-menus required.
	 *
	 * Adds extra submenus for the event type component.
	 *
	 * @since 0.1
	 *
	 * @uses $ep_views
	 * @uses $post
	 */
	function member_subnav() {
		global $ep_views, $post;
		$type = get_post_type_object( 'ep_event' );

		bp_core_new_subnav_item( Array(
			'name' => 'Registered',
			'slug' => 'registered',
			'parent_slug' => 'events',
			'position' => 50,
			'screen_function' => Array( &$ep_views['template'], 'bp_registered' ),
			'parent_url' => get_permalink()
		) );

		bp_core_new_subnav_item( Array(
			'name' => 'Send Invites',
			'slug' => 'send-invites',
			'parent_slug' => 'events',
			'position' => 60,
			'screen_function' => Array( &$ep_views['template'], 'bp_send_invites' ),
			'user_has_access' => is_user_logged_in(),
			'parent_url' => get_permalink()
		) );

		do_action( 'ep_subnav' );
	}

	/**
	 * Called to send user invites
	 *
	 * Runs for the url domain.com/events/eventname/send-invites/send . Attempts to send
	 * invites to the specified users, adding an appropriate message.
	 *
	 * @uses $bp
	 * @uses $post
	 * @uses $ep_models
	 *
	 * @see $ep_models['registration']->send_invites
	 */
	function send_invites() {
		global $bp, $post, $ep_models;
		do_action( 'ep_send_invites' );

		if ( isset( $bp->action_variables[0] ) && $bp->action_variables[0] == 'send' ) {
			if ( wp_verify_nonce( $_POST['ep_invite_nonce'], 'send_invites' ) ) {
				if ( !empty( $_POST['ep_invite'] ) ) { 
					foreach( $_POST['ep_invite'] as $key => $invite ) {
						$_POST['ep_invite'][$key] = (int) $invite;
					}
					
					if ( $ep_models['registration']->send_invites( $_POST['ep_invite'], bp_loggedin_user_id(), $post->ID ) ) {
						bp_core_add_message( __( 'The invites have been sent.', 'eventpress' ) );
						$this->generate_notification( $_POST['ep_invite'], bp_loggedin_user_id(), $post->ID );
						do_action( 'ep_sent_invites' );
					} else
						bp_core_add_message( __( 'The invites couldn\'t be sent.' ), 'eventpress' );
				} else bp_core_add_message( __( 'Please select people to send an invite to.', 'eventpress' ) );
			} else bp_core_add_message( __( 'Invalid wp-nonce.', 'eventpress' ) );
			bp_core_redirect( wp_get_referer() );
		}
	}

	/**
	 * Add the invites subnavigation menu to Events.
	 *
	 * @uses $bp
	 * @uses $ep_views
	 */
	function invites_nav() {
		global $bp, $ep_views;
		
		bp_core_new_subnav_item( Array(
			'name' => __( 'Invites' , 'eventpress' ),
			'slug' => 'invites',
			'parent_slug' => 'events',
			'parent_url' =>	$bp->loggedin_user->domain . 'events/',
			'position' => 20,
			'screen_function' => Array( &$ep_views['template'], 'invites' )
		) );

		do_action( 'ep_nav' );
	}

	/**
	 * Query for events that have invites for the current user
	 *
	 * @uses $bp
	 * @uses $ep_models
	 * @uses $ep_views
	 */
	function invites_query() {
		global $bp, $ep_models, $ep_views;

		if ( 'invites' == $bp->current_action ) {

			if ( !isset( $bp->action_variables[1] ) ) {
				$ids = $ep_models[ 'registration' ]->invites();
				if ( empty( $ids ) ) $ids = Array( 0 );
					
				query_posts( Array(
					'post__in' => $ids,
					'post_type' => 'ep_event'
				) );

				//This query triggers the is_home property of wp_query, which loads
				//the default blog template instead. Forcing is_home to false.
				global $wp_query;
				$wp_query->is_home = false;

				//Change the registration buttons with the invitation buttons
				remove_filter( 'bpcp_ep_event_loop_action_meta', Array( $ep_views['template'], 'bp_register_button' ) );
				add_filter( 'bpcp_ep_event_loop_action_meta', Array( $ep_views['template'], 'bp_invite_button' ) );

				do_action( 'ep_invites_page' );
			} else {
				if ( $bp->action_variables[1] == (int) $bp->action_variables[1] ) {
					
					$eventid = $bp->action_variables[1];
					
					$regid = query_posts( Array( 'author' => bp_loggedin_user_id(), 'post_parent' => $eventid, 'post_type' => 'ep_reg' ) );

					if ( !isset( $regid[0] ) ) 
						bp_core_add_message( __( "There was no invitation found for this event." ), "error" );

					else {
						switch( $bp->action_variables[0] ) {
							case 'accept':
								if ( update_post_meta( $regid[0]->ID, '_ep_invite_confirmed', 'accepted' ) )
									bp_core_add_message( __( "Invitation Accepted", "eventpress" ) );
								do_action( 'ep_accept_invite', $regid[0]->ID );
								break;
							case 'reject':
								$reg = get_post( $regid[0]->ID );
								$reg->post_status = 'reg_cancelled';

								if ( update_post_meta( $regid[0]->ID, '_ep_invite_confirmed', 'rejected' ) && wp_insert_post( $reg ) )
									bp_core_add_message( __( "Invitation Rejected", "eventpress" ) );
								do_action( 'ep_reject_invite', $regid[0]->ID );
								break;
						}
					}
				} else {
					bp_core_add_message( __( "No event was found.", "eventpress" ), "error" );
				}

				bp_core_redirect( $bp->loggedin_user->domain . 'events/invites/' );
			}
		}
	}

	/**
	 * Load the custom page templates for the given pages.
	 *
	 * Outside the standard bp controller as these will be treated as
	 * wordpress posts and must be handled as such.
	 *
	 * @since 0.1
	 */
	function events_pages( $template ) {
		global $post;

		if( $post->post_name == 'events-calendar' && !preg_match( '/events-calendar/', $template ) ) {
			$template = EP_THEMES_DIR . '/bp/page-events-calendar.php';
		}

		$template = apply_filters( 'ep_bp_calendarpage', $template );

		return $template;
	}

	/**
	 * Function to send notifications to users on receiving an invite.
	 *
	 * @since 0.1
	 */
	function generate_notification( $to_user_id, $from_user_id, $event_id ) {
		global $bp;
		
		$sender_name = bp_core_get_user_displayname( $from_user_id, false );
		$reciever_name = bp_core_get_user_displayname( $to_user_id, false );

		if ( 'no' == get_usermeta( (int)$to_user_id, '_ep_invite_notification' ) )
			return false;
		
		$reciever_ud = get_userdata( $to_user_id );
		$sender_ud = get_userdata( $from_user_id );
		
		$sender_profile_link = site_url( BP_MEMBERS_SLUG . '/' . $sender_ud->user_login . '/' . $bp->profile->slug );
		$sender_invite_link = site_url( BP_MEMBERS_SLUG . '/' . $sender_ud->user_login . '/events/invites' );
		$reciever_settings_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/settings/notifications' );

		$event = get_post( $event_id );
		$event_name = "<a href = '" . site_url( '/events/' . $event->post_name ) . "'>{$event->post_title}</a>";
			
		/* Set up and send the message */
		$to = $reciever_ud->user_email;
		$subject = '[' . get_blog_option( 1, 'blogname' ) . '] ' . sprintf( __( '%s invited you!.',  stripslashes($sender_name) ), 'eventpress' );

		$message = sprintf( __( 
		'%s invited you to %s

		To see %s\'s profile: %s

		To accept the invite: %s
		', 'eventpress' ), $sender_name, $event_name, $sender_name, $sender_profile_link, $sender_invite_link );

		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s' , $reciever_settings_link ), 'eventpress' );
		$message = apply_filters( 'ep_invites_message', $message );

		// Send it!
		wp_mail( $to, $subject, $message );
	}
}
