<?php
/**
 * WordPress configuration — works on both local (condo.test) and prod
 * (condo.com.my / ppp.my). DB credentials and IPP auth bridge creds are
 * selected automatically based on the request hostname.
 */

$condo_request_host = strtolower( preg_replace( '/:\d+$/', '', (string) ( $_SERVER['HTTP_HOST'] ?? '' ) ) );
$condo_local_domain = 'condo.test';
$condo_primary_domain = getenv( 'CONDO_MULTISITE_PRIMARY_DOMAIN' );
$condo_primary_domain = is_string( $condo_primary_domain ) && $condo_primary_domain !== '' ? $condo_primary_domain : 'condo.com.my';
$condo_primary_domain = preg_replace( '#^https?://#i', '', trim( $condo_primary_domain ) );
$condo_primary_domain = strtolower( trim( (string) explode( '/', (string) $condo_primary_domain, 2 )[0], '. ' ) );
$condo_primary_domain = strtolower( trim( (string) explode( ':', (string) $condo_primary_domain, 2 )[0], '. ' ) );

$condo_is_local_host = $condo_request_host === $condo_local_domain
    || $condo_request_host === 'www.' . $condo_local_domain
    || ( $condo_request_host !== '' && str_ends_with( $condo_request_host, '.' . $condo_local_domain ) );

// CLI / cron has no HTTP_HOST. Detect prod vs local from a server-side env var
// or a fallback file on the prod server. Default = prod (safer for cron).
if ( $condo_request_host === '' ) {
    $cli_env = getenv( 'CONDO_ENV' );
    $condo_is_local_host = $cli_env === 'local';
}

$condo_multisite_domain = $condo_is_local_host ? $condo_local_domain : $condo_primary_domain;

if ( ! defined( 'WP_CACHE' ) ) {
    define( 'WP_CACHE', ! $condo_is_local_host ); // Keep WP Rocket off local .test hosts.
}

if ( $condo_is_local_host ) {
    define( 'WP_HOME', 'https://' . $condo_request_host );
    define( 'WP_SITEURL', 'https://' . $condo_request_host );
}

if ( ! defined( 'CONDO_MULTISITE_PRIMARY_DOMAIN' ) ) {
    define( 'CONDO_MULTISITE_PRIMARY_DOMAIN', $condo_primary_domain );
}

if ( ! defined( 'CONDO_LOCAL_MULTISITE_DOMAIN' ) ) {
    define( 'CONDO_LOCAL_MULTISITE_DOMAIN', $condo_local_domain );
}

// ---------------------------------------------------------------------------
// Database settings — switch by environment
// ---------------------------------------------------------------------------
if ( $condo_is_local_host ) {
    define( 'DB_NAME',     'wp_condo' );
    define( 'DB_USER',     'root' );
    define( 'DB_PASSWORD', 'root' );
    define( 'DB_HOST',     'localhost' );

    // IPP centralized auth credentials (for mu-plugins/condo-ipp-auth.php)
    define( 'CONDO_IPP_AUTH_DB_HOST',     'localhost' );
    define( 'CONDO_IPP_AUTH_DB_NAME',     'ipp_user' );
    define( 'CONDO_IPP_AUTH_DB_USER',     'root' );
    define( 'CONDO_IPP_AUTH_DB_PASSWORD', 'root' );
} else {
    define( 'DB_NAME',     'property_condo1' );
    define( 'DB_USER',     'property_condo1' );
    define( 'DB_PASSWORD', 'hjRh2yW7WffAGTbcaF2L' );
    define( 'DB_HOST',     'localhost' );

    // Prod IPP user (the same DB Laravel reads from)
    define( 'CONDO_IPP_AUTH_DB_HOST',     'localhost' );
    define( 'CONDO_IPP_AUTH_DB_NAME',     'ipp_user' );
    define( 'CONDO_IPP_AUTH_DB_USER',     'ipp_user' );
    define( 'CONDO_IPP_AUTH_DB_PASSWORD', 'k+ic3U5"' );
}

define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// ---------------------------------------------------------------------------
// HTTPS / proxy detection
// ---------------------------------------------------------------------------
define( 'FORCE_SSL_ADMIN', true );

if (
    ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) ||
    ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' )
) {
    $_SERVER['HTTPS'] = 'on';
}

// ---------------------------------------------------------------------------
// Authentication keys and salts — keep identical between local + prod so
// users do not get logged out when DB is copied between environments.
// ---------------------------------------------------------------------------
define( 'AUTH_KEY',         'ebHPj<ZW;)Ne>rwD8;tl,8_MH#V>&oR8fqmG6%7^E4<;bw3.eW&}>Ohm$3md+xZ5' );
define( 'SECURE_AUTH_KEY',  'B@<=QXfv>1A-M>}`J.^TM|fm|cX+5p27|.Gd:Byu~a6GNmZp_r%c2,l/Y!5?`U=9' );
define( 'LOGGED_IN_KEY',    '8FK>H ZoU*tpO(1eF{_;1psMc_ANygGw.Ze:>!NASJeo;WWW!!IW=ad<ON}C o3T' );
define( 'NONCE_KEY',        '~1tec/yu*vD0cT0au`}D]v3aVm<:<BiW2mHu!GBl{iX<cyP5M]Qi>tzF{VcGX-Dj' );
define( 'AUTH_SALT',        'zEJvCso|2aPVm!etMEygrDZyg{MD&e{`f]Ct@}jcjwwNKN9z3<c$<);6FZq2LX@q' );
define( 'SECURE_AUTH_SALT', 'FTvu{P%II+LQiPZE1NXCApkHL<rgp<f}jC5j0#=4I1N]u.KkXsG`,x!P<(n{j)G.' );
define( 'LOGGED_IN_SALT',   'W0J_?A#laN^zTa.kYh@+B]<99b $j*iXT#M6zAq?Y5xnm)#yUr,x=?!0Zs}U%1q0' );
define( 'NONCE_SALT',       'wLk|g;kj#qF(wUr|hUy&a1*MQd..HU#L{I%1TO~|Bu?F!-ewH6d^@C<ALlxmfkxu' );

// ---------------------------------------------------------------------------
// Table prefix
// ---------------------------------------------------------------------------
$table_prefix = 'cd_';

// ---------------------------------------------------------------------------
// Debug
// ---------------------------------------------------------------------------
define( 'WP_DEBUG', $condo_is_local_host );
define( 'WP_DEBUG_LOG', $condo_is_local_host );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', '0' );

// ---------------------------------------------------------------------------
// Multisite (subdomain network)
// ---------------------------------------------------------------------------
define( 'WP_ALLOW_MULTISITE', true );
define( 'SUNRISE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', true );
define( 'DOMAIN_CURRENT_SITE', $condo_multisite_domain );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );

/* That's all, stop editing! Happy publishing. */

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
