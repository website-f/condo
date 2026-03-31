<?php

$template_title = sprintf(
	__('is %1$s feeling %2$s', 'peepso-core'),
	'<i class="ps-emoticon ps-emo-##icon##"></i>',
	'<strong>##mood##</strong>'
);

?><div class="pso-postbox__moods">
	<div class="pso-postbox-moods__title">
		<?php echo __('What is your mood?', 'peepso-core'); ?>
		<button class="pso-btn pso-btn--xs" data-ps="btn-cancel"><i class="pso-i-arrow-small-left"></i><?php echo __('Back', 'peepso-core'); ?></button>
	</div>
	<div class="pso-postbox-moods__list">
		<?php if (isset($moods) && count($moods)) foreach ($moods as $id => $mood) { ?>
		<a href="#" class="pso-postbox-moods__item" data-id="<?php echo $id ?>" data-mood="<?php echo $mood ?>">
			<div class="pso-postbox-moods__emo"><i class="ps-emoticon ps-emo-<?php echo $id ?>"></i></div>
			<span><?php echo $mood ?></span>
		</a>
		<?php } ?>
	</div>
	<button class="pso-btn pso-btn--neutral pso-postbox-moods__remove" style="display:none" data-ps="btn-remove" title="<?php echo __('Remove Mood', 'peepso-core') ?>">
		<?php echo __('Remove Mood', 'peepso-core') ?>
	</button>
	<script type="text/template" data-tmpl="title"><?php echo $template_title; ?></script>
</div>
