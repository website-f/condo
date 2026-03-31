<?php

if(!class_exists('PeepSo3_Search_Adapter')) {
    require_once(dirname(__FILE__) . '/search_adapter.php');
    //new PeepSoError('Autoload issue: PeepSo3_Search_Adapter not found ' . __FILE__);
}

if(!class_exists('PeepSo3_Search_Adapter_WP')) {
    require_once(dirname(__FILE__) . '/search_adapter_wp.php');
    //new PeepSoError('Autoload issue: PeepSo3_Search_Adapter_WP not found ' . __FILE__);
}

class PeepSo3_Search_Adapter_WPEM extends PeepSo3_Search_Adapter_WP {
    public function results() {

        $args=[
            's' => $this->query,
            'post_type' => $this->post_type,
            'posts_per_page' => $this->config['items_per_section'],
            'orderby' => 'date',
            'order' => 'desc',
        ];

        $the_query = new WP_Query($args);

        if ($the_query->have_posts()) {

            while ($the_query->have_posts()) {
                $the_query->the_post();

                $thumbnail  = get_event_thumbnail( get_the_ID(), 'post-thumbnail' ); 
                $this->results[] = $this->map_item([
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'text' => get_the_excerpt(),
                    'image' => $thumbnail,
                    'url' => get_permalink(),
                ]);
            }
        }

        wp_reset_postdata();

        return $this->results;
    }
}

add_action('init', function() {
    if(class_exists('WP_Event_Manager')) {
        new PeepSo3_Search_Adapter_WPEM(
            'event_listing',
            __('Events', 'peepso-core'),
            '/?post_type=event_listing&s=',
            "WP Events Manager"
        );
    }
});