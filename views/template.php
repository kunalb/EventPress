<?php
/**
 * template.php
 *
 * All functions involved in the showing events -- templates, etc.
 *
 * @package EventPress
 * @author Kunal Bhalla
 * 
 * @since 0.1
 */


/**
 * Class containing all functions involved in creating the view for an event.
 * 
 * @since 0.1
 */
class ep_template_view {

	/**
	 * Create the Event Data box in themes without having to create a new single-page template which becomes
	 * rather theme dependent.
	 * 
	 * @since 0.1.3
	 */
	function event_metadata($content) {
		global $post;

		if ($post->post_type == 'ep_event') {
			ob_start();
				get_template_part('event_details');
			$metadata = ob_get_clean();	

			$regform = "";
			if( is_single() ) {
				ob_start();
					ep_registration_template();
				$regform = ob_get_clean();
			}

			return $metadata . $content . $regform;
		}

		return $content;
	}

	/**
	 * Enqueue styles and scripts for registration, and the Map API.
	 * 
	 * @since 0.1
	 */
	function wp_styles() {
		global $post;
		if ( !defined( 'EP_BP' ) && $post->post_type == 'ep_event' )
			wp_enqueue_style( 'registration-css', EP_REL_URL . '/themes/wp/assets/css/register'.kb_ext().'.css' );

		//Conditionally load map API if the map is going to be shown
		if ( get_post_meta( $post->ID, '_ep_map', true ) == true ) {
			wp_enqueue_script( "google-map", "http://maps.google.com/maps/api/js?sensor=false" );
		}
	}

	/**
	 * Render a single registrant's data.
	 * 
	 * @since 0.1
	 */
	function render_registrant() {
		echo "<div class = 'ep-registrant ep-" . ep_get_reg_status() . "'>";
		
			echo get_avatar( ep_get_the_author_email(), 64 );

			echo "<div class = 'ep-registrant-data'>";
				echo "<span class = 'ep-registrant-name'>";
				if ( ep_get_the_author_url() != "" )
					echo "<a href = '" . ep_get_the_author_url() . "' rel = 'nofollow'>" . ep_get_the_author() . "</a>";
				else
					ep_the_author();
				echo "</span>";
			echo "</div>";

		echo "</div>";
	}

	/**
	 * Adds the event details before the description in the BuddyPress
	 * Single page.
	 * 
	 * @since 0.1
	 */
	function bp_before_home() {
		bpcp_locate_template( Array( 'event_details.php' ), true );
	}

	/**
	 * Add abridged Event details in the header.
	 * 
	 * @since 0.1
	 */
	function bp_header_information() {
		if ( !bpcp_is_home() && !bpcp_is_edit() )
			bpcp_locate_template( Array( 'event_details.php' ), true );
	}

	/**
	 * Enqueue the map script.
	 * 
	 * @since 0.1
	 */
	function bp_map_script() {
		wp_enqueue_script( "google-map", "http://maps.google.com/maps/api/js?sensor=false" );
	}

	/**
	 * Events styling for BuddyPress
	 * 
	 * @since 0.1
	 */
	function bp_style() {
		wp_enqueue_style( 'ep-events-style', EP_REL_URL . '/themes/bp/assets/css/events'.kb_ext().'.css' );
	}

	/**
	 * The register button -- or the message for people who can't/have already registered.
	 * 
	 * @since 0.1
	 */
	function bp_register_button() { 
		global $bp, $ep_models;	
		$output = "";

		if ( ep_registration_open() ) {
			$userid = bp_loggedin_user_id();
			if ( $userid != 0 ) {
				$user_status = false;
				$registered = $ep_models['registration']->get_avail_registrations();
				foreach( $registered as $registrant ) 
					if ( $registrant->post_author == $userid )
						$user_status = $registrant->post_status;
				switch ( $user_status ) {
					case 'reg_approved':
						$output = "<div id = 'message' class = 'ep-reg-status'><p>" . __( "You have signed up for this event.", 'eventpress' ) . "</p></div>";
						$output .= "<a href = '" . get_permalink() . "unregister/' class = 'button'>" . __( 'Unregister', 'eventpress' ) . "</a>";
						break;
					case 'reg_pending':
						$output = "<div id = 'message' class = 'ep-reg-status'><p>" . __( "Your registration has not been approved yet.", 'eventpress' ) . "</p></div>";
						$output .= "<a href = '" . get_permalink() . "unregister/' class = 'button'>" . __( 'Unregister', 'eventpress' ) . "</a>";
						break;
					case 'reg_cancelled':
						$output = "<div id = 'message' class = 'ep-reg-status'><p>" . __( "Your registration has been cancelled." ) . "</p></div>";
						break;
					default:
						$output = "<a href = '" . get_permalink() . "register/' class = 'button'>" . __( 'Register', 'eventpress' ) . "</a>";
				}
			} else $output = "<div id = 'message' class = 'ep-reg-status'><p>" . __( "You must be logged in to register for this event.", 'eventpress' ) . "</p></div>";
		} else $output = "<div id = 'message' class = 'ep-reg-status'><p>" . __( "Registration is not open.", 'eventpress' ) . "</p></div>";

		echo apply_filters( 'ep_register_button', $output, $user_status );
	}

