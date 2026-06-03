(function tb4cCommunityCore42126(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommunityCore42126Booted) return;
  window.tb4cCommunityCore42126Booted = true;

  var root = document.documentElement;
  var cfg = window.tb4cCommunity || {};

  function addClass(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function hasSurface() { try { return !!document.querySelector('.tb4c-shell,#tb4c-community-app,.tb4-home-feed-main,[data-tb4c-community-surface]'); } catch (e) { return false; } }
  function isSlowDevice() {
    var nav = window.navigator || {};
    var mem = Number(nav.deviceMemory || 0);
    var cores = Number(nav.hardwareConcurrency || 0);
    var coarse = false;
    try { coarse = !!(window.matchMedia && window.matchMedia('(hover:none),(pointer:coarse)').matches); } catch (e) {}
    return (mem && mem <= 4) || (cores && cores <= 4) || coarse;
  }

  function boot() {
    addClass(document.documentElement, 'tb4c-v42209-force-feed-card');
    addClass(document.documentElement, 'tb4c-v42211-flat-card-audience-media-reels');
    addClass(document.documentElement, 'tb4c-v42212-sharp-media-instagram-reels');
    addClass(document.documentElement, 'tb4c-v42213-no-rounded-media');
    addClass(document.documentElement, 'tb4c-v42214-comment-feed-tone');
    addClass(document.documentElement, 'tb4c-v42215-force-square-media');
    addClass(document.documentElement, 'tb4c-v42216-wide-card');
    addClass(document.documentElement, 'tb4c-v42217-reference-width');
    addClass(document.documentElement, 'tb4c-v42218-wider-inner-card');
    addClass(document.documentElement, 'tb4c-v42219-full-media-inner-wide');
    addClass(document.documentElement, 'tb4c-v42220-reference-comment-system');
    addClass(document.documentElement, 'tb4c-v42210-instagram-feed-width');
    addClass(root, 'tb4c-v42126-core-light');
    addClass(root, 'tb4c-v42126-hard-remove-ui');
    addClass(root, 'tb4c-v42126-ultra-runtime');
    if (!hasSurface()) addClass(root, 'tb4c-runtime-light');
    if (isSlowDevice()) addClass(root, 'tb4c-v42126-runtime-lite');
    if (document.body) {
      addClass(document.body, 'tb4c-v42209-force-feed-card-body');
      addClass(document.body, 'tb4c-v42211-flat-card-audience-media-reels-body');
      addClass(document.body, 'tb4c-v42212-sharp-media-instagram-reels-body');
      addClass(document.body, 'tb4c-v42213-no-rounded-media-body');
      addClass(document.body, 'tb4c-v42214-comment-feed-tone-body');
      addClass(document.body, 'tb4c-v42215-force-square-media-body');
      addClass(document.body, 'tb4c-v42216-wide-card-body');
      addClass(document.body, 'tb4c-v42217-reference-width-body');
      addClass(document.body, 'tb4c-v42218-wider-inner-card-body');
      addClass(document.body, 'tb4c-v42219-full-media-inner-wide-body');
      addClass(document.body, 'tb4c-v42220-reference-comment-system-body');
      addClass(document.body, 'tb4c-v42210-instagram-feed-width-body');
      addClass(document.body, 'tb4c-v42126-hard-remove-ui');
      addClass(document.body, 'tb4c-v42126-ultra-runtime');
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();

  window.tb4cPerf42126 = {
    version: (cfg && cfg.version) || '4.2.126',
    hasSurface: hasSurface,
    isSlowDevice: isSlowDevice
  };
})(window, document);


(function tb4cCommunityCompleteBeauty42150(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommunityCompleteBeauty42150Booted) return;
  window.tb4cCommunityCompleteBeauty42150Booted = true;

  var root = document.documentElement;
  var cfg = window.tb4cCommunity || {};
  var key = 'tb4c_smart_back_url_42150';

  function qs(selector, scope) {
    try { return (scope || document).querySelector(selector); }
    catch (e) { return null; }
  }
  function closest(node, selector) {
    try { return node && node.closest ? node.closest(selector) : null; }
    catch (e) { return null; }
  }
  function addClass(node, cls) {
    try { if (node && node.classList) node.classList.add(cls); } catch (e) {}
  }
  function sameOrigin(url) {
    try { return new URL(url, window.location.href).origin === window.location.origin; }
    catch (e) { return false; }
  }
  function cleanUrl(url) {
    try {
      var parsed = new URL(url, window.location.href);
      parsed.hash = '';
      return parsed.href;
    } catch (e) { return String(url || '').split('#')[0]; }
  }
  function getStoredBackUrl() {
    try {
      var url = window.sessionStorage ? window.sessionStorage.getItem(key) : '';
      if (!url || !sameOrigin(url) || cleanUrl(url) === cleanUrl(window.location.href)) return '';
      return url;
    } catch (e) { return ''; }
  }
  function rememberCurrentUrl() {
    try {
      if (!window.sessionStorage) return;
      var current = cleanUrl(window.location.href);
      if (current) window.sessionStorage.setItem(key, current);
    } catch (e) {}
  }
  function rememberBeforeNavigation(event) {
    var link = closest(event.target, 'a[href]');
    if (!link) return;
    if (link.target && link.target !== '_self') return;
    if (link.hasAttribute('download')) return;
    var href = link.getAttribute('href') || '';
    if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return;
    if (!sameOrigin(href)) return;
    if (cleanUrl(href) === cleanUrl(window.location.href)) return;
    if (!closest(link, '.tb4c-shell,#tb4c-community-app,.tb4-home-feed-main,[data-tb4c-community-surface],.tb4c-mobile-bottom-nav')) return;
    rememberCurrentUrl();
  }
  function createBackButton() {
    if (qs('.tb4c-single-social-page') || qs('.tb4c-back-link')) return;
    var target = qs('[data-tb4c-smart-back-target]');
    var surface = target || qs('.tb4c-social-feed') || qs('') || qs('.tb4c-single-social') || qs('.tb4-home-feed-main') || qs('#tb4c-community-app') || qs('.tb4c-shell');
    if (!surface || qs('[data-tb4c-smart-back="42150"]', surface) || (!target && qs('[data-tb4c-smart-back="42150"]'))) return;

    var wrap = document.createElement('div');
    wrap.className = 'tb4c-smart-back-wrap tb4c-reference-back-wrap';
    wrap.setAttribute('data-tb4c-smart-back', '42150');

    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'tb4c-smart-back';
    button.setAttribute('aria-label', 'ย้อนกลับไปหน้าก่อนหน้า');
    button.innerHTML = '<span aria-hidden="true">‹</span><strong>ย้อนกลับ</strong>';
    button.addEventListener('click', function () {
      var stored = getStoredBackUrl();
      if (stored) {
        window.location.href = stored;
        return;
      }
      if (window.history && window.history.length > 1) {
        window.history.back();
        return;
      }
      window.location.href = cfg.communityUrl || '/community/';
    });
    wrap.appendChild(button);

    try {
      if (surface.firstChild) surface.insertBefore(wrap, surface.firstChild);
      else surface.appendChild(wrap);
    } catch (e) {}
  }
  function boot() {
    addClass(root, 'tb4c-v42150-complete-beauty');
    addClass(document.body, 'tb4c-v42150-complete-beauty');
    createBackButton();
  }

  document.addEventListener('click', rememberBeforeNavigation, true);
  window.addEventListener('pagehide', rememberCurrentUrl, { passive: true });
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);


(function tb4cCommunityCompletePolish42151(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommunityCompletePolish42151Booted) return;
  window.tb4cCommunityCompletePolish42151Booted = true;
  function add(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function boot() {
    add(document.documentElement, 'tb4c-v42151-complete-polish');
    add(document.body, 'tb4c-v42151-complete-polish');
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);


(function tb4cCommunityRefinedLite42152(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommunityRefinedLite42152Booted) return;
  window.tb4cCommunityRefinedLite42152Booted = true;
  function add(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function markSurfaces() {
    try {
      var list = document.querySelectorAll('.tb4c-shell,.tb4c-social-shell,.tb4-home-feed-main,.tb4c-home-menu-feed,#tb4c-community-app,[data-tb4c-community-surface]');
      for (var i = 0; i < list.length; i += 1) add(list[i], 'tb4c-v42152-surface');
    } catch (e) {}
  }
  function boot() {
    add(document.documentElement, 'tb4c-v42152-refined-lite');
    add(document.body, 'tb4c-v42152-refined-lite');
    markSurfaces();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);


(function tb4cCommunityVisibleSocial42153(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommunityVisibleSocial42153Booted) return;
  window.tb4cCommunityVisibleSocial42153Booted = true;
  function add(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function boot() {
    add(document.documentElement, 'tb4c-v42153-visible-social');
    add(document.body, 'tb4c-v42153-visible-social');
    try {
      var surfaces = document.querySelectorAll('.tb4c-shell,.tb4c-social-shell,.tb4-home-feed-main,.tb4c-home-menu-feed,#tb4c-community-app,[data-tb4c-community-surface]');
      for (var i = 0; i < surfaces.length; i += 1) add(surfaces[i], 'tb4c-v42153-surface');
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);


(function tb4cCommunityVisibleSocial42154(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommunityVisibleSocial42154Booted) return;
  window.tb4cCommunityVisibleSocial42154Booted = true;
  function add(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function boot() {
    add(document.documentElement, 'tb4c-v42154-visible-social');
    add(document.body, 'tb4c-v42154-visible-social');
    try {
      var surfaces = document.querySelectorAll('.tb4c-v42154-force-social,.tb4c-shell,.tb4-home-feed-main,.tb4c-home-menu-feed,#tb4c-community-app');
      for (var i = 0; i < surfaces.length; i += 1) add(surfaces[i], 'tb4c-v42154-surface-ready');
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);


(function tb4cCommunityGplusLite42155(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommunityGplusLite42155Booted) return;
  window.tb4cCommunityGplusLite42155Booted = true;
  function add(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function boot() {
    add(document.documentElement, 'tb4c-v42155-gplus-lite-ready');
    add(document.body, 'tb4c-v42155-gplus-lite-body');
    try {
      var surfaces = document.querySelectorAll('.tb4c-v42155-gplus-lite,#tb4c-community-app,.tb4c-shell,.tb4-home-feed-main');
      for (var i = 0; i < surfaces.length; i += 1) add(surfaces[i], 'tb4c-v42155-surface-ready');
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);


(function tb4cCommunityGplusClean42156(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommunityGplusClean42156Booted) return;
  window.tb4cCommunityGplusClean42156Booted = true;
  function add(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function boot() {
    add(document.documentElement, 'tb4c-v42156-gplus-clean-ready');
    add(document.body, 'tb4c-v42156-gplus-clean-body');
    try {
      var surfaces = document.querySelectorAll('.tb4c-v42156-gplus-clean,#tb4c-community-app,.tb4-home-feed-main,.tb4c-home-menu-feed');
      for (var i = 0; i < surfaces.length; i += 1) add(surfaces[i], 'tb4c-v42156-surface-ready');
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);

  // v4.2.157: size-lock marker for pages rendered by cached templates or reused blocks.
  try {
    document.documentElement.classList.add('tb4c-v42157-social-size-lock-ready');
    if (document.body) document.body.classList.add('tb4c-v42157-social-size-lock-body');
    var lockSurfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-social-shell,.tb4c-shell,.tb4-home-feed-main,.tb4c-home-menu-feed');
    for (var s57 = 0; s57 < lockSurfaces.length; s57 += 1) {
      lockSurfaces[s57].classList.add('tb4c-v42157-social-size-lock');
      lockSurfaces[s57].setAttribute('data-ui-version', '4.2.157');
    }
  } catch (e) {}


// v4.2.159: full-bleed social marker. Adds the final layout class even if an older template fragment is cached.
(function tb4cFullBleedSocial42159(window, document) {
  'use strict';
  if (!window || !document || window.tb4cFullBleedSocial42159Booted) return;
  window.tb4cFullBleedSocial42159Booted = true;
  function add(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function boot() {
    add(document.documentElement, 'tb4c-v42159-full-bleed-ready');
    add(document.body, 'tb4c-v42159-full-bleed-body');
    try {
      var surfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-social-shell,.tb4c-shell');
      for (var i = 0; i < surfaces.length; i += 1) {
        add(surfaces[i], 'tb4c-v42159-full-bleed-social');
        surfaces[i].setAttribute('data-ui-version', '4.2.159');
        surfaces[i].setAttribute('data-tb4c-layout', 'full-bleed-social-v42159');
      }
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);

// v4.2.160: Position Rebuild marker. Runs after older markers and wins with the final CSS file.
(function tb4cPositionRebuild42160(window, document) {
  'use strict';
  if (!window || !document || window.tb4cPositionRebuild42160Booted) return;
  window.tb4cPositionRebuild42160Booted = true;
  function add(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function boot() {
    add(document.documentElement, 'tb4c-v42160-position-rebuild-ready');
    add(document.body, 'tb4c-v42160-position-rebuild-body');
    try {
      var surfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-position-social-rebuild');
      for (var i = 0; i < surfaces.length; i += 1) {
        add(surfaces[i], 'tb4c-v42160-position-rebuild');
        surfaces[i].setAttribute('data-ui-version', '4.2.160');
        surfaces[i].setAttribute('data-tb4c-layout', 'position-social-rebuild-v42160');
      }
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);


// v4.2.162: Header Aligned Social marker. Tiny marker only; CSS owns the layout.
(function tb4cHeaderAlign42162(window, document) {
  'use strict';
  if (!window || !document || window.tb4cHeaderAlign42162Booted) return;
  window.tb4cHeaderAlign42162Booted = true;
  function add(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function boot() {
    add(document.documentElement, 'tb4c-v42162-header-align-ready');
    add(document.body, 'tb4c-v42162-header-align-body');
    try {
      var surfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-v42162-header-align,.tb4c-social-shell');
      for (var i = 0; i < surfaces.length; i += 1) {
        add(surfaces[i], 'tb4c-v42162-header-align');
        surfaces[i].setAttribute('data-ui-version', '4.2.162');
        surfaces[i].setAttribute('data-tb4c-layout', 'header-aligned-social-lite-v42162');
      }
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);


// v4.2.167: UI/Icon Clean marker. Keeps the final icon-clean surface active even when a cached template is reused.
(function tb4cUiIconClean42167(window, document) {
  'use strict';
  if (!window || !document || window.tb4cUiIconClean42167Booted) return;
  window.tb4cUiIconClean42167Booted = true;
  function add(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function boot() {
    add(document.documentElement, 'tb4c-v42167-ui-icon-clean-ready');
    if (document.body) add(document.body, 'tb4c-v42167-ui-icon-clean-body');
    try {
      var surfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-social-shell,.tb4c-shell,.tb4c-community-modular');
      for (var i = 0; i < surfaces.length; i += 1) {
        add(surfaces[i], 'tb4c-v42167-ui-icon-clean');
        surfaces[i].setAttribute('data-ui-version', '4.2.167');
        surfaces[i].setAttribute('data-tb4c-layout', 'ui-icon-clean-lite-v42167');
      }
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);

// v4.2.168: Sidebar Transparent marker. Adds the non-layout visual layer even if a cached template omits the new class.
(function(){
  function add(el, cls){ if(el && el.classList && !el.classList.contains(cls)) el.classList.add(cls); }
  function mark(){
    var surfaces = document.querySelectorAll('.tb4c-community-modular, .tb4c-social-shell, #tb4c-community-app');
    for(var i=0;i<surfaces.length;i++){
      add(surfaces[i], 'tb4c-v42168-sidebar-transparent');
      surfaces[i].setAttribute('data-ui-version', '4.2.168');
      surfaces[i].setAttribute('data-sidebar-bg', 'transparent');
    }
  }
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mark, { once:true });
  else mark();
})();


// v4.2.170: Sidebar Clear Named Menu marker. Visual-only layer; keeps current left/feed/right positions.
(function tb4cSidebarClearNamed42170(window, document) {
  'use strict';
  if (!window || !document || window.tb4cSidebarClearNamed42170Booted) return;
  window.tb4cSidebarClearNamed42170Booted = true;
  function add(el, cls) { try { if (el && el.classList && !el.classList.contains(cls)) el.classList.add(cls); } catch (e) {} }
  function boot() {
    add(document.documentElement, 'tb4c-v42170-sidebar-clear-named-ready');
    if (document.body) add(document.body, 'tb4c-v42170-sidebar-clear-named-body');
    try {
      var surfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-community-modular,.tb4c-social-shell,.tb4c-shell');
      for (var i = 0; i < surfaces.length; i += 1) {
        add(surfaces[i], 'tb4c-v42170-sidebar-clear-named');
        surfaces[i].setAttribute('data-ui-version', '4.2.170');
        surfaces[i].setAttribute('data-sidebar-bg', 'transparent');
        surfaces[i].setAttribute('data-left-menu-labels', 'visible');
      }
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
})(window, document);

;document.documentElement.classList.add("tb4c-v42179-psychology-ui-loaded");

// v4.2.200: UI/UX Essential Focus Position Lock final marker. Runs last so cached/old markers cannot report an older UI version.
(function tb4cEssentialFocusPositionLock42200(window, document) {
  'use strict';
  if (!window || !document || window.tb4cEssentialFocusPositionLock42200Booted) return;
  window.tb4cEssentialFocusPositionLock42200Booted = true;
  function add(el, cls) { try { if (el && el.classList && !el.classList.contains(cls)) el.classList.add(cls); } catch (e) {} }
  function remove(el, cls) { try { if (el && el.classList && el.classList.contains(cls)) el.classList.remove(cls); } catch (e) {} }
  function mark() {
    add(document.documentElement, 'tb4c-v42200-essential-focus-position-lock-ready');
    remove(document.documentElement, 'tb4c-v42192-clean-rebuild-ready');
    remove(document.documentElement, 'tb4c-v42194-calm-social-rebuild-ready');
    if (document.body) {
      add(document.body, 'tb4c-v42200-essential-focus-position-lock-body');
      remove(document.body, 'tb4c-v42192-clean-rebuild-body');
      remove(document.body, 'tb4c-v42194-calm-social-rebuild-body');
      remove(document.body, 'tb4c-v42195-clean-position-lock-body');
      remove(document.body, 'tb4c-v42196-clean-light-position-lock-body');
      remove(document.body, 'tb4c-v42197-cleaner-light-position-lock-body');
      remove(document.body, 'tb4c-v42198-deep-clean-position-lock-body');
      remove(document.body, 'tb4c-v42199-ultra-clean-position-lock-body');
    }
    try {
      var surfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-community-modular,.tb4c-social-shell,.tb4c-shell');
      for (var i = 0; i < surfaces.length; i += 1) {
        add(surfaces[i], 'tb4c-v42200-essential-focus-position-lock');
        remove(surfaces[i], 'tb4c-v42192-clean-rebuild');
        remove(surfaces[i], 'tb4c-v42194-calm-social-rebuild');
        remove(surfaces[i], 'tb4c-v42195-clean-position-lock');
        remove(surfaces[i], 'tb4c-v42196-clean-light-position-lock');
        remove(surfaces[i], 'tb4c-v42197-cleaner-light-position-lock');
        remove(surfaces[i], 'tb4c-v42198-deep-clean-position-lock');
        remove(surfaces[i], 'tb4c-v42199-ultra-clean-position-lock');
        surfaces[i].setAttribute('data-ui-version', '4.2.200');
        surfaces[i].setAttribute('data-tb4c-layout', 'essential-focus-position-lock-v42200');
        surfaces[i].setAttribute('data-sidebar-bg', 'transparent');
        surfaces[i].setAttribute('data-left-menu-labels', 'visible');
      }
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mark, { once: true });
  else mark();
  window.addEventListener('pageshow', mark, { passive: true });
})(window, document);


// v4.2.200: remove stale lower nav and duplicate floating create nodes if old cache injects them.
(function tb4cEssentialFocusGuard42200(window, document){
  'use strict';
  if (!window || !document || window.tb4cEssentialFocusGuard42200Booted) return;
  window.tb4cEssentialFocusGuard42200Booted = true;
  function clean(){
    var nodes = document.querySelectorAll('.tb4c-mobile-bottom-nav,.tb4c-app-bottom-nav,.tb4c-global-mobile-nav,.tb4c-canonical-mobile-nav,#tb4c-community-app .tb4c-floating-create,#tb4c-community-app .tb4c-app-floating-create');
    for (var i=0;i<nodes.length;i+=1){
      try { nodes[i].setAttribute('hidden','hidden'); nodes[i].style.display='none'; } catch(e) {}
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', clean, {once:true}); else clean();
  window.addEventListener('pageshow', clean, {passive:true});
})(window, document);


// v4.2.201: UI/UX Core Focus Clean final marker. Runs after v4.2.200 so the newest clean layer wins without moving the layout.
(function tb4cCoreFocusClean42201(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCoreFocusClean42201Booted) return;
  window.tb4cCoreFocusClean42201Booted = true;
  function add(el, cls) { try { if (el && el.classList && !el.classList.contains(cls)) el.classList.add(cls); } catch (e) {} }
  function mark() {
    add(document.documentElement, 'tb4c-v42201-core-focus-clean-ready');
    if (document.body) add(document.body, 'tb4c-v42201-core-focus-clean-body');
    try {
      var surfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-community-modular,.tb4c-social-shell,.tb4c-shell');
      for (var i = 0; i < surfaces.length; i += 1) {
        add(surfaces[i], 'tb4c-v42201-core-focus-clean');
        surfaces[i].setAttribute('data-ui-version', '4.2.201');
        surfaces[i].setAttribute('data-tb4c-layout', 'core-focus-clean-v42201');
        surfaces[i].setAttribute('data-sidebar-bg', 'transparent');
        surfaces[i].setAttribute('data-left-menu-labels', 'visible');
      }
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mark, { once: true });
  else mark();
  window.addEventListener('pageshow', mark, { passive: true });
})(window, document);

// v4.2.201: clean any stale lower nav / duplicate preview nodes that might appear from cache.
(function tb4cCoreFocusCleanGuard42201(window, document){
  'use strict';
  if (!window || !document || window.tb4cCoreFocusCleanGuard42201Booted) return;
  window.tb4cCoreFocusCleanGuard42201Booted = true;
  function clean(){
    var nodes = document.querySelectorAll('.tb4c-mobile-bottom-nav,.tb4c-app-bottom-nav,.tb4c-global-mobile-nav,.tb4c-canonical-mobile-nav,#tb4c-community-app .tb4c-comment-popup-preview,#tb4c-community-app .tb4c-comment-fit-empty');
    for (var i=0;i<nodes.length;i+=1){
      try { nodes[i].setAttribute('hidden','hidden'); nodes[i].style.display='none'; } catch(e) {}
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', clean, {once:true}); else clean();
  window.addEventListener('pageshow', clean, {passive:true});
})(window, document);


// v4.2.202: Essential Feed + Comment Clean final marker. Runs after earlier clean layers so the latest essential UI wins.
(function tb4cEssentialFeedCommentClean42202(window, document) {
  'use strict';
  if (!window || !document || window.tb4cEssentialFeedCommentClean42202Booted) return;
  window.tb4cEssentialFeedCommentClean42202Booted = true;
  function add(el, cls) { try { if (el && el.classList && !el.classList.contains(cls)) el.classList.add(cls); } catch (e) {} }
  function mark() {
    add(document.documentElement, 'tb4c-v42202-essential-member-comment-clean-ready');
    if (document.body) add(document.body, 'tb4c-v42202-essential-member-comment-clean-body');
    try {
      var surfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-community-modular,.tb4c-social-shell,.tb4c-shell,.tb4c-single-social-page');
      for (var i = 0; i < surfaces.length; i += 1) {
        add(surfaces[i], 'tb4c-v42202-essential-member-comment-clean');
        surfaces[i].setAttribute('data-ui-version', '4.2.202');
        surfaces[i].setAttribute('data-tb4c-layout', 'essential-member-comment-clean-v42202');
      }
      var commentCards = document.querySelectorAll('.tb4c-comments-card');
      for (var j = 0; j < commentCards.length; j += 1) add(commentCards[j], 'tb4c-v42202-comment-essential');
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mark, { once: true });
  else mark();
  window.addEventListener('pageshow', mark, { passive: true });
})(window, document);

// v4.2.202: remove stale lower nav and repeated comment/member secondary nodes injected by cache.
(function tb4cEssentialFeedCommentCleanGuard42202(window, document){
  'use strict';
  if (!window || !document || window.tb4cEssentialFeedCommentCleanGuard42202Booted) return;
  window.tb4cEssentialFeedCommentCleanGuard42202Booted = true;
  function clean(){
    var nodes = document.querySelectorAll('.tb4c-mobile-bottom-nav,.tb4c-app-bottom-nav,.tb4c-global-mobile-nav,.tb4c-canonical-mobile-nav,.tb4c-single-social-toolbar,.tb4c-comment-popup-preview,.tb4c-comment-fit-empty');
    for (var i=0;i<nodes.length;i+=1){
      try { nodes[i].setAttribute('hidden','hidden'); nodes[i].style.display='none'; } catch(e) {}
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', clean, {once:true}); else clean();
  window.addEventListener('pageshow', clean, {passive:true});
})(window, document);


(function tb4cPremiumSocialUI42206(window, document) {
  'use strict';
  if (!window || !document || window.tb4cPremiumSocialUI42206Booted) return;
  window.tb4cPremiumSocialUI42206Booted = true;

  function qs(selector, scope) {
    try { return (scope || document).querySelector(selector); }
    catch (e) { return null; }
  }
  function qsa(selector, scope) {
    try { return Array.prototype.slice.call((scope || document).querySelectorAll(selector)); }
    catch (e) { return []; }
  }
  function addClass(node, cls) {
    try { if (node && node.classList) node.classList.add(cls); } catch (e) {}
  }
  function textOf(value) {
    var source = String(value || '').replace(/&amp;/g, '&').replace(/&#038;/g, '&').replace(/\\\//g, '/');
    try { source = decodeURIComponent(source); } catch (e) {}
    return source;
  }
  function youtubeId(value) {
    var parsed, host, path, id, match, source;
    source = textOf(value);
    if (!source) return '';

    /* v4.2.207: extract from raw links, iframes, srcdoc, thumbnail URLs, shorts/live/embed, and oEmbed proxy URLs. */
    match = source.match(/(?:youtube(?:-nocookie)?\.com\/(?:embed|shorts|live)\/|youtu\.be\/|youtube\.com\/watch\?[^\s"'<>]*v=|ytimg\.com\/vi(?:_webp)?\/|img\.youtube\.com\/vi\/)([A-Za-z0-9_-]{6,})/i);
    if (match && match[1]) return match[1];

    try { parsed = new URL(source, window.location.href); }
    catch (e) { return ''; }
    host = String(parsed.hostname || '').replace(/^www\./, '').toLowerCase();
    path = String(parsed.pathname || '').replace(/^\/+/, '');
    if (host === 'youtu.be') {
      id = path.split('/')[0] || '';
      return /^[A-Za-z0-9_-]{6,}$/.test(id) ? id : '';
    }
    if (host.indexOf('youtube.com') !== -1 || host.indexOf('youtube-nocookie.com') !== -1) {
      id = parsed.searchParams ? (parsed.searchParams.get('v') || '') : '';
      if (!id && parsed.searchParams) id = youtubeId(parsed.searchParams.get('url') || parsed.searchParams.get('q') || '');
      if (/^[A-Za-z0-9_-]{6,}$/.test(id)) return id;
      match = path.match(/(?:embed|shorts|live)\/([A-Za-z0-9_-]{6,})/);
      if (match) return match[1];
    }
    return '';
  }
  function vimeoId(value) {
    var source, match;
    source = textOf(value);
    if (!source) return '';
    match = source.match(/(?:player\.)?vimeo\.com\/(?:video\/)?([0-9]{5,})/i);
    return match && match[1] ? match[1] : '';
  }
  function uniqueValues(values) {
    var out = [], seen = {};
    (values || []).forEach(function (value) {
      value = String(value || '').trim();
      if (!value || seen[value]) return;
      seen[value] = true;
      out.push(value);
    });
    return out;
  }
  function youtubeCoverCandidates(id) {
    id = String(id || '').replace(/[^A-Za-z0-9_-]/g, '');
    if (!id) return [];
    return [
      'https://i.ytimg.com/vi_webp/' + id + '/maxresdefault.webp',
      'https://i.ytimg.com/vi/' + id + '/hq720.jpg',
      'https://i.ytimg.com/vi/' + id + '/maxresdefault.jpg',
      'https://img.youtube.com/vi/' + id + '/maxresdefault.jpg',
      'https://i.ytimg.com/vi/' + id + '/sddefault.jpg',
      'https://i.ytimg.com/vi/' + id + '/hqdefault.jpg',
      'https://img.youtube.com/vi/' + id + '/hqdefault.jpg',
      'https://i.ytimg.com/vi/' + id + '/mqdefault.jpg',
      'https://i.ytimg.com/vi/' + id + '/0.jpg'
    ];
  }
  function vimeoCoverCandidates(id) {
    id = String(id || '').replace(/[^0-9]/g, '');
    if (!id) return [];
    return [
      'https://vumbnail.com/' + id + '_large.jpg',
      'https://vumbnail.com/' + id + '.jpg'
    ];
  }
  function cssImage(url) {
    return 'url("' + String(url || '').replace(/"/g, '%22') + '")';
  }
  function buildCoverBackground(candidates) {
    candidates = uniqueValues(candidates);
    return candidates.map(cssImage).join(',') + (candidates.length ? ',' : '') + 'linear-gradient(135deg,#123f2c 0%,#1E6B45 54%,#0f172a 100%)';
  }
  function buildYoutubeCover(id) { return buildCoverBackground(youtubeCoverCandidates(id)); }
  function buildVimeoCover(id) { return buildCoverBackground(vimeoCoverCandidates(id)); }
  function parseCoverCandidates(node) {
    var raw, arr;
    if (!node) return [];
    raw = node.getAttribute('data-tb4c-cover-candidates') || '';
    if (!raw) return [];
    try { arr = JSON.parse(raw); }
    catch (e) { arr = raw.split(/\s*,\s*/); }
    return uniqueValues(arr || []);
  }
  function thumbnailLooksValid(img) {
    if (!img) return false;
    if (!img.naturalWidth || !img.naturalHeight) return false;
    /* YouTube returns tiny placeholder images for some missing HD variants. */
    return !(img.naturalWidth <= 180 && img.naturalHeight <= 140);
  }
  function ensureCoverImage(slot, coverButton, coverPhoto, candidates, provider) {
    var img, index = 0, list;
    list = uniqueValues((candidates || []).concat(parseCoverCandidates(coverButton)));
    if (!list.length || !coverButton || !coverPhoto) return false;

    coverPhoto.style.backgroundImage = buildCoverBackground(list);
    img = qs('.tb4c-video-cover-img', coverButton);
    if (!img) {
      img = document.createElement('img');
      img.className = 'tb4c-video-cover-img';
      img.alt = '';
      img.loading = 'eager';
      img.decoding = 'async';
      img.draggable = false;
      coverButton.insertBefore(img, coverButton.firstChild ? coverButton.firstChild.nextSibling : null);
    }
    img.setAttribute('data-tb4c-cover-candidates', JSON.stringify(list));

    function accept(url) {
      img.setAttribute('data-tb4c-provider-cover', provider || 'external');
      coverPhoto.setAttribute('data-tb4c-provider-cover', provider || 'external');
      coverPhoto.style.backgroundImage = cssImage(url) + ',linear-gradient(135deg,#123f2c,#111)';
      addClass(slot, 'tb4c-video-cover-provider-ready');
    }
    function next() {
      var url = list[index++];
      if (!url) {
        addClass(slot, 'tb4c-video-cover-no-provider');
        return;
      }
      img.onload = function () {
        if (thumbnailLooksValid(img) || index >= list.length) accept(url);
        else next();
      };
      img.onerror = next;
      if (img.getAttribute('src') !== url) img.setAttribute('src', url);
      else if (img.complete) img.onload();
    }
    next();
    return true;
  }
  function collectVideoSources(slot, coverButton, iframe, video) {
    var values = [];
    function push(value) { if (value) values.push(String(value)); }
    if (coverButton) {
      push(coverButton.getAttribute('data-tb4c-youtube-id'));
      push(coverButton.getAttribute('data-tb4c-vimeo-id'));
      push(coverButton.getAttribute('data-tb4c-video-url'));
      push(coverButton.getAttribute('href'));
      push(coverButton.getAttribute('data-tb4c-cover-candidates'));
    }
    if (slot) {
      push(slot.getAttribute('data-tb4c-video-src'));
      push(slot.getAttribute('data-tb4c-video-url'));
      push(slot.getAttribute('data-src'));
      push(slot.getAttribute('data-lazy-src'));
      push(slot.getAttribute('data-tb4c-cover-candidates'));
    }
    if (iframe) {
      push(iframe.getAttribute('src'));
      push(iframe.getAttribute('data-src'));
      push(iframe.getAttribute('data-lazy-src'));
      push(iframe.getAttribute('srcdoc'));
      push(iframe.outerHTML);
    }
    if (video) {
      push(video.getAttribute('poster'));
      push(video.getAttribute('src'));
      push(video.currentSrc);
    }
    if (slot && slot.innerHTML) push(slot.innerHTML);
    return values;
  }
  function ensureVideoCovers(scope) {
    qsa('[data-tb4c-video-cover-scope], [data-tb4c-video-slot], .tb4c-post-video-slot, .tb4c-video-has-cover', scope || document).forEach(function (slot) {
      var coverPhoto = qs('.tb4c-video-cover-photo', slot);
      var coverButton = qs('[data-tb4c-video-play]', slot);
      var iframe = qs('iframe', slot);
      var video = qs('video', slot);
      var sources, id, vid, poster, i, candidates;
      if (!coverPhoto || !coverButton) return;

      sources = collectVideoSources(slot, coverButton, iframe, video);

      id = coverButton ? (coverButton.getAttribute('data-tb4c-youtube-id') || '') : '';
      if (!id) {
        for (i = 0; i < sources.length; i += 1) {
          id = youtubeId(sources[i]);
          if (id) break;
        }
      }
      if (id) {
        candidates = youtubeCoverCandidates(id);
        coverPhoto.style.backgroundImage = buildCoverBackground(candidates);
        ensureCoverImage(slot, coverButton, coverPhoto, candidates, 'youtube');
        return;
      }

      vid = coverButton ? (coverButton.getAttribute('data-tb4c-vimeo-id') || '') : '';
      if (!vid) {
        for (i = 0; i < sources.length; i += 1) {
          vid = vimeoId(sources[i]);
          if (vid) break;
        }
      }
      if (vid) {
        candidates = vimeoCoverCandidates(vid);
        coverPhoto.style.backgroundImage = buildCoverBackground(candidates);
        ensureCoverImage(slot, coverButton, coverPhoto, candidates, 'vimeo');
        return;
      }

      poster = video ? (video.getAttribute('poster') || '') : '';
      candidates = parseCoverCandidates(coverButton);
      if (poster) candidates.unshift(poster);
      if (candidates.length) {
        ensureCoverImage(slot, coverButton, coverPhoto, candidates, poster ? 'poster' : 'external');
        return;
      }

      addClass(slot, 'tb4c-video-cover-no-provider');
    });
  }
  function bindSmoothAnchors() {
    qsa('[data-tb4c-scroll-target]').forEach(function (link) {
      if (link.tb4cRefScrollReady) return;
      link.tb4cRefScrollReady = true;
      link.addEventListener('click', function (event) {
        var id = link.getAttribute('data-tb4c-scroll-target');
        var target = id ? document.getElementById(id) : null;
        if (!target) return;
        event.preventDefault();
        try { target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        catch (e) { target.scrollIntoView(true); }
      });
    });
  }
  function boot() {
    addClass(document.documentElement, 'tb4c-v42208-facebook-like-balance');
    addClass(document.documentElement, 'tb4c-v42207-video-cover-refresh');
    addClass(document.documentElement, 'tb4c-v42206-external-video-cover');
    addClass(document.documentElement, 'tb4c-v42205-premium-social-ui');
    if (document.body) {
      addClass(document.body, 'tb4c-v42208-facebook-like-balance-body');
      addClass(document.body, 'tb4c-v42207-video-cover-refresh-body');
      addClass(document.body, 'tb4c-v42206-external-video-cover-body');
      addClass(document.body, 'tb4c-v42205-premium-social-ui-body');
    }
    ensureVideoCovers(document);
    bindSmoothAnchors();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
  window.addEventListener('pageshow', boot, { passive: true });
  window.setTimeout(boot, 450);
  window.setTimeout(boot, 1500);
})(window, document);


(function tb4cCommentFeedTone42214(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommentFeedTone42214Booted) return;
  window.tb4cCommentFeedTone42214Booted = true;

  function cleanText(value) {
    return String(value || '').replace(/\s+/g, '').trim();
  }

  function normalizeBackControls() {
    var nodes = document.querySelectorAll('a,button');
    for (var i = 0; i < nodes.length; i += 1) {
      var node = nodes[i];
      var text = cleanText(node.textContent);
      if (text.indexOf('กลับฟีด') !== -1) {
        node.classList.add('tb4c-back-feed-only');
      }
      if (text.indexOf('ย้อนกลับ') !== -1) {
        var wrap = node.closest('[data-tb4c-smart-back],.tb4c-smart-back-wrap,.tb4c-reference-back-wrap,.tb4c-floating-back,.tb4c-return-back,.tb4c-back-button') || node;
        wrap.setAttribute('hidden', 'hidden');
        wrap.style.display = 'none';
        wrap.style.visibility = 'hidden';
        wrap.style.pointerEvents = 'none';
      }
    }
  }

  function boot() {
    try { document.documentElement.classList.add('tb4c-v42214-comment-feed-tone'); } catch (e) {}
    try { document.documentElement.classList.add('tb4c-v42215-force-square-media'); } catch (e) {}
    try { document.documentElement.classList.add('tb4c-v42216-wide-card'); } catch (e) {}
    try { document.documentElement.classList.add('tb4c-v42217-reference-width'); } catch (e) {}
    try { document.documentElement.classList.add('tb4c-v42218-wider-inner-card'); } catch (e) {}
    try { document.documentElement.classList.add('tb4c-v42219-full-media-inner-wide'); } catch (e) {}
    try { document.documentElement.classList.add('tb4c-v42220-reference-comment-system'); } catch (e) {}
    if (document.body) {
      try { document.body.classList.add('tb4c-v42214-comment-feed-tone-body'); } catch (e) {}
      try { document.body.classList.add('tb4c-v42215-force-square-media-body'); } catch (e) {}
      try { document.body.classList.add('tb4c-v42216-wide-card-body'); } catch (e) {}
      try { document.body.classList.add('tb4c-v42217-reference-width-body'); } catch (e) {}
      try { document.body.classList.add('tb4c-v42218-wider-inner-card-body'); } catch (e) {}
      try { document.body.classList.add('tb4c-v42219-full-media-inner-wide-body'); } catch (e) {}
      try { document.body.classList.add('tb4c-v42220-reference-comment-system-body'); } catch (e) {}
    }
    normalizeBackControls();
    window.setTimeout(normalizeBackControls, 80);
    window.setTimeout(normalizeBackControls, 450);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
})(window, document);


// v4.2.230: runtime marker for post actions right/no shadow patch.
(function(){
  try { document.documentElement.classList.add('tb4c-v42230-post-actions-right-no-shadow'); } catch(e) {}
  try { document.documentElement.classList.add('tb4c-v42231-main-no-sidebar'); } catch(e) {}
  function tb4cV42230BodyClass(){
    try { if (document.body) { document.body.classList.add('tb4c-v42230-post-actions-right-no-shadow-body'); } } catch(e) {}
    try { if (document.body) { document.body.classList.add('tb4c-v42231-main-no-sidebar-body'); } } catch(e) {}
  }
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', tb4cV42230BodyClass, {once:true}); }
  else { tb4cV42230BodyClass(); }
})();


(function tb4cFeedLoadMore42233(window, document) {
  'use strict';
  if (!window || !document || window.tb4cFeedLoadMore42233Booted) return;
  window.tb4cFeedLoadMore42233Booted = true;

  var cfg = window.tb4cCommunity || {};

  function qs(selector, scope) {
    try { return (scope || document).querySelector(selector); } catch (e) { return null; }
  }

  function setButtonState(button, loading, text) {
    if (!button) return;
    var label = qs('.tb4c-load-more-label', button);
    button.disabled = !!loading;
    button.setAttribute('aria-busy', loading ? 'true' : 'false');
    if (label && text) label.textContent = text;
    if (loading) button.classList.add('is-loading');
    else button.classList.remove('is-loading');
  }

  function request(formData, done, fail) {
    var url = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
    if (window.fetch) {
      fetch(url, { method: 'POST', credentials: 'same-origin', body: formData })
        .then(function (res) { return res.json(); })
        .then(done)
        .catch(fail);
      return;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.withCredentials = true;
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;
      try { done(JSON.parse(xhr.responseText || '{}')); }
      catch (e) { fail(e); }
    };
    xhr.onerror = fail;
    xhr.send(formData);
  }

  function loadMore(button) {
    var list = qs('#tb4cFeedList');
    var page = parseInt(button.getAttribute('data-next-page') || '2', 10);
    var maxPages = parseInt(button.getAttribute('data-max-pages') || '1', 10);
    var topic = button.getAttribute('data-topic') || 'all';
    var search = button.getAttribute('data-search') || '';
    var formData;

    if (!list || button.disabled || !page || page > maxPages) return;
    if (!window.FormData) return;

    formData = new FormData();
    formData.append('action', 'tb4c_load_more_community_feed');
    formData.append('nonce', cfg.nonce || '');
    formData.append('page', String(page));
    formData.append('per_page', '6');
    formData.append('topic', topic);
    formData.append('search', search);

    setButtonState(button, true, 'กำลังโหลด...');

    request(formData, function (json) {
      var data = json && json.data ? json.data : {};
      var small = qs('small', button);
      var wrap = button.closest ? button.closest('[data-tb4c-feed-load-more-wrap]') : null;
      if (!json || !json.success) {
        setButtonState(button, false, 'ลองโหลดอีกครั้ง');
        return;
      }
      if (data.html) {
        list.insertAdjacentHTML('beforeend', data.html);
      }
      button.setAttribute('data-current-page', String(data.page || page));
      button.setAttribute('data-next-page', String(data.next_page || (page + 1)));
      button.setAttribute('data-max-pages', String(data.max_pages || maxPages));
      if (small) small.textContent = 'หน้า ' + String(data.page || page) + ' จาก ' + String(data.max_pages || maxPages);
      if (!data.has_more) {
        if (wrap) wrap.setAttribute('hidden', 'hidden');
        setButtonState(button, false, 'โหลดครบแล้ว');
        return;
      }
      setButtonState(button, false, 'โหลดเพิ่มเติม');
      try { window.dispatchEvent(new CustomEvent('tb4c:feed-load-more:done', { detail: data })); } catch (e) {}
    }, function () {
      setButtonState(button, false, 'ลองโหลดอีกครั้ง');
    });
  }

  document.addEventListener('click', function (event) {
    var button = event.target && event.target.closest ? event.target.closest('[data-tb4c-load-more-feed]') : null;
    if (!button) return;
    event.preventDefault();
    loadMore(button);
  });
})(window, document);

;/* v4.2.241 — runtime post-header normalizer */
(function () {
  'use strict';
  function normalizePostHeaders(scope) {
    var root = scope && scope.querySelectorAll ? scope : document;
    var heads = root.querySelectorAll ? root.querySelectorAll('#tb4c-community-app .tb4c-post-card .tb4c-post-head, .tb4c-single-social-page .tb4c-single-post-card .tb4c-post-head') : [];
    Array.prototype.forEach.call(heads, function (head) {
      var tools = head.querySelector('.tb4c-feed-owner-tools-inline, .tb4c-feed-owner-tools, .tb4c-single-owner-tools-inline');
      if (tools && tools.parentNode !== head) {
        head.appendChild(tools);
      }
      if (tools && tools.classList) {
        tools.classList.add('tb4c-v4241-owner-tools-normalized');
      }
      if (head.classList) {
        head.classList.add('tb4c-v4241-header-normalized');
      }
      var meta = head.querySelector('.tb4c-post-meta');
      if (meta && meta.classList) {
        meta.classList.add('tb4c-v4241-meta-normalized');
      }
      var audience = head.querySelector('.tb4c-author-audience-row');
      if (audience && audience.classList) {
        audience.classList.add('tb4c-v4241-audience-normalized');
      }
    });
  }
  function bootPostHeaderNormalizer() {
    normalizePostHeaders(document);
    window.setTimeout(function () { normalizePostHeaders(document); }, 80);
    window.setTimeout(function () { normalizePostHeaders(document); }, 500);
    document.addEventListener('click', function () {
      window.setTimeout(function () { normalizePostHeaders(document); }, 0);
      window.setTimeout(function () { normalizePostHeaders(document); }, 160);
    }, true);
    if (window.MutationObserver) {
      var app = document.getElementById('tb4c-community-app') || document.body;
      if (app) {
        var observer = new MutationObserver(function () {
          normalizePostHeaders(app);
        });
        observer.observe(app, { childList: true, subtree: true });
      }
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootPostHeaderNormalizer);
  } else {
    bootPostHeaderNormalizer();
  }
})();


;/* v4.2.242 — Touch Scroll Safe Guard: remove stale scroll locks and hidden overlay dead zones */
(function tb4cTouchScrollSafe42242(window, document) {
  'use strict';
  if (!window || !document || window.tb4cTouchScrollSafe42242Booted) return;
  window.tb4cTouchScrollSafe42242Booted = true;

  var SURFACE_SELECTOR = '#tb4cComposerModal,[data-tb4c-composer-modal="canonical"],#tb4cRuntimeLightbox,.tb4c-media-lightbox,.tb4c-comment-popup,[data-tb4c-comment-popup]';
  var guardTimer = 0;

  function addClass(el, cls) {
    try { if (el && el.classList) el.classList.add(cls); } catch (e) {}
  }
  function removeClass(el, cls) {
    try { if (el && el.classList) el.classList.remove(cls); } catch (e) {}
  }
  function hasClass(el, cls) {
    try { return !!(el && el.classList && el.classList.contains(cls)); } catch (e) { return false; }
  }
  function qsa(selector, scope) {
    try { return Array.prototype.slice.call((scope || document).querySelectorAll(selector)); } catch (e) { return []; }
  }
  function isSurfaceOpen(el) {
    if (!el) return false;
    if (hasClass(el, 'is-open') || hasClass(el, 'tb4c-composer-visible-now')) return true;
    if (String(el.getAttribute('aria-hidden') || '').toLowerCase() === 'false') return true;
    return false;
  }
  function isDisplayVisible(el) {
    try {
      var cs = window.getComputedStyle ? window.getComputedStyle(el) : null;
      if (!cs) return true;
      return cs.display !== 'none' && cs.visibility !== 'hidden' && cs.opacity !== '0';
    } catch (e) { return true; }
  }
  function markPage() {
    addClass(document.documentElement, 'tb4c-v42242-touch-scroll-safe');
    if (document.body) addClass(document.body, 'tb4c-v42242-touch-scroll-safe-body');
  }
  function closeStaleSurfaces() {
    var surfaces = qsa(SURFACE_SELECTOR);
    var openCount = 0;
    surfaces.forEach(function (el) {
      var open = isSurfaceOpen(el);
      if (open && isDisplayVisible(el)) {
        openCount += 1;
        el.style.pointerEvents = 'auto';
        return;
      }
      // Closed/inactive fixed layers must not catch touch events over the feed.
      el.style.pointerEvents = 'none';
      if (!open) {
        if (!el.hasAttribute('aria-hidden')) el.setAttribute('aria-hidden', 'true');
        if (el.id === 'tb4cComposerModal' || el.getAttribute('data-tb4c-composer-modal') === 'canonical' || el.id === 'tb4cRuntimeLightbox' || hasClass(el, 'tb4c-comment-popup') || hasClass(el, 'tb4c-media-lightbox')) {
          el.style.visibility = 'hidden';
          el.style.opacity = '0';
          el.style.display = 'none';
        }
      }
    });
    return openCount;
  }
  function releaseStaleScrollLock(openCount) {
    var body = document.body;
    var html = document.documentElement;
    if (!body || !html) return;

    if (openCount > 0) {
      addClass(body, 'tb4c-has-open-modal');
      return;
    }

    removeClass(body, 'tb4c-has-open-modal');
    removeClass(body, 'tb4c-modal-open');
    removeClass(html, 'tb4c-modal-open');

    try {
      if (body.style && (body.style.overflow === 'hidden' || body.style.overflowY === 'hidden')) {
        body.style.overflow = '';
        body.style.overflowY = '';
      }
      if (html.style && (html.style.overflow === 'hidden' || html.style.overflowY === 'hidden')) {
        html.style.overflow = '';
        html.style.overflowY = '';
      }
    } catch (e) {}
  }
  function guardNow() {
    markPage();
    releaseStaleScrollLock(closeStaleSurfaces());
  }
  function scheduleGuard() {
    if (guardTimer) return;
    guardTimer = window.setTimeout(function () {
      guardTimer = 0;
      guardNow();
    }, 30);
  }

  function boot() {
    guardNow();
    window.setTimeout(guardNow, 80);
    window.setTimeout(guardNow, 360);
    window.setTimeout(guardNow, 1200);

    document.addEventListener('touchstart', guardNow, { capture: true, passive: true });
    document.addEventListener('touchend', scheduleGuard, { capture: true, passive: true });
    document.addEventListener('pointerdown', guardNow, true);
    document.addEventListener('click', scheduleGuard, true);
    window.addEventListener('resize', scheduleGuard, { passive: true });
    window.addEventListener('pageshow', scheduleGuard, { passive: true });

    if (window.MutationObserver) {
      try {
        var observer = new MutationObserver(scheduleGuard);
        observer.observe(document.documentElement, { attributes: true, childList: true, subtree: true, attributeFilter: ['class', 'style', 'aria-hidden'] });
      } catch (e) {}
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
})(window, document);



;/* v4.2.263 — Feed-only media stability guard.
   Mini-video popup controllers are disabled because multiple historical versions were fighting each other.
   Videos stay in the real feed node, images/video surfaces allow vertical page scroll, and stale docks are removed. */
(function tb4cFeedOnlyMediaGuard4263(window, document){
  'use strict';
  if (!window || !document || window.tb4cFeedOnlyMediaGuard4263Booted) return;
  window.tb4cFeedOnlyMediaGuard4263Booted = true;

  var root = document.documentElement;
  var body = null;
  var raf = 0;
  var STALE_DOCKS = '#tb4cVideoDock4262,#tb4cStableMiniDock4257,#tb4cMiniRemote4253';
  var MEDIA_SURFACES = '.tb4c-post-media-slot,.tb4c-featured-lightbox-trigger,.tb4c-post-gallery,.tb4c-media-album,.tb4c-ig-media,.tb4c-thumb,[data-tb4c-video-slot]';
  var MINI_CLASSES = ['tb4c-video-mini-player','tb4c-mini-video-portaled','tb4c-v42243-mini-player','tb4c-hard-mini-active','tb4c-v4256-active','tb4c-v4256-hidden-duplicate','tb4c-v4250-mini-safe-ready','tb4c-v4250-mini-dragging','tb4c-v4253-dragging','tb4c-v4256-dragging','tb4c-v4260-force-closed','tb4c-v4262-hidden-source'];

  function qsa(sel, ctx){ try { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); } catch(e){ return []; } }
  function closest(el, sel){ try { return el && el.closest ? el.closest(sel) : null; } catch(e){ return null; } }
  function add(el, cls){ try { if (el && el.classList) el.classList.add(cls); } catch(e){} }
  function rem(el, cls){ try { if (el && el.classList) el.classList.remove(cls); } catch(e){} }
  function rmStyle(el, props){ try { props.forEach(function(p){ el.style.removeProperty(p); }); } catch(e){} }
  function isOpenModalTarget(t){ return !!closest(t, '#tb4cComposerModal,[data-tb4c-composer-modal="canonical"],#tb4cRuntimeLightbox,.tb4c-media-lightbox,.tb4c-comment-popup,[data-tb4c-comment-popup]'); }

  function injectStyle(){
    if (document.getElementById('tb4cFeedOnlyMediaGuard4263Style')) return;
    var st = document.createElement('style');
    st.id = 'tb4cFeedOnlyMediaGuard4263Style';
    st.textContent = '\nhtml.tb4c-v4263-feed-only-video body :is(#tb4cVideoDock4262,#tb4cStableMiniDock4257,#tb4cMiniRemote4253){display:none!important;visibility:hidden!important;opacity:0!important;pointer-events:none!important}\nhtml.tb4c-v4263-feed-only-video body :is(.tb4c-video-mini-player,.tb4c-mini-video-portaled,.tb4c-v42243-mini-player){position:relative!important;left:auto!important;right:auto!important;top:auto!important;bottom:auto!important;width:100%!important;max-width:100%!important;min-width:0!important;height:auto!important;min-height:0!important;max-height:none!important;display:block!important;box-shadow:none!important;transform:none!important;z-index:auto!important;contain:initial!important}\nhtml.tb4c-v4263-feed-only-video body :is(.tb4c-mini-video-bar,.tb4c-v4256-bar,.tb4c-v4262-bar,.tb4c-v4260-iframe-paused,.tb4c-v4262-placeholder){display:none!important;visibility:hidden!important;opacity:0!important;pointer-events:none!important}\nhtml.tb4c-v4263-feed-only-video body :is(.tb4c-post-media-slot,.tb4c-featured-lightbox-trigger,.tb4c-post-gallery,.tb4c-media-album,.tb4c-ig-media,.tb4c-thumb,[data-tb4c-video-slot],img,video,iframe){touch-action:pan-y!important;-webkit-user-drag:none!important}\nhtml.tb4c-v4263-feed-only-video body :is(.tb4c-post-media-slot img,.tb4c-featured-lightbox-trigger img,.tb4c-post-gallery img,.tb4c-media-album img,.tb4c-ig-media img,.tb4c-thumb img){pointer-events:auto!important;touch-action:pan-y!important}\n';
    (document.head || document.documentElement).appendChild(st);
  }
  function restoreSlot(slot){
    if (!slot || !slot.classList) return;
    MINI_CLASSES.forEach(function(c){ rem(slot, c); });
    ['data-tb4c-v4257-source','data-tb4c-v4257-duplicate','data-tb4c-v4253-active-mini','data-tb4c4260Closed','data-tb4c-v4261-duplicate-src','data-tb4c-v4262-paused'].forEach(function(a){ try { slot.removeAttribute(a); } catch(e){} });
    rmStyle(slot, ['position','left','right','top','bottom','width','min-width','max-width','height','min-height','max-height','display','visibility','opacity','pointer-events','z-index','transform','box-shadow','border-radius','contain','overflow']);
    qsa('.tb4c-mini-video-bar,.tb4c-v4256-bar,.tb4c-v4262-bar,.tb4c-v4260-iframe-paused,.tb4c-v4262-placeholder', slot).forEach(function(n){ try { n.parentNode && n.parentNode.removeChild(n); } catch(e){} });
    qsa('video,iframe,img', slot).forEach(function(m){
      try { m.style.removeProperty('pointer-events'); m.style.setProperty('touch-action','pan-y','important'); m.removeAttribute('draggable'); } catch(e){}
    });
  }
  function cleanup(){
    body = document.body || body;
    add(root, 'tb4c-v4263-feed-only-video');
    if (body) {
      add(body, 'tb4c-v4263-feed-only-video-body');
      ['tb4c-has-mini-video','tb4c-v4253-has-mini','tb4c-v4257-stable-mini-active','tb4c-v4262-video-active'].forEach(function(c){ rem(body, c); });
    }
    ['tb4c-v430-hardpatch-loaded','tb4c-v4247-mini-video-bar-safe','tb4c-v4253-mini-correct','tb4c-v4255-mini-compact','tb4c-v4257-stable-mini','tb4c-v4258-button-action-fix','tb4c-v4259-hard-action-bus','tb4c-v4260-true-pause-drag-close','tb4c-v4261-single-state','tb4c-v4262-video-single-state'].forEach(function(c){ rem(root, c); });
    qsa(STALE_DOCKS).forEach(function(n){ try { n.parentNode && n.parentNode.removeChild(n); } catch(e){} });
    qsa('.tb4c-video-mini-player,.tb4c-mini-video-portaled,.tb4c-v42243-mini-player,[data-tb4c-video-slot],.tb4c-post-video-slot').forEach(restoreSlot);
    qsa(MEDIA_SURFACES).forEach(function(surface){ try { surface.style.setProperty('touch-action','pan-y','important'); } catch(e){} });
  }
  function schedule(){ if (raf) return; raf = (window.requestAnimationFrame || window.setTimeout)(function(){ raf=0; cleanup(); }, 60); }

  document.addEventListener('touchmove', function(e){
    if (isOpenModalTarget(e.target)) return;
    if (closest(e.target, MEDIA_SURFACES + ',img,video,iframe')) {
      /* Stop older non-passive document handlers from blocking native vertical scroll over images/videos. */
      e.stopPropagation();
    }
  }, {capture:true, passive:true});

  document.addEventListener('pointermove', function(e){
    if (isOpenModalTarget(e.target)) return;
    if (closest(e.target, MEDIA_SURFACES + ',img,video,iframe')) e.stopPropagation();
  }, true);

  window.addEventListener('scroll', schedule, {passive:true});
  window.addEventListener('resize', schedule, {passive:true});
  document.addEventListener('click', function(){ window.setTimeout(schedule, 30); }, true);
  if (window.MutationObserver) {
    try { new MutationObserver(schedule).observe(document.documentElement, {childList:true,subtree:true,attributes:true,attributeFilter:['class','style']}); } catch(e){}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', cleanup, {once:true}); else cleanup();
  window.setTimeout(cleanup, 120);
  window.setTimeout(cleanup, 700);
})(window, document);

;/* v4.2.266 — Feed/comment parity + Single comment UI repair + Reels next/prev. */
(function tb4cRedlineReelFit4264(window, document) {
  'use strict';
  if (!window || !document || window.tb4cRedlineReelFit4264Booted) return;
  window.tb4cRedlineReelFit4264Booted = true;

  var currentIndex = 0;
  var scrollTimer = 0;

  function qsa(selector, scope) {
    try { return Array.prototype.slice.call((scope || document).querySelectorAll(selector)); }
    catch (e) { return []; }
  }
  function qs(selector, scope) {
    try { return (scope || document).querySelector(selector); }
    catch (e) { return null; }
  }
  function closest(node, selector) {
    try { return node && node.closest ? node.closest(selector) : null; }
    catch (e) { return null; }
  }
  function addClass(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function removeClass(node, cls) { try { if (node && node.classList) node.classList.remove(cls); } catch (e) {} }
  function isCommunityPage() {
    return !!(document.getElementById('tb4c-community-app') || (document.body && document.body.classList && document.body.classList.contains('tb4c-community-page')));
  }
  function getViewer() { return document.getElementById('tb4cReelViewer'); }
  function getStage(viewer) { return viewer ? qs('[data-tb4c-reel-stage],.tb4c-reel-stage', viewer) : null; }
  function getSlides(viewer) { return viewer ? qsa('.tb4c-reel-slide', viewer).filter(function (slide) { return slide.offsetParent !== null || slide.getClientRects().length; }) : []; }
  function clampIndex(index, slides) {
    slides = slides || getSlides(getViewer());
    if (!slides.length) return 0;
    index = Number(index || 0);
    if (!isFinite(index)) index = 0;
    return Math.max(0, Math.min(slides.length - 1, index));
  }
  function pauseSlide(slide) {
    if (!slide) return;
    qsa('video', slide).forEach(function (video) { try { video.pause(); } catch (e) {} });
    removeClass(slide, 'is-playing');
  }
  function pauseAll(viewer) { getSlides(viewer).forEach(pauseSlide); }
  function playSlide(slide) {
    if (!slide) return;
    var video = qs('video', slide);
    if (!video) return;
    try {
      video.muted = true;
      var p = video.play && video.play();
      if (p && p.catch) p.catch(function () {});
      addClass(slide, 'is-playing');
    } catch (e) {}
  }
  function updateNav(viewer) {
    viewer = viewer || getViewer();
    var slides = getSlides(viewer);
    currentIndex = clampIndex(currentIndex, slides);
    var prev = viewer ? qs('[data-tb4c-reel-prev]', viewer) : null;
    var next = viewer ? qs('[data-tb4c-reel-next]', viewer) : null;
    if (prev) prev.disabled = slides.length <= 1 || currentIndex <= 0;
    if (next) next.disabled = slides.length <= 1 || currentIndex >= slides.length - 1;
    slides.forEach(function (slide, index) {
      if (index === currentIndex) addClass(slide, 'is-active');
      else removeClass(slide, 'is-active');
    });
  }
  function scrollToSlide(index, behavior) {
    var viewer = getViewer();
    var stage = getStage(viewer);
    var slides = getSlides(viewer);
    if (!viewer || !stage || !slides.length) return;
    currentIndex = clampIndex(index, slides);
    var slide = slides[currentIndex];
    pauseAll(viewer);
    try {
      var top = slide.offsetTop - stage.offsetTop;
      stage.scrollTo({ top: Math.max(0, top), left: 0, behavior: behavior || 'smooth' });
    } catch (e) {
      try { slide.scrollIntoView({ block: 'start', inline: 'nearest', behavior: behavior || 'smooth' }); } catch (err) {}
    }
    window.setTimeout(function () { playSlide(slide); updateNav(viewer); }, behavior === 'auto' ? 10 : 280);
  }
  function findStartIndex(trigger) {
    var viewer = getViewer();
    var slides = getSlides(viewer);
    if (!slides.length) return 0;
    var postId = trigger ? String(trigger.getAttribute('data-tb4c-reel-post-id') || '') : '';
    if (postId) {
      for (var i = 0; i < slides.length; i += 1) {
        if (String(slides[i].getAttribute('data-tb4c-reel-post-id') || '') === postId) return i;
      }
    }
    var raw = trigger ? trigger.getAttribute('data-tb4c-reel-index') : '';
    if (raw !== null && raw !== '') return clampIndex(Number(raw), slides);
    return 0;
  }
  function openViewer(trigger) {
    var viewer = getViewer();
    if (!viewer) return false;
    addClass(viewer, 'is-open');
    viewer.setAttribute('aria-hidden', 'false');
    if (document.body) addClass(document.body, 'tb4c-reel-open');
    currentIndex = findStartIndex(trigger);
    updateNav(viewer);
    window.setTimeout(function () { scrollToSlide(currentIndex, 'auto'); }, 20);
    return true;
  }
  function closeViewer() {
    var viewer = getViewer();
    if (!viewer) return false;
    pauseAll(viewer);
    removeClass(viewer, 'is-open');
    viewer.setAttribute('aria-hidden', 'true');
    if (document.body) removeClass(document.body, 'tb4c-reel-open');
    return true;
  }
  function detectCurrentFromScroll() {
    var viewer = getViewer();
    var stage = getStage(viewer);
    var slides = getSlides(viewer);
    if (!stage || !slides.length) return;
    var best = 0;
    var bestDelta = Infinity;
    var stageTop = stage.scrollTop;
    slides.forEach(function (slide, index) {
      var delta = Math.abs((slide.offsetTop - stage.offsetTop) - stageTop);
      if (delta < bestDelta) { bestDelta = delta; best = index; }
    });
    if (best !== currentIndex) {
      pauseSlide(slides[currentIndex]);
      currentIndex = best;
      playSlide(slides[currentIndex]);
      updateNav(viewer);
    }
  }
  function scrollRail(button, direction) {
    var shell = closest(button, '#tb4cFeaturedReels,[data-tb4c-reel-standard],.tb4c-reel-shell') || document;
    var rail = qs('#tb4cStoryRail,.tb4c-story-rail,.tb4c-reel-rail', shell);
    if (!rail) return false;
    var amount = Math.max(180, Math.round((rail.clientWidth || 320) * 0.82));
    try { rail.scrollBy({ left: direction * amount, top: 0, behavior: 'smooth' }); }
    catch (e) { rail.scrollLeft += direction * amount; }
    window.setTimeout(updateRailNav, 140);
    return true;
  }
  function updateRailNav() {
    qsa('#tb4cFeaturedReels,[data-tb4c-reel-standard],.tb4c-reel-shell').forEach(function (shell) {
      var rail = qs('#tb4cStoryRail,.tb4c-story-rail,.tb4c-reel-rail', shell);
      if (!rail) return;
      var prev = qs('[data-tb4c-reel-rail-prev]', shell);
      var next = qs('[data-tb4c-reel-rail-next]', shell);
      var max = Math.max(0, rail.scrollWidth - rail.clientWidth - 2);
      if (prev) prev.disabled = rail.scrollLeft <= 2 || max <= 2;
      if (next) next.disabled = rail.scrollLeft >= max || max <= 2;
    });
  }
  function hideMemberNavigation() {
    if (!isCommunityPage()) return;
    var selector = [
      'nav a[href]',
      '[role="navigation"] a[href]',
      '.tb4-sidebar a[href]',
      '.tb4c-sidebar a[href]',
      '.tb4-left-sidebar a[href]',
      '.tb4c-left-sidebar-final a[href]',
      '.tb4-platform-menu a[href]',
      '.tb4c-social-nav a[href]',
      '.tb4-header-menu a[href]',
      '.tb4-mobile-menu a[href]'
    ].join(',');
    qsa(selector).forEach(function (link) {
      if (closest(link, '#wpadminbar,.tb4c-post-card,.tb4c-reel-viewer,.tb4c-reel-author,.tb4c-author-row')) return;
      var href = String(link.getAttribute('href') || '').toLowerCase();
      var text = String(link.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
      var isMember = /\/(member-area|members|member|profile|account)(\/|$|\?)/.test(href) || text === 'สมาชิก' || text === 'members' || text === 'member';
      if (!isMember) return;
      var wrapper = closest(link, 'li,.menu-item,.tb4-nav-item,.tb4-sidebar-item,.tb4c-nav-item,.tb4c-sidebar-item,.tb4-platform-menu-item,.tb4c-social-nav-item');
      var target = wrapper || link;
      target.setAttribute('data-tb4c-member-nav-hidden', '1');
      addClass(target, 'tb4c-member-nav-hidden');
      try { target.setAttribute('aria-hidden', 'true'); } catch (e) {}
    });
  }
  function applyFitClasses() {
    addClass(document.documentElement, 'tb4c-v4264-redline-reel-fit');
    addClass(document.documentElement, 'tb4c-v4265-feed-comment-parity');
    addClass(document.documentElement, 'tb4c-v4266-comment-ui-repair');
    addClass(document.documentElement, 'tb4c-v4267-single-feed-parity');
    if (document.body) addClass(document.body, 'tb4c-v4264-redline-reel-fit-body');
    if (document.body) addClass(document.body, 'tb4c-v4265-feed-comment-parity-body');
    if (document.body) addClass(document.body, 'tb4c-v4266-comment-ui-repair-body');
    if (document.body) addClass(document.body, 'tb4c-v4267-single-feed-parity-body');
    var app = document.getElementById('tb4c-community-app');
    if (app) {
      app.setAttribute('data-ui-version', '4.2.267');
      app.setAttribute('data-tb4c-redline-fit', '760');
    }
  }
  function handleScrollTarget(button) {
    var id = button ? button.getAttribute('data-tb4c-scroll-target') : '';
    if (!id) return false;
    var target = document.getElementById(id.replace(/^#/, '')) || qs('#' + id.replace(/^#/, ''));
    if (!target) return false;
    try { target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    catch (e) { window.location.hash = id.replace(/^#/, ''); }
    return true;
  }
  function onClick(event) {
    var target = event.target;
    var railPrev = closest(target, '[data-tb4c-reel-rail-prev]');
    if (railPrev) {
      event.preventDefault(); event.stopImmediatePropagation();
      scrollRail(railPrev, -1);
      return;
    }
    var railNext = closest(target, '[data-tb4c-reel-rail-next]');
    if (railNext) {
      event.preventDefault(); event.stopImmediatePropagation();
      scrollRail(railNext, 1);
      return;
    }
    var scrollBtn = closest(target, '[data-tb4c-scroll-target]');
    if (scrollBtn && handleScrollTarget(scrollBtn)) {
      event.preventDefault(); event.stopImmediatePropagation();
      return;
    }
    var openBtn = closest(target, '[data-tb4c-open-reels]');
    if (openBtn && openViewer(openBtn)) {
      event.preventDefault(); event.stopImmediatePropagation();
      return;
    }
    var closeBtn = closest(target, '[data-tb4c-close-reels]');
    if (closeBtn && closeViewer()) {
      event.preventDefault(); event.stopImmediatePropagation();
      return;
    }
    var prevBtn = closest(target, '[data-tb4c-reel-prev]');
    if (prevBtn) {
      event.preventDefault(); event.stopImmediatePropagation();
      scrollToSlide(currentIndex - 1, 'smooth');
      return;
    }
    var nextBtn = closest(target, '[data-tb4c-reel-next]');
    if (nextBtn) {
      event.preventDefault(); event.stopImmediatePropagation();
      scrollToSlide(currentIndex + 1, 'smooth');
      return;
    }
    var playBtn = closest(target, '[data-tb4c-reel-toggle-play]');
    if (playBtn) {
      event.preventDefault(); event.stopImmediatePropagation();
      var slide = closest(playBtn, '.tb4c-reel-slide');
      var video = slide ? qs('video', slide) : null;
      if (video) {
        try {
          if (video.paused) { video.muted = true; video.play(); addClass(slide, 'is-playing'); }
          else { video.pause(); removeClass(slide, 'is-playing'); }
        } catch (e) {}
      }
    }
  }
  function boot() {
    applyFitClasses();
    hideMemberNavigation();
    updateRailNav();
    var stage = getStage(getViewer());
    if (stage && !stage.getAttribute('data-tb4c-v4264-scroll-bound')) {
      stage.setAttribute('data-tb4c-v4264-scroll-bound', '1');
      stage.addEventListener('scroll', function () {
        if (scrollTimer) window.clearTimeout(scrollTimer);
        scrollTimer = window.setTimeout(detectCurrentFromScroll, 90);
      }, { passive: true });
    }
    qsa('#tb4cStoryRail,.tb4c-story-rail,.tb4c-reel-rail').forEach(function (rail) {
      if (rail.getAttribute('data-tb4c-v4264-rail-bound')) return;
      rail.setAttribute('data-tb4c-v4264-rail-bound', '1');
      rail.addEventListener('scroll', function () {
        if (scrollTimer) window.clearTimeout(scrollTimer);
        scrollTimer = window.setTimeout(updateRailNav, 90);
      }, { passive: true });
    });
  }

  document.addEventListener('click', onClick, true);
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && getViewer() && getViewer().classList.contains('is-open')) closeViewer();
    if (!getViewer() || !getViewer().classList.contains('is-open')) return;
    if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') { event.preventDefault(); scrollToSlide(currentIndex - 1, 'smooth'); }
    if (event.key === 'ArrowRight' || event.key === 'ArrowDown') { event.preventDefault(); scrollToSlide(currentIndex + 1, 'smooth'); }
  }, true);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true }); else boot();
  window.addEventListener('resize', function () { window.setTimeout(function () { applyFitClasses(); updateRailNav(); updateNav(getViewer()); }, 80); }, { passive: true });
  window.addEventListener('pageshow', function () { window.setTimeout(boot, 60); }, { passive: true });
  if (window.MutationObserver) {
    try { new MutationObserver(function () { window.setTimeout(function () { hideMemberNavigation(); updateRailNav(); }, 30); }).observe(document.documentElement, { childList: true, subtree: true }); } catch (e) {}
  }
})(window, document);


;/* v4.2.273 — Image popup tap guard + media vertical-scroll recovery.
   Goal: tap image = open popup, drag on image = native page scroll. No member/comment pages are restored. */
(function tb4cImagePopupTapGuard4273(window, document){
  'use strict';
  if (!window || !document || window.tb4cImagePopupTapGuard4273Booted) return;
  window.tb4cImagePopupTapGuard4273Booted = true;

  var TRIGGER_SELECTOR = '[data-tb4c-lightbox],button.tb4c-featured-lightbox-trigger,button.tb4c-album-image,button.tb4c-album-item,.tb4c-thumb.tb4c-post-image-slot,.tb4c-post-media-slot[data-tb4c-lightbox],.tb4c-album-item[data-tb4c-lightbox]';
  var MEDIA_ZONE_SELECTOR = '#tb4c-community-app .tb4c-post-media-slot,#tb4c-community-app .tb4c-featured-lightbox-trigger,#tb4c-community-app .tb4c-post-gallery,#tb4c-community-app .tb4c-media-album,#tb4c-community-app .tb4c-album-image,#tb4c-community-app .tb4c-ig-media,#tb4c-community-app .tb4c-thumb';
  var start = null;

  function closest(el, sel){ try { return el && el.closest ? el.closest(sel) : null; } catch(e){ return null; } }
  function q(sel, ctx){ try { return (ctx || document).querySelector(sel); } catch(e){ return null; } }
  function add(el, cls){ try { if (el && el.classList) el.classList.add(cls); } catch(e){} }
  function rem(el, cls){ try { if (el && el.classList) el.classList.remove(cls); } catch(e){} }
  function point(event){
    var t = (event.touches && event.touches[0]) || (event.changedTouches && event.changedTouches[0]) || event;
    return { x: Number(t.clientX || 0), y: Number(t.clientY || 0) };
  }
  function isOpenSurface(node){ return !!closest(node, '#tb4cRuntimeLightbox,#tb4cMediaLightbox,.tb4c-media-lightbox,#tb4cComposerModal,.tb4c-comment-popup,[data-tb4c-comment-popup]'); }
  function getTrigger(node){
    var trigger = closest(node, TRIGGER_SELECTOR);
    if (trigger) return trigger;
    var zone = closest(node, MEDIA_ZONE_SELECTOR);
    if (!zone) return null;
    var img = node && node.tagName && String(node.tagName).toLowerCase() === 'img' ? node : q('img', zone);
    if (!img) return null;
    return zone;
  }
  function mediaInfo(trigger){
    if (!trigger) return null;
    var img = q('img', trigger);
    var video = q('video', trigger);
    var iframe = q('iframe', trigger);
    var src = trigger.getAttribute('data-tb4c-lightbox-src') || '';
    var type = trigger.getAttribute('data-tb4c-lightbox-type') || '';
    var title = trigger.getAttribute('data-tb4c-lightbox-title') || trigger.getAttribute('aria-label') || document.title || 'ตัวอย่างสื่อ';

    if (!src && img) src = img.currentSrc || img.src || img.getAttribute('src') || '';
    if (!src && video) src = video.currentSrc || video.src || video.getAttribute('src') || '';
    if (!src && iframe) src = iframe.src || iframe.getAttribute('src') || '';
    if (!type) {
      if (video || /\.(mp4|m4v|webm|ogv|ogg)(\?|#|$)/i.test(src)) type = 'video';
      else if (iframe || /youtube|youtu\.be|vimeo/i.test(src)) type = 'iframe';
      else type = 'image';
    }
    return src ? {src:src, type:type, title:title} : null;
  }
  function ensureBox(){
    var box = document.getElementById('tb4cRuntimeLightbox') || document.getElementById('tb4cMediaLightbox');
    if (box) return box;
    box = document.createElement('div');
    box.id = 'tb4cRuntimeLightbox';
    box.className = 'tb4c-media-lightbox tb4c-runtime-lightbox tb4c-v4273-lightbox';
    box.setAttribute('aria-hidden', 'true');
    box.setAttribute('data-tb4c-lightbox-shell', 'runtime');
    box.innerHTML = '<button type="button" class="tb4c-media-lightbox-backdrop" data-tb4c-lightbox-close aria-label="ปิดตัวอย่าง"></button><div class="tb4c-media-lightbox-panel" role="dialog" aria-modal="true" aria-label="ดูสื่อ"><header class="tb4c-media-lightbox-head"><strong data-tb4c-lightbox-title>ตัวอย่างสื่อ</strong><button type="button" data-tb4c-lightbox-close aria-label="ปิด">×</button></header><div class="tb4c-media-lightbox-body" data-tb4c-lightbox-body></div></div>';
    (document.body || document.documentElement).appendChild(box);
    return box;
  }
  function openBox(info){
    var box = ensureBox();
    var body = q('[data-tb4c-lightbox-body]', box);
    var label = q('[data-tb4c-lightbox-title]', box);
    var node;
    if (!box || !body || !info || !info.src) return false;
    if (label) label.textContent = info.title || 'ตัวอย่างสื่อ';
    body.textContent = '';
    if (info.type === 'video') {
      node = document.createElement('video');
      node.src = info.src;
      node.controls = true;
      node.playsInline = true;
      node.preload = 'metadata';
    } else if (info.type === 'iframe') {
      node = document.createElement('iframe');
      node.src = info.src;
      node.loading = 'lazy';
      node.allow = 'autoplay; encrypted-media; picture-in-picture';
      node.setAttribute('allowfullscreen','');
    } else {
      node = document.createElement('img');
      node.src = info.src;
      node.alt = info.title || '';
      node.loading = 'eager';
      node.decoding = 'async';
    }
    body.appendChild(node);
    add(box, 'is-open');
    box.setAttribute('aria-hidden','false');
    add(document.body, 'tb4c-modal-open');
    add(document.body, 'tb4c-has-open-modal');
    return true;
  }
  function closeBox(){
    var boxes = [];
    try { boxes = Array.prototype.slice.call(document.querySelectorAll('#tb4cRuntimeLightbox,#tb4cMediaLightbox,.tb4c-media-lightbox.is-open')); } catch(e){}
    boxes.forEach(function(box){
      rem(box, 'is-open');
      box.setAttribute('aria-hidden','true');
      var body = q('[data-tb4c-lightbox-body]', box);
      if (body) {
        try { Array.prototype.forEach.call(body.querySelectorAll('video'), function(v){ v.pause(); }); } catch(e){}
        body.textContent = '';
      }
    });
    rem(document.body, 'tb4c-modal-open');
    rem(document.body, 'tb4c-has-open-modal');
  }
  function markMedia(scope){
    try {
      Array.prototype.forEach.call((scope || document).querySelectorAll(MEDIA_ZONE_SELECTOR + ',' + TRIGGER_SELECTOR), function(el){
        el.setAttribute('data-tb4c-v4273-media-safe', '1');
        el.style.setProperty('touch-action','pan-y pinch-zoom','important');
        el.style.setProperty('-webkit-user-drag','none','important');
      });
    } catch(e){}
  }
  function begin(event){
    if (isOpenSurface(event.target)) return;
    var trigger = getTrigger(event.target);
    if (!trigger) return;
    var p = point(event);
    start = { trigger: trigger, x:p.x, y:p.y, moved:false, time:Date.now() };
  }
  function move(event){
    if (!start) return;
    var p = point(event);
    if (Math.abs(p.x - start.x) > 8 || Math.abs(p.y - start.y) > 8) start.moved = true;
  }
  function end(){
    window.setTimeout(function(){ if (start && Date.now() - start.time > 650) start = null; }, 0);
  }
  function onClick(event){
    var close = closest(event.target, '[data-tb4c-lightbox-close]');
    if (close) {
      event.preventDefault();
      if (event.stopImmediatePropagation) event.stopImmediatePropagation();
      else event.stopPropagation();
      closeBox();
      return;
    }
    if (isOpenSurface(event.target)) return;
    var trigger = getTrigger(event.target);
    if (!trigger) return;

    if (start && start.trigger === trigger && start.moved) {
      event.preventDefault();
      if (event.stopImmediatePropagation) event.stopImmediatePropagation();
      else event.stopPropagation();
      start = null;
      return;
    }

    var info = mediaInfo(trigger);
    if (!info) return;
    event.preventDefault();
    if (event.stopImmediatePropagation) event.stopImmediatePropagation();
    else event.stopPropagation();
    openBox(info);
    start = null;
  }
  function boot(){
    add(document.documentElement, 'tb4c-v4273-image-popup-scroll-safe');
    if (document.body) add(document.body, 'tb4c-v4273-image-popup-scroll-safe-body');
    markMedia(document);
  }

  document.addEventListener('pointerdown', begin, {capture:true, passive:true});
  document.addEventListener('pointermove', move, {capture:true, passive:true});
  document.addEventListener('pointerup', end, {capture:true, passive:true});
  document.addEventListener('touchstart', begin, {capture:true, passive:true});
  document.addEventListener('touchmove', move, {capture:true, passive:true});
  document.addEventListener('touchend', end, {capture:true, passive:true});
  document.addEventListener('click', onClick, true);
  window.addEventListener('pageshow', boot, {passive:true});
  window.addEventListener('load', boot, {once:true, passive:true});
  try { new MutationObserver(function(){ markMedia(document); }).observe(document.documentElement, {childList:true,subtree:true}); } catch(e){}
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, {once:true}); else boot();
})(window, document);


;/* v4.2.274 — Hard image popup delegation: catch every feed image before older handlers. */
(function tb4cHardImagePopupAll4274(window, document){
  'use strict';
  if (!window || !document || window.tb4cHardImagePopupAll4274Booted) return;
  window.tb4cHardImagePopupAll4274Booted = true;

  var IMAGE_TRIGGER_SELECTOR = '[data-tb4c-hard-image-popup],[data-tb4c-v4274-image-trigger],[data-tb4c-lightbox][data-tb4c-lightbox-type="image"],.tb4c-album-image,.tb4c-featured-lightbox-trigger,.tb4c-post-image-slot,.tb4c-comment-image-slot';
  var IMAGE_ZONE_SELECTOR = '.tb4c-post-media-slot,.tb4c-post-gallery,.tb4c-media-album,.tb4c-album-grid,.tb4c-album-item,.tb4c-ig-media,.tb4c-thumb,.tb4c-comment-media-slot,.tb4c-comment-image-slot,[data-tb4c-v4274-image-zone]';
  var FEED_SCOPE_SELECTOR = '#tb4c-community-app,.tb4c-community-page,.tb4c-feed-shell,.tb4c-shell,.tb4c-home-feed-main,.tb4c-social-feed,.tb4c-feed-list,.tb4c-post-card,.tb4c-single-social-page';
  var UI_IMAGE_EXCLUDE = '.tb4c-avatar,.avatar,.profile-photo,.custom-logo,.site-logo,.tb4c-icon,img[class*="avatar"],img[class*="logo"],svg,button[data-tb4c-reel-prev],button[data-tb4c-reel-next]';
  var state = null;

  function closest(node, sel){ try { return node && node.closest ? node.closest(sel) : null; } catch(e){ return null; } }
  function qs(sel, ctx){ try { return (ctx || document).querySelector(sel); } catch(e){ return null; } }
  function qsa(sel, ctx){ try { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); } catch(e){ return []; } }
  function add(el, cls){ try { if (el && el.classList) el.classList.add(cls); } catch(e){} }
  function remove(el, cls){ try { if (el && el.classList) el.classList.remove(cls); } catch(e){} }
  function point(event){
    var t = (event.touches && event.touches[0]) || (event.changedTouches && event.changedTouches[0]) || event;
    return { x: Number(t && t.clientX || 0), y: Number(t && t.clientY || 0) };
  }
  function isInsideFeed(node){
    return !!closest(node, FEED_SCOPE_SELECTOR);
  }
  function isUiOrControl(node){
    if (!node) return true;
    if (closest(node, '#tb4cRuntimeLightbox,#tb4cMediaLightbox,.tb4c-media-lightbox,#tb4cComposerModal,.tb4c-comment-popup,[data-tb4c-comment-popup]')) return true;
    if (closest(node, 'a.tb4c-author-link,.tb4c-avatar-link,.tb4c-member-link,[data-tb4c-member-link],[data-tb4c-open-comment-popup],[data-tb4c-comment-link],[data-tb4c-react],[data-tb4c-edit-post],[data-tb4c-delete-post],[data-tb4c-poll-vote],.tb4c-feed-owner-tools,.tb4c-post-actions,.tb4c-social-actions')) return true;
    return false;
  }
  function imageFromNode(node){
    if (!node) return null;
    if (node.tagName && String(node.tagName).toLowerCase() === 'img') return node;
    return qs('img', node);
  }
  function isExcludedImage(img){
    if (!img) return true;
    if (closest(img, UI_IMAGE_EXCLUDE)) return true;
    var w = img.naturalWidth || img.width || 0;
    var h = img.naturalHeight || img.height || 0;
    if ((w && w < 48) || (h && h < 48)) {
      if (!closest(img, IMAGE_ZONE_SELECTOR) && !closest(img, IMAGE_TRIGGER_SELECTOR)) return true;
    }
    return false;
  }
  function srcFromImage(img){
    if (!img) return '';
    return img.currentSrc || img.src || img.getAttribute('src') || img.getAttribute('data-src') || img.getAttribute('data-lazy-src') || '';
  }
  function findCandidate(node){
    if (!node || isUiOrControl(node)) return null;
    var trigger = closest(node, IMAGE_TRIGGER_SELECTOR);
    var zone = trigger || closest(node, IMAGE_ZONE_SELECTOR);
    var img = null;

    if (trigger) img = imageFromNode(trigger);
    if (!img && zone) img = imageFromNode(zone);
    if (!img && node.tagName && String(node.tagName).toLowerCase() === 'img') img = node;

    if (!img || isExcludedImage(img) || !isInsideFeed(img)) return null;

    var src = '';
    var title = '';
    var holder = trigger || zone || img;
    if (holder && holder.getAttribute) {
      src = holder.getAttribute('data-tb4c-lightbox-src') || holder.getAttribute('href') || '';
      title = holder.getAttribute('data-tb4c-lightbox-title') || holder.getAttribute('aria-label') || '';
    }
    if (!src) src = srcFromImage(img);
    if (!title) title = img.getAttribute('alt') || document.title || 'ตัวอย่างรูปภาพ';
    if (!src || /^data:image\/svg/i.test(src)) return null;
    return { trigger: holder, img: img, src: src, title: title };
  }
  function ensureBox(){
    var box = document.getElementById('tb4cRuntimeLightbox') || document.getElementById('tb4cMediaLightbox');
    if (!box) {
      box = document.createElement('div');
      box.id = 'tb4cRuntimeLightbox';
      box.className = 'tb4c-media-lightbox tb4c-runtime-lightbox tb4c-v4274-image-lightbox';
      box.setAttribute('aria-hidden', 'true');
      box.setAttribute('data-tb4c-lightbox-shell', 'runtime');
      box.innerHTML = '<button type="button" class="tb4c-media-lightbox-backdrop" data-tb4c-lightbox-close aria-label="ปิดรูปภาพ"></button><div class="tb4c-media-lightbox-panel" role="dialog" aria-modal="true" aria-label="ดูรูปภาพ"><header class="tb4c-media-lightbox-head"><strong data-tb4c-lightbox-title>ตัวอย่างรูปภาพ</strong><button type="button" data-tb4c-lightbox-close aria-label="ปิด">×</button></header><div class="tb4c-media-lightbox-body" data-tb4c-lightbox-body></div></div>';
      (document.body || document.documentElement).appendChild(box);
    }
    return box;
  }
  function openImage(info){
    if (!info || !info.src) return false;
    var box = ensureBox();
    var body = qs('[data-tb4c-lightbox-body]', box);
    var label = qs('[data-tb4c-lightbox-title]', box);
    if (!box || !body) return false;
    if (label) label.textContent = info.title || 'ตัวอย่างรูปภาพ';
    try { qsa('video', body).forEach(function(v){ try { v.pause(); } catch(e){} }); } catch(e){}
    body.textContent = '';
    var img = document.createElement('img');
    img.src = info.src;
    img.alt = info.title || '';
    img.loading = 'eager';
    img.decoding = 'async';
    img.setAttribute('data-tb4c-v4274-popup-image', '1');
    body.appendChild(img);
    add(box, 'is-open');
    box.setAttribute('aria-hidden', 'false');
    add(document.body, 'tb4c-modal-open');
    add(document.body, 'tb4c-has-open-modal');
    return true;
  }
  function closeImagePopup(){
    qsa('#tb4cRuntimeLightbox,#tb4cMediaLightbox,.tb4c-media-lightbox.is-open').forEach(function(box){
      remove(box, 'is-open');
      box.setAttribute('aria-hidden', 'true');
      var body = qs('[data-tb4c-lightbox-body]', box);
      if (body) body.textContent = '';
    });
    remove(document.body, 'tb4c-modal-open');
    remove(document.body, 'tb4c-has-open-modal');
  }
  function cancelEvent(event){
    try { event.preventDefault(); } catch(e){}
    try { event.stopImmediatePropagation(); } catch(e){ try { event.stopPropagation(); } catch(x){} }
  }
  function onDown(event){
    var info = findCandidate(event.target);
    if (!info) { state = null; return; }
    var p = point(event);
    state = { info: info, x: p.x, y: p.y, moved: false, time: Date.now() };
  }
  function onMove(event){
    if (!state) return;
    var p = point(event);
    if (Math.abs(p.x - state.x) > 9 || Math.abs(p.y - state.y) > 9) state.moved = true;
  }
  function onClick(event){
    var close = closest(event.target, '[data-tb4c-lightbox-close]');
    if (close) { cancelEvent(event); closeImagePopup(); state = null; return; }

    var info = state && state.info ? state.info : findCandidate(event.target);
    if (!info) return;

    if (state && state.moved) { cancelEvent(event); state = null; return; }
    if (state && Date.now() - state.time > 1200) { cancelEvent(event); state = null; return; }

    if (openImage(info)) {
      cancelEvent(event);
      state = null;
    }
  }
  function mark(scope){
    qsa(IMAGE_ZONE_SELECTOR + ',' + IMAGE_TRIGGER_SELECTOR, scope || document).forEach(function(el){
      try {
        el.setAttribute('data-tb4c-v4274-image-ready', '1');
        el.style.setProperty('touch-action', 'pan-y pinch-zoom', 'important');
      } catch(e){}
    });
    qsa(IMAGE_ZONE_SELECTOR + ' img,' + IMAGE_TRIGGER_SELECTOR + ' img', scope || document).forEach(function(img){
      try {
        if (!isExcludedImage(img)) img.setAttribute('data-tb4c-v4274-image', '1');
        img.style.setProperty('pointer-events', 'auto', 'important');
        img.style.setProperty('touch-action', 'pan-y pinch-zoom', 'important');
        img.style.setProperty('-webkit-user-drag', 'none', 'important');
      } catch(e){}
    });
  }
  function boot(){
    add(document.documentElement, 'tb4c-v4274-hard-image-popup-all');
    if (document.body) add(document.body, 'tb4c-v4274-hard-image-popup-all-body');
    mark(document);
  }

  window.addEventListener('pointerdown', onDown, {capture:true, passive:true});
  window.addEventListener('pointermove', onMove, {capture:true, passive:true});
  window.addEventListener('touchstart', onDown, {capture:true, passive:true});
  window.addEventListener('touchmove', onMove, {capture:true, passive:true});
  window.addEventListener('click', onClick, true);
  document.addEventListener('keydown', function(event){ if (event.key === 'Escape') closeImagePopup(); }, true);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, {once:true}); else boot();
  window.addEventListener('pageshow', boot, {passive:true});
  try { new MutationObserver(function(muts){ mark(document); }).observe(document.documentElement, {childList:true, subtree:true}); } catch(e){}
})(window, document);

;/* v4.2.275/276 — Keep image popup full-screen under header, using lightweight overlay updates. */
(function(window, document){
  'use strict';
  if (!window || !document) return;
  var raf = 0;
  var HEADER_SELECTORS = [
    '#wpadminbar',
    '#tb4-header',
    '#tb4-header-menu',
    '#thinkb4do-header',
    '#thinkb4do-header-menu',
    '.thinkb4do-header',
    '.thinkb4do-header-menu',
    '.tb4-header',
    '.tb4-main-header',
    '.tb4-header-menu',
    '.tb4-site-header',
    '.site-header',
    '.main-header',
    '.header-menu',
    '.elementor-location-header',
    'header[role="banner"]',
    'header.site-header',
    'body > header'
  ];
  function qsa(selector){
    try { return Array.prototype.slice.call(document.querySelectorAll(selector)); } catch(e){ return []; }
  }
  function px(value){
    value = Number(value || 0);
    if (!isFinite(value) || value < 0) value = 0;
    return Math.round(value) + 'px';
  }
  function isVisible(el, rect){
    if (!el || !rect) return false;
    if (rect.width <= 0 || rect.height <= 0) return false;
    var style = null;
    try { style = window.getComputedStyle(el); } catch(e){}
    if (style && (style.display === 'none' || style.visibility === 'hidden' || Number(style.opacity || 1) === 0)) return false;
    return true;
  }
  function isHeaderLike(el, rect){
    if (!el || !rect || !isVisible(el, rect)) return false;
    var style = null;
    try { style = window.getComputedStyle(el); } catch(e){}
    var pos = style ? String(style.position || '') : '';
    var nearTop = rect.top <= 96 && rect.bottom > 8;
    var stickyLike = pos === 'fixed' || pos === 'sticky';
    var notHuge = rect.height <= Math.max(160, window.innerHeight * 0.28);
    return notHuge && (stickyLike || nearTop);
  }
  function computeTop(){
    var top = 0;
    var seen = [];
    HEADER_SELECTORS.forEach(function(selector){
      qsa(selector).forEach(function(el){
        if (seen.indexOf(el) !== -1) return;
        seen.push(el);
        var rect = null;
        try { rect = el.getBoundingClientRect(); } catch(e){}
        if (!isHeaderLike(el, rect)) return;
        top = Math.max(top, rect.bottom);
      });
    });
    var admin = document.getElementById('wpadminbar');
    if (admin) {
      try {
        var ar = admin.getBoundingClientRect();
        if (isVisible(admin, ar)) top = Math.max(top, ar.bottom);
      } catch(e){}
    }
    if (!top) {
      try {
        var fallback = getComputedStyle(document.documentElement).getPropertyValue('--tb4c-header-real-h') || getComputedStyle(document.documentElement).getPropertyValue('--tb4c-header-h') || '58';
        top = parseFloat(fallback) || 58;
      } catch(e){ top = 58; }
    }
    top = Math.min(Math.max(top, 0), Math.max(0, window.innerHeight - 180));
    return top;
  }
  function apply(){
    raf = 0;
    var top = computeTop();
    try {
      document.documentElement.classList.add('tb4c-v4275-image-popup-under-header');
      document.documentElement.classList.add('tb4c-v4278-natural-image-card');
      if (document.body) {
        document.body.classList.add('tb4c-v4275-image-popup-under-header-body');
        document.body.classList.add('tb4c-v4278-natural-image-card-body');
      }
      document.documentElement.style.setProperty('--tb4c-popup-under-header-top', px(top));
    } catch(e){}
  }
  function requestApply(){
    if (raf) return;
    raf = window.requestAnimationFrame ? window.requestAnimationFrame(apply) : window.setTimeout(apply, 16);
  }
  function patchOpenFunctions(){
    requestApply();
    var boxes = [];
    try { boxes = qsa('#tb4cRuntimeLightbox,#tb4cMediaLightbox,.tb4c-media-lightbox'); } catch(e){}
    boxes.forEach(function(box){
      try {
        if (!box.classList.contains('tb4c-v4278-standard-soft-overlay')) {
          box.classList.add('tb4c-v4275-under-header-lightbox');
          box.classList.add('tb4c-v4276-lite-overlay');
          box.classList.add('tb4c-v4277-opacity10-overlay');
          box.classList.add('tb4c-v4278-standard-soft-overlay');
          box.style.setProperty('top', 'var(--tb4c-popup-under-header-top)', 'important');
          box.style.setProperty('left', '0', 'important');
          box.style.setProperty('right', '0', 'important');
          box.style.setProperty('bottom', 'var(--tb4c-popup-under-header-bottom, env(safe-area-inset-bottom, 0px))', 'important');
          box.style.setProperty('background', 'rgba(0,0,0,.42)', 'important');
          box.style.setProperty('transition', 'none', 'important');
          box.style.setProperty('animation', 'none', 'important');
          box.style.setProperty('will-change', 'auto', 'important');
        }
      } catch(e){}
    });
  }
  ['DOMContentLoaded','load','pageshow','resize','orientationchange'].forEach(function(name){
    window.addEventListener(name, requestApply, {passive:true});
  });
  document.addEventListener('click', function(){ window.setTimeout(patchOpenFunctions, 0); }, true);
  document.addEventListener('pointerup', function(){ window.setTimeout(patchOpenFunctions, 0); }, true);
  try {
    var obsRoot = document.body || document.documentElement;
    new MutationObserver(function(){ patchOpenFunctions(); }).observe(obsRoot, {childList:true, subtree:false});
  } catch(e){}
  requestApply();
})(window, document);

