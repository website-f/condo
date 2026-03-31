<?php
$PeepSoLocation = PeepSoLocation::get_instance();

if($PeepSoLocation->is_enabled) {
    class PeepSoFieldLocation extends PeepSoField
    {
        public static $order = 500;
        public static $admin_label = 'Location';

        public function __construct($post, $user_id)
        {
            parent::__construct($post, $user_id);

            $this->render_methods['_render_link_location'] = __('clickable link', 'peepso-core');
            $this->render_form_methods['_render_map_selector'] = __('Map selector', 'peepso-core');

            $this->default_desc = __('Share your location', 'peepso-core');
            $this->data_type = 'array';
        }

        protected function _render_link_location()
        {
            if (empty($this->value) || ($this->is_registration_page)) {
                return $this->_render_empty_fallback();
            }

            $loc = $this->value;
            if (isset($loc['name']) && isset($loc['latitude']) && isset($loc['longitude'])) {
                $lat = $loc['latitude'] ?? null;
                $lng = $loc['longitude'] ?? null;
                $viewport = $loc['viewport'] ?? null;
                $name = $loc['name'] ?? '';

                $data_location = json_encode($lat && $lng ? ['lat' => $lat, 'lng' => $lng] : null);
                $data_viewport = json_encode($viewport);
                $data_label = json_encode($name ? $name : null);
                $onclick = "peepso.location.showMap($data_location, $data_viewport, $data_label)";

                ob_start();

                ?><a href="#" title="<?php echo esc_attr($name); ?>" onclick="<?php echo esc_attr($onclick ?? '') ?>; return false;">
                    <i class="gcis gci-map-marker-alt"></i>
                    <span><?php echo esc_attr($name); ?></span>
                </a><?php

                $html = ob_get_clean();
                return $html;
            }

            return $this->_render_empty_fallback();
        }

        protected function _render_map_selector_args()
        {
            ob_start();

            echo ' name="' . esc_attr($this->input_args['name']) . '"',
                ' id="' . esc_attr($this->input_args['id']) . '"',
                ' data-id="' . esc_attr($this->id) . '"',
            ' class="ps-input ps-input--sm ps-js-field-location"',
            ' role="textbox" aria-autocomplete="list" aria-haspopup="true"',
            ' placeholder="', esc_attr(__('Enter location name', 'peepso-core')), '"';

            return ob_get_clean();
        }

        protected function _render()
        {
            $name = '';

            if (!empty($this->value) && !$this->is_registration_page) {
                $loc = $this->value;
                $name = $loc['name'];
            }

            if (!strlen($name) || ($this->is_registration_page)) {
                return $this->_render_empty_fallback();
            }

            return esc_attr($name);
        }

        protected function _render_map_selector()
        {
            $name = '';
            $latitude = '';
            $longitude = '';

            if (!empty($this->value) && !$this->is_registration_page) {
                $loc = $this->value;
                $name = $loc['name'];
                $latitude = $loc['latitude'];
                $longitude = $loc['longitude'];
            }

            $ret = '<input type="text" value="' . esc_attr($name) . '"'
                . $this->_render_map_selector_args()
                . ' data-location="' . esc_attr($name) . '"'
                . ' data-latitude="' . esc_attr($latitude) . '"'
                . ' data-longitude="' . esc_attr($longitude) . '"'
                . $this->_render_required_args().'>';

            return $ret;
        }

        protected function _render_map_selector_register_args()
        {
            ob_start();

            $class = '';
            if (!empty($this->el_class)) {
                $class = ' ' . $this->el_class;
            }

            echo ' name="' . esc_attr($this->input_args['name']) . '"',
                ' id="' . esc_attr($this->input_args['id']) . '"',
                ' data-id="' . esc_attr($this->id) . '"',
                ' class="ps-input ps-js-field-location' . esc_attr($class) . '"';


            return ob_get_clean();
        }

        protected function _render_map_selector_register()
        {
            $name = '';
            $latitude = '';
            $longitude = '';

            if (!empty($this->value) && !$this->is_registration_page) {
                $loc = $this->value;
                $name = $loc['name'];
                $latitude = $loc['latitude'];
                $longitude = $loc['longitude'];
            }

            $ret = '<input type="text" value="' . esc_attr($name) . '"'
                . $this->_render_map_selector_args()
                . ' data-location="' . esc_attr($name) . '"'
                . ' data-latitude="' . esc_attr($latitude) . '"'
                . ' data-longitude="' . esc_attr($longitude) . '"'
                . $this->_render_required_args().'>';

            return $ret;
        }

        public function save($value, $validate_only = FALSE) {
            // #3881: Fix backslashes in JSON string are stripped by PeepSoField::save method, makes it invalid.
            $value = addslashes( html_entity_decode( $value ) );

            return parent::save($value, $validate_only);
        }

    }
}
