(function tb4cCommunityRuntime42126(window, document) {
  'use strict';
  if (!window || !document || window.tb4cCommunityRuntime42126Booted) return;
  window.tb4cCommunityRuntime42126Booted = true;

  var root = document.documentElement;
  var cfg = window.tb4cCommunity || {};
  var cleaned = new WeakSet();
  var scheduled = false;
  var videoObserver = null;
  var legacyState = window.tb4cLegacyFeatureState42126 || { loading: false, loaded: !!window.tb4cCommunityRuntimeBooted42114, callbacks: [] };
  window.tb4cLegacyFeatureState42126 = legacyState;

  function qsa(selector, scope) {
    try { return Array.prototype.slice.call((scope || document).querySelectorAll(selector)); }
    catch (e) { return []; }
  }
  function qs(selector, scope) {
    try { return (scope || document).querySelector(selector); }
    catch (e) { return null; }
  }
  function addClass(node, cls) { try { if (node && node.classList) node.classList.add(cls); } catch (e) {} }
  function removeClass(node, cls) { try { if (node && node.classList) node.classList.remove(cls); } catch (e) {} }
  function removeNode(node) { try { if (node && node.parentNode) node.parentNode.removeChild(node); } catch (e) {} }
  function closest(node, selector) { try { return node && node.closest ? node.closest(selector) : null; } catch (e) { return null; } }

  function hideDuplicate(node) {
    try {
      if (!node || node.classList.contains('is-open') || node.getAttribute('aria-hidden') === 'false') return;
      node.setAttribute('data-tb4c-runtime-hidden', 'true');
      node.setAttribute('data-tb4c-v42126-removed', 'true');
      node.setAttribute('aria-hidden', 'true');
      addClass(node, 'tb4c-ui-duplicate');
      addClass(node, 'tb4c-runtime-hidden');
      addClass(node, 'tb4c-v42126-removed-duplicate');
      removeNode(node);
    } catch (e) {}
  }

  function hasSurface() {
    return !!qs('.tb4c-shell,#tb4c-community-app,.tb4-home-feed-main,[data-tb4c-community-surface]');
  }
  function isSlowDevice() {
    var nav = window.navigator || {};
    var mem = Number(nav.deviceMemory || 0);
    var cores = Number(nav.hardwareConcurrency || 0);
    var coarse = false;
    try { coarse = !!(window.matchMedia && window.matchMedia('(hover:none),(pointer:coarse)').matches); } catch (e) {}
    return (mem && mem <= 4) || (cores && cores <= 4) || coarse;
  }
  function wantsReducedMotion() {
    try { return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches); } catch (e) { return false; }
  }

  function bootClasses() {
    addClass(root, 'tb4c-v42126-hard-remove-ui');
    addClass(root, 'tb4c-v42126-ultra-runtime');
    if (!hasSurface()) addClass(root, 'tb4c-runtime-light');
    if (isSlowDevice()) { addClass(root, 'tb4c-runtime-slow-device'); addClass(root, 'tb4c-v42126-runtime-lite'); }
    if (wantsReducedMotion()) addClass(root, 'tb4c-runtime-low-motion');
  }

  function idle(fn, timeout) {
    if (window.requestIdleCallback) return window.requestIdleCallback(fn, { timeout: timeout || 700 });
    return window.setTimeout(fn, 60);
  }
  function raf(fn) {
    if (window.requestAnimationFrame) return window.requestAnimationFrame(fn);
    return window.setTimeout(fn, 16);
  }

  function canonicalizeGlobal(selector, preferredSelector) {
    var nodes = qsa(selector);
    if (nodes.length <= 1) return;
    var keeper = preferredSelector ? qs(preferredSelector) : null;
    if (!keeper) {
      keeper = nodes.filter(function (node) {
        return node.getAttribute('data-tb4c-ui-role') || String(node.className || '').indexOf('canonical') >= 0 || node.id === 'tb4cComposerModal';
      })[0] || nodes[0];
    }
    nodes.forEach(function (node) { if (node !== keeper) hideDuplicate(node); });
  }

  function keyForAction(el) {
    if (!el) return '';
    var explicit = el.getAttribute('data-action') || el.getAttribute('data-tb4c-action') || el.getAttribute('aria-label') || '';
    var cls = String(el.className || '');
    var text = (el.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
    var icon = (el.querySelector && el.querySelector('[class*="ph-"]')) ? String(el.querySelector('[class*="ph-"]').className || '') : '';
    var raw = (explicit || icon || cls || text).toLowerCase();
    if (raw.indexOf('like') >= 0 || raw.indexOf('heart') >= 0 || raw.indexOf('ถูกใจ') >= 0) return 'like';
    if (raw.indexOf('comment') >= 0 || raw.indexOf('chat') >= 0 || raw.indexOf('แสดงความ') >= 0 || raw.indexOf('ความคิดเห็น') >= 0) return 'comment';
    if (raw.indexOf('share') >= 0 || raw.indexOf('ส่งต่อ') >= 0 || raw.indexOf('แชร์') >= 0) return 'share';
    if (raw.indexOf('bookmark') >= 0 || raw.indexOf('save') >= 0 || raw.indexOf('บันทึก') >= 0) return 'save';
    if (raw.indexOf('view') >= 0 || raw.indexOf('eye') >= 0 || raw.indexOf('ดู') >= 0) return 'view';
    if (raw.indexOf('edit') >= 0 || raw.indexOf('แก้ไข') >= 0) return 'edit';
    if (raw.indexOf('delete') >= 0 || raw.indexOf('trash') >= 0 || raw.indexOf('ลบ') >= 0) return 'delete';
    return raw.slice(0, 80);
  }

  function dedupeActionRows(scope) {
    var cards = qsa('.tb4c-post-card,.tb4c-single-social,.tb4c-member-post,.tb4c-feed-item,[data-tb4c-post-id]', scope || document);
    cards.forEach(function (card) {
      if (cleaned.has(card)) return;
      cleaned.add(card);
      var rows = qsa('.tb4c-post-actions,.tb4c-social-actions,.tb4c-ig-action-row,.tb4c-single-actions', card).filter(function (row) {
        return !closest(row, '[data-tb4c-runtime-hidden="true"]');
      });
      if (rows.length > 1) {
        var keeper = rows[0];
        rows.slice(1).forEach(function (row) {
          if (row !== keeper && row.children.length <= keeper.children.length + 2) hideDuplicate(row);
        });
      }
      rows.forEach(function (row) {
        var seen = Object.create(null);
        qsa('button,a,[role="button"]', row).forEach(function (btn) {
          var key = keyForAction(btn);
          if (!key) return;
          if (seen[key]) hideDuplicate(btn);
          else seen[key] = true;
        });
      });
    });
  }

  function getComposerModal() {
    return qs('#tb4cComposerModal,[data-tb4c-composer-modal="canonical"]');
  }
  function syncComposerPanels(modal) {
    if (!modal) return;
    var buttons = qsa('[data-tb4c-smart-panel-btn]', modal);
    var panels = qsa('[data-tb4c-smart-panel]', modal);
    if (!buttons.length || !panels.length) return;
    var active = buttons.filter(function (button) { return button.classList.contains('is-active') || button.getAttribute('aria-expanded') === 'true'; })[0] || buttons[0];
    var target = active.getAttribute('data-tb4c-smart-panel-btn') || 'meta';
    panels.forEach(function (panel) {
      var on = panel.getAttribute('data-tb4c-smart-panel') === target;
      panel.hidden = !on;
      panel.classList.toggle('is-open', on);
    });
    buttons.forEach(function (button) {
      var on = button === active || button.getAttribute('data-tb4c-smart-panel-btn') === target;
      button.classList.toggle('is-active', on);
      button.setAttribute('aria-expanded', on ? 'true' : 'false');
    });
  }
  function updateComposerUploadStatus(modal) {
    var input = modal ? qs('#tb4c-post-media-file,input[type="file"][name="media_files[]"]', modal) : null;
    var status = modal ? qs('[data-tb4c-smart-upload-status]', modal) : null;
    if (!input || !status) return;
    try {
      if (input.files && input.files.length) {
        status.hidden = false;
        status.textContent = 'แนบไฟล์แล้ว ' + input.files.length + ' ไฟล์';
        addClass(modal, 'tb4c-has-upload-file');
      } else {
        status.hidden = true;
        status.textContent = '';
        removeClass(modal, 'tb4c-has-upload-file');
      }
    } catch (e) {}
  }
  function normalizeComposer() {
    canonicalizeGlobal('#tb4cComposerModal,[data-tb4c-composer-modal]', '#tb4cComposerModal,[data-tb4c-composer-modal="canonical"]');
    var modal = getComposerModal();
    if (!modal) return;
    try {
      if (modal.parentNode !== document.body) document.body.appendChild(modal);
      modal.setAttribute('data-tb4c-runtime-managed', 'true');
      syncComposerPanels(modal);
      updateComposerUploadStatus(modal);
    } catch (e) {}
  }
  function openComposer(focusTarget) {
    var modal = getComposerModal();
    if (!modal) return false;
    normalizeComposer();
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    if (focusTarget) addClass(modal, 'tb4c-show-' + focusTarget);
    raf(function () {
      var input = qs('textarea,input,[contenteditable="true"]', modal);
      try { if (input && window.innerWidth > 760) input.focus({ preventScroll: true }); } catch (e) {}
    });
    return true;
  }
  function closeComposer(btn) {
    var modal = closest(btn, '#tb4cComposerModal,[data-tb4c-composer-modal]') || getComposerModal();
    if (!modal) return false;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    return true;
  }

  function normalizeNavAndFloating() {
    canonicalizeGlobal('.tb4c-mobile-bottom-nav,[data-tb4c-mobile-nav]', '.tb4c-mobile-bottom-nav[data-tb4c-mobile-nav="canonical"],.tb4c-canonical-mobile-nav');
    var floating = qsa('.tb4c-floating-create,.tb4c-app-floating-create,[data-tb4c-floating-create]');
    if (floating.length > 1) floating.slice(1).forEach(hideDuplicate);
  }
  function normalizePopups() {
    ['[data-tb4c-lightbox-shell]', '[data-tb4c-comment-popup]', '.tb4c-media-lightbox', '.tb4c-comment-popup'].forEach(function (selector) {
      var nodes = qsa(selector);
      if (nodes.length <= 1) return;
      var open = nodes.filter(function (node) { return node.classList.contains('is-open') || node.getAttribute('aria-hidden') === 'false'; })[0];
      var keeper = open || nodes[0];
      nodes.forEach(function (node) { if (node !== keeper) hideDuplicate(node); });
    });
  }

  function ensureMicroLightbox() {
    var box = qs('#tb4cRuntimeLightbox');
    if (box) return box;
    box = document.createElement('div');
    box.id = 'tb4cRuntimeLightbox';
    box.className = 'tb4c-media-lightbox tb4c-runtime-lightbox';
    box.setAttribute('aria-hidden', 'true');
    box.setAttribute('data-tb4c-lightbox-shell', 'runtime');
    box.innerHTML = '<button type="button" class="tb4c-media-lightbox-backdrop" data-tb4c-lightbox-close aria-label="ปิดตัวอย่าง"></button><div class="tb4c-media-lightbox-panel" role="dialog" aria-modal="true" aria-label="ดูสื่อ"><header class="tb4c-media-lightbox-head"><strong data-tb4c-lightbox-title>ตัวอย่างสื่อ</strong><button type="button" data-tb4c-lightbox-close aria-label="ปิด">×</button></header><div class="tb4c-media-lightbox-body" data-tb4c-lightbox-body></div></div>';
    (document.body || root).appendChild(box);
    return box;
  }
  function openMicroLightbox(trigger) {
    if (!trigger) return false;
    var src = trigger.getAttribute('data-tb4c-lightbox-src') || trigger.getAttribute('href') || trigger.getAttribute('src') || '';
    var type = trigger.getAttribute('data-tb4c-lightbox-type') || '';
    var title = trigger.getAttribute('data-tb4c-lightbox-title') || trigger.getAttribute('aria-label') || 'ตัวอย่างสื่อ';
    if (!src) {
      var img = qs('img', trigger);
      if (img) src = img.currentSrc || img.src || '';
    }
    if (!src) return false;
    var box = ensureMicroLightbox();
    var body = qs('[data-tb4c-lightbox-body]', box);
    var label = qs('[data-tb4c-lightbox-title]', box);
    if (!body) return false;
    if (label) label.textContent = title;
    body.textContent = '';
    var node;
    if (type === 'video' || /\.(mp4|webm|ogg)(\?|#|$)/i.test(src)) {
      node = document.createElement('video');
      node.src = src;
      node.controls = true;
      node.playsInline = true;
      node.preload = 'metadata';
    } else if (type === 'iframe' || /youtube|youtu\.be|vimeo/i.test(src)) {
      node = document.createElement('iframe');
      node.src = src;
      node.loading = 'lazy';
      node.allow = 'autoplay; encrypted-media; picture-in-picture';
      node.setAttribute('allowfullscreen', '');
    } else {
      node = document.createElement('img');
      node.src = src;
      node.alt = title;
      node.loading = 'eager';
      node.decoding = 'async';
    }
    body.appendChild(node);
    box.classList.add('is-open');
    box.setAttribute('aria-hidden', 'false');
    addClass(document.body, 'tb4c-modal-open');
    return true;
  }
  function closeMicroLightbox() {
    qsa('#tb4cRuntimeLightbox,[data-tb4c-lightbox-shell],.tb4c-media-lightbox.is-open').forEach(function (box) {
      box.classList.remove('is-open');
      box.setAttribute('aria-hidden', 'true');
      var body = qs('[data-tb4c-lightbox-body]', box);
      if (body && box.id === 'tb4cRuntimeLightbox') body.textContent = '';
    });
    removeClass(document.body, 'tb4c-modal-open');
  }

  function findCommentPopup(trigger) {
    var rootEl = closest(trigger, '[data-tb4c-comments-root]');
    if (rootEl) return qs('[data-tb4c-comment-popup]', rootEl) || qs('.tb4c-comment-popup', rootEl);
    return qs('[data-tb4c-comment-popup],.tb4c-comment-popup');
  }
  function openMicroCommentPopup(trigger) {
    var popup = findCommentPopup(trigger);
    if (!popup) return false;
    popup.classList.add('is-open');
    popup.setAttribute('aria-hidden', 'false');
    addClass(document.body, 'tb4c-comment-portal-active');
    raf(function () {
      var textarea = qs('[data-tb4c-comment-textarea],textarea', popup);
      try { if (textarea && window.innerWidth > 760) textarea.focus({ preventScroll: true }); } catch (e) {}
    });
    return true;
  }
  function closeMicroCommentPopup(trigger) {
    var popup = closest(trigger, '[data-tb4c-comment-popup],.tb4c-comment-popup') || qs('[data-tb4c-comment-popup].is-open,.tb4c-comment-popup.is-open');
    if (!popup) return false;
    popup.classList.remove('is-open');
    popup.setAttribute('aria-hidden', 'true');
    removeClass(document.body, 'tb4c-comment-portal-active');
    return true;
  }

  function lazyMedia(scope) {
    qsa('img,iframe,video', scope || document).forEach(function (el) {
      if (cleaned.has(el)) return;
      cleaned.add(el);
      try {
        if (el.tagName === 'IMG') {
          if (!el.getAttribute('loading')) el.setAttribute('loading', 'lazy');
          if (!el.getAttribute('decoding')) el.setAttribute('decoding', 'async');
          if (!el.getAttribute('fetchpriority')) el.setAttribute('fetchpriority', 'auto');
        } else if (el.tagName === 'IFRAME') {
          if (!el.getAttribute('loading')) el.setAttribute('loading', 'lazy');
        } else if (el.tagName === 'VIDEO') {
          if (!el.getAttribute('preload')) el.setAttribute('preload', 'metadata');
          if (!el.getAttribute('playsinline')) el.setAttribute('playsinline', '');
        }
      } catch (e) {}
    });
  }
  function installVideoPause() {
    if (!('IntersectionObserver' in window) || videoObserver || isSlowDevice()) return;
    var videos = qsa('video').filter(function (video) { return !video.getAttribute('data-tb4c-io-video'); });
    if (!videos.length) return;
    videoObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        var video = entry.target;
        if (!video || entry.isIntersecting) return;
        if (closest(video, '.tb4c-video-mini-player,.is-floating,.tb4c-reel-viewer.is-open')) return;
        try { if (!video.paused) video.pause(); } catch (e) {}
      });
    }, { root: null, threshold: 0.02, rootMargin: '160px 0px' });
    videos.forEach(function (video) {
      video.setAttribute('data-tb4c-io-video', '1');
      try { videoObserver.observe(video); } catch (e) {}
    });
  }

  function clean(scope) {
    bootClasses();
    normalizeComposer();
    normalizeNavAndFloating();
    normalizePopups();
    dedupeActionRows(scope || document);
    lazyMedia(scope || document);
    installVideoPause();
  }
  function schedule(scope) {
    if (scheduled) return;
    scheduled = true;
    idle(function () {
      scheduled = false;
      clean(scope || document);
    }, 900);
  }

  function legacyUrl() {
    if (!cfg.legacyJsLazy || !cfg.legacyScriptUrl) return '';
    var url = String(cfg.legacyScriptUrl || '');
    var ver = String(cfg.legacyScriptVersion || cfg.version || '');
    if (ver && url.indexOf('ver=') < 0) url += (url.indexOf('?') >= 0 ? '&' : '?') + 'ver=' + encodeURIComponent(ver);
    return url;
  }
  function loadLegacy(reason, done) {
    if (legacyState.loaded || window.tb4cCommunityRuntimeBooted42114) {
      legacyState.loaded = true;
      if (done) done();
      return;
    }
    var url = legacyUrl();
    if (!url) { if (done) done(); return; }
    if (done) legacyState.callbacks.push(done);
    if (legacyState.loading) return;
    legacyState.loading = true;
    addClass(root, 'tb4c-v42126-legacy-loading');
    var script = document.createElement('script');
    script.src = url;
    script.async = true;
    script.defer = true;
    script.setAttribute('data-tb4c-legacy-loader', reason || 'runtime');
    script.onload = function () {
      legacyState.loading = false;
      legacyState.loaded = true;
      addClass(root, 'tb4c-v42126-legacy-loaded');
      schedule(document);
      var callbacks = legacyState.callbacks.splice(0);
      callbacks.forEach(function (cb) { try { cb(); } catch (e) {} });
    };
    script.onerror = function () {
      legacyState.loading = false;
      addClass(root, 'tb4c-v42126-legacy-error');
      legacyState.callbacks.splice(0);
    };
    (document.head || document.body || root).appendChild(script);
  }
  function needsLegacyTarget(target) {
    if (!target || !target.closest) return false;
    return target.closest('[data-tb4c-react],[data-tb4c-edit-post],[data-tb4c-delete-post],[data-tb4c-comment-action],[data-tb4c-video-play],[data-tb4c-video-slot],.tb4c-post-video-slot,.tb4c-album-expand,[data-tb4c-poll-vote]');
  }
  function replayEvent(target, type) {
    if (!target || !type) return;
    raf(function () {
      try {
        var evt = new Event(type, { bubbles: true, cancelable: true });
        evt.tb4cLegacyReplayed42126 = true;
        target.dispatchEvent(evt);
      } catch (e) {}
    });
  }

  function installDelegation() {
    if (root.getAttribute('data-tb4c-runtime-delegated')) return;
    root.setAttribute('data-tb4c-runtime-delegated', '1');
    document.addEventListener('click', function (event) {
      var target = event.target;
      var panelBtn = closest(target, '[data-tb4c-smart-panel-btn]');
      if (panelBtn) {
        var panelModal = closest(panelBtn, '#tb4cComposerModal,[data-tb4c-composer-modal]');
        if (panelModal) {
          event.preventDefault();
          qsa('[data-tb4c-smart-panel-btn]', panelModal).forEach(function (button) { button.classList.remove('is-active'); button.setAttribute('aria-expanded', 'false'); });
          panelBtn.classList.add('is-active');
          panelBtn.setAttribute('aria-expanded', 'true');
          syncComposerPanels(panelModal);
          return;
        }
      }
      var uploadLabel = closest(target, '[data-tb4c-smart-upload]');
      if (uploadLabel && uploadLabel.tagName !== 'LABEL') {
        var uploadModal = closest(uploadLabel, '#tb4cComposerModal,[data-tb4c-composer-modal]') || getComposerModal();
        var fileInput = uploadModal ? qs('#tb4c-post-media-file,input[type="file"][name="media_files[]"]', uploadModal) : null;
        if (fileInput && fileInput.click) { event.preventDefault(); fileInput.click(); return; }
      }
      var openBtn = closest(target, '[data-tb4c-open-composer]');
      if (openBtn) {
        if (openComposer(openBtn.getAttribute('data-tb4c-tool-focus') || null)) {
          event.preventDefault();
          return;
        }
      }
      var closeBtn = closest(target, '[data-tb4c-close-modal]');
      if (closeBtn && closeComposer(closeBtn)) { event.preventDefault(); return; }

      var lightboxTrigger = closest(target, '[data-tb4c-lightbox]');
      if (lightboxTrigger && openMicroLightbox(lightboxTrigger)) { event.preventDefault(); return; }
      var lightboxClose = closest(target, '[data-tb4c-lightbox-close]');
      if (lightboxClose) { event.preventDefault(); closeMicroLightbox(); return; }
      var commentOpen = closest(target, '[data-tb4c-open-comment-popup]');
      if (commentOpen && openMicroCommentPopup(commentOpen)) { event.preventDefault(); return; }
      var commentClose = closest(target, '[data-tb4c-close-comment-popup]');
      if (commentClose && closeMicroCommentPopup(commentClose)) { event.preventDefault(); return; }

      if (needsLegacyTarget(target) && !legacyState.loaded && !event.tb4cLegacyReplayed42126) {
        event.preventDefault();
        event.stopPropagation();
        loadLegacy('heavy-action', function () { replayEvent(target, 'click'); });
        return;
      }
      schedule(closest(target, '.tb4c-shell,.tb4-home-feed-main,.tb4c-post-card') || document);
    }, true);

    document.addEventListener('change', function (event) {
      if (!event.target || !event.target.matches || !event.target.matches('#tb4c-post-media-file,input[type="file"][name="media_files[]"]')) return;
      var modal = closest(event.target, '#tb4cComposerModal,[data-tb4c-composer-modal]') || getComposerModal();
      updateComposerUploadStatus(modal || getComposerModal());
    }, true);
    document.addEventListener('submit', function (event) {
      if (event.tb4cLegacyReplayed42126 || legacyState.loaded) return;
      if (!event.target || !(event.target.matches && event.target.matches('#tb4cCommunityForm,[data-tb4c-community-form],#tb4cCommentForm,[data-tb4c-comment-form]'))) return;
      event.preventDefault();
      event.stopPropagation();
      var target = event.target;
      loadLegacy('write-submit', function () { replayEvent(target, 'submit'); });
    }, true);
    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') return;
      qsa('#tb4cComposerModal.is-open,[data-tb4c-composer-modal].is-open').forEach(function (modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
      });
      closeMicroLightbox();
      qsa('[data-tb4c-comment-popup].is-open,.tb4c-comment-popup.is-open').forEach(function (popup) {
        popup.classList.remove('is-open');
        popup.setAttribute('aria-hidden', 'true');
      });
    }, true);
  }

  bootClasses();
  installDelegation();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { clean(document); }, { once: true });
  } else {
    clean(document);
  }
  window.addEventListener('load', function () { schedule(document); }, { once: true, passive: true });
  window.addEventListener('pageshow', function () { schedule(document); }, { once: true, passive: true });
  window.addEventListener('beforeunload', function () { if (videoObserver) { try { videoObserver.disconnect(); } catch (e) {} } }, { once: true });

  window.tb4cRuntime42126 = {
    version: (cfg && cfg.version) || '4.2.126',
    clean: clean,
    schedule: schedule,
    loadLegacy: loadLegacy,
    isSlowDevice: isSlowDevice
  };
})(window, document);


