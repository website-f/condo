import { location as locationData } from 'peepsodata';

const API_KEY = locationData.api_key;
const API_MAP_ID = locationData.api_map_id;
const API_LEGACY = locationData.api_legacy;

let loading = false;
let loaded = false;
let loadCallbacks = [];

export default {
	load() {
		return new Promise((resolve, reject) => {
			if (loaded) return resolve();

			loadCallbacks.push({ resolve, reject });
			if (loading) return;

			loading = true;

			const callback = `gmap_cb_${Date.now()}`;
			window[callback] = function () {
				loaded = true;
				loading = false;
				while (loadCallbacks.length) loadCallbacks.shift().resolve();
				delete window[callback];
			};

			const script = document.createElement('script');
			script.async = true;
			script.type = 'text/javascript';
			script.src = `https://maps.googleapis.com/maps/api/js?libraries=places&key=${API_KEY}&loading=async&callback=${callback}`;
			document.body.appendChild(script);
		});
	},

	async detectLocation() {
		return await location.detect();
	},

	async search(keyword = '') {
		return await (keyword ? this.searchByKeyword(keyword) : this.searchNearby());
	},

	async searchNearby() {
		// Forward call if the user opts to use legacy Google Maps APIs.
		if (API_LEGACY) return await this.__legacySearchNearby();

		await this.load();

		const { Place, SearchNearbyRankPreference } = await google.maps.importLibrary('places');

		const latlng = await this.detectLocation();
		if (!latlng) throw new Error('Cannot estimate user location.');

		const request = {
			fields: ['displayName', 'formattedAddress', 'location', 'viewport'],
			locationRestriction: { center: new google.maps.LatLng(...latlng), radius: 50000 },
			maxResultCount: 5,
			rankPreference: SearchNearbyRankPreference.DISTANCE
		};

		const { places } = await Place.searchNearby(request);
		if (!places.length) return [];

		return await Promise.all(places.map(this.mapPlace));
	},

	async searchByKeyword(keyword) {
		// Forward call if the user opts to use legacy Google Maps APIs.
		if (API_LEGACY) return await this.__legacySearchByKeyword(keyword);

		await this.load();

		const { AutocompleteSuggestion } = await google.maps.importLibrary('places');

		const latlng = await this.detectLocation();
		if (!latlng) throw new Error('Cannot estimate user location.');

		const request = {
			input: keyword,
			locationBias: { center: new google.maps.LatLng(...latlng), radius: 5000 },
			language: document.documentElement.lang
		};

		const { suggestions } = await AutocompleteSuggestion.fetchAutocompleteSuggestions(request);
		if (!suggestions.length) return [];

		return await Promise.all(suggestions.map(this.mapPlace));
	},

	async renderMap(map, lat, lng, viewport) {
		// Forward call if the user opts to use legacy Google Maps APIs.
		if (API_LEGACY) return this.__legacyRenderMap(map, lat, lng, viewport);

		await this.load();

		const center = new google.maps.LatLng(lat, lng);

		if (map instanceof Element) {
			const { Map } = await google.maps.importLibrary('maps');
			map = new Map(map, {
				mapId: API_MAP_ID,
				center,
				zoom: 15,
				draggable: false,
				scrollwheel: false,
				disableDefaultUI: true
			});
		} else {
			map.setCenter(center);
		}

		viewport ? map.fitBounds(viewport) : map.setZoom(15);

		return map;
	},

	async renderMarker(map, lat, lng, title) {
		// Forward call if the user opts to use legacy Google Maps APIs.
		if (API_LEGACY) return this.__legacyRenderMarker(map, lat, lng, title);

		await this.load();

		const { AdvancedMarkerElement } = await google.maps.importLibrary('marker');

		return new AdvancedMarkerElement({
			map,
			title,
			position: new google.maps.LatLng(lat, lng)
		});
	},

	async mapPlace(place) {
		if (place.placePrediction) {
			place = place.placePrediction.toPlace();
			await place.fetchFields({
				fields: ['displayName', 'formattedAddress', 'location', 'viewport']
			});
		}

		return {
			id: place.id,
			name: place.displayName,
			description: place.formattedAddress,
			location: place.location.toJSON(),
			viewport: place.viewport.toJSON()
		};
	},

	/**
	 * The following methods call the legacy Google Maps APIs. It might raise errors in the future
	 * if Google decides not to support the library anymore.
	 */

	async __legacySearchNearby() {
		await this.load();

		const placesService = await this.__legacyImportLibrary('places');

		const latlng = await this.detectLocation();
		if (!latlng) throw new Error('Cannot estimate user location.');

		const request = {
			location: new google.maps.LatLng(...latlng),
			rankBy: google.maps.places.RankBy.DISTANCE,
			// https://developers.google.com/maps/documentation/javascript/legacy/supported_types#table3
			type: 'establishment'
		};

		return new Promise(resolve => {
			placesService.nearbySearch(request, (results, status) => {
				if (status === 'OK') {
					const places = results
						// Return only the first 5 result to match the new API results count.
						.slice(0, 5)
						// Compose results to match `this.mapPlace` return value format.
						.map(item => ({
							id: item.place_id,
							name: item.name,
							description: item.vicinity,
							location: item.geometry.location.toJSON(),
							viewport: item.geometry.viewport.toJSON()
						}));

					resolve(places);
				}
			});
		});
	},

	async __legacySearchByKeyword(keyword) {
		await this.load();

		const autocompleteService = await this.__legacyImportLibrary('autocomplete');

		const latlng = await this.detectLocation();
		if (!latlng) throw new Error('Cannot estimate user location.');

		const request = {
			input: keyword,
			locationBias: { center: new google.maps.LatLng(...latlng), radius: 5000 },
			language: document.documentElement.lang
		};

		return new Promise(resolve => {
			autocompleteService.getPlacePredictions(request, async (results, status) => {
				if (status === 'OK') {
					const placesService = await this.__legacyImportLibrary('places');
					const placesMap = place => {
						return new Promise(resolve => {
							const placeId = place.place_id;
							placesService.getDetails({ placeId }, (place, status) => {
								if (status === 'OK') {
									// Compose results to match `this.mapPlace` return value format.
									resolve({
										id: place.place_id,
										name: place.name,
										description: place.formatted_address,
										location: place.geometry.location.toJSON(),
										viewport: place.geometry.viewport.toJSON()
									});
								}
							});
						});
					};

					const places = await Promise.all(results.map(placesMap));
					resolve(places);
				}
			});
		});
	},

	async __legacyRenderMap(map, lat, lng, viewport) {
		await this.load();

		const center = new google.maps.LatLng(lat, lng);

		if (map instanceof Element) {
			map = new google.maps.Map(map, {
				center,
				zoom: 15,
				draggable: false,
				scrollwheel: false,
				disableDefaultUI: true
			});
		} else {
			map.setCenter(center);
		}

		viewport ? map.fitBounds(viewport) : map.setZoom(15);

		return map;
	},

	async __legacyRenderMarker(map, lat, lng, title) {
		await this.load();

		return new google.maps.Marker({
			map,
			title,
			position: new google.maps.LatLng(lat, lng)
		});
	},

	async __legacyImportLibrary(name) {
		await this.load();

		this.__legacyLibrary = this.__legacyLibrary || {};
		if (this.__legacyLibrary[name]) return this.__legacyLibrary[name];

		if ('autocomplete' === name) {
			this.__legacyLibrary[name] = new google.maps.places.AutocompleteService();
			return this.__legacyLibrary[name];
		}

		if ('places' === name) {
			const div = document.createElement('div');
			document.body.appendChild(div);
			this.__legacyLibrary[name] = new google.maps.places.PlacesService(div);
			return this.__legacyLibrary[name];
		}
	}
};

