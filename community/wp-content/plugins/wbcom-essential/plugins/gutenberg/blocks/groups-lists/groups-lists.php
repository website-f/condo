<?php
/**
 * Groups Lists Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from block.json.
 */
function wbcom_essential_groups_lists_block_init() {
	// Only register if BuddyPress is active and groups component is enabled.
	if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'groups' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/groups-lists/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_groups_lists_block_init' );

/**
 * Register REST API endpoint for group types.
 */
function wbcom_essential_register_group_types_endpoint() {
	register_rest_route(
		'wbcom-essential/v1',
		'/group-types',
		array(
			'methods'             => 'GET',
			'callback'            => 'wbcom_essential_get_group_types',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'rest_api_init', 'wbcom_essential_register_group_types_endpoint' );

/**
 * Get all registered group types.
 *
 * @return WP_REST_Response
 */
function wbcom_essential_get_group_types() {
	$types = array();

	if ( function_exists( 'bp_groups_get_group_types' ) ) {
		$group_types = bp_groups_get_group_types( array(), 'objects' );

		foreach ( $group_types as $group_type ) {
			if ( ! empty( $group_type->name ) ) {
				$types[] = array(
					'value' => $group_type->name,
					'label' => $group_type->labels['singular_name'],
				);
			}
		}
	}

	return rest_ensure_response( $types );
}
