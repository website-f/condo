<?php

class PeepSoGeneral
{
  protected static $_instance = NULL;

  private $navigation = array();

  public $template_tags = array(
    'access_types',				// options for post/content access types
    'post_types',				// options for post types
    'show_error',				// outputs a WP_Error object
  );

  private function __construct()
  {
    add_filter('peepso_navigation', array(&$this, 'init_navbar'), -1);
    add_filter('peepso_navigation', array(&$this, 'finish_navbar'), 999);
  }

  /*
   * return singleton instance
   */
  public static function get_instance()
  {
    if (self::$_instance === NULL) {
      self::$_instance = new self();
    }
    return (self::$_instance);
  }

  /** NAVIGATION DATA - BUILD **/
  public function init_navbar($navbar)
  {

    $user = PeepSoUser::get_instance(get_current_user_id());

    $navbar = array(
      'home' => array(
        'href' => PeepSo::get_page('activity'),
        'label' => __('Activity', 'peepso-core'),
        'icon' => 'pso-i-bars-staggered',
        'class' => '',

        'primary' => TRUE,
        'user' => FALSE,
        'secondary' => FALSE,
        'mobile-primary' => FALSE,
        'mobile-secondary' => TRUE,
        'widget' => TRUE,

        'icon-only' => TRUE,
      ),

      'home-following' => array(
        'href' => PeepSo::get_page('activity') . '#following',
        'label' => __('Following', 'peepso-core'),
        'icon' => 'pso-i-checkbox',
        'class' => '',

        'primary' => TRUE,
        'user' => FALSE,
        'secondary' => FALSE,
        'mobile-primary' => TRUE,
        'mobile-secondary' => FALSE,
        'widget' => TRUE,
      ),
      'home-saved' => array(
        'href' => PeepSo::get_page('activity') . '#saved',
        'label' => __('Saved', 'peepso-core'),
        'icon' => 'pso-i-bookmark',
        'class' => '',

        'primary' => TRUE,
        'user' => FALSE,
        'secondary' => FALSE,
        'mobile-primary' => TRUE,
        'mobile-secondary' => FALSE,
        'widget' => TRUE,
      ),
      'members-page' => array(
        'href' => PeepSo::get_page('members'),
        'label' => __('Members', 'peepso-core'),
        'icon' => 'pso-i-queue-alt',

        'primary' => TRUE,
        'user' => FALSE,
        'secondary' => FALSE,
        'mobile-primary' => TRUE,
        'mobile-secondary' => FALSE,
        'widget' => TRUE,
      ),

      // Profile - avatar and name
      // 'profile-home' => array(
      //   'class' => 'ps-navbar__menu-item--user',
      //   'href' => $user->get_profileurl(),
      //   'label' => '<div class="ps-avatar ps-avatar--toolbar ps-avatar--xs"><img src="' . $user->get_avatar() . '" alt="' . $user->get_fullname() . ' avatar"></div> ' . $user->get_firstname(),
      //   'title' => PeepSoUser::get_instance()->get_fullname(),

      //   'primary' => FALSE,
      //   'user' => TRUE,
      //   'secondary' => FALSE,
      //   'mobile-primary' => FALSE,
      //   'mobile-secondary' => FALSE,
      //   'widget' => FALSE,
      // ),

      // Profile segments
      'profile' => array(
        'href' => '',

        'primary' => FALSE,
        'user' => TRUE,
        'secondary' => FALSE,
        'mobile-primary' => TRUE,
        'mobile-secondary' => FALSE,
        'widget' => FALSE,

        'menu' => array(),
      ),
    );

    $extra_activity_items = TRUE;

    if ('core_community' != PeepSo::get_option('stream_id_default', 'core-community')) {
      $extra_activity_items = FALSE;
    } elseif (PeepSo::get_option_new('stream_id_sticky')) {
      $extra_activity_items = FALSE;
    }


    if (!$extra_activity_items || !PeepSo::get_option_new('peepso_navigation_following')) {
      unset($navbar['home-following']);
    }

    if (!$extra_activity_items || !PeepSo::get_option_new('post_save_enable') || !PeepSo::get_option_new('peepso_navigation_saved')) {
      unset($navbar['home-saved']);
    }

    return $navbar;
  }

  public function finish_navbar($navbar)
  {

    $note = PeepSoNotifications::get_instance();
    $unread_notes = $note->get_unread_count_for_user();

    $navbar['notifications'] = array(
      'href' => PeepSo::get_page('notifications'),
      'label' => __('Notifications', 'peepso-core'),
      'icon' => 'pso-i-bell',

      'primary' => FALSE,
      'user' => FALSE,
      'secondary' => TRUE,
      'mobile-primary' => FALSE,
      'mobile-secondary' => TRUE,
      'widget' => FALSE,

      'count' => $unread_notes,
      'class' => 'pso-notif--general ps-js-notifications',
      'icon-only' => TRUE,
      'notifications' => TRUE,
    );

    return $navbar;
  }

  /** NAVIGATION DATA - ACCESS **/

