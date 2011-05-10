<?php

define( 'EP_DATE_FORMAT', 'm/d/Y' );
define( 'EP_TIME_FORMAT', 'H:i' );

/**
 * Class to add all the view related stuff to the admin. (Metaboxes, etc.)
 *
 * @since 0.1
 */
class ep_admin_view {
	/**
	 * Constructor.
	 *
	 * Maps the error message in the passed in $_GET to error messages; adds scripts required.
	 *
	 * @since 0.1
	 */
	function ep_admin_view() {
		$error_maps = Array(
			1 => __( "<p>The format of the event's starting date cannot be recognized!</p>" ),
			2 => __( "<p>The format of the event's ending date cannot be recognized!</p>" ),
			3 => __( "<p>This event's end date has been set before the start date!</p>" ),
			4 => __( "<p>The format of the registration start date cannot be recognized!</p>" ),
			5 => __( "<p>The format of the registration stop date cannot be recognized!</p>" ),
			6 => __( "<p>The registration stop date has been set before the start date!</p>" ),
			7 => __( "<p>Registration starts after the event is over!</p>" ),
			8 => __( "<p>Please enter a valid registration limit!</p>" )
		);
		$errors = new kb_errors( Array( 'error_maps' => $error_maps, 'get_var' => 'ep_meta_msgs' ) );
	}

	/**
	 * Add the menu creation functions to run with admin.
	 *
	 * @since 0.1
	 */
	function init () {
		add_action ( 'admin_head', array( 'ep_admin_view', 'admin_head' ) );
		add_action ( 'admin_menu', array( 'ep_admin_view', 'admin_separator' ) );
		add_action ( 'admin_menu', array( 'ep_admin_view', 'add_to_menu' ) );
	}

	function add_to_menu() {
		/* TODO Enable after constructing menu page. */
		//add_menu_page( 'EventPress', 'EventPress', 'manage_options', 'epmain', Array( 'ep_admin_view', 'main_page' ), 'x', 29 );
	}
	
	/**
	 * The CSS Required to style these.
	 *
	 * @since 0.1
	 */
	function admin_head () {
		$event_icon_url		= EP_REL_URL . '/themes/admin/assets/images/admin-icon-events.png';
		$register_icon_url	= EP_REL_URL . '/themes/admin/assets/images/admin-icon-register.png';
		$main_icon_url	= EP_REL_URL . '/themes/admin/assets/images/logo-icon.png';
?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
			#menu-posts-epevent .wp-menu-image {
				background: url(<?php echo $event_icon_url; ?>) no-repeat 0px -32px !important;
			}
			#menu-posts-epevent:hover .wp-menu-image,
			#menu-posts-epevent.wp-has-current-submenu .wp-menu-image {
				background: url(<?php echo $event_icon_url; ?>) no-repeat 0px 0px !important;
			}

			#menu-posts-epreg .wp-menu-image {
				background: url(<?php echo $register_icon_url; ?>) no-repeat 0px -32px !important;
			}
			#menu-posts-epreg:hover .wp-menu-image,
			#menu-posts-epreg.wp-has-current-submenu .wp-menu-image {
				background: url(<?php echo $register_icon_url; ?>) no-repeat 0px -1px !important;
			}
			#toplevel_page_epmain .wp-menu-image {
				background: url(<?php echo $main_icon_url; ?>) no-repeat 2px -24px !important;
			}
			#toplevel_page_epmain:hover .wp-menu-image,
			#toplevel_page_epmain.wp-has-current-submenu .wp-menu-image {
				background: url(<?php echo $main_icon_url; ?>) no-repeat 2px 2px !important;
		/*]]>*/
		</style>
