(/**
 * Connect the design form to bounded previews and reviewed settings import.
 * @returns {void} No return value.
 */
function () {
 'use strict';
 var config = window.dailyScriptureStudio;
 var form = document.getElementById('ds-settings-form');
 if (!config || !form) { return; }
 var frame = document.getElementById('ds-preview-frame');
 var status = document.getElementById('ds-preview-status');
 var scale = document.getElementById('ds-type_scale');
 var slider = document.getElementById('ds-scale-slider');
 var expert = document.getElementById('ds-expert-toggle');
 var requestId = 0;
 var pending;
 var timer;
 /**
  * Update enabled expert controls from the current form state.
  * @returns {void} No return value.
  */
 function updateControls() {
  form.querySelectorAll('[data-ds-reading-slot]').forEach(/**
   * Keep inactive passage fields out of native form validation and submissions.
   * @param {HTMLElement} row Website passage slot.
   * @returns {void} No return value.
   */
  function (row) {
   var enabled = row.querySelector('input[type=checkbox]').checked;
   row.querySelectorAll('input:not([type=checkbox]),select').forEach(/**
    * Disable only definition fields, keeping the availability checkbox usable.
    * @param {HTMLInputElement} field Passage field.
    * @returns {void} No return value.
    */
   function (field) { field.disabled = !enabled; });
  });
  form.querySelectorAll('[data-ds-font]').forEach(/**
   * Apply the expert-mode state to one font control.
   * @param {HTMLInputElement} field Form control being updated.
   * @returns {void} No return value.
   */
  function (field) {
   var value = field.value.trim();
   var length = '(?:[0-9]+(?:\\.[0-9]+)?|\\.[0-9]+)(?:px|rem|em|%)';
   var simple = new RegExp('^' + length + '$');
   var token = new RegExp('^var\\((--[a-zA-Z_][a-zA-Z0-9_-]*)(?:,\\s*(' + length + '))?\\)$').exec(value);
   var valid = !value || (simple.test(value) && parseFloat(value) > 0 && parseFloat(value) <= 1000) || (token && !/^--ds-/i.test(token[1]) && (!token[2] || (parseFloat(token[2]) > 0 && parseFloat(token[2]) <= 1000)));
   field.setCustomValidity(expert.checked && !valid ? config.fontError : '');
  });
  document.getElementById('ds-expert-fields').hidden = !expert.checked;
  slider.value = scale.value;
  var width = document.getElementById('ds-preview-width').value;
  frame.style.width = width === 'wide' ? '100%' : width + 'px';
 }
 /**
  * Request a preview and replace only the latest response.
  * @returns {void} No return value.
  */
 function preview() {
  updateControls();
  requestId += 1;
  var id = requestId;
  if (pending) { pending.abort(); }
  if (!form.checkValidity()) { frame.setAttribute('aria-busy', 'false'); status.textContent = config.invalid; return; }
  pending = new AbortController();
  var data = new URLSearchParams();
  data.set('action', 'daily_scripture_preview');
  data.set('nonce', config.nonce);
  data.set('context', document.getElementById('ds-preview-context').value);
  data.set('stress', document.getElementById('ds-preview-stress').checked ? '1' : '0');
  new FormData(form).forEach(/**
   * Collect one permitted scalar field for the preview request.
   * @param {string} value Current control or source value.
   * @param {string} name Form field name.
   * @returns {void} No return value.
   */
  function (value, name) {
   var match = /^daily_scripture_settings\[([a-z_]+)\]$/.exec(name);
   if (match) { data.set('settings[' + match[1] + ']', value); }
  });
  status.textContent = config.loading;
  frame.setAttribute('aria-busy', 'true');
  fetch(config.url, { method: 'POST', credentials: 'same-origin', body: data, signal: pending.signal })
   .then(/**
    * Decode the preview response as JSON.
    * @param {Response} response Completed HTTP response.
    * @returns {Promise<Object>} Value consumed by the calling editor or request handler.
    */
   function (response) { return response.json(); })
   .then(/**
    * Apply the latest validated preview response to the frame.
    * @param {Object} result Validated server response.
    * @returns {void} No return value.
    */
   function (result) {
    if (id !== requestId) { return; }
    if (!result.success || !result.data.document) { throw new Error(config.error); }
    frame.srcdoc = result.data.document;
    frame.parentElement.scrollTop = 0;
    status.textContent = config.ready;
   })
   .catch(/**
    * Display a preview failure unless it belongs to an aborted request.
    * @param {Error} error Rejected asynchronous operation.
    * @returns {void} No return value.
    */
   function (error) { if (error.name !== 'AbortError' && id === requestId) { status.textContent = config.error; } })
   .finally(/**
    * Clear the latest preview busy state.
    * @returns {void} No return value.
    */
   function () { if (id === requestId) { frame.setAttribute('aria-busy', 'false'); } });
 }
 /**
  * Mark changes and debounce the next preview.
  * @returns {void} No return value.
  */
 function schedule() { document.getElementById('ds-save-status').textContent = config.dirty; clearTimeout(timer); timer = setTimeout(preview, 300); updateControls(); }
 document.querySelectorAll('[data-ds-scale]').forEach(/**
  * Attach a preset scale button to the preview scheduler.
  * @param {HTMLButtonElement} button Copy or preset control.
  * @returns {void} No return value.
  */
 function (button) { button.addEventListener('click', /**
  * Apply the selected scale preset.
  * @returns {void} No return value.
  */
 function () { scale.value = button.dataset.dsScale; schedule(); }); });
 form.addEventListener('input', schedule);
 form.addEventListener('change', schedule);
 slider.addEventListener('input', /**
  * Apply the scale slider value and schedule its preview.
  * @returns {void} No return value.
  */
 function () { scale.value = slider.value; schedule(); });
 ['ds-preview-width', 'ds-preview-context', 'ds-preview-stress'].forEach(/**
  * Attach one preview-context control to the scheduler.
  * @param {string} id Preview control identifier.
  * @returns {void} No return value.
  */
 function (id) { document.getElementById(id).addEventListener('change', schedule); });
 document.getElementById('ds-preview-refresh').addEventListener('click', preview);
 window.addEventListener('message', /**
  * Accept height reports only from this preview frame.
  * @param {Event} event Originating DOM event.
  * @returns {void} No return value.
  */
 function (event) {
  if (event.source !== frame.contentWindow || !event.data || event.data.type !== 'daily-scripture-preview') { return; }
  if (Number.isFinite(event.data.height)) { frame.style.height = Math.max(280, Math.min(1800, event.data.height + 8)) + 'px'; }
  if (event.data.lowContrast === true) { status.textContent = config.contrast; }
 });
 var importButton = document.getElementById('ds-import-settings');
 importButton.addEventListener('click', /**
  * Read an import file and request validation without saving settings.
  * @returns {void} No return value.
  */
 function () {
  var file = document.getElementById('ds-settings-file').files[0];
  var note = document.getElementById('ds-import-status');
  if (!file || file.size > 65536 || !/\.json$/i.test(file.name)) { note.textContent = config.fileError; return; }
  importButton.disabled = true;
  file.text().then(/**
   * Submit the exact settings JSON for server validation.
   * @param {string} text Exact uploaded JSON content.
   * @returns {Promise<Response>} Value consumed by the calling editor or request handler.
   */
  function (text) {
   var data = new URLSearchParams({ action: 'daily_scripture_validate_settings', nonce: config.nonce, document: text });
   return fetch(config.url, { method: 'POST', credentials: 'same-origin', body: data });
  }).then(/**
   * Decode the settings-validation response.
   * @param {Response} response Completed HTTP response.
   * @returns {Promise<Object>} Value consumed by the calling editor or request handler.
   */
  function (response) { return response.json(); }).then(/**
   * Populate reviewed settings for the user to save explicitly.
   * @param {Object} result Validated server response.
   * @returns {void} No return value.
   */
  function (result) {
   if (!result.success) { throw new Error(result.data && result.data.message ? result.data.message : config.fileError); }
   var values = result.data.settings;
   Object.keys(values).forEach(/**
    * Locate controls for one validated setting.
    * @param {string} key Selected property or setting identifier.
    * @returns {void} No return value.
    */
   function (key) {
    var fields = form.querySelectorAll('[name="daily_scripture_settings[' + key + ']"]');
    fields.forEach(/**
     * Apply a validated value to one matching control.
     * @param {HTMLInputElement} field Form control being updated.
     * @returns {void} No return value.
     */
    function (field) {
     if (field.type === 'radio') { field.checked = field.value === String(values[key]); }
     else if (field.type === 'checkbox') { field.checked = values[key] === 1; }
     else { field.value = values[key]; }
    });
   });
   if (result.data.dashboard_readings !== null && result.data.dashboard_readings !== undefined) {
    form.querySelectorAll('[name^="daily_scripture_dashboard_readings["]').forEach(/**
     * Stage validated reading definitions without changing current text data.
     * @param {HTMLInputElement} field Website reading control.
     * @returns {void} No return value.
     */
    function (field) {
     var match = /^daily_scripture_dashboard_readings\[([a-z0-9_]+)\]\[([a-z]+)\]$/.exec(field.name);
     if (!match) { return; }
     var row = result.data.dashboard_readings[match[1]];
     if (field.type === 'checkbox') { field.checked = !!(row && row.enabled); }
     else if (row && row[match[2]] !== undefined) { field.value = row[match[2]]; }
    });
   }
   form.dispatchEvent(new Event('ds-settings-imported'));
   note.textContent = config.imported;
   schedule();
  }).catch(/**
   * Show a settings import failure in its status area.
   * @param {Error} error Rejected asynchronous operation.
   * @returns {void} No return value.
   */
  function (error) { note.textContent = error.message; }).finally(/**
   * Re-enable import after the validation request finishes.
   * @returns {void} No return value.
   */
  function () { importButton.disabled = false; });
 });
 preview();
})();
