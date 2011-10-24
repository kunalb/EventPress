<?php

/**
 * Register the custom post type, and other functionality
 *
 * @package EventPress
 * @subpackage EP_Events
 */
class EP_Events extends KB_Cpt {
	protected $id = 'ep_event';

	/**
	 * @hook EP_init
	 */
	public function set_args() {
		$this->icon32  = EP_IMAGES_URL . '/calendar.png';
		$this->icon16  = EP_IMAGES_URL . '/calendar16.png';
		$this->icon16a = EP_IMAGES_URL . '/calendar16a.png';
		$this->icon16x = EP_IMAGES_URL . '/calendar16x.png';

		$this->args = Array(
			'labels'     => Array( 
				'name'               => __( 'Events',                   'eventpress' ), 
				'singular_name'      => __( 'Event',                    'eventpress' ),
				'add_new'            => __( 'Add New',                  'eventpress' ),
				'all_items'          => __( 'Events',                   'eventpress' ),
				'edit_item'          => __( 'Edit Event',               'eventpress' ),
				'new_item'           => __( 'New Event',                'eventpress' ),
				'view_item'          => __( 'View Event',               'eventpress' ),
				'search_items'       => __( 'Search Events',            'eventpress' ),
				'not_found'          => __( 'No events found',          'eventpress' ),
				'not_found_in_trash' => __( 'No events found in trash', 'eventpress' ),
				'parent_item_colon'  => __( 'Parent Event',             'eventpress' ),
				'menu_name'          => __( 'Events',                   'eventpress' )
			),
			'description'    => __( 'Any events you would like to share on your blog: anything from conventions to birthdays!', 'eventpress' ),
			'public'         => true,
			'show_in_menu'   => true,
			'menu_position'  => 5, 
			'map_meta_cap'   => true,
			'capability'     => Array( 'ep_event', 'ep_events' ), /* For backpat */
			'hierarchical'   => true,
			'supports'       => Array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackback', 'custom-fields', 'comments', 'revisions', 'page-attributes' ),
			'has_archive'    => true,
			'rewrite'        => Array( 'slug' => 'event' ),
			'taxonomies'     => Array( 'ep_category', 'ep_tag' ) 
		);
	}

	public function edit_resources( $screen ) {
		wp_enqueue_script( 'ep-edit', EP_SCRIPTS_URL . '/edit.js' );
	}

	public function help( $screen ) {
		return $screen->post_type;
	}
}

new EP_Events();
