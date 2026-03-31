<a role="menuitem" class="ps-postbox__pages-option" data-value="profile">
    <div class="ps-checkbox ps-checkbox--radio">
        <input class="ps-checkbox__input" type="radio" name="peepso_postbox_post_to" id="peepso_postbox_post_to_profile" value="profile" />
        <label class="ps-checkbox__label" for="peepso_postbox_post_to_profile">
            <i class="pso pso-i-user"></i>&nbsp;<span><?php echo esc_attr__('My profile', 'peepso-core') ?></span>
        </label>
    </div>
</a>
<?php
if(PeepSo::get_option_new('postbox_anon_enabled')) {
?>
<a role="menuitem" class="ps-postbox__pages-option" data-value="anon" data-anon-id="<?php echo PeepSo3_Anon::get_instance()->anon_id ?>">
    <div class="ps-checkbox ps-checkbox--radio">
        <input class="ps-checkbox__input" type="radio" name="peepso_postbox_post_to" id="peepso_postbox_post_to_anon" value="anon" />
        <label class="ps-checkbox__label" for="peepso_postbox_post_to_anon">
            <i class="pso pso-i-low-vision"></i>&nbsp;<span><?php echo esc_attr__('Anonymous post', 'peepso-core') ?></span>
        </label>
    </div>
</a>
<?php } ?>
