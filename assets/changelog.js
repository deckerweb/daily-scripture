(function () {
	'use strict';
	var dialog = document.getElementById('ds-changelog');
	var trigger = document.querySelector('[data-ds-changelog]');
	if (!dialog || !trigger || typeof dialog.showModal !== 'function') { return; }
	var close = dialog.querySelector('[data-ds-changelog-close]');
	trigger.setAttribute('aria-haspopup', 'dialog');
	trigger.setAttribute('aria-controls', dialog.id);
	trigger.addEventListener('click', function (event) {
		// Preserve normal link behavior for modified clicks and unsupported browsers.
		if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button) { return; }
		try {
			dialog.showModal();
			dialog.querySelector('.ds-changelog__content').scrollTop = 0;
			event.preventDefault();
		} catch (error) { /* The original readme link remains the fallback. */ }
	});
	close.addEventListener('click', function () { dialog.close(); });
	// Native modal dialogs provide focus containment and Escape handling.
	dialog.addEventListener('close', function () { trigger.focus(); });
	dialog.addEventListener('click', function (event) {
		var rect = dialog.getBoundingClientRect();
		if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) {
			dialog.close();
		}
	});
})();
