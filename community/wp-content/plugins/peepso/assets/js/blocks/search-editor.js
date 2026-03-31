(function (wp, data) {
	const { hooks, serverSideRender } = wp;
	const { __ } = wp.i18n;
	const { createElement } = wp.element;
	const { registerBlockType } = wp.blocks;
	const { useBlockProps, InspectorControls } = wp.blockEditor;
	const { PanelBody, TextControl } = wp.components;

	// Define block attributes.
	const { attributes } = data;

	function panelize(title, ...controls) {
		if ('string' !== typeof title) {
			controls.unshift(title);
			title = __('General Settings', 'peepso-core');
		}

		return createElement(PanelBody, { title }, ...controls);
	}

	function configTitle({ attributes, setAttributes }) {
		return createElement(TextControl, {
			label: __('Title', 'peepso-core'),
			value: attributes.title,
			onChange: value => setAttributes({ title: value })
		});
	}

	registerBlockType('peepso/search', {
		title: __('PeepSo Search', 'peepso-core'),
		description: __('Show PeepSo search box.', 'peepso-core'),
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
		attributes,
		edit(props) {
			// Enable Gutenberg color & alignment supports automatically.
			const blockProps = useBlockProps();
			
			// Assign timestamp if necessary for ID and caching purpose.
			let { attributes, setAttributes } = props;
			if (!+attributes.timestamp) {
				setAttributes({ timestamp: new Date().getTime() });
			}

			// Compose block settings section.
			let settings = [panelize(configTitle(props))];

			let controls = createElement(
				InspectorControls,
				null,
				...hooks.applyFilters('peepso_block_settings', settings, props, 'peepso/search')
			);

			// Render content.
			let content = createElement(serverSideRender, {
				block: 'peepso/search',
				attributes: props.attributes
			});

			return createElement('div', blockProps, controls, content);
		},
		save() {
			return null;
		}
	});
})(window.wp, window.peepsoBlockSearchEditorData);
