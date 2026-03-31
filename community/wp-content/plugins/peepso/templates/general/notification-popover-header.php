<?php

    $profile_url = PeepSoUser::get_instance(get_current_user_id())->get_profileurl();
    $notif_url = $profile_url . 'about/notifications/';

?>
<div class="pso-notifbox__head">
    <span class="pso-notifbox__title"><?php echo esc_attr__('Notifications', 'peepso-core'); ?></span>
    <a href="<?php echo $notif_url; ?>" class="pso-btn pso-btn--link pso-notifbox__settings"><i class="pso-i-settings"></i></a>
</div>
<div class="pso-tabs pso-notifbox__tabs">
    <div class="pso-tabs__inner">
        <a href="#" data-unread class="pso-tabs__item pso-active"><?php echo esc_attr__('All', 'peepso-core'); ?><!--<span class="pso-badge">34</span>--></a>
        <a href="#" data-unread="1" class="pso-tabs__item"><?php echo esc_attr__('Unread', 'peepso-core'); ?><!--<span class="pso-badge">8</span>--></a>
    </div>
</div>
