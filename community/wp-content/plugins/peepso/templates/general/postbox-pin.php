<?php

$random = rand();
$time_format = get_option('time_format');
$ampm = preg_match('/[gh]/', $time_format);

?><div class="pso-postbox__pin">
	<div class="pso-postbox-pin__title">
		<?php echo __('Pin your post', 'peepso-core'); ?>
		<button class="pso-btn pso-btn--xs" data-ps="btn-cancel"><i class="pso-i-arrow-small-left"></i><?php echo __('Back', 'peepso-core'); ?></button>
	</div>
    <div class="pso-postbox-pin__list">
        <a role="menuitem" class="pso-postbox-pin__option" data-value="no">
            <div class="ps-checkbox ps-checkbox--radio">
                <input class="ps-checkbox__input" type="radio" name="peepso_postbox_pin_<?php echo $random ?>" id="peepso_postbox_pin_<?php echo $random ?>_no" value="0" checked>
                <label class="ps-checkbox__label" for="peepso_postbox_pin_<?php echo $random ?>_no">
                    <?php echo esc_attr__('Do not pin', 'peepso-core') ?>
                </label>
            </div>
        </a>
        <a role="menuitem" class="pso-postbox-pin__option" data-value="indefinitely">
            <div class="ps-checkbox ps-checkbox--radio">
                <input class="ps-checkbox__input" type="radio" name="peepso_postbox_pin_<?php echo $random ?>" id="peepso_postbox_pin_<?php echo $random ?>_indefinitely" value="1">
                <label class="ps-checkbox__label" for="peepso_postbox_pin_<?php echo $random ?>_indefinitely">
                    <?php echo esc_attr__('Pin indefinitely', 'peepso-core') ?>
                </label>
            </div>
        </a>
        <a role="menuitem" class="pso-postbox-pin__option" data-value="until">
            <div class="ps-checkbox ps-checkbox--radio">
                <input class="ps-checkbox__input" type="radio" name="peepso_postbox_pin_<?php echo $random ?>" id="peepso_postbox_pin_<?php echo $random ?>_until" value="2">
                <label class="ps-checkbox__label" for="peepso_postbox_pin_<?php echo $random ?>_until">
                    <?php echo esc_attr__('Pin until...', 'peepso-core') ?>
                </label>
            </div>
            <div class="ps-postbox__pin-calendar ps-js-datetime">
                <div class="ps-postbox__pin-form">
                    <div class="ps-postbox__pin-label"><?php echo esc_attr__('Date', 'peepso-core') ?></div>
                    <div class="ps-postbox__pin-date">
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-date-dd"></select>
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-date-mm"></select>
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-date-yy"></select>
                    </div>
                    <div class="ps-postbox__pin-label"><?php echo esc_attr__('Time', 'peepso-core') ?></div>
                    <div class="ps-postbox__pin-time">
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-time-hh"></select>
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-time-mm" data-interval="<?php echo apply_filters('peepso_postbox_pin_interval_mm', 15); ?>"></select>
                        <?php if ($ampm) { ?>
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-time-ampm"></select>
                        <?php } ?>
                    </div>
                    <div class="ps-postbox__pin-actions">
                        <button class="ps-btn ps-btn--sm ps-btn--cp ps-btn--action ps-js-done"><?php echo esc_attr__('Done', 'peepso-core') ?></button>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
