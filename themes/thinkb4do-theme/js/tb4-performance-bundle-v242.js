/* Thinkb4do UI JS Bundle v2.4.2 — bundled to reduce HTTP requests and mobile lag. */


/* ===== Source: js/tb4-legal-dropdown-v218.js ===== */

(function () {
  'use strict';

  var DESKTOP_MIN = 1025;

  function isDesktop() {
    return window.matchMedia && window.matchMedia('(min-width: ' + DESKTOP_MIN + 'px)').matches;
  }

  function getMenuItems() {
    return Array.prototype.slice.call(document.querySelectorAll('#site-header .main-nav li.menu-item-has-children'));
  }

  function closeAll(except) {
    getMenuItems().forEach(function (item) {
      if (except && item === except) return;
      item.classList.remove('tb4-submenu-open');
      var link = item.querySelector(':scope > a');
      if (link) link.setAttribute('aria-expanded', 'false');
    });
  }

  function prepareMenu() {
    getMenuItems().forEach(function (item) {
      var link = item.querySelector(':scope > a');
      var submenu = item.querySelector(':scope > .sub-menu');
      if (!link || !submenu) return;
      link.setAttribute('aria-haspopup', 'true');
      link.setAttribute('aria-expanded', item.classList.contains('tb4-submenu-open') ? 'true' : 'false');
      submenu.setAttribute('aria-label', (link.textContent || 'เมนูย่อย').trim());
    });
  }

  document.addEventListener('DOMContentLoaded', prepareMenu, { once: true });

  document.addEventListener('click', function (event) {
    var link = event.target.closest && event.target.closest('#site-header .main-nav li.menu-item-has-children > a');

    if (!link) {
      if (!event.target.closest || !event.target.closest('#site-header .main-nav')) closeAll();
      return;
    }

    if (!isDesktop()) return;

    var item = link.parentElement;
    var submenu = item ? item.querySelector(':scope > .sub-menu') : null;
    if (!item || !submenu) return;

    event.preventDefault();
    event.stopPropagation();

    var willOpen = !item.classList.contains('tb4-submenu-open');
    closeAll(item);
    item.classList.toggle('tb4-submenu-open', willOpen);
    link.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
  }, true);

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeAll();
  });

  window.addEventListener('resize', function () {
    if (!isDesktop()) closeAll();
  }, { passive: true });
})();




/* ===== Source: js/tb4-header-master-v215.js ===== */

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




/* ===== Source: js/tb4-legal-portal-v219.js ===== */

