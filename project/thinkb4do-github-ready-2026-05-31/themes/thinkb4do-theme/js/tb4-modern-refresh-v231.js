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
