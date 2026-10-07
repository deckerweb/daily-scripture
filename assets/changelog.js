(/**
 * Enhance changelog links with an accessible native dialog.
 * @returns {void} No return value.
 */
function () {
	'use strict';
	var dialog = document.getElementById('ds-changelog');
	var trigger = document.querySelector('[data-ds-changelog]');
	if (!dialog || !trigger || typeof dialog.showModal !== 'function') { return; }
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
			dialog.showModal();
			dialog.querySelector('.ds-changelog__content').scrollTop = 0;
			event.preventDefault();
		} catch (error) { /* The original readme link remains the fallback. */ }
	});
	close.addEventListener('click', /**
	 * Close the active changelog dialog.
	 * @returns {void} No return value.
	 */
	function () { dialog.close(); });
	// Native modal dialogs provide focus containment and Escape handling.
	dialog.addEventListener('close', /**
	 * Restore keyboard focus to the changelog trigger.
	 * @returns {void} No return value.
	 */
	function () { trigger.focus(); });
	dialog.addEventListener('click', /**
	 * Close the dialog only when clicking its backdrop.
	 * @param {Event} event Originating DOM event.
	 * @returns {void} No return value.
	 */
	function (event) {
		var rect = dialog.getBoundingClientRect();
		if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) {
			dialog.close();
		}
	});
})();
