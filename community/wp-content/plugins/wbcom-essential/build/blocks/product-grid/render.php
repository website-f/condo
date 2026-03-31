<?php
/**
 * Product Grid Block - Server-Side Render
 *
 * @package wbcom-essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if WooCommerce is active.
if ( ! class_exists( 'WooCommerce' ) ) {
	echo '<p>' . esc_html__( 'WooCommerce is required for this block.', 'wbcom-essential' ) . '</p>';
	return;
}

// Extract attributes.
$use_theme_colors   = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$columns            = isset( $attributes['columns'] ) ? absint( $attributes['columns'] ) : 4;
$rows               = isset( $attributes['rows'] ) ? absint( $attributes['rows'] ) : 2;
$category           = ! empty( $attributes['category'] ) ? sanitize_text_field( $attributes['category'] ) : '';
$order_by           = ! empty( $attributes['orderBy'] ) ? sanitize_text_field( $attributes['orderBy'] ) : 'date';
$order              = ! empty( $attributes['order'] ) ? sanitize_text_field( $attributes['order'] ) : 'desc';
$show_on_sale       = ! empty( $attributes['showOnSale'] );
$show_featured      = ! empty( $attributes['showFeatured'] );
$show_sale_badge    = isset( $attributes['showSaleBadge'] ) ? $attributes['showSaleBadge'] : true;
$show_rating        = isset( $attributes['showRating'] ) ? $attributes['showRating'] : true;
$show_price         = isset( $attributes['showPrice'] ) ? $attributes['showPrice'] : true;
$show_add_to_cart   = isset( $attributes['showAddToCart'] ) ? $attributes['showAddToCart'] : true;
$gap                = isset( $attributes['gap'] ) ? absint( $attributes['gap'] ) : 24;
$image_ratio        = ! empty( $attributes['imageRatio'] ) ? $attributes['imageRatio'] : '1';
$card_bg_color      = ! empty( $attributes['cardBgColor'] ) ? $attributes['cardBgColor'] : '';
$card_border_radius = isset( $attributes['cardBorderRadius'] ) ? absint( $attributes['cardBorderRadius'] ) : 8;
$title_color        = ! empty( $attributes['titleColor'] ) ? $attributes['titleColor'] : '';
$price_color        = ! empty( $attributes['priceColor'] ) ? $attributes['priceColor'] : '';
$sale_badge_color   = ! empty( $attributes['saleBadgeColor'] ) ? $attributes['saleBadgeColor'] : '#ffffff';
$sale_badge_bg      = ! empty( $attributes['saleBadgeBgColor'] ) ? $attributes['saleBadgeBgColor'] : '#e53935';

// Build query args.
$args = array(
	'post_type'      => 'product',
	'posts_per_page' => $columns * $rows,
	'post_status'    => 'publish',
	'orderby'        => $order_by,
	'order'          => strtoupper( $order ),
);

// Handle WooCommerce-specific ordering.
if ( 'price' === $order_by ) {
	$args['meta_key'] = '_price';
	$args['orderby']  = 'meta_value_num';
} elseif ( 'popularity' === $order_by ) {
	$args['meta_key'] = 'total_sales';
	$args['orderby']  = 'meta_value_num';
} elseif ( 'rating' === $order_by ) {
	$args['meta_key'] = '_wc_average_rating';
	$args['orderby']  = 'meta_value_num';
}

// Category filter.
if ( ! empty( $category ) ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $category,
		),
	);
}

// On sale filter.
if ( $show_on_sale ) {
	$args['meta_query'][] = array(
		'key'     => '_sale_price',
		'value'   => '',
		'compare' => '!=',
	);
}

// Featured filter.
if ( $show_featured ) {
	$args['tax_query'][] = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => 'featured',
	);
	if ( count( $args['tax_query'] ) > 1 ) {
		$args['tax_query']['relation'] = 'AND';
	}
}

$products = new WP_Query( $args );

if ( ! $products->have_posts() ) {
	echo '<p>' . esc_html__( 'No products found.', 'wbcom-essential' ) . '</p>';
	return;
}

// Build inline styles - Layout variables (always applied).
$layout_vars = array(
	'--product-columns'    => $columns,
	'--product-gap'        => $gap . 'px',
	'--card-border-radius' => $card_border_radius . 'px',
	'--image-ratio'        => $image_ratio,
);

// Build style string for layout.
$style_string = '';
foreach ( $layout_vars as $property => $value ) {
	$style_string .= esc_attr( $property ) . ':' . esc_attr( $value ) . ';';
}

// Color variables (only when NOT using theme colors).
if ( ! $use_theme_colors ) {
	$color_vars = array(
		'--card-bg-color'       => $card_bg_color,
		'--title-color'         => $title_color,
		'--price-color'         => $price_color,
		'--sale-badge-color'    => $sale_badge_color,
		'--sale-badge-bg-color' => $sale_badge_bg,
	);
	$color_vars = array_filter( $color_vars );
	foreach ( $color_vars as $property => $value ) {
		$style_string .= esc_attr( $property ) . ':' . esc_attr( $value ) . ';';
	}
}

// Build wrapper classes.
$wrapper_classes = array( 'wbcom-essential-product-grid' );
if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => $style_string,
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wbcom-essential-product-grid__items">
		<?php
		while ( $products->have_posts() ) :
			$products->the_post();
			global $product;

			if ( ! $product instanceof WC_Product ) {
				continue;
			}
			?>
			<div class="wbcom-essential-product-grid__item">
				<div class="wbcom-essential-product-grid__card">
					<div class="wbcom-essential-product-grid__image">
						<a href="<?php the_permalink(); ?>">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'woocommerce_thumbnail' );
							} else {
								echo wc_placeholder_img( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce core function returns safe HTML.
							}
							?>
						</a>
						<?php if ( $show_sale_badge && $product->is_on_sale() ) : ?>
							<span class="wbcom-essential-product-grid__sale-badge">
								<?php esc_html_e( 'Sale!', 'wbcom-essential' ); ?>
							</span>
						<?php endif; ?>
					</div>

					<div class="wbcom-essential-product-grid__content">
						<h3 class="wbcom-essential-product-grid__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>

						<?php if ( $show_rating && wc_review_ratings_enabled() ) : ?>
							<div class="wbcom-essential-product-grid__rating">
								<?php
								$rating = $product->get_average_rating();
								echo wc_get_rating_html( $rating ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce core function returns safe HTML.
								?>
							</div>
						<?php endif; ?>

						<?php if ( $show_price ) : ?>
							<div class="wbcom-essential-product-grid__price">
								<?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endif; ?>

						<?php if ( $show_add_to_cart ) : ?>
							<div class="wbcom-essential-product-grid__actions">
								<?php
								woocommerce_template_loop_add_to_cart(
									array(
										'class' => 'wbcom-essential-product-grid__add-to-cart button',
									)
								);
								?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endwhile; ?>
	</div>
</div>
<?php
wp_reset_postdata();
