<?php
/**
 * Members Lists Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from block.json.
 */
function wbcom_essential_members_lists_block_init() {
	// Only register if BuddyPress is active.
	if ( ! function_exists( 'buddypress' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/members-lists/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_members_lists_block_init' );

/**
 * REST API endpoint to get member types.
 */
function wbcom_essential_register_member_types_endpoint() {
	register_rest_route(
		'wbcom-essential/v1',
		'/member-types',
		array(
			'methods'             => 'GET',
			'callback'            => 'wbcom_essential_get_member_types',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'rest_api_init', 'wbcom_essential_register_member_types_endpoint' );

/**
 * Get member types for the block editor.
 *
 * @return WP_REST_Response
 */
function wbcom_essential_get_member_types() {
	$member_types = array();

	if ( function_exists( 'bp_get_member_types' ) ) {
		$types = bp_get_member_types( array(), 'objects' );
		foreach ( $types as $type ) {
			if ( ! empty( $type->name ) ) {
				$member_types[] = array(
					'value' => $type->name,
					'label' => $type->labels['singular_name'],
				);
			}
		}
	}

	return rest_ensure_response( $member_types );
}
