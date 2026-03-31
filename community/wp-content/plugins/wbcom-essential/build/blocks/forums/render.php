<?php
/**
 * Server-side render for Forums block.
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

// Check if bbPress is active.
if ( ! class_exists( 'bbPress' ) ) {
	return;
}

// Extract attributes.
$use_theme_colors      = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$heading_text          = $attributes['headingText'] ?? 'Forums';
$forums_count          = $attributes['forumsCount'] ?? 5;
$row_space             = $attributes['rowSpace'] ?? 20;
$show_all_forums_link  = $attributes['showAllForumsLink'] ?? true;
$all_forums_link_text  = $attributes['allForumsLinkText'] ?? 'All Forums';
$show_avatar           = $attributes['showAvatar'] ?? true;
$avatar_size           = $attributes['avatarSize'] ?? 52;
$avatar_border_type    = $attributes['avatarBorderType'] ?? 'none';
$avatar_border_width   = $attributes['avatarBorderWidth'] ?? 0;
$avatar_border_color   = $attributes['avatarBorderColor'] ?? '';
$avatar_border_radius  = $attributes['avatarBorderRadius'] ?? array(
	'top'    => 50,
	'right'  => 50,
	'bottom' => 50,
	'left'   => 50,
	'unit'   => '%',
);
$avatar_opacity        = $attributes['avatarOpacity'] ?? 1;
$avatar_spacing        = $attributes['avatarSpacing'] ?? 15;
$show_meta             = $attributes['showMeta'] ?? true;
$show_meta_replies     = $attributes['showMetaReplies'] ?? true;
$show_last_reply       = $attributes['showLastReply'] ?? true;
$box_border_type       = $attributes['boxBorderType'] ?? 'solid';
$box_border_width      = $attributes['boxBorderWidth'] ?? 1;
$box_bg_color          = $attributes['boxBgColor'] ?? '#ffffff';
$box_border_color      = $attributes['boxBorderColor'] ?? '#e3e3e3';
$box_border_radius     = $attributes['boxBorderRadius'] ?? array(
	'top'    => 4,
	'right'  => 4,
	'bottom' => 4,
	'left'   => 4,
	'unit'   => 'px',
);
$all_forums_link_color = $attributes['allForumsLinkColor'] ?? '';
$title_font_size       = $attributes['titleFontSize'] ?? 14;
$title_font_weight     = $attributes['titleFontWeight'] ?? '400';
$title_line_height     = $attributes['titleLineHeight'] ?? 1.5;
$title_color           = $attributes['titleColor'] ?? '#122B46';
$title_hover_color     = $attributes['titleHoverColor'] ?? '#007CFF';
$meta_font_size        = $attributes['metaFontSize'] ?? 13;
$meta_color            = $attributes['metaColor'] ?? '#A3A5A9';
$last_reply_color      = $attributes['lastReplyColor'] ?? '#4D5C6D';

if ( ! function_exists( 'wbcom_forums_get_dimension' ) ) {
	/**
	 * Helper function to convert dimension object to CSS value.
	 *
	 * @param mixed $dimension The dimension value (object or number).
	 * @return string The CSS dimension value.
	 */
	function wbcom_forums_get_dimension( $dimension ) {
		if ( is_array( $dimension ) && isset( $dimension['top'] ) ) {
			$unit = $dimension['unit'] ?? 'px';
			return sprintf(
				'%s%s %s%s %s%s %s%s',
				$dimension['top'] ?? 0,
				$unit,
				$dimension['right'] ?? 0,
				$unit,
				$dimension['bottom'] ?? 0,
				$unit,
				$dimension['left'] ?? 0,
				$unit
			);
		}
		return $dimension . 'px';
	}
}

// Build inline styles - layout always applied, colors only when not using theme colors.
$inline_styles = array(
	// Layout styles - always applied.
	'--box-border-type'     => $box_border_type,
	'--box-border-width'    => $box_border_width . 'px',
	'--box-radius'          => wbcom_forums_get_dimension( $box_border_radius ),
	'--avatar-size'         => $avatar_size . 'px',
	'--avatar-border-type'  => $avatar_border_type,
	'--avatar-border-width' => $avatar_border_width . 'px',
	'--avatar-radius'       => wbcom_forums_get_dimension( $avatar_border_radius ),
	'--avatar-opacity'      => $avatar_opacity,
	'--avatar-spacing'      => $avatar_spacing . 'px',
	'--row-space'           => $row_space . 'px',
	'--title-font-size'     => $title_font_size . 'px',
	'--title-font-weight'   => $title_font_weight,
	'--title-line-height'   => $title_line_height,
	'--meta-font-size'      => $meta_font_size . 'px',
);

// Color styles - only when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--box-border-color']    = $box_border_color;
	$inline_styles['--box-bg']              = $box_bg_color;
	$inline_styles['--avatar-border-color'] = $avatar_border_color;
	$inline_styles['--title-color']         = $title_color;
	$inline_styles['--title-hover']         = $title_hover_color;
	$inline_styles['--meta-color']          = $meta_color;
	$inline_styles['--last-reply-color']    = $last_reply_color;
	$inline_styles['--link-color']          = $all_forums_link_color ? $all_forums_link_color : $title_color;
}

