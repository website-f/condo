// Handle toggle location.
jQuery(function ($) {
	const $enable = $('input[name=location_enable]');
	const $enableNewApi = $('input[name=location_gmap_api_new]');

	$enable.on('click', function () {
		const $fields = $(this).closest('.form-group').nextAll('.form-group');

		if (this.checked) {
			$fields.show();
			$enableNewApi.triggerHandler('click');
		} else {
			$fields.hide();
		}
	});

	$enableNewApi.on('click', function () {
		const $fields = $(this).closest('.form-group').nextAll('.form-group');

		if (this.checked) {
			$fields.show();
		} else {
			$fields.hide();
		}
	});

	$enable.triggerHandler('click');
});
