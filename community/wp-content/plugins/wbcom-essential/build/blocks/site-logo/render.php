<?php
/**
 * Server-side render for Site Logo block.
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
$use_theme_colors  = $attributes['useThemeColors'] ?? false;
$logo_source       = $attributes['logoSource'] ?? 'customizer';
$desktop_logo_id   = $attributes['desktopLogoId'] ?? 0;
$desktop_logo_url  = $attributes['desktopLogoUrl'] ?? '';
$mobile_logo_id    = $attributes['mobileLogoId'] ?? 0;
$mobile_logo_url   = $attributes['mobileLogoUrl'] ?? '';
$image_size        = $attributes['imageSize'] ?? 'full';
$mobile_image_size = $attributes['mobileImageSize'] ?? 'large';
$link_url          = $attributes['linkUrl'] ?? '';
$link_home         = $attributes['linkHome'] ?? true;
$link_new_tab      = $attributes['linkNewTab'] ?? false;
$link_rel          = $attributes['linkRel'] ?? '';
$mobile_breakpoint = $attributes['mobileBreakpoint'] ?? 768;
$alignment         = $attributes['alignment'] ?? 'flex-start';
$max_width         = $attributes['maxWidth'] ?? 200;
$max_width_tablet  = $attributes['maxWidthTablet'] ?? 150;
$max_width_mobile  = $attributes['maxWidthMobile'] ?? 120;
$background_color  = $attributes['backgroundColor'] ?? '';
$border_style      = $attributes['borderStyle'] ?? 'none';
$border_width      = $attributes['borderWidth'] ?? 0;
$border_color      = $attributes['borderColor'] ?? '';
$border_radius     = $attributes['borderRadius'] ?? 0;
$box_shadow        = $attributes['boxShadow'] ?? '';

// Determine link URL.
if ( $link_home ) {
	$link_url = home_url( '/' );
}

// Generate unique ID for scoped styles.
$block_id = 'site-logo-' . wp_unique_id();

// Build CSS custom properties - layout properties always applied.
$style_parts = array(
	'--logo-align: ' . esc_attr( $alignment ),
	'--logo-max-width: ' . intval( $max_width ) . 'px',
	'--logo-max-width-tablet: ' . intval( $max_width_tablet ) . 'px',
	'--logo-max-width-mobile: ' . intval( $max_width_mobile ) . 'px',
	'--logo-radius: ' . intval( $border_radius ) . 'px',
);

// Add color CSS variables only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$style_parts[] = '--logo-bg: ' . ( $background_color ? esc_attr( $background_color ) : 'transparent' );

	// Border styles.
	if ( 'none' !== $border_style && $border_width > 0 ) {
		$style_parts[] = '--logo-border-style: ' . esc_attr( $border_style );
		$style_parts[] = '--logo-border-width: ' . intval( $border_width ) . 'px';
		$style_parts[] = '--logo-border-color: ' . ( $border_color ? esc_attr( $border_color ) : '#000000' );
	}

	// Box shadow.
	if ( ! empty( $box_shadow ) ) {
		$style_parts[] = '--logo-box-shadow: ' . esc_attr( $box_shadow );
	}
} else {
	// When using theme colors, still apply border style/width if set.
	if ( 'none' !== $border_style && $border_width > 0 ) {
		$style_parts[] = '--logo-border-style: ' . esc_attr( $border_style );
		$style_parts[] = '--logo-border-width: ' . intval( $border_width ) . 'px';
	}
}

$style_vars = implode( '; ', $style_parts ) . ';';

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-site-logo';
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'id'    => $block_id,
		'class' => $wrapper_classes,
		'style' => $style_vars,
	)
);

// Link attributes - pre-escaped with esc_url() and hardcoded safe strings.
$link_attrs = '';
if ( $link_url ) {
	$link_attrs = 'href="' . esc_url( $link_url ) . '"';

	// Build rel attribute.
	$rel_parts = array();
	if ( $link_new_tab ) {
		$rel_parts[] = 'noopener';
	}
	if ( ! empty( $link_rel ) ) {
		$rel_values = array_filter( explode( ' ', $link_rel ) );
		$rel_parts  = array_merge( $rel_parts, $rel_values );
	}

	if ( ! empty( $rel_parts ) ) {
		$link_attrs .= ' rel="' . esc_attr( implode( ' ', array_unique( $rel_parts ) ) ) . '"';
	}

	if ( $link_new_tab ) {
		$link_attrs .= ' target="_blank"';
	}
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<div class="wbcom-site-logo-container">
		<?php if ( 'customizer' === $logo_source ) : ?>
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<p class="wbcom-site-logo-placeholder">
					<?php esc_html_e( 'Please add a logo in Appearance → Customize → Site Identity', 'wbcom-essential' ); ?>
				</p>
			<?php endif; ?>
		<?php else : ?>
			<?php if ( $desktop_logo_url ) : ?>
				<?php if ( $link_url ) : ?>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs is built with esc_url() and hardcoded strings ?>
					<a <?php echo $link_attrs; ?> class="wbcom-logo-desktop">
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() handles escaping internally ?>
						<?php echo wp_get_attachment_image( $desktop_logo_id, $image_size, false, array( 'alt' => get_bloginfo( 'name' ) ) ); ?>
					</a>
				<?php else : ?>
					<div class="wbcom-logo-desktop">
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() handles escaping internally ?>
						<?php echo wp_get_attachment_image( $desktop_logo_id, $image_size, false, array( 'alt' => get_bloginfo( 'name' ) ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $mobile_logo_url ) : ?>
					<?php if ( $link_url ) : ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs is built with esc_url() and hardcoded strings ?>
						<a <?php echo $link_attrs; ?> class="wbcom-logo-mobile">
							<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() handles escaping internally ?>
							<?php echo wp_get_attachment_image( $mobile_logo_id, $mobile_image_size, false, array( 'alt' => get_bloginfo( 'name' ) ) ); ?>
						</a>
					<?php else : ?>
						<div class="wbcom-logo-mobile">
							<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() handles escaping internally ?>
							<?php echo wp_get_attachment_image( $mobile_logo_id, $mobile_image_size, false, array( 'alt' => get_bloginfo( 'name' ) ) ); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			<?php else : ?>
				<p class="wbcom-site-logo-placeholder">
					<?php esc_html_e( 'Please select a logo image.', 'wbcom-essential' ); ?>
				</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<?php if ( 'custom' === $logo_source && $mobile_logo_url && $mobile_breakpoint ) : ?>
		<style>
			#<?php echo esc_attr( $block_id ); ?> .wbcom-logo-desktop {
				display: block;
			}
			#<?php echo esc_attr( $block_id ); ?> .wbcom-logo-mobile {
				display: none;
			}
			@media screen and (max-width: <?php echo intval( $mobile_breakpoint ); ?>px) {
				#<?php echo esc_attr( $block_id ); ?> .wbcom-logo-desktop {
					display: none;
				}
				#<?php echo esc_attr( $block_id ); ?> .wbcom-logo-mobile {
					display: block;
				}
			}
		</style>
	<?php endif; ?>
</div>
