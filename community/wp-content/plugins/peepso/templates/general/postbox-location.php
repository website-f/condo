<?php

$template_title_single = sprintf(__('is in %1$s', 'peepso-core'), '<strong>{{- data.name }}</strong>');
$template_title_miltiple = sprintf(__('at %1$s', 'peepso-core'), '<strong>{{- data.name }}</strong>');

?><div class="ps-dropdown__menu ps-postbox__location ps-postbox__location--loaded ps-js-postbox-location" style="display:block">
    <div class="ps-postbox__location-inner ps-js-location-wrapper">
        <div class="ps-postbox__location-box">
            <div class="ps-postbox__location-field ps-input__wrapper ps-input__wrapper--icon">
                <input type="text" class="ps-input ps-input--icon ps-postbox__location-input"
                    placeholder="<?php echo esc_attr__('Enter your location', 'peepso-core'); ?>" value="" />
                <i class="gcis gci-map-marked-alt"></i>
            </div>
            <div class="ps-postbox__location-search">
                <div class="ps-postbox__location-list ps-js-location-result"
                    data-no-items="<?php echo esc_attr__('No locations found', 'peepso-core'); ?>"></div>
                <div class="ps-postbox__location-map ps-js-location-map"></div>
                <div class="ps-loading" style="display: none">
                    <div class="ps-postbox__location-item ps-postbox__location-item--loading ps-js-postbox-location-item ps-js-location-loading">
                        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
                    </div>
                </div>
                <div class="ps-postbox__location-actions ps-js-location-action" style="display:block">
                    <button class="ps-btn ps-btn--sm ps-btn--action ps-postbox__location-action ps-js-select" style="display:none">
                        <i class="gcis gci-map-marker-alt"></i><span><?php echo esc_attr__('Select', 'peepso-core'); ?></span>
                    </button>
                    <button class="ps-btn ps-btn--sm ps-btn--abort ps-postbox__location-action ps-js-remove" style="display:none">
                        <i class="gcis gci-times"></i><span><?php echo esc_attr__('Remove', 'peepso-core'); ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/template" data-tmpl="item">
        <div class="ps-postbox__location-item"
            data-name="{{- data.name }}"
            data-place-id="{{= data.id }}">
            <p>{{= data.name }}</p>
            <span>{{= data.description || '&nbsp;' }}</span>
        </div>
    </script>
	<script type="text/template" data-tmpl="title_single"><?php echo $template_title_single; ?></script>
    <script type="text/template" data-tmpl="title_multi"><?php echo $template_title_miltiple; ?></script>
</div>
