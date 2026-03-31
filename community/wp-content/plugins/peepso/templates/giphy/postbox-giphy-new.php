<div class="ps-giphy__wrapper">
    <div class="ps-giphy__preview" data-ps="preview">
        <img /><br>
        <a href="#" class="ps-giphy__change ps-btn ps-btn--sm ps-btn--app" data-ps="btn-change">
            <?php echo esc_attr__('Change image', 'peepso-core'); ?>
        </a>
    </div>
    <div class="ps-giphy ps-giphy--slider" data-ps="container">
        <div class="ps-giphy__search">
            <input type="text" class="ps-input ps-input--sm ps-giphy__input" data-ps="query"
                placeholder="<?php echo esc_attr__('Search...', 'peepso-core') ?>" style="display:none" />
            <div class="ps-giphy__powered">
                <a href="https://giphy.com/" target="_blank"></a>
            </div>
        </div>

        <div class="ps-giphy__loading ps-loading" data-ps="loading">
            <i class="gcis gci-circle-notch gci-spin"></i>
        </div>

        <div class="ps-giphy__slider" data-ps="slider">
            <div class="ps-giphy__slides" data-ps="list"></div>
            <div class="ps-giphy__nav ps-giphy__nav--left" data-ps="nav-left"><i class="gcis gci-chevron-left"></i></div>
            <div class="ps-giphy__nav ps-giphy__nav--right" data-ps="nav-right"><i class="gcis gci-chevron-right"></i></div>
            <script type="text/template" data-tmpl="item">
                <div class="ps-giphy__slide ps-giphy__slides-item" data-ps="item">
                    <img class="ps-giphy__slide-image" src="{{= data.preview }}" data-id="{{= data.id }}" data-url="{{= data.src }}" />
                </div>
            </script>
        </div>
    </div>
</div>
