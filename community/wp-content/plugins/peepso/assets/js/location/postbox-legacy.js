import $ from 'jquery';
import api from './api';
import peepso, { hooks, observer, template } from 'peepso';
import { location as locationData } from 'peepsodata';

const PostboxDropdown = observer.applyFilters('class_postbox_dropdown');

class PostboxLocation extends PostboxDropdown {
	constructor($postbox, postbox = null) {
		super($postbox.find('#location-tab')[0]);

		if (!this.$postbox.is($postbox)) {
			this.$postbox = $postbox;
		}

		// Check whether the parent postbox is the create post's (main) postbox or edit post's postbox.
		this.isMainPostbox = !postbox;

		this.postbox = this.isMainPostbox ? $postbox : postbox;
		this.location = null;

		this.$dropdown = this.$container.find('.ps-js-postbox-location');
		this.$dropdown.html(locationData.template_postbox || '');

		this.$input = this.$dropdown.find('input[type=text]');
		this.$loading = this.$dropdown.find('.ps-js-location-loading');
		this.$result = this.$dropdown.find('.ps-js-location-result');
		this.$map = this.$dropdown.find('.ps-js-location-map');
		this.$select = this.$dropdown.find('.ps-js-select');
		this.$remove = this.$dropdown.find('.ps-js-remove');

		// item template
		this.listItemTemplate = template(this.$dropdown.find('.ps-js-location-fragment').text());

		this.$input.on('input', e => this.onInputInput(e));
		this.$result.on('click', '[data-place-id]', e => this.onItemClick(e));
		this.$select.on('click', e => this.onSelectClick(e));
		this.$remove.on('click', e => this.onRemoveClick(e));

		if (this.isMainPostbox) {
			// for main postbox
			this.$postbox.on('postbox.post_cancel postbox.post_saved', () => this.set(null));
			observer.addFilter('postbox_req', (...args) => this.mainData(...args));
			observer.addFilter('peepso_postbox_can_submit', flags => this.mainValidate(flags), 30);
			observer.addFilter('peepso_postbox_addons_update', list => this.mainRenderLabel(list));
		} else {
			// for edit postbox
			this.postbox.addAction('update', this.editLoad, 10, 2, this);
			this.postbox.addFilter('data', this.editData, 10, 1, this);
			this.postbox.addFilter('data_validate', this.editValidate, 10, 2, this);
			this.postbox.addFilter('render_addons', this.editRenderLabel, 10, 1, this);
		}
	}

	show() {
		super.show();
		this.$input.focus();

		// Initialize on first run.
		if (this.initialize) return;
		this.initialize = true;

		// Initial location is set.
		if (this.location) {
			const { name, latitude: lat, longitude: lng, viewport } = this.location;

			this.$input.val(name);
			this.updateList([{ name, location: { lat, lng }, viewport }]);
			this.updateMap(lat, lng).then(() => {
				this.updateMarker(lat, lng);
				this.$select.hide();
				this.$remove.show();
			});
			return;
		}

		this.$result.empty().append(this.$loading.clone());
		this.search('').done(results => {
			this.updateList(results);
			this.detectLocation().done((lat, lng) => {
				this.updateMarker(null);
				this.updateMap(lat, lng);
				this.$select.hide();
				this.$remove.hide();
			});
		});
	}

	set(location) {
		const $tooltip = this.$toggle.children('a');

		if (!location) {
			this.location = null;
			this.updateMarker(null);
			this.$toggle.removeClass('active');
			this.$remove.hide();

			// Reset tooltip.
			if ($tooltip.attr('data-tooltip-original')) {
				$tooltip.attr('data-tooltip', $tooltip.attr('data-tooltip-original'));
				$tooltip.removeAttr('data-tooltip-original');
			}
		} else {
			this.location = location;
			this.$toggle.addClass('active');
			this.$remove.show();

			// Update tooltip.
			if (!$tooltip.attr('data-tooltip-original')) {
				$tooltip.attr('data-tooltip-original', $tooltip.attr('data-tooltip'));
			}
			$tooltip.attr('data-tooltip', this.location.name);
		}

		if (this.isMainPostbox) {
			this.postbox.on_change();
		} else {
			this.postbox.doAction('refresh');
		}
	}

	search(input = '') {
		const token = (this.searchToken = Date.now());

		return $.Deferred(defer => {
			api.search(input)
				.then(results => token === this.searchToken && defer.resolve(results))
				.catch(defer.reject);
		});
	}

