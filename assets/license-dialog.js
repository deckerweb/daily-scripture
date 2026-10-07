(/**
 * Enhance local source notices with accessible dialogs.
 * @returns {void} No return value.
 */
function () {
	'use strict';

	var sequence = 0;

	// Progressive enhancement: native details/summary remains usable without JS.
	document.addEventListener('click', /**
	 * Open a source notice while preserving the originating component typography.
	 * @param {Event} event Originating DOM event.
	 * @returns {void} No return value.
	 */
	function (event) {
		var summary = event.target.closest && event.target.closest('.daily-scripture__license > summary');
		if (!summary || event.defaultPrevented || typeof HTMLDialogElement === 'undefined' ||
			typeof HTMLDialogElement.prototype.showModal !== 'function') {
			return;
		}

		var details = summary.parentElement;
		var content = details.querySelector('.daily-scripture__copyright');
		if (!content) {
			return;
		}

		// Keep builder typography when the copyright node moves outside its styled wrapper.
		var previousStyle = content.getAttribute('style');
		var contentStyle = window.getComputedStyle(content);
		var typography = {};
		['font-family', 'font-size', 'font-weight', 'font-style', 'line-height', 'letter-spacing', 'color'].forEach(/**
		 * Capture one computed typography property.
		 * @param {string} key Selected property or setting identifier.
		 * @returns {void} No return value.
		 */
		function (key) { typography[key] = contentStyle.getPropertyValue(key); });
		var dialog = document.createElement('dialog');
		var heading = document.createElement('h2');
		var close = document.createElement('button');
		do {
			sequence += 1;
			heading.id = 'daily-scripture-license-title-' + sequence;
		} while (document.getElementById(heading.id));
		heading.textContent = summary.textContent;
		dialog.className = 'daily-scripture-license-dialog';
		var palette = window.getComputedStyle(details.closest('.daily-scripture'));
		['--ds-bg', '--ds-text', '--ds-accent', '--ds-border', '--ds-meta-size'].forEach(/**
		 * Copy one component palette variable to the dialog.
		 * @param {string} key Selected property or setting identifier.
		 * @returns {void} No return value.
		 */
		function (key) {
			dialog.style.setProperty(key, palette.getPropertyValue(key));
		});
		dialog.setAttribute('aria-labelledby', heading.id);
		close.type = 'button';
		close.className = 'daily-scripture-license-dialog__close';
		close.textContent = details.getAttribute('data-close-label');
		close.autofocus = true;
		dialog.appendChild(heading);
		dialog.appendChild(close);
		Object.keys(typography).forEach(/**
		 * Apply captured typography to the notice content.
		 * @param {string} key Selected property or setting identifier.
		 * @returns {void} No return value.
		 */
		function (key) { content.style.setProperty(key, typography[key], 'important'); });
		dialog.appendChild(content);
		document.body.appendChild(dialog);

		/**
		 * Restore notice content to its original inline container.
		 * @returns {void} No return value.
		 */
		function restore() {
			if (previousStyle === null) { content.removeAttribute('style'); } else { content.setAttribute('style', previousStyle); }
			details.appendChild(content);
			dialog.remove();
		}

		close.addEventListener('click', /**
		 * Close the license dialog.
		 * @returns {void} No return value.
		 */
		function () { dialog.close(); });
		// Native dialog provides Escape handling, focus containment and inert backdrop.
		dialog.addEventListener('close', /**
		 * Restore content and focus and remove the temporary dialog.
		 * @returns {void} No return value.
		 */
		function () {
			restore();
			if (summary.isConnected) {
				summary.focus();
			}
		}, { once: true });
		dialog.addEventListener('click', /**
		 * Close the license dialog when its backdrop is clicked.
		 * @param {MouseEvent} click Dialog click event.
		 * @returns {void} No return value.
		 */
		function (click) {
			var rect = dialog.getBoundingClientRect();
			if (click.target === dialog && (click.clientX < rect.left || click.clientX > rect.right ||
				click.clientY < rect.top || click.clientY > rect.bottom)) {
				dialog.close();
			}
		});

		try {
			dialog.showModal();
			details.open = false;
			event.preventDefault();
		} catch (error) {
			// If enhancement fails, let the original disclosure open normally.
			restore();
		}
	});
})();
