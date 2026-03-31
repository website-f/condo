<?php

// Get block settings.
[
    'content_position' => $content_position,
    'guest_behavior' => $guest_behavior,
    'show_name' => $show_name,
    'compact_mode' => $compact_mode,
    'show_avatar' => $show_avatar,
    'show_notifications' => $show_notifications,
    'show_usermenu' => $show_usermenu,
    'show_logout' => $show_logout,
    'show_vip' => $show_vip,
    'show_badges' => $show_badges,
] = $attributes;

$compact_mode_class = '';

// Disable compact mode on preview.
if (isset($compact_mode) && !$preview) {
    $compact_mode = (int) $compact_mode;
    if (in_array($compact_mode, [1, 2, 3])) {
        if (in_array($compact_mode, [1, 3])) $compact_mode_class .= ' pso-w-userbar--mobile';
        if (in_array($compact_mode, [2, 3])) $compact_mode_class .= ' pso-w-userbar--desktop';
    }
}

?><div class="pso-w-userbar pso-w-userbar--<?php echo esc_attr($content_position); ?> ps-js-widget-userbar <?php echo esc_attr($compact_mode_class); ?>">
    <div class="pso-w-userbar__inner">
    <?php if (is_user_logged_in()) { ?>

        <div class="pso-notifs psw-notifs--userbar ps-js-widget-userbar-notifications"><?php

            do_action('peepso_action_userbar_notifications_before', $user->get_id());
            echo $toolbar;
            do_action('peepso_action_userbar_notifications_after', $user->get_id());

        ?></div>

        <div class="pso-w-userbar__user">
            <div class="pso-dropdown pso-w-userbar-user__dropdown ps-js-dropdown">
                <?php if (isset($show_usermenu) && 1 == (int) $show_usermenu) { ?>
                <a href="#" class="pso-dropdown__toggle pso-w-userbar-user__toggle ps-js-dropdown-toggle">
                <?php } else { ?>
                <a href="<?php echo esc_url($user->get_profileurl()); ?>" class="pso-w-userbar-user__link">
                <?php } ?>
                    <?php if (isset($show_avatar) && 1 === (int) $show_avatar) { ?>
                        <div class="pso-avatar pso-avatar--sm pso-w-userbar__avatar">
                            <img src="<?php echo esc_url($user->get_avatar()); ?>" alt="<?php echo esc_attr($user->get_fullname()); ?> avatar"
                                title="<?php echo esc_url($user->get_profileurl()); ?>">
                        </div>
                    <?php } ?>
                    <?php if (isset($show_usermenu) && 1 == (int) $show_usermenu) { ?>
                    <i class="pso-i-angle-small-down"></i>
                    <?php } ?>
                    <span>
                        <?php if (isset($show_vip) && 1 === (int) $show_vip) { ?>
                        <div class="pso-vip"><?php do_action('peepso_action_userbar_user_name_before', $user->get_id()); ?></div>
                        <?php } ?>

                        <span>
                        <?php
                        if (isset($show_name)) {
                            $show_name = (int) $show_name;
                            if (in_array($show_name, [1, 2])) {
                                $name = $show_name === 2 ? $user->get_fullname() : $user->get_firstname();
                                echo esc_attr($name);
                            }
                        }
                        ?>
                        </span>

                        <?php if (isset($show_badges) && 1 === (int) $show_badges) { ?>
                        <div class="pso-vip"><?php do_action('peepso_action_userbar_user_name_after', $user->get_id()); ?></div>
                        <?php } ?>
                    </span>
                </a>
                <?php if (isset($show_usermenu) && 1 == (int) $show_usermenu) { ?>
                <?php
                // Profile Submenu extra links
                if (apply_filters('peepso_filter_navigation_preferences', TRUE)) {
                    $links['peepso-core-preferences'] = array(
                        'href' => $user->get_profileurl() . 'about/preferences/',
                        'icon' => 'pso-i-user-pen',
                        'label' => __('Preferences', 'peepso-core'),
                    );
                }

                if (apply_filters('peepso_filter_navigation_log_out', TRUE)) {
                    $links['peepso-core-logout'] = array(
                        'href' => PeepSo::get_page('logout'),
                        'icon' => 'pso-i-power',
                        'label' => __('Log Out', 'peepso-core'),
                        'widget' => TRUE,
                    );
                }
                ?>
                <div class="pso-dropdown__menu ps-js-dropdown-menu">
                    <?php
                    if (isset($show_name)) {
                        $show_name = (int) $show_name;
                        if (in_array($show_name, [1, 2])) {
                    ?>
                    <div class="pso-dropdown__user">
                        <div class="pso-dropdown-user__label"><?php echo esc_attr__('Logged in as:', 'peepso-core'); ?></div>
                        <?php if (isset($show_vip) && 1 === (int) $show_vip) { ?>
                        <div class="pso-vip"><?php do_action('peepso_action_userbar_user_name_before', $user->get_id()); ?></div>
                        <?php } ?>

                        <span>
                        <?php
                            $name = $show_name === 2 ? $user->get_fullname() : $user->get_firstname();
                            echo esc_attr($name);
                        ?>
                        </span>

                        <?php if (isset($show_badges) && 1 === (int) $show_badges) { ?>
                        <div class="pso-vip"><?php do_action('peepso_action_userbar_user_name_after', $user->get_id()); ?></div>
                        <?php } ?>
                    </div>
                    <?php
                        }
                    }
                    ?>
                    <?php
                        foreach ($links as $id => $link) {
                            if (!isset($link['label']) || !isset($link['href']) || !isset($link['icon'])) {
                                var_dump($link);
                            }

                            $class = isset($link['class']) ? $link['class'] : 'pso-dropdown__item' ;

                            $href = $user->get_profileurl(). $link['href'];
                            if ('http' == substr(strtolower($link['href']), 0,4)) {
                                $href = $link['href'];
                            }

                            echo sprintf(
                                '<a href="%1$s" class="%2$s"><i class="%3$s"></i> %4$s</a>',
                                esc_url($href), esc_attr($class), esc_attr($link['icon']), esc_attr($link['label'])
                            );
                        }
                    ?>
                </div>
                <?php } ?>
                <?php if (isset($show_logout) && 1 === (int) $show_logout) { ?>
                <a class="pso-w-userbar__logout" href="<?php echo esc_url(PeepSo::get_page('logout')); ?>"
                        title="<?php echo esc_attr__('Log Out', 'peepso-core'); ?>"
                        arialabel="<?php echo esc_attr__('Log Out', 'peepso-core'); ?>">
                    <i class="pso-i-power"></i>
                </a>
                <?php } ?>
            </div>
        </div>

    <?php } elseif($guest_behavior=='login') { ?>

        <a class="pso-w-userbar__login" href="<?php echo esc_url(PeepSo::get_page('login')); ?>"><?php echo esc_attr__('Log in', 'peepso-core'); ?></a>

    <?php } ?>

    </div>

    <div class="pso-w-userbar__toggle ps-js-widget-userbar-toggle">
        <div class="pso-avatar pso-avatar--sm pso-w-userbar__avatar">
            <?php if (is_user_logged_in()) { ?>
                <img src="<?php echo esc_url($user->get_avatar());?>" alt="<?php echo esc_attr($user->get_fullname()); ?> avatar" title="<?php echo esc_url($user->get_profileurl()); ?>">
            <?php } else { ?>
                <i class="pso-i-user"></i>
            <?php } ?>
        </div>
        <span class="pso-badge pso-badge--float pso-badge--primary pso-notif__badge ps-js-notif-counter"></span>
        <i class="pso-w-userbar__close pso-i-cross-small"></i>
    </div>
</div>