	placeDetail(placeId, data) {
		if (placeId) {
			this.placeDetailCache = this.placeDetailCache || {};
			if (data) this.placeDetailCache[placeId] = data;
			if (this.placeDetailCache[placeId]) return this.placeDetailCache[placeId];
		}
	}

	onInputInput(e) {
		this.$result.empty().append(this.$loading.clone());

		// Debounce input.
		clearTimeout(this.searchTimer);
		this.searchTimer = setTimeout(() => {
			this.search(this.$input.val().trim()).done(results => {
				this.updateList(results);
				this.updateMarker(null);
				this.$select.hide();
				this.$remove.hide();
			});
		}, 1000);
	}

	onItemClick(e) {
		e.preventDefault();
		e.stopPropagation();

		const data = $(e.currentTarget).data();
		if (!data.placeId) return;

		const place = this.placeDetail(data.placeId);
		const { name, location, viewport } = place;
		const { lat: latitude, lng: longitude } = location;

		this.updateMap(latitude, longitude, viewport).then(() => {
			this.updateMarker(latitude, longitude);
		});

		this.$select.show();
		this.$remove.hide();

		// Temporariry store location data in this button.
		this.$select.data({ placeId: data.placeId, name, latitude, longitude, viewport });
	}

	onSelectClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.$select.hide();
		this.set(this.$select.data());
		this.hide();
	}

	onRemoveClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.set(null);
		this.hide();

		// Initial value of the Edit postbox doesn't have `placeId`. We detect this
		// in order to clear initial text on the search box and trigger re-initialize
		// when the dropdown is re-opened later on.
		const data = this.$select.data();
		if (!data.placeId) {
			this.$input.val('');
			this.initialize = false;
		}
	}

	detectLocation() {
		return $.Deferred(defer => {
			api.detectLocation()
				.then(coords => defer.resolve(...coords))
				.catch(defer.reject);
		});
	}

	updateMap(lat, lng, viewport) {
		return api.renderMap(this.map || this.$map[0], lat, lng, viewport).then(map => {
			if (!this.map) this.map = map;
		});
	}

	updateMarker(lat, lng) {
		if (!this.map) return;

		if (!(lat && lng)) {
			if (this.marker) this.marker.setMap(null);
			return;
		}

		api.renderMarker(this.map, lat, lng, 'You are here (more or less)').then(marker => {
			if (this.marker) this.marker.setMap(null);
			this.marker = marker;
		});
	}

	updateList(list) {
		// Save items to cache for later use.
		list.forEach(item => this.placeDetail(item.id, item));

		// Render items.
		const html = list
			.map(item => this.listItemTemplate({ ...item, place_id: item.id }))
			.join('');

		this.$result.html(html);
	}

	/**
	 * Specific methods for main postbox.
	 */

	mainData(data) {
		if (this.location) {
			const { name, latitude, longitude, viewport } = this.location;
			data.location = { name, latitude, longitude, viewport: JSON.stringify(viewport) };
		}

		return data;
	}

	mainValidate(flag) {
		flag.soft.push(!!this.location);

		return flag;
	}

	mainRenderLabel(list) {
		if (this.location) {
			list.unshift(`<b><i class=ps-icon-map-marker></i>${this.location.name}</b>`);
		}

		return list;
	}

	/**
	 * Specific methods for edit post's postbox.
	 */

	editLoad(data) {
		data = (data && data.data) || {};

		// Fix escaped viewport data.
		try {
			let viewport = data.location && data.location.viewport;
			if (viewport) {
				viewport = JSON.parse(viewport.replace(/&quot;/g, '"'));
				if (viewport instanceof Object) {
					data.location.viewport = viewport;
				}
			}
		} catch (e) {}

		this.set(data.location || null);
	}

	editData(data) {
		if (this.location) {
			const { name, latitude, longitude, viewport } = this.location;
			data.location = { name, latitude, longitude, viewport: JSON.stringify(viewport) };
		} else {
			data.location = '';
		}

		return data;
	}

	editValidate(valid) {
		return this.location ? true : valid;
	}

	editRenderLabel(list) {
		if (this.location) {
			const html = `<i class="gcis gci-map-marker-alt"></i><strong>${this.location.name}</strong>`;
			list.push(html);
		}

		return list;
	}
}

observer.addFilter('peepso_postbox_addons', addons => {
	addons.push({
		init() {},
		set_postbox($postbox) {
			if ($postbox.find('#location-tab').length) {
				new PostboxLocation($postbox);
			}
		}
	});

	return addons;
});

observer.addAction('postbox_init', postbox => {
	const $postbox = postbox.$el;
	if ($postbox.find('#location-tab').length) {
		new PostboxLocation($postbox, postbox);
	}
});
