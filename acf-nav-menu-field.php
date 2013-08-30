<?php
/*
Plugin Name: Advanced Custom Fields: Nav menu
Plugin URI: {{git_url}}
Description: {{short_description}}
Version: 1.0.0
Author: {{full_name}}
Author URI: {{website}}
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


class acf_field_nav_menu_plugin {
	/*
	*  Construct
	*
	*  @description:
	*  @since: 3.6
	*  @date	28/09/13
	*/

	function __construct() {
		$domain = 'acf-nav-menu-field';
		$mofile = trailingslashit( dirname( __File__) ) . 'lang/' . $domain . '-' . get_locale() . '.mo';
		load_textdomain( $domain, $mofile );

		// version 4+
		add_action( 'acf/register_fields', array( $this, 'register_fields' ) );
	}

	/*
	*  register_fields
	*
	*  @description:
	*  @since: 3.6
	*  @date	28/09/13
	*/

	function register_fields() {
		include_once( plugin_dir_path( __FILE__ ) . 'acf-nav-menu-field-v4.php' );
	}

}

new acf_field_nav_menu_plugin;
