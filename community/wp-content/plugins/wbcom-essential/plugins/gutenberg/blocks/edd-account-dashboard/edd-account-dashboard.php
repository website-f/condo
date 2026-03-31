<?php
/**
 * EDD Account Dashboard Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the EDD Account Dashboard block.
 *
 * Only registers when Easy Digital Downloads is active.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function wbcom_essential_edd_account_dashboard_block_init() {
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/edd-account-dashboard/';

	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_edd_account_dashboard_block_init' );

/**
 * Register REST API routes for EDD account tab content.
 */
function wbcom_essential_edd_account_rest_routes() {
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		return;
	}

	register_rest_route(
		'wbcom/v1',
		'/edd-account/(?P<tab>[a-z-]+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'wbcom_essential_edd_account_tab_callback',
			'permission_callback' => function () {
				return is_user_logged_in();
			},
			'args'                => array(
				'tab' => array(
					'required'          => true,
					'validate_callback' => function ( $param ) {
						return in_array(
							$param,
							array( 'dashboard', 'subscriptions', 'downloads', 'licenses', 'purchases', 'profile' ),
							true
						);
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'wbcom_essential_edd_account_rest_routes' );

/**
 * REST callback: render the requested tab as HTML.
 *
 * @param WP_REST_Request $request Full request object.
 * @return WP_REST_Response
 */
function wbcom_essential_edd_account_tab_callback( $request ) {
	$tab = $request->get_param( 'tab' );

	ob_start();

	switch ( $tab ) {
		case 'dashboard':
			wbcom_essential_edd_render_dashboard_tab();
			break;
		case 'subscriptions':
			wbcom_essential_edd_render_subscriptions_tab();
			break;
		case 'downloads':
			wbcom_essential_edd_render_downloads_tab();
			break;
		case 'licenses':
			wbcom_essential_edd_render_licenses_tab();
			break;
		case 'purchases':
			wbcom_essential_edd_render_purchases_tab();
			break;
		case 'profile':
			wbcom_essential_edd_render_profile_tab();
			break;
	}

	$html = ob_get_clean();

	return rest_ensure_response( array( 'html' => $html ) );
}

/**
 * Render Dashboard overview tab.
 */
function wbcom_essential_edd_render_dashboard_tab() {
	$user     = wp_get_current_user();
	$customer = edd_get_customer_by( 'user_id', $user->ID );

	$order_count = $customer ? $customer->purchase_count : 0;
	$total_value = $customer ? (float) $customer->purchase_value : 0.0;
	$total_spent = edd_currency_filter( edd_format_amount( $total_value ) );

	// License count via Software Licensing add-on.
	$license_count = 0;
	if ( function_exists( 'edd_software_licensing' ) && $customer ) {
		$licenses = edd_software_licensing()->licenses_db->get_licenses(
			array(
				'customer_id' => $customer->id,
				'number'      => -1,
			)
		);
		$license_count = is_array( $licenses ) ? count( $licenses ) : 0;
	}

	// Active subscription count via EDD Recurring add-on.
	$sub_count = 0;
	if ( class_exists( 'EDD_Recurring' ) && $customer ) {
		$subs_db   = new EDD_Subscriptions_DB();
		$subs      = $subs_db->get_subscriptions(
			array(
				'customer_id' => $customer->id,
				'status'      => 'active',
			)
		);
		$sub_count = is_array( $subs ) ? count( $subs ) : 0;
	}
	?>
	<div class="wbcom-edd-dashboard">
		<h2 class="wbcom-edd-dashboard__greeting">
			<?php
			printf(
				/* translators: %s: User's first name or display name. */
				esc_html__( 'Welcome back, %s!', 'wbcom-essential' ),
				esc_html( $user->first_name ? $user->first_name : $user->display_name )
			);
			?>
		</h2>

		<div class="wbcom-edd-dashboard__stats">
			<div class="wbcom-edd-dashboard__stat-card">
				<span class="wbcom-edd-dashboard__stat-number"><?php echo esc_html( $order_count ); ?></span>
				<span class="wbcom-edd-dashboard__stat-label"><?php esc_html_e( 'Total Orders', 'wbcom-essential' ); ?></span>
			</div>
			<div class="wbcom-edd-dashboard__stat-card">
				<span class="wbcom-edd-dashboard__stat-number"><?php echo wp_kses_post( $total_spent ); ?></span>
				<span class="wbcom-edd-dashboard__stat-label"><?php esc_html_e( 'Total Spent', 'wbcom-essential' ); ?></span>
			</div>
			<?php if ( function_exists( 'edd_software_licensing' ) ) : ?>
			<div class="wbcom-edd-dashboard__stat-card">
				<span class="wbcom-edd-dashboard__stat-number"><?php echo esc_html( $license_count ); ?></span>
				<span class="wbcom-edd-dashboard__stat-label"><?php esc_html_e( 'Active Licenses', 'wbcom-essential' ); ?></span>
			</div>
			<?php endif; ?>
			<?php if ( class_exists( 'EDD_Recurring' ) ) : ?>
			<div class="wbcom-edd-dashboard__stat-card">
				<span class="wbcom-edd-dashboard__stat-number"><?php echo esc_html( $sub_count ); ?></span>
				<span class="wbcom-edd-dashboard__stat-label"><?php esc_html_e( 'Active Subscriptions', 'wbcom-essential' ); ?></span>
			</div>
			<?php endif; ?>
		</div>

		<?php if ( $customer ) : ?>
			<?php
			$recent_orders = edd_get_orders(
				array(
					'customer_id' => $customer->id,
					'number'      => 3,
					'status'      => array( 'complete', 'partially_refunded' ),
					'orderby'     => 'date_created',
					'order'       => 'DESC',
				)
			);
			?>
			<?php if ( ! empty( $recent_orders ) ) : ?>
			<div class="wbcom-edd-dashboard__recent">
				<h3 class="wbcom-edd-dashboard__section-title"><?php esc_html_e( 'Recent Orders', 'wbcom-essential' ); ?></h3>
				<table class="wbcom-edd-dashboard__table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Order', 'wbcom-essential' ); ?></th>
							<th><?php esc_html_e( 'Date', 'wbcom-essential' ); ?></th>
							<th><?php esc_html_e( 'Amount', 'wbcom-essential' ); ?></th>
							<th><?php esc_html_e( 'Status', 'wbcom-essential' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_orders as $order ) : ?>
						<tr>
							<td>#<?php echo esc_html( $order->get_number() ); ?></td>
							<td><?php echo esc_html( edd_date_i18n( $order->date_created ) ); ?></td>
							<td><?php echo esc_html( edd_display_amount( $order->total, $order->currency ) ); ?></td>
							<td>
								<span class="wbcom-edd-status wbcom-edd-status--<?php echo esc_attr( $order->status ); ?>">
									<?php echo esc_html( edd_get_status_label( $order->status ) ); ?>
								</span>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Output a tab section header with title and optional description.
 *
 * @param string $title       Section title.
 * @param string $description Optional short description.
 */
function wbcom_essential_edd_tab_header( $title, $description = '' ) {
	?>
	<div class="wbcom-edd-tab-header">
		<h2 class="wbcom-edd-tab-header__title"><?php echo esc_html( $title ); ?></h2>
		<?php if ( $description ) : ?>
			<p class="wbcom-edd-tab-header__description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render Subscriptions tab.
 */
function wbcom_essential_edd_render_subscriptions_tab() {
	wbcom_essential_edd_tab_header(
		__( 'Subscriptions', 'wbcom-essential' ),
		__( 'Manage your active and past subscriptions.', 'wbcom-essential' )
	);
	if ( ! class_exists( 'EDD_Recurring' ) ) {
		echo '<p class="wbcom-edd-empty">' . esc_html__( 'No subscriptions found.', 'wbcom-essential' ) . '</p>';
		return;
	}
	echo do_shortcode( '[edd_subscriptions]' );
}

/**
 * Render Downloads tab.
 */
function wbcom_essential_edd_render_downloads_tab() {
	wbcom_essential_edd_tab_header(
		__( 'Downloads', 'wbcom-essential' ),
		__( 'Access and download the files from your purchases.', 'wbcom-essential' )
	);
	echo do_shortcode( '[download_history]' );
}

/**
 * Render Licenses tab.
 */
function wbcom_essential_edd_render_licenses_tab() {
	wbcom_essential_edd_tab_header(
		__( 'License Keys', 'wbcom-essential' ),
		__( 'View and manage your license keys and activations.', 'wbcom-essential' )
	);
	if ( ! function_exists( 'edd_software_licensing' ) ) {
		echo '<p class="wbcom-edd-empty">' . esc_html__( 'No licenses found.', 'wbcom-essential' ) . '</p>';
		return;
	}
	echo do_shortcode( '[edd_license_keys]' );
}

/**
 * Render Purchases/Order History tab.
 */
function wbcom_essential_edd_render_purchases_tab() {
	wbcom_essential_edd_tab_header(
		__( 'Order History', 'wbcom-essential' ),
		__( 'View your complete purchase history and order details.', 'wbcom-essential' )
	);
	echo do_shortcode( '[purchase_history]' );
}

/**
 * Render Profile / Edit Account tab.
 */
function wbcom_essential_edd_render_profile_tab() {
	wbcom_essential_edd_tab_header(
		__( 'Edit Profile', 'wbcom-essential' ),
		__( 'Update your account information and preferences.', 'wbcom-essential' )
	);
	echo do_shortcode( '[edd_profile_editor]' );
}
