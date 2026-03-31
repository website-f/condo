<?php
/**
 * Server-side render for Testimonial Carousel block.
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
$use_theme_colors       = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$testimonials           = $attributes['testimonials'] ?? array();
$slides_per_view        = $attributes['slidesPerView'] ?? 2;
$slides_per_view_tablet = $attributes['slidesPerViewTablet'] ?? 1;
$slides_per_view_mobile = $attributes['slidesPerViewMobile'] ?? 1;
$space_between          = $attributes['spaceBetween'] ?? 30;
$show_navigation        = $attributes['showNavigation'] ?? true;
$show_pagination        = $attributes['showPagination'] ?? true;
$loop                   = $attributes['loop'] ?? true;
$autoplay               = $attributes['autoplay'] ?? false;
$autoplay_delay         = $attributes['autoplayDelay'] ?? 5000;
$show_rating            = $attributes['showRating'] ?? true;
$card_background        = $attributes['cardBackground'] ?? '#ffffff';
$card_border_radius     = $attributes['cardBorderRadius'] ?? 12;
$quote_color            = $attributes['quoteColor'] ?? '#4a5568';
$name_color             = $attributes['nameColor'] ?? '#1a202c';
$role_color             = $attributes['roleColor'] ?? '#718096';
$rating_color           = $attributes['ratingColor'] ?? '#f6ad55';
$nav_color              = $attributes['navColor'] ?? '#3182ce';
$pause_on_interaction   = $attributes['pauseOnInteraction'] ?? false;
$effect                 = $attributes['effect'] ?? 'slide';
$enable_keyboard        = $attributes['enableKeyboard'] ?? true;
$grab_cursor            = $attributes['grabCursor'] ?? true;

// New styling attributes.
$card_padding            = $attributes['cardPadding'] ?? 24;
$card_border_width       = $attributes['cardBorderWidth'] ?? 0;
$card_border_color       = $attributes['cardBorderColor'] ?? '#e2e8f0';
$card_shadow             = $attributes['cardShadow'] ?? true;
$avatar_size             = $attributes['avatarSize'] ?? 60;
$avatar_border_radius    = $attributes['avatarBorderRadius'] ?? 50;
$quote_font_size         = $attributes['quoteFontSize'] ?? 16;
$name_font_size          = $attributes['nameFontSize'] ?? 16;
$role_font_size          = $attributes['roleFontSize'] ?? 14;
$pagination_color        = $attributes['paginationColor'] ?? '#cbd5e0';
$pagination_active_color = $attributes['paginationActiveColor'] ?? '#3182ce';

// Don't render if no testimonials.
if ( empty( $testimonials ) ) {
	return;
}

if ( ! function_exists( 'wbcom_sanitize_css_color' ) ) {
	/**
	 * Sanitize CSS color value (hex, rgb, rgba, or named colors).
	 *
	 * @param string $color   The color value to sanitize.
	 * @param string $fallback Fallback color if invalid.
	 * @return string The sanitized color or fallback.
	 */
	function wbcom_sanitize_css_color( $color, $fallback = '' ) {
		// Handle hex colors.
		if ( preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $color ) ) {
			return $color;
		}
		// Handle rgb/rgba colors.
		if ( preg_match( '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+))?\s*\)$/', $color ) ) {
			return $color;
		}
		// Handle named colors (basic list).
		$named_colors = array( 'transparent', 'inherit', 'initial', 'currentcolor', 'black', 'white', 'red', 'green', 'blue', 'yellow', 'orange', 'purple', 'pink', 'gray', 'grey' );
		if ( in_array( strtolower( $color ), $named_colors, true ) ) {
			return $color;
		}
		return $fallback;
	}
}

// Build unique ID for this instance.
$unique_id = wp_unique_id( 'wbcom-testimonial-carousel-' );

// Card styles - layout always, colors only when NOT using theme colors.
$card_style_parts = array(
	sprintf( 'border-radius: %dpx', absint( $card_border_radius ) ),
	sprintf( 'padding: %dpx', absint( $card_padding ) ),
);

// Add background color only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	array_unshift( $card_style_parts, sprintf( 'background-color: %s', esc_attr( $card_background ) ) );
}

if ( $card_border_width > 0 ) {
	if ( $use_theme_colors ) {
		$card_style_parts[] = sprintf( 'border-width: %dpx', absint( $card_border_width ) );
		$card_style_parts[] = 'border-style: solid';
	} else {
		$card_style_parts[] = sprintf( 'border: %dpx solid %s', absint( $card_border_width ), esc_attr( $card_border_color ) );
	}
}

if ( $card_shadow ) {
	$card_style_parts[] = 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
}

$card_style = implode( '; ', $card_style_parts ) . ';';

