<?php
/**
 * Blog Listing Pattern.
 *
 * Clean blog index with side-by-side post layout and pagination.
 *
 * @package    Wbcom_Essential
 * @subpackage Wbcom_Essential/plugins/gutenberg/patterns
 * @since      4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_block_pattern(
	'wbcom-essential/blog-listing',
	array(
		'title'       => __( 'Blog Listing', 'wbcom-essential' ),
		'description' => __( 'A clean blog listing with side-by-side post cards and pagination.', 'wbcom-essential' ),
		'categories'  => array( 'wbcom-essential-magazine' ),
		'keywords'    => array( 'blog', 'listing', 'posts', 'pagination' ),
		'content'     => '<!-- wp:wbcom-essential/heading {"text":"Blog","alignment":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"24px"} -->
<div style="height:24px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/posts-revolution {"displayType":"posts_type4","postsPerPage":8,"columns":2,"showExcerpt":true,"excerptLength":120,"enablePagination":true,"paginationType":"numeric","useThemeColors":true} /-->',
	)
);
