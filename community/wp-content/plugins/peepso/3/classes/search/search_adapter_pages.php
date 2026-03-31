<?php

if(!class_exists('PeepSo3_Search_Adapter')) {
	require_once(dirname(__FILE__) . '/search_adapter.php');
	//new PeepSoError('Autoload issue: PeepSo3_Search_Adapter not found ' . __FILE__);
}

class PeepSo3_Search_Adapter_Pages  extends PeepSo3_Search_Adapter {

	public function __construct()
	{
		$this->section = 'pages';
		$this->title = __('Pages', 'peepso-core');
        $this->parent="PeepSo";
		$this->url = PeepSo::get_page('pages').'?category=0&filter=';

		parent::__construct();
	}

	public function results() {

		// look for $this->>query

		$PeepSoPages = new PeepSoPages();
		$results = [];

		$items = $PeepSoPages->get_pages(0, $this->config['items_per_section'], '','', $this->query);


		if(count($items)) {
			foreach($items as $PeepSoPage) {

				$meta = [
					[
						'context' => 'membercount',
						'icon' => 'gcis gci-users',
						'title' => sprintf(_n('%s follower', '%s followers', $PeepSoPage->members_count, 'pageso'), number_format_i18n($PeepSoPage->members_count)),
					]
				];

				$membership = NULL;

				$member = FALSE;
				if(get_current_user_id()) {
					$PeepSoPageUser = new PeepSoPageUser($PeepSoPage->get('id'));
					$member = $PeepSoPageUser->is_member;
					$role = $PeepSoPageUser->role_l8n;
					if($member) {
						$membership = [
							'context' => 'membership',
							'icon' => 'pso-i-user-check',
							'title' => ucfirst($role),
						];
					}
				}

				if($membership) {
					$meta[]=$membership;
				}

				$item = [
					'id' => $PeepSoPage->id,
					'title' => $PeepSoPage->get('name'),
					'text' => $PeepSoPage->get('description'),
					'meta' => $meta,
					'url' => $PeepSoPage->get('url'),
					'image' => $PeepSoPage->get('avatar_url'),
				];

				$results[] = $this->map_item(
					$item
				);
			}
		}

		return $results;
	}

}

add_action('init', function() {
	if(class_exists('PeepSoPagesPlugin')) {
		new PeepSo3_Search_Adapter_Pages();
	}
});
