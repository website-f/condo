<?php
/**
 * Server-side render for Posts Revolution block.
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
$display_type     = $attributes['displayType'] ?? 'posts_type1';
$columns          = $attributes['columns'] ?? 2;
$show_excerpt     = $attributes['showExcerpt'] ?? true;
$excerpt_length   = $attributes['excerptLength'] ?? 150;
$date_format      = $attributes['dateFormat'] ?? 'F j, Y';
$query_source     = $attributes['querySource'] ?? 'wp_posts';
$custom_post_type = $attributes['customPostType'] ?? '';
$categories       = $attributes['categories'] ?? array();
$sticky_posts     = $attributes['stickyPosts'] ?? 'allposts';
$posts_per_page   = $attributes['postsPerPage'] ?? 5;
$order_by         = $attributes['orderBy'] ?? 'date';
$sort_order       = $attributes['order'] ?? 'DESC';

// Pagination settings.
$enable_pagination = $attributes['enablePagination'] ?? false;
$pagination_type   = $attributes['paginationType'] ?? 'numeric';

// Animation settings.
$enable_animation = $attributes['enableAnimation'] ?? false;
$animation_type   = $attributes['animationType'] ?? 'fade-in';
$animation_delay  = $attributes['animationDelay'] ?? 1000;

// Theme colors toggle.
$use_theme_colors = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;

// Style settings.
$enable_custom_style = $attributes['enableCustomStyle'] ?? false;
$main_color          = $attributes['mainColor'] ?? '#1d76da';
$hover_color         = $attributes['hoverColor'] ?? '#1d76da';

// Generate unique block ID.
$instance = 'wbcom-pr-' . substr( md5( wp_json_encode( $attributes ) ), 0, 8 );

// Get current page for pagination.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_page = isset( $_GET[ 'paged_' . $instance ] ) ? absint( $_GET[ 'paged_' . $instance ] ) : 1;

// Build query args.
$query_args = array(
	'post_status' => 'publish',
	'orderby'     => $order_by,
	'order'       => $sort_order,
	'paged'       => $current_page,
);

// Determine post type.
if ( 'wp_custom_posts_type' === $query_source && ! empty( $custom_post_type ) ) {
	$query_args['post_type'] = $custom_post_type;
} else {
	$query_args['post_type'] = 'post';
}

// Handle pagination vs number of posts.
if ( $enable_pagination ) {
	$query_args['posts_per_page'] = $posts_per_page;
} else {
	$query_args['posts_per_page'] = $posts_per_page;
	$query_args['no_found_rows']  = true;
}

// Handle categories.
if ( ! empty( $categories ) && 'post' === $query_args['post_type'] ) {
	$query_args['category_name'] = implode( ',', array_map( 'sanitize_title', $categories ) );
}

// Only include posts with featured images since all layouts are image-driven.
$query_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	array(
		'key'     => '_thumbnail_id',
		'compare' => 'EXISTS',
	),
);

// Handle sticky posts.
if ( 'onlystickyposts' === $sticky_posts && 'post' === $query_args['post_type'] ) {
	$sticky = get_option( 'sticky_posts' );
	if ( ! empty( $sticky ) ) {
		$query_args['post__in']            = $sticky;
		$query_args['ignore_sticky_posts'] = 1;
	} else {
		$query_args['post__in'] = array( 0 );
	}
}

$loop = new WP_Query( $query_args );

if ( ! $loop->have_posts() ) {
	echo '<p>' . esc_html__( 'No posts found.', 'wbcom-essential' ) . '</p>';
	return;
}

// Build custom styles if enabled.
$custom_styles = '';
if ( $enable_custom_style ) {
	$custom_styles = '<style type="text/css">';
	$selector      = '.wbcom-essential-posts-revolution.' . esc_attr( $display_type ) . '.wbselector-' . esc_attr( $instance );

	if ( in_array( $display_type, array( 'posts_type1', 'posts_type2', 'posts_type3', 'posts_type4', 'posts_type5' ), true ) ) {
		$custom_styles .= $selector . ' .wb-category a { color: ' . esc_attr( $main_color ) . ' !important; }';
		$custom_styles .= $selector . ' .wb-category a:hover { color: ' . esc_attr( $hover_color ) . ' !important; }';
		$custom_styles .= $selector . ' .wb-title a:hover { color: ' . esc_attr( $hover_color ) . ' !important; }';
	}

	if ( 'posts_type6' === $display_type ) {
		$custom_styles .= $selector . ' .wb_last .wb-category a { color: ' . esc_attr( $main_color ) . ' !important; }';
		$custom_styles .= $selector . ' .wb_last .wb-category a:hover { color: ' . esc_attr( $hover_color ) . ' !important; }';
		$custom_styles .= $selector . ' .wb_last .wb-title a:hover { color: ' . esc_attr( $hover_color ) . ' !important; }';
		$custom_styles .= $selector . ' .wb_two_half .wb-category a { background-color: ' . esc_attr( $main_color ) . ' !important; }';
		$custom_styles .= $selector . ' .wb_two_half:first-child:hover .wb-category a { background-color: ' . esc_attr( $hover_color ) . ' !important; }';
		$custom_styles .= $selector . ' .wbcom-essential-posts-second-half:hover .wb-category a { background-color: ' . esc_attr( $hover_color ) . ' !important; }';
	}

	if ( 'posts_type7' === $display_type ) {
		$custom_styles .= $selector . ' .wb-category a { background-color: ' . esc_attr( $main_color ) . ' !important; }';
		$custom_styles .= $selector . ' .wb-category a:hover { background-color: ' . esc_attr( $hover_color ) . ' !important; }';
		$custom_styles .= $selector . ' .wb-title a:hover { color: ' . esc_attr( $hover_color ) . ' !important; }';
	}

	// Pagination styles.
	if ( $enable_pagination ) {
		if ( 'numeric' === $pagination_type ) {
			$custom_styles .= '.wb-pagination.numeric .current { background-color: ' . esc_attr( $main_color ) . ' !important; border-color: ' . esc_attr( $main_color ) . ' !important; }';
			$custom_styles .= '.wb-pagination.numeric a:hover { background-color: ' . esc_attr( $main_color ) . ' !important; border-color: ' . esc_attr( $main_color ) . ' !important; }';
		} else {
			$custom_styles .= '.wb-pagination a:hover { color: ' . esc_attr( $hover_color ) . ' !important; }';
		}
	}

	$custom_styles .= '</style>';
}

if ( ! function_exists( 'wbcom_pr_block_get_excerpt' ) ) {
	/**
	 * Helper function to get a clean, word-boundary excerpt.
	 *
	 * Handles posts with no manual excerpt by generating from content.
	 * Always truncates at word boundaries, never mid-word.
	 *
	 * @param int $length Excerpt length in characters.
	 * @return string
	 */
	function wbcom_pr_block_get_excerpt( $length ) {
		$excerpt = get_the_excerpt();

		// Strip WordPress auto-excerpt artifacts.
		$excerpt = str_replace( array( '[…]', '[&hellip;]', '&hellip;' ), '', $excerpt );
		$excerpt = trim( $excerpt );

		// If excerpt is empty or too short, generate from post content.
		if ( mb_strlen( $excerpt, 'UTF-8' ) < 20 ) {
			$content = get_the_content();
			$content = wp_strip_all_tags( strip_shortcodes( $content ) );
			$content = preg_replace( '/\s+/', ' ', trim( $content ) );
			if ( mb_strlen( $content, 'UTF-8' ) > 0 ) {
				$excerpt = $content;
			}
		}

		if ( empty( $excerpt ) ) {
			return '';
		}

		// Truncate at word boundary.
		if ( mb_strlen( $excerpt, 'UTF-8' ) > $length ) {
			$excerpt = mb_substr( $excerpt, 0, $length, 'UTF-8' );
			// Find last space to avoid cutting mid-word.
			$last_space = mb_strrpos( $excerpt, ' ', 0, 'UTF-8' );
			if ( false !== $last_space && $last_space > $length * 0.6 ) {
				$excerpt = mb_substr( $excerpt, 0, $last_space, 'UTF-8' );
			}
			$excerpt = rtrim( $excerpt, '.,;:!? ' ) . '…';
		}

		return $excerpt;
	}
}