/* v4.2.237 — Mobile Click Safe Guard: keeps Feed Main single-column after any tap/click. */
(function () {
  'use strict';
  var doc = document;
  var root = null;
  var mq = window.matchMedia ? window.matchMedia('(max-width: 860px)') : null;
  function isCommunityPage() {
    root = root || doc.getElementById('tb4c-community-app');
    return !!(root || (doc.body && doc.body.classList && doc.body.classList.contains('tb4c-community-page')));
  }
  function isMobile() {
    return mq ? mq.matches : (window.innerWidth <= 860);
  }
  function setHidden(el, shouldHide) {
    if (!el || el.getAttribute('data-tb4c-module') === 'center-feed') return;
    if (shouldHide) {
      if (!el.hasAttribute('data-tb4c-v4237-prev-display')) {
        el.setAttribute('data-tb4c-v4237-prev-display', el.style.display || '');
      }
      el.setAttribute('aria-hidden', 'true');
      el.style.display = 'none';
      el.style.visibility = 'hidden';
      el.style.pointerEvents = 'none';
      el.style.width = '0px';
      el.style.maxWidth = '0px';
      el.style.minWidth = '0px';
      el.style.height = '0px';
      el.style.overflow = 'hidden';
    } else if (el.hasAttribute('data-tb4c-v4237-prev-display')) {
      var oldDisplay = el.getAttribute('data-tb4c-v4237-prev-display') || '';
      el.style.display = oldDisplay;
      el.style.visibility = '';
      el.style.pointerEvents = '';
      el.style.width = '';
      el.style.maxWidth = '';
      el.style.minWidth = '';
      el.style.height = '';
      el.style.overflow = '';
      el.removeAttribute('aria-hidden');
      el.removeAttribute('data-tb4c-v4237-prev-display');
    }
  }
  function guard() {
    if (!isCommunityPage()) return;
    doc.documentElement.classList.add('tb4c-v4237-mobile-click-safe');
    root = root || doc.getElementById('tb4c-community-app');
    var mobile = isMobile();
    var selector = 'body.tb4c-community-page [data-tb4c-module="left-sidebar"],body.tb4c-community-page [data-tb4c-module="right-sidebar"],body.tb4c-community-page .tb4c-social-left,body.tb4c-community-page .tb4c-social-right,body.tb4c-community-page .tb4c-left-sidebar-final,body.tb4c-community-page .tb4c-right-sidebar-final,body.tb4c-community-page .tb4c-right-visible-final';
    Array.prototype.forEach.call(doc.querySelectorAll(selector), function (el) { setHidden(el, mobile); });
    if (root) {
      Array.prototype.forEach.call(root.querySelectorAll('.tb4c-member-area-page,.tb4c-member-area-container,.tb4c-member-area-layout,.tb4c-member-profile-grid,.tb4c-profile-layout,.tb4c-profile-feed,.tb4c-profile-side,.tb4c-member-card,.tb4c-member-area-panel'), function (el) { setHidden(el, true); });
      root.style.maxWidth = '100%';
      root.style.overflowX = 'hidden';
    }
  }
  function scheduleGuard() {
    window.requestAnimationFrame ? window.requestAnimationFrame(guard) : setTimeout(guard, 0);
  }
  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', scheduleGuard, { once: true });
  else scheduleGuard();
  doc.addEventListener('click', scheduleGuard, true);
  doc.addEventListener('touchend', scheduleGuard, true);
  window.addEventListener('resize', scheduleGuard);
})();

