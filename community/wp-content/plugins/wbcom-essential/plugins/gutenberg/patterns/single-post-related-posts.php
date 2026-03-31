<?php
/**
 * Single Post Related Posts Pattern.
 *
 * A related posts carousel section for use after article content.
 *
 * @package    Wbcom_Essential
 * @subpackage Wbcom_Essential/plugins/gutenberg/patterns
 * @since      4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_block_pattern(
	'wbcom-essential/single-post-related-posts',
	array(
		'title'       => __( 'Related Posts Carousel', 'wbcom-essential' ),
		'description' => __( 'A related posts carousel section with divider and heading.', 'wbcom-essential' ),
		'categories'  => array( 'wbcom-essential-single-post' ),
		'keywords'    => array( 'related', 'posts', 'carousel', 'recommended' ),
		'content'     => '<!-- wp:wbcom-essential/divider {"style":"solid","width":100,"alignment":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"32px"} -->
<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/heading {"text":"You May Also Like","tag":"h3","alignment":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"16px"} -->
<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/posts-carousel {"slidesToShow":3,"showExcerpt":false,"showMeta":true,"useThemeColors":true} /-->',
	)
);
