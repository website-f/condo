<?php
/**
 * Icon Box Block - Server-Side Render
 *
 * @package wbcom-essential
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract attributes with defaults.
$use_theme_colors  = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$icon              = ! empty( $attributes['icon'] ) ? $attributes['icon'] : 'star-filled';
$icon_type         = ! empty( $attributes['iconType'] ) ? $attributes['iconType'] : 'dashicon';
$custom_icon_url   = ! empty( $attributes['customIconUrl'] ) ? $attributes['customIconUrl'] : '';
$title             = ! empty( $attributes['title'] ) ? $attributes['title'] : '';
$description       = ! empty( $attributes['description'] ) ? $attributes['description'] : '';
$title_tag         = ! empty( $attributes['titleTag'] ) ? $attributes['titleTag'] : 'h3';
$link_url          = ! empty( $attributes['linkUrl'] ) ? $attributes['linkUrl'] : '';
$link_target       = ! empty( $attributes['linkTarget'] ) ? $attributes['linkTarget'] : false;
$layout            = ! empty( $attributes['layout'] ) ? $attributes['layout'] : 'top';
$alignment         = ! empty( $attributes['alignment'] ) ? $attributes['alignment'] : 'center';
$icon_size         = isset( $attributes['iconSize'] ) ? absint( $attributes['iconSize'] ) : 48;
$icon_color        = ! empty( $attributes['iconColor'] ) ? $attributes['iconColor'] : '';
$icon_bg_color     = ! empty( $attributes['iconBgColor'] ) ? $attributes['iconBgColor'] : '';
$icon_border_radius = isset( $attributes['iconBorderRadius'] ) ? absint( $attributes['iconBorderRadius'] ) : 50;
$icon_padding      = isset( $attributes['iconPadding'] ) ? absint( $attributes['iconPadding'] ) : 16;
$title_color       = ! empty( $attributes['titleColor'] ) ? $attributes['titleColor'] : '';
$description_color = ! empty( $attributes['descriptionColor'] ) ? $attributes['descriptionColor'] : '';
$hover_icon_color  = ! empty( $attributes['hoverIconColor'] ) ? $attributes['hoverIconColor'] : '';
$hover_icon_bg     = ! empty( $attributes['hoverIconBgColor'] ) ? $attributes['hoverIconBgColor'] : '';
$hover_title_color = ! empty( $attributes['hoverTitleColor'] ) ? $attributes['hoverTitleColor'] : '';
$icon_spacing      = isset( $attributes['iconSpacing'] ) ? absint( $attributes['iconSpacing'] ) : 16;
$title_spacing     = isset( $attributes['titleSpacing'] ) ? absint( $attributes['titleSpacing'] ) : 12;

// Sanitize title tag.
$allowed_tags = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p' );
if ( ! in_array( $title_tag, $allowed_tags, true ) ) {
	$title_tag = 'h3';
}

// Build inline styles - non-color styles always applied.
$inline_styles = array(
	'--icon-size'          => $icon_size . 'px',
	'--icon-border-radius' => $icon_border_radius . '%',
	'--icon-padding'       => $icon_padding . 'px',
	'--icon-spacing'       => $icon_spacing . 'px',
	'--title-spacing'      => $title_spacing . 'px',
);

// Only add color styles when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--icon-color']        = $icon_color;
	$inline_styles['--icon-bg-color']     = $icon_bg_color;
	$inline_styles['--title-color']       = $title_color;
	$inline_styles['--description-color'] = $description_color;
	$inline_styles['--hover-icon-color']  = $hover_icon_color;
	$inline_styles['--hover-icon-bg']     = $hover_icon_bg;
	$inline_styles['--hover-title-color'] = $hover_title_color;
}

// Filter out empty values.
$inline_styles = array_filter( $inline_styles );

// Convert to style string.
$style_string = '';
foreach ( $inline_styles as $property => $value ) {
	$style_string .= esc_attr( $property ) . ':' . esc_attr( $value ) . ';';
}

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-icon-box layout-' . esc_attr( $layout ) . ' align-' . esc_attr( $alignment );
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Build wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $wrapper_classes,
		'style' => $style_string,
	)
);

// Start output.
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $link_url ) : ?>
		<a href="<?php echo esc_url( $link_url ); ?>" class="wbcom-essential-icon-box__link"<?php echo $link_target ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
	<?php endif; ?>

	<div class="wbcom-essential-icon-box__icon">
		<?php if ( 'custom' === $icon_type && $custom_icon_url ) : ?>
			<img src="<?php echo esc_url( $custom_icon_url ); ?>" alt="" />
		<?php else : ?>
			<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
		<?php endif; ?>
	</div>

	<div class="wbcom-essential-icon-box__content">
		<?php if ( $title ) : ?>
			<<?php echo esc_attr( $title_tag ); ?> class="wbcom-essential-icon-box__title">
				<?php echo wp_kses_post( $title ); ?>
			</<?php echo esc_attr( $title_tag ); ?>>
		<?php endif; ?>

		<?php if ( $description ) : ?>
			<p class="wbcom-essential-icon-box__description">
				<?php echo wp_kses_post( $description ); ?>
			</p>
		<?php endif; ?>
	</div>

	<?php if ( $link_url ) : ?>
		</a>
	<?php endif; ?>
</div>
