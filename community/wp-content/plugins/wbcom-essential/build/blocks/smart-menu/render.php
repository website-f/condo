<?php
/**
 * Server-side render for Smart Menu block.
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

// Handle backward compatibility with old attribute names.
$menu_id = isset( $attributes['menuId'] ) ? absint( $attributes['menuId'] ) : ( isset( $attributes['menu'] ) ? absint( $attributes['menu'] ) : 0 );

// Early return if no menu selected.
if ( ! $menu_id ) {
	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'wbcom-essential-smart-menu' ) );
	?>
	<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="smart-menu-placeholder">
			<p><?php esc_html_e( 'Please select a menu.', 'wbcom-essential' ); ?></p>
		</div>
	</div>
	<?php
	return;
}

// Extract attributes with defaults.
$use_theme_colors = $attributes['useThemeColors'] ?? false;
$menu_layout      = $attributes['menuLayout'] ?? 'horizontal';
$menu_align       = $attributes['menuAlign'] ?? ( $attributes['menuHAlign'] ?? 'flex-start' );
$vertical_width   = $attributes['verticalMenuWidth'] ?? '100%';
$show_toggle      = $attributes['showMobileToggle'] ?? ( $attributes['menuToggle'] ?? true );
$toggle_text      = $attributes['mobileToggleText'] ?? ( $attributes['menuToggleText'] ?? 'MENU' );
$toggle_align     = $attributes['mobileToggleAlign'] ?? ( $attributes['menuToggleTextHAlign'] ?? 'flex-start' );
$dropdown_icon    = $attributes['dropdownIcon'] ?? 'chevron-down';
$breakpoint       = $attributes['mobileBreakpoint'] ?? ( $attributes['menuBreakpoint'] ?? 1024 );

// Handle verticalMenuWidth - could be string or object.
if ( is_array( $vertical_width ) && isset( $vertical_width['size'] ) ) {
	$vertical_width = $vertical_width['size'] . ( $vertical_width['unit'] ?? '%' );
}

// Styling attributes.
$main_menu_bg                = $attributes['mainMenuBackground'] ?? array();
$main_menu_transition        = $attributes['mainMenuTransitionDuration'] ?? 0.2;
$dropdown_icon_size          = $attributes['dropdownIconSize'] ?? 14;
$main_menu_item_color        = $attributes['mainMenuItemColor'] ?? '';
$main_menu_item_bg           = $attributes['mainMenuItemBg'] ?? '';
$main_menu_item_color_hover  = $attributes['mainMenuItemColorHover'] ?? '';
$main_menu_item_bg_hover     = $attributes['mainMenuItemBgHover'] ?? '';
$main_menu_item_color_active = $attributes['mainMenuItemColorActive'] ?? '';
$main_menu_item_bg_active    = $attributes['mainMenuItemBgActive'] ?? '';

$sub_menu_bg                = $attributes['subMenuBg'] ?? '';
$sub_menu_item_color        = $attributes['subMenuItemColor'] ?? '';
$sub_menu_item_bg           = $attributes['subMenuItemBg'] ?? '';
$sub_menu_item_color_hover  = $attributes['subMenuItemColorHover'] ?? '';
$sub_menu_item_bg_hover     = $attributes['subMenuItemBgHover'] ?? '';
$sub_menu_item_color_active = $attributes['subMenuItemColorActive'] ?? '';
$sub_menu_item_bg_active    = $attributes['subMenuItemBgActive'] ?? '';
$mobile_menu_color          = $attributes['mobileMenuColor'] ?? '';
$mobile_menu_bg             = $attributes['mobileMenuBackground'] ?? array();
$mobile_menu_width          = $attributes['mobileMenuWidth'] ?? array();

// New submenu attributes.
$collapsible_behavior    = $attributes['collapsibleBehavior'] ?? 'link';
$submenu_animation       = $attributes['submenuAnimation'] ?? '';
$submenu_min_width       = $attributes['submenuMinWidth'] ?? array(
	'size' => 10,
	'unit' => 'em',
);
$submenu_max_width       = $attributes['submenuMaxWidth'] ?? array(
	'size' => 20,
	'unit' => 'em',
);
$submenu_offset_x        = $attributes['submenuOffsetX'] ?? 0;
$submenu_offset_y        = $attributes['submenuOffsetY'] ?? 0;
$submenu_level2_offset_x = $attributes['submenuLevel2OffsetX'] ?? 0;
$submenu_level2_offset_y = $attributes['submenuLevel2OffsetY'] ?? 0;
$submenu_transition      = $attributes['submenuTransitionDuration'] ?? 0.3;
$submenu_indicator_icon  = $attributes['submenuIndicatorIcon'] ?? 'caret';
$submenu_indicator_size  = $attributes['submenuIndicatorIconSize'] ?? 12;

// Build classes.
$container_classes = array( 'smart-menu-container' );
$menu_classes      = array(
	'smart-menu',
	$menu_layout,
);

// Add alignment class to menu classes.
if ( 'horizontal-justified' !== $menu_layout && $menu_align ) {
	$menu_classes[] = 'align-' . str_replace( '-', '', $menu_align );
}

// Build container styles.
$container_style = '';
if ( 'vertical' === $menu_layout ) {
	$container_style = 'width: ' . esc_attr( $vertical_width ) . ';';
}
// Only add custom background color when NOT using theme colors.
if ( ! $use_theme_colors && ! empty( $main_menu_bg['color'] ) ) {
	$container_style .= 'background-color: ' . esc_attr( $main_menu_bg['color'] ) . ';';
}
$container_style .= '--transition-duration: ' . esc_attr( $main_menu_transition ) . 's;';

// Toggle container style.
$toggle_container_style = 'justify-content: ' . esc_attr( $toggle_align ) . ';';

// Mobile toggle styles - colors only when NOT using theme colors.
$toggle_style = '';
if ( ! $use_theme_colors ) {
	if ( ! empty( $mobile_menu_color ) ) {
		$toggle_style .= 'color: ' . esc_attr( $mobile_menu_color ) . ';';
	}
	if ( ! empty( $mobile_menu_bg['color'] ) ) {
		$toggle_style .= 'background-color: ' . esc_attr( $mobile_menu_bg['color'] ) . ';';
	}
}
// Width is always applied.
if ( ! empty( $mobile_menu_width['size'] ) ) {
	$toggle_style .= 'width: ' . esc_attr( $mobile_menu_width['size'] ) . ( $mobile_menu_width['unit'] ?? 'px' ) . ';';
}

// Get SVG icon for dropdown (main menu items).
$icon_svg = wbcom_essential_smart_menu_get_icon_svg( $dropdown_icon );

// Get SVG icon for submenu indicator (nested items) - rotated 90deg for horizontal pointing.
$submenu_icon_svg = wbcom_essential_smart_menu_get_icon_svg( $submenu_indicator_icon );

// Build menu CSS variables style - sizes and layout always applied.
$menu_style  = '--icon-size: ' . esc_attr( $dropdown_icon_size ) . 'px;';
$menu_style .= '--submenu-min-width: ' . esc_attr( $submenu_min_width['size'] . $submenu_min_width['unit'] ) . ';';
$menu_style .= '--submenu-max-width: ' . esc_attr( $submenu_max_width['size'] . $submenu_max_width['unit'] ) . ';';
$menu_style .= '--submenu-offset-x: ' . esc_attr( $submenu_offset_x ) . 'px;';
$menu_style .= '--submenu-offset-y: ' . esc_attr( $submenu_offset_y ) . 'px;';
$menu_style .= '--submenu-level2-offset-x: ' . esc_attr( $submenu_level2_offset_x ) . 'px;';
$menu_style .= '--submenu-level2-offset-y: ' . esc_attr( $submenu_level2_offset_y ) . 'px;';
$menu_style .= '--submenu-transition: ' . esc_attr( $submenu_transition ) . 's;';
$menu_style .= '--submenu-indicator-size: ' . esc_attr( $submenu_indicator_size ) . 'px;';

// Add color CSS variables only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$menu_style .= '--item-color: ' . esc_attr( $main_menu_item_color ) . ';';
	$menu_style .= '--item-bg: ' . esc_attr( $main_menu_item_bg ) . ';';
	$menu_style .= '--item-color-hover: ' . esc_attr( $main_menu_item_color_hover ) . ';';
	$menu_style .= '--item-bg-hover: ' . esc_attr( $main_menu_item_bg_hover ) . ';';
	$menu_style .= '--item-color-active: ' . esc_attr( $main_menu_item_color_active ) . ';';
	$menu_style .= '--item-bg-active: ' . esc_attr( $main_menu_item_bg_active ) . ';';
	$menu_style .= '--sub-bg: ' . esc_attr( $sub_menu_bg ) . ';';
	$menu_style .= '--sub-item-color: ' . esc_attr( $sub_menu_item_color ) . ';';
	$menu_style .= '--sub-item-bg: ' . esc_attr( $sub_menu_item_bg ) . ';';
	$menu_style .= '--sub-item-color-hover: ' . esc_attr( $sub_menu_item_color_hover ) . ';';
	$menu_style .= '--sub-item-bg-hover: ' . esc_attr( $sub_menu_item_bg_hover ) . ';';
	$menu_style .= '--sub-item-color-active: ' . esc_attr( $sub_menu_item_color_active ) . ';';
	$menu_style .= '--sub-item-bg-active: ' . esc_attr( $sub_menu_item_bg_active ) . ';';
}

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-smart-menu';
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $wrapper_classes ) );
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>"
		data-breakpoint="<?php echo esc_attr( $breakpoint ); ?>"
		data-mtoggle="<?php echo esc_attr( $show_toggle ? 'yes' : '' ); ?>"
		data-collapsible-behavior="<?php echo esc_attr( $collapsible_behavior ); ?>"
		data-submenu-animation="<?php echo esc_attr( $submenu_animation ); ?>"
		data-submenu-indicator="<?php echo esc_attr( $submenu_indicator_icon ); ?>"
		style="<?php echo esc_attr( $container_style ); ?>">

		<?php if ( $show_toggle ) : ?>
			<div class="smart-menu-toggle-container" style="<?php echo esc_attr( $toggle_container_style ); ?>">
				<button class="smart-menu-toggle" aria-label="<?php esc_attr_e( 'Toggle menu', 'wbcom-essential' ); ?>" style="<?php echo esc_attr( $toggle_style ); ?>">
					<span class="icon">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
							<path d="M2 4h16v2H2V4zm0 5h16v2H2V9zm0 5h16v2H2v-2z"/>
						</svg>
					</span>
					<span class="text"><?php echo esc_html( $toggle_text ); ?></span>
				</button>
			</div>
		<?php endif; ?>

		<nav class="smart-menu-nav">
			<ul class="<?php echo esc_attr( implode( ' ', $menu_classes ) ); ?>" style="<?php echo esc_attr( $menu_style ); ?>">
				<?php
				wp_nav_menu(
					array(
						'menu'       => $menu_id,
						'menu_class' => '',
						'container'  => false,
						'walker'     => new WBCOM_Essential_Smart_Menu_Walker( $icon_svg, $submenu_icon_svg ),
						'items_wrap' => '%3$s',
					)
				);
				?>
			</ul>
		</nav>
	</div>
</div>
