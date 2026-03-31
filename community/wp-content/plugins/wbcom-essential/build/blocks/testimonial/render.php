<?php
/**
 * Server-side render for Testimonial block.
 *
 * @package WBCOM_Essential
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract attributes with defaults.
$use_theme_colors      = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$testimonial_content  = $attributes['content'] ?? '';
$author_name          = $attributes['authorName'] ?? '';
$author_role          = $attributes['authorRole'] ?? '';
$image_id             = $attributes['imageId'] ?? 0;
$image_size           = $attributes['imageSize'] ?? 'thumbnail';
$show_rating          = $attributes['showRating'] ?? true;
$rating               = $attributes['rating'] ?? 5;
$layout               = $attributes['layout'] ?? 'column';
$author_info_direction = $attributes['authorInfoDirection'] ?? 'row';
$text_align           = $attributes['textAlign'] ?? 'center';
$background_color     = $attributes['backgroundColor'] ?? '#ffffff';
$item_max_width       = $attributes['itemMaxWidth'] ?? 600;
$border_radius        = $attributes['borderRadius'] ?? 12;
$border_width         = $attributes['borderWidth'] ?? 0;
$border_style         = $attributes['borderStyle'] ?? 'solid';
$border_color         = $attributes['borderColor'] ?? '#e2e8f0';
$quote_color          = $attributes['quoteColor'] ?? '#4a5568';
$name_color           = $attributes['nameColor'] ?? '#1a202c';
$role_color           = $attributes['roleColor'] ?? '#718096';
$rating_color         = $attributes['ratingColor'] ?? '#f6ad55';

// New attributes.
$padding               = $attributes['padding'] ?? 32;
$box_shadow            = $attributes['boxShadow'] ?? true;
$box_shadow_color      = $attributes['boxShadowColor'] ?? 'rgba(0, 0, 0, 0.08)';
$box_shadow_blur       = $attributes['boxShadowBlur'] ?? 20;
$box_shadow_spread     = $attributes['boxShadowSpread'] ?? 0;
$box_shadow_horizontal = $attributes['boxShadowHorizontal'] ?? 0;
$box_shadow_vertical   = $attributes['boxShadowVertical'] ?? 4;
$avatar_size           = $attributes['avatarSize'] ?? 60;
$avatar_border_radius  = $attributes['avatarBorderRadius'] ?? 50;
$avatar_border_width   = $attributes['avatarBorderWidth'] ?? 0;
$avatar_border_style   = $attributes['avatarBorderStyle'] ?? 'solid';
$avatar_border_color   = $attributes['avatarBorderColor'] ?? '#e2e8f0';
$avatar_box_shadow     = $attributes['avatarBoxShadow'] ?? false;
$quote_icon_size       = $attributes['quoteIconSize'] ?? 64;
$quote_icon_color      = $attributes['quoteIconColor'] ?? '#1d76da';
$quote_font_size       = $attributes['quoteFontSize'] ?? 18;
$name_font_size        = $attributes['nameFontSize'] ?? 16;
$role_font_size        = $attributes['roleFontSize'] ?? 14;
$spacing               = $attributes['spacing'] ?? 24;
$content_arrow         = $attributes['contentArrow'] ?? 'none';
$content_arrow_color   = $attributes['contentArrowColor'] ?? '#ffffff';
$content_arrow_size    = $attributes['contentArrowSize'] ?? 15;

// Build box shadow value.
$box_shadow_value = $box_shadow
	? sprintf(
		'%dpx %dpx %dpx %dpx %s',
		$box_shadow_horizontal,
		$box_shadow_vertical,
		$box_shadow_blur,
		$box_shadow_spread,
		$box_shadow_color
	)
	: 'none';

// Build CSS variables - layout always, colors conditionally.
$css_vars = array(
	// Layout variables (always applied).
	'--item-max-width'        => $item_max_width . 'px',
	'--border-radius'         => $border_radius . 'px',
	'--border-width'          => $border_width . 'px',
	'--border-style'          => $border_style,
	'--padding'               => $padding . 'px',
	'--box-shadow'            => $box_shadow_value,
	'--avatar-size'           => $avatar_size . 'px',
	'--avatar-border-radius'  => $avatar_border_radius . '%',
	'--avatar-border-width'   => $avatar_border_width . 'px',
	'--avatar-border-style'   => $avatar_border_style,
	'--avatar-box-shadow'     => $avatar_box_shadow ? '0 4px 10px rgba(0, 0, 0, 0.15)' : 'none',
	'--quote-icon-size'       => $quote_icon_size . 'px',
	'--quote-font-size'       => $quote_font_size . 'px',
	'--name-font-size'        => $name_font_size . 'px',
	'--role-font-size'        => $role_font_size . 'px',
	'--spacing'               => $spacing . 'px',
	'--arrow-size'            => $content_arrow_size . 'px',
);

// Add color variables only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$css_vars['--bg-color']             = $background_color;
	$css_vars['--border-color']         = $border_color;
	$css_vars['--avatar-border-color']  = $avatar_border_color;
	$css_vars['--quote-icon-color']     = $quote_icon_color;
	$css_vars['--quote-color']          = $quote_color;
	$css_vars['--name-color']           = $name_color;
	$css_vars['--role-color']           = $role_color;
	$css_vars['--rating-color']         = $rating_color;
	$css_vars['--arrow-color']          = $content_arrow_color;
}

$style_string = '';
foreach ( $css_vars as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Build class names.
$class_names = sprintf(
	'wbcom-essential-testimonial layout-%s info-%s text-%s arrow-%s%s',
	esc_attr( $layout ),
	esc_attr( $author_info_direction ),
	esc_attr( $text_align ),
	esc_attr( $content_arrow ),
	$use_theme_colors ? ' use-theme-colors' : ''
);

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $class_names,
		'style' => $style_string,
	)
);

// Get author image.
$author_image = '';
if ( $image_id ) {
	$author_image = wp_get_attachment_image(
		$image_id,
		$image_size,
		false,
		array(
			'class' => 'wbcom-testimonial-avatar-img',
			'alt'   => esc_attr( $author_name ),
		)
	);
}

/**
 * Render star rating HTML.
 *
 * @param int $rating Rating value (1-5).
 * @return string HTML string.
 */
if ( ! function_exists( 'wbcom_render_testimonial_stars' ) ) {
function wbcom_render_testimonial_stars( $rating ) {
	$output = '';
	for ( $i = 1; $i <= 5; $i++ ) {
		$filled = $i <= $rating;
		$class  = $filled ? 'filled' : 'empty';
		$output .= sprintf(
			'<span class="star %s">★</span>',
			esc_attr( $class )
		);
	}
	return $output;
}
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wbcom-testimonial-content">
		<?php if ( $show_rating ) : ?>
			<div class="wbcom-testimonial-rating">
				<?php echo wbcom_render_testimonial_stars( $rating ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $testimonial_content ) ) : ?>
			<div class="wbcom-testimonial-quote">
				<span class="quote-mark">"</span>
				<p><?php echo wp_kses_post( $testimonial_content ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<div class="wbcom-testimonial-author">
		<?php if ( $author_image ) : ?>
			<div class="wbcom-testimonial-avatar">
				<?php echo $author_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>

		<div class="wbcom-testimonial-info">
			<?php if ( ! empty( $author_name ) ) : ?>
				<span class="wbcom-testimonial-name"><?php echo esc_html( $author_name ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $author_role ) ) : ?>
				<span class="wbcom-testimonial-role"><?php echo esc_html( $author_role ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</div>
