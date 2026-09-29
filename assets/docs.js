/* global dailyScriptureDocs */
(function () {
 'use strict';
 document.querySelectorAll('.ds-docs [data-copy-target]').forEach(function (button) {
  button.hidden = false;
  button.addEventListener('click', async function () {
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
