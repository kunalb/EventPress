<?php

/** 
 * Miscellaneous utility functions that didn't have any better home..
 */

/**
 * Add capabilities to arrays specified as: 
 * Array(
 *	'type' => Array( 'cap1', 'cap2', ... )
 * )
 */
function kb_extend_caps( $extend ) {
	global $wp_roles;

	foreach ( $extend as $type => $caps ) {
		foreach ( $caps as $cap ) {
			$wp_roles->add_cap( $type, $cap );
		}
	}
}

/**
 * Remove capabilities to arrays specified as: 
 * Array(
 *	'type' => Array( 'cap1', 'cap2', ... )
 * )
 */
function kb_remove_caps( $extend ) {
	global $wp_roles;

	foreach ( $extend as $type => $caps ) {
		foreach ( $caps as $cap ) {
			$wp_roles->remove_cap( $type, $cap );
		}
	}
}

/**
 * Used to handle passing errors to be displayed on the final page in admin.
 */
class kb_errors {
	var $error_codes;
	var $get_var;
	var $error_maps;

	function kb_errors( $args ) {
		$defaults = Array(
			'error_codes'	=> Array(),
			'get_var'	=> '',
			'error_maps'	=> Array()
		);

		extract( wp_parse_args( $args, $defaults ) );

		$this->error_codes = $error_codes;
		$this->get_var = $get_var;
		$this->error_maps = $error_maps;

		if ( $get_var != '' && count( $error_maps ) != 0 ) {
			add_action( 'admin_notices', Array( &$this, 'display' ) );
		} else if ( $get_var != '' ) {
			add_filter( 'redirect_post_location', Array( &$this, 'add_codes' ) );
		}
 	}

	function add_codes( $loc ) {
		return add_query_arg( $this->get_var, implode( '+', $this->error_codes ), $loc );
	}

	function log( $code ) {
		$this->error_codes[] = (int) $code;
	}

	function display() {
		if( array_key_exists( $this->get_var, $_GET ) && !empty( $_GET[$this->get_var] ) ) {
			$codes = explode( ' ', $_GET[$this->get_var] );
			
			foreach( $codes as $code )
				echo "<div class = 'error'>" . $this->error_maps[ (int) $code ] . "</div>";
		}
	}

	function add_error( $code, $message ) {
		$this->error_maps[$code] = $message;
	}
}

/** 
 * Returns .dev when SCRIPT_DEBUG is enabled.
 */
if( !function_exists( 'kb_ext' ) ) {
	function kb_ext() {
		/* TODO Make this function behave as described once minification has been completed. */
		return ".dev";

	/*
		if( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			return ".dev";

		return "";
	*/
	}
}
