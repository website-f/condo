import $ from 'jquery';
import peepso, { hooks, observer } from 'peepso';
import { userid as USER_ID } from 'peepsodata';

// Initialize location selector on the Create Album dialog.
observer.addFilter('photo_create_album', obj => {
	const $el = obj.popup;
	const $input = $el.find('.ps-js-location');

	if ($input.length) {
		hooks.doAction('init_input_location', $input);
	}

	return obj;
});

// Initialize location selector on the Edit Album Location view.
hooks.addAction('photo_edit_album', container => {
	const $container = $(container);
	const $text = $container.find('.ps-js-album-location-text');
	const $empty = $container.find('.ps-js-album-location-empty');
	const $editor = $container.find('.ps-js-album-location-editor');
	const $btnEdit = $container.find('.ps-js-album-location-edit');
	const $btnRemove = $container.find('.ps-js-album-location-remove');
	const $input = $editor.find('input').eq(0);

	let value;

	$btnEdit.on('click', () => {
		if ($editor.is(':visible')) return;

		$text.hide();
		$empty.hide();
		$btnEdit.hide();
		$btnRemove.hide();
		$editor.show();

		$input.data('original-value', (value = $input.val())); // save original value
		$input.trigger('focus').val(value); // focus

		hooks.doAction('init_input_location', $input);

		$editor.off('click input');

		// handle cancel button
		$editor.on('click', '.ps-js-cancel', () => {
			$input.val(value);
			$editor.off('click').hide();
			$btnEdit.show();

			if (value) {
				$text.show();
				$btnRemove.show();
			} else {
				$empty.show();
			}
		});

		// handle save button
		$editor.on('click', '.ps-js-submit', function () {
			const data = $input.data();
			const params = {
				user_id: USER_ID,
				post_id: data.postId,
				type_extra_field: 'location',
				'location[name]': data.location,
				'location[latitude]': data.latitude,
				'location[longitude]': data.longitude,
				'location[viewport]': JSON.stringify(data.viewport),
				_wpnonce: $('#_wpnonce_set_album_location').val()
			};

			peepso.postJson('photosajax.set_album_extra_field', params, json => {
				if (json.success) {
					$editor.off('click').hide();
					$input.val(data.location);
					$text.find('span').html(data.location);
					$text.show();
					$empty.hide();
					$btnEdit.show();
					$btnRemove.show();
				}
			});
		});
	});

	$btnRemove.on('click', () => {
		const data = $btnRemove.data();
		const params = {
			user_id: USER_ID,
			post_id: data.postId,
			type_extra_field: 'location',
			location: '',
			_wpnonce: $('#_wpnonce_set_album_location').val()
		};

		peepso.postJson('photosajax.set_album_extra_field', params, json => {
			if (json.success) {
				$input.val('');
				$text.find('span').html('');
				$text.hide();
				$empty.show();
				$btnRemove.hide();
			}
		});
	});
});

$(() => {
	document.querySelectorAll('.ps-js-album-location').forEach(container => {
		hooks.doAction('photo_edit_album', container);
	});
});

// edit location
$(function () {
	return;

	var $ct = $('.ps-js-album-location'),
		$text = $ct.find('.ps-js-album-location-text'),
		$empty = $ct.find('.ps-js-album-location-empty'),
		$editor = $ct.find('.ps-js-album-location-editor'),
		$btnEdit = $ct.find('.ps-js-album-location-edit'),
		$btnRemove = $ct.find('.ps-js-album-location-remove'),
		$submit = $editor.find('.ps-js-submit'),
		$input = $editor.find('input').eq(0),
		value;

	// edit location
	$btnEdit.click(function () {});

	// remove location
	$btnRemove.click(function () {
		var data = $btnRemove.data();
		var params = {
			user_id: peepsodata.userid,
			post_id: data.postId,
			type_extra_field: 'location',
			location: '',
			_wpnonce: $('#_wpnonce_set_album_location').val()
		};
		peepso.postJson('photosajax.set_album_extra_field', params, function (json) {
			if (json.success) {
				$input.val('');
				$text.find('span').html('');
				$text.hide();
				$empty.show();
				$btnRemove.hide();
			}
		});
	});
});
