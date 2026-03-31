import peepso from 'peepso';
import api from './api';

export default {
	// Export API to be used on addon plugins.
	api,

	showMap(location, viewport, label) {
		const id = `ps-js-map-${new Date().getTime()}`;
		const { lat, lng } = location || {};

		if (!(lat && lng)) return;

		peepso.lightbox([{ content: `<div class="ps-location__map" data-id="${id}" />` }], {
			simple: true,
			nofulllink: true,
			afterchange(lightbox) {
				api.load().then(() => {
					const $map = lightbox.$container.find(`[data-id="${id}"]`);
					api.renderMap($map[0], lat, lng, viewport).then(map => {
						viewport || map.setZoom(14);
						api.renderMarker(map, lat, lng, label);
					});
				});
			}
		});
	}
};
