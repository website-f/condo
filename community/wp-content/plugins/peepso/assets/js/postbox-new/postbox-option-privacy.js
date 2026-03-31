import $ from 'jquery';
import { hooks } from 'peepso';

class PostboxPrivacy extends peepso.class('PostboxOption') {
	constructor(postbox) {
		super(postbox);

		this.postbox = postbox;
		this.$postbox = postbox.$postbox;
		this.$toggle = this.$postbox.find('[data-ps=option][data-id=privacy]');
		this.$dropdown = this.$postbox.find('[data-ps=dropdown][data-id=privacy]');
		this.$cancel = this.$dropdown.find('[data-ps=btn-cancel]');

		this.privacy = +this.$dropdown.find('script[data-var=default_privacy]').text();

		this.$toggle.on('click', () => this.toggle());
		this.$dropdown.on('click', '[data-id]', e => this.onItemClick(e));
		this.$cancel.on('click', e => this.onCancelClick(e));

		hooks.addFilter('postbox_data', 'privacy', (...args) => this.onPostboxData(...args));
		hooks.addFilter('postbox_validate', 'priv', (...args) => this.onPostboxValidate(...args));
	}

	onPostboxData(data, postbox) {
		if (postbox === this.postbox) {
			data.acc = this.privacy;
		}

		return data;
	}

	onPostboxValidate(valid, postbox, data) {
		if (postbox === this.postbox) {
			// No need to alter valid state, just hide privacy option if post_to is set.
			if (data.module_id) {
				this.$toggle.hide();
				this.toggle(false);
			} else {
				this.$toggle.show();
			}
		}

		return valid;
	}

	onItemClick(e) {
		e.preventDefault();
		e.stopPropagation();

		let $el = $(e.currentTarget);
		let $icon = $el.find('[data-icon]');
		let $label = $el.find('[data-label]');
		let data = $el.data();

		this.privacy = data.id;
		this.toggle(false);
		this.$toggle.find('[data-icon]').attr('class', $icon.attr('class'));
		this.$toggle.find('[data-label]').html($label.html());
	}

	onCancelClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.toggle(false);
	}
}

hooks.addAction('postbox_init', 'privacy', postbox => new PostboxPrivacy(postbox));