(function () {
  'use strict';

  var DESKTOP_MIN = 1025;
  var panel = null;
  var activeTrigger = null;

  var fallbackLinks = [
    ['กฎหมายและนโยบาย', '/legal-center/'],
    ['นโยบายความเป็นส่วนตัว', '/privacy-policy/'],
    ['นโยบายคุกกี้', '/cookie-policy/'],
    ['ข้อกำหนดการใช้งาน', '/terms-of-use/'],
    ['คืนเงิน/ยกเลิกบริการ', '/refund-cancellation-policy/'],
    ['คำขอใช้สิทธิข้อมูลส่วนบุคคล', '/data-rights-request/'],
    ['ติดต่อ/ร้องเรียน', '/contact-complaint/'],
    ['ทรัพย์สินทางปัญญา', '/intellectual-property/'],
    ['ข้อมูลผู้ให้บริการ', '/business-status/']
  ];

  function isDesktop() {
    return window.matchMedia && window.matchMedia('(min-width: ' + DESKTOP_MIN + 'px)').matches;
  }

  function absUrl(path) {
    try { return new URL(path, window.location.origin).href; }
    catch (e) { return path; }
  }

  function textOf(el) {
    return (el && el.textContent ? el.textContent : '').replace(/\s+/g, ' ').trim();
  }

  function isPolicyText(text) {
    return text === 'นโยบาย' || text.indexOf('กฎหมายและนโยบาย') !== -1 || text.indexOf('Policy') !== -1;
  }

  function isPolicyHref(href) {
    return /\/legal-center\/?(?:[#?].*)?$/i.test(href || '') || /legal|policy|privacy|cookie|terms/i.test(href || '');
  }

  function getNav() {
    return document.querySelector('#site-header .main-nav');
  }

  function isInsideSubMenu(link) {
    return !!(link && link.closest && link.closest('.sub-menu'));
  }

  function findPolicyTriggers() {
    var nav = getNav();
    if (!nav) { return []; }

    var links = Array.prototype.slice.call(nav.querySelectorAll('a'));
    var triggers = links.filter(function (link) {
      if (isInsideSubMenu(link)) { return false; }
      var txt = textOf(link);
      var href = link.getAttribute('href') || '';
      var parent = link.parentElement;
      return isPolicyText(txt) || (parent && parent.classList && parent.classList.contains('menu-item-has-children') && isPolicyHref(href));
    });

    // Prefer a top-level link whose visible label is exactly “นโยบาย”.
    triggers.sort(function (a, b) {
      var at = textOf(a) === 'นโยบาย' ? 0 : 1;
      var bt = textOf(b) === 'นโยบาย' ? 0 : 1;
      return at - bt;
    });

    return triggers;
  }

  function getLinksFromNativeSubmenu(trigger) {
    var links = [];
    var parent = trigger && trigger.parentElement;
    var submenu = parent && parent.querySelector ? parent.querySelector(':scope > .sub-menu') : null;

    if (!submenu && parent) {
      // Older browsers may not support :scope reliably.
      var children = Array.prototype.slice.call(parent.children || []);
      submenu = children.filter(function (child) { return child.classList && child.classList.contains('sub-menu'); })[0] || null;
    }

    if (submenu) {
      Array.prototype.slice.call(submenu.querySelectorAll('a')).forEach(function (a) {
        var label = textOf(a);
        var href = a.getAttribute('href') || '';
        if (label && href) { links.push([label, href]); }
      });
    }

    if (!links.length) {
      links = fallbackLinks.map(function (item) { return [item[0], absUrl(item[1])]; });
    }

    // Make sure the legal center page is available as the first item.
    var hasLegalCenter = links.some(function (item) { return /\/legal-center\/?/.test(item[1]); });
    if (!hasLegalCenter) { links.unshift(['กฎหมายและนโยบาย', absUrl('/legal-center/')]); }

    return links;
  }

  function buildPanel(trigger) {
    if (!panel) {
      panel = document.createElement('div');
      panel.id = 'tb4PolicyPortalDropdown';
      panel.className = 'tb4-policy-portal-dropdown';
      panel.setAttribute('role', 'menu');
      panel.setAttribute('aria-label', 'เมนูนโยบาย');
      panel.setAttribute('aria-hidden', 'true');
      document.body.appendChild(panel);

      panel.addEventListener('click', function (event) {
        event.stopPropagation();
      }, true);
    }

    var links = getLinksFromNativeSubmenu(trigger);
    panel.innerHTML = links.map(function (item) {
      return '<a role="menuitem" href="' + String(item[1]).replace(/"/g, '&quot;') + '">' + String(item[0]).replace(/[&<>]/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;'}[c]; }) + '</a>';
    }).join('');

    return panel;
  }

  function positionPanel(trigger) {
    if (!panel || !trigger) { return; }

    panel.style.display = 'grid';
    var rect = trigger.getBoundingClientRect();
    var width = Math.min(Math.max(panel.offsetWidth || 280, 270), Math.min(360, window.innerWidth - 28));
    panel.style.width = width + 'px';

    var desiredLeft = rect.left + (rect.width / 2) - (width / 2);
    var left = Math.max(14, Math.min(desiredLeft, window.innerWidth - width - 14));
    var top = rect.bottom + 12;
    var arrowLeft = Math.max(18, Math.min(rect.left + rect.width / 2 - left, width - 18));

    panel.style.left = left + 'px';
    panel.style.top = top + 'px';
    panel.style.setProperty('--tb4-policy-arrow-left', arrowLeft + 'px');
  }

  function open(trigger) {
    if (!isDesktop()) { return; }
    activeTrigger = trigger;
    buildPanel(trigger);
    positionPanel(trigger);
    panel.classList.add('is-open');
    panel.setAttribute('aria-hidden', 'false');
    trigger.setAttribute('aria-expanded', 'true');
    if (trigger.parentElement) { trigger.parentElement.classList.add('tb4-policy-trigger-open'); }
  }

  function close() {
    if (activeTrigger) {
      activeTrigger.setAttribute('aria-expanded', 'false');
      if (activeTrigger.parentElement) { activeTrigger.parentElement.classList.remove('tb4-policy-trigger-open'); }
    }
    activeTrigger = null;
    if (panel) {
      panel.classList.remove('is-open');
      panel.setAttribute('aria-hidden', 'true');
      panel.style.display = 'none';
    }
  }

  function toggle(trigger) {
    if (activeTrigger === trigger && panel && panel.classList.contains('is-open')) { close(); }
    else { open(trigger); }
  }

  function markTriggers() {
    findPolicyTriggers().forEach(function (link) {
      link.setAttribute('data-tb4-policy-trigger', 'true');
      link.setAttribute('aria-haspopup', 'menu');
      link.setAttribute('aria-expanded', 'false');
      if (link.parentElement) { link.parentElement.classList.add('tb4-policy-trigger'); }
    });
  }

  function getTriggerFromEvent(event) {
    var target = event.target;
    if (!target || !target.closest) { return null; }
    var link = target.closest('#site-header .main-nav a');
    if (!link || isInsideSubMenu(link)) { return null; }
    if (link.getAttribute('data-tb4-policy-trigger') === 'true') { return link; }

    // Fallback for cache/late menu rendering.
    var txt = textOf(link);
    var href = link.getAttribute('href') || '';
    if (isPolicyText(txt) || isPolicyHref(href)) { return link; }
    return null;
  }

  function bind() {
    markTriggers();

    // Repeat once because some WordPress/admin-bar scripts alter the menu after DOMContentLoaded.
    window.setTimeout(markTriggers, 400);
    window.setTimeout(markTriggers, 1200);

    document.addEventListener('click', function (event) {
      var trigger = getTriggerFromEvent(event);
      if (!trigger) {
        if (panel && event.target && event.target.closest && event.target.closest('#tb4PolicyPortalDropdown')) { return; }
        close();
        return;
      }

      if (!isDesktop()) { return; }

      event.preventDefault();
      event.stopPropagation();
      if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
      toggle(trigger);
    }, true);

    document.addEventListener('pointerdown', function (event) {
      var trigger = getTriggerFromEvent(event);
      if (!trigger || !isDesktop()) { return; }
      event.stopPropagation();
      if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
    }, true);

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' || event.keyCode === 27) { close(); }
    });

    ['resize', 'orientationchange', 'scroll'].forEach(function (name) {
      window.addEventListener(name, function () {
        if (!isDesktop()) { close(); return; }
        if (activeTrigger && panel && panel.classList.contains('is-open')) { positionPanel(activeTrigger); }
      }, { passive: true });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
})();




/* ===== Source: js/tb4-modern-refresh-v231.js ===== */

/* Thinkb4do Modern Refresh v2.3.1 — device/browser layout helpers. */
(function () {
  'use strict';
  var doc = document;
  var root = doc.documentElement;
  var body = doc.body;

  function px(value) { return Math.max(0, Math.round(value || 0)) + 'px'; }

  function setVars() {
    var admin = doc.getElementById('wpadminbar');
    var header = doc.getElementById('site-header');
    var adminH = admin && window.getComputedStyle(admin).display !== 'none' ? admin.getBoundingClientRect().height : 0;
    var headerH = header ? header.getBoundingClientRect().height : 0;
    root.style.setProperty('--tb4-adminbar-h', px(adminH));
    root.style.setProperty('--tb4-wp-adminbar-h', px(adminH));
    root.style.setProperty('--tb4-header-real-h', px(headerH || 68));
    root.style.setProperty('--tb4-vh', (window.innerHeight * 0.01) + 'px');
    if (body) {
      body.classList.toggle('tb4-has-adminbar', adminH > 0);
      body.classList.toggle('tb4-small-screen', window.innerWidth < 760);
      body.classList.toggle('tb4-touch-device', ('ontouchstart' in window) || navigator.maxTouchPoints > 0);
    }
  }

  function browserClass() {
    var ua = navigator.userAgent || '';
    var cls = 'tb4-browser-other';
    if (/Edg\//.test(ua)) cls = 'tb4-browser-edge';
    else if (/Chrome\//.test(ua) && !/Edg\//.test(ua)) cls = 'tb4-browser-chrome';
    else if (/Safari\//.test(ua) && !/Chrome\//.test(ua)) cls = 'tb4-browser-safari';
    else if (/Firefox\//.test(ua)) cls = 'tb4-browser-firefox';
    if (body) body.classList.add(cls);
    if (/wv|WebView|Version\/.*Mobile.*Safari/i.test(ua)) body && body.classList.add('tb4-webview-likely');
  }

  function bindSearchShortcut() {
    doc.addEventListener('keydown', function (event) {
      var key = event.key || '';
      if ((event.ctrlKey || event.metaKey) && key.toLowerCase() === 'k') {
        var input = doc.getElementById('headerSearchInput') || doc.querySelector('.tb4-modern-search input[type="search"]');
        if (input) {
          event.preventDefault();
          input.focus();
        }
      }
    }, false);
  }

  function bindResize() {
    var ticking = false;
    function requestSetVars() {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(function () {
        setVars();
        ticking = false;
      });
    }
    window.addEventListener('resize', requestSetVars, { passive: true });
    window.addEventListener('orientationchange', requestSetVars, { passive: true });
    if (window.visualViewport) {
      window.visualViewport.addEventListener('resize', requestSetVars, { passive: true });
      window.visualViewport.addEventListener('scroll', requestSetVars, { passive: true });
    }
  }

  function init() {
    body = doc.body;
    if (body) body.classList.add('tb4-modern-refresh-active');
    root.classList.remove('no-js');
    root.classList.add('js');
    browserClass();
    setVars();
    bindResize();
    bindSearchShortcut();
    setTimeout(setVars, 250);
    setTimeout(setVars, 900);
  }

  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', init, false);
  } else {
    init();
  }
}());




/* ===== Source: js/tb4-modern-polish-v232.js ===== */

/* Thinkb4do Modern Polish v2.3.2 — layout position and viewport guards */
(function () {
  'use strict';

  var root = document.documentElement;

  function px(value) {
    return Math.max(0, Math.round(value || 0)) + 'px';
  }

  function setLayoutVars() {
    var admin = document.getElementById('wpadminbar');
    var header = document.getElementById('site-header');
    var nav = document.querySelector('.tb4-mobile-app-nav');
    var adminHeight = admin ? admin.getBoundingClientRect().height : 0;
    var headerHeight = header ? header.getBoundingClientRect().height : 66;
    var navHeight = nav && window.getComputedStyle(nav).display !== 'none' ? nav.getBoundingClientRect().height : 0;

    root.style.setProperty('--tb4-adminbar-h', px(adminHeight));
    root.style.setProperty('--tb4-header-real-h', px(headerHeight));
    root.style.setProperty('--tb4-mobile-nav-h', px(navHeight));
    root.style.setProperty('--tb4-vh', (window.innerHeight * 0.01) + 'px');

    document.body.classList.toggle('tb4-has-adminbar', !!adminHeight);
    document.body.classList.toggle('tb4-has-mobile-bottom-nav', !!navHeight);
  }

  function addDeviceClasses() {
    var w = window.innerWidth || root.clientWidth;
    document.body.classList.toggle('tb4-device-compact', w < 681);
    document.body.classList.toggle('tb4-device-tablet', w >= 681 && w < 1025);
    document.body.classList.toggle('tb4-device-wide', w >= 1280);
  }

  function refresh() {
    setLayoutVars();
    addDeviceClasses();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', refresh);
  } else {
    refresh();
  }

  window.addEventListener('load', refresh, { passive: true });
  window.addEventListener('resize', refresh, { passive: true });
  window.addEventListener('orientationchange', function () { setTimeout(refresh, 120); }, { passive: true });

  if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', refresh, { passive: true });
    window.visualViewport.addEventListener('scroll', refresh, { passive: true });
  }
})();




/* ===== Source: js/tb4-logo-visibility-v234.js ===== */

/* Thinkb4do Logo Visibility v2.3.4 */
(function () {
  'use strict';

  function parseRGB(value) {
    if (!value || value === 'transparent') return null;
    var match = value.match(/rgba?\(([^)]+)\)/i);
    if (!match) return null;
    var parts = match[1].split(',').map(function (part) { return parseFloat(part.trim()); });
    if (parts.length < 3) return null;
    return {
      r: parts[0],
      g: parts[1],
      b: parts[2],
      a: parts.length > 3 ? parts[3] : 1
    };
  }

  function luminance(color) {
    function channel(v) {
      v = v / 255;
      return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    }
    return (0.2126 * channel(color.r)) + (0.7152 * channel(color.g)) + (0.0722 * channel(color.b));
  }

  function readBackground(el) {
    var current = el;
    var depth = 0;
    while (current && current !== document.documentElement && depth < 8) {
      var style = window.getComputedStyle(current);
      var bg = parseRGB(style.backgroundColor);
      if (bg && bg.a > 0.12) {
        // Blend semi-transparent backgrounds against white for a safe fallback.
        if (bg.a < 1) {
          bg = {
            r: (bg.r * bg.a) + (255 * (1 - bg.a)),
            g: (bg.g * bg.a) + (255 * (1 - bg.a)),
            b: (bg.b * bg.a) + (255 * (1 - bg.a)),
            a: 1
          };
        }
        return bg;
      }
      current = current.parentElement;
      depth += 1;
    }
    return { r: 255, g: 255, b: 255, a: 1 };
  }

  function updateLogo(logo) {
    if (!logo || logo.getAttribute('data-logo-mode') !== 'auto') return;
    var surface = logo.closest('#site-header, #site-footer, header, footer, .site-header, .site-footer') || logo.parentElement || document.body;
    var bg = readBackground(surface);
    var isDark = luminance(bg) < 0.42;
    logo.classList.toggle('tb4-logo-bg-dark', isDark);
    logo.classList.toggle('tb4-logo-bg-light', !isDark);
  }

  var ticking = false;
  function updateAll() {
    ticking = false;
    var logos = document.querySelectorAll('.site-logo[data-logo-mode="auto"]');
    for (var i = 0; i < logos.length; i += 1) updateLogo(logos[i]);
  }

  function requestUpdate() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame ? window.requestAnimationFrame(updateAll) : setTimeout(updateAll, 16);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateAll);
  } else {
    updateAll();
  }
  window.addEventListener('load', updateAll, { passive: true });
  window.addEventListener('resize', requestUpdate, { passive: true });
  window.addEventListener('orientationchange', requestUpdate, { passive: true });
  window.addEventListener('scroll', requestUpdate, { passive: true });
})();




