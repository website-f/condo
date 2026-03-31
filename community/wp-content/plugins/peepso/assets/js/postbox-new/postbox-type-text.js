import peepsodata from 'peepsodata';

const embedData = peepsodata.embed || {};

peepso.class('PostboxTypeText', function (name, peepso, $) {
	const { hooks } = peepso;

	hooks.addAction('postbox_init', 'text', postbox => new (peepso.class(name))(postbox));

	return class extends peepso.class('PostboxType') {
		constructor(postbox, type = 'text') {
			super(postbox, type);

			this.active = true;
			this.embed = null;
			this.embedExceptions = [];

			this.$preview = null;

			this.postbox.$textarea.on('blur keyup', e => {
				if (e.type === 'blur' || e.code === 'Space') this.checkUrlPreview();
			});
		}

		show() {
			super.show();
			this.$preview && this.$preview.show();
		}

		hide() {
			super.hide();
			this.$preview && this.$preview.hide();
		}

		onFilterData(data, postbox) {
			if (postbox === this.postbox && this.active) {
				if (this.embed) {
					data.embed = this.embed;
				} else {
					data.show_preview = 0;
				}
			}

			return data;
		}

		onFilterValidate(valid, postbox, data) {
			if (postbox === this.postbox && this.active) {
				valid = valid && !this.fetching;
			}

			return valid;
		}

		onActionReset(postbox) {
			if (postbox === this.postbox) {
				this.active = true;
				this.embed = null;
				this.embedExceptions = [];
				this.$types.children(`[data-id=${this.type}]`).addClass('pso-active');
				this.$preview && this.$preview.remove();
				delete this.$preview;
			}
		}

		checkUrlPreview(force) {
			if (!+embedData.enable) return; // Skip if embed feature is disabled.
			if (!this.active) return; // Only trigger on simple status update.
			if (this.fetching) return; // Skip if previous fetching is not done yet.
			if (!force && this.embed) return; // Do not overwrite current embed preview.

			// Common TLDs does not need to have a scheme.
			const reCommonTLD =
				/(^|\s)(https?:\/\/)?([a-z0-9-]+\.)+((com|net|org|int|edu|gov|mil|biz|info|mobi|co|io|me)(\.[a-z]{2})?)(?![a-z])(:\d+)?(\/[^\s]*)?/gi;

			// Other TLDs need to have a scheme to make sure it is a URL.
			const reOtherTLD =
				/(^|\s)(https?:\/\/)([a-z0-9-]+\.)+([a-z]{2,24})(:\d+)?(\/[^\s]*)?/gi;

			// Get the first matching URL.
			let content = this.postbox.$textarea.val();
			let url = content.match(reCommonTLD) || content.match(reOtherTLD);
			url = url && url[0] && url[0].trim();
			if (!url) return; // Skip if no url is found.

			// Automatically add HTTPS by default if no scheme is provided.
			if (!url.match(/^https?:\/\//i)) url = `https://${url}`;

			// Disable embedding insecure content if setting is disabled.
			let embedEnableNonSSL = +embedData.enable_non_ssl;
			if (!embedEnableNonSSL && !url.match(/^https:\/\//i)) return;

			// Do not re-fetch previously removed embed preview.
			if (this.embedExceptions && this.embedExceptions.indexOf(url) > -1) return;

			// Set the flag.
			this.fetching = true;

			this.postbox.$loading.show();

			peepso.modules.url
				.getEmbed(url)
				.then(data => {
					this.embed = url;

					// Apply wrapper HTML.
					let html = `<div class="ps-postbox__url-preview url-preview ps-stream-container-narrow">
						<div class="ps-postbox__url-close close">
							<a href="#" class="ps-js-remove"><i class="gcis gci-times"></i></a>
						</div>
						${data.html}
					</div>`;

					// Manually fix problem with WP Embed as described here:
					// https://core.trac.wordpress.org/ticket/34971
					html = html.replace(/\/embed\/(#\?secret=[a-zA-Z0-9]+)?"/g, '/?embed=true$1"');

					this.$preview = $('<div data-ps="url-preview"></div>')
						.insertAfter(this.postbox.$textarea.parent().parent())
						.append(html);

					this.$preview.on('click.ps-embed-remove', '.ps-js-remove', () => {
						this.$preview.off('click.ps-embed-remove').remove();
						this.embed = null;
						// Add to embed exception list until the next post update.
						this.embedExceptions.push(url);
					});

					// Fix Instagram embed issue.
					if (html.match(/\sdata-instgrm-permalink/)) {
						setTimeout(function () {
							try {
								window.instgrm.Embeds.process();
							} catch (e) {}
						}, 1000);
					}
				})
				.catch(() => {})
				.then(() => {
					this.fetching = false;
					this.postbox.$loading.hide();
					this.postbox.$textarea.trigger('input');
				});
		}
	};
});
