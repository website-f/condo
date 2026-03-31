(function (wp, data) {
	const { hooks, serverSideRender } = wp;
	const { __ } = wp.i18n;
	const { select } = wp.data;
	const { createElement } = wp.element;
	const { registerBlockType } = wp.blocks;
	const { useBlockProps, InspectorControls } = wp.blockEditor;
	const { PanelBody, TextControl, ToggleControl, RangeControl } = wp.components;

	// Define block attributes.
	const { attributes } = data;

	function panelize(...controls) {
		return createElement(
			PanelBody,
			{ title: __('General Settings', 'peepso-core') },
			...controls
		);
	}

	function configTitle({ attributes, setAttributes }) {
		return createElement(TextControl, {
			label: __('Title', 'peepso-core'),
			value: attributes.title,
			onChange: value => setAttributes({ title: value })
		});
	}

	function configLimit({ attributes, setAttributes }) {
		return createElement(RangeControl, {
			label: __('Limit', 'peepso-core'),
			value: +attributes.limit,
			onChange: value => setAttributes({ limit: +value }),
			min: 1,
			max: 100
		});
	}

	function configHideEmpty({ attributes, setAttributes }) {
		return createElement(ToggleControl, {
			label: __('Hide when empty', 'peepso-core'),
			checked: +attributes.hide_empty,
			onChange: value => setAttributes({ hide_empty: +value })
		});
	}

	function configShowTotalMembers({ attributes, setAttributes }) {
		return createElement(ToggleControl, {
			label: __('Show total members count', 'peepso-core'),
			checked: +attributes.show_total_members,
			onChange: value => setAttributes({ show_total_members: +value })
		});
	}

	registerBlockType('peepso/latest-members', {
		title: __('PeepSo Latest Members', 'peepso-core'),
		description: __('Show PeepSo Latest Members based on a specific settings.', 'peepso-core'),
		category: 'widgets',
		supports: {
			color: {
				text: true,
				background: true,
				link: true
			},
			background: {
				backgroundImage: true
			},
			typography: {
				fontSize: true,
				lineHeight: true,
				fontStyle: true,
				fontWeight: true,
				textTransform: true,
				letterSpacing: true
			},
			spacing: {
				margin: true,
				padding: true
			},
			border: {
				color: true,
				radius: true,
				style: true,
				width: true
			},
			shadow: true
		},
		// icon: 'calendar',
		attributes,
		edit(props) {
			let { attributes, setAttributes } = props;

			// Enable Gutenberg color & alignment supports automatically.
			const blockProps = useBlockProps();

			// Assign block ID.
			if (attributes.__internalWidgetId) {
				setAttributes({ __psBlockId: attributes.__internalWidgetId });
			}

			// Check if block is rendered as a widget, inside a "sidebar".
			const { clientId } = props;
			const parentId = select('core/block-editor').getBlockHierarchyRootClientId(clientId);
			if (parentId !== clientId) {
				const parentAttributes = select('core/block-editor').getBlockAttributes(parentId);
				setAttributes({ __psSidebarId: parentAttributes.id });
			}

			// Compose block settings section.
			let settings = [
				panelize(
					configTitle(props),
					configLimit(props),
					configHideEmpty(props),
					configShowTotalMembers(props)
				)
			];

			let controls = createElement(
				InspectorControls,
				null,
				...hooks.applyFilters(
					'peepso_block_settings',
					settings,
					props,
					'peepso/latest-members'
				)
			);

			// Render content.
			let content = createElement(serverSideRender, {
				block: 'peepso/latest-members',
				attributes: props.attributes
			});

			setTimeout(() => hooks.doAction('peepso_block_updated', 'peepso/latest-members'), 1000);

			return createElement('div', blockProps, controls, content);
		},
		save() {
			return null;
		}
	});
})(window.wp, window.peepsoBlockLatestMembersEditorData);
