<?php

/**
 * Title: Footer Default
 * Slug: wydegrid/footer-default
 * Categories: wydegrid, footer
 */
?>
<!-- wp:group {"metadata":{"categories":["footer"],"patternName":"wydegrid/footer-col-3","name":"Footer with column 3 Box Style"},"style":{"spacing":{"margin":{"top":"0","bottom":"0"},"padding":{"right":"var:preset|spacing|40","left":"var:preset|spacing|40","top":"84px","bottom":"30px"}}},"backgroundColor":"background-alt","layout":{"type":"constrained","contentSize":"1180px"}} -->
<div class="wp-block-group has-background-alt-background-color has-background" style="margin-top:0;margin-bottom:0;padding-top:84px;padding-right:var(--wp--preset--spacing--40);padding-bottom:30px;padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"28px"}}}} -->
    <div class="wp-block-columns"><!-- wp:column {"style":{"border":{"width":"1px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"},"blockGap":"var:preset|spacing|40"}},"borderColor":"gray-color"} -->
        <div class="wp-block-column has-border-color has-gray-color-border-color" style="border-width:1px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40","margin":{"top":"0","bottom":"0"}}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
            <div class="wp-block-group" style="margin-top:0;margin-bottom:0"><!-- wp:site-title {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"},":hover":{"color":{"text":"var:preset|color|secondary"}}}},"typography":{"fontSize":"40px"}},"textColor":"primary"} /--></div>
            <!-- /wp:group -->

            <!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|background"}}}},"textColor":"background"} -->
            <p class="has-background-color has-text-color has-link-color"><?php esc_html_e('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.', 'wydegrid') ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30","margin":{"top":"40px"}}},"layout":{"type":"flex","orientation":"vertical"}} -->
            <div class="wp-block-group" style="margin-top:40px"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}},"typography":{"fontSize":"18px"}},"textColor":"light-color"} -->
                <p class="has-light-color-color has-text-color has-link-color" style="font-size:18px"><?php esc_html_e('14th Street, Caltech, New Jersey, Alabama, United States', 'wydegrid') ?></p>
                <!-- /wp:paragraph -->

                <!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}},"typography":{"fontSize":"18px"}},"textColor":"light-color"} -->
                <p class="has-light-color-color has-text-color has-link-color" style="font-size:18px"><?php esc_html_e('+1 (888) 012-3456', 'wydegrid') ?></p>
                <!-- /wp:paragraph -->

                <!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}},"typography":{"fontSize":"18px"}},"textColor":"light-color"} -->
                <p class="has-light-color-color has-text-color has-link-color" style="font-size:18px"><?php esc_html_e('email@example.com', 'wydegrid') ?></p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"style":{"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}},"border":{"width":"1px"}},"borderColor":"gray-color"} -->
        <div class="wp-block-column has-border-color has-gray-color-border-color" style="border-width:1px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"2px"}},"spacing":{"padding":{"left":"15px"}}},"layout":{"type":"constrained","contentSize":"100%"}} -->
            <div class="wp-block-group" style="border-left-color:var(--wp--preset--color--primary);border-left-width:2px;padding-left:15px"><!-- wp:heading {"level":3,"style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}},"typography":{"fontSize":"24px","fontStyle":"normal","fontWeight":"600","textTransform":"uppercase"}},"textColor":"light-color"} -->
                <h3 class="wp-block-heading has-light-color-color has-text-color has-link-color" style="font-size:24px;font-style:normal;font-weight:600;text-transform:uppercase"><?php esc_html_e('Latest Articles', 'wydegrid') ?></h3>
                <!-- /wp:heading -->
            </div>
            <!-- /wp:group -->

            <!-- wp:query {"queryId":13,"query":{"perPage":"3","pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false}} -->
            <div class="wp-block-query"><!-- wp:post-template {"style":{"spacing":{"blockGap":"15px"}}} -->
                <!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"15px"},"margin":{"top":"0","bottom":"0"}}}} -->
                <div class="wp-block-columns are-vertically-aligned-center" style="margin-top:0;margin-bottom:0"><!-- wp:column {"verticalAlignment":"center","width":"100px"} -->
                    <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:100px"><!-- wp:post-featured-image {"isLink":true,"height":"84px","style":{"border":{"radius":"0px"}}} /--></div>
                    <!-- /wp:column -->

                    <!-- wp:column {"verticalAlignment":"center","width":"","style":{"spacing":{"blockGap":"0"}}} -->
                    <div class="wp-block-column is-vertically-aligned-center"><!-- wp:post-title {"level":4,"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|background"},":hover":{"color":{"text":"var:preset|color|primary"}}}},"typography":{"fontStyle":"normal","fontWeight":"500","fontSize":"18px"},"spacing":{"margin":{"top":"0","bottom":"10px","left":"0","right":"0"}}}} /-->

                        <!-- wp:post-date {"format":"M j, Y","className":"is-style-post-date-with-icon","style":{"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}}} /-->
                    </div>
                    <!-- /wp:column -->
                </div>
                <!-- /wp:columns -->
                <!-- /wp:post-template -->
            </div>
            <!-- /wp:query -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"style":{"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}},"border":{"width":"1px"}},"borderColor":"gray-color"} -->
        <div class="wp-block-column has-border-color has-gray-color-border-color" style="border-width:1px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"2px"}},"spacing":{"padding":{"left":"15px"}}},"layout":{"type":"constrained","contentSize":"100%"}} -->
            <div class="wp-block-group" style="border-left-color:var(--wp--preset--color--primary);border-left-width:2px;padding-left:15px"><!-- wp:heading {"level":3,"style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}},"typography":{"fontSize":"24px","fontStyle":"normal","fontWeight":"600","textTransform":"uppercase"}},"textColor":"light-color"} -->
                <h3 class="wp-block-heading has-light-color-color has-text-color has-link-color" style="font-size:24px;font-style:normal;font-weight:600;text-transform:uppercase"><?php esc_html_e('Tags', 'wydegrid') ?></h3>
                <!-- /wp:heading -->
            </div>
            <!-- /wp:group -->

            <!-- wp:tag-cloud {"smallestFontSize":"14px","largestFontSize":"14px","className":"wydegrid-footer-tags"} /-->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->

    <!-- wp:group {"style":{"spacing":{"padding":{"top":"20px","bottom":"30px"},"margin":{"top":"40px"}},"border":{"top":{"width":"0px","style":"none"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
    <div class="wp-block-group" style="border-top-style:none;border-top-width:0px;margin-top:40px;padding-top:20px;padding-bottom:30px"><!-- wp:social-links {"iconColor":"light-color","iconColorValue":"#FFFFFF","className":"is-style-logos-only wydegrid-social-icons","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|30","left":"var:preset|spacing|50"}}}} -->
        <ul class="wp-block-social-links has-icon-color is-style-logos-only wydegrid-social-icons"><!-- wp:social-link {"url":"#","service":"facebook"} /-->

            <!-- wp:social-link {"url":"#","service":"x"} /-->

            <!-- wp:social-link {"url":"#","service":"instagram"} /-->

            <!-- wp:social-link {"url":"#","service":"youtube"} /-->

            <!-- wp:social-link {"url":"#","service":"linkedin"} /-->
        </ul>
        <!-- /wp:social-links -->

        <!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|background"}}}},"textColor":"background"} -->
        <p class="has-background-color has-text-color has-link-color"><?php esc_html_e('Proudly Powered by WordPress | Theme: WYDEGRID by WebsiteinWP', 'wydegrid') ?></p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->