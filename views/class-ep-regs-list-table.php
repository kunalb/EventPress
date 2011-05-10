<?php

class EP_Regs_List_Table extends WP_List_Table {

	public $regs;
	public $total_items;

	function WP_Posts_List_Table() {
		parent::WP_List_Table();
	}

	function ajax_user_can() {
		/* To be implemented */
	}

	/**
	 * Gets and stores all the registration details.
	 */
	function prepare_items() {
		global $post;

		$this->regs = query_posts( "post_parent={$post->ID}&post_type=ep_reg" );
		$this->total_items = count( $this->regs );
	}

	function has_items() {
		if( $this->total_items > 0 )
			return true;
		return false;
	}

	function no_items() {
		_e( "There are no registrations for this event.", eventpress );
	}

	/**
	 * Shows the possible views.
	 *
	 * To be implemented properly.
	 */
	function get_views() {
		return Array(
			'all' => '<A href = "/" class = "current">All</a>',
			'approved' => '<a href = "approved">Approved</a>',
			'cancelled' => '<a href = "cancelled">Cancelled</a>'
		);
	}

	function get_bulk_actions() {
		$actions = array();

		$actions = Array(
				__( 'Approve', 'eventpress' ),
				__( 'Cancel', 'eventpress' )
		);

		return $actions;
	}
	
	/**
	 * Display the bulk actions dropdown.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function bulk_actions() {
		$screen = get_current_screen();

		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions();
			// This filter can currently only be used to remove actions.
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) )
			return;

		echo "<select name='ep-reg-bulk[action$two]'>\n";
		echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions' ) . "</option>\n";
		foreach ( $this->_actions as $name => $title )
			echo "\t<option value='$name'>$title</option>\n";
		echo "</select>\n";

		submit_button( __( 'Apply' ), 'button-secondary action', false, false, array( 'id' => "ep-bulk-reg-doaction$two" ) );
		echo "\n";
	}

	function extra_tablenav( $which ) {
		global $post_type, $post_type_object, $cat, $post;
?>
		<div class="alignleft actions">
			<a href = '<?php echo EP_REL_URL . '/controllers/csv.php?ep-eventid=' . $post->ID ; ?>' id = 'ep-reg-export' class = 'button secondary-action'><?php _e( 'Export as .csv', 'eventpress' ); ?></a>
		</div>
<?php
	}

	function current_action() {
		return parent::current_action();
	}

	function pagination( $which ) {
		global $post_type_object, $mode;

		parent::pagination( $which );

		if ( 'top' == $which && !$post_type_object->hierarchical )
			$this->view_switcher( $mode );
	}

	function get_table_classes() {
		global $post_type_object;

		return array( 'widefat', 'fixed', $post_type_object->hierarchical ? 'pages' : 'posts' );
	}

	function get_columns() {
		$screen = get_current_screen();

		if ( empty( $screen ) )
			$post_type = 'post';
		else
			$post_type = $screen->post_type;

		return Array(
			'cb' => '<input class = "checkbox cb" type = "checkbox" />',
			'author' => __( 'Registered User', 'eventpress' ),
			'date' => __( 'Time', 'eventpress' ),
			'status' => __( 'Status', 'eventpress' )
		);
	}

	function get_sortable_columns() {
		return array();
	}

	function display_rows( $posts = array() ) {
		$this->_display_rows( $this->regs );
	}

	function _display_rows( $posts ) {
		add_filter( 'the_title', 'esc_html' );

		// Create array of post IDs.
		$post_ids = array();

		foreach ( $this->regs as $a_reg )
			$post_ids[] = $a_reg->ID;

		foreach ( $this->regs as $reg )
			$this->single_row( $reg );
	}

	function single_row( $a_post, $level = 0 ) {
		static $rowclass;

		$global_post = $post;
		$post = $a_post;

		setup_postdata( $post );

		$rowclass = 'alternate' == $rowclass ? '' : 'alternate';
		$post_owner = ( get_current_user_id() == $post->post_author ? 'self' : 'other' );
		$edit_link = get_edit_post_link( $post->ID );
		$title = _draft_or_post_title();
		$post_type_object = get_post_type_object( $post->post_type );
		$can_edit_post = current_user_can( $post_type_object->cap->edit_post, $post->ID );
		$post_format = get_post_format( $post->ID );
		$post_format_class = ( $post_format && !is_wp_error($post_format) ) ? 'format-' . sanitize_html_class( $post_format ) : 'format-default';
	?>
		<tr id='post-<?php echo $post->ID; ?>' class='<?php echo trim( $rowclass . ' author-' . $post_owner . ' status-' . $post->post_status . ' ' . $post_format_class); ?> iedit' valign="top">
	<?php

		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			switch ( $column_name ) {

			case 'cb':
			?>
			<th scope="row" class="check-column"><?php if ( $can_edit_post ) { ?><input type="checkbox" name="ep-reg-bulk[post][]" value="<?php echo $post->ID; ?>" /><?php } ?></th>
			<?php
			break;

			case 'date':
				if ( '0000-00-00 00:00:00' == $post->post_date && 'date' == $column_name ) {
					$t_time = $h_time = __( 'Unpublished' );
					$time_diff = 0;
				} else {
					$t_time = get_the_time( __( 'Y/m/d g:i:s A' ) );
					$m_time = $post->post_date;
					$time = get_post_time( 'G', true, $post );

					$time_diff = time() - $time;

					if ( $time_diff > 0 && $time_diff < 24*60*60 )
						$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
					else
						$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
				}

				echo '<td ' . $attributes . '>';
					echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post, $column_name, $mode ) . '</abbr>';
				echo '</td>';
			break;

			case 'author':
			?>
			<td <?php echo $attributes ?>><?php
				printf( '<a href="%s">%s</a>',
					esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'author' => get_the_author_meta( 'ID' ) ), 'edit.php' )),
					get_the_author()
				);

				$approve_link = EP_REL_URL . '/controllers/post.php?action=approve&eventid=' . $post->ID;  
				$cancel_link =	EP_REL_URL . '/controllers/post.php?action=cancel&eventid=' . $post->ID;  

				$actions = array();
				if ( current_user_can( 'edit_reg', $post->ID) && $post->post_status != 'trash' ) {
					$actions['ep_approve'] = '<a href = "' . $approve_link . '" title = "' . __('Approve this registration.') . '">' . __('Approve') . '</a>';
					$actions['ep_cancel'] = '<a href = "' . $cancel_link . '" title = "'. __('Cancel this registration.') .'">' . __('Cancel') . '</a>';
				}
				$actions = apply_filters('page_row_actions', $actions, $post);

				$action_count = count($actions);

				$i = 0;
				echo '<div class="row-actions">';
				foreach ( $actions as $action => $link ) {
					++$i;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					echo "<span class='$action'>$link$sep</span>";
				}
				echo '</div>';
			?></td>
			<?php
			break;

			default:
			?>
			<td <?php echo $attributes ?>><?php
				if ( is_post_type_hierarchical( $post->post_type ) )
					do_action( 'manage_pages_custom_column', $column_name, $post->ID );
				else
					do_action( 'manage_posts_custom_column', $column_name, $post->ID );
				do_action( "manage_{$post->post_type}_posts_custom_column", $column_name, $post->ID );
			?></td>
			<?php
			break;
		}
	}
	?>
		</tr>
	<?php
		$post = $global_post;
	}

	function display_tablenav( $which ) {
?>
	<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<div class="alignleft actions">
			<?php $this->bulk_actions( $which ); ?>
		</div>
<?php
		$this->extra_tablenav( $which );
?>

		<br class="clear" />
	</div>
<?php
	}
}
