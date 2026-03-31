import $ from 'jquery';
import _ from 'underscore';
import { ajax, dialog, hooks, observer } from 'peepso';
import {
	profile as profileData,
	ajaxurl_legacy as AJAX_URL,
	peepso_nonce as AJAX_NONCE
} from 'peepsodata';

$(function () {
	let $verify = $('input[name=verify_password]');
	if (!$verify.length) return;

	let $form = $verify.closest('form');
	let $fields = $form.find('input[type=text], input[type=password]').not($verify);
	let $save = $form.find('[type=submit]');

	// Save initial field values.
	$fields.each((i, el) => $(el).data('ps-value', $(el).data('value') || el.value));

	// Determine whether the form field values are changed.
	function isChanged() {
		let changed = $fields.map((i, el) => el.value !== $(el).data('ps-value')).toArray();
		return changed.includes(true);
	}

	// Determine whether the form field values are validated.
	function isValidated() {
		let validated = $fields.map((i, el) => $(el).data('ps-validated')).toArray();
		return !validated.includes(false);
	}

	// Remove invalid characters on username field.
	$fields.filter('input[name=user_nicename]').on('input', function () {
		var sanitized = this.value.replace(/[^a-z0-9-_\.@]/gi, '');
		if (this.value !== sanitized) {
			this.value = sanitized;
		}
	});

	// Password validation.
	$fields.filter('input[name=change_password]').on('input', function () {
		let $input = $(this);
		let $errors = $input.closest('.ps-form__field').find('.ps-form__error');
		let token = $input.data('ps-validate-token') || 0;

		$input.data('ps-validate-token', ++token);

		if (!$input.val().trim()) {
			$input.removeData('ps-validated');
			$errors.empty().hide();
			return;
		}

		$input.data('ps-validated', false);

		validate($input).done(errors => {
			// Skip if token is expired.
			if ($input.data('ps-validate-token') !== token) return;

			if (errors) {
				let html = errors.map(e => `<div class="ps-form__error-item">${e}</div>`);
				$errors.html(html).show();
			} else {
				$input.removeData('ps-validated');
				$errors.empty().hide();
				toggleEditing();
			}
		});
	});

	// Validate password input.
	function validate($input) {
		let value = observer.applyFilters('profile_field_save', $input.val(), $input);
		let params = { name: 'password', password: value };

		return $.Deferred(defer => {
			$.ajax({
				url: `${AJAX_URL}profilefieldsajax.validate_register`,
				type: 'post',
				dataType: 'json',
				data: params,
				beforeSend: xhr => xhr.setRequestHeader('X-PeepSo-Nonce', AJAX_NONCE)
			}).always(json => {
				json = json.responseJSON || json;
				json.errors && json.errors.length ? defer.resolve(json.errors) : defer.resolve();
			});
		});
	}

	// Enable/disable editing form field values.
	function toggleEditing() {
		requestAnimationFrame(() => {
			if ($verify.val().trim().length < 5) {
				$fields.attr('readonly', 'readonly');
				$save.attr('disabled', 'disabled');
			} else {
				$fields.removeAttr('readonly');
				isChanged() && isValidated()
					? $save.removeAttr('disabled')
					: $save.attr('disabled', 'disabled');
			}
		});
	}

	// Toggle editing state on page load and input event.
	$verify.on('input', toggleEditing);
	$fields.on('input', toggleEditing);
	toggleEditing();
});

/**
 * Profile deletion script.
 */
$(function () {
	let popup;

	$('.ps-js-profile-delete').on('click', e => {
		e.preventDefault();
		showDialog();
	});

	function showDialog() {
		if (!popup) {
			popup = dialog(profileData.template_profile_deletion, { destroyOnClose: false }).show();
			popup.$el.find('form').on('submit', e => e.preventDefault());
			popup.$el.find('.ps-js-cancel').on('click', () => popup.hide());
			popup.$el.find('.ps-js-submit').on('click', e => submit(e.currentTarget));
			hooks.doAction('init_password_preview');
		}

		popup.$el.find('#ps-js-profile-deletion-pass').val('');
		popup.$el.find('.ps-js-error').hide();
		popup.show();
	}

	function submit(button) {
		let $button = $(button).attr('disabled', 'disabled'),
			$loading = $button.find('.ps-js-loading').show(),
			$error = popup.$el.find('.ps-js-error').hide(),
			password = popup.$el.find('#ps-js-profile-deletion-pass').val();

		ajax.post('profile.delete_profile', { password })
			.then(json => {
				if (json.success) {
					if (json.data) {
						dialog(json.data.messages).show();
						setTimeout(() => (window.location = json.data.url), 3000);
					}
				} else if (json.errors) {
					$error.html(json.errors[0]).show();
				}
			})
			.always(() => {
				$loading.hide();
				$button.removeAttr('disabled');
			});
	}
});

