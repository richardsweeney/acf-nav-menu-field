<?php

class acf_field_nav_menu extends acf_field
{
	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options


	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/

	function __construct()
	{
		// vars
		$this->name     = 'acf_field_nav_menu';
		$this->label    = __( 'Nav Menu', 'acf' );
		$this->category = __( 'Relational','acf' );
		$this->defaults = array(
			// add default here to merge into your field.
			// This makes life easy when creating the field options as you don't need to use any if( isset('') ) logic. eg:
			//'preview_size' => 'thumbnail'
		);

    	parent::__construct();


    	// settings
		$this->settings = array(
			'path'    => apply_filters( 'acf/helpers/get_path', __FILE__ ),
			'dir'     => apply_filters( 'acf/helpers/get_dir', __FILE__ ),
			'version' => '1.0.0'
		);

	}


	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/

	function create_options( $field ) {

		$value = isset( $field['menu'] ) ? $field['menu'] : '0';

		// key is needed in the field names to correctly save the data
		$key = $field['name'];

		// Get a list of all available navigation menus
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
		$choices = array();

		// Build an array for the select list
		foreach ( $menus as $menu )
			$choices[ $menu->term_id ] = esc_attr( $menu->name ); ?>

		<tr class="field_option field_option_<?php echo $this->name; ?>">

			<td class="label">
				<label><?php _e( 'Select a menu', 'acf' ); ?></label>
				<p class="description"><?php _e( 'Select a menu from the list', 'acf'); ?></p>
			</td>

			<td>
				<?php do_action( 'acf/create_field', array(
					'type'    => 'select',
					'name'    => 'fields[' . $key . '][menu]',
					'value'   => $value,
					'choices' => $choices,
				)) ?>
			</td>

		</tr>

	<?php }


	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function create_field( $field ) {
		include_once( plugin_dir_path( __FILE__ ) . 'acf-nav-menu-custom-walker.php' );

		extract( $field );

		// Not sure if this will ever happen, but just in case!
		if ( ! $menu || empty( $menu ) ) : ?>
			<p><?php _e( 'Please select a menu when adding the field group.', 'acf' ) ?></p>
			<?php return;
		endif ?>

		<input type="hidden" name="acf-menu-id" value="<?php echo $menu ?>">
		<label for="<?php echo $id ?>"><?php _e( 'Select a parent for the menu item', 'acf' ) ?></label>

		<?php wp_nav_menu( array(
			'menu'       => $menu,
			'container'  => 'div',
			'items_wrap' => "<select name='$name' id='$id' class='$class'><option value='0'> -- " . __( 'No parent', 'acf' ) . ' -- </option>' . '%3$s</select>',
			'walker'     => new Acf_Nav_Menu_Custom_Walker( $value ),
		) );

	}


	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded from
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in te database
	*/

	function load_value($value, $post_id, $field)
	{
		// Note: This function can be removed if not used
		return $value;
	}


	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/

	function update_value( $value, $post_id, $field ) {
		if ( 'acf_field_nav_menu' != $field['type'] )
			return $value;

		$menu_id        = (int) $_REQUEST['acf-menu-id']; // The menu to which to add the menu item
		$post_type      = get_post_type( $post_id );
		$nav_menu_items = wp_get_nav_menu_items( $menu_id );
		$title          = get_the_title( $post_id );
		$exists         = false;

		// Check if the item already exists
		foreach ( $nav_menu_items as $nav_item ) {

			// If the title is the same and they have the same parent, we've got a match
		    if ( strtolower( $title ) == strtolower( $nav_item->title ) && $value == $nav_item->menu_item_parent ) {
		        $exists = true;
		        break;
		    }

		}


		if ( ! $exists ) {

		    wp_update_nav_menu_item( $menu_id, 0, array(
		        'menu-item-title'     => $title,
		        'menu-item-url'       => get_permalink( $post_id ),
		        'menu-item-status'    => 'publish',
		        // $value is the parent item in the menu for the page, it can be 0 ( no parent )
		        'menu-item-parent-id' => $value,
		        'menu-item-object-id' => $post_id,
		        'menu-item-object'    => $post_type,
		        'menu-item-type'      => 'post_type',
		    ) );

		}

	    return $value;

	}


	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed to the create_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/

	function format_value($value, $post_id, $field)
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/

		// perhaps use $field['preview_size'] to alter the $value?


		// Note: This function can be removed if not used
		return $value;
	}


	/*
	*  format_value_for_api()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/

	function format_value_for_api($value, $post_id, $field)
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/

		// perhaps use $field['preview_size'] to alter the $value?


		// Note: This function can be removed if not used
		return $value;
	}


	/*
	*  load_field()
	*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/

	function load_field($field)
	{
		// Note: This function can be removed if not used
		return $field;
	}


	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	function update_field($field, $post_id)
	{
		// Note: This function can be removed if not used
		return $field;
	}


}


new acf_field_nav_menu;