if ( ! function_exists( 'wbcom_pr_block_get_category' ) ) {
	/**
	 * Helper function to get category.
	 *
	 * @param string $source    Query source.
	 * @param string $post_type Custom post type.
	 * @return string
	 */
	function wbcom_pr_block_get_category( $source, $post_type ) {
		if ( 'wp_custom_posts_type' === $source && ! empty( $post_type ) ) {
			$taxonomies = get_object_taxonomies( $post_type, 'objects' );
			foreach ( $taxonomies as $taxonomy ) {
				if ( $taxonomy->hierarchical ) {
					$terms = get_the_terms( get_the_ID(), $taxonomy->name );
					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
						return '<a href="' . esc_url( get_term_link( $terms[0] ) ) . '">' . esc_html( $terms[0]->name ) . '</a>';
					}
				}
			}
		} else {
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				return '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
			}
		}
		return '';
	}
}

if ( ! function_exists( 'wbcom_pr_block_get_thumbnail' ) ) {
	/**
	 * Helper function to get thumbnail.
	 *
	 * @param string $size Image size.
	 * @return string
	 */
	function wbcom_pr_block_get_thumbnail( $size = 'large' ) {
		if ( has_post_thumbnail() ) {
			return '<a href="' . esc_url( get_permalink() ) . '">' . get_the_post_thumbnail( get_the_ID(), $size ) . '</a>';
		}
		return '';
	}
}

