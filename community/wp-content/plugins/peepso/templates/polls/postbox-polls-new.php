<?php

$random = rand(); // generate random number to prevent duplicated ID

?><div class="ps-postbox__poll">
	<div class="ps-postbox__fetched ps-postbox-fetched"></div>
	<div class="ps-postbox__poll-inner">
		<div class="ps-postbox__poll-container">
			<div class="ps-postbox__poll-options ui-sortable" data-ps="sortable">
				<div class="ps-postbox__poll-option ps-poll__option" data-ps="option">
					<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ui-sortable-handle" data-ps="sortable-handle" title="<?php echo esc_attr__('Move', 'peepso-core'); ?>">
						<i class="gcis gci-arrows-alt"></i>
					</a>
					<input class="ps-input ps-input--sm" type="text" placeholder="<?php echo esc_attr__('Option 1', 'peepso-core'); ?>">
					<a href="#" class="ps-btn ps-btn--sm ps-btn--cp ps-btn--delete ps-tip ps-tip--arrow" data-ps="btn-delete"
						aria-label="<?php echo esc_attr__('Delete', 'peepso-core'); ?>"><i class="gcis gci-trash"></i></a>
				</div>
				<div class="ps-postbox__poll-option ps-poll__option" data-ps="option">
					<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ui-sortable-handle" data-ps="sortable-handle" title="<?php echo esc_attr__('Move', 'peepso-core'); ?>">
						<i class="gcis gci-arrows-alt"></i>
					</a>
					<input class="ps-input ps-input--sm" type="text" placeholder="<?php echo esc_attr__('Option 2', 'peepso-core'); ?>">
					<a href="#" class="ps-btn ps-btn--sm ps-btn--cp ps-btn--delete ps-tip ps-tip--arrow" data-ps="btn-delete"
						aria-label="<?php echo esc_attr__('Delete', 'peepso-core'); ?>"><i class="gcis gci-trash"></i></a>
				</div>
			</div>
			<div class="ps-postbox__poll-actions">
				<button class="ps-btn ps-btn--action ps-btn--sm ps-button-action" data-ps="btn-add"><?php echo esc_attr__('Add new option', 'peepso-core');?></button>
				<?php if (isset($multiselect) && $multiselect) { ?>
					<?php  ?>
					<div class="ps-checkbox">
						<input type="checkbox" data-ps="allow-multiple" id="allow-multiple-<?php echo $random ?>" class="ps-checkbox__input ace ace-switch ace-switch-2 allow-multiple" />
						<label class="ps-checkbox__label lbl" for="allow-multiple-<?php echo $random ?>">
							<?php echo esc_attr__('Allow multiple options selection', 'peepso-core'); ?>
						</label>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
