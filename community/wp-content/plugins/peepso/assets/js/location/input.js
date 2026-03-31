import $ from 'jquery';
import _ from 'underscore';
import api from './api';
import { hooks, observer, template } from 'peepso';
import { location as locationData } from 'peepsodata';

// Place detail cache.
const placeDetail = {};

class InputLocation {
	constructor(input) {
		this.$input = $(input);
		this.$dropdown = $(locationData.template_selector).insertAfter(this.$input);
		this.$list = this.$dropdown.find('.ps-js-location-list');
		this.$map = this.$dropdown.find('.ps-js-location-map');
		this.$result = this.$dropdown.find('.ps-js-location-result');
		this.$loading = this.$dropdown.find('.ps-js-location-loading');
		this.$placeholder = this.$dropdown.find('.ps-js-location-placeholder');
		this.$btnClose = this.$dropdown.find('.ps-js-close');
		this.$btnSelect = this.$dropdown.find('.ps-js-select');
		this.$btnRemove = this.$dropdown.find('.ps-js-remove');

		this.tmplItem = this.$dropdown.find('.ps-js-location-listitem').get(0).outerHTML;

		this.$input.on('focus.ps-location', e => this.show(e));
		this.$input.on('blur.ps-location', e => this.hide(e));
		this.$input.on('input.ps-location', _.debounce(e => this.onInput(e), 200).bind(this));
		this.$list.on('mousedown', 'a.ps-js-location-listitem', e => this.onItemClick(e));
		this.$btnSelect.on('mousedown', e => this.onBtnSelectClick(e));
		this.$btnRemove.on('mousedown', e => this.onBtnRemoveClick(e));
		this.$btnClose.on('mousedown', () => this.$input.trigger('blur.ps-location'));
	}

	show() {
		this.$list.find('.ps-location-selected').removeClass('ps-location-selected');
		this.$dropdown.show();
	}

	hide() {
		this.$dropdown.hide();
		this.$input.val(this.$input.data('location') || '');
	}

	onInput(e) {
		const query = e.target.value;
		if (!query) return;

		if (this.$placeholder) {
			this.$placeholder.remove();
			delete this.$placeholder;
		}

		this.$result.hide();
		this.$loading.show();
		this.$dropdown.show();

		api.search(query).then(places => {
			let html = [];

			if (places instanceof Array) {
				// Save place detail for later use.
				places.forEach(place => (placeDetail[place.id] = place));

				html = places.map(place => {
					return this.tmplItem
						.replace('{place_id}', place.id)
						.replace('{name}', place.name)
						.replace('{description}', place.description || '&nbsp;');
				});
			}

			this.$list.html(html.join(''));
			this.$loading.hide();
			this.$result.show();
			this.$dropdown.show();
		});
	}

	onItemClick(e) {
		e.preventDefault();
		e.stopPropagation();

		const $item = $(e.currentTarget);

		$item.addClass('ps-location-selected');
		$item.siblings().removeClass('ps-location-selected');
		this.$btnSelect.show();
		this.$btnRemove.hide();

		const place = placeDetail[$item.data('place-id')];
		const { name, location, viewport } = place;
		const { lat, lng } = location;

		this.$input
			.data('tmp-location', name)
			.data('tmp-latitude', lat)
			.data('tmp-longitude', lng)
			.data('tmp-viewport', viewport);

		this.$map.show();
		const map = this.$map.data('ps-map');
		const marker = this.$map.data('ps-map-marker');

		marker && marker.setMap(null);
		api.renderMap(map || this.$map[0], lat, lng, viewport).then(map => {
			this.$map.data('ps-map', map);
			api.renderMarker(map, lat, lng, 'You are here (more or less)').then(marker => {
				this.$map.data('ps-map-marker', marker);
			});
		});
	}

	onBtnSelectClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.$input.data('location', this.$input.data('tmp-location'));
		this.$input.data('latitude', this.$input.data('tmp-latitude'));
		this.$input.data('longitude', this.$input.data('tmp-longitude'));
		this.$input.data('viewport', this.$input.data('tmp-viewport'));

		this.$input.val(this.$input.data('location'));
		this.$btnSelect.hide();
		this.$btnRemove.show();
		this.$input.trigger('blur.ps-location');
	}

	onBtnRemoveClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.$input.removeData('location latitude longitude viewport').val('');
		this.$list.find('.ps-location-selected').removeClass('ps-location-selected');
		this.$map.hide();
		this.$btnRemove.hide();
	}
}

function initInputLocation(input) {
	let instance = $(input).data('ps-location');
	if (!instance) {
		instance = new InputLocation(input);
		$(input).data('ps-location', instance);
	}

	return instance;
}

// Initialize input elements available on page load.
$(function () {
	$('.ps-js-field-location').each((i, input) => initInputLocation(input));
});

// Initialize arbitrary input element.
hooks.addAction('init_input_location', 'location', input => initInputLocation(input));

// Handle save profile field.
observer.addFilter(
	'profile_field_save',
	(value, $input) => {
		if ($input.hasClass('ps-js-field-location')) {
			const { location, latitude, longitude, viewport } = $input.data();
			if (location && latitude && longitude) {
				return JSON.stringify({ name: location, latitude, longitude, viewport });
			}
		}

		return value;
	},
	10,
	2
);

// Handle save profile field on the register page.
observer.addAction('profile_field_save_register', $input => {
	if ($input.hasClass('ps-js-field-location')) {
		const { location, latitude, longitude, viewport } = $input.data();

		if (location && latitude && longitude) {
			let $hidden = $(`<input type="hidden" name="${$input.attr('name')}" />`);
			$input.removeAttr('name');
			$hidden.insertAfter($input);
			$hidden.val(JSON.stringify({ name: location, latitude, longitude, viewport }));
		}
	}
});
