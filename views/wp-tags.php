<?php

if ( !function_exists( 'ep_registration_template' ) ) {
	function ep_registration_template() {
		global $ep_models, $post;

		if ( post_password_required() ) {
			echo "This event is password protected &mdash; enter the password to register, as well as see registrations.";
			return;
		}
?>
		<div id = 'ep-register'>
			<?php 
				$current_reg = $ep_models['events']->get_registration_number( $post->ID );
				$current_reg = $current_reg->reg_approved;
				$limit = (int) ep_get_registration_limit();
				if ( $limit != 0 ) { 
			?>
				<h3 id = 'ep-register-title'>
				<?php printf( __( '%s seats left (of %s)' ), $limit-$current_reg, $limit ); ?>
				</h3>
			<?php } ?>

			<div id = 'ep-registrants' >
			<?php if ( ep_have_registrants() ) { ?>
			<h3 id = 'ep-registrants-title'><?php _e( 'Already signed up', 'eventpress' ); ?></h3>
			<?php 
				while( ep_have_registrants() ) : ep_the_registrant();
					ep_render_registrant();
				endwhile; 
			} else {
			?>
				<p><?php _e( 'Be the first to register for this event!', 'eventpress' ); ?></p>
			<?php } ?>
			</div>

			<?php ep_registration_form(); ?>
		</div>
<?php
	}

	add_action( 'ep_registration_template', 'ep_registration_template' );
}

if ( !function_exists( 'ep_registration_form' ) ) {
	function ep_registration_form() {
		global $post_id, $current_user, $ep_registrants;

		get_currentuserinfo();
		
		if( ep_registration_open() ) { ?>
			<div class = "ep-registration-open" >
		<?php
			if ( is_user_logged_in() ) {
				$user_status = false;
				foreach( $ep_registrants as $registrant ) 
					if ( $registrant->post_author == $current_user->ID )
						$user_status = $registrant->post_status;

				switch ( $user_status ) {
					case 'reg_approved':
						echo "<div class = 'ep-reg-status'>" . __( 'You have signed up for this event.', 'eventpress' ) . "</div>"; ?>
						<form action = '<?php echo EP_REL_URL . '/controllers/unregister.php'; ?>' method = 'post' id = 'ep-register-form'>
							<input name="unregister" type="submit" id="ep-reg-submit" value="<?php _e( 'Unregister', 'eventpress' ); ?>" />
							<?php ep_reg_id_fields(); ?>	
						</form>
						<?php break;
					case 'reg_pending':
						echo "<div class = 'ep-reg-status'>" . __( 'Your registration has not been approved yet.', 'eventpress' ) . "</div>"; ?>
						<form action = '<?php echo EP_REL_URL . '/controllers/unregister.php'; ?>' method = 'post' id = 'ep-register-form'>
							<input name="unregister" type="submit" id="ep-reg-submit" value="<?php _e( 'Unregister', 'eventpress' ); ?>" />
							<?php ep_reg_id_fields(); ?>	
						</form> 
						<?php break;
					case 'reg_cancelled':
						echo "<div class = 'ep-reg-status'>" . __( 'Your registration has been cancelled.', 'eventpress' ) . "</div>";
						break;
					default: ?>
						<h3 id = 'ep-signup'>Sign up for this event!</h3>
						<form action = '<?php echo EP_REL_URL . '/controllers/register.php'; ?>' method = 'post' id = 'ep-register-form'>
							<?php ep_render_reg_form(); ?>
							<input name="submit" type="submit" id="ep-reg-submit" value="<?php _e( 'Register', 'eventpress' ); ?>" />
							<?php ep_reg_id_fields(); ?>	
						</form>
					<?php
				}
			} else {
				echo "<div class = 'ep-reg-status'>" . __( 'You must be logged in to register for this event.', 'eventpress' ) . "</div>";
			}	
		?>
			</div>
		<?php } else if( ( $startreg = ep_registration_future() ) != false ) { ?>
			<p class = 'ep-registration-closed'>
				<?php printf( __( 'Registration for this event will start on %s', 'eventpress' ), $startreg ); ?>.
			</p>
		<?php } else if( $stopreg = ep_registration_past() ) {?>
			<p class = 'ep-registration-closed'>
				<?php printf( __( 'Registration for this event stopped on %s', 'eventpress' ), $stopreg ); ?>.
			</p>
		<?php } else if( $end = ep_registration_over() ) {?>
			<p class = 'ep-registration-closed'>
				<?php printf( __( 'This event ended on %s', 'eventpress' ), $end ); ?>.
			</p>
		<?php } else {?>
			<p class = 'ep-registration-closed'>
				<?php _e( 'Registration for this event is over.', 'eventpress' ); ?>
			</p>
		<?php } 
	}
	add_action( 'ep_registration_form', 'ep_registration_form' );
}