$style_string = '';
foreach ( $inline_styles as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Query args for bbPress topics.
$args = array(
	'order'          => 'DESC',
	'posts_per_page' => $forums_count,
	'max_num_pages'  => $forums_count,
);

// Ensure bbPress function exists before calling.
if ( ! function_exists( 'bbp_has_topics' ) ) {
	return;
}

$has_topics = bbp_has_topics( $args );

// Container classes.
$container_classes = array(
	'wbcom-essential-forums',
);

if ( $use_theme_colors ) {
	$container_classes[] = 'use-theme-colors';
}

if ( ! $has_topics ) {
	$container_classes[] = 'wbcom-essential-forums--blank';
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $container_classes ),
		'style' => $style_string,
	)
);

// Forums URL.
$forums_url = function_exists( 'bbp_get_root_slug' ) ? home_url( bbp_get_root_slug() ) : '#';
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<?php if ( $has_topics ) : ?>

		<div class="wbcom-essential-block-header">
			<div class="wbcom-essential-block-header__title">
				<h3><?php echo esc_html( $heading_text ); ?></h3>
			</div>
			<?php if ( $show_all_forums_link && $all_forums_link_text ) : ?>
				<div class="wbcom-essential-block-header__extra">
					<a href="<?php echo esc_url( $forums_url ); ?>" class="count-more">
						<?php echo esc_html( $all_forums_link_text ); ?>
						<svg viewBox="0 0 24 24" width="16" height="16">
							<path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z" fill="currentColor" />
						</svg>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<div class="wbcom-forums-list-flow">
			<ul class="wbcom-essential-forums__list">
				<?php
				while ( bbp_topics() ) :
					bbp_the_topic();
					$topic_id = bbp_get_topic_id();
					?>
					<li>
						<div class="wbcom-essential-forums__item">
							<div class="wbcom-forums-flex">
								<?php if ( $show_avatar ) : ?>
									<div class="wbcom-essential-forums__avatar">
										<?php echo wp_kses_post( bbp_get_topic_author_link( array( 'size' => $avatar_size * 2 ) ) ); ?>
									</div>
								<?php endif; ?>

								<div class="wbcom-essential-forums__content">
									<div class="item-title">
										<a class="topic-permalink" href="<?php bbp_topic_permalink(); ?>">
											<?php bbp_topic_title(); ?>
										</a>
									</div>

									<div class="item-meta">
										<?php if ( $show_meta ) : ?>
											<div class="wbcom-essential-forums__ww">
												<span class="bs-replied">
													<?php esc_html_e( 'replied', 'wbcom-essential' ); ?>
													<?php bbp_topic_freshness_link(); ?>
												</span>
												<?php if ( $show_meta_replies ) : ?>
													<span class="bs-voices-wrap">
														<?php
														$voice_count = bbp_get_topic_voice_count( $topic_id );
														$voice_text  = $voice_count > 1 ? __( 'Members', 'wbcom-essential' ) : __( 'Member', 'wbcom-essential' );

														$reply_count = bbp_get_topic_reply_count( $topic_id );
														$reply_text  = $reply_count > 1 ? __( 'Replies', 'wbcom-essential' ) : __( 'Reply', 'wbcom-essential' );
														?>
														<span class="bs-voices">
															<?php bbp_topic_voice_count(); ?> <?php echo esc_html( $voice_text ); ?>
														</span>
														<span class="bs-separator">&middot;</span>
														<span class="bs-replies">
															<?php
															if ( bbp_show_lead_topic() ) {
																bbp_topic_reply_count();
															} else {
																bbp_topic_post_count();
															}
															?>
															<?php echo esc_html( $reply_text ); ?>
														</span>
													</span>
												<?php endif; ?>
											</div>
										<?php endif; ?>

										<?php if ( $show_last_reply ) : ?>
											<?php
											$last_reply_id     = bbp_get_topic_last_reply_id( $topic_id );
											$reply_excerpt     = bbp_get_reply_excerpt( $last_reply_id, 90 );
											$last_reply_author = '';
											$last_reply_avatar = '';

											// Get the last reply author info.
											if ( $last_reply_id && $last_reply_id !== $topic_id ) {
												$last_reply_user_id = bbp_get_reply_author_id( $last_reply_id );
												if ( $last_reply_user_id ) {
													$last_reply_author = bbp_get_reply_author_display_name( $last_reply_id );
													$last_reply_avatar = get_avatar( $last_reply_user_id, 24, '', $last_reply_author );
												}
											}
											?>
											<?php if ( $reply_excerpt || $last_reply_author ) : ?>
												<div class="wbcom-essential-forums__last-reply">
													<?php if ( $last_reply_author ) : ?>
														<span class="bs-last-reply-author">
															<?php echo $last_reply_avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
															<span class="bs-author-name"><?php echo esc_html( $last_reply_author ); ?></span>
														</span>
													<?php endif; ?>
													<?php if ( $reply_excerpt ) : ?>
														<span class="bs-last-reply">
															<?php echo esc_html( $reply_excerpt ); ?>
														</span>
													<?php endif; ?>
												</div>
											<?php endif; ?>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</li>
				<?php endwhile; ?>
			</ul>
		</div>

	<?php else : ?>

		<div class="wbcom-essential-no-data wbcom-essential-no-data--forums">
			<img
				class="wbcom-essential-no-data__image"
				src="<?php echo esc_url( WBCOM_ESSENTIAL_ASSETS_URL ); ?>images/no-data-found.svg"
				alt="<?php esc_attr_e( 'No Forums', 'wbcom-essential' ); ?>"
			/>
			<p><?php esc_html_e( 'No topics found.', 'wbcom-essential' ); ?></p>
			<a href="<?php echo esc_url( $forums_url ); ?>" class="wbcom-essential-no-data__link">
				<?php esc_html_e( 'Start a Discussion', 'wbcom-essential' ); ?>
			</a>
		</div>

	<?php endif; ?>
</div>
