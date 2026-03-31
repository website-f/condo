<?php
/**
 * Server-side render for Dashboard Intro block.
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

// Check if user is logged in.
if ( ! is_user_logged_in() ) {
	$show_logged_out = $attributes['showLoggedOutMessage'] ?? true;

	if ( $show_logged_out ) {
		$logged_out_message = $attributes['loggedOutMessage'] ?? __( 'Please log in to see your dashboard.', 'wbcom-essential' );
		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => 'wbcom-essential-dashboard-intro logged-out',
		) );
		?>
		<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
			<div class="wbcom-essential-logged-out-message">
				<p><?php echo esc_html( $logged_out_message ); ?></p>
			</div>
		</div>
		<?php
	}
	return;
}

// Get current user data.
$current_user = wp_get_current_user();
$display_name = $current_user->display_name;

// Get avatar URL.
$avatar_url = '';
if ( function_exists( 'bp_core_fetch_avatar' ) ) {
	$avatar_url = bp_core_fetch_avatar( array(
		'item_id' => $current_user->ID,
		'type'    => 'full',
		'html'    => false,
	) );
} else {
	$avatar_url = get_avatar_url( $current_user->ID, array( 'size' => 200 ) );
}

// Get profile URL.
$profile_url = '';
if ( function_exists( 'bp_core_get_user_domain' ) ) {
	$profile_url = bp_core_get_user_domain( $current_user->ID );
} else {
	$profile_url = get_author_posts_url( $current_user->ID );
}

// Extract attributes.
$use_theme_colors     = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$greeting_text        = $attributes['greetingText'] ?? 'Hello,';
$description_text     = $attributes['descriptionText'] ?? '';
$show_avatar          = $attributes['showAvatar'] ?? true;
$avatar_size          = $attributes['avatarSize'] ?? 80;
$avatar_border_radius = $attributes['avatarBorderRadius'] ?? 50;
$avatar_border_style  = $attributes['avatarBorderStyle'] ?? 'none';
$avatar_border_width  = $attributes['avatarBorderWidth'] ?? 1;
$avatar_border_color  = $attributes['avatarBorderColor'] ?? '#000000';
$avatar_padding       = $attributes['avatarPadding'] ?? 3;
$avatar_shadow        = $attributes['avatarShadow'] ?? array(
	'horizontal' => 0,
	'vertical'   => 0,
	'blur'       => 0,
	'spread'     => 0,
	'color'      => 'rgba(0,0,0,0.5)',
);
$layout               = $attributes['layout'] ?? 'left';
$content_align        = $attributes['contentAlign'] ?? 'left';
$greeting_color       = $attributes['greetingColor'] ?? '#A3A5A9';
$greeting_font_size   = $attributes['greetingFontSize'] ?? 14;
$name_color           = $attributes['nameColor'] ?? '#122B46';
$name_font_size       = $attributes['nameFontSize'] ?? 24;
$description_color    = $attributes['descriptionColor'] ?? '#666666';
$description_font_size = $attributes['descriptionFontSize'] ?? 14;
$gap                  = $attributes['gap'] ?? 20;
$container_bg_color   = $attributes['containerBgColor'] ?? '';
$container_padding    = $attributes['containerPadding'] ?? 30;
$container_radius     = $attributes['containerBorderRadius'] ?? 8;

// Build box shadow string.
$shadow_string = sprintf(
	'%dpx %dpx %dpx %dpx %s',
	$avatar_shadow['horizontal'] ?? 0,
	$avatar_shadow['vertical'] ?? 0,
	$avatar_shadow['blur'] ?? 0,
	$avatar_shadow['spread'] ?? 0,
	$avatar_shadow['color'] ?? 'rgba(0,0,0,0.5)'
);

// Build inline styles - layout always applied, colors only when not using theme colors.
$inline_styles = array(
	// Layout styles - always applied.
	'--gap'                   => $gap . 'px',
	'--container-padding'     => $container_padding . 'px',
	'--container-radius'      => $container_radius . 'px',
	'--avatar-size'           => $avatar_size . 'px',
	'--avatar-radius'         => $avatar_border_radius . '%',
	'--avatar-border-style'   => $avatar_border_style,
	'--avatar-border-width'   => $avatar_border_width . 'px',
	'--avatar-padding'        => $avatar_padding . 'px',
	'--avatar-shadow'         => $shadow_string,
	'--greeting-font-size'    => $greeting_font_size . 'px',
	'--name-font-size'        => $name_font_size . 'px',
	'--description-font-size' => $description_font_size . 'px',
);

// Color styles - only when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--container-bg']       = $container_bg_color;
	$inline_styles['--avatar-border-color'] = $avatar_border_color;
	$inline_styles['--greeting-color']     = $greeting_color;
	$inline_styles['--name-color']         = $name_color;
	$inline_styles['--description-color']  = $description_color;
}

$style_string = '';
foreach ( $inline_styles as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Container classes.
$container_classes = array(
	'wbcom-essential-dashboard-intro',
	'layout-' . $layout,
	'align-' . $content_align,
);

if ( $use_theme_colors ) {
	$container_classes[] = 'use-theme-colors';
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $container_classes ),
	'style' => $style_string,
) );
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<div class="wbcom-dashboard-intro-inner">
		<?php if ( $show_avatar ) : ?>
			<div class="wbcom-dashboard-intro-avatar">
				<a href="<?php echo esc_url( $profile_url ); ?>">
					<img
						src="<?php echo esc_url( $avatar_url ); ?>"
						alt="<?php echo esc_attr( $display_name ); ?>"
						width="<?php echo esc_attr( $avatar_size ); ?>"
						height="<?php echo esc_attr( $avatar_size ); ?>"
					/>
				</a>
			</div>
		<?php endif; ?>

		<div class="wbcom-dashboard-intro-content">
			<?php if ( $greeting_text ) : ?>
				<p class="wbcom-dashboard-intro-greeting">
					<?php echo esc_html( $greeting_text ); ?>
				</p>
			<?php endif; ?>

			<h2 class="wbcom-dashboard-intro-name">
				<a href="<?php echo esc_url( $profile_url ); ?>">
					<?php echo esc_html( $display_name ); ?>
				</a>
			</h2>

			<?php if ( $description_text ) : ?>
				<p class="wbcom-dashboard-intro-description">
					<?php echo esc_html( $description_text ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</div>