if ( !function_exists( 'ep_registration_open' ) ) {
	function ep_registration_open() {
		global $ep_models;
		$status = $ep_models['events']->registration_status(); //to be used only within the loop

		if ($status['status'] == 'open') return true;
		else return false;
	}
}

if ( !function_exists( 'ep_registration_future' ) ) {
	function ep_registration_future() {
		global $ep_models;
		$status = $ep_models['events']->registration_status(); //to be used only within the loop

		if ($status['status'] == 'future') return date( get_option('date_format'), $status['startreg'] );
		else return false;
	}
}

if ( !function_exists( 'ep_registration_past' ) ) {
	function ep_registration_past() {
		global $ep_models;
		$status = $ep_models['events']->registration_status(); //to be used only within the loop

		if ($status['status'] == 'past') return date( get_option('date_format'), $status['stopreg'] );
		else return false;
	}
}

//Misleading name. But couldn't come up with anything better.
if ( !function_exists( 'ep_registration_over' ) ) {
	function ep_registration_over() {
		global $ep_models;
		$status = $ep_models['events']->registration_status(); //to be used only within the loop

		if ($status['status'] == 'over') return date( get_option('date_format'), $status['end'] );
		else return false;
	}
}

if ( !function_exists( 'ep_have_registrants' ) ) {
	function ep_have_registrants() {
		global $ep_models, $ep_registrants_count;
	
		return $ep_models['registration']->have_registrants();
	}
}

if ( !function_exists( 'ep_the_registrant' ) ) {
	function ep_the_registrant() {
		global $ep_models;
	
		return $ep_models['registration']->the_registrant();
	}
}

if ( !function_exists( 'ep_get_reg_status' ) ) {
	function ep_get_reg_status() {
		global $ep_registrant;
		return $ep_registrant->post_status;
	}
}

if ( !function_exists( 'ep_reg_status' ) ) {
	function ep_reg_status() {
		echo ep_get_reg_status();
	}
}


if ( !function_exists( 'ep_get_the_author' ) ) {
	function ep_get_the_author() {
		global $ep_registrant_name;
		return $ep_registrant_name;
	}
}

if ( !function_exists( 'ep_the_author' ) ) {
	function ep_the_author() {
		echo ep_get_the_author();
	}
}

if ( !function_exists( 'ep_get_the_author_url' ) ) {
	function ep_get_the_author_url() {
		global $ep_registrant_url;
		return $ep_registrant_url;
	}
}

if ( !function_exists( 'ep_the_author_url' ) ) {
	function ep_the_author_url() {
		echo ep_get_the_author_url();
	}
}

if ( !function_exists( 'ep_get_the_author_email' ) ) {
	function ep_get_the_author_email() {
		global $ep_registrant_email;
		return $ep_registrant_email;
	}
}

if ( !function_exists( 'ep_the_author_email' ) ) {
	function ep_the_author_email() {
		echo ep_get_the_author_email();
	}
}

if ( !function_exists( 'ep_get_the_author' ) ) {
	function ep_get_the_author() {
		global $ep_registrant_name;
		return $ep_registrant_name;
	}
}

if ( !function_exists( 'ep_the_author' ) ) {
	function ep_the_author() {
		echo ep_get_the_author();
	}
}

if ( !function_exists( 'ep_render_registrant' ) ) {
	function ep_render_registrant() {
		global $ep_views;
		$ep_views['template']->render_registrant();
	}
}

