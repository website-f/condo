<?php
/**
 * Server-side render for Countdown block.
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
$use_theme_colors = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$due_date         = $attributes['dueDate'] ?? '';
$show_days      = $attributes['showDays'] ?? true;
$show_hours     = $attributes['showHours'] ?? true;
$show_minutes   = $attributes['showMinutes'] ?? true;
$show_seconds   = $attributes['showSeconds'] ?? true;
$days_label     = $attributes['daysLabel'] ?? __( 'Days', 'wbcom-essential' );
$hours_label    = $attributes['hoursLabel'] ?? __( 'Hours', 'wbcom-essential' );
$minutes_label  = $attributes['minutesLabel'] ?? __( 'Minutes', 'wbcom-essential' );
$seconds_label  = $attributes['secondsLabel'] ?? __( 'Seconds', 'wbcom-essential' );
$expiry_message = $attributes['expiryMessage'] ?? '';
$content_layout = $attributes['contentLayout'] ?? 'v-layout';
$box_align      = $attributes['boxAlign'] ?? 'center';
$box_background = $attributes['boxBackground'] ?? '';
$box_radius     = $attributes['boxBorderRadius'] ?? 0;
$box_padding    = $attributes['boxPadding'] ?? array(
	'top'    => '20px',
	'right'  => '20px',
	'bottom' => '20px',
	'left'   => '20px',
);
$box_gap        = $attributes['boxGap'] ?? 10;
$digit_color    = $attributes['digitColor'] ?? '';
$digit_size     = $attributes['digitFontSize'] ?? 60;
$label_color    = $attributes['labelColor'] ?? '';
$label_size     = $attributes['labelFontSize'] ?? 16;
$message_color  = $attributes['messageColor'] ?? '';
$message_size   = $attributes['messageFontSize'] ?? 24;

// Set default due date if not provided.
if ( empty( $due_date ) ) {
	$due_date = gmdate( 'Y-m-d H:i', strtotime( '+1 month' ) );
}

// Build container style.
$container_style = sprintf(
	'justify-content: %s; gap: %dpx;',
	esc_attr( $box_align ),
	absint( $box_gap )
);

// Build box style.
$box_style_parts = array();
if ( ! empty( $box_background ) ) {
	$box_style_parts[] = 'background-color: ' . esc_attr( $box_background );
}
if ( $box_radius > 0 ) {
	$box_style_parts[] = 'border-radius: ' . absint( $box_radius ) . 'px';
}
if ( ! empty( $box_padding ) ) {
	$padding_value = sprintf(
		'%s %s %s %s',
		esc_attr( $box_padding['top'] ?? '20px' ),
		esc_attr( $box_padding['right'] ?? '20px' ),
		esc_attr( $box_padding['bottom'] ?? '20px' ),
		esc_attr( $box_padding['left'] ?? '20px' )
	);
	$box_style_parts[] = 'padding: ' . $padding_value;
}
$box_style = implode( '; ', $box_style_parts );

// Build digit style.
$digit_style_parts = array();
if ( ! $use_theme_colors && ! empty( $digit_color ) ) {
	$digit_style_parts[] = 'color: ' . esc_attr( $digit_color );
}
if ( $digit_size > 0 ) {
	$digit_style_parts[] = 'font-size: ' . absint( $digit_size ) . 'px';
}
$digit_style = implode( '; ', $digit_style_parts );

// Build label style.
$label_style_parts = array();
if ( ! $use_theme_colors && ! empty( $label_color ) ) {
	$label_style_parts[] = 'color: ' . esc_attr( $label_color );
}
if ( $label_size > 0 ) {
	$label_style_parts[] = 'font-size: ' . absint( $label_size ) . 'px';
}
$label_style = implode( '; ', $label_style_parts );

// Build message style.
$message_style_parts = array();
if ( ! $use_theme_colors && ! empty( $message_color ) ) {
	$message_style_parts[] = 'color: ' . esc_attr( $message_color );
}
if ( $message_size > 0 ) {
	$message_style_parts[] = 'font-size: ' . absint( $message_size ) . 'px';
}
$message_style = implode( '; ', $message_style_parts );

// Build wrapper classes.
$wrapper_classes = array( 'wbcom-essential-countdown' );
if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $wrapper_classes ),
) );
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wbcom-countdown <?php echo esc_attr( $content_layout ); ?>"
		data-duedate="<?php echo esc_attr( $due_date ); ?>"
		style="<?php echo esc_attr( $container_style ); ?>">

		<?php if ( $show_days ) : ?>
		<div class="wbcom-countdown-days" style="<?php echo esc_attr( $box_style ); ?>">
			<span class="wbcom-countdown-value" style="<?php echo esc_attr( $digit_style ); ?>">00</span>
			<span class="wbcom-countdown-label" style="<?php echo esc_attr( $label_style ); ?>"><?php echo esc_html( $days_label ); ?></span>
		</div>
		<?php endif; ?>

		<?php if ( $show_hours ) : ?>
		<div class="wbcom-countdown-hours" style="<?php echo esc_attr( $box_style ); ?>">
			<span class="wbcom-countdown-value" style="<?php echo esc_attr( $digit_style ); ?>">00</span>
			<span class="wbcom-countdown-label" style="<?php echo esc_attr( $label_style ); ?>"><?php echo esc_html( $hours_label ); ?></span>
		</div>
		<?php endif; ?>

		<?php if ( $show_minutes ) : ?>
		<div class="wbcom-countdown-minutes" style="<?php echo esc_attr( $box_style ); ?>">
			<span class="wbcom-countdown-value" style="<?php echo esc_attr( $digit_style ); ?>">00</span>
			<span class="wbcom-countdown-label" style="<?php echo esc_attr( $label_style ); ?>"><?php echo esc_html( $minutes_label ); ?></span>
		</div>
		<?php endif; ?>

		<?php if ( $show_seconds ) : ?>
		<div class="wbcom-countdown-seconds" style="<?php echo esc_attr( $box_style ); ?>">
			<span class="wbcom-countdown-value" style="<?php echo esc_attr( $digit_style ); ?>">00</span>
			<span class="wbcom-countdown-label" style="<?php echo esc_attr( $label_style ); ?>"><?php echo esc_html( $seconds_label ); ?></span>
		</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $expiry_message ) ) : ?>
	<div class="wbcom-countdown-msg" style="display: none; <?php echo esc_attr( $message_style ); ?>">
		<?php echo wp_kses_post( $expiry_message ); ?>
	</div>
	<?php endif; ?>
</div>
