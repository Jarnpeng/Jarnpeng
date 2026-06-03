/**
 * Thinkb4do Theme v2.0.6 — Mobile Header Clamp Controller
 * Fixes mobile hamburger menu position by moving the overlay outside #site-header,
 * measuring the real white header bottom, covering the green development notice,
 * and clamping the overlay to the viewport so it cannot overflow horizontally.
 */
(function (window, document) {
  'use strict';

  var MOBILE_MAX = 1024;
  var root = document.documentElement;
  var lastFocus = null;
  var syncTimer = null;
  var lastInputAt = 0;

  function byId(id) { return document.getElementById(id); }

  function viewportWidth() {
    return Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
  }

  function viewportHeight() {
    if (window.visualViewport && window.visualViewport.height) {
      return Math.round(window.visualViewport.height);
    }
    return Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
  }

  function isMobileViewport() {
    return viewportWidth() <= MOBILE_MAX;
  }

  function closest(el, selector) {
    if (!el) { return null; }
    if (el.closest) { return el.closest(selector); }
    while (el && el.nodeType === 1) {
      if (matches(el, selector)) { return el; }
      el = el.parentNode;
    }
    return null;
  }

  function matches(el, selector) {
    var proto;
    var fn;
    var nodes;
    var i;
    if (!el || el.nodeType !== 1) { return false; }
    proto = (window.Element && window.Element.prototype) ? window.Element.prototype : {};
    fn = el.matches || el.webkitMatchesSelector || el.msMatchesSelector || el.mozMatchesSelector || proto.matches || proto.webkitMatchesSelector || proto.msMatchesSelector || proto.mozMatchesSelector;
    if (fn) { return fn.call(el, selector); }
    nodes = (el.parentNode || document).querySelectorAll(selector);
    for (i = 0; i < nodes.length; i += 1) {
      if (nodes[i] === el) { return true; }
    }
    return false;
  }

  function hasClass(el, name) {
    if (!el) { return false; }
    if (el.classList) { return el.classList.contains(name); }
    return (' ' + el.className + ' ').indexOf(' ' + name + ' ') > -1;
  }

  function addClass(el, name) {
    if (!el || hasClass(el, name)) { return; }
    if (el.classList) { el.classList.add(name); }
    else { el.className = (el.className ? el.className + ' ' : '') + name; }
  }

  function removeClass(el, name) {
    if (!el) { return; }
    if (el.classList) { el.classList.remove(name); }
    else { el.className = (' ' + el.className + ' ').replace(' ' + name + ' ', ' ').replace(/^\s+|\s+$/g, ''); }
  }

  function toggleClass(el, name, force) {
    if (force) { addClass(el, name); }
    else { removeClass(el, name); }
  }

  function portOverlay() {
    var overlay = byId('mobileMenuOverlay');
    if (!overlay || !document.body) { return overlay; }
    if (overlay.parentNode !== document.body) {
      document.body.appendChild(overlay);
    }
    addClass(overlay, 'tb4-mobile-menu-ported');
    addClass(overlay, 'tb4-mobile-menu-v206');
    return overlay;
  }

  function clearInlineMenuStyles(overlay) {
    var props = [
      'display','position','top','left','right','bottom','inset','width','max-width','min-width','height','max-height',
      'overflow','overflow-x','overflow-y','overscroll-behavior','z-index','background','margin','margin-top','border-top','box-shadow',
      'visibility','opacity','pointer-events','padding','box-sizing','touch-action','border-radius','translate','transform','contain','isolation'
    ];
    var i;
    if (!overlay || !overlay.style) { return; }
    for (i = 0; i < props.length; i += 1) {
      overlay.style.removeProperty(props[i]);
    }
  }

  function visibleHeaderBottom() {
    var header = byId('site-header');
    var admin = byId('wpadminbar');
    var rect;
    var adminRect;
    var top = 0;

    /* Use the fixed white header element itself. Do not add the green development notice. */
    if (header && header.getBoundingClientRect) {
      rect = header.getBoundingClientRect();
      if (rect && rect.bottom > 0 && rect.height > 20) {
        top = Math.floor(rect.bottom);
      }
    }

    if (!top && header && header.querySelector) {
      var inner = header.querySelector('.header-inner') || header.querySelector('.container');
      if (inner && inner.getBoundingClientRect) {
        rect = inner.getBoundingClientRect();
        if (rect && rect.bottom > 0) { top = Math.floor(rect.bottom); }
      }
    }

    /* Last fallback: WP admin bar + mobile header height. */
    if (!top) {
      if (admin && admin.getBoundingClientRect) {
        adminRect = admin.getBoundingClientRect();
        if (adminRect && adminRect.height > 0) { top += Math.ceil(adminRect.height); }
      }
      top += 72;
    }

    /* Small overlap prevents a 1px green/white gap between header and dropdown. */
    top = Math.max(0, top - 1);
    return Math.min(top, Math.max(72, viewportHeight() - 170));
  }

  function setIcon(toggle, open) {
    var spans;
    if (!toggle) { return; }
    spans = toggle.querySelectorAll('span');
    if (!spans || spans.length < 3) { return; }
    if (open) {
      spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
      spans[1].style.opacity = '0';
      spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
    } else {
      spans[0].style.transform = '';
      spans[1].style.opacity = '';
      spans[2].style.transform = '';
    }
  }

  function setClosed() {
    var overlay = portOverlay();
    var toggle = byId('mobileMenuToggle');
    if (overlay) {
      clearInlineMenuStyles(overlay);
      overlay.setAttribute('hidden', 'hidden');
      overlay.setAttribute('aria-hidden', 'true');
      removeClass(overlay, 'is-open');
      removeClass(overlay, 'tb4-menu-v206-open');
    }
    removeClass(document.body, 'tb4-menu-open');
    removeClass(document.body, 'tb4-mobile-menu-open');
    removeClass(document.body, 'tb4-mobile-menu-head-locked');
    removeClass(document.body, 'tb4-mobile-menu-tight-open');
    removeClass(document.body, 'tb4-mobile-menu-hard-open');
    removeClass(document.body, 'tb4-mobile-menu-v206-open');
    if (toggle) {
      toggle.setAttribute('aria-expanded', 'false');
      setIcon(toggle, false);
    }
  }

  function setOpen() {
    var overlay = portOverlay();
    var toggle = byId('mobileMenuToggle');
    var top;
    var maxH;
    if (!overlay || !toggle) { return; }
    if (!isMobileViewport()) {
      setClosed();
      return;
    }

    lastFocus = document.activeElement;
    top = visibleHeaderBottom();
    maxH = Math.max(180, viewportHeight() - top);

    root.style.setProperty('--tb4-visual-vh', viewportHeight() + 'px');
    root.style.setProperty('--tb4-v206-vh', viewportHeight() + 'px');
    root.style.setProperty('--tb4-menu-v206-top', top + 'px');
    root.style.setProperty('--tb4-menu-v205-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-tight-top', top + 'px');
    root.style.setProperty('--tb4-header-popup-tight-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-hard-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-top', top + 'px');
    root.style.setProperty('--tb4-popup-anchor-top', top + 'px');
    root.style.setProperty('--tb4-header-popup-lock-top', top + 'px');

    overlay.removeAttribute('hidden');
    overlay.setAttribute('aria-hidden', 'false');
    addClass(overlay, 'is-open');
    addClass(overlay, 'tb4-menu-v206-open');
    addClass(document.body, 'tb4-menu-open');
    addClass(document.body, 'tb4-mobile-menu-open');
    addClass(document.body, 'tb4-mobile-menu-head-locked');
    addClass(document.body, 'tb4-mobile-menu-tight-open');
    addClass(document.body, 'tb4-mobile-menu-hard-open');
    addClass(document.body, 'tb4-mobile-menu-v206-open');
    toggle.setAttribute('aria-expanded', 'true');
    setIcon(toggle, true);

    overlay.style.setProperty('display', 'block', 'important');
    overlay.style.setProperty('position', 'fixed', 'important');
    overlay.style.setProperty('top', top + 'px', 'important');
    overlay.style.setProperty('left', '0', 'important');
    overlay.style.setProperty('right', '0', 'important');
    overlay.style.setProperty('bottom', '0', 'important');
    overlay.style.setProperty('inset', top + 'px 0 0 0', 'important');
    overlay.style.setProperty('width', '100vw', 'important');
    overlay.style.setProperty('max-width', '100vw', 'important');
    overlay.style.setProperty('min-width', '0', 'important');
    overlay.style.setProperty('height', maxH + 'px', 'important');
    overlay.style.setProperty('max-height', maxH + 'px', 'important');
    overlay.style.setProperty('box-sizing', 'border-box', 'important');
    overlay.style.setProperty('overflow-x', 'hidden', 'important');
    overlay.style.setProperty('overflow-y', 'auto', 'important');
    overlay.style.setProperty('overscroll-behavior', 'contain', 'important');
    overlay.style.setProperty('-webkit-overflow-scrolling', 'touch', 'important');
    overlay.style.setProperty('z-index', '2147483400', 'important');
    overlay.style.setProperty('background', '#ffffff', 'important');
    overlay.style.setProperty('visibility', 'visible', 'important');
    overlay.style.setProperty('opacity', '1', 'important');
    overlay.style.setProperty('pointer-events', 'auto', 'important');
    overlay.style.setProperty('padding', '10px max(12px, env(safe-area-inset-left)) calc(14px + env(safe-area-inset-bottom)) max(12px, env(safe-area-inset-right))', 'important');
    overlay.style.setProperty('margin', '0', 'important');
    overlay.style.setProperty('border-top', '1px solid rgba(15, 23, 42, .08)', 'important');
    overlay.style.setProperty('border-radius', '0', 'important');
    overlay.style.setProperty('box-shadow', '0 18px 40px rgba(15, 23, 42, .12)', 'important');
    overlay.style.setProperty('touch-action', 'pan-y', 'important');
    overlay.style.setProperty('transform', 'none', 'important');
    overlay.style.setProperty('contain', 'none', 'important');
  }

  function isOpen() {
    var overlay = byId('mobileMenuOverlay');
    return !!(overlay && (hasClass(overlay, 'is-open') || overlay.getAttribute('aria-hidden') === 'false' || overlay.style.display === 'block'));
  }

  function syncResponsiveClasses() {
    var mobile = isMobileViewport();
    toggleClass(root, 'tb4-viewport-mobile', mobile);
    toggleClass(root, 'tb4-viewport-desktop', !mobile);
    if (!mobile) {
      removeClass(root, 'tb4-actual-mobile');
      setClosed();
    } else if (hasClass(root, 'tb4-device-mobile') || ('ontouchstart' in window) || (navigator.maxTouchPoints && navigator.maxTouchPoints > 0)) {
      addClass(root, 'tb4-actual-mobile');
    }
  }

  function scheduleSync() {
    if (syncTimer) { window.clearTimeout(syncTimer); }
    syncResponsiveClasses();
    if (isOpen()) { setOpen(); }
    syncTimer = window.setTimeout(function () {
      syncResponsiveClasses();
      if (isOpen()) { setOpen(); }
    }, 120);
  }

  function handleToggle(event) {
    var now = Date.now ? Date.now() : new Date().getTime();
    var toggle = closest(event.target, '#mobileMenuToggle');
    if (!toggle) { return; }
    if (now - lastInputAt < 320) {
      event.preventDefault();
      event.stopPropagation();
      if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
      return;
    }
    lastInputAt = now;
    event.preventDefault();
    event.stopPropagation();
    if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
    syncResponsiveClasses();
    if (!isMobileViewport()) {
      setClosed();
      return;
    }
    if (isOpen()) { setClosed(); }
    else { setOpen(); }
  }

  function onOverlayClick(event) {
    var link;
    if (!isOpen()) { return; }
    link = closest(event.target, '#mobileMenuOverlay a');
    if (link) { setClosed(); }
  }

  function onKeydown(event) {
    if (event.key === 'Escape' || event.keyCode === 27) {
      if (isOpen()) {
        setClosed();
        if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
      }
    }
  }

  function init() {
    portOverlay();
    syncResponsiveClasses();
    document.addEventListener('click', handleToggle, true);
    document.addEventListener('touchstart', handleToggle, true);
    document.addEventListener('pointerdown', handleToggle, true);
    document.addEventListener('click', onOverlayClick, false);
    document.addEventListener('keydown', onKeydown, false);
    window.addEventListener('resize', scheduleSync, false);
    window.addEventListener('orientationchange', function () { window.setTimeout(scheduleSync, 80); window.setTimeout(scheduleSync, 280); }, false);
    window.addEventListener('load', scheduleSync, false);
    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', scheduleSync, false);
      window.visualViewport.addEventListener('scroll', scheduleSync, false);
    }

    window.tb4MobileMenuV206 = {
      open: setOpen,
      close: setClosed,
      refresh: scheduleSync,
      top: visibleHeaderBottom
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, false);
  } else {
    init();
  }
})(window, document);
