/**
 * Thinkb4do Theme — Main JavaScript
 * Universal UI/UX Stabilization Patch
 * ES5-safe for wider browser, Android WebView, Samsung Internet and in-app browser support.
 * @version 2.0.9
 */
(function (window, document) {
  'use strict';

  var TB4 = {};

  function $(selector, context) {
    return (context || document).querySelector(selector);
  }

  function $all(selector, context) {
    return (context || document).querySelectorAll(selector);
  }

  function each(nodes, callback) {
    if (!nodes) { return; }
    for (var i = 0; i < nodes.length; i += 1) {
      callback(nodes[i], i);
    }
  }

  function hasClass(el, className) {
    if (!el) { return false; }
    if (el.classList) { return el.classList.contains(className); }
    return (' ' + el.className + ' ').indexOf(' ' + className + ' ') > -1;
  }

  function addClass(el, className) {
    if (!el || hasClass(el, className)) { return; }
    if (el.classList) { el.classList.add(className); }
    else { el.className = (el.className ? el.className + ' ' : '') + className; }
  }

  function removeClass(el, className) {
    if (!el) { return; }
    if (el.classList) { el.classList.remove(className); }
    else { el.className = (' ' + el.className + ' ').replace(' ' + className + ' ', ' ').replace(/^\s+|\s+$/g, ''); }
  }

  function toggleClass(el, className, force) {
    if (!el) { return; }
    if (force) { addClass(el, className); }
    else { removeClass(el, className); }
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
    if (!el || el.nodeType !== 1) { return false; }
    var p = (window.Element && window.Element.prototype) ? window.Element.prototype : {};
    var fn = el.matches || el.webkitMatchesSelector || el.msMatchesSelector || el.mozMatchesSelector || p.matches || p.webkitMatchesSelector || p.msMatchesSelector || p.mozMatchesSelector;
    if (fn) { return fn.call(el, selector); }
    var nodes = (el.parentNode || document).querySelectorAll(selector);
    for (var i = 0; i < nodes.length; i += 1) {
      if (nodes[i] === el) { return true; }
    }
    return false;
  }

  function on(el, eventName, handler) {
    if (!el) { return; }
    el.addEventListener(eventName, handler, false);
  }

  function setExpanded(trigger, open) {
    if (trigger) { trigger.setAttribute('aria-expanded', open ? 'true' : 'false'); }
  }

  function setHidden(panel, hidden) {
    if (!panel) { return; }
    panel.setAttribute('aria-hidden', hidden ? 'true' : 'false');
    if (hidden) { panel.setAttribute('hidden', 'hidden'); }
    else { panel.removeAttribute('hidden'); }
  }

  function htmlEscape(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function getFocusable(container) {
    if (!container) { return []; }
    var nodes = container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');
    var list = [];
    each(nodes, function (node) {
      if (node.offsetWidth || node.offsetHeight || node === document.activeElement) {
        list.push(node);
      }
    });
    return list;
  }

  function trapFocus(event, container, closeCallback) {
    if (event.key === 'Escape' || event.keyCode === 27) {
      if (closeCallback) { closeCallback(); }
      return;
    }
    if (event.key !== 'Tab' && event.keyCode !== 9) { return; }

    var focusable = getFocusable(container);
    if (!focusable.length) { return; }
    var first = focusable[0];
    var last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  function ajaxJson(url, done, fail) {
    if (window.fetch && window.Promise) {
      window.fetch(url, { credentials: 'same-origin' })
        .then(function (response) { return response.json(); })
        .then(done)
        .catch(function () { if (fail) { fail(); } });
      return;
    }

    try {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', url, true);
      xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) { return; }
        if (xhr.status >= 200 && xhr.status < 300) {
          try { done(JSON.parse(xhr.responseText)); }
          catch (err) { if (fail) { fail(); } }
        } else if (fail) { fail(); }
      };
      xhr.send(null);
    } catch (err2) {
      if (fail) { fail(); }
    }
  }


  TB4.getWhiteHeaderBottom = function (trigger) {
    var header = document.getElementById('site-header');
    var rect = header && header.getBoundingClientRect ? header.getBoundingClientRect() : null;
    var viewportH = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
    var top = 0;

    /* v2.1.0: anchor to the bottom of the WHITE #site-header only.
       Do not add tb4-dev-notice / green announcement height. */
    if (rect && rect.bottom > 0) {
      top = Math.round(rect.bottom);
    }

    if (!top && trigger && trigger.getBoundingClientRect) {
      rect = trigger.getBoundingClientRect();
      top = Math.round(rect.bottom + 8);
    }

    if (!top) {
      top = (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--tb4-sticky-top'), 10) || 0) +
            (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--tb4-header-real-h'), 10) ||
             parseInt(getComputedStyle(document.documentElement).getPropertyValue('--tb4-v172-header-h'), 10) || 72);
    }

    top = Math.max(0, Math.min(top, Math.max(72, viewportH - 160)));
    document.documentElement.style.setProperty('--tb4-white-header-bottom', top + 'px');
    document.documentElement.style.setProperty('--tb4-popup-top', top + 'px');
    document.documentElement.style.setProperty('--tb4-mobile-menu-top', top + 'px');
    document.documentElement.style.setProperty('--tb4-search-popup-top', top + 'px');
    document.documentElement.style.setProperty('--tb4-header-popup-lock-top', top + 'px');
    document.documentElement.style.setProperty('--tb4-popup-anchor-top', top + 'px');
    return top;
  };

  TB4.alignHeaderPopup = function (trigger, panel, type) {
    var rect;
    var top;
    var right;
    var left;
    var width;
    var gap = 2;
    var viewportW = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
    var viewportH = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
    var mobile = viewportW <= 1024;

    if (!trigger || !panel || !trigger.getBoundingClientRect) { return; }
    rect = trigger.getBoundingClientRect();

    /* v2.1.0: user + notification dropdowns must sit directly under the WHITE header.
       They must cover the green development notice, not start below it. */
    if (type === 'user' || type === 'notification') {
      top = TB4.getWhiteHeaderBottom(trigger);
      panel.style.setProperty('position', 'fixed', 'important');
      panel.style.setProperty('top', top + 'px', 'important');
      panel.style.setProperty('--tb4-popup-top', top + 'px');
      panel.style.setProperty('--tb4-white-header-bottom', top + 'px');
      panel.style.setProperty('transform', 'none', 'important');
      panel.style.setProperty('margin-top', '0px', 'important');
      panel.style.setProperty('z-index', '2147483001', 'important');
      panel.style.setProperty('visibility', 'visible', 'important');
      panel.style.setProperty('opacity', '1', 'important');
      panel.style.setProperty('pointer-events', 'auto', 'important');
      panel.style.setProperty('overflow-y', 'auto', 'important');
      panel.style.setProperty('overflow-x', 'hidden', 'important');
      panel.style.setProperty('max-height', Math.max(160, viewportH - top - 8) + 'px', 'important');

      if (mobile) {
        panel.style.setProperty('left', '12px', 'important');
        panel.style.setProperty('right', '12px', 'important');
        panel.style.setProperty('width', 'auto', 'important');
        panel.style.setProperty('min-width', '0', 'important');
        panel.style.setProperty('max-width', 'calc(100vw - 24px)', 'important');
        panel.style.setProperty('border-radius', '0 0 22px 22px', 'important');
      } else {
        right = Math.max(10, Math.round(viewportW - rect.right));
        panel.style.setProperty('right', right + 'px', 'important');
        panel.style.setProperty('left', 'auto', 'important');
        panel.style.setProperty('width', type === 'notification' ? 'min(360px, calc(100vw - 20px))' : 'max-content', 'important');
        panel.style.setProperty('max-width', type === 'notification' ? 'min(360px, calc(100vw - 20px))' : 'min(300px, calc(100vw - 20px))', 'important');
        document.documentElement.style.setProperty('--tb4-popup-right', right + 'px');
      }
      return;
    }

    /* Search remains fixed because the result panel lives outside the header element. */
    top = Math.round(Math.max(0, rect.bottom) + gap);
    if (viewportH && top > viewportH - 80) { top = Math.max(0, Math.round(rect.bottom + gap)); }

    document.documentElement.style.setProperty('--tb4-header-popup-lock-top', top + 'px');
    document.documentElement.style.setProperty('--tb4-popup-anchor-top', top + 'px');
    panel.style.setProperty('position', 'fixed', 'important');
    panel.style.setProperty('top', top + 'px', 'important');
    panel.style.setProperty('--tb4-popup-top', top + 'px');
    panel.style.setProperty('--tb4-popup-anchor-top', top + 'px');
    panel.style.setProperty('margin-top', '0px', 'important');
    panel.style.setProperty('z-index', '100120', 'important');
    panel.style.setProperty('visibility', 'visible', 'important');
    panel.style.setProperty('opacity', '1', 'important');
    panel.style.setProperty('max-height', 'calc(100vh - ' + top + 'px - 8px)', 'important');
    panel.style.setProperty('overflow-y', 'auto', 'important');

    if (type === 'search') {
      left = Math.max(10, Math.round(rect.left));
      width = Math.min(Math.max(Math.round(rect.width), 280), viewportW - 20);
      if (left + width > viewportW - 10) { left = Math.max(10, viewportW - width - 10); }
      panel.style.setProperty('left', left + 'px', 'important');
      panel.style.setProperty('right', 'auto', 'important');
      panel.style.setProperty('width', width + 'px', 'important');
      panel.style.setProperty('transform', 'none', 'important');
      panel.style.setProperty('--tb4-search-popup-top', top + 'px');
      panel.style.setProperty('--tb4-search-popup-left', left + 'px');
      panel.style.setProperty('--tb4-search-popup-width', width + 'px');
      return;
    }

    right = Math.max(10, Math.round(viewportW - rect.right));
    panel.style.setProperty('right', right + 'px', 'important');
    panel.style.setProperty('left', 'auto', 'important');
    panel.style.setProperty('transform', 'none', 'important');
    panel.style.setProperty('--tb4-popup-right', right + 'px');
  };

  TB4.alignMobileMenu = function (overlay) {
    var top;
    var viewportH = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
    if (!overlay) { return; }
    top = (TB4.getWhiteHeaderBottom ? TB4.getWhiteHeaderBottom(document.getElementById('mobileMenuToggle')) : 72);
    overlay.style.setProperty('position', 'fixed', 'important');
    overlay.style.setProperty('top', top + 'px', 'important');
    overlay.style.setProperty('inset', top + 'px 0 0 0', 'important');
    overlay.style.setProperty('height', Math.max(180, viewportH - top) + 'px', 'important');
    overlay.style.setProperty('max-height', Math.max(180, viewportH - top) + 'px', 'important');
    overlay.style.setProperty('width', '100vw', 'important');
    overlay.style.setProperty('max-width', '100vw', 'important');
    overlay.style.setProperty('overflow-x', 'hidden', 'important');
    overlay.style.setProperty('overflow-y', 'auto', 'important');
    overlay.style.setProperty('z-index', '2147483000', 'important');
  };

  TB4.mobileMenu = function () {
    var toggle = document.getElementById('mobileMenuToggle');
    var overlay = document.getElementById('mobileMenuOverlay');
    var spans;
    var lastFocus = null;

    if (!toggle || !overlay) { return; }
    spans = overlay ? toggle.querySelectorAll('span') : [];
    setHidden(overlay, true);
    overlay.style.display = 'none';

    function setIcon(open) {
      if (!spans || spans.length < 3) { return; }
      if (open) {
        spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
      } else {
        each(spans, function (span) {
          span.style.transform = '';
          span.style.opacity = '';
        });
      }
    }

    function openMenu() {
      lastFocus = document.activeElement;
      if (window.TB4 && typeof window.TB4.alignMobileMenu === 'function') { window.TB4.alignMobileMenu(overlay); }
      overlay.style.display = 'block';
      setHidden(overlay, false);
      addClass(overlay, 'is-open');
      addClass(document.body, 'tb4-menu-open');
      setExpanded(toggle, true);
      setIcon(true);
      window.setTimeout(function () {
        var focusable = getFocusable(overlay);
        if (focusable.length) { focusable[0].focus(); }
      }, 10);
    }

    function closeMenu() {
      overlay.style.display = 'none';
      setHidden(overlay, true);
      removeClass(overlay, 'is-open');
      removeClass(document.body, 'tb4-menu-open');
      setExpanded(toggle, false);
      setIcon(false);
      if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
    }

    function isOpen() {
      return hasClass(overlay, 'is-open') || overlay.style.display === 'block';
    }

    on(toggle, 'click', function (event) {
      event.preventDefault();
      if (isOpen()) { closeMenu(); }
      else { openMenu(); }
    });

    each(overlay.querySelectorAll('a'), function (link) {
      on(link, 'click', closeMenu);
    });

    on(overlay, 'keydown', function (event) { trapFocus(event, overlay, closeMenu); });
    on(window, 'resize', function () {
      var viewportW = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
      if (isOpen() && viewportW >= 1025) { closeMenu(); }
    });
  };

  TB4.userMenu = function () {
    var trigger = document.getElementById('userMenuTrigger');
    var dropdown = document.getElementById('userMenuDropdown');
    if (!trigger || !dropdown) { return; }

    function setOpen(open) {
      dropdown.style.display = open ? 'block' : 'none';
      toggleClass(dropdown, 'is-open', open);
      setExpanded(trigger, open);
      setHidden(dropdown, !open);
      if (open) {
        if (window.TB4 && typeof window.TB4.alignHeaderPopup === 'function') { window.TB4.alignHeaderPopup(trigger, dropdown, 'user'); }
        window.setTimeout(function () {
          if (window.TB4 && typeof window.TB4.alignHeaderPopup === 'function') { window.TB4.alignHeaderPopup(trigger, dropdown, 'user'); }
          var focusable = getFocusable(dropdown);
          if (focusable.length) { focusable[0].focus(); }
        }, 10);
      }
    }

    setOpen(false);

    on(trigger, 'click', function (event) {
      event.stopPropagation();
      setOpen(!hasClass(dropdown, 'is-open'));
    });

    on(dropdown, 'click', function (event) { event.stopPropagation(); });
    on(dropdown, 'keydown', function (event) { trapFocus(event, dropdown, function () { setOpen(false); trigger.focus(); }); });
    on(document, 'click', function () { setOpen(false); });
    on(document, 'keydown', function (event) {
      if (event.key === 'Escape' || event.keyCode === 27) { setOpen(false); }
    });
  };

  TB4.notificationPanel = function () {
    var trigger = document.getElementById('tb4NotificationTrigger');
    var panel = document.getElementById('tb4NotificationPanel');
    if (!trigger || !panel) { return; }

    function setOpen(open) {
      toggleClass(panel, 'is-open', open);
      panel.style.display = open ? 'block' : 'none';
      setHidden(panel, !open);
      setExpanded(trigger, open);
      if (open && window.TB4 && typeof window.TB4.alignHeaderPopup === 'function') { window.TB4.alignHeaderPopup(trigger, panel, 'notification'); }
    }

    setOpen(false);

    on(trigger, 'click', function (event) {
      event.stopPropagation();
      setOpen(!hasClass(panel, 'is-open'));
    });
    on(panel, 'click', function (event) { event.stopPropagation(); });
    on(panel, 'keydown', function (event) { trapFocus(event, panel, function () { setOpen(false); trigger.focus(); }); });
    on(document, 'click', function () { setOpen(false); });
    on(document, 'keydown', function (event) {
      if (event.key === 'Escape' || event.keyCode === 27) { setOpen(false); }
    });
  };

  TB4.messageButton = function () {
    var trigger = document.getElementById('tb4MessageTrigger');
    if (!trigger) { return; }
    on(trigger, 'click', function () {
      window.alert('ระบบข้อความยังอยู่ระหว่างพัฒนา ตอนนี้ปุ่มนี้ใช้เป็นตัวอย่างตำแหน่ง UI ก่อนเชื่อมระบบจริง');
    });
  };

  TB4.searchDropdown = function () {
    var input = document.getElementById('headerSearchInput');
    var results = document.getElementById('searchResults');
    var debounceTimer = null;
    var activeIndex = -1;

    if (!input || !results) { return; }
    setHidden(results, true);

    function hideResults() {
      results.style.display = 'none';
      setHidden(results, true);
      activeIndex = -1;
    }

    function showResults() {
      var searchBox = document.getElementById('headerSearch') || input;
      results.style.display = 'block';
      setHidden(results, false);
      if (window.TB4 && typeof window.TB4.alignHeaderPopup === 'function') { window.TB4.alignHeaderPopup(searchBox, results, 'search'); }
    }

    function setLoading() {
      showResults();
      results.innerHTML = '<div class="tb4-search-state">กำลังค้นหา...</div>';
    }

    function renderFallback(q) {
      var safeQ = htmlEscape(q);
      results.innerHTML = '<a class="tb4-search-item" role="option" href="/?s=' + encodeURIComponent(q) + '"><span class="tb4-search-thumb" aria-hidden="true">🔎</span><span><strong>ค้นหา “' + safeQ + '”</strong><small>ใช้หน้าค้นหาของเว็บไซต์</small></span></a>';
      showResults();
    }

    function renderResults(data, q) {
      var html = '';
      var list = data && data.success && data.data && data.data.length ? data.data : [];
      if (!list.length) {
        results.innerHTML = '<div class="tb4-search-state">ไม่พบผลการค้นหา</div>';
        showResults();
        return;
      }
      for (var i = 0; i < list.length; i += 1) {
        var item = list[i] || {};
        var url = htmlEscape(item.url || '/?s=' + encodeURIComponent(q));
        var title = htmlEscape(item.title || 'ไม่ระบุชื่อ');
        var type = htmlEscape(item.type || 'เนื้อหา');
        var thumb = item.thumb ? '<img src="' + htmlEscape(item.thumb) + '" width="40" height="40" alt="" loading="lazy">' : '<span aria-hidden="true">📄</span>';
        html += '<a class="tb4-search-item" role="option" href="' + url + '"><span class="tb4-search-thumb">' + thumb + '</span><span><strong>' + title + '</strong><small>' + type + '</small></span></a>';
      }
      results.innerHTML = html;
      showResults();
    }

    function updateActive(delta) {
      var options = results.querySelectorAll('[role="option"]');
      if (!options.length) { return; }
      activeIndex += delta;
      if (activeIndex < 0) { activeIndex = options.length - 1; }
      if (activeIndex >= options.length) { activeIndex = 0; }
      each(options, function (option) { removeClass(option, 'is-active'); option.setAttribute('aria-selected', 'false'); });
      addClass(options[activeIndex], 'is-active');
      options[activeIndex].setAttribute('aria-selected', 'true');
      input.setAttribute('aria-activedescendant', 'tb4-search-option-' + activeIndex);
      options[activeIndex].id = 'tb4-search-option-' + activeIndex;
    }

    function doSearch() {
      var q = input.value.replace(/^\s+|\s+$/g, '');
      if (q.length < 2) { hideResults(); return; }
      setLoading();
      if (typeof window.tb4Data !== 'undefined' && window.tb4Data.ajaxUrl) {
        ajaxJson(window.tb4Data.ajaxUrl + '?action=tb4_search&q=' + encodeURIComponent(q) + '&nonce=' + encodeURIComponent(window.tb4Data.nonce || ''), function (data) {
          renderResults(data, q);
        }, function () {
          renderFallback(q);
        });
      } else {
        renderFallback(q);
      }
    }

    on(input, 'input', function () {
      window.clearTimeout(debounceTimer);
      debounceTimer = window.setTimeout(doSearch, 300);
    });

    on(input, 'focus', function () {
      if (input.value.replace(/^\s+|\s+$/g, '').length >= 2) { showResults(); }
    });

    on(document, 'click', function (event) {
      if (!closest(event.target, '#headerSearch') && !closest(event.target, '#searchResults')) { hideResults(); }
    });

    on(input, 'keydown', function (event) {
      var key = event.key || event.keyCode;
      if (key === 'Escape' || key === 27) { hideResults(); input.blur(); return; }
      if (key === 'ArrowDown' || key === 40) { event.preventDefault(); updateActive(1); return; }
      if (key === 'ArrowUp' || key === 38) { event.preventDefault(); updateActive(-1); return; }
      if (key === 'Enter' || key === 13) {
        var active = results.querySelector('.tb4-search-item.is-active');
        if (active && active.href) { window.location.href = active.href; }
        else { window.location.href = '/?s=' + encodeURIComponent(input.value.replace(/^\s+|\s+$/g, '')); }
      }
    });
  };

  TB4.affiliateTabs = function () {
    var tabs = document.querySelectorAll('.affiliate-tab');
    each(tabs, function (tab) {
      on(tab, 'click', function () {
        each(tabs, function (item) { removeClass(item, 'active'); });
        addClass(tab, 'active');
      });
    });
  };


  TB4.devNotice = function () {
    var body = document.body;
    var notice = document.querySelector('.tb4-dev-notice[data-tb4-dev-notice="topmost"]');
    var closeBtn = document.querySelector('[data-tb4-dev-notice-close]');
    var storageKey = 'tb4_dev_notice_closed_v172';

    function getTextKey() {
      if (!notice) { return storageKey; }
      return storageKey + '_' + encodeURIComponent((notice.textContent || '').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '').slice(0, 80));
    }

    function safeGet(key) {
      try { return window.localStorage ? window.localStorage.getItem(key) : null; }
      catch (e) { return null; }
    }

    function safeSet(key, value) {
      try { if (window.localStorage) { window.localStorage.setItem(key, value); } }
      catch (e) {}
    }

    function hideNotice(persist) {
      if (!notice) { return; }
      notice.style.display = 'none';
      notice.setAttribute('aria-hidden', 'true');
      addClass(body, 'tb4-dev-notice-dismissed');
      if (persist) { safeSet(getTextKey(), 'yes'); }
      if (window.TB4 && typeof window.TB4.syncStickyOffset === 'function') {
        window.setTimeout(function () { window.TB4.syncStickyOffset(); }, 30);
      }
      try { window.dispatchEvent(new Event('resize')); }
      catch (e) {}
    }

    if (!notice) { return; }
    if (safeGet(getTextKey()) === 'yes') {
      hideNotice(false);
      return;
    }
    if (closeBtn) {
      on(closeBtn, 'click', function () { hideNotice(true); });
    }
  };

  TB4.syncStickyOffset = function () {
    var root = document.documentElement;
    var body = document.body;
    var header = document.getElementById('site-header');
    var adminBar = document.getElementById('wpadminbar');
    var devNotice = null;

    function update() {
      adminBar = document.getElementById('wpadminbar');
      devNotice = document.querySelector('.tb4-dev-notice');
      var viewportW = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
      var sw = window.screen && window.screen.width ? window.screen.width : viewportW;
      var sh = window.screen && window.screen.height ? window.screen.height : window.innerHeight;
      var screenMin = Math.min(sw || viewportW || 0, sh || window.innerHeight || 0);
      var isTouch = ('ontouchstart' in window || (navigator.maxTouchPoints && navigator.maxTouchPoints > 0));
      /* v2.0.1: do not use an old tb4-actual-mobile class as the source of truth.
         It made the theme stay shrunk/mobile after resizing back to desktop. */
      var physicalMobile = !!(isTouch && screenMin && screenMin <= 820);
      var viewportMobile = viewportW <= 1024;
      var actualMobile = !!(physicalMobile && viewportMobile);
      var adminVisible = adminBar && window.getComputedStyle && window.getComputedStyle(adminBar).display !== 'none' && window.getComputedStyle(adminBar).visibility !== 'hidden';
      var adminHeight = adminVisible ? Math.round(adminBar.getBoundingClientRect().height || adminBar.offsetHeight || 0) : 0;
      var headerHeight = header ? Math.round(header.getBoundingClientRect().height || header.offsetHeight || 64) : 64;
      var noticeVisible = devNotice && window.getComputedStyle && window.getComputedStyle(devNotice).display !== 'none' && window.getComputedStyle(devNotice).visibility !== 'hidden';
      var noticeHeight = noticeVisible ? Math.round(devNotice.getBoundingClientRect().height || devNotice.offsetHeight || 0) : 0;
      var ua = navigator.userAgent || '';
      var androidLike = /Android|SamsungBrowser|wv\)/i.test(ua);
      var desktopViewportOnPhone = actualMobile && screenMin && viewportW > (screenMin * 1.25);
      var loggedIn = window.tb4Data && tb4Data.isLoggedIn === 'yes';
      var adminMissing = loggedIn && (!adminBar || !adminVisible || adminHeight < 12);
      var overlay = document.getElementById('mobileMenuOverlay');
      var toggle = document.getElementById('mobileMenuToggle');

      toggleClass(root, 'tb4-device-mobile', !!physicalMobile);
      toggleClass(root, 'tb4-actual-mobile', !!actualMobile);
      toggleClass(root, 'tb4-viewport-mobile', !!viewportMobile);
      toggleClass(root, 'tb4-viewport-desktop', !viewportMobile);
      root.style.setProperty('--tb4-sticky-top', adminHeight + 'px');
      root.style.setProperty('--tb4-header-real-h', headerHeight + 'px');
      root.style.setProperty('--tb4-dev-notice-real-h', noticeHeight + 'px');
      root.style.setProperty('--tb4-fixed-header-stack-h', (headerHeight + noticeHeight) + 'px');
      root.style.setProperty('--tb4-live-vw', viewportW + 'px');
      addClass(body, 'tb4-header-safe-ready');
      addClass(body, 'tb4-force-fixed-header-ready');
      toggleClass(root, 'tb4-desktop-mode-on-phone', !!desktopViewportOnPhone);
      toggleClass(body, 'tb4-mobile-sticky-fixed', !!(actualMobile && (androidLike || desktopViewportOnPhone)));
      toggleClass(body, 'tb4-wp-adminbar-missing', !!adminMissing);

      if (viewportW >= 1025 && overlay) {
        removeClass(overlay, 'is-open');
        removeClass(body, 'tb4-menu-open');
        overlay.setAttribute('hidden', 'hidden');
        overlay.setAttribute('aria-hidden', 'true');
        overlay.style.removeProperty('display');
        overlay.style.removeProperty('position');
        overlay.style.removeProperty('top');
        overlay.style.removeProperty('left');
        overlay.style.removeProperty('right');
        overlay.style.removeProperty('bottom');
        overlay.style.removeProperty('inset');
        overlay.style.removeProperty('height');
        overlay.style.removeProperty('max-height');
        overlay.style.removeProperty('overflow-y');
        overlay.style.removeProperty('z-index');
        overlay.style.removeProperty('background');
        overlay.style.removeProperty('visibility');
        overlay.style.removeProperty('opacity');
        overlay.style.removeProperty('pointer-events');
        if (toggle) { setExpanded(toggle, false); }
      }
    }

    update();
    on(window, 'resize', update);
    on(window, 'load', update);
    on(window, 'orientationchange', function () { window.setTimeout(update, 140); });
    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', update, false);
    }
  };

  TB4.stickyHeader = function () {
    var header = document.getElementById('site-header');
    if (!header) { return; }
    function update() {
      if ((window.pageYOffset || document.documentElement.scrollTop || 0) > 10) {
        header.style.boxShadow = 'var(--shadow-md)';
      } else {
        header.style.boxShadow = '';
      }
    }
    on(window, 'scroll', update);
    update();
  };

  TB4.animateOnScroll = function () {
    var els = document.querySelectorAll('.card, .product-card, .post-card, .aff-card, .stat-item, .platform-feature');
    if (!('IntersectionObserver' in window)) {
      each(els, function (el) { addClass(el, 'tb4-visible'); });
      return;
    }
    var observer = new IntersectionObserver(function (entries) {
      for (var i = 0; i < entries.length; i += 1) {
        if (entries[i].isIntersecting) {
          entries[i].target.style.animation = 'fadeInUp .5s var(--ease) both';
          observer.unobserve(entries[i].target);
        }
      }
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    each(els, function (el) { observer.observe(el); });
  };



  TB4.headerClickSafety = function () {
    var header = document.getElementById('site-header');
    if (!header) { return; }

    function syncSoon() {
      if (window.TB4 && typeof window.TB4.syncStickyOffset === 'function') {
        window.setTimeout(function () { window.TB4.syncStickyOffset(); }, 20);
      }
    }

    function stopOnlyInsidePanel(event) {
      var panel = closest(event.target, '.tb4-notification-panel, #userMenuDropdown, #searchResults, #mobileMenuOverlay');
      if (panel) { event.stopPropagation(); }
    }

    function alignOpenPanels() {
      var userTrigger = document.getElementById('userMenuTrigger');
      var userPanel = document.getElementById('userMenuDropdown');
      var notifTrigger = document.getElementById('tb4NotificationTrigger');
      var notifPanel = document.getElementById('tb4NotificationPanel');
      var searchInput = document.getElementById('headerSearch') || document.getElementById('headerSearchInput');
      var searchPanel = document.getElementById('searchResults');
      var mobilePanel = document.getElementById('mobileMenuOverlay');
      if (userPanel && hasClass(userPanel, 'is-open') && window.TB4.alignHeaderPopup) { window.TB4.alignHeaderPopup(userTrigger, userPanel, 'user'); }
      if (notifPanel && hasClass(notifPanel, 'is-open') && window.TB4.alignHeaderPopup) { window.TB4.alignHeaderPopup(notifTrigger, notifPanel, 'notification'); }
      if (searchPanel && !searchPanel.hidden && window.TB4.alignHeaderPopup) { window.TB4.alignHeaderPopup(searchInput, searchPanel, 'search'); }
      if (mobilePanel && hasClass(mobilePanel, 'is-open') && window.TB4.alignMobileMenu) { window.TB4.alignMobileMenu(mobilePanel); }
    }

    on(header, 'pointerdown', syncSoon);
    on(header, 'touchstart', syncSoon);
    on(document, 'click', stopOnlyInsidePanel);
    on(window, 'resize', alignOpenPanels);
    on(window, 'scroll', alignOpenPanels);
    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', alignOpenPanels, false);
      window.visualViewport.addEventListener('scroll', alignOpenPanels, false);
    }
  };

  TB4.keyboardShortcuts = function () {
    on(document, 'keydown', function (event) {
      var key = event.key || event.keyCode;
      if ((event.ctrlKey || event.metaKey) && (key === 'k' || key === 'K' || key === 75)) {
        var input = document.getElementById('headerSearchInput');
        if (input) { event.preventDefault(); input.focus(); }
      }
    });
  };

  TB4.newsletter = function () {
    var btns = document.querySelectorAll('.footer-newsletter button');
    each(btns, function (btn) {
      on(btn, 'click', function () {
        var input = btn.previousElementSibling;
        var email;
        if (!input) { return; }
        email = input.value.replace(/^\s+|\s+$/g, '');
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          input.style.borderColor = '#ef4444';
          window.setTimeout(function () { input.style.borderColor = ''; }, 2000);
          return;
        }
        btn.innerHTML = '✓';
        btn.style.background = '#22c55e';
        input.value = '';
        input.placeholder = 'ขอบคุณ! เราจะส่งอัปเดตให้คุณ';
      });
    });
  };

  TB4.overflowGuard = function () {
    function mark() {
      var root = document.documentElement;
      var viewport = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
      var selectors = '.container, .site-main, .entry-content, main, article, section, img, video, iframe, table, pre, .tb4-404-page, .tb4-404-container, .tb4-404-copy, .tb4-404-actions, .tb4-404-grid, .tb4-premium-hero-grid, .products-grid, .posts-grid';
      var nodes = document.querySelectorAll(selectors);
      if (!viewport) { return; }
      root.style.setProperty('--tb4-live-vw', viewport + 'px');
      each(nodes, function (el) {
        var rect;
        if (!el || !el.getBoundingClientRect) { return; }
        rect = el.getBoundingClientRect();
        if (rect.width > viewport + 2 || rect.right > viewport + 2 || rect.left < -2) {
          addClass(el, 'tb4-overflow-clamp');
        }
      });
    }
    mark();
    on(window, 'load', mark);
    on(window, 'resize', mark);
    on(window, 'orientationchange', function () { window.setTimeout(mark, 180); });
  };

  if (document.readyState === 'loading') {
    on(document, 'DOMContentLoaded', function () { TB4.init(); });
  } else {
    TB4.init();
  }

  window.TB4 = TB4;
})(window, document);
