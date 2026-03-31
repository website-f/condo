<?php
/**
 * Server-side render for Portfolio Grid block.
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

// Extract attributes.
$use_theme_colors       = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;
$items                  = $attributes['items'] ?? array();
$filters                = $attributes['filters'] ?? array();
$show_filters           = $attributes['showFilters'] ?? true;
$layout                 = $attributes['layout'] ?? 'grid';
$layout_type            = $attributes['layoutType'] ?? 'grid';
$show_layout_switch     = $attributes['showLayoutSwitcher'] ?? false;
$columns                = $attributes['columns'] ?? 3;
$columns_tablet         = $attributes['columnsTablet'] ?? 2;
$columns_mobile         = $attributes['columnsMobile'] ?? 1;
$gap                    = $attributes['gap'] ?? 30;
$image_size             = $attributes['imageSize'] ?? 'large';
$image_aspect_ratio     = $attributes['imageAspectRatio'] ?? '4:3';
$title_html_tag         = $attributes['titleHtmlTag'] ?? 'h3';
$description_html_tag   = $attributes['descriptionHtmlTag'] ?? 'p';
$hover_effect           = $attributes['hoverEffect'] ?? 'zoom';
$hover_animation        = $attributes['hoverAnimation'] ?? '';
$enable_lightbox        = $attributes['enableLightbox'] ?? false;
$lightbox_icon          = $attributes['lightboxIcon'] ?? 'search';
$text_placement         = $attributes['textPlacement'] ?? 'overlay';
$overlay_vertical_align = $attributes['overlayVerticalAlign'] ?? 'flex-end';
$text_align             = $attributes['textAlign'] ?? 'left';
$item_background        = $attributes['itemBackground'] ?? '#ffffff';
$item_border_radius     = $attributes['itemBorderRadius'] ?? 8;
$item_border_width      = $attributes['itemBorderWidth'] ?? 0;
$item_border_color      = $attributes['itemBorderColor'] ?? '#e2e8f0';
$item_box_shadow        = $attributes['itemBoxShadow'] ?? array(
	'enabled'    => true,
	'horizontal' => 0,
	'vertical'   => 2,
	'blur'       => 8,
	'spread'     => 0,
	'color'      => 'rgba(0, 0, 0, 0.08)',
);
$item_hover_box_shadow  = $attributes['itemHoverBoxShadow'] ?? array(
	'enabled'    => true,
	'horizontal' => 0,
	'vertical'   => 8,
	'blur'       => 24,
	'spread'     => 0,
	'color'      => 'rgba(0, 0, 0, 0.12)',
);
$overlay_color          = $attributes['overlayColor'] ?? 'rgba(0, 0, 0, 0.7)';
$overlay_hover_color    = $attributes['overlayHoverColor'] ?? 'rgba(0, 0, 0, 0.85)';
$title_color            = $attributes['titleColor'] ?? '#ffffff';
$title_font_size        = $attributes['titleFontSize'] ?? 18;
$title_font_weight      = $attributes['titleFontWeight'] ?? '600';
$title_margin           = $attributes['titleMargin'] ?? array(
	'top'    => 0,
	'right'  => 0,
	'bottom' => 8,
	'left'   => 0,
);
$description_color      = $attributes['descriptionColor'] ?? 'rgba(255, 255, 255, 0.9)';
$description_font_size  = $attributes['descriptionFontSize'] ?? 14;
$description_line_clamp = $attributes['descriptionLineClamp'] ?? 2;
$filter_active          = $attributes['filterActiveColor'] ?? '#3182ce';
$filter_text            = $attributes['filterTextColor'] ?? '#4a5568';
$filter_active_bg       = $attributes['filterActiveBackground'] ?? '';
$filter_bg              = $attributes['filterBackground'] ?? 'rgba(0, 0, 0, 0.05)';
$filter_border_radius   = $attributes['filterBorderRadius'] ?? 4;
$filter_padding         = $attributes['filterPadding'] ?? array(
	'top'    => 8,
	'right'  => 20,
	'bottom' => 8,
	'left'   => 20,
);
$image_padding          = $attributes['imagePadding'] ?? array(
	'top'    => 0,
	'right'  => 0,
	'bottom' => 0,
	'left'   => 0,
);
$image_border_radius    = $attributes['imageBorderRadius'] ?? 0;
$content_padding        = $attributes['contentPadding'] ?? array(
	'top'    => 24,
	'right'  => 24,
	'bottom' => 24,
	'left'   => 24,
);
$content_margin         = $attributes['contentMargin'] ?? array(
	'top'    => 0,
	'right'  => 0,
	'bottom' => 0,
	'left'   => 0,
);
$content_background     = $attributes['contentBackground'] ?? '';
$content_border_radius  = $attributes['contentBorderRadius'] ?? 0;

// Generate unique ID.
$block_id = 'portfolio-grid-' . wp_unique_id();

// Calculate aspect ratio padding.
$aspect_ratios = array(
	'1:1'  => 100,
	'4:3'  => 75,
	'16:9' => 56.25,
	'3:2'  => 66.67,
	'2:3'  => 150,
	'auto' => 0,
);
$aspect_padding = isset( $aspect_ratios[ $image_aspect_ratio ] ) ? $aspect_ratios[ $image_aspect_ratio ] : 75;

// Build box shadow CSS.
$box_shadow_css = 'none';
if ( ! empty( $item_box_shadow['enabled'] ) ) {
	$box_shadow_css = sprintf(
		'%dpx %dpx %dpx %dpx %s',
		$item_box_shadow['horizontal'] ?? 0,
		$item_box_shadow['vertical'] ?? 2,
		$item_box_shadow['blur'] ?? 8,
		$item_box_shadow['spread'] ?? 0,
		$item_box_shadow['color'] ?? 'rgba(0, 0, 0, 0.08)'
	);
}

$hover_box_shadow_css = 'none';
if ( ! empty( $item_hover_box_shadow['enabled'] ) ) {
	$hover_box_shadow_css = sprintf(
		'%dpx %dpx %dpx %dpx %s',
		$item_hover_box_shadow['horizontal'] ?? 0,
		$item_hover_box_shadow['vertical'] ?? 8,
		$item_hover_box_shadow['blur'] ?? 24,
		$item_hover_box_shadow['spread'] ?? 0,
		$item_hover_box_shadow['color'] ?? 'rgba(0, 0, 0, 0.12)'
	);
}

// Build CSS custom properties - Layout variables (always applied).
$layout_vars = sprintf(
	'--portfolio-columns: %d; --portfolio-columns-tablet: %d; --portfolio-columns-mobile: %d; --portfolio-gap: %dpx; --portfolio-item-radius: %dpx; --portfolio-filter-radius: %dpx; --portfolio-aspect-ratio: %s; --portfolio-title-size: %dpx; --portfolio-title-weight: %s; --portfolio-desc-size: %dpx; --portfolio-desc-clamp: %d; --portfolio-box-shadow: %s; --portfolio-box-shadow-hover: %s; --portfolio-border-width: %dpx; --portfolio-image-radius: %dpx; --portfolio-overlay-valign: %s; --portfolio-text-align: %s; --portfolio-content-padding: %dpx %dpx %dpx %dpx; --portfolio-content-margin: %dpx %dpx %dpx %dpx; --portfolio-content-radius: %dpx; --portfolio-filter-padding: %dpx %dpx %dpx %dpx; --portfolio-title-margin: %dpx %dpx %dpx %dpx;',
	$columns,
	$columns_tablet,
	$columns_mobile,
	$gap,
	$item_border_radius,
	$filter_border_radius,
	$aspect_padding > 0 ? $aspect_padding . '%' : 'auto',
	$title_font_size,
	esc_attr( $title_font_weight ),
	$description_font_size,
	$description_line_clamp,
	esc_attr( $box_shadow_css ),
	esc_attr( $hover_box_shadow_css ),
	$item_border_width,
	$image_border_radius,
	esc_attr( $overlay_vertical_align ),
	esc_attr( $text_align ),
	$content_padding['top'] ?? 24,
	$content_padding['right'] ?? 24,
	$content_padding['bottom'] ?? 24,
	$content_padding['left'] ?? 24,
	$content_margin['top'] ?? 0,
	$content_margin['right'] ?? 0,
	$content_margin['bottom'] ?? 0,
	$content_margin['left'] ?? 0,
	$content_border_radius,
	$filter_padding['top'] ?? 8,
	$filter_padding['right'] ?? 20,
	$filter_padding['bottom'] ?? 8,
	$filter_padding['left'] ?? 20,
	$title_margin['top'] ?? 0,
	$title_margin['right'] ?? 0,
	$title_margin['bottom'] ?? 8,
	$title_margin['left'] ?? 0
);

// Color variables (only when NOT using theme colors).
$color_vars = '';
if ( ! $use_theme_colors ) {
	$color_vars = sprintf(
		' --portfolio-item-bg: %s; --portfolio-overlay: %s; --portfolio-overlay-hover: %s; --portfolio-title-color: %s; --portfolio-desc-color: %s; --portfolio-filter-active: %s; --portfolio-filter-text: %s; --portfolio-filter-bg: %s; --portfolio-filter-active-bg: %s; --portfolio-border-color: %s; --portfolio-content-bg: %s;',
		esc_attr( $item_background ),
		esc_attr( $overlay_color ),
		esc_attr( $overlay_hover_color ),
		esc_attr( $title_color ),
		esc_attr( $description_color ),
		esc_attr( $filter_active ),
		esc_attr( $filter_text ),
		esc_attr( $filter_bg ),
		esc_attr( $filter_active_bg ),
		esc_attr( $item_border_color ),
		esc_attr( $content_background )
	);
}

// Combine style vars.
$style_vars = $layout_vars . $color_vars;

// Build wrapper classes.
$wrapper_classes = array(
	'wbcom-essential-portfolio-grid',
	'hover-effect-' . sanitize_html_class( $hover_effect ),
	'text-placement-' . sanitize_html_class( $text_placement ),
);

if ( $use_theme_colors ) {
	$wrapper_classes[] = 'use-theme-colors';
}

if ( ! empty( $hover_animation ) ) {
	$wrapper_classes[] = 'hover-animation-' . sanitize_html_class( $hover_animation );
}

if ( $enable_lightbox ) {
	$wrapper_classes[] = 'has-lightbox';
}

if ( 'masonry' === $layout_type ) {
	$wrapper_classes[] = 'layout-type-masonry';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'id'               => $block_id,
		'class'            => implode( ' ', $wrapper_classes ),
		'style'            => $style_vars,
		'data-layout'      => esc_attr( $layout ),
		'data-layout-type' => esc_attr( $layout_type ),
		'data-lightbox'    => $enable_lightbox ? 'true' : 'false',
	)
);

// Find default filter.
$default_filter = 'all';
foreach ( $filters as $filter ) {
	if ( ! empty( $filter['isDefault'] ) ) {
		$default_filter = $filter['id'];
		break;
	}
}

// Lightbox icons.
$lightbox_icons = array(
	'search' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
	'plus'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
	'expand' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>',
	'eye'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
);

$lightbox_icon_svg = isset( $lightbox_icons[ $lightbox_icon ] ) ? $lightbox_icons[ $lightbox_icon ] : $lightbox_icons['search'];

// Allowed HTML tags for title and description.
$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span' );
$title_tag    = in_array( $title_html_tag, $allowed_tags, true ) ? $title_html_tag : 'h3';
$desc_tag     = in_array( $description_html_tag, $allowed_tags, true ) ? $description_html_tag : 'p';
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<?php if ( $show_filters || $show_layout_switch ) : ?>
		<div class="wbcom-portfolio-controls">
			<?php if ( $show_filters && ! empty( $filters ) ) : ?>
				<div class="wbcom-portfolio-filters" role="tablist">
					<?php foreach ( $filters as $filter ) : ?>
						<button
							type="button"
							class="wbcom-portfolio-filter<?php echo $filter['id'] === $default_filter ? ' is-active' : ''; ?>"
							data-filter="<?php echo esc_attr( $filter['id'] ); ?>"
							role="tab"
							aria-selected="<?php echo $filter['id'] === $default_filter ? 'true' : 'false'; ?>"
						>
							<?php echo esc_html( $filter['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_layout_switch ) : ?>
				<div class="wbcom-portfolio-layout-switch">
					<button
						type="button"
						class="wbcom-layout-btn<?php echo 'grid' === $layout ? ' is-active' : ''; ?>"
						data-layout="grid"
						aria-label="<?php esc_attr_e( 'Grid view', 'wbcom-essential' ); ?>"
					>
						<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
							<rect x="2" y="2" width="7" height="7" rx="1"/>
							<rect x="11" y="2" width="7" height="7" rx="1"/>
							<rect x="2" y="11" width="7" height="7" rx="1"/>
							<rect x="11" y="11" width="7" height="7" rx="1"/>
						</svg>
					</button>
					<button
						type="button"
						class="wbcom-layout-btn<?php echo 'list' === $layout ? ' is-active' : ''; ?>"
						data-layout="list"
						aria-label="<?php esc_attr_e( 'List view', 'wbcom-essential' ); ?>"
					>
						<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
							<rect x="2" y="3" width="16" height="3" rx="1"/>
							<rect x="2" y="8.5" width="16" height="3" rx="1"/>
							<rect x="2" y="14" width="16" height="3" rx="1"/>
						</svg>
					</button>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="wbcom-portfolio-items layout-<?php echo esc_attr( $layout ); ?><?php echo 'masonry' === $layout_type ? ' masonry-layout' : ''; ?>">
		<?php if ( ! empty( $items ) ) : ?>
			<?php foreach ( $items as $item ) : ?>
				<?php
				$item_filters     = ! empty( $item['filters'] ) ? explode( ' ', trim( $item['filters'] ) ) : array();
				$filter_classes   = array_map(
					function ( $f ) {
						return 'filter-' . sanitize_html_class( $f );
					},
					$item_filters
				);
				$filter_class_str = implode( ' ', $filter_classes );
				$is_visible       = 'all' === $default_filter || in_array( $default_filter, $item_filters, true );

				// Masonry grid span.
				$grid_style = '';
				if ( 'masonry' === $layout_type ) {
					$col_span    = isset( $item['columnWidth'] ) ? absint( $item['columnWidth'] ) : 1;
					$row_span    = isset( $item['rowHeight'] ) ? absint( $item['rowHeight'] ) : 1;
					$grid_style  = sprintf( 'grid-column: span %d; grid-row: span %d;', $col_span, $row_span );
				}

				// Get image URL.
				$image_url = '';
				if ( ! empty( $item['imageId'] ) ) {
					$image_data = wp_get_attachment_image_src( $item['imageId'], $image_size );
					if ( $image_data ) {
						$image_url = $image_data[0];
					}
				}
				if ( empty( $image_url ) && ! empty( $item['image'] ) ) {
					$image_url = $item['image'];
				}

				// Get full image URL for lightbox.
				$full_image_url = '';
				if ( $enable_lightbox && ! empty( $item['imageId'] ) ) {
					$full_image_data = wp_get_attachment_image_src( $item['imageId'], 'full' );
					if ( $full_image_data ) {
						$full_image_url = $full_image_data[0];
					}
				}
				if ( empty( $full_image_url ) && ! empty( $image_url ) ) {
					$full_image_url = $image_url;
				}
				// translators: %s is the portfolio item title.
				$lightbox_aria_label = esc_attr( sprintf( __( 'Open %s in lightbox', 'wbcom-essential' ), $item['title'] ?? '' ) );
				// translators: %s is the portfolio item title.
				$view_aria_label = esc_attr( sprintf( __( 'View %s', 'wbcom-essential' ), $item['title'] ?? '' ) );
				?>
				<div
					class="wbcom-portfolio-item <?php echo esc_attr( $filter_class_str ); ?><?php echo ! $is_visible ? ' is-hidden' : ''; ?>"
					data-filters="<?php echo esc_attr( $item['filters'] ?? '' ); ?>"
					<?php if ( $grid_style ) : ?>
						style="<?php echo esc_attr( $grid_style ); ?>"
					<?php endif; ?>
				>
					<div class="wbcom-portfolio-item-inner">
						<?php if ( ! empty( $image_url ) ) : ?>
							<div class="wbcom-portfolio-image">
								<img
									src="<?php echo esc_url( $image_url ); ?>"
									alt="<?php echo esc_attr( $item['title'] ?? '' ); ?>"
									loading="lazy"
								/>
								<?php if ( $enable_lightbox ) : ?>
									<button
										type="button"
										class="wbcom-portfolio-lightbox-btn"
										data-src="<?php echo esc_url( $full_image_url ); ?>"
										data-title="<?php echo esc_attr( $item['title'] ?? '' ); ?>"
										aria-label="<?php echo $lightbox_aria_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped above ?>"
									>
										<?php echo $lightbox_icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</button>
								<?php endif; ?>
							</div>
						<?php else : ?>
							<div class="wbcom-portfolio-image wbcom-portfolio-placeholder">
								<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
									<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
									<circle cx="8.5" cy="8.5" r="1.5"/>
									<polyline points="21 15 16 10 5 21"/>
								</svg>
							</div>
						<?php endif; ?>

						<?php if ( 'overlay' === $text_placement ) : ?>
							<div class="wbcom-portfolio-overlay">
								<div class="wbcom-portfolio-content">
									<?php if ( ! empty( $item['title'] ) ) : ?>
										<<?php echo esc_html( $title_tag ); ?> class="wbcom-portfolio-title">
											<?php if ( ! empty( $item['link'] ) ) : ?>
												<a
													href="<?php echo esc_url( $item['link'] ); ?>"
													<?php echo ( '_blank' === ( $item['linkTarget'] ?? '_self' ) ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
												>
													<?php echo esc_html( $item['title'] ); ?>
												</a>
											<?php else : ?>
												<?php echo esc_html( $item['title'] ); ?>
											<?php endif; ?>
										</<?php echo esc_html( $title_tag ); ?>>
									<?php endif; ?>

									<?php if ( ! empty( $item['description'] ) ) : ?>
										<<?php echo esc_html( $desc_tag ); ?> class="wbcom-portfolio-description">
											<?php echo esc_html( $item['description'] ); ?>
										</<?php echo esc_html( $desc_tag ); ?>>
									<?php endif; ?>

									<?php if ( ! empty( $item['link'] ) && ! $enable_lightbox ) : ?>
										<a
											href="<?php echo esc_url( $item['link'] ); ?>"
											class="wbcom-portfolio-link"
											aria-label="<?php echo $view_aria_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped above ?>"
											<?php echo ( '_blank' === ( $item['linkTarget'] ?? '_self' ) ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
										>
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
												<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
												<polyline points="15 3 21 3 21 9"/>
												<line x1="10" y1="14" x2="21" y2="3"/>
											</svg>
										</a>
									<?php endif; ?>
								</div>
							</div>
						<?php else : ?>
							<div class="wbcom-portfolio-content-below">
								<?php if ( ! empty( $item['title'] ) ) : ?>
									<<?php echo esc_html( $title_tag ); ?> class="wbcom-portfolio-title">
										<?php if ( ! empty( $item['link'] ) ) : ?>
											<a
												href="<?php echo esc_url( $item['link'] ); ?>"
												<?php echo ( '_blank' === ( $item['linkTarget'] ?? '_self' ) ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
											>
												<?php echo esc_html( $item['title'] ); ?>
											</a>
										<?php else : ?>
											<?php echo esc_html( $item['title'] ); ?>
										<?php endif; ?>
									</<?php echo esc_html( $title_tag ); ?>>
								<?php endif; ?>

								<?php if ( ! empty( $item['description'] ) ) : ?>
									<<?php echo esc_html( $desc_tag ); ?> class="wbcom-portfolio-description">
										<?php echo esc_html( $item['description'] ); ?>
									</<?php echo esc_html( $desc_tag ); ?>>
								<?php endif; ?>

								<?php if ( ! empty( $item['link'] ) ) : ?>
									<a
											href="<?php echo esc_url( $item['link'] ); ?>"
											class="wbcom-portfolio-link"
										<?php echo ( '_blank' === ( $item['linkTarget'] ?? '_self' ) ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
									>
										<?php esc_html_e( 'View Project', 'wbcom-essential' ); ?>
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<line x1="5" y1="12" x2="19" y2="12"/>
											<polyline points="12 5 19 12 12 19"/>
										</svg>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="wbcom-portfolio-empty">
				<p><?php esc_html_e( 'No portfolio items added yet. Add items in the block settings.', 'wbcom-essential' ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $enable_lightbox ) : ?>
		<div class="wbcom-portfolio-lightbox" aria-hidden="true">
			<div class="wbcom-portfolio-lightbox-overlay"></div>
			<div class="wbcom-portfolio-lightbox-content">
				<button type="button" class="wbcom-portfolio-lightbox-close" aria-label="<?php esc_attr_e( 'Close lightbox', 'wbcom-essential' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="18" y1="6" x2="6" y2="18"/>
						<line x1="6" y1="6" x2="18" y2="18"/>
					</svg>
				</button>
				<button type="button" class="wbcom-portfolio-lightbox-prev" aria-label="<?php esc_attr_e( 'Previous image', 'wbcom-essential' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<polyline points="15 18 9 12 15 6"/>
					</svg>
				</button>
				<button type="button" class="wbcom-portfolio-lightbox-next" aria-label="<?php esc_attr_e( 'Next image', 'wbcom-essential' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<polyline points="9 18 15 12 9 6"/>
					</svg>
				</button>
				<figure class="wbcom-portfolio-lightbox-figure">
					<img src="" alt="" class="wbcom-portfolio-lightbox-image" />
					<figcaption class="wbcom-portfolio-lightbox-caption"></figcaption>
				</figure>
			</div>
		</div>
	<?php endif; ?>
</div>
