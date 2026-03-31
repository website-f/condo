<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items            = isset( $attributes['items'] ) ? $attributes['items'] : array();
$open_single      = isset( $attributes['openSingle'] ) ? $attributes['openSingle'] : false;
$self_close       = isset( $attributes['selfClose'] ) ? $attributes['selfClose'] : false;
$auto_scroll      = isset( $attributes['autoScroll'] ) ? $attributes['autoScroll'] : false;
$scroll_offset    = isset( $attributes['scrollOffset'] ) ? $attributes['scrollOffset'] : 0;
$scroll_speed     = isset( $attributes['scrollSpeed'] ) ? $attributes['scrollSpeed'] : 400;
$open_speed       = isset( $attributes['openSpeed'] ) ? $attributes['openSpeed'] : 200;
$close_speed      = isset( $attributes['closeSpeed'] ) ? $attributes['closeSpeed'] : 200;
$enable_faq_schema = isset( $attributes['enableFaqSchema'] ) ? $attributes['enableFaqSchema'] : false;
$title_tag        = isset( $attributes['titleTag'] ) ? $attributes['titleTag'] : 'h3';
$item_spacing     = isset( $attributes['itemSpacing'] ) ? $attributes['itemSpacing'] : 10;
$border_radius    = isset( $attributes['borderRadius'] ) ? $attributes['borderRadius'] : 4;
$border_width     = isset( $attributes['borderWidth'] ) ? $attributes['borderWidth'] : 1;
$use_theme_colors = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;

// Color attributes (only used when useThemeColors is false).
$title_color        = isset( $attributes['titleColor'] ) ? $attributes['titleColor'] : '';
$title_bg_color     = isset( $attributes['titleBgColor'] ) ? $attributes['titleBgColor'] : '#f8f9fa';
$content_color      = isset( $attributes['contentColor'] ) ? $attributes['contentColor'] : '';
$content_bg_color   = isset( $attributes['contentBgColor'] ) ? $attributes['contentBgColor'] : '#fff';
$border_color       = isset( $attributes['borderColor'] ) ? $attributes['borderColor'] : '#ddd';
$title_font_size    = isset( $attributes['titleFontSize'] ) ? $attributes['titleFontSize'] : 16;
$title_font_weight  = isset( $attributes['titleFontWeight'] ) ? $attributes['titleFontWeight'] : '600';
$title_line_height  = isset( $attributes['titleLineHeight'] ) ? $attributes['titleLineHeight'] : 1.5;
$content_font_size  = isset( $attributes['contentFontSize'] ) ? $attributes['contentFontSize'] : 14;
$content_font_weight = isset( $attributes['contentFontWeight'] ) ? $attributes['contentFontWeight'] : '400';
$content_line_height = isset( $attributes['contentLineHeight'] ) ? $attributes['contentLineHeight'] : 1.6;

$settings = array(
	'openSingle' => $open_single,
	'selfClose' => $self_close,
	'autoScroll' => $auto_scroll,
	'scrollOffset' => $scroll_offset,
	'scrollSpeed' => $scroll_speed,
	'openSpeed' => $open_speed,
	'closeSpeed' => $close_speed,
);

$wrapper_classes = $use_theme_colors ? 'use-theme-colors' : '';

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class'             => $wrapper_classes,
	'data-opensingle'   => $open_single ? 'true' : 'false',
	'data-selfclose'    => $self_close ? 'true' : 'false',
	'data-autoscroll'   => $auto_scroll ? 'true' : 'false',
	'data-scrolloffset' => $scroll_offset,
	'data-scrollspeed'  => $scroll_speed,
	'data-openspeed'    => $open_speed,
	'data-closespeed'   => $close_speed,
) );

