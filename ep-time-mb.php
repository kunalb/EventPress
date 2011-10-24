<?php

/**
 * Show and save metadata regarding event's schedule.
 *
 * @package EventPress
 * @subpackage EP_MetaBoxes
 */
class EP_Time_MB extends KB_Meta_Box {
	/**
	 * Base plugin identifier.
	 * @var string
	 */
	protected $plugin = 'eventpress';

	/**
	 * Meta box identifier
	 * @var String
	 */
	protected $id = 'ep-time-mb';

	/**
	 * Settings for this plugin
	 * @var StdClass
	 */
	protected $settings;

	/**
	 * Post type to be attached to.
	 * @var String
	 */
	protected $post_type = 'ep_event';

	/**
	 * Attach to the side.
	 * @var String
	 */
	protected $context = 'side';

	/** 
	 * Constructor: set the i18n-ed strings and grab settings.
	 */
	public function __construct() {
		$this->title = __( "Event Schedule", 'eventpress' );
		$this->priority = 'high';
		$this->settings = new StdClass();
		parent::__construct();

		$this->settings->time = new EP_Setting( __( "Event Metadata\\Schedule\\Show time controls", 'eventpress' ), true );
		$this->settings->date_input_format = new EP_Setting( __("Event Metadata\\Schedule\\Date input format", 'eventpress' ), __("dd-mm-yyyy", 'eventpress') );
		$this->settings->time_input_format = new EP_Setting( __("Event Metadata\\Schedule\\Time input format", 'eventpress' ), __("hh:mm", 'eventpress') );
		/** Supported input format list: http://www.php.net/manual/en/datetime.formats.php */
	}

	protected function body( $event ) {
		$input_format = $this->settings->date_input_format->get() 
		              . (( $this->settings->time->get() )? " " .$this->settings->time_input_format->get() : "");

		echo <<<SCRIPTLESS
		<div class = 'ep-no-js' id = 'ep-schedule-scriptless'>
			<p><label for = 'ep-schedule-from'>From <span class='ep-schedule-format'>($input_format)</span></label><br/>
			<input type = 'text' name = 'ep-schedule-from' id = 'ep-schedule-from'></p>
			<p><label for = 'ep-schedule-from'>To <span class='ep-schedule-format'>($input_format)</span></label><br/>
			<input type = 'text' name = 'ep-schedule-to' id = 'ep-schedule-to'></p>
		</div>
SCRIPTLESS;

		echo <<<SCRIPTED
		
SCRIPTED;
	}

	protected function save( $eventid, $event ) {
	}

	protected function resources() {
		wp_enqueue_style( 'ep-event-metabox', EP_STYLES_URL . '/metabox.css' );
		wp_enqueue_script( 'ep-schedule-metabox', EP_SCRIPTS_URL . '/schedule.js' );
	}
}

new EP_Time_MB();
