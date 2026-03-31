<?php
/**
 * Groups Grid Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from block.json.
 */
function wbcom_essential_groups_grid_block_init() {
	// Only register if BuddyPress is active and groups component is enabled.
	if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'groups' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/groups-grid/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_groups_grid_block_init' );
