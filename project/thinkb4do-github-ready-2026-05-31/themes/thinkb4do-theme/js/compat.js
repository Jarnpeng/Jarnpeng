/**
 * Thinkb4do Compatibility Bootstrap
 * ES5-only: adds feature/device classes before main UI runs.
 * @version 1.5.1
 */
(function (window, document) {
  'use strict';

  var docEl = document.documentElement;
  var cls = docEl.className || '';
  cls = cls.replace(/\bno-js\b/g, '').replace(/\s+/g, ' ');
  docEl.className = (cls + ' js').replace(/^\s+|\s+$/g, '');

  function hasClass(name) {
    return (' ' + (docEl.className || '') + ' ').indexOf(' ' + name + ' ') > -1;
  }

  function addClass(name) {
    if (!hasClass(name)) {
      docEl.className = ((docEl.className || '') + ' ' + name).replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '');
    }
  }

  function removeClass(name) {
    docEl.className = (' ' + (docEl.className || '') + ' ').replace(' ' + name + ' ', ' ').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '');
  }

  function toggleClass(name, force) {
    if (force) { addClass(name); }
    else { removeClass(name); }
  }

  function supportsCSS(prop, value) {
    if (!window.CSS || !window.CSS.supports) { return false; }
    try { return window.CSS.supports(prop, value); } catch (e) { return false; }
  }

  if (supportsCSS('display', 'grid')) { addClass('supports-grid'); } else { addClass('no-css-grid'); }
  if (supportsCSS('position', 'sticky') || supportsCSS('position', '-webkit-sticky')) { addClass('supports-sticky'); } else { addClass('no-sticky'); }
  if (supportsCSS('aspect-ratio', '1 / 1')) { addClass('supports-aspect-ratio'); } else { addClass('no-aspect-ratio'); }
  if ('IntersectionObserver' in window) { addClass('supports-io'); } else { addClass('no-io'); }
  if ('fetch' in window && 'Promise' in window) { addClass('supports-fetch'); } else { addClass('no-fetch'); }
  var isTouch = ('ontouchstart' in window || (navigator.maxTouchPoints && navigator.maxTouchPoints > 0));
  if (isTouch) { addClass('is-touch'); }

  // Real-device mobile detection. This still works when a phone requests “Desktop site”,
  // where CSS viewport width can become ~980px and normal max-width media queries fail.
  // v2.0.1: make the classes reversible. The previous version added tb4-actual-mobile
  // permanently, so the theme could stay in mobile layout after shrinking then expanding.
  function getViewportWidth() {
    return Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
  }

  function updateResponsiveDeviceClasses() {
    var sw = (window.screen && window.screen.width) ? window.screen.width : getViewportWidth();
    var sh = (window.screen && window.screen.height) ? window.screen.height : (window.innerHeight || 0);
    var screenMin = Math.min(sw || getViewportWidth(), sh || window.innerHeight || 0);
    var viewportW = getViewportWidth();
    var smallPhysicalScreen = !!(isTouch && screenMin && screenMin <= 820);
    var desktopViewportOnPhone = !!(smallPhysicalScreen && viewportW > (screenMin * 1.25));

    toggleClass('tb4-actual-mobile', smallPhysicalScreen);
    toggleClass('tb4-desktop-mode-on-phone', desktopViewportOnPhone);
    toggleClass('tb4-viewport-mobile', viewportW <= 900);
  }

  updateResponsiveDeviceClasses();
  if (window.addEventListener) {
    window.addEventListener('resize', updateResponsiveDeviceClasses, false);
    window.addEventListener('orientationchange', function () { window.setTimeout(updateResponsiveDeviceClasses, 120); }, false);
  }
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { addClass('prefers-reduced-motion'); }
  if (window.matchMedia && window.matchMedia('(forced-colors: active)').matches) { addClass('forced-colors-active'); }

  var ua = navigator.userAgent || '';
  if (/Android/i.test(ua)) { addClass('os-android'); }
  if (/iPhone|iPad|iPod/i.test(ua)) { addClass('os-ios'); }
  if (/Windows/i.test(ua)) { addClass('os-windows'); }
  if (/Macintosh|Mac OS X/i.test(ua) && !/iPhone|iPad|iPod/i.test(ua)) { addClass('os-macos'); }

  function isModernEnough() {
    var hasSelectors = !!document.querySelector;
    var hasClassList = !!(docEl && docEl.classList);
    var hasGrid = supportsCSS('display', 'grid');
    var hasSticky = supportsCSS('position', 'sticky') || supportsCSS('position', '-webkit-sticky');
    var hasCoreJS = ('addEventListener' in window) && ('Promise' in window) && ('fetch' in window);

    /*
     * Do not warn modern Android Chrome / Samsung Internet / Safari / Edge / Firefox.
     * v1.4.7 used window.classList, which is undefined in normal browsers and caused
     * a false “old browser” warning on mobile.
     */
    return !!(hasSelectors && hasClassList && hasGrid && hasCoreJS && hasSticky);
  }

  function addBrowserNotice() {
    if (!window.tb4Data || !tb4Data.compat || !tb4Data.compat.browserNoticeEnabled) { return; }
    if (isModernEnough()) { return; }
    if (document.querySelector && document.querySelector('.tb4-browser-warning')) { return; }

    var notice = document.createElement('div');
    notice.className = 'tb4-browser-warning';
    notice.setAttribute('role', 'status');
    notice.innerHTML = '<strong>' + (tb4Data.compat.browserNoticeTitle || 'เบราว์เซอร์ของคุณอาจเก่าเกินไป') + '</strong><span>' + (tb4Data.compat.browserNoticeBody || 'เว็บไซต์ยังแสดงเนื้อหาหลักได้ แต่บางส่วนอาจทำงานไม่สมบูรณ์') + '</span>';
    if (document.body.firstChild) { document.body.insertBefore(notice, document.body.firstChild); }
    else { document.body.appendChild(notice); }
  }

  if (document.readyState === 'loading') {
    if (document.addEventListener) { document.addEventListener('DOMContentLoaded', addBrowserNotice); }
    else { window.onload = addBrowserNotice; }
  } else {
    addBrowserNotice();
  }
})(window, document);
