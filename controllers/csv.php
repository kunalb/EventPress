<?php
/**
 * CSV File generator for registration.
 *
 * @since 0.1
 *
 * @author Kunal Bhalla
 */

/**
 * Extracts the root URL from this file, assuming it is kept in wp-content.
 * ABSPATH isn't defined yet!
 */
$abspath = substr( __FILE__, 0, strpos( __FILE__, "wp-content" ) - 1 );
require $abspath . '/wp-load.php';

$eventid = $_GET[ 'ep-eventid' ];
$eventid = (int) $eventid;

$event = get_post( $eventid );
$regs = query_posts( "post_parent=$eventid&post_type=ep_reg" );

//Get additional headers
$form = unserialize( get_post_meta( $eventid, '_ep_regform', true ) );

header("Content-type: application/csv");
header("Content-Disposition: attachment; filename={$event->post_name}-registration.csv");
header("Pragma: no-cache");
header("Expires: 0");

//The headers, monsiegneur.
echo '"' . __( 'Registrant', 'eventpress' ) . '","' . __( 'Registered at', 'eventpress' ) .'","' . __( 'Registration Status', 'eventpress' ) . '"';

foreach( $form['label'] as $value ) 
	echo ",\"$value\"";

do_action( 'ep_csv_headers', $event );

//And the data, of course.
foreach( $regs as $reg ) {
	echo "\n";
	$user = new WP_user( $reg->post_author );
	
	echo _ep_csv_printer( $user->display_name ) . "," . _ep_csv_printer( $event->post_date ) . ",";

	switch( $reg->post_status ) {
		case 'reg_approved': echo "\"" . __( 'Approved', 'eventpress' ) . "\""; break;
		case 'reg_cancelled': echo "\"" . __( 'Cancelled', 'eventpress' ) . "\""; break;
		default: _ep_csv_printer( $reg->post_status );
	}

	foreach( $form['label'] as $value ) {
		echo "," . _ep_csv_printer( get_post_meta( $reg->ID, $value, true ) );
	}

	do_action( 'ep_csv_details', $reg, $event );
}

//Pretty printing with CSV formatting.
function _ep_csv_printer( $val ) {
	$val = preg_replace( '/"/', '""', $val );
	return "\"$val\"";
}

//And that is all.
