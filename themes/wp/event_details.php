<div class = 'ep-event-details'>
<span class = 'ep-event-detail' id = 'ep-start-time'><?php ep_start_time(); ?></span>,
<span class = 'ep-event-detail' id = 'ep-start-date'><?php ep_start_date(); ?></span> 

<?php if( ep_get_end_time() != '') { ?>
to
<span class = 'ep-event-detail' id = 'ep-end-date'><?php ep_end_time(); ?></span>,
<span class = 'ep-event-detail' id = 'ep-end-time'><?php ep_end_date(); ?></span> 
<?php } ?>

<?php if( ep_get_venue() != '') { ?>
at
<span class = 'ep-event-detail' id = 'ep-venue'><?php ep_venue(); ?></span>
<?php } ?>.

<?php if ( is_single() ) { ?>
	<br />
	<?php _e( 'Registration is from ', 'eventpress' ); ?><span class = 'ep-event-detail' id = 'ep-startreg-time'><?php ep_registration_time_open(); ?></span>, <span class = 'ep-event-detail' id = 'ep-startreg'><?php ep_registration_date_open(); ?></span> 
	<?php _e( ' to ', 'eventpress' ); ?><span class = 'ep-event-detail' id = 'ep-stopreg-time'><?php ep_registration_time_close(); ?></span>, <span class = 'ep-event-detail' id = 'ep-stopreg'><?php ep_registration_date_close(); ?></span>.

<?php } ?>
</div>

<?php if ( is_single() ) 
	ep_map(); ?>	
