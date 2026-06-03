(function () {
  'use strict';

  var cfg = window.TB4DResponsiveGuard || {};
  if (!cfg.enabled) {
    return;
  }

  var doc = document;
  var docEl = doc.documentElement;
  var body = doc.body;
  var threshold = Number(cfg.threshold || 2);
  var scheduled = false;

  if (!body) {
    return;
  }

  function isSkippable(el) {
    if (!el || !el.tagName) {
      return true;
    }
    var tag = el.tagName.toLowerCase();
    if (tag === 'script' || tag === 'style' || tag === 'noscript' || tag === 'meta' || tag === 'link') {
      return true;
    }
    if (el.closest && el.closest('.wpadminbar, #wpadminbar, .tb4d-skip-overflow-scan')) {
      return true;
    }
    return false;
  }

  function isVisible(el) {
    var style = window.getComputedStyle(el);
    return style.display !== 'none' && style.visibility !== 'hidden' && style.position !== 'fixed';
  }

  function scanOverflow() {
    scheduled = false;
    var viewport = Math.max(docEl.clientWidth || 0, window.innerWidth || 0);
    if (!viewport) {
      return;
    }

    var offenders = [];
    var nodes = body.querySelectorAll('body *');

    nodes.forEach(function (el) {
      if (isSkippable(el) || !isVisible(el)) {
        return;
      }

      var rect = el.getBoundingClientRect();
      if (!rect || rect.width <= 0) {
        return;
      }

      var rightOverflow = rect.right - viewport;
      var leftOverflow = 0 - rect.left;
      if (rightOverflow > threshold || leftOverflow > threshold) {
        el.classList.add('tb4d-overflow-fixed');
        el.setAttribute('data-tb4d-overflow-fixed', '1');
        offenders.push({
          tag: el.tagName.toLowerCase(),
          className: el.className,
          width: Math.round(rect.width),
          rightOverflow: Math.round(rightOverflow),
          leftOverflow: Math.round(leftOverflow)
        });
      }
    });

    body.classList.toggle('tb4d-has-overflow-fixed', offenders.length > 0);

    if (cfg.debug && offenders.length && window.console && console.table) {
      console.groupCollapsed('[Thinkb4do Responsive Guard] fixed overflow elements: ' + offenders.length);
      console.table(offenders.slice(0, 50));
      console.groupEnd();
    }
  }

  function scheduleScan() {
    if (scheduled) {
      return;
    }
    scheduled = true;
    window.requestAnimationFrame(scanOverflow);
  }

  body.classList.add('tb4d-responsive-guard-on');


  function markAdminBarState() {
    var adminbar = doc.getElementById('wpadminbar');
    if (!adminbar) {
      body.classList.remove('tb4d-wp-adminbar-visible');
      docEl.style.removeProperty('--tb4d-adminbar-height');
      return;
    }
    var rect = adminbar.getBoundingClientRect ? adminbar.getBoundingClientRect() : null;
    var height = rect && rect.height ? Math.round(rect.height) : adminbar.offsetHeight;
    if (height > 0) {
      body.classList.add('tb4d-wp-adminbar-visible');
      docEl.style.setProperty('--tb4d-adminbar-height', height + 'px');
    }
  }

  markAdminBarState();

  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', scheduleScan, { once: true });
  } else {
    scheduleScan();
  }

  window.addEventListener('load', scheduleScan, { once: true });
  window.addEventListener('resize', function () {
    markAdminBarState();
    window.clearTimeout(window.__tb4dResponsiveGuardTimer);
    window.__tb4dResponsiveGuardTimer = window.setTimeout(scheduleScan, 160);
  });
})();
