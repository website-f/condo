<?php
if (!defined('WYDEGRID_VERSION')) {
    // Replace the version number of the theme on each release.
    define('WYDEGRID_VERSION', wp_get_theme()->get('Version'));
}
define('WYDEGRID_DEBUG', defined('WP_DEBUG') && WP_DEBUG === true);
define('WYDEGRID_DIR', trailingslashit(get_template_directory()));
define('WYDEGRID_URL', trailingslashit(get_template_directory_uri()));

if (!function_exists('wydegrid_support')) :

    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * @since walker_fse 1.0.0
     *
     * @return void
     */
    function wydegrid_support()
    {
        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');
        // Add support for block styles.
        add_theme_support('wp-block-styles');
        add_theme_support('post-thumbnails');
        // Enqueue editor styles.
        add_editor_style('style.css');
        // Removing default patterns.
        remove_theme_support('core-block-patterns');
    }

endif;
add_action('after_setup_theme', 'wydegrid_support');
/**
 * Filter whether we need to check for URL mismatch or not.
 */
add_filter( 'rank_math/registration/do_url_check', '__return_false' );
/*----------------------------------------------------------------------------------
Enqueue Styles
-----------------------------------------------------------------------------------*/
if (!function_exists('wydegrid_styles')) :
    function wydegrid_styles()
    {
        // registering style for theme
        wp_enqueue_style('wydegrid-style', get_stylesheet_uri(), array(), WYDEGRID_VERSION);
        if (is_rtl()) {
            wp_enqueue_style('wydegrid-rtl-css', get_template_directory_uri() . '/assets/css/rtl.css', 'rtl_css');
        }
        // registering js for theme
        wp_enqueue_script('jquery');
    }
endif;

add_action('wp_enqueue_scripts', 'wydegrid_styles');

/**
 * Enqueue scripts for admin area
 */
function wydegrid_admin_style()
{
    if (!is_user_logged_in()) {
        return;
    }
    $hello_notice_current_screen = get_current_screen();
    if (!empty($_GET['page']) && 'about-wydegrid' === $_GET['page'] || $hello_notice_current_screen->id === 'themes' || $hello_notice_current_screen->id === 'dashboard') {
        wp_enqueue_style('wydegrid-admin-style', get_template_directory_uri() . '/assets/css/admin-style.css', array(), WYDEGRID_VERSION, 'all');
        wp_enqueue_script('wydegrid-admin-scripts', get_template_directory_uri() . '/assets/js/wydegrid-admin-scripts.js', array(), WYDEGRID_VERSION, true);
        wp_localize_script('wydegrid-admin-scripts', 'wydegrid_localize', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wydegrid_nonce'),
            'redirect_url' => admin_url('themes.php?page=templategalaxy')
        ));
    }
}
add_action('admin_enqueue_scripts', 'wydegrid_admin_style');

/**
 * Enqueue assets scripts for both backend and frontend
 */
function wydegrid_block_assets()
{
    wp_enqueue_style('wydegrid-blocks-style', get_template_directory_uri() . '/assets/css/blocks.css');
}
add_action('enqueue_block_assets', 'wydegrid_block_assets');

/**
 * Load core file.
 */
require_once get_template_directory() . '/inc/core/init.php';

/**
 * Load welcome page file.
 */
require_once get_template_directory() . '/inc/admin/welcome-notice.php';

if (!function_exists('wydegrid_excerpt_more_postfix')) {
    function wydegrid_excerpt_more_postfix($more)
    {
        if (is_admin()) {
            return $more;
        }
        return '...';
    }
    add_filter('excerpt_more', 'wydegrid_excerpt_more_postfix');
}
function wydegrid_add_woocommerce_support()
{
    add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'wydegrid_add_woocommerce_support');
