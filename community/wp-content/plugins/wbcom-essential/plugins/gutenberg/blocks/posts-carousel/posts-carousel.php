<?php
/**
 * Posts Carousel Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from block.json.
 */
function wbcom_essential_posts_carousel_block_init() {
	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/posts-carousel/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_posts_carousel_block_init' );

/**
 * Enqueue Swiper assets for Posts Carousel block on frontend.
 */
function wbcom_essential_posts_carousel_enqueue_assets() {
	if ( ! has_block( 'wbcom-essential/posts-carousel' ) ) {
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
add_action( 'wp_enqueue_scripts', 'wbcom_essential_posts_carousel_enqueue_assets' );

/**
 * Enqueue Swiper assets for Posts Carousel block in editor.
 */
function wbcom_essential_posts_carousel_enqueue_editor_assets() {
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
add_action( 'enqueue_block_editor_assets', 'wbcom_essential_posts_carousel_enqueue_editor_assets' );

/**
 * Get categories for REST API.
 */
function wbcom_essential_get_post_categories() {
	$categories = get_categories(
		array(
			'hide_empty' => false,
		)
	);

	$options = array();
	foreach ( $categories as $category ) {
		$options[] = array(
			'value' => $category->slug,
			'label' => $category->name,
		);
	}

	return $options;
}

/**
 * Register REST endpoint for categories.
 */
function wbcom_essential_register_posts_carousel_rest() {
	register_rest_route(
		'wbcom-essential/v1',
		'/categories',
		array(
			'methods'             => 'GET',
			'callback'            => 'wbcom_essential_get_post_categories',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'rest_api_init', 'wbcom_essential_register_posts_carousel_rest' );
