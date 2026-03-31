<?php
/**
 * EDD Order Success Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the EDD Order Success block.
 *
 * Only registers when Easy Digital Downloads is active.
 */
function wbcom_essential_edd_order_success_block_init() {
	// Only register if Easy Digital Downloads is active.
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/edd-order-success/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_edd_order_success_block_init' );