if ( !function_exists( 'ep_get_reg_id_fields' ) ) {
	function ep_get_reg_id_fields() {
		global $post, $current_user;
		$nonce = wp_nonce_field( 'regform', 'ep-regform-nonce' );
return <<<FIELDS
		<input type = 'hidden' name = 'ep_reg_event_id' value = '{$post->ID}'>
FIELDS;
	}
}
if ( !function_exists( 'ep_reg_id_fields' ) ) {
	function ep_reg_id_fields() {
		echo ep_get_reg_id_fields();
	}
}

if ( !function_exists( 'ep_get_start_date') ) {
	function ep_get_start_date( $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		$date = get_post_meta( $postid, '_ep_start', true );
		$formatted = ( empty( $date ) ) ? "" : date_i18n( get_option( 'date_format' ), $date );

		return $formatted;
	}
}

if ( !function_exists( 'ep_start_date' ) ) {
	function ep_start_date() {
		echo ep_get_start_date();
	}
}

if ( !function_exists( 'ep_get_end_date' ) ) {
	function ep_get_end_date( $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		$date = get_post_meta( $postid, '_ep_end', true );
		$formatted = ( empty( $date ) ) ? "" : date_i18n( get_option( 'date_format' ), $date );

		return $formatted;
	}
}

if ( !function_exists( 'ep_end_date' ) ) {
	function ep_end_date() {
		echo ep_get_end_date();
	}
}

if ( !function_exists( 'ep_get_start_time') ) {
	function ep_get_start_time( $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		$date = get_post_meta( $postid, '_ep_start', true );
		$formatted = ( empty( $date ) ) ? "" : date_i18n( get_option( 'time_format' ), $date );

		return $formatted;
	}
}

if ( !function_exists( 'ep_start_time' ) ) {
	function ep_start_time() {
		echo ep_get_start_time();
	}
}

if ( !function_exists( 'ep_get_end_time' ) ) {
	function ep_get_end_time( $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		$date = get_post_meta( $postid, '_ep_end', true );
		$formatted = ( empty( $date ) ) ? "" : date_i18n( get_option( 'time_format' ), $date );

		return $formatted;
	}
}

if ( !function_exists( 'ep_end_time' ) ) {
	function ep_end_time() {
		echo ep_get_end_time();
	}
}

if ( !function_exists( 'ep_get_venue' ) ) {
	function ep_get_venue( $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		return get_post_meta( $postid, '_ep_venue', true );
	}
}

if ( !function_exists( 'ep_venue' ) ) {
	function ep_venue() {
		echo ep_get_venue();
	}
}

if ( !function_exists( 'ep_get_registration_date_open' ) ) {
	function ep_get_registration_date_open( $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		$date = get_post_meta( $postid, '_ep_startreg', true );
		$formatted = ( empty( $date ) ) ? "" : date_i18n( get_option( 'date_format' ), $date );

		return $formatted;
	}
}

if ( !function_exists( 'ep_registration_date_open' ) ) {
	function ep_registration_date_open() {
		echo ep_get_registration_date_open();
	}
}

if ( !function_exists( 'ep_get_registration_date_close' ) ) {
	function ep_get_registration_date_close( $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		$date = get_post_meta( $postid, '_ep_stopreg', true );
		$formatted = ( empty( $date ) ) ? "" : date_i18n( get_option( 'date_format' ), $date );

		return $formatted;
	}
}

if ( !function_exists( 'ep_registration_date_close' ) ) {
	function ep_registration_date_close() {
		echo ep_get_registration_date_close();
	}
}

if ( !function_exists( 'ep_get_registration_time_open' ) ) {
	function ep_get_registration_time_open( $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		$date = get_post_meta( $postid, '_ep_startreg', true );
		$formatted = ( empty( $date ) ) ? "" : date_i18n( get_option( 'time_format' ), $date );

		return $formatted;
	}
}

if ( !function_exists( 'ep_registration_time_open' ) ) {
	function ep_registration_time_open() {
		echo ep_get_registration_time_open();
	}
}

