<?php

// Get block settings.
[
    'title' => $title,
    'view_option' => $view_option
] = $attributes;

$login_with_email = 2 === (int) PeepSo::get_option('login_with_email', 0);
$disable_registration = intval(PeepSo::get_option('site_registration_disabled', 0));

// Show notice on preview mode.
if ($preview) {
    echo sprintf(
        '<div class="notice notice-warning" style="font-size:14px; margin:0">%1$s</div>',
        esc_attr__('The login form below is only visible to guests.', 'peepso-core')
    );
}

?><div class="pso-w-profile pso-w-profile--guest psw-login--<?php echo esc_attr($view_option); ?>">
    <?php if (!empty($title)) { ?>
        <?php if (isset($widget_instance['before_title'])) echo $widget_instance['before_title']; ?>
        <h2 class="ps-widget__title has-medium-font-size">
            <?php echo esc_attr($title); ?>
        </h2>
        <?php if (isset($widget_instance['after_title'])) echo $widget_instance['after_title']; ?>
    <?php } ?>
    <div class="psf-login">
        <form class="ps-form ps-form--login ps-js-form-login-blocks" action="" onsubmit="return false;" method="post" name="login" id="ps-form-login-me">
            <!-- Login -->
            <div class="ps-form__row ps-form__row--username ps-js-username-field">
                <div class="ps-form__field ps-form__field--icon">
                    <div class="ps-input__wrapper--icon">
                        <input class="ps-input ps-input--sm ps-input--icon" type="text" name="username" placeholder="<?php echo esc_attr(PeepSoGeneral::get_login_input_label()); ?>" mouseev="true"
                             autocomplete="off" keyev="true" clickev="true" />
                        <?php if ($login_with_email) { ?>
                        <i class="gcis gci-envelope"></i>
                        <?php } else { ?>
                        <i class="gcis gci-user"></i>
                        <?php } ?>
                    </div>
                    <?php if ($login_with_email) { ?>
                    <div class="ps-form__field-notice ps-form__field-notice--important ps-js-email-notice" style="display:none"><?php echo esc_attr__('Please use a valid email address.', 'peepso-core'); ?></div>
                    <?php } ?>
                </div>
            </div>

            <!-- Password -->
            <div class="ps-form__row ps-form__row--password ps-js-password-field">
                <div class="ps-form__field ps-form__field--icon">
                    <input class="ps-input ps-input--sm ps-input--icon <?php echo PeepSo::get_option_new('password_preview_enable') ? 'ps-js-password-preview' : '' ?>"
                            type="password" name="password" placeholder="<?php echo esc_attr__('Password', 'peepso-core'); ?>" mouseev="true"
                            autocomplete="off" keyev="true" clickev="true" />
                    <i class="gcis gci-key"></i>
                </div>
            </div>

            <?php include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); ?>
            <?php if( PeepSo::two_factor_plugin_enabled() /* is_plugin_active('two-factor-authentication/two-factor-login.php') */ ) { ?>
                <!-- Two Factor authentication -->
                <div class="ps-form__row ps-form__row--password ps-js-tfa-field" style="display:none">
                    <div class="ps-form__field ps-form__field--icon">
                        <input class="ps-input ps-input--sm ps-input--icon" type="password" name="two_factor_code" placeholder="<?php echo esc_attr__('TFA code', 'peepso-core'); ?>" mouseev="true"
                               autocomplete="off" keyev="true" clickev="true" data-ps-extra="1" />
                        <i class="gcis gci-fingerprint"></i>
                    </div>
                </div>
            <?php } ?>

            <!-- Remember password -->
            <div class="ps-form__row ps-form__row--remember ps-js-password-field">
                <div class="ps-form__field ps-form__field--checkbox">
                    <div class="ps-checkbox ps-checkbox--login">
                        <input class="ps-checkbox__input" type="checkbox" alt="<?php echo esc_attr__('Remember Me', 'peepso-core'); ?>" value="yes" name="remember" id="ps-form-login-me-remember" <?php echo PeepSo::get_option('site_frontpage_rememberme_default', 0) ? ' checked':'';?>>
                        <label class="ps-checkbox__label" for="ps-form-login-me-remember"><?php echo esc_attr__('Remember Me', 'peepso-core'); ?></label>
                    </div>
                </div>
            </div>

            <!-- Submit form -->
            <div class="ps-form__row ps-form__row--submit ps-js-password-field">
                <div class="ps-form__field ps-form__field--submit">
                    <?php $recaptchaEnabled = PeepSo::get_option('recaptcha_login_enable', 0); ?>
                    <button type="submit"
                        class="ps-btn ps-btn--sm ps-btn--action ps-btn--login ps-btn--loading <?php echo $recaptchaEnabled ? 'ps-js-recaptcha' : ''; ?>"
                        <?php echo $recaptchaEnabled ? 'disabled="disabled"' : '' ?>>
                        <span><?php echo esc_attr__('Login', 'peepso-core'); ?></span>
                        <img src="<?php echo esc_url(PeepSo::get_asset('images/ajax-loader.gif')); ?>">
                    </button>
                </div>
            </div>

            <input type="hidden" name="option" value="ps_users">
            <input type="hidden" name="task" value="-user-login">
            <input type="hidden" name="redirect_to" value="<?php echo esc_url(PeepSo::get_page('redirectlogin')); ?>" />
            <?php
            // Remove ID attribute from nonce field.
            $nonce = wp_nonce_field('ajax-login-nonce', 'security', true, false);
            $nonce = preg_replace( '/\sid="[^"]+"/', '', $nonce );
            echo $nonce;
            ?>

            <?php do_action('peepso_action_render_login_form_after'); ?>
        </form>

        <?php do_action('peepso_after_login_form'); ?>

        <div class="psf-login__links">
            <?php if (0 === $disable_registration) { ?>
            <a class="psf-login__link psf-login__link--register" href="<?php echo esc_url(PeepSo::get_page('register')); ?>"><?php echo esc_attr__('Register', 'peepso-core'); ?></a>
            <?php } ?>
            <a class="psf-login__link psf-login__link--recover" href="<?php echo esc_url(PeepSo::get_page('recover')); ?>"><?php echo esc_attr__('Forgot Password', 'peepso-core'); ?></a>
            <?php if (0 === $disable_registration) { ?>
            <a class="psf-login__link psf-login__link--activation ps-js-register-activation" href="<?php echo esc_url(PeepSo::get_page('register')); ?>?resend"><?php echo esc_attr__('Resend activation code', 'peepso-core'); ?></a>
            <?php } ?>
        </div>
    </div>
    <script>
        (function() {
            // naively check if jQuery exist to prevent error
            var timer = setInterval(function() {
                if ( window.jQuery && window.peepso ) {
                    clearInterval( timer );
                    peepso.login.initForm( jQuery('.ps-js-form-login-blocks') );
                }
            }, 1000 );
        })();
    </script>
</div>