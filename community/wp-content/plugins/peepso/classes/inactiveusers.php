<?php

class PeepSoInactiveUsers
{
	public static function process_remove_inactive_users()
	{
		if(1 != PeepSo::get_option('remove_inactive_users', 0)) {
			return;
		}

		// Fetch all inactive users
		$inactive_users = PeepSoInactiveUsers::get_inactive_users();
		if ($inactive_users) {
			require_once(ABSPATH . 'wp-admin/includes/user.php');
			foreach ($inactive_users as $user) {
				// Skip anonymous users
				if(PeepSo3_Anon::get_instance()->anon_id == $user->ID) {
					continue;
				}

				$archive_uid = intval(PeepSo::get_option_new('archive_uid', 0));
				if (intval($user->ID) !== $archive_uid) {
					// Move / Delete Related Activity to Archive User
					PeepSoInactiveUsers::move_activity($user->ID, $archive_uid);
					PeepSoInactiveUsers::move_comments($user->ID, $archive_uid);
					PeepSoInactiveUsers::remove_likes($user->ID, $archive_uid);
					PeepSoInactiveUsers::remove_polls($user->ID, $archive_uid);
					PeepSoInactiveUsers::move_postbackgrounds($user->ID, $archive_uid);
					PeepSoInactiveUsers::move_notification($user->ID, $archive_uid);
					PeepSoInactiveUsers::remove_friends($user->ID, $archive_uid);
					PeepSoInactiveUsers::move_message($user->ID, $archive_uid);
					PeepSoInactiveUsers::remove_groups($user->ID, $archive_uid);
					PeepSoInactiveUsers::remove_pages($user->ID, $archive_uid);
					PeepSoInactiveUsers::move_photos($user->ID, $archive_uid);
					PeepSoInactiveUsers::move_videos($user->ID, $archive_uid);
					PeepSoInactiveUsers::move_files($user->ID, $archive_uid);

					// Finally delete the user
					wp_delete_user($user->ID);
				}
			}
		}
	}

