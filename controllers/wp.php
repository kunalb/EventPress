<?php
/**
 * The core/wordpress controller for the site.
 * Always called/included.
 *
 * @author Kunal Bhalla
 * @package EventPress
 */

/**
 * The class for handling all actions, etc. for WordPress
 * specific functions of the plugin. Always included.
 *
 * @since 0.1
 */
class ep_WP {

	/**
	 * Constructor. Adds all actions and filters.
	 *
	 * The basic initializer -- adds all filters and actions
	 * required. The post initialization, etc. are carried out in 
	 * init, which is run on the hook of the same name.
	 *
	 * @uses $ep_models
	 * @uses $ep_controllers
	 * @uses $ep_views
	 */
	function ep_WP() {
		global $ep_models, $ep_controllers, $ep_views;

		//Register translation support
		load_plugin_textdomain( 'eventpress', false, basename( EP_DIR ) . '/lang' );

		//Enqueue all the post, taxonomy, etc. registration at the init action.
		add_action( 'init', Array( &$this, 'init' ) );

		//Add the options menu for events.
		add_action( 'admin_menu', Array( &$this, 'add_options_menu' ) );

		//Save the meta data along with the post.
		add_action( 'save_post', Array( &$this, 'save_meta' ), 10, 2 );

		//Register the function to show the event metadata on a single page
		if (!defined('BP_VERSION'))
			add_filter( 'the_content', Array( $ep_views['template'], 'event_metadata' ) );

		//Register function for getting the right template part
		add_action( 'get_template_part_event_details', Array( &$this, 'register_event_part_template' ), 10, 2 );

		//Modify the messages for events and registration.
		add_filter( 'post_updated_messages', Array( $ep_models['events'], 'update_messages' ) );
		add_filter( 'post_updated_messages', Array( $ep_models['registration'], 'update_messages' ) );

		//Modify the display columns for registration and events.
		add_action( 'manage_posts_custom_column', Array( $ep_views['admin'], 'event_column_values' ), 10, 2 );
		add_filter( 'manage_edit-ep_event_columns', Array( $ep_views['admin'], 'event_column_headers' ) );
		add_action( 'manage_pages_custom_column', Array( $ep_views['admin'], 'registration_column_values' ), 10, 2 );
		add_filter( 'manage_edit-ep_reg_columns', Array( $ep_views['admin'], 'registration_column_headers' ) );

		//Trash, delete, untrash registrations as well with their corresponding events.
		add_action( 'trashed_post', Array( &$ep_models['registration'], 'trash' ) );
		add_action( 'untrash_post', Array( &$ep_models['registration'], 'untrash' ) );
		add_action( 'delete_post', Array( &$ep_models['registration'], 'delete' ) );
	
		//Add filters to map the new capabilities registered
		add_filter( 'map_meta_cap', Array( &$ep_models['events'], 'map_capabilities' ), 11, 4 );
		add_filter( 'map_meta_cap', Array( &$ep_models['registration'], 'map_capabilities' ), 10, 4 );

		//Add the init action to init if we're in the admin
		if ( is_admin() ) 
			add_action( 'init', array( 'ep_admin_view', 'init' ) );

		//Modify the page templates array /* Deprecated. DROP BY 1.3. */
		add_filter( 'page_template', Array( &$this, 'events_pages' ) );

		//Conditionally load the styles and scripts required
		add_action( 'wp', Array( &$ep_views['template'], 'wp_styles' ) );
		
		//Enqueue scripts in admin. Checking for post type, etc. is done within the function
		add_action( 'admin_enqueue_scripts', Array( &$ep_views['admin'], 'admin_resources' ) );

		//Add the widgets
		add_action( 'widgets_init', Array( &$this, 'register_widgets' ) );

	}

	/**
	 * Adds a new rewrite rule when permalinks are regenerated.
	 *
	 * @since 0.1
	 */
	function landing_page() {
		add_rewrite_rule( 'events/?$', 'index.php?post_type=ep_event', 'top' );
	}

	/**
	 * Registers widgets for WordPress.
	 *
	 * @since 0.1
	 */
	function register_widgets() {
		register_widget( 'EP_Upcoming_Events' );
		register_widget( 'EP_Calendar_Widget' );
	}

