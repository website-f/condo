<?php
/** SEARCH POSTS **/
$hashtag = FALSE;
$PeepSoUrlSegments = PeepSoUrlSegments::get_instance();

if('hashtag' == $PeepSoUrlSegments->get(1)) {
    $hashtag = $PeepSoUrlSegments->get(2);
}

?>
<input type="hidden" id="peepso_search_hashtag" value="<?php echo $hashtag; ?>" />
<!-- Hashtags -->
<div class="pso-posts__filter pso-posts__filter--hashtags ps-js-dropdown ps-js-activitystream-filter" data-id="peepso_search_hashtag">
	<a href="javascript:" class="pso-posts-filter__toggle pso-tip pso-tip--top ps-js-dropdown-toggle" aria-haspopup="true" aria-label="<?php echo esc_attr__('Hashtag', 'peepso-core'); ?>">
		<i class="pso-i-hashtag"></i>
	</a>
	<div class="pso-posts-filter__box ps-js-dropdown-menu" role="menu">
		<div class="pso-posts-filter__search">
			<i class="pso-i-hashtag"></i>
			<input maxlength="<?php echo PeepSo::get_option('hashtags_max_length',16);?>" type="text" class="pso-input--reset pso-posts-filter-search__input"
				placeholder="<?php echo esc_attr__('Type to search', 'peepso-core'); ?>" value="<?php echo $hashtag;?>" />
		</div>
		<div class="pso-posts-filter__notice">
			<i class="pso-i-info"></i>
			<span>
				<?php
				echo sprintf(
					__('Letters and numbers only, minimum %d and maximum %d character(s)','peepso-core'),
					PeepSo::get_option('hashtags_min_length',3),
					PeepSo::get_option('hashtags_max_length',16)
				);?>
			</span>
		</div>
		<div class="pso-posts-filter__actions">
			<button class="pso-btn pso-btn--neutral ps-js-cancel"><?php echo esc_attr__('Cancel', 'peepso-core'); ?></button>
			<button class="pso-btn pso-btn--primary ps-js-search-hashtag" ><?php echo esc_attr__('Apply', 'peepso-core'); ?></button>
		</div>
	</div>
</div>
