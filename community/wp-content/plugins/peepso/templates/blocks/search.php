<?php

// Get block settings.
[
    'title' => $title,
] = $attributes;

?><div class="ps-widget__wrapper--external ps-widget--external ps-js-widget-search">
    <?php if (!empty($title)) { ?>
    <div class="ps-widget__header--external">
        <?php if (isset($widget_instance['before_title'])) echo $widget_instance['before_title']; ?>
        <h2 class="ps-widget__title has-medium-font-size">
            <?php echo esc_attr($title); ?>
        </h2>
        <?php if (isset($widget_instance['after_title'])) echo $widget_instance['after_title']; ?>
    </div>
    <?php } ?>
    <div class="ps-widget__body--external">
        <div class="pso-w-search">
            <?php PeepSoTemplate::exec_template('search', 'search', array('context' => 'widget')); ?>
        </div>
    </div>
</div>
