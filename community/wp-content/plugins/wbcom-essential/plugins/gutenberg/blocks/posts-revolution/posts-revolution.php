<?php
/**
 * Posts Revolution Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Posts Revolution block.
 */
function wbcom_essential_posts_revolution_block_init() {
	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/posts-revolution/';

	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_posts_revolution_block_init' );
