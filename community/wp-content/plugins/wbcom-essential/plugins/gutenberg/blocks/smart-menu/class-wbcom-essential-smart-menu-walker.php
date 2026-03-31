<?php
/**
 * Smart Menu Walker Class.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Walker for Smart Menu block.
 *
 * Extends Walker_Nav_Menu to add dropdown icons for menu items with children.
 *
 * @since 4.0.0
 */
class WBCOM_Essential_Smart_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Icon SVG markup for top-level dropdown items.
	 *
	 * @var string
	 */
	private $icon_svg;

	/**
	 * Icon SVG markup for submenu indicator (nested items).
	 *
	 * @var string
	 */
	private $submenu_icon_svg;

	/**
	 * Constructor.
	 *
	 * @param string $icon_svg        Icon SVG markup for top-level items.
	 * @param string $submenu_icon_svg Icon SVG markup for nested submenu items.
	 */
	public function __construct( $icon_svg = '', $submenu_icon_svg = '' ) {
		$this->icon_svg         = $icon_svg;
		$this->submenu_icon_svg = $submenu_icon_svg ? $submenu_icon_svg : $icon_svg;
	}

	/**
	 * Start element output.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$menu_id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$menu_id = $menu_id ? ' id="' . esc_attr( $menu_id ) . '"' : '';

		$output .= $indent . '<li' . $menu_id . $class_names . '>';

		$atts           = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']   = ! empty( $item->url ) ? $item->url : '';

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$item_output  = $args->before;
		$item_output .= '<a' . $attributes . '>';
		$item_output .= $args->link_before . $title . $args->link_after;

		// Add dropdown icon if item has children.
		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			// Use different icons: dropdown icon for top-level, submenu indicator for nested items.
			$icon         = ( $depth > 0 ) ? $this->submenu_icon_svg : $this->icon_svg;
			$item_output .= '<span class="dropdown-icon">' . $icon . '</span>';
		}

		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}
