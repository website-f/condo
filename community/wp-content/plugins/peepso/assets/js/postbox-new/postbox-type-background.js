peepso.class('PostboxTypeBackground', function (name, peepso, $) {
	const { hooks, observer, ContentEditable } = peepso;
	const { backgrounds: backgroundsData = {} } = window.peepsodata;

	// Configurations.
	const POST_MAX_LENGTH = +backgroundsData.post_max_length || 0;

	hooks.addAction('postbox_init', 'background', postbox => new (peepso.class(name))(postbox));

	return class extends peepso.class('PostboxType') {
		constructor(postbox, type = 'background') {
			super(postbox, type);

			this.$presets = this.$typeInput.find('[data-preset-id]');
			this.$background = this.$typeInput.find('[data-ps=background]');
			this.$text = this.$typeInput.find('[data-ps=text]');
			this.$warning = this.$typeInput.find('[data-ps=warning]').hide();

			this.$background.on('click', () => this.focus());
			this.$presets.on('click', e => this.onClickPreset(e));

			this.editor = new ContentEditable(this.$text[0], {
				onChange: this.onChange.bind(this),
				transform: this.contentTransform.bind(this)
			});

			hooks.addFilter('postbox_max_length', (...args) => this.onFilterMaxLength(...args));
		}

		show() {
			/** @see PostboxType.show */
			this.active = true;
			this.$types.children(`[data-id=${this.type}]`).addClass('pso-active');
			this.$typeInputs.children(`[data-id=${this.type}]`).show();

			this.postbox.$textarea.parent().hide();

			// Update text content from the original textarea.
			let contentObj = { content: this.postbox.$textarea.val() };
			this.postbox.$textarea.ps_tagging('val', val => (contentObj.content = val));
			this.editor.value(contentObj.content);

			// Call in-place content transform hook.
			observer.doAction('postbox_content_update', this.$text[0], this.editor);

			// Select the first preset if nothing is selected.
			let $selected = this.$presets.filter('.active');
			if (!$selected.length) {
				this.select(this.$presets.eq(0));
			}

			/** @see PostboxType.show */
			hooks.doAction('postbox_toggle_type', this.type, this.postbox);
		}

		select(preset) {
			let $preset = $(preset),
				bgImage = $preset.css('background-image'),
				bgColor = $preset.attr('data-background'),
				textColor = $preset.attr('data-text-color'),
				id = $preset.attr('data-preset-id');

			// Update background and text.
			this.$background.css('background-image', bgImage).attr('data-background', bgColor);
			this.$text
				.css('color', textColor)
				.attr('data-text-color', textColor)
				.attr('data-preset-id', id);

			// Update selected preset.
			$preset.addClass('active');
			this.$presets.not($preset).removeClass('active');

			// Sync placeholder text color.
			if (!this.__styleOverride) {
				this.__styleOverride = document.createElement('style');
				this.__styleOverride.id = 'peepso-post-background';
				document.head.appendChild(this.__styleOverride);
			}
			this.__styleOverride.innerHTML = `.ps-post__background-text:before { color: ${textColor} !important }`;

			// Focus on the "textarea".
			this.focus();
		}

		focus() {
			setTimeout(() => {
				let value = this.editor.value();

				if (value) {
					let selection = window.getSelection();
					let children = this.$text[0].children;
					let lastChild = children[children.length - 1];

					// TODO
				} else {
					// Should just focus on the element if it is empty.
					this.$text[0].focus();
				}
			}, 0);
		}

		contentTransform(elem, editor) {
			// Call in-place content transform hook.
			observer.doAction('postbox_content_transform', elem, editor);
		}

		onChange() {
			let content = this.editor.value();

			this.postbox.$textarea.val(content);
			// this.postbox.$textarea.trigger('input');
			// hack, should not call this function directly.
			// however there is currently no way to trigger event without executing all other textarea's event handler.
			this.postbox.onInputTextarea();

			let showWarning = POST_MAX_LENGTH && content.length > POST_MAX_LENGTH;
			showWarning ? this.$warning.show() : this.$warning.hide();

			// Call in-place content transform hook.
			observer.doAction('postbox_content_change', this.$text[0], this.editor);
		}

		onFilterData(data, postbox) {
			if (postbox === this.postbox && this.active) {
				data.type = 'post_backgrounds';
				data.content = this.editor.value();
				data.preset_id = this.$text.attr('data-preset-id');
				data.text_color = this.$text.attr('data-text-color');
				data.background = this.$background.attr('data-background');
			}

			return data;
		}

		onFilterValidate(valid, postbox, data) {
			if (postbox === this.postbox && this.active) {
				if (!data.preset_id) {
					valid = false;
					this.$warning.hide();
				} else if (!data.content) {
					valid = false;
					this.$warning.hide();
				} else if (POST_MAX_LENGTH && data.content.length > POST_MAX_LENGTH) {
					valid = false;
					this.$warning.show();
				} else {
					valid = true;
					this.$warning.hide();
				}
			}

			return valid;
		}

		onFilterIsEmpty(empty, postbox, data) {
			if (postbox === this.postbox && this.active) {
				if (data.content) empty = false;
			}

			return empty;
		}

		onFilterMaxLength(length, postbox) {
			if (postbox === this.postbox && this.active) {
				length = POST_MAX_LENGTH;
			}

			return length;
		}

		onActionReset(postbox) {
			if (postbox === this.postbox) {
				this.hide();
				this.postbox.$textarea.parent().show();

				this.select(this.$presets.eq(0));
				this.editor.value('');
				this.$warning.hide();
			}
		}

		onActionToggleType(type, postbox) {
			if (postbox === this.postbox && type !== this.type) {
				if (this.$typeInput.is(':visible')) {
					this.postbox.$textarea.parent().show();
				}
			}

			super.onActionToggleType(type, postbox);
		}

		onClickPreset(e) {
			e.preventDefault();
			e.stopPropagation();

			this.select(e.currentTarget);
		}
	};
});
