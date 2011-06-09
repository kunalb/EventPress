<?php
/**
 * Core file. Initializes constants, loads required files.
 *
 * @package EventPress
 */

/**#@+
 * Constants
 */

/**
 * The current version of the plugin 
 */
define( 'EP_VERSION', '0.2-bleeding' );

/**
 * The plugin directory, for including files, etc.
 */
define( 'EP_DIR', dirname(__FILE__) );

/**
 * The URL of the plugin
 */
define( 'EP_URL', plugins_url( $path = '/' . basename( dirname( __FILE__ ) ) ) );
/**#@-*/

/**
 * Load the text domain as soon as possible.
 */
load_plugin_textdomain( 'eventpress', false, basename( EP_DIR ) . '/lang' );


/**
 * Get the classes that do the heavy lifting.
 */
require "core/class-eventpress.php";

require "includes/class-ep-cpt.php";
require "core/class-ep-event-cpt.php";
require "core/class-ep-reg-cpt.php";

/**
 * Initialize the EventPress Class.
 */
