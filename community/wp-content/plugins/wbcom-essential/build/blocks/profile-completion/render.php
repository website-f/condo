<?php
/**
 * Server-side render for Profile Completion block.
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

// Check if BuddyPress is active and user is logged in.
if ( ! function_exists( 'buddypress' ) || ! is_user_logged_in() ) {
	return;
}

// Extract attributes.
$use_theme_colors       = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$skin_style             = $attributes['skinStyle'] ?? 'circle';
$alignment              = $attributes['alignment'] ?? 'right';
$profile_photo          = $attributes['profilePhoto'] ?? true;
$cover_photo            = $attributes['coverPhoto'] ?? true;
$profile_groups_attr    = $attributes['profileGroups'] ?? array();
$hide_widget            = $attributes['hideWidget'] ?? false;
$show_profile_btn       = $attributes['showProfileBtn'] ?? true;
$heading_text           = $attributes['headingText'] ?? __( 'Complete your profile', 'wbcom-essential' );
$completion_text        = $attributes['completionText'] ?? __( 'Complete', 'wbcom-essential' );
$completion_btn_text    = $attributes['completionButtonText'] ?? __( 'Complete Profile', 'wbcom-essential' );
$edit_btn_text          = $attributes['editButtonText'] ?? __( 'Edit Profile', 'wbcom-essential' );
$show_heading           = $attributes['showHeading'] ?? true;
$show_completion_icon   = $attributes['showCompletionIcon'] ?? true;
$show_completion_status = $attributes['showCompletionStatus'] ?? true;

// Style attributes.
$progress_border_width = $attributes['progressBorderWidth'] ?? 6;
$completion_color      = $attributes['completionColor'] ?? '#1CD991';
$incomplete_color      = $attributes['incompleteColor'] ?? '#EF3E46';
$ring_border_color     = $attributes['ringBorderColor'] ?? '#DEDFE2';
$ring_num_color        = $attributes['ringNumColor'] ?? '';
$ring_text_color       = $attributes['ringTextColor'] ?? '';
$details_color         = $attributes['detailsColor'] ?? '#fff';
$button_color          = $attributes['buttonColor'] ?? '';
$button_bg_color       = $attributes['buttonBgColor'] ?? '';
$button_border_color   = $attributes['buttonBorderColor'] ?? '';

// Get selected profile groups.
$selected_groups = array();
if ( ! empty( $profile_groups_attr ) && is_array( $profile_groups_attr ) ) {
	foreach ( $profile_groups_attr as $group_id => $enabled ) {
		if ( $enabled ) {
			$selected_groups[] = $group_id;
		}
	}
}

// Build photo types array.
$profile_phototype_selected = array();
if ( $profile_photo && function_exists( 'bp_disable_avatar_uploads' ) && ! bp_disable_avatar_uploads() ) {
	$profile_phototype_selected[] = 'profile_photo';
}
if ( $cover_photo && function_exists( 'bp_disable_cover_image_uploads' ) && ! bp_disable_cover_image_uploads() ) {
	$profile_phototype_selected[] = 'cover_photo';
}

// If nothing selected, return.
if ( empty( $selected_groups ) && empty( $profile_phototype_selected ) ) {
	return;
}

// Calculate profile completion.
$user_id = get_current_user_id();

// Ensure the helper function exists.
if ( ! function_exists( 'wbcom_essential_calculate_profile_completion' ) ) {
	return;
}

$profile_percent = wbcom_essential_calculate_profile_completion( $user_id, $selected_groups, $profile_phototype_selected );

if ( empty( $profile_percent ) ) {
	return;
}

$completion_percentage = $profile_percent['completion_percentage'];

// Hide widget if completion is 100% and hideWidget is enabled.
if ( $hide_widget && 100 === $completion_percentage ) {
	echo '<div class="wbcom-essential-profile-completion wbcom-essential-profile-completion--blank"></div>';
	return;
}

// Build inline styles - layout always applied, colors only when not using theme colors.
$inline_style = sprintf(
	'--progress-width: %dpx; --progress-percent: %d;',
	absint( $progress_border_width ),
	absint( $completion_percentage )
);

// Color styles - only when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_style .= sprintf(
		' --completion-color: %s; --incomplete-color: %s; --progress-border: %s; --details-bg: %s;',
		esc_attr( $completion_color ),
		esc_attr( $incomplete_color ),
		esc_attr( $ring_border_color ),
		esc_attr( $details_color )
	);

	if ( ! empty( $ring_num_color ) ) {
		$inline_style .= sprintf( ' --number-color: %s;', esc_attr( $ring_num_color ) );
	}
	if ( ! empty( $ring_text_color ) ) {
		$inline_style .= sprintf( ' --text-color: %s;', esc_attr( $ring_text_color ) );
	}
	if ( ! empty( $button_color ) ) {
		$inline_style .= sprintf( ' --button-color: %s;', esc_attr( $button_color ) );
	}
	if ( ! empty( $button_bg_color ) ) {
		$inline_style .= sprintf( ' --button-bg: %s;', esc_attr( $button_bg_color ) );
	}
	if ( ! empty( $button_border_color ) ) {
		$inline_style .= sprintf( ' --button-border: %s;', esc_attr( $button_border_color ) );
	}
}

// Get wrapper attributes.
$wrapper_classes = array(
	'wbcom-essential-profile-completion',
	'wbcom-profile-completion-skin-' . $skin_style,
	'wbcom-profile-completion-align-' . $alignment,
);

if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', array_filter( $wrapper_classes ) ),
		'style' => $inline_style,
	)
);

$is_complete = ( 100 === $completion_percentage );
$profile_url = function_exists( 'bp_loggedin_user_domain' ) ? bp_loggedin_user_domain() . 'profile/edit/' : '#';
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wbcom-profile-completion-wrapper">
		<div class="wbcom-profile-completion-figure">
			<?php if ( 'circle' === $skin_style ) : ?>
				<!-- Circle Skin -->
				<div class="wbcom-profile-completion-progress">
					<div class="wbcom-progress-ring">
						<div class="wbcom-progress-ring-inner"></div>
						<div class="wbcom-progress-data">
							<span class="wbcom-progress-num"><?php echo absint( $completion_percentage ); ?><span>%</span></span>
							<span class="wbcom-progress-text"><?php echo esc_html( $completion_text ); ?></span>
						</div>
					</div>
				</div>

				<div class="wbcom-profile-completion-details">
					<?php if ( $show_heading ) : ?>
						<div class="wbcom-details-header">
							<span class="wbcom-details-percent"><?php echo absint( $completion_percentage ); ?>%</span>
							<span class="wbcom-details-ring-small">
								<span class="wbcom-progress-ring-small"></span>
							</span>
							<span class="wbcom-details-label"><?php echo esc_html( $completion_text ); ?></span>
						</div>
					<?php endif; ?>

					<ul class="wbcom-profile-completion-list">
						<?php foreach ( $profile_percent['groups'] as $single_section_details ) : ?>
							<li class="<?php echo $single_section_details['is_group_completed'] ? 'completed' : 'incomplete'; ?>">
								<?php if ( $show_completion_icon ) : ?>
									<span class="wbcom-section-icon">
										<?php if ( $single_section_details['is_group_completed'] ) : ?>
											<span class="dashicons dashicons-yes"></span>
										<?php else : ?>
											<span class="wbcom-section-dot"></span>
										<?php endif; ?>
									</span>
								<?php endif; ?>
								<span class="wbcom-section-name">
									<a href="<?php echo esc_url( $single_section_details['link'] ); ?>"><?php echo esc_html( $single_section_details['label'] ); ?></a>
								</span>
								<?php if ( $show_completion_status ) : ?>
									<span class="wbcom-section-status">
										<?php echo absint( $single_section_details['completed'] ); ?>/<?php echo absint( $single_section_details['total'] ); ?>
									</span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>

					<?php if ( $show_profile_btn ) : ?>
						<div class="wbcom-profile-completion-action">
							<a class="wbcom-profile-button" href="<?php echo esc_url( $profile_url ); ?>">
								<?php echo esc_html( $is_complete ? $edit_btn_text : $completion_btn_text ); ?>
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</a>
						</div>
					<?php endif; ?>
				</div>

			<?php else : ?>
				<!-- Linear Skin -->
				<div class="wbcom-profile-completion-progress">
					<div class="wbcom-progress-linear-header">
						<h3><?php echo esc_html( $heading_text ); ?></h3>
						<span class="wbcom-toggle-icon dashicons dashicons-arrow-right-alt2"></span>
					</div>
					<div class="wbcom-progress-bar">
						<div class="wbcom-progress-bar-fill" style="width: <?php echo absint( $completion_percentage ); ?>%"></div>
					</div>
					<div class="wbcom-progress-info">
						<span class="wbcom-progress-num"><?php echo absint( $completion_percentage ); ?>%</span>
						<span class="wbcom-progress-text"><?php echo esc_html( $completion_text ); ?></span>
					</div>
				</div>

				<div class="wbcom-profile-completion-details">
					<ul class="wbcom-profile-completion-list">
						<?php foreach ( $profile_percent['groups'] as $single_section_details ) : ?>
							<li class="<?php echo $single_section_details['is_group_completed'] ? 'completed' : 'incomplete'; ?>">
								<?php if ( $show_completion_icon ) : ?>
									<span class="wbcom-section-icon">
										<?php if ( $single_section_details['is_group_completed'] ) : ?>
											<span class="dashicons dashicons-yes"></span>
										<?php else : ?>
											<span class="wbcom-section-dot"></span>
										<?php endif; ?>
									</span>
								<?php endif; ?>
								<span class="wbcom-section-name">
									<a href="<?php echo esc_url( $single_section_details['link'] ); ?>"><?php echo esc_html( $single_section_details['label'] ); ?></a>
								</span>
								<?php if ( $show_completion_status ) : ?>
									<span class="wbcom-section-status">
										<?php echo absint( $single_section_details['completed'] ); ?>/<?php echo absint( $single_section_details['total'] ); ?>
									</span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>

					<?php if ( $show_profile_btn ) : ?>
						<div class="wbcom-profile-completion-action">
							<a class="wbcom-profile-button" href="<?php echo esc_url( $profile_url ); ?>">
								<?php echo esc_html( $is_complete ? $edit_btn_text : $completion_btn_text ); ?>
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</a>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