/**
 * Self-contained user's location detection procedure.
 */
const location = {
	cache: null,

	async detect() {
		if (this.cache && false) return this.cache;

		let result = await this.detectByDevice();
		if (!result) result = await this.detectByAPI();
		if (!result) result = await this.detectByIP();
		if (result) this.cache = result;
		return result;
	},

	async detectByDevice() {
		if (window.location.protocol !== 'https:') return;

		return new Promise(resolve => {
			navigator.geolocation.getCurrentPosition(
				pos => resolve([pos.coords.latitude, pos.coords.longitude]),
				() => resolve(),
				{ timeout: 10000 }
			);
		});
	},

	// The Google Maps Geolocation API must be enabled for this API key.
	async detectByAPI() {
		if (!API_KEY) return;

		try {
			const url = `https://www.googleapis.com/geolocation/v1/geolocate?key=${API_KEY}`;
			const response = await fetch(url, { method: 'POST' });
			if (!response.ok) return;

			const json = await response.json();
			if (typeof json !== 'object') return;
			if (typeof json.location !== 'object') return;

			const { lat, lng } = json.location;
			return [lat, lng];
		} catch (error) {}
	},

	async detectByIP() {
		try {
			const url = `https://ipapi.co/json`;
			const response = await fetch(url);
			if (!response.ok) return;

			const json = await response.json();
			if (typeof json !== 'object') return;

			const { latitude, longitude } = json;
			return [latitude, longitude];
		} catch (error) {}
	}
};