// Build wrapper classes.
$wrapper_classes = array(
	'wbcom-essential-posts-revolution',
	esc_attr( $display_type ),
	'wbselector-' . esc_attr( $instance ),
);

if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
	)
);

// Output custom styles.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the style generation above.
echo $custom_styles;

echo '<div class="wbclear"></div>';

// Animation wrapper.
if ( $enable_animation ) {
	echo '<div class="animate-in" data-anim-type="' . esc_attr( $animation_type ) . '" data-anim-delay="' . esc_attr( $animation_delay ) . '">';
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<?php
$count = 0;
if ( 'posts_type3' === $display_type ) {
	$count = 1;
}

while ( $loop->have_posts() ) :
	$loop->the_post();
	$post_link = get_permalink();

	switch ( $display_type ) :
		case 'posts_type1':
			// TYPE 1: Featured (3/5) + Sidebar (2/5) with thumbnails.
			if ( 0 === $count ) {
				?>
				<div class="wb_three_fifth">
					<div class="wbcom-essential-posts-revolution-thumbs-container">
						<?php echo wbcom_pr_block_get_thumbnail( 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-left">
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<?php if ( $show_excerpt ) : ?>
							<span class="wb-content"><?php echo esc_html( wbcom_pr_block_get_excerpt( $excerpt_length ) ); ?></span>
						<?php endif; ?>
						<span class="wb-author"><?php echo esc_html( get_the_author() ); ?></span>
						<span class="wb-date"><?php echo esc_html( get_the_date( $date_format ) ); ?></span>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="wb_two_fifth wb_last">
					<div class="wbcom-essential-posts-revolution-thumbs-container wb_one_third">
						<?php echo wbcom_pr_block_get_thumbnail( 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-right wb_two_third wb_last">
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</div>
					<div class="wbclear"></div>
				</div>
				<?php
			}
			break;

		case 'posts_type2':
			// TYPE 2: Featured full width + list below.
			if ( 0 === $count ) {
				?>
				<div class="wb-columns-1 firstpost">
					<div class="wbcom-essential-posts-revolution-thumbs-container">
						<?php echo wbcom_pr_block_get_thumbnail( 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-left">
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<?php if ( $show_excerpt ) : ?>
							<span class="wb-content"><?php echo esc_html( wbcom_pr_block_get_excerpt( $excerpt_length ) ); ?></span>
						<?php endif; ?>
						<span class="wb-author"><?php echo esc_html( get_the_author() ); ?></span>
						<span class="wb-date"><?php echo esc_html( get_the_date( $date_format ) ); ?></span>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="wb-columns-1 moreposts">
					<div class="wbcom-essential-posts-revolution-thumbs-container wb_one_third">
						<?php echo wbcom_pr_block_get_thumbnail( 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-right wb_two_third wb_last">
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<?php if ( $show_excerpt ) : ?>
							<span class="wb-content"><?php echo esc_html( wbcom_pr_block_get_excerpt( $excerpt_length ) ); ?></span>
						<?php endif; ?>
					</div>
					<div class="wbclear"></div>
				</div>
				<?php
			}
			break;

		case 'posts_type3':
			// TYPE 3: Grid layout with columns.
			?>
			<div class="wb-columns-<?php echo esc_attr( $columns ); ?>">
				<div class="wbcom-essential-posts-revolution-thumbs-container">
					<?php echo wbcom_pr_block_get_thumbnail( 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="wb-info-left">
					<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
					<?php if ( $show_excerpt ) : ?>
						<span class="wb-content"><?php echo esc_html( wbcom_pr_block_get_excerpt( $excerpt_length ) ); ?></span>
					<?php endif; ?>
					<span class="wb-author"><?php echo esc_html( get_the_author() ); ?></span>
					<span class="wb-date"><?php echo esc_html( get_the_date( $date_format ) ); ?></span>
				</div>
			</div>
			<?php
			if ( 0 === $count % $columns ) {
				echo '<br class="wbclear">';
			}
			break;

		case 'posts_type4':
			// TYPE 4: Side by side (image left, content right).
			?>
			<div class="container-display4">
				<div class="wbcom-essential-posts-revolution-thumbs-container wb_one_half">
					<?php echo wbcom_pr_block_get_thumbnail( 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="wb-info-right wb_one_half wb_last">
					<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
					<?php if ( $show_excerpt ) : ?>
						<span class="wb-content"><?php echo esc_html( wbcom_pr_block_get_excerpt( $excerpt_length ) ); ?></span>
					<?php endif; ?>
					<span class="wb-author"><?php echo esc_html( get_the_author() ); ?></span>
					<span class="wb-date"><?php echo esc_html( get_the_date( $date_format ) ); ?></span>
				</div>
				<div class="wbclear"></div>
			</div>
			<?php
			break;

		case 'posts_type5':
			// TYPE 5: Featured (3/5) + Text-only sidebar (2/5).
			if ( 0 === $count ) {
				?>
				<div class="wb_three_fifth">
					<div class="wbcom-essential-posts-revolution-thumbs-container">
						<?php echo wbcom_pr_block_get_thumbnail( 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-left">
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<?php if ( $show_excerpt ) : ?>
							<span class="wb-content"><?php echo esc_html( wbcom_pr_block_get_excerpt( $excerpt_length ) ); ?></span>
						<?php endif; ?>
						<span class="wb-author"><?php echo esc_html( get_the_author() ); ?></span>
						<span class="wb-date"><?php echo esc_html( get_the_date( $date_format ) ); ?></span>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="wb_two_fifth wb_last">
					<div class="wb-info-right wb_last">
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</div>
					<div class="wbclear"></div>
				</div>
				<?php
			}
			break;

		case 'posts_type6':
			// TYPE 6: Magazine style - 1 large + 3 medium + rest small.
			if ( 0 === $count ) {
				?>
				<div class="wb_two_half">
					<div class="wbcom-essential-posts-revolution-thumbs-container">
						<?php echo wbcom_pr_block_get_thumbnail( 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-left">
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<?php if ( $show_excerpt ) : ?>
							<span class="wb-content"><?php echo esc_html( wbcom_pr_block_get_excerpt( $excerpt_length ) ); ?></span>
						<?php endif; ?>
						<span class="wb-author"><?php echo esc_html( get_the_author() ); ?></span>
						<span class="wb-date"><?php echo esc_html( get_the_date( $date_format ) ); ?></span>
					</div>
				</div>
				<?php
			} elseif ( $count >= 1 && $count <= 3 ) {
				if ( 1 === $count ) {
					echo '<div class="wb_two_half">';
				}
				?>
				<div class="wbcom-essential-posts-second-half">
					<div class="wbcom-essential-posts-revolution-thumbs-container">
						<?php echo wbcom_pr_block_get_thumbnail( 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-left">
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<?php if ( $show_excerpt ) : ?>
							<span class="wb-content"><?php echo esc_html( wbcom_pr_block_get_excerpt( $excerpt_length ) ); ?></span>
						<?php endif; ?>
						<span class="wb-author"><?php echo esc_html( get_the_author() ); ?></span>
						<span class="wb-date"><?php echo esc_html( get_the_date( $date_format ) ); ?></span>
					</div>
				</div>
				<?php
				if ( 3 === $count ) {
					echo '</div>';
				}
			} else {
				?>
				<div class="wb_two_fifth wb_last">
					<div class="wbcom-essential-posts-revolution-thumbs-container wb_one_third">
						<?php echo wbcom_pr_block_get_thumbnail( 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-right wb_two_third wb_last">
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</div>
					<div class="wbclear"></div>
				</div>
				<?php
			}
			break;

		case 'posts_type7':
			// TYPE 7: Two large posts + rest as list.
			if ( $count <= 1 ) {
				?>
				<div class="wb_two_half">
					<div class="wbcom-essential-posts-revolution-thumbs-container">
						<?php echo wbcom_pr_block_get_thumbnail( 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-left">
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<span class="wb-author"><?php echo esc_html( get_the_author() ); ?></span>
						<span class="wb-date"><?php echo esc_html( get_the_date( $date_format ) ); ?></span>
						<div class="clearfix wbclear"></div>
						<?php if ( $show_excerpt ) : ?>
							<span class="wb-content"><?php echo esc_html( wbcom_pr_block_get_excerpt( $excerpt_length ) ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="wb_two_fifth wb_last">
					<div class="wbcom-essential-posts-revolution-thumbs-container wb_one_third">
						<?php echo wbcom_pr_block_get_thumbnail( 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-right wb_two_third wb_last">
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<span class="wb-date"><?php echo esc_html( get_the_date( $date_format ) ); ?></span>
					</div>
					<div class="wbclear"></div>
				</div>
				<?php
			}
			break;

		default:
			// Default to type 1.
			if ( 0 === $count ) {
				?>
				<div class="wb_three_fifth">
					<div class="wbcom-essential-posts-revolution-thumbs-container">
						<?php echo wbcom_pr_block_get_thumbnail( 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-left">
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<?php if ( $show_excerpt ) : ?>
							<span class="wb-content"><?php echo esc_html( wbcom_pr_block_get_excerpt( $excerpt_length ) ); ?></span>
						<?php endif; ?>
						<span class="wb-author"><?php echo esc_html( get_the_author() ); ?></span>
						<span class="wb-date"><?php echo esc_html( get_the_date( $date_format ) ); ?></span>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="wb_two_fifth wb_last">
					<div class="wbcom-essential-posts-revolution-thumbs-container wb_one_third">
						<?php echo wbcom_pr_block_get_thumbnail( 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="wb-info-right wb_two_third wb_last">
						<span class="wb-title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( get_the_title() ); ?></a></span>
						<span class="wb-category"><?php echo wbcom_pr_block_get_category( $query_source, $custom_post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</div>
					<div class="wbclear"></div>
				</div>
				<?php
			}
	endswitch;

	++$count;
endwhile;

// Pagination.
if ( $enable_pagination && $loop->max_num_pages > 1 ) :
	echo '<div class="wbclear"></div>';
	echo '<div class="wb-post-display-' . esc_attr( $instance ) . ' wb-pagination ' . esc_attr( $pagination_type ) . '">';

	if ( 'numeric' === $pagination_type ) {
		$pagination_args = array(
			'base'      => add_query_arg( 'paged_' . $instance, '%#%' ),
			'format'    => '',
			'current'   => $current_page,
			'total'     => $loop->max_num_pages,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'type'      => 'list',
			'mid_size'  => 2,
			'end_size'  => 1,
		);
		echo wp_kses_post( paginate_links( $pagination_args ) );
	} else {
		$older = get_next_posts_link( esc_html__( 'Older posts', 'wbcom-essential' ), $loop->max_num_pages );
		$newer = get_previous_posts_link( esc_html__( 'Newer posts', 'wbcom-essential' ) );
		if ( $older ) {
			echo wp_kses_post( $older );
		}
		if ( $newer ) {
			echo wp_kses_post( $newer );
		}
	}

	echo '</div>';
endif;
?>
</div>
<?php

if ( $enable_animation ) {
	echo '</div>';
}

wp_reset_postdata();
