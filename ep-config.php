<?php

/**
 * Configuration page for eventpress
 *
 * @package EventPress
 * @subpackage EP_Events
 */
class EP_Config extends KB_Config {
	protected $parent   = "options-general.php";
	protected $position = 1;
	protected $title;
	protected $plugin = 'eventpress';

	public function __construct() {
		$this->menu_title = __( "Events", "eventpress" );
		$this->page_title = __( "EventPress Settings", "eventpress" );
		$this->title = $this->page_title;
		parent::__construct();
	}
	
	public function help() {
		return "Detailed explanation of configuration options in EventPress here.";
	}
}

new EP_Config();
