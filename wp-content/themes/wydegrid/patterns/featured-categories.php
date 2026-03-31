<?php

/**
 * Title: Featured Categories
 * Slug: wydegrid/featured-categories
 * Categories: wydegrid
 */
$wydegrid_agency_url = trailingslashit(get_template_directory_uri());
$wydegrid_images = array(
    $wydegrid_agency_url . 'assets/images/cat_1.jpg',
    $wydegrid_agency_url . 'assets/images/cat_2.jpg',
    $wydegrid_agency_url . 'assets/images/cat_3.jpg',
);
?>
<!-- wp:group {"metadata":{"categories":["wydegrid"],"patternName":"wydegrid/featured-categories","name":"Featured Categories"},"style":{"spacing":{"padding":{"top":"24px","bottom":"24px","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:0;padding-top:24px;padding-right:var(--wp--preset--spacing--40);padding-bottom:24px;padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns -->
    <div class="wp-block-columns"><!-- wp:column -->
        <div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"padding":{"top":"10px","bottom":"20px","left":"10px","right":"10px"},"blockGap":"16px"}},"backgroundColor":"light-color","layout":{"type":"constrained"}} -->
            <div class="wp-block-group has-light-color-background-color has-background" style="padding-top:10px;padding-right:10px;padding-bottom:20px;padding-left:10px"><!-- wp:cover {"url":"<?php echo esc_url($wydegrid_images[0]) ?>","id":5728,"dimRatio":0,"customOverlayColor":"#afadad","isUserOverlayColor":true,"minHeight":230,"isDark":false,"className":"wydegrid-hover-cover","layout":{"type":"constrained"}} -->
                <div class="wp-block-cover is-light wydegrid-hover-cover" style="min-height:230px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim" style="background-color:#afadad"></span><img class="wp-block-cover__image-background wp-image-5728" alt="" src="<?php echo esc_url($wydegrid_images[0]) ?>" data-object-fit="cover" />
                    <div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","placeholder":"Write title…","fontSize":"large"} -->
                        <p class="has-text-align-center has-large-font-size"></p>
                        <!-- /wp:paragraph -->
                    </div>
                </div>
                <!-- /wp:cover -->

                <!-- wp:heading {"textAlign":"center","level":3,"style":{"elements":{"link":{"color":{"text":"var:preset|color|heading-color"},":hover":{"color":{"text":"var:preset|color|secondary"}}}},"typography":{"textTransform":"uppercase","fontSize":"24px","fontStyle":"normal","fontWeight":"700"}},"textColor":"heading-color"} -->
                <h3 class="wp-block-heading has-text-align-center has-heading-color-color has-text-color has-link-color" style="font-size:24px;font-style:normal;font-weight:700;text-transform:uppercase"><a href="#"><?php esc_html_e('Life Style', 'wydegrid') ?></a></h3>
                <!-- /wp:heading -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"padding":{"top":"10px","bottom":"20px","left":"10px","right":"10px"},"blockGap":"16px"}},"backgroundColor":"light-color","layout":{"type":"constrained"}} -->
            <div class="wp-block-group has-light-color-background-color has-background" style="padding-top:10px;padding-right:10px;padding-bottom:20px;padding-left:10px"><!-- wp:cover {"url":"<?php echo esc_url($wydegrid_images[1]) ?>","id":5759,"dimRatio":0,"customOverlayColor":"#383937","isUserOverlayColor":true,"minHeight":230,"className":"wydegrid-hover-cover","layout":{"type":"constrained"}} -->
                <div class="wp-block-cover wydegrid-hover-cover" style="min-height:230px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim" style="background-color:#383937"></span><img class="wp-block-cover__image-background wp-image-5759" alt="" src="<?php echo esc_url($wydegrid_images[1]) ?>" data-object-fit="cover" />
                    <div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","placeholder":"Write title…","fontSize":"large"} -->
                        <p class="has-text-align-center has-large-font-size"></p>
                        <!-- /wp:paragraph -->
                    </div>
                </div>
                <!-- /wp:cover -->

                <!-- wp:heading {"textAlign":"center","level":3,"style":{"elements":{"link":{"color":{"text":"var:preset|color|heading-color"},":hover":{"color":{"text":"var:preset|color|secondary"}}}},"typography":{"textTransform":"uppercase","fontSize":"24px","fontStyle":"normal","fontWeight":"700"}},"textColor":"heading-color"} -->
                <h3 class="wp-block-heading has-text-align-center has-heading-color-color has-text-color has-link-color" style="font-size:24px;font-style:normal;font-weight:700;text-transform:uppercase"><a href="#"><?php esc_html_e('Sports', 'wydegrid') ?></a></h3>
                <!-- /wp:heading -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"padding":{"top":"10px","bottom":"20px","left":"10px","right":"10px"},"blockGap":"16px"}},"backgroundColor":"light-color","layout":{"type":"constrained"}} -->
            <div class="wp-block-group has-light-color-background-color has-background" style="padding-top:10px;padding-right:10px;padding-bottom:20px;padding-left:10px"><!-- wp:cover {"url":"<?php echo esc_url($wydegrid_images[2]) ?>","id":4613,"dimRatio":0,"customOverlayColor":"#c09b92","isUserOverlayColor":true,"minHeight":230,"isDark":false,"className":"wydegrid-hover-cover","layout":{"type":"constrained"}} -->
                <div class="wp-block-cover is-light wydegrid-hover-cover" style="min-height:230px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim" style="background-color:#c09b92"></span><img class="wp-block-cover__image-background wp-image-4613" alt="" src="<?php echo esc_url($wydegrid_images[2]) ?>" data-object-fit="cover" />
                    <div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","placeholder":"Write title…","fontSize":"large"} -->
                        <p class="has-text-align-center has-large-font-size"></p>
                        <!-- /wp:paragraph -->
                    </div>
                </div>
                <!-- /wp:cover -->

                <!-- wp:heading {"textAlign":"center","level":3,"style":{"elements":{"link":{"color":{"text":"var:preset|color|heading-color"},":hover":{"color":{"text":"var:preset|color|secondary"}}}},"typography":{"textTransform":"uppercase","fontSize":"24px","fontStyle":"normal","fontWeight":"700"}},"textColor":"heading-color"} -->
                <h3 class="wp-block-heading has-text-align-center has-heading-color-color has-text-color has-link-color" style="font-size:24px;font-style:normal;font-weight:700;text-transform:uppercase"><a href="#"><?php esc_html_e('Technology', 'wydegrid') ?></a></h3>
                <!-- /wp:heading -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->