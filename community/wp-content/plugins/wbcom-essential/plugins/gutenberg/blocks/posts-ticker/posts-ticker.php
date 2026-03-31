<?php
/**
 * Posts Ticker Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Posts Ticker block.
 */
function wbcom_essential_posts_ticker_block_init() {
	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/posts-ticker/';

	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_posts_ticker_block_init' );