	/**
	 * Add the options menu to settings. (Not used)
	 *
	 * @since 0.1
	 * 
	 * @uses $ep_views
	 */
	function add_options_menu() {
		global $ep_views;

		//add_options_page( __( 'Events Options' ), __( 'Events' ), 'administrator', 'file', Array( &$ep_views['admin'], 'options_page' ) );
	}

	/**
	 * Saves metadata on saving a post.
	 * 
	 * Note: also updates revision post meta data.
	 *
	 * @since 0.1
	 *
	 * @uses $ep_models
	 *
	 * @param int $eventid
	 */
	function save_meta( $eventid, $event ) {
		global $ep_models;

		if( $event->post_type == 'ep_event' ) {
			if ( array_key_exists( 'ep-nonce' , $_POST ) ) {

				//Log error #s to be passed on to the redirect.
				$error_msgs = new kb_errors( Array( 'get_var' => 'ep_meta_msgs' ) );

				//Verify the nonce for submitting the data.
				if  ( array_key_exists( 'metabox', $_POST['ep-nonce'] ) && false == wp_verify_nonce( $_POST['ep-nonce']['metabox'],  'ep-admin-metabox' ) ) 
					wp_die( "Something's gone wrong somewhere!" );
					
				if ( isset( $_POST['ep-meta'] ) ) {
					$meta = $_POST['ep-meta'];

					// Start date exists but doesn't make sense.
					if ( !empty( $meta['start'] ) && ( -1 == ( $start = strtotime( $meta['start'] ) ) || false == $start ) ) {
						$error_msgs->log(1);
						unset( $start );
					}

					// End date exists but doesn't make sense.
					if ( !empty( $meta['end'] ) && ( -1 == ( $end = strtotime( $meta['end'] ) ) || false == $end ) ) {
						$error_msgs->log(2);
						unset( $end );
					}

					// End and start date exist, but end date is before start date
					if ( isset( $end ) && isset( $start ) && $end < $start ) {
						$error_msgs->log(3);
						unset( $start );
						unset( $end );
					}

					//Sanity checks for the start and end timings for registration
					// Start date exists but doesn't make sense.
					if ( !empty( $meta['startreg'] ) && ( -1 == ( $startreg = strtotime( $meta['startreg'] ) ) || false == $startreg ) ) {
						$error_msgs->log(4);
						unset( $startreg );
					}

					// Stop date exists but doesn't make sense.
					if ( !empty( $meta['stopreg'] ) && ( -1 == ( $stopreg = strtotime( $meta['stopreg'] ) ) || false == $stopreg ) ) {
						$error_msgs->log(5);
						unset( $stopreg );
					}

					// Stop and start date exist, but stop date is before start date
					if ( isset( $stopreg ) && isset( $startreg ) && $stopreg < $startreg ) {
						$error_msgs->log(6);
						unset( $startreg );
						unset( $stopreg );
					}

					// Startreg and end date have been set, with registration starting after the event is over
					if ( isset( $startreg ) && isset( $end ) && $end < $startreg ) {
						$error_msgs->log(7);
						unset( $startreg );
						unset( $end );
					}

					// Accept and sanitize the venue.
					if ( !empty( $meta['venue'] ) )
						$venue = esc_html( $meta['venue'] );

					// Save a positive value for registration limit.
					if ( !empty( $meta['limitreg'] ) && ( ( $limitreg = absint( $meta['limitreg'] ) ) != $meta['limitreg'] ) ) {
						$error_msgs->log(8);
						unset( $limitreg );
					}

					if ( !empty( $meta['map'] ) )
						$map = ( true == (bool) $meta['map'] ) ? true : false;
					else
						$map = false;

					if ( !empty( $meta['latlong'] ) )
						$latlong = $meta['latlong'];

					if ( array_key_exists( 'confirmreg', $meta ) )
						$confirmreg = $meta['confirmreg'];

					//Whew! All validation done. Just save the stuff, please.
					$metalist = Array( 'limitreg', 'venue', 'startreg', 'stopreg', 'start', 'end', 'map', 'latlong', 'confirmreg' );
					$clean_meta = compact( $metalist );
					$clean_meta = apply_filters( 'ep_metadata', $clean_meta, $eventid );

					foreach( $metalist as $themeta )
						if( !isset( $clean_meta[$themeta] ) )
							$clean_meta[$themeta] = "";
					

					foreach( $clean_meta as $key => $val ) {
						// _ to avoid showing up in custom fields.
						update_post_meta( $eventid, "_ep_" . $key, $val );
					}
					//And we're done!
				}

			}

			//Saving changes to the registration form. Not checking a new nonce as I already have one.
			if(  isset( $_POST['ep-reg'] ) ) {
				$_POST['ep-reg'] = apply_filters( 'ep_save_regform', $_POST['ep-reg'] );
				update_post_meta( $eventid, "_ep_regform", serialize( $_POST['ep-reg'] ) );
			} else delete_post_meta( $eventid, "_ep_regform" );

			//Actions for bulk editing the registrations
			if( isset( $_POST['ep-reg-bulk']['post'] ) ) {
				switch( $_POST['ep-reg-bulk']['action'] ) {
					case '0':
					case 'approve':
						do_action( 'ep_approve_regs', $_POST['ep-reg-bulk']['post'] );
						foreach( $_POST['ep-reg-bulk']['post'] as $id )
							$ep_models['registration']->approve( $id );
						break;
					case '1':
					case 'cancel':
						do_action( 'ep_cancel_regs', $_POST['ep-reg-bulk']['post'] );
						foreach( $_POST['ep-reg-bulk']['post'] as $id )
							$ep_models['registration']->cancel( $id );
						break;
				}
				switch( $_POST['ep-reg-bulk']['action2'] ) {
					case '0':
					case 'approve':
						do_action( 'ep_approve_regs', $_POST['ep-reg-bulk']['post'] );
						foreach( $_POST['ep-reg-bulk']['post'] as $id )
							$ep_models['registration']->approve( $id );
						break;
					case '1':
					case 'cancel':
						do_action( 'ep_cancel_regs', $_POST['ep-reg-bulk']['post'] );
						foreach( $_POST['ep-reg-bulk']['post'] as $id )
							$ep_models['registration']->cancel( $id );
						break;
				}
			}
			unset( $_POST['ep-reg-bulk'] );
			//Cleanup to avoid an infinite loop

			//Saving the schedule
			if( isset( $_POST['ep-schedule'] ) ) {

				//Get original values
				$unit = get_post_meta( $eventid, '_ep_schedule_unit', true );
				$magnitude = get_post_meta( $eventid, '_ep_schedule_magnitude', true );
				$till = get_post_meta( $eventid, '_ep_schedule_till', true );
				$series = get_post_meta( $eventid, '_ep_schedule_series', true );

				//Get new values
				$n_magnitude = $_POST['ep-schedule']['magnitude'];
				$n_unit = $_POST['ep-schedule']['unit'];
				$n_till = strtotime( $_POST['ep-schedule']['end'] );

				if( $_POST['ep-schedule']['end'] == '' ) $n_till = strtotime( "+1 year", $start );

				//Anything changed, has? (Yoda-esque, occasionally I become)
				if( ( $unit != $n_unit || $magnitude != $n_magnitude || $till != $n_till ) &&
				    ( $n_unit != '' && $n_magnitude != '' ) ) {
					$start = ( (int) get_post_meta( $eventid, '_ep_start', true ) );
					$end = ( (int) get_post_meta( $eventid, '_ep_end', true ) );
					$regstart = ( (int) get_post_meta( $eventid, '_ep_startreg', true ) );
					$regend = ( (int) get_post_meta( $eventid, '_ep_stopreg', true ) );

					update_post_meta( $eventid, '_ep_schedule_unit', $n_unit );
					update_post_meta( $eventid, '_ep_schedule_magnitude', $n_magnitude );
					update_post_meta( $eventid, '_ep_schedule_till', $n_till );
					update_post_meta( $eventid, '_ep_schedule_series', true );

					$custom = get_post_custom( $eventid );
					
					$temp = $event;

					unset( $temp->ID );
					unset( $_POST['ep-schedule'] );
					unset( $temp->post_modified );
					unset( $temp->post_modified_gmt );

					//Cleanup to avoid infinite loop
					
					while( $start && (int) $start < (int) $n_till ) {
						
						$start = strtotime( "+$n_magnitude $n_unit", $start );
						$end = ($end == 0) ? 0 : strtotime( "+$n_magnitude $n_unit", $end );
						$regend = ($regend == 0) ? 0 : strtotime( "+$n_magnitude $n_unit", $regend );
						$regstart = ($regstart == 0 ) ? 0 : strtotime( "+$n_magnitude $n_unit", $regstart );

						$temp = apply_filters( 'ep_repeat_event', $temp, $event );

						$new_post = wp_insert_post( $temp );
						$custom[ '_ep_start' ] = Array( $start );
						$custom[ '_ep_end' ] = Array( $end );
						$custom[ '_ep_startreg' ] = Array( $regstart );
						$custom[ '_ep_stopreg' ] = Array( $regend );	
						$custom[ '_ep_schedule_unit' ] = Array( $n_unit );
						$custom[ '_ep_schedule_magnitude' ] = Array( $n_magnitude );
						$custom[ '_ep_schedule_till' ] = Array( $n_till );
						$custom[ '_ep_schedule_series' ] = Array( true );

						$custom = apply_filters( 'ep_repeat_event_meta', $custom, $new_post, $event );

						foreach( $custom as $key=>$val )
							update_post_meta( $new_post, $key, $val[0] );
					}
				}
			}
		}
	}

