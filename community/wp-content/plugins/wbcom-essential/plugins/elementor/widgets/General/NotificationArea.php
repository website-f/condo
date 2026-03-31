<?php
/**
 * Elementor Notification Area Widget.
 *
 * @since      1.0.0
 *
 * @package    Wbcom_Essential
 * @subpackage Wbcom_Essential/plugins/elementor/widget/general
 */

namespace WBCOM_ESSENTIAL\ELEMENTOR\Widgets\General;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

use WBCOM_ESSENTIAL\ELEMENTOR\Plugin;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

/**
 * Elementor NotificationArea
 *
 * Elementor widget for NotificationArea
 *
 * @since 1.0.0
 */
class NotificationArea extends \Elementor\Widget_Base {

	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'notification-area', WBCOM_ESSENTIAL_ELEMENTOR_URL . 'assets/css/notification-area.css', array(), WBCOM_ESSENTIAL_VERSION );

		wp_register_script( 'notification-area', WBCOM_ESSENTIAL_ELEMENTOR_URL . 'assets/js/notification-area.js', array( 'jquery' ), WBCOM_ESSENTIAL_VERSION, true );
	}

	public function get_name() {
		return 'wbcom-notification-area';
	}

	public function get_title() {
		return esc_html__( 'Header Notification Area', 'wbcom-essential' );
	}

	public function get_icon() {
		return 'eicon-alert';
	}

	public function get_categories() {
		return array( 'wbcom-elements' );
	}

	/**
	 * Get keywords.
	 */
	public function get_keywords() {
		return array( 'notification', 'alert', 'header', 'bell', 'messages' );
	}

	public function get_style_depends() {
		return array( 'notification-area' );
	}

	public function get_script_depends() {
		return array( 'notification-area' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_reign_notification_area',
			array(
				'label' => __( 'Notification Area', 'wbcom-essential' ),
			)
		);

		$this->add_responsive_control(
			'search_form_enabled',
			array(
				'label'          => esc_html__( 'Enable Search Form', 'wbcom-essential' ),
				'type'           => Controls_Manager::CHOOSE,
				'options'        => array(
					'yes' => array(
						'title' => esc_html__( 'Show', 'wbcom-essential' ),
						'icon'  => 'eicon-check',
					),
					'no'  => array(
						'title' => esc_html__( 'Hide', 'wbcom-essential' ),
						'icon'  => 'eicon-close',
					),
				),
				'default'        => 'yes',
				'tablet_default' => 'yes',
				'mobile_default' => 'yes',
				'toggle'         => true,
			)
		);

		if ( class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' ) ) {
			$this->add_responsive_control(
				'rtm_cart_icon_enabled',
				array(
					'label'          => esc_html__( 'Enable Cart Icon', 'wbcom-essential' ),
					'type'           => Controls_Manager::CHOOSE,
					'options'        => array(
						'yes' => array(
							'title' => esc_html__( 'Show', 'wbcom-essential' ),
							'icon'  => 'eicon-check',
						),
						'no'  => array(
							'title' => esc_html__( 'Hide', 'wbcom-essential' ),
							'icon'  => 'eicon-close',
						),
					),
					'default'        => 'yes',
					'tablet_default' => 'yes',
					'mobile_default' => 'yes',
					'toggle'         => true,
				)
			);
		}

		if ( class_exists( 'BuddyPress' ) && bp_is_active( 'messages' ) ) {
			$this->add_responsive_control(
				'user_message_bell_enabled',
				array(
					'label'          => esc_html__( 'Enable User Message Icon', 'wbcom-essential' ),
					'type'           => Controls_Manager::CHOOSE,
					'options'        => array(
						'yes' => array(
							'title' => esc_html__( 'Show', 'wbcom-essential' ),
							'icon'  => 'eicon-check',
						),
						'no'  => array(
							'title' => esc_html__( 'Hide', 'wbcom-essential' ),
							'icon'  => 'eicon-close',
						),
					),
					'default'        => 'yes',
					'tablet_default' => 'yes',
					'mobile_default' => 'yes',
					'toggle'         => true,
				)
			);
		}

		if ( class_exists( 'BuddyPress' ) && bp_is_active( 'notifications' ) ) {

			$this->add_responsive_control(
				'notification_bell_enabled',
				array(
					'label'          => esc_html__( 'Enable Notification Icon', 'wbcom-essential' ),
					'type'           => Controls_Manager::CHOOSE,
					'options'        => array(
						'yes' => array(
							'title' => esc_html__( 'Show', 'wbcom-essential' ),
							'icon'  => 'eicon-check',
						),
						'no'  => array(
							'title' => esc_html__( 'Hide', 'wbcom-essential' ),
							'icon'  => 'eicon-close',
						),
					),
					'default'        => 'yes',
					'tablet_default' => 'yes',
					'mobile_default' => 'yes',
					'toggle'         => true,
				)
			);
		}

		$this->add_responsive_control(
			'avatar_enabled',
			array(
				'label'          => esc_html__( 'Display User Avatar', 'wbcom-essential' ),
				'type'           => Controls_Manager::CHOOSE,
				'options'        => array(
					'yes' => array(
						'title' => esc_html__( 'Show', 'wbcom-essential' ),
						'icon'  => 'eicon-check',
					),
					'no'  => array(
						'title' => esc_html__( 'Hide', 'wbcom-essential' ),
						'icon'  => 'eicon-close',
					),
				),
				'default'        => 'yes',
				'tablet_default' => 'yes',
				'mobile_default' => 'yes',
				'toggle'         => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_notification_area_style',
			array(
				'label' => esc_html__( 'Notification Area', 'wbcom-essential' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon Color', 'wbcom-essential' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#000000',
				'selectors' => array(
					'{{WRAPPER}} #masthead.wbcom-notification-area .header-notifications-dropdown-toggle a.rg-icon-wrap' => 'color: {{VALUE}};',
					'{{WRAPPER}} #masthead.wbcom-notification-area .rg-search-icon:before' => 'color: {{VALUE}};',
					'{{WRAPPER}} #masthead.wbcom-notification-area .rg-icon-wrap span:before' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_hover_color',
			array(
				'label'     => __( 'Icon Hover Color', 'wbcom-essential' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#000000',
				'selectors' => array(
					'{{WRAPPER}} #masthead.wbcom-notification-area .header-notifications-dropdown-toggle a.rg-icon-wrap:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} #masthead.wbcom-notification-area .rg-search-icon:hover:before' => 'color: {{VALUE}};',
					'{{WRAPPER}} #masthead.wbcom-notification-area .rg-icon-wrap span:hover:before' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'user_name_font_color',
			array(
				'label'     => __( 'User Name Font Color', 'wbcom-essential' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#000',
				'selectors' => array(
					'{{WRAPPER}} #masthead.wbcom-notification-area a.user-link' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'user_name_font_color_hover',
			array(
				'label'     => __( 'User Name Font Color (Hover)', 'wbcom-essential' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#000',
				'selectors' => array(
					'{{WRAPPER}} #masthead.wbcom-notification-area a.user-link:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'notification_height',
			array(
				'label'     => __( 'Line Height (px)', 'wbcom-essential' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 90,
				'selectors' => array(
					'{{WRAPPER}} #masthead.wbcom-notification-area .rg-icon-wrap, {{WRAPPER}} #masthead.wbcom-notification-area .user-link-main-wrap' => 'line-height: {{VALUE}}px;height: {{VALUE}}px;',
				),
			)
		);

		$this->add_control(
			'counter_top',
			array(
				'label'     => __( 'Counter Top Space (px)', 'wbcom-essential' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 20,
				'selectors' => array(
					'{{WRAPPER}} .header-right.wb-grid-flex.wbesntl-notification-area .rg-count' => 'top: {{VALUE}}px;',
				),
			)
		);

		$this->end_controls_section();

		do_action( 'reign_wp_menu_elementor_controls', $this );
	}

	/**
	 * Render our custom menu onto the page.
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		// Get search form visibility settings per device.
		$search_form_visibility_desktop = isset( $settings['search_form_enabled'] ) ? $settings['search_form_enabled'] : 'yes';
		$search_form_visibility_tablet  = isset( $settings['search_form_enabled_tablet'] ) ? $settings['search_form_enabled_tablet'] : 'yes';
		$search_form_visibility_mobile  = isset( $settings['search_form_enabled_mobile'] ) ? $settings['search_form_enabled_mobile'] : 'yes';

		// Get search form visibility settings per device.
		$cart_icon_visibility_desktop = isset( $settings['rtm_cart_icon_enabled'] ) ? $settings['rtm_cart_icon_enabled'] : 'yes';
		$cart_icon_visibility_tablet  = isset( $settings['rtm_cart_icon_enabled_tablet'] ) ? $settings['rtm_cart_icon_enabled_tablet'] : 'yes';
		$cart_icon_visibility_mobile  = isset( $settings['rtm_cart_icon_enabled_mobile'] ) ? $settings['rtm_cart_icon_enabled_mobile'] : 'yes';

		// Get message visibility settings per device.
		$message_visibility_desktop = isset( $settings['user_message_bell_enabled'] ) ? $settings['user_message_bell_enabled'] : 'yes';
		$message_visibility_tablet  = isset( $settings['user_message_bell_enabled_tablet'] ) ? $settings['user_message_bell_enabled_tablet'] : 'yes';
		$message_visibility_mobile  = isset( $settings['user_message_bell_enabled_mobile'] ) ? $settings['user_message_bell_enabled_mobile'] : 'yes';

		// Get notification visibility settings per device.
		$notification_visibility_desktop = isset( $settings['notification_bell_enabled'] ) ? $settings['notification_bell_enabled'] : 'yes';
		$notification_visibility_tablet  = isset( $settings['notification_bell_enabled_tablet'] ) ? $settings['notification_bell_enabled_tablet'] : 'yes';
		$notification_visibility_mobile  = isset( $settings['notification_bell_enabled_mobile'] ) ? $settings['notification_bell_enabled_mobile'] : 'yes';

		// Get avatar visibility settings per device.
		$avatar_visibility_desktop = isset( $settings['avatar_enabled'] ) ? $settings['avatar_enabled'] : 'yes';
		$avatar_visibility_tablet  = isset( $settings['avatar_enabled_tablet'] ) ? $settings['avatar_enabled_tablet'] : 'yes';
		$avatar_visibility_mobile  = isset( $settings['avatar_enabled_mobile'] ) ? $settings['avatar_enabled_mobile'] : 'yes';

		$notification_height = isset( $settings['notification_height'] ) ? $settings['notification_height'] : 90;

		ob_start();
		?>

		<style type="text/css">
			#masthead .header-right.wb-grid-flex.wbesntl-notification-area .user-link-wrap .user-profile-menu,
			#masthead .header-right.wb-grid-flex.wbesntl-notification-area .rg-header-submenu.rg-dropdown {
				top: <?php echo esc_attr( $notification_height ); ?>px;
			}
		</style>

		<div id="masthead" class="wbcom-notification-area">
			<div class="header-right no-gutter wb-grid-flex grid-center wbesntl-notification-area">
				<div class="wbcom-notification-area-navbar">
				<?php
				// Display search form if enabled.
				$search_form_output = '';

				// Start output buffering to capture the search form output.
				ob_start();
				if ( function_exists( 'get_search_form' ) ) {
					get_search_form();
				}
				$search_form_output = ob_get_clean();

				$devices = array(
					'desktop' => array(
						'visible'            => $search_form_visibility_desktop,
						'class'              => 'search-desktop',
						'additional_classes' => 'elementor-hidden-tablet elementor-hidden-mobile',
					),
					'tablet'  => array(
						'visible'            => $search_form_visibility_tablet,
						'class'              => 'search-tablet',
						'additional_classes' => 'elementor-hidden-desktop elementor-hidden-mobile',
					),
					'mobile'  => array(
						'visible'            => $search_form_visibility_mobile,
						'class'              => 'search-mobile',
						'additional_classes' => 'elementor-hidden-desktop elementor-hidden-tablet',
					),
				);

				// Loop through devices and display search form based on visibility.
				foreach ( $devices as $device ) {
					if ( 'yes' === $device['visible'] ) {
						echo '<div class="search-wrap rg-icon-wrap ' . esc_attr( $device['class'] ) . ' ' . esc_attr( $device['additional_classes'] ) . '">';
						echo '<span class="rg-search-icon far fa-search"></span>';
						echo '<div class="rg-search-form-wrap">';
						echo '<span class="rg-search-close far fa-times-circle"></span>';
						echo $search_form_output; // phpcs:ignore
						echo '</div>';
						echo '</div>';
					}
				}

				// Display cart if enabled.
				$cart_output = '';

				// Check if Elementor editor is active or the cart count function is available.
				if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
					$cart_output = '<div class="woo-cart-wrap rg-icon-wrap"><span class="far fa-shopping-cart"></span></div>';
				} elseif ( function_exists( 'my_wc_cart_count' ) ) {
					// Capture the WooCommerce cart count output.
					ob_start();
					my_wc_cart_count();
					$cart_output = ob_get_clean();
				}

				$devices = array(
					'desktop' => array(
						'visible'            => $cart_icon_visibility_desktop,
						'class'              => 'cart-desktop',
						'additional_classes' => 'elementor-hidden-tablet elementor-hidden-mobile',
					),
					'tablet'  => array(
						'visible'            => $cart_icon_visibility_tablet,
						'class'              => 'cart-tablet',
						'additional_classes' => 'elementor-hidden-desktop elementor-hidden-mobile',
					),
					'mobile'  => array(
						'visible'            => $cart_icon_visibility_mobile,
						'class'              => 'cart-mobile',
						'additional_classes' => 'elementor-hidden-desktop elementor-hidden-tablet',
					),
				);

				// Loop through devices and display cart icon based on visibility.
				foreach ( $devices as $device ) {
					if ( class_exists( 'WooCommerce' ) && 'yes' === $device['visible'] ) {
						echo '<div class="woo-cart-wrap rg-icon-wrap ' . esc_attr( $device['class'] ) . ' ' . esc_attr( $device['additional_classes'] ) . '">';
						echo wp_kses_post( $cart_output );
						echo '</div>';
					}
				}

				// Display user message bell and notifications if logged in.
				if ( is_user_logged_in() ) {

					// Start output buffering to capture the template part output.
					ob_start();
					get_template_part( 'template-parts/header-icons/message' );
					$message_output = ob_get_clean();

					$devices = array(
						'desktop' => array(
							'visible'            => $message_visibility_desktop,
							'class'              => 'message-desktop',
							'additional_classes' => 'elementor-hidden-tablet elementor-hidden-mobile',
						),
						'tablet'  => array(
							'visible'            => $message_visibility_tablet,
							'class'              => 'message-tablet',
							'additional_classes' => 'elementor-hidden-desktop elementor-hidden-mobile',
						),
						'mobile'  => array(
							'visible'            => $message_visibility_mobile,
							'class'              => 'message-mobile',
							'additional_classes' => 'elementor-hidden-desktop elementor-hidden-tablet',
						),
					);

					// Loop through devices and display message based on visibility.
					foreach ( $devices as $device ) {
						if ( 'yes' === $device['visible'] ) {
							echo '<div class="' . esc_attr( $device['class'] ) . ' ' . esc_attr( $device['additional_classes'] ) . '">' . wp_kses_post( $message_output ) . '</div>';
						}
					}

					// Start output buffering to capture the template part output.
					ob_start();
					get_template_part( 'template-parts/header-icons/notification' );
					$notification_output = ob_get_clean();

					$devices = array(
						'desktop' => array(
							'visible'            => $notification_visibility_desktop,
							'class'              => 'notification-desktop',
							'additional_classes' => 'elementor-hidden-tablet elementor-hidden-mobile',
						),
						'tablet'  => array(
							'visible'            => $notification_visibility_tablet,
							'class'              => 'notification-tablet',
							'additional_classes' => 'elementor-hidden-desktop elementor-hidden-mobile',
						),
						'mobile'  => array(
							'visible'            => $notification_visibility_mobile,
							'class'              => 'notification-mobile',
							'additional_classes' => 'elementor-hidden-desktop elementor-hidden-tablet',
						),
					);

					// Loop through devices and display notification based on visibility.
					foreach ( $devices as $device ) {
						if ( 'yes' === $device['visible'] ) {
							echo '<div class="' . esc_attr( $device['class'] ) . ' ' . esc_attr( $device['additional_classes'] ) . '">' . wp_kses_post( $notification_output ) . '</div>';
						}
					}

					// Start output buffering to capture the template part output.
					ob_start();
					get_template_part( 'template-parts/header-icons/user-menu' );
					$avatar_output = ob_get_clean();

					$devices = array(
						'desktop' => array(
							'visible'            => $avatar_visibility_desktop,
							'class'              => 'avatar-desktop',
							'additional_classes' => 'elementor-hidden-tablet elementor-hidden-mobile',
						),
						'tablet'  => array(
							'visible'            => $avatar_visibility_tablet,
							'class'              => 'avatar-tablet',
							'additional_classes' => 'elementor-hidden-desktop elementor-hidden-mobile',
						),
						'mobile'  => array(
							'visible'            => $avatar_visibility_mobile,
							'class'              => 'avatar-mobile',
							'additional_classes' => 'elementor-hidden-desktop elementor-hidden-tablet',
						),
					);

					// Loop through devices and display avatars based on visibility.
					foreach ( $devices as $device ) {
						if ( 'yes' === $device['visible'] ) {
							echo '<div class="' . esc_attr( $device['class'] ) . ' ' . esc_attr( $device['additional_classes'] ) . '"><div class="user-link-main-wrap">' . wp_kses_post( $avatar_output ) . '</div></div>';
						}
					}
				} else {
					// Login/Register Links.
					$wbcom_ele_login_url    = apply_filters( 'wbcom_ele_notification_login_url', wp_login_url() );
					$wbcom_ele_register_url = apply_filters( 'wbcom_ele_notification_registration_url', wp_registration_url() );
					?>
					<div class="rg-icon-wrap">
						<a href="<?php echo esc_url( $wbcom_ele_login_url ); ?>" class="btn-login" title="Login">
							<span class="far fa-sign-in-alt"></span>
						</a>
					</div>
					<?php
					if ( get_option( 'users_can_register' ) ) {
						?>
						<span class="sep">|</span>
						<div class="rg-icon-wrap">
							<a href="<?php echo esc_url( $wbcom_ele_register_url ); ?>" class="btn-register" title="Register">
								<span class="far fa-address-book"></span>
							</a>
						</div>
						<?php
					}
				}
				?>
				</div>
			</div>
		</div>

		<?php
		echo apply_filters( 'reign_notification_area_output', ob_get_clean(), $settings['notification_bell_enabled'], $settings['notification_bell_enabled'], $settings ); //phpcs:ignore
	}
}
