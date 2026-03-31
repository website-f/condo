<?php

class PeepSoBlockProfile extends PeepSoBlockAbstract
{
	protected function get_slug()
	{
		return 'profile';
	}

	protected function get_attributes()
	{
		$attributes = [
			'title' => ['type' => 'string', 'default' => ''],
			'guest_behavior' => ['type' => 'string', 'default' => 'login'],
			'show_notifications' => ['type' => 'integer', 'default' => 1],
			'show_community_links' => ['type' => 'integer', 'default' => 0],
			'show_cover' => ['type' => 'integer', 'default' => 0],
			'show_in_profile' => ['type' => 'integer', 'default' => 3],
			'timestamp' => ['type' => 'integer', 'default' => 0],
		];

		return apply_filters('peepso_block_attributes', $attributes, $this->get_slug());
	}

	protected function get_render_args($attributes, $preview)
	{
		$user_id = get_current_user_id();
		$user = PeepSoUser::get_instance($user_id);
		$toolbar = $this->toolbar();
		$links = apply_filters('peepso_navigation_profile', ['_user_id' => get_current_user_id()]);
		$community_links = apply_filters('peepso_navigation', []);
		unset($community_links['profile']);

		return [
			'user_id' => $user_id,
			'user' => $user,
			'toolbar' => $toolbar,
			'links' => $links,
			'community_links' => $community_links,
		];
	}

	public function render_component($attributes)
	{
		if ($this->is_admin_page()) {
			return parent::render_component($attributes);
		}

		if (!is_user_logged_in()) {
			if ('hide' === $attributes['guest_behavior']) {
				return $this->widget_empty_content();
			}
		}

		// Hide from profile page?
		$show_in_profile = intval($attributes['show_in_profile']);
		if ($this->is_profile_page() && $show_in_profile < 3) {
			if (0 === $show_in_profile) {
				return $this->widget_empty_content();
			}

			$PeepSoProfile = PeepSoProfileShortcode::get_instance();
			$view_id = $PeepSoProfile->get_view_user_id();
			$user_id = get_current_user_id();

			// 1 = show on "mine" and hide on "theirs"
			if (1 === $show_in_profile && $view_id != $user_id) {
				return $this->widget_empty_content();
			}

			// 2 = hide on "mine" and show on "theirs"
			if (2 === $show_in_profile && $view_id == $user_id) {
				return $this->widget_empty_content();
			}
		}

		return parent::render_component($attributes);
	}

	private function is_profile_page()
	{
		$profile_page = false;

		global $post;
		if ($post instanceof WP_Post) {
			$profile_page =
				$post->post_type == 'page' && stristr($post->post_content, '[peepso_profile');

			// https://gitlab.com/PeepSo/PeepSo/-/issues/4753
			if (!$profile_page) {
				global $wp_query;

				if (
					$wp_query instanceof WP_Query &&
					isset($wp_query->post) &&
					$wp_query->post instanceof WP_Post &&
					stristr($wp_query->post->post_content, '[peepso_profile')
				) {
					$profile_page = true;
				}
			}

			if (!$profile_page && $post->post_type === 'peepso-post') {
				$url = PeepSoUrlSegments::get_instance();
				if ($url->_shortcode === 'peepso_profile') {
					$profile_page = true;
				}
			}
		}

		return $profile_page;
	}

	private function toolbar()
	{
		$note = PeepSoNotifications::get_instance();
		$unread_notes = $note->get_unread_count_for_user();

		$toolbar = [
			'notifications' => [
				'href' => PeepSo::get_page('notifications'),
				'icon' => 'pso-i-bell',
				'class' => 'pso-notif--general dropdown-notification ps-js-notifications',
				'title' => __('Pending Notifications', 'peepso-core'),
				'count' => $unread_notes,
				'order' => 100,
			],
		];

		$toolbar = PeepSoGeneral::get_instance()->get_navigation('notifications');
		$new_icon_map = ['pso-i-messages' => 'pso-i-envelope'];

		ob_start();

		foreach ($toolbar as $item => $data) { ?>
			<div class="pso-notif <?php echo esc_attr($data['class']); ?>">
				<a href="<?php echo esc_url($data['href']); ?>" class="pso-btn pso-btn--ui pso-tip" 
						aria-label="<?php echo esc_attr($data['label']); ?>">
					<i class="<?php echo esc_attr($new_icon_map[$data['icon']] ?? $data['icon']); ?>"></i>
					<span class="pso-badge pso-badge--float pso-badge--primary js-counter ps-js-counter" 
						><?php echo esc_html($data['count'] > 0 ? $data['count'] : ''); ?></span>
				</a>
			</div>
		<?php }

		$html = str_replace(PHP_EOL, '', ob_get_clean());

		return $html;
	}
}