  public function get_navigation($context = 'primary', $user_id = NULL)
  {

    // Return instance if any
    if (isset($this->navigation[$context])) {
      return $this->navigation[$context];
    }

    // Don't run the filters again if we have the raw data
    if (isset($this->navigation['unfiltered'])) {
      $navbar = $this->navigation['unfiltered'];
    } else {
      // Build the navigation
      $navbar = apply_filters('peepso_navigation', array());

      // Attach Profile sub-menu
      $navbar['profile']['menu'] = apply_filters('peepso_navigation_profile', array('_user_id' => $user_id));
      $this->navigation['unfiltered'] = $navbar;
    }

    // Mobile: squish Profile + Submenu together and move to the end
    if ('mobile-primary' == $context) {
      $navbar['profile']['label'] = $navbar['profile-home']['label'];

      $profile = $navbar['profile'];
      unset($navbar['profile']);

      $navbar['profile'] = $profile;
    }

    // Profile Submenu links shoud be absolute
    $user = PeepSoUser::get_instance();
    if (isset($navbar['profile']['menu'])) {
      unset($navbar['profile']['menu']['_user_id']);

      foreach ($navbar['profile']['menu'] as $id => $menu) {
        $url = $user->get_profileurl() . $navbar['profile']['menu'][$id]['href'];

        if ('http' == substr($navbar['profile']['menu'][$id]['href'], 0, 4)) {
          $url = $navbar['profile']['menu'][$id]['href'];
        }

        $navbar['profile']['menu'][$id]['href'] = $url;
      }
    }

    // Profile Submenu extra links
    if (apply_filters('peepso_filter_navigation_preferences', TRUE)) {
      $navbar['profile']['menu']['peepso-core-preferences'] = array(
        'href' => $user->get_profileurl() . 'about/preferences/',
        'icon' => 'pso-i-user-pen',
        'label' => __('Preferences', 'peepso-core'),
      );
    }

    // @todo #2274 this has to be peepso_navigation_profile
//        if(class_exists('PeepSoPMP')) {
//            $navbar['profile']['menu']['peepso-pmp'] = array(
//                'href' => pmpro_url("account"),
//                'label' => __('Membership', 'peepso-pmp'),
//                'icon' => 'ps-icon-vcard',
//            );
//        }
    if (apply_filters('peepso_filter_navigation_log_out', TRUE)) {
      $navbar['profile']['menu']['peepso-core-logout'] = array(
        'href' => PeepSo::get_page('logout'),
        'icon' => 'pso-i-power',
        'label' => __('Log Out', 'peepso-core'),
      );
    }

    $filtered_navbar = array();
    foreach ($navbar as $nav) {

      $nav['class'] = isset($nav['class']) ? $nav['class'] : '';
      $nav['count'] = isset($nav['count']) ? $nav['count'] : 0;
      $nav['label'] = isset($nav['label']) ? $nav['label'] : '';
      $nav['title'] = isset($nav['title']) ? $nav['title'] : $nav['label'];
      $nav['menuclass'] = isset($nav['menuclass']) ? $nav['menuclass'] : '';
      $nav[$context] = isset($nav[$context]) ? $nav[$context] : FALSE;
      $nav['icon-only'] = isset($nav['icon-only']) ? $nav['icon-only'] : FALSE;

      if (TRUE == $nav[$context]) {
        $filtered_navbar[] = $nav;
      }
    }

    $navbar = $filtered_navbar;

    $this->navigation[$context] = $navbar;
    return $navbar;
  }

  /** RENDERING **/

  public function access_types()
  {
    $access = array(
      'public' => array(
        'icon' => 'gcis gci-globe-americas',
        'label' => __('Public', 'peepso-core'),
        'descript' => __('Can be seen by everyone, even if they\'re not members', 'peepso-core'),
      ),
      'site_members' => array(
        'icon' => 'pso-i-queue-alt',
        'label' => __('Site Members', 'peepso-core'),
        'descript' => __('Can be seen by registered members', 'peepso-core'),
      ),
      'friends' => array(
        'icon' => 'pso-i-users',
        'label' => __('Friends', 'peeps'),
        'descript' => __('Can be seen by your friends', 'peepso-core'),
      ),
      'me' => array(
        'icon' => 'gcis gci-lock',
        'label' => __('Only Me', 'peepso-core'),
        'descript' => __('Can only be seen by you', 'peepso-core'),
      )
    );

    foreach ($access as $name => $data) {
      echo '<li data-priv="', esc_attr($name), '">', PHP_EOL;
      echo '<i class="', esc_attr($data['icon']), '"></i>', PHP_EOL;
      echo esc_attr($data['label']), "</p>\r\n";
      echo '<span>', esc_attr($data['descript']), "</span></li>", PHP_EOL;
    }
  }

