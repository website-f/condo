<?php
/**
 * Forums Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Forums block.
 */
function wbcom_essential_forums_block_init() {
	// Only register if bbPress is active.
	if ( ! class_exists( 'bbPress' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/forums/';

	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_forums_block_init' );
