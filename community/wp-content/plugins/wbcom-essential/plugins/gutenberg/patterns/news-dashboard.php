<?php
/**
 * News Dashboard Pattern.
 *
 * Multi-section news dashboard with varied post layouts.
 *
 * @package    Wbcom_Essential
 * @subpackage Wbcom_Essential/plugins/gutenberg/patterns
 * @since      4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_block_pattern(
	'wbcom-essential/news-dashboard',
	array(
		'title'       => __( 'News Dashboard', 'wbcom-essential' ),
		'description' => __( 'A multi-section news dashboard with hero, category spotlight columns, and more stories.', 'wbcom-essential' ),
		'categories'  => array( 'wbcom-essential-magazine' ),
		'keywords'    => array( 'news', 'dashboard', 'sections', 'magazine' ),
		'content'     => '<!-- wp:wbcom-essential/heading {"headingText":"Top Stories","textAlign":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"16px"} -->
<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/posts-revolution {"displayType":"posts_type1","postsPerPage":5,"useThemeColors":true,"sectionLabel":"Top Stories \u2014 All Categories"} /-->

<!-- wp:spacer {"height":"48px"} -->
<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/divider {"useThemeColors":true} /-->

<!-- wp:spacer {"height":"48px"} -->
<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/heading {"headingText":"Category Spotlight","textAlign":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"16px"} -->
<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:wbcom-essential/posts-revolution {"displayType":"posts_type3","postsPerPage":3,"columns":1,"showExcerpt":false,"useThemeColors":true,"sectionLabel":"Category Section 1 \u2014 Select a Category"} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:wbcom-essential/posts-revolution {"displayType":"posts_type3","postsPerPage":3,"columns":1,"showExcerpt":false,"useThemeColors":true,"sectionLabel":"Category Section 2 \u2014 Select a Category"} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:wbcom-essential/posts-revolution {"displayType":"posts_type3","postsPerPage":3,"columns":1,"showExcerpt":false,"useThemeColors":true,"sectionLabel":"Category Section 3 \u2014 Select a Category"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:spacer {"height":"48px"} -->
<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/divider {"useThemeColors":true} /-->

<!-- wp:spacer {"height":"48px"} -->
<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/heading {"headingText":"More Stories","textAlign":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"16px"} -->
<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/posts-revolution {"displayType":"posts_type4","postsPerPage":6,"columns":2,"showExcerpt":true,"excerptLength":80,"enablePagination":true,"paginationType":"numeric","useThemeColors":true,"sectionLabel":"More Stories"} /-->

<!-- wp:spacer {"height":"48px"} -->
<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/heading {"headingText":"Explore Categories","textAlign":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"16px"} -->
<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/category-grid {"columns":4,"maxCategories":8,"showPostCount":true,"showImage":true,"useThemeColors":true} /-->',
	)
);
