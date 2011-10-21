<?php

/**
 * Show the registration details metabox
 *
 * @package EventPress
 * @subpackage EP_MetaBoxes
 */
class EP_Reg_MB extends KB_Meta_Box {
	protected $plugin = 'eventpress';
	protected $id = 'ep-reg-mb';
	protected $setting;
	protected $post_type = 'ep_event';
	protected $context = 'normal';

	public function __construct() {
		$this->title = __( "Registration Details", 'eventpress' );
		parent::__construct();

		$this->setting = new EP_Setting( 'Registration', Array(
			'Enabled' => true
		) );
	}
}

new EP_Reg_MB();
