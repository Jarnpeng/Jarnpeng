(function tb4cCommunityCore42126(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommunityCore42126Booted) return;
  window.tb4cCommunityCore42126Booted = true;

  var root = document.documentElement;
  var cfg = window.tb4cCommunity || {};

  function addClass(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function hasSurface() { try { return !!document.querySelector('.tb4c-shell,#tb4c-community-app,.tb4-home-feed-main,.tb4c-member-area-shell,[data-tb4c-community-surface]'); } catch (e) { return false; } }
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
    if (!closest(link, '.tb4c-shell,#tb4c-community-app,.tb4c-member-area-shell,.tb4-home-feed-main,[data-tb4c-community-surface],.tb4c-mobile-bottom-nav')) return;
    rememberCurrentUrl();
  }
  function createBackButton() {
    if (qs('.tb4c-single-social-page') || qs('.tb4c-back-link')) return;
    var target = qs('[data-tb4c-smart-back-target]');
    var surface = target || qs('.tb4c-social-feed') || qs('.tb4c-member-area-shell') || qs('.tb4c-single-social') || qs('.tb4-home-feed-main') || qs('#tb4c-community-app') || qs('.tb4c-shell');
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
      var list = document.querySelectorAll('.tb4c-shell,.tb4c-social-shell,.tb4c-member-area-shell,.tb4-home-feed-main,.tb4c-home-menu-feed,#tb4c-community-app,[data-tb4c-community-surface]');
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
      var surfaces = document.querySelectorAll('.tb4c-shell,.tb4c-social-shell,.tb4c-member-area-shell,.tb4-home-feed-main,.tb4c-home-menu-feed,#tb4c-community-app,[data-tb4c-community-surface]');
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
      var surfaces = document.querySelectorAll('.tb4c-v42154-force-social,.tb4c-shell,.tb4c-member-area-shell,.tb4-home-feed-main,.tb4c-home-menu-feed,#tb4c-community-app');
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
      var surfaces = document.querySelectorAll('.tb4c-v42155-gplus-lite,#tb4c-community-app,.tb4c-shell,.tb4c-member-area-shell,.tb4-home-feed-main');
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
      var surfaces = document.querySelectorAll('.tb4c-v42156-gplus-clean,#tb4c-community-app,.tb4c-member-area-shell,.tb4-home-feed-main,.tb4c-home-menu-feed');
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
    var lockSurfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-social-shell,.tb4c-shell,.tb4c-member-area-shell,.tb4-home-feed-main,.tb4c-home-menu-feed');
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


// v4.2.202: Essential Member + Comment Clean final marker. Runs after earlier clean layers so the latest essential UI wins.
(function tb4cEssentialMemberCommentClean42202(window, document) {
  'use strict';
  if (!window || !document || window.tb4cEssentialMemberCommentClean42202Booted) return;
  window.tb4cEssentialMemberCommentClean42202Booted = true;
  function add(el, cls) { try { if (el && el.classList && !el.classList.contains(cls)) el.classList.add(cls); } catch (e) {} }
  function mark() {
    add(document.documentElement, 'tb4c-v42202-essential-member-comment-clean-ready');
    if (document.body) add(document.body, 'tb4c-v42202-essential-member-comment-clean-body');
    try {
      var surfaces = document.querySelectorAll('#tb4c-community-app,.tb4c-community-modular,.tb4c-social-shell,.tb4c-shell,.tb4c-single-social-page,.tb4c-member-area-page');
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
(function tb4cEssentialMemberCommentCleanGuard42202(window, document){
  'use strict';
  if (!window || !document || window.tb4cEssentialMemberCommentCleanGuard42202Booted) return;
  window.tb4cEssentialMemberCommentCleanGuard42202Booted = true;
  function clean(){
    var nodes = document.querySelectorAll('.tb4c-mobile-bottom-nav,.tb4c-app-bottom-nav,.tb4c-global-mobile-nav,.tb4c-canonical-mobile-nav,.tb4c-single-social-toolbar,.tb4c-comment-popup-preview,.tb4c-comment-fit-empty,.tb4c-member-area-side');
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

(function tb4cMemberFeedLoadMore4253(window, document) {
  'use strict';
  if (!window || !document || window.tb4cMemberFeedLoadMore4253Booted) return;
  window.tb4cMemberFeedLoadMore4253Booted = true;

  var cfg = window.tb4cCommunity || {};

  function qs(selector, scope) {
    try { return (scope || document).querySelector(selector); }
    catch (e) { return null; }
  }

  function setButtonState(button, isLoading, label) {
    var labelNode = qs('.tb4c-load-more-label', button) || button;
    if (labelNode) labelNode.textContent = label || (isLoading ? 'กำลังโหลด...' : 'โหลดเพิ่มเติม');
    button.disabled = !!isLoading;
    if (isLoading) button.classList.add('is-loading');
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

  function loadMoreMemberFeed(button) {
    var listId = button.getAttribute('data-list-id') || 'tb4cMemberFeedList';
    var list = document.getElementById(listId);
    var page = parseInt(button.getAttribute('data-next-page') || '2', 10);
    var maxPages = parseInt(button.getAttribute('data-max-pages') || '1', 10);
    var authorId = button.getAttribute('data-author-id') || '';
    var statusFilter = button.getAttribute('data-status-filter') || 'all';
    var formData;

    if (!list || button.disabled || !page || page > maxPages) return;
    if (!window.FormData) return;

    formData = new FormData();
    formData.append('action', 'tb4c_load_more_member_feed');
    formData.append('nonce', cfg.nonce || '');
    formData.append('page', String(page));
    formData.append('per_page', '6');
    formData.append('author_id', authorId);
    formData.append('status_filter', statusFilter);

    setButtonState(button, true, 'กำลังโหลด...');

    request(formData, function (json) {
      var data = json && json.data ? json.data : {};
      var small = qs('small', button);
      var wrap = button.closest ? button.closest('[data-tb4c-member-feed-load-more-wrap]') : null;
      if (!json || !json.success) {
        setButtonState(button, false, 'ลองโหลดอีกครั้ง');
        return;
      }
      if (data.html) list.insertAdjacentHTML('beforeend', data.html);
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
      try { window.dispatchEvent(new CustomEvent('tb4c:member-feed-load-more:done', { detail: data })); } catch (e) {}
    }, function () {
      setButtonState(button, false, 'ลองโหลดอีกครั้ง');
    });
  }

  document.addEventListener('click', function (event) {
    var button = event.target && event.target.closest ? event.target.closest('[data-tb4c-load-more-member-feed]') : null;
    if (!button) return;
    event.preventDefault();
    loadMoreMemberFeed(button);
  });
})(window, document);
