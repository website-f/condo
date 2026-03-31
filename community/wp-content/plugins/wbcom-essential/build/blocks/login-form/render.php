<?php
/**
 * Server-side render for Login Form block.
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
$use_theme_colors       = $attributes['useThemeColors'] ?? false;
$show_logo              = $attributes['showLogo'] ?? true;
$logo_url               = $attributes['logoUrl'] ?? '';
$logo_width             = $attributes['logoWidth'] ?? 150;
$logo_align             = $attributes['logoAlign'] ?? 'center';
$logo_border_radius     = $attributes['logoBorderRadius'] ?? 0;
$logo_margin_bottom     = $attributes['logoMarginBottom'] ?? 24;
$show_title             = $attributes['showTitle'] ?? true;
$form_title             = $attributes['title'] ?? 'Welcome Back';
$title_tag              = $attributes['titleTag'] ?? 'h2';
$title_align            = $attributes['titleAlign'] ?? 'center';
$title_margin_bottom    = $attributes['titleMarginBottom'] ?? 8;
$show_subtitle          = $attributes['showSubtitle'] ?? true;
$subtitle               = $attributes['subtitle'] ?? 'Please login to your account';
$subtitle_tag           = $attributes['subtitleTag'] ?? 'p';
$subtitle_align         = $attributes['subtitleAlign'] ?? 'center';
$subtitle_margin_bottom = $attributes['subtitleMarginBottom'] ?? 24;
$username_label         = $attributes['usernameLabel'] ?? 'Username or Email Address';
$username_placeholder   = $attributes['usernamePlaceholder'] ?? 'Enter your username or email';
$password_label         = $attributes['passwordLabel'] ?? 'Password';
$password_placeholder   = $attributes['passwordPlaceholder'] ?? 'Enter your password';
$show_labels            = $attributes['showLabels'] ?? true;
$label_align            = $attributes['labelAlign'] ?? 'left';
$show_remember_me       = $attributes['showRememberMe'] ?? true;
$remember_me_label      = $attributes['rememberMeLabel'] ?? 'Remember Me';
$button_text            = $attributes['buttonText'] ?? 'Log In';
$button_width           = $attributes['buttonWidth'] ?? 100;
$button_align           = $attributes['buttonAlign'] ?? 'center';
$show_lost_password     = $attributes['showLostPassword'] ?? true;
$lost_password_text     = $attributes['lostPasswordText'] ?? 'Lost Password';
$show_register          = $attributes['showRegister'] ?? false;
$register_text          = $attributes['registerText'] ?? 'Register';
$link_separator         = $attributes['linkSeparator'] ?? '|';
$links_align            = $attributes['linksAlign'] ?? 'flex-end';
$links_margin_top       = $attributes['linksMarginTop'] ?? 0;
$redirect_enabled       = $attributes['redirectEnabled'] ?? false;
$redirect_url           = $attributes['redirectUrl'] ?? '';
$show_logged_in_message = $attributes['showLoggedInMessage'] ?? true;
$logged_in_message      = $attributes['loggedInMessage'] ?? 'You are logged in as ';
$logged_in_msg_align    = $attributes['loggedInMsgAlign'] ?? 'left';
$test_mode              = $attributes['testMode'] ?? false;

// Styling attributes - only get values that are explicitly set (not defaults).
// Colors use 'inherit' as default to allow CSS/theme to control them.
$form_padding         = $attributes['formPadding'] ?? 30;
$form_border_radius   = $attributes['formBorderRadius'] ?? 8;
$form_width           = $attributes['formWidth'] ?? 100;
$form_width_unit      = $attributes['formWidthUnit'] ?? '%';
$form_align           = $attributes['formAlign'] ?? 'center';
$form_border_width    = $attributes['formBorderWidth'] ?? 0;
$form_box_shadow      = $attributes['formBoxShadow'] ?? true;
$rows_gap             = $attributes['rowsGap'] ?? 20;
$input_border_radius  = $attributes['inputBorderRadius'] ?? 6;
$input_padding_v      = $attributes['inputPaddingV'] ?? 12;
$input_padding_h      = $attributes['inputPaddingH'] ?? 16;
$input_width          = $attributes['inputWidth'] ?? 100;
$input_align          = $attributes['inputAlign'] ?? 'left';
$button_border_radius = $attributes['buttonBorderRadius'] ?? 6;
$button_border_width  = $attributes['buttonBorderWidth'] ?? 0;
$button_padding_v     = $attributes['buttonPaddingV'] ?? 14;
$button_padding_h     = $attributes['buttonPaddingH'] ?? 24;

// Get color attributes - only use if explicitly set (not empty defaults)
$form_bg_color              = $attributes['formBgColor'] ?? '';
$title_color                = $attributes['titleColor'] ?? '';
$subtitle_color             = $attributes['subtitleColor'] ?? '';
$label_color                = $attributes['labelColor'] ?? '';
$input_bg_color             = $attributes['inputBgColor'] ?? '';
$input_border_color         = $attributes['inputBorderColor'] ?? '';
$input_text_color           = $attributes['inputTextColor'] ?? '';
$input_placeholder_color    = $attributes['inputPlaceholderColor'] ?? '';
$input_focus_border_color   = $attributes['inputFocusBorderColor'] ?? '';
$input_focus_bg_color       = $attributes['inputFocusBgColor'] ?? '';
$button_bg_color            = $attributes['buttonBgColor'] ?? '';
$button_text_color          = $attributes['buttonTextColor'] ?? '';
$button_hover_bg_color      = $attributes['buttonHoverBgColor'] ?? '';
$button_hover_text_color    = $attributes['buttonHoverTextColor'] ?? '';
$button_border_color        = $attributes['buttonBorderColor'] ?? '';
$button_hover_border_color  = $attributes['buttonHoverBorderColor'] ?? '';
$link_color                 = $attributes['linkColor'] ?? '';
$link_hover_color           = $attributes['linkHoverColor'] ?? '';
$checkbox_color             = $attributes['checkboxColor'] ?? '';
$logged_in_msg_color        = $attributes['loggedInMsgColor'] ?? '';
$form_border_color          = $attributes['formBorderColor'] ?? '';

// Build inline styles - include both layout/spacing AND colors.
// Colors are included here to ensure they show on frontend when explicitly set.
$inline_styles = array(
	'--form-padding'           => $form_padding . 'px',
	'--form-border-radius'     => $form_border_radius . 'px',
	'--form-width'             => $form_width . $form_width_unit,
	'--form-align'             => $form_align,
	'--form-border-width'      => $form_border_width . 'px',
	'--form-box-shadow'        => $form_box_shadow ? '0 4px 20px rgba(0, 0, 0, 0.08)' : 'none',
	'--rows-gap'               => $rows_gap . 'px',
	'--logo-width'             => $logo_width . 'px',
	'--logo-align'             => $logo_align,
	'--logo-border-radius'     => $logo_border_radius . 'px',
	'--logo-margin-bottom'     => $logo_margin_bottom . 'px',
	'--title-align'            => $title_align,
	'--title-margin-bottom'    => $title_margin_bottom . 'px',
	'--subtitle-align'         => $subtitle_align,
	'--subtitle-margin-bottom' => $subtitle_margin_bottom . 'px',
	'--label-align'            => $label_align,
	'--input-border-radius'    => $input_border_radius . 'px',
	'--input-padding-v'        => $input_padding_v . 'px',
	'--input-padding-h'        => $input_padding_h . 'px',
	'--input-width'            => $input_width . '%',
	'--input-align'            => $input_align,
	'--button-border-radius'   => $button_border_radius . 'px',
	'--button-border-width'    => $button_border_width . 'px',
	'--button-padding-v'       => $button_padding_v . 'px',
	'--button-padding-h'       => $button_padding_h . 'px',
	'--button-width'           => $button_width . '%',
	'--button-align'           => $button_align,
	'--links-align'            => $links_align,
	'--links-margin-top'       => $links_margin_top . 'px',
	'--logged-in-msg-align'    => $logged_in_msg_align,
);

// Add color variables only if NOT using theme colors and they're explicitly set (not empty).
if ( ! $use_theme_colors ) {
	if ( ! empty( $form_bg_color ) ) {
		$inline_styles['--form-bg-color'] = $form_bg_color;
	}
	if ( ! empty( $form_border_color ) ) {
		$inline_styles['--form-border-color'] = $form_border_color;
	}
	if ( ! empty( $title_color ) ) {
		$inline_styles['--title-color'] = $title_color;
	}
	if ( ! empty( $subtitle_color ) ) {
		$inline_styles['--subtitle-color'] = $subtitle_color;
	}
	if ( ! empty( $label_color ) ) {
		$inline_styles['--label-color'] = $label_color;
	}
	if ( ! empty( $input_bg_color ) ) {
		$inline_styles['--input-bg-color'] = $input_bg_color;
	}
	if ( ! empty( $input_border_color ) ) {
		$inline_styles['--input-border-color'] = $input_border_color;
	}
	if ( ! empty( $input_text_color ) ) {
		$inline_styles['--input-text-color'] = $input_text_color;
	}
	if ( ! empty( $input_placeholder_color ) ) {
		$inline_styles['--input-placeholder-color'] = $input_placeholder_color;
	}
	if ( ! empty( $input_focus_border_color ) ) {
		$inline_styles['--input-focus-border-color'] = $input_focus_border_color;
	}
	if ( ! empty( $input_focus_bg_color ) ) {
		$inline_styles['--input-focus-bg-color'] = $input_focus_bg_color;
	}
	if ( ! empty( $button_bg_color ) ) {
		$inline_styles['--button-bg-color'] = $button_bg_color;
	}
	if ( ! empty( $button_text_color ) ) {
		$inline_styles['--button-text-color'] = $button_text_color;
	}
	if ( ! empty( $button_hover_bg_color ) ) {
		$inline_styles['--button-hover-bg-color'] = $button_hover_bg_color;
	}
	if ( ! empty( $button_hover_text_color ) ) {
		$inline_styles['--button-hover-text-color'] = $button_hover_text_color;
	}
	if ( ! empty( $button_border_color ) ) {
		$inline_styles['--button-border-color'] = $button_border_color;
	}
	if ( ! empty( $button_hover_border_color ) ) {
		$inline_styles['--button-hover-border-color'] = $button_hover_border_color;
	}
	if ( ! empty( $link_color ) ) {
		$inline_styles['--link-color'] = $link_color;
	}
	if ( ! empty( $link_hover_color ) ) {
		$inline_styles['--link-hover-color'] = $link_hover_color;
	}
	if ( ! empty( $checkbox_color ) ) {
		$inline_styles['--checkbox-color'] = $checkbox_color;
	}
	if ( ! empty( $logged_in_msg_color ) ) {
		$inline_styles['--logged-in-msg-color'] = $logged_in_msg_color;
	}
}

$style_string = '';
foreach ( $inline_styles as $prop => $value ) {
	$style_string .= esc_attr( $prop ) . ': ' . esc_attr( $value ) . '; ';
}

// Wrapper attributes.
$wrapper_classes    = 'wbcom-essential-login-form-wrapper';
$wrapper_classes   .= $use_theme_colors ? ' use-theme-colors' : '';
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $wrapper_classes,
		'style' => $style_string,
	)
);

// Check if user is logged in.
$is_logged_in = is_user_logged_in();

// Determine if we should show the form.
$show_form = ! $is_logged_in || $test_mode;

// Check if PMPro is active.
$has_pmpro = function_exists( 'pmpro_login_head' );

// Generate unique form ID.
$form_id = 'wbcom-login-form-' . wp_unique_id();

// Get URLs.
$lost_password_url = wp_lostpassword_url();
$register_url      = wp_registration_url();

// Apply filters for custom URLs.
$lost_password_url = apply_filters( 'wbcom_essential_login_lost_password_url', $lost_password_url );
$register_url      = apply_filters( 'wbcom_essential_login_register_url', $register_url );
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>
	<?php if ( $is_logged_in && ! $test_mode ) : ?>
		<?php if ( $show_logged_in_message ) : ?>
			<?php $logged_in_user = wp_get_current_user(); ?>
			<div class="wbcom-essential-login-form wbcom-essential-login-form--logged-in">
				<p class="wbcom-essential-login-form__logged-in-message">
					<?php echo esc_html( $logged_in_message ); ?>
					<strong><?php echo esc_html( $logged_in_user->display_name ); ?></strong>
				</p>
				<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="wbcom-essential-login-form__logout-link">
					<?php esc_html_e( 'Log Out', 'wbcom-essential' ); ?>
				</a>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="wbcom-essential-login-form" id="<?php echo esc_attr( $form_id ); ?>">
			<?php if ( $show_logo && $logo_url ) : ?>
				<div class="wbcom-essential-login-form__logo">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Logo', 'wbcom-essential' ); ?>" />
				</div>
			<?php endif; ?>

			<?php if ( $show_title && $form_title ) : ?>
				<<?php echo esc_attr( $title_tag ); ?> class="wbcom-essential-login-form__title">
					<?php echo esc_html( $form_title ); ?>
				</<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>

			<?php if ( $show_subtitle && $subtitle ) : ?>
				<<?php echo esc_attr( $subtitle_tag ); ?> class="wbcom-essential-login-form__subtitle">
					<?php echo esc_html( $subtitle ); ?>
				</<?php echo esc_attr( $subtitle_tag ); ?>>
			<?php endif; ?>

			<?php if ( $has_pmpro ) : ?>
				<?php
				// Use PMPro login form.
				// Validate redirect URL to prevent open redirect attacks.
				$validated_pmpro_redirect = wp_validate_redirect( $redirect_url, home_url() );
				echo do_shortcode( '[pmpro_login redirect="' . esc_attr( $validated_pmpro_redirect ) . '"]' );
				?>
			<?php else : ?>
				<form class="wbcom-essential-login-form__form" method="post" data-ajax="true">
					<div class="wbcom-essential-login-form__message" aria-live="polite" hidden></div>

					<div class="wbcom-essential-login-form__field">
						<?php if ( $show_labels ) : ?>
							<label for="<?php echo esc_attr( $form_id ); ?>-username" class="wbcom-essential-login-form__label">
								<?php echo esc_html( $username_label ); ?>
							</label>
						<?php endif; ?>
						<input
							type="text"
							id="<?php echo esc_attr( $form_id ); ?>-username"
							name="username"
							class="wbcom-essential-login-form__input"
							placeholder="<?php echo esc_attr( $username_placeholder ); ?>"
							autocomplete="username"
							required
						/>
					</div>

					<div class="wbcom-essential-login-form__field">
						<?php if ( $show_labels ) : ?>
							<label for="<?php echo esc_attr( $form_id ); ?>-password" class="wbcom-essential-login-form__label">
								<?php echo esc_html( $password_label ); ?>
							</label>
						<?php endif; ?>
						<input
							type="password"
							id="<?php echo esc_attr( $form_id ); ?>-password"
							name="password"
							class="wbcom-essential-login-form__input"
							placeholder="<?php echo esc_attr( $password_placeholder ); ?>"
							autocomplete="current-password"
							required
						/>
					</div>

					<?php if ( $show_remember_me ) : ?>
						<div class="wbcom-essential-login-form__options">
							<label class="wbcom-essential-login-form__remember">
								<input type="checkbox" name="remember" value="1" />
								<span><?php echo esc_html( $remember_me_label ); ?></span>
							</label>
						</div>
					<?php endif; ?>

					<input type="hidden" name="redirect" value="<?php echo $redirect_enabled ? esc_attr( $redirect_url ) : ''; ?>" />
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'wbcom_essential_login_nonce' ) ); ?>" />

					<button type="submit" class="wbcom-essential-login-form__button">
						<span class="wbcom-essential-login-form__button-text"><?php echo esc_html( $button_text ); ?></span>
						<span class="wbcom-essential-login-form__button-loading" hidden>
							<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
								<path d="M12 2A10 10 0 1 0 22 12 10 10 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" opacity="0.25"/>
								<path d="M12 4a8 8 0 0 1 8 8h2a10 10 0 0 0-10-10Z">
									<animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/>
								</path>
							</svg>
						</span>
					</button>

					<?php if ( ( $show_register && get_option( 'users_can_register' ) ) || $show_lost_password ) : ?>
						<div class="wbcom-essential-login-form__links">
							<?php if ( $show_lost_password ) : ?>
								<a href="<?php echo esc_url( $lost_password_url ); ?>" class="wbcom-essential-login-form__link">
									<?php echo esc_html( $lost_password_text ); ?>
								</a>
							<?php endif; ?>

							<?php if ( $show_lost_password && $show_register && get_option( 'users_can_register' ) && $link_separator ) : ?>
								<span class="wbcom-essential-login-form__link-separator"><?php echo esc_html( $link_separator ); ?></span>
							<?php endif; ?>

							<?php if ( $show_register && get_option( 'users_can_register' ) ) : ?>
								<a href="<?php echo esc_url( $register_url ); ?>" class="wbcom-essential-login-form__link">
									<?php echo esc_html( $register_text ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</form>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
