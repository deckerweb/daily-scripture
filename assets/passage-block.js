/* global wp, dailyScripturePassage */
(/**
 * Register the local-passage block with native WordPress editor APIs.
 * @returns {void} No return value.
 */
function () {
 'use strict';
 const el = wp.element.createElement;
 const __ = wp.i18n.__;
 /** Convert a localized lookup to native selector options. @param {Object} map Values and labels. @returns {Array} Selector options. */
 const options = (map) => Object.entries(map).map(/** Preserve one lookup entry as a selector option. @param {Array} entry Value and label pair. @returns {Object} Selector option. */ ([value, label]) => ({value, label}));
 wp.blocks.registerBlockType('daily-scripture/passage', {
  apiVersion: 3,
  title: __('Bible passage · Daily Scripture', 'daily-scripture'),
  description: __('Selected passages from freely licensed Bible editions installed locally.', 'daily-scripture'),
  icon: 'book-alt', category: 'widgets', supports: {html: false},
  attributes: {
   translation: {type: 'string', default: 'luther-1912'}, book: {type: 'string', default: 'JOH'},
   chapter: {type: 'integer', default: 3}, from: {type: 'integer', default: 16}, to: {type: 'integer', default: 16},
   title: {type: 'string', default: ''}, layout: {type: 'string', default: ''}, density: {type: 'string', default: ''}, theme: {type: 'string', default: ''}
  },
  edit: /**
   * Render passage controls and the shared server preview.
   * @param {Object} options Block attributes and attribute updater.
   * @param {Object} options.attributes Current block attribute values.
   * @param {Function} options.setAttributes WordPress attribute updater.
   * @returns {Object} Value consumed by the calling editor or request handler.
   */
  function ({attributes: a, setAttributes}) {
   /** Render an inheritable selection control. @param {string} key Attribute key. @param {string} label Localized label. @param {Array} choices Options. @returns {Object} Editor element. */
   const select = (key, label, choices) => el(wp.components.SelectControl, {label, value: a[key], options: choices, onChange: /** Save the selected attribute value. @param {string} value Selected option. @returns {void} No return value. */ value => setAttributes({[key]: value})});
   /** Render an integer passage-coordinate control. @param {string} key Attribute key. @param {string} label Localized label. @returns {Object} Editor element. */
   const number = (key, label) => el(wp.components.TextControl, {label, type: 'number', min: 1, max: key === 'chapter' ? 150 : 176, value: a[key], onChange: /** Save only a valid positive verse coordinate. @param {string} value Numeric control input. @returns {void} No return value. */ value => { const n = Number(value); if (Number.isInteger(n) && n >= 1 && n <= 176) setAttributes({[key]: n}); }});
   const inherit = {value: '', label: __('Site setting', 'daily-scripture')};
   // Support both the legacy component and newer named WordPress exports.
   const SSR = wp.serverSideRender.ServerSideRender || wp.serverSideRender.default || wp.serverSideRender;
   return el('div', wp.blockEditor.useBlockProps(),
    el(wp.blockEditor.InspectorControls, {},
     el(wp.components.PanelBody, {title: __('Passage & translation', 'daily-scripture')},
      select('translation', __('Translation', 'daily-scripture'), options(dailyScripturePassage.editions)),
      el('p', {}, __('Install the edition under Daily Scripture → Bible library first. Until then, the preview displays a notice.', 'daily-scripture')),
      select('book', __('Book', 'daily-scripture'), options(dailyScripturePassage.books)),
      number('chapter', __('Chapter', 'daily-scripture')), number('from', __('First verse', 'daily-scripture')), number('to', __('Last verse', 'daily-scripture')),
      el('p', {}, __('Up to 50 verses in one chapter. Verse numbering follows the selected edition.', 'daily-scripture')),
      el(wp.components.TextControl, {label: __('Custom heading', 'daily-scripture'), help: __('Leave blank to use the Bible reference as the title.', 'daily-scripture'), value: a.title, maxLength: 160, onChange: /** Save the user-defined passage heading. @param {string} title Heading text. @returns {void} No return value. */ title => setAttributes({title})})),
     el(wp.components.PanelBody, {title: __('Presentation', 'daily-scripture'), initialOpen: false},
      select('layout', __('Layout', 'daily-scripture'), [inherit].concat(options(dailyScripturePassage.layouts))),
      select('density', __('Spacing mode', 'daily-scripture'), [inherit, {value:'standard', label:__('Standard','daily-scripture')}, {value:'compact', label:__('Compact','daily-scripture')}]),
      select('theme', __('Color scheme', 'daily-scripture'), [inherit, {value:'light',label:__('Light','daily-scripture')},{value:'dark',label:__('Dark','daily-scripture')},{value:'auto',label:__('Device setting','daily-scripture')},{value:'custom',label:__('Custom colors','daily-scripture')}]))),
    el(wp.components.Disabled, {}, el(SSR, {block: 'daily-scripture/passage', attributes: a, httpMethod: 'POST'})));
  }, save: /**
   * Keep the dynamic block free of saved verse HTML.
   * @returns {null} Value consumed by the calling editor or request handler.
   */
  function () { return null; }
 });
}());
