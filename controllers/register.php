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
	wp_die( __( "Cannot register for this event.", 'eventpress' ) );

//Already registered for this event.
$existing_registrations = get_children( Array(
	'post_parent' => $eventid,
	'post_author'=> $current_user->ID,
	'post_type' => 'ep_reg'
) );
foreach( $existing_registrations as $reg )
	if ( $reg->post_author == $current_user->ID )
		wp_die( __( "You've already registered for this event.", 'eventpress' ) );

//Get user specified registration data.
$regform = unserialize( get_post_meta( $eventid, '_ep_regform', true ) );

//Submitted post data
$post_data = $_POST[ 'ep-regform' ]; 
$sanitized_post_data = Array();

//Read only that data that's required
foreach( $regform['label'] as $index => $approved_label )
	if( isset( $post_data[ $approved_label ] ) )
		$sanitized_post_data[ $approved_label ] = $post_data[ $approved_label ];

//Check, and save data. Or die. Either's good enough for me.
foreach( $regform['label'] as $index => $label ) {
	//Save only data that exists
	if( isset( $sanitized_post_data[ $label ] ) ) {
		if( $regform['type'][$index] == 'text' && isset( $regform['regex'][$index] ) )
			if( !preg_match( "/{$regform['regex'][$index]}/", $sanitized_post_data[$label] ) )
				wp_die( sprintf( __( "Please check the value you entered in %s (%s).", 'eventpress' ), $label, htmlentities( $sanitized_post_data[ $label ] ) ) );
	}
}

//Now $sanitized_post_data is safe. I think.
do_action( 'ep_wp_register' );
$regid = $ep_models['registration']->register( Array( 'userid' => $current_user->ID, 'eventid' => $eventid, 'data' => $sanitized_post_data ) );

wp_redirect( $ep_models['registration']->get_link( $regid ) );