  // Displays the frontend navbar
  public function render_navigation($context = 'primary')
  {
    ob_start();
    $navbar = $this->get_navigation($context, get_current_user_id());

    foreach ($navbar as $item => $data) {

      if (isset($data['menu'])) { ?>

        <div class="pso-dropdown__label">
          <?php echo esc_attr__('My Profile', 'peepso-core'); ?>
        </div>

        <?php foreach ($data['menu'] as $name => $submenu) { ?>
          <a class="pso-dropdown__item"
            href="<?php echo esc_url($submenu['href']); ?>">
            <i class="<?php echo esc_attr($submenu['icon']); ?>"></i>
            <?php echo wp_kses_post($submenu['label']); ?>
          </a>
        <?php } ?>

      <?php
      } elseif ($context === 'mobile-primary') { ?>

        <a class="pso-dropdown__item"
          href="<?php echo esc_url($data['href']); ?>">
          <i class="<?php echo esc_attr($data['icon']); ?>"></i>
          <?php if (FALSE == $data['icon-only']) {
            echo wp_kses_post($data['label']);
          } ?>
        </a>

      <?php
      } elseif ($context === 'primary') { 

        // Add pso-active class only if there is no other navigation with the same href, otherwise add pso-maybe-active
        $href = explode('#', $data['href'])[0];
        if ($href === get_permalink()) {
          $href_same = array_filter(array_column($navbar, 'href'), function($href) {
            return explode('#', $href)[0] === get_permalink();
          });
          $data['class'] .= count($href_same) === 1 ? ' pso-active' : ' pso-maybe-active';
        }
        
        ?>

        <span class="pso-navbar__tab <?php echo esc_attr($data['class']); ?> pso-tip pso-tip--top"
          aria-label="<?php echo esc_attr($data['title']); ?>">
          <a class="pso-navbar__link ps-js-navbar-menu" href="<?php echo esc_url($data['href']); ?>">
            <?php if (isset($data['icon']) && (FALSE == $data['primary'] || isset($data['icon-only']))) { ?>
              <i class="<?php echo esc_attr($data['icon']); ?>"></i>
            <?php } ?>

            <span class="pso-navbar-link__label">
              <?php if (FALSE == $data['icon-only']) {
                echo wp_kses_post($data['label']);
              } ?>
            </span>
          </a>
        </span>

      <?php
      } else { ?>

        <span class="pso-navbar__tab <?php echo esc_attr($data['class']); ?>">
          <a class="pso-navbar__notif pso-tip pso-tip--top ps-js-navbar-menu" href="<?php echo esc_url($data['href']); ?>" aria-label="<?php echo esc_attr($data['title']); ?>">
            <?php if (isset($data['icon'])) { ?>
              <i class="<?php echo esc_attr($data['icon']); ?>"></i>
            <?php } ?>

            <span class="pso-badge pso-badge--float pso-badge--primary js-counter ps-js-counter"><?php echo esc_attr($data['count'] > 0 ? $data['count'] : ''); ?></span>

            <span class="pso-navbar-link__label">
              <?php if (FALSE == $data['icon-only']) {
                echo wp_kses_post($data['label']);
              } ?>
            </span>
          </a>
        </span>

      <?php
      }
    }

    return preg_replace(['/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s'], ['>', '<', '\\1'], ob_get_clean());
  }



  /**
   * Displays the post types available on the post box. Plugins can add to these via the `peepso_post_types` filter.
   */
  public function post_types($params = array())
  {
    $opts = array(
      'status' => array(
        'icon' => 'gcis gci-pen',
        'name' => __('Status', 'peepso-core'),
        'class' => 'ps-postbox__menu-item active',
      ),
    );

    $opts = apply_filters('peepso_post_types', $opts, $params);


    foreach ($opts as $type => $data) {
      echo '<div data-tab="', esc_attr($type), '" ';
      if (isset($data['class']) && !empty($data['class'])) {
        echo 'class="', esc_attr($data['class']), '" ';
      }
      echo '>', PHP_EOL;
      echo '<a href="#" onclick="return false;">';

      echo '<i class="', esc_attr($data['icon']), '"></i>';
      echo '<span>', esc_attr($data['name']), '</span>', PHP_EOL;

      echo '</a></div>', PHP_EOL;
    }
  }

  /*
   * Displays error messages contained within an error object
   * @param WP_Error $error The instance of WP_Error to display messages from.
   */
  public function show_error($error)
  {
    if (!is_wp_error($error))
      return;

    $codes = $error->get_error_codes();
    foreach ($codes as $code) {
      echo '<div class="ps-alert ps-alert--abort">', PHP_EOL;
      $msg = $error->get_error_message($code);
      echo wp_kses_post($msg);
      echo '</div>';
    }
  }

  /**
   * Returns the max upload size from php.ini and wp.
   * @return string The max upload size bytes in human readable format.
   */
  public function upload_size()
  {
    $upload_max_filesize = convert_php_size_to_bytes(ini_get('upload_max_filesize'));
    $post_max_size = convert_php_size_to_bytes(ini_get('post_max_size'));

    return (size_format(min($upload_max_filesize, $post_max_size, wp_max_upload_size())));
  }

  /**
   * Returns the label for login input.
   * @return string The label 'Username or email' OR 'Email'.
   */
  public static function get_login_input_label()
  {
    $login_with_email = PeepSo::get_option('login_with_email', 0);
    if ($login_with_email == 2) {
      return __('Email', 'peepso-core');
    } else {
      return __('Username or Email', 'peepso-core');
    }
  }

}

// EOF
