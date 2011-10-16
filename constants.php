<?php

/**#@+
 * Constants
 */

/** Plugin Folder */
define( 'EP_FOLDER', 'eventpress' );

/** Plugin Directory */
define( 'EP_DIR', WP_PLUGIN_DIR . '/' . EP_FOLDER  );

/** Hand written basename to avoid soft linking issues */
define( 'EP_FILE', EP_FOLDER . '/eventpress.php' );

/** The current version of the plugin */
define( 'EP_VERSION', '0.2-bleeding' );

/** The URL of the plugin */
define( 'EP_URL', plugins_url( 'eventpress' ) );

/** Resources URL */
define( 'EP_RESOURCES_URL', EP_URL . '/resources' );

/** CSS resources directory */
define( 'EP_STYLES_URL', EP_RESOURCES_URL . '/css' );

/** Images resources directory */
define( 'EP_IMAGES_URL', EP_RESOURCES_URL . '/images' );

/** Scripts resources directory */
define( 'EP_SCRIPTS_URL', EP_RESOURCES_URL . '/js' );

/** The default slug for an event. */
define( 'EP_EVENT_SLUG', 'event' );

/** The i18n version */
define( 'EP_EVENT_I18N_SLUG', _x( 'event', 'Translated slug', 'eventpress' ) );

/** The default slug for events. */
define( 'EP_EVENTS_SLUG', 'events' );

/** The i18n version for events. */
define( 'EP_EVENTS_I18N_SLUG', _x( 'events', 'Translated slug', 'eventpress' ) );

/**#@-*/
