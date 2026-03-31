import $ from 'jquery';
import api from './api';
import peepso, { hooks, template } from 'peepso';

class PostboxLocation extends peepso.class('PostboxOption') {
	constructor(postbox) {
		super(postbox);

		this.location = null;

		this.postbox = postbox;
		this.$postbox = postbox.$postbox;
		this.$toggle = this.$postbox.find('[data-ps=option][data-id=location]');
		this.$dropdown = this.$postbox.find('[data-ps=dropdown][data-id=location]');
		this.$input = this.$dropdown.find('input[type=text]');
		this.$loading = this.$dropdown.find('.ps-js-location-loading');
		this.$result = this.$dropdown.find('.ps-js-location-result');
		this.$map = this.$dropdown.find('.ps-js-location-map');
		this.$select = this.$dropdown.find('.ps-js-select');
		this.$remove = this.$dropdown.find('.ps-js-remove');

		// item template
		this.listItemTemplate = template(this.$dropdown.find('[data-tmpl=item]').text());
		this.titleTemplateSingle = template(this.$dropdown.find('[data-tmpl=title_single]').text());
		this.titleTemplateMulti = template(this.$dropdown.find('[data-tmpl=title_multi]').text());

		this.$toggle.on('click', () => this.toggle());
		this.$input.on('input', e => this.onInputInput(e));
		this.$result.on('click', '[data-place-id]', e => this.onItemClick(e));
		this.$select.on('click', e => this.onSelectClick(e));
		this.$remove.on('click', e => this.onRemoveClick(e));

		hooks.addFilter('postbox_data', 'loc', (...args) => this.onPostboxData(...args));
		hooks.addFilter('postbox_title_extra', 'loc', (...args) => this.onPostboxTitle(...args));
		hooks.addFilter('postbox_is_empty', (...args) => this.onFilterIsEmpty(...args));
		hooks.addAction('postbox_reset', 'loc', (...args) => this.onPostboxReset(...args));
	}

	show() {
		super.show();
		this.$input.focus();

		// Initialize on first run.
		if (this.initialize) return;
		this.initialize = true;
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
		if (!location) {
			this.location = null;
			this.updateMarker(null);
			this.$toggle.removeClass('pso-postbox__option--active');
			this.$remove.hide();
		} else {
			this.location = location;
			this.$toggle.addClass('pso-postbox__option--active');
			this.$remove.show();
		}

		this.postbox.render();
		this.postbox.$textarea.trigger('input');
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

	onPostboxData(data, postbox) {
		if (postbox === this.postbox) {
			if (this.location) {
				const { name, latitude, longitude, viewport } = this.location;
				data.location = { name, latitude, longitude, viewport: JSON.stringify(viewport) };
			}
		}

		return data;
	}

	onPostboxTitle(list = [], data, postbox) {
		if (postbox === this.postbox) {
			if (this.location) {
				const tmpl = data.mood ? this.titleTemplateMulti : this.titleTemplateSingle;
				const html = tmpl(this.location);
				list.push(html);
			}
		}

		return list;
	}

	onFilterIsEmpty(empty, postbox, data) {
		if (postbox === this.postbox) {
			if (data.location) empty = false;
		}

		return empty;
	}

	onPostboxReset(postbox) {
		if (postbox === this.postbox) {
			this.set(null);
			this.$select.hide();
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
		this.toggle(false);
	}

	onRemoveClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.set(null);
		this.toggle(false);
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
		const html = list.map(item => this.listItemTemplate(item)).join('');
		this.$result.html(html);
	}
}

hooks.addAction('postbox_init', 'location', postbox => new PostboxLocation(postbox));
