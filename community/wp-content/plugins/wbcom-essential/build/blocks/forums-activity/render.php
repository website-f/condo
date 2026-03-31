<?php
/**
 * Server-side render for Forums Activity block.
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

// Check if bbPress and BuddyPress are active.
if ( ! class_exists( 'bbPress' ) || ! function_exists( 'buddypress' ) ) {
	return;
}

// Extract attributes.
$use_theme_colors          = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$show_forum_title          = $attributes['showForumTitle'] ?? true;
$show_meta                 = $attributes['showMeta'] ?? true;
$show_excerpt              = $attributes['showExcerpt'] ?? true;
$show_view_button          = $attributes['showViewButton'] ?? true;
$view_button_text          = $attributes['viewButtonText'] ?? 'View Discussion';
$show_my_discussions       = $attributes['showMyDiscussionsButton'] ?? true;
$my_discussions_text       = $attributes['myDiscussionsButtonText'] ?? 'View My Discussions';
$no_forums_text            = $attributes['noForumsText'] ?? "You don't have any discussions yet.";
$no_forums_button_text     = $attributes['noForumsButtonText'] ?? 'Explore Forums';
$box_bg_color              = $attributes['boxBgColor'] ?? '#ffffff';
$box_border_color          = $attributes['boxBorderColor'] ?? '#e3e3e3';
$box_border_radius         = $attributes['boxBorderRadius'] ?? 4;
$box_padding               = $attributes['boxPadding'] ?? 20;
$forum_title_color         = $attributes['forumTitleColor'] ?? '#A3A5A9';
$topic_title_color         = $attributes['topicTitleColor'] ?? '#122B46';
$meta_color                = $attributes['metaColor'] ?? '#A3A5A9';
$excerpt_color             = $attributes['excerptColor'] ?? '#666666';
$button_color              = $attributes['buttonColor'] ?? '#122B46';
$button_border_color       = $attributes['buttonBorderColor'] ?? '#e3e3e3';
$button_align              = $attributes['buttonAlign'] ?? 'right';

// Build inline styles - layout always applied, colors only when not using theme colors.
$inline_styles = array(
	// Layout styles - always applied.
	'--box-radius'  => $box_border_radius . 'px',
	'--box-padding' => $box_padding . 'px',
);

// Color styles - only when not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--box-bg']              = $box_bg_color;
	$inline_styles['--box-border-color']    = $box_border_color;
	$inline_styles['--forum-title-color']   = $forum_title_color;
	$inline_styles['--topic-title-color']   = $topic_title_color;
	$inline_styles['--meta-color']          = $meta_color;
	$inline_styles['--excerpt-color']       = $excerpt_color;
	$inline_styles['--button-color']        = $button_color;
	$inline_styles['--button-border-color'] = $button_border_color;
}

$style_string = '';
foreach ( $inline_styles as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Container classes.
$wrapper_classes = array(
	'wbcom-essential-forums-activity-wrapper',
	'button-align-' . $button_align,
);

if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

if ( $show_my_discussions ) {
	$wrapper_classes[] = 'wbcom-essential-forums-activity-wrapper--ismy';
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $wrapper_classes ),
	'style' => $style_string,
) );

// Get forums root URL.
$forums_url = function_exists( 'bbp_get_root_slug' ) ? home_url( bbp_get_root_slug() ) : '#';

// Get my discussions link (for logged-in users).
$my_discussions_link = '';
if ( is_user_logged_in() && function_exists( 'bp_loggedin_user_domain' ) && function_exists( 'bbp_maybe_get_root_slug' ) ) {
	$my_discussions_link = trailingslashit( bp_loggedin_user_domain() . bbp_maybe_get_root_slug() );
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<?php if ( $show_my_discussions && is_user_logged_in() && $my_discussions_link ) : ?>
		<div class="wbcom-essential-forums-activity-btn">
			<a class="wbcom-essential-forums-activity-btn__link" href="<?php echo esc_url( $my_discussions_link ); ?>">
				<?php echo esc_html( $my_discussions_text ); ?>
				<svg viewBox="0 0 24 24" width="20" height="20">
					<path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z" fill="currentColor" />
				</svg>
			</a>
		</div>
	<?php endif; ?>

	<div class="wbcom-essential-forums-activity">
		<?php
		if ( is_user_logged_in() ) {
			$current_user_id = get_current_user_id();

			$has_topics = bbp_has_topics( array(
				'author'         => $current_user_id,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'posts_per_page' => 1,
			) );

			if ( $has_topics ) {
				while ( bbp_topics() ) :
					bbp_the_topic();

					$topic_id                = bbp_get_topic_id();
					$forum_title             = bbp_get_forum_title( bbp_get_topic_forum_id() );
					$topic_title             = bbp_get_topic_title( $topic_id );
					$topic_reply_count       = bbp_get_topic_reply_count( $topic_id );
					$last_reply_id           = bbp_get_topic_last_reply_id();
					$last_reply_author       = bbp_get_reply_author_display_name( $last_reply_id );
					$last_reply_time         = bbp_get_topic_last_active_time( $topic_id );
					$discussion_link         = bbp_get_topic_permalink( $topic_id );
					$last_reply_excerpt      = bbp_get_reply_excerpt( $last_reply_id, 50 );
					$reply_text              = ( 1 === (int) $topic_reply_count ) ? __( 'reply', 'wbcom-essential' ) : __( 'replies', 'wbcom-essential' );
					?>
					<div class="wbcom-essential-fa wbcom-essential-fa--item">
						<?php if ( $show_forum_title && $forum_title ) : ?>
							<div class="wbcom-essential-fa__forum-title">
								<?php echo esc_html( $forum_title ); ?>
							</div>
						<?php endif; ?>

						<div class="wbcom-essential-fa__topic-title">
							<h2><?php echo esc_html( $topic_title ); ?></h2>
						</div>

						<?php if ( $show_meta ) : ?>
							<div class="wbcom-essential-fa__meta">
								<span class="wbcom-essential-fa__meta-count">
									<?php echo esc_html( $topic_reply_count . ' ' . $reply_text ); ?>
								</span>
								<span class="bs-separator">·</span>
								<span class="wbcom-essential-fa__meta-who">
									<?php echo esc_html( $last_reply_author ); ?>
									<?php esc_html_e( 'replied', 'wbcom-essential' ); ?>
								</span>
								<span class="wbcom-essential-fa__meta-when">
									<?php echo esc_html( $last_reply_time ); ?>
								</span>
							</div>
						<?php endif; ?>

						<?php if ( $show_excerpt && $last_reply_excerpt ) : ?>
							<div class="wbcom-essential-fa__excerpt">
								<?php echo esc_html( $last_reply_excerpt ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $show_view_button ) : ?>
							<div class="wbcom-essential-fa__link">
								<a href="<?php echo esc_url( $discussion_link ); ?>">
									<?php echo esc_html( $view_button_text ); ?>
								</a>
							</div>
						<?php endif; ?>
					</div>
					<?php
				endwhile;
			} else {
				// No topics found for this user.
				?>
				<div class="wbcom-essential-no-data wbcom-essential-no-data--fa-activity">
					<img
						class="wbcom-essential-no-data__image"
						src="<?php echo esc_url( WBCOM_ESSENTIAL_ASSETS_URL ); ?>images/no-data-found.svg"
						alt="<?php esc_attr_e( 'No Forums Activity', 'wbcom-essential' ); ?>"
					/>
					<div class="wbcom-essential-no-data__msg">
						<?php echo esc_html( $no_forums_text ); ?>
					</div>
					<?php if ( $no_forums_button_text ) : ?>
						<a href="<?php echo esc_url( $forums_url ); ?>" class="wbcom-essential-no-data__link">
							<?php echo esc_html( $no_forums_button_text ); ?>
						</a>
					<?php endif; ?>
				</div>
				<?php
			}
		} else {
			// User not logged in.
			?>
			<div class="wbcom-essential-no-data wbcom-essential-no-data--fa-activity">
				<img
					class="wbcom-essential-no-data__image"
					src="<?php echo esc_url( WBCOM_ESSENTIAL_ASSETS_URL ); ?>images/no-data-found.svg"
					alt="<?php esc_attr_e( 'Not Logged In', 'wbcom-essential' ); ?>"
				/>
				<div class="wbcom-essential-no-data__msg">
					<?php esc_html_e( 'Please log in to see your forum activity.', 'wbcom-essential' ); ?>
				</div>
				<?php if ( $no_forums_button_text ) : ?>
					<a href="<?php echo esc_url( $forums_url ); ?>" class="wbcom-essential-no-data__link">
						<?php echo esc_html( $no_forums_button_text ); ?>
					</a>
				<?php endif; ?>
			</div>
			<?php
		}
		?>
	</div>
</div>
