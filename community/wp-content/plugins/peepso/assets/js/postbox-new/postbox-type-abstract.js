peepso.class('PostboxType', function (name, peepso, $) {
	const { hooks } = peepso;

	return class {
		constructor(postbox, type) {
			this.type = type;
			this.active = false;

			this.postbox = postbox;
			this.$postbox = postbox.$postbox;
			this.$shortcuts = this.$postbox.find('[data-ps=shortcuts]');
			this.$types = this.$postbox.find('[data-ps=types]');
			this.$typeInputs = this.$postbox.find('[data-ps=type_inputs]');
			this.$typeInput = this.$typeInputs.children(`[data-id=${this.type}]`);

			this.attachEvents();
			this.attachHooks();
		}

		attachEvents() {
			this.$types.on('click', `[data-id=${this.type}]`, e => this.onClickToggle(e));
			this.$shortcuts.on('click', `[data-id=${this.type}]`, e => this.onClickShortcut(e));
		}

		attachHooks() {
			hooks.addFilter('postbox_data', (...args) => this.onFilterData(...args));
			hooks.addFilter('postbox_validate', (...args) => this.onFilterValidate(...args));
			hooks.addFilter('postbox_is_empty', (...args) => this.onFilterIsEmpty(...args));
			hooks.addAction('postbox_saved', (...args) => this.onActionSaved(...args));
			hooks.addAction('postbox_reset', (...args) => this.onActionReset(...args));
			hooks.addAction('postbox_toggle_type', (...args) => this.onActionToggleType(...args));
			hooks.addAction('postbox_toggle_type', (...args) => this._triggerInput(...args), 99);
		}

		show() {
			this.active = true;
			this.$types.children(`[data-id=${this.type}]`).addClass('pso-active');
			this.$typeInputs.children(`[data-id=${this.type}]`).show();

			// todo update placeholder

			hooks.doAction('postbox_toggle_type', this.type, this.postbox);
		}

		hide() {
			this.active = false;
			this.$types.children(`[data-id=${this.type}]`).removeClass('pso-active');
			this.$typeInputs.children(`[data-id=${this.type}]`).hide();
		}

		onFilterData(data, postbox) {
			if (postbox === this.postbox && this.active) {
				// filter data here
			}

			return data;
		}

		onFilterValidate(valid, postbox, data) {
			if (postbox === this.postbox && this.active) {
				// validate data here
			}

			return valid;
		}

		onFilterIsEmpty(empty, postbox, data) {
			if (postbox === this.postbox && this.active) {
				// add checking here
			}

			return empty;
		}

		onActionSaved(postbox) {
			if (postbox === this.postbox) {
				// add action here
			}
		}

		onActionReset(postbox) {
			if (postbox === this.postbox) {
				this.hide();
			}
		}

		onActionToggleType(type, postbox) {
			if (postbox === this.postbox && type !== this.type) {
				this.hide();
			}
		}

		onClickToggle(e) {
			e.preventDefault();

			this.show();
		}

		onClickShortcut(e) {
			e.preventDefault();

			this.postbox.toggle(true);
			this.show();
		}

		_triggerInput(type, postbox) {
			if (postbox === this.postbox && type === this.type) {
				this.postbox.$textarea.trigger('input');
			}
		}
	};
});
