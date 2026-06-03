/**
 * Thinkb4do Theme v2.0.7 — Mobile Header Popup Clamp
 * Final high-priority mobile controller for hamburger, notification, and user dropdown.
 */
(function (window, document) {
  'use strict';

  if (window.tb4HeaderPopupClampV207Booted) { return; }
  window.tb4HeaderPopupClampV207Booted = true;

  var MOBILE_MAX = 1024;
  var root = document.documentElement;
  var body = null;
  var suppressClickUntil = 0;
  var refreshTimer = null;
  var observer = null;

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  function id(name) { return document.getElementById(name); }

  function vw() {
    return Math.max(root.clientWidth || 0, window.innerWidth || 0);
  }

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
  function setBody(cls, on) { if (!body) { return; } if (on) { add(body, cls); } else { remove(body, cls); } }

  function closest(el, sel) {
    if (!el) { return null; }
    return el.closest ? el.closest(sel) : null;
  }

  function setImportant(el, prop, value) {
    if (el && el.style) { el.style.setProperty(prop, value, 'important'); }
  }

  function removeInline(el, props) {
    var i;
    if (!el || !el.style) { return; }
    for (i = 0; i < props.length; i += 1) { el.style.removeProperty(props[i]); }
  }

  function headerBottom() {
    var header = id('site-header');
    var inner = header && (header.querySelector('.header-inner') || header.querySelector('.container'));
    var rect = null;
    var innerRect = null;
    var bottom = 0;

    if (header && header.getBoundingClientRect) {
      rect = header.getBoundingClientRect();
      if (rect && rect.height > 20 && rect.bottom > 0) { bottom = Math.max(bottom, Math.round(rect.bottom)); }
    }
    if (inner && inner.getBoundingClientRect) {
      innerRect = inner.getBoundingClientRect();
      if (innerRect && innerRect.height > 20 && innerRect.bottom > 0) { bottom = Math.max(bottom, Math.round(innerRect.bottom)); }
    }

    if (!bottom) {
      bottom = parseInt(getComputedStyle(root).getPropertyValue('--tb4-sticky-top'), 10) || 0;
      bottom += parseInt(getComputedStyle(root).getPropertyValue('--tb4-header-real-h'), 10) || 72;
    }

    /* Clamp to a usable viewport height. The dropdown must start at the white header, not under the green notice. */
    bottom = Math.max(0, bottom);
    bottom = Math.min(bottom, Math.max(72, vh() - 160));
    return bottom;
  }

  function syncVars() {
    var top = headerBottom();
    root.style.setProperty('--tb4-v207-vh', vh() + 'px');
    root.style.setProperty('--tb4-menu-v207-top', top + 'px');
    root.style.setProperty('--tb4-header-popup-v207-top', top + 'px');
    /* Feed old fallback variables too, so previous CSS cannot drag the dropdown down. */
    root.style.setProperty('--tb4-menu-v206-top', top + 'px');
    root.style.setProperty('--tb4-menu-v205-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-hard-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-tight-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-top', top + 'px');
    root.style.setProperty('--tb4-popup-anchor-top', top + 'px');
    root.style.setProperty('--tb4-header-popup-lock-top', top + 'px');
    return top;
  }

  function setHamburgerIcon(open) {
    var toggle = id('mobileMenuToggle');
    var spans;
    if (!toggle) { return; }
    spans = toggle.querySelectorAll('span');
    if (!spans || spans.length < 3) { return; }
    spans[0].style.transform = open ? 'rotate(45deg) translate(5px, 5px)' : '';
    spans[1].style.opacity = open ? '0' : '';
    spans[2].style.transform = open ? 'rotate(-45deg) translate(5px, -5px)' : '';
  }

  function portOverlay() {
    var overlay = id('mobileMenuOverlay');
    if (!overlay || !document.body) { return overlay; }
    if (overlay.parentNode !== document.body) { document.body.appendChild(overlay); }
    add(overlay, 'tb4-mobile-menu-ported');
    add(overlay, 'tb4-mobile-menu-v207');
    return overlay;
  }

  function closeMenu() {
    var overlay = portOverlay();
    var toggle = id('mobileMenuToggle');
    if (overlay) {
      remove(overlay, 'is-open');
      remove(overlay, 'tb4-menu-v207-open');
      remove(overlay, 'tb4-menu-v206-open');
      overlay.setAttribute('hidden', 'hidden');
      overlay.setAttribute('aria-hidden', 'true');
      removeInline(overlay, ['display','position','top','left','right','bottom','inset','width','max-width','min-width','height','max-height','overflow','overflow-x','overflow-y','z-index','margin','padding','box-sizing','background','border-top','border-radius','box-shadow','transform','translate','opacity','visibility','pointer-events','contain','isolation','touch-action','overscroll-behavior']);
    }
    setBody('tb4-menu-open', false);
    setBody('tb4-mobile-menu-open', false);
    setBody('tb4-mobile-menu-v206-open', false);
    setBody('tb4-mobile-menu-v207-open', false);
    setBody('tb4-menu-v207-open', false);
    setBody('tb4-mobile-menu-hard-open', false);
    setBody('tb4-mobile-menu-tight-open', false);
    setBody('tb4-mobile-menu-head-locked', false);
    if (toggle) { toggle.setAttribute('aria-expanded', 'false'); }
    setHamburgerIcon(false);
  }

  function openMenu() {
    var overlay = portOverlay();
    var toggle = id('mobileMenuToggle');
    var top;
    var maxH;
    if (!overlay || !toggle) { return; }
    if (!isMobile()) { closeMenu(); return; }

    top = syncVars();
    maxH = Math.max(180, vh() - top);

    overlay.removeAttribute('hidden');
    overlay.setAttribute('aria-hidden', 'false');
    add(overlay, 'is-open');
    add(overlay, 'tb4-menu-v207-open');
    add(overlay, 'tb4-menu-v206-open');
    setBody('tb4-menu-open', true);
    setBody('tb4-mobile-menu-open', true);
    setBody('tb4-mobile-menu-v206-open', true);
    setBody('tb4-mobile-menu-v207-open', true);
    setBody('tb4-menu-v207-open', true);
    setBody('tb4-mobile-menu-hard-open', true);
    setBody('tb4-mobile-menu-tight-open', true);
    setBody('tb4-mobile-menu-head-locked', true);

    toggle.setAttribute('aria-expanded', 'true');
    setHamburgerIcon(true);

    setImportant(overlay, 'display', 'block');
    setImportant(overlay, 'position', 'fixed');
    setImportant(overlay, 'top', top + 'px');
    setImportant(overlay, 'left', '0');
    setImportant(overlay, 'right', '0');
    setImportant(overlay, 'bottom', '0');
    setImportant(overlay, 'inset', top + 'px 0 0 0');
    setImportant(overlay, 'width', '100vw');
    setImportant(overlay, 'max-width', '100vw');
    setImportant(overlay, 'min-width', '0');
    setImportant(overlay, 'height', maxH + 'px');
    setImportant(overlay, 'max-height', maxH + 'px');
    setImportant(overlay, 'margin', '0');
    setImportant(overlay, 'padding', '10px 12px calc(14px + env(safe-area-inset-bottom)) 12px');
    setImportant(overlay, 'box-sizing', 'border-box');
    setImportant(overlay, 'overflow-x', 'hidden');
    setImportant(overlay, 'overflow-y', 'auto');
    setImportant(overlay, 'overscroll-behavior', 'contain');
    setImportant(overlay, '-webkit-overflow-scrolling', 'touch');
    setImportant(overlay, 'background', '#ffffff');
    setImportant(overlay, 'border-top', '1px solid rgba(15,23,42,.08)');
    setImportant(overlay, 'border-radius', '0');
    setImportant(overlay, 'box-shadow', '0 18px 40px rgba(15,23,42,.12)');
    setImportant(overlay, 'transform', 'none');
    setImportant(overlay, 'translate', 'none');
    setImportant(overlay, 'opacity', '1');
    setImportant(overlay, 'visibility', 'visible');
    setImportant(overlay, 'pointer-events', 'auto');
    setImportant(overlay, 'z-index', '2147483400');
    setImportant(overlay, 'contain', 'none');
    setImportant(overlay, 'isolation', 'isolate');
  }

  function menuOpen() {
    var overlay = id('mobileMenuOverlay');
    return !!(overlay && (has(overlay, 'is-open') || overlay.getAttribute('aria-hidden') === 'false' || !overlay.hasAttribute('hidden')));
  }

  function clampOnePopup(panel) {
    var top;
    var maxH;
    if (!panel || !isMobile()) { return; }
    if (panel.hasAttribute('hidden') || panel.getAttribute('aria-hidden') === 'true' || panel.style.display === 'none') {
      remove(panel, 'tb4-header-popup-v207');
      return;
    }
    top = syncVars();
    maxH = Math.max(160, vh() - top - 12);
    add(panel, 'tb4-header-popup-v207');
    setImportant(panel, 'display', 'block');
    setImportant(panel, 'position', 'fixed');
    setImportant(panel, 'top', top + 'px');
    setImportant(panel, 'left', '12px');
    setImportant(panel, 'right', '12px');
    setImportant(panel, 'width', 'auto');
    setImportant(panel, 'min-width', '0');
    setImportant(panel, 'max-width', 'calc(100vw - 24px)');
    setImportant(panel, 'max-height', maxH + 'px');
    setImportant(panel, 'margin', '0');
    setImportant(panel, 'box-sizing', 'border-box');
    setImportant(panel, 'overflow-x', 'hidden');
    setImportant(panel, 'overflow-y', 'auto');
    setImportant(panel, 'overscroll-behavior', 'contain');
    setImportant(panel, 'transform', 'none');
    setImportant(panel, 'translate', 'none');
    setImportant(panel, 'z-index', '2147483450');
  }

  function clampHeaderPopups() {
    clampOnePopup(id('tb4NotificationPanel'));
    clampOnePopup(id('userMenuDropdown'));
  }

  function refresh() {
    body = document.body;
    syncVars();
    portOverlay();
    if (!isMobile()) {
      closeMenu();
      remove(id('tb4NotificationPanel'), 'tb4-header-popup-v207');
      remove(id('userMenuDropdown'), 'tb4-header-popup-v207');
      return;
    }
    if (menuOpen()) { openMenu(); }
    clampHeaderPopups();
  }

  function scheduleRefresh() {
    refresh();
    if (refreshTimer) { window.clearTimeout(refreshTimer); }
    refreshTimer = window.setTimeout(refresh, 80);
    window.setTimeout(refresh, 240);
    window.setTimeout(refresh, 520);
  }

  function handleHamburgerEvent(e) {
    var target = closest(e.target, '#mobileMenuToggle');
    var now = Date.now ? Date.now() : new Date().getTime();
    if (!target) { return; }
    if (!isMobile()) { closeMenu(); return; }

    e.preventDefault();
    e.stopPropagation();
    if (e.stopImmediatePropagation) { e.stopImmediatePropagation(); }

    if (now < suppressClickUntil) { return; }
    suppressClickUntil = now + 420;

    if (menuOpen()) { closeMenu(); }
    else { openMenu(); }
    scheduleRefresh();
  }

  function handleGeneralHeaderEvent(e) {
    if (closest(e.target, '#tb4NotificationTrigger, #userMenuTrigger, .tb4-notification-panel, #userMenuDropdown')) {
      window.setTimeout(clampHeaderPopups, 0);
      window.setTimeout(clampHeaderPopups, 80);
      window.setTimeout(clampHeaderPopups, 240);
    }
  }

  function watchPopups() {
    var panels = [id('tb4NotificationPanel'), id('userMenuDropdown')].filter(Boolean);
    if (!('MutationObserver' in window) || !panels.length) { return; }
    if (observer) { observer.disconnect(); }
    observer = new MutationObserver(function () { clampHeaderPopups(); });
    panels.forEach(function (panel) {
      observer.observe(panel, { attributes: true, attributeFilter: ['class', 'style', 'hidden', 'aria-hidden'] });
    });
  }

  ready(function () {
    body = document.body;
    refresh();
    watchPopups();

    document.addEventListener('pointerdown', handleHamburgerEvent, true);
    document.addEventListener('touchstart', handleHamburgerEvent, true);
    document.addEventListener('click', handleHamburgerEvent, true);
    document.addEventListener('pointerdown', handleGeneralHeaderEvent, true);
    document.addEventListener('touchstart', handleGeneralHeaderEvent, true);
    document.addEventListener('click', handleGeneralHeaderEvent, true);

    window.addEventListener('resize', scheduleRefresh, false);
    window.addEventListener('orientationchange', scheduleRefresh, false);
    window.addEventListener('scroll', function () { if (menuOpen()) { scheduleRefresh(); } else { clampHeaderPopups(); } }, true);
    if (window.visualViewport) {
      window.visualViewport.addEventListener('resize', scheduleRefresh, false);
      window.visualViewport.addEventListener('scroll', scheduleRefresh, false);
    }
  });

  window.tb4HeaderPopupClampV207 = {
    refresh: refresh,
    closeMenu: closeMenu,
    openMenu: openMenu,
    clampHeaderPopups: clampHeaderPopups
  };
}(window, document));
