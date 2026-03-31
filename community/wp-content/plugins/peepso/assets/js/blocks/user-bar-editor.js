(function (wp, data) {
	const { hooks, serverSideRender } = wp;
	const { __ } = wp.i18n;
	const { createElement } = wp.element;
	const { registerBlockType } = wp.blocks;
	const { useBlockProps, InspectorControls } = wp.blockEditor;
	const { PanelBody, ToggleControl, SelectControl } = wp.components;

	// Define block attributes.
	const { attributes } = data;

	function panelize(title, ...controls) {
		if ('string' !== typeof title) {
			controls.unshift(title);
			title = __('General Settings', 'peepso-core');
		}

		return createElement(PanelBody, { title }, ...controls);
	}

	function configContentPosition({ attributes, setAttributes }) {
		return createElement(SelectControl, {
			label: __('Content Position', 'peepso-core'),
			value: attributes.content_position,
			onChange: value => setAttributes({ content_position: value }),
			options: [
				{ value: 'left', label: __('Left', 'peepso-core') },
				{ value: 'right', label: __('Right', 'peepso-core') },
				{ value: 'center', label: __('Center', 'peepso-core') },
				{ value: 'space', label: __('Space Between', 'peepso-core') }
			]
		});
	}

	function configGuestBehavior({ attributes, setAttributes }) {
		return createElement(SelectControl, {
			label: __('Guest view', 'peepso-core'),
			value: attributes.guest_behavior,
			onChange: value => setAttributes({ guest_behavior: value }),
			options: [
				{ value: 'login', label: __('Log-in link', 'peepso-core') },
				{ value: 'hide', label: __('Hide', 'peepso-core') }
			]
		});
	}

	function configName({ attributes, setAttributes }) {
		return createElement(SelectControl, {
			label: __('Name style', 'peepso-core'),
			value: +attributes.show_name,
			onChange: value => setAttributes({ show_name: +value }),
			options: [
				{ value: 0, label: __('Hidden', 'peepso-core') },
				{ value: 1, label: __('Short name', 'peepso-core') },
				{ value: 2, label: __('Full name', 'peepso-core') }
			]
		});
	}

	function configCompactMode({ attributes, setAttributes }) {
		return createElement(SelectControl, {
			label: __('Compact mode', 'peepso-core'),
			help: __(
				'When enabled, the Userbar is hidden under a profile icon toggle. "Disabled" will only work properly on mobile if there are no other widgets and elements (like logo) next to the widget. This setting has no effect when previewing the widget in a block editor.',
				'peepso-core'
			),
			value: +attributes.compact_mode,
			onChange: value => setAttributes({ compact_mode: +value }),
			options: [
				{ value: 0, label: __('Disable', 'peepso-core') },
				{ value: 1, label: __('Mobile', 'peepso-core') },
				{ value: 2, label: __('Desktop', 'peepso-core') },
				{ value: 3, label: __('Always', 'peepso-core') }
			]
		});
	}

	function configAvatar({ attributes, setAttributes }) {
		return createElement(ToggleControl, {
			label: __('Show avatar', 'peepso-core'),
			checked: +attributes.show_avatar,
			onChange: value => setAttributes({ show_avatar: +value })
		});
	}

	function configNotifications({ attributes, setAttributes }) {
		return createElement(ToggleControl, {
			label: __('Show notifications', 'peepso-core'),
			checked: +attributes.show_notifications,
			onChange: value => setAttributes({ show_notifications: +value })
		});
	}

	function configUsermenu({ attributes, setAttributes }) {
		return createElement(ToggleControl, {
			label: __('User dropdown menu', 'peepso-core'),
			checked: +attributes.show_usermenu,
			onChange: value => setAttributes({ show_usermenu: +value })
		});
	}

	function configUserlogout({ attributes, setAttributes }) {
		return createElement(ToggleControl, {
			label: __('Logout icon', 'peepso-core'),
			checked: +attributes.show_logout,
			onChange: value => setAttributes({ show_logout: +value })
		});
	}

	function configVIP({ attributes, setAttributes }) {
		return createElement(ToggleControl, {
			label: __('VIP icons', 'peepso-core'),
			checked: +attributes.show_vip,
			onChange: value => setAttributes({ show_vip: +value })
		});
	}

	function configBadges({ attributes, setAttributes }) {
		if (!+data.badgeos) return null;

		return createElement(ToggleControl, {
			label: __('Badges', 'peepso-core'),
			checked: +attributes.show_badges,
			onChange: value => setAttributes({ show_badges: +value })
		});
	}

	registerBlockType('peepso/user-bar', {
		title: __('PeepSo UserBar', 'peepso-core'),
		description: __('Show PeepSo UserBar based on the following settings.', 'peepso-core'),
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
			let settings = [
				panelize(
					configContentPosition(props),
					configGuestBehavior(props),
					configName(props),
					configCompactMode(props)
				),
				panelize(
					__('Other elements', 'peepso-core'),
					configAvatar(props),
					configNotifications(props),
					configUsermenu(props),
					configUserlogout(props),
					configVIP(props),
					configBadges(props)
				)
			];

			let controls = createElement(
				InspectorControls,
				null,
				...hooks.applyFilters('peepso_block_settings', settings, props, 'peepso/user-bar')
			);

			// Render content.
			let content = createElement(serverSideRender, {
				block: 'peepso/user-bar',
				attributes: props.attributes
			});

			return createElement('div', blockProps, controls, content);
		},
		save() {
			return null;
		}
	});
})(window.wp, window.peepsoBlockUserBarEditorData);
