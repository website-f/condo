<?php

class PeepSo3_Anon {

	private static $instance;
	public $anon_id = null;

	const META_POST_ANON_OP = 'peepso_anon_op';

	public static function get_instance()
	{
		return isset(self::$instance) ? self::$instance : self::$instance = new self;
	}

	private function __construct() {

		// If the option is disabled, do nothing
		if(!PeepSo::get_option_new('postbox_anon_enabled')) {
			return;
		}

		// Check that anon user was properly created, or create one
		add_action('init', function() {
			if(!$this->anon_id) {
				$anon_id = PeepSo3_Mayfly::get('anon_id');
				if($anon_id > 0) {
					// Remove mayfly and move to config
					PeepSoConfigSettings::get_instance()->set_option('anon_id', $anon_id);
					PeepSo3_Mayfly::del('anon_id');
				}
				
				$this->anon_id = PeepSo::get_option_new('anon_id', 0);
				if (!$this->anon_id || !get_userdata($this->anon_id)) {
					$this->create_anon();
				}
			}

			add_action('peepso_init', array(&$this, 'init'));
		});

		// Prevent admin from accidentally deleting our anon user
		add_filter('user_row_actions', function($a, $u){
			if (intval($u->ID) === intval($this->anon_id)) {
				unset($a['delete']);
				unset($a['resetpassword']);
				unset($a['view']);
			}
			return $a;
		}, 10, 2);

		add_filter('peepso_taggable', function($taggable, $act_id) {
			$instance = PeepSoActivity::get_instance();
			$post = $instance->get_activity_post($act_id);

			// check if post is a comment/reply
			if ($post->post_type == PeepSoActivityStream::CPT_COMMENT) {
				$parent_activity = $instance->get_activity_data($post->act_comment_object_id, $post->act_comment_module_id);
				if (is_object($parent_activity)) {
					$parent_post = $instance->get_activity_post($parent_activity->act_id);

					// check if comment is a reply
					if ($parent_post->post_type == PeepSoActivityStream::CPT_COMMENT) {
						$parent_activity = $instance->get_activity_data($parent_activity->act_comment_object_id, $parent_activity->act_comment_module_id);
						$parent_post = $instance->get_activity_post($parent_activity->act_id);
					}

					$post = $parent_post;
				}
			}

			// replace author with anonymous user
			$anon_op = get_post_meta($post->ID, PeepSo3_anon::META_POST_ANON_OP, TRUE);
			if (strlen($anon_op)) {
				$author = PeepSoUser::get_instance($anon_op);
				$author_id = $author->get_id();
				if (isset($taggable[$author_id])) {
					unset($taggable[$author_id]);
				}

				$author_anon = PeepSoUser::get_instance(PeepSo3_Anon::get_instance()->anon_id);
				$author_anon_id = $author_anon->get_id();
				if (!isset($taggable[$author_anon_id])) {
					$taggable[$author_anon_id] = array(
						'id' => $author_anon->get_id(),
						'name' => $author_anon->get_fullname(),
						'avatar' => $author_anon->get_avatar(),
						'icon' => $author_anon->get_avatar(),
						'type' => 'author'
					);
				}
			}

			return $taggable;
		}, 100, 2);
	}

	public function init()
	{
		// Store Anon ID
		add_action('wp_insert_post', array(&$this, 'save_anon_op'), 100, 3);
		add_action('peepso_activity_after_save_post', array(&$this, 'save_anon_op'), 100);
		add_action('peepso_after_add_comment', array(&$this, 'save_comments_anon_op'), 100, 4);

		// Override avatar and name
		add_filter('peepso_activity_stream_author_avatar', 		array(&$this, 'filter_activity_stream_author'), 20, 5);
		add_filter('peepso_activity_stream_comments_author_avatar', 		array(&$this, 'filter_activity_stream_comments_author_avatar'), 20, 4);
		add_filter('peepso_activity_stream_comments_author_name', 		array(&$this, 'filter_activity_stream_comments_author_name'), 20, 4);
		add_filter('peepso_activity_commentsbox_author_avatar', 		array(&$this, 'filter_activity_commentsbox_author_avatar'), 20, 4);

		// notification
		add_filter('peepso_notification_avatar', array(&$this, 'filter_notification_avatar'), 20, 3);
		add_filter('peepso_notification_user_firstname', array(&$this, 'filter_notification_user_firstname'), 20, 3);
	}

