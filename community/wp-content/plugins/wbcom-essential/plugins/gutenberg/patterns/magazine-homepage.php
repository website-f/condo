<?php
/**
 * Magazine Homepage Pattern.
 *
 * Full news site homepage: ticker + hero slider + featured grid + category grid.
 *
 * @package    Wbcom_Essential
 * @subpackage Wbcom_Essential/plugins/gutenberg/patterns
 * @since      4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_block_pattern(
	'wbcom-essential/magazine-homepage',
	array(
		'title'       => __( 'Magazine Homepage', 'wbcom-essential' ),
		'description' => __( 'A full news site homepage with breaking news ticker, hero post slider, featured posts grid, and category grid.', 'wbcom-essential' ),
		'categories'  => array( 'wbcom-essential-magazine' ),
		'keywords'    => array( 'magazine', 'homepage', 'news', 'blog' ),
		'content'     => '<!-- wp:wbcom-essential/posts-ticker {"useThemeColors":true} /-->

<!-- wp:spacer {"height":"24px"} -->
<div style="height:24px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/post-slider {"useThemeColors":true,"postsPerPage":4} /-->

<!-- wp:spacer {"height":"48px"} -->
<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/heading {"headingText":"Latest Stories","textAlign":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"16px"} -->
<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/posts-revolution {"displayType":"posts_type6","postsPerPage":6,"columns":3,"useThemeColors":true,"sectionLabel":"Latest Stories \u2014 Select Categories to Filter"} /-->

<!-- wp:spacer {"height":"48px"} -->
<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/heading {"headingText":"Browse by Category","textAlign":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"16px"} -->
<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/category-grid {"columns":4,"maxCategories":8,"showPostCount":true,"showImage":true,"useThemeColors":true} /-->',
	)
);
