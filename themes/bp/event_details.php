<div class = 'ep-event-details-container'>
	<div class = 'ep-event-details'>
	<?php if( ep_get_start_date() ) { ?>
		<span class = 'ep-event-detail' id = 'ep-start-time'><?php ep_start_time(); ?></span>,
		<span class = 'ep-event-detail' id = 'ep-start-date'><?php ep_start_date(); ?></span>
	<?php } ?>
	
	<?php if( ep_get_start_date() && ep_get_end_date() ) ?>
		&mdash;

	<?php if( ep_get_end_date() ) { ?>
		<span class = 'ep-event-detail' id = 'ep-end-date'><?php ep_end_time(); ?></span>,
		<span class = 'ep-event-detail' id = 'ep-end-time'><?php ep_end_date(); ?></span>
	<?php } ?>

	<?php if( ep_get_venue() ) { ?>
		<?php if( ep_get_start_date() || ep_get_end_date() ) { ?>
			<?php _e( 'at', 'eventpress' ); ?>
		<?php } else { ?>
			<?php _e( 'At', 'eventpress' ); ?>
		<?php } ?>

		<span class = 'ep-event-detail' id = 'ep-venue'><?php ep_venue(); ?></span>.
	<?php } ?>

	<?php if ( bpcp_is_home() ) { ?>
		<br />
		<?php if( ep_get_registration_time_open() ) { ?>
			<?php _e( 'Registration', 'eventpress' ); ?>
			<?php if( ep_get_registration_time_open() ) { ?>
				<?php _e( ' from ', 'eventpress' ); ?>
				<span class = 'ep-event-detail' id = 'ep-startreg-time'><?php ep_registration_time_open(); ?></span>, 
				<span class = 'ep-event-detail' id = 'ep-startreg'><?php ep_registration_date_open(); ?></span>
			<?php } ?>
			<?php if( ep_get_registration_time_close() ) { ?>
				<?php _e( ' to ', 'eventpress' ); ?>
				<span class = 'ep-event-detail' id = 'ep-stopreg-time'><?php ep_registration_time_close(); ?></span>, 
				<span class = 'ep-event-detail' id = 'ep-stopreg'><?php ep_registration_date_close(); ?></span>.
			<?php } ?>
		<?php } ?>
	<?php } ?>
	</div>

	<?php if ( bpcp_is_home() ) 
		ep_map( "100%", "300px" ); ?>	
</div>
