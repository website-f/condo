<?php

/**
 * file for holding dashboard welcome page for theme
 */
if (!function_exists('wydegrid_welcome_notice')) :
    function wydegrid_welcome_notice()
    {
        if (get_option('wydegrid_dashboard_dismissed_notice')) {
            return;
        }
        global $pagenow;
        $current_screen  = get_current_screen();

        if (is_admin()) {
            if ($current_screen->id !== 'dashboard' && $current_screen->id !== 'themes') {
                return;
            }
            if (is_network_admin()) {
                return;
            }
            if (!current_user_can('manage_options')) {
                return;
            }


?>
            <div class="wydegrid-admin-notice notice notice-info is-dismissible content-install-plugin theme-info-notice" id="wydegrid-dismiss-notice">
                <div class="info-content">
                    <h3><span class="theme-name"><span><?php echo __('Thank you for using WYDEGRID. In order to complete the task correctly, kindly install and activate the recommended plugin.', 'wydegrid'); ?></span></h3>
                    <p class="notice-text"><?php echo __('1. TemplateGalaxy: Please install and activate TemplateGalaxy pluign from our website to use additional patterns, templates  and import demo with "one click demo import" feature.', 'wydegrid') ?></p>
                    <p class="notice-text"><?php echo __('2. Advanced Import: This is required only for the one-click demo import features and can be deleted once the demo is imported.', 'wydegrid') ?></p>
                    <a href="#" id="install-activate-button" class="button admin-button info-button"><?php echo __('Getting started with a single click', 'wydegrid'); ?></a>
                    <a href="<?php echo admin_url(); ?>themes.php?page=about-wydegrid" class="button admin-button info-button"><?php echo __('Explore WYDEGRID', 'wydegrid'); ?></a>
                </div>
                <div class="wydegrid-theme-screen">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/inc/admin/images/dashboard_screen.png'); ?>" />
                </div>


            </div>
    <?php
        }
    }
endif;
add_action('admin_notices', 'wydegrid_welcome_notice');
function wydegrid_dashboard_dismissble_notice()
{
    update_option('wydegrid_dashboard_dismissed_notice', 1);
}
add_action('wp_ajax_wydegrid_dashboard_dismissble_notice', 'wydegrid_dashboard_dismissble_notice');
add_action('wp_ajax_wydegrid_dismissble_notice', 'wydegrid_dismissble_notice');
// Hook into a custom action when the button is clicked
add_action('wp_ajax_wydegrid_install_and_activate_plugins', 'wydegrid_install_and_activate_plugins');
add_action('wp_ajax_nopriv_wydegrid_install_and_activate_plugins', 'wydegrid_install_and_activate_plugins');
add_action('wp_ajax_wydegrid_rplugin_activation', 'wydegrid_rplugin_activation');
add_action('wp_ajax_nopriv_wydegrid_rplugin_activation', 'wydegrid_rplugin_activation');

// Function to install and activate the plugins



function wydegrid_check_plugin_installed_status($pugin_slug, $plugin_file)
{
    return file_exists(ABSPATH . 'wp-content/plugins/' . $pugin_slug . '/' . $plugin_file) ? true : false;
}

/* Check if plugin is activated */


function wydegrid_check_plugin_active_status($pugin_slug, $plugin_file)
{
    return is_plugin_active($pugin_slug . '/' . $plugin_file) ? true : false;
}

