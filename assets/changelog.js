(/**
 * Enhance changelog links with an accessible native dialog.
 * @returns {void} No return value.
 */
function () {
	'use strict';
	var dialog = document.getElementById('ds-changelog');
	var trigger = document.querySelector('[data-ds-changelog]');
	if (!dialog || !trigger || typeof HTMLDialogElement === 'undefined' || typeof HTMLDialogElement.prototype.showModal !== 'function') { return; }
	var close = dialog.querySelector('[data-ds-changelog-close]');
	trigger.setAttribute('aria-haspopup', 'dialog');
	trigger.setAttribute('aria-controls', dialog.id);
	trigger.addEventListener('click', /**
	 * Open the changelog and retain its trigger for focus restoration.
	 * @param {Event} event Originating DOM event.
	 * @returns {void} No return value.
	 */
	function (event) {
		// Preserve normal link behavior for modified clicks and unsupported browsers.
		if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button) { return; }
		try {
			dialog.hidden = false;
			HTMLDialogElement.prototype.showModal.call(dialog);
			if (!dialog.open) { dialog.hidden = true; return; }
			dialog.querySelector('.ds-changelog__content').scrollTop = 0;
			event.preventDefault();
		} catch (error) { dialog.hidden = true; /* Retain the original documentation link. */ }
	});
	close.addEventListener('click', /**
	 * Close the active changelog dialog.
	 * @returns {void} No return value.
	 */
	function () { try { HTMLDialogElement.prototype.close.call(dialog); } catch (error) { /* The native dialog form closes independently. */ } });
	// Native modal dialogs provide focus containment and Escape handling.
	dialog.addEventListener('close', /**
	 * Restore keyboard focus to the changelog trigger.
	 * @returns {void} No return value.
	 */
	function () { dialog.hidden = true; trigger.focus(); });
	dialog.addEventListener('click', /**
	 * Close the dialog only when clicking its backdrop.
	 * @param {Event} event Originating DOM event.
	 * @returns {void} No return value.
	 */
	function (event) {
		var rect = dialog.getBoundingClientRect();
		if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) {
			HTMLDialogElement.prototype.close.call(dialog);
		}
	});
})();
