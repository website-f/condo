<?php
/**
 * Server-side render for Groups Grid block.
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
$use_theme_colors   = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$sort_type          = $attributes['sortType'] ?? 'active';
$total_groups       = $attributes['totalGroups'] ?? 12;
$columns            = $attributes['columns'] ?? 3;
$columns_tablet     = $attributes['columnsTablet'] ?? 2;
$columns_mobile     = $attributes['columnsMobile'] ?? 1;
$gap                = $attributes['gap'] ?? 30;
$show_avatar        = $attributes['showAvatar'] ?? true;
$show_name          = $attributes['showName'] ?? true;
$show_description   = $attributes['showDescription'] ?? false;
$show_meta          = $attributes['showMeta'] ?? true;
$show_member_count  = $attributes['showMemberCount'] ?? true;
$show_join_button   = $attributes['showJoinButton'] ?? true;
$card_bg_color      = $attributes['cardBgColor'] ?? '#ffffff';
$card_border_radius = $attributes['cardBorderRadius'] ?? 8;
$card_shadow        = $attributes['cardShadow'] ?? true;
$card_padding       = $attributes['cardPadding'] ?? 20;
$name_color         = $attributes['nameColor'] ?? '#122B46';
$meta_color         = $attributes['metaColor'] ?? '#A3A5A9';
$button_bg_color    = $attributes['buttonBgColor'] ?? '#1d76da';
$button_text_color  = $attributes['buttonTextColor'] ?? '#ffffff';

// Build inline styles - layout always applied, colors only when not using theme colors.
$inline_styles = array(
	// Layout styles - always applied.
	'--columns'        => $columns,
	'--columns-tablet' => $columns_tablet,
	'--columns-mobile' => $columns_mobile,
	'--gap'            => $gap . 'px',
	'--card-radius'    => $card_border_radius . 'px',
	'--card-padding'   => $card_padding . 'px',
);

// Color styles - only when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--card-bg']     = $card_bg_color;
	$inline_styles['--name-color']  = $name_color;
	$inline_styles['--meta-color']  = $meta_color;
	$inline_styles['--button-bg']   = $button_bg_color;
	$inline_styles['--button-text'] = $button_text_color;
}

$style_string = '';
foreach ( $inline_styles as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Container classes.
$container_classes = array( 'wbcom-essential-groups-grid-list' );
if ( $card_shadow ) {
	$container_classes[] = 'has-shadow';
}

// Groups query args.
$groups_args = array(
	'user_id'         => 0,
	'type'            => $sort_type,
	'per_page'        => $total_groups,
	'max'             => $total_groups,
	'populate_extras' => true,
);

// Wrapper classes.
$wrapper_classes = array( 'wbcom-essential-groups-grid' );
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
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<?php if ( bp_has_groups( $groups_args ) ) : ?>
		<div class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>">
			<?php
			while ( bp_groups() ) :
				bp_the_group();

				// Get group URL.
				$group_url = function_exists( 'bp_get_group_url' )
					? bp_get_group_url()
					: bp_get_group_permalink();
				?>
				<div class="wbcom-groups-grid-card">
					<?php if ( $show_avatar ) : ?>
						<div class="wbcom-groups-grid-avatar">
							<a href="<?php echo esc_url( $group_url ); ?>">
								<?php
								bp_group_avatar(
									array(
										'type'  => 'full',
										'class' => 'avatar',
									)
								);
								?>
							</a>
						</div>
					<?php endif; ?>

					<div class="wbcom-groups-grid-content">
						<?php if ( $show_name ) : ?>
							<h4 class="wbcom-groups-grid-name">
								<a href="<?php echo esc_url( $group_url ); ?>">
									<?php bp_group_name(); ?>
								</a>
							</h4>
						<?php endif; ?>

						<?php if ( $show_description ) : ?>
							<div class="wbcom-groups-grid-description">
								<?php
								$description = bp_get_group_description_excerpt();
								echo wp_kses_post( wp_trim_words( $description, 15 ) );
								?>
							</div>
						<?php endif; ?>

						<?php if ( $show_meta ) : ?>
							<p class="wbcom-groups-grid-meta">
								<?php
								/* translators: %s: Group last active time */
								printf( esc_html__( 'active %s', 'wbcom-essential' ), esc_html( bp_get_group_last_active() ) );
								?>
							</p>
						<?php endif; ?>

						<?php if ( $show_member_count ) : ?>
							<p class="wbcom-groups-grid-members">
								<?php bp_group_member_count(); ?>
							</p>
						<?php endif; ?>
					</div>

					<?php if ( $show_join_button && is_user_logged_in() ) : ?>
						<div class="wbcom-groups-grid-action">
							<?php bp_group_join_button(); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<div class="wbcom-essential-no-data">
			<p><?php esc_html_e( 'Sorry, no groups were found.', 'wbcom-essential' ); ?></p>
		</div>
	<?php endif; ?>
</div>