if ( !function_exists( 'ep_get_registration_time_close' ) ) {
	function ep_get_registration_time_close( $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		$date = get_post_meta( $postid, '_ep_stopreg', true );
		$formatted = ( empty( $date ) ) ? "" : date_i18n( get_option( 'time_format' ), $date );

		return $formatted;
	}
}

if ( !function_exists( 'ep_registration_time_close' ) ) {
	function ep_registration_time_close() {
		echo ep_get_registration_time_close();
	}
}

if ( !function_exists( 'ep_get_registration_limit' ) ) {
	function ep_get_registration_limit( $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		return get_post_meta( $postid, '_ep_limitreg', true );
	}
}

if ( !function_exists( 'ep_registration_limit' ) ) {
	function ep_registration_limit() {
		echo ep_get_registration_limit();
	}
}

if ( !function_exists( 'ep_get_map' ) ) {
	function ep_get_map( $width = "100%", $height = "100%", $postid = 0 ) {
		global $post;
		if ( $postid == 0 ) $postid = $post->ID;

		$show_map = (bool) get_post_meta( $postid, '_ep_map', true );
		if ( $show_map ) {
			$latlong = get_post_meta( $postid, '_ep_latlong', true );

			$code = <<<CODE

			<script type = 'text/javascript'>
				window.onload = (function(){
					var map, options, latlong, marker;
					latlong = new google.maps.LatLng$latlong;
					options = {
						zoom: 8,
						center: latlong,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					map = new google.maps.Map( document.getElementById( 'ep-map-$postid' ), options );
					marker = new google.maps.Marker({
						map: map,
						position: latlong
					});
				});
			</script>

			<div id = 'ep-map-$postid-container' class = 'ep-map-container' style = 'width: {$width}; height: {$height};'>
				<div id = 'ep-map-$postid' class = 'ep-map' style = 'width: 100%; height: 100%'>
				</div>
			</div>
CODE;
			return $code;
		} else return "";
	}
}

if ( !function_exists( 'ep_map' ) ) {
	function ep_map( $width = "100%" , $height = "200px" ) {
		echo ep_get_map( $width, $height );
	}
}

if ( !function_exists( 'ep_render_event' ) ) {
	function ep_render_event( $event = '' ) {
		global $post;
		if ( empty( $event ) ) $event = $post;

		$link = get_permalink( $event->ID );
		echo <<<EVENT
		<a class = 'ep-event-title' href = '$link'>{$event->post_title}</a>
EVENT;
	}
}


/**
 * ep_calendar
 *
 * Wrapper for a call to EP_Calendar.
 *
 * @since 0.1
 */
if ( !function_exists( 'ep_calendar' ) ) {
	function ep_calendar( $args = '' ) { 
		$calendar = new EP_Calendar( $args );	
	}
}

if ( !function_exists( 'ep_render_reg_form' ) ) {
	function ep_render_reg_form( $postid = 0 ) {
		global $post;

		if ( $postid == 0 ) $event = $post;
		else $event = get_post( $postid );

		$reg_form = unserialize( get_post_meta( $event->ID, '_ep_regform', true ) );

		if( $reg_form ) {
			foreach( $reg_form['label'] as $index => $value ) {
				echo "<label for = 'ep-regform-{$reg_form['label'][$index]}' class = 'ep-regform-label'>{$reg_form['label'][$index]}</label>";
				switch( $reg_form['type'][$index] ) {
					case 'text':
						echo "<input type = 'text' name = 'ep-regform[{$reg_form['label'][$index]}]' id = 'ep-regform-{$reg_form['label'][$index]}' value = '{$reg_form['default'][$index]}' />";
						break;
					case 'textarea':
						echo "<textarea name = 'ep-regform[{$reg_form['label'][$index]}]' id = 'ep-regform-{$reg_form['label'][$index]}' />{$reg_form['default'][$index]}</textarea>";
						break;
					case 'checkbox':
						echo "<input type = 'checkbox' name = 'ep-regform[{$reg_form['label'][$index]}]' id = 'ep-regform-{$reg_form['label'][$index]}' value = '{$reg_form['default'][$index]}' />";
						break;
				}
				echo "<p class = 'ep-regform-description'>{$reg_form['description'][$index]}</p>";
			}
		}
	}
}
