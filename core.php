<?php
/**
 * Core file. Initializes constants, loads required files.
 *
 * @package EventPress
 */

/**
 * Load the text domain as soon as possible.
 */
load_plugin_textdomain( 'eventpress', false, basename( EP_DIR ) . '/lang' );

/** 
 * Get the constants.
 */
require 'constants.php'; 

/**
 * Initialize the EventPress Class.
 */
