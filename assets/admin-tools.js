(/**
 * Add copy convenience to administrator-only diagnostics with a text fallback.
 * @returns {void} No return value.
 */
function () {
 'use strict';
 var labels = window.dailyScriptureAdminTools;
 if (!labels) { return; }
 document.querySelectorAll('[data-ds-diagnostic-copy]').forEach(/**
  * Enhance one collapsed diagnostic without changing or submitting data.
  * @param {HTMLButtonElement} button Copy control.
  * @returns {void} No return value.
  */
 function (button) {
  button.hidden = false;
  button.addEventListener('click', /**
   * Copy only the visible approved diagnostic, or select it for manual copying.
   * @returns {Promise<void>} No return value.
   */
  async function () {
   var details = button.closest('.ds-download-diagnostic');
   var field = details.querySelector('textarea');
   var status = details.querySelector('[role="status"]');
   try {
    if (!navigator.clipboard || !window.isSecureContext) { throw new Error('Clipboard unavailable'); }
    await navigator.clipboard.writeText(field.value);
    status.textContent = labels.copied;
   } catch (error) {
    field.focus(); field.select(); status.textContent = labels.failed;
   }
  });
 });
})();