/* ===== Source: js/tb4-app-grid-menu-v238.js ===== */

(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  ready(function () {
    var trigger = document.getElementById('tb4AppGridTrigger');
    var panel = document.getElementById('tb4AppGridPanel');
    var closeBtn = document.getElementById('tb4AppGridClose');
    var root = document.documentElement;

    if (!trigger || !panel) {
      return;
    }

    function getPanelWidth() {
      var previousHidden = panel.hidden;
      var previousVisibility = panel.style.visibility;
      var previousDisplay = panel.style.display;

      if (previousHidden) {
        panel.hidden = false;
        panel.style.visibility = 'hidden';
        panel.style.display = 'block';
      }

      var width = Math.ceil(panel.getBoundingClientRect().width || 390);

      if (previousHidden) {
        panel.hidden = true;
        panel.style.visibility = previousVisibility;
        panel.style.display = previousDisplay;
      }

      return width;
    }

    function positionPanel() {
      var viewportW = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
      var rect = trigger.getBoundingClientRect();
      var gap = viewportW <= 782 ? 8 : 10;
      var edge = viewportW <= 430 ? 8 : 12;
      var panelW = getPanelWidth();

      if (viewportW <= 782) {
        root.style.setProperty('--tb4-app-grid-panel-left', Math.max(edge, 8) + 'px');
        root.style.setProperty('--tb4-app-grid-panel-top', Math.round(rect.bottom + gap) + 'px');
        return;
      }

      // Prefer right-aligning the panel to the 9-square button so the popup
      // stays visually connected to the trigger even when account/notice
      // controls exist on the right side of the header.
      var preferredLeft = Math.round(rect.right - panelW);
      var maxLeft = viewportW - panelW - edge;
      var left = Math.min(Math.max(preferredLeft, edge), maxLeft);
      var top = Math.round(rect.bottom + gap);

      root.style.setProperty('--tb4-app-grid-panel-left', left + 'px');
      root.style.setProperty('--tb4-app-grid-panel-top', top + 'px');
    }

    function closeOtherPanels() {
      var notification = document.getElementById('tb4NotificationPanel');
      var notificationTrigger = document.getElementById('tb4NotificationTrigger');
      var message = document.getElementById('tb4MessagePanel');
      var messageTrigger = document.getElementById('tb4MessageTrigger');

      if (notification) {
        notification.hidden = true;
        notification.classList.remove('is-open');
        notification.setAttribute('aria-hidden', 'true');
      }
      if (notificationTrigger) {
        notificationTrigger.setAttribute('aria-expanded', 'false');
      }
      if (message) {
        message.hidden = true;
        message.classList.remove('is-open');
        message.setAttribute('aria-hidden', 'true');
      }
      if (messageTrigger) {
        messageTrigger.setAttribute('aria-expanded', 'false');
      }
    }

    function setOpen(open) {
      if (open) {
        closeOtherPanels();
        positionPanel();
      }

      trigger.classList.toggle('is-open', open);
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      panel.classList.toggle('is-open', open);
      panel.hidden = !open;
      panel.setAttribute('aria-hidden', open ? 'false' : 'true');

      if (open) {
        // Run one more time after the panel becomes visible so browser zoom,
        // Thai font metrics and scrollbar width do not offset the popup.
        window.requestAnimationFrame(positionPanel);
      }
    }

    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      setOpen(!panel.classList.contains('is-open'));
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        setOpen(false);
        trigger.focus();
      });
    }

    document.addEventListener('click', function (event) {
      if (!panel.classList.contains('is-open')) {
        return;
      }
      if (panel.contains(event.target) || trigger.contains(event.target)) {
        return;
      }
      setOpen(false);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && panel.classList.contains('is-open')) {
        setOpen(false);
        trigger.focus();
      }
    });

    window.addEventListener('resize', function () {
      if (panel.classList.contains('is-open')) {
        positionPanel();
      }
    }, { passive: true });

    window.addEventListener('orientationchange', function () {
      if (panel.classList.contains('is-open')) {
        window.setTimeout(positionPanel, 80);
      }
    }, { passive: true });
  });
})();




