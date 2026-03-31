;(function($){
    "use strict";
    
    /* 
    * Product Slider 
    */
    var WidgetProductSliderHandler = function ($scope, $) {

        var slider_elem = $scope.find('.product-slider').eq(0);

        if (slider_elem.length > 0) {
            
            slider_elem[0].style.display='block';

            var settings = slider_elem.data('settings');
            var arrows = settings['arrows'];
            var dots = settings['dots'];
            var autoplay = settings['autoplay'];
            var rtl = settings['rtl'];
            var autoplay_speed = parseInt(settings['autoplay_speed']) || 3000;
            var animation_speed = parseInt(settings['animation_speed']) || 300;
            var fade = settings['fade'];
            var pause_on_hover = settings['pause_on_hover'];
            var display_columns = parseInt(settings['product_items']) || 4;
            var scroll_columns = parseInt(settings['scroll_columns']) || 4;
            var tablet_width = parseInt(settings['tablet_width']) || 800;
            var tablet_display_columns = parseInt(settings['tablet_display_columns']) || 2;
            var tablet_scroll_columns = parseInt(settings['tablet_scroll_columns']) || 2;
            var mobile_width = parseInt(settings['mobile_width']) || 480;
            var mobile_display_columns = parseInt(settings['mobile_display_columns']) || 1;
            var mobile_scroll_columns = parseInt(settings['mobile_scroll_columns']) || 1;

            slider_elem.not('.slick-initialized').slick({
                arrows: arrows,
                prevArrow: '<button type="button" class="slick-prev"><i class="wbe-icons wbe-icon-angle-left"></i></button>',
                nextArrow: '<button type="button" class="slick-next"><i class="wbe-icons wbe-icon-angle-right"></i></button>',
                dots: dots,
                infinite: true,
                autoplay: autoplay,
                autoplaySpeed: autoplay_speed,
                speed: animation_speed,
                fade: false,
                pauseOnHover: pause_on_hover,
                slidesToShow: display_columns,
                slidesToScroll: scroll_columns,
                rtl: rtl,
                responsive: [
                    {
                        breakpoint: tablet_width,
                        settings: {
                            slidesToShow: tablet_display_columns,
                            slidesToScroll: tablet_scroll_columns
                        }
                    },
                    {
                        breakpoint: mobile_width,
                        settings: {
                            slidesToShow: mobile_display_columns,
                            slidesToScroll: mobile_scroll_columns
                        }
                    }
                ]
            });
        };
    };

    /*
    * Custom Tab
    */
    function wbcom_tabs( $tabmenus, $tabpane ){
        $tabmenus.on('click', 'a', function(e){
            e.preventDefault();
            var $this = $(this),
                $target = $this.attr('href');
            $this.addClass('htactive').parent().siblings().children('a').removeClass('htactive');
            $( $tabpane + $target ).addClass('htactive').siblings().removeClass('htactive');

            // slick refresh
            if( $('.slick-slider').length > 0 ){
                var $id = $this.attr('href');
                $( $id ).find('.slick-slider').slick('refresh');
            }

        });
    }

    /* 
    * Universal product 
    */
    function productImageThumbnailsSlider( $slider ){
        $slider.slick({
            dots: true,
            arrows: true,
            prevArrow: '<button class="slick-prev"><i class="wbe-icons wbe-icon-angle-left"></i></button>',
            nextArrow: '<button class="slick-next"><i class="wbe-icons wbe-icon-angle-right"></i></button>',
        });

        
    }
    if( $(".wb-product-image-slider").length > 0 ) {
        productImageThumbnailsSlider( $(".wb-product-image-slider") );
    }

    var WidgetThumbnaisImagesHandler = function thumbnailsimagescontroller(){
        wbcom_tabs( $(".wb-product-cus-tab-links"), '.wb-product-cus-tab-pane' );
        wbcom_tabs( $(".wb-tab-menus"), '.wb-tab-pane' );
    }

    /*
    * Tool Tip
    */
    function wbcom_tool_tips(element, content) {
        if ( content == 'html' ) {
            var tipText = element.html();
        } else {
            var tipText = element.attr('title');
        }
        element.on('mouseover', function() {
            if ( $('.wbcom-tip').length == 0 ) {
                element.before('<span class="wbcom-tip">' + tipText + '</span>');
                $('.wbcom-tip').css('transition', 'all 0.5s ease 0s');
                $('.wbcom-tip').css('margin-left', 0);
            }
        });
        element.on('mouseleave', function() {
            $('.wbcom-tip').remove();
        });
    }

    /*
    * Tooltip Render
    */
    var WidgetWoolentorTooltipHandler = function wbcom_tool_tip(){
        $('a.wbcom-compare').each(function() {
            wbcom_tool_tips( $(this), 'title' );
        });
        $('.wbcom-cart a.add_to_cart_button,.wbcom-cart a.added_to_cart,.wbcom-cart a.button').each(function() {
            wbcom_tool_tips( $(this), 'html');
        });
    }

    /*
    * Product Tab
    */
    var  WidgetProducttabsHandler = wbcom_tabs( $(".wb-tab-menus"),'.wb-tab-pane' );

    /*
    * Run this code under Elementor.
    */
    $(window).on('elementor/frontend/init', function () {

        elementorFrontend.hooks.addAction( 'frontend/element_ready/wbcom-product-tab.default', WidgetProductSliderHandler);
        elementorFrontend.hooks.addAction( 'frontend/element_ready/wbcom-product-tab.default', WidgetProducttabsHandler);

        elementorFrontend.hooks.addAction( 'frontend/element_ready/wbcom-universal-product.default', WidgetProductSliderHandler);
        elementorFrontend.hooks.addAction( 'frontend/element_ready/wbcom-universal-product.default', WidgetWoolentorTooltipHandler);
        elementorFrontend.hooks.addAction( 'frontend/element_ready/wbcom-universal-product.default', WidgetThumbnaisImagesHandler);
        
        elementorFrontend.hooks.addAction( 'frontend/element_ready/wbcom-wc-testimonial.default', WidgetProductSliderHandler );

    });

})(jQuery);