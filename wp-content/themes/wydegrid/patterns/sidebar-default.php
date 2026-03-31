<?php

/**
 * Title: Sidebar Default
 * Slug: wydegrid/sidebar-default
 * Categories: wydegrid
 */
$wydegrid_agency_url = trailingslashit(get_template_directory_uri());
$wydegrid_images = array(
    $wydegrid_agency_url . 'assets/images/tick_mark.png',
);
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"30px","bottom":"30px","left":"24px","right":"24px"},"margin":{"top":"0px"}},"border":{"radius":"0px","width":"0px","style":"none"}},"backgroundColor":"light-color","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-light-color-background-color has-background" style="border-style:none;border-width:0px;border-radius:0px;margin-top:0px;padding-top:30px;padding-right:24px;padding-bottom:30px;padding-left:24px"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"3px"}},"spacing":{"padding":{"left":"15px"}}},"layout":{"type":"constrained","contentSize":"100%"}} -->
    <div class="wp-block-group" style="border-left-color:var(--wp--preset--color--primary);border-left-width:3px;padding-left:15px"><!-- wp:heading {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"600","textTransform":"uppercase"}},"textColor":"primary"} -->
        <h2 class="wp-block-heading has-primary-color has-text-color has-link-color" style="font-size:20px;font-style:normal;font-weight:600;text-transform:uppercase"><?php esc_html_e('Search', 'wydegrid') ?></h2>
        <!-- /wp:heading -->
    </div>
    <!-- /wp:group -->

    <!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search the site...","width":100,"widthUnit":"%","buttonText":"Search","buttonPosition":"button-inside","buttonUseIcon":true,"style":{"border":{"width":"1px","color":"#E7E7E7","radius":"0px"}}} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"30px","bottom":"30px","left":"24px","right":"24px"},"margin":{"top":"22px"}},"border":{"radius":"0px","width":"0px","style":"none"}},"backgroundColor":"light-color","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-light-color-background-color has-background" style="border-style:none;border-width:0px;border-radius:0px;margin-top:22px;padding-top:30px;padding-right:24px;padding-bottom:30px;padding-left:24px"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"3px"}},"spacing":{"padding":{"left":"15px"}}},"layout":{"type":"constrained","contentSize":"100%"}} -->
    <div class="wp-block-group" style="border-left-color:var(--wp--preset--color--primary);border-left-width:3px;padding-left:15px"><!-- wp:heading {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"600","textTransform":"uppercase"}},"textColor":"primary"} -->
        <h2 class="wp-block-heading has-primary-color has-text-color has-link-color" style="font-size:20px;font-style:normal;font-weight:600;text-transform:uppercase"><?php esc_html_e('Latest Posts', 'wydegrid') ?></h2>
        <!-- /wp:heading -->
    </div>
    <!-- /wp:group -->

    <!-- wp:query {"queryId":13,"query":{"perPage":"5","pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false}} -->
    <div class="wp-block-query"><!-- wp:post-template {"style":{"spacing":{"blockGap":"15px"}}} -->
        <!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"15px"},"margin":{"top":"0","bottom":"0"}}}} -->
        <div class="wp-block-columns are-vertically-aligned-center" style="margin-top:0;margin-bottom:0"><!-- wp:column {"verticalAlignment":"center","width":"100px"} -->
            <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:100px"><!-- wp:post-featured-image {"isLink":true,"height":"84px","style":{"border":{"radius":"0px"}}} /--></div>
            <!-- /wp:column -->

            <!-- wp:column {"verticalAlignment":"center","width":"","style":{"spacing":{"blockGap":"0"}}} -->
            <div class="wp-block-column is-vertically-aligned-center"><!-- wp:post-title {"level":4,"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|heading-color"},":hover":{"color":{"text":"var:preset|color|primary"}}}},"typography":{"fontStyle":"normal","fontWeight":"500","fontSize":"16px"},"spacing":{"margin":{"top":"0","bottom":"10px","left":"0","right":"0"}}}} /-->

                <!-- wp:post-date {"format":"M j, Y","className":"is-style-post-date-with-icon","style":{"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}}} /-->
            </div>
            <!-- /wp:column -->
        </div>
        <!-- /wp:columns -->
        <!-- /wp:post-template -->
    </div>
    <!-- /wp:query -->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"30px","bottom":"30px","left":"24px","right":"24px"},"margin":{"top":"22px"}},"border":{"radius":"0px","width":"0px","style":"none"}},"backgroundColor":"light-color","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-light-color-background-color has-background" style="border-style:none;border-width:0px;border-radius:0px;margin-top:22px;padding-top:30px;padding-right:24px;padding-bottom:30px;padding-left:24px"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"3px"}},"spacing":{"padding":{"left":"15px"}}},"layout":{"type":"constrained","contentSize":"100%"}} -->
    <div class="wp-block-group" style="border-left-color:var(--wp--preset--color--primary);border-left-width:3px;padding-left:15px"><!-- wp:heading {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"600","textTransform":"uppercase"}},"textColor":"primary"} -->
        <h2 class="wp-block-heading has-primary-color has-text-color has-link-color" style="font-size:20px;font-style:normal;font-weight:600;text-transform:uppercase"><?php esc_html_e('Latest Comments', 'wydegrid') ?></h2>
        <!-- /wp:heading -->
    </div>
    <!-- /wp:group -->

    <!-- wp:latest-comments {"displayExcerpt":false,"className":"wydegrid-latest-comments","style":{"spacing":{"margin":{"top":"20px"}}}} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"30px","bottom":"30px","left":"24px","right":"24px"},"margin":{"top":"20px","bottom":"20px"}},"border":{"radius":"0px","width":"0px","style":"none"}},"backgroundColor":"light-color","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-light-color-background-color has-background" style="border-style:none;border-width:0px;border-radius:0px;margin-top:20px;margin-bottom:20px;padding-top:30px;padding-right:24px;padding-bottom:30px;padding-left:24px"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"3px"}},"spacing":{"padding":{"left":"15px"}}},"layout":{"type":"constrained","contentSize":"100%"}} -->
    <div class="wp-block-group" style="border-left-color:var(--wp--preset--color--primary);border-left-width:3px;padding-left:15px"><!-- wp:heading {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"600","textTransform":"uppercase"}},"textColor":"primary"} -->
        <h2 class="wp-block-heading has-primary-color has-text-color has-link-color" style="font-size:20px;font-style:normal;font-weight:600;text-transform:uppercase"><?php esc_html_e('Categories', 'wydegrid') ?></h2>
        <!-- /wp:heading -->
    </div>
    <!-- /wp:group -->

    <!-- wp:categories {"showPostCounts":true,"className":"is-style-wydegrid-categories-bullet-hide-style wydegrid-sidebar-categories","style":{"typography":{"lineHeight":"2","fontStyle":"normal","fontWeight":"500"},"spacing":{"margin":{"top":"20px"}}}} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"30px","bottom":"30px","left":"24px","right":"24px"},"margin":{"top":"20px","bottom":"20px"}},"border":{"radius":"0px","width":"0px","style":"none"}},"backgroundColor":"light-color","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-light-color-background-color has-background" style="border-style:none;border-width:0px;border-radius:0px;margin-top:20px;margin-bottom:20px;padding-top:30px;padding-right:24px;padding-bottom:30px;padding-left:24px"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"3px"}},"spacing":{"padding":{"left":"15px"}}},"layout":{"type":"constrained","contentSize":"100%"}} -->
    <div class="wp-block-group" style="border-left-color:var(--wp--preset--color--primary);border-left-width:3px;padding-left:15px"><!-- wp:heading {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"600","textTransform":"uppercase"}},"textColor":"primary"} -->
        <h2 class="wp-block-heading has-primary-color has-text-color has-link-color" style="font-size:20px;font-style:normal;font-weight:600;text-transform:uppercase"><?php esc_html_e('Archives', 'wydegrid') ?></h2>
        <!-- /wp:heading -->
    </div>
    <!-- /wp:group -->

    <!-- wp:archives {"showPostCounts":true,"className":"wydegrid-archive-list"} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"30px","bottom":"30px","left":"24px","right":"24px"},"margin":{"top":"20px","bottom":"20px"}},"border":{"radius":"0px","width":"0px","style":"none"}},"backgroundColor":"light-color","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-light-color-background-color has-background" style="border-style:none;border-width:0px;border-radius:0px;margin-top:20px;margin-bottom:20px;padding-top:30px;padding-right:24px;padding-bottom:30px;padding-left:24px"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"3px"}},"spacing":{"padding":{"left":"15px"}}},"layout":{"type":"constrained","contentSize":"100%"}} -->
    <div class="wp-block-group" style="border-left-color:var(--wp--preset--color--primary);border-left-width:3px;padding-left:15px"><!-- wp:heading {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"600","textTransform":"uppercase"}},"textColor":"primary"} -->
        <h2 class="wp-block-heading has-primary-color has-text-color has-link-color" style="font-size:20px;font-style:normal;font-weight:600;text-transform:uppercase"><?php esc_html_e('Tags', 'wydegrid') ?></h2>
        <!-- /wp:heading -->
    </div>
    <!-- /wp:group -->

    <!-- wp:tag-cloud {"smallestFontSize":"14px","largestFontSize":"14px","className":"wydegrid-tags-list"} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"},"blockGap":"var:preset|spacing|30"}},"backgroundColor":"light-color","layout":{"type":"constrained","contentSize":"680px"}} -->
