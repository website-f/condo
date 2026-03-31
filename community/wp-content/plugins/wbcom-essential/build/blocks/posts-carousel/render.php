<?php
/**
 * Server-side render for Posts Carousel block.
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
$display_type           = $attributes['displayType'] ?? 'card';
$post_type              = $attributes['postType'] ?? 'post';
$categories             = $attributes['categories'] ?? array();
$number_of_posts        = $attributes['numberOfPosts'] ?? 6;
$order_by               = $attributes['orderBy'] ?? 'date';
$order                  = $attributes['order'] ?? 'DESC';
$show_excerpt           = $attributes['showExcerpt'] ?? true;
$excerpt_length         = $attributes['excerptLength'] ?? 120;
$show_date              = $attributes['showDate'] ?? true;
$show_author            = $attributes['showAuthor'] ?? true;
$show_category          = $attributes['showCategory'] ?? true;
$date_format            = $attributes['dateFormat'] ?? 'F j, Y';
$slides_per_view        = $attributes['slidesPerView'] ?? 3;
$slides_per_view_tablet = $attributes['slidesPerViewTablet'] ?? 2;
$slides_per_view_mobile = $attributes['slidesPerViewMobile'] ?? 1;
$space_between          = $attributes['spaceBetween'] ?? 30;
$show_navigation        = $attributes['showNavigation'] ?? true;
$show_pagination        = $attributes['showPagination'] ?? true;
$loop                   = $attributes['loop'] ?? true;
$autoplay               = $attributes['autoplay'] ?? false;
$autoplay_delay         = $attributes['autoplayDelay'] ?? 5000;
$card_background        = $attributes['cardBackground'] ?? '#ffffff';
$card_border_radius     = $attributes['cardBorderRadius'] ?? 10;
$category_color         = $attributes['categoryColor'] ?? '#3182ce';
$title_color            = $attributes['titleColor'] ?? '#1a202c';
$excerpt_color          = $attributes['excerptColor'] ?? '#4a5568';
$meta_color             = $attributes['metaColor'] ?? '#718096';
$nav_color              = $attributes['navColor'] ?? '#3182ce';

// Build query args.
$query_args = array(
	'post_type'      => $post_type,
	'posts_per_page' => $number_of_posts,
	'orderby'        => $order_by,
	'order'          => $order,
	'post_status'    => 'publish',
);

// Add category filter if set.
if ( ! empty( $categories ) && 'post' === $post_type ) {
	$query_args['category_name'] = implode( ',', $categories );
}

$posts_query = new WP_Query( $query_args );

if ( ! $posts_query->have_posts() ) {
	echo '<p class="wbcom-essential-posts-carousel-empty">' . esc_html__( 'No posts found.', 'wbcom-essential' ) . '</p>';
	return;
}

// Generate unique ID for this carousel instance.
$carousel_id = 'wbcom-posts-carousel-' . wp_unique_id();

// Swiper configuration data.
$swiper_config = array(
	'slidesPerView'  => $slides_per_view_mobile,
	'spaceBetween'   => $space_between,
	'loop'           => $loop,
	'navigation'     => $show_navigation,
	'pagination'     => $show_pagination,
	'autoplay'       => $autoplay ? array( 'delay' => $autoplay_delay ) : false,
	'breakpoints'    => array(
		768  => array(
			'slidesPerView' => $slides_per_view_tablet,
		),
		1024 => array(
			'slidesPerView' => $slides_per_view,
		),
	),
);

// CSS custom properties for styling.
// Layout styles - always applied.
$style_vars = sprintf(
	'--card-radius: %dpx;',
	absint( $card_border_radius )
);

// Color styles - only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$style_vars .= sprintf(
		' --card-bg: %s; --category-color: %s; --title-color: %s; --excerpt-color: %s; --meta-color: %s; --nav-color: %s;',
		esc_attr( $card_background ),
		esc_attr( $category_color ),
		esc_attr( $title_color ),
		esc_attr( $excerpt_color ),
		esc_attr( $meta_color ),
		esc_attr( $nav_color )
	);
}

// Build wrapper classes.
$wrapper_classes = array(
	'wbcom-essential-posts-carousel',
	'display-type-' . esc_attr( $display_type ),
);

if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $wrapper_classes ),
	'style' => $style_vars,
	'id'    => $carousel_id,
) );
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?> data-swiper-config="<?php echo esc_attr( wp_json_encode( $swiper_config ) ); ?>">
	<div class="swiper">
		<div class="swiper-wrapper">
			<?php
			while ( $posts_query->have_posts() ) :
				$posts_query->the_post();
				$post_id = get_the_ID();
				?>
				<div class="swiper-slide">
					<article class="wbcom-posts-carousel-item">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="wbcom-posts-carousel-image">
								<a href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'medium_large' ); ?>
								</a>
								<?php if ( 'overlay' === $display_type && $show_category ) : ?>
									<?php
									$post_categories = get_the_category();
									if ( ! empty( $post_categories ) ) :
										?>
										<span class="wbcom-posts-carousel-category">
											<?php echo esc_html( $post_categories[0]->name ); ?>
										</span>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="wbcom-posts-carousel-content">
							<?php if ( 'card' === $display_type && $show_category ) : ?>
								<?php
								$post_categories = get_the_category();
								if ( ! empty( $post_categories ) ) :
									?>
									<span class="wbcom-posts-carousel-category">
										<?php echo esc_html( $post_categories[0]->name ); ?>
									</span>
								<?php endif; ?>
							<?php endif; ?>

							<h3 class="wbcom-posts-carousel-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>

							<?php if ( $show_excerpt ) : ?>
								<div class="wbcom-posts-carousel-excerpt">
									<?php
									$excerpt = get_the_excerpt();
									if ( strlen( $excerpt ) > $excerpt_length ) {
										$excerpt = substr( $excerpt, 0, $excerpt_length ) . '...';
									}
									echo wp_kses_post( $excerpt );
									?>
								</div>
							<?php endif; ?>

							<?php if ( $show_date || $show_author ) : ?>
								<div class="wbcom-posts-carousel-meta">
									<?php if ( $show_author ) : ?>
										<span class="wbcom-posts-carousel-author">
											<?php
											printf(
												/* translators: %s: Author name */
												esc_html__( 'By %s', 'wbcom-essential' ),
												'<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a>'
											);
											?>
										</span>
									<?php endif; ?>

									<?php if ( $show_date ) : ?>
										<span class="wbcom-posts-carousel-date">
											<?php echo esc_html( get_the_date( $date_format ) ); ?>
										</span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</article>
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