/* ===== Source: js/tb4-mobile-instagram-nav-v239.js ===== */

(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  function firstVisibleSearchField() {
    var selectors = [
      '[data-tb4-live-component="search"] input[type="search"]',
      '#headerSearchInput',
      '.site-search input[type="search"]',
      'input[type="search"]'
    ];

    for (var i = 0; i < selectors.length; i += 1) {
      var nodes = document.querySelectorAll(selectors[i]);
      for (var j = 0; j < nodes.length; j += 1) {
        var node = nodes[j];
        var rect = node.getBoundingClientRect();
        var visible = rect.width > 0 && rect.height > 0 && window.getComputedStyle(node).visibility !== 'hidden' && window.getComputedStyle(node).display !== 'none';
        if (visible) {
          return node;
        }
      }
    }
    return null;
  }

  ready(function () {
    var searchButtons = document.querySelectorAll('[data-tb4-focus-search]');
    var appGridButtons = document.querySelectorAll('[data-tb4-app-grid-open]');
    var topTrigger = document.getElementById('tb4AppGridTrigger');

    searchButtons.forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        var field = firstVisibleSearchField();
        if (field) {
          field.scrollIntoView({ behavior: 'smooth', block: 'center' });
          window.setTimeout(function () {
            field.focus({ preventScroll: true });
          }, 160);
        }
      });
    });

    appGridButtons.forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        if (topTrigger) {
          topTrigger.click();
        }
      });
    });
  });
})();





