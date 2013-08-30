<?php

class acf_field_nav_menu extends acf_field {

	public $settings, $defaults, $name, $label, $category;

	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	28/09/13
	*/

	function __construct() {

		$this->name     = 'acf_field_nav_menu';
		$this->label    = __( 'Nav Menu', 'acf-nav-menu-field' );
		$this->category = __( 'Relational','acf-nav-menu-field' );
		$this->defaults = array();

    	parent::__construct();

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
	*  @date	28/09/13
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
				<label><?php _e( 'Select a menu', 'acf-nav-menu-field' ); ?></label>
				<p class="description"><?php _e( 'Select a menu from the list', 'acf-nav-menu-field'); ?></p>
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
	*  @date	28/09/13
	*/

	function create_field( $field ) {
		global $wpdb, $post;

		extract( $field );

		$select_name   = $name . '[select]';
		$checkbox_name = $name . '[checkbox]';
		$menu_name     = $name . '[menu_id]';
		$cb            = isset( $value['checkbox'] ) ? $value['checkbox'] : 0;

		// Not sure if this will ever happen, but just in case!
		if ( ! $menu || empty( $menu ) ) : ?>
			<p><?php _e( 'Please select a menu when adding the field group.', 'acf-nav-menu-field' ) ?></p>
			<?php return;

		endif;

		// Get the parent of the current post_id's menu link from the DB. This
		// may be different from the post meta if the user may have updated the
		// menu item via the admin UI.

		// This value becomes the selected value for the select lists, so it'll
		// update itself when the user saves the post.
		$sql          = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_menu_item_object_id' AND meta_value='%d'";
		$menu_item_id = $wpdb->get_var( $wpdb->prepare( $sql, $post->ID ) );
		$parent_id    = get_post_meta( $menu_item_id, '_menu_item_menu_item_parent', true ); ?>

		<p>
			<input type="checkbox" name="<?php echo esc_attr( $checkbox_name ) ?>" id="acf-show-in-menu" value="1" <?php checked( $cb, 1 ) ?>>
			<label for="acf-show-in-menu"><?php _e( 'Add this page to the menu', 'acf-nav-menu-field' ) ?> *</label>
		</p>

		<input type="hidden" name="<?php echo esc_attr( $menu_name ) ?>" value="<?php echo esc_attr( $menu ) ?>">
		<label for="<?php echo esc_attr( $id ) ?>"><?php _e( 'Select a parent for the menu item', 'acf-nav-menu-field' ) ?></label>

		<?php $menu_items = wp_get_nav_menu_items( $menu ); // Check if the meny is empty

		if ( empty( $menu_items ) ) :
			echo "<select name='$select_name' id='$id' class='$class'><option value='0'> -- " . __( 'No parent', 'acf-nav-menu-field' ) . ' -- </option></select>';

		else :
			include_once( plugin_dir_path( __FILE__ ) . 'acf-nav-menu-custom-walker.php' );

			wp_nav_menu( array(
				'menu'       => $menu,
				'container'  => 'div',
				'items_wrap' => "<select name='$select_name' id='$id' class='$class'><option value='0'> -- " . __( 'No parent', 'acf-nav-menu-field' ) . ' -- </option>' . '%3$s</select>',
				'walker'     => new Acf_Nav_Menu_Custom_Walker( $parent_id ),
			) );

		endif ?>

		<p><br><em>* <?php printf( _( 'To delete an item from the menu please use the %smenu admin page%s', 'acf-nav-menu-field' ), '<a href="' . admin_url( 'nav-menus.php') . '">', '</a>' ) ?>.</em></p>

	<?php }


	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	28/09/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/

	function update_value( $value, $post_id, $field ) {
		// Not our field
		if ( 'acf_field_nav_menu' != $field['type'] )
			return $value;

		// Don't add the page to the menu
		if ( ! isset( $value['checkbox'] ) || '1' != $value['checkbox'] )
			return $value;

		$post_type = get_post_type( $post_id );

		if ( 'revision' == $post_type )
			return $value;


		$menu_id         = (int) $value['menu_id']; // The menu to which to add the menu item
		$nav_menu_items  = wp_get_nav_menu_items( $menu_id );
		$title           = get_the_title( $post_id );
		$exists          = false;
		$select_value    = ( $value['select'] ) ? $value['select'] : 0;
		$menu_item_db_id = 0;

		// Check if the item already exists in the menu
		foreach ( $nav_menu_items as $nav_item ) {

			// If the title is the same we've got a match
		    if ( strtolower( $title ) == strtolower( $nav_item->title ) ) {

		    	// If they have the same parent, bail
		    	if ( $select_value == $nav_item->menu_item_parent ) {
		    		return $value;

		    	// New parent: we need to update the menu item & we can exit the loop
		    	} else {
		    		$menu_item_db_id = $nav_item->ID;
		    		break;

		    	}

		    }

		}

		// If we've got this far we need to create / update the nav menu item
	    wp_update_nav_menu_item( $menu_id, $menu_item_db_id, array(
	        'menu-item-title'     => $title,
	        'menu-item-url'       => get_permalink( $post_id ),
	        'menu-item-status'    => 'publish',
	        // $value is the parent item in the menu for the page, it can be 0 ( no parent )
	        'menu-item-parent-id' => $select_value,
	        'menu-item-object-id' => $post_id,
	        'menu-item-object'    => $post_type,
	        'menu-item-type'      => 'post_type',
		) );

	    return $value;
	}

}


new acf_field_nav_menu;
