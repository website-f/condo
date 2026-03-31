<?php
/**
 * Single Post Author Bio Pattern.
 *
 * A styled author bio section with divider and testimonial block.
 *
 * @package    Wbcom_Essential
 * @subpackage Wbcom_Essential/plugins/gutenberg/patterns
 * @since      4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_block_pattern(
	'wbcom-essential/single-post-author-bio',
	array(
		'title'       => __( 'Author Bio Section', 'wbcom-essential' ),
		'description' => __( 'A polished author bio section with divider and testimonial-style layout.', 'wbcom-essential' ),
		'categories'  => array( 'wbcom-essential-single-post' ),
		'keywords'    => array( 'author', 'bio', 'profile', 'about' ),
		'content'     => '<!-- wp:wbcom-essential/divider {"style":"solid","width":100,"alignment":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"24px"} -->
<div style="height:24px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/heading {"text":"About the Author","tag":"h3","alignment":"left","useThemeColors":true} /-->

<!-- wp:spacer {"height":"16px"} -->
<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wbcom-essential/testimonial {"showRating":false,"layout":"row","useThemeColors":true} /-->',
	)
);
