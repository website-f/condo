<?php

class PeepSoFieldSelectSingle extends PeepSoField {

	protected $field_meta_keys_extra = array(
			'select_options',
	);

    public static $order = 200;
	public static $admin_label='Select - Single';

	public $admin_can_add_delete_options = TRUE;


	public function __construct($post, $user_id)
	{
		$this->field_meta_keys = array_merge($this->field_meta_keys, $this->field_meta_keys_extra);
		parent::__construct($post, $user_id);

		$this->render_form_methods = array(
			'_render_form_select' => __('dropdown', 'peepso-core'),
			'_render_form_checklist' => __('checklist', 'peepso-core'),
		);

		$this->default_desc = __('Pick only one.', 'peepso-core');

		$this->el_class = 'ps-input';
	}

	// Renderers

	protected function _render($echo = false)
	{
		$options = $this->get_options();
		return ( (is_scalar($this->value) && isset($options[$this->value])  && (!$this->is_registration_page) )  ? __($options[$this->value], 'peepso-core') :  $this->_render_empty_fallback() );
	}

	protected function _render_input_args()
	{
		ob_start();

		echo ' name="'.esc_attr($this->input_args['name']).'"',
			' id="'.esc_attr($this->input_args['id']).'"',
			' data-id="'.esc_attr($this->id).'"';

		return ob_get_clean();
	}

	protected function _render_input_register_args()
	{
		ob_start();

		echo ' name="'.esc_attr($this->input_args['name']).'"',
			' id="'.esc_attr($this->input_args['id']).'"',
			' data-id="'.esc_attr($this->id).'"';

		if (!empty($this->el_class )) {
			echo ' class="'.esc_attr($this->el_class).'"';
		}

		return ob_get_clean();
	}

	protected function _render_form_select( )
	{
		$options = $this->get_options();

		if(!count($options)) {
			return FALSE;
		}

		ob_start();
		?>
		<div class="ps-input__wrapper">
			<select class="ps-input ps-input--sm ps-input--select" <?php echo wp_kses_post($this->_render_input_args() . $this->_render_required_args()); ?>>
				<option value=""><?php echo esc_attr(__('Select an option...', 'peepso-core')); ?></option>
				<?php
				foreach ($options as $k => $v) {

					$selected = '';

					if ($this->value == $k) {
						$selected = 'selected';
					}

					echo '<option '.esc_attr($selected).' value="'.esc_attr($k).'">'.esc_attr(__($v, 'peepso-core')).'</option>';
				}
				?>
			</select>
		</div>
		<?php

		$ret = ob_get_clean();
		return $ret;
	}

	protected function _render_form_select_register( )
	{
		$options = $this->get_options();

		if(!count($options)) {
			return FALSE;
		}

		ob_start();
		?>
		<select<?php echo wp_kses_post($this->_render_input_register_args() . $this->_render_required_args()); ?>>
			<option value=""><?php echo esc_attr(__('Select an option...', 'peepso-core')); ?></option>
			<?php
			foreach ($options as $k => $v) {

				$selected = '';

				if ($this->value == $k) {
					$selected = 'selected';
				}

				echo '<option '.esc_attr($selected).' value="'.esc_attr($k).'">'.esc_attr(__($v, 'peepso-core')).'</option>';
			}
			?>
		</select>
		<?php

		$ret = ob_get_clean();
		return $ret;
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

			if ($this->value == $k) {
				$checked = 'checked';
			}

			echo '<div class="ps-checkbox"><input class="ps-checkbox__input" name="'.'profile_field_' . esc_attr($this->id).'" type="radio" id="'.'profile_field_' . esc_attr($this->id).'-'.esc_attr($k).'" '.esc_attr($checked).' value="'.esc_attr($k).'" ' . wp_kses_post($this->_render_input_args() . $this->_render_required_args()) . ' /> <label class="ps-checkbox__label" for="'.'profile_field_' . esc_attr($this->id).'-'.esc_attr($k).'">'.esc_attr($v).'</label></div>';
		}

		return ob_get_clean();
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

			if ($this->value == $k) {
				$checked = 'checked';
			}

			$this->el_class = 'ps-radio';

			echo '<div class="ps-checkbox"><input class="ps-checkbox__input" name="'.'profile_field_' . esc_attr($this->id).'" type="radio" '.esc_attr($checked).' value="'.esc_attr($k).'" id="'.esc_attr($k).'" ' . wp_kses_post($this->_render_input_register_args() . $this->_render_required_args()) . ' /> <label class="ps-checkbox__label" for="'.esc_attr($k).'">'.esc_attr($v).'</label></div>';
		}

		return ob_get_clean();
	}

	// Utils
	public function get_options()
	{
		$options = $this->meta->select_options;
		if(!is_array($options)) {
			$options = array();
		}

		return $options;
	}
}
