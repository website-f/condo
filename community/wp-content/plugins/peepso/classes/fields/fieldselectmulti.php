<?php

class PeepSoFieldSelectMulti extends PeepSoFieldSelectSingle {

    public static $order = 300;
	public static $admin_label='Select - Multiple';

	public $data_type = 'array';

	public function __construct($post, $user_id)
	{
		$this->field_meta_keys = array_merge($this->field_meta_keys, $this->field_meta_keys_extra);
		parent::__construct($post, $user_id);

		$this->render_form_methods = array(
			'_render_form_checklist' => __('checklist', 'peepso-core'),
		);

		if(!is_array($this->value)) {
			$this->value = array();
		}
		$this->validation_methods[] = 'countmin';
		$this->validation_methods[] = 'countmax';

		$this->el_class = '';

		$this->default_desc = __('Select as many as you like.','peepso-core');
	}

	// Renderers

	protected function _render($echo = false)
	{
		$options = $this->get_options();

		if (!is_countable($this->value) || !count($this->value) || ($this->is_registration_page)) {
			return $this->_render_empty_fallback();
		}

		if(!count($options)) {
			return FALSE;
		}

		ob_start();

		foreach ($options as $k => $v) {

			if (is_array($this->value) && in_array($k, $this->value)) {
				echo '<span id="'.esc_attr($k).'" class="ps-profile__field-'.esc_attr($k).'">'.esc_attr($v).'</span>';
			}
		}

		return "<div class='ps-list ps-list--dots'>" . ob_get_clean() . "</div>";

	}

	protected function _render_input_checklist_args()
	{
		ob_start();

		echo ' name="'.esc_attr($this->input_args['name']).'"',
			' id="'.esc_attr($this->input_args['id']).'"',
			' data-id="'.esc_attr($this->id).'"';

		return ob_get_clean();
	}

	protected function _render_input_checklist_register_args()
	{
		ob_start();

		echo ' name="'.esc_attr($this->input_args['name']).'"',
			' data-id="'.esc_attr($this->id).'"';

		if (!empty($this->el_class )) {
			echo ' class="'.esc_attr($this->el_class).'"';
		}

		return ob_get_clean();
	}

	protected function _render_form_checklist()
	{
		$options = $this->get_options();

		if(!count($options)) {
			return FALSE;
		}

		ob_start();

		foreach ($options as $k => $v) {

			$checked = '';

			if (is_array($this->value) && in_array($k, $this->value)) {
				$checked = 'checked';
			}

			echo '<div class="ps-checkbox"><input class="ps-checkbox__input" id="' . 'profile_field_' . esc_attr($this->id) .'-' . esc_attr($k) .'" name="'.'profile_field_' . esc_attr($this->id) .'" type="checkbox" '.esc_attr($checked).' value="'.esc_attr($k).'" ' . wp_kses_post($this->_render_input_checklist_args()) . ' /> <label class="ps-checkbox__label" for="' . 'profile_field_' . esc_attr($this->id) .'-' . esc_attr($k) .'">'.esc_attr($v).'</label></div>';
		}

		$ret = ob_get_clean();
		return $ret;
	}

	protected function _render_form_checklist_register()
	{
		$options = $this->get_options();

		if(!count($options)) {
			return FALSE;
		}

		ob_start();

		foreach ($options as $k => $v) {

			$checked = '';

			if (is_array($this->value) && in_array($k, $this->value)) {
				$checked = 'checked';
			}

			// in registration page set name to `name[]` so we can get as an array
			echo '<div class="ps-checkbox"><input class="ps-checkbox__input" name="'.'profile_field_' . esc_attr($this->id).'[]" type="checkbox" '.esc_attr($checked).' value="'.esc_attr($k).'" id="'.esc_attr($k).'" ' . wp_kses_post($this->_render_input_checklist_register_args()). ' /> <label class="ps-checkbox__label" for="'.esc_attr($k).'">'.esc_attr($v).'</label></div>';
		}

		$ret = ob_get_clean();
		return $ret;
	}
}
