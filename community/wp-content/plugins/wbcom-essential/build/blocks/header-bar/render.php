<?php
/**
 * Server-side render for Header Bar block.
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
$alignment            = $attributes['alignment'] ?? 'right';
$show_profile         = $attributes['showProfileDropdown'] ?? true;
$profile_menu         = $attributes['profileMenu'] ?? '';
$show_separator       = $attributes['showSeparator'] ?? true;
$show_friend_requests = $attributes['showFriendRequests'] ?? true;
$show_search          = $attributes['showSearch'] ?? true;
$show_messages        = $attributes['showMessages'] ?? true;
$show_notifications   = $attributes['showNotifications'] ?? true;
$show_cart            = $attributes['showCart'] ?? true;
$show_dark_mode       = $attributes['showDarkModeToggle'] ?? false;
$space_between        = $attributes['spaceBetween'] ?? 15;
$icon_size            = $attributes['iconSize'] ?? 18;
$avatar_size          = $attributes['avatarSize'] ?? 36;
$avatar_border_radius = $attributes['avatarBorderRadius'] ?? 50;
$separator_color      = $attributes['separatorColor'] ?? 'rgba(0,0,0,0.1)';
$separator_width      = $attributes['separatorWidth'] ?? 1;
$icon_color           = $attributes['iconColor'] ?? '#303030';

// Individual icon settings.
$friend_requests_icon       = $attributes['friendRequestsIcon'] ?? '';
$friend_requests_icon_color = $attributes['friendRequestsIconColor'] ?? '';
$search_icon                = $attributes['searchIcon'] ?? '';
$search_icon_color          = $attributes['searchIconColor'] ?? '';
$messages_icon              = $attributes['messagesIcon'] ?? '';
$messages_icon_color        = $attributes['messagesIconColor'] ?? '';
$notifications_icon         = $attributes['notificationsIcon'] ?? '';
$notifications_icon_color   = $attributes['notificationsIconColor'] ?? '';
$cart_icon                  = $attributes['cartIcon'] ?? '';
$cart_icon_color            = $attributes['cartIconColor'] ?? '';
$dark_mode_icon             = $attributes['darkModeIcon'] ?? '';
$dark_mode_icon_color       = $attributes['darkModeIconColor'] ?? '';
$icon_text_shadow           = $attributes['iconTextShadow'] ?? '';

// Counter styles.
$counter_bg_color   = $attributes['counterBgColor'] ?? '#1D76DA';
$counter_text_color = $attributes['counterTextColor'] ?? '#ffffff';
$counter_box_shadow = $attributes['counterBoxShadow'] ?? '';

// Dropdown styles.
$dropdown_bg_color      = $attributes['dropdownBgColor'] ?? '#ffffff';
$dropdown_text_color    = $attributes['dropdownTextColor'] ?? '#303030';
$dropdown_hover_bg      = $attributes['dropdownHoverBgColor'] ?? '';
$dropdown_hover_text    = $attributes['dropdownHoverTextColor'] ?? '';
$dropdown_border_color  = $attributes['dropdownBorderColor'] ?? '';
$dropdown_border_width  = $attributes['dropdownBorderWidth'] ?? 0;
$dropdown_border_radius = $attributes['dropdownBorderRadius'] ?? 8;
$dropdown_box_shadow    = $attributes['dropdownBoxShadow'] ?? '0 4px 20px rgba(0, 0, 0, 0.15)';

// Profile styles.
$user_name_color       = $attributes['userNameColor'] ?? '#303030';
$user_name_hover_color = $attributes['userNameHoverColor'] ?? '';

// Auth styles.
$sign_in_color         = $attributes['signInColor'] ?? '';
$sign_in_hover_color   = $attributes['signInHoverColor'] ?? '';
$sign_up_bg_color      = $attributes['signUpBgColor'] ?? '';
$sign_up_text_color    = $attributes['signUpTextColor'] ?? '';
$sign_up_hover_bg      = $attributes['signUpHoverBgColor'] ?? '';
$sign_up_hover_text    = $attributes['signUpHoverTextColor'] ?? '';
$sign_up_border_radius = $attributes['signUpBorderRadius'] ?? 4;

// BuddyPress checks.
$bp_active            = function_exists( 'buddypress' );
$friends_active       = $bp_active && function_exists( 'bp_is_active' ) && bp_is_active( 'friends' );
$messages_active      = $bp_active && function_exists( 'bp_is_active' ) && bp_is_active( 'messages' );
$notifications_active = $bp_active && function_exists( 'bp_is_active' ) && bp_is_active( 'notifications' );
$wc_active            = class_exists( 'WooCommerce' );

// Build inline styles with CSS custom properties.
// Dimension styles - always applied.
$inline_styles = array(
	'--space-between'          => $space_between . 'px',
	'--icon-size'              => $icon_size . 'px',
	'--avatar-size'            => $avatar_size . 'px',
	'--avatar-radius'          => $avatar_border_radius . '%',
	'--separator-width'        => $separator_width . 'px',
	'--dropdown-border-radius' => $dropdown_border_radius . 'px',
	'--sign-up-border-radius'  => $sign_up_border_radius . 'px',
);

// Color styles - only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--separator-color']    = $separator_color;
	$inline_styles['--icon-color']         = $icon_color;
	$inline_styles['--counter-bg-color']   = $counter_bg_color;
	$inline_styles['--counter-text-color'] = $counter_text_color;
	$inline_styles['--dropdown-bg-color']  = $dropdown_bg_color;
	$inline_styles['--dropdown-text-color'] = $dropdown_text_color;
	$inline_styles['--user-name-color']    = $user_name_color;

	// Add optional color styles only if set.
	if ( ! empty( $friend_requests_icon_color ) ) {
		$inline_styles['--friend-requests-icon-color'] = $friend_requests_icon_color;
	}
	if ( ! empty( $search_icon_color ) ) {
		$inline_styles['--search-icon-color'] = $search_icon_color;
	}
	if ( ! empty( $messages_icon_color ) ) {
		$inline_styles['--messages-icon-color'] = $messages_icon_color;
	}
	if ( ! empty( $notifications_icon_color ) ) {
		$inline_styles['--notifications-icon-color'] = $notifications_icon_color;
	}
	if ( ! empty( $cart_icon_color ) ) {
		$inline_styles['--cart-icon-color'] = $cart_icon_color;
	}
	if ( ! empty( $dark_mode_icon_color ) ) {
		$inline_styles['--dark-mode-icon-color'] = $dark_mode_icon_color;
	}
	if ( ! empty( $icon_text_shadow ) ) {
		$inline_styles['--icon-text-shadow'] = $icon_text_shadow;
	}
	if ( ! empty( $counter_box_shadow ) ) {
		$inline_styles['--counter-box-shadow'] = $counter_box_shadow;
	}
	if ( ! empty( $dropdown_hover_bg ) ) {
		$inline_styles['--dropdown-hover-bg-color'] = $dropdown_hover_bg;
	}
	if ( ! empty( $dropdown_hover_text ) ) {
		$inline_styles['--dropdown-hover-text-color'] = $dropdown_hover_text;
	}
	if ( ! empty( $dropdown_border_color ) ) {
		$inline_styles['--dropdown-border-color'] = $dropdown_border_color;
	}
	if ( $dropdown_border_width > 0 ) {
		$inline_styles['--dropdown-border-width'] = $dropdown_border_width . 'px';
	}
	if ( ! empty( $dropdown_box_shadow ) ) {
		$inline_styles['--dropdown-box-shadow'] = $dropdown_box_shadow;
	}
	if ( ! empty( $user_name_hover_color ) ) {
		$inline_styles['--user-name-hover-color'] = $user_name_hover_color;
	}
	if ( ! empty( $sign_in_color ) ) {
		$inline_styles['--sign-in-color'] = $sign_in_color;
	}
	if ( ! empty( $sign_in_hover_color ) ) {
		$inline_styles['--sign-in-hover-color'] = $sign_in_hover_color;
	}
	if ( ! empty( $sign_up_bg_color ) ) {
		$inline_styles['--sign-up-bg-color'] = $sign_up_bg_color;
	}
	if ( ! empty( $sign_up_text_color ) ) {
		$inline_styles['--sign-up-text-color'] = $sign_up_text_color;
	}
	if ( ! empty( $sign_up_hover_bg ) ) {
		$inline_styles['--sign-up-hover-bg-color'] = $sign_up_hover_bg;
	}
	if ( ! empty( $sign_up_hover_text ) ) {
		$inline_styles['--sign-up-hover-text-color'] = $sign_up_hover_text;
	}
}

$style_string = '';
foreach ( $inline_styles as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Get wrapper attributes.
$wrapper_classes = 'wbcom-essential-header-bar wbcom-header-bar-align-' . esc_attr( $alignment );
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $wrapper_classes,
		'style' => $style_string,
	)
);

if ( ! function_exists( 'wbcom_header_bar_render_icon' ) ) {
	/**
	 * Render an icon - either custom SVG or dashicon fallback.
	 *
	 * @param string $custom_icon    Custom icon name from icon picker.
	 * @param string $dashicon_class Fallback dashicon class.
	 * @param string $extra_class    Additional CSS class.
	 * @return string HTML for the icon.
	 */
	function wbcom_header_bar_render_icon( $custom_icon, $dashicon_class, $extra_class = '' ) {
		$class = 'wbcom-header-bar-icon-inner';
		if ( ! empty( $extra_class ) ) {
			$class .= ' ' . $extra_class;
		}

		if ( ! empty( $custom_icon ) ) {
			$svg = wbcom_header_bar_get_icon_svg( $custom_icon );
			if ( $svg ) {
				return '<span class="' . esc_attr( $class ) . ' wbcom-custom-icon">' . $svg . '</span>';
			}
		}

		return '<span class="' . esc_attr( $class ) . ' dashicons ' . esc_attr( $dashicon_class ) . '"></span>';
	}
}

