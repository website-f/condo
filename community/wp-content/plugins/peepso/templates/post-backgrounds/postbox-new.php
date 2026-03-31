<div class="pso-postbox__backgrounds-wrapper">
	<div class="pso-postbox__backgrounds-notice ps-alert ps-alert--warning" data-ps="warning" style="display:none">
		<span><?php echo esc_attr__('Please shorten the text or change the post type', 'peepso-core') ?></span>
	</div>
	<div class="pso-post__background <?php if (PeepSo::get_option('post_backgrounds_scrollable', 1)) { ?>pso-post__background--scroll<?php } ?>" data-ps="background">
		<div class="pso-post__background-inner">
			<div class="pso-post__background-text" contenteditable="true" data-ps="text"
			data-placeholder="<?php echo esc_attr__('Say what is on your mind...', 'peepso-core'); ?>"></div>
		</div>
	</div>
	<div class="pso-postbox__backgrounds">
		<?php foreach ($post_backgrounds as $post_background) : ?>
			<?php if ($post_background->image != '0.jpg') : ?>
				<div class="pso-postbox__backgrounds-item pso-tip pso-tip--inline"
					aria-label="<?php echo $post_background->title; ?>" style="background-image:url(<?php echo $post_background->image_url; ?>)"
					data-preset-id="<?php echo $post_background->post_id; ?>"
					data-background="<?php echo $post_background->image_url; ?>"
					data-text-color="<?php echo $post_background->content->text_color; ?>"><span></span></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>
