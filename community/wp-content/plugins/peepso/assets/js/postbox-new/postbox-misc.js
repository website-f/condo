import $ from 'jquery';
import { observer, hooks } from 'peepso';
import peepsodata from 'peepsodata';

hooks.addAction('postbox_init', postbox => {
	// Branding hook.
	if (+peepsodata.show_powered_by) {
		observer.addAction('show_branding', () => {
			let $branding = $(peepsodata.powered_by);
			if (!postbox.$postbox.children(`.${$branding.attr('class')}`).length) {
				$branding.appendTo(postbox.$postbox);
			}
		});
	}
});
