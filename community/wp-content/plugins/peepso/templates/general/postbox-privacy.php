<div class="pso-postbox-privacy__title">
    <?php echo __('Privacy Settings', 'peepso-core'); ?>
    <button class="pso-btn pso-btn--xs" data-ps="btn-cancel"><i class="pso-i-arrow-small-left"></i><?php echo __('Back', 'peepso-core'); ?></button>
</div>
<div class="pso-postbox-privacy__menu">
    <?php foreach ($privacy_settings as $value => $setting) { ?>
    <a href="#" class="pso-postbox-privacy__item" data-id="<?php echo $value; ?>">
        <i class="<?php echo esc_attr($setting['icon']); ?>" data-icon></i>
        <div class="pso-postbox-privacy-item__data">
            <span class="pso-postbox-privacy-item__name" data-label><?php echo esc_attr($setting['label']); ?></span>
            <span class="pso-postbox-privacy-item__desc"><?php echo esc_attr($setting['description']); ?></span>
        </div>
    </a>
    <?php } ?>
    <script type="text/template" data-var="default_privacy"><?php echo $default_privacy; ?></script>
</div>