	/**
	 * This function saves the mood data for the post
	 * @param $post_id is the ID assign to the posted content
	 */
	public function save_anon_op($post_id, $post = null, $update = false)
	{
		$input = new PeepSoInput();
		$anon_id = $input->int('anon_id');

		if (apply_filters('peepso_moods_apply_to_post_types', array(PeepSoActivityStream::CPT_POST))) {
			if (empty($anon_id) && !$post) {
				// check anonymous
				$anon_op = get_post_meta($post_id, self::META_POST_ANON_OP, TRUE);
				if(!strlen($anon_op)) {
					delete_post_meta($post_id, self::META_POST_ANON_OP);
				}
			} else if ($anon_id) {
				update_post_meta($post_id, self::META_POST_ANON_OP, get_current_user_id());
			}
		}
	}

	public function save_comments_anon_op($post_id, $act_id, $did_notify, $did_email)
	{
        $peepso_activity = new PeepSoActivity();

        // get root post
        $comment = $peepso_activity->get_comment($post_id);
		$comment = $comment->post;

		if ($comment) {
			$root_act = $peepso_activity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
			$root_post = $peepso_activity->get_activity_post($root_act->act_id);

			// if root post still a comment
			if ($root_post->post_type == PeepSoActivityStream::CPT_COMMENT) {
				$comment = $root_post;
				$root_act = $peepso_activity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
				$root_post = $peepso_activity->get_activity_post($root_act->act_id);
			}
			if ($root_post->post_type == PeepSoActivityStream::CPT_POST) {
				$anon_op = get_post_meta($root_post->ID,PeepSo3_anon::META_POST_ANON_OP,TRUE);
                if(strlen($anon_op) && intval($anon_op) == get_current_user_id()) {
                    update_post_meta($post_id, PeepSo3_anon::META_POST_ANON_OP, $anon_op);
                }
			}
		}
	}

	private function create_anon() {

		// Prevent username collision

		$username_base = 'anonymous';
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
		$userdata['first_name'] = 'Anonymous';
		$userdata['last_name'] = 'User';
		$userdata['display_name'] = $userdata['first_name'] . ' ' . $userdata['last_name'];
		$userdata['user_pass'] = wp_generate_password();

		$user_id = wp_insert_user($userdata);

		if (!is_wp_error($user_id)) {
			$this->anon_id = $user_id;
			// PeepSo/PeepSo#7815 Don't use mayfly
			// PeepSo3_Mayfly::set('anon_id', $user_id);
			PeepSoConfigSettings::get_instance()->set_option('anon_id', $user_id);
		} else {
			error_log('Failed to create anonymous user: ' . $user_id->get_error_message());
		}
	}

    /**
     * PeepSo stream action title
     * @param $title default stream action title
     * @param $post global post variable
     */
    public function filter_activity_stream_author($output, $post_id, $hide_post_header, $post_author, $PeepSoUser)
    {
		// check anonymous
		$anon_op = get_post_meta($post_id, self::META_POST_ANON_OP, TRUE);
		if(strlen($anon_op)) {
			$PeepSoUser = PeepSoUser::get_instance(self::get_instance()->anon_id);
			$output = '<a ' . $hide_post_header . ' class="ps-avatar ps-avatar--post" href="' . $PeepSoUser->get_profileurl() . '"><img data-author="' . $post_author . '" src="'. $PeepSoUser->get_avatar().'" alt="' .$PeepSoUser->get_fullname().' avatar" /></a>';
		}

        return $output;
    }

    /**
     * PeepSo stream action title
     * @param $title default stream action title
     * @param $post global post variable
     */
    public function filter_activity_stream_comments_author_avatar($output, $post_id, $post_author, $PeepSoUser)
    {
		$anon_op = get_post_meta($post_id, self::META_POST_ANON_OP, TRUE);
        if(strlen($anon_op)) {
            $post_author = self::get_instance()->anon_id;
            $PeepSoUser = PeepSoUser::get_instance($post_author);
			$output = '<a href="' . $PeepSoUser->get_profileurl() . '"><img data-author="' . $post_author . '" src="'. $PeepSoUser->get_avatar().'" alt="' .$PeepSoUser->get_fullname().' avatar" /></a>';
        }

		return $output;
	}

