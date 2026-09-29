/* global wp, dailyScripturePassage */
(function () {
 'use strict';
 const el = wp.element.createElement;
 const __ = wp.i18n.__;
 const options = (map) => Object.entries(map).map(([value, label]) => ({value, label}));
 wp.blocks.registerBlockType('daily-scripture/passage', {
  apiVersion: 3,
  title: __('Bibelstelle · Daily Scripture', 'daily-scripture'),
  description: __('Eigene Bibelstellen aus lokal installierten freien Übersetzungen.', 'daily-scripture'),
  icon: 'book-alt', category: 'widgets', supports: {html: false},
  attributes: {
   translation: {type: 'string', default: 'luther-1912'}, book: {type: 'string', default: 'JOH'},
   chapter: {type: 'integer', default: 3}, from: {type: 'integer', default: 16}, to: {type: 'integer', default: 16},
   title: {type: 'string', default: ''}, layout: {type: 'string', default: ''}, density: {type: 'string', default: ''}, theme: {type: 'string', default: ''}
  },
  edit: function ({attributes: a, setAttributes}) {
   const select = (key, label, choices) => el(wp.components.SelectControl, {label, value: a[key], options: choices, onChange: value => setAttributes({[key]: value})});
   const number = (key, label) => el(wp.components.TextControl, {label, type: 'number', min: 1, max: key === 'chapter' ? 150 : 176, value: a[key], onChange: value => { const n = Number(value); if (Number.isInteger(n) && n >= 1 && n <= 176) setAttributes({[key]: n}); }});
   const inherit = {value: '', label: __('Website-Einstellung', 'daily-scripture')};
   // Support both the legacy component and newer named WordPress exports.
   const SSR = wp.serverSideRender.ServerSideRender || wp.serverSideRender.default || wp.serverSideRender;
   return el('div', wp.blockEditor.useBlockProps(),
    el(wp.blockEditor.InspectorControls, {},
     el(wp.components.PanelBody, {title: __('Bibelstelle & Übersetzung', 'daily-scripture')},
      select('translation', __('Übersetzung', 'daily-scripture'), options(dailyScripturePassage.editions)),
      el('p', {}, __('Installiere die Ausgabe zuerst unter Daily Scripture → Bibelbibliothek. Bis dahin zeigt die Vorschau einen Hinweis.', 'daily-scripture')),
      select('book', __('Buch', 'daily-scripture'), options(dailyScripturePassage.books)),
      number('chapter', __('Kapitel', 'daily-scripture')), number('from', __('Erster Vers', 'daily-scripture')), number('to', __('Letzter Vers', 'daily-scripture')),
      el('p', {}, __('Bis zu 50 Verse in einem Kapitel. Die Verszählung richtet sich nach der gewählten Ausgabe.', 'daily-scripture')),
      el(wp.components.TextControl, {label: __('Eigene Überschrift', 'daily-scripture'), help: __('Leer zeigt die Bibelstelle als Titel.', 'daily-scripture'), value: a.title, maxLength: 160, onChange: title => setAttributes({title})})),
     el(wp.components.PanelBody, {title: __('Darstellung', 'daily-scripture'), initialOpen: false},
      select('layout', __('Layout', 'daily-scripture'), [inherit].concat(options(dailyScripturePassage.layouts))),
      select('density', __('Ansicht', 'daily-scripture'), [inherit, {value:'standard', label:__('Standard','daily-scripture')}, {value:'compact', label:__('Kompakt','daily-scripture')}]),
      select('theme', __('Farbschema', 'daily-scripture'), [inherit, {value:'light',label:__('Hell','daily-scripture')},{value:'dark',label:__('Dunkel','daily-scripture')},{value:'auto',label:__('Geräteeinstellung','daily-scripture')},{value:'custom',label:__('Eigene Farben','daily-scripture')}]))),
    el(wp.components.Disabled, {}, el(SSR, {block: 'daily-scripture/passage', attributes: a, httpMethod: 'POST'})));
  }, save: function () { return null; }
 });
}());
