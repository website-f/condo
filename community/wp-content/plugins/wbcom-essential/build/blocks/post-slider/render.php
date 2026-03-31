<?php
/**
 * Server-side render for Post Slider block.
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
$use_theme_colors        = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$slider_post_type        = $attributes['postType'] ?? 'post';
$categories              = $attributes['categories'] ?? array();
$tags                    = $attributes['tags'] ?? array();
$number_of_posts         = $attributes['numberOfPosts'] ?? 5;
$order_by                = $attributes['orderBy'] ?? 'date';
$sort_order              = $attributes['order'] ?? 'DESC';
$include_ids             = $attributes['includeIds'] ?? '';
$exclude_ids             = $attributes['excludeIds'] ?? '';
$show_excerpt            = $attributes['showExcerpt'] ?? true;
$excerpt_length          = $attributes['excerptLength'] ?? 140;
$show_date               = $attributes['showDate'] ?? true;
$show_button             = $attributes['showButton'] ?? true;
$button_text             = $attributes['buttonText'] ?? __( 'Read More', 'wbcom-essential' );
$title_tag               = $attributes['titleTag'] ?? 'h2';
$slider_height           = $attributes['sliderHeight'] ?? 600;
$slider_height_unit      = $attributes['sliderHeightUnit'] ?? 'px';
$transition              = $attributes['transition'] ?? 'fade';
$transition_duration     = $attributes['transitionDuration'] ?? 500;
$autoplay                = $attributes['autoplay'] ?? true;
$autoplay_delay          = $attributes['autoplayDelay'] ?? 5000;
$show_navigation         = $attributes['showNavigation'] ?? true;
$show_pagination         = $attributes['showPagination'] ?? true;
$hide_nav_on_hover       = $attributes['hideNavOnHover'] ?? false;
$bg_animation            = $attributes['bgAnimation'] ?? 'none';
$bg_animation_duration   = $attributes['bgAnimationDuration'] ?? 8;
$text_animation          = $attributes['textAnimation'] ?? 'fadeInUp';
$overlay_color           = $attributes['overlayColor'] ?? 'rgba(0, 0, 0, 0.4)';
$content_width           = $attributes['contentWidth'] ?? 800;
$content_align           = $attributes['contentAlign'] ?? 'center';
$vertical_align          = $attributes['verticalAlign'] ?? 'center';
$title_color             = $attributes['titleColor'] ?? '#ffffff';
$excerpt_color           = $attributes['excerptColor'] ?? 'rgba(255, 255, 255, 0.9)';
$date_color              = $attributes['dateColor'] ?? 'rgba(255, 255, 255, 0.8)';
$button_bg_color         = $attributes['buttonBgColor'] ?? '#ffffff';
$button_text_color       = $attributes['buttonTextColor'] ?? '#1a202c';
$button_hover_bg_color   = $attributes['buttonHoverBgColor'] ?? '#1a202c';
$button_hover_text_color = $attributes['buttonHoverTextColor'] ?? '#ffffff';
$button_border_radius    = $attributes['buttonBorderRadius'] ?? 4;
$nav_color               = $attributes['navColor'] ?? '#ffffff';
$bg_position             = $attributes['bgPosition'] ?? 'center center';
$bg_size                 = $attributes['bgSize'] ?? 'cover';
$show_divider            = $attributes['showDivider'] ?? false;
$divider_color           = $attributes['dividerColor'] ?? '#0073aa';
$divider_width           = $attributes['dividerWidth'] ?? 80;
$content_padding         = $attributes['contentPadding'] ?? 40;
$default_image_url       = $attributes['defaultImageUrl'] ?? '';

// Sanitize title tag.
$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p' );
if ( ! in_array( $title_tag, $allowed_tags, true ) ) {
	$title_tag = 'h2';
}

// Build query args.
$query_args = array(
	'post_type'      => sanitize_key( $slider_post_type ),
	'posts_per_page' => absint( $number_of_posts ),
	'orderby'        => sanitize_key( $order_by ),
	'order'          => 'ASC' === strtoupper( $sort_order ) ? 'ASC' : 'DESC',
	'post_status'    => 'publish',
);

// Add category filter if set.
if ( ! empty( $categories ) && 'post' === $slider_post_type ) {
	$query_args['category_name'] = implode( ',', array_map( 'sanitize_title', $categories ) );
}

// Add tags filter if set.
if ( ! empty( $tags ) && 'post' === $slider_post_type ) {
	$query_args['tag'] = implode( ',', array_map( 'sanitize_title', $tags ) );
}

// Add include IDs if set.
if ( ! empty( $include_ids ) ) {
	$include_array = array_map( 'absint', array_filter( explode( ',', $include_ids ) ) );
	if ( ! empty( $include_array ) ) {
		$query_args['post__in'] = $include_array;
	}
}

// Add exclude IDs if set.
if ( ! empty( $exclude_ids ) ) {
	$exclude_array = array_map( 'absint', array_filter( explode( ',', $exclude_ids ) ) );
	if ( ! empty( $exclude_array ) ) {
		$query_args['post__not_in'] = $exclude_array;
	}
}

$posts_query = new WP_Query( $query_args );

if ( ! $posts_query->have_posts() ) {
	echo '<p class="wbcom-essential-post-slider-empty">' . esc_html__( 'No posts found.', 'wbcom-essential' ) . '</p>';
	return;
}

// Generate unique ID for this slider instance.
$slider_id = 'wbcom-post-slider-' . wp_unique_id();

// Swiper configuration data.
$swiper_config = array(
	'effect'     => $transition,
	'speed'      => absint( $transition_duration ),
	'loop'       => true,
	'navigation' => $show_navigation,
	'pagination' => $show_pagination,
	'autoplay'   => $autoplay ? array( 'delay' => absint( $autoplay_delay ) ) : false,
);

if ( 'fade' === $transition ) {
	$swiper_config['fadeEffect'] = array( 'crossFade' => true );
}

// CSS custom properties for styling.
// Layout styles - always applied.
$style_vars = sprintf(
	'--slider-height: %d%s; --content-width: %dpx; --content-padding: %dpx; --button-radius: %dpx; --bg-anim-duration: %ds; --bg-position: %s; --bg-size: %s; --divider-width: %dpx;',
	absint( $slider_height ),
	esc_attr( $slider_height_unit ),
	absint( $content_width ),
	absint( $content_padding ),
	absint( $button_border_radius ),
	absint( $bg_animation_duration ),
	esc_attr( $bg_position ),
	esc_attr( $bg_size ),
	absint( $divider_width )
);

// Color styles - only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$style_vars .= sprintf(
		' --overlay-color: %s; --title-color: %s; --excerpt-color: %s; --date-color: %s; --button-bg: %s; --button-text: %s; --button-hover-bg: %s; --button-hover-text: %s; --nav-color: %s; --divider-color: %s;',
		esc_attr( $overlay_color ),
		esc_attr( $title_color ),
		esc_attr( $excerpt_color ),
		esc_attr( $date_color ),
		esc_attr( $button_bg_color ),
		esc_attr( $button_text_color ),
		esc_attr( $button_hover_bg_color ),
		esc_attr( $button_hover_text_color ),
		esc_attr( $nav_color ),
		esc_attr( $divider_color )
	);
}

// Build classes.
$wrapper_classes = array(
	'wbcom-essential-post-slider',
	'content-align-' . esc_attr( $content_align ),
	'vertical-align-' . esc_attr( $vertical_align ),
	'bg-animation-' . esc_attr( $bg_animation ),
	'text-animation-' . esc_attr( $text_animation ),
);

if ( $hide_nav_on_hover ) {
	$wrapper_classes[] = 'nav-on-hover';
}

if ( $show_divider ) {
	$wrapper_classes[] = 'has-divider';
}

if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => $style_vars,
		'id'    => $slider_id,
	)
);
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?> data-swiper-config="<?php echo esc_attr( wp_json_encode( $swiper_config ) ); ?>">
	<div class="swiper">
		<div class="swiper-wrapper">
			<?php
			while ( $posts_query->have_posts() ) :
				$posts_query->the_post();
				$thumbnail_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );

				// Use default image if no thumbnail and default is set.
				if ( ! $thumbnail_url && ! empty( $default_image_url ) ) {
					$thumbnail_url = $default_image_url;
				}

				// Fallback placeholder if still no image.
				if ( ! $thumbnail_url ) {
					$thumbnail_url = 'data:image/svg+xml,' . rawurlencode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1920 1080"><rect fill="#4a5568" width="1920" height="1080"/></svg>' );
				}
				?>
				<div class="swiper-slide">
					<div class="wbcom-post-slider-slide" style="background-image: url('<?php echo esc_url( $thumbnail_url ); ?>');">
						<div class="wbcom-post-slider-overlay"></div>
						<div class="wbcom-post-slider-content">
							<?php if ( $show_date ) : ?>
								<div class="wbcom-post-slider-date">
									<?php echo esc_html( get_the_date() ); ?>
								</div>
							<?php endif; ?>

							<?php if ( $show_divider ) : ?>
								<div class="wbcom-post-slider-divider"></div>
							<?php endif; ?>

							<<?php echo esc_attr( $title_tag ); ?> class="wbcom-post-slider-title">
								<a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a>
							</<?php echo esc_attr( $title_tag ); ?>>

							<?php if ( $show_excerpt && $excerpt_length > 0 ) : ?>
								<div class="wbcom-post-slider-excerpt">
									<?php
									$excerpt = get_the_excerpt();
									if ( mb_strlen( $excerpt, 'UTF-8' ) > $excerpt_length ) {
										$excerpt = mb_substr( $excerpt, 0, $excerpt_length, 'UTF-8' ) . '...';
									}
									echo wp_kses_post( $excerpt );
									?>
								</div>
							<?php endif; ?>

							<?php if ( $show_button ) : ?>
								<a href="<?php the_permalink(); ?>" class="wbcom-post-slider-button">
									<?php echo esc_html( $button_text ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
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
