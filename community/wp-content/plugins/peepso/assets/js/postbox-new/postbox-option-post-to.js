import $ from 'jquery';
import { hooks } from 'peepso';

class PostboxPostTo extends peepso.class('PostboxOption') {
	constructor(postbox) {
		super(postbox);

		this.postTo = null;

		this.postbox = postbox;
		this.$postbox = postbox.$postbox;
		this.$toggle = this.$postbox.find('[data-ps=option][data-id=post_to]');
		this.$dropdown = this.$postbox.find('[data-ps=dropdown][data-id=post_to]');
		this.$radio = this.$dropdown.find('[type=radio]');

		// Do not proceed if dropdown is not available.
		if (!this.$dropdown.length) return;

		this.defaultName = this.$toggle.find('span').text();
		this.defaultIcon = this.$toggle.find('i').eq(0).attr('class');

		this.$toggle.on('click', () => this.toggle());
		this.$dropdown.on('click', '[data-value]', e => this.onOptionClick(e));
		this.$dropdown.on('click', '[data-ps=finder] [data-item]', e => this.onItemClick(e));

		hooks.addFilter('postbox_data', 'post_to', (...args) => this.onPostboxData(...args));
		hooks.addAction('postbox_reset', 'post_to', (...args) => this.onPostboxReset(...args));

		hooks.doAction('postbox_option_init', this, postbox);
	}

	show() {
		super.show();

		// Attempt to automatically trigger search if there is only one option.
		if (!this.$radio.length) {
			let $finder = this.$dropdown.find('[data-ps=finder]');
			if ($finder.length) {
				$finder.show().find('[name=query]').trigger('focus');
			}
		}
	}

	set(data = {}) {
		if (data.name && data.icon) {
			this.$toggle.find('i').eq(0).attr('class', data.icon);
			this.$toggle.find('span').html(data.name);
		}

		if (data.value === 'profile') {
			this.postTo = null;
		} else if (data.value === 'anon') {
			this.postTo = { anon_id: data.anonId };
		} else {
			delete data.item;
			delete data.value;
			delete data.name;
			delete data.icon;
			delete data.canPinPosts;
			this.postTo = Object.assign({}, data);
		}

		this.postbox.render();
		this.postbox.$textarea.trigger('input');
	}

	onPostboxData(data, postbox) {
		if (postbox === this.postbox) {
			if (this.postTo && typeof this.postTo === 'object') {
				if (this.postTo.anon_id) {
					Object.assign(data, this.postTo);
				} else {
					Object.assign(data, this.postTo, { acc: undefined });
				}
			}
		}

		return data;
	}

	onPostboxReset(postbox) {
		if (postbox === this.postbox) {
			this.postTo = null;
			this.$toggle.find('i').eq(0).attr('class', this.defaultIcon);
			this.$toggle.find('span').html(this.defaultName);
			this.$dropdown.find('[data-value]').eq(0).trigger('click');
		}
	}

	onOptionClick(e) {
		e.preventDefault();
		e.stopPropagation();

		let $el = $(e.currentTarget);
		let $radio = $el.find('[type=radio]');
		let $finder = $el.find('[data-ps=finder]');
		let name = $el.find('label span').html();
		let icon = $el.find('label i').attr('class');
		let data = Object.assign({}, $el.data(), { name, icon });

		$radio.prop('checked', true);
		this.$dropdown.find('[data-ps=finder]').not($finder).hide();

		if ($finder.length) {
			$finder.show();
			// Attempt to automatically trigger search.
			$finder.is(':visible') && $finder.find('[name=query]').trigger('focus');
		} else {
			this.set(data);
			this.toggle(false);
		}
	}

	onItemClick(e) {
		e.preventDefault();
		e.stopPropagation();

		let $el = $(e.currentTarget);
		let $option = $el.closest('[data-value]');
		let value = $option.data('value');
		let data = Object.assign({}, $el.data(), { value });

		this.set(data);
		this.toggle(false);
	}
}

hooks.addAction('postbox_init', 'post_to', postbox => new PostboxPostTo(postbox));
