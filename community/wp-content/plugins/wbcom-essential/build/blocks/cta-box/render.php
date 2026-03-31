<?php
/**
 * CTA Box Block - Server-Side Render
 *
 * @package wbcom-essential
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract attributes with defaults.
$use_theme_colors       = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$title                  = ! empty( $attributes['title'] ) ? $attributes['title'] : '';
$title_tag              = ! empty( $attributes['titleTag'] ) ? $attributes['titleTag'] : 'h2';
$description            = ! empty( $attributes['description'] ) ? $attributes['description'] : '';
$button_text            = ! empty( $attributes['buttonText'] ) ? $attributes['buttonText'] : '';
$button_url             = ! empty( $attributes['buttonUrl'] ) ? $attributes['buttonUrl'] : '';
$button_target          = ! empty( $attributes['buttonTarget'] ) ? $attributes['buttonTarget'] : false;
$show_second_button     = ! empty( $attributes['showSecondButton'] ) ? $attributes['showSecondButton'] : false;
$second_button_text     = ! empty( $attributes['secondButtonText'] ) ? $attributes['secondButtonText'] : '';
$second_button_url      = ! empty( $attributes['secondButtonUrl'] ) ? $attributes['secondButtonUrl'] : '';
$second_button_target   = ! empty( $attributes['secondButtonTarget'] ) ? $attributes['secondButtonTarget'] : false;
$layout                 = ! empty( $attributes['layout'] ) ? $attributes['layout'] : 'centered';
$title_color            = ! empty( $attributes['titleColor'] ) ? $attributes['titleColor'] : '';
$description_color      = ! empty( $attributes['descriptionColor'] ) ? $attributes['descriptionColor'] : '';
$button_bg_color        = ! empty( $attributes['buttonBgColor'] ) ? $attributes['buttonBgColor'] : '';
$button_text_color      = ! empty( $attributes['buttonTextColor'] ) ? $attributes['buttonTextColor'] : '';
$button_hover_bg        = ! empty( $attributes['buttonHoverBgColor'] ) ? $attributes['buttonHoverBgColor'] : '';
$button_hover_text      = ! empty( $attributes['buttonHoverTextColor'] ) ? $attributes['buttonHoverTextColor'] : '';
$second_button_bg       = ! empty( $attributes['secondButtonBgColor'] ) ? $attributes['secondButtonBgColor'] : '';
$second_button_text_clr = ! empty( $attributes['secondButtonTextColor'] ) ? $attributes['secondButtonTextColor'] : '';
$button_border_radius   = isset( $attributes['buttonBorderRadius'] ) ? absint( $attributes['buttonBorderRadius'] ) : 4;
$button_padding         = ! empty( $attributes['buttonPadding'] ) ? $attributes['buttonPadding'] : array(
	'top'    => 12,
	'right'  => 24,
	'bottom' => 12,
	'left'   => 24,
);
$title_size             = isset( $attributes['titleSize'] ) ? absint( $attributes['titleSize'] ) : 32;
$description_size       = isset( $attributes['descriptionSize'] ) ? absint( $attributes['descriptionSize'] ) : 16;
$content_spacing        = isset( $attributes['contentSpacing'] ) ? absint( $attributes['contentSpacing'] ) : 24;

// Sanitize title tag.
$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'p' );
if ( ! in_array( $title_tag, $allowed_tags, true ) ) {
	$title_tag = 'h2';
}

// Build inline styles.
$padding_string = sprintf(
	'%dpx %dpx %dpx %dpx',
	absint( $button_padding['top'] ),
	absint( $button_padding['right'] ),
	absint( $button_padding['bottom'] ),
	absint( $button_padding['left'] )
);

$inline_styles = array(
	'--button-radius'       => $button_border_radius . 'px',
	'--button-padding'      => $padding_string,
	'--title-size'          => $title_size . 'px',
	'--description-size'    => $description_size . 'px',
	'--content-spacing'     => $content_spacing . 'px',
);

// Only add color styles if not using theme colors.
if ( ! $use_theme_colors ) {
	$inline_styles['--title-color']        = $title_color;
	$inline_styles['--description-color']  = $description_color;
	$inline_styles['--button-bg-color']    = $button_bg_color;
	$inline_styles['--button-text-color']  = $button_text_color;
	$inline_styles['--button-hover-bg']    = $button_hover_bg;
	$inline_styles['--button-hover-text']  = $button_hover_text;
	$inline_styles['--second-button-bg']   = $second_button_bg;
	$inline_styles['--second-button-text'] = $second_button_text_clr;
}

// Filter out empty values.
$inline_styles = array_filter( $inline_styles );

// Convert to style string.
$style_string = '';
foreach ( $inline_styles as $property => $value ) {
	$style_string .= esc_attr( $property ) . ':' . esc_attr( $value ) . ';';
}

// Build wrapper classes.
$wrapper_classes = 'wbcom-essential-cta-box layout-' . esc_attr( $layout );
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Build wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $wrapper_classes,
		'style' => $style_string,
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wbcom-essential-cta-box__content">
		<?php if ( $title ) : ?>
			<<?php echo esc_attr( $title_tag ); ?> class="wbcom-essential-cta-box__title">
				<?php echo wp_kses_post( $title ); ?>
			</<?php echo esc_attr( $title_tag ); ?>>
		<?php endif; ?>

		<?php if ( $description ) : ?>
			<p class="wbcom-essential-cta-box__description">
				<?php echo wp_kses_post( $description ); ?>
			</p>
		<?php endif; ?>
	</div>

	<div class="wbcom-essential-cta-box__buttons">
		<?php if ( $button_text ) : ?>
			<?php if ( $button_url ) : ?>
				<a href="<?php echo esc_url( $button_url ); ?>" class="wbcom-essential-cta-box__button wbcom-essential-cta-box__button--primary"<?php echo $button_target ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
					<?php echo esc_html( $button_text ); ?>
				</a>
			<?php else : ?>
				<span class="wbcom-essential-cta-box__button wbcom-essential-cta-box__button--primary">
					<?php echo esc_html( $button_text ); ?>
				</span>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $show_second_button && $second_button_text ) : ?>
			<?php if ( $second_button_url ) : ?>
				<a href="<?php echo esc_url( $second_button_url ); ?>" class="wbcom-essential-cta-box__button wbcom-essential-cta-box__button--secondary"<?php echo $second_button_target ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
					<?php echo esc_html( $second_button_text ); ?>
				</a>
			<?php else : ?>
				<span class="wbcom-essential-cta-box__button wbcom-essential-cta-box__button--secondary">
					<?php echo esc_html( $second_button_text ); ?>
				</span>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
