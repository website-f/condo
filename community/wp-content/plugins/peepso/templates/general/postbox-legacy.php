<?php if(TRUE === apply_filters('peepso_permissions_post_create', is_user_logged_in())) {
    $PeepSoPostbox = PeepSoPostbox::get_instance();
    $PeepSoGeneral = PeepSoGeneral::get_instance();
    $PeepSoUser = PeepSoUser::get_instance();
    $PeepSoActivity = PeepSoActivity::get_instance();

    $use_legacy_postbox = PeepSo::get_option('postbox_new_enabled', 1) == 0;

    ?>

    <?php if (is_user_logged_in() && FALSE === PeepSoActivityShortcode::get_instance()->is_permalink_page()) { ?>

        <?php if ($use_legacy_postbox) { ?>
            <style>
                .ps-postbox__disabler { display: none }
                .ps-postbox--disabled .ps-postbox__inner { opacity: .5 }
                .ps-postbox--disabled .ps-postbox__disabler { display: block; position: absolute; top: 0; left: 0; right: 0; bottom: 0 }
            </style>
            <div id="postbox-main" class="ps-postbox ps-postbox--disabled ps-js-postbox">
                <?php $PeepSoPostbox->before_postbox(); ?>
                <div class="ps-postbox__inner">
                    <div id="ps-postbox-status" class="ps-postbox__content ps-postbox-content">
                        <div class="ps-postbox__views ps-postbox-tabs"><?php $PeepSoPostbox->postbox_tabs(); ?></div>
                        <?php PeepSoTemplate::exec_template('general', 'postbox-status'); ?>
                    </div>

                    <div class="ps-postbox__footer ps-js-postbox-footer ps-postbox-tab ps-postbox-tab-root ps-clearfix" style="display:none">
                        <div class="ps-postbox__menu ps-postbox__menu--tabs">
                            <?php $PeepSoGeneral->post_types(array('is_current_user' => isset($is_current_user) ? $is_current_user : NULL)); ?>
                        </div>
                    </div>

                    <div class="ps-postbox__footer ps-js-postbox-footer ps-postbox-tab selected interactions" style="display:none">
                        <div class="ps-postbox__menu ps-postbox__menu--interactions">
                            <?php $PeepSoPostbox->post_interactions(array('is_current_user' => isset($is_current_user) ? $is_current_user : NULL)); ?>
                        </div>
                        <div class="ps-postbox__actions ps-postbox-action">
                            <?php if(PeepSo::is_admin() && PeepSo::is_dev_mode('embeds')) { ?>
                                <button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-postbox__action--cancel ps-js-btn-preview"><?php echo esc_attr__('Fetch URL', 'peepso-core'); ?></button>
                            <?php } ?>
                            <button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-tip ps-tip--arrow ps-postbox__action--cancel ps-button-cancel"
                                    aria-label="<?php echo esc_attr__('Cancel', 'peepso-core'); ?>"
                                    style="display:none"><i class="gcis gci-times"></i></button>
                            <button type="button" class="ps-btn ps-btn--sm ps-btn--action ps-postbox__action ps-postbox__action--post ps-button-action postbox-submit"
                                    style="display:none"><?php echo esc_attr__('Post', 'peepso-core'); ?></button>
                        </div>
                        <div class="ps-loading ps-postbox-loading" style="display: none">
                            <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
                            <div> </div>
                        </div>
                    </div>
                </div>
                <div class="ps-postbox__disabler"></div>
                <?php $PeepSoPostbox->after_postbox(); ?>
            </div>

        <?php } else { // use new postbox ?>

            <?php

            $params = ['is_current_user' => isset($is_current_user) ? $is_current_user : NULL];
            $options = apply_filters('peepso_postbox_options', [], $params);
            $types = apply_filters('peepso_postbox_types', [], $params);

            //$shortcuts = [];//apply_filters('peepso_postbox_shortcuts', [], $params);
            $shortcuts_labels = PeepSo::get_option('postbox_inactive_post_type_labels', 1);
            $types_labels = PeepSo::get_option('postbox_active_post_type_labels', 2);

            $types_classes = [
                0 => 'hide',
                1 => 'show',
                2 => 'mobile',
                3 => 'desktop'
            ];

            $types_class_inactive = isset($types_classes[$shortcuts_labels]) ? $types_classes[$shortcuts_labels] : '';
            $types_class_active = isset($types_classes[$types_labels]) ? $types_classes[$types_labels] : '';
            $types_tip_visibility = in_array($types_labels, [1, 3]) ? '' : ' pso-tip';

            // Separate privacy option.
            $options_privacy = isset($options['privacy']) ? $options['privacy'] : null;
            unset($options['privacy']);

            // Separate post-to option.
            $options_post_to = isset($options['post_to']) ? $options['post_to'] : null;
            unset($options['post_to']);

            // Hide default type if there is no other types.
            if (isset($types['text']) && count($types) === 1) {
                unset($types['text']);
            }

            ?><div data-ps="postbox">
            <div class="pso-postbox" data-ps="postbox-view">
                <div class="pso-postbox__head">
                    <div class="ps-avatar pso-postbox-head__avatar">
                        <?php $PeepSoActivity->postbox_author_avatar($PeepSoUser); ?>
                    </div>
                    <?php $placeholder = __('Say what is on your mind...', 'peepso-core'); ?>
                    <div class="pso-postbox-head__title" data-ps="content"><?php echo $placeholder; ?></div>

                    <!--			--><?php //if (isset($shortcuts['moods'])) { // separate moods shortcut ?>
                    <!--			<div class="pso-postbox-head__actions">-->
                    <!--				<button class="pso-btn pso-btn--link pso-tip pso-tip--left pso-mood__toggle" data-ps="moods"-->
                    <!--						aria-label="--><?php //echo esc_attr($shortcuts['moods']['label']); ?><!--">-->
                    <!--					<i class="pso-i-grin-alt"></i>-->
                    <!--				</button>-->
                    <!--			</div>-->
                    <!--			--><?php //unset($shortcuts['moods']); ?>
                    <!--			--><?php //} ?>
                </div>


                <?php if (count($types)) { ?>
                    <div class="pso-postbox__types pso-postbox__types--<?php echo esc_attr($types_class_inactive); if (PeepSo::get_option('postbox_inactive_post_type_shortcuts') > 4) { echo " pso-postbox__types--wrap"; } ?>" data-ps="shortcuts"><?php
                        $i = 0;
                        $limit = (int) PeepSo::get_option('postbox_inactive_post_type_shortcuts');
                        foreach ($types as $id => $shortcut) {
                            if($i >= $limit) break;                 // Apply shortcut limit
                            if($i==0 && $id=='text') continue;      // Skip "Text" if it's the first shortcut
                            $i++;
                            ?>
                            <button class="pso-postbox__type" data-ps="shortcut" data-id="<?php echo esc_attr($id) ?>">
                                <i class="<?php echo esc_attr($shortcut['icon']) ?>"></i>
                                <span><?php echo esc_attr($shortcut['label']) ?></span>
                            </button>
                    <?php } ?></div>
                <?php } ?>
            </div>

            <div class="pso-modal pso-modal--postbox" data-ps="postbox-edit" style="display:none">
                <div class="pso-modal__body">
                    <div class="pso-postbox__modal">
                        <div class="pso-postbox__head">
                            <div class="ps-avatar pso-postbox-head__avatar">
                                <?php $PeepSoActivity->postbox_author_avatar($PeepSoUser); ?>
                            </div>
                            <div class="pso-postbox-head__title">
                            <?php $PeepSoActivity->postbox_author_fullname($PeepSoUser); ?>

                                <?php if ($options_privacy) { ?>
                                    <div class="pso-postbox-head__privacy">
                                        <button class="pso-btn pso-btn--neutral pso-btn--xs pso-tip pso-tip--right" aria-label="<?php echo __('Privacy', 'peepso-core');?>" data-ps="option" data-id="privacy">
                                            <i class="<?php echo esc_attr($options_privacy['icon']) ?>" data-icon></i>
                                            <span data-label><?php echo esc_attr($options_privacy['label']); ?></span>
                                            <i class="pso-i-angle-small-down"></i>
                                        </button>
                                        <div class="pso-postbox__privacy pso-postbox__filter" data-ps="dropdown" data-id="privacy" style="display:none">
                                            <?php echo $options_privacy['html']; ?>
                                        </div>
                                    </div>
                                <?php } ?>

                            </div>
                            <div class="pso-postbox-head__actions">
                                <button class="pso-btn pso-btn--link pso-tip pso-tip--left" data-ps="btn-cancel" aria-label="<?php echo __('Cancel', 'peepso-core'); ?>">
                                    <i class="pso-i-cross"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pso-postbox__inputs">
                            <?php if (count($types)) { ?>
                                <div class="pso-postbox-inputs__wrapper" data-ps="type_inputs">
                                    <?php foreach ($types as $id => $type) { if (isset($type['html'])) { ?>
                                        <div class="pso-postbox-inputs__view" data-ps="type_input" data-id="<?php echo esc_attr($id) ?>" style="position:relative; display:none">
                                            <?php echo $type['html'] ?>
                                        </div>
                                    <?php } } ?>
                                </div>
                            <?php } ?>

                            <?php $placeholder = __('Say what is on your mind...', 'peepso-core'); ?>
                            <div class="pso-postbox-input__wrapper">
                                <div class="pso-postbox__input">
                                    <div data-ps="textarea-shadow" class="pso-postbox-input__shadow"></div>
                                    <textarea data-ps="textarea" rows="1" placeholder="<?php echo $placeholder; ?>"></textarea>
                                </div>
                                <div class="pso-postbox__loading ps-loading ps-postbox-loading" data-ps="loading" style="display:none">
                                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
                                </div>
                                <div class="ps-postbox__chars-count ps-postbox-charcount" data-ps="charcount" style="display:none">3</div>
                            </div>
                        </div>

                        <?php if (count($options)) { ?>
                            <div class="pso-postbox__options">
                                <div class="pso-postbox-options__list" data-ps="options" style="position:relative">
                                    <?php foreach ($options as $key => $option) { ?>
                                        <button class="pso-postbox-options__item" data-ps="option" data-id="<?php echo $key ?>">
                                            <i class="<?php echo esc_attr($option['icon']) ?>"></i>
                                            <span><?php echo esc_attr($option['label']) ?></span>
                                        </button>
                                    <?php } ?>
                                </div>
                                <div class="pso-postbox-options__tabs">
                                    <?php $dropdowns = array_filter($options, function($opt) { return isset($opt['html']); }); ?>
                                    <?php foreach ($dropdowns as $key => $dropdown) { ?>
                                        <div class="pso-postbox-options__tab pso-postbox__filter" data-ps="dropdown" data-id="<?php echo $key ?>" style="display:none;">
                                            <?php echo $dropdown['html'] ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (count($types)) { ?>
                            <div class="pso-postbox__types pso-postbox__types--<?php echo esc_attr($types_class_active); ?>" data-ps="types">
                                <?php foreach ($types as $id => $type) { ?>
                                    <button class="pso-postbox__type<?php echo $types_tip_visibility ?>" data-ps="type" data-id="<?php echo $id ?>" aria-label="<?php echo esc_attr($type['label']) ?>">
                                        <i class="<?php echo esc_attr($type['icon']) ?>"></i>
                                    </button>
                                <?php } ?>
                            </div>
                        <?php } ?>

                    </div>
                </div>
                <div class="pso-modal__footer" style="position:relative">
                    <?php if ($options_post_to) { ?>
                        <button class="pso-btn pso-btn--neutral pso-postbox__post-to pso-tip" data-ps="option" data-id="post_to" aria-label="<?php echo __('Post to', 'peepso-core'); ?>">
                            <i class="<?php echo esc_attr($options_post_to['icon']) ?>"></i>
                            <span><?php echo esc_attr($options_post_to['label']) ?></span>
                            <i class="pso-i-angle-small-down"></i>
                        </button>
                    <?php } ?>
                    <button class="pso-btn pso-btn--primary" data-ps="btn-submit"><?php echo __('Publish', 'peepso-core'); ?></button>
                    <?php if ($options_post_to) { ?>
                    <div class="ps-dropdown__menu ps-postbox__schedule pso-postbox-post-to__dropdown" data-ps="dropdown" data-id="post_to">
                        <?php echo $options_post_to['html'] ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            </div>

        <?php } // $use_legacy_postbox ?>
    <?php } // is_user_logged_in() ?>
<?php } else { PeepSoTemplate::exec_template('general','postbox-permission-denied'); }// peepso_permissions_post_create ?>