/* Thinkb4do Performance Lite v2.4.2 */
(function () {
  'use strict';

  var doc = document;
  var root = doc.documentElement;
  var idle = window.requestIdleCallback || function (cb) { return window.setTimeout(cb, 80); };
  var cancelIdle = window.cancelIdleCallback || window.clearTimeout;
  var scrollTimer = 0;
  var resizeTimer = 0;

  function ready(fn) {
    if (doc.readyState === 'loading') {
      doc.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  function markScrolling() {
    root.classList.add('tb4-is-scrolling');
    if (scrollTimer) window.clearTimeout(scrollTimer);
    scrollTimer = window.setTimeout(function () {
      root.classList.remove('tb4-is-scrolling');
    }, 140);
  }

  function setLazyMediaAttributes() {
    var images = doc.querySelectorAll('img');
    for (var i = 0; i < images.length; i += 1) {
      var img = images[i];
      if (!img.hasAttribute('loading')) img.setAttribute('loading', i < 2 ? 'eager' : 'lazy');
      if (!img.hasAttribute('decoding')) img.setAttribute('decoding', 'async');
      if (i > 1 && !img.hasAttribute('fetchpriority')) img.setAttribute('fetchpriority', 'low');
    }

    var frames = doc.querySelectorAll('iframe');
    for (var j = 0; j < frames.length; j += 1) {
      if (!frames[j].hasAttribute('loading')) frames[j].setAttribute('loading', 'lazy');
    }

    var videos = doc.querySelectorAll('video');
    for (var k = 0; k < videos.length; k += 1) {
      if (!videos[k].hasAttribute('preload')) videos[k].setAttribute('preload', 'metadata');
      videos[k].setAttribute('playsinline', 'playsinline');
    }
  }

  function observeHeavyBlocks() {
    if (!('IntersectionObserver' in window)) return;
    var blocks = doc.querySelectorAll('.tb4-card, .post-card, .product-card, .widget, .tb4-feed-card, .tb4-community-card, article.post, article.page');
    if (!blocks.length) return;
    var observer = new IntersectionObserver(function (entries) {
      for (var i = 0; i < entries.length; i += 1) {
        if (entries[i].isIntersecting) {
          entries[i].target.classList.add('tb4-in-view');
          observer.unobserve(entries[i].target);
        }
      }
    }, { rootMargin: '220px 0px' });
    for (var j = 0; j < blocks.length; j += 1) observer.observe(blocks[j]);
  }

  function updateViewportVars() {
    if (resizeTimer) window.cancelAnimationFrame(resizeTimer);
    resizeTimer = window.requestAnimationFrame(function () {
      root.style.setProperty('--tb4-vh', (window.innerHeight * 0.01) + 'px');
      root.style.setProperty('--tb4-window-w', window.innerWidth + 'px');
    });
  }

  ready(function () {
    root.classList.add('tb4-perf-ready');
    setLazyMediaAttributes();
    observeHeavyBlocks();
    updateViewportVars();

    window.addEventListener('scroll', markScrolling, { passive: true });
    window.addEventListener('resize', updateViewportVars, { passive: true });
    window.addEventListener('orientationchange', updateViewportVars, { passive: true });

    var idleTask = idle(function () {
      setLazyMediaAttributes();
      cancelIdle(idleTask);
    });
  });
})();