<?php
	}
	
	/**
	 * Modifies the menu to add the separator.
	 *
	 * @since 0.1
	 *
	 * @uses $menu
	 */
	function admin_separator () {
		global $menu;

		if ( current_user_can( 'edit_events' ) ) {
			$menu[24] = $menu[25];
			$menu[25] = array( '', 'read', 'separator1', '', 'wp-menu-separator' );
		}
	}

	/**
	 * Displays the main configuration/about page for EventPress.
	 *
	 * @since 0.2
	 */

	function main_page() {
	?>
		<div class = 'wrap'>
			<div id = 'ep-icon'><br /></div>
			<h2>EventPress</h2>
			<h3>Configuration</h3>
		</div>
	<?php
	}


	/** 
	 * Scripts and styles required for editing in BuddyPress.
	 *
	 * @since 0.1
	 */
	function bp_edit_resources() {
		wp_enqueue_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false' );
		wp_enqueue_script( 'ep-admin-script', EP_REL_URL . '/themes/admin/assets/js/ep_admin' . kb_ext() . '.js', 'google-maps' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_script( 'ep-jquery-ui-slider', EP_REL_URL . '/themes/admin/assets/js/jquery.ui.slider' . kb_ext() . '.js', 'jquery-ui' );
		wp_enqueue_script( 'ep-jquery-ui-datepicker', EP_REL_URL . '/themes/admin/assets/js/jquery.ui.datepicker' . kb_ext() . '.js', 'ep-jquery-ui-slider' );
		wp_enqueue_script( 'ep-jquery-ui-timepicker', EP_REL_URL . '/themes/admin/assets/js/jquery-ui-timepicker-addon' . kb_ext() . '.js', 'ep-jquery-ui-datepicker' );
		wp_enqueue_style( 'ep-edit', EP_REL_URL . '/themes/admin/assets/css/bp_edit' . kb_ext() . '.css' );
		wp_enqueue_style( 'ep-jquery-css', EP_REL_URL . '/themes/bp/assets/css/ui-lightness/jquery.ui.all' . kb_ext() . '.css' );
	}

	/** 
	 * Scripts and styles required for editing in WordPress Admin.
	 *
	 * @since 0.1
	 */
	function admin_resources( $var ) {
		global $post;

		if ( ( 'post.php' == $var || 'post-new.php' == $var ) && $post->post_type == 'ep_event' ) {
			wp_enqueue_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'ep-jquery-ui-slider', EP_REL_URL . '/themes/admin/assets/js/jquery.ui.slider' . kb_ext() . '.js', 'jquery-ui' );
			wp_enqueue_script( 'ep-jquery-ui-datepicker', EP_REL_URL . '/themes/admin/assets/js/jquery.ui.datepicker' . kb_ext() . '.js', 'ep-jquery-ui-slider' );
			wp_enqueue_script( 'ep-jquery-ui-timepicker', EP_REL_URL . '/themes/admin/assets/js/jquery-ui-timepicker-addon' . kb_ext() . '.js', 'ep-jquery-ui-datepicker' );
			wp_enqueue_script( 'ep-admin-script', EP_REL_URL . '/themes/admin/assets/js/ep_admin' . kb_ext() . '.js', 'google-maps' );
			wp_enqueue_style( 'ep-admin-css', EP_REL_URL . '/themes/admin/assets/css/ep_admin' . kb_ext() . '.css' );
			wp_enqueue_style( 'ep-jquery-css', EP_REL_URL . '/themes/admin/assets/css/smoothness/jquery.ui.all' . kb_ext() . '.css' );
		} else if( 'toplevel_page_epmain' == $var ) {
			wp_enqueue_style( 'ep-main-admin-css', EP_REL_URL . '/themes/admin/assets/css/ep_main_admin'. kb_ext() .'.css' );
		}

	}

	/**
	 * Code for generating the google map metabox.
	 *
	 * @since 0.1
	 */
	function map_metabox() {
		global $post_ID;

		$default = (bool) get_post_meta( $post_ID, '_ep_map', true );
		$checked = ($default)? " checked = 'checked' " : " ";

		$default2 = get_post_meta( $post_ID, '_ep_latlong', true );

		?>
		<noscript><?php _e( 'JavaScript must be enabled for this feature to work.', 'eventpress' ); ?></noscript>
	
		<div id = 'ep-map-container' style = 'width: 100%; height: 200px;' >
			<div id = 'ep-map-map' style = 'width: 100%; height: 100%;' >
			</div>
		</div>
		<label class="selectit" for="ep_map">
			<input type="checkbox"<?php echo $checked; ?>value="true" id="ep-map" name="ep-meta[map]"><?php _e( 'Display Map?', 'eventpress' ); ?>
		</label>

		<p><?php _e( '(Changing the venue will automatically update the map.)', 'eventpress' ); ?></p>

		<input type = 'hidden' value = '' id = 'ep-map-latlong' name = 'ep-meta[latlong]' />
		<?php
	}

	/**
	 * Metabox for generating a custom form for registration.
	 *
	 * @since 0.1
	 */
	function metabox_custom_registration() {
		global $post;
		
		$existing_data = ( unserialize( ( get_post_meta( $post->ID, '_ep_regform', true ) ) ) );
		$existing_data = maybe_unserialize( $existing_data );		

		$existing_data[ 'type' ][] = '';
		$existing_data[ 'label' ][] = '';
		$existing_data[ 'validation' ][] = '';
		$existing_data[ 'regex' ][] = '';
		$existing_data[ 'description' ][] = '';
		$existing_data[ 'default' ][] = '';

		$count = count( $existing_data['type'] );

		echo "<div id = 'ep-add-form-row'>";
		foreach( $existing_data['type'] as $index => $value ) {

			if( $count - 1 == $index )
				$id = 'generic';
			else
				$id = $index;

			$text_selected = ( $existing_data['type'][$index] == 'text' ) ? "selected = 'selected'" : "";
			$checkbox_selected = ( $existing_data['type'][$index] == 'checkbox' ) ? "selected = 'selected'" : "";
			$textarea_selected = ( $existing_data['type'][$index] == 'textarea' ) ? "selected = 'selected'" : "";

			$validation[ 'None' ] = '';
			$validation[ 'Number' ] = '';
			$validation[ 'Alphanumeric' ] = '';
			$validation[ 'Email' ] = '';
			$validation[ 'Other' ] = '';

			$validation[ $existing_data['validation'][$index] ] = "selected = 'selected'";

			$i18n_label = __( 'Label', 'eventpress' );
			$i18n_textbox = __( 'Textbox', 'eventpress' );
			$i18n_checkbox = __( 'Checkbox', 'eventpress' );
			$i18n_textarea = __( 'Textarea', 'eventpress' );
			$i18n_description = __( 'Description', 'eventpress' );
			$i18n_type = __( 'Type', 'eventpress' );
			$i18n_defaultval = __( 'Default Value', 'eventpress' );
			$i18n_validation = __( 'Validation', 'eventpress' );
			$i18n_none = __( 'None', 'eventpress' );
			$i18n_number = __( 'Number', 'eventpress' );
			$i18n_alphanumeric = __( 'Alphanumeric', 'eventpress' );
			$i18n_email = __( 'Email', 'eventpress' );
			$i18n_other = __( 'Other', 'eventpress' );
			$i18n_regex = __( 'Regular Expression', 'eventpress' );
			$i18n_deletefield = __( 'Delete this field', 'eventpress' );

			echo <<<FORMTABLE
				<table class = 'ep-reg-row' id = 'ep-reg-row-$id'>
					<tr>
						<td>
							<label for = 'ep-reg[label][]'>$i18n_label</label>
							<input type = 'text' name = 'ep-reg[label][]' class = 'ep-reg-element' value = '{$existing_data['label'][$index]}' />
						</td>
						<td>
							<label for = 'ep-reg[type][]'>$i18n_type</label>
							<select name = 'ep-reg[type][]' class = 'ep-reg-type ep-reg-element'>
								<option value = 'text' $text_selected >$i18n_textbox</option>
								<option value = 'checkbox' $checkbox_selected >$i18n_checkbox</option>
								<option value = 'textarea' $textarea_selected >$i18n_textarea</option>
							</select>
						</td>
					</tr><tr>
						<td colspan = '2'>
							<label for = 'ep-reg[description][]'>$i18n_description</label><br />
							<textarea type = 'text' name = 'ep-reg[description][]' class = 'ep-reg-element'>{$existing_data['description'][$index]}</textarea>
						</td>
					</tr><tr>
						<td>
							<label for = 'ep-reg[default][]'>$i18n_defaultval</label>
							<input type = 'text' name = 'ep-reg[default][]' value = '{$existing_data['default'][$index]}' class = 'ep-reg-element'/>
						</td>
					</tr><tr class = 'textbox-only'>
						<td>
							<label for = 'ep-reg[validation][]'>$i18n_validation</label>
							<select name = 'ep-reg[validation][]' class = 'ep-regexoption ep-reg-element hide-if-no-js'>
								<option value = 'None' {$validation['None']}>$i18n_none</option>
								<option value = 'Number' {$validation['Number']}>$i18n_number</option>
								<option value = 'Alphanumeric' {$validation['Alphanumeric']}>$i18n_alphanumeric</option>
								<option value = 'Email' {$validation['Email']}>$i18n_email</option>
								<option value = 'Other' {$validation['Other']}>$i18n_other</option>
							</select>
						</td>
						<td>
							<label for = 'ep-reg[regex][]'>$i18n_regex</label>
							<input type = 'text' name = 'ep-reg[regex][]' class = 'ep-reg-element ep-regexoption-value' value = '{$existing_data['regex'][$index]}' />
						</td>
					</tr>
					<tr>
						<td colspan = '2'><a href = '#' class = 'hide-if-no-js ep-reg-delete'>$i18n_deletefield</a></td>
					</tr>
				</table>

FORMTABLE;
		}
		?>
		</div>
		<a href = '#' id = 'ep-new-field' class = 'hide-if-no-js'><?php _e( 'Add a new field', 'eventpress' ); ?></a>
		<?php
	}

	/**
	 * Metabox for the start date.
	 * 
	 * @param int $id The Post Id
	 * @param mixed $default The default value for this
	 *
	 * @since 0.1
	 */
	function metabox_start( $id, $default ) {
		$date = ( '' == $default ) ? '' : date( EP_DATE_FORMAT , $default ) . " " . date( EP_TIME_FORMAT , $default ); 
	?>
		<label for = 'ep-<?php echo $id; ?>-input'><?php _e('When does the event start?', 'eventpress') ?></label>
		<input type = 'text' id = 'ep-<?php echo $id; ?>-input' name = 'ep-meta[<?php echo $id ?>]' class = 'datepicker' value = "<?php echo $date; ?>"/>
	<?php
	}

	/**
	 * Metabox for the end date.
	 * 
	 * @param int $id The Post Id
	 * @param mixed $default The default value for this
	 *
	 * @since 0.1
	 */
	function metabox_end( $id, $default ) {
		$date = ( '' == $default ) ? '' : date( EP_DATE_FORMAT , $default ) . " " . date( EP_TIME_FORMAT , $default ); 
	?>
		<label for = 'ep-<?php echo $id; ?>-input'><?php _e('When does the event end?', 'eventpress') ?></label>
		<input type = 'text' id = 'ep-<?php echo $id; ?>-input' name = 'ep-meta[<?php echo $id; ?>]' class = 'datepicker' value = "<?php echo $date; ?>"/>
	<?php
	}

	/**
	 * Metabox for the venue.
	 * 
	 * @param int $id The Post Id
	 * @param mixed $default The default value for this
	 *
	 * @since 0.1
	 */
	function metabox_venue( $id, $default ) {
	?>
		<label for = 'ep-<?php echo $id; ?>-input'><?php _e('Where will it be held?', 'eventpress') ?></label>
		<input type = 'text' id = 'ep-<?php echo $id; ?>-input' name = 'ep-meta[<?php echo $id; ?>]' value = "<?php echo $default; ?>"/>
	<?php
	}

	/**
	 * Metabox for the  registration start date.
	 * 
	 * @param int $id The Post Id
	 * @param mixed $default The default value for this
	 *
	 * @since 0.1
	 */
	function metabox_regstart( $id, $default ) {
		$date = ( '' == $default ) ? '' : date( EP_DATE_FORMAT , $default ) . " " . date( EP_TIME_FORMAT , $default ); 
	?>
		<label for = 'ep-<?php echo $id; ?>-input'><?php _e('Registration opens at?', 'eventpress') ?></label>
		<input type = 'text' id = 'ep-<?php echo $id; ?>-input' name = 'ep-meta[<?php echo $id; ?>]' class = 'datepicker' value = "<?php echo $date; ?>"/>
	<?php
	}

	/**
	 * Metabox for the registration end date.
	 * 
	 * @param int $id The Post Id
	 * @param mixed $default The default value for this
	 *
	 * @since 0.1
	 */
	function metabox_regend( $id, $default ) {
		$date = ( '' == $default ) ? '' : date( EP_DATE_FORMAT , $default ) . " " . date( EP_TIME_FORMAT , $default ); 
	?>
		<label for = 'ep-<?php echo $id; ?>-input'><?php _e('Registration closes at?', 'eventpress') ?></label>
		<input type = 'text' id = 'ep-<?php echo $id; ?>-input' name = 'ep-meta[<?php echo $id; ?>]' class = 'datepicker' value = "<?php echo $date; ?>"/>
	<?php
	}

	/**
	 * Metabox for the registration limit.
	 * 
	 * @param int $id The Post Id
	 * @param mixed $default The default value for this
	 *
	 * @since 0.1
	 */
	function metabox_limitreg( $id, $default ) {
	?>
		<label for = 'ep-<?php echo $id; ?>-input'><?php _e('Maximum number of attendees?', 'eventpress') ?></label>
		<input type = 'text' id = 'ep-<?php echo $id; ?>-input' name = 'ep-meta[<?php echo $id; ?>]' value = "<?php echo $default; ?>"/>
	<?php
	}

	/**
	 * Metabox for asking if registration approval is required.
	 * 
	 * @param int $id The Post Id
	 * @param mixed $default The default value for this
	 *
	 * @since 0.1
	 */
	function metabox_confirmreg( $id, $default ) {
		if ( $default != '' && $default != false )
			$checked = "checked = 'checked'";
	?>
		<label class = 'checkbox' for = 'ep-<?php echo $id; ?>-input'><?php _e('Approve registrations automatically?', 'eventpress') ?></label>
		<input type = 'checkbox' id = 'ep-<?php echo $id; ?>-input' name = 'ep-meta[<?php echo $id; ?>]' value = "autoreg" <?php echo $checked; ?>/>
	<?php
	}

	/**
	 * One metabox to rule them all /
	 * One metabox to find them /
	 * One metabox to bring them all /
	 * and in the admin theme to bind them.
	 *
	 * @since 0.1
	 */
	function metabox() {
		global $post;

		$grid = Array(
				Array(
					Array(
						'width' => 4,
						'id' => 'start',
						'contents' => Array(&$this, 'metabox_start')
					),
					Array(
						'width' => 6,
						'id' => 'end',
						'contents' => Array(&$this, 'metabox_end')
					),
				),
				Array(
					Array(
						'width' => 10,
						'id' => 'venue',
						'contents' => Array(&$this, 'metabox_venue')
					)
				),
				Array(
					Array(
						'width' => 3,
						'id' => 'startreg',
						'contents' => Array(&$this, 'metabox_regstart')

					),
					Array(
						'width' => 3,
						'id' => 'stopreg',
						'contents' => Array(&$this, 'metabox_regend')

					),
					Array(
						'width' => 4,
						'id' => 'limitreg',
						'contents' => Array(&$this, 'metabox_limitreg')
					)
				),
				Array(
					Array(
						'width' => 10,
						'id' => 'confirmreg',
						'contents' => Array(&$this, 'metabox_confirmreg')
					)
				)
		);

		echo "<table id = 'ep-metabox-container'>";
		foreach ($grid as $row) {
			echo "<tr>";
			foreach ($row as $box) {
				$default = esc_html( get_post_meta( $post->ID, '_ep_' . $box['id'], true ) );
				echo "<td colspan = {$box['width']} id = 'ep-meta-{$box['id']}'>";
				if ( is_callable( $box['contents'] ) )
					call_user_func( $box['contents'], $box['id'], $default );
				echo "</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	?>
		<input type = 'hidden' value = '<?php echo wp_create_nonce( 'ep-admin-metabox' ) ?>' name = 'ep-nonce[metabox]' />
	<?php
	}


	/**
	 * Modify the headers for reg post type.
	 *
	 * If part of a registration edit page, then show the standard checkbox.
	 * Otherwise, in a single event page -- give a checkbox with a modified name.
	 * 
	 * @since 0.1
	 */
	function registration_column_headers( $args ) {
		global $post;

		$cbhead = ( $post->post_type == 'ep_reg' )? 'cb' : 'cbreg';
		$class = ( $post->post_type == 'ep_reg' )? '' : 'ep-regs-checkbox';

		return Array(
			$cbhead => '<input class = "checkbox ' . $class . ' " type = "checkbox" />',
			'event' => __( 'Event' ),
			'author' => __( 'Registered User' ),
			'date' => __( 'Time' ),
			'status' => __( 'Status' )
		);
	}

	/**
	 * Output values for registration columns.
	 *
	 * @since 0.1
	 */
	function registration_column_values( $col, $id ) {
		global $post, $post_type_object;

		$page = get_post( $id );
		switch( $col ) {
			case 'event': 
				echo get_the_title( $post->post_parent );

				$edit_link = get_edit_post_link( $page->ID );
				$approve_link = EP_REL_URL . '/controllers/post.php?action=approve&eventid=' . $post->ID;  
				$cancel_link =	EP_REL_URL . '/controllers/post.php?action=cancel&eventid=' . $post->ID;  

				$actions = array();
				if ( current_user_can( 'edit_reg', $page->ID) && $post->post_status != 'trash' ) {
					$actions['edit'] = '<a href="' . $edit_link . '" title="' . esc_attr(__('Edit this page')) . '">' . __('Edit') . '</a>';
					$actions['ep_approve'] = '<a href = "' . $approve_link . '" title = "' . __('Approve this registration.') . '">' . __('Approve') . '</a>';
					$actions['ep_cancel'] = '<a href = "' . $cancel_link . '" title = "'. __('Cancel this registration.') .'">' . __('Cancel') . '</a>';
				}
				$actions = apply_filters('page_row_actions', $actions, $page);

				$action_count = count($actions);

				$i = 0;
				echo '<div class="row-actions">';
				foreach ( $actions as $action => $link ) {
					++$i;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					echo "<span class='$action'>$link$sep</span>";
				}
				echo '</div>';
				break;
			case 'status':
				$status = get_post_status_object( $page->post_status );
				echo $status->label;
				break;	
			case 'cbreg':
			?><input type="checkbox" name="ep-regs[ids][]" value="<?php echo $page->ID; ?>" class = 'checkbox ep-regs-checkboxes'/><?php		
		}
	}

	/**
	 * Set headers for event columns.
	 *
	 * @since 0.1
	 */
	function event_column_headers( $args ) {
		return Array(
			'cb' => '<input class = "checkbox" type = "checkbox" />',
			'title' => __( 'Event' ),
			'author' => __( 'Author' ),
			'regs' => __( 'Registrations (Pending, Cancelled)' ),
			'starts' => __( 'Starts' ),
			'ends'	=> __( 'Ends' )
		);
	}

	/**
	 * Output values for event columns.
	 *
	 * @since 0.1
	 */
	function event_column_values( $col, $id ) {
		global $ep_models;
		$post = get_post( $id );

		switch( $col ) {
			case 'regs': 
				$no = $ep_models['events']->get_registration_number( $post->ID );
				$val = $no->reg_approved . " (<span class = 'ep_reg_pending' style = 'color: #020661'>" . $no->reg_pending . "</span>, <span class = 'ep_reg_cancelled' style = 'color: #7a1506'>" . $no->reg_cancelled ."</span>)";
				break;
			case 'starts':
				$val = ( true == ( $date = get_post_meta( $post->ID, '_ep_start', true ) ) )? date( get_option( 'date_format' ), $date ) : '&mdash;';
				$val .= '<br />';
				$val .= ( true == ( $date = get_post_meta( $post->ID, '_ep_start', true ) ) )? date( get_option( 'time_format' ), $date ) : '&mdash;';
				break;
			case 'ends':
				$val =  ( true == ( $date = get_post_meta( $post->ID, '_ep_end', true ) ) )? date( get_option( 'date_format' ), $date ) : '&mdash;';
				$val .= '<br />';
				$val .=  ( true == ( $date = get_post_meta( $post->ID, '_ep_end', true ) ) )? date( get_option( 'time_format' ), $date ) : '&mdash;';
				break;
			default:
				$val = '';
		}

		echo $val;
	}

	/**
	 * Metabox to change registration status.
	 *
	 * @since 0.1
	 *
	 * @uses $post
	 */
	function metabox_reg_status() {
		global $post;
		switch( $post->post_status ) {
			case 'reg_approved':
				echo "Approved. (<a href ='" . EP_REL_URL . "/controllers/post.php?action=cancel&eventid={$post->ID}'>Cancel</a>)";
				break;
			case 'reg_cancelled':
				echo "Cancelled. (<a href ='" . EP_REL_URL . "/controllers/post.php?action=approve&eventid={$post->ID}'>Approve</a>)";
				break;
			case 'reg_pending':
				echo "Pending. ( <a href ='" . EP_REL_URL . "/controllers/post.php?action=approve&eventid={$post->ID}'>Approve</a> | <a href ='" . EP_REL_URL . "/controllers/post.php?action=cancel&eventid={$post->ID}'>Cancel</a> )";
				break;
			default:
				echo "Unknown.";
		}
	}

	/**
	 * Metabox to change registration status.
	 *
	 * @since 0.1
	 *
	 * @uses $post
	 */
	function metabox_author_details() {
		global $post;

		$author = $post->post_author;
		$data = get_userdata( $author );

		$avatar = get_avatar( $data->user_email, 96 );
		echo <<<AUTH_DETAILS
		<table>
			<tr>
				<td>$avatar</td>
				<td class = 'ep-auth-details'>
					<span class = 'ep-auth-detail'><a href = '{$data->user_url}'>{$data->display_name}</a></span>
					<span class = 'ep-auth-detail'>{$data->user_email}</span>
					<span class = 'ep-auth-detail'>{$data->description}</span>
				</td>
			</tr>
		</table>
AUTH_DETAILS;
	}

	/**
	 * Metabox to display registration details for current page.
	 *
	 * @since 0.1
	 */
	function metabox_registration_details() {
		if( !class_exists( 'WP_List_Table' ) )
			include ABSPATH . '/wp-admin/includes/class-wp-list-table.php';

		include "class-ep-regs-list-table.php";

		global $post, $current_screen, $_wp_column_headers;

		$sum = 0;
		$total = (Array)wp_count_posts( 'ep_reg' );
		foreach( $total as $type => $count )
			if ( $type != 'trash' )
				$sum += $count;

		if ( 1 > $sum ) {
			echo '<p>' . __( 'No registrations yet.', 'eventpress' ) . '</p>';
			return;
		}
		
		$regs = query_posts( "post_parent={$post->ID}&post_type=ep_reg" );
		
		?>

		<?php
			$global_current_screen = $current_screen;
			$current_screen = $screen;
			$screen = convert_to_screen( 'edit' );
			$screen->post_type = 'ep_reg';
			$screen->base = 'edit';

			$reg_table = new EP_Regs_List_Table();
			add_action( 'manage_posts_custom_column', Array( &$this, 'registration_column_values' ), 10, 2 );

			$reg_table->prepare_items();
			$reg_table->display();

			//Cleanup
			remove_action( 'manage_posts_custom_column', Array( &$this, 'registration_column_values' ), 10, 2 );
			remove_filter( 'manage_posts_columns', Array( &$this, 'registration_column_headers' ) );
			$current_screen = $global_current_screen;
	}

	/**
	 * Metabox for scheduling the event.
	 *
	 * @since 0.1
	 *
	 */
	function repeat_metabox( $postid ) {
		global $post;
		$postid = $post->ID;

		$unit = get_post_meta( $postid, '_ep_schedule_unit', true );
		$magnitude = get_post_meta( $postid, '_ep_schedule_magnitude', true );
		$till = get_post_meta( $postid, '_ep_schedule_till', true );

		$selected['days'] = '';
		$selected['weeks'] = '';
		$selected['months'] = '';
		$selected['years'] = '';
		$selected['hours'] = '';

		if ( $unit )
			$selected[$unit] = 'selected = "selected"';

		_e( 'Repeat this event every', 'eventpress' ); ?>
		<input name = 'ep-schedule[magnitude]' type = 'text' id = 'ep-schedule-mag' value = '<?php echo $magnitude; ?>'/>
		<select name = 'ep-schedule[unit]'>
			<option value = 'days' <?php echo $selected['days']; ?>><?php _e( 'days', 'eventpress' ); ?></option>
			<option value = 'weeks' <?php echo $selected['weeks']; ?>><?php _e( 'weeks', 'eventpress' ); ?></option>
			<option value = 'months' <?php echo $selected['months']; ?>><?php _e( 'months', 'eventpress' ); ?></option>
			<option value = 'years' <?php echo $selected['years']; ?>><?php _e( 'years', 'eventpress' ); ?></option>
			<option value = 'hours' <?php echo $selected['hours']; ?>><?php _e( 'hours', 'eventpress' ); ?></option>
		</select> <?php echo _x( 'until', 'continuing from "Repeat this event every x units _until_"', 'eventpress' ) ?>
		<input name = 'ep-schedule[end]' type = 'text' id = 'ep-schedule-end' class = 'datepicker' value = '<?php echo ( empty( $till ) ) ?  '' : date( get_option( 'date_format' ), (int) $till ) . " " . date( get_option( 'time_format' ), (int) $till ); ?>'/>
	<?php }

	/**
	 * Explain politely that all BuddyPress goodness centers around
	 * having BuddyPress Custom Posts enabled and installed.
	 *
	 * @since 0.1
	 */
	function need_bpcp() {
		echo "<div id = 'message' class = 'updated'><p>" . __( "Please install the plugin <a href ='http://wordpress.org/extend/plugins/buddypress-custom-posts/'>BuddyPress Custom Posts</a> for EventPress to become BuddyPress enabled." ) . "</p></div>";
	}
}

//Create an instance of this class
global $ep_views; $ep_views['admin'] = new ep_admin_view();
