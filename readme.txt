=== Advanced Custom Fields: Nav Menu Field ===
Contributors: theorboman
Tags:
Requires at least: 3.5
Tested up to: 3.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add pages or posts (or custom post types) directly to a navigation menu from the edit screen.

== Description ==

Add pages or posts (or custom post types) directly to a navigation menu from the edit screen.

= Compatibility =

This add-on will only work with:

* version 4 and up

== Installation ==

This add-on can be treated as both a WP plugin and a theme include.

= Plugin =
1. Copy the 'acf-nav-menu-field' folder into your plugins folder
2. Activate the plugin via the Plugins admin page

= Include =
1.	Copy the 'acf-nav-menu-field' folder into your theme folder (can use sub folders). You can place the folder anywhere inside the 'wp-content' directory
2.	Edit your functions.php file and add the code below (Make sure the path is correct to include the acf-nav-menu-field.php file)

`
add_action( 'acf/register_fields', 'my_register_fields' );

function my_register_fields() {
	include_once( 'acf-nav-menu-field/acf-nav-menu-field.php' );
}
`

== Changelog ==

= 0.0.1 =
* Initial Release.
