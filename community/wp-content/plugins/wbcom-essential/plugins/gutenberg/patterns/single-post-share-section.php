<?php
/**
 * Single Post Share Section Pattern.
 *
 * A share section with social icons in outline style.
 *
 * @package    Wbcom_Essential
 * @subpackage Wbcom_Essential/plugins/gutenberg/patterns
 * @since      4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_block_pattern(
	'wbcom-essential/single-post-share-section',
	array(
		'title'       => __( 'Share Section', 'wbcom-essential' ),
		'description' => __( 'A share section with text and social media icons.', 'wbcom-essential' ),
		'categories'  => array( 'wbcom-essential-single-post' ),
		'keywords'    => array( 'share', 'social', 'icons', 'media' ),
		'content'     => '<!-- wp:wbcom-essential/divider {"style":"solid","width":100,"alignment":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"24px"} -->
<div style="height:24px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}}} -->
<p style="font-weight:600">Share this article</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%"><!-- wp:wbcom-essential/social-icons {"style":"outline","alignment":"right","useThemeColors":true} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
	)
);
