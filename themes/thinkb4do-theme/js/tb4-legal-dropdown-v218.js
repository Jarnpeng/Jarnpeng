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
