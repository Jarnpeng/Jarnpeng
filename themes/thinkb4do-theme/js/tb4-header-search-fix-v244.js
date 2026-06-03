(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  function trim(value) {
    return String(value || '').replace(/^\s+|\s+$/g, '');
  }

  function homeSearchUrl(query) {
    var base = (window.tb4Data && window.tb4Data.siteUrl) ? window.tb4Data.siteUrl : window.location.origin;
    return base.replace(/\/$/, '') + '/?s=' + encodeURIComponent(query);
  }

  ready(function () {
    var desktopInput = document.getElementById('headerSearchInput');
    var desktopForm = document.getElementById('headerSearch');
    var trigger = document.getElementById('tb4MobileSearchTrigger');
    var panel = document.getElementById('tb4MobileHeaderSearchPanel');
    var closeButton = panel ? panel.querySelector('.tb4-mobile-header-search-close') : null;
    var mobileInput = document.getElementById('tb4MobileHeaderSearchInput');
    var searchForms = document.querySelectorAll('.tb4-header-search-form');

    function openPanel() {
      if (!panel || !trigger) return;
      panel.hidden = false;
      panel.setAttribute('aria-hidden', 'false');
      panel.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
      window.setTimeout(function () {
        if (mobileInput) mobileInput.focus({ preventScroll: true });
      }, 40);
    }

    function closePanel() {
      if (!panel || !trigger) return;
      panel.hidden = true;
      panel.setAttribute('aria-hidden', 'true');
      panel.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
    }

    if (desktopInput) {
      desktopInput.setAttribute('name', 's');
      desktopInput.setAttribute('autocomplete', 'off');
      desktopInput.addEventListener('focus', function () {
        desktopInput.setAttribute('aria-expanded', 'true');
      });
      desktopInput.addEventListener('blur', function () {
        window.setTimeout(function () {
          var results = document.getElementById('searchResults');
          if (!results || results.hidden) desktopInput.setAttribute('aria-expanded', 'false');
        }, 180);
      });
    }

    if (desktopForm && desktopForm.tagName && desktopForm.tagName.toLowerCase() !== 'form') {
      desktopForm.addEventListener('keydown', function (event) {
        var key = event.key || event.keyCode;
        if (key === 'Enter' || key === 13) {
          var q = desktopInput ? trim(desktopInput.value) : '';
          if (q) window.location.href = homeSearchUrl(q);
        }
      });
    }

    for (var i = 0; i < searchForms.length; i += 1) {
      searchForms[i].addEventListener('submit', function (event) {
        var input = this.querySelector('input[type="search"]');
        var q = input ? trim(input.value) : '';
        if (!q) {
          event.preventDefault();
          if (input) input.focus();
          return;
        }
        if (!input.name) input.setAttribute('name', 's');
      });
    }

    if (trigger) {
      trigger.addEventListener('click', function (event) {
        event.preventDefault();
        if (panel && panel.hidden) openPanel(); else closePanel();
      });
    }

    if (closeButton) {
      closeButton.addEventListener('click', function (event) {
        event.preventDefault();
        closePanel();
      });
    }

    document.addEventListener('keydown', function (event) {
      var key = event.key || event.keyCode;
      if ((event.ctrlKey || event.metaKey) && (key === 'k' || key === 'K' || key === 75)) {
        var target = window.matchMedia && window.matchMedia('(max-width: 920px)').matches ? mobileInput : desktopInput;
        event.preventDefault();
        if (target === mobileInput) openPanel();
        if (target) target.focus({ preventScroll: true });
      }
      if (key === 'Escape' || key === 27) closePanel();
    });

    document.addEventListener('click', function (event) {
      if (!panel || panel.hidden) return;
      if (panel.contains(event.target) || (trigger && trigger.contains(event.target))) return;
      closePanel();
    });
  });
})();
