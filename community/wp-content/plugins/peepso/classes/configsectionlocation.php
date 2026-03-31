<?php

class PeepSoConfigSectionLocation extends PeepSoConfigSectionAbstract
{
    // Builds the groups array
    public function register_config_groups()
    {
        $this->context='left';
        $this->location();

        $this->context='right';
        //$this->user_seach();
    }

    private function location()
    {

        // Enable Location
        $this->set_field(
            'location_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_field(
            'separator_google_maps',
            __('Google Maps', 'peepso-core'),
            'separator'
        );

        $this->set_field(
            'location_gmap_intro',
            __('A Google Maps API key is mandatory to enable the core functionality of location suggestions, address autocomplete, and validation. Without it, these features cannot connect to Google\'s services and will not work properly.', 'peepso-core'),
            'message'
        );

        ob_start();
        echo sprintf(
            __('Get your Google Maps API key <a href="%s" target="_blank">here</a> and enable the following services for it:', 'peepso-core'),
            'https://developers.google.com/maps/documentation/places/web-service/get-api-key'
        ) . '<br>';
        echo '- ' . sprintf(
            __('Required: <a href="%s" target="_blank">Maps JavaScript API</a>', 'peepso-core'),
            'https://console.cloud.google.com/apis/library/maps-backend.googleapis.com'
        ) . '<br>';
        echo '- ' . sprintf(
            __('Required: <a href="%1$s" target="_blank">Places API</a> or <a href="%2$s" target="_blank">Places API (New)</a>', 'peepso-core'),
            'https://console.cloud.google.com/apis/library/places-backend.googleapis.com',
            'https://console.cloud.google.com/apis/library/places.googleapis.com'
        ) . '<br>';
        echo '- ' . sprintf(
            __('Optional: <a href="%s" target="_blank">Geolocation API</a>', 'peepso-core'),
            'https://console.cloud.google.com/apis/library/geolocation.googleapis.com'
        );
        $this->args('descript', ob_get_clean());

        $this->set_field(
            'location_gmap_api_key',
            __('API key', 'peepso-core'),
            'text'
        );

        $this->args(
            'descript',
            sprintf(
                __('Google is <a href="%s" target="_blank">retiring some legacy Maps APIs</a>. Enable this setting to remove deprecation warnings and ensure your application is future-compatible.', 'peepso-core'),
                'https://developers.google.com/maps/legacy'
            )
        );

        $this->set_field(
            'location_gmap_api_new',
            __('Use new API', 'peepso-core'),
            'yesno_switch'
        );

        $this->args(
            'descript',
            sprintf(
                __('The new Google Maps APIs require a Map ID for correct rendering and customization. Get and configure your Map ID <a href="%s" target="_blank">here</a>.', 'peepso-core'),
                'https://developers.google.com/maps/documentation/get-map-id',
            )
        );

        $this->set_field(
            'location_gmap_api_map_id',
            __('Map ID', 'peepso-core'),
            'text'
        );

        $this->set_group(
            'location',
            __('Location', 'peepso-core')
        );
    }

    private function user_seach()
    {
        $this->set_field(
            'location_user_search_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->args('options',['mi'=> __('Miles','peepso-core'),'km'=> __('Kilometres','peepso-core')]);
        $this->set_field(
            'location_user_search_units',
            __('Default units', 'peepso-core'),
            'select'
        );

        $PeepSoUser = PeepSoUser::get_instance(0);
        $profile_fields = new PeepSoProfileFields($PeepSoUser);
        $fields = $profile_fields->load_fields();

        $options = [0=>'-- '.__('Select a field','peepso-core').' --'];

        foreach($fields as $id=>$field) {
            // Remove fields that are not of type Location
            if(!$field instanceof PeepSoFieldLocation) {
                continue;
            }

            $label = $field->title . " (ID: {$field->id})";
            $options[$field->id] = $label;
        }


        $this->args('options', $options);
        $this->set_field(
            'location_user_search_field',
            __('Profile field', 'peepso-core'),
            'select'
        );

        $this->set_group(
            'user_search',
            __('User search', 'peepso-core')
        );
    }
}
?>
