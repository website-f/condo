<?php
/**
 * EDD Product Catalog REST API Endpoints.
 *
 * Registers REST routes that power the Product Catalog block.
 * Only active when Easy Digital Downloads is installed and active.
 *
 * Routes:
 *   GET /wbcom/v1/products
 *   GET /wbcom/v1/product-categories
 *
 * @package WBCOM_Essential
 * @since   4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register REST API routes for the product catalog block.
 *
 * Skips registration entirely when EDD is not active to avoid
 * orphaned endpoints.
 */
function wbcom_essential_product_catalog_register_routes() {
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		return;
	}

	register_rest_route(
		'wbcom/v1',
		'/products',
		array(
			'methods'             => 'GET',
			'callback'            => 'wbcom_essential_product_catalog_get_products',
			'permission_callback' => '__return_true',
			'args'                => array(
				'category'    => array(
					'type'              => 'integer',
					'default'           => 0,
					'sanitize_callback' => 'absint',
				),
				'search'      => array(
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'orderby'     => array(
					'type'    => 'string',
					'default' => 'title',
					'enum'    => array( 'title', 'date', 'price', 'popular' ),
				),
				'order'       => array(
					'type'    => 'string',
					'default' => 'ASC',
					'enum'    => array( 'ASC', 'DESC' ),
				),
				'per_page'    => array(
					'type'              => 'integer',
					'default'           => 12,
					'sanitize_callback' => 'absint',
				),
				'page'        => array(
					'type'              => 'integer',
					'default'           => 1,
					'sanitize_callback' => 'absint',
				),
				'price_range' => array(
					'type'    => 'string',
					'default' => 'all',
					'enum'    => array( 'all', 'free', 'under25', '25to99', '100plus' ),
				),
			),
		)
	);

	register_rest_route(
		'wbcom/v1',
		'/product-categories',
		array(
			'methods'             => 'GET',
			'callback'            => 'wbcom_essential_product_catalog_get_categories',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'wbcom_essential_product_catalog_register_routes' );

/**
 * GET /wbcom/v1/products
 *
 * Returns EDD products with filters for category, search, price, and sort.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
function wbcom_essential_product_catalog_get_products( $request ) {
	$args = array(
		'post_type'      => 'download',
		'post_status'    => 'publish',
		'posts_per_page' => min( $request['per_page'], 48 ),
		'paged'          => $request['page'],
		'order'          => $request['order'],
	);

	// Category filter.
	if ( $request['category'] ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'download_category',
				'field'    => 'term_id',
				'terms'    => $request['category'],
			),
		);
	}

	// Search filter.
	if ( $request['search'] ) {
		$args['s'] = $request['search'];
	}

	// Sort.
	switch ( $request['orderby'] ) {
		case 'price':
			$args['meta_key'] = 'edd_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = 'meta_value_num';
			break;
		case 'popular':
			$args['meta_key'] = '_edd_download_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		default:
			$args['orderby'] = $request['orderby'];
	}

	// Price range filter via meta_query.
	$price_range = $request['price_range'];
	if ( 'all' !== $price_range ) {
		$price_query = array( 'relation' => 'AND' );
		switch ( $price_range ) {
			case 'free':
				$price_query[] = array(
					'key'     => 'edd_price',
					'value'   => 0,
					'compare' => '=',
					'type'    => 'NUMERIC',
				);
				break;
			case 'under25':
				$price_query[] = array(
					'key'     => 'edd_price',
					'value'   => 25,
					'compare' => '<',
					'type'    => 'NUMERIC',
				);
				$price_query[] = array(
					'key'     => 'edd_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC',
				);
				break;
			case '25to99':
				$price_query[] = array(
					'key'     => 'edd_price',
					'value'   => array( 25, 99 ),
					'compare' => 'BETWEEN',
					'type'    => 'NUMERIC',
				);
				break;
			case '100plus':
				$price_query[] = array(
					'key'     => 'edd_price',
					'value'   => 100,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				);
				break;
		}
		if ( isset( $args['meta_query'] ) ) {
			$args['meta_query'][] = $price_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		} else {
			$args['meta_query'] = array( $price_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}
	}

	$query    = new WP_Query( $args );
	$products = array();

	foreach ( $query->posts as $post ) {
		$products[] = wbcom_essential_product_catalog_format_product( $post );
	}

	return new WP_REST_Response(
		array(
			'products'    => $products,
			'total'       => (int) $query->found_posts,
			'total_pages' => (int) $query->max_num_pages,
			'page'        => (int) $request['page'],
		),
		200
	);
}

/**
 * Format a single download post for the API response.
 *
 * @param WP_Post $post Download post object.
 * @return array
 */
function wbcom_essential_product_catalog_format_product( $post ) {
	$price   = '';
	$is_free = false;

	if ( function_exists( 'edd_has_variable_prices' ) && edd_has_variable_prices( $post->ID ) ) {
		$prices  = edd_get_variable_prices( $post->ID );
		$amounts = wp_list_pluck( $prices, 'amount' );
		$min     = min( $amounts );
		$max     = max( $amounts );
		$price   = html_entity_decode( edd_currency_filter( edd_format_amount( $min ) ) . ' – ' . edd_currency_filter( edd_format_amount( $max ) ) );
	} elseif ( function_exists( 'edd_get_download_price' ) ) {
		$amount = edd_get_download_price( $post->ID );
		if ( (float) $amount > 0 ) {
			$price = html_entity_decode( edd_currency_filter( edd_format_amount( $amount ) ) );
		} else {
			$price   = 'Free';
			$is_free = true;
		}
	}

	$excerpt = $post->post_excerpt;
	if ( empty( $excerpt ) ) {
		$excerpt = wp_trim_words( wp_strip_all_tags( $post->post_content ), 20, '...' );
	} else {
		$excerpt = wp_trim_words( $excerpt, 20, '...' );
	}

	return array(
		'id'      => $post->ID,
		'title'   => $post->post_title,
		'excerpt' => $excerpt,
		'url'     => get_permalink( $post->ID ),
		'image'   => get_the_post_thumbnail_url( $post->ID, 'large' ),
		'price'   => $price,
		'is_free' => $is_free,
	);
}

/**
 * GET /wbcom/v1/product-categories
 *
 * Returns top-level EDD download categories with counts.
 *
 * @return array
 */
function wbcom_essential_product_catalog_get_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'download_category',
			'hide_empty' => true,
			'parent'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$categories = array();
	foreach ( $terms as $term ) {
		$categories[] = array(
			'id'    => $term->term_id,
			'name'  => $term->name,
			'slug'  => $term->slug,
			'count' => $term->count,
		);
	}

	return $categories;
}