	/**
	 * Get arguments for loading the registration template.
	 * 
	 * @since 0.1
	 */
	function _reg_template( $template ) {
		return Array( 'registered.php' );
	}

	/**
	 * Load the registration sub template in the home template.
	 * 
	 * @since 0.1
	 */
	function bp_registered() {
		rewind_posts();

		add_filter( 'bpcp_ep_event_single_home_template', Array( &$this, '_reg_template' ) );
		bp_core_load_template( 'type/single/home' );
	}

	/**
	 * Add event details to the loop.
	 * 
	 * @since 0.1
	 */
	function bp_loop_meta() {
		bpcp_locate_template( Array( 'event_details.php' ), true, false );
	}

	/**
	 * Get arguments for loading the invite template.
	 * 
	 * @since 0.1
	 */
	function _invite_template() {
		return Array( 'send-invites.php' );
	}

	/**
	 * Load send-invite sub template.
	 * 
	 * @since 0.1
	 */
	function bp_send_invites() {
		rewind_posts();
		wp_enqueue_script( 'ep-add-invites', EP_REL_URL . '/themes/bp/assets/js/invites' . kb_ext() . '.js' );
		add_filter( 'bpcp_ep_event_single_home_template', Array( &$this, '_invite_template' ) );
		bp_core_load_template( 'type/single/home' );
	}

	/**
	 * Get arguments for loading the send-invites page.
	 * 
	 * @since 0.1
	 */
	function _invites_template() {
		return Array( 'invites.php' );
	}

	/**
	 * Load the invites template in the home page.
	 * 
	 * @since 0.1
	 */
	function invites() {
		add_filter( 'bpcp_members_type_loop', Array( &$this, '_invites_template' ) );
		bp_core_load_template( 'members/single/home' );
	}

	/**
	 * Add the actions for invites.
	 * 
	 * @since 0.1
	 */
	function bp_invite_button() {
		global $post, $bp;

		echo "<a href = '{$bp->loggedin_user->domain}events/invites/accept/{$post->ID}/' class = 'button'>" . __( 'Accept', 'eventpress' ) . "</a>" . " ";
		echo "<a href = '{$bp->loggedin_user->domain}events/invites/reject/{$post->ID}/' class = 'button'>" . __( 'Reject', 'eventpress' ) . "</a>";
	}

	/**
	 * Returns the template array for reg forms.
	 *
	 * @since 0.1
	 */
	function _reg_form() {
		return Array( 'regform.php' );
	}

	/**
	 * Shows the registration form to users.
	 *
	 * @since 0.1
	 */
	function bp_registration_form() {
		rewind_posts();
		add_filter( 'bpcp_ep_event_single_home_template', Array( &$this, '_reg_form' ) );
		bp_core_load_template( 'type/single/home' );
	}

	function bp_sort_options() { 
	?>
		<option value="ep-event-start"><?php _e( 'Start date', 'eventpress' ) ?></option>
		<option value="ep-event-end"><?php _e( 'End date', 'eventpress' ) ?></option>
		<option value="ep-reg-start"><?php _e( 'Registration start', 'eventpress' ) ?></option>
		<option value="ep-reg-end"><?php _e( 'Registration end', 'eventpress' ) ?></option>
	<?php }
}

//Create an instance of this class
global $ep_views;
$ep_views['template'] = new ep_template_view();
