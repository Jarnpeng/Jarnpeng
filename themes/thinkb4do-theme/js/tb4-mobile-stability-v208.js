/**
 * Thinkb4do Theme v2.0.8 — Mobile Stability Guard
 * Single lightweight controller replacing stacked mobile-menu patches.
 */
(function (window, document) {
  'use strict';

  if (window.tb4MobileStabilityV208Booted) { return; }
  window.tb4MobileStabilityV208Booted = true;

  var MOBILE_MAX = 1024;
  var root = document.documentElement;
  var body = null;
  var lastToggle = 0;
  var resizeTimer = 0;
  var oldBodyClasses = [
    'tb4-menu-open',
    'tb4-mobile-menu-open',
    'tb4-mobile-menu-tight-open',
    'tb4-mobile-menu-hard-open',
    'tb4-mobile-menu-head-locked',
    'tb4-mobile-menu-v206-open',
    'tb4-mobile-menu-v207-open',
    'tb4-menu-v207-open'
  ];
  var oldOverlayClasses = [
    'tb4-mobile-menu-ported',
    'tb4-mobile-menu-v206',
    'tb4-mobile-menu-v207',
    'tb4-menu-v206-open',
    'tb4-menu-v207-open'
  ];

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  function id(name) { return document.getElementById(name); }
  function vw() { return Math.max(root.clientWidth || 0, window.innerWidth || 0); }
  function vh() {
    if (window.visualViewport && window.visualViewport.height) {
      return Math.round(window.visualViewport.height);
    }
    return Math.max(root.clientHeight || 0, window.innerHeight || 0);
  }
  function isMobile() { return vw() <= MOBILE_MAX; }
  function add(el, cls) { if (el && el.classList) { el.classList.add(cls); } }
  function remove(el, cls) { if (el && el.classList) { el.classList.remove(cls); } }
  function has(el, cls) { return !!(el && el.classList && el.classList.contains(cls)); }
  function each(list, fn) { Array.prototype.forEach.call(list || [], fn); }
  function closest(el, selector) { return el && el.closest ? el.closest(selector) : null; }

  function setImportant(el, prop, value) {
    if (el && el.style) { el.style.setProperty(prop, value, 'important'); }
  }

  function removeInline(el, props) {
    if (!el || !el.style) { return; }
    props.forEach(function (prop) { el.style.removeProperty(prop); });
  }

  function cleanOldBodyLocks() {
    if (!body) { return; }
    oldBodyClasses.forEach(function (cls) { remove(body, cls); });
    body.style.removeProperty('touch-action');
  }

  function cleanOldOverlayClasses(overlay) {
    oldOverlayClasses.forEach(function (cls) { remove(overlay, cls); });
  }

  function headerBottom() {
    var header = id('site-header');
    var rect = header && header.getBoundingClientRect ? header.getBoundingClientRect() : null;
    var top = 72;
    var maxTop;

    if (rect && rect.bottom > 0) {
      top = Math.round(rect.bottom);
    } else {
      top = (parseInt(getComputedStyle(root).getPropertyValue('--tb4-sticky-top'), 10) || 0) +
            (parseInt(getComputedStyle(root).getPropertyValue('--tb4-header-real-h'), 10) || 72);
    }

    maxTop = Math.max(72, vh() - 160);
    return Math.max(0, Math.min(top, maxTop));
  }

  function syncVars() {
    var top = headerBottom();
    root.style.setProperty('--tb4-v208-vh', vh() + 'px');
    root.style.setProperty('--tb4-v208-header-bottom', top + 'px');
    /* Keep older CSS variables harmless if old cached CSS exists. */
    root.style.setProperty('--tb4-menu-v207-top', top + 'px');
    root.style.setProperty('--tb4-menu-v206-top', top + 'px');
    root.style.setProperty('--tb4-menu-v205-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-hard-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-tight-top', top + 'px');
    root.style.setProperty('--tb4-popup-anchor-top', top + 'px');
    root.style.setProperty('--tb4-header-popup-lock-top', top + 'px');
    root.style.setProperty('--tb4-header-popup-v207-top', top + 'px');
    return top;
  }

  function getOverlay() {
    var overlay = id('mobileMenuOverlay');
    if (overlay && document.body && overlay.parentNode !== document.body) {
      document.body.appendChild(overlay);
    }
    if (overlay) {
      cleanOldOverlayClasses(overlay);
      add(overlay, 'tb4-v208-menu');
    }
    return overlay;
  }

  function setIcon(open) {
    var toggle = id('mobileMenuToggle');
    var spans = toggle ? toggle.querySelectorAll('span') : [];
    if (!spans || spans.length < 3) { return; }
    spans[0].style.transform = open ? 'rotate(45deg) translate(5px, 5px)' : '';
    spans[1].style.opacity = open ? '0' : '';
    spans[2].style.transform = open ? 'rotate(-45deg) translate(5px, -5px)' : '';
  }

  function resetOverlayInline(overlay) {
    removeInline(overlay, [
      'display','position','top','left','right','bottom','inset','width','max-width','min-width',
      'height','max-height','margin','padding','box-sizing','overflow','overflow-x','overflow-y',
      'overscroll-behavior','background','border-top','border-radius','box-shadow','transform','translate',
      'opacity','visibility','pointer-events','z-index','contain','isolation','touch-action'
    ]);
  }

  function closeMenu() {
    var overlay = getOverlay();
    var toggle = id('mobileMenuToggle');
    cleanOldBodyLocks();
    remove(body, 'tb4-v208-menu-open');
    if (overlay) {
      remove(overlay, 'is-open');
      remove(overlay, 'tb4-v208-open');
      cleanOldOverlayClasses(overlay);
      overlay.setAttribute('hidden', 'hidden');
      overlay.setAttribute('aria-hidden', 'true');
      resetOverlayInline(overlay);
      setImportant(overlay, 'display', 'none');
    }
    if (toggle) { toggle.setAttribute('aria-expanded', 'false'); }
    setIcon(false);
  }

  function openMenu() {
    var overlay = getOverlay();
    var toggle = id('mobileMenuToggle');
    var top;
    if (!overlay || !toggle) { return; }
    if (!isMobile()) { closeMenu(); return; }

    top = syncVars();
    cleanOldBodyLocks();
    add(body, 'tb4-v208-menu-open');

    overlay.removeAttribute('hidden');
    overlay.setAttribute('aria-hidden', 'false');
    add(overlay, 'is-open');
    add(overlay, 'tb4-v208-open');
    toggle.setAttribute('aria-expanded', 'true');
    setIcon(true);

    setImportant(overlay, 'display', 'block');
    setImportant(overlay, 'position', 'fixed');
    setImportant(overlay, 'top', top + 'px');
    setImportant(overlay, 'left', '0');
    setImportant(overlay, 'right', '0');
    setImportant(overlay, 'bottom', '0');
    setImportant(overlay, 'inset', top + 'px 0 0 0');
    setImportant(overlay, 'width', '100vw');
    setImportant(overlay, 'max-width', '100vw');
    setImportant(overlay, 'height', Math.max(180, vh() - top) + 'px');
    setImportant(overlay, 'max-height', Math.max(180, vh() - top) + 'px');
    setImportant(overlay, 'overflow-x', 'hidden');
    setImportant(overlay, 'overflow-y', 'auto');
    setImportant(overlay, '-webkit-overflow-scrolling', 'touch');
    setImportant(overlay, 'overscroll-behavior', 'contain');
    setImportant(overlay, 'transform', 'none');
    setImportant(overlay, 'z-index', '99980');
  }

  function isMenuOpen() {
    var overlay = id('mobileMenuOverlay');
    return !!(overlay && has(overlay, 'tb4-v208-open'));
  }

  function alignMenu(overlay) {
    var top;
    overlay = overlay || getOverlay();
    if (!overlay) { return; }
    if (!isMobile()) { closeMenu(); return; }
    top = syncVars();
    if (isMenuOpen()) {
      setImportant(overlay, 'top', top + 'px');
      setImportant(overlay, 'inset', top + 'px 0 0 0');
      setImportant(overlay, 'height', Math.max(180, vh() - top) + 'px');
      setImportant(overlay, 'max-height', Math.max(180, vh() - top) + 'px');
    }
  }

  function alignPopup(trigger, panel) {
    var top;
    if (!panel) { return; }
    if (!isMobile()) {
      remove(panel, 'tb4-v208-popup');
      return;
    }
    if (panel.hasAttribute('hidden') || panel.getAttribute('aria-hidden') === 'true' || panel.style.display === 'none') {
      remove(panel, 'tb4-v208-popup');
      return;
    }
    top = syncVars();
    add(panel, 'tb4-v208-popup');
    setImportant(panel, 'display', 'block');
    setImportant(panel, 'position', 'fixed');
    setImportant(panel, 'top', top + 'px');
    setImportant(panel, 'left', '12px');
    setImportant(panel, 'right', '12px');
    setImportant(panel, 'width', 'auto');
    setImportant(panel, 'min-width', '0');
    setImportant(panel, 'max-width', 'calc(100vw - 24px)');
    setImportant(panel, 'max-height', Math.max(160, vh() - top - 12) + 'px');
    setImportant(panel, 'overflow-x', 'hidden');
    setImportant(panel, 'overflow-y', 'auto');
    setImportant(panel, 'transform', 'none');
    setImportant(panel, 'z-index', '99985');
  }

  function alignVisiblePopups() {
    alignPopup(id('tb4NotificationTrigger'), id('tb4NotificationPanel'));
    alignPopup(id('userMenuTrigger'), id('userMenuDropdown'));
  }

  function scheduleRefresh() {
    if (resizeTimer) { window.clearTimeout(resizeTimer); }
    resizeTimer = window.setTimeout(function () {
      syncVars();
      if (!isMobile()) {
        closeMenu();
      } else if (isMenuOpen()) {
        alignMenu();
      }
      alignVisiblePopups();
    }, 80);
  }

  function handleToggle(event) {
    var toggle = closest(event.target, '#mobileMenuToggle');
    var now = Date.now ? Date.now() : new Date().getTime();
    if (!toggle) { return; }
    event.preventDefault();
    event.stopPropagation();
    if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
    if (now - lastToggle < 250) { return; }
    lastToggle = now;
    if (isMenuOpen()) { closeMenu(); }
    else { openMenu(); }
  }

  function handleHeaderPopupClick(event) {
    if (closest(event.target, '#tb4NotificationTrigger, #userMenuTrigger')) {
      window.setTimeout(alignVisiblePopups, 30);
      window.setTimeout(alignVisiblePopups, 120);
    }
  }

  function handleDocumentClick(event) {
    if (!isMenuOpen()) { return; }
    if (closest(event.target, '#mobileMenuOverlay, #mobileMenuToggle')) { return; }
    closeMenu();
  }

  ready(function () {
    body = document.body;
    syncVars();
    getOverlay();
    cleanOldBodyLocks();

    if (window.TB4) {
      window.TB4.alignMobileMenu = alignMenu;
      window.TB4.alignHeaderPopup = alignPopup;
    }

    document.addEventListener('click', handleToggle, true);
    document.addEventListener('click', handleHeaderPopupClick, true);
    document.addEventListener('click', handleDocumentClick, false);
    window.addEventListener('resize', scheduleRefresh, false);
    window.addEventListener('orientationchange', scheduleRefresh, false);
    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', scheduleRefresh, false);
    }

    if (!isMobile()) { closeMenu(); }
  });

  window.tb4MobileStabilityV208 = {
    closeMenu: closeMenu,
    openMenu: openMenu,
    refresh: scheduleRefresh,
    alignMenu: alignMenu,
    alignPopup: alignPopup
  };
}(window, document));
