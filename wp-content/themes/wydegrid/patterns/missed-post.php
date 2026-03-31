<?php

/**
 * Title: You May Missed Posts
 * Slug: wydegrid/missed-posts
 * Categories: wydegrid
 */
?>
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