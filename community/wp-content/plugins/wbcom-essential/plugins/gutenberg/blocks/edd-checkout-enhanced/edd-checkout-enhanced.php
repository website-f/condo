<?php
/**
 * EDD Enhanced Checkout Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the EDD Enhanced Checkout block.
 *
 * Only registers when Easy Digital Downloads is active.
 */
function wbcom_essential_edd_checkout_enhanced_block_init() {
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/edd-checkout-enhanced/';

	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_edd_checkout_enhanced_block_init' );
