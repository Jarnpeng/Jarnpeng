/*
 * Thinkb4do Header Master Stabilizer v2.1.3
 * Single final controller for header stack, dropdowns, messages, and mobile menu.
 */
(function (window, document) {
  'use strict';

  var root = document.documentElement;
  var body = document.body;
  var activePanel = null;
  var syncing = false;
  var rafId = 0;

  function qs(selector, context) { return (context || document).querySelector(selector); }
  function qsa(selector, context) { return Array.prototype.slice.call((context || document).querySelectorAll(selector)); }
  function hasClass(el, name) { return !!(el && el.classList && el.classList.contains(name)); }
  function addClass(el, name) { if (el && el.classList) { el.classList.add(name); } }
  function removeClass(el, name) { if (el && el.classList) { el.classList.remove(name); } }

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

  function getHeader() { return document.getElementById('site-header'); }

  function getHeaderHeight() {
    var header = getHeader();
    if (!header) { return window.innerWidth <= 1024 ? 72 : 64; }
    var rect = header.getBoundingClientRect ? header.getBoundingClientRect() : null;
    return Math.max(54, Math.round((rect && rect.height) || header.offsetHeight || (window.innerWidth <= 1024 ? 72 : 64)));
  }

  function getHeaderBottom() {
    return getAdminHeight() + getHeaderHeight();
  }

  function updateVars() {
    body = document.body;
    var adminH = getAdminHeight();
    var headerH = getHeaderHeight();
    var bottom = adminH + headerH;
    var header = getHeader();

    root.classList.add('tb4-v213-ready');
    root.classList.toggle('tb4-v213-admin-on', adminH > 0);
    root.style.setProperty('--tb4-v213-admin-h', adminH + 'px');
    root.style.setProperty('--tb4-v213-header-h', headerH + 'px');
    root.style.setProperty('--tb4-v213-header-bottom', bottom + 'px');
    root.style.setProperty('--tb4-sticky-top', adminH + 'px');
    root.style.setProperty('--tb4-header-real-h', headerH + 'px');
    root.style.setProperty('--tb4-white-header-bottom', bottom + 'px');
    root.style.setProperty('--tb4-popup-top', bottom + 'px');
    root.style.setProperty('--tb4-mobile-menu-top', bottom + 'px');
    root.style.setProperty('--tb4-fixed-header-stack-h', headerH + 'px');
    root.style.setProperty('--tb4-v172-header-stack-h', headerH + 'px');

    if (header) {
      header.style.setProperty('top', adminH + 'px', 'important');
      header.style.setProperty('height', headerH + 'px', 'important');
      header.style.setProperty('z-index', '2147483200', 'important');
      header.style.setProperty('pointer-events', 'auto', 'important');
      header.style.setProperty('overflow', 'visible', 'important');
      header.style.setProperty('contain', 'none', 'important');
      header.style.setProperty('transform', 'none', 'important');
    }

    qsa('.tb4-dev-notice, .tb4-dev-notice-topmost, [data-tb4-dev-notice="topmost"]').forEach(function (notice) {
      notice.style.setProperty('position', 'relative', 'important');
      notice.style.setProperty('top', 'auto', 'important');
      notice.style.setProperty('height', 'auto', 'important');
      notice.style.setProperty('max-height', 'none', 'important');
      notice.style.setProperty('z-index', '5', 'important');
    });

    if (window.innerWidth >= 1025) {
      closePanel(document.getElementById('mobileMenuOverlay'), false);
      removeClass(body, 'tb4-menu-open');
    }
  }

  function scheduleSync() {
    if (rafId) { window.cancelAnimationFrame ? window.cancelAnimationFrame(rafId) : window.clearTimeout(rafId); }
    rafId = window.requestAnimationFrame ? window.requestAnimationFrame(function () {
      rafId = 0;
      syncLayout();
    }) : window.setTimeout(function () {
      rafId = 0;
      syncLayout();
    }, 16);
  }

  function syncLayout() {
    if (syncing) { return; }
    syncing = true;
    updateVars();
    realignOpenPanels();
    syncing = false;
  }

  function moveToBody(panel) {
    if (!panel || panel.parentNode === document.body) { return; }
    document.body.appendChild(panel);
  }

  function closePanel(panel, resetTrigger) {
    if (!panel) { return; }
    removeClass(panel, 'is-open');
    panel.style.display = 'none';
    setHidden(panel, true);
    if (activePanel === panel) { activePanel = null; }

    var trigger = null;
    if (panel.id === 'mobileMenuOverlay') { trigger = document.getElementById('mobileMenuToggle'); }
    if (panel.id === 'tb4NotificationPanel') { trigger = document.getElementById('tb4NotificationTrigger'); }
    if (panel.id === 'tb4MessagePanel') { trigger = document.getElementById('tb4MessageTrigger'); }
    if (panel.id === 'userMenuDropdown') { trigger = document.getElementById('userMenuTrigger'); }
    if (resetTrigger !== false && trigger) { trigger.setAttribute('aria-expanded', 'false'); }

    if (panel.id === 'mobileMenuOverlay') {
      removeClass(body, 'tb4-menu-open');
      qsa('#mobileMenuToggle span').forEach(function (span) {
        span.style.transform = '';
        span.style.opacity = '';
      });
    }
  }

  function closeAll(except) {
    [
      document.getElementById('tb4NotificationPanel'),
      document.getElementById('tb4MessagePanel'),
      document.getElementById('userMenuDropdown'),
      document.getElementById('mobileMenuOverlay'),
      document.getElementById('searchResults')
    ].forEach(function (panel) {
      if (panel && panel !== except) { closePanel(panel); }
    });
  }

  function applyPanelPosition(trigger, panel, type) {
    if (!trigger || !panel) { return; }
    updateVars();

    var top = getHeaderBottom();
    var viewportW = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
    var viewportH = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
    var mobile = viewportW <= 1024;
    var rect = trigger.getBoundingClientRect ? trigger.getBoundingClientRect() : { right: viewportW - 16, left: 16, width: 240 };

    panel.style.setProperty('position', 'fixed', 'important');
    panel.style.setProperty('top', top + 'px', 'important');
    panel.style.setProperty('margin-top', '0px', 'important');
    panel.style.setProperty('visibility', 'visible', 'important');
    panel.style.setProperty('opacity', '1', 'important');
    panel.style.setProperty('pointer-events', 'auto', 'important');
    panel.style.setProperty('transform', 'none', 'important');
    panel.style.setProperty('contain', 'none', 'important');
    panel.style.setProperty('overflow-x', 'hidden', 'important');
    panel.style.setProperty('overflow-y', 'auto', 'important');
    panel.style.setProperty('background', '#fff', 'important');

    root.style.setProperty('--tb4-v213-header-bottom', top + 'px');
    root.style.setProperty('--tb4-white-header-bottom', top + 'px');
    root.style.setProperty('--tb4-popup-top', top + 'px');
    root.style.setProperty('--tb4-mobile-menu-top', top + 'px');

    if (type === 'mobile') {
      panel.style.setProperty('z-index', '2147483228', 'important');
      panel.style.setProperty('left', '0', 'important');
      panel.style.setProperty('right', '0', 'important');
      panel.style.setProperty('bottom', '0', 'important');
      panel.style.setProperty('inset', top + 'px 0 0 0', 'important');
      panel.style.setProperty('width', '100vw', 'important');
      panel.style.setProperty('max-width', '100vw', 'important');
      panel.style.setProperty('height', Math.max(180, viewportH - top) + 'px', 'important');
      panel.style.setProperty('max-height', Math.max(180, viewportH - top) + 'px', 'important');
      panel.style.setProperty('padding', '18px 16px 28px', 'important');
      panel.style.setProperty('border-radius', '0', 'important');
      return;
    }

    panel.style.setProperty('z-index', '2147483230', 'important');
    panel.style.setProperty('max-height', Math.max(160, viewportH - top - 10) + 'px', 'important');

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
    root.style.setProperty('--tb4-v213-popup-right', right + 'px');
    panel.style.setProperty('right', right + 'px', 'important');
    panel.style.setProperty('left', 'auto', 'important');
    panel.style.setProperty('min-width', type === 'user' ? '220px' : '320px', 'important');
    panel.style.setProperty('width', type === 'user' ? 'max-content' : 'min(380px, calc(100vw - 40px))', 'important');
    panel.style.setProperty('max-width', type === 'user' ? 'min(320px, calc(100vw - 40px))' : 'min(380px, calc(100vw - 40px))', 'important');
  }

  function openPanel(trigger, panel, type) {
    if (!trigger || !panel) { return; }
    moveToBody(panel);
    closeAll(panel);
    addClass(panel, 'is-open');
    panel.style.display = 'block';
    setHidden(panel, false);
    activePanel = panel;
    trigger.setAttribute('aria-expanded', 'true');
    applyPanelPosition(trigger, panel, type);

    if (type === 'mobile') {
      addClass(body, 'tb4-menu-open');
      qsa('#mobileMenuToggle span').forEach(function (span, index) {
        if (index === 0) { span.style.transform = 'rotate(45deg) translate(5px, 5px)'; }
        if (index === 1) { span.style.opacity = '0'; }
        if (index === 2) { span.style.transform = 'rotate(-45deg) translate(5px, -5px)'; }
      });
    }
  }

  function togglePanel(trigger, panel, type) {
    if (!panel) { return; }
    if (hasClass(panel, 'is-open')) {
      closePanel(panel);
    } else {
      openPanel(trigger, panel, type);
    }
  }

  function realignOpenPanels() {
    var pairs = [
      ['tb4NotificationTrigger', 'tb4NotificationPanel', 'notification'],
      ['tb4MessageTrigger', 'tb4MessagePanel', 'message'],
      ['userMenuTrigger', 'userMenuDropdown', 'user'],
      ['mobileMenuToggle', 'mobileMenuOverlay', 'mobile']
    ];
    pairs.forEach(function (pair) {
      var trigger = document.getElementById(pair[0]);
      var panel = document.getElementById(pair[1]);
      if (!trigger || !panel || !hasClass(panel, 'is-open')) { return; }
      if (pair[2] === 'mobile' && window.innerWidth >= 1025) {
        closePanel(panel);
      } else {
        applyPanelPosition(trigger, panel, pair[2]);
      }
    });
  }

  function bindTrigger(triggerId, panelId, type) {
    var trigger = document.getElementById(triggerId);
    var panel = document.getElementById(panelId);
    if (!trigger || !panel) { return; }

    moveToBody(panel);
    closePanel(panel, false);

    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
      togglePanel(trigger, panel, type);
    }, true);

    trigger.addEventListener('pointerdown', function (event) {
      event.stopPropagation();
      if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
    }, true);

    panel.addEventListener('click', function (event) {
      event.stopPropagation();
    }, true);
  }

  function bindNoticeClose() {
    var buttons = qsa('[data-tb4-dev-notice-close], .tb4-dev-notice__close');
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function (event) {
        var notice = qs('.tb4-dev-notice, .tb4-dev-notice-topmost, [data-tb4-dev-notice="topmost"]');
        event.preventDefault();
        event.stopPropagation();
        addClass(body, 'tb4-dev-notice-dismissed');
        if (notice) { notice.style.display = 'none'; }
        scheduleSync();
      }, true);
    });
  }

  function init() {
    body = document.body;
    syncLayout();
    bindTrigger('tb4NotificationTrigger', 'tb4NotificationPanel', 'notification');
    bindTrigger('tb4MessageTrigger', 'tb4MessagePanel', 'message');
    bindTrigger('userMenuTrigger', 'userMenuDropdown', 'user');
    bindTrigger('mobileMenuToggle', 'mobileMenuOverlay', 'mobile');
    bindNoticeClose();

    document.addEventListener('click', function (event) {
      var target = event.target;
      if (target && target.closest && target.closest('#tb4NotificationTrigger,#tb4MessageTrigger,#userMenuTrigger,#mobileMenuToggle,#tb4NotificationPanel,#tb4MessagePanel,#userMenuDropdown,#mobileMenuOverlay')) {
        return;
      }
      closeAll(null);
    }, true);

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' || event.keyCode === 27) { closeAll(null); }
    });

    ['resize', 'orientationchange', 'load'].forEach(function (eventName) {
      window.addEventListener(eventName, scheduleSync, { passive: true });
    });

    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', scheduleSync, { passive: true });
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
