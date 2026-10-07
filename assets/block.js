(/**
 * Register the daily-reading block with native WordPress editor APIs.
 * @param {Object} blocks WordPress block registration API.
 * @param {Object} element WordPress element API.
 * @param {Object} i18n WordPress translation API.
 * @param {Object} components WordPress component API.
 * @param {Object} blockEditor WordPress block editor API.
 * @param {Object} serverSideRender WordPress server rendering component API.
 * @returns {void} No return value.
 */
function (blocks, element, i18n, components, blockEditor, serverSideRender) {
	'use strict';
	var el = element.createElement;
	var __ = i18n.__;
	var config = window.dailyScriptureBlock || {};
	// WordPress exposes a legacy default component and, in newer versions, named exports.
	var ServerSideRender = serverSideRender.ServerSideRender || serverSideRender.default || serverSideRender;
	var attributes = {};
	['source', 'layout', 'density', 'theme', 'title_herrnhuter', 'title_bible2'].forEach(/**
	 * Define an inheritable string attribute.
	 * @param {string} key Selected property or setting identifier.
	 * @returns {void} No return value.
	 */
	function (key) {
		attributes[key] = { type: 'string', default: '' };
	});
	blocks.registerBlockType('daily-scripture/today', {
		apiVersion: 3,
		title: 'Daily Scripture',
		icon: 'book-alt',
		category: 'widgets',
		attributes: attributes,
		supports: { html: false },
		edit: /**
		 * Render daily-reading controls and the shared server preview.
		 * @param {Object} props WordPress block properties with attributes and setAttributes.
		 * @returns {Object} Value consumed by the calling editor or request handler.
		 */
		function (props) {
			var current = props.attributes;
			var source = current.source || config.source || 'herrnhuter';
			/**
			 * Create an attribute update callback for one block setting.
			 * @param {string} key Selected property or setting identifier.
			 * @returns {Function} Value consumed by the calling editor or request handler.
			 */
			function update(key) {
				/**
				 * Apply one editor value to the selected attribute.
				 * @param {string} value Current control or source value.
				 * @returns {void} No return value.
				 */
				return function (value) { var change = {}; change[key] = value; props.setAttributes(change); };
			}
			/**
			 * Render an inheritable source-heading control.
			 * @param {string} provider Source identifier.
			 * @param {string} label Localized control label.
			 * @returns {Object} Value consumed by the calling editor or request handler.
			 */
			function heading(provider, label) {
				if (source !== 'both' && source !== provider) { return null; }
				var key = 'title_' + provider;
				return el(components.TextControl, {
					key: key, label: label, value: current[key], placeholder: config[key], maxLength: 160,
					help: __('Leave blank to inherit the heading from plugin settings.', 'daily-scripture'), onChange: update(key)
				});
			}
			return el(element.Fragment, null,
				el(blockEditor.InspectorControls, null,
					el(components.PanelBody, { title: __('Source & headings', 'daily-scripture'), initialOpen: true },
						el(components.SelectControl, {
							label: __('Data source', 'daily-scripture'), value: current.source,
							options: [
								{ label: __('Site setting', 'daily-scripture'), value: '' },
								{ label: 'Die Losungen', value: 'herrnhuter' },
								{ label: 'Bible 2.0', value: 'bible2' },
								{ label: __('Both sources', 'daily-scripture'), value: 'both' }
							], onChange: update('source')
						}),
						heading('herrnhuter', __('Heading for Die Losungen', 'daily-scripture')),
						heading('bible2', __('Heading for Bible 2.0', 'daily-scripture'))
					),
					el(components.PanelBody, { title: __('Presentation', 'daily-scripture'), initialOpen: true },
						[['layout', __('Layout', 'daily-scripture')], ['density', __('Spacing mode', 'daily-scripture')], ['theme', __('Color scheme', 'daily-scripture')]].map(/**
						 * Render one presentation selector.
						 * @param {Array} setting Setting identifier and localized label.
						 * @returns {Object} Value consumed by the calling editor or request handler.
						 */
						function (setting) {
							var choices = (config.choices || {})[setting[0]] || {};
							return el(components.SelectControl, {
								key: setting[0], label: setting[1], value: current[setting[0]],
								options: [{ label: __('Site setting', 'daily-scripture'), value: '' }].concat(Object.keys(choices).map(/**
								 * Convert a presentation choice to a selector option.
								 * @param {string} key Selected property or setting identifier.
								 * @returns {Object} Value consumed by the calling editor or request handler.
								 */
								function (key) { return { value: key, label: choices[key] }; })),
								onChange: update(setting[0])
							});
						}),
						el('p', null, __('Font sizes, custom colors and date format follow plugin settings. The preview shows today’s local verses.', 'daily-scripture'))
					)
				),
				el('div', blockEditor.useBlockProps(),
					el(components.Disabled, null,
						el(ServerSideRender, { block: 'daily-scripture/today', attributes: current, httpMethod: 'POST' })
					)
				)
			);
		},
		save: /**
		 * Keep the dynamic block free of saved verse HTML.
		 * @returns {null} Value consumed by the calling editor or request handler.
		 */
		function () { return null; }
	});
})(window.wp.blocks, window.wp.element, window.wp.i18n, window.wp.components, window.wp.blockEditor, window.wp.serverSideRender);
