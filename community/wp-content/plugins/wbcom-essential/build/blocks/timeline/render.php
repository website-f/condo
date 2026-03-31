<?php
/**
 * Server-side render for Timeline block.
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
$use_theme_colors             = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$items                        = $attributes['items'] ?? array();
$layout                       = $attributes['layout'] ?? 'two-column';
$show_arrow                   = $attributes['showArrow'] ?? true;
$enable_animation             = $attributes['enableAnimation'] ?? true;
$bar_thickness                = $attributes['barThickness'] ?? 4;
$bar_color                    = $attributes['barColor'] ?? '#e2e8f0';
$icon_container_size          = $attributes['iconContainerSize'] ?? 60;
$icon_container_background    = $attributes['iconContainerBackground'] ?? '#3182ce';
$icon_container_border_radius = $attributes['iconContainerBorderRadius'] ?? 50;
$icon_size                    = $attributes['iconSize'] ?? 24;
$icon_color                   = $attributes['iconColor'] ?? '#ffffff';
$content_background           = $attributes['contentBackground'] ?? '#f7fafc';
$content_border_radius        = $attributes['contentBorderRadius'] ?? 12;
$date_color                   = $attributes['dateColor'] ?? '#718096';
$title_color                  = $attributes['titleColor'] ?? '#1a202c';
$text_color                   = $attributes['textColor'] ?? '#4a5568';
$content_padding              = $attributes['contentPadding'] ?? 24;
$content_box_shadow           = $attributes['contentBoxShadow'] ?? true;
$date_font_size               = $attributes['dateFontSize'] ?? 14;
$title_font_size              = $attributes['titleFontSize'] ?? 20;
$text_font_size               = $attributes['textFontSize'] ?? 16;
$item_spacing                 = $attributes['itemSpacing'] ?? 40;

// New attributes for border and shadow.
$content_border_type     = $attributes['contentBorderType'] ?? 'none';
$content_border_width    = $attributes['contentBorderWidth'] ?? 1;
$content_border_color    = $attributes['contentBorderColor'] ?? '#e2e8f0';
$shadow_horizontal       = $attributes['contentBoxShadowHorizontal'] ?? 0;
$shadow_vertical         = $attributes['contentBoxShadowVertical'] ?? 4;
$shadow_blur             = $attributes['contentBoxShadowBlur'] ?? 15;
$shadow_spread           = $attributes['contentBoxShadowSpread'] ?? 0;
$shadow_color            = $attributes['contentBoxShadowColor'] ?? 'rgba(0, 0, 0, 0.08)';

// Date typography attributes.
$date_font_weight      = $attributes['dateFontWeight'] ?? '600';
$date_text_transform   = $attributes['dateTextTransform'] ?? 'none';
$date_line_height      = $attributes['dateLineHeight'] ?? 1.4;
$date_letter_spacing   = $attributes['dateLetterSpacing'] ?? 0;

// Image settings.
$image_border_radius = $attributes['imageBorderRadius'] ?? 8;

// Don't render if no items.
if ( empty( $items ) ) {
	return;
}

// Icon mapping.
$icons = array(
	'star'     => '★',
	'flag'     => '⚑',
	'check'    => '✓',
	'heart'    => '♥',
	'bolt'     => '⚡',
	'rocket'   => '🚀',
	'trophy'   => '🏆',
	'target'   => '◎',
	'clock'    => '⏰',
	'calendar' => '📅',
);

// Build unique ID.
$unique_id = wp_unique_id( 'wbcom-timeline-' );

// Build box shadow value.
$box_shadow_value = $content_box_shadow
	? sprintf(
		'%dpx %dpx %dpx %dpx %s',
		intval( $shadow_horizontal ),
		intval( $shadow_vertical ),
		absint( $shadow_blur ),
		intval( $shadow_spread ),
		esc_attr( $shadow_color )
	)
	: 'none';

// Build border value.
$border_value = 'none' !== $content_border_type
	? sprintf(
		'%dpx %s %s',
		absint( $content_border_width ),
		esc_attr( $content_border_type ),
		esc_attr( $content_border_color )
	)
	: 'none';

// CSS variables - layout always, colors only when NOT using theme colors.
$container_style_parts = array(
	// Layout variables (always applied).
	sprintf( '--bar-thickness: %dpx', absint( $bar_thickness ) ),
	sprintf( '--icon-container-size: %dpx', absint( $icon_container_size ) ),
	sprintf( '--content-padding: %dpx', absint( $content_padding ) ),
	sprintf( '--content-box-shadow: %s', esc_attr( $box_shadow_value ) ),
	sprintf( '--content-border: %s', esc_attr( $border_value ) ),
	sprintf( '--date-font-size: %dpx', absint( $date_font_size ) ),
	sprintf( '--title-font-size: %dpx', absint( $title_font_size ) ),
	sprintf( '--text-font-size: %dpx', absint( $text_font_size ) ),
	sprintf( '--item-spacing: %dpx', absint( $item_spacing ) ),
	sprintf( '--image-border-radius: %dpx', absint( $image_border_radius ) ),
);

// Color variables (only when NOT using theme colors).
if ( ! $use_theme_colors ) {
	$container_style_parts[] = sprintf( '--bar-color: %s', esc_attr( $bar_color ) );
	$container_style_parts[] = sprintf( '--content-background: %s', esc_attr( $content_background ) );
}

$container_style = implode( '; ', $container_style_parts ) . ';';

// Date typography style.
$date_typography_style = sprintf(
	'font-weight: %s; text-transform: %s; line-height: %s; letter-spacing: %spx;',
	esc_attr( $date_font_weight ),
	esc_attr( $date_text_transform ),
	esc_attr( $date_line_height ),
	esc_attr( $date_letter_spacing )
);

// Icon container style - layout always, colors only when NOT using theme colors.
$icon_style_parts = array(
	sprintf( 'width: %dpx', absint( $icon_container_size ) ),
	sprintf( 'height: %dpx', absint( $icon_container_size ) ),
	sprintf( 'border-radius: %d%%', absint( $icon_container_border_radius ) ),
	sprintf( 'font-size: %dpx', absint( $icon_size ) ),
);

if ( ! $use_theme_colors ) {
	$icon_style_parts[] = sprintf( 'background-color: %s', esc_attr( $icon_container_background ) );
	$icon_style_parts[] = sprintf( 'color: %s', esc_attr( $icon_color ) );
}

$icon_style = implode( '; ', $icon_style_parts ) . ';';

// Content style - layout always, colors only when NOT using theme colors.
$content_style_parts = array(
	sprintf( 'border-radius: %dpx', absint( $content_border_radius ) ),
);

if ( ! $use_theme_colors ) {
	$content_style_parts[] = sprintf( 'background-color: %s', esc_attr( $content_background ) );
}

$content_style = implode( '; ', $content_style_parts ) . ';';

// Get wrapper attributes.
$classes = array(
	'wbcom-essential-timeline',
	'wbcom-timeline-' . esc_attr( $layout ),
);

if ( $use_theme_colors ) {
	$classes[] = 'use-theme-colors';
}

if ( $enable_animation ) {
	$classes[] = 'wbcom-timeline-animated';
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $classes ),
	'id'    => $unique_id,
	'style' => $container_style,
) );
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wbcom-timeline-container">
		<?php foreach ( $items as $index => $item ) : ?>
			<?php
			$item_title      = $item['title'] ?? '';
			$item_date       = $item['date'] ?? '';
			$item_content    = $item['content'] ?? '';
			$item_icon       = $item['icon'] ?? 'star';
			$item_image_id   = $item['imageId'] ?? 0;
			$item_title_tag  = $item['titleTag'] ?? 'h3';
			$item_text_align = $item['textAlign'] ?? 'left';
			$item_class      = $index % 2 === 0 ? 'even' : 'odd';

			// Validate title tag.
			$allowed_tags   = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p' );
			$item_title_tag = in_array( $item_title_tag, $allowed_tags, true ) ? $item_title_tag : 'h3';

			// Get icon character.
			$icon_char = $icons[ $item_icon ] ?? '★';

			// Get image if set.
			$image_html = '';
			if ( ! empty( $item_image_id ) && $item_image_id > 0 ) {
				$image_html = wp_get_attachment_image( $item_image_id, 'large', false, array(
					'class' => 'wbcom-timeline-img',
				) );
			}

			$content_classes = 'wbcom-timeline-content';
			if ( $show_arrow ) {
				$content_classes .= ' show-arrow';
			}
			?>
			<div class="wbcom-timeline-block <?php echo esc_attr( $item_class ); ?>">
				<div class="wbcom-timeline-icon" style="<?php echo esc_attr( $icon_style ); ?>">
					<?php echo esc_html( $icon_char ); ?>
				</div>
				<div class="<?php echo esc_attr( $content_classes ); ?>" style="<?php echo esc_attr( $content_style ); ?> text-align: <?php echo esc_attr( $item_text_align ); ?>;">
					<?php if ( $image_html ) : ?>
						<div class="wbcom-timeline-image">
							<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $item_date ) ) : ?>
						<span class="wbcom-timeline-date" style="<?php echo ! $use_theme_colors ? 'color: ' . esc_attr( $date_color ) . '; ' : ''; ?><?php echo esc_attr( $date_typography_style ); ?>">
							<?php echo esc_html( $item_date ); ?>
						</span>
					<?php endif; ?>

					<?php if ( ! empty( $item_title ) ) : ?>
						<<?php echo esc_html( $item_title_tag ); ?> class="wbcom-timeline-title" style="<?php echo ! $use_theme_colors ? 'color: ' . esc_attr( $title_color ) . ';' : ''; ?>">
							<?php echo esc_html( $item_title ); ?>
						</<?php echo esc_html( $item_title_tag ); ?>>
					<?php endif; ?>

					<?php if ( ! empty( $item_content ) ) : ?>
						<p class="wbcom-timeline-text" style="<?php echo ! $use_theme_colors ? 'color: ' . esc_attr( $text_color ) . ';' : ''; ?>">
							<?php echo wp_kses_post( $item_content ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
