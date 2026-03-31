<?php
/**
 * Server-side render for Flip Box block.
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
$use_theme_colors         = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$front_icon               = $attributes['frontIcon'] ?? 'star-filled';
$front_title              = $attributes['frontTitle'] ?? '';
$front_title_tag          = $attributes['frontTitleTag'] ?? 'h3';
$front_content            = $attributes['frontContent'] ?? '';
$back_icon                = $attributes['backIcon'] ?? '';
$back_title               = $attributes['backTitle'] ?? '';
$back_title_tag           = $attributes['backTitleTag'] ?? 'h3';
$back_content             = $attributes['backContent'] ?? '';
$button_text              = $attributes['buttonText'] ?? '';
$button_url               = $attributes['buttonUrl'] ?? '';
$button_new_tab           = $attributes['buttonNewTab'] ?? false;
$flip_direction           = $attributes['flipDirection'] ?? 'flip-right';
$animation_duration       = $attributes['animationDuration'] ?? 0.6;
$animation_timing         = $attributes['animationTiming'] ?? 'ease';
$box_width                = $attributes['boxWidth'] ?? 300;
$box_height               = $attributes['boxHeight'] ?? 300;
$box_align                = $attributes['boxAlign'] ?? 'center';
$front_background         = $attributes['frontBackground'] ?? '#f5f5f5';
$front_title_color        = $attributes['frontTitleColor'] ?? '#1a1a1a';
$front_content_color      = $attributes['frontContentColor'] ?? '#666666';
$front_icon_color         = $attributes['frontIconColor'] ?? '#3182ce';
$front_icon_size          = $attributes['frontIconSize'] ?? 48;
$back_background          = $attributes['backBackground'] ?? '#3182ce';
$back_title_color         = $attributes['backTitleColor'] ?? '#ffffff';
$back_content_color       = $attributes['backContentColor'] ?? 'rgba(255, 255, 255, 0.9)';
$back_icon_color          = $attributes['backIconColor'] ?? '#ffffff';
$back_icon_size           = $attributes['backIconSize'] ?? 48;
$button_background        = $attributes['buttonBackground'] ?? '#ffffff';
$button_text_color        = $attributes['buttonTextColor'] ?? '#3182ce';
$border_radius            = $attributes['borderRadius'] ?? 8;
$button_skin              = $attributes['buttonSkin'] ?? '';
$button_hover_bg          = $attributes['buttonHoverBackground'] ?? '#2c5aa0';
$button_hover_text        = $attributes['buttonHoverTextColor'] ?? '#ffffff';
$front_border_width       = $attributes['frontBorderWidth'] ?? 0;
$front_border_color       = $attributes['frontBorderColor'] ?? '#e0e0e0';
$front_box_shadow         = $attributes['frontBoxShadow'] ?? false;
$back_border_width        = $attributes['backBorderWidth'] ?? 0;
$back_border_color        = $attributes['backBorderColor'] ?? '#e0e0e0';
$back_box_shadow          = $attributes['backBoxShadow'] ?? false;

// Allowed heading tags.
$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
$front_title_tag = in_array( $front_title_tag, $allowed_tags, true ) ? $front_title_tag : 'h3';
$back_title_tag = in_array( $back_title_tag, $allowed_tags, true ) ? $back_title_tag : 'h3';

// Build inline styles - layout vars always.
$style_vars = sprintf(
	'--flip-box-width: %dpx; --flip-box-height: %dpx; --flip-box-align: %s; --flip-duration: %ss; --flip-timing: %s; --flip-front-icon-size: %dpx; --flip-back-icon-size: %dpx; --flip-radius: %dpx; --flip-front-border-width: %dpx; --flip-back-border-width: %dpx;',
	$box_width,
	$box_height,
	esc_attr( $box_align ),
	$animation_duration,
	esc_attr( $animation_timing ),
	$front_icon_size,
	$back_icon_size,
	$border_radius,
	$front_border_width,
	$back_border_width
);

// Add color vars only when not using theme colors.
if ( ! $use_theme_colors ) {
	$style_vars .= sprintf(
		' --flip-front-bg: %s; --flip-front-title: %s; --flip-front-content: %s; --flip-front-icon: %s; --flip-back-bg: %s; --flip-back-title: %s; --flip-back-content: %s; --flip-back-icon: %s; --flip-btn-bg: %s; --flip-btn-text: %s; --flip-btn-hover-bg: %s; --flip-btn-hover-text: %s; --flip-front-border-color: %s; --flip-back-border-color: %s;',
		esc_attr( $front_background ),
		esc_attr( $front_title_color ),
		esc_attr( $front_content_color ),
		esc_attr( $front_icon_color ),
		esc_attr( $back_background ),
		esc_attr( $back_title_color ),
		esc_attr( $back_content_color ),
		esc_attr( $back_icon_color ),
		esc_attr( $button_background ),
		esc_attr( $button_text_color ),
		esc_attr( $button_hover_bg ),
		esc_attr( $button_hover_text ),
		esc_attr( $front_border_color ),
		esc_attr( $back_border_color )
	);
}

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-flip-box';
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

// Build front and back side classes.
$front_classes = 'wbcom-flip-box-front';
if ( $front_box_shadow ) {
	$front_classes .= ' has-box-shadow';
}

$back_classes = 'wbcom-flip-box-back';
if ( $back_box_shadow ) {
	$back_classes .= ' has-box-shadow';
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<div class="wbcom-flip-box-wrapper <?php echo esc_attr( $flip_direction ); ?>">
		<div class="wbcom-flip-box-inner">
			<!-- Front Side -->
			<div class="<?php echo esc_attr( $front_classes ); ?>">
				<?php if ( $front_icon ) : ?>
					<div class="wbcom-flip-box-icon">
						<span class="dashicons dashicons-<?php echo esc_attr( $front_icon ); ?>"></span>
					</div>
				<?php endif; ?>

				<?php if ( $front_title ) : ?>
					<<?php echo esc_html( $front_title_tag ); ?> class="wbcom-flip-box-title">
						<?php echo esc_html( $front_title ); ?>
					</<?php echo esc_html( $front_title_tag ); ?>>
				<?php endif; ?>

				<?php if ( $front_content ) : ?>
					<div class="wbcom-flip-box-content">
						<?php echo wp_kses_post( $front_content ); ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Back Side -->
			<div class="<?php echo esc_attr( $back_classes ); ?>">
				<?php if ( $back_icon ) : ?>
					<div class="wbcom-flip-box-icon">
						<span class="dashicons dashicons-<?php echo esc_attr( $back_icon ); ?>"></span>
					</div>
				<?php endif; ?>

				<?php if ( $back_title ) : ?>
					<<?php echo esc_html( $back_title_tag ); ?> class="wbcom-flip-box-title">
						<?php echo esc_html( $back_title ); ?>
					</<?php echo esc_html( $back_title_tag ); ?>>
				<?php endif; ?>

				<?php if ( $back_content ) : ?>
					<div class="wbcom-flip-box-content">
						<?php echo wp_kses_post( $back_content ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $button_url && $button_text ) : ?>
					<a
						href="<?php echo esc_url( $button_url ); ?>"
						class="wbcom-flip-box-button <?php echo esc_attr( $button_skin ); ?>"
						<?php echo $button_new_tab ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
					>
						<?php echo esc_html( $button_text ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
