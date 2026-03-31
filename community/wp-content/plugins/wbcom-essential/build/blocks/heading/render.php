<?php
/**
 * Server-side render for Heading block.
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
$use_theme_colors     = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$heading_text         = $attributes['headingText'] ?? 'Add Your Heading Text Here';
$html_tag             = $attributes['htmlTag'] ?? 'h2';
$link                 = $attributes['link'] ?? array( 'url' => '', 'isExternal' => false, 'nofollow' => false );
$title_color          = $attributes['titleColor'] ?? '';
$typography           = $attributes['typography'] ?? array();
$text_shadow          = $attributes['textShadow'] ?? array();
$blend_mode           = $attributes['blendMode'] ?? '';
$flex_direction       = $attributes['flexDirection'] ?? 'column';
$text_align           = $attributes['textAlign'] ?? '';
$max_width            = $attributes['maxWidth'] ?? array( 'value' => '', 'unit' => 'px' );
$margin               = $attributes['margin'] ?? array( 'top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => 'px' );
$padding              = $attributes['padding'] ?? array( 'top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => 'px' );
$gradient_heading     = $attributes['gradientHeading'] ?? false;
$gradient_color_start = $attributes['gradientColorStart'] ?? '#000000';
$gradient_color_end   = $attributes['gradientColorEnd'] ?? '#ffffff';
$gradient_type        = $attributes['gradientType'] ?? 'linear';
$gradient_direction   = $attributes['gradientDirection'] ?? 'to right';
$rotate_switch        = $attributes['rotateSwitch'] ?? false;
$text_rotate          = $attributes['textRotate'] ?? 180;
$before_line          = $attributes['beforeLine'] ?? array();
$after_line           = $attributes['afterLine'] ?? array();

// Ensure colors are strings (handle both string and object formats).
if ( is_array( $title_color ) ) {
	$title_color = $title_color['color'] ?? ( $title_color['hex'] ?? '' );
}
if ( is_array( $gradient_color_start ) ) {
	$gradient_color_start = $gradient_color_start['color'] ?? ( $gradient_color_start['hex'] ?? '#000000' );
}
if ( is_array( $gradient_color_end ) ) {
	$gradient_color_end = $gradient_color_end['color'] ?? ( $gradient_color_end['hex'] ?? '#ffffff' );
}

// Ensure line colors are strings.
if ( isset( $before_line['color'] ) && is_array( $before_line['color'] ) ) {
	$before_line['color'] = $before_line['color']['color'] ?? ( $before_line['color']['hex'] ?? '' );
}
if ( isset( $after_line['color'] ) && is_array( $after_line['color'] ) ) {
	$after_line['color'] = $after_line['color']['color'] ?? ( $after_line['color']['hex'] ?? '' );
}

// Return early if no heading text.
if ( empty( $heading_text ) ) {
	return;
}

// Build classes.
$classes = 'wbcom-heading';
if ( $gradient_heading ) {
	$classes .= ' wbcom-gradient-heading';
}

// Build wrapper styles.
$wrapper_styles = '';
if ( ! empty( $flex_direction ) ) {
	$wrapper_styles .= 'flex-direction: ' . esc_attr( $flex_direction ) . ';';
}
if ( ! empty( $text_align ) ) {
	$wrapper_styles .= 'text-align: ' . esc_attr( $text_align ) . ';';
}
if ( ! empty( $max_width['value'] ) ) {
	$wrapper_styles .= 'max-width: ' . esc_attr( $max_width['value'] ) . $max_width['unit'] . ';';
}

// Margin.
if ( ! empty( $margin['top'] ) || ! empty( $margin['right'] ) || ! empty( $margin['bottom'] ) || ! empty( $margin['left'] ) ) {
	$wrapper_styles .= 'margin: ' . esc_attr( $margin['top'] ) . $margin['unit'] . ' ' . esc_attr( $margin['right'] ) . $margin['unit'] . ' ' . esc_attr( $margin['bottom'] ) . $margin['unit'] . ' ' . esc_attr( $margin['left'] ) . $margin['unit'] . ';';
}

// Padding.
if ( ! empty( $padding['top'] ) || ! empty( $padding['right'] ) || ! empty( $padding['bottom'] ) || ! empty( $padding['left'] ) ) {
	$wrapper_styles .= 'padding: ' . esc_attr( $padding['top'] ) . $padding['unit'] . ' ' . esc_attr( $padding['right'] ) . $padding['unit'] . ' ' . esc_attr( $padding['bottom'] ) . $padding['unit'] . ' ' . esc_attr( $padding['left'] ) . $padding['unit'] . ';';
}

// Build heading styles.
$heading_styles = '';
if ( ! $use_theme_colors && ! $gradient_heading && ! empty( $title_color ) ) {
	$heading_styles .= 'color: ' . esc_attr( $title_color ) . ';';
}
if ( ! empty( $blend_mode ) ) {
	$heading_styles .= 'mix-blend-mode: ' . esc_attr( $blend_mode ) . ';';
}

// Typography.
if ( ! empty( $typography['fontFamily'] ) ) {
	$heading_styles .= 'font-family: ' . esc_attr( $typography['fontFamily'] ) . ';';
}
if ( ! empty( $typography['fontSize'] ) ) {
	$heading_styles .= 'font-size: ' . esc_attr( $typography['fontSize'] ) . ';';
}
if ( ! empty( $typography['fontWeight'] ) ) {
	$heading_styles .= 'font-weight: ' . esc_attr( $typography['fontWeight'] ) . ';';
}
if ( ! empty( $typography['lineHeight'] ) ) {
	$heading_styles .= 'line-height: ' . esc_attr( $typography['lineHeight'] ) . ';';
}
if ( ! empty( $typography['letterSpacing'] ) ) {
	$heading_styles .= 'letter-spacing: ' . esc_attr( $typography['letterSpacing'] ) . ';';
}
if ( ! empty( $typography['textTransform'] ) ) {
	$heading_styles .= 'text-transform: ' . esc_attr( $typography['textTransform'] ) . ';';
}
if ( ! empty( $typography['textDecoration'] ) ) {
	$heading_styles .= 'text-decoration: ' . esc_attr( $typography['textDecoration'] ) . ';';
}

// Text shadow.
if ( ! empty( $text_shadow['horizontal'] ) || ! empty( $text_shadow['vertical'] ) || ! empty( $text_shadow['blur'] ) || ! empty( $text_shadow['color'] ) ) {
	$shadow_value    = ( $text_shadow['horizontal'] ?? 0 ) . 'px ' . ( $text_shadow['vertical'] ?? 0 ) . 'px ' . ( $text_shadow['blur'] ?? 0 ) . 'px ' . ( $text_shadow['color'] ?? 'transparent' );
	$heading_styles .= 'text-shadow: ' . esc_attr( $shadow_value ) . ';';
}

// Gradient background.
if ( $gradient_heading && ! empty( $gradient_color_start ) && ! empty( $gradient_color_end ) ) {
	if ( 'linear' === $gradient_type ) {
		$gradient_css = 'linear-gradient(' . $gradient_direction . ', ' . $gradient_color_start . ', ' . $gradient_color_end . ')';
	} elseif ( 'radial' === $gradient_type ) {
		$shape        = strpos( $gradient_direction, 'circle' ) !== false ? 'circle' : 'ellipse';
		$gradient_css = 'radial-gradient(' . $shape . ' at center, ' . $gradient_color_start . ', ' . $gradient_color_end . ')';
	} else {
		$gradient_css = 'linear-gradient(to right, ' . $gradient_color_start . ', ' . $gradient_color_end . ')';
	}
	$heading_styles .= 'background-image: ' . esc_attr( $gradient_css ) . ';';
	$heading_styles .= '-webkit-background-clip: text;';
	$heading_styles .= 'background-clip: text;';
	$heading_styles .= '-webkit-text-fill-color: transparent;';
	$heading_styles .= 'color: transparent;';
}

// Rotation.
if ( $rotate_switch ) {
	$heading_styles .= 'writing-mode: vertical-rl;';
	$heading_styles .= 'transform: rotate(' . esc_attr( $text_rotate ) . 'deg);';
}

// Before line styles.
$before_styles = '';
if ( ! empty( $before_line['width']['value'] ) ) {
	$before_styles .= 'width: ' . esc_attr( $before_line['width']['value'] ) . ( $before_line['width']['unit'] ?? 'px' ) . ';';
	$before_styles .= 'min-width: ' . esc_attr( $before_line['width']['value'] ) . ( $before_line['width']['unit'] ?? 'px' ) . ';';
}
if ( ! empty( $before_line['height']['value'] ) ) {
	$before_styles .= 'height: ' . esc_attr( $before_line['height']['value'] ) . ( $before_line['height']['unit'] ?? 'px' ) . ';';
}
// Only apply line colors when not using theme colors.
if ( ! $use_theme_colors && ! empty( $before_line['color'] ) ) {
	$before_styles .= 'background: ' . esc_attr( $before_line['color'] ) . ';';
}
if ( ! empty( $before_line['align'] ) ) {
	$before_styles .= 'align-self: ' . esc_attr( $before_line['align'] ) . ';';
}

// After line styles.
$after_styles = '';
if ( ! empty( $after_line['width']['value'] ) ) {
	$after_styles .= 'width: ' . esc_attr( $after_line['width']['value'] ) . ( $after_line['width']['unit'] ?? 'px' ) . ';';
	$after_styles .= 'min-width: ' . esc_attr( $after_line['width']['value'] ) . ( $after_line['width']['unit'] ?? 'px' ) . ';';
}
if ( ! empty( $after_line['height']['value'] ) ) {
	$after_styles .= 'height: ' . esc_attr( $after_line['height']['value'] ) . ( $after_line['height']['unit'] ?? 'px' ) . ';';
}
// Only apply line colors when not using theme colors.
if ( ! $use_theme_colors && ! empty( $after_line['color'] ) ) {
	$after_styles .= 'background: ' . esc_attr( $after_line['color'] ) . ';';
}
if ( ! empty( $after_line['align'] ) ) {
	$after_styles .= 'align-self: ' . esc_attr( $after_line['align'] ) . ';';
}

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-heading';
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $wrapper_classes ) );

// Allowed HTML tags for the heading element.
$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div' );
if ( ! in_array( $html_tag, $allowed_tags, true ) ) {
	$html_tag = 'h2';
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wbcom-heading-wrapper" style="<?php echo esc_attr( $wrapper_styles ); ?>">
		<<?php echo esc_attr( $html_tag ); ?> class="<?php echo esc_attr( $classes ); ?>" style="<?php echo esc_attr( $heading_styles ); ?>">
			<?php if ( ! empty( $before_styles ) ) : ?>
				<span class="wbcom-heading-before" style="<?php echo esc_attr( $before_styles ); ?>"></span>
			<?php endif; ?>

			<span class="wbcom-heading-text">
				<?php if ( ! empty( $link['url'] ) ) : ?>
					<a href="<?php echo esc_url( $link['url'] ); ?>"
						<?php if ( ! empty( $link['isExternal'] ) ) : ?>
							target="_blank"
						<?php endif; ?>
						<?php if ( ! empty( $link['nofollow'] ) ) : ?>
							rel="nofollow"
						<?php endif; ?>>
						<?php echo esc_html( $heading_text ); ?>
					</a>
				<?php else : ?>
					<?php echo esc_html( $heading_text ); ?>
				<?php endif; ?>
			</span>

			<?php if ( ! empty( $after_styles ) ) : ?>
				<span class="wbcom-heading-after" style="<?php echo esc_attr( $after_styles ); ?>"></span>
			<?php endif; ?>
		</<?php echo esc_attr( $html_tag ); ?>>
	</div>
</div>
