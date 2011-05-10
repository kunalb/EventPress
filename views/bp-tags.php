<?php

function ep_get_registrant_domain( $postid = 0 ) {
	global $ep_registrant;
	
	if ( $postid != 0 ) $reg = get_post( $postid );
	else $reg = $ep_registrant;

	return bp_core_get_user_domain( $reg->post_author );
}

function ep_registrant_domain() {
	echo ep_get_registrant_domain();
}

function ep_get_registrant_avatar_thumb( $postid = 0 ) {
	global $ep_registrant;
	
	if ( $postid != 0 ) $reg = get_post( $postid );
	else $reg = $ep_registrant;

	$args =  Array( 'item_id' => $reg->post_author, 'type' => 'thumb', 'object' => 'user' );
	return bp_core_fetch_avatar( $args );
}

function ep_registrant_avatar_thumb() {
	echo ep_get_registrant_avatar_thumb();
}

function ep_get_registered_since( $postid = 0 ) {
	global $ep_registrant;
	
	if ( $postid != 0 ) $reg = get_post( $postid );
	else $reg = $ep_registrant;

	return bp_core_get_last_activity( $reg->post_date, __( 'registered %s ago.', 'eventpress' ) );
}

function ep_registered_since() {
	echo ep_get_registered_since();
}

function ep_get_inviteable_ids() {
	global $ep_models;
	return $ep_models['registration']->get_inviteable_ids();
}

/**
 * Class for loop for getting the list of inviteable people.
 *
 * Extends the generic loop KB_Loop using the registration model
 * to get the basic (single-query) data of people who can be 
 * invited for an event.
 *
 * @since 0.1
 */
class EP_Inviteable extends KB_Loop {

	/**
	 * Initiate the loop and save the data.
	 *
	 * @uses $ep_models Global containing instances of all the models.
	 *
	 * @param array $args Array of arguments for customizing results.
	 */
	function EP_Inviteable( $args ) {
		global $ep_models;

		$defaults = Array(
			'max' => 0
		);

		extract( wp_parse_args( $args, $defaults ) );

		$this->results = $ep_models['registration']->get_inviteable_ids();
		$this->total = count( $this->results );
		
		if ( isset( $max ) && $max ) $this->max = $max;
		else $this->max = $this->total;
	}

	/**
	 * Return the current user id.
	 *
	 * @return int The current invitable user's ID
	 */
	function id() {
		return $this->result->ID;
	}

	/**
	 * Return the current user id.
	 *
	 * @return int The current invitable user's permalink
	 */
	function permalink() {
		return bp_core_get_user_domain(
			$this->result->ID
		);
	}

	/**
	 * Return the current user id.
	 *
	 * @uses bp_core_fetch_avatar 
	 *
	 * @return int The current invitable user's avatar
	 */
	function avatar( $args ) {
		$defaults = Array(
			'item_id' => $this->result->ID,
			'object' => 'user',
			'type' => 'thumb',
			'class' => 'avatar',
			'email' => $this->result->user_email,
			'alt' => $this->result->value
		);
		$args = wp_parse_args( $args, $defaults );
		return bp_core_fetch_avatar( $args );
	}

	function nicename() {
		return $this->result->value;
	}
}
