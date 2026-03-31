<?php
/**
 * Site Logo Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from block.json.
 */
function wbcom_essential_site_logo_block_init() {
	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/site-logo/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_site_logo_block_init' );