// Avatar styles.
$avatar_style = sprintf(
	'width: %dpx; height: %dpx; border-radius: %d%%;',
	absint( $avatar_size ),
	absint( $avatar_size ),
	absint( $avatar_border_radius )
);

// Swiper configuration.
$swiper_config = wp_json_encode(
	array(
		'slidesPerView' => absint( $slides_per_view ),
		'spaceBetween'  => absint( $space_between ),
		'loop'          => (bool) $loop,
		'direction'     => 'horizontal',
		'effect'        => sanitize_text_field( $effect ),
		'grabCursor'    => (bool) $grab_cursor,
		'keyboard'      => array(
			'enabled' => (bool) $enable_keyboard,
		),
		'autoplay'      => $autoplay ? array(
			'delay'                => absint( $autoplay_delay ),
			'disableOnInteraction' => (bool) $pause_on_interaction,
		) : false,
		'navigation'    => $show_navigation ? array(
			'nextEl' => '#' . $unique_id . ' .swiper-button-next',
			'prevEl' => '#' . $unique_id . ' .swiper-button-prev',
		) : false,
		'pagination'    => $show_pagination ? array(
			'el'        => '#' . $unique_id . ' .swiper-pagination',
			'clickable' => true,
		) : false,
		'breakpoints'   => array(
			320  => array(
				'slidesPerView' => absint( $slides_per_view_mobile ),
			),
			768  => array(
				'slidesPerView' => absint( $slides_per_view_tablet ),
			),
			1024 => array(
				'slidesPerView' => absint( $slides_per_view ),
			),
		),
	)
);

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-testimonial-carousel';
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'              => $wrapper_classes,
		'id'                 => $unique_id,
		'data-swiper-config' => $swiper_config,
	)
);

// Build inline styles - layout always, colors only when NOT using theme colors.
$inline_style = '';

if ( $use_theme_colors ) {
	// Layout styles only (font sizes, avatar dimensions).
	$inline_style = sprintf(
		'<style>
		#%1$s .wbcom-testimonial-quote p { font-size: %2$dpx; }
		#%1$s .wbcom-testimonial-name { font-size: %3$dpx; }
		#%1$s .wbcom-testimonial-role { font-size: %4$dpx; }
		#%1$s .wbcom-testimonial-avatar { width: %5$dpx; height: %5$dpx; border-radius: %6$d%%; overflow: hidden; }
		#%1$s .wbcom-testimonial-avatar img { width: 100%%; height: 100%%; object-fit: cover; }
		</style>',
		esc_attr( $unique_id ),
		absint( $quote_font_size ),
		absint( $name_font_size ),
		absint( $role_font_size ),
		absint( $avatar_size ),
		absint( $avatar_border_radius )
	);
} else {
	// Full styles including colors.
	$sanitized_nav_color         = wbcom_sanitize_css_color( $nav_color, '#3182ce' );
	$sanitized_pagination_color  = wbcom_sanitize_css_color( $pagination_color, '#cbd5e0' );
	$sanitized_pagination_active = wbcom_sanitize_css_color( $pagination_active_color, '#3182ce' );
	$sanitized_quote_color       = wbcom_sanitize_css_color( $quote_color, '#4a5568' );
	$sanitized_name_color        = wbcom_sanitize_css_color( $name_color, '#1a202c' );
	$sanitized_role_color        = wbcom_sanitize_css_color( $role_color, '#718096' );

	$inline_style = sprintf(
		'<style>
		#%1$s .swiper-button-next,
		#%1$s .swiper-button-prev { color: %2$s; }
		#%1$s .swiper-pagination-bullet { background-color: %3$s; }
		#%1$s .swiper-pagination-bullet-active { background-color: %4$s; }
		#%1$s .wbcom-testimonial-quote p { color: %5$s; font-size: %6$dpx; }
		#%1$s .wbcom-testimonial-name { color: %7$s; font-size: %8$dpx; }
		#%1$s .wbcom-testimonial-role { color: %9$s; font-size: %10$dpx; }
		#%1$s .wbcom-testimonial-avatar { width: %11$dpx; height: %11$dpx; border-radius: %12$d%%; overflow: hidden; }
		#%1$s .wbcom-testimonial-avatar img { width: 100%%; height: 100%%; object-fit: cover; }
		</style>',
		esc_attr( $unique_id ),
		esc_attr( $sanitized_nav_color ),
		esc_attr( $sanitized_pagination_color ),
		esc_attr( $sanitized_pagination_active ),
		esc_attr( $sanitized_quote_color ),
		absint( $quote_font_size ),
		esc_attr( $sanitized_name_color ),
		absint( $name_font_size ),
		esc_attr( $sanitized_role_color ),
		absint( $role_font_size ),
		absint( $avatar_size ),
		absint( $avatar_border_radius )
	);
}

