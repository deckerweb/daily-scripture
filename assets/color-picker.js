(/**
 * Enhance only studio color fields with the modern WordPress ColorPicker.
 * Text inputs remain the submitted values and work without JavaScript.
 * @returns {void} No return value.
 */
function () {
 'use strict';
 var form = document.getElementById('ds-settings-form');
 var wp = window.wp;
 if (!form || !wp || !wp.components || !wp.element || !wp.components.Modal) { return; }
 var el = wp.element.createElement;
 var opened = null;
 var expert = document.getElementById('ds-expert-toggle');
 /**
  * Close the current picker and optionally restore its trigger's focus.
  * @param {boolean} focus Whether to return keyboard focus.
  * @returns {void} No return value.
  */
 function close(focus) {
  if (!opened) { return; }
  opened.panel.hidden = true;
    opened.button.setAttribute('aria-expanded', 'false');
  if (focus) { opened.button.focus(); }
  opened.root.unmount();
  opened.root = null;
  opened = null;
 }
 form.querySelectorAll('[data-ds-color]').forEach(/**
  * Connect a canonical hex field to one lazy, alpha-free WordPress picker.
  * @param {HTMLInputElement} field The setting submitted by WordPress.
  * @returns {void} No return value.
  */
 function (field) {
  var label = field.closest('label');
  var section = field.closest('fieldset');
  var title = section ? section.querySelector('legend').textContent : label.querySelector('span').textContent;
  var tools = document.createElement('div');
  tools.className = 'ds-color-tools';
  var actions = document.createElement('div');
  actions.className = 'ds-color-tools__actions';
  var button = document.createElement('button');
  button.type = 'button';
  button.className = 'button';
  button.setAttribute('aria-expanded', 'false');
  button.setAttribute('aria-label', wp.i18n.__('Choose color', 'daily-scripture') + ': ' + title);
  var swatch = document.createElement('span');
  swatch.className = 'ds-color-swatch';
  swatch.setAttribute('aria-hidden', 'true');
  button.append(swatch, document.createTextNode(wp.i18n.__('Choose color', 'daily-scripture')));
  var panel = document.createElement('div');
  panel.className = 'ds-color-picker';
  panel.id = field.id + '-picker';
  panel.hidden = true;
  panel.setAttribute('role', 'group');
  panel.setAttribute('aria-label', title);
  button.setAttribute('aria-haspopup', 'dialog');
  actions.append(button);
  tools.append(actions, panel);
  var wrapper = document.createElement('div');
  wrapper.className = 'ds-color-field';
  label.before(wrapper);
  wrapper.append(label, tools);
  var state = { panel: panel, button: button, wrapper: wrapper, root: null };
  /**
   * Validate active fields and update the current selection without storing options.
   * @returns {void} No return value.
   */
  function sync() {
   var value = field.value.trim();
   var valid = /^#(?:[a-f0-9]{3}|[a-f0-9]{6})$/i.test(value);
   var applicable = field.required || (expert && expert.checked);
   field.setCustomValidity(applicable && value && !valid ? wp.i18n.__('Please enter a hexadecimal color such as #245c73.', 'daily-scripture') : '');
   swatch.style.backgroundColor = valid ? value : 'transparent';
   if (state.root) {
    state.root.render(el(wp.components.Modal, {
     title: wp.i18n.__('Choose color', 'daily-scripture') + ': ' + title,
     className: 'ds-color-modal',
     onRequestClose: /**
      * Let WordPress dismiss the dialog and return to the invoking field.
      * @returns {void} No return value.
      */
     function () { close(true); }
    }, el(wp.components.ColorPicker, {
     color: valid ? value : '#ffffff', enableAlpha: false,
     onChange: /**
      * Forward only supported hex values to the canonical settings form.
      * @param {string} color WordPress picker output.
      * @returns {void} No return value.
      */
     function (color) {
      if (!/^#[a-f0-9]{6}(?:ff)?$/i.test(color)) { return; }
      field.value = color.slice(0, 7);
      field.dispatchEvent(new Event('input', { bubbles: true }));
     }
    }), el('p', null, wp.i18n.__('Colors are applied to the form. Save settings to publish them.', 'daily-scripture')), el(wp.components.Button, {
     variant: 'primary',
     onClick: /**
      * Close the dialog without implicitly saving website settings.
      * @returns {void} No return value.
      */
     function () { close(true); }
    }, wp.i18n.__('Close', 'daily-scripture'))));
   }
  }
  button.addEventListener('click', /**
   * Toggle this picker, mounting at most one WordPress component at a time.
   * @returns {void} No return value.
   */
  function () {
   var same = opened === state;
   close(false);
   if (same) { return; }
   panel.hidden = false;
   button.setAttribute('aria-expanded', 'true');
   state.root = wp.element.createRoot(panel);
   opened = state;
   sync();
  });
  if (!field.required) {
   var clear = document.createElement('button');
   clear.type = 'button';
   clear.className = 'button-link';
   clear.textContent = wp.i18n.__('Use color scheme', 'daily-scripture');
   clear.addEventListener('click', /**
    * Restore inherited text color without changing the saved color scheme.
    * @returns {void} No return value.
    */
   function () {
    field.value = '';
    close(false);
    field.dispatchEvent(new Event('input', { bubbles: true }));
   });
   actions.append(clear);
  }
  field.addEventListener('input', sync);
  form.addEventListener('ds-settings-imported', sync);
  expert.addEventListener('change', sync);
  sync();
 });
 form.addEventListener('keydown', /**
  * Close an open picker with Escape without losing the settings form.
  * @param {KeyboardEvent} event Current keyboard event.
  * @returns {void} No return value.
  */
 function (event) {
  if (event.key === 'Escape' && opened) { event.preventDefault(); event.stopPropagation(); close(true); }
 });
 expert.addEventListener('change', /**
  * Remove hidden expert-picker controls from the active interaction context.
  * @returns {void} No return value.
  */
 function () { if (!expert.checked) { close(false); } });
})();
