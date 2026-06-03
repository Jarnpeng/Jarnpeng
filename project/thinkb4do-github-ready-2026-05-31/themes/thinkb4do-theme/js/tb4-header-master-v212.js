/*
 * Thinkb4do Header Master Stabilizer v2.1.2
 * Final controller for header stack, dropdowns and mobile menu.
 */
(function (window, document) {
  'use strict';

  var root = document.documentElement;
  var body = document.body;
  var activePanel = null;

  function qs(selector, context) {
    return (context || document).querySelector(selector);
  }

  function qsa(selector, context) {
    return Array.prototype.slice.call((context || document).querySelectorAll(selector));
  }

  function hasClass(el, name) {
    return !!(el && el.classList && el.classList.contains(name));
  }

  function addClass(el, name) {
    if (el && el.classList) { el.classList.add(name); }
  }

  function removeClass(el, name) {
    if (el && el.classList) { el.classList.remove(name); }
  }

  function setHidden(el, hidden) {
    if (!el) { return; }
    if (hidden) {
      el.setAttribute('hidden', 'hidden');
      el.setAttribute('aria-hidden', 'true');
    } else {
      el.removeAttribute('hidden');
      el.setAttribute('aria-hidden', 'false');
    }
  }

  function isVisible(el) {
    if (!el) { return false; }
    var style = window.getComputedStyle ? window.getComputedStyle(el) : null;
    return !!(el.offsetWidth || el.offsetHeight || (style && style.position === 'fixed')) && (!style || (style.display !== 'none' && style.visibility !== 'hidden'));
  }

  function getAdminHeight() {
    var bar = document.getElementById('wpadminbar');
    if (!bar || !isVisible(bar)) { return 0; }
    var rect = bar.getBoundingClientRect ? bar.getBoundingClientRect() : null;
    return Math.round((rect && rect.height) || bar.offsetHeight || 0);
  }

  function getHeader() {
    return document.getElementById('site-header');
  }

  function getHeaderHeight() {
    var header = getHeader();
    if (!header) { return window.innerWidth <= 1024 ? 72 : 64; }
    var rect = header.getBoundingClientRect ? header.getBoundingClientRect() : null;
    return Math.max(54, Math.round((rect && rect.height) || header.offsetHeight || (window.innerWidth <= 1024 ? 72 : 64)));
  }

  function getNoticeHeight() {
    var notice = qs('.tb4-dev-notice, .tb4-dev-notice-topmost');
    if (!notice || !isVisible(notice) || body.classList.contains('tb4-dev-notice-off') || body.classList.contains('tb4-dev-notice-dismissed')) { return 0; }
    var rect = notice.getBoundingClientRect ? notice.getBoundingClientRect() : null;
    return Math.max(0, Math.round((rect && rect.height) || notice.offsetHeight || 0));
  }

  function headerBottom() {
    var adminH = getAdminHeight();
    var headerH = getHeaderHeight();
    return Math.round(adminH + headerH);
  }

  function syncLayout() {
    body = document.body;
    var adminH = getAdminHeight();
    var headerH = getHeaderHeight();
    var noticeH = getNoticeHeight();
    var header = getHeader();

    root.classList.add('tb4-v212-ready');
    root.classList.toggle('tb4-v212-admin-on', adminH > 0);
    root.style.setProperty('--tb4-v212-admin-h', adminH + 'px');
    root.style.setProperty('--tb4-v212-header-h', headerH + 'px');
    root.style.setProperty('--tb4-v212-notice-h', noticeH + 'px');
    root.style.setProperty('--tb4-v212-header-bottom', (adminH + headerH) + 'px');
    root.style.setProperty('--tb4-v212-stack-h', (headerH + noticeH) + 'px');
    root.style.setProperty('--tb4-sticky-top', adminH + 'px');
    root.style.setProperty('--tb4-header-real-h', headerH + 'px');
    root.style.setProperty('--tb4-dev-notice-real-h', noticeH + 'px');
    root.style.setProperty('--tb4-fixed-header-stack-h', (headerH + noticeH) + 'px');

    if (header) {
      header.style.setProperty('top', adminH + 'px', 'important');
      header.style.setProperty('height', headerH + 'px', 'important');
      header.style.setProperty('z-index', '1000002', 'important');
      header.style.setProperty('pointer-events', 'auto', 'important');
      header.style.setProperty('overflow', 'visible', 'important');
      header.style.setProperty('contain', 'none', 'important');
    }

    var notice = qs('.tb4-dev-notice, .tb4-dev-notice-topmost');
    if (notice && noticeH > 0) {
      notice.style.setProperty('top', (adminH + headerH) + 'px', 'important');
      notice.style.setProperty('height', noticeH + 'px', 'important');
      notice.style.setProperty('z-index', '1000001', 'important');
    }

    if (window.innerWidth >= 1025) {
      closePanel(document.getElementById('mobileMenuOverlay'));
      removeClass(body, 'tb4-menu-open');
    }

    realignOpenPanels();
  }

  function moveToBody(panel) {
    if (!panel || panel.parentNode === document.body) { return; }
    document.body.appendChild(panel);
  }

  function closePanel(panel) {
    if (!panel) { return; }
    removeClass(panel, 'is-open');
    panel.style.display = 'none';
    setHidden(panel, true);
    if (activePanel === panel) { activePanel = null; }

    if (panel.id === 'mobileMenuOverlay') {
      var toggle = document.getElementById('mobileMenuToggle');
      removeClass(body, 'tb4-menu-open');
      if (toggle) { toggle.setAttribute('aria-expanded', 'false'); }
      qsa('#mobileMenuToggle span').forEach(function (span) {
        span.style.transform = '';
        span.style.opacity = '';
      });
    }
  }

  function closeAll(except) {
    [
      document.getElementById('tb4NotificationPanel'),
      document.getElementById('userMenuDropdown'),
      document.getElementById('mobileMenuOverlay'),
      document.getElementById('searchResults')
    ].forEach(function (panel) {
      if (panel && panel !== except) { closePanel(panel); }
    });
  }

  function openPanel(trigger, panel, type) {
    if (!trigger || !panel) { return; }
    syncLayout();
    moveToBody(panel);
    closeAll(panel);

    var top = headerBottom();
    var viewportW = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
    var viewportH = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
    var mobile = viewportW <= 1024;
    var rect = trigger.getBoundingClientRect ? trigger.getBoundingClientRect() : { right: viewportW - 16, left: 16, width: 240 };

    addClass(panel, 'is-open');
    panel.style.display = 'block';
    setHidden(panel, false);
    activePanel = panel;

    panel.style.setProperty('position', 'fixed', 'important');
    panel.style.setProperty('top', top + 'px', 'important');
    panel.style.setProperty('margin-top', '0px', 'important');
    panel.style.setProperty('z-index', type === 'mobile' ? '1000011' : '1000012', 'important');
    panel.style.setProperty('visibility', 'visible', 'important');
    panel.style.setProperty('opacity', '1', 'important');
    panel.style.setProperty('pointer-events', 'auto', 'important');
    panel.style.setProperty('transform', 'none', 'important');
    panel.style.setProperty('contain', 'none', 'important');
    panel.style.setProperty('overflow-x', 'hidden', 'important');
    panel.style.setProperty('overflow-y', 'auto', 'important');

    if (type === 'mobile') {
      addClass(body, 'tb4-menu-open');
      trigger.setAttribute('aria-expanded', 'true');
      panel.style.setProperty('left', '0', 'important');
      panel.style.setProperty('right', '0', 'important');
      panel.style.setProperty('bottom', '0', 'important');
      panel.style.setProperty('inset', top + 'px 0 0 0', 'important');
      panel.style.setProperty('width', '100vw', 'important');
      panel.style.setProperty('max-width', '100vw', 'important');
      panel.style.setProperty('height', Math.max(180, viewportH - top) + 'px', 'important');
      panel.style.setProperty('max-height', Math.max(180, viewportH - top) + 'px', 'important');
      panel.style.setProperty('background', '#fff', 'important');
      panel.style.setProperty('padding', '18px 16px 28px', 'important');
      var spans = qsa('#mobileMenuToggle span');
      if (spans.length >= 3) {
        spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
      }
      return;
    }

    trigger.setAttribute('aria-expanded', 'true');
    panel.style.setProperty('max-height', Math.max(160, viewportH - top - 8) + 'px', 'important');

    if (mobile) {
      panel.style.setProperty('left', '12px', 'important');
      panel.style.setProperty('right', '12px', 'important');
      panel.style.setProperty('width', 'auto', 'important');
      panel.style.setProperty('min-width', '0', 'important');
      panel.style.setProperty('max-width', 'calc(100vw - 24px)', 'important');
      panel.style.setProperty('border-radius', '0 0 22px 22px', 'important');
      return;
    }

    var right = Math.max(16, Math.round(viewportW - rect.right));
    root.style.setProperty('--tb4-v212-popup-right', right + 'px');
    panel.style.setProperty('right', right + 'px', 'important');
    panel.style.setProperty('left', 'auto', 'important');
    panel.style.setProperty('min-width', type === 'user' ? '220px' : '320px', 'important');
    panel.style.setProperty('width', type === 'user' ? 'max-content' : 'min(380px, calc(100vw - 40px))', 'important');
    panel.style.setProperty('max-width', type === 'user' ? 'min(320px, calc(100vw - 40px))' : 'min(380px, calc(100vw - 40px))', 'important');
  }

  function togglePanel(trigger, panel, type) {
    if (!panel) { return; }
    if (hasClass(panel, 'is-open')) {
      closePanel(panel);
      if (trigger) { trigger.setAttribute('aria-expanded', 'false'); }
    } else {
      openPanel(trigger, panel, type);
    }
  }

  function realignOpenPanels() {
    var notifTrigger = document.getElementById('tb4NotificationTrigger');
    var notifPanel = document.getElementById('tb4NotificationPanel');
    var userTrigger = document.getElementById('userMenuTrigger');
    var userPanel = document.getElementById('userMenuDropdown');
    var mobileTrigger = document.getElementById('mobileMenuToggle');
    var mobilePanel = document.getElementById('mobileMenuOverlay');

    if (notifPanel && hasClass(notifPanel, 'is-open')) { openPanel(notifTrigger, notifPanel, 'notification'); }
    if (userPanel && hasClass(userPanel, 'is-open')) { openPanel(userTrigger, userPanel, 'user'); }
    if (mobilePanel && hasClass(mobilePanel, 'is-open') && window.innerWidth <= 1024) { openPanel(mobileTrigger, mobilePanel, 'mobile'); }
  }

  function bindTrigger(triggerId, panelId, type) {
    var trigger = document.getElementById(triggerId);
    var panel = document.getElementById(panelId);
    if (!trigger || !panel) { return; }

    moveToBody(panel);
    closePanel(panel);

    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
      togglePanel(trigger, panel, type);
    }, true);

    trigger.addEventListener('pointerdown', function (event) {
      event.stopPropagation();
    }, true);

    panel.addEventListener('click', function (event) {
      event.stopPropagation();
    }, true);
  }

  function bindNoticeClose() {
    var btn = qs('[data-tb4-dev-notice-close], .tb4-dev-notice__close');
    var notice = qs('.tb4-dev-notice, .tb4-dev-notice-topmost');
    if (!btn || !notice) { return; }
    btn.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      addClass(body, 'tb4-dev-notice-dismissed');
      notice.style.display = 'none';
      root.style.setProperty('--tb4-v212-notice-h', '0px');
      root.style.setProperty('--tb4-v212-stack-h', getHeaderHeight() + 'px');
      syncLayout();
    }, true);
  }

  function init() {
    body = document.body;
    syncLayout();
    bindTrigger('tb4NotificationTrigger', 'tb4NotificationPanel', 'notification');
    bindTrigger('userMenuTrigger', 'userMenuDropdown', 'user');
    bindTrigger('mobileMenuToggle', 'mobileMenuOverlay', 'mobile');
    bindNoticeClose();

    document.addEventListener('click', function (event) {
      var target = event.target;
      if (target && target.closest && target.closest('#tb4NotificationTrigger,#userMenuTrigger,#mobileMenuToggle,#tb4NotificationPanel,#userMenuDropdown,#mobileMenuOverlay')) {
        return;
      }
      closeAll(null);
      var nt = document.getElementById('tb4NotificationTrigger');
      var ut = document.getElementById('userMenuTrigger');
      if (nt) { nt.setAttribute('aria-expanded', 'false'); }
      if (ut) { ut.setAttribute('aria-expanded', 'false'); }
    }, true);

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' || event.keyCode === 27) {
        closeAll(null);
      }
    });

    ['resize', 'orientationchange', 'load', 'scroll'].forEach(function (eventName) {
      window.addEventListener(eventName, function () {
        window.requestAnimationFrame ? window.requestAnimationFrame(syncLayout) : window.setTimeout(syncLayout, 16);
      }, { passive: true });
    });

    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', syncLayout, { passive: true });
      window.visualViewport.addEventListener('scroll', syncLayout, { passive: true });
    }

    window.setTimeout(syncLayout, 80);
    window.setTimeout(syncLayout, 400);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window, document);
