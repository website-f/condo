<?php
/**
 * Server-side render for Post Timeline block.
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
$use_theme_colors   = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$post_type          = $attributes['postType'] ?? 'post';
$categories         = $attributes['categories'] ?? array();
$number_of_posts    = $attributes['numberOfPosts'] ?? 6;
$order_by           = $attributes['orderBy'] ?? 'date';
$order              = $attributes['order'] ?? 'DESC';
$show_thumbnail     = $attributes['showThumbnail'] ?? true;
$show_excerpt       = $attributes['showExcerpt'] ?? true;
$excerpt_length     = $attributes['excerptLength'] ?? 140;
$show_button        = $attributes['showButton'] ?? true;
$button_text        = $attributes['buttonText'] ?? __( 'Read More', 'wbcom-essential' );
$layout             = $attributes['layout'] ?? 'two-column';
$date_format        = $attributes['dateFormat'] ?? 'M j, Y';
$bar_color          = $attributes['barColor'] ?? '#e2e8f0';
$bar_width          = $attributes['barWidth'] ?? 4;
$dot_color          = $attributes['dotColor'] ?? '#3182ce';
$dot_size           = $attributes['dotSize'] ?? 16;
$card_background    = $attributes['cardBackground'] ?? '#ffffff';
$card_border_radius = $attributes['cardBorderRadius'] ?? 8;
$title_color        = $attributes['titleColor'] ?? '#1a202c';
$excerpt_color      = $attributes['excerptColor'] ?? '#4a5568';
$date_color         = $attributes['dateColor'] ?? '#718096';
$button_bg_color    = $attributes['buttonBgColor'] ?? '#3182ce';
$button_text_color  = $attributes['buttonTextColor'] ?? '#ffffff';

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
	echo '<p class="wbcom-essential-post-timeline-empty">' . esc_html__( 'No posts found.', 'wbcom-essential' ) . '</p>';
	return;
}

// CSS custom properties for styling.
// Layout styles - always applied.
$style_vars = sprintf(
	'--bar-width: %dpx; --dot-size: %dpx; --card-radius: %dpx;',
	absint( $bar_width ),
	absint( $dot_size ),
	absint( $card_border_radius )
);

// Color styles - only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$style_vars .= sprintf(
		' --bar-color: %s; --dot-color: %s; --card-bg: %s; --title-color: %s; --excerpt-color: %s; --date-color: %s; --button-bg: %s; --button-text: %s;',
		esc_attr( $bar_color ),
		esc_attr( $dot_color ),
		esc_attr( $card_background ),
		esc_attr( $title_color ),
		esc_attr( $excerpt_color ),
		esc_attr( $date_color ),
		esc_attr( $button_bg_color ),
		esc_attr( $button_text_color )
	);
}

// Build wrapper classes.
$wrapper_classes = array(
	'wbcom-essential-post-timeline',
	'layout-' . esc_attr( $layout ),
);

if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $wrapper_classes ),
	'style' => $style_vars,
) );
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<div class="wbcom-post-timeline-bar"></div>
	<div class="wbcom-post-timeline-items">
		<?php
		$index = 0;
		while ( $posts_query->have_posts() ) :
			$posts_query->the_post();
			$is_left = ( $index % 2 === 0 );
			$side_class = ( 'two-column' === $layout ) ? ( $is_left ? 'side-left' : 'side-right' ) : 'side-right';
			?>
			<div class="wbcom-post-timeline-item <?php echo esc_attr( $side_class ); ?>">
				<div class="wbcom-post-timeline-dot"></div>
				<div class="wbcom-post-timeline-date">
					<?php echo esc_html( get_the_date( $date_format ) ); ?>
				</div>
				<div class="wbcom-post-timeline-card">
					<?php if ( $show_thumbnail && has_post_thumbnail() ) : ?>
						<div class="wbcom-post-timeline-thumbnail">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'medium_large' ); ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="wbcom-post-timeline-content">
						<h3 class="wbcom-post-timeline-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>

						<?php if ( $show_excerpt && $excerpt_length > 0 ) : ?>
							<div class="wbcom-post-timeline-excerpt">
								<?php
								$excerpt = get_the_excerpt();
								if ( strlen( $excerpt ) > $excerpt_length ) {
									$excerpt = substr( $excerpt, 0, $excerpt_length ) . '...';
								}
								echo wp_kses_post( $excerpt );
								?>
							</div>
						<?php endif; ?>

						<?php if ( $show_button ) : ?>
							<a href="<?php the_permalink(); ?>" class="wbcom-post-timeline-button">
								<?php echo esc_html( $button_text ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
			$index++;
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</div>
