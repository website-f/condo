import $ from 'jquery';
import peepso, { observer } from 'peepso';

import location from './location';
peepso.location = location;

import './input';
import './postbox';
import './postbox-legacy';
import './photos';

observer.addFilter(
	'human_friendly_extras',
	function (extras, content, root) {
		if (!content && root) {
			var $location = $(root).find('.ps-js-activity-extras [data-preview]');
			if ($location.length) {
				extras.push($location.data('preview'));
			}
		}
		return extras;
	},
	20,
	3
);
