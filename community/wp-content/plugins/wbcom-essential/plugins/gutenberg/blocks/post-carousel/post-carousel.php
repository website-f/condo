<?php
/**
 * Post Carousel Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from block.json.
 */
function wbcom_essential_post_carousel_block_init() {
	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/post-carousel/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_post_carousel_block_init' );

/**
 * Enqueue Swiper assets for Post Carousel block on frontend.
 */
function wbcom_essential_post_carousel_enqueue_assets() {
	if ( ! has_block( 'wbcom-essential/post-carousel' ) ) {
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
add_action( 'wp_enqueue_scripts', 'wbcom_essential_post_carousel_enqueue_assets' );

/**
 * Enqueue Swiper assets for Post Carousel block in editor.
 */
function wbcom_essential_post_carousel_enqueue_editor_assets() {
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
add_action( 'enqueue_block_editor_assets', 'wbcom_essential_post_carousel_enqueue_editor_assets' );
