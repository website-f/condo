<?php

abstract class PeepSo3_Search_Adapter {

    public $query;
    public $section = NULL;
    public $title = NULL;
    public $parent;
    public $title_frontend;
    public $url = NULL;
    public $order = 0;
    public $config;

    public $item = array(
        'id' => 0,
        'title' => '',
        'actor' => null,
        'text' => '',
        'url' => '',
        'image' => '',
        'meta' => [],
        'extras' => [],
    );

    public function __construct() {

        // Default config can be overriden by passing a config array to meta
        $this->config = [
            'items_per_section' => isset($_REQUEST['items_per_section']) ? intval($_REQUEST['items_per_section']) : PeepSo::get_option('peepso_search_limit_items_per_section'),
            'max_length_title' => isset($_REQUEST['max_length_title']) ? intval($_REQUEST['max_length_title']) : PeepSo::get_option('peepso_search_limit_length_title'),
            'max_length_text' => isset($_REQUEST['max_length_text']) ? intval($_REQUEST['max_length_text']) : PeepSo::get_option('peepso_search_limit_length_text'),
        ];

        $order = 'peepso_search_section_order_'.$this->section;
        $this->order = PeepSo::get_option_new($order);

        $title_frontend = 'peepso_search_section_title_'.$this->section;
        $this->title_frontend = PeepSo::get_option_new($title_frontend);

        // make sure order is applied immediately when savig config
        if(isset($_REQUEST[$order])) {
            $this->order = (int) $_REQUEST[$order];
        }

        if($this->order === NULL) {
            $this->order = 10;
        }

        add_filter('peepso_search_sections', function($sections) { $sections[$this->order.$this->section] = $this; ksort($sections,SORT_NUMERIC); return $sections; });

        if(!PeepSo::get_option('peepso_search_section_enable_'.$this->section)) {
            return;
        }

        add_filter('peepso_search_results', array(&$this, 'filter_peepso_search_results'), $this->order);
    }

    public function filter_peepso_search_results($results)
    {
        // skip if the filter is asking for a specific section
        if(!$this->filter_check_section($results)) {
            return $results;
        }

        $this->query = $results['meta']['query'];

        if(isset($results['meta']['config'])) {
           $this->config = array_merge($this->config,$results['meta']['config']);
        }

        $results['meta']['sections'][$this->section] = array(
            'title' => strlen($this->title_frontend) ? $this->title_frontend : $this->title,
            'url' => $this->url . $this->query,
            'order' => $this->order,
        );

        $this_results = [];
        if(strlen($this->query)) {
            $this_results = $this->results();
        }

        $results['results'][$this->section] = $this_results;

        if(!count($this_results) && !PeepSo::get_option_new('peepso_search_show_empty_sections')) {
            unset($results['results'][$this->section]);
            unset($results['sections'][$this->section]);
        }

        return $results;
    }

    public function filter_check_section($results) {
        return (!$results['meta']['section'] || $this->section == $results['meta']['section']);
    }

    public function map_item($item) {
        $item = array_merge($this->item, $item);
        foreach($item as $k=>$v) {
            if(!array_key_exists($k, $this->item)) {
                unset($item[$k]);
            }
        }

        if(isset($item['title']) && strlen($item['title']) > $this->config['max_length_title']) {
            $item['title'] = truncateHtml($item['title'], $this->config['max_length_title']);
        }

        if(isset($item['text']) && strlen($item['text']) > $this->config['max_length_text']) {
            $item['text'] = truncateHtml($item['text'], $this->config['max_length_text']);
        }

        $item['title'] = html_entity_decode($item['title']);
        $item['text'] = html_entity_decode($item['text']);

	    if (isset($_REQUEST['peepso_new_app'])) {
		    $item['title'] = strip_tags($item['title']);
		    $item['text'] = strip_tags($item['text']);

		    if (is_array($item['meta']) && count($item['meta'])) {
			    foreach ($item['meta'] as &$metaItem) {
				    if (isset($metaItem['title'])) {
					    $metaItem['title'] = strip_tags($metaItem['title']);
				    }
			    }
			    unset($metaItem); // Avoid reference issues
		    }
	    }


        if(!is_array($item['meta']) || !count($item['meta'])) {
            unset($item['meta']);
        }

        return $item;
    }

    abstract function results();
}
