<?php
/**
 * Divider Block - Server-Side Render
 *
 * @package wbcom-essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$use_theme_colors = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$style            = ! empty( $attributes['style'] ) ? $attributes['style'] : 'solid';
$width         = isset( $attributes['width'] ) ? absint( $attributes['width'] ) : 100;
$width_unit    = ! empty( $attributes['widthUnit'] ) ? $attributes['widthUnit'] : '%';
$thickness     = isset( $attributes['thickness'] ) ? absint( $attributes['thickness'] ) : 2;
$color         = ! empty( $attributes['color'] ) ? $attributes['color'] : '';
$alignment     = ! empty( $attributes['alignment'] ) ? $attributes['alignment'] : 'center';
$margin_top    = isset( $attributes['marginTop'] ) ? absint( $attributes['marginTop'] ) : 20;
$margin_bottom = isset( $attributes['marginBottom'] ) ? absint( $attributes['marginBottom'] ) : 20;
$show_icon     = ! empty( $attributes['showIcon'] ) ? $attributes['showIcon'] : false;
$icon          = ! empty( $attributes['icon'] ) ? $attributes['icon'] : 'star-filled';
$icon_size     = isset( $attributes['iconSize'] ) ? absint( $attributes['iconSize'] ) : 20;
$icon_color    = ! empty( $attributes['iconColor'] ) ? $attributes['iconColor'] : '';

// Build inline styles - non-color styles always applied.
$inline_styles = array(
	'--divider-width'     => $width . $width_unit,
	'--divider-thickness' => $thickness . 'px',
	'--margin-top'        => $margin_top . 'px',
	'--margin-bottom'     => $margin_bottom . 'px',
	'--icon-size'         => $icon_size . 'px',
);

// Only add color styles when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--divider-color'] = $color;
	$inline_styles['--icon-color']    = $icon_color ? $icon_color : $color;
}

$inline_styles = array_filter( $inline_styles );
$style_string  = '';
foreach ( $inline_styles as $property => $value ) {
	$style_string .= esc_attr( $property ) . ':' . esc_attr( $value ) . ';';
}

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-divider align-' . esc_attr( $alignment ) . ' style-' . esc_attr( $style );
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $wrapper_classes,
		'style' => $style_string,
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $show_icon ) : ?>
		<span class="wbcom-essential-divider__line"></span>
		<span class="wbcom-essential-divider__icon">
			<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
		</span>
		<span class="wbcom-essential-divider__line"></span>
	<?php else : ?>
		<span class="wbcom-essential-divider__line wbcom-essential-divider__line--full"></span>
	<?php endif; ?>
</div>