	/**
	 * Does all the work at init.
	 *
	 * Registers the custom post types, taxonomies.
	 * post statuses, maps capabilities.
	 *
	 * @since 0.1
	 *
	 * @uses $ep_models
	 * @uses $ep_views
	 * @uses kb_extend_caps() Utility function to add capabilities
	 */
	function init() {
		global $ep_models, $ep_views;
		
		//Register the event and registration specific taxonomies.
		register_taxonomy( 'event_tag', 'ep_event', $ep_models['events']->register_tags() );
		register_taxonomy( 'event_category', 'ep_event', $ep_models['events']->register_cats() );

		//Register the custom post types.
		register_post_type( 'ep_event', $ep_models['events']->register_event_type() );
		register_post_type( 'ep_reg', $ep_models['registration']->register_reg_type() );

		//Register the registration statuses.
		$reg_statuses = $ep_models['registration']->register_registration_statuses();	
		foreach ( $reg_statuses as $status => $args ) {
			register_post_status( $status, $args );	
		} 


		/** @ToDo Add Role and Capability removal to plugin deactivation */
		/** @ToDo Add Event, Registration Deletion to plugin deactivation */ 

		//Give a pretty URL to events. Only for WordPress, as BuddyPress will use a screen.
		if ( !defined( 'EP_BP' ) ) { /* Deprecated. DROP BY 1.3. */
			$this->landing_page();
		}

		//Register the shortcodes
		add_shortcode( 'ep-calendar', 'ep_calendar' );
	}

