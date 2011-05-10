<?php

/**
 * To do what wp-admin/post.php couldn't.
 *
 * Actually does the work behind the column hover links
 * for approving/cancelling registrations.
 *
 * @author Kunal Bhalla
 * @since 0.1
 */

/**
 * Extracts the root URL from this file, assuming it is kept in wp-content.
 * ABSPATH isn't defined yet!
 */
$abspath = substr( __FILE__, 0, strpos( __FILE__, "wp-content" ) - 1 );
require $abspath . '/wp-load.php';


if ( !is_user_logged_in() )
	wp_die( __('Must be logged in to moderate registration details.') );

if ( !isset( $_GET['eventid'] ) )
	wp_die( __('No event passed.' ) );

$eventid = $_GET['eventid'];

if ( $eventid != (int) $eventid ) 
	wp_die( __('Invalid event id passed.') );

if ( !isset( $_GET['action'] ) )
	wp_die( __('What should I do? No action specified.') );

$action = $_GET['action'];

if ( !($event = get_post( $eventid, ARRAY_A ) ) )
	wp_die( __('Could not load the specified event.') );

$post_type = get_post_type_object( 'ep_event' );
if ( !(current_user_can( $post_type->cap->edit_post, $eventid ) ) || $event['post_status'] == 'trash' ) 
	wp_die( __('Cannot edit this registration.') );

if ( 'approve' == $action ) {
	$event['post_status'] = 'reg_approved';
	do_action( 'ep_approve_reg', $event );
	wp_insert_post( $event );	
} else if ( 'cancel' == $action ) {
	$event['post_status'] = 'reg_cancelled';
	do_action( 'ep_cancel_reg', $event );
	wp_insert_post( $event );	
} else {
	wp_die( __( 'Invalid action specified.', 'eventpress' ) );
}

wp_redirect( wp_get_referer() );
