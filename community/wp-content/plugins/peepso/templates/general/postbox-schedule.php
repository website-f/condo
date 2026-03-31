<?php

$random = rand();
$time_format = get_option('time_format');
$ampm = preg_match('/[gh]/', $time_format);

?><div class="pso-postbox__schedule">
	<div class="pso-postbox-schedule__title">
		<?php echo __('Schedule your post', 'peepso-core'); ?>
		<button class="pso-btn pso-btn--xs" data-ps="btn-cancel"><i class="pso-i-arrow-small-left"></i><?php echo __('Back', 'peepso-core'); ?></button>
	</div>
    <div class="pso-postbox-schedule__list">
        <a role="menuitem" class="pso-postbox-schedule__option" data-value="now">
            <div class="ps-checkbox ps-checkbox--radio">
                <input class="ps-checkbox__input" type="radio" name="peepso_postbox_schedule_<?php echo $random ?>" id="peepso_postbox_schedule_<?php echo $random ?>_now" value="now" checked>
                <label class="ps-checkbox__label" for="peepso_postbox_schedule_<?php echo $random ?>_now">
                    <?php echo esc_attr__('Post immediately', 'peepso-core') ?>
                </label>
            </div>
        </a>
        <a role="menuitem" class="pso-postbox-schedule__option" data-value="future">
            <div class="ps-checkbox ps-checkbox--radio">
                <input class="ps-checkbox__input" type="radio" name="peepso_postbox_schedule_<?php echo $random ?>" id="peepso_postbox_schedule_<?php echo $random ?>_future" value="future">
                <label class="ps-checkbox__label" for="peepso_postbox_schedule_<?php echo $random ?>_future">
                    <?php echo esc_attr__('Select date and time', 'peepso-core') ?>
                </label>
            </div>
            <div class="ps-postbox__schedule-calendar ps-js-datetime">
                <div class="ps-postbox__schedule-form">
                    <div class="ps-postbox__schedule-label"><?php echo esc_attr__('Date', 'peepso-core') ?></div>
                    <div class="ps-postbox__schedule-date">
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-date-dd"></select>
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-date-mm"></select>
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-date-yy"></select>
                    </div>
                    <div class="ps-postbox__schedule-label"><?php echo esc_attr__('Time', 'peepso-core') ?></div>
                    <div class="ps-postbox__schedule-time">
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-time-hh"></select>
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-time-mm" data-interval="<?php echo apply_filters('peepso_postbox_schedule_interval_mm', 5); ?>"></select>
                        <?php if ($ampm) { ?>
                        <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-time-ampm"></select>
                        <?php } ?>
                    </div>
                    <div class="ps-postbox__schedule-actions">
                        <button class="ps-btn ps-btn--sm ps-btn--cp ps-btn--action ps-js-done"><?php echo esc_attr__('Done', 'peepso-core') ?></button>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
