<?php

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

<?php
	if ( have_posts() )
		the_post();
?>

			<h1 class="page-title">
				<?php _e( 'Event Archives' ); ?>
			</h1>

<?php
	rewind_posts();

	 get_template_part( 'events_loop' );
?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
