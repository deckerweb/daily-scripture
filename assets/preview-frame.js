(function () {
 'use strict';
 function luminance(color) {
  var values = color.match(/[\d.]+/g);
  if (!values || values.length < 3) { return null; }
  return values.slice(0, 3).map(function (value) { var n = Number(value) / 255; return n <= .04045 ? n / 12.92 : Math.pow((n + .055) / 1.055, 2.4); }).reduce(function (sum, n, i) { return sum + n * [.2126, .7152, .0722][i]; }, 0);
 }
 function report() {
  var low = false;
  document.querySelectorAll('.daily-scripture__source').forEach(function (source) {
   var bg = luminance(getComputedStyle(source).backgroundColor);
   source.querySelectorAll('.daily-scripture__title,.daily-scripture__text,.daily-scripture__reference a,.daily-scripture__meta a,.daily-scripture__date').forEach(function (node) {
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
