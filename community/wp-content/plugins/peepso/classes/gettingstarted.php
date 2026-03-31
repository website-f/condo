<?php

class PeepSoGettingStarted
{
    public static function init()
    {
        $PeepSoInput = new PeepSoInput();

        $section = $PeepSoInput->value('section', 'peepso', FALSE); // SQL Safe
        $step = $PeepSoInput->int('step', 1);

        $args = array('step' => $step, 'license' => '');

        // Installer
        if ($step == 2) {
            if (isset($_REQUEST['activate_plugins'])) {
                PeepSoAdmin::perform_activation('plugins');
            }

            if (isset($_REQUEST['activate_themes'])) {
                PeepSoAdmin::perform_activation('themes');
            }

            wp_register_script(
                'peepso-admin-addons',
                PeepSo::get_asset('js/admin-addons.js'),
                array('jquery', 'peepso'),
                PeepSo::PLUGIN_VERSION,
                TRUE
            );


            wp_localize_script('peepso-admin-addons', 'peepsoadminaddonsdata', array(
                'label' => [
                    'install' => __('Install', 'peepso-core'),
                    'installing' => __('Installing...', 'peepso-core'),
                    'installed' => __('Installed', 'peepso-core'),
                    'install_failed' => __('Failed to install', 'peepso-core'),
                    'not_installed' => __('Not installed', 'peepso-core'),
                    'active' => __('Active', 'peepso-core'),
                    'activate' => __('Activate', 'peepso-core'),
                    'activating' => __('Activating...', 'peepso-core'),
                    'activated' => __('Activated', 'peepso-core'),
                    'activate_failed' => __('Failed to activate', 'peepso-core'),
                    'inactive' => __('Inactive', 'peepso-core'),
                    'your_license' => __('Your license', 'peepso-core'),

                    // Activate theme warning message.
                    'activate_theme_warning_title' => 'You are about to switch the active theme on your site!',
                    'activate_theme_warning_message' => 'You chose to activate a <b>PeepSo Theme</b>, which is a fully featured WordPress Theme. Activating it will <b>immediately switch your entire site to the selected PeepSo Theme</b>.<br/><br/>Do you want to continue and <b>switch your site theme</b>?',
                    'activate_theme_warning_btn_cancel' => '<b>Go back</b>',
                    'activate_theme_warning_btn_confirm' => '<b>Activate</b>',

                    'license_check_error_message' => '<strong>' . PeepSo3_Helper_PeepSoAJAX_Online::get_message('installer') . '</strong><br/>',
                    'license_check_error_description' => PeepSo3_Helper_PeepSoAJAX_Online::get_description(),
                ]
            ));

            wp_enqueue_script('peepso-admin-addons');
            $license = PeepSo3_Helper_Addons::get_license();
            $args['license'] = $license;
        }

        PeepSoTemplate::exec_template('gettingstarted', $section, $args);
    }

    public static function get_youtube_video() {
        // Fetch the content from the URL
        $yt = file_get_contents('https://cdn.peepso.com/upsell/yt.txt');

        if(NULL === $yt) {
            return '';
        }

        return $yt;
    }
}
