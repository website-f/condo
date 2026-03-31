<?php
/**
 * Server-side render for Members Grid block.
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

// Check if BuddyPress is active.
if ( ! function_exists( 'buddypress' ) ) {
	echo '<p>' . esc_html__( 'BuddyPress is required for this block.', 'wbcom-essential' ) . '</p>';
	return;
}

// Extract attributes.
$use_theme_colors = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$sort_type        = $attributes['sortType'] ?? 'newest';
$total_members    = $attributes['totalMembers'] ?? 12;
$columns          = $attributes['columns'] ?? 3;
$columns_tablet   = $attributes['columnsTablet'] ?? 2;
$columns_mobile   = $attributes['columnsMobile'] ?? 1;
$gap              = $attributes['gap'] ?? 30;
$show_avatar      = $attributes['showAvatar'] ?? true;
$show_name        = $attributes['showName'] ?? true;
$show_last_active = $attributes['showLastActive'] ?? true;
$show_friend_btn  = $attributes['showFriendButton'] ?? true;
$avatar_size      = $attributes['avatarSize'] ?? 126;
$card_bg_color    = $attributes['cardBgColor'] ?? '#ffffff';
$card_radius      = $attributes['cardBorderRadius'] ?? 8;
$card_padding     = $attributes['cardPadding'] ?? 20;
$card_shadow      = $attributes['cardShadow'] ?? true;
$name_color       = $attributes['nameColor'] ?? '#122B46';
$meta_color       = $attributes['metaColor'] ?? '#A3A5A9';
$button_color     = $attributes['buttonColor'] ?? '#3182ce';
$button_text      = $attributes['buttonTextColor'] ?? '#ffffff';

// Build unique ID for each block instance to avoid CSS conflicts.
$unique_id = 'wbcom-members-grid-' . wp_unique_id();

// Build inline styles - layout always applied, colors only when not using theme colors.
$inline_styles = array(
	// Layout styles - always applied.
	'--wbcom-grid-columns'        => $columns,
	'--wbcom-grid-columns-tablet' => $columns_tablet,
	'--wbcom-grid-columns-mobile' => $columns_mobile,
	'--wbcom-grid-gap'            => $gap . 'px',
	'--wbcom-card-radius'         => $card_radius . 'px',
	'--wbcom-card-padding'        => $card_padding . 'px',
	'--wbcom-avatar-size'         => $avatar_size . 'px',
);

// Color styles - only when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--wbcom-card-bg']      = $card_bg_color;
	$inline_styles['--wbcom-name-color']   = $name_color;
	$inline_styles['--wbcom-meta-color']   = $meta_color;
	$inline_styles['--wbcom-button-color'] = $button_color;
	$inline_styles['--wbcom-button-text']  = $button_text;
}

$style_string = '';
foreach ( $inline_styles as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Classes.
$grid_classes = array(
	'wbcom-essential-members-grid-list',
);

if ( $card_shadow ) {
	$grid_classes[] = 'has-shadow';
}

// Avatar args.
$avatar_args = array(
	'type'   => 'full',
	'width'  => $avatar_size,
	'height' => $avatar_size,
	'class'  => 'avatar',
);

// Members query args.
$members_args = array(
	'user_id'         => 0,
	'type'            => $sort_type,
	'per_page'        => $total_members,
	'max'             => $total_members,
	'populate_extras' => true,
	'search_terms'    => false,
);

// Wrapper classes.
$wrapper_classes = array( 'wbcom-essential-members-grid' );
if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'id'    => $unique_id,
		'style' => $style_string,
	)
);
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<?php if ( bp_has_members( $members_args ) ) : ?>
		<div class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>">
			<?php
			while ( bp_members() ) :
				bp_the_member();
				?>
				<div class="wbcom-member-grid-item">
					<div class="wbcom-member-grid-card">
						<?php if ( $show_avatar ) : ?>
							<div class="wbcom-member-grid-avatar">
								<a href="<?php bp_member_permalink(); ?>">
									<?php bp_member_avatar( $avatar_args ); ?>
								</a>
							</div>
						<?php endif; ?>

						<div class="wbcom-member-grid-content">
							<?php if ( $show_name ) : ?>
								<h4 class="wbcom-member-grid-name">
									<a href="<?php bp_member_permalink(); ?>">
										<?php bp_member_name(); ?>
									</a>
								</h4>
							<?php endif; ?>

							<?php if ( $show_last_active ) : ?>
								<p class="wbcom-member-grid-meta">
									<?php bp_member_last_active(); ?>
								</p>
							<?php endif; ?>

							<?php if ( $show_friend_btn && bp_is_active( 'friends' ) && is_user_logged_in() ) : ?>
								<div class="wbcom-member-grid-action">
									<?php bp_add_friend_button( bp_get_member_user_id() ); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<div class="wbcom-essential-no-data">
			<p><?php esc_html_e( 'Sorry, no members were found.', 'wbcom-essential' ); ?></p>
		</div>
	<?php endif; ?>
</div>
