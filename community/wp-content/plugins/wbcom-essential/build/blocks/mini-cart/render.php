<?php
/**
 * Mini Cart Block - Server-Side Render
 *
 * @package wbcom-essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if WooCommerce is active.
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$show_icon     = isset( $attributes['showIcon'] ) ? $attributes['showIcon'] : true;
$show_count    = isset( $attributes['showCount'] ) ? $attributes['showCount'] : true;
$show_total    = isset( $attributes['showTotal'] ) ? $attributes['showTotal'] : true;
$show_dropdown = isset( $attributes['showDropdown'] ) ? $attributes['showDropdown'] : true;
$icon_size     = isset( $attributes['iconSize'] ) ? absint( $attributes['iconSize'] ) : 24;

// Theme colors toggle.
$use_theme_colors = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;

// Colors.
$icon_color       = ! empty( $attributes['iconColor'] ) ? $attributes['iconColor'] : '';
$count_bg_color   = ! empty( $attributes['countBgColor'] ) ? $attributes['countBgColor'] : '#e53935';
$count_text_color = ! empty( $attributes['countTextColor'] ) ? $attributes['countTextColor'] : '#ffffff';
$total_color      = ! empty( $attributes['totalColor'] ) ? $attributes['totalColor'] : '';
$dropdown_bg      = ! empty( $attributes['dropdownBgColor'] ) ? $attributes['dropdownBgColor'] : '#ffffff';

// Get cart data - ensure WC() is available.
$wc_instance = WC();
$cart_count  = ( $wc_instance && $wc_instance->cart ) ? $wc_instance->cart->get_cart_contents_count() : 0;
$cart_total  = ( $wc_instance && $wc_instance->cart ) ? $wc_instance->cart->get_cart_total() : wc_price( 0 );
$cart_url    = wc_get_cart_url();

// Build inline styles - layout always, colors only when not using theme colors.
$inline_styles = array(
	'--icon-size' => $icon_size . 'px',
);

// Add color styles only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--icon-color']       = $icon_color;
	$inline_styles['--count-bg-color']   = $count_bg_color;
	$inline_styles['--count-text-color'] = $count_text_color;
	$inline_styles['--total-color']      = $total_color;
	$inline_styles['--dropdown-bg']      = $dropdown_bg;
}

$inline_styles = array_filter( $inline_styles );
$style_string  = '';
foreach ( $inline_styles as $property => $value ) {
	$style_string .= esc_attr( $property ) . ':' . esc_attr( $value ) . ';';
}

// Build wrapper classes.
$wrapper_classes = array( 'wbcom-essential-mini-cart' );
if ( $show_dropdown ) {
	$wrapper_classes[] = 'has-dropdown';
}
if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'         => implode( ' ', $wrapper_classes ),
		'style'         => $style_string,
		'data-dropdown' => $show_dropdown ? 'true' : 'false',
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<a href="<?php echo esc_url( $cart_url ); ?>" class="wbcom-essential-mini-cart__trigger">
		<?php if ( $show_icon ) : ?>
			<span class="wbcom-essential-mini-cart__icon">
				<span class="dashicons dashicons-cart"></span>
			</span>
		<?php endif; ?>

		<?php if ( $show_count ) : ?>
			<span class="wbcom-essential-mini-cart__count" data-cart-count="<?php echo esc_attr( $cart_count ); ?>">
				<?php echo esc_html( $cart_count ); ?>
			</span>
		<?php endif; ?>

		<?php if ( $show_total ) : ?>
			<span class="wbcom-essential-mini-cart__total" data-cart-total>
				<?php echo wp_kses_post( $cart_total ); ?>
			</span>
		<?php endif; ?>
	</a>

	<?php if ( $show_dropdown ) : ?>
		<!-- Side Drawer for Cart -->
		<div class="wbcom-essential-mini-cart__drawer" aria-hidden="true">
			<div class="wbcom-essential-mini-cart__drawer-overlay"></div>
			<div class="wbcom-essential-mini-cart__drawer-content">
				<div class="wbcom-essential-mini-cart__drawer-header">
					<h3 class="wbcom-essential-mini-cart__drawer-title"><?php esc_html_e( 'Shopping Cart', 'wbcom-essential' ); ?></h3>
					<button type="button" class="wbcom-essential-mini-cart__drawer-close" aria-label="<?php esc_attr_e( 'Close cart', 'wbcom-essential' ); ?>">
						<span class="close-text"><?php esc_html_e( 'Close', 'wbcom-essential' ); ?></span>
						<span class="close-icon">&mdash;</span>
					</button>
				</div>
				<div class="wbcom-essential-mini-cart__drawer-body">
					<div class="widget_shopping_cart_content">
						<?php
						if ( function_exists( 'woocommerce_mini_cart' ) ) {
							woocommerce_mini_cart();
						}
						?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
