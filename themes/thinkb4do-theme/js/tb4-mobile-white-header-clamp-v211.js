/**
 * Thinkb4do Theme v2.1.1 — White Header Dropdown Restore
 * Restores hamburger dropdown visibility and keeps all header popups pinned to the
 * bottom of the white #site-header, not the green development notice.
 */
(function (window, document) {
  'use strict';

  if (window.tb4WhiteHeaderClampV211Booted) { return; }
  window.tb4WhiteHeaderClampV211Booted = true;

  var MOBILE_MAX = 1024;
  var root = document.documentElement;
  var refreshTimer = 0;

  function ready(fn) {
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', fn, { once: true }); }
    else { fn(); }
  }

  function id(name) { return document.getElementById(name); }
  function vw() { return Math.max(root.clientWidth || 0, window.innerWidth || 0); }
  function vh() {
    if (window.visualViewport && window.visualViewport.height) { return Math.round(window.visualViewport.height); }
    return Math.max(root.clientHeight || 0, window.innerHeight || 0);
  }
  function isMobile() { return vw() <= MOBILE_MAX; }
  function css(el, prop, value) { if (el && el.style) { el.style.setProperty(prop, value, 'important'); } }
  function add(el, cls) { if (el && el.classList) { el.classList.add(cls); } }
  function remove(el, cls) { if (el && el.classList) { el.classList.remove(cls); } }
  function closest(el, selector) { return el && el.closest ? el.closest(selector) : null; }
  function isOpen(el) {
    if (!el) { return false; }
    return el.classList.contains('is-open') ||
      el.classList.contains('tb4-v211-open') ||
      el.getAttribute('aria-hidden') === 'false' ||
      (!el.hidden && !el.hasAttribute('hidden')) ||
      (el.style && el.style.display === 'block');
  }

  function whiteHeaderBottom(trigger) {
    var header = id('site-header');
    var inner = header && header.querySelector ? (header.querySelector('.header-inner') || header.querySelector('.container')) : null;
    var rect = null;
    var top = 0;
    var fallbackH = 72;
    var maxTop = Math.max(64, vh() - 170);

    /* Use the white header row only. Never add the green notice height. */
    if (inner && inner.getBoundingClientRect) {
      rect = inner.getBoundingClientRect();
      if (rect && rect.bottom > 0 && rect.height > 24) { top = Math.round(rect.bottom); }
    }
    if (!top && header && header.getBoundingClientRect) {
      rect = header.getBoundingClientRect();
      if (rect && rect.bottom > 0 && rect.height > 24) { top = Math.round(rect.bottom); }
    }
    if (!top && trigger && trigger.getBoundingClientRect) {
      rect = trigger.getBoundingClientRect();
      if (rect && rect.bottom > 0) { top = Math.round(rect.bottom + 8); }
    }
    if (!top) {
      fallbackH = parseInt(window.getComputedStyle(root).getPropertyValue('--tb4-header-real-h'), 10) ||
                  parseInt(window.getComputedStyle(root).getPropertyValue('--tb4-v172-header-h'), 10) || 72;
      top = (parseInt(window.getComputedStyle(root).getPropertyValue('--tb4-sticky-top'), 10) || 0) + fallbackH;
    }

    top = Math.max(0, Math.min(top, maxTop));
    root.style.setProperty('--tb4-white-header-bottom', top + 'px');
    root.style.setProperty('--tb4-popup-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-top', top + 'px');
    root.style.setProperty('--tb4-header-popup-lock-top', top + 'px');
    root.style.setProperty('--tb4-popup-anchor-top', top + 'px');
    root.style.setProperty('--tb4-v211-top', top + 'px');
    return top;
  }

  function clampPanel(trigger, panel) {
    var top;
    if (!panel || !isOpen(panel)) { return; }
    top = whiteHeaderBottom(trigger);
    add(panel, 'tb4-v211-popup');

    css(panel, 'display', 'block');
    css(panel, 'position', 'fixed');
    css(panel, 'top', top + 'px');
    css(panel, 'margin-top', '0px');
    css(panel, 'transform', 'none');
    css(panel, 'translate', 'none');
    css(panel, 'visibility', 'visible');
    css(panel, 'opacity', '1');
    css(panel, 'pointer-events', 'auto');
    css(panel, 'z-index', '2147483001');
    css(panel, 'overflow-x', 'hidden');
    css(panel, 'overflow-y', 'auto');
    css(panel, '-webkit-overflow-scrolling', 'touch');
    css(panel, 'max-height', Math.max(160, vh() - top - 8) + 'px');

    if (isMobile()) {
      css(panel, 'left', '12px');
      css(panel, 'right', '12px');
      css(panel, 'width', 'auto');
      css(panel, 'min-width', '0');
      css(panel, 'max-width', 'calc(100vw - 24px)');
      css(panel, 'border-radius', '0 0 22px 22px');
    }
  }

  function unclampMenu(overlay) {
    if (!overlay) { return; }
    remove(overlay, 'tb4-v211-open');
    if (!isMobile()) {
      overlay.style.removeProperty('top');
      overlay.style.removeProperty('inset');
      overlay.style.removeProperty('height');
      overlay.style.removeProperty('max-height');
      overlay.style.removeProperty('z-index');
    }
  }

  function clampMenu(overlay, forcePrepare) {
    var top;
    var height;
    overlay = overlay || id('mobileMenuOverlay');
    if (!overlay) { return; }

    if (overlay.parentNode !== document.body && document.body) { document.body.appendChild(overlay); }

    if (!isMobile()) {
      unclampMenu(overlay);
      return;
    }

    /* Main.js calls alignMobileMenu before it removes [hidden]. In that exact case,
       forcePrepare must still calculate layout, otherwise the menu can disappear. */
    if (!forcePrepare && !isOpen(overlay)) {
      unclampMenu(overlay);
      return;
    }

    top = whiteHeaderBottom(id('mobileMenuToggle'));
    height = Math.max(180, vh() - top);

    css(overlay, 'position', 'fixed');
    css(overlay, 'top', top + 'px');
    css(overlay, 'left', '0');
    css(overlay, 'right', '0');
    css(overlay, 'bottom', '0');
    css(overlay, 'inset', top + 'px 0 0 0');
    css(overlay, 'width', '100vw');
    css(overlay, 'max-width', '100vw');
    css(overlay, 'min-width', '0');
    css(overlay, 'height', height + 'px');
    css(overlay, 'max-height', height + 'px');
    css(overlay, 'box-sizing', 'border-box');
    css(overlay, 'overflow-x', 'hidden');
    css(overlay, 'overflow-y', 'auto');
    css(overlay, 'overscroll-behavior', 'contain');
    css(overlay, '-webkit-overflow-scrolling', 'touch');
    css(overlay, 'background', '#ffffff');
    css(overlay, 'border-top', '1px solid rgba(15, 23, 42, .08)');
    css(overlay, 'border-radius', '0');
    css(overlay, 'box-shadow', '0 18px 42px rgba(15, 23, 42, .12)');
    css(overlay, 'transform', 'none');
    css(overlay, 'translate', 'none');
    css(overlay, 'visibility', 'visible');
    css(overlay, 'opacity', '1');
    css(overlay, 'pointer-events', 'auto');
    css(overlay, 'z-index', '2147483000');

    if (!forcePrepare || isOpen(overlay)) { add(overlay, 'tb4-v211-open'); }
  }

  function clampAll() {
    whiteHeaderBottom(id('mobileMenuToggle') || id('userMenuTrigger') || id('tb4NotificationTrigger'));
    clampPanel(id('tb4NotificationTrigger'), id('tb4NotificationPanel'));
    clampPanel(id('userMenuTrigger'), id('userMenuDropdown'));
    clampMenu(id('mobileMenuOverlay'), false);
  }

  function repeatedClamp() {
    clampAll();
    window.setTimeout(clampAll, 0);
    window.setTimeout(clampAll, 40);
    window.setTimeout(clampAll, 120);
    window.setTimeout(clampAll, 260);
  }

  function schedule() {
    if (refreshTimer) { window.clearTimeout(refreshTimer); }
    refreshTimer = window.setTimeout(clampAll, 25);
  }

  function overrideTB4() {
    if (!window.TB4) { window.TB4 = {}; }
    var oldAlign = window.TB4.alignHeaderPopup;
    window.TB4.alignHeaderPopup = function (trigger, panel, type) {
      if (type === 'user' || type === 'notification') {
        clampPanel(trigger, panel);
        return;
      }
      if (typeof oldAlign === 'function') { oldAlign(trigger, panel, type); }
    };
    window.TB4.alignMobileMenu = function (overlay) {
      clampMenu(overlay || id('mobileMenuOverlay'), true);
    };
    window.TB4.getWhiteHeaderBottom = whiteHeaderBottom;
  }

  ready(function () {
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
    window.addEventListener('load', repeatedClamp, { passive: true });
    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', schedule, { passive: true });
      window.visualViewport.addEventListener('scroll', schedule, { passive: true });
    }
  });

  window.tb4WhiteHeaderClampV211 = {
    refresh: repeatedClamp,
    whiteHeaderBottom: whiteHeaderBottom,
    clampMenu: function () { clampMenu(id('mobileMenuOverlay'), true); }
  };
}(window, document));