require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/misc.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
function wydegrid_install_and_activate_plugins()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    check_ajax_referer('wydegrid_nonce', 'nonce');
    // Define the plugins to be installed and activated
    $recommended_plugins = array(
        array(
            'slug' => 'templategalaxy',
            'file' => 'templategalaxy.php',
            'name' => __('TemplateGalaxy', 'wydegrid')
        ),
        array(
            'slug' => 'advanced-import',
            'file' => 'advanced-import.php',
            'name' =>  __('Advanced Import', 'wydegrid')
        )
        // Add more plugins here as needed
    );

    // Include the necessary WordPress functions


    // Set up a transient to store the installation progress
    set_transient('install_and_activate_progress', array(), MINUTE_IN_SECONDS * 10);

    // Loop through each plugin
    foreach ($recommended_plugins as $plugin) {
        $plugin_slug = $plugin['slug'];
        $plugin_file = $plugin['file'];
        $plugin_name = $plugin['name'];

        // Check if the plugin is active
        if (is_plugin_active($plugin_slug . '/' . $plugin_file)) {
            wydegrid_update_install_and_activate_progress($plugin_name, 'Already Active');
            continue; // Skip to the next plugin
        }

        // Check if the plugin is installed but not active
        if (wydegrid_is_plugin_installed($plugin_slug . '/' . $plugin_file)) {
            $activate = activate_plugin($plugin_slug . '/' . $plugin_file);
            if (is_wp_error($activate)) {
                wydegrid_update_install_and_activate_progress($plugin_name, 'Error');
                continue; // Skip to the next plugin
            }
            wydegrid_update_install_and_activate_progress($plugin_name, 'Activated');
            continue; // Skip to the next plugin
        }

        // Plugin is not installed or activated, proceed with installation
        wydegrid_update_install_and_activate_progress($plugin_name, 'Installing');

        // Fetch plugin information
        $api = plugins_api('plugin_information', array(
            'slug' => $plugin_slug,
            'fields' => array('sections' => false),
        ));

        // Check if plugin information is fetched successfully
        if (is_wp_error($api)) {
            wydegrid_update_install_and_activate_progress($plugin_name, 'Error');
            continue; // Skip to the next plugin
        }

        // Set up the plugin upgrader
        $upgrader = new Plugin_Upgrader();
        $install = $upgrader->install($api->download_link);

        // Check if installation is successful
        if ($install) {
            // Activate the plugin
            $activate = activate_plugin($plugin_slug . '/' . $plugin_file);

            // Check if activation is successful
            if (is_wp_error($activate)) {
                wydegrid_update_install_and_activate_progress($plugin_name, 'Error');
                continue; // Skip to the next plugin
            }
            wydegrid_update_install_and_activate_progress($plugin_name, 'Activated');
        } else {
            wydegrid_update_install_and_activate_progress($plugin_name, 'Error');
        }
    }

    // Delete the progress transient
    $redirect_url = admin_url('themes.php?page=advanced-import');

    // Delete the progress transient
    delete_transient('install_and_activate_progress');
    // Return JSON response
    wp_send_json_success(array('redirect_url' => $redirect_url));
}

// Function to check if a plugin is installed but not active
function wydegrid_is_plugin_installed($plugin_slug)
{
    $plugins = get_plugins();
    return isset($plugins[$plugin_slug]);
}

// Function to update the installation and activation progress
function wydegrid_update_install_and_activate_progress($plugin_name, $status)
{
    $progress = get_transient('install_and_activate_progress');
    $progress[] = array(
        'plugin' => $plugin_name,
        'status' => $status,
    );
    set_transient('install_and_activate_progress', $progress, MINUTE_IN_SECONDS * 10);
}

