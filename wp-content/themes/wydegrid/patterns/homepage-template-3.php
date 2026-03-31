<?php

/**
 * Title: Homepage Template 3
 * Slug: wydegrid/homepage-template-3
 * Categories: wydegrid-homes
 */
$wydegrid_agency_url = trailingslashit(get_template_directory_uri());
$wydegrid_images = array(
    $wydegrid_agency_url . 'assets/images/cat_1.jpg',
    $wydegrid_agency_url . 'assets/images/cat_2.jpg',
    $wydegrid_agency_url . 'assets/images/cat_3.jpg',
);
?>
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"0px","bottom":"0px","left":"0","right":"0"}}},"backgroundColor":"light-shade","layout":{"type":"constrained","contentSize":"100%"}} -->
<main class="wp-block-group has-light-shade-background-color has-background" style="padding-top:0px;padding-right:0;padding-bottom:0px;padding-left:0"><!-- wp:group {"style":{"spacing":{"margin":{"top":"0px"},"padding":{"top":"28px"}}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
    <div class="wp-block-group" style="margin-top:0px;padding-top:28px"><!-- wp:columns {"metadata":{"categories":["wydegrid"],"patternName":"wydegrid/featured-banner","name":"Featured Banner"},"style":{"spacing":{"blockGap":{"top":"10px","left":"10px"}}}} -->
        <div class="wp-block-columns"><!-- wp:column {"width":"50%"} -->
            <div class="wp-block-column" style="flex-basis:50%"><!-- wp:query {"queryId":6,"query":{"perPage":"1","pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false}} -->
                <div class="wp-block-query"><!-- wp:post-template -->
                    <!-- wp:cover {"useFeaturedImage":true,"isUserOverlayColor":true,"minHeight":500,"gradient":"dark-gradient","contentPosition":"bottom left","className":"wydegrid-hover-cover","style":{"spacing":{"padding":{"left":"24px","right":"24px","top":"20px","bottom":"20px"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained"}} -->
                    <div class="wp-block-cover has-custom-content-position is-position-bottom-left wydegrid-hover-cover" style="padding-top:20px;padding-right:24px;padding-bottom:20px;padding-left:24px;min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim has-background-gradient has-dark-gradient-gradient-background"></span>
                        <div class="wp-block-cover__inner-container"><!-- wp:post-terms {"term":"category","className":"is-style-categories-background-with-round"} /-->

                            <!-- wp:post-title {"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"},":hover":{"color":{"text":"var:preset|color|primary"}}}},"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"40px","textTransform":"uppercase"},"spacing":{"margin":{"top":"0","bottom":"0"}}}} /-->

                            <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                            <div class="wp-block-group"><!-- wp:post-author-name {"className":"is-style-author-name-with-white-icon","style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}},"typography":{"textTransform":"capitalize"}},"textColor":"light-color"} /-->

                                <!-- wp:post-date {"format":"M j, Y","className":"is-style-post-date-with-white-icon","style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}}},"textColor":"light-color"} /-->
                            </div>
                            <!-- /wp:group -->
                        </div>
                    </div>
                    <!-- /wp:cover -->
                    <!-- /wp:post-template -->

                    <!-- wp:query-no-results -->
                    <!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
                    <p></p>
                    <!-- /wp:paragraph -->
                    <!-- /wp:query-no-results -->
                </div>
                <!-- /wp:query -->
            </div>
            <!-- /wp:column -->

            <!-- wp:column {"width":"50%","style":{"spacing":{"blockGap":"10px"}}} -->
            <div class="wp-block-column" style="flex-basis:50%"><!-- wp:query {"queryId":6,"query":{"perPage":"1","pages":0,"offset":"1","postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false}} -->
                <div class="wp-block-query"><!-- wp:post-template -->
                    <!-- wp:cover {"useFeaturedImage":true,"isUserOverlayColor":true,"minHeight":245,"gradient":"dark-gradient","contentPosition":"bottom left","className":"wydegrid-hover-cover","style":{"spacing":{"padding":{"top":"20px","bottom":"20px","left":"24px","right":"24px"}}},"layout":{"type":"constrained"}} -->
                    <div class="wp-block-cover has-custom-content-position is-position-bottom-left wydegrid-hover-cover" style="padding-top:20px;padding-right:24px;padding-bottom:20px;padding-left:24px;min-height:245px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim has-background-gradient has-dark-gradient-gradient-background"></span>
                        <div class="wp-block-cover__inner-container"><!-- wp:post-terms {"term":"category","className":"is-style-categories-background-with-round"} /-->

                            <!-- wp:post-title {"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"},":hover":{"color":{"text":"var:preset|color|primary"}}}},"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"24px","lineHeight":"1.3","textTransform":"uppercase"},"spacing":{"margin":{"top":"0","bottom":"0"}}}} /-->

                            <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                            <div class="wp-block-group"><!-- wp:post-author-name {"className":"is-style-author-name-with-white-icon","style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}},"typography":{"textTransform":"capitalize"}},"textColor":"light-color"} /-->

                                <!-- wp:post-date {"format":"M j, Y","className":"is-style-post-date-with-white-icon","style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}}},"textColor":"light-color"} /-->
                            </div>
                            <!-- /wp:group -->
                        </div>
                    </div>
                    <!-- /wp:cover -->
                    <!-- /wp:post-template -->

                    <!-- wp:query-no-results -->
                    <!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
                    <p></p>
                    <!-- /wp:paragraph -->
                    <!-- /wp:query-no-results -->
                </div>
                <!-- /wp:query -->

                <!-- wp:query {"queryId":6,"query":{"perPage":"2","pages":0,"offset":"2","postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false}} -->
                <div class="wp-block-query"><!-- wp:post-template {"style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"grid","columnCount":"2"}} -->
                    <!-- wp:cover {"useFeaturedImage":true,"isUserOverlayColor":true,"minHeight":245,"gradient":"dark-gradient","contentPosition":"bottom left","className":"wydegrid-hover-cover","style":{"spacing":{"padding":{"top":"16px","bottom":"16px","left":"20px","right":"20px"}}},"layout":{"type":"constrained"}} -->
                    <div class="wp-block-cover has-custom-content-position is-position-bottom-left wydegrid-hover-cover" style="padding-top:16px;padding-right:20px;padding-bottom:16px;padding-left:20px;min-height:245px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim has-background-gradient has-dark-gradient-gradient-background"></span>
                        <div class="wp-block-cover__inner-container"><!-- wp:post-terms {"term":"category","className":"is-style-categories-background-with-round"} /-->

                            <!-- wp:post-title {"level":4,"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"},":hover":{"color":{"text":"var:preset|color|primary"}}}},"typography":{"fontSize":"20px","textTransform":"uppercase"},"spacing":{"margin":{"top":"0","bottom":"0"}}}} /-->

                            <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                            <div class="wp-block-group"><!-- wp:post-author-name {"className":"is-style-author-name-with-white-icon","style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}},"typography":{"textTransform":"capitalize"}},"textColor":"light-color"} /-->

                                <!-- wp:post-date {"format":"M j, Y","className":"is-style-post-date-with-white-icon","style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}}},"textColor":"light-color"} /-->
                            </div>
                            <!-- /wp:group -->
                        </div>
                    </div>
                    <!-- /wp:cover -->
                    <!-- /wp:post-template -->

                    <!-- wp:query-no-results -->
                    <!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
                    <p></p>
                    <!-- /wp:paragraph -->
                    <!-- /wp:query-no-results -->
                </div>
                <!-- /wp:query -->
            </div>
            <!-- /wp:column -->
        </div>
        <!-- /wp:columns -->
    </div>
    <!-- /wp:group -->

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

    <!-- wp:group {"metadata":{"categories":["wydegrid"],"patternName":"wydegrid/full-image-post-list-with-sidebar","name":"Post List with Sidebar and Full Featured Image"},"style":{"spacing":{"padding":{"top":"0px","bottom":"28px","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
    <div class="wp-block-group" style="margin-top:0;margin-bottom:0;padding-top:0px;padding-right:var(--wp--preset--spacing--40);padding-bottom:28px;padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns -->
        <div class="wp-block-columns"><!-- wp:column {"width":"68%"} -->
            <div class="wp-block-column" style="flex-basis:68%"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"3px"}},"spacing":{"padding":{"top":"5px","bottom":"5px","left":"20px","right":"20px"}}},"backgroundColor":"background","layout":{"type":"constrained","justifyContent":"left","contentSize":"100%"}} -->
                <div class="wp-block-group has-background-background-color has-background" style="border-left-color:var(--wp--preset--color--primary);border-left-width:3px;padding-top:5px;padding-right:20px;padding-bottom:5px;padding-left:20px"><!-- wp:heading {"textAlign":"left","level":4,"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"textTransform":"uppercase","fontSize":"24px"}},"textColor":"primary"} -->
                    <h4 class="wp-block-heading has-text-align-left has-primary-color has-text-color has-link-color" style="font-size:24px;text-transform:uppercase"><?php esc_html_e('Must Recent', 'wydegrid') ?></h4>
                    <!-- /wp:heading -->
                </div>
                <!-- /wp:group -->

                <!-- wp:query {"queryId":6,"query":{"perPage":"5","pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false}} -->
                <div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"default","columnCount":"2"}} -->
                    <!-- wp:group {"className":"is-style-wydegrid-boxshadow","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"backgroundColor":"light-color","layout":{"type":"constrained"}} -->
                    <div class="wp-block-group is-style-wydegrid-boxshadow has-light-color-background-color has-background" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:post-featured-image {"isLink":true,"height":"460px"} /-->

                        <!-- wp:group {"style":{"spacing":{"padding":{"top":"28px","bottom":"28px","left":"28px","right":"28px"},"margin":{"top":"0","bottom":"0"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained"}} -->
                        <div class="wp-block-group" style="margin-top:0;margin-bottom:0;padding-top:28px;padding-right:28px;padding-bottom:28px;padding-left:28px"><!-- wp:post-terms {"term":"category","className":"is-style-categories-background-with-round"} /-->

                            <!-- wp:post-title {"level":3,"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|heading-color"},":hover":{"color":{"text":"var:preset|color|secondary"}}}},"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":"1.3","textTransform":"uppercase","fontSize":"24px"}}} /-->

                            <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                            <div class="wp-block-group"><!-- wp:post-author-name {"className":"is-style-author-name-with-icon","style":{"typography":{"textTransform":"capitalize"}}} /-->

                                <!-- wp:post-date {"format":"M j, Y","className":"is-style-post-date-with-icon"} /-->
                            </div>
                            <!-- /wp:group -->

                            <!-- wp:post-excerpt {"excerptLength":45,"style":{"spacing":{"margin":{"top":"20px"}}},"fontSize":"normal"} /-->

                            <!-- wp:group {"style":{"border":{"top":{"color":"var:preset|color|border-color","width":"1px"}},"spacing":{"margin":{"top":"20px"}}},"layout":{"type":"flex","orientation":"vertical"}} -->
                            <div class="wp-block-group" style="border-top-color:var(--wp--preset--color--border-color);border-top-width:1px;margin-top:20px"><!-- wp:read-more {"content":"Continue Reading","className":"is-style-readmore-hover-secondary-fill","style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"}}},"spacing":{"padding":{"top":"10px","bottom":"10px","left":"16px","right":"16px"},"margin":{"top":"20px"}}},"backgroundColor":"primary","textColor":"light-color"} /--></div>
                            <!-- /wp:group -->
                        </div>
                        <!-- /wp:group -->
                    </div>
                    <!-- /wp:group -->
                    <!-- /wp:post-template -->

                    <!-- wp:query-pagination {"paginationArrow":"arrow","showLabel":false,"className":"wydegrid-pagination","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"24px"}},"layout":{"type":"flex","justifyContent":"center"}} -->
                    <!-- wp:query-pagination-previous /-->

                    <!-- wp:query-pagination-numbers /-->

                    <!-- wp:query-pagination-next /-->
                    <!-- /wp:query-pagination -->

                    <!-- wp:query-no-results -->
                    <!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
                    <p></p>
                    <!-- /wp:paragraph -->
                    <!-- /wp:query-no-results -->
                </div>
                <!-- /wp:query -->
            </div>
            <!-- /wp:column -->

            <!-- wp:column {"width":""} -->
            <div class="wp-block-column"><!-- wp:template-part {"slug":"sidebar","theme":"wydegrid","area":"uncategorized"} /--></div>
            <!-- /wp:column -->
        </div>
        <!-- /wp:columns -->
    </div>
    <!-- /wp:group -->

    <!-- wp:group {"metadata":{"categories":["wydegrid"],"patternName":"wydegrid/missed-posts","name":"You May Missed Posts"},"style":{"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
    <div class="wp-block-group" style="padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:group {"style":{"border":{"left":{"color":"var:preset|color|primary","width":"3px"}},"spacing":{"padding":{"top":"5px","bottom":"5px","left":"20px","right":"20px"}}},"backgroundColor":"background","layout":{"type":"constrained","justifyContent":"left","contentSize":"100%"}} -->
        <div class="wp-block-group has-background-background-color has-background" style="border-left-color:var(--wp--preset--color--primary);border-left-width:3px;padding-top:5px;padding-right:20px;padding-bottom:5px;padding-left:20px"><!-- wp:heading {"textAlign":"left","level":4,"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"textTransform":"uppercase","fontSize":"24px"}},"textColor":"primary"} -->
            <h4 class="wp-block-heading has-text-align-left has-primary-color has-text-color has-link-color" style="font-size:24px;text-transform:uppercase"><?php esc_html_e('You May Missed', 'wydegrid') ?></h4>
            <!-- /wp:heading -->
        </div>
        <!-- /wp:group -->

        <!-- wp:query {"queryId":6,"query":{"perPage":"3","pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false}} -->
        <div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":"3"}} -->
            <!-- wp:cover {"useFeaturedImage":true,"isUserOverlayColor":true,"minHeight":280,"gradient":"dark-gradient","contentPosition":"bottom left","className":"wydegrid-hover-cover","style":{"spacing":{"padding":{"left":"16px","right":"16px","top":"16px","bottom":"16px"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-cover has-custom-content-position is-position-bottom-left wydegrid-hover-cover" style="padding-top:16px;padding-right:16px;padding-bottom:16px;padding-left:16px;min-height:280px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim has-background-gradient has-dark-gradient-gradient-background"></span>
                <div class="wp-block-cover__inner-container"><!-- wp:post-title {"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|light-color"},":hover":{"color":{"text":"var:preset|color|primary"}}}},"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"24px"}}} /-->

                    <!-- wp:post-terms {"term":"category","className":"is-style-categories-background-with-round"} /-->

                    <!-- wp:post-date {"format":"M j, Y"} /-->
                </div>
            </div>
            <!-- /wp:cover -->
            <!-- /wp:post-template -->

            <!-- wp:query-no-results -->
            <!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
            <p></p>
            <!-- /wp:paragraph -->
            <!-- /wp:query-no-results -->
        </div>
        <!-- /wp:query -->
    </div>
    <!-- /wp:group -->
</main>
<!-- /wp:group -->