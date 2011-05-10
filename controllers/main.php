<?php
/**
 * The main controller for EventPress
 *
 * Initializes and loads the code required
 * for eventpress; conditionally loads the code
 * required for BuddyPress.
 *
 * @author Kunal Bhalla
 * @since 0.1
 */

/**
 * Misc Utility functions.
 */
require EP_DIR . '/mods/kb_utilities.php';

/**
 * Stores all the view instances
 *
 * Primarily singletons, only used
 * for namespacing and delimiting code 
 * logically.
 *
 * @global Array $ep_views
 * @since 0.1
 */
global $ep_views;

/**
 * Stores all the model instances.
 * 
 * @global Array $ep_models
 * @since 0.1
 */
global $ep_models;

/**
 * Stores all the controller instances.
 * 
 * @global Array $ep_controllers
 * @since 0.1
 */
global $ep_controllers;

/**
 * Events arguments, saving, creation functions, etc.
 * Also, model functions. Has everything related to the 
 * event post type.
 */
require EP_DIR . '/models/events.php';

/**
 * Registration arguments, saving, creation functions, etc.
 * Also, model functions. Has everything related to the 
 * reg post type.
 */
require EP_DIR . '/models/registration.php';

/**
 * Utility class definition, provides generic template tags
 * and a class for quickly creating loops.
 *
 * In other words, abstracted loopiness.
 */
require EP_DIR . '/views/kb-loop.php';

/**
 * Calendar class definition, used to generate calendars anywhere.
 *
 * In other words, abstracted loopiness.
 */
require EP_DIR . '/views/ep-calendar.php';

/**
 * All view related functions that appear on the frontend.
 */
require EP_DIR . '/views/template.php';

/**
 * All view related functions for the admin area -- whether wp-admin
 * or the buddypress special frontend.
 */
require EP_DIR . '/views/admin.php';

/**
 * Template tags for WordPress
 */
require EP_DIR . '/views/wp-tags.php';

/**
 * Widgets for WordPress
 */
require EP_DIR . '/views/wp-widgets.php';

/**
 * The main controller for all core functions for the plugin
 * Always initialized -- the post types, taxonomies, etc are 
 * handled here.
 */
require( EP_DIR . '/controllers/wp.php' );

//Initialize the WordPress Controller and set it up.
$ep_controllers['wp'] = new ep_WP();

/**
 * Initializes BuddyPress controllers and load required files.
 *
 * Called in case BuddyPress is available -- on load, or if it is loaded
 * a bit early.
 *
 * @since 0.1
 * 
 * @uses $ep_controllers
 * @uses $ep_views
 */
function ep_init_bp() {
	global $ep_controllers, $ep_views;

	/**
	 * A check to see if BP was initialized and loaded for the plugin.
	 *
	 * @global bool EP_BP
	 */
	define( 'EP_BP', true );

	if (function_exists( 'bpcp_register_post_type' ) ) {
		/**
		 * Gets the BuddyPress specific controller.
		 */
		require( EP_DIR . '/controllers/bp.php' );

		/**
		 * Get BuddyPress tags for templates.
		 */
		require( EP_DIR . '/views/bp-tags.php' );

		//Initializes the BuddyPress Controller
		$ep_controllers['bp'] = new ep_BP();

		do_action( 'ep_bp_init' );
	} else add_action( 'admin_notices', Array( &$ep_views['admin'], 'need_bpcp' ) );
}

//Start BuddyPress support if required.
if ( defined( 'BP_VERSION' ) || did_action( 'bp_include' ) )
	ep_init_bp();
else
	add_action( 'bp_loaded', 'ep_init_bp' );
