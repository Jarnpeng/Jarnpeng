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
