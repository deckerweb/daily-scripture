(/**
 * Enhance personal reading order without changing saved preferences implicitly.
 * @returns {void} No return value.
 */
function () {
 'use strict';
 var list = document.querySelector('#daily_scripture_widget .ds-dashboard-readings');
 if (!list) { return; }
 /**
  * Match submitted order to the visual, keyboard-operable reading list.
  * @returns {void} No return value.
  */
 function sync() {
  var rows = Array.from(list.querySelectorAll('.ds-dashboard-reading'));
  rows.forEach(/**
   * Update position and disable moves beyond list boundaries.
   * @param {HTMLElement} row Reading controls.
   * @param {number} index Zero-based visual position.
   * @returns {void} No return value.
   */
  function (row, index) {
   row.querySelector('input[type=number]').value = index + 1;
   row.querySelector('.ds-reading-up').disabled = index === 0;
   row.querySelector('.ds-reading-down').disabled = index === rows.length - 1;
  });
 }
 list.querySelectorAll('.ds-dashboard-reading').forEach(/**
  * Provide progressive enhancement while retaining checkbox and numeric controls.
  * @param {HTMLElement} row Reading controls.
  * @returns {void} No return value.
  */
 function (row) {
  row.querySelector('.ds-reading-order').hidden = true;
  row.querySelectorAll('button').forEach(/**
   * Move a reading and keep focus on the action used.
   * @param {HTMLButtonElement} button Move action.
   * @returns {void} No return value.
   */
  function (button) {
   button.hidden = false;
   button.addEventListener('click', /**
    * Change form order only; saving remains an explicit user action.
    * @returns {void} No return value.
    */
   function () {
    var up = button.classList.contains('ds-reading-up');
    var sibling = up ? row.previousElementSibling : row.nextElementSibling;
    if (!sibling || !sibling.classList.contains('ds-dashboard-reading')) { return; }
    if (up) { sibling.before(row); } else { sibling.after(row); }
    sync();
    var alternate = row.querySelector(up ? '.ds-reading-down' : '.ds-reading-up');
    (button.disabled ? alternate : button).focus();
    list.querySelector('.ds-reading-status').textContent = row.querySelector('label').textContent.trim() + ': ' + row.querySelector('input[type=number]').value;
   });
  });
 });
 sync();
})();
