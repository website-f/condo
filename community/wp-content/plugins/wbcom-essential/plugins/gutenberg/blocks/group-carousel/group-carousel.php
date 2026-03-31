<?php
/**
 * Group Carousel Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from block.json.
 */
function wbcom_essential_group_carousel_block_init() {
	// Only register if BuddyPress is active and groups component is enabled.
	if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'groups' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/group-carousel/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_group_carousel_block_init' );

/**
 * Enqueue Swiper assets for Group Carousel block on frontend.
 */
function wbcom_essential_group_carousel_enqueue_assets() {
	if ( ! has_block( 'wbcom-essential/group-carousel' ) ) {
		return;
	}

	// Enqueue Swiper CSS.
	wp_enqueue_style(
		'swiper',
		WBCOM_ESSENTIAL_URL . 'assets/vendor/swiper/swiper-bundle.min.css',
		array(),
		'11.2.10'
	);

	// Enqueue Swiper JS.
	wp_enqueue_script(
		'swiper',
		WBCOM_ESSENTIAL_URL . 'assets/vendor/swiper/swiper-bundle.min.js',
		array(),
		'11.2.10',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'wbcom_essential_group_carousel_enqueue_assets' );

/**
 * Enqueue Swiper assets for Group Carousel block in editor.
 */
function wbcom_essential_group_carousel_enqueue_editor_assets() {
	// Enqueue Swiper CSS for editor preview.
	wp_enqueue_style(
		'swiper',
		WBCOM_ESSENTIAL_URL . 'assets/vendor/swiper/swiper-bundle.min.css',
		array(),
		'11.2.10'
	);

	// Enqueue Swiper JS for editor preview.
	wp_enqueue_script(
		'swiper',
		WBCOM_ESSENTIAL_URL . 'assets/vendor/swiper/swiper-bundle.min.js',
		array(),
		'11.2.10',
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'wbcom_essential_group_carousel_enqueue_editor_assets' );
