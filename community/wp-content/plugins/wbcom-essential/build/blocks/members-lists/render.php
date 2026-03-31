<?php
/**
 * Server-side render for Members Lists block.
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
$use_theme_colors     = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$members_order        = $attributes['membersOrder'] ?? 'active';
$profile_types        = $attributes['profileTypes'] ?? array();
$members_count        = $attributes['membersCount'] ?? 5;
$row_space            = $attributes['rowSpace'] ?? 10;
$alignment            = $attributes['alignment'] ?? 'left';
$show_all_members     = $attributes['showAllMembersLink'] ?? true;
$show_filter_types    = $attributes['showFilterTypes'] ?? true;
$show_avatar          = $attributes['showAvatar'] ?? true;
$show_name            = $attributes['showName'] ?? true;
$show_online_status   = $attributes['showOnlineStatus'] ?? true;
$heading_text         = $attributes['headingText'] ?? __( 'Members', 'wbcom-essential' );
$member_link_text     = $attributes['memberLinkText'] ?? __( 'All Members', 'wbcom-essential' );
$box_border_color     = $attributes['boxBorderColor'] ?? '#e3e3e3';
$box_border_radius    = $attributes['boxBorderRadius'] ?? 4;
$box_bg_color         = $attributes['boxBackgroundColor'] ?? '#ffffff';
$all_members_link_color = $attributes['allMembersLinkColor'] ?? '';
$filter_border_style  = $attributes['filterBorderStyle'] ?? 'solid';
$filter_border_color  = $attributes['filterBorderColor'] ?? '#e3e3e3';
$avatar_size          = $attributes['avatarSize'] ?? 40;
$avatar_border_radius = $attributes['avatarBorderRadius'] ?? 50;
$avatar_spacing       = $attributes['avatarSpacing'] ?? 15;
$online_status_color  = $attributes['onlineStatusColor'] ?? '#1CD991';
$online_status_size   = $attributes['onlineStatusSize'] ?? 13;
$name_color           = $attributes['nameColor'] ?? '#122B46';

// Build inline styles - layout always applied, colors only when not using theme colors.
$inline_styles = array(
	// Layout styles - always applied.
	'--box-border-radius' => $box_border_radius . 'px',
	'--avatar-size'       => $avatar_size . 'px',
	'--avatar-radius'     => $avatar_border_radius . '%',
	'--avatar-spacing'    => $avatar_spacing . 'px',
	'--online-size'       => $online_status_size . 'px',
	'--row-space'         => $row_space . 'px',
);

// Color styles - only when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--box-border-color']    = $box_border_color;
	$inline_styles['--box-bg-color']        = $box_bg_color;
	$inline_styles['--filter-border-color'] = $filter_border_color;
	$inline_styles['--online-color']        = $online_status_color;
	$inline_styles['--name-color']          = $name_color;
	$inline_styles['--link-color']          = ! empty( $all_members_link_color ) ? $all_members_link_color : 'inherit';
}

$style_string = '';
foreach ( $inline_styles as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Members types for tabs.
$members_types = array(
	'active'  => __( 'Active', 'wbcom-essential' ),
	'popular' => __( 'Popular', 'wbcom-essential' ),
	'newest'  => __( 'Newest', 'wbcom-essential' ),
);

// Wrapper classes.
$wrapper_classes = array( 'wbcom-essential-members-lists' );
if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => $style_string,
	)
);

// Avatar args.
$avatar_args = array(
	'type'   => 'full',
	'width'  => $avatar_size,
	'height' => $avatar_size,
	'class'  => 'avatar',
);
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<div class="wbcom-essential-members">
		<?php if ( $heading_text || ( $show_all_members && $member_link_text ) ) : ?>
			<div class="wbcom-essential-block-header">
				<?php if ( $heading_text ) : ?>
					<div class="wbcom-essential-block-header__title">
						<h3><?php echo esc_html( $heading_text ); ?></h3>
					</div>
				<?php endif; ?>

				<?php if ( $show_all_members && $member_link_text ) : ?>
					<div class="wbcom-essential-block-header__extra">
						<a href="<?php bp_members_directory_permalink(); ?>" class="count-more">
							<?php echo esc_html( $member_link_text ); ?>
							<span class="dashicons dashicons-arrow-right-alt2"></span>
						</a>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $show_filter_types ) : ?>
			<div class="wbcom-essential-members-filters border-<?php echo esc_attr( $filter_border_style ); ?>">
				<?php foreach ( $members_types as $type_key => $type_label ) : ?>
					<a href="#"
						class="wbcom-essential-members__tab <?php echo $type_key === $members_order ? 'selected' : ''; ?>"
						data-type="<?php echo esc_attr( $type_key ); ?>">
						<?php echo esc_html( $type_label ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="wbcom-essential-members-flow">
			<?php
			foreach ( $members_types as $type_key => $type_label ) :
				// Query members args.
				$members_args = array(
					'user_id'         => 0,
					'type'            => $type_key,
					'per_page'        => $members_count,
					'max'             => $members_count,
					'member_type'     => ! empty( $profile_types ) ? $profile_types : false,
					'populate_extras' => true,
					'search_terms'    => false,
				);

				if ( bp_has_members( $members_args ) ) :
					?>
					<div class="wbcom-essential-members-list wbcom-essential-members-list--<?php echo esc_attr( $type_key ); ?> wbcom-essential-members-list--align-<?php echo esc_attr( $alignment ); ?> <?php echo $type_key === $members_order ? 'active' : ''; ?>">
						<?php
						while ( bp_members() ) :
							bp_the_member();
							global $members_template;

							// Check if member is online (active within last 5 minutes).
							$current_time = current_time( 'mysql', 1 );
							$diff         = strtotime( $current_time ) - strtotime( $members_template->member->last_activity );
							$is_online    = $diff < 300;
							?>
							<div class="wbcom-essential-members-list__item" style="margin-bottom: <?php echo esc_attr( $row_space ); ?>px">
								<?php if ( $show_avatar ) : ?>
									<div class="wbcom-essential-members-list__avatar">
										<a href="<?php bp_member_permalink(); ?>">
											<?php bp_member_avatar( $avatar_args ); ?>
										</a>
									</div>
								<?php endif; ?>

								<?php if ( $show_name ) : ?>
									<div class="wbcom-essential-members-list__name">
										<a href="<?php bp_member_permalink(); ?>">
											<?php bp_member_name(); ?>
										</a>
									</div>
								<?php endif; ?>

								<?php if ( $show_online_status && $is_online ) : ?>
									<span class="wbcom-member-status online"></span>
								<?php endif; ?>
							</div>
						<?php endwhile; ?>
					</div>
				<?php else : ?>
					<div class="wbcom-essential-members-list wbcom-essential-members-list--<?php echo esc_attr( $type_key ); ?> wbcom-essential-no-data <?php echo $type_key === $members_order ? 'active' : ''; ?>">
						<p><?php esc_html_e( 'Sorry, no members were found.', 'wbcom-essential' ); ?></p>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>
