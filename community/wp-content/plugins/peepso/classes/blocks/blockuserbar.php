<?php

add_filter('peepso_block_data_user-bar', function ($data) {
	$data['badgeos'] = class_exists('BadgeOS_PeepSo');
	return $data;
});

class PeepSoBlockUserBar extends PeepSoBlockAbstract
{
	protected function get_slug()
	{
		return 'user-bar';
	}

	protected function get_attributes()
	{
		$attributes = [
			'content_position' => ['type' => 'string', 'default' => 'left'],
			'guest_behavior' => ['type' => 'string', 'default' => 'hide'],
			'show_name' => ['type' => 'integer', 'default' => 1],
			'compact_mode' => ['type' => 'integer', 'default' => 1],
			'show_avatar' => ['type' => 'integer', 'default' => 1],
			'show_notifications' => ['type' => 'integer', 'default' => 1],
			'show_usermenu' => ['type' => 'integer', 'default' => 1],
			'show_logout' => ['type' => 'integer', 'default' => 0],
			'show_vip' => ['type' => 'integer', 'default' => 0],
			'show_badges' => ['type' => 'integer', 'default' => 0],
		];

		return apply_filters('peepso_block_attributes', $attributes, $this->get_slug());
	}

	protected function get_render_args($attributes, $preview)
	{
		$user_id = get_current_user_id();
		$user = PeepSoUser::get_instance($user_id);
		$links = apply_filters('peepso_navigation_profile', ['_user_id' => get_current_user_id()]);

		$toolbar = '';
		if (isset($attributes['show_notifications']) && intval($attributes['show_notifications'])) {
			$toolbar = $this->toolbar();
		}

		return [
			'user_id' => $user_id,
			'user' => $user,
			'toolbar' => $toolbar,
			'links' => $links,
		];
	}

	public function render_component($attributes)
	{
		if (!is_user_logged_in()) {
			if ('hide' === $attributes['guest_behavior']) {
				return $this->widget_empty_content();
			}
		}

		return parent::render_component($attributes);
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

		ob_start();

		foreach ($toolbar as $item => $data) { ?>
 			<div class="pso-notif <?php echo esc_attr($data['class']); ?>">
 				<a class="pso-notif__toggle" href="<?php echo esc_url($data['href']); ?>" 
 						title="<?php echo esc_attr($data['label']); ?>">
 					<i class="<?php echo esc_attr($data['icon']); ?>"></i>
 					<span class="pso-badge pso-badge--float pso-badge--primary pso-notif__badge js-counter ps-js-counter"
						><?php echo esc_attr($data['count'] > 0 ? $data['count'] : ''); ?></span>
 				</a>
 			</div>
		<?php }

		$html = str_replace(PHP_EOL, '', ob_get_clean());

		return $html;
	}
}
