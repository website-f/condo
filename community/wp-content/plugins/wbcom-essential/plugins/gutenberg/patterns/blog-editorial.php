<?php
/**
 * Blog Editorial Pattern.
 *
 * Hero slider + magazine complex layout for editorial-style blogs.
 *
 * @package    Wbcom_Essential
 * @subpackage Wbcom_Essential/plugins/gutenberg/patterns
 * @since      4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_block_pattern(
	'wbcom-essential/blog-editorial',
	array(
		'title'       => __( 'Blog Editorial', 'wbcom-essential' ),
		'description' => __( 'An editorial-style blog layout with hero slider and magazine grid.', 'wbcom-essential' ),
		'categories'  => array( 'wbcom-essential-magazine' ),
		'keywords'    => array( 'blog', 'editorial', 'magazine', 'hero' ),
		'content'     => '<!-- wp:wbcom-essential/post-slider {"useThemeColors":true,"postsPerPage":3} /-->

<!-- wp:spacer {"height":"48px"} -->
<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/heading {"headingText":"Editor\'s Picks","textAlign":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"16px"} -->
<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/posts-revolution {"displayType":"posts_type6","postsPerPage":5,"columns":3,"showExcerpt":true,"excerptLength":100,"useThemeColors":true,"sectionLabel":"Editor\u0027s Picks \u2014 Select Categories to Filter"} /-->',
	)
);
