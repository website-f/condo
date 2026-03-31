<?php
/**
 * WBCom Essential Gutenberg Blocks.
 *
 * @link       https://wbcomdesigns.com/plugins
 * @since      1.0.0
 *
 * @package    Wbcom_Essential
 * @subpackage Wbcom_Essential/plugins/gutenberg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Main Gutenberg Blocks Class
 */
class WBCOM_Essential_Gutenberg {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load_blocks();
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
		add_filter( 'style_loader_src', array( $this, 'filter_kirki_style_src' ), 10, 2 );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_blocks_reset_styles' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_blocks_reset_styles' ) );
		
	}

	/**
	 * Register Swiper script and CSS for carousel blocks.
	 *
	 * Makes Swiper available as a dependency for blocks that need it.
	 */
	public function register_swiper_script() {
		wp_register_script(
			'wbcom-swiper',
			WBCOM_ESSENTIAL_URL . 'plugins/elementor/assets/js/swiper.min.js',
			array( 'jquery' ),
			WBCOM_ESSENTIAL_VERSION,
			true
		);

		// Register Swiper CSS
		wp_register_style(
			'wbcom-swiper-css',
			WBCOM_ESSENTIAL_URL . 'assets/vendor/swiper/swiper-bundle.min.css',
			array(),
			WBCOM_ESSENTIAL_VERSION
		);

		// Auto-enqueue Swiper CSS when script is used
		wp_enqueue_style( 'wbcom-swiper-css' );
	}

	/**
	 * Enqueue global reset styles for all blocks.
	 *
	 * Prevents theme styles from adding unwanted borders and outlines
	 * to block wrappers.
	 */
	public function enqueue_blocks_reset_styles() {
		wp_enqueue_style(
			'wbcom-essential-blocks-reset',
			WBCOM_ESSENTIAL_URL . 'plugins/gutenberg/assets/css/blocks-reset.css',
			array(),
			WBCOM_ESSENTIAL_VERSION
		);

		// Theme colors integration - maps WordPress theme.json colors to block variables.
		wp_enqueue_style(
			'wbcom-essential-theme-colors',
			WBCOM_ESSENTIAL_URL . 'plugins/gutenberg/assets/css/theme-colors.css',
			array( 'wbcom-essential-blocks-reset' ),
			WBCOM_ESSENTIAL_VERSION
		);
	}

	/**
	 * Automatically load all blocks from the blocks directory
	 */
	private function load_blocks() {
		$blocks_dir = WBCOM_ESSENTIAL_PATH . 'plugins/gutenberg/blocks/';

		if ( ! is_dir( $blocks_dir ) ) {
			return;
		}

		$block_dirs = scandir( $blocks_dir );

		foreach ( $block_dirs as $block_dir ) {
			// Skip current and parent directories
			if ( $block_dir === '.' || $block_dir === '..' ) {
				continue;
			}

			$full_block_path = $blocks_dir . $block_dir;

			// Check if it's a directory
			if ( ! is_dir( $full_block_path ) ) {
				continue;
			}

			// Look for the main PHP file (usually named after the block)
			$php_file = $full_block_path . '/' . $block_dir . '.php';

			if ( file_exists( $php_file ) ) {
				require_once $php_file;
			}

			// Look for render.php file for dynamic blocks
			$render_file = $full_block_path . '/render.php';

			if ( file_exists( $render_file ) ) {
				require_once $render_file;
			}
		}
	}

	/**
	 * Register block categories.
	 *
	 * Organizes blocks into logical groups for easier discovery.
	 *
	 * @param array $categories Block categories.
	 * @return array
	 */
	public function register_block_category( $categories ) {
		$starter_categories = array(
			array(
				'slug'  => 'starter-header',
				'title' => __( 'Starter Pack - Header', 'wbcom-essential' ),
				'icon'  => 'admin-links',
			),
			array(
				'slug'  => 'starter-design',
				'title' => __( 'Starter Pack - Design', 'wbcom-essential' ),
				'icon'  => 'art',
			),
			array(
				'slug'  => 'starter-content',
				'title' => __( 'Starter Pack - Content', 'wbcom-essential' ),
				'icon'  => 'editor-table',
			),
			array(
				'slug'  => 'starter-blog',
				'title' => __( 'Starter Pack - Blog', 'wbcom-essential' ),
				'icon'  => 'admin-post',
			),
			array(
				'slug'  => 'starter-marketing',
				'title' => __( 'Starter Pack - Marketing', 'wbcom-essential' ),
				'icon'  => 'megaphone',
			),
			array(
				'slug'  => 'starter-buddypress',
				'title' => __( 'Starter Pack - BuddyPress', 'wbcom-essential' ),
				'icon'  => 'groups',
			),
		);

		// Only register WooCommerce category when WooCommerce is active.
		if ( class_exists( 'WooCommerce' ) ) {
			$starter_categories[] = array(
				'slug'  => 'starter-woocommerce',
				'title' => __( 'Starter Pack - WooCommerce', 'wbcom-essential' ),
				'icon'  => 'cart',
			);
		}

		return array_merge( $starter_categories, $categories );
	}

	/**
	 * Filter Kirki style src to prevent loading in editor
	 */
	public function filter_kirki_style_src( $src, $handle ) {
		if ( strpos( $src, 'action=kirki-styles' ) !== false ) {
			return false;
		}
		return $src;
	}

	/**
	 * Register REST API routes for Gutenberg blocks.
	 */
	public function register_rest_routes() {
		register_rest_route(
			'wbcom-essential/v1',
			'/xprofile-groups',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_xprofile_groups' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Get available BuddyPress xProfile groups for the editor.
	 *
	 * @return WP_REST_Response
	 */
	public function get_xprofile_groups() {
		$groups = array();

		// Check if BuddyPress xProfile is available.
		if ( ! function_exists( 'bp_xprofile_get_groups' ) ) {
			return rest_ensure_response( $groups );
		}

		$profile_groups = bp_xprofile_get_groups();

		if ( ! empty( $profile_groups ) ) {
			foreach ( $profile_groups as $group ) {
				$groups[] = array(
					'id'   => $group->id,
					'name' => $group->name,
				);
			}
		}

		// Add photo options if not disabled.
		$photo_options = array();

		if ( function_exists( 'bp_disable_avatar_uploads' ) && ! bp_disable_avatar_uploads() ) {
			$photo_options[] = array(
				'id'   => 'profile_photo',
				'name' => __( 'Profile Photo', 'wbcom-essential' ),
			);
		}

		if ( function_exists( 'bp_disable_cover_image_uploads' ) && ! bp_disable_cover_image_uploads() ) {
			$photo_options[] = array(
				'id'   => 'cover_photo',
				'name' => __( 'Cover Photo', 'wbcom-essential' ),
			);
		}

		return rest_ensure_response(
			array(
				'fieldGroups'  => $groups,
				'photoOptions' => $photo_options,
			)
		);
	}
}

// Initialize the Gutenberg blocks
new WBCOM_Essential_Gutenberg();