if ( ! function_exists( 'wbcom_render_carousel_stars' ) ) {
	/**
	 * Render star rating HTML.
	 *
	 * @param int    $rating       The rating value (1-5).
	 * @param string $rating_color The color for filled stars (empty for theme colors mode).
	 * @return string The star rating HTML.
	 */
	function wbcom_render_carousel_stars( $rating, $rating_color ) {
		$output = '';
		for ( $i = 1; $i <= 5; $i++ ) {
			$filled = $i <= $rating;
			$class  = $filled ? 'filled' : 'empty';

			// Only add inline color style if rating_color is provided.
			if ( ! empty( $rating_color ) ) {
				$color   = $filled ? $rating_color : '#e2e8f0';
				$output .= sprintf(
					'<span class="star %s" style="color: %s;">★</span>',
					esc_attr( $class ),
					esc_attr( $color )
				);
			} else {
				$output .= sprintf(
					'<span class="star %s">★</span>',
					esc_attr( $class )
				);
			}
		}
		return $output;
	}
}
?>

<?php echo $inline_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="swiper wbcom-testimonial-swiper">
		<div class="swiper-wrapper">
			<?php foreach ( $testimonials as $testimonial ) : ?>
				<?php
				$t_content  = $testimonial['content'] ?? '';
				$t_name     = $testimonial['authorName'] ?? '';
				$t_role     = $testimonial['authorRole'] ?? '';
				$t_image_id = $testimonial['imageId'] ?? 0;
				$t_rating   = $testimonial['rating'] ?? 5;
				$t_image    = '';
$t_image_url = $testimonial['imageUrl'] ?? '';

				if ( $t_image_id ) {
					$t_image = wp_get_attachment_image(
						$t_image_id,
						'medium',
						false,
						array(
							'class' => 'wbcom-testimonial-avatar-img',
							'alt'   => esc_attr( $t_name ),
						)
					);
				} elseif ( $t_image_url ) {
					// Fallback to imageUrl if imageId fails
					$t_image = sprintf(
						'<img src="%s" alt="%s" class="wbcom-testimonial-avatar-img" />',
						esc_url( $t_image_url ),
						esc_attr( $t_name )
					);
				}
				?>
				<div class="swiper-slide">
					<div class="wbcom-testimonial-card" style="<?php echo esc_attr( $card_style ); ?>">
						<?php if ( $show_rating ) : ?>
							<div class="wbcom-testimonial-rating">
								<?php echo wbcom_render_carousel_stars( $t_rating, $use_theme_colors ? '' : $rating_color ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $t_content ) ) : ?>
							<div class="wbcom-testimonial-quote">
								<span class="quote-mark">"</span>
								<p<?php echo ! $use_theme_colors ? ' style="color: ' . esc_attr( $quote_color ) . ';"' : ''; ?>>
									<?php echo wp_kses_post( $t_content ); ?>
								</p>
							</div>
						<?php endif; ?>

						<div class="wbcom-testimonial-author">
							<?php if ( $t_image ) : ?>
								<div class="wbcom-testimonial-avatar">
									<?php echo $t_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							<?php else : ?>
								<div class="wbcom-testimonial-avatar wbcom-testimonial-avatar-placeholder">
									<?php 
									// Get initials from name
									$initials = '';
									if ( ! empty( $t_name ) ) {
										$name_parts = explode( ' ', trim( $t_name ) );
										if ( count( $name_parts ) >= 2 ) {
											$initials = strtoupper( substr( $name_parts[0], 0, 1 ) ) . strtoupper( substr( $name_parts[1], 0, 1 ) );
										} else {
											$initials = strtoupper( substr( $t_name, 0, 2 ) );
										}
									}
									echo esc_html( $initials );
									?>
								</div>
							<?php endif; ?>

							<div class="wbcom-testimonial-info">
								<?php if ( ! empty( $t_name ) ) : ?>
									<span class="wbcom-testimonial-name"<?php echo ! $use_theme_colors ? ' style="color: ' . esc_attr( $name_color ) . ';"' : ''; ?>>
										<?php echo esc_html( $t_name ); ?>
									</span>
								<?php endif; ?>
								<?php if ( ! empty( $t_role ) ) : ?>
									<span class="wbcom-testimonial-role"<?php echo ! $use_theme_colors ? ' style="color: ' . esc_attr( $role_color ) . ';"' : ''; ?>>
										<?php echo esc_html( $t_role ); ?>
									</span>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $show_pagination ) : ?>
			<div class="swiper-pagination"></div>
		<?php endif; ?>

		<?php if ( $show_navigation ) : ?>
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
		<?php endif; ?>
	</div>
</div>