	public static function get_inactive_users()
	{
		global $wpdb;

		$period = PeepSo::get_option_new('remove_inactive_users_period', 30);
		$date = date('Y-m-d H:i:s', strtotime("-$period days"));

		$results = $wpdb->get_results('
			SELECT u.ID
			FROM ' . $wpdb->users . ' AS u LEFT JOIN `' . $wpdb->prefix . PeepSoUser::TABLE . '` `acc`
				ON `acc`.`usr_id` = `u`.`ID`
			WHERE (`acc`.`usr_last_activity` IS NULL
				OR `acc`.`usr_last_activity` < \'' . $date . '\')
				AND `acc`.`usr_role` <> \'admin\'
			LIMIT 5
		');

		return $results;
	}

	public static function move_activity($user_id, $archive_uid)
	{
		global $wpdb;

		// Move Activity to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '`
			SET `act_owner_id` = ' . $archive_uid . '
			WHERE `act_owner_id` = ' . $user_id . '
				AND `act_module_id` = 1
		');

		// Move Posts to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->posts . '`
			SET `post_author` = ' . $archive_uid . '
			WHERE `post_type` = \'' . PeepSoActivityStream::CPT_POST . '\'
				AND `post_author` = ' . $user_id . '
		');

		// Delete followers from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoActivity::ACTIVITY_FOLLOWERS_TABLE_NAME . '`
			WHERE `user_id` = ' . $user_id . '
		');

		// Delete views from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoActivityRanking::TABLE_VIEWS . '`
			WHERE `user_id` = ' . $user_id . '
		');

		// Delete read from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoActivityRanking::TABLE_READ . '`
			WHERE `user_id` = ' . $user_id . '
		');

		// Delete saved posts
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoActivity::SAVED_POSTS_TABLE_NAME . '`
			WHERE `user_id` = ' . $user_id . '
		');

		// Delete followers from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoUserFollower::TABLE . '`
			WHERE `uf_passive_user_id` = ' . $user_id . ' 
				OR `uf_active_user_id` = ' . $user_id . '
		');
	}

	public static function move_comments($user_id, $archive_uid)
	{
		global $wpdb;

		// Move Posts to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->posts . '`
			SET `post_author` = ' . $archive_uid . '
			WHERE `post_type` = \'' . PeepSoActivityStream::CPT_COMMENT . '\'
				AND `post_author` = ' . $user_id . '
		');
	}

	public static function remove_likes($user_id, $archive_uid)
	{
		global $wpdb;

		// Delete likes from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoLike::TABLE . '`
			WHERE `like_user_id` = ' . $user_id . '
		');

		// Delete reactions from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoReactionsModel::TABLE . '`
			WHERE `reaction_user_id` = ' . $user_id . '
		');
	}

	public static function remove_polls($user_id, $archive_uid)
	{
		global $wpdb;

		// Delete polls from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoPollsModel::TABLE . '`
			WHERE `pu_user_id` = ' . $user_id . '
		');

		// Move polls ownership to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '`
			SET `act_owner_id` = ' . $archive_uid . '
			WHERE `act_owner_id` = ' . $user_id . '
				AND `act_module_id` = ' . PeepSoPolls::MODULE_ID . '
		');

		$res = $wpdb->get_results('
			SELECT `act_external_id` FROM `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '`
				WHERE `act_module_id` = ' . PeepSoPolls::MODULE_ID . '
		');

		// Update total options voted.
		$polls_model = new PeepSoPollsModel();
		foreach ( $res as $polls ) {
			$poll_id = $polls->act_external_id;
			$poll_answers = $polls_model->get_polls_answers($poll_id);

			$options = unserialize(get_post_meta($poll_id, 'select_options', TRUE));
			foreach ($options as $key => $value) {
				$total = 0;
				foreach($poll_answers as $answers) {
					if ($answers->pu_value == $key) {
						$total++;
					}
				}
				$options[$key]['total_user_poll'] = $total;
			}
			update_post_meta($poll_id, 'select_options', serialize($options));

			// Update total users voted.
			$total_user_poll = get_post_meta($poll_id, 'total_user_poll', TRUE);
			$total_user_poll = 0;
			foreach($options as $key => $value) {
				$total_user_poll += $options[$key]['total_user_poll'];
			}

			update_post_meta($poll_id, 'total_user_poll', $total_user_poll);
		}
	}

	public static function move_postbackgrounds($user_id, $archive_uid)
	{
		global $wpdb;

		// Move polls ownership to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '`
			SET `act_owner_id` = ' . $archive_uid . '
			WHERE `act_owner_id` = ' . $user_id . '
				AND `act_module_id` = ' . PeepSoPostBackgrounds::MODULE_ID . '
		');
	}

	public static function move_notification($user_id, $archive_uid)
	{
		global $wpdb;

		// Move Notifications to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoNotifications::TABLE . '`
			SET `not_user_id` = ' . $archive_uid . '
			WHERE `not_user_id` = ' . $user_id . '
		');

		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoNotifications::TABLE . '`
			SET `not_from_user_id` = ' . $archive_uid . '
			WHERE `not_from_user_id` = ' . $user_id . '
		');
	}

	public static function remove_friends($user_id, $archive_uid)
	{
		if (!class_exists('PeepSoFriendsPlugin')) {
			return;
		}

		global $wpdb;

		// Delete friends from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoFriendsModel::TABLE . '`
			WHERE `fnd_user_id` = ' . $user_id . ' 
				OR `fnd_friend_id` = ' . $user_id . '
		');

		// Delete friends requests from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoFriendsRequests::TABLE . '`
			WHERE `freq_user_id` = ' . $user_id . '
				OR `freq_friend_id` = ' . $user_id . '
		');

		// Delete friends cache from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . 'peepso_friends_cache`
			WHERE `user_id` = ' . $user_id . '
				OR `friend_id` = ' . $user_id . '
		');
	}

	public static function move_message($user_id, $archive_uid)
	{
		if (!class_exists('PeepSoMessagesPlugin')) {
			return;
		}

		global $wpdb;

		// Move Messages to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoMessageRecipients::TABLE . '`
			SET `mrec_user_id` = ' . $archive_uid . '
			WHERE `mrec_user_id` = ' . $user_id . '
		');

		// Move Message Participants to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoMessageParticipants::TABLE . '`
			SET `mpart_user_id` = ' . $archive_uid . '
			WHERE `mpart_user_id` = ' . $user_id . '
		');

		// Move Message Activity to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '`
			SET `act_owner_id` = ' . $archive_uid . '
			WHERE `act_module_id` = ' . PeepSoMessagesPlugin::MODULE_ID . '
				AND `act_owner_id` = ' . $user_id . '
		');

		// Move Message Post Author to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->posts . '`
			SET `post_author` = ' . $archive_uid . '
			WHERE `post_type` = \'' . PeepSoMessagesPlugin::CPT_MESSAGE . '\'
				AND `post_author` = ' . $user_id . '
		');
	}

	public static function remove_groups($user_id, $archive_uid)
	{
		if (!class_exists('PeepSoGroupsPlugin')) {
			return;
		}

		global $wpdb;

		// Delete groups from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoGroupFollowers::TABLE . '`
			WHERE `gf_user_id` = ' . $user_id . '
		');

		$list_groups = $wpdb->get_results('
			SELECT * FROM `' . $wpdb->prefix . PeepSoGroupUsers::TABLE . '`
			WHERE `gm_user_id` = ' . $user_id . '
		');

		foreach ($list_groups as $group) {
			if ($group->gm_user_status != 'member_owner'){
				// Delete groups from inactive user
				$wpdb->query('
					DELETE FROM `' . $wpdb->prefix . PeepSoGroupUsers::TABLE . '`
					WHERE `gm_user_id` = ' . $user_id . '
						AND `gm_user_status` <> \'member_owner\'
				');
			} else if($group->gm_user_status == 'member_owner') {
				// Update Group ownership
				$wpdb->query('
					UPDATE `' . $wpdb->prefix . PeepSoGroupUsers::TABLE . '`
					SET `gm_user_id` = ' . $archive_uid . '
					WHERE `gm_user_id` = ' . $user_id . '
						AND `gm_user_status` = \'member_owner\'
				');
			}

			$PeepSoGroupUsers = new PeepSoGroupUsers($group->gm_group_id);
			$PeepSoGroupUsers->update_members_count();
		}

		// Update Group Followers
		$wpdb->query('
			UPDATE `' . $wpdb->posts . '`
			SET `post_author` = ' . $archive_uid . '
			WHERE `post_type` = \'' . PeepSoGroup::POST_TYPE . '\'
				AND `post_author` = ' . $user_id . '
		');

		// Move Activity to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '`
			SET `act_owner_id` = ' . $archive_uid . '
			WHERE `act_owner_id` = ' . $user_id . '
				AND `act_module_id` = ' . PeepSoGroupsPlugin::MODULE_ID . '
		');
	}

	public static function remove_pages($user_id, $archive_uid)
	{
		if (!class_exists('PeepSoPagesPlugin')) {
			return;
		}

		global $wpdb;

		// Delete pages from inactive user
		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoPageFollowers::TABLE . '`
			WHERE `pf_user_id` = ' . $user_id . '
		');

		$list_pages = $wpdb->get_results('
			SELECT * FROM `' . $wpdb->prefix . PeepSoPageUsers::TABLE . '`
			WHERE `pm_user_id` = ' . $user_id . '
		');

		foreach ($list_pages as $page) {
			if ($page->pm_user_status != 'member_owner'){
				// Delete pages from inactive user
				$wpdb->query('
					DELETE FROM `' . $wpdb->prefix . PeepSoPageUsers::TABLE . '`
					WHERE `pm_user_id` = ' . $user_id . '
						AND `pm_user_status` <> \'member_owner\'
				');
			} else if($page->pm_user_status == 'member_owner') {
				// Update Page ownership
				$wpdb->query('
					UPDATE `' . $wpdb->prefix . PeepSoPageUsers::TABLE . '`
					SET `pm_user_id` = ' . $archive_uid . '
					WHERE `pm_user_id` = ' . $user_id . '
						AND `pm_user_status` = \'member_owner\'
				');
			}

			$PeepSoPagesUsers = new PeepSoPageUsers($page->pm_page_id);
			$PeepSoPagesUsers->update_members_count();
		}

		// Delete pages from inactive user
		$wpdb->query('
			UPDATE `' . $wpdb->posts . '`
			SET `post_author` = ' . $archive_uid . '
			WHERE `post_type` = \'' . PeepSoPage::POST_TYPE . '\'
				AND `post_author` = ' . $user_id . '
		');

		// Move Activity to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '`
			SET `act_owner_id` = ' . $archive_uid . '
			WHERE `act_owner_id` = ' . $user_id . '
				AND `act_module_id` = ' . PeepSoPagesPlugin::MODULE_ID . '
		');
	}

	public static function move_photos($user_id, $archive_uid)
	{
		if (!class_exists('PeepSoSharePhotos')) {
			return;
		}

		$user = PeepSoUser::get_instance($user_id);
		$archive_user = PeepSoUser::get_instance($archive_uid);

		global $wpdb;

		$photo_user_dir = $user->get_image_dir() . DIRECTORY_SEPARATOR . 'photos';
		$photo_archive_user_dir = $archive_user->get_image_dir() . DIRECTORY_SEPARATOR . 'photos';
		self::recursive_move($photo_user_dir, $photo_archive_user_dir);

		// Move photos to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoPhotosModel::TABLE . '`
			SET `pho_owner_id` = ' . $archive_uid . '
			WHERE `pho_owner_id` = ' . $user_id . '
				AND (`pho_album_id` = 0
				OR `pho_module_id` <> 0)
		');

		// Setup album archive user
		$photos = PeepSoSharePhotos::get_instance();
		$photos->create_album(PeepSoUser::get_instance($archive_uid));

		$list_albums = $wpdb->get_results('
			SELECT * FROM `' . $wpdb->prefix . PeepSoPhotosAlbumModel::TABLE . '`
			WHERE `pho_owner_id` = ' . $user_id . '
				AND `pho_module_id` = 0
				AND `pho_system_album` IN (1,2,3)
		');

		$album_model = new PeepSoPhotosAlbumModel();
		foreach ($list_albums as $album) {
			$new_album_id = $album_model->get_photo_album_id($archive_uid, $album->pho_system_album);
			// Move photos to Archive User
			$wpdb->query('
				UPDATE `' . $wpdb->prefix . PeepSoPhotosModel::TABLE . '`
				SET `pho_owner_id` = ' . $archive_uid . ', `pho_album_id` = ' . $new_album_id . '
				WHERE `pho_owner_id` = ' . $user_id . '
					AND `pho_album_id` = ' . $album->pho_album_id . '
			');
		}

		$wpdb->query('
			DELETE FROM `' . $wpdb->prefix . PeepSoPhotosAlbumModel::TABLE . '`
			WHERE `pho_owner_id` = ' . $user_id . '
				AND `pho_module_id` = 0
				AND `pho_system_album` IN (1,2,3)
		');

		// Move photos custom albums to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoPhotosAlbumModel::TABLE . '`
			SET `pho_owner_id` = ' . $archive_uid . '
			WHERE `pho_owner_id` = ' . $user_id . '
				AND `pho_system_album` = 0
				AND `pho_module_id` = 0
		');

		// Cleanup move photos to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoPhotosModel::TABLE . '`
			SET `pho_owner_id` = ' . $archive_uid . '
			WHERE `pho_owner_id` = ' . $user_id . '
		');

		// Move Activity to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '`
			SET `act_owner_id` = ' . $archive_uid . '
			WHERE `act_owner_id` = ' . $user_id . '
				AND `act_module_id` = ' . PeepSoSharePhotos::MODULE_ID . '
		');
	}

	public static function move_videos($user_id, $archive_uid)
	{
		if (!class_exists('PeepSoVideos')) {
			return;
		}

		global $wpdb;

		$user = PeepSoUser::get_instance($user_id);
		$archive_user = PeepSoUser::get_instance($archive_uid);

		$video_user_dir = $user->get_image_dir() . DIRECTORY_SEPARATOR . 'videos';
		$video_archive_user_dir = $archive_user->get_image_dir() . DIRECTORY_SEPARATOR . 'videos';
		self::recursive_move($video_user_dir, $video_archive_user_dir);

		// Move attachment author to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->posts . '`
			SET `post_author` = ' . $archive_uid . '
			WHERE `post_type` = \'attachment\'
				AND `post_author` = ' . $user_id . '
				AND `post_parent` IN (
					SELECT `act_external_id` FROM `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '` WHERE `act_module_id` = ' . PeepSoVideos::MODULE_ID . ' AND `act_owner_id` = ' . $user_id . '
				)
		');

		// Move Activity to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '`
			SET `act_owner_id` = ' . $archive_uid . '
			WHERE `act_owner_id` = ' . $user_id . '
				AND `act_module_id` = ' . PeepSoVideos::MODULE_ID . '
		');
	}

	public static function move_files($user_id, $archive_uid)
	{
		if (!class_exists('PeepSoFileUploads')) {
			return;
		}

		global $wpdb;

		$user = PeepSoUser::get_instance($user_id);
		$archive_user = PeepSoUser::get_instance($archive_uid);

		$file_user_dir = $user->get_image_dir() . DIRECTORY_SEPARATOR . 'files';
		$file_archive_user_dir = $archive_user->get_image_dir() . DIRECTORY_SEPARATOR . 'files';
		self::recursive_move($file_user_dir, $file_archive_user_dir);

		// Move attachment author to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->posts . '`
			SET `post_author` = ' . $archive_uid . '
			WHERE `post_type` = \'attachment\'
				AND `post_author` = ' . $user_id . '
				AND `post_parent` IN (
					SELECT `act_external_id` FROM `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '` WHERE `act_module_id` = ' . PeepSoFileUploads::MODULE_ID . ' AND `act_owner_id` = ' . $user_id . '
				)
		');

		// Move Activity to Archive User
		$wpdb->query('
			UPDATE `' . $wpdb->prefix . PeepSoActivity::TABLE_NAME . '`
			SET `act_owner_id` = ' . $archive_uid . '
			WHERE `act_owner_id` = ' . $user_id . '
				AND `act_module_id` = ' . PeepSoFileUploads::MODULE_ID . '
		');
	}

	public static function recursive_move($source, $destination)
	{
		// If source doesn't exist, stop
		if (!is_dir($source)) {
			return false;
		}

		// Fallback: manual move (copy + delete)
		if (!is_dir($destination)) {
			mkdir($destination, 0755, true);
		}

		$directory = opendir($source);

		while (($file = readdir($directory)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}

			$srcPath = $source . DIRECTORY_SEPARATOR . $file;
			$dstPath = $destination . DIRECTORY_SEPARATOR . $file;

			if (is_dir($srcPath)) {
				self::recursive_move($srcPath, $dstPath);
			} else {
				// Move file (copy then delete)
				copy($srcPath, $dstPath);
				unlink($srcPath);
			}
		}

		closedir($directory);

		// Remove empty source directory
		rmdir($source);

		return true;
	}
}

// EOF
