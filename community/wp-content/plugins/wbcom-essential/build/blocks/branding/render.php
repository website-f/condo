<?php
/**
 * Server-side render for Branding block.
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
$branding_type           = $attributes['brandingType'] ?? 'title';
$alignment               = $attributes['alignment'] ?? '';
$title_color             = $attributes['titleColor'] ?? '#333333';
$title_hover_color       = $attributes['titleHoverColor'] ?? '#333333';
$description_color       = $attributes['descriptionColor'] ?? '#333333';
$title_padding           = $attributes['titlePadding'] ?? array( 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0' );
$description_padding     = $attributes['descriptionPadding'] ?? array( 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0' );
$logo_padding            = $attributes['logoPadding'] ?? array( 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0' );
$logo_width              = $attributes['logoWidth'] ?? 'auto';
$logo_height             = $attributes['logoHeight'] ?? 'auto';
$title_typography        = $attributes['titleTypography'] ?? array(
	'fontFamily'     => '',
	'fontSize'       => '',
	'fontWeight'     => '',
	'lineHeight'     => '',
	'letterSpacing'  => '',
	'textTransform'  => '',
	'textDecoration' => '',
);
$description_typography  = $attributes['descriptionTypography'] ?? array(
	'fontFamily'     => '',
	'fontSize'       => '',
	'fontWeight'     => '',
	'lineHeight'     => '',
	'letterSpacing'  => '',
	'textTransform'  => '',
	'textDecoration' => '',
);
$title_link_url          = $attributes['titleLinkUrl'] ?? '';
$title_link_target       = $attributes['titleLinkTarget'] ?? '_self';
$border                  = $attributes['border'] ?? array( 'width' => '0', 'style' => 'none', 'color' => '#000000' );
$border_radius           = $attributes['borderRadius'] ?? array( 'top' => '0px', 'right' => '0px', 'bottom' => '0px', 'left' => '0px' );
$use_theme_colors        = isset( $attributes['useThemeColors'] ) ? $attributes['useThemeColors'] : false;

// Build inline styles.
$inline_styles = '';
if ( '0' !== $border['width'] && 'none' !== $border['style'] && $border['color'] ) {
	$inline_styles .= 'border: ' . $border['width'] . ' ' . $border['style'] . ' ' . $border['color'] . ';';
}
if ( $border_radius['top'] || $border_radius['right'] || $border_radius['bottom'] || $border_radius['left'] ) {
	$inline_styles .= 'border-radius: ' . $border_radius['top'] . ' ' . $border_radius['right'] . ' ' . $border_radius['bottom'] . ' ' . $border_radius['left'] . ';';
}

// Build classes.
$classes = array( 'wbcom-essential-branding' );
if ( $alignment ) {
	$classes[] = 'align' . $alignment;
}
if ( $use_theme_colors ) {
	$classes[] = 'use-theme-colors';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $classes ),
		'style' => $inline_styles,
	)
);

// Build title styles.
$title_styles = '';
if ( ! $use_theme_colors ) {
	$title_styles .= 'color: ' . esc_attr( $title_color ) . ';';
}
$title_styles .= 'padding: ' . esc_attr( $title_padding['top'] ) . ' ' . esc_attr( $title_padding['right'] ) . ' ' . esc_attr( $title_padding['bottom'] ) . ' ' . esc_attr( $title_padding['left'] ) . ';';

// Apply typography styles.
if ( ! empty( $title_typography['fontFamily'] ) ) {
	$title_styles .= 'font-family: ' . esc_attr( $title_typography['fontFamily'] ) . ';';
}
if ( ! empty( $title_typography['fontSize'] ) ) {
	$title_styles .= 'font-size: ' . esc_attr( $title_typography['fontSize'] ) . ';';
}
if ( ! empty( $title_typography['fontWeight'] ) ) {
	$title_styles .= 'font-weight: ' . esc_attr( $title_typography['fontWeight'] ) . ';';
}
if ( ! empty( $title_typography['lineHeight'] ) ) {
	$title_styles .= 'line-height: ' . esc_attr( $title_typography['lineHeight'] ) . ';';
}
if ( ! empty( $title_typography['letterSpacing'] ) ) {
	$title_styles .= 'letter-spacing: ' . esc_attr( $title_typography['letterSpacing'] ) . ';';
}
if ( ! empty( $title_typography['textTransform'] ) ) {
	$title_styles .= 'text-transform: ' . esc_attr( $title_typography['textTransform'] ) . ';';
}
if ( ! empty( $title_typography['textDecoration'] ) ) {
	$title_styles .= 'text-decoration: ' . esc_attr( $title_typography['textDecoration'] ) . ';';
}

// Get site info.
$site_title       = get_bloginfo( 'name' );
$site_description = get_bloginfo( 'description', 'display' );

if ( empty( $site_title ) ) {
	$site_title = 'Site Title';
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="header-title">
		<?php if ( 'title' === $branding_type ) : ?>
			<?php
			$link_url = ! empty( $title_link_url ) ? $title_link_url : home_url( '/' );
			$link_target_attr = '_blank' === $title_link_target ? ' target="_blank" rel="noopener noreferrer"' : '';
			?>
			<span class="site-title">
				<a href="<?php echo esc_url( $link_url ); ?>"
				   title="<?php echo esc_attr( $site_title ); ?>"
				   style="<?php echo esc_attr( $title_styles ); ?>"
				   <?php if ( ! $use_theme_colors ) : ?>
				   data-hover-color="<?php echo esc_attr( $title_hover_color ); ?>"
				   data-normal-color="<?php echo esc_attr( $title_color ); ?>"
				   <?php endif; ?><?php echo $link_target_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo esc_html( $site_title ); ?>
				</a>
			</span>

			<?php
			$desc_styles = '';
			if ( ! $use_theme_colors ) {
				$desc_styles .= 'color: ' . esc_attr( $description_color ) . ';';
			}
			$desc_styles .= 'padding: ' . esc_attr( $description_padding['top'] ) . ' ' . esc_attr( $description_padding['right'] ) . ' ' . esc_attr( $description_padding['bottom'] ) . ' ' . esc_attr( $description_padding['left'] ) . ';';

			// Apply description typography styles.
			if ( ! empty( $description_typography['fontFamily'] ) ) {
				$desc_styles .= 'font-family: ' . esc_attr( $description_typography['fontFamily'] ) . ';';
			}
			if ( ! empty( $description_typography['fontSize'] ) ) {
				$desc_styles .= 'font-size: ' . esc_attr( $description_typography['fontSize'] ) . ';';
			}
			if ( ! empty( $description_typography['fontWeight'] ) ) {
				$desc_styles .= 'font-weight: ' . esc_attr( $description_typography['fontWeight'] ) . ';';
			}
			if ( ! empty( $description_typography['lineHeight'] ) ) {
				$desc_styles .= 'line-height: ' . esc_attr( $description_typography['lineHeight'] ) . ';';
			}
			if ( ! empty( $description_typography['letterSpacing'] ) ) {
				$desc_styles .= 'letter-spacing: ' . esc_attr( $description_typography['letterSpacing'] ) . ';';
			}
			if ( ! empty( $description_typography['textTransform'] ) ) {
				$desc_styles .= 'text-transform: ' . esc_attr( $description_typography['textTransform'] ) . ';';
			}
			if ( ! empty( $description_typography['textDecoration'] ) ) {
				$desc_styles .= 'text-decoration: ' . esc_attr( $description_typography['textDecoration'] ) . ';';
			}
			?>
			<?php if ( $site_description ) : ?>
				<p class="site-description" style="<?php echo esc_attr( $desc_styles ); ?>">
					<?php echo wp_kses_post( $site_description ); ?>
				</p>
			<?php endif; ?>

		<?php elseif ( 'logo' === $branding_type ) : ?>
			<?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) : ?>
				<?php
				$logo_styles = 'padding: ' . esc_attr( $logo_padding['top'] ) . ' ' . esc_attr( $logo_padding['right'] ) . ' ' . esc_attr( $logo_padding['bottom'] ) . ' ' . esc_attr( $logo_padding['left'] ) . ';';
				if ( 'auto' !== $logo_width ) {
					$logo_styles .= 'width: ' . esc_attr( $logo_width ) . ';';
				}
				if ( 'auto' !== $logo_height ) {
					$logo_styles .= 'height: ' . esc_attr( $logo_height ) . ';';
				}
				$custom_logo_id = get_theme_mod( 'custom_logo' );
				$logo_url       = wp_get_attachment_image_url( $custom_logo_id, 'full' );
				?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $site_title ); ?>" class="custom-logo" style="<?php echo esc_attr( $logo_styles ); ?>" />
				</a>
			<?php else : ?>
				<span class="site-title">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
					   title="<?php echo esc_attr( $site_title ); ?>"
					   style="<?php echo esc_attr( $title_styles ); ?>">
						<?php echo esc_html( $site_title ); ?>
					</a>
				</span>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