	/**
	 * Register the meta boxes for the event admin page.
	 *
	 * @since 0.1
	 *
	 * @uses $ep_views
	 */
	function register_meta_boxes() {
		global $ep_views;

		add_meta_box( 'ep-register-box', _x( 'Custom registration form', 'Metabox Heading', 'eventpress' ), Array( &$ep_views['admin'], 'metabox_custom_registration' ), 'ep_event', 'normal', 'high' );
		add_meta_box( 'ep-registration-box', _x( 'Registration Details', 'Metabox Heading', 'eventpress' ), Array( &$ep_views['admin'], 'metabox_registration_details' ), 'ep_event', 'normal', 'low' );
		add_meta_box( 'ep-metabox', _x( 'Customize your event', 'Metabox Heading', 'eventpress' ),  Array(&$ep_views['admin'], 'metabox'), 'ep_event', 'normal', 'high' );
		add_meta_box( 'ep-map', _x( 'Map', 'Map Metabox Heading', 'eventpress' ), Array( &$ep_views['admin'], 'map_metabox' ), 'ep_event', 'side', 'low' );
		add_meta_box( 'ep-schedule', _x( 'Repeat', 'Metabox Heading', 'eventpress' ), Array( &$ep_views['admin'], 'repeat_metabox' ), 'ep_event', 'side', 'low' );
	}

