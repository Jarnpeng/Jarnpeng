/**
 * Thinkb4do v2.0.6 — Mobile Menu Hard-Port Fix
 * Root cause fixed here: the original overlay lived inside a fixed/transformed header.
 * Some mobile browsers treat fixed children of transformed/contained elements as if they were trapped inside that element.
 * This file ports the overlay to body, then positions it under the visible white header row.
 */
(function (window, document) {
  'use strict';

  var MOBILE_MAX = 1024;
  var root = document.documentElement;
  var state = { lastFocus: null, timer: null };

  function byId(id) { return document.getElementById(id); }

  function viewportW() {
    return Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
  }

  function viewportH() {
    if (window.visualViewport && window.visualViewport.height) {
      return Math.round(window.visualViewport.height);
    }
    return Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
  }

  function isMobile() {
    return viewportW() <= MOBILE_MAX;
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
    var fn;
    var nodes;
    var i;
    if (!el || el.nodeType !== 1) { return false; }
    fn = el.matches || el.webkitMatchesSelector || el.msMatchesSelector || el.mozMatchesSelector;
    if (fn) { return fn.call(el, selector); }
    nodes = (el.parentNode || document).querySelectorAll(selector);
    for (i = 0; i < nodes.length; i += 1) {
      if (nodes[i] === el) { return true; }
    }
    return false;
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

  function clearOverlayInline(overlay) {
    var props = [
      'display','position','top','right','bottom','left','inset','width','max-width','min-width','height','max-height',
      'margin','padding','box-sizing','overflow','overflow-x','overflow-y','overscroll-behavior','background','border-top',
      'border-radius','box-shadow','transform','translate','opacity','visibility','pointer-events','z-index','contain','isolation','touch-action'
    ];
    var i;
    if (!overlay || !overlay.style) { return; }
    for (i = 0; i < props.length; i += 1) {
      overlay.style.removeProperty(props[i]);
    }
  }

  function portOverlay() {
    var overlay = byId('mobileMenuOverlay');
    if (!overlay || !document.body) { return overlay; }

    /* Escape #site-header transform/contain/fixed stacking context. */
    if (overlay.parentNode !== document.body) {
      document.body.appendChild(overlay);
    }
    addClass(overlay, 'tb4-mobile-menu-ported');
    return overlay;
  }

  function headerBottom() {
    var header = byId('site-header');
    var anchor;
    var rect;
    var bottom = 0;

    if (header && header.querySelector) {
      anchor = header.querySelector('.header-inner') || header.querySelector('.container') || header;
    } else {
      anchor = header;
    }

    if (anchor && anchor.getBoundingClientRect) {
      rect = anchor.getBoundingClientRect();
      if (rect && rect.bottom > 0) {
        bottom = Math.ceil(rect.bottom);
      }
    }

    if (!bottom && header && header.getBoundingClientRect) {
      rect = header.getBoundingClientRect();
      if (rect && rect.bottom > 0) {
        bottom = Math.ceil(rect.bottom);
      }
    }

    if (!bottom) {
      bottom = 72;
    }

    /* Clamp to viewport so a bad measured value cannot push the menu off screen. */
    bottom = Math.max(0, Math.min(bottom, Math.max(72, viewportH() - 160)));
    return bottom;
  }

  function applyOpenLayout() {
    var overlay = portOverlay();
    var top = headerBottom();
    var maxH = Math.max(180, viewportH() - top);

    if (!overlay) { return; }

    root.style.setProperty('--tb4-v205-vh', viewportH() + 'px');
    root.style.setProperty('--tb4-menu-v205-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-tight-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-hard-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-top', top + 'px');
    root.style.setProperty('--tb4-popup-anchor-top', top + 'px');
    root.style.setProperty('--tb4-header-popup-tight-top', top + 'px');

    overlay.removeAttribute('hidden');
    overlay.setAttribute('aria-hidden', 'false');
    addClass(overlay, 'is-open');
    addClass(document.body, 'tb4-menu-open');
    addClass(document.body, 'tb4-mobile-menu-open');
    addClass(document.body, 'tb4-mobile-menu-tight-open');
    addClass(document.body, 'tb4-mobile-menu-head-locked');
    addClass(document.body, 'tb4-mobile-menu-hard-open');

    overlay.style.setProperty('display', 'block', 'important');
    overlay.style.setProperty('position', 'fixed', 'important');
    overlay.style.setProperty('top', top + 'px', 'important');
    overlay.style.setProperty('right', '0', 'important');
    overlay.style.setProperty('bottom', '0', 'important');
    overlay.style.setProperty('left', '0', 'important');
    overlay.style.setProperty('inset', top + 'px 0 0 0', 'important');
    overlay.style.setProperty('width', '100vw', 'important');
    overlay.style.setProperty('max-width', '100vw', 'important');
    overlay.style.setProperty('min-width', '0', 'important');
    overlay.style.setProperty('height', maxH + 'px', 'important');
    overlay.style.setProperty('max-height', maxH + 'px', 'important');
    overlay.style.setProperty('margin', '0', 'important');
    overlay.style.setProperty('padding', '10px max(12px, env(safe-area-inset-left)) calc(14px + env(safe-area-inset-bottom)) max(12px, env(safe-area-inset-right))', 'important');
    overlay.style.setProperty('box-sizing', 'border-box', 'important');
    overlay.style.setProperty('overflow-x', 'hidden', 'important');
    overlay.style.setProperty('overflow-y', 'auto', 'important');
    overlay.style.setProperty('background', '#ffffff', 'important');
    overlay.style.setProperty('border-top', '1px solid rgba(15,23,42,.08)', 'important');
    overlay.style.setProperty('border-radius', '0', 'important');
    overlay.style.setProperty('box-shadow', '0 18px 40px rgba(15,23,42,.12)', 'important');
    overlay.style.setProperty('transform', 'none', 'important');
    overlay.style.setProperty('opacity', '1', 'important');
    overlay.style.setProperty('visibility', 'visible', 'important');
    overlay.style.setProperty('pointer-events', 'auto', 'important');
    overlay.style.setProperty('z-index', '2147483600', 'important');
    overlay.style.setProperty('contain', 'none', 'important');
    overlay.style.setProperty('isolation', 'isolate', 'important');
  }

  function closeMenu() {
    var overlay = portOverlay();
    var toggle = byId('mobileMenuToggle');
    if (overlay) {
      clearOverlayInline(overlay);
      removeClass(overlay, 'is-open');
      overlay.setAttribute('hidden', 'hidden');
      overlay.setAttribute('aria-hidden', 'true');
    }
    removeClass(document.body, 'tb4-menu-open');
    removeClass(document.body, 'tb4-mobile-menu-open');
    removeClass(document.body, 'tb4-mobile-menu-tight-open');
    removeClass(document.body, 'tb4-mobile-menu-head-locked');
    removeClass(document.body, 'tb4-mobile-menu-hard-open');
    if (toggle) {
      toggle.setAttribute('aria-expanded', 'false');
      setIcon(toggle, false);
    }
  }

  function openMenu() {
    var toggle = byId('mobileMenuToggle');
    if (!isMobile()) {
      closeMenu();
      return;
    }
    state.lastFocus = document.activeElement;
    applyOpenLayout();
    if (toggle) {
      toggle.setAttribute('aria-expanded', 'true');
      setIcon(toggle, true);
    }
  }

  function isOpen() {
    var overlay = byId('mobileMenuOverlay');
    return !!(overlay && (hasClass(overlay, 'is-open') || overlay.getAttribute('aria-hidden') === 'false'));
  }

  function refresh() {
    portOverlay();
    if (!isMobile()) {
      closeMenu();
      root.classList.remove('tb4-actual-mobile');
      root.classList.add('tb4-viewport-desktop');
      root.classList.remove('tb4-viewport-mobile');
      return;
    }
    root.classList.add('tb4-viewport-mobile');
    root.classList.remove('tb4-viewport-desktop');
    if (isOpen()) { applyOpenLayout(); }
  }

  function scheduleRefresh() {
    if (state.timer) { window.clearTimeout(state.timer); }
    refresh();
    state.timer = window.setTimeout(refresh, 120);
  }

  function onDocumentClickCapture(event) {
    var toggle = closest(event.target, '#mobileMenuToggle');
    if (!toggle) { return; }
    event.preventDefault();
    event.stopPropagation();
    if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
    if (isOpen()) { closeMenu(); }
    else { openMenu(); }
  }

  function onDocumentClickBubble(event) {
    if (closest(event.target, '#mobileMenuOverlay a')) {
      closeMenu();
    }
  }

  function onKeydown(event) {
    if ((event.key === 'Escape' || event.keyCode === 27) && isOpen()) {
      closeMenu();
      if (state.lastFocus && state.lastFocus.focus) { state.lastFocus.focus(); }
    }
  }

  function init() {
    portOverlay();
    refresh();
    document.addEventListener('click', onDocumentClickCapture, true);
    document.addEventListener('click', onDocumentClickBubble, false);
    document.addEventListener('keydown', onKeydown, false);
    window.addEventListener('resize', scheduleRefresh, false);
    window.addEventListener('orientationchange', function () {
      window.setTimeout(refresh, 80);
      window.setTimeout(refresh, 280);
    }, false);
    window.addEventListener('load', scheduleRefresh, false);
    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', scheduleRefresh, false);
      window.visualViewport.addEventListener('scroll', scheduleRefresh, false);
    }
  }

  window.tb4MobileMenuHardPortV205 = {
    refresh: refresh,
    open: openMenu,
    close: closeMenu
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, false);
  } else {
    init();
  }
})(window, document);
