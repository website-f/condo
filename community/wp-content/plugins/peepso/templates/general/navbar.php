<?php
global $post;

$navbar_sticky = "";

if(0==PeepSo::get_option('disable_navbar', 0)) {
    //PeepSoTemplate::exec_template('general', 'js-unavailable');
    $PeepSoGeneral = PeepSoGeneral::get_instance();

    $show_focus = "";

    if (class_exists('Gecko_Customizer')) {
      $settings = GeckoConfigSettings::get_instance();

        if (1 < $settings->get_option( 'opt_ps_profile_page_cover', 1 ) && has_shortcode( $post->post_content, 'peepso_profile' )) {
            $show_focus = 1;
        }

      if(1 == $settings->get_option('opt_ps_navbar_sticky', 0 ) ) {
        $navbar_sticky = "gc-navbar--sticky";
      }
    }

    if (!$show_focus) {
    ?>

    <?php if (is_user_logged_in()) { ?>
        <!-- PeepSo Navbar -->
        <div class="pso-navbar <?php echo $navbar_sticky; ?> js-toolbar">
          <div class="pso-navbar__inner">
            <div class="pso-navbar__tabs"><?php echo $PeepSoGeneral->render_navigation('primary'); ?></div>
            <div class="pso-navbar__tabs pso-navbar__tabs--mobile"><?php echo $PeepSoGeneral->render_navigation('mobile-secondary'); ?></div>
            <div class="pso-navbar__user">
                <div class="pso-dropdown pso-navbar-user__dropdown ps-js-dropdown">
                    <a href="#" class="pso-dropdown__toggle pso-navbar-user__toggle ps-js-dropdown-toggle">
                        <span><?php echo PeepSoUser::get_instance()->get_firstname(); ?></span>
                        <i class="pso-i-angle-small-down"></i>
                        <div class="pso-avatar pso-avatar--sm">
                            <img src="<?php echo PeepSoUser::get_instance()->get_avatar(); ?>" alt="<?php echo PeepSoUser::get_instance()->get_firstname(); ?> avatar">
                        </div>
                    </a>
                    <div class="pso-dropdown__menu ps-js-dropdown-menu">
                        <?php echo $PeepSoGeneral->render_navigation('user'); ?>
                    </div>
                </div>
                <div class="pso-navbar__notifs"><?php echo $PeepSoGeneral->render_navigation('secondary'); ?></div>
            </div>
            <div class="pso-navbar-toggle__wrapper">
              <a href="#" class="pso-navbar__toggle ps-js-navbar-toggle" onclick="return false;">
                <i class="pso-i-menu-dots-vertical"></i>
              </a>
            </div>
          </div>
          <div id="ps-mobile-navbar" class="pso-navbar__submenu">
            <div class="pso-dropdown__label">
              <?php echo esc_attr__('Community', 'peepso-core'); ?>
            </div>
            <?php echo $PeepSoGeneral->render_navigation('mobile-primary'); ?>
          </div>
        </div>
        <!-- end: PeepSo Navbar -->
    <?php }
    }
}

do_action('peepso_action_render_navbar_after');
?>
