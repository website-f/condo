<?php
/**
 * Multisite bootstrap.
 *   - Production hosts (condo.com.my, www.condo.com.my): default behavior.
 *   - Staging host (ppp.my, www.ppp.my): map to the prod blog row but rewrite
 *     all output URLs to the staging host so links stay on ppp.my.
 *   - Wildcard agent subdomains (sonny.condo.com.my, sonny.ppp.my): handled
 *     by Laravel via the host-aware dispatcher in /index.php — sunrise should
 *     never see them.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'condo_sunrise_normalize_host' ) ) {
	function condo_sunrise_normalize_host( $host ) {
		$host = preg_replace( '#^https?://#i', '', trim( (string) $host ) );
		$host = strtolower( trim( (string) explode( '/', (string) $host, 2 )[0] ) );
		return trim( preg_replace( '/:\d+$/', '', $host ), '. ' );
	}
}

if ( ! function_exists( 'condo_sunrise_is_https' ) ) {
	function condo_sunrise_is_https() {
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
			return 'https' === strtolower( (string) $_SERVER['HTTP_X_FORWARDED_PROTO'] );
		}
		if ( ! empty( $_SERVER['HTTPS'] ) ) {
			return 'off' !== strtolower( (string) $_SERVER['HTTPS'] );
		}
		return ! empty( $_SERVER['SERVER_PORT'] ) && '443' === (string) $_SERVER['SERVER_PORT'];
	}
}

$condo_request_host  = condo_sunrise_normalize_host( $_SERVER['HTTP_HOST'] ?? '' );
$condo_primary       = condo_sunrise_normalize_host( defined( 'CONDO_MULTISITE_PRIMARY_DOMAIN' ) ? CONDO_MULTISITE_PRIMARY_DOMAIN : 'condo.com.my' );
$condo_staging       = condo_sunrise_normalize_host( defined( 'CONDO_MULTISITE_STAGING_DOMAIN' ) ? CONDO_MULTISITE_STAGING_DOMAIN : 'ppp.my' );

if ( $condo_request_host === '' ) {
	return;
}

// Only intercept staging root + www. Subdomains of ppp.my are Laravel's job.
$is_staging_root = ( $condo_request_host === $condo_staging || $condo_request_host === 'www.' . $condo_staging );

if ( ! $is_staging_root ) {
	return;
}

// Staging maps to the prod blog row in cd_blogs.
$lookup_host = $condo_primary;

global $current_blog, $current_site, $blog_id, $site_id, $public, $wpdb;

$network_id          = defined( 'SITE_ID_CURRENT_SITE' ) ? (int) SITE_ID_CURRENT_SITE : 1;
$main_blog_id        = defined( 'BLOG_ID_CURRENT_SITE' ) ? (int) BLOG_ID_CURRENT_SITE : 1;
$resolved_blog_id    = $main_blog_id;
$resolved_blog_path  = '/';

if ( isset( $wpdb ) && ! empty( $wpdb->blogs ) ) {
	$blog_row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT blog_id, domain, path FROM {$wpdb->blogs} WHERE domain = %s LIMIT 1",
			$lookup_host
		)
	);
	if ( $blog_row ) {
		$resolved_blog_id   = (int) $blog_row->blog_id;
		$resolved_blog_path = ! empty( $blog_row->path ) ? (string) $blog_row->path : '/';
	}
}

$scheme           = condo_sunrise_is_https() ? 'https://' : 'http://';
$request_origin   = $scheme . $condo_request_host;
$canonical_origin = $scheme . $condo_primary;

$GLOBALS['condo_sunrise_request_origin']   = $request_origin;
$GLOBALS['condo_sunrise_canonical_origin'] = $canonical_origin;

$current_site = (object) array(
	'id'        => $network_id,
	'domain'    => $condo_staging,
	'path'      => '/',
	'blog_id'   => $main_blog_id,
	'site_name' => 'Malaysia Property Condo (Staging)',
);

$current_blog = (object) array(
	'blog_id'      => $resolved_blog_id,
	'domain'       => $condo_request_host,
	'path'         => $resolved_blog_path,
	'site_id'      => $network_id,
	'registered'   => '0000-00-00 00:00:00',
	'last_updated' => '0000-00-00 00:00:00',
	'public'       => '1',
	'archived'     => '0',
	'mature'       => '0',
	'spam'         => '0',
	'deleted'      => '0',
	'lang_id'      => '0',
);

$blog_id = $resolved_blog_id;
$site_id = $network_id;
$public  = 1;

if ( ! function_exists( 'condo_sunrise_rewrite_url' ) ) {
	function condo_sunrise_rewrite_url( $value ) {
		if ( ! is_string( $value ) || $value === '' ) return $value;
		$req = $GLOBALS['condo_sunrise_request_origin']   ?? '';
		$can = $GLOBALS['condo_sunrise_canonical_origin'] ?? '';
		if ( $req === '' || $can === '' ) return $value;
		$can_http = preg_replace( '#^https://#', 'http://', $can );
		$req_http = preg_replace( '#^https://#', 'http://', $req );
		return str_replace( array( $can, $can_http ), array( $req, $req_http ), $value );
	}
}

add_filter( 'pre_option_home',    function () { return $GLOBALS['condo_sunrise_request_origin']; } );
add_filter( 'pre_option_siteurl', function () { return $GLOBALS['condo_sunrise_request_origin']; } );

foreach ( array(
	'option_home', 'option_siteurl', 'home_url', 'site_url',
	'content_url', 'plugins_url',
	'stylesheet_directory_uri', 'template_directory_uri', 'theme_file_uri',
	'network_home_url', 'network_site_url',
	'script_loader_src', 'style_loader_src', 'wp_redirect',
) as $f ) {
	add_filter( $f, 'condo_sunrise_rewrite_url' );
}

add_filter( 'allowed_redirect_hosts', function ( $hosts ) use ( $condo_request_host, $condo_primary, $condo_staging ) {
	$hosts[] = $condo_request_host;
	$hosts[] = $condo_primary;
	$hosts[] = 'www.' . $condo_primary;
	$hosts[] = $condo_staging;
	$hosts[] = 'www.' . $condo_staging;
	return array_values( array_unique( array_filter( $hosts ) ) );
} );

add_filter( 'upload_dir', function ( $uploads ) {
	if ( isset( $uploads['baseurl'] ) ) $uploads['baseurl'] = condo_sunrise_rewrite_url( $uploads['baseurl'] );
	if ( isset( $uploads['url'] ) )     $uploads['url']     = condo_sunrise_rewrite_url( $uploads['url'] );
	return $uploads;
} );

add_action( 'template_redirect', function () {
	if (
		is_admin() ||
		wp_doing_ajax() ||
		( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) ||
		is_feed() || is_embed() || is_robots() || is_trackback()
	) return;
	ob_start( 'condo_sunrise_rewrite_url' );
}, 0 );
