<?php
/**
 * Forums Activity Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Forums Activity block.
 */
function wbcom_essential_forums_activity_block_init() {
	// Only register if bbPress and BuddyPress are active.
	if ( ! class_exists( 'bbPress' ) || ! function_exists( 'buddypress' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/forums-activity/';

	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_forums_activity_block_init' );
