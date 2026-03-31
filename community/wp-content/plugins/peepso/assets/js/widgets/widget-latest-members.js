import $ from 'jquery';
import { ajax } from 'peepso';

$(function () {
	function initWidget(container) {
		let $container = $(container),
			$content = $container.find('.ps-js-widget-content'),
			hideEmpty = +$container.data('hideempty'),
			limit = +$container.data('limit'),
			totalmember = +$container.data('totalmember') ? 1 : 0,
			params = { limit, totalmember };

		ajax.get('widgetajax.latest_members', params).done(json => {
			if (json.success) {
				if (hideEmpty && +json.data.empty) {
					$content.empty();
					$container.parent('[class*="widget_"]').hide();
				} else {
					$content.html(json.data.html);
					$container.parent('[class*="widget_"]').show();
				}
			}
		});
	}

	function init() {
		const iframe = document.querySelector('iframe');
		const doc = iframe && iframe.name === 'editor-canvas' ? iframe.contentDocument : document;
		const widgets = doc.querySelectorAll('.ps-js-widget-latest-members:not([data-init])');
		widgets.forEach(widget => {
			widget.setAttribute('data-init', 1);
			initWidget(widget);
		});
	}

	function listen(type) {
		if (type === 'peepso/latest-members') init();
	}

	if ('object' === typeof wp && wp.domReady) {
		wp.domReady(() => {
			setTimeout(() => {
				init();
				if (wp.hooks) {
					wp.hooks.addAction('peepso_block_updated', 'peepso_latest_members', listen);
				}
			}, 1000);
		});
	} else {
		init();
	}
});
