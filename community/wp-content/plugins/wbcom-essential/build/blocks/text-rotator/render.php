<?php
/**
 * Server-side render for Text Rotator block.
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
$prefix_text         = $attributes['prefixText'] ?? '';
$rotating_texts      = $attributes['rotatingTexts'] ?? array();
$suffix_text         = $attributes['suffixText'] ?? '';
$html_tag            = $attributes['htmlTag'] ?? 'h2';
$text_align          = $attributes['textAlign'] ?? 'center';
$animation           = $attributes['animation'] ?? 'fadeIn';
$duration            = $attributes['duration'] ?? 3000;
$show_cursor         = $attributes['showCursor'] ?? true;
$cursor_char         = $attributes['cursorChar'] ?? '|';
$loop_count          = $attributes['loopCount'] ?? 0;
$text_color          = $attributes['textColor'] ?? '';
$rotating_text_color = $attributes['rotatingTextColor'] ?? '#3182ce';
$rotating_text_bg    = $attributes['rotatingTextBg'] ?? '';
$prefix_color        = $attributes['prefixColor'] ?? '';
$suffix_color        = $attributes['suffixColor'] ?? '';

// Allowed tags.
$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' );
if ( ! in_array( $html_tag, $allowed_tags, true ) ) {
	$html_tag = 'h2';
}

// Build inline styles - layout always, colors only when NOT using theme colors.
$inline_styles = array();
if ( $text_align ) {
	$inline_styles[] = 'text-align: ' . esc_attr( $text_align );
}

// Color variables only when NOT using theme colors.
if ( ! $use_theme_colors ) {
	if ( $text_color ) {
		$inline_styles[] = '--text-color: ' . esc_attr( $text_color );
	}
	if ( $rotating_text_color ) {
		$inline_styles[] = '--rotating-color: ' . esc_attr( $rotating_text_color );
	}
	if ( $rotating_text_bg ) {
		$inline_styles[] = '--rotating-bg: ' . esc_attr( $rotating_text_bg );
	}
	if ( $prefix_color ) {
		$inline_styles[] = '--prefix-color: ' . esc_attr( $prefix_color );
	}
	if ( $suffix_color ) {
		$inline_styles[] = '--suffix-color: ' . esc_attr( $suffix_color );
	}
}

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-text-rotator';
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'            => $wrapper_classes,
		'style'            => implode( '; ', $inline_styles ),
		'data-animation'   => esc_attr( $animation ),
		'data-duration'    => esc_attr( $duration ),
		'data-show-cursor' => $show_cursor ? '1' : '0',
		'data-cursor-char' => esc_attr( $cursor_char ),
		'data-loop-count'  => esc_attr( $loop_count ),
	)
);

// Prepare rotating texts as JSON for JS.
$texts_json = wp_json_encode( array_column( $rotating_texts, 'text' ) );
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<<?php echo esc_html( $html_tag ); ?> class="wbcom-text-rotator-wrapper">
		<?php if ( $prefix_text ) : ?>
			<span class="wbcom-text-rotator-prefix"><?php echo esc_html( $prefix_text ); ?> </span>
		<?php endif; ?>
		<span
			class="wbcom-text-rotator-rotating wbcom-animation-<?php echo esc_attr( $animation ); ?>"
			data-texts="<?php echo esc_attr( $texts_json ); ?>"
		>
			<?php echo esc_html( $rotating_texts[0]['text'] ?? '' ); ?>
		</span>
		<?php if ( $suffix_text ) : ?>
			<span class="wbcom-text-rotator-suffix"> <?php echo esc_html( $suffix_text ); ?></span>
		<?php endif; ?>
	</<?php echo esc_html( $html_tag ); ?>>
</div>
