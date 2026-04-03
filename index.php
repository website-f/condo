<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Route the embedded Laravel agent portal before booting WordPress.
 */
$condo_request_uri = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ?: '/';

if ( '/agent' === $condo_request_uri || str_starts_with( $condo_request_uri, '/agent/' ) ) {
    require __DIR__ . '/agent/index.php';
    return;
}

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define( 'WP_USE_THEMES', true );

/** Loads the WordPress Environment and Template */
require __DIR__ . '/wp-blog-header.php';
