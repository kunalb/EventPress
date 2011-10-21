<?php

/**
 * Show and save metadata regarding event's schedule.
 *
 * @package EventPress
 * @subpackage EP_MetaBoxes
 */
class EP_Time_MB extends KB_Meta_Box {
	protected $plugin = 'eventpress';
	protected $title  = 'ep-times';
	protected $id = 'ep-time-mb';
	protected $setting;
	protected $post_type = 'ep_event';
	protected $context = 'side';

	public function __construct() {
		$this->title = __( "Event Schedule", 'eventpress' );
		$this->priority = 'high';
		parent::__construct();

		$this->setting = new EP_Setting( 'Event Schedule', Array(
			'Show controls for hours and minutes' => true
		) );
	}
}

new EP_Time_MB();
