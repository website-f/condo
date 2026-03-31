<?php
/*
Plugin Name: Multisite Force HTTPS
Description: Automatically sets all existing and future multisite subdomains to HTTPS
Version: 1.0
Author: Your Name
*/

// Force HTTPS for admin and login
if (!defined('FORCE_SSL_ADMIN')) {
    define('FORCE_SSL_ADMIN', true);
}

// Detect HTTPS behind proxy
if (
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
) {
    $_SERVER['HTTPS'] = 'on';
}

// Update new subsite URLs automatically
add_filter('wpmu_new_blog_data', function($data) {
    if (isset($data['domain'])) {
        $data['domain'] = str_replace('http://', 'https://', $data['domain']);
    }
    return $data;
});

// Update existing subsites to HTTPS
add_action('network_admin_menu', function() {
    global $wpdb;

    $blogs = $wpdb->get_results("SELECT blog_id, domain FROM {$wpdb->blogs}");
    foreach ($blogs as $blog) {
        switch_to_blog($blog->blog_id);
        $home = get_option('home');
        $siteurl = get_option('siteurl');

        $updated = false;
        if (strpos($home, 'http://') === 0) {
            $home = preg_replace('/^http:/i', 'https:', $home);
            update_option('home', $home);
            $updated = true;
        }
        if (strpos($siteurl, 'http://') === 0) {
            $siteurl = preg_replace('/^http:/i', 'https:', $siteurl);
            update_option('siteurl', $siteurl);
            $updated = true;
        }

        if ($updated) {
            error_log("Updated blog_id {$blog->blog_id} to HTTPS");
        }

        restore_current_blog();
    }
});
