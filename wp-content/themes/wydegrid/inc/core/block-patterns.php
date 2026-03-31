<?php

/**
 * wydegrid: Block Patterns
 *
 * @since wydegrid 1.0.0
 */

/**
 * Registers pattern categories for wydegrid
 *
 * @since wydegrid 1.0.0
 *
 * @return void
 */
function wydegrid_register_pattern_category()
{
	$block_pattern_categories = array(
		'wydegrid' => array('label' => __('WYDEGRID PATTERNS', 'wydegrid')),
		'wydegrid-homes' => array('label' => __('WYDEGRID HOMES', 'wydegrid'))
	);

	$block_pattern_categories = apply_filters('wydegrid_block_pattern_categories', $block_pattern_categories);

	foreach ($block_pattern_categories as $name => $properties) {
		if (!WP_Block_Pattern_Categories_Registry::get_instance()->is_registered($name)) {
			register_block_pattern_category($name, $properties); // phpcs:ignore WPThemeReview.PluginTerritory.ForbiddenFunctions.editor_blocks_register_block_pattern_category
		}
	}
}
add_action('init', 'wydegrid_register_pattern_category', 9);
