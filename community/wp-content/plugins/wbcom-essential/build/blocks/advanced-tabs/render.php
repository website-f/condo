<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tabs = $attributes['tabs'] ?? array();
$layout = $attributes['layout'] ?? 'horizontal';
$enable_url_hash = $attributes['enableUrlHash'] ?? false;
$use_theme_colors = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;

// Style attributes
$title_color = $attributes['titleColor'] ?? '';
$title_active_color = $attributes['titleActiveColor'] ?? '';
$title_bg_color = $attributes['titleBgColor'] ?? '';
$title_active_bg_color = $attributes['titleActiveBgColor'] ?? '';
$title_border_color = $attributes['titleBorderColor'] ?? '';
$title_active_border_color = $attributes['titleActiveBorderColor'] ?? '';
$content_color = $attributes['contentColor'] ?? '';
$content_bg_color = $attributes['contentBgColor'] ?? '';
$content_border_color = $attributes['contentBorderColor'] ?? '';
$icon_color = $attributes['iconColor'] ?? '';
$icon_active_color = $attributes['iconActiveColor'] ?? '';
$icon_size = $attributes['iconSize'] ?? 16;
$title_alignment = $attributes['titleAlignment'] ?? 'left';

$unique_id = 'tabs-' . uniqid();

// Build wrapper classes.
$wrapper_classes = "layout-{$layout}";
if ( $use_theme_colors ) {
	$wrapper_classes .= ' use-theme-colors';
}

// Build inline styles - only when not using theme colors.
$inline_styles = '';

if ( ! $use_theme_colors && ( $title_color || $title_bg_color || $title_border_color ) ) {
	$inline_styles .= "#{$unique_id} .tab-title {";
	if ( $title_color ) {
		$inline_styles .= "color: {$title_color};";
	}
	if ( $title_bg_color ) {
		$inline_styles .= "background-color: {$title_bg_color};";
	}
	if ( $title_border_color ) {
		$inline_styles .= "border-color: {$title_border_color};";
	}
	$inline_styles .= "text-align: {$title_alignment};";
	$inline_styles .= '}';
}

if ( ! $use_theme_colors && ( $title_active_color || $title_active_bg_color || $title_active_border_color ) ) {
	$inline_styles .= "#{$unique_id} .tab-title.active, #{$unique_id} .accordion-mobile-title.active {";
	if ( $title_active_color ) {
		$inline_styles .= "color: {$title_active_color};";
	}
	if ( $title_active_bg_color ) {
		$inline_styles .= "background-color: {$title_active_bg_color};";
	}
	if ( $title_active_border_color ) {
		$inline_styles .= "border-color: {$title_active_border_color};";
	}
	$inline_styles .= '}';
}

if ( ! $use_theme_colors && ( $icon_color || $icon_size ) ) {
	$inline_styles .= "#{$unique_id} .dashicons {";
	if ( $icon_color ) {
		$inline_styles .= "color: {$icon_color};";
	}
	$inline_styles .= "font-size: {$icon_size}px;";
	$inline_styles .= '}';
}

if ( ! $use_theme_colors && $icon_active_color ) {
	$inline_styles .= "#{$unique_id} .tab-title.active .dashicons, #{$unique_id} .accordion-mobile-title.active .dashicons {";
	$inline_styles .= "color: {$icon_active_color};";
	$inline_styles .= '}';
}

if ( ! $use_theme_colors && ( $content_color || $content_bg_color || $content_border_color ) ) {
	$inline_styles .= "#{$unique_id} .tab-content {";
	if ( $content_color ) {
		$inline_styles .= "color: {$content_color};";
	}
	if ( $content_bg_color ) {
		$inline_styles .= "background-color: {$content_bg_color};";
	}
	if ( $content_border_color ) {
		$inline_styles .= "border-color: {$content_border_color};";
	}
	$inline_styles .= '}';
}

?>

<?php if ( ! empty( $inline_styles ) ) : ?>
	<style><?php echo wp_kses_post( $inline_styles ); ?></style>
<?php endif; ?>

<div
	<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>
	<?php echo get_block_wrapper_attributes( array( 'class' => $wrapper_classes, 'id' => $unique_id ) ); ?>
	data-enable-url-hash="<?php echo $enable_url_hash ? 'true' : 'false'; ?>"
>
	<div class="tabs-header">
		<?php foreach ( $tabs as $index => $tab ) : 
			$tab_id = $tab['id'] ?? 'tab-' . $index;
			?>
			<div
				class="tab-title <?php echo 0 === $index ? 'active' : ''; ?>"
				role="tab"
				id="tab-title-<?php echo esc_attr( $tab_id ); ?>"
				tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"
				aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
				aria-controls="tab-content-<?php echo esc_attr( $tab_id ); ?>"
				data-tab-index="<?php echo esc_attr( $index ); ?>"
			>
				<?php if ( ! empty( $tab['icon'] ) ) : ?>
					<span class="dashicons dashicons-<?php echo esc_attr( $tab['icon'] ); ?>"></span>
				<?php endif; ?>
				<span><?php echo esc_html( $tab['title'] ?? "Tab " . ($index + 1) ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>

	<?php foreach ( $tabs as $index => $tab ) : 
		$tab_id = $tab['id'] ?? 'tab-' . $index;
		?>
		<div
			class="accordion-mobile-title <?php echo 0 === $index ? 'active' : ''; ?>"
			role="tab"
			id="accordion-title-<?php echo esc_attr( $tab_id ); ?>"
			tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"
			aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
			aria-controls="tab-content-<?php echo esc_attr( $tab_id ); ?>"
			data-tab-index="<?php echo esc_attr( $index ); ?>"
		>
			<?php if ( ! empty( $tab['icon'] ) ) : ?>
				<span class="dashicons dashicons-<?php echo esc_attr( $tab['icon'] ); ?>"></span>
			<?php endif; ?>
			<span><?php echo esc_html( $tab['title'] ?? "Tab " . ($index + 1) ); ?></span>
		</div>

		<div
			class="tab-content-wrapper <?php echo 0 === $index ? 'active' : ''; ?>"
			role="tabpanel"
			id="tab-content-<?php echo esc_attr( $tab_id ); ?>"
			aria-labelledby="tab-title-<?php echo esc_attr( $tab_id ); ?>"
			data-tab-id="<?php echo esc_attr( $tab_id ); ?>"
		>
			<div class="tab-content">
				<div class="tab-content-inner">
					<?php if ( ! empty( $tab['imageUrl'] ) ) : ?>
						<div class="tab-image">
							<img src="<?php echo esc_url( $tab['imageUrl'] ); ?>" alt="<?php echo esc_attr( $tab['title'] ?? "Tab " . ($index + 1) ); ?>" />
						</div>
					<?php endif; ?>
					<div class="tab-text">
						<?php echo wp_kses_post( $tab['content'] ?? '' ); ?>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>