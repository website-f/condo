<?php
/**
 * Blog Timeline Pattern.
 *
 * A chronological two-column timeline layout for blog posts.
 *
 * @package    Wbcom_Essential
 * @subpackage Wbcom_Essential/plugins/gutenberg/patterns
 * @since      4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_block_pattern(
	'wbcom-essential/blog-timeline',
	array(
		'title'       => __( 'Blog Timeline', 'wbcom-essential' ),
		'description' => __( 'A chronological two-column timeline layout for blog posts.', 'wbcom-essential' ),
		'categories'  => array( 'wbcom-essential-magazine' ),
		'keywords'    => array( 'blog', 'timeline', 'chronological', 'history' ),
		'content'     => '<!-- wp:wbcom-essential/heading {"headingText":"Our Story","textAlign":"center","useThemeColors":true} /-->

<!-- wp:spacer {"height":"24px"} -->
<div style="height:24px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/post-timeline {"postsPerPage":10,"showExcerpt":true,"excerptLength":120,"useThemeColors":true} /-->',
	)
);
