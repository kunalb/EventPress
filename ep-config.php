<?php

/**
 * Configuration page for eventpress
 *
 * @package EventPress
 * @subpackage EP_Events
 */
class EP_Config extends KB_Config {
	/**
	 * Set the plugin identification details.
	 * @var String
	 */
	protected $plugin = 'eventpress';

	/**
	 * Set up inital settings: the menu and page titles.
	 */
	public function __construct() {
		$this->menu_title = __( "Events", "eventpress" );
		$this->page_title = __( "EventPress Settings", "eventpress" );
		$this->title = $this->page_title;
		parent::__construct();
	}

	/**
	 * The help text for this plugin.
	 */
	public function help() {
		return "Detailed explanation of configuration options in EventPress here.";
	}
}

new EP_Config();
