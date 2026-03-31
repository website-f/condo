=== WBcom Essential ===
Contributors: wbcomdesigns
Donate link: https://wbcomdesigns.com/contact/
Tags: elementor, gutenberg, buddypress, woocommerce, blocks
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 4.3.0
Requires PHP: 8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Companion plugin for BuddyX theme providing 43 Elementor widgets and 45 Gutenberg blocks for BuddyPress, WooCommerce, and WordPress.

== Description ==

WBcom Essential extends both Elementor and Gutenberg with widgets and blocks designed for community websites, online stores, and content-rich sites.

= What's Included =

**43 Elementor Widgets**

* 27 General widgets (sliders, carousels, pricing tables, forms)
* 11 BuddyPress widgets (member grids, group carousels, activity feeds)
* 5 WooCommerce widgets (product displays, reviews, banners)

**45 Gutenberg Blocks**

* 26 General blocks with full Elementor widget parity
* 11 BuddyPress blocks for community features
* 2 WooCommerce blocks (product grid, mini cart)
* 7 Bonus blocks (counter, CTA box, divider, icon box, social icons, star rating)

= Key Features =

* **Use Theme Colors** - All 45 blocks support automatic theme color inheritance
* **Server-Side Rendering** - Dynamic blocks for BuddyPress, WooCommerce, and post queries
* **Responsive Design** - Mobile-first approach with breakpoint controls
* **Performance Optimized** - Conditional loading based on active plugins

= Block Categories =

Blocks are organized into 7 categories:

* Starter Pack - Header (4 blocks)
* Starter Pack - Design (14 blocks)
* Starter Pack - Content (8 blocks)
* Starter Pack - Blog (8 blocks)
* Starter Pack - Marketing (4 blocks)
* Starter Pack - BuddyPress (11 blocks)
* Starter Pack - WooCommerce (2 blocks)

= Best Used With =

* BuddyX Theme or Starter Templates Theme
* BuddyPress for community features
* WooCommerce for e-commerce features
* bbPress for forum features

== Installation ==

1. Upload `wbcom-essential` to `/wp-content/plugins/`
2. Activate through the Plugins menu
3. Elementor widgets appear in the "Starter Templates" category
4. Gutenberg blocks appear in "Starter Pack" categories

= Requirements =

* WordPress 6.0 or higher
* PHP 8.0 or higher
* Elementor 3.0+ (for Elementor widgets)
* BuddyPress 10.0+ (for BuddyPress features)
* WooCommerce 7.0+ (for WooCommerce features)

== Frequently Asked Questions ==

= Do I need Elementor installed? =

For Elementor widgets, yes. Gutenberg blocks work without Elementor - they're native WordPress blocks.

= What happens if BuddyPress isn't active? =

BuddyPress blocks only appear when BuddyPress is active. The plugin loads them conditionally.

= Can I use theme colors in blocks? =

Yes. All 45 blocks have a "Use Theme Colors" toggle in the Color Settings panel. When enabled, blocks inherit colors from your theme's CSS custom properties.

= Are blocks compatible with Full Site Editing? =

Yes. All blocks work in the Site Editor, post editor, and widget areas.

== Screenshots ==

1. Gutenberg block inserter showing block categories
2. Members Grid block with theme colors enabled
3. Product Grid block settings panel
4. Elementor widgets in the editor
5. BuddyPress carousel blocks on frontend

== Changelog ==

= 4.3.0 =
* New: Blog Index layouts - choose from Grid, List, Magazine, or Newspaper layouts for your blog and archive pages
* New: Magazine layout with multi-section content areas, each configurable with its own category and display style
* New: Newspaper layout featuring breaking news ticker, hero slider, and dual-column article sections
* New: Category Grid block to showcase your categories in a visual grid with post counts and images
* New: Ready-to-use magazine and blog patterns including News Dashboard, Magazine Homepage, Blog Editorial, and more
* New: Single post templates (Classic, Modern, Magazine, Minimal) with matching patterns for author bios, related posts, and share sections
* New: EDD (Easy Digital Downloads) blocks with enhanced checkout, trust badges, reviews, and product recommendations
* Enhanced: Posts Revolution block now includes an easy-to-use category picker with autocomplete search
* Enhanced: Posts Revolution automatically assigns categories across sections for quick setup
* Enhanced: Smarter post excerpts with proper word-boundary truncation
* Enhanced: WordPress version support headers added for better compatibility checks
* Fixed: PHP deprecated warning for str_replace() in Elementor Forums widget
* Fixed: PHPCS coding standards and internationalization improvements across blocks

= 4.2.1 =
* Fixed: Fatal TypeError in Elementor AJAX handler when source value is non-scalar (PHP 8+)
* Fixed: Missing array validation after json_decode in register_ajax_actions
* Fixed: Missing type checks in insert_inner_template for $_REQUEST values
* Fixed: Null pointer errors in license activation, deactivation, and check APIs
* Fixed: Null pointer in EDD updater cached version info
* Fixed: Type safety in AJAX login handler for non-string POST values
* Security: Replaced esc_attr with sanitize_text_field for source/template validation

= 4.2.0 =
* Major Release: All 45 Gutenberg blocks production-ready
* Added: AJAX handler for Mark as Read notifications in header bar
* Added: Architecture documentation (docs/architecture/)
* Fixed: Header bar dark mode icon visibility
* Fixed: Header bar cart and search UI improvements
* Fixed: Header bar friend list action button UI
* Fixed: CTA box hover secondary button UI
* Fixed: Forum box border styling
* Fixed: Post carousel adaptive height for Slick carousel
* Fixed: Member carousel Slides to Scroll functionality
* Fixed: Icon display after backend settings change
* Fixed: Block build process (npm run build:blocks)
* Updated: Complete block-to-widget mapping (98% coverage)
* Updated: Documentation with manifest files

= 4.0.2 =
* Added: "Use Theme Colors" toggle to all 45 Gutenberg blocks
* Added: 7 bonus blocks (counter, cta-box, divider, icon-box, mini-cart, social-icons, star-rating)
* Added: Theme color CSS variables support
* Fixed: Swiper initialization for all carousel blocks
* Fixed: Profile completion block compatibility
* Updated: Documentation with complete block mapping

= 4.0.1 =
* Fixed: Memory optimization in carousel initialization
* Fixed: Block category registration timing
* Fixed: Console warnings for deprecated block APIs

= 4.0.0 =
* New: 45 Gutenberg blocks with Elementor widget parity
* New: Block editor integration with ServerSideRender
* New: Centralized build system for blocks
* Changed: Minimum PHP version to 8.0
* Changed: Minimum WordPress version to 6.0

= 3.9.4 =
* Added: Member and group carousel arrows in logout mode
* Updated: Login widget user experience
* Fixed: Plugin conflicts with Elementor Pro

= 3.9.3 =
* Fix: Elementor Schemes to Globals migration

= 3.9.2 =
* Fix: Fatal error with Elementor compatibility

For full changelog, see the changelog.md file in the docs folder.

== Upgrade Notice ==

= 4.0.2 =
Major update: All 45 Gutenberg blocks now support theme color inheritance. Clear your cache after updating.

= 4.0.0 =
Breaking changes: PHP 8.0+ required. Full Gutenberg block parity with Elementor widgets.