function wydegrid_dashboard_menu()
{
    add_theme_page(esc_html__('About WYDEGRID', 'wydegrid'), esc_html__('About WYDEGRID', 'wydegrid'), 'edit_theme_options', 'about-wydegrid', 'wydegrid_theme_info_display');
}
add_action('admin_menu', 'wydegrid_dashboard_menu');
function wydegrid_theme_info_display()
{ ?>
    <div class="dashboard-about-wydegrid">
        <h1> <?php echo __('Welcome to WYDEGRID- Full Site Editing WordPress Theme', 'wydegrid') ?></h1>
        <p><?php echo __('WYDEGRID is a versatile Full Site Editing (FSE) WordPress theme tailored for blogs, news, and magazine sites. With an array of pre-built sections and homepage templates, WYDEGRID makes building a website effortless for any blog, news portal, or magazine. Whether you’re a personal blogger, travel writer, tech enthusiast, storyteller, or content creator, WYDEGRID has the tools you need to design a stunning, professional website with ease. Explore more about WYDEGRID at https://websiteinwp.com/wydegrid-minimal-wordpress-theme/', 'wydegrid') ?></p>
        <h3><span class="theme-name"><span><?php echo __('Recommended Plugins:', 'wydegrid'); ?></span></h3>
        <p class="notice-text"><?php echo __('1. TemplateGalaxy: Please install and activate TemplateGalaxy pluign from our website to use additional patterns, templates  and import demo with "one click demo import" feature.', 'wydegrid') ?></p>
        <p class="notice-text"><?php echo __('2. Advanced Import: This is required only for the one-click demo import features and can be deleted once the demo is imported.', 'wydegrid') ?></p>
        <a href="#" id="install-activate-button" class="installing-all-pluign button admin-button info-button"><?php echo __('Getting started with a single click', 'wydegrid'); ?></a>
        <h3 class="wydegrid-baisc-guideline-header"><?php echo __('Basic Theme Setup', 'wydegrid') ?></h3>
        <div class="wydegrid-baisc-guideline">
            <div class="featured-box">
                <ul>
                    <li><strong><?php echo __('Setup Header Layout:', 'wydegrid') ?></strong>
                        <ul>
                            <li> - <?php echo __('Go to Appearance -> Editor -> Patterns -> Template Parts -> Header:', 'wydegrid') ?></li>
                            <li> - <?php echo __('click on Header > Click on Edit (Icon) -> Add or Remove Requirend block/content as your requirement.:', 'wydegrid') ?></li>
                            <li> - <?php echo __('Click on save to update your layout', 'wydegrid') ?></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="featured-box">
                <ul>
                    <li><strong><?php echo __('Setup Footer Layout:', 'wydegrid') ?></strong>
                        <ul>
                            <li> - <?php echo __('Go to Appearance -> Editor -> Patterns -> Template Parts -> Footer:', 'wydegrid') ?></li>
                            <li> - <?php echo __('click on Footer > Click on Edit (Icon) > Add or Remove Requirend block/content as your requirement.:', 'wydegrid') ?></li>
                            <li> - <?php echo __('Click on save to update your layout', 'wydegrid') ?></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="featured-box">
                <ul>
                    <li><strong><?php echo __('Setup Templates like Homepage/404/Search/Page/Single and more templates Layout:', 'wydegrid') ?></strong>
                        <ul>
                            <li> - <?php echo __('Go to Appearance -> Editor -> Templates:', 'wydegrid') ?></li>
                            <li> - <?php echo __('click on Template(You need to edit/update) > Click on Edit (Icon) > Add or Remove Requirend block/content as your requirement.:', 'wydegrid') ?></li>
                            <li> - <?php echo __('Click on save to update your layout', 'wydegrid') ?></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="featured-box">
                <ul>
                    <li><strong><?php echo __('Restore/Reset Default Content layout of Template(Like: Frontpage/Blog/Archive etc.)', 'wydegrid') ?></strong>
                        <ul>
                            <li> - <?php echo __('Go to Appearance -> Editor -> Templates:', 'wydegrid') ?></li>
                            <li> - <?php echo __('Click on Manage all Templates', 'wydegrid') ?></li>
                            <li> - <?php echo __('Click on 3 Dots icon at right side of respective Template', 'wydegrid') ?></li>
                            <li> - <?php echo __('Click on Clear Customization', 'wydegrid') ?></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="featured-box">
                <ul>
                    <li><strong><?php echo __('Restore/Reset Default Content layout of Template Parts(Header/Footer/Sidebar)', 'wydegrid') ?></strong>
                        <ul>
                            <li> - <?php echo __('Go to Appearance -> Editor -> Patterns:', 'wydegrid') ?></li>
                            <li> - <?php echo __('Click on Manage All Template Parts', 'wydegrid') ?></li>
                            <li> - <?php echo __('Click on 3 Dots icon at right side of respective Template parts', 'wydegrid') ?></li>
                            <li> - <?php echo __('Click on Clear Customization', 'wydegrid') ?></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="featured-list">
            <div class="half-col free-features">
                <h3><?php echo __('Wydegrid Features (Free)', 'wydegrid') ?></h3>
                <ul>
                    <li> <strong>- <?php echo __('Pre-build sections', 'wydegrid') ?></strong>
                        <ul>

                            <li> <?php echo __('Featured Banner', 'wydegrid') ?></li>
                            <li> <?php echo __('About Us Section', 'wydegrid') ?></li>
                            <li> <?php echo __('Newsletter Section', 'wydegrid') ?></li>
                            <li> <?php echo __('Post List Section', 'wydegrid') ?></li>
                            <li> <?php echo __('Post List with Sidebar Section', 'wydegrid') ?></li>
                            <li> <?php echo __('Post List with Sidebar and Full Featured Image Section', 'wydegrid') ?></li>
                            <li> <?php echo __('Post Grid Section', 'wydegrid') ?></li>
                            <li> <?php echo __('Post Grid Section with Sidebar', 'wydegrid') ?></li>
                            <li> <?php echo __('Featured Categories Section', 'wydegrid') ?></li>
                            <li> <?php echo __('Author Profile Layout', 'wydegrid') ?></li>
                            <li> <?php echo __('You May Missed Post Section', 'wydegrid') ?></li>
                        </ul>
                    <li>
                    <li> <strong>- <?php echo __('Base Templates Ready', 'wydegrid') ?></strong>
                        <ul>
                            <li> <?php echo __('404 Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Archive Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Blank Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Front Page Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Blog Home Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Index Page Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Search Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Page Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Full Width Page Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Left Sidebar Page Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Product Catalog Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Product Single Page Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Blank Template with Header and Footer', 'wydegrid') ?></li>
                            <li> <?php echo __('Single Blog Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Full Width Single Template', 'wydegrid') ?></li>
                            <li> <?php echo __('Left Sidebar Single Template', 'wydegrid') ?></li>

                        </ul>
                    <li>
                    <li><strong> - <?php echo __('3 Pre-built ready to import starter sites', 'wydegrid') ?></strong></li>
                    <li><strong> - <?php echo __('10 Global Styles Variations', 'wydegrid') ?></strong></li>
                    <li><strong> - <?php echo __('20+ Pre-built ready to use patterns/section collection', 'wydegrid') ?></strong></li>
                    <li><strong> - <?php echo __('Fully Customizable Header Layouts - 5', 'wydegrid') ?></strong></li>
                    <li> <strong>- <?php echo __('Fully Customizable Footer Layouts - 5 ', 'wydegrid') ?></strong></li>
                    <li><strong> - <?php echo __('Multiple Typography Option', 'wydegrid') ?></strong></li>
                    <li> <strong>- <?php echo __('Advanced Color Options', 'wydegrid') ?></strong></li>
                </ul>
            </div>
            <div class="half-col pro-features">
                <h3><?php echo __('Premium Version Offer', 'wydegrid') ?></h3>
                <ul>
                    <li><?php echo __('Includes all free features', 'wydegrid') ?></li>
                    <li><?php echo __('4 more additional (6 total) Pre-build ready to import starter sites', 'wydegrid') ?></li>
                    <li><?php echo __('20+ additional sections, totaling 40+ pre-built, ready-to-use sections', 'wydegrid') ?></li>
                    <li><?php echo __('Multiple Featured Banner with Slider Patterns - 5+', 'wydegrid') ?></li>
                    <li><?php echo __('Multiple Categories Post List Section', 'wydegrid') ?></li>
                    <li><?php echo __('Featured Post List Section', 'wydegrid') ?></li>
                    <li><?php echo __('Featured Post Grid Section', 'wydegrid') ?></li>
                    <li><?php echo __('Additional Post Grid Section', 'wydegrid') ?></li>
                    <li><?php echo __('Additional Post List Section', 'wydegrid') ?></li>
                    <li><?php echo __('Post Carousel Section Column 3', 'wydegrid') ?></li>
                    <li><?php echo __('Featured Categories Carousel', 'wydegrid') ?></li>
                    <li><?php echo __('Post Carousel Section Column 4', 'wydegrid') ?></li>
                    <li><?php echo __('Post Carousel Section Column 5', 'wydegrid') ?></li>
                    <li><?php echo __('Post Carousel Section Cover Style', 'wydegrid') ?></li>
                    <li><?php echo __('Multiple News Ticker Layouts', 'wydegrid') ?></li>
                    <li><?php echo __('Multiple Breaking News Layouts', 'wydegrid') ?></li>
                    <li><?php echo __('Social Share Icons display shortcode as Pattern', 'wydegrid') ?></li>
                    <li><?php echo __('Breadcrumb display shortcode as Pattern', 'wydegrid') ?></li>
                    <li><?php echo __('Related Posts display shortcode as Pattern', 'wydegrid') ?></li>
                    <li><?php echo __('Current Date display shortcode as Pattern', 'wydegrid') ?></li>
                    <li><?php echo __('Current Time display shortcode as Pattern', 'wydegrid') ?></li>
                </ul>
                <a href="https://websiteinwp.com/plan-and-pricing/" class="upgrade-btn button" target="_blank"><?php echo __('Upgrade to Pro', 'wydegrid') ?></a>
            </div>
        </div>
    </div>
<?php
}
