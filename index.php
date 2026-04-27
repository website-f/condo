<?php
/**
 * Front controller. Dispatches between three apps based on host + path:
 *   1. condo.com.my/agent/...        -> Laravel agent portal (admin)
 *   2. *.condo.com.my (subdomains)   -> Laravel public agent site
 *   3. condo.com.my (main / www)     -> WordPress
 *
 * Local mirrors: condo.test follows the same rules.
 */

$condo_request_uri  = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ?: '/';
$condo_request_host = strtolower( preg_replace( '/:\d+$/', '', (string) ( $_SERVER['HTTP_HOST'] ?? '' ) ) );

// 1) Existing Laravel agent portal under /agent
if ( '/agent' === $condo_request_uri || str_starts_with( $condo_request_uri, '/agent/' ) ) {
    require __DIR__ . '/agent/index.php';
    return;
}

// 2) Public agent subdomain -> Laravel
$condo_public_bases = array( 'condo.com.my', 'condo.test' );

$condo_is_agent_subdomain = false;
foreach ( $condo_public_bases as $base ) {
    if ( $condo_request_host === $base || $condo_request_host === 'www.' . $base ) {
        continue;
    }
    if ( str_ends_with( $condo_request_host, '.' . $base ) ) {
        $condo_is_agent_subdomain = true;
        break;
    }
}

if ( $condo_is_agent_subdomain ) {
    define( 'LARAVEL_START', microtime( true ) );

    if ( file_exists( $maintenance = __DIR__ . '/_agent/storage/framework/maintenance.php' ) ) {
        require $maintenance;
    }

    require __DIR__ . '/_agent/vendor/autoload.php';

    /** @var Illuminate\Foundation\Application $app */
    $app = require_once __DIR__ . '/_agent/bootstrap/app.php';

    $app->handleRequest( Illuminate\Http\Request::capture() );
    return;
}

// 3) Default: WordPress
define( 'WP_USE_THEMES', true );
require __DIR__ . '/wp-blog-header.php';
