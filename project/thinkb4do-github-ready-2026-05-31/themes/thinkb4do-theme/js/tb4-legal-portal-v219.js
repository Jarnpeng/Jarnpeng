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
