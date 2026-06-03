/**
 * Thinkb4do Theme v2.1.0 — White Header Clamp
 * Force mobile menu / notification / user dropdown to start at #site-header bottom.
 */
(function (window, document) {
  'use strict';

  if (window.tb4WhiteHeaderClampV210Booted) { return; }
  window.tb4WhiteHeaderClampV210Booted = true;

  var MOBILE_MAX = 1024;
  var root = document.documentElement;
  var body = null;
  var refreshTimer = 0;

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
    if (window.visualViewport && window.visualViewport.height) { return Math.round(window.visualViewport.height); }
    return Math.max(root.clientHeight || 0, window.innerHeight || 0);
  }
  function isMobile() { return vw() <= MOBILE_MAX; }
  function visible(el) {
    if (!el || el.hidden || el.hasAttribute('hidden') || el.getAttribute('aria-hidden') === 'true') { return false; }
    var cs = window.getComputedStyle ? window.getComputedStyle(el) : null;
    return !cs || (cs.display !== 'none' && cs.visibility !== 'hidden' && cs.opacity !== '0');
  }
  function setImportant(el, prop, value) {
    if (el && el.style) { el.style.setProperty(prop, value, 'important'); }
  }
  function add(el, cls) { if (el && el.classList) { el.classList.add(cls); } }
  function remove(el, cls) { if (el && el.classList) { el.classList.remove(cls); } }
  function closest(el, selector) { return el && el.closest ? el.closest(selector) : null; }

  function whiteHeaderBottom(trigger) {
    var header = id('site-header');
    var rect = header && header.getBoundingClientRect ? header.getBoundingClientRect() : null;
    var top = 0;
    var maxTop = Math.max(72, vh() - 160);

    /* Only #site-header bottom. Do not add .tb4-dev-notice height. */
    if (rect && rect.bottom > 0) { top = Math.round(rect.bottom); }

    if (!top && trigger && trigger.getBoundingClientRect) {
      rect = trigger.getBoundingClientRect();
      if (rect && rect.bottom > 0) { top = Math.round(rect.bottom + 8); }
    }

    if (!top) {
      top = (parseInt(getComputedStyle(root).getPropertyValue('--tb4-sticky-top'), 10) || 0) +
            (parseInt(getComputedStyle(root).getPropertyValue('--tb4-header-real-h'), 10) ||
             parseInt(getComputedStyle(root).getPropertyValue('--tb4-v172-header-h'), 10) || 72);
    }

    top = Math.max(0, Math.min(top, maxTop));
    root.style.setProperty('--tb4-white-header-bottom', top + 'px');
    root.style.setProperty('--tb4-popup-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-top', top + 'px');
    root.style.setProperty('--tb4-header-popup-lock-top', top + 'px');
    root.style.setProperty('--tb4-popup-anchor-top', top + 'px');
    return top;
  }

  function clampPanel(trigger, panel, type) {
    var top;
    if (!panel || !visible(panel)) { return; }
    top = whiteHeaderBottom(trigger);
    add(panel, 'tb4-v210-popup');

    setImportant(panel, 'display', 'block');
    setImportant(panel, 'position', 'fixed');
    setImportant(panel, 'top', top + 'px');
    setImportant(panel, 'margin-top', '0px');
    setImportant(panel, 'transform', 'none');
    setImportant(panel, 'translate', 'none');
    setImportant(panel, 'visibility', 'visible');
    setImportant(panel, 'opacity', '1');
    setImportant(panel, 'pointer-events', 'auto');
    setImportant(panel, 'z-index', '2147483001');
    setImportant(panel, 'overflow-x', 'hidden');
    setImportant(panel, 'overflow-y', 'auto');
    setImportant(panel, '-webkit-overflow-scrolling', 'touch');
    setImportant(panel, 'max-height', Math.max(160, vh() - top - 8) + 'px');

    if (isMobile()) {
      setImportant(panel, 'left', '12px');
      setImportant(panel, 'right', '12px');
      setImportant(panel, 'width', 'auto');
      setImportant(panel, 'min-width', '0');
      setImportant(panel, 'max-width', 'calc(100vw - 24px)');
      setImportant(panel, 'border-radius', '0 0 22px 22px');
    }
  }

  function clampMenu() {
    var overlay = id('mobileMenuOverlay');
    var toggle = id('mobileMenuToggle');
    var top;
    if (!overlay) { return; }
    if (overlay.parentNode !== document.body) { document.body.appendChild(overlay); }
    if (!isMobile()) {
      remove(overlay, 'tb4-v210-open');
      return;
    }
    if (!visible(overlay) && !overlay.classList.contains('is-open')) { return; }

    top = whiteHeaderBottom(toggle);
    add(overlay, 'tb4-v210-open');
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
    setImportant(overlay, 'translate', 'none');
    setImportant(overlay, 'z-index', '2147483000');
  }

  function clampAll() {
    whiteHeaderBottom(id('mobileMenuToggle') || id('userMenuTrigger') || id('tb4NotificationTrigger'));
    clampPanel(id('tb4NotificationTrigger'), id('tb4NotificationPanel'), 'notification');
    clampPanel(id('userMenuTrigger'), id('userMenuDropdown'), 'user');
    clampMenu();
  }

  function schedule() {
    if (refreshTimer) { window.clearTimeout(refreshTimer); }
    refreshTimer = window.setTimeout(clampAll, 30);
  }

  function repeatedClamp() {
    clampAll();
    window.setTimeout(clampAll, 0);
    window.setTimeout(clampAll, 40);
    window.setTimeout(clampAll, 120);
    window.setTimeout(clampAll, 260);
  }

  function overrideTB4() {
    if (!window.TB4) { window.TB4 = {}; }
    var oldAlign = window.TB4.alignHeaderPopup;
    window.TB4.alignHeaderPopup = function (trigger, panel, type) {
      if (type === 'user' || type === 'notification') {
        clampPanel(trigger, panel, type);
        return;
      }
      if (typeof oldAlign === 'function') { oldAlign(trigger, panel, type); }
    };
    window.TB4.alignMobileMenu = function (overlay) {
      clampMenu(overlay);
    };
    window.TB4.getWhiteHeaderBottom = whiteHeaderBottom;
  }

  ready(function () {
    body = document.body;
    overrideTB4();
    whiteHeaderBottom();

    document.addEventListener('click', function (event) {
      if (closest(event.target, '#tb4NotificationTrigger, #userMenuTrigger, #mobileMenuToggle')) {
        repeatedClamp();
      }
    }, true);

    ['tb4NotificationPanel', 'userMenuDropdown', 'mobileMenuOverlay'].forEach(function (name) {
      var el = id(name);
      if (!el || !window.MutationObserver) { return; }
      new MutationObserver(schedule).observe(el, {
        attributes: true,
        attributeFilter: ['class', 'style', 'hidden', 'aria-hidden']
      });
    });

    window.addEventListener('resize', schedule, { passive: true });
    window.addEventListener('orientationchange', schedule, { passive: true });
    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', schedule, { passive: true });
    }
  });

  window.tb4WhiteHeaderClampV210 = {
    refresh: repeatedClamp,
    whiteHeaderBottom: whiteHeaderBottom
  };
}(window, document));