if ( $enable_faq_schema && ! empty( $items ) ) {
	$faq_data = array(
		'@context' => 'https://schema.org',
		'@type' => 'FAQPage',
		'mainEntity' => array(),
	);

	foreach ( $items as $item ) {
		$faq_data['mainEntity'][] = array(
			'@type' => 'Question',
			'name' => wp_strip_all_tags( isset( $item['title'] ) ? $item['title'] : '' ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text' => wp_kses_post( isset( $item['content'] ) ? $item['content'] : '' ),
			),
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $faq_data, JSON_UNESCAPED_SLASHES ) . '</script>';
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<div class="accordion-wrapper">
		<?php if ( ! empty( $items ) ) : ?>
			<?php foreach ( $items as $index => $item ) : ?>
				<?php
				$item_id = isset( $item['id'] ) ? $item['id'] : $index;
				$is_open = isset( $item['isOpen'] ) ? $item['isOpen'] : false;
				$item_class = 'accordion-item' . ( $is_open ? ' is-open' : '' );
				$margin_bottom = $index < count( $items ) - 1 ? $item_spacing : 0;
				$title = isset( $item['title'] ) ? $item['title'] : '';
				$content = isset( $item['content'] ) ? $item['content'] : '';
				
				// Build item styles - colors only when theme colors disabled.
				$item_style = sprintf(
					'margin-bottom: %dpx; border-radius: %dpx; border-width: %dpx; border-style: solid;',
					$margin_bottom,
					$border_radius,
					$border_width
				);
				// Only add border color when not using theme colors.
				if ( ! $use_theme_colors ) {
					$item_style .= ' border-color: ' . esc_attr( $border_color ) . ';';
				}

				// Build header styles - colors only when theme colors disabled.
				$header_style = '';
				if ( ! $use_theme_colors ) {
					if ( ! empty( $title_color ) ) {
						$header_style .= 'color: ' . esc_attr( $title_color ) . ';';
					}
					if ( ! empty( $title_bg_color ) ) {
						$header_style .= ' background-color: ' . esc_attr( $title_bg_color ) . ';';
					}
				}

				// Title typography (always applied).
				$title_style = 'font-size: ' . esc_attr( $title_font_size ) . 'px; font-weight: ' . esc_attr( $title_font_weight ) . '; line-height: ' . esc_attr( $title_line_height ) . ';';

				// Content typography (always applied) + colors only when theme colors disabled.
				$content_style = 'font-size: ' . esc_attr( $content_font_size ) . 'px; font-weight: ' . esc_attr( $content_font_weight ) . '; line-height: ' . esc_attr( $content_line_height ) . ';';
				if ( ! $use_theme_colors ) {
					if ( ! empty( $content_color ) ) {
						$content_style .= 'color: ' . esc_attr( $content_color ) . ';';
					}
					if ( ! empty( $content_bg_color ) ) {
						$content_style .= ' background-color: ' . esc_attr( $content_bg_color ) . ';';
					}
				}
				
				$icon_type = isset( $item['iconType'] ) ? $item['iconType'] : 'icon';
				?>
				<div 
					class="<?php echo esc_attr( $item_class ); ?>"
					data-item-id="<?php echo esc_attr( $item_id ); ?>"
					style="<?php echo esc_attr( $item_style ); ?>"
				>
					<div class="accordion-header" style="<?php echo esc_attr( $header_style ); ?>">
						<?php if ( $icon_type === 'text' && ! empty( $item['iconText'] ) ) : ?>
							<span class="accordion-icon"><?php echo esc_html( $item['iconText'] ); ?></span>
						<?php elseif ( $icon_type === 'icon' && ! empty( $item['icon'] ) ) : ?>
							<span class="accordion-icon dashicons <?php echo esc_attr( $item['icon'] ); ?>"></span>
						<?php endif; ?>
						
						<?php
						printf(
							'<%1$s class="accordion-title" style="%2$s">%3$s</%1$s>',
							esc_attr( $title_tag ),
							esc_attr( $title_style ),
							wp_kses_post( $title )
						);
						?>
						
						<span class="accordion-arrow"></span>
					</div>
					<div class="accordion-content" style="<?php echo esc_attr( $content_style ); ?>">
						<?php echo wp_kses_post( wpautop( $content ) ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No accordion items found. Please add items in the editor.', 'wbcom-essential' ); ?></p>
		<?php endif; ?>
	</div>
</div>