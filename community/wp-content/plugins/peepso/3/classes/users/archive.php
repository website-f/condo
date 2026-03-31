<?php

class PeepSo3_ArchiveUser {

	private static $instance;
	public $archive_uid = null;

	public static function get_instance()
	{
		return isset(self::$instance) ? self::$instance : self::$instance = new self;
	}

	private function __construct() {

		// If the option is disabled, do nothing
		if(!PeepSo::get_option_new('remove_inactive_users')) {
			return;
		}

		// Check that anon user was properly created, or create one
		add_action('init', function() {
			if(!$this->archive_uid) {
				$this->archive_uid = PeepSo::get_option_new('archive_uid', 0);
				if (!$this->archive_uid || !get_userdata($this->archive_uid)) {
					$this->create_archive_user();
				}
			}

			add_action('peepso_init', array(&$this, 'init'));
		});

		// Prevent admin from accidentally deleting our archive user
		add_filter('user_row_actions', function($a, $u){
			if (intval($u->ID) === intval($this->archive_uid)) {
				unset($a['delete']);
				unset($a['resetpassword']);
				unset($a['view']);
			}
			return $a;
		}, 10, 2);
	}

	public function init()
	{

	}

	private function create_archive_user() {

		// Prevent username collision

		$username_base = 'archive';
		$username = $username_base;
		$suffix = 1;

		while (username_exists($username)) {
			$username = $username_base . $suffix;
			$suffix++;
		}

		// Insert the new user

		$userdata = [];
		$userdata['user_login'] = $username;
		$userdata['user_nicename'] = $username;
		$userdata['first_name'] = 'Archive';
		$userdata['last_name'] = 'User';
		$userdata['display_name'] = $userdata['first_name'] . ' ' . $userdata['last_name'];
		$userdata['user_pass'] = wp_generate_password();

		$user_id = wp_insert_user($userdata);

		if (!is_wp_error($user_id)) {
			$this->archive_uid = $user_id;
            PeepSoConfigSettings::get_instance()->set_option('archive_uid', $user_id);
		} else {
			error_log('Failed to create archive user: ' . $user_id->get_error_message());
		}
	}
}

PeepSo3_ArchiveUser::get_instance();
