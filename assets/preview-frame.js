(/**
 * Report preview height and readability to the parent studio.
 * @returns {void} No return value.
 */
function () {
 'use strict';
 /**
  * Compute relative luminance from a rendered RGB color.
  * @param {string} color Computed CSS RGB color.
  * @returns {number} Value consumed by the calling editor or request handler.
  */
 function luminance(color) {
  var values = color.match(/[\d.]+/g);
  if (!values || values.length < 3) { return null; }
  return values.slice(0, 3).map(/**
   * Convert one sRGB channel into linear light.
   * @param {string} value Current control or source value.
   * @returns {number} Value consumed by the calling editor or request handler.
   */
  function (value) { var n = Number(value) / 255; return n <= .04045 ? n / 12.92 : Math.pow((n + .055) / 1.055, 2.4); }).reduce(/**
   * Combine weighted linear channels into luminance.
   * @param {number} sum Accumulated weighted luminance.
   * @param {number} n Linear-light channel value.
   * @param {number} i Channel index.
   * @returns {number} Value consumed by the calling editor or request handler.
   */
  function (sum, n, i) { return sum + n * [.2126, .7152, .0722][i]; }, 0);
 }
 /**
  * Inspect the preview and report its measured dimensions and contrast.
  * @returns {void} No return value.
  */
 function report() {
  var low = false;
  document.querySelectorAll('.daily-scripture__source').forEach(/**
   * Inspect one rendered source section.
   * @param {HTMLElement} source Rendered source section.
   * @returns {void} No return value.
   */
  function (source) {
   var bg = luminance(getComputedStyle(source).backgroundColor);
   source.querySelectorAll('.daily-scripture__title,.daily-scripture__text,.daily-scripture__reference a,.daily-scripture__meta a,.daily-scripture__date').forEach(/**
    * Collect contrast evidence for a rendered text element.
    * @param {HTMLElement} node Rendered text element.
    * @returns {void} No return value.
    */
   function (node) {
    var style = getComputedStyle(node);
    var color = luminance(style.color);
    if (bg !== null && color !== null) {
     var ratio = (Math.max(bg, color) + .05) / (Math.min(bg, color) + .05);
     var size = parseFloat(style.fontSize);
     var large = size >= 24 || (size >= 18.66 && parseInt(style.fontWeight, 10) >= 700);
     if (ratio < (large ? 3 : 4.5)) { low = true; }
    }
   });
  });
  parent.postMessage({ type: 'daily-scripture-preview', height: Math.ceil(document.querySelector('main').getBoundingClientRect().height + 24), lowContrast: low }, '*');
 }
 window.addEventListener('load', report);
 window.addEventListener('resize', report);
 if (window.ResizeObserver) { new ResizeObserver(report).observe(document.querySelector('main')); }
})();