/* v4.2.238 — Click Layout Stable Guard
   Normalizes feed/single post card layout after any tap/click/AJAX update. */
(function tb4cClickLayoutStable4238(window, document) {
  'use strict';
  if (!window || !document || window.tb4cClickLayoutStable4238Booted) return;
  window.tb4cClickLayoutStable4238Booted = true;

  function qsa(selector, scope) {
    try { return Array.prototype.slice.call((scope || document).querySelectorAll(selector)); }
    catch (e) { return []; }
  }
  function closest(node, selector) {
    try { return node && node.closest ? node.closest(selector) : null; }
    catch (e) { return null; }
  }
  function setStyles(el, styles) {
    if (!el || !el.style) return;
    Object.keys(styles).forEach(function (key) {
      try { el.style[key] = styles[key]; } catch (e) {}
    });
  }
  function mark() {
    try { document.documentElement.classList.add('tb4c-v4238-click-layout-stable'); } catch (e) {}
    try { if (document.body) document.body.classList.add('tb4c-v4238-click-layout-stable-body'); } catch (e) {}
  }
  function normalizeSurface(surface) {
    if (!surface) return;
    surface.setAttribute('data-tb4c-click-layout-stable', '4238');
    setStyles(surface, { width: '100%', maxWidth: '100%', minWidth: '0px', overflowX: 'hidden' });

    qsa('.tb4c-container,.tb4c-social-layout,.tb4c-module-grid,.tb4c-social-feed,.tb4c-feed-list,.tb4c-single-layout', surface).forEach(function (el) {
      setStyles(el, {
        width: '100%', maxWidth: surface.classList && surface.classList.contains('tb4c-single-social-page') ? '760px' : '720px',
        minWidth: '0px', display: 'block', gridTemplateColumns: 'none', marginLeft: 'auto', marginRight: 'auto', float: 'none', overflowX: 'hidden'
      });
    });
    if (window.innerWidth <= 767) {
      qsa('.tb4c-container,.tb4c-social-layout,.tb4c-module-grid,.tb4c-social-feed,.tb4c-feed-list,.tb4c-single-layout', surface).forEach(function (el) {
        setStyles(el, { maxWidth: '100%' });
      });
    }

    qsa('.tb4c-post-card,.tb4c-social-post-card,.tb4c-single-post-card,.tb4c-native-post,.tb4c-ig-post', surface).forEach(function (card) {
      setStyles(card, { display: 'block', position: 'relative', width: '100%', maxWidth: '100%', minWidth: '0px', float: 'none', clear: 'both', gridColumn: '1 / -1', overflowX: 'hidden' });
      card.setAttribute('data-tb4c-card-stable', '4238');
    });

    qsa('.tb4c-post-head,.tb4c-post-inline-text,.tb4c-post-type-tag-strip,.tb4c-type-tag-strip,.tb4c-ig-custom-tags,.tb4c-tags,.tb4c-poll-card,.tb4c-post-actions,.tb4c-social-actions,.tb4c-ig-action-row,.tb4c-ig-single-media-column,.tb4c-ig-single-detail-column,.tb4c-single-content,.tb4c-single-tags,.tb4c-comments-card', surface).forEach(function (el) {
      setStyles(el, { width: '100%', maxWidth: '100%', minWidth: '0px', float: 'none', clear: 'both', gridColumn: '1 / -1' });
    });

    qsa('.tb4c-post-inline-text,.tb4c-ig-single-detail-column', surface).forEach(function (el) {
      setStyles(el, { display: 'block' });
    });

    qsa('.tb4c-post-inline-text h2,.tb4c-post-inline-text p,.tb4c-single-title,.tb4c-single-content,.tb4c-single-content p,.tb4c-poll-head strong,.tb4c-author-row', surface).forEach(function (el) {
      setStyles(el, { maxWidth: '100%', minWidth: '0px', whiteSpace: 'normal', overflowWrap: 'break-word', wordBreak: 'normal', writingMode: 'horizontal-tb' });
    });

    qsa('.tb4c-poll-options', surface).forEach(function (el) {
      setStyles(el, { display: 'grid', gridTemplateColumns: '1fr', gap: '8px', width: '100%', maxWidth: '100%', minWidth: '0px' });
    });
    qsa('.tb4c-poll-option', surface).forEach(function (el) {
      setStyles(el, { position: 'relative', display: 'grid', gridTemplateColumns: 'minmax(0,1fr) auto', alignItems: 'center', gap: '10px', width: '100%', maxWidth: '100%', minWidth: '0px', overflow: 'hidden', whiteSpace: 'normal', textAlign: 'left' });
    });
    qsa('.tb4c-poll-option i', surface).forEach(function (el) {
      setStyles(el, { position: 'absolute', zIndex: '0', top: '0', left: '0', bottom: '0', display: 'block', height: '100%', pointerEvents: 'none' });
    });
    qsa('.tb4c-poll-option span,.tb4c-poll-option em', surface).forEach(function (el) {
      setStyles(el, { position: 'relative', zIndex: '1', minWidth: '0px', whiteSpace: 'normal', overflowWrap: 'break-word', wordBreak: 'normal' });
    });
    qsa('.tb4c-poll-option em', surface).forEach(function (el) {
      setStyles(el, { justifySelf: 'end', whiteSpace: 'nowrap' });
    });
    qsa('.tb4c-post-actions,.tb4c-social-actions,.tb4c-ig-action-row,.tb4c-single-actions', surface).forEach(function (row) {
      setStyles(row, { display: 'grid', gridTemplateColumns: 'repeat(5,minmax(42px,1fr))', alignItems: 'center', gap: '0px', overflow: 'hidden', width: '100%', maxWidth: '100%', minWidth: '0px' });
      if (window.innerWidth <= 767) setStyles(row, { gridTemplateColumns: 'repeat(5,minmax(36px,1fr))' });
    });
  }
  function normalizeAll() {
    mark();
    qsa('#tb4c-community-app,.tb4c-single-social-page').forEach(normalizeSurface);
  }
  function schedule() {
    if (window.requestAnimationFrame) window.requestAnimationFrame(normalizeAll);
    else window.setTimeout(normalizeAll, 0);
  }
  function onInteraction(event) {
    var action = closest(event.target, '[data-tb4c-poll-vote],[data-tb4c-react],[data-tb4c-copy-link],[data-tb4c-delete-post],[data-tb4c-comment-action],.tb4c-post-inline-text,.tb4c-post-actions button,.tb4c-post-actions a,.tb4c-poll-option');
    if (!action) return;
    var surface = closest(action, '#tb4c-community-app,.tb4c-single-social-page');
    if (!surface) return;
    var card = closest(action, '.tb4c-post-card,.tb4c-single-post-card');
    if (card) card.classList.add('tb4c-v4238-interacting');
    schedule();
    window.setTimeout(schedule, 60);
    window.setTimeout(schedule, 260);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', schedule, { once: true });
  else schedule();
  window.addEventListener('load', schedule, { once: true, passive: true });
  window.addEventListener('pageshow', schedule, { passive: true });
  window.addEventListener('resize', schedule, { passive: true });
  document.addEventListener('click', onInteraction, true);
  document.addEventListener('touchend', onInteraction, true);

  try {
    var mo = new MutationObserver(function (mutations) {
      for (var i = 0; i < mutations.length; i += 1) {
        if (mutations[i].addedNodes && mutations[i].addedNodes.length) { schedule(); return; }
      }
    });
    mo.observe(document.documentElement || document.body, { childList: true, subtree: true });
  } catch (e) {}

  window.tb4cClickLayoutStable4238 = { normalize: normalizeAll, schedule: schedule };
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