if ( ! function_exists( 'wbcom_header_bar_get_icon_svg' ) ) {
	/**
	 * Get SVG markup for a custom icon.
	 *
	 * @param string $icon_name Icon name.
	 * @return string|false SVG markup or false if not found.
	 */
	function wbcom_header_bar_get_icon_svg( $icon_name ) {
		$icons = array(
			// General icons.
			'search'           => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>',
			'search-circle'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="m15 13 2.5 2.5"/></svg>',
			'magnifying-glass' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/></svg>',
			'zoom'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>',
			'menu'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>',
			'settings'         => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
			'home'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
			// Communication icons.
			'mail'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
			'mail-open'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z"/><path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10"/></svg>',
			'inbox'            => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>',
			'send'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>',
			'message-circle'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>',
			'message-square'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
			'chat'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 10h.01"/><path d="M12 10h.01"/><path d="M16 10h.01"/></svg>',
			// Notification icons.
			'bell'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>',
			'bell-ring'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><path d="M4 2C2.8 3.7 2 5.7 2 8"/><path d="M22 8c0-2.3-.8-4.3-2-6"/></svg>',
			'bell-dot'         => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19.4 14.9C20.2 16.4 21 17 21 17H3s3-2 3-9c0-3.3 2.7-6 6-6 .7 0 1.3.1 1.9.3"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><circle cx="18" cy="8" r="3"/></svg>',
			'alert-circle'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
			// Shopping icons.
			'cart'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>',
			'shopping-bag'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
			'shopping-basket'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 11-1 9"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6l1.7-7.4"/><path d="M4.5 15.5h15"/><path d="m5 11 4-7"/><path d="m9 11 1 9"/></svg>',
			'store'            => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2v0a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12v0a2 2 0 0 1-2-2V7"/></svg>',
			// User icons.
			'user'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
			'user-circle'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/></svg>',
			'user-plus'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>',
			'users'            => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
			// Theme icons.
			'sun'              => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>',
			'moon'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>',
			'sun-moon'         => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a2.83 2.83 0 0 0 4 4 4 4 0 1 1-4-4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.9 4.9 1.4 1.4"/><path d="m17.7 17.7 1.4 1.4"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.3 17.7-1.4 1.4"/><path d="m19.1 4.9-1.4 1.4"/></svg>',
			'monitor'          => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>',
			'palette'          => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.555C21.965 6.012 17.461 2 12 2z"/></svg>',
		);

		if ( isset( $icons[ $icon_name ] ) ) {
			return $icons[ $icon_name ];
		}

		return false;
	}
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<div id="header-aside" class="header-aside wbcom-header-bar-inner">
		<div class="header-aside-inner">
		<?php if ( is_user_logged_in() ) : ?>

			<?php if ( $show_search ) : ?>
				<a href="#" class="header-search-link" data-balloon-pos="down" data-balloon="<?php esc_attr_e( 'Search', 'wbcom-essential' ); ?>">
					<?php if ( ! empty( $search_icon ) ) : ?>
						<?php echo wbcom_header_bar_render_icon( $search_icon, 'dashicons-search', 'search-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<i class="fa fa-search"></i>
					<?php endif; ?>
				</a>
			<?php endif; ?>

			<?php if ( $show_friend_requests && $friends_active ) : ?>
				<?php
				$friend_requests_count = function_exists( 'bp_friend_get_total_requests_count' ) ? bp_friend_get_total_requests_count() : 0;
				$friend_requests_url   = $bp_active && function_exists( 'bp_loggedin_user_domain' ) ? trailingslashit( bp_loggedin_user_domain() ) . bp_get_friends_slug() . '/requests/' : '#';
				?>
				<div class="dropdown-passive dropdown-right notification-wrap friend-requests-wrap menu-item-has-children">
					<a href="<?php echo esc_url( $friend_requests_url ); ?>" class="header-friend-requests-link notification-link">
						<span class="friend-requests-icon-wrap" data-balloon-pos="down" data-balloon="<?php esc_attr_e( 'Friend Requests', 'wbcom-essential' ); ?>">
							<?php if ( ! empty( $friend_requests_icon ) ) : ?>
								<?php echo wbcom_header_bar_render_icon( $friend_requests_icon, 'dashicons-groups', 'friend-requests-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<i class="fa fa-user-plus"></i>
							<?php endif; ?>
							<?php if ( $friend_requests_count > 0 ) : ?>
								<span class="count"><?php echo esc_html( $friend_requests_count ); ?></span>
							<?php endif; ?>
						</span>
					</a>
					<div class="user-menu-dropdown-menu notification-dropdown" aria-labelledby="nav_friend_requests">
						<div class="dropdown-header">
							<div class="dropdown-title"><?php esc_html_e( 'Friend requests', 'wbcom-essential' ); ?></div>
						</div>
						<div class="user-menu-dropdown-item-wrapper">
							<?php
							if ( function_exists( 'bp_get_friendship_requests' ) ) :
								$friendship_requests = bp_get_friendship_requests( bp_loggedin_user_id() );
								if ( ! empty( $friendship_requests ) && bp_has_members( 'type=alphabetical&include=' . $friendship_requests ) ) :
									$request_count = 0;
									while ( bp_members() && $request_count < 5 ) :
										bp_the_member();
										++$request_count;
										?>
										<div class="dropdown-item buddyx-friend-request">
											<div class="dropdown-item-inner">
												<div class="item-avatar rounded-avatar">
													<a href="<?php bp_member_link(); ?>">
														<?php
														echo bp_core_fetch_avatar( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
															array(
																'item_id' => bp_get_member_user_id(),
																'type'    => 'thumb',
																'class'   => 'avatar rounded-circle',
																'width'   => 40,
																'height'  => 40,
															)
														);
														?>
													</a>
												</div>
												<div class="item-info">
													<div class="item-detail-data">
														<a class="ellipsis" href="<?php bp_member_link(); ?>"><?php bp_member_name(); ?></a>
														<p class="item-time mute response"><?php bp_member_last_active(); ?></p>
													</div>
												</div>
												<div class="request-button">
													<?php $friendship_id = friends_get_friendship_id( bp_get_member_user_id(), bp_loggedin_user_id() ); ?>
													<a class="btn buddyx-friendship-btn item-btn accept" data-friendship-id="<?php echo esc_attr( $friendship_id ); ?>" href="<?php bp_friend_accept_request_link(); ?>" title="<?php esc_attr_e( 'Accept', 'wbcom-essential' ); ?>"><i class="fa fa-check"></i></a>
													<a class="btn buddyx-friendship-btn item-btn reject" data-friendship-id="<?php echo esc_attr( $friendship_id ); ?>" href="<?php bp_friend_reject_request_link(); ?>" title="<?php esc_attr_e( 'Reject', 'wbcom-essential' ); ?>"><i class="fa fa-times"></i></a>
												</div>
											</div>
										</div>
										<?php
									endwhile;
								else :
									?>
									<div class="alert-message">
										<div class="alert alert-warning" role="alert"><?php esc_html_e( 'No friend request.', 'wbcom-essential' ); ?></div>
									</div>
									<?php
								endif;
							endif;
							?>
						</div>
						<div class="dropdown-footer">
							<a href="<?php echo esc_url( $friend_requests_url ); ?>" class="button read-more">
								<?php esc_html_e( 'All Requests', 'wbcom-essential' ); ?>
								<i class="fa fa-angle-right"></i>
							</a>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $show_messages && $messages_active ) : ?>
				<?php
				$unread_count = function_exists( 'bp_get_total_unread_messages_count' ) ? bp_get_total_unread_messages_count() : 0;
				$messages_url = $bp_active && function_exists( 'bp_loggedin_user_domain' ) ? trailingslashit( bp_loggedin_user_domain() ) . bp_get_messages_slug() . '/' : '#';
				?>
				<div class="dropdown-passive dropdown-right notification-wrap messages-wrap menu-item-has-children">
					<a class="notification-link" href="<?php echo esc_url( $messages_url ); ?>">
						<span data-balloon-pos="down" data-balloon="<?php esc_attr_e( 'Messages', 'wbcom-essential' ); ?>">
							<?php if ( ! empty( $messages_icon ) ) : ?>
								<?php echo wbcom_header_bar_render_icon( $messages_icon, 'dashicons-email', 'messages-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<i class="fa fa-envelope"></i>
							<?php endif; ?>
							<?php if ( $unread_count > 0 ) : ?>
								<span class="count"><?php echo esc_html( $unread_count ); ?></span>
							<?php endif; ?>
						</span>
					</a>
					<section class="notification-dropdown">
						<header class="notification-header">
							<h2 class="title"><?php esc_html_e( 'Messages', 'wbcom-essential' ); ?></h2>
						</header>

						<ul class="notification-list">
							<?php
							if ( function_exists( 'bp_has_message_threads' ) && bp_has_message_threads( array( 'user_id' => get_current_user_id() ) ) ) :
								$msg_count = 0;
								while ( bp_message_threads() && $msg_count < 5 ) :
									bp_message_thread();
									++$msg_count;
									$thread_id = bp_get_message_thread_id();

									$view_link = bp_get_message_thread_view_link( $thread_id );
									if ( empty( $view_link ) ) {
										$view_link = trailingslashit( bp_loggedin_user_domain() ) . bp_get_messages_slug() . '/view/' . $thread_id . '/';
									}

									$is_unread = bp_message_thread_has_unread() ? 'unread' : '';
									?>
									<li class="read-item <?php echo esc_attr( $is_unread ); ?>">
										<span class="wbcom-essential--full-link">
											<a href="<?php echo esc_url( $view_link ); ?>"></a>
										</span>
										<div class="notification-avatar">
											<?php
											bp_message_thread_avatar(
												array(
													'width'  => 40,
													'height' => 40,
												)
											);
											?>
										</div>
										<div class="notification-content">
											<span class="bb-full-link">
												<a href="<?php echo esc_url( $view_link ); ?>"></a>
											</span>
											<span class="notification-message"><?php bp_message_thread_subject(); ?></span>
											<span class="posted"><?php bp_message_thread_excerpt(); ?></span>
										</div>
									</li>
									<?php
								endwhile;
							else :
								?>
								<li class="bs-item-wrap">
									<div class="notification-content"><?php esc_html_e( 'No new messages', 'wbcom-essential' ); ?>!</div>
								</li>
							<?php endif; ?>
						</ul>

						<footer class="notification-footer">
							<a href="<?php echo esc_url( $messages_url ); ?>" class="delete-all">
								<?php esc_html_e( 'All Messages', 'wbcom-essential' ); ?>
								<i class="fa fa-angle-right"></i>
							</a>
						</footer>
					</section>
				</div>
			<?php endif; ?>

			<?php if ( $show_notifications && $notifications_active ) : ?>
				<?php
				$notification_count = function_exists( 'bp_notifications_get_unread_notification_count' ) ? bp_notifications_get_unread_notification_count( bp_loggedin_user_id() ) : 0;
				$notifications_url  = $bp_active && function_exists( 'bp_loggedin_user_domain' ) ? trailingslashit( bp_loggedin_user_domain() ) . bp_get_notifications_slug() . '/' : '#';
				?>
				<div id="header-notifications-dropdown-block" class="dropdown-passive dropdown-right notification-wrap notifications-wrap menu-item-has-children">
					<a href="<?php echo esc_url( $notifications_url ); ?>" ref="notification_bell" class="notification-link">
						<span data-balloon-pos="down" data-balloon="<?php esc_attr_e( 'Notifications', 'wbcom-essential' ); ?>">
							<?php if ( ! empty( $notifications_icon ) ) : ?>
								<?php echo wbcom_header_bar_render_icon( $notifications_icon, 'dashicons-bell', 'notifications-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<i class="fa fa-bell"></i>
							<?php endif; ?>
							<?php if ( $notification_count > 0 ) : ?>
								<span class="count"><?php echo esc_html( $notification_count ); ?></span>
							<?php endif; ?>
						</span>
					</a>
					<div class="user-menu-dropdown-menu notification-dropdown" aria-labelledby="nav_notification">
						<div class="dropdown-header">
							<div class="dropdown-title"><?php esc_html_e( 'Notifications', 'wbcom-essential' ); ?></div>
							<a class="mark-read-all action-unread" data-notification-id="all" <?php echo $notification_count > 0 ? '' : 'style="display: none;"'; ?>>
								<?php esc_html_e( 'Mark all as read', 'wbcom-essential' ); ?>
							</a>
						</div>

						<div class="user-menu-dropdown-item-wrapper">
							<?php
							$notif_query = bp_ajax_querystring( 'notifications' ) . '&user_id=' . bp_loggedin_user_id() . '&is_new=1&per_page=5';

							if ( function_exists( 'bp_has_notifications' ) && bp_has_notifications( $notif_query ) ) :
								while ( bp_the_notifications() ) :
									bp_the_notification();
									$notification_is_new = isset( buddypress()->notifications->query_loop->notification->is_new ) ? buddypress()->notifications->query_loop->notification->is_new : 0;
									?>
									<div class="dropdown-item read-item <?php echo $notification_is_new ? 'unread' : ''; ?>">
										<span class="bx-full-link">
											<?php bp_the_notification_description(); ?>
										</span>
										<div class="notification-item-content">
											<div class="dropdown-item-title notification ellipsis"><?php bp_the_notification_description(); ?></div>
											<p class="mute"><?php bp_the_notification_time_since(); ?></p>
										</div>
										<div class="actions">
											<a class="mark-read action-unread primary" data-bp-tooltip-pos="left" data-bp-tooltip="<?php esc_attr_e( 'Mark as Read', 'wbcom-essential' ); ?>" data-notification-id="<?php bp_the_notification_id(); ?>">
												<i class="fa-regular fa-eye-slash"></i>
											</a>
										</div>
									</div>
									<?php
								endwhile;
							else :
								?>
								<div class="alert-message">
									<div class="alert alert-warning" role="alert"><?php esc_html_e( 'No notifications found.', 'wbcom-essential' ); ?></div>
								</div>
							<?php endif; ?>
						</div>

						<div class="dropdown-footer">
							<a href="<?php echo esc_url( $notifications_url ); ?>" class="button">
								<?php esc_html_e( 'All Notifications', 'wbcom-essential' ); ?>
							</a>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $show_cart && $wc_active ) : ?>
				<?php
				$wc_instance = WC();
				$cart_count  = ( $wc_instance && $wc_instance->cart ) ? $wc_instance->cart->get_cart_contents_count() : 0;
				$cart_url    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#';
				?>
				<div class="notification-wrap header-cart-link-wrap cart-wrap">
					<a href="<?php echo esc_url( $cart_url ); ?>" class="header-cart-link notification-link header-cart-drawer-trigger">
						<span data-balloon-pos="down" data-balloon="<?php esc_attr_e( 'Cart', 'wbcom-essential' ); ?>">
							<?php if ( ! empty( $cart_icon ) ) : ?>
								<?php echo wbcom_header_bar_render_icon( $cart_icon, 'dashicons-cart', 'cart-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<i class="fa fa-shopping-cart"></i>
							<?php endif; ?>
							<?php if ( $cart_count > 0 ) : ?>
								<span class="count header-cart-count"><?php echo esc_html( $cart_count ); ?></span>
							<?php endif; ?>
						</span>
					</a>
				</div>

				<!-- Side Drawer for Cart -->
				<div class="header-cart-drawer" aria-hidden="true">
					<div class="header-cart-drawer__overlay"></div>
					<div class="header-cart-drawer__content">
						<div class="header-cart-drawer__header">
							<h3 class="header-cart-drawer__title"><?php esc_html_e( 'Shopping Cart', 'wbcom-essential' ); ?></h3>
							<button type="button" class="header-cart-drawer__close" aria-label="<?php esc_attr_e( 'Close cart', 'wbcom-essential' ); ?>">
								<span class="widget-close-text"><?php esc_html_e( 'Close', 'wbcom-essential' ); ?></span>
								<span class="widget-close-icon">&mdash;</span>
							</button>
						</div>
						<div class="header-cart-drawer__body">
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

			<?php if ( $show_dark_mode ) : ?>
				<div class="switch-mode">
					<button type="button" class="buddyx-switch-mode" title="<?php esc_attr_e( 'Toggle Dark Mode', 'wbcom-essential' ); ?>" aria-label="<?php esc_attr_e( 'Toggle Dark Mode', 'wbcom-essential' ); ?>">
						<?php if ( ! empty( $dark_mode_icon ) ) : ?>
							<?php echo wbcom_header_bar_render_icon( $dark_mode_icon, 'dashicons-admin-appearance', 'dark-mode-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<span class="dark-mode-icon wbcom-custom-icon"><?php echo wbcom_header_bar_get_icon_svg( 'sun-moon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
					</button>
				</div>
			<?php endif; ?>

			<?php if ( $show_separator ) : ?>
				<span class="wbcom-essential-separator"></span>
			<?php endif; ?>

			<?php if ( $show_profile ) : ?>
				<?php
				$logged_in_user = wp_get_current_user();
				$display_name   = function_exists( 'bp_core_get_user_displayname' ) ? bp_core_get_user_displayname( $logged_in_user->ID ) : $logged_in_user->display_name;

				if ( $bp_active && function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) ) {
					$profile_url = function_exists( 'bp_members_get_user_url' ) ? bp_members_get_user_url( $logged_in_user->ID ) : get_author_posts_url( $logged_in_user->ID );
				} else {
					$profile_url = function_exists( 'bp_core_get_user_domain' ) ? bp_core_get_user_domain( $logged_in_user->ID ) : get_author_posts_url( $logged_in_user->ID );
				}
				?>
				<div class="user-wrap user-wrap-container menu-item-has-children">
					<a class="user-link" href="<?php echo esc_url( $profile_url ); ?>">
						<span class="user-name"><?php echo esc_html( $display_name ); ?></span><i class="fa fa-angle-down"></i>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar() is a safe WordPress core function
						echo get_avatar( $logged_in_user->ID, 100 );
						?>
					</a>

					<div class="sub-menu">
						<div class="wrapper">
							<?php
							if ( $bp_active && ! empty( $profile_menu ) ) {
								$profile_nav_menu = wp_nav_menu(
									array(
										'menu'        => $profile_menu,
										'echo'        => false,
										'fallback_cb' => '__return_false',
									)
								);

								if ( ! empty( $profile_nav_menu ) ) {
									wp_nav_menu(
										array(
											'menu'       => $profile_menu,
											'menu_id'    => 'header-my-account-menu',
											'container'  => false,
											'menu_class' => 'wbcom-essential-my-account-menu',
										)
									);
								} else {
									do_action( 'wbcom_essential_header_user_menu_items' );
								}
							} else {
								do_action( 'wbcom_essential_header_user_menu_items' );
							}
							?>
						</div>
					</div>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<?php // Logged out state. ?>
			<?php if ( $show_search ) : ?>
				<a href="#" class="header-search-link" data-balloon-pos="down" data-balloon="<?php esc_attr_e( 'Search', 'wbcom-essential' ); ?>">
					<?php if ( ! empty( $search_icon ) ) : ?>
						<?php echo wbcom_header_bar_render_icon( $search_icon, 'dashicons-search', 'search-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<i class="fa fa-search"></i>
					<?php endif; ?>
				</a>
			<?php endif; ?>

			<?php if ( $show_cart && $wc_active ) : ?>
				<?php
				$wc_instance = WC();
				$cart_count  = ( $wc_instance && $wc_instance->cart ) ? $wc_instance->cart->get_cart_contents_count() : 0;
				$cart_url    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#';
				?>
				<div class="notification-wrap header-cart-link-wrap cart-wrap">
					<a href="<?php echo esc_url( $cart_url ); ?>" class="header-cart-link notification-link header-cart-drawer-trigger">
						<span data-balloon-pos="down" data-balloon="<?php esc_attr_e( 'Cart', 'wbcom-essential' ); ?>">
							<?php if ( ! empty( $cart_icon ) ) : ?>
								<?php echo wbcom_header_bar_render_icon( $cart_icon, 'dashicons-cart', 'cart-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<i class="fa fa-shopping-cart"></i>
							<?php endif; ?>
							<?php if ( $cart_count > 0 ) : ?>
								<span class="count header-cart-count"><?php echo esc_html( $cart_count ); ?></span>
							<?php endif; ?>
						</span>
					</a>
				</div>

				<!-- Side Drawer for Cart -->
				<div class="header-cart-drawer" aria-hidden="true">
					<div class="header-cart-drawer__overlay"></div>
					<div class="header-cart-drawer__content">
						<div class="header-cart-drawer__header">
							<h3 class="header-cart-drawer__title"><?php esc_html_e( 'Shopping Cart', 'wbcom-essential' ); ?></h3>
							<button type="button" class="header-cart-drawer__close" aria-label="<?php esc_attr_e( 'Close cart', 'wbcom-essential' ); ?>">
								<span class="widget-close-text"><?php esc_html_e( 'Close', 'wbcom-essential' ); ?></span>
								<span class="widget-close-icon">&mdash;</span>
							</button>
						</div>
						<div class="header-cart-drawer__body">
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

			<?php if ( $show_dark_mode ) : ?>
				<div class="switch-mode">
					<button type="button" class="buddyx-switch-mode" title="<?php esc_attr_e( 'Toggle Dark Mode', 'wbcom-essential' ); ?>" aria-label="<?php esc_attr_e( 'Toggle Dark Mode', 'wbcom-essential' ); ?>">
						<?php if ( ! empty( $dark_mode_icon ) ) : ?>
							<?php echo wbcom_header_bar_render_icon( $dark_mode_icon, 'dashicons-admin-appearance', 'dark-mode-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<span class="dark-mode-icon wbcom-custom-icon"><?php echo wbcom_header_bar_get_icon_svg( 'sun-moon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
					</button>
				</div>
			<?php endif; ?>

			<span class="search-separator wbcom-essential-separator"></span>

			<div class="wbcom-essential-header-buttons buddypress-icons-wrapper">
				<a href="<?php echo esc_url( wp_login_url() ); ?>" class="button small outline signin-button link btn-login"><?php esc_html_e( 'Sign in', 'wbcom-essential' ); ?></a>

				<?php if ( get_option( 'users_can_register' ) ) : ?>
					<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="button small singup btn-register"><?php esc_html_e( 'Sign up', 'wbcom-essential' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		</div>
	</div>

	<?php // Search overlay. ?>
	<div class="wbcom-header-bar-search-overlay">
		<div class="wbcom-search-container">
			<?php get_search_form(); ?>
			<a href="#" class="wbcom-search-close">
				<span class="dashicons dashicons-no-alt"></span>
			</a>
		</div>
	</div>
</div>
