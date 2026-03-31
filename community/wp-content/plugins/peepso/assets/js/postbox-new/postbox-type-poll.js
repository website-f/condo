import { polls as pollsData } from 'peepsodata';

peepso.class('PostboxTypePoll', function (name, peepso, $) {
	const { hooks } = peepso;

	hooks.addAction('postbox_init', 'poll', postbox => new (peepso.class(name))(postbox));

	return class extends peepso.class('PostboxType') {
		constructor(postbox, type = 'poll') {
			super(postbox, type);

			this.$typeInput = this.$typeInputs.children(`[data-id=${this.type}]`);
			this.$allowMultiple = this.$typeInput.find('[data-ps=allow-multiple]');

			this.$typeInput.on('click', '[data-ps=btn-add]', e => this.onClickBtnAdd(e));
			this.$typeInput.on('click', '[data-ps=btn-delete]', e => this.onClickBtnDelete(e));
			this.$typeInput.on('input', '[type=text]', e => this.onInputInput(e));

			this.$typeInput.find('[data-ps=sortable]').sortable({
				handle: '[data-ps=sortable-handle]',
				update: () => this.reorderPlaceholders(),
				// Fix jQuery UI Sortable problem.
				// https://github.com/angular-ui/ui-sortable/issues/286#issuecomment-333867678
				start(ev, ui) {
					let scrollTop = $(window).scrollTop();
					if (scrollTop > 0) ui.helper.css('margin-top', scrollTop);
				},
				stop(ev, ui) {
					ui.item.css('margin-top', 0);
				}
			});
		}

		onFilterData(data, postbox) {
			if (postbox === this.postbox && this.active) {
				data.type = 'poll';

				// Get non-empty options.
				data.options = this.$typeInput
					.find('[data-ps=option] [type=text]')
					.map((i, el) => el.value.trim())
					.get()
					.filter(str => str);

				// Check for multiple selection settings.
				data.allow_multiple = 0;
				if (this.$allowMultiple.length && this.$allowMultiple.is(':checked')) {
					data.allow_multiple = 1;
				}
			}

			return data;
		}

		onFilterValidate(valid, postbox, data) {
			if (postbox === this.postbox && this.active) {
				valid = data.content && data.options.length > 1;
			}

			return valid;
		}

		onFilterIsEmpty(empty, postbox, data) {
			if (postbox === this.postbox && this.active) {
				if (data.options.length > 0) empty = false;
			}

			return empty;
		}

		onActionReset(postbox) {
			if (postbox === this.postbox) {
				this.hide();

				// Reset UI.
				this.$allowMultiple.prop('checked', false);
				this.$typeInput.find('[data-ps=option]').each((i, el) => {
					i < 2 ? $(el).find('[type=text]').val('') : $(el).remove();
				});
			}
		}

		onClickBtnAdd(e) {
			e.preventDefault();
			e.stopPropagation();

			let $options = this.$typeInput.find('[data-ps=option]');
			let $clone = $options.eq(0).clone();

			$clone.find('[type=text]').val('');
			$clone.appendTo(this.$typeInput.find('[data-ps=sortable]'));
			this.reorderPlaceholders();
		}

		onClickBtnDelete(e) {
			e.preventDefault();
			e.stopPropagation();

			let $option = $(e.currentTarget).closest('[data-ps=option]');
			if ($option.siblings('[data-ps=option]').length >= 2) {
				$option.remove();
				this.reorderPlaceholders();
			}
		}

		onInputInput(e) {
			this.postbox.$textarea.trigger('input');
		}

		reorderPlaceholders() {
			let $inputs = this.$typeInput.find('[data-ps=option] [type=text]');
			let placeholder = pollsData.textOptionPlaceholder;

			$inputs.each((i, el) => $(el).attr('placeholder', placeholder.replace('%d', i + 1)));
		}
	};
});
