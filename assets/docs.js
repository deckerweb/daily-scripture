/* global dailyScriptureDocs */
(/**
 * Attach copy controls to the plugin shortcode documentation.
 * @returns {void} No return value.
 */
function () {
 'use strict';
 document.querySelectorAll('.ds-docs [data-copy-target]').forEach(/**
  * Prepare one shortcode copy control and status message.
  * @param {HTMLButtonElement} button Copy or preset control.
  * @returns {void} No return value.
  */
 function (button) {
  button.hidden = false;
  button.addEventListener('click', /**
   * Copy the shortcode with a selectable-text fallback.
   * @returns {Promise<void>} No return value.
   */
  async function () {
   const field = document.getElementById(button.dataset.copyTarget);
   const status = button.parentElement.querySelector('[role="status"]');
   if (!field || !status) return;
   try {
    if (!navigator.clipboard || !window.isSecureContext) throw new Error('Clipboard unavailable');
    await navigator.clipboard.writeText(field.value);
    status.textContent = dailyScriptureDocs.copied;
   } catch (error) {
    field.focus(); field.select();
    status.textContent = dailyScriptureDocs.failed;
   }
  });
 });
}());
