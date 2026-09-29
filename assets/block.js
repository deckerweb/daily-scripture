(function (blocks, element, i18n, components, blockEditor, serverSideRender) {
	'use strict';
	var el = element.createElement;
	var __ = i18n.__;
	var config = window.dailyScriptureBlock || {};
	// WordPress exposes a legacy default component and, in newer versions, named exports.
	var ServerSideRender = serverSideRender.ServerSideRender || serverSideRender.default || serverSideRender;
	var attributes = {};
	['source', 'layout', 'density', 'theme', 'title_herrnhuter', 'title_bible2'].forEach(function (key) {
		attributes[key] = { type: 'string', default: '' };
	});
	blocks.registerBlockType('daily-scripture/today', {
		apiVersion: 3,
		title: 'Daily Scripture',
		icon: 'book-alt',
		category: 'widgets',
		attributes: attributes,
		supports: { html: false },
		edit: function (props) {
			var current = props.attributes;
			var source = current.source || config.source || 'herrnhuter';
			function update(key) {
				return function (value) { var change = {}; change[key] = value; props.setAttributes(change); };
			}
			function heading(provider, label) {
				if (source !== 'both' && source !== provider) { return null; }
				var key = 'title_' + provider;
				return el(components.TextControl, {
					key: key, label: label, value: current[key], placeholder: config[key], maxLength: 160,
					help: __('Leer übernimmt die Überschrift aus den Plugin-Einstellungen.', 'daily-scripture'), onChange: update(key)
				});
			}
			return el(element.Fragment, null,
				el(blockEditor.InspectorControls, null,
					el(components.PanelBody, { title: __('Quelle & Überschriften', 'daily-scripture'), initialOpen: true },
						el(components.SelectControl, {
							label: __('Datenquelle', 'daily-scripture'), value: current.source,
							options: [
								{ label: __('Website-Einstellung', 'daily-scripture'), value: '' },
								{ label: 'Die Losungen', value: 'herrnhuter' },
								{ label: 'Bible 2.0', value: 'bible2' },
								{ label: __('Beide Quellen', 'daily-scripture'), value: 'both' }
							], onChange: update('source')
						}),
						heading('herrnhuter', __('Überschrift für Die Losungen', 'daily-scripture')),
						heading('bible2', __('Überschrift für Bible 2.0', 'daily-scripture'))
					),
					el(components.PanelBody, { title: __('Darstellung', 'daily-scripture'), initialOpen: true },
						[['layout', __('Layout', 'daily-scripture')], ['density', __('Ansicht', 'daily-scripture')], ['theme', __('Farbschema', 'daily-scripture')]].map(function (setting) {
							var choices = (config.choices || {})[setting[0]] || {};
							return el(components.SelectControl, {
								key: setting[0], label: setting[1], value: current[setting[0]],
								options: [{ label: __('Website-Einstellung', 'daily-scripture'), value: '' }].concat(Object.keys(choices).map(function (key) { return { value: key, label: choices[key] }; })),
								onChange: update(setting[0])
							});
						}),
						el('p', null, __('Schriftgrößen, eigene Farben und Datumsformat folgen den Plugin-Einstellungen. Die Vorschau zeigt die lokalen Verse des heutigen Tages.', 'daily-scripture'))
					)
				),
				el('div', blockEditor.useBlockProps(),
					el(components.Disabled, null,
						el(ServerSideRender, { block: 'daily-scripture/today', attributes: current, httpMethod: 'POST' })
					)
				)
			);
		},
		save: function () { return null; }
	});
})(window.wp.blocks, window.wp.element, window.wp.i18n, window.wp.components, window.wp.blockEditor, window.wp.serverSideRender);
