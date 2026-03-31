<?php
/**
 * Testimonial Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Testimonial block.
 */
function wbcom_essential_testimonial_block_init() {
	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/testimonial/';

	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_testimonial_block_init' );
