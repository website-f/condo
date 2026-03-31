<div class="psa-starter__welcome">
	<div class="psa-starter__welcome-inner">
		<?php echo __('The following settings were chosen for the Getting Started to give you a glimpse of PeepSo\'s customization possibilities.','peepso-core');?>
		<br/>
		<?php echo sprintf(__('They can always be adjusted further in %s.','peepso-core'), '<a target="_blank" href="'.admin_url('admin.php?page=peepso_config&tab=appearance').'">'.__('PeepSo &raquo; Configuration &raquo; Appearance','peepso-core').' <i class="fa fa-external-link"></i></a> and other configuration tabs. </br>If you decide to go with Gecko Theme (that is available in all PeepSo Bundles including PeepSo Free Bundle) there are a lot more appearance customization options within the theme customizer');?>
	</div>
</div>
<div class="psa-starter__page psa-starter__page--split psa-starter__page--customize">
	<div class="psa-starter__column">
		<div class="psa-starter__customize">
		<?php

		class PeepSoGettingStartedPeepSoStep3 {
			static function f($key, $label, $desc, $type='checkbox', $args=array()) {
				$value = PeepSo::get_option($key);

				if('separator' == $type) { ?>

					<div class="psa-starter__box psa-starter__box--head">
						<h2 class="psa-starter__box-title"><?php echo $label;?></h2>
						<p><?php echo $desc;?></p>
					</div>

				<?php } else { ?>
					<div class="psa-starter__box psa-starter__box--option">
						<h5 class="psa-starter__box-subtitle"><?php echo $label;?></h5>

						<?php if('checkbox' == $type) { ?>
							<input class="ace ace-switch ace-switch-2" type="checkbox" name="<?php echo $key;?>" <?php if($value) { echo 'checked="checked"'; }?>>
							<label class="lbl" for="<?php echo $key;?>"></label>
						<?php } ?>

						<?php if('text' == $type) { ?>

							<input type="text" name="<?php echo $key;?>" value="<?php echo $value;?>" size="100"/>
							<button class="button ps-js-btn ps-js-cancel" style="display:none"><?php echo __('Cancel', 'peepso-core'); ?></button>
							<button class="button button-primary ps-js-btn ps-js-save" style="display:none"><?php echo __('Save', 'peepso-core'); ?></button>

						<?php } ?>

						<?php if('textarea' == $type) { ?>

							<textarea name="<?php echo $key;?>" cols="84" rows="5"><?php echo $value;?></textarea>
							<button class="button ps-js-btn ps-js-cancel" style="display:none"><?php echo __('Cancel', 'peepso-core'); ?></button>
							<button class="button button-primary ps-js-btn ps-js-save" style="display:none"><?php echo __('Save', 'peepso-core'); ?></button>

						<?php } ?>


						<?php if('select' == $type) { ?>

							<select name="<?php echo $key;?>">
								<?php foreach($args['options'] as $k=>$v) { ?>
									<option value="<?php echo $k;?>" <?php if($k==$value) { echo 'selected="selected"'; }?>>
										<?php echo $v;?>
									</option>
								<?php } ?>
							</select>

						<?php } ?>


						<?php if('image' == $type) { ?>
							<?php
								wp_enqueue_media();

								$is_default = FALSE;
								if (!$value) {
									$value = $args['default'];
									$is_default = TRUE;
								}
							?>
							<input type="hidden" data-type="image" name="<?php echo $key;?>" value="<?php echo $value;?>"/>
							<button class="button button-primary ps-js-btn ps-js-select"><?php echo __('Select Image', 'peepso-core'); ?></button>
							<button class="button button-link-delete ps-js-btn ps-js-remove" <?php echo $is_default ? 'style="display:none"' : '' ?>><?php echo __('Remove Image', 'peepso-core'); ?></button>
							<span style="line-height:26px; display:none"><img src="images/loading.gif" /></span>
							<i class="ace-icon fa fa-check bigger-110" style="color:green; line-height:26px; display:none"></i>
							<span class="ps-js-notice" style="line-height:26px; <?php echo $is_default ? '' : 'display:none' ?>"><?php echo __('Default image selected', 'peepso-core'); ?></span>
							<img class="img-responsive img-landing-page-preview ps-js-img" src="<?php echo $value;?>"
								data-defaultsrc="<?php echo $args['default'];?>"
								style="margin-top:10px" />
						<?php } ?>

						<?php if('placeholder' != $type && 'image' != $type) { ?>
							<span style="display:none"><img src="images/loading.gif" /></span>
							<i class="ace-icon fa fa-check bigger-110" style="color:green; display:none"></i>
						<?php } ?>

						<?php if(strlen($desc)) { ?>
							<p><?php echo $desc; ?></p>
						<?php } ?>
					</div>
				<?php
				}
			}
		}

			$gs=new PeepSoGettingStartedPeepSoStep3();

			if ( 
				! class_exists( 'Gecko_Theme_Settings' ) && 
				! class_exists( 'PeepSo_Block_Theme_Settings' ) 
			) {
				$gs::f(
					'',
					__('Appearance','peepso-core'),
					__('This option allows you to configure the basic color scheme for PeepSo. You can choose between light and dark modes, which are compatible with most themes.
If the Gecko Theme is installed, this option is disabled, and all appearance settings are managed through Gecko’s built-in Customizer panel.
The Gecko Theme is available in the PeepSo Free Bundle, Community Bundle, and Ultimate Bundle.', 'peepso-core'),
					'separator'
				);

				// COLOR SCHEME
				$options = array(
					'' => __('Light', 'peepso-core'),
				);

				$dir = plugin_dir_path(__FILE__) . '/../../templates/css';

				$dir = scandir($dir);
				$from_key = array('template-', '.css');
				$to_key = array('');

				$from_name = array('_', '-');
				$to_name = array(' ', ' ');

				foreach ($dir as $file) {
					if ('template-' == substr($file, 0, 9) && !strpos($file, 'rtl') && !strpos($file, 'round')) {
						$key = str_replace($from_key, $to_key, $file);
						$name = str_replace($from_name, $to_name, $key);
						$options[$key] = ucwords($name);
					}
				}

				if(!is_string(apply_filters('peepso_theme_override', false))) {
					$gs::f(
						'site_css_template',
						__('Color scheme', 'peepso-core'),
						sprintf(
							__('Pick a color from the list that suits your site best. If the list doesn\'t contain the color you\'re looking for you can always use %s.', 'peepso-core'),
							'<a target="_blank" href="https://peep.so/docs_css_overrides">' . __('CSS overrides', 'peepso-core') . ' <i class="fa fa-external-link"></i></a>' . __(' as well as Gecko Theme customizer', 'peepso-core'),
						),
						'select',
						array('options' => $options)
					);
				}
			}

			// Profiles separator
			$gs::f(
				'',
				__('Profiles','peepso-core'),
				__('Options related to user profiles','peepso-core'),
				'separator'
			);

			// Display name style
			$options = apply_filters('peepso_filter_display_name_styles', []);
			$gs::f(
				'system_display_name_style',
				__('Display name style', 'peepso-core'),
				__('Do you want your community to use real names or usernames?', 'peepso-core'),
				'select',
				array('options'=>$options)
			);

			$options = array(
				0 => __('No', 'peepso-core'),
				1 => __('Yes', 'peepso-core'),
			);

			// Registration separator
			$gs::f(
					'',
					__('Landing page','peepso-core'),
					__('Encourage people to join your community with the following options. These are shown as a part of the landing page - [peepso_activity] shortcode.<br/>Please note, the landing page is visible only to users who are not logged in. You can take a look at it in incognito mode in your browser.','peepso-core'),
					'separator'
			);


			// Image

	//        $gs::f(
	//            'landing_page_image_header',
	//            __('Landing page image', 'peepso-core'),
	//            __('Suggested size is: 1140px x 469px.', 'peepso-core'),
	//            'placeholder'
	//        );
	//
	//        $gs::f(
	//            'landing_page_image',
	//            '',
	//            '',
	//            'text'
	//        );
	//
	//        $gs::f(
	//            'landing_page_image_default',
	//            '',
	//            '',
	//            'text'
	//        );

			// Callout header
			$gs::f(
				'site_registration_header',
				__('Callout header', 'peepso-core'),
				'',
				'text'
			);


			// Callout text
			$gs::f(
				'site_registration_callout',
				__('Callout text', 'peepso-core'),
				'',
				'text'
			);


			// Button text
			$gs::f(
				'site_registration_buttontext',
				__('Button text', 'peepso-core'),
				'',
				'text'
			);

			// Landing page image
			$gs::f(
				'landing_page_image',
				__('Landing page image', 'peepso-core'),
				'',
				'image',
				array('default'=>PeepSo::get_asset('images/landing/register-bg.jpg'))
			);

			// Opengraph separator
			$gs::f(
				'',
				__('Open Graph','peepso-core'),
				__('Options related to open graph','peepso-core'),
				'separator'
			);

			$gs::f(
				'opengraph_enable',
				__('Enable Open Graph', 'peepso-core'),
				'',
				'checkbox'
			);

			// Open Graph Title
			$gs::f(
				'opengraph_title',
				__('Title (og:title)', 'peepso-core'),
				'',
				'text'
			);

			// Open Graph Title
			$gs::f(
				'opengraph_description',
				__('Description (og:description)', 'peepso-core'),
				'',
				'textarea'
			);

			// Open Graph image
			$gs::f(
				'opengraph_image',
				__('Image (og:image)', 'peepso-core'),
				'',
				'image',
			);

			// Notifications separator
			$gs::f(
				'',
				__('Notifications','peepso-core'),
				__('Options related to user notifications','peepso-core'),
				'separator'
			);

			// Email Sender
			$gs::f(
				'site_emails_sender',
				__('Email sender', 'peepso-core'),
				'',
				'text'
			);


			// Admin Email
			$gs::f(
				'site_emails_admin_email',
				__('Admin Email', 'peepso-core'),
				__('To improve email delivery, do not use a generic address like @gmail.com - instead try using your own domain name, like this: no-reply@example.com. Remember that many hosting options, especially shared ones and VPS impose limits on how many emails can be sent from your website. To further ensure delivery please <a href="https://peepso.com/your-emails-go-to-spam-ensure-email-deliverability/" target="_blank">read this article <i class="fa fa-external-link"></i>.</a>','peepso-core'),
				'text'
			);
		?>
	</div>
</div>
