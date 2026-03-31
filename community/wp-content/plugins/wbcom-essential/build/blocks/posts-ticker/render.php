<?php
/**
 * Server-side render for Posts Ticker block.
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
$ticker_type     = $attributes['tickerType'] ?? 'horizontal';
$ticker_label    = $attributes['tickerLabel'] ?? 'Latest News';
$show_label      = $attributes['showLabel'] ?? true;
$speed           = $attributes['speed'] ?? 50;
$pause_on_hover  = $attributes['pauseOnHover'] ?? true;
$show_controls   = $attributes['showControls'] ?? true;
$show_thumbnail  = $attributes['showThumbnail'] ?? false;
$show_date       = $attributes['showDate'] ?? false;
$date_format     = $attributes['dateFormat'] ?? 'M j, Y';
$post_type       = $attributes['postType'] ?? 'post';
$categories      = $attributes['categories'] ?? array();
$posts_per_page  = $attributes['postsPerPage'] ?? 10;
$order_by        = $attributes['orderBy'] ?? 'date';
$order           = $attributes['order'] ?? 'DESC';

// Theme colors toggle.
$use_theme_colors = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;

// Colors.
$label_bg_color   = $attributes['labelBgColor'] ?? '#1d76da';
$label_text_color = $attributes['labelTextColor'] ?? '#ffffff';
$ticker_bg_color  = $attributes['tickerBgColor'] ?? '#f8f9fa';
$text_color       = $attributes['textColor'] ?? '#122B46';
$hover_color      = $attributes['hoverColor'] ?? '#1d76da';
$border_color     = $attributes['borderColor'] ?? '#e3e3e3';
$height           = $attributes['height'] ?? 50;

// Build query args.
$query_args = array(
	'post_type'      => $post_type,
	'posts_per_page' => $posts_per_page,
	'orderby'        => $order_by,
	'order'          => $order,
	'post_status'    => 'publish',
);

// Add category filter if set.
if ( ! empty( $categories ) && 'post' === $post_type ) {
	$query_args['cat'] = implode( ',', $categories );
}

$posts = get_posts( $query_args );

if ( empty( $posts ) ) {
	return;
}

// Build inline styles - layout always, colors only when not using theme colors.
$inline_styles = array(
	'--ticker-height' => $height . 'px',
);

// Add color styles only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--label-bg-color']   = $label_bg_color;
	$inline_styles['--label-text-color'] = $label_text_color;
	$inline_styles['--ticker-bg-color']  = $ticker_bg_color;
	$inline_styles['--text-color']       = $text_color;
	$inline_styles['--hover-color']      = $hover_color;
	$inline_styles['--border-color']     = $border_color;
}

$style_string = '';
foreach ( $inline_styles as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Build wrapper classes.
$wrapper_classes = array( 'wbcom-essential-posts-ticker-wrapper' );
if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $wrapper_classes ),
	'style' => $style_string,
) );

// Generate unique ID.
$ticker_id = 'wbcom-ticker-' . wp_unique_id();
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<div
		class="wbcom-essential-posts-ticker wbcom-essential-posts-ticker--<?php echo esc_attr( $ticker_type ); ?>"
		id="<?php echo esc_attr( $ticker_id ); ?>"
		data-ticker-type="<?php echo esc_attr( $ticker_type ); ?>"
		data-speed="<?php echo esc_attr( $speed ); ?>"
		data-pause-on-hover="<?php echo $pause_on_hover ? 'true' : 'false'; ?>"
	>
		<?php if ( $show_label ) : ?>
			<div class="wbcom-essential-posts-ticker__label">
				<span><?php echo esc_html( $ticker_label ); ?></span>
			</div>
		<?php endif; ?>

		<div class="wbcom-essential-posts-ticker__content">
			<div class="wbcom-essential-posts-ticker__track">
				<ul class="wbcom-essential-posts-ticker__list">
					<?php foreach ( $posts as $post ) : ?>
						<li class="wbcom-essential-posts-ticker__item">
							<?php if ( $show_thumbnail && has_post_thumbnail( $post->ID ) ) : ?>
								<span class="wbcom-essential-posts-ticker__thumb">
									<?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?>
								</span>
							<?php endif; ?>

							<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="wbcom-essential-posts-ticker__link">
								<?php echo esc_html( get_the_title( $post->ID ) ); ?>
							</a>

							<?php if ( $show_date ) : ?>
								<span class="wbcom-essential-posts-ticker__date">
									<?php echo esc_html( get_the_date( $date_format, $post->ID ) ); ?>
								</span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

		<?php if ( $show_controls ) : ?>
			<div class="wbcom-essential-posts-ticker__controls">
				<button type="button" class="wbcom-essential-posts-ticker__prev" aria-label="<?php esc_attr_e( 'Previous', 'wbcom-essential' ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
						<path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
					</svg>
				</button>
				<button type="button" class="wbcom-essential-posts-ticker__pause" aria-label="<?php esc_attr_e( 'Pause', 'wbcom-essential' ); ?>">
					<svg class="pause-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
						<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
					</svg>
					<svg class="play-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="display:none;">
						<path d="M8 5v14l11-7z"/>
					</svg>
				</button>
				<button type="button" class="wbcom-essential-posts-ticker__next" aria-label="<?php esc_attr_e( 'Next', 'wbcom-essential' ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
						<path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/>
					</svg>
				</button>
			</div>
		<?php endif; ?>
	</div>
</div>
