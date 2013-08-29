<?php
/**
 * Convert the Nav Menu into a Dropdown
 */

class Acf_Nav_Menu_Custom_Walker extends Walker_Nav_Menu {

    private $selected;

    function __construct( $selected ) {
        $this->selected = (int) $selected;
    }

    function start_lvl( &$output, $depth = 0, $args = array() ) {
    }

    function end_lvl( &$output, $depth = 0, $args = array() ) {
    }

    function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
        $indent = str_repeat( "-", $depth ) . ' ';

        $output .= sprintf( '<option %s value="%d">%s%s', selected( $this->selected, $object->ID, false ), $object->ID, $indent, $object->title );
    }

    function end_el( &$output, $object, $depth = 0, $args = array() ) {
        $output .= "</option>\n";
    }

}
