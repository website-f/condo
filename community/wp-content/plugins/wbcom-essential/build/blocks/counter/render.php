<?php
/**
 * Counter Block - Server-Side Render
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
$use_theme_colors   = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$start_number       = isset( $attributes['startNumber'] ) ? absint( $attributes['startNumber'] ) : 0;
$end_number         = isset( $attributes['endNumber'] ) ? absint( $attributes['endNumber'] ) : 100;
$prefix             = ! empty( $attributes['prefix'] ) ? $attributes['prefix'] : '';
$suffix             = ! empty( $attributes['suffix'] ) ? $attributes['suffix'] : '';
$title              = ! empty( $attributes['title'] ) ? $attributes['title'] : '';
$duration           = isset( $attributes['duration'] ) ? absint( $attributes['duration'] ) : 2000;
$use_grouping       = isset( $attributes['useGrouping'] ) ? $attributes['useGrouping'] : true;
$layout             = ! empty( $attributes['layout'] ) ? $attributes['layout'] : 'vertical';
$alignment          = ! empty( $attributes['alignment'] ) ? $attributes['alignment'] : 'center';
$number_color       = ! empty( $attributes['numberColor'] ) ? $attributes['numberColor'] : '';
$title_color        = ! empty( $attributes['titleColor'] ) ? $attributes['titleColor'] : '';
$prefix_suffix_color = ! empty( $attributes['prefixSuffixColor'] ) ? $attributes['prefixSuffixColor'] : '';
$number_size        = isset( $attributes['numberSize'] ) ? absint( $attributes['numberSize'] ) : 48;
$title_size         = isset( $attributes['titleSize'] ) ? absint( $attributes['titleSize'] ) : 16;
$prefix_suffix_size = isset( $attributes['prefixSuffixSize'] ) ? absint( $attributes['prefixSuffixSize'] ) : 24;
$number_weight      = ! empty( $attributes['numberWeight'] ) ? $attributes['numberWeight'] : '700';
$title_spacing      = isset( $attributes['titleSpacing'] ) ? absint( $attributes['titleSpacing'] ) : 12;
$icon               = ! empty( $attributes['icon'] ) ? $attributes['icon'] : '';
$icon_type          = ! empty( $attributes['iconType'] ) ? $attributes['iconType'] : 'dashicon';
$custom_icon_url    = ! empty( $attributes['customIconUrl'] ) ? $attributes['customIconUrl'] : '';
$icon_size          = isset( $attributes['iconSize'] ) ? absint( $attributes['iconSize'] ) : 40;
$icon_color         = ! empty( $attributes['iconColor'] ) ? $attributes['iconColor'] : '';
$icon_spacing       = isset( $attributes['iconSpacing'] ) ? absint( $attributes['iconSpacing'] ) : 16;
$show_icon          = isset( $attributes['showIcon'] ) ? $attributes['showIcon'] : false;

// Build inline styles - non-color styles always applied.
$inline_styles = array(
	'--number-size'        => $number_size . 'px',
	'--title-size'         => $title_size . 'px',
	'--prefix-suffix-size' => $prefix_suffix_size . 'px',
	'--number-weight'      => $number_weight,
	'--title-spacing'      => $title_spacing . 'px',
	'--icon-size'          => $icon_size . 'px',
	'--icon-spacing'       => $icon_spacing . 'px',
);

// Only add color styles when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--number-color']        = $number_color;
	$inline_styles['--title-color']         = $title_color;
	$inline_styles['--prefix-suffix-color'] = $prefix_suffix_color;
	$inline_styles['--icon-color']          = $icon_color;
}

// Filter out empty values.
$inline_styles = array_filter( $inline_styles );

// Convert to style string.
$style_string = '';
foreach ( $inline_styles as $property => $value ) {
	$style_string .= esc_attr( $property ) . ':' . esc_attr( $value ) . ';';
}

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-counter layout-' . esc_attr( $layout ) . ' align-' . esc_attr( $alignment );
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Build wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'           => $wrapper_classes,
		'style'           => $style_string,
		'data-start'      => $start_number,
		'data-end'        => $end_number,
		'data-duration'   => $duration,
		'data-grouping'   => $use_grouping ? 'true' : 'false',
	)
);

// Format number for initial display.
$formatted_number = $use_grouping ? number_format( $start_number ) : $start_number;
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $show_icon && ( $icon || $custom_icon_url ) ) : ?>
		<div class="wbcom-essential-counter__icon">
			<?php if ( 'custom' === $icon_type && $custom_icon_url ) : ?>
				<img src="<?php echo esc_url( $custom_icon_url ); ?>" alt="" />
			<?php elseif ( $icon ) : ?>
				<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="wbcom-essential-counter__content">
		<div class="wbcom-essential-counter__number-wrap">
			<?php if ( $prefix ) : ?>
				<span class="wbcom-essential-counter__prefix">
					<?php echo esc_html( $prefix ); ?>
				</span>
			<?php endif; ?>

			<span class="wbcom-essential-counter__number" data-count="<?php echo esc_attr( $end_number ); ?>">
				<?php echo esc_html( $formatted_number ); ?>
			</span>

			<?php if ( $suffix ) : ?>
				<span class="wbcom-essential-counter__suffix">
					<?php echo esc_html( $suffix ); ?>
				</span>
			<?php endif; ?>
		</div>

		<?php if ( $title ) : ?>
			<div class="wbcom-essential-counter__title">
				<?php echo wp_kses_post( $title ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
