<?php
/**
 * Login Form Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Login Form block.
 */
function wbcom_essential_login_form_block_init() {
	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/login-form/';

	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_login_form_block_init' );

/**
 * Enqueue frontend script data for AJAX login.
 */
function wbcom_essential_login_form_enqueue_scripts() {
	if ( ! has_block( 'wbcom-essential/login-form' ) ) {
		return;
	}

	wp_localize_script(
		'wbcom-essential-login-form-view-script',
		'wbcomEssentialLogin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'wbcom_essential_login_form_enqueue_scripts' );

/**
 * AJAX Login Handler.
 */
function wbcom_essential_ajax_login() {
	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wbcom_essential_login_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wbcom-essential' ) ) );
	}

	// Check if PMPro is active and should handle login.
	if ( function_exists( 'pmpro_login_head' ) ) {
		wp_send_json_error( array( 'message' => __( 'Please use the standard form submission.', 'wbcom-essential' ) ) );
	}

	$username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
	$password = isset( $_POST['password'] ) ? $_POST['password'] : ''; // phpcs:ignore
	$remember = ! empty( $_POST['remember'] );

	if ( empty( $username ) || empty( $password ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter both username and password.', 'wbcom-essential' ) ) );
	}

	$creds = array(
		'user_login'    => $username,
		'user_password' => $password,
		'remember'      => $remember,
	);

	$user = wp_signon( $creds, is_ssl() );

	if ( is_wp_error( $user ) ) {
		$error_code    = $user->get_error_code();
		$error_message = $user->get_error_message();

		// Provide user-friendly error messages.
		if ( 'invalid_username' === $error_code || 'invalid_email' === $error_code ) {
			$error_message = __( 'Unknown username or email address. Please check again.', 'wbcom-essential' );
		} elseif ( 'incorrect_password' === $error_code ) {
			$error_message = __( 'The password you entered is incorrect.', 'wbcom-essential' );
		}

		wp_send_json_error( array( 'message' => $error_message ) );
	}

	// Get redirect URL - validate to prevent open redirect attacks.
	$redirect_url = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : '';

	// Validate redirect URL to ensure it's on the same site (prevent open redirect).
	if ( ! empty( $redirect_url ) ) {
		$redirect_url = wp_validate_redirect( $redirect_url, home_url() );
	}

	if ( empty( $redirect_url ) ) {
		// Default redirect based on user role.
		if ( user_can( $user, 'manage_options' ) ) {
			$redirect_url = admin_url();
		} elseif ( function_exists( 'bp_loggedin_user_domain' ) ) {
			$redirect_url = bp_loggedin_user_domain();
		} else {
			$redirect_url = home_url();
		}
	}

	wp_send_json_success(
		array(
			'message'  => __( 'Login successful! Redirecting...', 'wbcom-essential' ),
			'redirect' => $redirect_url,
		)
	);
}
add_action( 'wp_ajax_nopriv_wbcom_essential_ajax_login', 'wbcom_essential_ajax_login' );
add_action( 'wp_ajax_wbcom_essential_ajax_login', 'wbcom_essential_ajax_login' );
