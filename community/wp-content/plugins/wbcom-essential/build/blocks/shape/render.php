<?php
/**
 * Server-side render for Shape block.
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

// Extract attributes.
$use_theme_colors = $attributes['useThemeColors'] ?? false;
$point1           = $attributes['point1'] ?? 30;
$point2           = $attributes['point2'] ?? 70;
$point3           = $attributes['point3'] ?? 70;
$point4           = $attributes['point4'] ?? 30;
$point5           = $attributes['point5'] ?? 30;
$point6           = $attributes['point6'] ?? 30;
$point7           = $attributes['point7'] ?? 70;
$point8           = $attributes['point8'] ?? 70;
$rotation         = $attributes['rotation'] ?? 0;
$width            = $attributes['width'] ?? 200;
$height           = $attributes['height'] ?? 200;
$background_color = $attributes['backgroundColor'] ?? '#3182ce';
$gradient_from    = $attributes['gradientFrom'] ?? '';
$gradient_to      = $attributes['gradientTo'] ?? '';
$gradient_angle   = $attributes['gradientAngle'] ?? 135;
$icon             = $attributes['icon'] ?? '';
$icon_color       = $attributes['iconColor'] ?? '#ffffff';
$icon_size        = $attributes['iconSize'] ?? 48;
$icon_rotation    = $attributes['iconRotation'] ?? 0;
$svg_width        = $attributes['svgWidth'] ?? 0;
$svg_height       = $attributes['svgHeight'] ?? 0;
$icon_bg_color    = $attributes['iconBackgroundColor'] ?? '';
$icon_bg_size     = $attributes['iconBackgroundSize'] ?? 80;
$icon_bg_radius   = $attributes['iconBackgroundRadius'] ?? 50;
$link_url         = $attributes['linkUrl'] ?? '';
$link_new_tab     = $attributes['linkNewTab'] ?? false;
$alignment        = $attributes['alignment'] ?? 'center';
$hover_animation  = $attributes['hoverAnimation'] ?? '';

// Border attributes.
$border_width = $attributes['borderWidth'] ?? 0;
$border_style = $attributes['borderStyle'] ?? 'solid';
$border_color = $attributes['borderColor'] ?? '';

// Box shadow attributes.
$box_shadow_enabled    = $attributes['boxShadowEnabled'] ?? false;
$box_shadow_horizontal = $attributes['boxShadowHorizontal'] ?? 0;
$box_shadow_vertical   = $attributes['boxShadowVertical'] ?? 10;
$box_shadow_blur       = $attributes['boxShadowBlur'] ?? 20;
$box_shadow_spread     = $attributes['boxShadowSpread'] ?? 0;
$box_shadow_color      = $attributes['boxShadowColor'] ?? 'rgba(0,0,0,0.15)';

// Build border-radius value.
$border_radius = sprintf(
	'%d%% %d%% %d%% %d%% / %d%% %d%% %d%% %d%%',
	$point1,
	$point2,
	$point3,
	$point4,
	$point5,
	$point6,
	$point7,
	$point8
);

// Build background style.
$background_style = '';
if ( $gradient_from && $gradient_to ) {
	$background_style = sprintf(
		'linear-gradient(%ddeg, %s, %s)',
		$gradient_angle,
		esc_attr( $gradient_from ),
		esc_attr( $gradient_to )
	);
} else {
	$background_style = esc_attr( $background_color );
}

// Build border style.
$border_css = '';
if ( $border_width > 0 && $border_color ) {
	$border_css = sprintf(
		'%dpx %s %s',
		$border_width,
		esc_attr( $border_style ),
		esc_attr( $border_color )
	);
}

// Build box shadow style.
$box_shadow_css = 'none';
if ( $box_shadow_enabled ) {
	$box_shadow_css = sprintf(
		'%dpx %dpx %dpx %dpx %s',
		$box_shadow_horizontal,
		$box_shadow_vertical,
		$box_shadow_blur,
		$box_shadow_spread,
		esc_attr( $box_shadow_color )
	);
}

// SVG dimensions (use icon size if not set).
$svg_width_css  = $svg_width > 0 ? $svg_width . 'px' : 'auto';
$svg_height_css = $svg_height > 0 ? $svg_height . 'px' : 'auto';

// Icon background.
$icon_bg_css = $icon_bg_color ? esc_attr( $icon_bg_color ) : 'transparent';

// Build CSS custom properties - dimensions always applied.
$style_vars = sprintf(
	'--shape-width: %dpx; --shape-height: %dpx; --shape-radius: %s; --shape-rotation: %ddeg; --shape-icon-size: %dpx; --shape-icon-rotation: %ddeg; --shape-align: %s; --shape-svg-width: %s; --shape-svg-height: %s; --shape-icon-bg-size: %dpx; --shape-icon-bg-radius: %d%%;',
	$width,
	$height,
	esc_attr( $border_radius ),
	$rotation,
	$icon_size,
	$icon_rotation,
	esc_attr( $alignment ),
	esc_attr( $svg_width_css ),
	esc_attr( $svg_height_css ),
	$icon_bg_size,
	$icon_bg_radius
);

// Add color CSS variables only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$style_vars .= sprintf( ' --shape-background: %s;', $background_style );
	$style_vars .= sprintf( ' --shape-icon-color: %s;', esc_attr( $icon_color ) );
	$style_vars .= sprintf( ' --shape-border: %s;', $border_css ? esc_attr( $border_css ) : 'none' );
	$style_vars .= sprintf( ' --shape-box-shadow: %s;', esc_attr( $box_shadow_css ) );
	$style_vars .= sprintf( ' --shape-icon-bg: %s;', $icon_bg_css );
}

// Animation class.
$animation_class = $hover_animation ? 'wbcom-shape-animation-' . esc_attr( $hover_animation ) : '';

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-shape';
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $wrapper_classes,
		'style' => $style_vars,
	)
);
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<div class="wbcom-shape-wrapper">
		<div class="wbcom-shape <?php echo esc_attr( $animation_class ); ?>">
			<?php if ( $icon ) : ?>
				<?php if ( $icon_bg_color ) : ?>
					<span class="wbcom-shape-icon-wrapper">
						<span class="wbcom-shape-icon dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
					</span>
				<?php else : ?>
					<span class="wbcom-shape-icon dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $link_url ) : ?>
				<a
					href="<?php echo esc_url( $link_url ); ?>"
					class="wbcom-shape-link"
					<?php echo $link_new_tab ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
					aria-label="<?php esc_attr_e( 'Shape link', 'wbcom-essential' ); ?>"
				></a>
			<?php endif; ?>
		</div>
	</div>
</div>
