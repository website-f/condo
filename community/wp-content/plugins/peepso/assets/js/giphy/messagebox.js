import _ from 'underscore';
import Giphy from './giphy';

function maybeDeclare(name) {
	if (peepso.class(name)) return;

	peepso.class(name, function (name, peepso, $) {
		const { hooks, template } = peepso;

		const giphyData = window.peepsogiphydata || {};
		const rendition = giphyData.giphy_rendition_messages || 'fixed_width';

		return class extends peepso.class('MessageboxType') {
			constructor(messagebox, type = 'giphy') {
				super(messagebox, type);

				this.giphy = null;
				this.selectedImage = null;

				this.$typeInput = this.$typeInputs.children(`[data-id=${this.type}]`);
				this.$preview = this.$typeInput.find('[data-ps=preview]').hide();
				this.$selector = this.$typeInput.find('[data-ps=container]').show();
				this.$loading = this.$selector.find('[data-ps=loading]');
				this.$query = this.$selector.find('[data-ps=query]');
				this.$result = this.$selector.find('[data-ps=list]');
				this.$slider = this.$selector.find('[data-ps=slider]');

				this.itemTemplate = template(this.$typeInput.find('[data-tmpl=item]').html());

				this.$query.on('input', e => this.onInputQuery(e));
				this.$result.on('click', '[data-ps=item]', e => this.onClickItem(e));
				this.$preview.on('click', '[data-ps=btn-change]', e => this.onClickBtnChange(e));
				this.$slider.on('click', '[data-ps=nav-left]', e => this.onClickSlider(e, 'left'));
				this.$slider.on('click', '[data-ps=nav-right]', e =>
					this.onClickSlider(e, 'right')
				);
			}

			show() {
				super.show();

				// Focus and highlight the search box on show.
				function highlight($query) {
					$query.show();
					$query[0].focus();
					$query.css({ backgroundColor: peepso.getLinkColor() });
					$query.css({ transition: 'background-color 3s ease' });
					setTimeout(() => {
						$query.css({ backgroundColor: '' });
					}, 500);
				}

				if (this.giphy) {
					highlight(this.$query);
				} else {
					this.giphy = Giphy.getInstance();
					this.search().done(() => highlight(this.$query));
				}
			}

			search(keyword = '') {
				this.$result.hide();
				this.$loading.show();

				return $.Deferred(defer => {
					clearTimeout(this.searchDelay);
					let searchDelay = (this.searchDelay = setTimeout(() => {
						this.giphy.search(keyword).done(data => {
							if (this.searchDelay === searchDelay) {
								this.render(data);
								this.$loading.hide();
								this.$result.show();
								this.$query.show();
							}

							defer.resolveWith(this);
						});
					}, 1000));
				});
			}

			render(data) {
				let html = data.map(item => {
					var images = item.images,
						src = images[rendition],
						html = '',
						preview;

					if (src) {
						preview =
							images.preview_gif ||
							images.downsized_still ||
							images.fixed_width_still ||
							images.original_still;

						if (preview) {
							Object.assign(item, { src: src.url, preview: preview.url });
							html = this.itemTemplate(item);
						}
					}

					return html;
				});

				this.$result.html(html.join(''));
			}

			onFilterData(data, messagebox) {
				if (messagebox === this.messagebox && this.active) {
					data.type = 'giphy';
					data.giphy = this.selectedImage || undefined;
				}

				return data;
			}

			onFilterValidate(valid, messagebox, data) {
				if (messagebox === this.messagebox && this.active) {
					valid = !!data.giphy;
				}

				return valid;
			}

			onFilterIsEmpty(empty, messagebox, data) {
				if (messagebox === this.messagebox && this.active) {
					if (data.giphy) empty = false;
				}

				return empty;
			}

			onActionReset(messagebox) {
				if (messagebox === this.messagebox) {
					this.hide();

					this.selectedImage = null;

					this.$preview.hide();
					this.$selector.show();
				}
			}

			onInputQuery(e) {
				let keyword = e.target.value;

				this.$result.hide();
				this.$loading.show();
				this.search(keyword.trim());
			}

			onClickItem(e) {
				let $item = $(e.currentTarget);
				let $img = $item.find('img');
				let srcPreview = $img.attr('src');
				let srcActual = $img.attr('data-url');

				this.selectedImage = srcActual;

				this.$selector.hide();
				this.$preview.find('img').attr('src', srcPreview);
				this.$preview.show();
				this.messagebox.$textarea.trigger('input');
			}

			onClickBtnChange(e) {
				this.selectedImage = null;

				this.$preview.hide();
				this.$selector.show();
				this.messagebox.$textarea.trigger('input');
			}

			onClickSlider(e, direction) {
				e.preventDefault();
				e.stopPropagation();

				let isRTL = peepso.rtl;
				let viewportWidth = this.$slider.width();
				let currMargin =
					parseInt(this.$result.css(isRTL ? 'marginRight' : 'marginLeft')) || 0;

				if (direction === (isRTL ? 'right' : 'left') /* scroll left */) {
					currMargin = Math.min(currMargin + viewportWidth, 0);
				} else if (direction === (isRTL ? 'left' : 'right') /* scroll right */) {
					let $lastItem = this.$result.children('[data-ps=item]').last();
					let maxMargin = isRTL
						? Math.abs($lastItem.position().left)
						: $lastItem.position().left + $lastItem.width() - viewportWidth;

					currMargin -= Math.min(viewportWidth, maxMargin);
				}

				this.$result.css(isRTL ? 'marginRight' : 'marginLeft', currMargin);
			}
		};
	});
}

peepso.hooks.addAction('messagebox_init', 'giphy', messagebox => {
	const name = 'MessageboxTypeGiphy';
	maybeDeclare(name);
	new (peepso.class(name))(messagebox);
});
