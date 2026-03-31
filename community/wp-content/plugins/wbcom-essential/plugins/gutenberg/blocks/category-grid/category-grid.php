<?php
/**
 * Category Grid Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Category Grid block.
 *
 * @return void
 */
function wbcom_essential_category_grid_block_init() {
	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/category-grid/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_category_grid_block_init' );