    public function filter_activity_stream_comments_author_name($output, $post_id, $post_author, $PeepSoUser)
    {
        $anon_op = get_post_meta($post_id, self::META_POST_ANON_OP, TRUE);
        if(strlen($anon_op)) {
            $post_author = self::get_instance()->anon_id;
            $PeepSoUser = PeepSoUser::get_instance($post_author);
			$output = '<div class="ps-comment__author">@peepso_user_'.$post_author.'('. $PeepSoUser->get_fullname().')</div>';
        }

        return $output;
    }

	public function filter_activity_commentsbox_author_avatar($output, $post_id, $post_author, $PeepSoUser)
    {
		$anon_op = get_post_meta($post_id, self::META_POST_ANON_OP, TRUE);
        if(strlen($anon_op) && $post_author == get_current_user_id()) {
            $post_author = self::get_instance()->anon_id;
            $PeepSoUser = PeepSoUser::get_instance($post_author);
			$output = '<a class="ps-avatar cstream-avatar cstream-author" href="' . $PeepSoUser->get_profileurl() . '"><img data-author="' . $post_author . '" src="'. $PeepSoUser->get_avatar() . '" alt="" /></a>';
        }

		return $output;
	}

    public function filter_notification_avatar($output, $not_id, $PeepSoUser)
    {
        $notification = new PeepSoNotifications(intval($not_id));
        $data = $notification->get_data();
        if (NULL !== $data) {
            $post_id = $data->not_external_id;

			$peepso_activity = new PeepSoActivity();

			// get root post
			$comment = $peepso_activity->get_comment($post_id);
			$comment = $comment->post;
	
			if ($comment) {
				$root_act = $peepso_activity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
				$root_post = $peepso_activity->get_activity_post($root_act->act_id);
	
				// if root post still a comment
				if ($root_post->post_type == PeepSoActivityStream::CPT_COMMENT) {
					$comment = $root_post;
					$root_act = $peepso_activity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
					$root_post = $peepso_activity->get_activity_post($root_act->act_id);
				}
				if ($root_post->post_type == PeepSoActivityStream::CPT_POST) {
					$anon_op = get_post_meta($root_post->ID, self::META_POST_ANON_OP, true);
					if (strlen($anon_op) && intval($anon_op) != get_current_user_id()) {
						$PeepSoUser = PeepSoUser::get_instance(PeepSo3_Anon::get_instance()->anon_id);
						return '<img src="' . $PeepSoUser->get_avatar(). '" alt="' . trim(strip_tags($PeepSoUser->get_fullname())). '">';
					}
				}
			}
        }

        return $output;
    }

    public function filter_notification_user_firstname($output, $not_id, $PeepSoUser)
    {
        $notification = new PeepSoNotifications(intval($not_id));
        $data = $notification->get_data();
        if (NULL !== $data) {
            $post_id = $data->not_external_id;

			$peepso_activity = new PeepSoActivity();

			// get root post
			$comment = $peepso_activity->get_comment($post_id);
			$comment = $comment->post;
	
			if ($comment) {
				$root_act = $peepso_activity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
				$root_post = $peepso_activity->get_activity_post($root_act->act_id);
	
				// if root post still a comment
				if ($root_post->post_type == PeepSoActivityStream::CPT_COMMENT) {
					$comment = $root_post;
					$root_act = $peepso_activity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
					$root_post = $peepso_activity->get_activity_post($root_act->act_id);
				}
				if ($root_post->post_type == PeepSoActivityStream::CPT_POST) {
					$anon_op = get_post_meta($root_post->ID, self::META_POST_ANON_OP, true);
					if (strlen($anon_op) && intval($anon_op) != get_current_user_id()) {
						$PeepSoUser = PeepSoUser::get_instance(PeepSo3_Anon::get_instance()->anon_id);
						return trim(strip_tags($PeepSoUser->get_fullname()));
					}
				}
			}
        }

        return $output;
    }
}

PeepSo3_Anon::get_instance();
