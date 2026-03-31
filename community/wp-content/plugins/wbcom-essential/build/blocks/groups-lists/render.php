<?php
/**
 * Server-side render for Groups Lists block.
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

// Check if BuddyPress is active and groups component is enabled.
if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'groups' ) ) {
	return;
}

// Extract attributes with defaults.
$use_theme_colors         = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$groups_order             = $attributes['groupsOrder'] ?? 'active';
$group_types              = $attributes['groupTypes'] ?? array();
$groups_count             = $attributes['groupsCount'] ?? 5;
$show_all_groups_link     = $attributes['showAllGroupsLink'] ?? true;
$show_filter_types        = $attributes['showFilterTypes'] ?? true;
$show_avatar              = $attributes['showAvatar'] ?? true;
$show_meta                = $attributes['showMeta'] ?? true;
$heading_text             = $attributes['headingText'] ?? 'Groups';
$all_groups_link_text     = $attributes['allGroupsLinkText'] ?? 'All Groups';
$box_bg_color             = $attributes['boxBgColor'] ?? '#ffffff';
$box_border_color         = $attributes['boxBorderColor'] ?? '#e3e3e3';
$box_border_radius        = $attributes['boxBorderRadius'] ?? 4;
$avatar_size              = $attributes['avatarSize'] ?? 40;
$avatar_border_radius     = $attributes['avatarBorderRadius'] ?? 3;
$title_color              = $attributes['titleColor'] ?? '#303030';
$meta_color               = $attributes['metaColor'] ?? '#a3a5a9';
$link_color               = $attributes['linkColor'] ?? '#1d76da';
$filter_normal_color      = $attributes['filterNormalColor'] ?? '#9c9c9c';
$filter_active_color      = $attributes['filterActiveColor'] ?? '#303030';
$filter_active_border     = $attributes['filterActiveBorderColor'] ?? '#1d76da';

// Build inline styles - layout always applied, colors only when not using theme colors.
$inline_styles = array(
	// Layout styles - always applied.
	'--box-border-radius'    => $box_border_radius . 'px',
	'--avatar-size'          => $avatar_size . 'px',
	'--avatar-border-radius' => $avatar_border_radius . 'px',
);

// Color styles - only when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--box-bg']              = $box_bg_color;
	$inline_styles['--box-border-color']    = $box_border_color;
	$inline_styles['--title-color']         = $title_color;
	$inline_styles['--meta-color']          = $meta_color;
	$inline_styles['--link-color']          = $link_color;
	$inline_styles['--filter-normal-color'] = $filter_normal_color;
	$inline_styles['--filter-active-color'] = $filter_active_color;
	$inline_styles['--filter-active-border'] = $filter_active_border;
}

$style_string = '';
foreach ( $inline_styles as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Filter types.
$filter_types = array(
	'active'  => __( 'Active', 'wbcom-essential' ),
	'popular' => __( 'Popular', 'wbcom-essential' ),
	'newest'  => __( 'Newest', 'wbcom-essential' ),
);

// Wrapper classes.
$wrapper_classes = array( 'wbcom-essential-groups-lists' );
if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => $style_string,
	)
);

// Groups directory URL.
$groups_url = function_exists( 'bp_get_groups_directory_url' )
	? bp_get_groups_directory_url()
	: bp_get_groups_directory_permalink();
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<?php if ( ! empty( $heading_text ) || ( $show_all_groups_link && ! empty( $all_groups_link_text ) ) ) : ?>
		<div class="wbcom-groups-lists-header">
			<?php if ( ! empty( $heading_text ) ) : ?>
				<h3 class="wbcom-groups-lists-title"><?php echo esc_html( $heading_text ); ?></h3>
			<?php endif; ?>
			<?php if ( $show_all_groups_link && ! empty( $all_groups_link_text ) ) : ?>
				<a href="<?php echo esc_url( $groups_url ); ?>" class="wbcom-groups-lists-link">
					<?php echo esc_html( $all_groups_link_text ); ?>
					<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
						<path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/>
					</svg>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $show_filter_types ) : ?>
		<div class="wbcom-groups-lists-filters">
			<?php foreach ( $filter_types as $type_key => $type_label ) : ?>
				<a href="#"
					class="wbcom-groups-filter-tab<?php echo $type_key === $groups_order ? ' active' : ''; ?>"
					data-type="<?php echo esc_attr( $type_key ); ?>">
					<?php echo esc_html( $type_label ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="wbcom-groups-lists-content">
		<?php
		foreach ( $filter_types as $type_key => $type_label ) :
			// Build group query args.
			$group_args = array(
				'user_id'    => 0,
				'type'       => $type_key,
				'per_page'   => $groups_count,
				'max'        => $groups_count,
				'group_type' => ! empty( $group_types ) ? $group_types : false,
			);

			$is_active = $type_key === $groups_order;
			?>

			<?php if ( bp_has_groups( $group_args ) ) : ?>
				<ul class="wbcom-groups-list wbcom-groups-list--<?php echo esc_attr( $type_key ); ?><?php echo $is_active ? ' active' : ''; ?>">
					<?php
					while ( bp_groups() ) :
						bp_the_group();

						// Get group URL.
						$group_url = function_exists( 'bp_get_group_url' )
							? bp_get_group_url()
							: bp_get_group_permalink();
						?>
						<li class="wbcom-groups-list-item">
							<?php if ( $show_avatar ) : ?>
								<div class="wbcom-groups-list-avatar">
									<a href="<?php echo esc_url( $group_url ); ?>">
										<?php bp_group_avatar_thumb(); ?>
									</a>
								</div>
							<?php endif; ?>

							<div class="wbcom-groups-list-info">
								<div class="wbcom-groups-list-name">
									<?php bp_group_link(); ?>
								</div>
								<?php if ( $show_meta ) : ?>
									<span class="wbcom-groups-list-meta">
										<?php
										if ( 'newest' === $type_key ) {
											/* translators: %s: Group creation date */
											printf( esc_html__( 'created %s', 'wbcom-essential' ), esc_html( bp_get_group_date_created() ) );
										} elseif ( 'popular' === $type_key ) {
											bp_group_member_count();
										} else {
											/* translators: %s: Group last active time */
											printf( esc_html__( 'active %s', 'wbcom-essential' ), esc_html( bp_get_group_last_active() ) );
										}
										?>
									</span>
								<?php endif; ?>
							</div>
						</li>
					<?php endwhile; ?>
				</ul>

			<?php else : ?>
				<div class="wbcom-groups-list wbcom-groups-list--<?php echo esc_attr( $type_key ); ?> wbcom-groups-no-data<?php echo $is_active ? ' active' : ''; ?>">
					<p><?php esc_html_e( 'No groups matched the current filter.', 'wbcom-essential' ); ?></p>
					<?php
					$create_url = function_exists( 'bp_get_groups_directory_url' )
						? trailingslashit( bp_get_groups_directory_url() . 'create' )
						: trailingslashit( bp_get_groups_directory_permalink() . 'create' );
					?>
					<a href="<?php echo esc_url( $create_url ); ?>" class="wbcom-groups-create-link">
						<?php esc_html_e( 'Create a group', 'wbcom-essential' ); ?>
					</a>
				</div>
			<?php endif; ?>

		<?php endforeach; ?>
	</div>
</div>
