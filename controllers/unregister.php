<?php
/**
 * Controller for registering users for an event. Requires people to be registered on the site.
 */

if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
	header('Allow: POST');
	header('HTTP/1.1 405 Method Not Allowed');
	header('Content-Type: text/plain');
	exit;
}

/**
 * Extracts the root URL from this file, assuming it is kept in wp-content.
 * ABSPATH isn't defined yet!
 */
$abspath = substr( __FILE__, 0, strpos( __FILE__, "wp-content" ) - 1 );
require $abspath . '/wp-load.php';


$eventid = isset( $_POST['ep_reg_event_id'] ) ? $_POST['ep_reg_event_id'] : 0;
//Invalid eventid
if ( $eventid != (int) $eventid ) 
	wp_die( __( "Incorrect eventid passed. Are you playing with the POST data?", 'eventpress' ) );


global $current_user;
get_currentuserinfo();
//Invalid user
if ( !isset( $current_user ) || !$current_user )
	wp_die( __( "Must be logged in to register/unregister for an event.", 'eventpress' ) );

global $ep_models;
//Registration not open.
$event = $ep_models['events']->registration_status( $eventid );

if ( $event['status'] != 'open' )
	wp_die( __( "Cannot unregister for this event.", 'eventpress' ) );


$result = $ep_models['registration']->unregister( Array( 'userid' => $current_user->ID, 'eventid' => $eventid ) );

if( !$result )
	wp_die( __( "Could not unregister you from this event.", "eventpress" ) );

wp_redirect( get_permalink( $eventid ) );
