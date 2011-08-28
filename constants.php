<?php

/**#@+
 * Constants
 */

/** The current version of the plugin */
define( 'EP_VERSION', '0.2-bleeding' );

/** The plugin directory, for including files, etc. */
define( 'EP_DIR', dirname(__FILE__) );

/** The URL of the plugin */
define( 'EP_URL', plugins_url( $path = '/' . basename( dirname( __FILE__ ) ) ) );

/** The default slug for an event. */
define( 'EP_EVENT_SLUG', 'event' );

/** The i18n version */
define( 'EP_EVENT_I18N_SLUG', _x( 'event', 'Translated slug', 'eventpress' ) );

/** The default slug for events. */
define( 'EP_EVENTS_SLUG', 'events' );

/** The i18n version for events. */
define( 'EP_EVENTS_I18N_SLUG', _x( 'events', 'Translated slug', 'eventpress' ) );

/**#@-*/
