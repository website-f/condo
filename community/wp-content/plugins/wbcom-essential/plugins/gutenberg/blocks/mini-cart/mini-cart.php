<?php
/**
 * Mini Cart Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from block.json.
 */
function wbcom_essential_mini_cart_block_init() {
	// Only register if WooCommerce is active.
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/mini-cart/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_mini_cart_block_init' );

/**
 * Add mini cart fragments for AJAX updates.
 *
 * @param array $fragments Cart fragments.
 * @return array Modified fragments.
 */
function wbcom_essential_mini_cart_fragments( $fragments ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $fragments;
	}

	$cart_count = WC()->cart->get_cart_contents_count();

	$fragments['.wbcom-essential-mini-cart__count'] = $cart_count;

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'wbcom_essential_mini_cart_fragments' );
