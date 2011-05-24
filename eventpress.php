<?php
/*
	Plugin Name: EventPress
	Plugin URI: http://code.google.com/p/eventpress/
	Description: A BuddyPress aware events plugin for WordPress. See more details, raise issues and contribute at <a href = 'http://goo.gl/8lN4v'>http://code.google.com/p/eventpress/</a>.
	Version: 0.1.2.7
	Author: Kunal Bhalla. 
	Author URI: http://kunal-b.in
	License: GPL2
	Text Domain: eventpress

	Copyright 2010  Kunal Bhalla  (email : bhalla.kunal@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Loader. Set constants and load the main controller.
 *
 * @package EventPress
 * @author Kunal Bhalla
 * @version 0.1
 */

/** 
 * The current version of the plugin 
 *
 * @global string EP_VERSION
 * @since 0.1
 */
define( 'EP_VERSION', '0.1.2.2' );

/**
 * The plugin directory, for including files, etc.
 *
 * @global string EP_DIR
 * @since 0.1
 */
define( 'EP_DIR', dirname(__FILE__) );

/**
 * The theme directory path.
 *
 * @global string EP_THEMES_DIR
 * @since 0.1
 */
define( 'EP_THEMES_DIR', EP_DIR . '/themes' );

/**
 * The URL for the plugin
 *
 * @global string EP_REL_URL
 * @since 0.1
 */
define( 'EP_REL_URL', plugins_url( $path = '/' . basename( dirname( __FILE__ ) ) ) );

/**
 * Key with which all eventpress options will be stored.
 *
 * @global string EP_OPTIONS
 * @since 0.1
 */
define( 'EP_OPTIONS', 'ep_options_group' );

/**
 * Load the main controller and relax. It'll handle everything. 
 */
require( 'controllers/main.php' );

//Register the activation hook
register_activation_hook( __FILE__, Array( 'ep_WP', 'initiate_plugin' ) );

//Register the deactivation hook
register_deactivation_hook( __FILE__, Array( 'ep_WP', 'kill_plugin' ) );