/**
 * Data export request script.
 */
$(function () {
	let popup;

	$('.ps-js-export-data-request').on('click', e => {
		e.preventDefault();
		showDialog();
	});

	function showDialog() {
		if (!popup) {
			popup = dialog(profileData.template_export_data_request, {
				destroyOnClose: false
			}).show();

			popup.$el.find('.ps-js-cancel').on('click', () => popup.hide());
			popup.$el.find('.ps-js-submit').on('click', () => submit());
			popup.$el.find('form').on('submit', e => {
				e.preventDefault();
				submit();
			});
			hooks.doAction('init_password_preview');
		}

		popup.$el.find('#ps-js-export-data-request-pass').val('');
		popup.$el.find('.ps-js-error').hide();
		popup.show();
	}

	function submit() {
		let $button = popup.$el.find('.ps-js-submit').attr('disabled', 'disabled'),
			$loading = $button.find('.ps-js-loading').show(),
			$error = popup.$el.find('.ps-js-error').hide(),
			password = popup.$el.find('#ps-js-export-data-request-pass').val();

		ajax.post('profile.request_account_data', { password })
			.then(json => {
				if (json.success) {
					if (json.data) {
						let title = popup.title(),
							successPopup = dialog(json.data.messages, { title });

						popup.hide();
						successPopup.show();
						setTimeout(() => {
							window.location = json.data.url;
						}, 3000);
					}
				} else if (json.errors) {
					$error.html(json.errors[0]).show();
				}
			})
			.always(() => {
				$loading.hide();
				$button.removeAttr('disabled');
			});
	}
});

/**
 * Data export download script.
 */
$(function () {
	let popup;

	$('.ps-js-export-data-download').on('click', e => {
		e.preventDefault();
		showDialog();
	});

	function showDialog() {
		if (!popup) {
			popup = dialog(profileData.template_export_data_download, {
				destroyOnClose: false
			}).show();

			popup.$el.find('.ps-js-cancel').on('click', () => popup.hide());
			popup.$el.find('.ps-js-submit').on('click', () => submit());
			popup.$el.find('form').on('submit', e => {
				e.preventDefault();
				submit();
			});
			hooks.doAction('init_password_preview');
		}

		popup.$el.find('#ps-js-export-data-download-pass').val('');
		popup.$el.find('.ps-js-error').hide();
		popup.show();
	}

	function submit() {
		let $button = popup.$el.find('.ps-js-submit').attr('disabled', 'disabled'),
			$loading = $button.find('.ps-js-loading').show(),
			$error = popup.$el.find('.ps-js-error').hide(),
			password = popup.$el.find('#ps-js-export-data-download-pass').val();

		ajax.post('profile.download_account_data', { password })
			.then(json => {
				if (json.success) {
					if (json.data) {
						let title = popup.title(),
							successPopup = dialog(json.data.messages, { title });

						popup.hide();
						successPopup.show();
						setTimeout(() => {
							successPopup.hide();
							window.location = json.data.url;
						}, 1500);
					}
				} else if (json.errors) {
					$error.html(json.errors[0]).show();
				}
			})
			.always(() => {
				$loading.hide();
				$button.removeAttr('disabled');
			});
	}
});

/**
 * Data export delete script.
 */
$(function () {
	let popup;

	$('.ps-js-export-data-delete').on('click', e => {
		e.preventDefault();
		showDialog();
	});

	function showDialog() {
		if (!popup) {
			popup = dialog(profileData.template_export_data_delete, {
				destroyOnClose: false
			}).show();

			popup.$el.find('.ps-js-cancel').on('click', () => popup.hide());
			popup.$el.find('.ps-js-submit').on('click', () => submit());
			popup.$el.find('form').on('submit', e => {
				e.preventDefault();
				submit();
			});
			hooks.doAction('init_password_preview');
		}

		popup.$el.find('#ps-js-export-data-delete-pass').val('');
		popup.$el.find('.ps-js-error').hide();
		popup.show();
	}

	function submit() {
		let $button = popup.$el.find('.ps-js-submit').attr('disabled', 'disabled'),
			$loading = $button.find('.ps-js-loading').show(),
			$error = popup.$el.find('.ps-js-error').hide(),
			password = popup.$el.find('#ps-js-export-data-delete-pass').val();

		ajax.post('profile.delete_account_data_archive', { password })
			.then(json => {
				if (json.success) {
					if (json.data) {
						let title = popup.title(),
							successPopup = dialog(json.data.messages, { title });

						popup.hide();
						successPopup.show();
						setTimeout(() => {
							window.location = json.data.url;
						}, 3000);
					}
				} else if (json.errors) {
					$error.html(json.errors[0]).show();
				}
			})
			.always(() => {
				$loading.hide();
				$button.removeAttr('disabled');
			});
	}
});
