<?php

abstract class PeepSoBlockAbstract
{
	abstract protected function get_slug();

	public function __construct()
	{
		$slug = $this->get_slug();

		wp_register_script(
			"peepso-block-{$slug}-editor",
			$this->get_block_editor($slug),
			['wp-blocks', 'wp-i18n', 'wp-element'],
			PeepSo::PLUGIN_VERSION,
			true,
		);

		$attributes = array_merge($this->get_attributes(), [
			'__psBlockId' => ['type' => 'string', 'default' => ''],
			'__psSidebarId' => ['type' => 'string', 'default' => ''],
			'__psUniqueId' => ['type' => 'string', 'default' => ''], // for mayfly purposes
		]);

		$dataKey = implode('', array_map('ucfirst', explode('-', $slug)));
		$dataKey = "peepsoBlock{$dataKey}EditorData";
		$dataValue = apply_filters("peepso_block_data_{$slug}", ['attributes' => $attributes]);
		wp_localize_script("peepso-block-{$slug}-editor", $dataKey, $dataValue);

		register_block_type("peepso/{$slug}", [
			'attributes' => $attributes,
			'editor_script' => "peepso-block-{$slug}-editor",
			'render_callback' => [$this, 'render_component'],
		]);
	}

	protected function is_admin_page()
	{
		$is_admin_page = is_admin();

		// The is_admin() will return false when using the block editor.
		// https://developer.wordpress.org/reference/functions/is_admin/#comment-4939
		if (!$is_admin_page) {
			$is_admin_page = defined('REST_REQUEST') && REST_REQUEST;
		}

		return $is_admin_page;
	}

	protected function get_block_editor($slug)
	{
		return PeepSo::get_asset("js/blocks/{$slug}-editor.js");
	}

	protected function get_attributes()
	{
		return [];
	}

	protected function get_render_args($attributes, $preview)
	{
		return [];
	}

	public function render_component($attributes)
	{
		$preview = isset($_GET['context']) && 'edit' === $_GET['context'];
		$widget_instance = $this->widget_instance($attributes);

		$args = array_merge(
			[
				'attributes' => $attributes,
				'preview' => $preview,
				'widget_instance' => $widget_instance,
			],
			$this->get_render_args($attributes, $preview),
		);

		if ($this->maybe_hide($args)) {
			$widget_instance = $this->widget_instance($attributes);
			return $widget_instance && !$this->is_admin_page() ? $this->widget_empty_content() : '';
		}

		$html = PeepSoTemplate::exec_template('blocks', $this->get_slug(), $args, true);

		if ($preview && trim($html)) {
			$class = $widget_instance ? 'ps-widget--preview' : '';
			$html = sprintf(
				'<div class="%2$s" style="position:relative"> %1$s <div class="ps-widget__disabler" style="position:absolute; top:0; left:0; right:0; bottom:0"></div></div>',
				$html,
				$class,
			);
		}

		return $html;
	}

	public function maybe_hide($args)
	{
		return false;
	}

	/**
	 * For backward compatibility when the block is rendered as a widget, inside a "sidebar".
	 */
	protected function widget_instance($attributes)
	{
		if (isset($attributes['__psSidebarId'])) {
			global $wp_registered_sidebars;

			$sidebar_id = $attributes['__psSidebarId'];
			if (isset($wp_registered_sidebars[$sidebar_id])) {
				return $wp_registered_sidebars[$sidebar_id];
			}
		}

		return null;
	}

	/**
	 * Cannot remove enclosing `before_widget` wrapper from here,
	 * so we do it on the client side as the last resort.
	 */
	protected function widget_empty_content()
	{
		return sprintf(
			'<div data-widget-empty id="%1$s"><script>try {
				const div = document.getElementById("%1$s");
				const par = div.closest(".widget_peepso");
				par ? par.remove() : div.remove();
			} catch (e) {}</script></div>',
			uniqid('ps_widget_empty_'),
		);
	}
}

add_filter(
	'widget_block_dynamic_classname',
	function ($classname, $block_name) {
		if (0 === strpos($block_name, 'peepso/')) {
			$classname .= ' widget_peepso';
		}

		return $classname;
	},
	10,
	2,
);
