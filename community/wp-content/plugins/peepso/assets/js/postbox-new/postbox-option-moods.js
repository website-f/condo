import $ from 'jquery';
import { hooks } from 'peepso';

class PostboxMoods extends peepso.class('PostboxOption') {
	constructor(postbox) {
		super(postbox);

		this.mood = null;

		this.postbox = postbox;
		this.$postbox = postbox.$postbox;
		this.$toggle = this.$postbox.find('[data-ps=option][data-id=moods]');
		this.$dropdown = this.$postbox.find('[data-ps=dropdown][data-id=moods]');
		this.$cancel = this.$dropdown.find('[data-ps=btn-cancel]');
		this.$remove = this.$dropdown.find('[data-ps=btn-remove]');

		// parse opts
		this.titleTemplate = this.$dropdown.find('script[data-tmpl=title]').text();

		this.postbox.$viewer.on('click', '[data-ps=moods]', e => this.onViewMoodsClick(e));
		this.$toggle.on('click', () => this.toggle());
		this.$dropdown.on('click', '[data-mood]', e => this.onItemClick(e));
		this.$cancel.on('click', e => this.onCancelClick(e));
		this.$remove.on('click', e => this.onRemoveClick(e));

		hooks.addFilter('postbox_data', 'moods', (...args) => this.onPostboxData(...args));
		hooks.addFilter('postbox_is_empty', (...args) => this.onFilterIsEmpty(...args));
		hooks.addFilter('postbox_title_extra', 'moods', (...args) => this.onPostboxTitle(...args));
		hooks.addAction('postbox_reset', 'moods', (...args) => this.onPostboxReset(...args));
	}

	set(id, mood) {
		if (!id) {
			this.mood = null;
			this.$toggle.removeClass('pso-postbox__option--active');
			this.$dropdown.find('.active').removeClass('active');
			this.$remove.hide();
		} else {
			let $item = this.$dropdown.find(`[data-id=${id}]`).addClass('active');

			this.mood = [id, mood];
			this.$toggle.addClass('pso-postbox__option--active');
			this.$dropdown.find('.active').not($item).removeClass('active');
			this.$remove.show();
		}

		this.postbox.render();
		this.postbox.$textarea.trigger('input');
	}

	onPostboxData(data, postbox) {
		if (postbox === this.postbox) {
			data.mood = this.mood ? this.mood[0] : undefined;
		}

		return data;
	}

	onFilterIsEmpty(empty, postbox, data) {
		if (postbox === this.postbox) {
			if (data.mood) empty = false;
		}

		return empty;
	}

	onPostboxTitle(list = [], data, postbox) {
		if (postbox === this.postbox) {
			if (this.mood) {
				let html = this.titleTemplate
					.replace('##icon##', this.mood[0])
					.replace('##mood##', this.mood[1]);

				list.push(html);
			}
		}

		return list;
	}

	onPostboxReset(postbox) {
		if (postbox === this.postbox) {
			this.set(null);
		}
	}

	onViewMoodsClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.postbox.toggle(true);
		this.toggle(true);
	}

	onItemClick(e) {
		e.preventDefault();
		e.stopPropagation();

		let data = $(e.currentTarget).data();
		let remove = this.mood && this.mood[0] === data.id;

		remove ? this.set(null) : this.set(data.id, data.mood);
		this.toggle(false);
	}

	onRemoveClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.set(null);
		this.toggle(false);
	}

	onCancelClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.toggle(false);
	}
}

hooks.addAction('postbox_init', 'moods', postbox => new PostboxMoods(postbox));
