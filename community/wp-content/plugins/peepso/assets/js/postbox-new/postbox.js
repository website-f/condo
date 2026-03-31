import $ from 'jquery';
import { observer, hooks } from 'peepso';
import peepsodata from 'peepsodata';

let AJAX_URL = peepsodata.ajaxurl_legacy;
let AJAX_NONCE = peepsodata.peepso_nonce;
let USER_ID = peepsodata.currentuserid;
let VIEWED_USER_ID = peepsodata.userid;
let CONTENT_MAX_LENGTH = +peepsodata.postsize || 0;

let postboxCounter = 0;

class Postbox {
	constructor(postbox, opts = {}) {
		this.id = ++postboxCounter;
		this.opts = Object.assign(opts);

		this.$postbox = $(postbox);
		this.$viewer = this.$postbox.children('[data-ps=postbox-view]');
		this.$editor = this.$postbox.children('[data-ps=postbox-edit]');
		this.$infoName = this.$editor.find('[data-name-orig]');
		this.$infoAvatar = this.$editor.find('img[src-orig]');
		this.$titleExtra = this.$editor.find('[data-ps=title-extra]');
		this.$textarea = this.$editor.find('textarea').attr('maxlength', CONTENT_MAX_LENGTH);
		this.$txShadow = this.$textarea.prev('[data-ps=textarea-shadow]');
		this.$loading = this.$editor.find('[data-ps=loading]').hide();
		this.$charcount = this.$editor.find('[data-ps=charcount]').hide();
		this.$btnSubmit = this.$editor.find('[data-ps=btn-submit]').attr('disabled', 'disabled');

		this.$viewer.on('click', '[data-ps=content]', e => this.onViewContentClick(e));
		this.$editor.on('click', '[data-ps=btn-cancel]', e => this.onEditCancelClick(e));
		this.$editor.on('click', '[data-ps=btn-submit]', e => this.onEditSubmitClick(e));
		this.$textarea.on('input', e => this.onInputTextarea(e));
		this.$textarea.on('paste', e => setTimeout(() => this.onInputTextarea(e), 100));
		this.$textarea.on('keypress', e => this.onKeyPressTextarea(e));

		hooks.doAction('postbox_init', this);
	}

	render() {
		let data = this.data();
		let title = hooks.applyFilters('postbox_title_extra', [], data, this);

		title.length ? this.$titleExtra.html(`${title.join(' ')}.`) : this.$titleExtra.empty();

		if (typeof data.anon_id !== 'undefined') {
			this.$infoName.html(this.$infoName.data('name-anon'));
			this.$infoAvatar.attr('src', this.$infoAvatar.attr('src-anon'));
		} else {
			this.$infoName.html(this.$infoName.data('name-orig'));
			this.$infoAvatar.attr('src', this.$infoAvatar.attr('src-orig'));
		}
	}

	data() {
		let data = {
			content: this.$textarea.val().trim(),
			id: USER_ID,
			uid: VIEWED_USER_ID,
			acc: 10,
			type: 'activity'
		};

		return hooks.applyFilters('postbox_data', data, this);
	}

	toggle(expand) {
		expand ? this.expand() : this.collapse();
	}

	expand() {
		this.$viewer.hide();
		this.$editor.show();
		this.$textarea.ps_autosize();

		setTimeout(() => {
			$(document)
				.off(`mouseup.postbox-${this.id}`)
				.on(`mouseup.postbox-${this.id}`, e => {
					if (this.$editor.filter(e.target).length) return;
					if (this.$editor.find(e.target).length) return;
					this.collapse();
				});
		}, 1);
	}

	collapse() {
		// Do not collapse postbox if it is not empty.
		let data = this.data();
		let content = data.content.trim();
		let empty = hooks.applyFilters('postbox_is_empty', !content, this, data);
		if (!empty) return;

		this.$editor.hide();
		this.$viewer.show();
		$(document).off(`mouseup.postbox-${this.id}`);
	}

	reset() {
		this.$textarea.val('');
		this.$txShadow.html('');
		this.$btnSubmit.attr('disabled', 'disabled');

		hooks.doAction('postbox_reset', this);
		observer.removeFilter('beforeunload', this.beforeUnloadHandler);
	}

	cancel() {
		return $.Deferred(defer => {
			this.reset();
			defer.resolve();
		});
	}

	submit() {
		return $.Deferred(defer => {
			if (this.submitting) return defer.reject();

			this.submitting = true;
			this.$btnSubmit.addClass('pso-btn--loading');

			let params = {
				url: `${AJAX_URL}postbox.post`,
				type: 'POST',
				data: this.data(),
				dataType: 'json',
				beforeSend: xhr => xhr.setRequestHeader('X-PeepSo-Nonce', AJAX_NONCE)
			};

			let xhr = $.ajax(params);
			xhr.fail(defer.reject);
			xhr.done(json => {
				if (json.success) {
					hooks.doAction('postbox_saved', this);
					hooks.doAction('activitystream_append', json);
					this.reset();
					defer.resolve();
				} else {
					defer.reject();
				}
			});
			xhr.always(() => {
				delete this.submitting;
				this.$btnSubmit.removeClass('pso-btn--loading');
			});
		});
	}

	updateCharCount(text = '') {
		let maxLen = hooks.applyFilters('postbox_max_length', CONTENT_MAX_LENGTH, this);
		let len = Math.max(0, maxLen - text.length);

		this.$charcount.html(len + '');

		// Do not show the counter if it is below 50% of the characters limit.
		if (len && len / maxLen > 0.5) {
			this.$charcount.hide();
			return;
		}

		this.$charcount.show();

		// Update character counter text color.
		let color = '';
		if (0 === len || len / maxLen < 0.1) {
			color = 'red'; // Above 90% of the characters limit.
		} else if (len / maxLen < 0.25) {
			color = 'orange'; // Above 75% of the characters limit.
		}

		this.$charcount.css({ color: color });
	}

	onInputTextarea() {
		let data = this.data();
		let content = data.content.trim();
		let valid = hooks.applyFilters('postbox_validate', !!content, this, data);

		if (valid) {
			this.$btnSubmit.removeAttr('disabled');
		} else {
			this.$btnSubmit.attr('disabled', 'disabled');
		}

		// Update character counter.
		this.updateCharCount(content);

		// Toggle beforeunload warning if postbox is not empty.
		if (hooks.applyFilters('postbox_is_empty', !content, this, data)) {
			if (this.beforeUnloadHandler) {
				observer.removeFilter('beforeunload', this.beforeUnloadHandler);
				delete this.beforeUnloadHandler;
			}
		} else {
			if (!this.beforeUnloadHandler) {
				this.beforeUnloadHandler = () => true;
				observer.addFilter('beforeunload', this.beforeUnloadHandler);
			}
		}
	}

	onKeyPressTextarea() {
		if (this.$textarea.val().length >= CONTENT_MAX_LENGTH) return false;
	}

	onViewContentClick(e) {
		e.preventDefault();

		this.toggle(true);
		this.$textarea.trigger('focus');
	}

	onEditCancelClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.cancel().done(() => this.toggle(false));
	}

	onEditSubmitClick(e) {
		e.preventDefault();
		e.stopPropagation();

		// Trigger oninput handler before submitting the postbox
		// to make sure the last postbox state validity is taken into account.
		this.onInputTextarea();
		requestAnimationFrame(() => {
			if (!this.$btnSubmit.is(':disabled')) {
				this.submit().done(() => this.toggle(false));
			}
		});
	}
}

// Initialize postbox.
$(() => document.querySelectorAll('[data-ps=postbox]').forEach(p => new Postbox(p)));
