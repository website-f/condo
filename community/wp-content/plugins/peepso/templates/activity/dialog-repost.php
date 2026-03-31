<?php
$PeepSoPrivacy	= PeepSoPrivacy::get_instance();
$privacyFirst = $PeepSoPrivacy->get_first_access_setting();
?>
<div id="repost-dialog">
	<div class="dialog-title">
		<?php echo esc_attr__('Share This Post', 'peepso-core'); ?>
	</div>
	<div class="dialog-content">
		<form class="ps-form ps-form--repost ps-form--vertical">
			<div class="ps-form__row">
				<textarea id="share-post-box" class="ps-input ps-input--textarea" placeholder="<?php echo esc_attr__('Say what is on your mind...', 'peepso-core'); ?>"></textarea>
			</div>
			<div class="ps-form__row">
				<div class="ps-dropdown ps-dropdown--menu ps-js-dropdown ps-js-dropdown--privacy">
					<button class="ps-btn ps-btn--sm ps-dropdown__toggle ps-js-dropdown-toggle" data-value="">
						<span class="dropdown-value"><i class="gcis <?php echo $privacyFirst['icon'];?>"></i></span>
					</button>
					<input type="hidden" id="repost_acc" name="repost_acc" value="<?php echo $privacyFirst['id']; ?>" />
					<?php echo $PeepSoPrivacy->render_dropdown(); ?>
				</div>
				<input type="hidden" id="postbox-post-id" name="post_id" value="{post-id}" />
			</div>
			<div class="ps-form__row">
				<blockquote>
					{post-content}
				</blockquote>
			</div>
		</form>
	</div>
	<div class="dialog-action">
		<button type="button" name="rep_cacel" class="ps-btn ps-btn--sm" onclick="pswindow.hide(); return false;"><?php echo esc_attr__('Cancel', 'peepso-core'); ?></button>
		<button type="button" name="rep_submit" class="ps-btn ps-btn--sm ps-btn--action" onclick="activity.submit_repost(); return false;"><?php echo esc_attr__('Share', 'peepso-core'); ?></button>
	</div>
</div>
