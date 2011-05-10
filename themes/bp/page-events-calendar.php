<?php
/*
Template Name: Events Calendar
*/

wp_enqueue_style( 'ep-calendar-style', EP_REL_URL . '/themes/bp/assets/css/calendar' . kb_ext() . '.css' );

?>

<?php get_header(); ?>

	<div id="content">
		<div class="padder">
			<div class="page" id="blog-single">
				<h2 class="page-title">
					<?php _e( 'Events Calendar', 'eventpress' ); ?>
				</h2>

				<h1><?php echo date( 'F Y' ); ?></h1>

					<?php ep_calendar(); ?>
			</div>
		</div><!-- .padder -->
	</div><!-- #content -->

	<?php locate_template( array( 'sidebar.php' ), true ) ?>

<?php get_footer() ?>