	/**
	 * Register the meta boxes for the registration admin page.
	 *
	 * @since 0.1
	 *
	 * @uses $ep_views
	 */
	function register_reg_meta_boxes() {
		global $ep_views;

		remove_meta_box( 'submitdiv', 'ep_reg', 'side' );
		remove_meta_box( 'authordiv', 'ep_reg', 'normal' );
		add_meta_box( 'ep-change-reg-status', _x( "Registration Status", 'Metabox Heading', 'eventpress' ), Array( &$ep_views['admin'], 'metabox_reg_status' ), 'ep_reg', 'side', 'low' );
		add_meta_box( 'ep-registrant-details', _x( "Registrant Details", 'Metabox Heading', 'eventpress' ), Array( &$ep_views['admin'], 'metabox_author_details' ), 'ep_reg', 'side', 'high' );
	}

	/**
	 * Registers the plugin's inbuilt single events template.
	 *
	 * @since 0.1
	 *
	 * @uses $post
	 * 
	 * @param string $current The file path that has been found
	 * @return string The determined file path.
	 */
	function register_events_template( $current ) {
		global $post;

		if ( 'ep_event' == $post->post_type )
			if ( !preg_match( '/events.php$/', $current ) )
				$current = EP_THEMES_DIR . '/wp/events.php';

		$current = apply_filters( 'ep_events_template', $current );
		return $current;
	}

	/**
	 * Registered to be called only when event_details.php is required.
	 *
	 * First tries to determine if a file can be found normally, as is done
	 * in locate_template_part, if there's no such file -- this has not been
	 * over-ridden by the user -- then loads the plugin's version. However,
	 * this is a bit of a hack, as even after loading this template the 
	 * function will try to load the template it couldn't find.
	 *
	 * @since 0.1
	 *
	 * @param string $slug The slug of the file to be loaded.
	 * @param string $name The sub-type, so that the file $slug-$name is loaded.
	 */	
	function register_event_part_template( $slug, $name ) {
		$templates = array();
		if ( isset($name) )
			$templates[] = "{$slug}-{$name}.php";

		$templates[] = "{$slug}.php";

		$located = locate_template($templates, false);

		if ( '' == $located )
			load_template( EP_THEMES_DIR . '/wp/event_details.php', false );
	}

	/**
	 * Get the event-loop from the plugin's directory.
	 *
	 * Same logic as {@link register_event_part_template}.
	 *
	 * @since 0.1
	 *
	 * @param string $slug The slug of the file to be loaded.
	 * @param string $name The sub-type, so that the file $slug-$name is loaded.
	 */
	function register_event_loop( $slug, $name ) {
		$templates = array();
		if ( isset($name) )
			$templates[] = "{$slug}-{$name}.php";
		else
			$templates[] = "{$slug}.php";

		$located = locate_template($templates, false);

		if ( '' == $located )
			load_template( EP_THEMES_DIR . '/wp/loop-event.php', false );
	}

	/**
	 * Flush permalinks after init to allow event based permalinks to work.
	 *
	 * @since 0.1.2
	 */
	function _flush_permalinks() {
		flush_rewrite_rules( false );
	}

	/**
	 * Called the first time the plugin is initiated.
	 *
	 * Creates role, enhances capabilities and makes the pages.
	 *
	 * @since 0.1
	 */
	function initiate_plugin() {
		global $ep_models, $wp_user_roles;

		unset( $wp_user_roles );

		remove_role( 'event_creator' );
		//Create the Event Creator Role
		add_role( 'event_creator', _x( 'Event Creator', 'Role title', 'eventpress' ), $ep_models['events']->create_role() );  

		//Extend the capabilities
		kb_extend_caps( $ep_models['events']->extend_capabilities() );
		kb_extend_caps( $ep_models['registration']->extend_capabilities() );

		//Add hook to flush permalinks
		add_action( 'wp', '_flush_permalinks' );

	}

	/**
	 * Called on deactivating the plugin.
	 *
	 * Removes the pages only, leaving capabilities and roles as they are.
	 *
	 * @since 0.1
	 */
	function kill_plugin() {
	}

	/**
	 * Load the custom page templates for the given pages.
	 *
	 * @since 0.1
	 */
	function events_pages( $template ) {
		global $post;
		
		if( $post->post_name == 'events-calendar' && !preg_match( '/events-calendar/', $template ) ) {
			$template = EP_THEMES_DIR . '/wp/page-events-calendar.php';
		} else if( $post->post_name == 'events-list' && !preg_match( '/events-list/', $template ) ) {
			$template = EP_THEMES_DIR . '/wp/page-events-list.php';
		}

		$template = apply_filters( 'ep_custom_page_templates', $template );
		return $template;
	}
}

