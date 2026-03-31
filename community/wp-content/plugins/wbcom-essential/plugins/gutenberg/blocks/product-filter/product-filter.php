<?php
/**
 * Product Filter Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Product Filter block.
 *
 * Only registers when Easy Digital Downloads is active.
 */
function wbcom_essential_product_filter_block_init() {
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		return;
	}
	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/product-filter/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_product_filter_block_init' );
