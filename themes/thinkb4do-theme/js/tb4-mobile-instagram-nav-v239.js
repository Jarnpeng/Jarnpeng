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
