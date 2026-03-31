jQuery(function ($) {
	var _const = window.peepso_devtools_mayfly || {};
	var REST_URL = _const.rest_url;
	var REST_NONCE = _const.rest_nonce;

	var $query = $('#peepso_mayfly_filter_query').on('input', searchDelayed);
	var $autoreload = $('#peepso_mayfly_filter_autoreload').on('change', () => toggleAutoReload());
	var $limit = $('#peepso_mayfly_filter_limit').on('change', search);
	var $status = $('#peepso_mayfly_filter_status').on('change', search);
	var $orderby = $('#peepso_mayfly_filter_orderby').on('change', search);
	var $order = $('#peepso_mayfly_filter_order').on('change', search);
	var $sortColumn = $('.peepso_mayfly_sort_column').on('click', sortColumn);
	var $actions = $('#peepso_mayfly_actions');
	var $actionsApply = $('#peepso_mayfly_actions_apply').on('click', actionsApply);
	var $actionDeleteAll = $('#peepso_mayfly_action_delete_all').on('click', actionDeleteAll);
	var $checkall = $('#peepso_mayfly_checkall').on('click', checkAll);
	var $results = $('#peepso_mayfly_results');

	/**
	 * Load results.
	 *
	 * @param {Object} data
	 * @returns {JQueryDeferred}
	 */
	function load(data) {
		var xhr, json;

		xhr = $.ajax({
			url: REST_URL,
			type: 'GET',
			dataType: 'json',
			data: data,
			beforeSend: function (xhr) {
				xhr.setRequestHeader('X-WP-Nonce', REST_NONCE);
			}
		});

		xhr.done(function (resp) {
			if (resp && resp.results) {
				json = resp.results;
			}
		});

		xhr.always(function () {
			if (json) {
				render(json);
			}
		});

		return xhr;
	}

	/**
	 * Delete checked results.
	 */
	function deleteItems(mode = 'checked') {
		var ids;

		if (mode === 'all') {
			ids = 'all';
		} else {
			var $checked = $results.find('input[type=checkbox]').filter(':checked');
			ids = [...$checked].map(el => el.value);
		}

		if (ids instanceof Array && !ids.length) return;

		$.ajax({
			url: REST_URL,
			type: 'DELETE',
			dataType: 'json',
			data: { id: ids },
			beforeSend: xhr => xhr.setRequestHeader('X-WP-Nonce', REST_NONCE)
		}).done(resp => {
			alert(`${ids === 'all' ? 'All' : ids.length} entries are successfully deleted.`);
			search();
		});
	}

	/**
	 * Render results.
	 *
	 * @param {Object[]} results
	 */
	function render(results) {
		var $items = $();

		results.forEach(function (item) {
			var name = item.name.replace(/"/g, '&quot;');
			var $name = $('<td class="column-primary" data-colname="Name" />').html(
				'<input type="text" value="' + name + '" title="' + name + '" readonly />'
			);

			var value = item.value.replace(/</g, '&lt;');
			var $value = $('<td data-colname="Value"><div>' + value + '</div></td>');

			var $item = $('<tr class="is-expanded" />')
				.append('<th><input type="checkbox" name="mayfly[]" value="' + item.id + '"></th>')
				.append('<th>' + item.id + '</th>')
				.append($name)
				.append($value)
				.append('<td data-colname="Created">' + item.created + '</td>')
				.append('<td data-colname="Expired">' + item.expires + '</td>');

			$items = $items.add($item);
		});

		$checkall.prop('checked', false);
		$results.empty().append($items);
	}

	/**
	 * Search.
	 *
	 * @returns {JQueryDeferred}
	 */
	function search() {
		var params = {
			query: $query.val().trim(),
			limit: $limit.val(),
			status: $status.val(),
			orderby: $orderby.val(),
			order: $order.val()
		};

		// Add sort indicator in the table header if necessary.
		$sortColumn.each(function () {
			var $button = $(this),
				oldLabel = $button.html(),
				newLabel = oldLabel;

			newLabel = newLabel.replace(/[↓↑]/g, '');
			if ($button.data('orderby') === params.orderby) {
				newLabel = newLabel + ' ' + (params.order === 'asc' ? '↑' : '↓');
			}

			if (newLabel !== oldLabel) {
				$button.html(newLabel);
			}
		});

		$results.css('opacity', 0.2);
		return load(params).always(function () {
			toggleAutoReload();
			$results.css('opacity', '');
		});
	}

	/**
	 * Delayed search.
	 */
	var _delayedTimer;
	function searchDelayed() {
		$results.css('opacity', 0.2);

		clearTimeout(_delayedTimer);
		_delayedTimer = setTimeout(search, 500);
	}

	/**
	 * Toggle autoreload function.
	 *
	 * @param {number|boolean} [enable]
	 */
	var _autoreloadTimer;
	function toggleAutoReload(enable) {
		if ('undefined' === typeof enable) {
			enable = +$autoreload[0].checked;
		} else {
			enable = +enable;
			if ($autoreload[0].checked !== !!enable) {
				$autoreload[0].checked = !!enable;
			}
		}

		clearInterval(_autoreloadTimer);
		if (!enable) {
			return;
		}

		var minInterval = 10 * 1000; // Set minimum reload interval to 10 seconds.
		_autoreloadTimer = setInterval(search, Math.max(enable, minInterval));
	}

	/**
	 * Sort result by column.
	 *
	 * @param {Event} e
	 */
	function sortColumn(e) {
		e.preventDefault();
		e.stopPropagation();

		var orderby = $(this).data('orderby');

		if (orderby === $orderby.val()) {
			$order.val($order.val() === 'asc' ? 'desc' : 'asc');
		} else {
			$orderby.val(orderby);
			$order.val('desc');
		}

		search();
	}

	/**
	 * Bulk actions handler.
	 *
	 * @param {Event} e
	 */
	function actionsApply(e) {
		e.stopPropagation();

		switch ($actions.val()) {
			case 'delete':
				deleteItems('checked');
				break;
		}

		$actions.prop('selectedIndex', 0);
	}

	/**
	 * Delete-all button handler.
	 *
	 * @param {Event} e
	 */
	function actionDeleteAll(e) {
		e.stopPropagation();
		deleteItems('all');
	}

	/**
	 * Check-all checkbox handler.
	 *
	 * @param {Event} e
	 */
	function checkAll(e) {
		e.stopPropagation();

		$results.find('input[type=checkbox]').prop('checked', e.target.checked);
	}

	// Checkbox handler.
	$results.on('click', 'input[type=checkbox]', function (e) {
		var $checkboxes = $results.find('input[type=checkbox]');
		var $checked = $checkboxes.filter(':checked');
		$checkall.prop('checked', $checkboxes.length === $checked.length);
	});

	// Auto select-all on input click.
	$results.on('click', 'input[type=text][readonly]', function (e) {
		$(e.currentTarget).select();
	});

	// Search initial data and toggle autoreload on page load.
	search();
	$autoreload.trigger('change');
});
