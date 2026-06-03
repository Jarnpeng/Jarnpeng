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
    return !!qs('.tb4c-shell,#tb4c-community-app,.tb4-home-feed-main,.tb4c-member-area-shell,[data-tb4c-community-surface]');
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
    return target.closest('[data-tb4c-react],[data-tb4c-edit-post],[data-tb4c-delete-post],[data-tb4c-follow],[data-tb4c-comment-action],[data-tb4c-open-reels],[data-tb4c-close-reels],[data-tb4c-reel-prev],[data-tb4c-reel-next],[data-tb4c-video-play],[data-tb4c-video-slot],.tb4c-post-video-slot,.tb4c-album-expand,[data-tb4c-poll-vote],[data-tb4c-scroll-target]');
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
      schedule(closest(target, '.tb4c-shell,.tb4-home-feed-main,.tb4c-member-area-shell,.tb4c-post-card') || document);
    }, true);

    document.addEventListener('change', function (event) {
      if (!event.target || !event.target.matches || !event.target.matches('#tb4c-post-media-file,input[type="file"][name="media_files[]"]')) return;
      var modal = closest(event.target, '#tb4cComposerModal,[data-tb4c-composer-modal]') || getComposerModal();
      updateComposerUploadStatus(modal || getComposerModal());
    }, true);
    document.addEventListener('submit', function (event) {
      if (event.tb4cLegacyReplayed42126 || legacyState.loaded) return;
      if (!event.target || !(event.target.matches && event.target.matches('#tb4cCommunityForm,[data-tb4c-community-form],#tb4cCommentForm,[data-tb4c-comment-form],#tb4cMemberAreaProfileForm'))) return;
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
