/**
 * Thinkb4do Theme v2.0.9 — Mobile Header Tight Clamp
 * Single lightweight controller for mobile menu + header popups.
 */
(function (window, document) {
  'use strict';

  if (window.tb4MobileHeaderTightV209Booted) { return; }
  window.tb4MobileHeaderTightV209Booted = true;

  var MOBILE_MAX = 1024;
  var root = document.documentElement;
  var body = null;
  var lastToggle = 0;
  var resizeTimer = 0;
  var originalAlignHeaderPopup = null;
  var originalAlignMobileMenu = null;
  var oldBodyClasses = [
    'tb4-menu-open',
    'tb4-mobile-menu-open',
    'tb4-mobile-menu-tight-open',
    'tb4-mobile-menu-hard-open',
    'tb4-mobile-menu-head-locked',
    'tb4-mobile-menu-v206-open',
    'tb4-mobile-menu-v207-open',
    'tb4-menu-v207-open',
    'tb4-v208-menu-open'
  ];
  var oldOverlayClasses = [
    'tb4-v208-menu',
    'tb4-v208-open',
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
  function closest(el, selector) { return el && el.closest ? el.closest(selector) : null; }
  function isVisible(el) {
    if (!el) { return false; }
    if (el.hidden || el.hasAttribute('hidden') || el.getAttribute('aria-hidden') === 'true') { return false; }
    if (window.getComputedStyle) {
      var cs = window.getComputedStyle(el);
      return cs.display !== 'none' && cs.visibility !== 'hidden' && cs.opacity !== '0';
    }
    return el.style.display !== 'none';
  }
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
    body.style.removeProperty('overflow');
  }

  function cleanOldOverlayClasses(overlay) {
    oldOverlayClasses.forEach(function (cls) { remove(overlay, cls); });
  }

  function getRect(el) {
    if (!el || !el.getBoundingClientRect) { return null; }
    try { return el.getBoundingClientRect(); }
    catch (err) { return null; }
  }

  function headerTightTop(trigger) {
    var header = id('site-header');
    var inner = header ? header.querySelector('.header-inner') : null;
    var hRect = getRect(header);
    var iRect = getRect(inner);
    var tRect = getRect(trigger);
    var top = 72;
    var candidate = [];
    var height = vh();

    /* Prefer the visible white header bottom. Do not add the green dev-notice height. */
    if (hRect && hRect.bottom > 0) { candidate.push(Math.round(hRect.bottom)); }
    if (iRect && iRect.bottom > 0) { candidate.push(Math.round(iRect.bottom + 6)); }
    if (tRect && tRect.bottom > 0) { candidate.push(Math.round(tRect.bottom + 8)); }

    if (candidate.length) {
      top = candidate.reduce(function (min, value) {
        return value > 0 && value < min ? value : min;
      }, 99999);
      if (top === 99999) { top = candidate[0]; }
    } else {
      top = (parseInt(window.getComputedStyle(root).getPropertyValue('--tb4-sticky-top'), 10) || 0) +
            (parseInt(window.getComputedStyle(root).getPropertyValue('--tb4-header-real-h'), 10) || 72);
    }

    /* Safety clamp: never let popup start too low on small screens. */
    top = Math.max(0, Math.min(top, Math.max(72, height - 180)));
    return top;
  }

  function syncVars(trigger) {
    var top = headerTightTop(trigger || id('mobileMenuToggle') || id('userMenuTrigger') || id('tb4NotificationTrigger'));
    root.style.setProperty('--tb4-v209-vh', vh() + 'px');
    root.style.setProperty('--tb4-v209-header-tight-top', top + 'px');
    root.style.setProperty('--tb4-v209-popup-top', top + 'px');
    /* Neutralize old variables if cached CSS survives. */
    root.style.setProperty('--tb4-v208-header-bottom', top + 'px');
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
      add(overlay, 'tb4-v209-menu');
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
    remove(body, 'tb4-v209-menu-open');
    if (overlay) {
      remove(overlay, 'is-open');
      remove(overlay, 'tb4-v209-open');
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

    top = syncVars(toggle);
    cleanOldBodyLocks();
    add(body, 'tb4-v209-menu-open');

    overlay.removeAttribute('hidden');
    overlay.setAttribute('aria-hidden', 'false');
    add(overlay, 'is-open');
    add(overlay, 'tb4-v209-open');
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
    setImportant(overlay, 'z-index', '2147483000');
  }

  function isMenuOpen() {
    var overlay = id('mobileMenuOverlay');
    return !!(overlay && (has(overlay, 'tb4-v209-open') || has(overlay, 'is-open')) && !overlay.hasAttribute('hidden'));
  }

  function alignMenu(overlay) {
    var top;
    overlay = overlay || getOverlay();
    if (!overlay) { return; }
    if (!isMobile()) { closeMenu(); return; }
    top = syncVars(id('mobileMenuToggle'));
    add(overlay, 'tb4-v209-menu');
    if (isMenuOpen()) {
      setImportant(overlay, 'top', top + 'px');
      setImportant(overlay, 'inset', top + 'px 0 0 0');
      setImportant(overlay, 'height', Math.max(180, vh() - top) + 'px');
      setImportant(overlay, 'max-height', Math.max(180, vh() - top) + 'px');
    }
  }

  function alignPopup(trigger, panel, type) {
    var top;
    if (!panel) { return; }
    if (!isMobile() || (type !== 'user' && type !== 'notification')) {
      remove(panel, 'tb4-v209-popup');
      if (originalAlignHeaderPopup && originalAlignHeaderPopup !== alignPopup) {
        return originalAlignHeaderPopup(trigger, panel, type);
      }
      return;
    }
    if (!isVisible(panel)) {
      remove(panel, 'tb4-v209-popup');
      return;
    }

    top = syncVars(trigger);
    add(panel, 'tb4-v209-popup');
    setImportant(panel, 'display', 'block');
    setImportant(panel, 'position', 'fixed');
    setImportant(panel, 'top', top + 'px');
    setImportant(panel, 'left', '12px');
    setImportant(panel, 'right', '12px');
    setImportant(panel, 'width', 'auto');
    setImportant(panel, 'min-width', '0');
    setImportant(panel, 'max-width', 'calc(100vw - 24px)');
    setImportant(panel, 'max-height', Math.max(160, vh() - top - 10) + 'px');
    setImportant(panel, 'overflow-x', 'hidden');
    setImportant(panel, 'overflow-y', 'auto');
    setImportant(panel, '-webkit-overflow-scrolling', 'touch');
    setImportant(panel, 'margin', '0');
    setImportant(panel, 'transform', 'none');
    setImportant(panel, 'translate', 'none');
    setImportant(panel, 'z-index', '2147483001');
  }

  function alignVisiblePopups() {
    alignPopup(id('tb4NotificationTrigger'), id('tb4NotificationPanel'), 'notification');
    alignPopup(id('userMenuTrigger'), id('userMenuDropdown'), 'user');
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
    }, 60);
  }

  function handleToggle(event) {
    var toggle = closest(event.target, '#mobileMenuToggle');
    var now = Date.now ? Date.now() : new Date().getTime();
    if (!toggle) { return; }
    event.preventDefault();
    event.stopPropagation();
    if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
    if (now - lastToggle < 220) { return; }
    lastToggle = now;
    if (isMenuOpen()) { closeMenu(); }
    else { openMenu(); }
  }

  function handleHeaderPopupClick(event) {
    if (closest(event.target, '#tb4NotificationTrigger, #userMenuTrigger')) {
      window.setTimeout(alignVisiblePopups, 0);
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

    if (!window.TB4) { window.TB4 = {}; }
    originalAlignHeaderPopup = window.TB4.alignHeaderPopup;
    originalAlignMobileMenu = window.TB4.alignMobileMenu;
    window.TB4.alignHeaderPopup = alignPopup;
    window.TB4.alignMobileMenu = alignMenu;

    document.addEventListener('click', handleToggle, true);
    document.addEventListener('click', handleHeaderPopupClick, true);
    document.addEventListener('click', handleDocumentClick, false);
    window.addEventListener('resize', scheduleRefresh, false);
    window.addEventListener('orientationchange', scheduleRefresh, false);
    window.addEventListener('scroll', alignVisiblePopups, { passive: true });
    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', scheduleRefresh, false);
      window.visualViewport.addEventListener('scroll', alignVisiblePopups, false);
    }

    if (!isMobile()) { closeMenu(); }
  });

  window.tb4MobileHeaderTightV209 = {
    closeMenu: closeMenu,
    openMenu: openMenu,
    refresh: scheduleRefresh,
    alignMenu: alignMenu,
    alignPopup: alignPopup,
    headerTightTop: headerTightTop
  };
}(window, document));
