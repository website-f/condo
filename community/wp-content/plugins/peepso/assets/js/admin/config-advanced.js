jQuery(function($) {
	var $loadMore = $('select[name=loadmore_enable]'),
		$loadMoreRepeat = $('select[name=loadmore_repeat]'),
		$enableRepost = $('input[name=site_repost_enable]'),
		$repostPosition = $('select[name=site_repost_position]');

	$loadMore.on('change', function() {
		var $field = $loadMoreRepeat.closest('.form-group');
		this.value == 0 ? $field.hide() : $field.show();
	});
	$loadMore.triggerHandler('change');

	$enableRepost.on('click', function () {
		var $field = $repostPosition.closest('.form-group');
		this.checked ? $field.show() : $field.hide();
	});
	$enableRepost.triggerHandler('click');
});
