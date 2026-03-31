<div class="pso-d-avatar">
    <div class="pso-d-avatar__inner">
        <div class="pso-d-avatar__editor">
            <div class="ps-alert ps-alert--abort ps-js-error"></div>

            <div class="pso-d-avatar__actions">
                <a href="#" class="pso-btn pso-btn--abort ps-js-remove">
                    <i class="pso-i-trash-xmark"></i>
                    <span><?php echo esc_attr__('Remove', 'peepso-core'); ?></span>
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" style="display:none" />
                </a>
                <a href="#" class="pso-btn pso-btn--primary-l ps-js-upload">
                    <i class="pso-i-add-image"></i>
                    <span><?php echo esc_attr__('Upload new', 'peepso-core'); ?></span>
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" style="display:none" />
                </a>
                <?php if ( PeepSo::get_option('avatars_gravatar_enable') == 1 ) { ?>
                    <a href="#" class="pso-btn pso-btn--primary-l ps-js-gravatar">
                        <i class="pso-i-circle-user"></i>
                        <span><?php echo esc_attr__('Use Gravatar', 'peepso-core'); ?></span>
                        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" style="display:none" />
                    </a>
                <?php } ?>
            </div>
            <div class="pso-d-avatar__view ps-js-has-avatar">
                <div class="pso-d-avatar__title">
                    <?php echo esc_attr__('Uploaded Photo', 'peepso-core'); ?>
                </div>
                <img alt="<?php echo esc_attr__('Automatically Generated. (Maximum width: 160px)', 'peepso-core'); ?>"
                     class="ps-image-preview ps-name-tips ps-js-original" />
                <div class="ps-avatar__loading ps-js-avatar-loading" style="display:none;">
                    <div class="ps-avatar__loading-inner">
                        <i class="gcis gci-circle-notch gci-spin"></i>
                    </div>
                </div>
            </div>
            <div class="pso-d-avatar__edit ps-js-has-avatar">
                <a href="#" class="pso-btn pso-btn--bordered ps-js-btn-rotate-l">
                    <i class="pso-i-rotate-left"></i>
                </a>
                <a href="#" class="pso-btn pso-btn--bordered ps-js-btn-crop">
                    <i class="pso-i-tool-crop"></i>
                    <span><?php echo esc_attr__('Crop', 'peepso-core'); ?></span>
                </a>
                <a href="#" class="pso-btn pso-btn--bordered ps-js-btn-rotate-r">
                    <i class="pso-i-rotate-right"></i>
                </a>
                <a href="#" class="pso-btn pso-btn--bordered ps-js-btn-crop-cancel">
                    <i class="pso-i-cross"></i>
                    <span><?php echo esc_attr__('Cancel', 'peepso-core'); ?></span>
                </a>
                <a href="#" class="pso-btn pso-btn--primary ps-js-btn-crop-save" style="display:none">
                    <i class="pso-i-check"></i>
                    <span><?php echo esc_attr__('Confirm', 'peepso-core'); ?></span>
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" style="display:none" />
                </a>
            </div>
            <div class="ps-js-no-avatar">
                <div class="ps-alert ps-alert--neutral"><?php echo esc_attr__('No avatar uploaded. Use the button above to select and upload one.', 'peepso-core'); ?></div>
            </div>
        </div>
        <div class="pso-d-avatar__preview">
            <div class="pso-d-avatar__title">
                <?php echo esc_attr__('Preview', 'peepso-core'); ?>
            </div>
            <div class="pso-d-avatar__image">
                <a href="#" class="pso-avatar pso-avatar--lg">
                    <img src="<?php echo $data['img_avatar'] ?>" alt="<?php echo esc_attr__('Avatar Preview', 'peepso-core'); ?>"
                        class="ps-js-preview" />
                </a>
            </div>
            <div class="pso-d-avatar__desc">
                <p>
                <?php

                if ($data['id'] == get_current_user_id()) {
                    printf(
                        __('This is how your avatar will appear throughout the entire community.', 'peepso-core'),
                        $data['name']
                    );
                } else {
                    printf(
                        __('This is how <strong>%s</strong> avatar will appear throughout the entire community.', 'peepso-core'),
                        $data['name']
                    );
                }
                ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Avatar uploader element -->
<div style="position:relative; width:1px; height:1px; overflow:hidden">
    <input type="file" name="filedata" accept="image/*" />
</div>
<!-- Form disabler and loading -->
<div class="ps-modal__loading ps-js-disabler" style="display:none">
    <span class="ps-icon-spinner"></span>
</div>

<?php

// Additional popup options (optional).
$opts = array(
    'title' => __('Avatar', 'peepso-core'),
    'actions' => array(
        array(
            'label' => __('Done', 'peepso-core'),
            'class' => 'ps-js-submit',
            'loading' => true,
            'primary' => true
        )
    )
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>
