<?php
/*
Template Name: Events Calendar
*/

wp_enqueue_style( 'ep-calendar-style', EP_REL_URL . '/themes/wp/assets/css/calendar.css' );

?>

<?php get_header(); ?>

<div id="container">
	<div id="content" class = 'main'>
		<h1 class="page-title">
			<?php _e( 'Events Calendar', 'eventpress' ); ?>
		</h1>

		<h1><?php echo date( 'F Y' ); ?></h1>

		<div id  = 'ep_calendar'>
			<?php ep_calendar(); ?>
		</div>
	</div><!-- #content -->
</div><!-- #container -->

<?php get_footer(); ?>
