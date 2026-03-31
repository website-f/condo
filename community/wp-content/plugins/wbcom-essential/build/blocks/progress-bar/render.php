<?php
/**
 * Server-side render for Progress Bar block.
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
$use_theme_colors    = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$title               = $attributes['title'] ?? __( 'Progress', 'wbcom-essential' );
$percent             = $attributes['percent'] ?? 75;
$display_percent     = $attributes['displayPercent'] ?? 'in';
$show_stripes        = $attributes['showStripes'] ?? false;
$animate_stripes     = $attributes['animateStripes'] ?? true;
$animation_duration  = $attributes['animationDuration'] ?? 1500;
$scroll_animation    = $attributes['scrollAnimation'] ?? true;
$bar_height          = $attributes['barHeight'] ?? 20;
$border_radius       = $attributes['borderRadius'] ?? 10;
$bar_color           = $attributes['barColor'] ?? '#3182ce';
$bar_background      = $attributes['barBackground'] ?? '#e2e8f0';
$title_color         = $attributes['titleColor'] ?? '#1a202c';
$percent_color       = $attributes['percentColor'] ?? '#ffffff';
$percent_out_color   = $attributes['percentOutColor'] ?? '#1a202c';
$inner_border_radius = $attributes['innerBorderRadius'] ?? 10;
$box_shadow          = $attributes['boxShadow'] ?? false;
$background_height   = $attributes['backgroundHeight'] ?? 20;

// Build wrapper style - layout vars always, colors only when not using theme colors.
$box_shadow_style = $box_shadow ? 'inset 0 1px 3px rgba(0, 0, 0, 0.15)' : 'none';
$wrapper_style    = sprintf(
	'border-radius: %dpx; height: %dpx; box-shadow: %s;',
	absint( $border_radius ),
	absint( $background_height ),
	esc_attr( $box_shadow_style )
);
if ( ! $use_theme_colors ) {
	$wrapper_style .= sprintf( ' background-color: %s;', esc_attr( $bar_background ) );
}

// Build bar style - layout vars always, colors only when not using theme colors.
$initial_width = $scroll_animation ? '0' : $percent . '%';
$bar_style     = sprintf(
	'width: %s; border-radius: %dpx; height: %dpx; transition: width %dms ease-out;',
	esc_attr( $initial_width ),
	absint( $inner_border_radius ),
	absint( $bar_height ),
	absint( $animation_duration )
);
if ( ! $use_theme_colors ) {
	$bar_style .= sprintf( ' background-color: %s;', esc_attr( $bar_color ) );
}

// Build stripes class.
$stripes_class = '';
if ( $show_stripes ) {
	$stripes_class = $animate_stripes ? ' has-stripes stripes-animated' : ' has-stripes';
}

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-progress-bar';
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes( array(
	'class'                   => $wrapper_classes,
	'data-percent'            => absint( $percent ),
	'data-scroll-animation'   => $scroll_animation ? 'true' : 'false',
	'data-animation-duration' => absint( $animation_duration ),
) );
?>

<?php
// Build element styles - colors only when not using theme colors.
$title_style       = $use_theme_colors ? '' : sprintf( 'color: %s;', esc_attr( $title_color ) );
$percent_out_style = $use_theme_colors ? '' : sprintf( 'color: %s;', esc_attr( $percent_out_color ) );
$percent_in_style  = $use_theme_colors ? '' : sprintf( 'color: %s;', esc_attr( $percent_color ) );
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wbcom-progress-bar-header">
		<?php if ( ! empty( $title ) ) : ?>
			<span class="wbcom-progress-bar-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>>
				<?php echo esc_html( $title ); ?>
			</span>
		<?php endif; ?>
		<?php if ( 'out' === $display_percent ) : ?>
			<span class="wbcom-progress-bar-percent-out"<?php echo $percent_out_style ? ' style="' . esc_attr( $percent_out_style ) . '"' : ''; ?>>
				<?php echo absint( $percent ); ?>%
			</span>
		<?php endif; ?>
	</div>
	<div class="wbcom-progress-bar-wrapper" style="<?php echo esc_attr( $wrapper_style ); ?>">
		<div class="wbcom-progress-bar-fill<?php echo esc_attr( $stripes_class ); ?>" style="<?php echo esc_attr( $bar_style ); ?>">
			<?php if ( 'in' === $display_percent ) : ?>
				<span class="wbcom-progress-bar-percent-in"<?php echo $percent_in_style ? ' style="' . esc_attr( $percent_in_style ) . '"' : ''; ?>>
					<?php echo absint( $percent ); ?>%
				</span>
			<?php endif; ?>
		</div>
	</div>
</div>
