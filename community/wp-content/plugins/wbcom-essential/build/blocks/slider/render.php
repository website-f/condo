<?php
/**
 * Server-side render for Slider block.
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
$use_theme_colors     = $attributes['useThemeColors'] ?? false;
$slides               = $attributes['slides'] ?? array();
$slider_height        = $attributes['sliderHeight'] ?? array( 'size' => 700, 'unit' => 'px' );
$slider_height_tablet = $attributes['sliderHeightTablet'] ?? array( 'size' => 500, 'unit' => 'px' );
$slider_height_mobile = $attributes['sliderHeightMobile'] ?? array( 'size' => 400, 'unit' => 'px' );
$autoplay             = $attributes['autoplay'] ?? false;
$autoplay_duration    = $attributes['autoplayDuration'] ?? 5;
$slide_transition     = $attributes['slideTransition'] ?? 'fade';
$show_dots            = $attributes['showDots'] ?? false;
$show_arrows          = $attributes['showArrows'] ?? true;
$nav_arrows_desktop   = $attributes['navArrowsDesktop'] ?? false;
$nav_arrows_tablet    = $attributes['navArrowsTablet'] ?? false;
$nav_arrows_mobile    = $attributes['navArrowsMobile'] ?? false;
$nav_dots_desktop     = $attributes['navDotsDesktop'] ?? false;
$nav_dots_tablet      = $attributes['navDotsTablet'] ?? false;
$nav_dots_mobile      = $attributes['navDotsMobile'] ?? false;
$slide_bg_color       = $attributes['slideBgColor'] ?? '#0073aa';
$title_color          = $attributes['titleColor'] ?? '#111111';
$content_color        = $attributes['contentColor'] ?? '#333333';

// New styling attributes.
$transition_duration   = $attributes['transitionDuration'] ?? 300;
$arrow_color           = $attributes['arrowColor'] ?? '#ffffff';
$arrow_hover_color     = $attributes['arrowHoverColor'] ?? '#ffffff';
$arrow_bg_color        = $attributes['arrowBgColor'] ?? 'rgba(0, 0, 0, 0.5)';
$arrow_bg_hover_color  = $attributes['arrowBgHoverColor'] ?? 'rgba(0, 0, 0, 0.7)';
$arrow_size            = $attributes['arrowSize'] ?? 24;
$arrow_box_size        = $attributes['arrowBoxSize'] ?? 50;
$arrow_border_radius   = $attributes['arrowBorderRadius'] ?? 50;
$dots_color            = $attributes['dotsColor'] ?? 'rgba(255, 255, 255, 0.5)';
$dots_active_color     = $attributes['dotsActiveColor'] ?? '#ffffff';
$dots_size             = $attributes['dotsSize'] ?? 12;
$dots_spacing          = $attributes['dotsSpacing'] ?? 10;
$dots_position         = $attributes['dotsPosition'] ?? 'inside';
$dots_bottom_offset    = $attributes['dotsBottomOffset'] ?? 20;
$enable_keyboard_nav   = $attributes['enableKeyboardNav'] ?? true;
$pause_on_hover        = $attributes['pauseOnHover'] ?? true;
$infinite_loop         = $attributes['infiniteLoop'] ?? true;

// Early return if no slides.
if ( empty( $slides ) ) {
	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'wbcom-essential-slider' ) );
	?>
	<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<p><?php esc_html_e( 'No slides to display.', 'wbcom-essential' ); ?></p>
	</div>
	<?php
	return;
}

// Generate unique ID for the slider.
$slider_id = 'wbcom-slider-' . wp_unique_id();

// Build responsive CSS variables - sizes always applied.
$responsive_styles  = '--slider-height-desktop: ' . esc_attr( $slider_height['size'] . $slider_height['unit'] ) . ';';
$responsive_styles .= '--slider-height-tablet: ' . esc_attr( $slider_height_tablet['size'] . $slider_height_tablet['unit'] ) . ';';
$responsive_styles .= '--slider-height-mobile: ' . esc_attr( $slider_height_mobile['size'] . $slider_height_mobile['unit'] ) . ';';
$responsive_styles .= '--slider-transition-duration: ' . esc_attr( $transition_duration ) . 'ms;';
$responsive_styles .= '--slider-arrow-size: ' . esc_attr( $arrow_size ) . 'px;';
$responsive_styles .= '--slider-arrow-box-size: ' . esc_attr( $arrow_box_size ) . 'px;';
$responsive_styles .= '--slider-arrow-border-radius: ' . esc_attr( $arrow_border_radius ) . '%;';
$responsive_styles .= '--slider-dots-size: ' . esc_attr( $dots_size ) . 'px;';
$responsive_styles .= '--slider-dots-spacing: ' . esc_attr( $dots_spacing ) . 'px;';
$responsive_styles .= '--slider-dots-bottom-offset: ' . esc_attr( $dots_bottom_offset ) . 'px;';

// Add color CSS variables only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$responsive_styles .= 'background-color: ' . esc_attr( $slide_bg_color ) . ';';
	$responsive_styles .= '--slider-arrow-color: ' . esc_attr( $arrow_color ) . ';';
	$responsive_styles .= '--slider-arrow-hover-color: ' . esc_attr( $arrow_hover_color ) . ';';
	$responsive_styles .= '--slider-arrow-bg-color: ' . esc_attr( $arrow_bg_color ) . ';';
	$responsive_styles .= '--slider-arrow-bg-hover-color: ' . esc_attr( $arrow_bg_hover_color ) . ';';
	$responsive_styles .= '--slider-dots-color: ' . esc_attr( $dots_color ) . ';';
	$responsive_styles .= '--slider-dots-active-color: ' . esc_attr( $dots_active_color ) . ';';
}

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-slider';
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}
// Build dots position class.
$dots_position_class = ( 'outside' === $dots_position ) ? 'wbcom-dots-outside' : 'wbcom-dots-inside';
$wrapper_classes    .= ' ' . $dots_position_class;

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'id'    => $slider_id,
		'class' => $wrapper_classes,
		'style' => $responsive_styles,
	)
);

// Build arrow classes.
$arrow_classes_prev = 'wbcom-slider-prev';
$arrow_classes_next = 'wbcom-slider-next';
if ( $nav_arrows_desktop ) {
	$arrow_classes_prev .= ' hide-on-desktop';
	$arrow_classes_next .= ' hide-on-desktop';
}
if ( $nav_arrows_tablet ) {
	$arrow_classes_prev .= ' hide-on-tablet';
	$arrow_classes_next .= ' hide-on-tablet';
}
if ( $nav_arrows_mobile ) {
	$arrow_classes_prev .= ' hide-on-mobile';
	$arrow_classes_next .= ' hide-on-mobile';
}

// Build dots classes.
$dots_classes = 'wbcom-slider-dots';
if ( $nav_dots_desktop ) {
	$dots_classes .= ' hide-on-desktop';
}
if ( $nav_dots_tablet ) {
	$dots_classes .= ' hide-on-tablet';
}
if ( $nav_dots_mobile ) {
	$dots_classes .= ' hide-on-mobile';
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-autoplay="<?php echo esc_attr( $autoplay ? 'true' : 'false' ); ?>"
	data-autoplay-duration="<?php echo esc_attr( $autoplay_duration ); ?>"
	data-transition="<?php echo esc_attr( $slide_transition ); ?>"
	data-transition-duration="<?php echo esc_attr( $transition_duration ); ?>"
	data-keyboard-nav="<?php echo esc_attr( $enable_keyboard_nav ? 'true' : 'false' ); ?>"
	data-pause-on-hover="<?php echo esc_attr( $pause_on_hover ? 'true' : 'false' ); ?>"
	data-infinite-loop="<?php echo esc_attr( $infinite_loop ? 'true' : 'false' ); ?>"
	>

	<div class="wbcom-slider-wrapper">
		<div class="wbcom-slider-inner">
			<?php foreach ( $slides as $slide ) : ?>
				<?php
				// Build background styles.
				$bg_style  = '';
				if ( ! empty( $slide['image']['url'] ) ) {
					$bg_style .= 'background-image: url(' . esc_url( $slide['image']['url'] ) . ');';
				}
				$bg_style .= 'background-size: ' . esc_attr( $slide['image_bg_size'] ?? 'cover' ) . ';';
				$bg_style .= 'background-position: ' . esc_attr( $slide['image_position'] ?? 'center center' ) . ';';
				$bg_style .= 'background-repeat: ' . esc_attr( $slide['image_repeat'] ?? 'no-repeat' ) . ';';
				$bg_style .= 'min-height: var(--slider-height-desktop);';

				$overlay_color   = $slide['overlay_color'] ?? '#ffffff';
				$overlay_opacity = $slide['overlay_opacity'] ?? 0.5;
				?>
				<div class="wbcom-slider-slide">
					<div class="wbcom-slider-bg" style="<?php echo esc_attr( $bg_style ); ?>"
						<?php if ( ! empty( $slide['image']['alt'] ) ) : ?>
							aria-label="<?php echo esc_attr( $slide['image']['alt'] ); ?>"
						<?php endif; ?>>
						<div class="wbcom-slider-overlay" style="background-color: <?php echo esc_attr( $overlay_color ); ?>; opacity: <?php echo esc_attr( $overlay_opacity ); ?>;"></div>
					</div>

					<div class="wbcom-slider-text-wrapper">
						<div class="wbcom-slider-text-box">
							<?php if ( ! empty( $slide['title'] ) ) : ?>
								<h1 class="wbcom-slider-title"<?php echo ! $use_theme_colors ? ' style="color: ' . esc_attr( $title_color ) . ';"' : ''; ?>>
									<?php echo wp_kses_post( $slide['title'] ); ?>
								</h1>
							<?php endif; ?>

							<?php if ( ! empty( $slide['content'] ) ) : ?>
								<div class="wbcom-slider-desc"<?php echo ! $use_theme_colors ? ' style="color: ' . esc_attr( $content_color ) . ';"' : ''; ?>>
									<?php echo wp_kses_post( $slide['content'] ); ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $slide['link']['url'] ) ) : ?>
								<?php
								$link_attrs = '';
								if ( ! empty( $slide['link']['is_external'] ) ) {
									$link_attrs .= ' target="_blank"';
								}
								if ( ! empty( $slide['link']['nofollow'] ) ) {
									$link_attrs .= ' rel="nofollow"';
								}
								?>
								<div class="wbcom-slider-link">
									<a href="<?php echo esc_url( $slide['link']['url'] ); ?>" class="wbcombtn-primary"<?php echo $link_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<?php esc_html_e( 'Learn More', 'wbcom-essential' ); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $show_arrows ) : ?>
			<button class="<?php echo esc_attr( $arrow_classes_prev ); ?>" aria-label="<?php esc_attr_e( 'Previous slide', 'wbcom-essential' ); ?>">&#8249;</button>
			<button class="<?php echo esc_attr( $arrow_classes_next ); ?>" aria-label="<?php esc_attr_e( 'Next slide', 'wbcom-essential' ); ?>">&#8250;</button>
		<?php endif; ?>

		<?php if ( $show_dots ) : ?>
			<div class="<?php echo esc_attr( $dots_classes ); ?>">
				<?php for ( $i = 0; $i < count( $slides ); $i++ ) : ?>
					<?php /* translators: %d: Slide number */ ?>
					<button class="wbcom-slider-dot" data-slide="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'wbcom-essential' ), $i + 1 ) ); ?>"></button>
				<?php endfor; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