<div class="wp-block-group has-light-color-background-color has-background" style="padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"3px"}},"spacing":{"padding":{"left":"15px"}}},"layout":{"type":"constrained","contentSize":"100%"}} -->
    <div class="wp-block-group" style="border-left-color:var(--wp--preset--color--primary);border-left-width:3px;padding-left:15px"><!-- wp:heading {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"600","textTransform":"uppercase"}},"textColor":"primary"} -->
        <h2 class="wp-block-heading has-primary-color has-text-color has-link-color" style="font-size:20px;font-style:normal;font-weight:600;text-transform:uppercase"><?php esc_html_e('Newsletter', 'wydegrid') ?></h2>
        <!-- /wp:heading -->
    </div>
    <!-- /wp:group -->

    <!-- wp:paragraph {"align":"left","style":{"spacing":{"margin":{"top":"20px"}}}} -->
    <p class="has-text-align-left" style="margin-top:20px"><?php esc_html_e('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'wydegrid') ?></p>
    <!-- /wp:paragraph -->

    <!-- wp:paragraph {"align":"left","style":{"spacing":{"margin":{"top":"20px"}}}} -->
    <p class="has-text-align-left" style="margin-top:20px"><?php esc_html_e('Insert the contact form shortcode with the additional CSS class- "wydegrid-newsletter-section"', 'wydegrid') ?></p>
    <!-- /wp:paragraph -->

    <!-- wp:group {"style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
    <div class="wp-block-group"><!-- wp:image {"id":4638,"width":"16px","sizeSlug":"full","linkDestination":"none"} -->
        <figure class="wp-block-image size-full is-resized"><img src="<?php echo esc_url($wydegrid_images[0]) ?>" alt="" class="wp-image-4638" style="width:16px" /></figure>
        <!-- /wp:image -->

        <!-- wp:paragraph {"style":{"spacing":{"padding":{"bottom":"0px","top":"5px"}}}} -->
        <p style="padding-top:5px;padding-bottom:0px"><?php esc_html_e('By signing up, you agree to the our terms and our Privacy Policy agreement.', 'wydegrid') ?></p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->