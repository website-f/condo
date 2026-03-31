<div class="peepso">
  <div class="ps-page ps-page--register ps-page--register-resend">
    <h2><?php echo esc_attr__('Resend Activation Code', 'peepso-core'); ?></h2>
    <p><?php echo esc_attr__('Please enter your registered email address here so that we can resend you the activation link.', 'peepso-core'); ?></p>
    <?php
		if (isset($error)) {
			PeepSoGeneral::get_instance()->show_error($error);
		}
		?>

    <div class="psf-register psf-register--resend">
      <form class="ps-form ps-form--register ps-form--register-resend" name="resend-activation" action="<?php PeepSo::get_page('register'); ?>?resend" method="post">
				<input type="hidden" name="task" value="-resend-activation" />
				<input type="hidden" name="-form-id" value="<?php echo wp_create_nonce('resent-activation-form'); ?>" />
				<div class="ps-form__grid">
					<div class="ps-form__row">
						<label for="email" class="ps-form__label"><?php echo esc_attr__('Email Address', 'peepso-core'); ?>
							<span class="ps-form__required">&nbsp;*<span></span></span>
						</label>
						<div class="ps-form__field">
							<input class="ps-input" type="email" name="email" id="email" placeholder="<?php echo esc_attr__('Email address', 'peepso-core'); ?>" />
						</div>
					</div>
					<div class="ps-form__row ps-form__row--submit">
						<?php $prevUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PeepSo::get_page('activity'); ?>
						<a class="ps-btn" href="<?php echo $prevUrl; ?>"><?php echo esc_attr__('Back', 'peepso-core'); ?></a>
						<?php $recaptchaEnabled = PeepSo::get_option('site_registration_recaptcha_enable', 0); ?>
						<input type="submit" name="submit-resend"
							class="ps-btn ps-btn--action <?php echo $recaptchaEnabled ? 'ps-js-recaptcha' : ''; ?>"
							value="<?php echo esc_attr__('Submit', 'peepso-core'); ?>"
							<?php echo $recaptchaEnabled ? 'disabled="disabled"' : '' ?> />
					</div>
				</div>
			</form>
    </div>
  </div>
</div>
