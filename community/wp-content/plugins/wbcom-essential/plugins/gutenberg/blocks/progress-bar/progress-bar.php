<?php
/**
 * Progress Bar Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Progress Bar block.
 */
function wbcom_essential_progress_bar_block_init() {
	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/progress-bar/';

	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_progress_bar_block_init' );
