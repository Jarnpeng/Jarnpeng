(function () {
  'use strict';

  var cfg = window.tb4cCommunity || {};
  window.tb4cDisableLegacyComposerControllers42111 = true;
  var doc = document;
  var root = doc.documentElement;

  /* v4.2.114 — Performance Anti-Lag Boot Guard
   * ถ้าหน้านี้ไม่มีผิวชุมชนจริง ให้หยุด runtime ชุดใหญ่ทันที เพื่อลดหน่วงบนหน้าเว็บทั่วไป/หน้าแรกที่ยังไม่ได้แสดง feed
   */
  if (window.tb4cCommunityRuntimeBooted42114) return;
  window.tb4cCommunityRuntimeBooted42114 = true;
  function tb4cHasCommunitySurface42114() {
    if (!doc.querySelector) return true;
    return !!doc.querySelector('.tb4c-shell, #tb4c-community-app, .tb4c-post-card, .tb4c-comments-card, .tb4c-home-menu-feed, .tb4-home-feed-main, .tb4c-member-area-shell, #tb4cCommunityForm, [data-tb4c-video-slot], [data-tb4c-community-surface]');
  }
  if (!tb4cHasCommunitySurface42114()) {
    try { root.classList.add('tb4c-v42114-runtime-skipped'); } catch (ignoreSkipClass) {}
    return;
  }
  try { root.classList.add('tb4c-v42114-anti-lag'); } catch (ignoreAntiLagClass) {}

  /* v4.2.124 — Legacy Source Prune + Lazy Feature Runtime
   * Removed the old global EventTarget monkeypatch/throttle layer from legacy.js.
   * community-runtime.js owns duplicate cleanup, slow-device classes, and lazy-loads this legacy feature runtime on demand.
   */
  try { root.classList.add('tb4c-v42126-legacy-feature-loaded'); } catch (ignoreLegacyPruneClass) {}

  /* v4.2.101 — Performance Hard Cut Boot
   * ลดหน่วง: ตัด observer/scroll watcher ที่ไม่จำเป็น และทำงานเฉพาะตอนผู้ใช้กดจริง
   */
  try { root.classList.add('tb4c-v42101-performance-hard-cut'); } catch (ignorePerfRoot) {}
  function tb4cPerf101BodyReady() {
    try { if (doc.body) doc.body.classList.add('tb4c-v42101-performance-hard-cut'); } catch (ignorePerfBody) {}
  }
  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', tb4cPerf101BodyReady, { once: true });
  else tb4cPerf101BodyReady();


  /* v4.2.101: removed v4.2.99 eager Reel Hard Nav Rescue. Lightweight Reel nav is handled by the v4.2.101 runtime below. */

  function addClass(el, className) {
    if (!el) return;
    if (el.classList) {
      el.classList.add(className);
      return;
    }
    if ((' ' + el.className + ' ').indexOf(' ' + className + ' ') === -1) {
      el.className += ' ' + className;
    }
  }

  function removeClass(el, className) {
    if (!el) return;
    if (el.classList) {
      el.classList.remove(className);
      return;
    }
    el.className = (' ' + el.className + ' ').replace(' ' + className + ' ', ' ').replace(/^\s+|\s+$/g, '');
  }


  function hasClass(el, className) {
    if (!el) return false;
    if (el.classList) return el.classList.contains(className);
    return (' ' + (el.className || '') + ' ').indexOf(' ' + className + ' ') !== -1;
  }

  var proto = window.Element && Element.prototype ? Element.prototype : null;
  var matches = proto && (proto.matches || proto.msMatchesSelector || proto.webkitMatchesSelector);
  if (!matches) {
    matches = function (selector) {
      var nodes = (this.parentNode || doc).querySelectorAll(selector);
      var i = -1;
      while (nodes[++i] && nodes[i] !== this) {}
      return !!nodes[i];
    };
  }


  addClass(root, 'tb4c-js-ready');
  /* v4.2.124: stop adding every historical UI patch class. Keep only stable feature-state classes needed by current templates. */
  addClass(root, 'tb4c-v42126-legacy-feature-loaded');
    addClass(root, 'tb4c-v471-member-dedupe');
  addClass(root, 'tb4c-v472-member-cover-feed');
  addClass(root, 'tb4c-v473-home-menu-feed');
  addClass(root, 'tb4c-v475-member-own-wide-feed');
  addClass(root, 'tb4c-v476-member-red-fit-left-conversations');
  addClass(root, 'tb4c-v477-member-conversation-reply-alert');
  addClass(root, 'tb4c-v478-member-profile-media-sync');
  addClass(root, 'tb4c-v479-member-premium-compact');
  addClass(root, 'tb4c-v480-member-premium-clean-fit');
  addClass(root, 'tb4c-v481-member-inner-clean-fit');
  addClass(root, 'tb4c-v482-member-premium-flow');
  addClass(root, 'tb4c-v484-member-post-footer-composer-clean');
  addClass(root, 'tb4c-v485-feed-standard-composer-hardline');
  addClass(root, 'tb4c-v486-feed-popup-clone');
  addClass(root, 'tb4c-v487-post-tag-clean');
  addClass(root, 'tb4c-v488-feed-clone-final');
  addClass(root, 'tb4c-v489-composer-tag-type-reel-fit');
  addClass(root, 'tb4c-v490-composer-feed-hard-reset');
    addClass(root, 'tb4c-v491-feed-ratio-copy');
  addClass(root, 'tb4c-v492-feed-source-clone');
  addClass(root, 'tb4c-v493-feed-popup-rebuild');
  addClass(root, 'tb4c-v494-feed-popup-top-lock');
  addClass(root, 'tb4c-v42105-smart-composer-center');

  function ensureCommunityActivePageClass() {
    if (!doc.body || !doc.querySelector) return;
    if (doc.querySelector('.tb4c-shell, #tb4c-community-app, [data-tb4c-video-slot], .tb4c-post-card, .tb4c-comments-card')) {
      addClass(doc.body, 'tb4c-active-page');
      addClass(doc.body, 'tb4c-v429-runtime-ready');
      addClass(doc.body, 'tb4c-v430-runtime-ready');
      addClass(root, 'tb4c-v42126-legacy-feature-loaded');
  addClass(root, 'tb4c-v458-composer-draft-lock');
  addClass(root, 'tb4c-v459-post-bounds-final');
      addClass(root, 'tb4c-v462-composer-reels-fix');
      addClass(root, 'tb4c-v463-reel-cover-composer-return');
      addClass(root, 'tb4c-v464-reel-safe-next');
    }
  }

  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', ensureCommunityActivePageClass);
  } else {
    ensureCommunityActivePageClass();
  }
  window.setTimeout(ensureCommunityActivePageClass, 120);
  window.setTimeout(ensureCommunityActivePageClass, 800);

  if (!window.CSS || !CSS.supports || !CSS.supports('display', 'grid')) addClass(root, 'no-css-grid');
  if (!window.fetch) addClass(root, 'tb4c-no-fetch');
  if (!navigator.clipboard) addClass(root, 'tb4c-no-clipboard');
  if (window.matchMedia && window.matchMedia('(hover: none) and (pointer: coarse)').matches) addClass(root, 'tb4c-touch-device');

  function detectIconFont() {
    var probe = doc.createElement('i');
    var before;
    probe.className = 'ph ph-house';
    probe.style.position = 'absolute';
    probe.style.left = '-9999px';
    probe.style.top = '-9999px';
    doc.body.appendChild(probe);
    try {
      before = window.getComputedStyle ? window.getComputedStyle(probe, ':before').getPropertyValue('content') : '';
    } catch (e) {
      before = '';
    }
    doc.body.removeChild(probe);
    if (!before || before === 'none' || before === 'normal' || before === '""') addClass(root, 'tb4c-icons-failed');
  }

  if (doc.body) window.setTimeout(detectIconFont, 400);
  else doc.addEventListener('DOMContentLoaded', function () { window.setTimeout(detectIconFont, 400); });

  function closest(el, selector) {
    while (el && el !== doc) {
      if (el.nodeType === 1 && matches.call(el, selector)) return el;
      el = el.parentNode;
    }
    return null;
  }


  var commentPortalSeq = 0;

  function cssEscapeAttr(value) {
    return String(value || '').replace(/\\/g, '\\\\').replace(/"/g, '\\"');
  }

  function ensureCommentRootId(rootEl) {
    var id;
    if (!rootEl) return '';
    id = rootEl.getAttribute('data-tb4c-comment-root-id');
    if (!id) {
      commentPortalSeq += 1;
      id = 'tb4c-comment-root-' + commentPortalSeq + '-' + Math.floor(Math.random() * 100000);
      rootEl.setAttribute('data-tb4c-comment-root-id', id);
    }
    return id;
  }

  function getCommentPopupFromRoot(rootEl) {
    var id, popup;
    if (!rootEl) return null;
    popup = rootEl.querySelector ? rootEl.querySelector('[data-tb4c-comment-popup]') : null;
    if (popup) return popup;
    id = rootEl.getAttribute ? rootEl.getAttribute('data-tb4c-comment-root-id') : '';
    if (!id || !doc.querySelector) return null;
    return doc.querySelector('[data-tb4c-comment-popup][data-tb4c-root-id="' + cssEscapeAttr(id) + '"]');
  }

  function getCommentRootFromNode(node) {
    var rootEl, popup, id;
    rootEl = closest(node, '[data-tb4c-comments-root]');
    if (rootEl) return rootEl;
    popup = closest(node, '[data-tb4c-comment-popup]');
    id = popup && popup.getAttribute ? popup.getAttribute('data-tb4c-root-id') : '';
    if (!id || !doc.querySelector) return null;
    return doc.querySelector('[data-tb4c-comments-root][data-tb4c-comment-root-id="' + cssEscapeAttr(id) + '"]');
  }

  function getCommentScope(rootEl, fallbackNode) {
    var popup;
    if (rootEl) {
      popup = getCommentPopupFromRoot(rootEl);
      if (popup) return popup;
      return rootEl;
    }
    popup = fallbackNode ? closest(fallbackNode, '[data-tb4c-comment-popup]') : null;
    return popup || null;
  }

  function getCommentCombinedNodes(rootEl) {
    var nodes = [];
    var popup;
    if (rootEl) nodes.push(rootEl);
    popup = rootEl ? getCommentPopupFromRoot(rootEl) : null;
    if (popup && nodes.indexOf(popup) === -1) nodes.push(popup);
    return nodes;
  }

  function portalCommentPopup(rootEl, popup) {
    var id, placeholder;
    if (!rootEl || !popup || !doc.body) return;
    id = ensureCommentRootId(rootEl);
    popup.setAttribute('data-tb4c-root-id', id);
    if (popup.parentNode === doc.body) {
      addClass(popup, 'tb4c-comment-is-portaled');
      addClass(doc.body, 'tb4c-comment-portal-active');
      return;
    }
    if (!popup.tb4cOriginalParent) popup.tb4cOriginalParent = popup.parentNode;
    if (!popup.tb4cPlaceholder) {
      placeholder = doc.createComment('tb4c comment popup portal placeholder');
      popup.tb4cPlaceholder = placeholder;
      if (popup.parentNode) popup.parentNode.insertBefore(placeholder, popup);
    }
    doc.body.appendChild(popup);
    addClass(popup, 'tb4c-comment-is-portaled');
    addClass(doc.body, 'tb4c-comment-portal-active');
  }

  function restoreCommentPopup(popup) {
    var placeholder, parent;
    if (!popup) return;
    removeClass(popup, 'tb4c-comment-is-portaled');
    placeholder = popup.tb4cPlaceholder;
    parent = placeholder && placeholder.parentNode ? placeholder.parentNode : popup.tb4cOriginalParent;
    if (parent && parent !== popup.parentNode) {
      if (placeholder && placeholder.parentNode) {
        placeholder.parentNode.insertBefore(popup, placeholder);
        placeholder.parentNode.removeChild(placeholder);
      } else {
        parent.appendChild(popup);
      }
    }
    popup.tb4cPlaceholder = null;
    if (!doc.querySelector || !doc.querySelector('[data-tb4c-comment-popup].is-open')) {
      if (doc.body) removeClass(doc.body, 'tb4c-comment-portal-active');
    }
  }

  function appHaptic() {
    try { if (navigator.vibrate) navigator.vibrate(8); } catch (e) {}
  }

  function lockAppModal() {
    addClass(root, 'tb4c-modal-open');
    if (doc.body) addClass(doc.body, 'tb4c-modal-open');
  }

  function unlockAppModal() {
    removeClass(root, 'tb4c-modal-open');
    if (doc.body) removeClass(doc.body, 'tb4c-modal-open');
  }

  function setFieldValue(form, name, value) {
    var field = form ? form.querySelector('[name="' + name + '"]') : null;
    if (!field) return;
    field.value = value || '';
  }

  function getInlineComposer() {
    return doc.querySelector('[data-tb4c-inline-composer]');
  }

  function getComposerForm() {
    /* v4.2.55: popup composer is the source of truth again. Do not let any cached inline composer steal the form. */
    var modal = doc.getElementById('tb4cComposerModal');
    var modalForm = modal ? modal.querySelector('#tb4cCommunityForm, [data-tb4c-community-form]') : null;
    if (modalForm) return modalForm;
    var inline = getInlineComposer();
    if (inline) {
      var inlineForm = inline.querySelector('#tb4cCommunityForm, [data-tb4c-community-form]');
      if (inlineForm) return inlineForm;
    }
    return null;
  }

  function openInlineComposer(focusTarget) {
    if (!cfg.isLoggedIn) {
      window.location.href = cfg.loginUrl || '/wp-login.php';
      return null;
    }
    var inline = getInlineComposer();
    if (!inline) return null;
    addClass(inline, 'is-open');
    var panel = inline.querySelector('[data-tb4c-inline-composer-panel]');
    if (panel) panel.setAttribute('aria-hidden', 'false');
    var form = inline.querySelector('#tb4cCommunityForm, [data-tb4c-community-form]');
    var focusSelector = 'input[name="title"]';
    if (focusTarget === 'tag') focusSelector = 'input[name="tags"]';
    else if (focusTarget === 'media' || focusTarget === 'video') focusSelector = 'textarea[name="media_url"], input[type="file"]';
    else if (focusTarget === 'feeling') focusSelector = 'textarea[name="content"]';
    if (form) {
      window.setTimeout(function () {
        var focusEl = form.querySelector(focusSelector) || form.querySelector('input[name="title"]');
        if (focusEl && focusEl.focus) focusEl.focus();
      }, 90);
    }
    return form;
  }

  function closeInlineComposer() {
    var inline = getInlineComposer();
    if (!inline) return false;
    removeClass(inline, 'is-open');
    removeClass(inline, 'is-edit-mode');
    var panel = inline.querySelector('[data-tb4c-inline-composer-panel]');
    if (panel) panel.setAttribute('aria-hidden', 'true');
    return true;
  }

  function showPostingLoader(form) {
    var feed = doc.getElementById('tb4cFeedList');
    var loader = doc.getElementById('tb4cPostSoftLoader');
    if (!loader && feed && feed.parentNode) {
      loader = doc.createElement('div');
      loader.id = 'tb4cPostSoftLoader';
      loader.className = 'tb4c-post-soft-loader';
      loader.innerHTML = '<span>กำลังโพสต์</span><i></i><i></i><i></i>';
      feed.parentNode.insertBefore(loader, feed);
    }
    if (loader) addClass(loader, 'is-active');
    if (form) addClass(form, 'is-posting');
  }

  function hidePostingLoader(form) {
    var loader = doc.getElementById('tb4cPostSoftLoader');
    if (loader) removeClass(loader, 'is-active');
    if (form) removeClass(form, 'is-posting');
  }

  function setComposerMode(mode, data) {
    var modal = doc.getElementById('tb4cComposerModal');
    var form = getComposerForm();
    var inline = getInlineComposer();
    var heading = modal ? modal.querySelector('#tb4cComposerTitle') : null;
    var action = form ? form.querySelector('[data-tb4c-form-action]') : null;
    var submit = form ? form.querySelector('button[type="submit"]') : null;
    if (!form) return;

    form.reset();
    setStatus(form, '', '');
    removeClass(form, 'is-edit-mode');
    if (inline) removeClass(inline, 'is-edit-mode');

    if (mode === 'edit' && data) {
      addClass(form, 'is-edit-mode');
      if (inline) addClass(inline, 'is-edit-mode');
      if (heading) heading.textContent = 'แก้ไขโพสต์';
      if (action) action.value = 'tb4c_update_community_post';
      setFieldValue(form, 'post_id', data.id || '');
      setFieldValue(form, 'title', data.title || '');
      setFieldValue(form, 'content', data.content || '');
      setFieldValue(form, 'post_type', data.type || 'discussion');
      setFieldValue(form, 'visibility', data.visibility || 'public');
      setFieldValue(form, 'media_url', data.mediaAlbum || data.mediaUrl || '');
      setFieldValue(form, 'tags', data.tags || '');
      setFieldValue(form, 'new_tag', '');
      setFieldValue(form, 'feeling', data.feeling || '');
      setFieldValue(form, 'poll_question', data.pollQuestion || '');
      setFieldValue(form, 'poll_options', data.pollOptions || '');
      if (submit) submit.innerHTML = '<span class="tb4c-submit-text"><i class="ph ph-check"></i> บันทึก</span><span class="tb4c-submit-loader" aria-hidden="true"><i></i><i></i><i></i></span>';
      return;
    }

    if (heading) heading.textContent = 'สร้างโพสต์ใหม่';
    if (action) action.value = 'tb4_submit_community_post';
    setFieldValue(form, 'post_id', '');
    setFieldValue(form, 'tags', '');
    setFieldValue(form, 'new_tag', '');
    setFieldValue(form, 'feeling', '');
    setFieldValue(form, 'poll_question', '');
    setFieldValue(form, 'poll_options', '');
    if (submit) submit.innerHTML = '<span class="tb4c-submit-text"><i class="ph ph-paper-plane-tilt"></i> โพสต์</span><span class="tb4c-submit-loader" aria-hidden="true"><i></i><i></i><i></i></span>';
  }

  function openModal(editData, focusTarget) {
    if (!cfg.isLoggedIn) {
      window.location.href = cfg.loginUrl || '/wp-login.php';
      return;
    }
    var modal = doc.getElementById('tb4cComposerModal');
    var inline = getInlineComposer();
    if (!modal) return;
    addClass(root, 'tb4c-v493-feed-popup-rebuild');
    addClass(root, 'tb4c-v494-feed-popup-top-lock');
    if (doc.body) { addClass(doc.body, 'tb4c-v493-feed-popup-rebuild'); addClass(doc.body, 'tb4c-v494-feed-popup-top-lock'); }
    addClass(modal, 'tb4c-v493-feed-popup-modal');
    addClass(modal, 'tb4c-v494-feed-popup-top-lock-modal');
    var panel493 = modal.querySelector ? modal.querySelector('.tb4c-modal-panel') : null;
    if (panel493) { addClass(panel493, 'tb4c-v493-feed-popup-panel'); addClass(panel493, 'tb4c-v494-feed-popup-top-lock-panel'); }
    removeClass(modal, 'tb4c-composer-from-member-area');
    removeClass(modal, 'tb4c-composer-feed-standard');
    if (doc.body) removeClass(doc.body, 'tb4c-member-composer-open');
    if (inline) {
      removeClass(inline, 'is-open');
      var inlinePanel = inline.querySelector('[data-tb4c-inline-composer-panel]');
      if (inlinePanel) inlinePanel.setAttribute('aria-hidden', 'true');
    }
    addClass(root, 'tb4c-v454-popup-composer-tools-poll');
    addClass(root, 'tb4c-v455-popup-composer-force-ready');
    addClass(root, 'tb4c-v456-composer-scroll-tools-row');
  addClass(root, 'tb4c-v458-composer-draft-lock');
  addClass(root, 'tb4c-v459-post-bounds-final');
    addClass(root, 'tb4c-v457-composer-tools-second-row');
    removeClass(modal, 'tb4c-show-media-url');
    removeClass(modal, 'tb4c-show-poll');
    removeClass(modal, 'tb4c-show-feeling');
    if (focusTarget === 'media' || focusTarget === 'video' || focusTarget === 'media-url' || focusTarget === 'image-url') addClass(modal, 'tb4c-show-media-url');
    if (focusTarget === 'poll') addClass(modal, 'tb4c-show-poll');
    if (focusTarget === 'feeling') addClass(modal, 'tb4c-show-feeling');
    setComposerMode(editData ? 'edit' : 'create', editData || null);
    if (window.tb4cPrepareSmartComposer42110) window.tb4cPrepareSmartComposer42110();
    else updateComposerOverlayBounds();
    addClass(modal, 'is-open');
    addClass(modal, 'tb4c-composer-visible-now');
    addClass(modal, 'tb4c-composer-v457-scroll');
    modal.setAttribute('aria-hidden', 'false');
    modal.style.display = 'flex';
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
    if (window.tb4cApplySmartComposer42110) window.tb4cApplySmartComposer42110();
    lockAppModal();
    appHaptic();
    window.setTimeout(function () {
      var selector = 'textarea[name="content"]';
      if (focusTarget === 'tag') selector = 'input[name="tags"]';
      else if (focusTarget === 'new_tag') selector = 'input[name="new_tag"]';
      else if (focusTarget === 'type') selector = 'select[name="post_type"]';
      else if (focusTarget === 'poll') selector = 'input[name="poll_question"]';
      else if (focusTarget === 'media' || focusTarget === 'video' || focusTarget === 'media-url' || focusTarget === 'image-url') selector = 'textarea[name="media_url"], input[type="file"]';
      else if (focusTarget === 'feeling') selector = 'select[name="feeling"]';
      var target = modal.querySelector(selector) || modal.querySelector('textarea[name="content"]') || modal.querySelector('input[name="title"]');
      if (target && target.focus) target.focus();
      if (focusTarget) {
        var row = target ? closest(target, '.tb4c-form-row') : null;
        if (row) {
          try { row.scrollIntoView({ block: 'center', behavior: 'smooth' }); }
          catch (ignoreScroll) { row.scrollIntoView(false); }
          addClass(row, 'tb4c-form-focus-pulse');
          window.setTimeout(function () { removeClass(row, 'tb4c-form-focus-pulse'); }, 1100);
        }
      }
    }, 90);
  }

  function closeModal() {
    closeInlineComposer();
    var modal = doc.getElementById('tb4cComposerModal');
    var form = getComposerForm();
    if (form) saveComposerDraft(form, false);
    if (!modal) return;
    removeClass(modal, 'is-open');
    removeClass(modal, 'tb4c-composer-visible-now');
    removeClass(modal, 'tb4c-composer-from-member-area');
    removeClass(modal, 'tb4c-composer-feed-standard');
    modal.setAttribute('aria-hidden', 'true');
    modal.style.display = '';
    modal.style.visibility = '';
    modal.style.opacity = '';
    if (doc.body) removeClass(doc.body, 'tb4c-member-composer-open');
    unlockAppModal();
  }


  function initComposerSecondRowAndScroll() {
    var modal = doc.getElementById('tb4cComposerModal');
    var triggers = doc.querySelectorAll ? doc.querySelectorAll('.tb4c-popup-composer-trigger-card') : [];
    var i;
    addClass(root, 'tb4c-v457-composer-tools-second-row');
    addClass(root, 'tb4c-v458-composer-draft-lock');
  addClass(root, 'tb4c-v459-post-bounds-final');
    if (modal) addClass(modal, 'tb4c-composer-v457-scroll');
    for (i = 0; i < triggers.length; i++) addClass(triggers[i], 'tb4c-tools-second-row');
  }


  function ensureMediaLightbox() {
    var box = doc.getElementById('tb4cMediaLightbox');
    if (box) return box;
    box = doc.createElement('div');
    box.id = 'tb4cMediaLightbox';
    box.className = 'tb4c-media-lightbox';
    box.setAttribute('aria-hidden', 'true');
    box.innerHTML = '<button type="button" class="tb4c-media-lightbox-backdrop" data-tb4c-lightbox-close aria-label="ปิดตัวอย่าง"></button><div class="tb4c-media-lightbox-panel" role="dialog" aria-modal="true" aria-label="ดูสื่อ"><header class="tb4c-media-lightbox-head"><strong data-tb4c-lightbox-title>ตัวอย่างสื่อ</strong><button type="button" data-tb4c-lightbox-close aria-label="ปิด">×</button></header><div class="tb4c-media-lightbox-body" data-tb4c-lightbox-body></div></div>';
    if (doc.body) doc.body.appendChild(box);
    return box;
  }

  function openMediaLightbox(trigger) {
    var box = ensureMediaLightbox();
    var body = box ? box.querySelector('[data-tb4c-lightbox-body]') : null;
    var title = box ? box.querySelector('[data-tb4c-lightbox-title]') : null;
    var src = trigger ? trigger.getAttribute('data-tb4c-lightbox-src') : '';
    var type = trigger ? trigger.getAttribute('data-tb4c-lightbox-type') : '';
    var label = trigger ? trigger.getAttribute('data-tb4c-lightbox-title') : '';
    var media;
    if (!box || !body || !src) return;
    body.innerHTML = '';
    if (title) title.textContent = label || 'ตัวอย่างสื่อ';

    if (type === 'video') {
      media = doc.createElement('video');
      media.controls = true;
      media.playsInline = true;
      media.preload = 'metadata';
      media.src = src;
      body.appendChild(media);
      try { media.play(); } catch (e) {}
    } else {
      media = doc.createElement('img');
      media.src = src;
      media.alt = label || '';
      media.decoding = 'async';
      body.appendChild(media);
    }

    addClass(box, 'is-open');
    box.setAttribute('aria-hidden', 'false');
    lockAppModal();
    appHaptic();
  }

  function closeMediaLightbox() {
    var box = doc.getElementById('tb4cMediaLightbox');
    var body;
    if (!box) return;
    body = box.querySelector('[data-tb4c-lightbox-body]');
    if (body) {
      Array.prototype.forEach.call(body.querySelectorAll('video'), function (video) {
        try { video.pause(); } catch (e) {}
      });
      body.innerHTML = '';
    }
    removeClass(box, 'is-open');
    box.setAttribute('aria-hidden', 'true');
    unlockAppModal();
  }


  function tb4cBuildComposerTitle(form) {
    var title = form ? form.querySelector('[name="title"]') : null;
    var content = form ? form.querySelector('[name="content"]') : null;
    var mediaUrl = form ? form.querySelector('[name="media_url"]') : null;
    if (!title) return;
    if (String(title.value || '').trim()) return;
    var source = String((content && content.value) || '').replace(/\s+/g, ' ').trim();
    if (!source && mediaUrl && String(mediaUrl.value || '').trim()) source = 'โพสต์สื่อใหม่';
    if (!source) source = 'โพสต์ใหม่';
    title.value = source.slice(0, 86);
  }

  function tb4cNormalizePostFooters(scope) {
    var base = scope || doc;
    var extras, actions, i;
    if (!base || !base.querySelectorAll) return;
    extras = base.querySelectorAll('.tb4c-reaction-summary, .tb4c-legacy-post-summary, .tb4c-native-reply-row, .tb4c-comment-preview-row');
    for (i = 0; i < extras.length; i += 1) {
      extras[i].setAttribute('hidden', 'hidden');
      extras[i].style.display = 'none';
    }
    actions = base.querySelectorAll('.tb4c-post-actions, .tb4c-single-actions, .tb4c-ig-action-row');
    for (i = 0; i < actions.length; i += 1) {
      addClass(actions[i], 'tb4c-force-one-line-actions');
    }
  }
  function setStatus(form, message, type) {
    var status = form ? form.querySelector('[data-tb4c-form-status]') : null;
    if (!status) return;
    removeClass(status, 'is-success');
    removeClass(status, 'is-error');
    if (type) addClass(status, 'is-' + type);
    if (message && String(message).indexOf('tb4c-status-dots') !== -1) status.innerHTML = message;
    else status.textContent = message || '';
  }

  function parseJSON(text) {
    try {
      return JSON.parse(text);
    } catch (e) {
      return null;
    }
  }

  function xhrPost(formData, done, fail) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', cfg.ajaxUrl || '/wp-admin/admin-ajax.php', true);
    xhr.withCredentials = true;
    xhr.onreadystatechange = function () {
      var json;
      if (xhr.readyState !== 4) return;
      if (xhr.status >= 200 && xhr.status < 300) {
        json = parseJSON(xhr.responseText);
        if (json) done(json);
        else fail(new Error('Invalid JSON response'));
      } else {
        fail(new Error('HTTP ' + xhr.status));
      }
    };
    xhr.onerror = function () { fail(new Error('Network error')); };
    xhr.send(formData);
  }

  function postFormData(formData, done, fail) {
    if (!window.FormData) {
      fail(new Error('เบราว์เซอร์นี้เก่าเกินไปสำหรับการส่งฟอร์มแบบ AJAX'));
      return;
    }

    if (window.fetch) {
      fetch(cfg.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      }).then(function (res) {
        return res.json();
      }).then(function (json) {
        done(json);
      }).catch(function () {
        xhrPost(formData, done, fail);
      });
      return;
    }

    xhrPost(formData, done, fail);
  }

  function storageGet(key) {
    try { return window.localStorage ? localStorage.getItem(key) : null; } catch (e) { return null; }
  }

  function storageSet(key, value) {
    try { if (window.localStorage) localStorage.setItem(key, value); } catch (e) {}
  }

  function storageRemove(key) {
    try { if (window.localStorage) localStorage.removeItem(key); } catch (e) {}
  }

  function createComposerDraftId() {
    return 'draft-' + String(Date.now()) + '-' + String(Math.floor(Math.random() * 1000000));
  }

  function getComposerDraftStatusNode(form) {
    return form ? form.querySelector('[data-tb4c-draft-status]') : null;
  }

  function getComposerDraftHintNode(form) {
    return form ? form.querySelector('[data-tb4c-draft-hint]') : null;
  }

  function updateComposerDraftUi(form, text, type, hint) {
    var node = getComposerDraftStatusNode(form);
    var hintNode = getComposerDraftHintNode(form);
    if (node) {
      node.textContent = text || 'ยังไม่บันทึกร่าง';
      removeClass(node, 'is-success');
      removeClass(node, 'is-warn');
      removeClass(node, 'is-error');
      if (type) addClass(node, 'is-' + type);
    }
    if (hintNode) hintNode.textContent = hint || 'ระบบจะจำข้อมูลร่างไว้ให้อัตโนมัติ แม้ปิดป๊อปอัปหรือกลับเข้ามาใหม่';
  }

  function getComposerDraftMeta(form) {
    var postField = form ? form.querySelector('[data-tb4c-edit-post-id]') : null;
    var draftField = form ? form.querySelector('[name="draft_session_id"]') : null;
    var mode = form ? (form.getAttribute('data-tb4c-composer-mode') || 'create') : 'create';
    var postId = postField ? String(postField.value || '') : '';
    var draftId = draftField ? String(draftField.value || '') : '';
    return { mode: mode, postId: postId, draftId: draftId };
  }

  function getComposerDraftKey(form) {
    var meta = getComposerDraftMeta(form);
    var userKey = cfg.currentUserId ? String(cfg.currentUserId) : 'guest';
    var pageKey = (window.location && window.location.pathname ? window.location.pathname : 'community').replace(/[^a-z0-9]+/ig, '_').replace(/^_+|_+$/g, '') || 'community';
    var modeKey = meta.mode === 'edit' ? 'edit_' + (meta.postId || 'new') : 'create';
    return 'tb4c_composer_draft_' + userKey + '_' + pageKey + '_' + modeKey;
  }

  function readComposerDraft(form) {
    var raw = storageGet(getComposerDraftKey(form));
    if (!raw) return null;
    try { return JSON.parse(raw); } catch (e) { return null; }
  }

  function applyComposerDraft(form, draft) {
    var draftField, allowedMode;
    if (!form || !draft) return false;
    allowedMode = form.getAttribute('data-tb4c-composer-mode') || 'create';
    if ((draft.mode || 'create') !== allowedMode) return false;
    setFieldValue(form, 'title', draft.title || '');
    setFieldValue(form, 'post_type', draft.post_type || 'discussion');
    setFieldValue(form, 'visibility', draft.visibility || 'public');
    setFieldValue(form, 'content', draft.content || '');
    setFieldValue(form, 'tags', draft.tags || '');
    setFieldValue(form, 'new_tag', draft.new_tag || '');
    setFieldValue(form, 'feeling', draft.feeling || '');
    setFieldValue(form, 'poll_question', draft.poll_question || '');
    setFieldValue(form, 'poll_options', draft.poll_options || '');
    setFieldValue(form, 'media_url', draft.media_url || '');
    draftField = form.querySelector('[name="draft_session_id"]');
    if (draftField) draftField.value = draft.draft_id || draftField.value || createComposerDraftId();
    updateComposerDraftUi(form, draft.mode === 'edit' ? 'กู้คืนร่างแก้ไขแล้ว' : 'กู้คืนร่างแล้ว', 'success', 'ระบบจำค่าที่กรอกไว้ล่าสุดแล้ว สามารถทำงานต่อได้ทันที');
    return true;
  }

  function captureComposerDraft(form) {
    var meta, draftField;
    if (!form) return null;
    meta = getComposerDraftMeta(form);
    draftField = form.querySelector('[name="draft_session_id"]');
    if (draftField && !draftField.value) draftField.value = createComposerDraftId();
    return {
      draft_id: draftField ? draftField.value : createComposerDraftId(),
      mode: meta.mode,
      post_id: meta.postId,
      title: ((form.querySelector('[name="title"]') || {}).value || '').trim(),
      post_type: ((form.querySelector('[name="post_type"]') || {}).value || 'discussion'),
      visibility: ((form.querySelector('[name="visibility"]') || {}).value || 'public'),
      content: ((form.querySelector('[name="content"]') || {}).value || ''),
      tags: ((form.querySelector('[name="tags"]') || {}).value || ''),
      new_tag: ((form.querySelector('[name="new_tag"]') || {}).value || ''),
      feeling: ((form.querySelector('[name="feeling"]') || {}).value || ''),
      poll_question: ((form.querySelector('[name="poll_question"]') || {}).value || ''),
      poll_options: ((form.querySelector('[name="poll_options"]') || {}).value || ''),
      media_url: ((form.querySelector('[name="media_url"]') || {}).value || ''),
      updated_at: Date.now()
    };
  }

  function isComposerDraftMeaningful(draft) {
    if (!draft) return false;
    return !!((draft.title && draft.title.replace(/\s+/g, '').length) || (draft.content && draft.content.replace(/\s+/g, '').length) || (draft.tags && draft.tags.replace(/\s+/g, '').length) || (draft.new_tag && draft.new_tag.replace(/\s+/g, '').length) || (draft.feeling && draft.feeling.replace(/\s+/g, '').length) || (draft.poll_question && draft.poll_question.replace(/\s+/g, '').length) || (draft.poll_options && draft.poll_options.replace(/\s+/g, '').length) || (draft.media_url && draft.media_url.replace(/\s+/g, '').length));
  }

  function saveComposerDraft(form, silent) {
    var draft = captureComposerDraft(form);
    if (!draft) return null;
    if (!isComposerDraftMeaningful(draft)) {
      storageRemove(getComposerDraftKey(form));
      updateComposerDraftUi(form, 'ยังไม่บันทึกร่าง', '', 'เริ่มพิมพ์เมื่อไร ระบบจะบันทึกร่างให้อัตโนมัติ');
      return null;
    }
    storageSet(getComposerDraftKey(form), JSON.stringify(draft));
    if (!silent) updateComposerDraftUi(form, 'บันทึกร่างอัตโนมัติแล้ว', 'success', 'ปิดป๊อปอัปหรือกลับเข้ามาใหม่ ข้อมูลยังอยู่และทำงานต่อได้');
    return draft;
  }

  function clearComposerDraft(form) {
    if (!form) return;
    storageRemove(getComposerDraftKey(form));
    updateComposerDraftUi(form, 'ล้างร่างแล้ว', '', 'เริ่มโพสต์ใหม่ได้เลย');
    var draftField = form.querySelector('[name="draft_session_id"]');
    if (draftField) draftField.value = createComposerDraftId();
  }

  function restoreComposerDraftIfAny(form) {
    var draft = readComposerDraft(form);
    if (!draft) {
      updateComposerDraftUi(form, 'ยังไม่บันทึกร่าง', '', 'เริ่มพิมพ์เมื่อไร ระบบจะบันทึกร่างให้อัตโนมัติ');
      return false;
    }
    return applyComposerDraft(form, draft);
  }

  function queueComposerDraftSave(form) {
    if (!form) return;
    if (form.tb4cDraftTimer) window.clearTimeout(form.tb4cDraftTimer);
    form.tb4cDraftTimer = window.setTimeout(function () { saveComposerDraft(form, false); }, 280);
  }

  function fallbackCopy(text, done) {
    var input = doc.createElement('textarea');
    input.value = text;
    input.setAttribute('readonly', 'readonly');
    input.style.position = 'fixed';
    input.style.left = '-9999px';
    doc.body.appendChild(input);
    input.select();
    try { doc.execCommand('copy'); } catch (e) {}
    doc.body.removeChild(input);
    if (done) done();
  }

  function copyText(url, done) {
    if (!url) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(done).catch(function () { fallbackCopy(url, done); });
      return;
    }
    fallbackCopy(url, done);
  }

  function showCopied(copyBtn) {
    var original = copyBtn.getAttribute('data-original-label') || copyBtn.innerHTML;
    copyBtn.setAttribute('data-original-label', original);
    copyBtn.innerHTML = '<i class="ph ph-check"></i> คัดลอกแล้ว';
    window.setTimeout(function () { copyBtn.innerHTML = original; }, 1500);
  }



  function setCommentStatus(rootEl, message, type) {
    var scope = getCommentScope(rootEl);
    var status = scope ? scope.querySelector('[data-tb4c-comment-status]') : null;
    if (!status && rootEl) status = rootEl.querySelector('[data-tb4c-comment-status]');
    if (!status) return;
    removeClass(status, 'is-success');
    removeClass(status, 'is-error');
    if (type) addClass(status, 'is-' + type);
    if (message && String(message).indexOf('tb4c-status-dots') !== -1) status.innerHTML = message;
    else status.textContent = message || '';
  }

  function updateCommentCount(rootEl, count) {
    var nodes = getCommentCombinedNodes(rootEl);
    var seen = [];
    var i, j, countEls, el;
    if (count === undefined || count === null) return;
    for (i = 0; i < nodes.length; i++) {
      countEls = nodes[i] && nodes[i].querySelectorAll ? nodes[i].querySelectorAll('[data-tb4c-comment-count]') : [];
      for (j = 0; j < countEls.length; j++) {
        el = countEls[j];
        if (seen.indexOf(el) !== -1) continue;
        seen.push(el);
        el.textContent = String(count) + ' ความคิดเห็น';
      }
    }
  }


  function getTextClean(el) {
    return (el && el.textContent ? el.textContent : '').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '');
  }

  function refreshCommentSubmitState(form) {
    var textarea = form ? form.querySelector('[data-tb4c-comment-textarea]') : null;
    var submit = form ? form.querySelector('button[type="submit"], .tb4c-comment-send') : null;
    var hasValue = textarea && textarea.value.replace(/\s+/g, '').length > 0;
    if (!form || !submit) return;
    if (hasValue) addClass(form, 'has-value');
    else removeClass(form, 'has-value');
    if (!form.getAttribute('data-tb4c-sending')) submit.disabled = !hasValue;
  }

  function refreshAllCommentSubmitStates(scope) {
    var forms = (scope || doc).querySelectorAll ? (scope || doc).querySelectorAll('[data-tb4c-comment-form]') : [];
    var i;
    for (i = 0; i < forms.length; i++) refreshCommentSubmitState(forms[i]);
  }

  function markNewestComment(scope, item) {
    if (!item) return;
    addClass(item, 'is-new-comment');
    window.setTimeout(function () { removeClass(item, 'is-new-comment'); }, 2600);
    try {
      if (item.scrollIntoView) item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    } catch (e) {
      if (item.scrollIntoView) item.scrollIntoView(false);
    }
  }

  function ensureCommentVisibleState(scope) {
    var list = scope && scope.querySelector ? scope.querySelector('[data-tb4c-comment-list]') : null;
    var empty = scope && scope.querySelector ? scope.querySelector('[data-tb4c-comments-empty]') : null;
    if (list && list.querySelector('.tb4c-comment-item')) {
      list.removeAttribute('hidden');
      if (empty) empty.setAttribute('hidden', 'hidden');
    }
  }

  function buildPreviewDataFromHtml(commentHtml) {
    var temp = doc.createElement('div');
    var item, data, avatar, author, text;
    temp.innerHTML = commentHtml || '';
    item = temp.querySelector('.tb4c-comment-item');
    if (!item) return null;
    avatar = item.querySelector('.tb4c-comment-avatar');
    author = item.querySelector('.tb4c-comment-meta strong');
    text = item.querySelector('[data-tb4c-comment-text]');
    data = {
      id: item.getAttribute('data-comment-id') || '',
      author: getTextClean(author) || 'สมาชิก',
      text: getTextClean(text) || 'แสดงความคิดเห็นแล้ว',
      time: 'ตอนนี้',
      avatar_html: avatar ? avatar.innerHTML : '💬'
    };
    return data;
  }

  function updateCommentPreview(rootEl, commentHtml, responseData) {
    var preview, emptyPreview, trigger, popup, data, btn, face, body, strong, span, em;
    if (!rootEl) return;
    data = responseData && responseData.preview ? responseData.preview : buildPreviewDataFromHtml(commentHtml);
    if (!data) return;

    preview = rootEl.querySelector('.tb4c-comment-popup-preview');
    emptyPreview = rootEl.querySelector('.tb4c-comment-popup-empty-preview');
    popup = getCommentPopupFromRoot(rootEl);

    if (!preview) {
      preview = doc.createElement('div');
      preview.className = 'tb4c-comment-popup-preview tb4c-comment-fit-preview';
      preview.setAttribute('aria-label', 'ความคิดเห็นล่าสุด');
      trigger = rootEl.querySelector('.tb4c-comment-popup-trigger');
      if (trigger && trigger.parentNode) trigger.parentNode.insertBefore(preview, trigger.nextSibling);
      else if (popup && popup.parentNode) popup.parentNode.insertBefore(preview, popup);
      else rootEl.appendChild(preview);
    }

    if (emptyPreview) emptyPreview.setAttribute('hidden', 'hidden');

    btn = doc.createElement('button');
    btn.type = 'button';
    btn.className = 'tb4c-comment-preview-item tb4c-comment-fit-preview-item tb4c-comment-preview-new';
    btn.setAttribute('data-tb4c-open-comment-popup', '');
    if (data.id) {
      btn.setAttribute('data-reply-comment-id', String(data.id));
      btn.setAttribute('data-reply-author', data.author || '');
    }

    face = doc.createElement('span');
    face.className = 'tb4c-comment-preview-face';
    if (data.avatar_html) face.innerHTML = data.avatar_html;
    else face.textContent = '💬';

    body = doc.createElement('span');
    body.className = 'tb4c-comment-preview-body';
    strong = doc.createElement('strong');
    strong.textContent = data.author || 'สมาชิก';
    span = doc.createElement('span');
    span.textContent = data.text || 'แสดงความคิดเห็นแล้ว';
    em = doc.createElement('em');
    em.textContent = data.time || 'ตอนนี้';
    body.appendChild(strong);
    body.appendChild(span);
    body.appendChild(em);

    btn.appendChild(face);
    btn.appendChild(body);
    preview.insertBefore(btn, preview.firstChild);
    while (preview.children.length > 3) preview.removeChild(preview.lastChild);
  }

  function autoGrowTextarea(textarea) {
    if (!textarea) return;
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
  }

  function setReplyTarget(rootEl, commentId, author) {
    var popup = getCommentPopupFromRoot(rootEl);
    var scope = popup || rootEl;
    var parent = scope ? scope.querySelector('[data-tb4c-comment-parent]') : null;
    var ctx = scope ? scope.querySelector('[data-tb4c-reply-context]') : null;
    var label = scope ? scope.querySelector('[data-tb4c-reply-label]') : null;
    var textarea = scope ? scope.querySelector('[data-tb4c-comment-textarea]') : null;
    if (parent) parent.value = commentId || '0';
    if (ctx) {
      if (commentId) ctx.removeAttribute('hidden');
      else ctx.setAttribute('hidden', 'hidden');
    }
    if (label) label.textContent = commentId ? ('กำลังตอบกลับ ' + (author || 'ความคิดเห็นนี้')) : 'กำลังตอบกลับ';
    if (textarea) {
      if (commentId && author && textarea.value.replace(/\s/g, '') === '') textarea.value = '@' + author + ' ';
      textarea.focus();
      autoGrowTextarea(textarea);
      refreshCommentSubmitState(closest(textarea, '[data-tb4c-comment-form]'));
    }
  }

  function clearReplyTarget(rootEl) {
    setReplyTarget(rootEl, 0, '');
  }

  function openCommentPopup(rootEl) {
    var popup = getCommentPopupFromRoot(rootEl);
    var textarea;
    if (!popup) return;
    portalCommentPopup(rootEl, popup);
    updateCommentOverlayBounds();
    addClass(popup, 'is-open');
    popup.setAttribute('aria-hidden', 'false');
    lockAppModal();
    appHaptic();
    textarea = popup.querySelector('[data-tb4c-comment-textarea]');
    refreshAllCommentSubmitStates(popup);
    if (textarea) {
      window.setTimeout(function () { textarea.focus(); autoGrowTextarea(textarea); refreshCommentSubmitState(closest(textarea, '[data-tb4c-comment-form]')); }, 100);
    }
  }

  function closeCommentPopup(rootEl) {
    var popup = getCommentPopupFromRoot(rootEl);
    if (!popup) return;
    removeClass(popup, 'is-open');
    popup.setAttribute('aria-hidden', 'true');
    clearReplyTarget(rootEl);
    unlockAppModal();
    window.setTimeout(function () { restoreCommentPopup(popup); }, 40);
  }

  function closeAllCommentPopups() {
    var popups = doc.querySelectorAll ? doc.querySelectorAll('[data-tb4c-comment-popup].is-open') : [];
    var i, rootEl;
    for (i = 0; i < popups.length; i++) {
      rootEl = getCommentRootFromNode(popups[i]);
      closeCommentPopup(rootEl);
    }
  }

  function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, function (m) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
    });
  }

  function showCommentEditor(item) {
    var text = item ? item.querySelector('[data-tb4c-comment-text]') : null;
    var bubble = item ? item.querySelector('[data-tb4c-comment-bubble]') : null;
    var raw = text ? (text.getAttribute('data-raw') || text.textContent || '') : '';
    var existing = item ? item.querySelector('.tb4c-comment-editbox') : null;
    if (!item || !text || !bubble) return;
    if (existing) existing.parentNode.removeChild(existing);
    addClass(item, 'is-editing');
    var editbox = doc.createElement('div');
    editbox.className = 'tb4c-comment-editbox';
    editbox.innerHTML = '<textarea data-tb4c-edit-text rows="3" aria-label="แก้ไขความคิดเห็น">' + escapeHtml(raw) + '</textarea><div class="tb4c-comment-edit-actions"><button type="button" class="tb4c-btn tb4c-btn-primary" data-tb4c-comment-action="save-edit"><span class="tb4c-edit-action-icon" aria-hidden="true">✓</span> บันทึก</button><button type="button" class="tb4c-btn tb4c-btn-secondary" data-tb4c-comment-action="cancel-edit"><span class="tb4c-edit-action-icon" aria-hidden="true">×</span> ยกเลิก</button></div>';
    bubble.appendChild(editbox);
    var ta = editbox.querySelector('textarea');
    if (ta) { ta.focus(); autoGrowTextarea(ta); }
  }

  function hideCommentEditor(item) {
    var editbox = item ? item.querySelector('.tb4c-comment-editbox') : null;
    if (editbox && editbox.parentNode) editbox.parentNode.removeChild(editbox);
    removeClass(item, 'is-editing');
  }

  doc.addEventListener('click', function (event) {
    var lightboxTrigger = closest(event.target, '[data-tb4c-lightbox]');
    if (lightboxTrigger) {
      event.preventDefault();
      event.stopPropagation();
      openMediaLightbox(lightboxTrigger);
      return;
    }

    var lightboxClose = closest(event.target, '[data-tb4c-lightbox-close]');
    if (lightboxClose) {
      event.preventDefault();
      closeMediaLightbox();
      return;
    }

    var commentOpenBtn = closest(event.target, '[data-tb4c-open-comment-popup]');
    if (commentOpenBtn) {
      event.preventDefault();
      var commentRoot = closest(commentOpenBtn, '[data-tb4c-comments-root]') || doc.querySelector('[data-tb4c-comments-root]');
      var replyId = commentOpenBtn.getAttribute('data-reply-comment-id');
      var replyAuthor = commentOpenBtn.getAttribute('data-reply-author') || '';
      window.setTimeout(function () {
        openCommentPopup(commentRoot);
        if (replyId) setReplyTarget(commentRoot, replyId, replyAuthor);
      }, 35);
      return;
    }

    var commentCloseBtn = closest(event.target, '[data-tb4c-close-comment-popup]');
    if (commentCloseBtn) {
      event.preventDefault();
      closeCommentPopup(getCommentRootFromNode(commentCloseBtn));
      return;
    }

    var composerShowBtn = closest(event.target, '[data-tb4c-composer-show]');
    if (composerShowBtn) {
      event.preventDefault();
      var modalForTool = doc.getElementById('tb4cComposerModal');
      var section = composerShowBtn.getAttribute('data-tb4c-composer-show') || '';
      if (modalForTool && section) {
        addClass(modalForTool, 'tb4c-show-' + section);
        window.setTimeout(function () {
          var target = null;
          if (section === 'media-url') target = modalForTool.querySelector('[name="media_url"]');
          if (section === 'poll') target = modalForTool.querySelector('[name="poll_question"]');
          if (section === 'feeling') target = modalForTool.querySelector('[name="feeling"]');
          if (target && target.focus) target.focus();
        }, 40);
      }
      return;
    }

    var editPostBtn = closest(event.target, '[data-tb4c-edit-post]');
    if (editPostBtn) {
      event.preventDefault();
      openModal({
        id: editPostBtn.getAttribute('data-tb4c-edit-post') || '',
        title: editPostBtn.getAttribute('data-post-title') || '',
        content: editPostBtn.getAttribute('data-post-content') || '',
        type: editPostBtn.getAttribute('data-post-type') || 'discussion',
        visibility: editPostBtn.getAttribute('data-post-visibility') || 'public',
        mediaUrl: editPostBtn.getAttribute('data-post-media-url') || '',
        mediaAlbum: editPostBtn.getAttribute('data-post-media-album') || '',
          tags: editPostBtn.getAttribute('data-post-tags') || '',
        feeling: editPostBtn.getAttribute('data-post-feeling') || '',
        pollQuestion: editPostBtn.getAttribute('data-post-poll-question') || '',
        pollOptions: editPostBtn.getAttribute('data-post-poll-options') || ''
      });
      return;
    }

    var openBtn = closest(event.target, '[data-tb4c-open-composer]');
    if (openBtn) {
      event.preventDefault();
      if (openBtn.getAttribute('data-tb4c-composer-source') === 'member-area') {
        openBtn.removeAttribute('data-tb4c-composer-source');
      }
      openModal(null, openBtn.getAttribute('data-tb4c-tool-focus') || null);
      return;
    }

    var closeBtn = closest(event.target, '[data-tb4c-close-modal]');
    if (closeBtn) {
      event.preventDefault();
      closeModal();
      return;
    }

    var modal = doc.getElementById('tb4cComposerModal');
    if (modal && event.target === modal) {
      closeModal();
      return;
    }

    var pollBtn = closest(event.target, '[data-tb4c-poll-vote]');
    if (pollBtn) {
      event.preventDefault();
      var pollWrap = closest(pollBtn, '[data-tb4c-poll]');
      var pollPostId = pollBtn.getAttribute('data-post-id') || (pollWrap ? pollWrap.getAttribute('data-tb4c-poll') : '');
      var pollOption = pollBtn.getAttribute('data-poll-option') || '0';
      var pollKey = 'tb4c_poll_' + pollPostId;
      if (!window.FormData || !pollPostId) return;
      if (storageGet(pollKey)) {
        addClass(pollWrap, 'has-voted');
        return;
      }
      var fdPoll = new FormData();
      fdPoll.append('action', 'tb4c_vote_post_poll');
      fdPoll.append('nonce', cfg.nonce || '');
      fdPoll.append('post_id', pollPostId);
      fdPoll.append('option', pollOption);
      pollBtn.disabled = true;
      postFormData(fdPoll, function (json) {
        pollBtn.disabled = false;
        if (!json || !json.success) return;
        storageSet(pollKey, pollOption);
        addClass(pollWrap, 'has-voted');
        var buttons = pollWrap ? pollWrap.querySelectorAll('[data-tb4c-poll-vote]') : [];
        Array.prototype.forEach.call(buttons, function (btn, index) {
          var pct = json.data.percents && json.data.percents[index] !== undefined ? json.data.percents[index] : 0;
          var bar = btn.querySelector('i');
          var text = btn.querySelector('[data-tb4c-poll-percent]');
          if (bar) bar.style.width = pct + '%';
          if (text) text.textContent = pct + '%';
          btn.disabled = true;
        });
        var total = pollWrap ? pollWrap.querySelector('[data-tb4c-poll-total]') : null;
        if (total) total.textContent = (json.data.total || 0) + ' โหวต';
      }, function () { pollBtn.disabled = false; });
      return;
    }

    var reactBtn = closest(event.target, '[data-tb4c-react]');
    if (reactBtn) {
      event.preventDefault();
      var postId = reactBtn.getAttribute('data-post-id');
      var reaction = reactBtn.getAttribute('data-tb4c-react') || 'like';
      var localKey = 'tb4c_' + reaction + '_' + postId;
      if (!cfg.isLoggedIn && storageGet(localKey)) return;

      if (!window.FormData) { return; }
      var fd = new FormData();
      fd.append('action', 'tb4_react_community_post');
      fd.append('nonce', cfg.nonce || '');
      fd.append('post_id', postId);
      fd.append('reaction', reaction);

      reactBtn.disabled = true;
      postFormData(fd, function (json) {
        if (!json || !json.success) {
          reactBtn.disabled = false;
          return;
        }
        var span = reactBtn.querySelector('span');
        if (span) span.textContent = json.data.count;
        if (json.data.state === 'removed') {
          removeClass(reactBtn, 'is-reacted');
          try { if (window.localStorage) localStorage.removeItem(localKey); } catch (e) {}
        } else {
          addClass(reactBtn, 'is-reacted');
          storageSet(localKey, '1');
        }
        reactBtn.disabled = false;
      }, function () {
        reactBtn.disabled = false;
      });
      return;
    }

    var followBtn = closest(event.target, '[data-tb4c-follow]');
    if (followBtn) {
      event.preventDefault();
      appHaptic();
      if (!cfg.isLoggedIn) {
        window.location.href = cfg.loginUrl || '/wp-login.php';
        return;
      }
      if (!window.FormData) return;

      var userId = followBtn.getAttribute('data-tb4c-follow');
      var followFd = new FormData();
      followFd.append('action', 'tb4c_toggle_follow_user');
      followFd.append('nonce', cfg.nonce || '');
      followFd.append('user_id', userId);

      followBtn.disabled = true;
      postFormData(followFd, function (json) {
        var label;
        var icon;
        if (!json || !json.success) {
          followBtn.disabled = false;
          return;
        }
        label = followBtn.querySelector('span');
        icon = followBtn.querySelector('i');
        if (json.data.state === 'following') {
          addClass(followBtn, 'is-following');
          if (label) label.textContent = json.data.label || followBtn.getAttribute('data-following-label') || 'กำลังติดตาม';
          if (icon) icon.className = 'ph ph-check';
        } else {
          removeClass(followBtn, 'is-following');
          if (label) label.textContent = json.data.label || followBtn.getAttribute('data-follow-label') || 'ติดตาม';
          if (icon) icon.className = 'ph ph-plus';
        }
        followBtn.disabled = false;
      }, function () {
        followBtn.disabled = false;
      });
      return;
    }


    var commentAction = closest(event.target, '[data-tb4c-comment-action]');
    if (commentAction) {
      event.preventDefault();
      var action = commentAction.getAttribute('data-tb4c-comment-action');
      var commentsRoot = getCommentRootFromNode(commentAction);
      var item = closest(commentAction, '.tb4c-comment-item');
      var commentId = commentAction.getAttribute('data-comment-id') || (item ? item.getAttribute('data-comment-id') : '0');
      var fdComment;

      if (action === 'reply') {
        openCommentPopup(commentsRoot);
        setReplyTarget(commentsRoot, commentId, commentAction.getAttribute('data-comment-author') || '');
        appHaptic();
        return;
      }
      if (action === 'cancel-reply') {
        clearReplyTarget(commentsRoot);
        return;
      }
      if (action === 'edit') {
        showCommentEditor(item);
        return;
      }
      if (action === 'cancel-edit') {
        hideCommentEditor(item);
        return;
      }
      if (action === 'save-edit') {
        if (!window.FormData || !item) return;
        var editText = item.querySelector('[data-tb4c-edit-text]');
        fdComment = new FormData();
        fdComment.append('action', 'tb4c_update_comment');
        fdComment.append('nonce', cfg.nonce || '');
        fdComment.append('comment_id', commentId);
        fdComment.append('content', editText ? editText.value : '');
        commentAction.disabled = true;
        postFormData(fdComment, function (json) {
          var textEl;
          commentAction.disabled = false;
          if (!json || !json.success) {
            setCommentStatus(commentsRoot, (json && json.data && json.data.message) || 'แก้ไขไม่สำเร็จ', 'error');
            return;
          }
          textEl = item.querySelector('[data-tb4c-comment-text]');
          if (textEl) {
            textEl.innerHTML = json.data.content_html || '';
            textEl.setAttribute('data-raw', json.data.content || (editText ? editText.value : ''));
            if (json.data.content_html) removeClass(textEl, 'is-empty');
            else addClass(textEl, 'is-empty');
          }
          var oldMedia = item.querySelector('[data-tb4c-comment-media]');
          if (json.data.media_html) {
            if (oldMedia) oldMedia.outerHTML = json.data.media_html;
            else if (textEl) textEl.insertAdjacentHTML('afterend', json.data.media_html);
          } else if (oldMedia && oldMedia.parentNode) {
            oldMedia.parentNode.removeChild(oldMedia);
          }
          hideCommentEditor(item);
          setCommentStatus(commentsRoot, json.data.message || 'แก้ไขความคิดเห็นแล้ว', 'success');
        }, function (err) {
          commentAction.disabled = false;
          setCommentStatus(commentsRoot, err.message || 'แก้ไขไม่สำเร็จ', 'error');
        });
        return;
      }
      if (action === 'delete') {
        if (!window.FormData || !item) return;
        if (!window.confirm('ต้องการลบความคิดเห็นนี้ใช่ไหม?')) return;
        fdComment = new FormData();
        fdComment.append('action', 'tb4c_delete_comment');
        fdComment.append('nonce', cfg.nonce || '');
        fdComment.append('comment_id', commentId);
        commentAction.disabled = true;
        postFormData(fdComment, function (json) {
          var list;
          if (!json || !json.success) {
            commentAction.disabled = false;
            setCommentStatus(commentsRoot, (json && json.data && json.data.message) || 'ลบไม่สำเร็จ', 'error');
            return;
          }
          if (item.parentNode) item.parentNode.removeChild(item);
          updateCommentCount(commentsRoot, json.data.count);
          var commentScope = getCommentScope(commentsRoot, commentAction);
          list = commentScope ? commentScope.querySelector('[data-tb4c-comment-list]') : null;
          if (list && !list.querySelector('.tb4c-comment-item')) {
            list.setAttribute('hidden', 'hidden');
            var empty = commentScope ? commentScope.querySelector('[data-tb4c-comments-empty]') : null;
            if (empty) empty.removeAttribute('hidden');
          }
          setCommentStatus(commentsRoot, json.data.message || 'ลบความคิดเห็นแล้ว', 'success');
        }, function (err) {
          commentAction.disabled = false;
          setCommentStatus(commentsRoot, err.message || 'ลบไม่สำเร็จ', 'error');
        });
        return;
      }
    }

    var deletePostBtn = closest(event.target, '[data-tb4c-delete-post]');
    if (deletePostBtn) {
      event.preventDefault();
      if (!window.FormData) return;
      if (!window.confirm('ยืนยันลบโพสต์นี้ใช่ไหม? โพสต์จะถูกย้ายไปถังขยะและสามารถกู้คืนได้จากหลังบ้าน WordPress')) return;
      var deleteFd = new FormData();
      deleteFd.append('action', 'tb4c_delete_post');
      deleteFd.append('nonce', cfg.nonce || '');
      deleteFd.append('post_id', deletePostBtn.getAttribute('data-tb4c-delete-post'));
      deletePostBtn.disabled = true;
      postFormData(deleteFd, function (json) {
        if (!json || !json.success) {
          deletePostBtn.disabled = false;
          window.alert((json && json.data && json.data.message) || 'ลบโพสต์ไม่สำเร็จ');
          return;
        }
        window.location.href = json.data.url || (cfg.communityUrl || '/community/');
      }, function () {
        deletePostBtn.disabled = false;
        window.alert('ลบโพสต์ไม่สำเร็จ');
      });
      return;
    }

    var copyBtn = closest(event.target, '[data-tb4c-copy-link]');
    if (copyBtn) {
      event.preventDefault();
      copyText(copyBtn.getAttribute('data-tb4c-copy-link'), function () { showCopied(copyBtn); });
    }
  });

  doc.addEventListener('keydown', function (event) {
    var key = event.key || event.keyCode;
    if (key === 'Escape' || key === 27) {
      closeModal();
      closeAllCommentPopups();
      closeMediaLightbox();
    }
  });
  /* v4.2.126: duplicate capture-phase composer fallback removed. The ultra runtime owns composer open/close before legacy is loaded. */

  doc.addEventListener('submit', function (event) {

    var commentForm = closest(event.target, '#tb4cCommentForm');
    if (commentForm) {
      event.preventDefault();
      var commentsRoot = getCommentRootFromNode(commentForm);
      var submit = commentForm.querySelector('button[type="submit"]');
      var textarea = commentForm.querySelector('[data-tb4c-comment-textarea]');
      var parentInput = commentForm.querySelector('[data-tb4c-comment-parent]');
      var parentId = parentInput ? parentInput.value : '0';
      var commentScope = getCommentScope(commentsRoot, commentForm) || commentForm;
      var list = commentScope ? commentScope.querySelector('[data-tb4c-comment-list]') : null;
      var empty = commentScope ? commentScope.querySelector('[data-tb4c-comments-empty]') : null;
      var scrollBox = commentScope ? commentScope.querySelector('[data-tb4c-comment-scroll]') : null;
      var targetReplies;

      if (!cfg.isLoggedIn) {
        window.location.href = cfg.loginUrl || '/wp-login.php';
        return;
      }
      if (!window.FormData) {
        setCommentStatus(commentsRoot, 'เบราว์เซอร์นี้เก่าเกินไปสำหรับการส่งความคิดเห็นแบบไม่รีโหลด', 'error');
        return;
      }

      if (!textarea || !textarea.value.replace(/\s+/g, '').length) {
        setCommentStatus(commentsRoot, 'กรุณาเขียนความคิดเห็นก่อนส่ง', 'error');
        refreshCommentSubmitState(commentForm);
        if (textarea) textarea.focus();
        return;
      }

      var fdCommentSubmit = new FormData(commentForm);
      if (!fdCommentSubmit.get('nonce')) fdCommentSubmit.append('nonce', cfg.nonce || '');
      commentForm.setAttribute('data-tb4c-sending', '1');
      addClass(commentForm, 'is-sending');
      if (submit) submit.disabled = true;
      setCommentStatus(commentsRoot, 'กำลังส่งความคิดเห็น...', '');

      postFormData(fdCommentSubmit, function (json) {
        commentForm.removeAttribute('data-tb4c-sending');
        removeClass(commentForm, 'is-sending');
        if (submit) submit.disabled = false;
        if (!json || !json.success) {
          setCommentStatus(commentsRoot, (json && json.data && json.data.message) || 'ส่งความคิดเห็นไม่สำเร็จ', 'error');
          return;
        }
        if (json.data.html) {
          var insertedItem = null;
          if (parentId && parentId !== '0') {
            targetReplies = commentScope ? commentScope.querySelector('[data-tb4c-replies-for="' + parentId + '"]') : null;
            if (targetReplies) {
              targetReplies.insertAdjacentHTML('beforeend', json.data.html);
              targetReplies.removeAttribute('hidden');
              insertedItem = targetReplies.lastElementChild;
            } else if (list) {
              list.insertAdjacentHTML('beforeend', json.data.html);
              list.removeAttribute('hidden');
              insertedItem = list.lastElementChild;
            }
          } else if (list) {
            list.insertAdjacentHTML('beforeend', json.data.html);
            list.removeAttribute('hidden');
            insertedItem = list.lastElementChild;
          }
          if (empty) empty.setAttribute('hidden', 'hidden');
          ensureCommentVisibleState(commentScope);
          updateCommentPreview(commentsRoot, json.data.html, json.data);
          markNewestComment(commentScope, insertedItem);
          if (scrollBox) {
            window.setTimeout(function () { scrollBox.scrollTop = scrollBox.scrollHeight; }, 40);
          }
        } else if (json.data && json.data.approved === false) {
          setCommentStatus(commentsRoot, json.data.message || 'ส่งความคิดเห็นแล้ว รออนุมัติ', 'success');
        }
        if (textarea) { textarea.value = ''; autoGrowTextarea(textarea); }
        refreshCommentSubmitState(commentForm);
        clearReplyTarget(commentsRoot);
        updateCommentCount(commentsRoot, json.data.count);
        setCommentStatus(commentsRoot, json.data.message || 'ส่งความคิดเห็นแล้ว', 'success');
      }, function (err) {
        commentForm.removeAttribute('data-tb4c-sending');
        removeClass(commentForm, 'is-sending');
        if (submit) submit.disabled = false;
        refreshCommentSubmitState(commentForm);
        setCommentStatus(commentsRoot, err.message || 'ส่งความคิดเห็นไม่สำเร็จ', 'error');
      });
      return;
    }

    var memberAreaForm = closest(event.target, '#tb4cMemberAreaProfileForm');
    if (memberAreaForm) {
      event.preventDefault();
      if (!cfg.isLoggedIn) {
        window.location.href = cfg.loginUrl || '/wp-login.php';
        return;
      }
      if (!window.FormData) {
        var legacyStatus = memberAreaForm.querySelector('[data-tb4c-member-area-status]');
        if (legacyStatus) legacyStatus.textContent = 'เบราว์เซอร์นี้เก่าเกินไปสำหรับการบันทึกแบบไม่รีโหลด';
        return;
      }
      var memberStatus = memberAreaForm.querySelector('[data-tb4c-member-area-status]');
      var memberSubmit = memberAreaForm.querySelector('button[type="submit"]');
      var memberFd = new FormData(memberAreaForm);
      if (!memberFd.get('nonce')) memberFd.append('nonce', cfg.nonce || '');
      if (memberSubmit) memberSubmit.disabled = true;
      if (memberStatus) {
        removeClass(memberStatus, 'is-success');
        removeClass(memberStatus, 'is-error');
        memberStatus.textContent = 'กำลังบันทึก...';
      }
      postFormData(memberFd, function (json) {
        if (memberSubmit) memberSubmit.disabled = false;
        if (!json || !json.success) {
          if (memberStatus) {
            addClass(memberStatus, 'is-error');
            memberStatus.textContent = (json && json.data && json.data.message) || 'บันทึกไม่สำเร็จ';
          }
          return;
        }
        if (memberStatus) {
          addClass(memberStatus, 'is-success');
          memberStatus.textContent = json.data.message || 'บันทึกแล้ว';
        }
        if (json.data && typeof json.data.cover_url === 'string') {
          updateMemberCoverPreview(json.data.cover_url);
        }
        if (json.data && typeof json.data.avatar_url === 'string' && json.data.avatar_url) {
          updateMemberAvatarPreview(json.data.avatar_url);
        }
      }, function (err) {
        if (memberSubmit) memberSubmit.disabled = false;
        if (memberStatus) {
          addClass(memberStatus, 'is-error');
          memberStatus.textContent = err.message || 'บันทึกไม่สำเร็จ';
        }
      });
      return;
    }

    var form = closest(event.target, '#tb4cCommunityForm');
    if (!form) return;
    event.preventDefault();

    if (!cfg.isLoggedIn) {
      window.location.href = cfg.loginUrl || '/wp-login.php';
      return;
    }

    tb4cBuildComposerTitle(form);
    var submitBtn = form.querySelector('button[type="submit"]');
    if (!window.FormData) {
      setStatus(form, 'เบราว์เซอร์นี้เก่าเกินไปสำหรับการส่งฟอร์มแบบ AJAX', 'error');
      return;
    }
    var fd = new FormData(form);
    if (!fd.get('nonce')) fd.append('nonce', cfg.nonce || '');

    saveComposerDraft(form, true);
    if (submitBtn) submitBtn.disabled = true;
    showPostingLoader(form);
    setStatus(form, 'กำลังโพสต์<span class="tb4c-status-dots"><i></i><i></i><i></i></span>', '');

    postFormData(fd, function (json) {
      if (!json || !json.success) {
        hidePostingLoader(form);
        setStatus(form, (json && json.data && json.data.message) || ((cfg.messages && cfg.messages.failed) || 'เกิดข้อผิดพลาด'), 'error');
        if (submitBtn) submitBtn.disabled = false;
        return;
      }
      setStatus(form, json.data.message || ((cfg.messages && cfg.messages.saved) || 'สำเร็จ'), 'success');
      clearComposerDraft(form);
      window.setTimeout(function () {
        if (json.data.status === 'publish' || json.data.status === 'updated') window.location.reload();
        else {
          hidePostingLoader(form);
          form.reset();
          closeModal();
        }
      }, 900);
      if (submitBtn) submitBtn.disabled = false;
    }, function (err) {
      hidePostingLoader(form);
      setStatus(form, err.message || ((cfg.messages && cfg.messages.failed) || 'เกิดข้อผิดพลาด'), 'error');
      if (submitBtn) submitBtn.disabled = false;
    });
  });


  doc.addEventListener('input', function (event) {
    var textarea = closest(event.target, '[data-tb4c-comment-textarea], [data-tb4c-edit-text]');
    var composerField = closest(event.target, '#tb4cCommunityForm input, #tb4cCommunityForm textarea, #tb4cCommunityForm select');
    if (textarea) {
      autoGrowTextarea(textarea);
      if (textarea.getAttribute('data-tb4c-comment-textarea') !== null) refreshCommentSubmitState(closest(textarea, '[data-tb4c-comment-form]'));
    }
    if (composerField) queueComposerDraftSave(closest(composerField, '#tb4cCommunityForm'));
  });

  doc.addEventListener('change', function (event) {
    var composerField = closest(event.target, '#tb4cCommunityForm input, #tb4cCommunityForm textarea, #tb4cCommunityForm select');
    if (composerField) saveComposerDraft(closest(composerField, '#tb4cCommunityForm'), false);
  });


  function isVisibleBox(el, rect) {
    var style;
    if (!el || !rect) return false;
    if (rect.width <= 0 || rect.height <= 0) return false;
    try { style = window.getComputedStyle ? window.getComputedStyle(el) : null; } catch (e) { style = null; }
    if (style && (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0')) return false;
    return true;
  }

  function updateCommentOverlayBounds() {
    var height = window.innerHeight || doc.documentElement.clientHeight || 0;
    var topGuard = 0;
    var bottomGuard = 0;
    var topSelectors = [
      '#wpadminbar',
      'body > header',
      '.wp-site-blocks > header',
      '.site-header',
      '#masthead',
      '.tb4-header',
      '.tb4-site-header',
      '.tb4-modern-header',
      '.tb4-global-header',
      '.tb4-main-header',
      '.tb4-topbar',
      '.tb4-announcement-bar'
    ];
    var bottomSelectors = [
      '.tb4c-mobile-bottom-nav',
      '.tb4-mobile-bottom-nav',
      '.tb4-bottom-nav',
      '.site-footer',
      '#colophon',
      '.tb4-footer',
      'body > footer',
      '.wp-site-blocks > footer',
      '[role="contentinfo"]'
    ];

    if (window.visualViewport && window.visualViewport.height) height = window.visualViewport.height;

    function readTopGuard(selector) {
      var nodes = doc.querySelectorAll ? doc.querySelectorAll(selector) : [];
      var i, el, rect, style, pos;
      for (i = 0; i < nodes.length; i++) {
        el = nodes[i];
        if (!el || closest(el, '.tb4c-comment-popup')) continue;
        rect = el.getBoundingClientRect ? el.getBoundingClientRect() : null;
        if (!isVisibleBox(el, rect)) continue;
        try { style = window.getComputedStyle ? window.getComputedStyle(el) : null; } catch (e) { style = null; }
        pos = style ? style.position : '';
        if (rect.bottom > 0 && rect.top < Math.min(260, height * 0.38)) {
          topGuard = Math.max(topGuard, rect.bottom);
        } else if ((pos === 'fixed' || pos === 'sticky') && rect.bottom > 0 && rect.top < height) {
          topGuard = Math.max(topGuard, rect.bottom);
        }
      }
    }

    function readBottomGuard(selector) {
      var nodes = doc.querySelectorAll ? doc.querySelectorAll(selector) : [];
      var i, el, rect, style, pos, visibleHeight;
      for (i = 0; i < nodes.length; i++) {
        el = nodes[i];
        if (!el || closest(el, '.tb4c-comment-popup')) continue;
        rect = el.getBoundingClientRect ? el.getBoundingClientRect() : null;
        if (!isVisibleBox(el, rect)) continue;
        try { style = window.getComputedStyle ? window.getComputedStyle(el) : null; } catch (e) { style = null; }
        pos = style ? style.position : '';
        if (rect.top < height && rect.bottom > 0 && rect.top > height * 0.55) {
          visibleHeight = Math.max(0, height - Math.max(0, rect.top));
          bottomGuard = Math.max(bottomGuard, visibleHeight);
        } else if ((pos === 'fixed' || pos === 'sticky') && rect.bottom > height * 0.55) {
          visibleHeight = Math.max(0, height - Math.max(0, rect.top));
          bottomGuard = Math.max(bottomGuard, visibleHeight);
        }
      }
    }

    topSelectors.forEach(readTopGuard);
    bottomSelectors.forEach(readBottomGuard);

    /* v4.2.7: Auto-detect stacked site bars that themes may not name with a stable selector.
     * This catches WordPress admin bar + theme header + announcement/status strip, but ignores
     * the main content area so the backdrop does not start too low.
     */
    (function readStackedTopBars() {
      var nodes = doc.body && doc.body.children ? doc.body.children : [];
      var pass, i, el, rect, style, pos, h;
      for (pass = 0; pass < 8; pass++) {
        for (i = 0; i < nodes.length; i++) {
          el = nodes[i];
          if (!el || closest(el, '.tb4c-comment-popup')) continue;
          rect = el.getBoundingClientRect ? el.getBoundingClientRect() : null;
          if (!isVisibleBox(el, rect)) continue;
          try { style = window.getComputedStyle ? window.getComputedStyle(el) : null; } catch (e) { style = null; }
          pos = style ? style.position : '';
          h = rect.height || (rect.bottom - rect.top);
          if (rect.top <= topGuard + 10 && rect.bottom > topGuard && rect.bottom < Math.min(340, height * 0.48)) {
            if (h <= 128 || pos === 'fixed' || pos === 'sticky' || el.id === 'wpadminbar') {
              topGuard = Math.max(topGuard, rect.bottom);
            }
          }
        }
      }
    }());

    topGuard = Math.max(0, Math.min(Math.ceil(topGuard), Math.max(0, height - 220)));
    bottomGuard = Math.max(0, Math.min(Math.ceil(bottomGuard), Math.max(0, height - topGuard - 220)));

    root.style.setProperty('--tb4c-comment-overlay-top', topGuard + 'px');
    root.style.setProperty('--tb4c-comment-overlay-bottom', bottomGuard + 'px');
  }



  function updateComposerOverlayBounds() {
    var modal = doc.getElementById('tb4cComposerModal');
    var layoutHeight = window.innerHeight || doc.documentElement.clientHeight || 0;
    var height = layoutHeight;
    var topGuard = 0;
    var bottomGuard = 0;
    var panelMax;
    var topSelectors = [
      '#wpadminbar',
      'body > header',
      '.wp-site-blocks > header',
      '.site-header',
      '#masthead',
      '.tb4-header',
      '.tb4-site-header',
      '.tb4-modern-header',
      '.tb4-global-header',
      '.tb4-main-header',
      '.tb4-topbar',
      '.tb4-announcement-bar',
      '.tb4-sticky-top',
      '.tb4-fixed-top',
      '[data-tb4-header]',
      '[data-tb4-fixed-top]',
      '[role="banner"]'
    ];
    var bottomSelectors = [
      '.tb4c-mobile-bottom-nav',
      '.tb4-mobile-bottom-nav',
      '.tb4-bottom-nav',
      '.tb4-fixed-bottom',
      '.tb4-sticky-bottom',
      '.site-footer',
      '#colophon',
      '.tb4-footer',
      '.tb4-site-footer',
      '.tb4-modern-footer',
      'body > footer',
      '.wp-site-blocks > footer',
      '[data-tb4-footer]',
      '[data-tb4-fixed-bottom]',
      '[role="contentinfo"]'
    ];

    if (window.visualViewport && window.visualViewport.height) {
      height = Math.max(240, Math.floor(window.visualViewport.height));
    }

    function isComposerOwned(el) {
      return !!(el && closest(el, '#tb4cComposerModal, .tb4c-modal, .tb4c-comment-popup, .tb4c-media-lightbox, #tb4cMediaLightbox'));
    }

    function readTopGuard(selector) {
      var nodes = doc.querySelectorAll ? doc.querySelectorAll(selector) : [];
      var i, el, rect, style, pos, h;
      for (i = 0; i < nodes.length; i++) {
        el = nodes[i];
        if (!el || isComposerOwned(el)) continue;
        rect = el.getBoundingClientRect ? el.getBoundingClientRect() : null;
        if (!isVisibleBox(el, rect)) continue;
        try { style = window.getComputedStyle ? window.getComputedStyle(el) : null; } catch (e) { style = null; }
        pos = style ? style.position : '';
        h = rect.height || (rect.bottom - rect.top);
        if (rect.bottom > 0 && rect.top < Math.min(300, layoutHeight * 0.42)) {
          if (h <= 160 || pos === 'fixed' || pos === 'sticky' || el.id === 'wpadminbar') topGuard = Math.max(topGuard, rect.bottom);
        } else if ((pos === 'fixed' || pos === 'sticky') && rect.bottom > 0 && rect.top < Math.min(layoutHeight, 340)) {
          topGuard = Math.max(topGuard, rect.bottom);
        }
      }
    }

    function readBottomGuard(selector) {
      var nodes = doc.querySelectorAll ? doc.querySelectorAll(selector) : [];
      var i, el, rect, style, pos, visibleHeight;
      for (i = 0; i < nodes.length; i++) {
        el = nodes[i];
        if (!el || isComposerOwned(el)) continue;
        rect = el.getBoundingClientRect ? el.getBoundingClientRect() : null;
        if (!isVisibleBox(el, rect)) continue;
        try { style = window.getComputedStyle ? window.getComputedStyle(el) : null; } catch (e) { style = null; }
        pos = style ? style.position : '';
        if (rect.top < layoutHeight && rect.bottom > 0 && rect.top > layoutHeight * 0.52) {
          visibleHeight = Math.max(0, layoutHeight - Math.max(0, rect.top));
          bottomGuard = Math.max(bottomGuard, visibleHeight);
        } else if ((pos === 'fixed' || pos === 'sticky') && rect.bottom > layoutHeight * 0.52) {
          visibleHeight = Math.max(0, layoutHeight - Math.max(0, rect.top));
          bottomGuard = Math.max(bottomGuard, visibleHeight);
        }
      }
    }

    topSelectors.forEach(readTopGuard);
    bottomSelectors.forEach(readBottomGuard);

    (function readStackedTopBars() {
      var nodes = doc.body && doc.body.children ? doc.body.children : [];
      var pass, i, el, rect, style, pos, h;
      for (pass = 0; pass < 8; pass++) {
        for (i = 0; i < nodes.length; i++) {
          el = nodes[i];
          if (!el || isComposerOwned(el)) continue;
          rect = el.getBoundingClientRect ? el.getBoundingClientRect() : null;
          if (!isVisibleBox(el, rect)) continue;
          try { style = window.getComputedStyle ? window.getComputedStyle(el) : null; } catch (e) { style = null; }
          pos = style ? style.position : '';
          h = rect.height || (rect.bottom - rect.top);
          if (rect.top <= topGuard + 10 && rect.bottom > topGuard && rect.bottom < Math.min(360, layoutHeight * 0.50)) {
            if (h <= 160 || pos === 'fixed' || pos === 'sticky' || el.id === 'wpadminbar') topGuard = Math.max(topGuard, rect.bottom);
          }
        }
      }
    }());

    topGuard = Math.max(0, Math.min(Math.ceil(topGuard), Math.max(0, height - 280)));
    bottomGuard = Math.max(0, Math.min(Math.ceil(bottomGuard), Math.max(0, height - topGuard - 280)));
    panelMax = Math.max(260, Math.floor(height - topGuard - bottomGuard - 18));

    root.style.setProperty('--tb4c-composer-overlay-top', topGuard + 'px');
    root.style.setProperty('--tb4c-composer-overlay-bottom', bottomGuard + 'px');
    root.style.setProperty('--tb4c-composer-panel-max-height', panelMax + 'px');
    root.style.setProperty('--tb4c-composer-available-height', Math.max(280, Math.floor(height - topGuard - bottomGuard)) + 'px');

    if (modal) {
      modal.setAttribute('data-tb4c-safe-top', String(topGuard));
      modal.setAttribute('data-tb4c-safe-bottom', String(bottomGuard));
      modal.setAttribute('data-tb4c-safe-panel', String(panelMax));
    }
  }


  function updateViewportVars() {
    var height = window.innerHeight || doc.documentElement.clientHeight || 0;
    if (window.visualViewport && window.visualViewport.height) height = window.visualViewport.height;
    root.style.setProperty('--tb4c-viewport-height', height + 'px');
    updateCommentOverlayBounds();
    updateComposerOverlayBounds();
  }
  updateViewportVars();
  if (window.addEventListener) {
    window.addEventListener('resize', updateViewportVars);
    /* v4.2.101: removed always-on scroll sync; popup bounds update only on open/resize to reduce feed jank. */
  }
  if (window.visualViewport && window.visualViewport.addEventListener) {
    window.visualViewport.addEventListener('resize', updateViewportVars);
    /* v4.2.101: removed visualViewport scroll listener to prevent mobile/desktop jank while reading. */
  }
  if (doc.addEventListener) {
    doc.addEventListener('focusin', function (event) {
      if (closest(event.target, '#tb4cComposerModal')) {
        window.setTimeout(updateViewportVars, 60);
        window.setTimeout(updateViewportVars, 240);
      }
    });
  }



  function normalizeClosedCommentPopups() {
    var popups, i;
    if (!doc.querySelectorAll) return;
    popups = doc.querySelectorAll('[data-tb4c-comment-popup]');
    for (i = 0; i < popups.length; i++) {
      removeClass(popups[i], 'is-open');
      popups[i].setAttribute('aria-hidden', 'true');
    }
    unlockAppModal();
    if (doc.body && !doc.querySelector('[data-tb4c-comment-popup].is-open')) {
      removeClass(doc.body, 'tb4c-comment-portal-active');
    }
  }

  function initCommentClickOnlyPopup() {
    function scrollToCommentsOnly() {
      var commentRoot;
      if (!window.location || window.location.hash !== '#comments') return;
      commentRoot = doc.querySelector ? doc.querySelector('[data-tb4c-comments-root]') : null;
      if (!commentRoot || !commentRoot.scrollIntoView) return;
      window.setTimeout(function () {
        try { commentRoot.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        catch (e) { commentRoot.scrollIntoView(true); }
      }, 120);
    }

    /* v4.2.43: Comment popup is click-only.
       The #comments hash should bring the user to the comment area only; it must not auto-open the popup. */
    if (doc.readyState === 'loading') {
      doc.addEventListener('DOMContentLoaded', function () {
        normalizeClosedCommentPopups();
        scrollToCommentsOnly();
      });
    } else {
      normalizeClosedCommentPopups();
      scrollToCommentsOnly();
    }
    if (window.addEventListener) window.addEventListener('hashchange', scrollToCommentsOnly);
  }
  initCommentClickOnlyPopup();

  (function initSheetGesture() {
    var modal = doc.getElementById('tb4cComposerModal');
    if (!modal) return;
    var panel = modal.querySelector('.tb4c-modal-panel');
    if (!panel) return;
    var startY = 0;
    var currentY = 0;
    var dragging = false;

    function onStart(e) {
      var touch = e.touches && e.touches[0];
      if (!touch) return;
      if (window.innerWidth > 680) return;
      startY = touch.clientY;
      currentY = startY;
      dragging = true;
      panel.style.transition = 'none';
    }
    function onMove(e) {
      var touch = e.touches && e.touches[0];
      var delta;
      if (!dragging || !touch) return;
      currentY = touch.clientY;
      delta = Math.max(0, currentY - startY);
      if (delta > 0) panel.style.transform = 'translateY(' + delta + 'px)';
    }
    function onEnd() {
      var delta;
      if (!dragging) return;
      dragging = false;
      delta = Math.max(0, currentY - startY);
      panel.style.transition = '';
      panel.style.transform = '';
      if (delta > 110) closeModal();
    }
    panel.addEventListener('touchstart', onStart, { passive: true });
    panel.addEventListener('touchmove', onMove, { passive: true });
    panel.addEventListener('touchend', onEnd);
    panel.addEventListener('touchcancel', onEnd);
  })();




  function getVideoSlotTitle(slot) {
    var title = slot ? slot.getAttribute('data-tb4c-video-title') : '';
    return title || 'วิดีโอที่กำลังเล่น';
  }

  var activeVideoSlot = null;
  var videoMiniClosed = false;
  var tb4cLastIframeIntent = null;
  addClass(doc.documentElement, 'tb4c-v446-mini-video-bottom-controls');
  addClass(doc.documentElement, 'tb4c-v447-continue-after-close');
  addClass(doc.documentElement, 'tb4c-v448-media-audio-fix');
  addClass(doc.documentElement, 'tb4c-v449-rounded-media-play-balance');
  addClass(doc.documentElement, 'tb4c-v450-no-media-radius');
  addClass(doc.documentElement, 'tb4c-v451-mobile-reel-tabs');
  addClass(doc.documentElement, 'tb4c-v452-rail-next-click-fix');
  addClass(doc.documentElement, 'tb4c-v454-popup-composer-tools-poll');
  addClass(doc.documentElement, 'tb4c-v455-popup-composer-force-ready');
  addClass(doc.documentElement, 'tb4c-v456-composer-scroll-tools-row');
  addClass(doc.documentElement, 'tb4c-v460-think-reels');
  addClass(doc.documentElement, 'tb4c-v461-composer-opt-in');
  addClass(doc.documentElement, 'tb4c-v462-composer-reels-fix');

  /* v4.2.28: Mini video must only start after a real user play/click intent.
   * Scrolling past a video or focusing an iframe must not create the popup by itself.
   */

  function tb4cNowMs() {
    return Date.now ? Date.now() : new Date().getTime();
  }

  function getVideoSlotFromNode(node) {
    return closest(node, '[data-tb4c-video-slot], .tb4c-post-video-slot');
  }


  function isMiniVideoIntentIgnoredTarget(node) {
    return !!closest(node, '[data-tb4c-video-mini-action], [data-tb4c-lightbox], .tb4c-album-expand, a[href]');
  }

  function markVideoSlotUserIntent(slot) {
    if (!slot) return;
    slot.tb4cVideoUserEngaged = true;
    slot.tb4cVideoUserEngagedAt = tb4cNowMs();
  }

  function clearVideoSlotUserIntent(slot) {
    if (!slot) return;
    slot.tb4cVideoUserEngaged = false;
    slot.tb4cVideoUserEngagedAt = 0;
    slot.tb4cVideoIntentGesture = null;
  }

  function getVideoEventPoint(event) {
    var touch;
    if (!event) return { x: 0, y: 0 };
    touch = event.changedTouches && event.changedTouches[0] ? event.changedTouches[0] : (event.touches && event.touches[0] ? event.touches[0] : null);
    if (touch) return { x: touch.clientX || 0, y: touch.clientY || 0 };
    return { x: event.clientX || 0, y: event.clientY || 0 };
  }

  function beginVideoUserGesture(slot, event) {
    var point;
    if (!slot || isMiniVideoIntentIgnoredTarget(event && event.target)) return;
    point = getVideoEventPoint(event);
    slot.tb4cVideoIntentGesture = {
      x: point.x,
      y: point.y,
      time: tb4cNowMs(),
      moved: false
    };
    /* Mark intent early so native <video> play/playing events triggered by this click can activate. */
    markVideoSlotUserIntent(slot);
    if (event && event.target && String(event.target.tagName || '').toUpperCase() === 'IFRAME') {
      tb4cLastIframeIntent = { slot: slot, time: tb4cNowMs() };
    }
  }

  function moveVideoUserGesture(slot, event) {
    var gesture, point, dx, dy;
    if (!slot || !slot.tb4cVideoIntentGesture) return;
    gesture = slot.tb4cVideoIntentGesture;
    point = getVideoEventPoint(event);
    dx = Math.abs(point.x - gesture.x);
    dy = Math.abs(point.y - gesture.y);
    if (dx > 10 || dy > 10) gesture.moved = true;
  }

  function finishVideoUserGesture(slot, event) {
    var gesture, elapsed;
    if (!slot || !slot.tb4cVideoIntentGesture) return;
    moveVideoUserGesture(slot, event);
    gesture = slot.tb4cVideoIntentGesture;
    elapsed = tb4cNowMs() - gesture.time;
    slot.tb4cVideoIntentGesture = null;
    if (!gesture.moved && elapsed < 1300) {
      markVideoSlotUserIntent(slot);
      activateVideoSlot(slot, true);
    }
  }

  function cancelVideoUserGesture(slot) {
    if (slot) slot.tb4cVideoIntentGesture = null;
  }

  function markMiniVideoTransition(slot, duration) {
    if (!slot) return;
    slot.tb4cMiniTransitionUntil = tb4cNowMs() + (duration || 1400);
  }

  function markContinuousPlaybackGuard(slot, duration) {
    if (!slot) return;
    slot.tb4cContinuousPlaybackUntil = tb4cNowMs() + (duration || 2600);
  }

  function shouldIgnoreVideoPause(slot) {
    var now = tb4cNowMs();
    return !!(slot && ((slot.tb4cMiniTransitionUntil && now < slot.tb4cMiniTransitionUntil) || (slot.tb4cContinuousPlaybackUntil && now < slot.tb4cContinuousPlaybackUntil)));
  }

  function captureNativeVideoState(slot) {
    var video;
    if (!slot || !slot.querySelector) return null;
    video = slot.querySelector('video');
    if (!video) return null;
    return {
      wasPlaying: !!(!video.paused && !video.ended),
      currentTime: isFinite(video.currentTime) ? video.currentTime : 0,
      muted: !!video.muted,
      volume: typeof video.volume === 'number' ? video.volume : null,
      playbackRate: typeof video.playbackRate === 'number' ? video.playbackRate : 1,
      controls: !!video.controls
    };
  }

  function resumeNativeVideoIfNeeded(slot, state) {
    var video, playPromise;
    if (!slot || !state || !state.wasPlaying || !slot.querySelector) return;
    video = slot.querySelector('video');
    if (!video || !video.play) return;
    try {
      if (state.muted !== undefined) video.muted = state.muted;
      if (state.volume !== null && state.volume !== undefined) video.volume = state.volume;
      if (state.playbackRate) video.playbackRate = state.playbackRate;
      if (Math.abs((video.currentTime || 0) - state.currentTime) > 0.75) video.currentTime = state.currentTime;
    } catch (ignoreState) {}
    try {
      playPromise = video.play();
      if (playPromise && playPromise.catch) playPromise.catch(function () {});
    } catch (ignorePlay) {}
  }

  function prepareIframeForContinuousMini(slot) {
    var iframe, allow;
    if (!slot || !slot.querySelector) return;
    iframe = slot.querySelector('iframe');
    if (!iframe) return;
    allow = iframe.getAttribute('allow') || '';
    if (allow.indexOf('autoplay') === -1) iframe.setAttribute('allow', allow ? allow + '; autoplay' : 'autoplay');
    iframe.setAttribute('loading', 'eager');
  }

  function setMiniVideoNativeControls(slot, enabled) {
    var video;
    if (!slot || !slot.querySelector) return;
    video = slot.querySelector('video');
    if (!video) return;
    try { video.controls = !!enabled; } catch (ignoreControls) {}
    if (enabled) removeClass(slot, 'tb4c-mini-native-controls-hidden');
    else addClass(slot, 'tb4c-mini-native-controls-hidden');
  }

  function restoreMiniVideoNativeControls(slot, state) {
    var shouldEnable = true;
    if (state && state.controls !== undefined) shouldEnable = !!state.controls;
    setMiniVideoNativeControls(slot, shouldEnable);
  }

  function scheduleNativeVideoResume(slot, state) {
    if (!slot || !state || !state.wasPlaying) return;
    markContinuousPlaybackGuard(slot, 3200);
    window.setTimeout(function () { resumeNativeVideoIfNeeded(slot, state); }, 30);
    window.setTimeout(function () { resumeNativeVideoIfNeeded(slot, state); }, 140);
    window.setTimeout(function () { resumeNativeVideoIfNeeded(slot, state); }, 420);
    window.setTimeout(function () { resumeNativeVideoIfNeeded(slot, state); }, 950);
  }

  function ensureMiniVideoControls(slot) {
    var bar, actions, pause, close;
    if (!slot || slot.querySelector('.tb4c-mini-video-bar')) return;
    bar = doc.createElement('div');
    bar.className = 'tb4c-mini-video-bar';
    actions = doc.createElement('span');
    actions.className = 'tb4c-mini-video-actions';
    pause = doc.createElement('button');
    pause.type = 'button';
    pause.className = 'tb4c-mini-video-btn tb4c-mini-video-btn-pause';
    pause.setAttribute('data-tb4c-video-mini-action', 'pause');
    pause.setAttribute('aria-label', 'หยุดวิดีโอ');
    pause.innerHTML = '<span aria-hidden="true">Ⅱ</span><em>หยุด</em>';
    close = doc.createElement('button');
    close.type = 'button';
    close.className = 'tb4c-mini-video-btn tb4c-mini-video-btn-close';
    close.setAttribute('data-tb4c-video-mini-action', 'close');
    close.setAttribute('aria-label', 'ปิดวิดีโอเล็ก');
    close.innerHTML = '<span aria-hidden="true">×</span><em>ปิด</em>';
    actions.appendChild(pause);
    actions.appendChild(close);
    bar.appendChild(actions);
    slot.appendChild(bar);
  }

  function enterMiniVideo(slot) {
    var rect, placeholder, parent, state;
    if (!slot || slot.tb4cMiniMode) return;
    rect = slot.getBoundingClientRect ? slot.getBoundingClientRect() : null;
    state = captureNativeVideoState(slot);
    prepareIframeForContinuousMini(slot);
    ensureMiniVideoControls(slot);
    setMiniVideoNativeControls(slot, false);
    markMiniVideoTransition(slot, 2200);
    markContinuousPlaybackGuard(slot, 3200);
    placeholder = doc.createElement('div');
    placeholder.className = 'tb4c-video-mini-placeholder';
    if (rect && rect.height) placeholder.style.height = Math.max(160, rect.height) + 'px';
    parent = slot.parentNode;
    slot.tb4cMiniPlaceholder = placeholder;
    slot.tb4cMiniOriginalParent = parent || null;
    if (parent) parent.insertBefore(placeholder, slot);

    /* v4.2.26: portal the same active player node to <body> and preserve playback while the user keeps reading. */
    if (doc.body && slot.parentNode !== doc.body) {
      doc.body.appendChild(slot);
      addClass(slot, 'tb4c-mini-video-portaled');
    }

    slot.tb4cMiniMode = true;
    slot.tb4cMiniKeepUntilStop = true;
    slot.tb4cMiniLastSwitch = tb4cNowMs();
    addClass(slot, 'tb4c-video-mini-player');
    addClass(slot, 'tb4c-mini-video-entering');
    addClass(doc.body, 'tb4c-has-mini-video');
    if (window.requestAnimationFrame) {
      window.requestAnimationFrame(function () {
        window.requestAnimationFrame(function () { removeClass(slot, 'tb4c-mini-video-entering'); });
      });
    } else {
      window.setTimeout(function () { removeClass(slot, 'tb4c-mini-video-entering'); }, 80);
    }
    scheduleNativeVideoResume(slot, state);
  }

  function restoreMiniVideo(slot) {
    var placeholder, restoreParent, state;
    if (!slot) return;
    state = captureNativeVideoState(slot);
    markMiniVideoTransition(slot, 2200);
    markContinuousPlaybackGuard(slot, 3200);
    removeClass(slot, 'tb4c-video-mini-player');
    removeClass(slot, 'tb4c-mini-video-portaled');
    removeClass(slot, 'tb4c-mini-video-entering');
    slot.tb4cMiniMode = false;
    slot.tb4cMiniKeepUntilStop = false;
    slot.tb4cMiniLastSwitch = tb4cNowMs();
    placeholder = slot.tb4cMiniPlaceholder;
    restoreParent = placeholder && placeholder.parentNode ? placeholder.parentNode : slot.tb4cMiniOriginalParent;

    if (restoreParent && slot.parentNode !== restoreParent) {
      if (placeholder && placeholder.parentNode === restoreParent) restoreParent.insertBefore(slot, placeholder.nextSibling);
      else restoreParent.appendChild(slot);
    }

    if (placeholder && placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
    slot.tb4cMiniPlaceholder = null;
    slot.tb4cMiniOriginalParent = null;
    if (doc.body && !doc.querySelector('.tb4c-video-mini-player')) removeClass(doc.body, 'tb4c-has-mini-video');
    restoreMiniVideoNativeControls(slot, state);
    scheduleNativeVideoResume(slot, state);
  }

  function stopVideoSlot(slot) {
    var video, iframe, src;
    if (!slot) return;
    slot.tb4cMiniManualStop = true;
    slot.tb4cMiniKeepUntilStop = false;
    slot.tb4cMiniTransitionUntil = 0;
    slot.tb4cContinuousPlaybackUntil = 0;
    video = slot.querySelector('video');
    if (video && video.pause) {
      try { video.pause(); } catch (e) {}
      try { video.currentTime = video.currentTime; } catch (ignore) {}
    }
    iframe = slot.querySelector('iframe');
    if (iframe) {
      src = iframe.getAttribute('src');
      if (src) iframe.setAttribute('src', src);
    }
    restoreMiniVideo(slot);
    removeClass(slot, 'tb4c-video-active');
    clearVideoSlotUserIntent(slot);
    if (activeVideoSlot === slot) activeVideoSlot = null;
    videoMiniClosed = true;
    window.setTimeout(function () { if (slot) slot.tb4cMiniManualStop = false; }, 80);
  }

  function closeMiniVideoOnly(slot) {
    var state;
    if (!slot) return;
    state = captureNativeVideoState(slot);
    slot.tb4cMiniKeepUntilStop = false;
    markContinuousPlaybackGuard(slot, 4200);
    restoreMiniVideo(slot);
    addClass(slot, 'tb4c-video-active');
    markVideoSlotUserIntent(slot);
    activeVideoSlot = slot;
    videoMiniClosed = true;
    scheduleNativeVideoResume(slot, state);
  }

  function activateVideoSlot(slot, fromUserIntent) {
    if (!slot) return;
    if (fromUserIntent) markVideoSlotUserIntent(slot);
    if (!slot.tb4cVideoUserEngaged && !slot.tb4cMiniMode) return;
    if (activeVideoSlot === slot) {
      videoMiniClosed = false;
      ensureMiniVideoControls(slot);
      updateMiniVideoPosition();
      return;
    }
    if (activeVideoSlot && activeVideoSlot !== slot) {
      restoreMiniVideo(activeVideoSlot);
      removeClass(activeVideoSlot, 'tb4c-video-active');
      clearVideoSlotUserIntent(activeVideoSlot);
    }
    activeVideoSlot = slot;
    videoMiniClosed = false;
    prepareIframeForContinuousMini(slot);
    addClass(slot, 'tb4c-video-active');
    ensureMiniVideoControls(slot);
    updateMiniVideoPosition();
  }

  function getMiniVideoAnchorRect(slot) {
    var anchor;
    if (!slot) return null;
    anchor = slot.tb4cMiniMode && slot.tb4cMiniPlaceholder ? slot.tb4cMiniPlaceholder : slot;
    return anchor && anchor.getBoundingClientRect ? anchor.getBoundingClientRect() : null;
  }

  function getMiniVideoAnchorVisibleRatio(rect, topGuard, height) {
    var viewportHeight, visibleTop, visibleBottom, visibleHeight, baseHeight;
    if (!rect || !height) return 0;
    viewportHeight = Math.max(1, height - topGuard);
    visibleTop = Math.max(rect.top, topGuard);
    visibleBottom = Math.min(rect.bottom, height);
    visibleHeight = Math.max(0, visibleBottom - visibleTop);
    baseHeight = Math.max(1, Math.min(rect.height || viewportHeight, viewportHeight));
    return visibleHeight / baseHeight;
  }

  function isMiniVideoAnchorOffscreen(rect, topGuard, height) {
    var ratio, safeBottom, readableTop, readableBottom;
    if (!rect) return false;
    ratio = getMiniVideoAnchorVisibleRatio(rect, topGuard, height);
    safeBottom = parseInt((getComputedStyle(root).getPropertyValue('--tb4c-comment-overlay-bottom') || '0').replace('px', ''), 10) || 0;
    readableTop = topGuard + 24;
    readableBottom = height - safeBottom - 96;
    /* v4.2.29: trigger faster after the user really started a video.
       This feels closer to Facebook: once the player mostly leaves the reading area, float it. */
    return rect.bottom < readableTop + 44 || rect.top > readableBottom || ratio < 0.82;
  }

  function isMiniVideoAnchorBackInView(rect, topGuard, height) {
    var ratio, safeBottom;
    if (!rect) return false;
    ratio = getMiniVideoAnchorVisibleRatio(rect, topGuard, height);
    safeBottom = parseInt((getComputedStyle(root).getPropertyValue('--tb4c-comment-overlay-bottom') || '0').replace('px', ''), 10) || 0;
    return ratio > 0.92 && rect.bottom > topGuard + 160 && rect.top < height - safeBottom - 180;
  }

  function updateMiniVideoPosition() {
    var slot = activeVideoSlot;
    var rect, height, topGuard, offscreen, backInView, now, lastSwitch;
    if (!slot || videoMiniClosed || (!slot.tb4cVideoUserEngaged && !slot.tb4cMiniMode)) return;

    /*
     * v4.2.21 flicker guard:
     * When the video is already mini/fixed, the slot's own rect becomes the mini popup rect.
     * Reading that rect made the script think the original video was visible again, so it
     * restored immediately, then entered mini again on the next scroll tick.
     * We must measure the placeholder/original position while mini mode is active.
     */
    rect = getMiniVideoAnchorRect(slot);
    if (!rect) return;

    height = window.innerHeight || doc.documentElement.clientHeight || 0;
    topGuard = parseInt((getComputedStyle(root).getPropertyValue('--tb4c-comment-overlay-top') || '0').replace('px', ''), 10) || 0;
    offscreen = isMiniVideoAnchorOffscreen(rect, topGuard, height);
    backInView = isMiniVideoAnchorBackInView(rect, topGuard, height);

    now = tb4cNowMs();
    lastSwitch = slot.tb4cMiniLastSwitch || 0;
    if (!slot.tb4cMiniMode && offscreen && now - lastSwitch > 160) enterMiniVideo(slot);
    else if (slot.tb4cMiniMode && backInView && !slot.tb4cMiniKeepUntilStop && now - lastSwitch > 700) restoreMiniVideo(slot);
  }

  function initPopupComposerForceReady() {
    addClass(root, 'tb4c-v454-popup-composer-tools-poll');
    addClass(root, 'tb4c-v455-popup-composer-force-ready');
    addClass(root, 'tb4c-v456-composer-scroll-tools-row');
  addClass(root, 'tb4c-v458-composer-draft-lock');
  addClass(root, 'tb4c-v459-post-bounds-final');
    var modal = doc.getElementById('tb4cComposerModal');
    if (modal) {
      modal.setAttribute('aria-hidden', modal.classList && modal.classList.contains('is-open') ? 'false' : 'true');
      addClass(modal, 'tb4c-popup-composer-v455');
    }
    var inline = getInlineComposer();
    if (inline) {
      removeClass(inline, 'is-open');
      var panel = inline.querySelector('[data-tb4c-inline-composer-panel]');
      if (panel) panel.setAttribute('aria-hidden', 'true');
    }
  }

  function initHorizontalRailButtons() {
    var controls = doc.querySelectorAll('[data-tb4c-scroll-target][data-tb4c-scroll-dir]');
    function findTarget(id) { return id ? doc.getElementById(id) : null; }
    function setSoftDisabled(btn, disabled) {
      if (!btn) return;
      btn.removeAttribute('disabled');
      btn.setAttribute('aria-disabled', disabled ? 'true' : 'false');
      if (disabled) addClass(btn, 'is-disabled');
      else removeClass(btn, 'is-disabled');
    }
    function updateButtonState(target) {
      var shell, prev, next, maxLeft, left;
      if (!target) return;
      shell = closest(target, '[data-tb4c-rail-shell]');
      if (!shell) return;
      prev = shell.querySelector('[data-tb4c-scroll-dir="-1"]');
      next = shell.querySelector('[data-tb4c-scroll-dir="1"]');
      maxLeft = Math.max(0, target.scrollWidth - target.clientWidth - 2);
      left = target.scrollLeft || 0;
      setSoftDisabled(prev, left <= 2);
      setSoftDisabled(next, maxLeft <= 2 ? true : left >= maxLeft);
    }
    function scrollFromButton(btn) {
      var targetId, dir, target, amount;
      if (!btn) return;
      targetId = btn.getAttribute('data-tb4c-scroll-target');
      dir = parseInt(btn.getAttribute('data-tb4c-scroll-dir') || '0', 10);
      target = findTarget(targetId);
      if (!target || !dir) return;
      amount = Math.max(120, Math.round((target.clientWidth || 180) * 0.78)) * (dir < 0 ? -1 : 1);
      try { target.scrollBy({ left: amount, behavior: 'smooth' }); }
      catch (e) { target.scrollLeft += amount; }
      window.setTimeout(function () { updateButtonState(target); }, 80);
      window.setTimeout(function () { updateButtonState(target); }, 260);
      window.setTimeout(function () { updateButtonState(target); }, 560);
    }
    controls.forEach(function (btn) {
      var targetId = btn.getAttribute('data-tb4c-scroll-target');
      var target = findTarget(targetId);
      if (!target) return;
      btn.removeAttribute('disabled');
      if (!btn.tb4cRailBound) {
        btn.tb4cRailBound = true;
        btn.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          scrollFromButton(btn);
        });
      }
      if (!target.tb4cRailStateBound) {
        target.tb4cRailStateBound = true;
        target.addEventListener('scroll', function () { updateButtonState(target); }, { passive: true });
      }
      window.setTimeout(function () { updateButtonState(target); }, 80);
      window.setTimeout(function () { updateButtonState(target); }, 500);
    });
    if (!doc.tb4cRailDelegatedClick) {
      doc.tb4cRailDelegatedClick = true;
      doc.addEventListener('click', function (event) {
        var btn = closest(event.target, '[data-tb4c-scroll-target][data-tb4c-scroll-dir]');
        if (!btn) return;
        event.preventDefault();
        event.stopPropagation();
        scrollFromButton(btn);
      }, true);
    }
  }





  function initComposerOptInOnly() {
    addClass(root, 'tb4c-v461-composer-opt-in');
    var modal = doc.getElementById('tb4cComposerModal');
    var cards = doc.querySelectorAll ? doc.querySelectorAll('[data-tb4c-composer-opt-in-card], .tb4c-composer-opt-in-card') : [];

    function hideIdleComposerCard(card) {
      if (!card || hasClass(card, 'is-open') || hasClass(card, 'tb4c-composer-user-opened')) return;
      card.setAttribute('aria-hidden', 'true');
    }

    function hideIdleModal() {
      if (!modal) return;
      if (hasClass(modal, 'is-open') || hasClass(modal, 'tb4c-composer-visible-now')) return;
      modal.setAttribute('aria-hidden', 'true');
      modal.style.display = 'none';
      modal.style.visibility = 'hidden';
      modal.style.opacity = '0';
      modal.style.pointerEvents = 'none';
    }

    function markComposerChosen() {
      if (root) root.tb4cComposerUserChose = true;
      if (doc.body) addClass(doc.body, 'tb4c-composer-was-chosen');
      /* v4.2.62: do not reveal the inline/quick composer card anymore. Popup composer is the only composer UI. */
      Array.prototype.forEach.call(cards, function (card) {
        if (!card) return;
        removeClass(card, 'tb4c-composer-user-opened');
        removeClass(card, 'is-open');
        card.setAttribute('aria-hidden', 'true');
      });
      if (modal) {
        modal.style.display = '';
        modal.style.visibility = '';
        modal.style.opacity = '';
        modal.style.pointerEvents = '';
      }
    }

    Array.prototype.forEach.call(cards, hideIdleComposerCard);
    hideIdleModal();

    if (!doc.tb4cComposerOptInBound) {
      doc.tb4cComposerOptInBound = true;
      doc.addEventListener('click', function (event) {
        var openBtn = closest(event.target, '[data-tb4c-open-composer], [data-tb4c-edit-post]');
        var closeBtn = closest(event.target, '[data-tb4c-close-modal]');
        if (openBtn) {
          markComposerChosen();
          return;
        }
        if (closeBtn) {
          window.setTimeout(function () {
            Array.prototype.forEach.call(cards, function (card) {
              if (!card) return;
              removeClass(card, 'tb4c-composer-user-opened');
              removeClass(card, 'is-open');
              card.setAttribute('aria-hidden', 'true');
            });
            hideIdleModal();
          }, 90);
        }
      }, true);
    }
  }


  function initComposerInlineReturn() {
    addClass(root, 'tb4c-v463-reel-cover-composer-return');
    var cards = doc.querySelectorAll ? doc.querySelectorAll('[data-tb4c-inline-return-composer], .tb4c-inline-composer-return-card') : [];
    Array.prototype.forEach.call(cards, function (card) {
      if (!card) return;
      card.setAttribute('aria-hidden', 'false');
      card.style.display = '';
      card.style.visibility = '';
      card.style.opacity = '';
      card.style.pointerEvents = '';
      card.style.height = '';
      card.style.maxHeight = '';
      removeClass(card, 'tb4c-composer-opt-in-card');
      addClass(card, 'tb4c-inline-composer-return-card');
    });
  }

  function initReelExperience() {
    addClass(root, 'tb4c-v491-feed-ratio-copy');
    addClass(root, 'tb4c-v492-feed-source-clone');
    addClass(root, 'tb4c-v493-feed-popup-rebuild');
    if (doc.body) { addClass(doc.body, 'tb4c-v491-feed-ratio-copy'); addClass(doc.body, 'tb4c-v492-feed-source-clone'); addClass(doc.body, 'tb4c-v493-feed-popup-rebuild'); addClass(doc.body, 'tb4c-v494-feed-popup-top-lock'); }
      addClass(doc.body, 'tb4c-v492-feed-source-clone');
    var viewer = doc.getElementById('tb4cReelViewer');
    var stage = viewer ? viewer.querySelector('[data-tb4c-reel-stage]') : null;
    var slides = stage ? stage.querySelectorAll('.tb4c-reel-slide') : [];
    var syncTimer = 0;
    var activeIndex = -1;

    if (!viewer || !stage) return;
    if (!slides || !slides.length) {
      var emptySlide = doc.createElement('article');
      emptySlide.className = 'tb4c-reel-slide tb4c-reel-empty-slide';
      emptySlide.innerHTML = '<div class="tb4c-reel-phone"><div class="tb4c-reel-phone-media tb4c-reel-empty-card"><div class="tb4c-reel-shade"></div><div class="tb4c-reel-content"><div class="tb4c-reel-text"><h3>ยังไม่มี Reels</h3><p>เมื่อมีโพสต์รูปหรือวิดีโอ ระบบจะแสดงเป็น Reels ให้อัตโนมัติ</p></div></div><div class="tb4c-reel-side"><button type="button" data-tb4c-open-composer><i class="ph ph-plus"></i><span>โพสต์</span></button></div></div></div>';
      stage.appendChild(emptySlide);
      slides = stage.querySelectorAll('.tb4c-reel-slide');
    }

    function refreshSlides() {
      slides = stage ? stage.querySelectorAll('.tb4c-reel-slide') : [];
      return slides;
    }

    function eachSlide(callback) {
      refreshSlides();
      Array.prototype.forEach.call(slides, callback);
    }

    function readPxVar(name, fallback) {
      var value = '';
      var parsed;
      try { value = window.getComputedStyle(doc.documentElement).getPropertyValue(name) || ''; } catch (ignoreStyle) {}
      parsed = parseFloat(String(value).replace('px', '').trim());
      return isNaN(parsed) ? fallback : parsed;
    }

    function visibleBottomNavHeight() {
      var nav = doc.querySelector('.tb4c-mobile-bottom-nav, .tb4c-global-mobile-nav, .site-footer, footer, #colophon');
      var rect;
      var vpH = window.innerHeight || doc.documentElement.clientHeight || 0;
      if (!nav) return 0;
      try { rect = nav.getBoundingClientRect(); } catch (e) { return 0; }
      if (!rect || rect.height <= 0) return 0;
      if (rect.top >= vpH || rect.bottom <= 0) return 0;
      if (rect.bottom >= vpH - 6) return Math.min(rect.height, 120);
      return 0;
    }

    function updateReelBounds() {
      var vv = window.visualViewport;
      var viewportHeight = vv && vv.height ? vv.height : (window.innerHeight || doc.documentElement.clientHeight || 0);
      var viewportWidth = vv && vv.width ? vv.width : (window.innerWidth || doc.documentElement.clientWidth || 0);
      var adminH = readPxVar('--tb4c-admin-h', 0);
      var headerH = readPxVar('--tb4c-header-h', readPxVar('--tb4-modern-header-height', 68));
      var isMobile = window.matchMedia && window.matchMedia('(max-width: 782px)').matches;
      var mobileNavH = isMobile ? readPxVar('--tb4c-mobile-nav-h', 68) : 0;
      var footerH = Math.max(visibleBottomNavHeight(), mobileNavH);
      var top = Math.max(0, Math.round(adminH + headerH));
      var bottom = Math.max(0, Math.round(footerH + readPxVar('--tb4c-safe-bottom', 0)));
      var areaH = Math.max(320, Math.round(viewportHeight - top - bottom));
      var maxStageW = Math.max(240, Math.round(viewportWidth - (isMobile ? 104 : 176)));
      var maxCardH = Math.max(260, Math.round(areaH - (isMobile ? 88 : 96)));
      var cardH = Math.min(maxCardH, isMobile ? 720 : 760, Math.round(maxStageW * 16 / 9));
      var cardW = Math.max(180, Math.round(cardH * 9 / 16));
      var rootStyle = doc.documentElement.style;
      rootStyle.setProperty('--tb4c-reel-safe-top', top + 'px');
      rootStyle.setProperty('--tb4c-reel-safe-bottom', bottom + 'px');
      rootStyle.setProperty('--tb4c-reel-area-h', areaH + 'px');
      rootStyle.setProperty('--tb4c-reel-card-h', cardH + 'px');
      rootStyle.setProperty('--tb4c-reel-card-w', cardW + 'px');
      if (viewer) {
        viewer.style.setProperty('--tb4c-reel-area-h', areaH + 'px');
        viewer.style.setProperty('--tb4c-reel-card-h', cardH + 'px');
        viewer.style.setProperty('--tb4c-reel-card-w', cardW + 'px');
        viewer.style.setProperty('--tb4c-reel-card-ratio', '9 / 16');
      }
    }

    function setViewerState(open) {
      updateReelBounds();
      if (open) {
        addClass(viewer, 'is-open');
        viewer.setAttribute('aria-hidden', 'false');
        if (doc.body) addClass(doc.body, 'tb4c-reel-open');
      } else {
        removeClass(viewer, 'is-open');
        viewer.setAttribute('aria-hidden', 'true');
        if (doc.body) removeClass(doc.body, 'tb4c-reel-open');
      }
    }

    function slideLocalVideo(slide) {
      return slide && slide.querySelector ? slide.querySelector('video.tb4c-reel-video') : null;
    }

    function updateSlidePlayingState(slide, isPlaying) {
      if (!slide) return;
      if (isPlaying) addClass(slide, 'is-playing');
      else removeClass(slide, 'is-playing');
    }

    function pauseAllExcept(index) {
      eachSlide(function (slide, idx) {
        var video = slideLocalVideo(slide);
        if (!video) {
          updateSlidePlayingState(slide, false);
          return;
        }
        if (idx === index && hasClass(viewer, 'is-open')) {
          try {
            video.muted = true;
            var playPromise = video.play();
            if (playPromise && playPromise.catch) playPromise.catch(function () {});
            updateSlidePlayingState(slide, true);
          } catch (e) {
            updateSlidePlayingState(slide, false);
          }
        } else {
          try { video.pause(); } catch (ignorePause) {}
          updateSlidePlayingState(slide, false);
        }
      });
    }

    function getIndexFromPostId(postId) {
      var found = -1;
      if (!postId) return 0;
      eachSlide(function (slide, idx) {
        if (String(slide.getAttribute('data-tb4c-reel-post-id') || '') === String(postId) && found < 0) {
          found = idx;
        }
      });
      return found >= 0 ? found : 0;
    }

    function getClosestSlideIndex() {
      var stageRect = stage.getBoundingClientRect();
      var stageMid = stageRect.top + (stage.clientHeight / 2);
      var bestIndex = 0;
      var bestDistance = Number.MAX_VALUE;
      eachSlide(function (slide, idx) {
        var rect = slide.getBoundingClientRect();
        var mid = rect.top + (rect.height / 2);
        var distance = Math.abs(stageMid - mid);
        if (distance < bestDistance) {
          bestDistance = distance;
          bestIndex = idx;
        }
      });
      return bestIndex;
    }

    function syncActiveSlide() {
      var bestIndex = getClosestSlideIndex();
      if (bestIndex !== activeIndex) {
        activeIndex = bestIndex;
        pauseAllExcept(activeIndex);
      }
      updateNavState();
    }

    function scheduleSync() {
      if (syncTimer) window.clearTimeout(syncTimer);
      syncTimer = window.setTimeout(syncActiveSlide, 80);
    }

    function scrollToSlide(index, behavior) {
      refreshSlides();
      if (!slides.length) return;
      index = Math.max(0, Math.min(index, slides.length - 1));
      try {
        slides[index].scrollIntoView({ block: 'start', behavior: behavior || 'smooth' });
      } catch (ignoreScroll) {
        stage.scrollTop = slides[index].offsetTop || 0;
      }
      activeIndex = index;
      pauseAllExcept(activeIndex);
      window.setTimeout(syncActiveSlide, behavior === 'auto' ? 20 : 180);
    }

    function moveReel(delta) {
      refreshSlides();
      if (!slides.length) return;
      var baseIndex = activeIndex >= 0 ? activeIndex : getClosestSlideIndex();
      var nextIndex = Math.max(0, Math.min(baseIndex + delta, slides.length - 1));
      scrollToSlide(nextIndex, 'smooth');
    }

    function updateNavState() {
      var prevBtn = viewer.querySelector('[data-tb4c-reel-prev]');
      var nextBtn = viewer.querySelector('[data-tb4c-reel-next]');
      refreshSlides();
      if (prevBtn) prevBtn.disabled = activeIndex <= 0;
      if (nextBtn) nextBtn.disabled = activeIndex >= slides.length - 1;
    }

    function openViewer(postId) {
      var index = getIndexFromPostId(postId);
      setViewerState(true);
      scrollToSlide(index, 'auto');
      activeIndex = index;
      updateNavState();
      window.setTimeout(syncActiveSlide, 20);
      window.setTimeout(syncActiveSlide, 180);
    }

    function closeViewer() {
      pauseAllExcept(-1);
      setViewerState(false);
      activeIndex = -1;
      updateNavState();
    }

    function toggleSlideVideo(slide) {
      var video = slideLocalVideo(slide);
      var playPromise;
      if (!video) return;
      if (video.paused) {
        try {
          video.muted = true;
          playPromise = video.play();
          if (playPromise && playPromise.catch) playPromise.catch(function () {});
          updateSlidePlayingState(slide, true);
        } catch (e) {
          updateSlidePlayingState(slide, false);
        }
      } else {
        try { video.pause(); } catch (ignorePause) {}
        updateSlidePlayingState(slide, false);
      }
    }

    if (!viewer.tb4cReelBound) {
      viewer.tb4cReelBound = true;
      stage.addEventListener('scroll', scheduleSync, { passive: true });
      window.addEventListener('resize', function () { updateReelBounds(); scheduleSync(); });
      if (window.visualViewport) window.visualViewport.addEventListener('resize', function () { updateReelBounds(); scheduleSync(); });
      doc.addEventListener('click', function (event) {
        var openBtn = closest(event.target, '[data-tb4c-open-reels]');
        var closeBtn = closest(event.target, '[data-tb4c-close-reels]');
        var playBtn = closest(event.target, '[data-tb4c-reel-toggle-play]');
        var prevBtn = closest(event.target, '[data-tb4c-reel-prev]');
        var nextBtn = closest(event.target, '[data-tb4c-reel-next]');
        var slide;
        if (openBtn) {
          event.preventDefault();
          openViewer(openBtn.getAttribute('data-tb4c-reel-post-id') || '');
          return;
        }
        if (closeBtn) {
          event.preventDefault();
          closeViewer();
          return;
        }
        if (prevBtn) {
          event.preventDefault();
          moveReel(-1);
          return;
        }
        if (nextBtn) {
          event.preventDefault();
          moveReel(1);
          return;
        }
        if (playBtn) {
          event.preventDefault();
          slide = closest(playBtn, '.tb4c-reel-slide');
          toggleSlideVideo(slide);
          return;
        }
      }, true);
      doc.addEventListener('keydown', function (event) {
        if (!hasClass(viewer, 'is-open')) return;
        if (event.key === 'Escape') {
          event.preventDefault();
          closeViewer();
          return;
        }
        if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
          event.preventDefault();
          moveReel(-1);
          return;
        }
        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
          event.preventDefault();
          moveReel(1);
        }
      });
      stage.addEventListener('click', function (event) {
        var video = closest(event.target, 'video.tb4c-reel-video');
        var slide;
        if (!video) return;
        slide = closest(video, '.tb4c-reel-slide');
        toggleSlideVideo(slide);
      });
      updateReelBounds();
      updateNavState();
    }
  }

  function initMiniVideoPlayer() {
    ensureCommunityActivePageClass();
    var slots = doc.querySelectorAll ? doc.querySelectorAll('[data-tb4c-video-slot], .tb4c-post-video-slot') : [];
    var i, videos, iframe;
    for (i = 0; i < slots.length; i++) {
      if (slots[i].tb4cMiniVideoInitialized) {
        ensureMiniVideoControls(slots[i]);
        prepareIframeForContinuousMini(slots[i]);
        continue;
      }
      slots[i].tb4cMiniVideoInitialized = true;
      ensureMiniVideoControls(slots[i]);
      prepareIframeForContinuousMini(slots[i]);
      if (slots[i].addEventListener) {
        slots[i].addEventListener('pointerdown', function (event) { beginVideoUserGesture(this, event); }, { passive: true });
        slots[i].addEventListener('pointermove', function (event) { moveVideoUserGesture(this, event); }, { passive: true });
        slots[i].addEventListener('pointerup', function (event) { finishVideoUserGesture(this, event); }, { passive: true });
        slots[i].addEventListener('pointercancel', function () { cancelVideoUserGesture(this); }, { passive: true });
        slots[i].addEventListener('touchstart', function (event) { beginVideoUserGesture(this, event); }, { passive: true });
        slots[i].addEventListener('touchmove', function (event) { moveVideoUserGesture(this, event); }, { passive: true });
        slots[i].addEventListener('touchend', function (event) { finishVideoUserGesture(this, event); }, { passive: true });
        slots[i].addEventListener('touchcancel', function () { cancelVideoUserGesture(this); }, { passive: true });
        slots[i].addEventListener('keydown', function (event) {
          var key = event.key || event.code || '';
          if (key === 'Enter' || key === ' ' || key === 'Spacebar' || key === 'Space') {
            markVideoSlotUserIntent(this);
            activateVideoSlot(this, true);
          }
        });
      }
      videos = slots[i].querySelectorAll ? slots[i].querySelectorAll('video') : [];
      Array.prototype.forEach.call(videos, function (video) {
        video.addEventListener('play', function () {
          var slot = getVideoSlotFromNode(video);
          markVideoSlotUserIntent(slot);
          activateVideoSlot(slot, true);
          window.setTimeout(requestMiniVideoPositionUpdate, 80);
          window.setTimeout(requestMiniVideoPositionUpdate, 260);
        });
        video.addEventListener('playing', function () {
          var slot = getVideoSlotFromNode(video);
          markVideoSlotUserIntent(slot);
          activateVideoSlot(slot, true);
          window.setTimeout(requestMiniVideoPositionUpdate, 80);
        });
        video.addEventListener('pause', function () {
          var slot = getVideoSlotFromNode(video);
          if (shouldIgnoreVideoPause(slot)) return;
          if (slot && activeVideoSlot === slot) {
            slot.tb4cMiniKeepUntilStop = false;
            restoreMiniVideo(slot);
            removeClass(slot, 'tb4c-video-active');
            clearVideoSlotUserIntent(slot);
            activeVideoSlot = null;
          }
        });
        video.addEventListener('ended', function () { stopVideoSlot(getVideoSlotFromNode(video)); });
      });
      iframe = slots[i].querySelector ? slots[i].querySelector('iframe') : null;
      if (iframe) {
        iframe.addEventListener('pointerdown', function (event) {
          var slot = getVideoSlotFromNode(this);
          beginVideoUserGesture(slot, event);
          tb4cLastIframeIntent = { slot: slot, time: tb4cNowMs() };
        }, { passive: true });
        iframe.addEventListener('pointerup', function (event) { finishVideoUserGesture(getVideoSlotFromNode(this), event); }, { passive: true });
        iframe.addEventListener('touchstart', function (event) {
          var slot = getVideoSlotFromNode(this);
          beginVideoUserGesture(slot, event);
          tb4cLastIframeIntent = { slot: slot, time: tb4cNowMs() };
        }, { passive: true });
        iframe.addEventListener('touchend', function (event) { finishVideoUserGesture(getVideoSlotFromNode(this), event); }, { passive: true });
        iframe.addEventListener('focus', function () {
          var slot = getVideoSlotFromNode(this);
          if (tb4cLastIframeIntent && tb4cLastIframeIntent.slot === slot && tb4cNowMs() - tb4cLastIframeIntent.time < 1800) {
            markVideoSlotUserIntent(slot);
            activateVideoSlot(slot, true);
          }
        });
        iframe.addEventListener('load', function () { prepareIframeForContinuousMini(getVideoSlotFromNode(this)); });
      }
    }
  }

  doc.addEventListener('click', function (event) {
    var miniAction = closest(event.target, '[data-tb4c-video-mini-action]');
    var slot, action;
    if (miniAction) {
      event.preventDefault();
      event.stopPropagation();
      slot = getVideoSlotFromNode(miniAction);
      action = miniAction.getAttribute('data-tb4c-video-mini-action');
      if (action === 'pause') stopVideoSlot(slot);
      else if (action === 'close') closeMiniVideoOnly(slot);
      return;
    }
    slot = getVideoSlotFromNode(event.target);
    if (slot && (slot.querySelector('iframe') || slot.querySelector('video')) && !isMiniVideoIntentIgnoredTarget(event.target)) {
      markVideoSlotUserIntent(slot);
      activateVideoSlot(slot, true);
      window.setTimeout(requestMiniVideoPositionUpdate, 80);
      window.setTimeout(requestMiniVideoPositionUpdate, 260);
    }
  }, true);

  var tb4cMiniVideoRaf = 0;
  function requestMiniVideoPositionUpdate() {
    if (tb4cMiniVideoRaf) return;
    tb4cMiniVideoRaf = window.requestAnimationFrame ? window.requestAnimationFrame(function () {
      tb4cMiniVideoRaf = 0;
      updateMiniVideoPosition();
    }) : window.setTimeout(function () {
      tb4cMiniVideoRaf = 0;
      updateMiniVideoPosition();
    }, 80);
  }

  if (window.addEventListener) {
    /* v4.2.101: no scroll/wheel/touchmove watcher. Mini video position updates only after direct video interaction or viewport resize. */
    window.addEventListener('resize', requestMiniVideoPositionUpdate, { passive: true });
    window.addEventListener('blur', function () {
      window.setTimeout(function () {
        if (!tb4cLastIframeIntent || !tb4cLastIframeIntent.slot) return;
        if (tb4cNowMs() - tb4cLastIframeIntent.time > 1800) return;
        markVideoSlotUserIntent(tb4cLastIframeIntent.slot);
        activateVideoSlot(tb4cLastIframeIntent.slot, true);
      }, 80);
    });
  }

  doc.addEventListener('change', function (event) {
    var input = closest(event.target, 'input[type="file"][name="media_file"], input[type="file"][name="media_files[]"]');
    var row, name, strong, count;
    if (!input) return;
    row = closest(input, '.tb4c-form-upload-row');
    count = input.files ? input.files.length : 0;
    name = count ? input.files[0].name : '';
    if (row) {
      strong = row.querySelector('.tb4c-upload-copy strong');
      if (name) {
        addClass(row, 'has-file');
        if (strong) strong.textContent = count > 1 ? (count + ' ไฟล์ · ' + name) : name;
      } else {
        removeClass(row, 'has-file');
        if (strong) strong.textContent = 'เลือกไฟล์จากเครื่อง';
      }
    }
  });

  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', initComposerSecondRowAndScroll);
  else initComposerSecondRowAndScroll();

  function initStoredReactions() {
    var buttons = doc.querySelectorAll ? doc.querySelectorAll('[data-tb4c-react][data-post-id]') : [];
    var i, btn, key;
    for (i = 0; i < buttons.length; i++) {
      btn = buttons[i];
      key = 'tb4c_' + (btn.getAttribute('data-tb4c-react') || 'like') + '_' + btn.getAttribute('data-post-id');
      if (storageGet(key)) addClass(btn, 'is-reacted');
    }
  }
  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', initStoredReactions);
  else initStoredReactions();




  /* v4.2.31 — Video cover + branded play button.
   * Rule: every visible video card shows a cover layer first. The cover uses a real provider/attachment
   * thumbnail when available and a Thinkb4do fallback when no poster exists.
   */
  function initVideoCovers(context) {
    var scope = context && context.querySelectorAll ? context : doc;
    var buttons;
    if (!scope.querySelectorAll) return;
    addClass(root, 'tb4c-v431-video-cover-loaded');
    addClass(root, 'tb4c-v435-clear-circle-play-loaded');
      addClass(root, 'tb4c-v436-dribbble-player-play-loaded');
      addClass(root, 'tb4c-v437-round-play-bg-loaded');
      addClass(root, 'tb4c-v438-simple-hd-cover-loaded');
      addClass(root, 'tb4c-v439-big-play-triangle-loaded');
      addClass(root, 'tb4c-v440-centered-play-loaded');

    function withAutoplay(src) {
      var hash = '';
      var joiner;
      if (!src) return src;
      if (src.indexOf('#') !== -1) {
        hash = src.slice(src.indexOf('#'));
        src = src.slice(0, src.indexOf('#'));
      }
      joiner = src.indexOf('?') === -1 ? '?' : '&';
      if (src.indexOf('autoplay=') === -1) src += joiner + 'autoplay=1';
      if (src.indexOf('playsinline=') === -1) src += (src.indexOf('?') === -1 ? '?' : '&') + 'playsinline=1';
      return src + hash;
    }

    function videoScope(node) {
      return closest(node, '[data-tb4c-video-cover-scope], [data-tb4c-video-slot], .tb4c-post-video-slot, .tb4c-comment-video-slot');
    }

    function hideCover(slot) {
      if (!slot) return;
      addClass(slot, 'tb4c-video-cover-started');
      removeClass(slot, 'tb4c-video-cover-waiting');
    }

    function showCover(slot) {
      if (!slot) return;
      removeClass(slot, 'tb4c-video-cover-started');
      addClass(slot, 'tb4c-video-cover-waiting');
    }

    function activateSlot(slot) {
      if (!slot) return;
      if (window.tb4cHardPatchActivateVideoSlot) {
        try { window.tb4cHardPatchActivateVideoSlot(slot); } catch (ignore) {}
      }
    }

    function playSlot(slot) {
      var video, iframe, src, promise;
      if (!slot) return;
      hideCover(slot);
      activateSlot(slot);
      video = slot.querySelector ? slot.querySelector('video') : null;
      if (video && video.play) {
        try {
          promise = video.play();
          if (promise && promise.catch) promise.catch(function () { showCover(slot); });
        } catch (ignorePlay) {
          showCover(slot);
        }
        return;
      }
      iframe = slot.querySelector ? slot.querySelector('iframe') : null;
      if (iframe) {
        src = iframe.getAttribute('src') || iframe.getAttribute('data-src') || '';
        if (src) iframe.setAttribute('src', withAutoplay(src));
        activateSlot(slot);
      }
    }

    buttons = scope.querySelectorAll('[data-tb4c-video-play]');
    Array.prototype.forEach.call(buttons, function (btn) {
      var slot = videoScope(btn);
      if (!slot) return;
      addClass(slot, 'tb4c-video-cover-waiting');
      if (btn.tb4cVideoCoverReady) return;
      btn.tb4cVideoCoverReady = true;
      btn.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        playSlot(videoScope(btn));
      }, true);
    });

    Array.prototype.forEach.call(scope.querySelectorAll('[data-tb4c-video-cover-scope], .tb4c-video-has-cover'), function (slot) {
      var video, iframe;
      if (!slot || slot.tb4cVideoCoverScopeReady) return;
      slot.tb4cVideoCoverScopeReady = true;
      addClass(slot, 'tb4c-video-cover-waiting');
      video = slot.querySelector ? slot.querySelector('video') : null;
      if (video) {
        video.addEventListener('play', function () { hideCover(slot); });
        video.addEventListener('playing', function () { hideCover(slot); });
        video.addEventListener('pause', function () {
          if (!video.currentTime || video.currentTime < .35 || video.ended) showCover(slot);
        });
        video.addEventListener('ended', function () { showCover(slot); });
      }
      iframe = slot.querySelector ? slot.querySelector('iframe') : null;
      if (iframe && !iframe.tb4cVideoCoverReady) {
        iframe.tb4cVideoCoverReady = true;
        iframe.setAttribute('allow', ((iframe.getAttribute('allow') || '').indexOf('autoplay') === -1) ? ((iframe.getAttribute('allow') || '') + '; autoplay').replace(/^;\s*/, '') : iframe.getAttribute('allow'));
      }
    });
  }

  /* v4.2.30 — HardPatch mini video engine.
   * Purpose: make the visible behavior change even on pages where the earlier layered CSS/JS
   * was overridden by nested video slots, shortcode rendering, theme overflow, or cached body classes.
   * Rule: do not auto-popup while reading; only after the user clicks/plays a video, then keep mini until stop/X/pause/end.
   */
  function initMiniVideoHardPatch() {
    if (!doc.querySelectorAll || !doc.body) return;
    addClass(root, 'tb4c-v430-hardpatch-loaded');
    addClass(root, 'tb4c-v431-video-cover-loaded');
    addClass(root, 'tb4c-v435-clear-circle-play-loaded');
      addClass(root, 'tb4c-v436-dribbble-player-play-loaded');
      addClass(root, 'tb4c-v437-round-play-bg-loaded');
      addClass(root, 'tb4c-v438-simple-hd-cover-loaded');
      addClass(root, 'tb4c-v439-big-play-triangle-loaded');
      addClass(root, 'tb4c-v440-centered-play-loaded');
    addClass(doc.body, 'tb4c-v430-runtime-ready');

    var slots = doc.querySelectorAll('[data-tb4c-video-slot], .tb4c-post-video-slot');
    var active = window.tb4cHardPatchActiveSlot || null;
    var raf = 0;

    function now() { return Date.now ? Date.now() : new Date().getTime(); }

    function guardPause(slot, duration) {
      if (!slot) return;
      slot.tb4cHardIgnorePauseUntil = now() + (duration || 3200);
    }

    function shouldIgnoreHardPause(slot) {
      return !!(slot && slot.tb4cHardIgnorePauseUntil && now() < slot.tb4cHardIgnorePauseUntil);
    }

    function isControlTarget(node) {
      return !!closest(node, '[data-tb4c-hard-mini-action], [data-tb4c-video-mini-action], [data-tb4c-video-play], [data-tb4c-lightbox], .tb4c-album-expand, a[href]');
    }

    function pickSlot(node) {
      var slot = closest(node, '[data-tb4c-video-slot], .tb4c-post-video-slot');
      if (!slot) return null;
      /* For old nested markup, always promote the visible outer media card when it is the same single video card. */
      var parentSlot = slot.parentNode ? closest(slot.parentNode, '[data-tb4c-video-slot], .tb4c-post-video-slot') : null;
      if (parentSlot && parentSlot.querySelector && parentSlot.querySelector('video, iframe')) return parentSlot;
      return slot;
    }

    function hasPlayable(slot) {
      return !!(slot && slot.querySelector && slot.querySelector('video, iframe'));
    }

    function getNativeState(slot) {
      var v = slot && slot.querySelector ? slot.querySelector('video') : null;
      if (!v) return null;
      return {
        wasPlaying: !v.paused && !v.ended,
        currentTime: isFinite(v.currentTime) ? v.currentTime : 0,
        muted: !!v.muted,
        volume: typeof v.volume === 'number' ? v.volume : null,
        playbackRate: typeof v.playbackRate === 'number' ? v.playbackRate : 1,
        controls: !!v.controls
      };
    }

    function resumeNative(slot, state) {
      var v, promise;
      if (!slot || !state || !state.wasPlaying) return;
      guardPause(slot, 2600);
      v = slot.querySelector ? slot.querySelector('video') : null;
      if (!v || !v.play) return;
      try {
        v.muted = state.muted;
        if (state.volume !== null && state.volume !== undefined) v.volume = state.volume;
        if (state.playbackRate) v.playbackRate = state.playbackRate;
        if (Math.abs((v.currentTime || 0) - state.currentTime) > .75) v.currentTime = state.currentTime;
      } catch (ignore) {}
      try {
        promise = v.play();
        if (promise && promise.catch) promise.catch(function () {});
      } catch (ignorePlay) {}
    }

    function applyNativeRatio(slot) {
      var v = slot && slot.querySelector ? slot.querySelector('video') : null;
      var ratio;
      if (!v) return;
      function setRatio() {
        if (!v.videoWidth || !v.videoHeight) return;
        ratio = v.videoWidth + ' / ' + v.videoHeight;
        slot.style.setProperty('--tb4c-video-ratio', ratio);
        v.style.setProperty('--tb4c-video-ratio', ratio);
      }
      setRatio();
      if (!v.tb4cRatioPatched) {
        v.tb4cRatioPatched = true;
        v.addEventListener('loadedmetadata', setRatio);
      }
    }

    function prepareIframe(slot) {
      var iframe = slot && slot.querySelector ? slot.querySelector('iframe') : null;
      var allow;
      if (!iframe) return;
      allow = iframe.getAttribute('allow') || '';
      if (allow.indexOf('autoplay') === -1) iframe.setAttribute('allow', allow ? allow + '; autoplay' : 'autoplay');
      iframe.setAttribute('loading', 'eager');
    }

    function ensureBar(slot) {
      var bar, actions, pause, close;
      if (!slot || slot.querySelector('.tb4c-mini-video-bar')) return;
      bar = doc.createElement('div');
      bar.className = 'tb4c-mini-video-bar tb4c-hard-mini-video-bar';
      actions = doc.createElement('span');
      actions.className = 'tb4c-mini-video-actions';
      pause = doc.createElement('button');
      pause.type = 'button';
      pause.className = 'tb4c-mini-video-btn';
      pause.setAttribute('data-tb4c-hard-mini-action', 'pause');
      pause.setAttribute('aria-label', 'หยุดวิดีโอ');
      pause.innerHTML = '<span aria-hidden="true">Ⅱ</span><em>หยุด</em>';
      close = doc.createElement('button');
      close.type = 'button';
      close.className = 'tb4c-mini-video-btn';
      close.setAttribute('data-tb4c-hard-mini-action', 'close');
      close.setAttribute('aria-label', 'ปิดวิดีโอเล็ก');
      close.innerHTML = '<span aria-hidden="true">×</span>';
      actions.appendChild(pause);
      actions.appendChild(close);
      bar.appendChild(actions);
      slot.appendChild(bar);
    }

    function originalRect(slot) {
      var anchor = slot && slot.tb4cHardMiniPlaceholder ? slot.tb4cHardMiniPlaceholder : slot;
      return anchor && anchor.getBoundingClientRect ? anchor.getBoundingClientRect() : null;
    }

    function isOutOfReadingArea(slot) {
      var rect = originalRect(slot);
      var h = window.innerHeight || doc.documentElement.clientHeight || 0;
      var top = parseInt((getComputedStyle(root).getPropertyValue('--tb4c-comment-overlay-top') || '0').replace('px', ''), 10) || 0;
      var bottomGap = parseInt((getComputedStyle(root).getPropertyValue('--tb4c-comment-overlay-bottom') || '0').replace('px', ''), 10) || 0;
      var visibleTop, visibleBottom, visible, base, ratio;
      if (!rect || !h) return false;
      visibleTop = Math.max(rect.top, top + 8);
      visibleBottom = Math.min(rect.bottom, h - bottomGap - 70);
      visible = Math.max(0, visibleBottom - visibleTop);
      base = Math.max(1, Math.min(rect.height || h, h - top - bottomGap));
      ratio = visible / base;
      return rect.bottom < top + 80 || rect.top > h - bottomGap - 110 || ratio < .78;
    }

    function enter(slot) {
      var ph, rect, parent, state;
      if (!slot || slot.tb4cHardMini || !slot.tb4cHardUserStarted) return;
      rect = slot.getBoundingClientRect ? slot.getBoundingClientRect() : null;
      state = getNativeState(slot);
      ensureBar(slot);
      applyNativeRatio(slot);
      prepareIframe(slot);
      setMiniVideoNativeControls(slot, false);
      guardPause(slot, 3600);
      ph = doc.createElement('div');
      ph.className = 'tb4c-video-mini-placeholder tb4c-hard-mini-placeholder';
      if (rect && rect.height) ph.style.height = Math.max(160, rect.height) + 'px';
      parent = slot.parentNode;
      slot.tb4cHardMiniOriginalParent = parent || null;
      slot.tb4cHardMiniPlaceholder = ph;
      if (parent) parent.insertBefore(ph, slot);
      if (doc.body && slot.parentNode !== doc.body) doc.body.appendChild(slot);
      slot.tb4cHardMini = true;
      slot.tb4cHardLastSwitch = now();
      addClass(slot, 'tb4c-video-mini-player');
      addClass(slot, 'tb4c-mini-video-portaled');
      addClass(slot, 'tb4c-hard-mini-active');
      addClass(slot, 'tb4c-mini-video-entering');
      addClass(doc.body, 'tb4c-has-mini-video');
      window.setTimeout(function () { removeClass(slot, 'tb4c-mini-video-entering'); }, 120);
      window.setTimeout(function () { resumeNative(slot, state); }, 40);
      window.setTimeout(function () { resumeNative(slot, state); }, 180);
      window.setTimeout(function () { resumeNative(slot, state); }, 520);
      window.setTimeout(function () { resumeNative(slot, state); }, 1000);
    }

    function restore(slot, keepPlaying) {
      var ph, parent, state;
      if (!slot) return;
      state = getNativeState(slot);
      guardPause(slot, keepPlaying ? 4200 : 1800);
      ph = slot.tb4cHardMiniPlaceholder || slot.tb4cMiniPlaceholder;
      parent = ph && ph.parentNode ? ph.parentNode : (slot.tb4cHardMiniOriginalParent || slot.tb4cMiniOriginalParent);
      removeClass(slot, 'tb4c-video-mini-player');
      removeClass(slot, 'tb4c-mini-video-portaled');
      removeClass(slot, 'tb4c-hard-mini-active');
      removeClass(slot, 'tb4c-mini-video-entering');
      slot.tb4cHardMini = false;
      if (parent && slot.parentNode !== parent) {
        if (ph && ph.parentNode === parent) parent.insertBefore(slot, ph.nextSibling);
        else parent.appendChild(slot);
      }
      if (ph && ph.parentNode) ph.parentNode.removeChild(ph);
      slot.tb4cHardMiniPlaceholder = null;
      slot.tb4cHardMiniOriginalParent = null;
      if (doc.body && !doc.querySelector('.tb4c-video-mini-player')) removeClass(doc.body, 'tb4c-has-mini-video');
      restoreMiniVideoNativeControls(slot, state);
      if (keepPlaying) {
        window.setTimeout(function () { resumeNative(slot, state); }, 40);
        window.setTimeout(function () { resumeNative(slot, state); }, 180);
        window.setTimeout(function () { resumeNative(slot, state); }, 520);
        window.setTimeout(function () { resumeNative(slot, state); }, 1100);
      }
    }

    function stop(slot) {
      var v, iframe, src;
      if (!slot) return;
      v = slot.querySelector ? slot.querySelector('video') : null;
      if (v && v.pause) {
        try { v.pause(); } catch (ignore) {}
      }
      iframe = slot.querySelector ? slot.querySelector('iframe') : null;
      if (iframe) {
        src = iframe.getAttribute('src');
        if (src) iframe.setAttribute('src', src);
      }
      restore(slot, false);
      removeClass(slot, 'tb4c-video-active');
      slot.tb4cHardUserStarted = false;
      slot.tb4cHardMiniClosed = true;
      if (active === slot) active = null;
      window.tb4cHardPatchActiveSlot = active;
    }

    function closeOnly(slot) {
      if (!slot) return;
      guardPause(slot, 4200);
      restore(slot, true);
      slot.tb4cHardUserStarted = true;
      slot.tb4cHardMiniClosed = true;
      active = slot;
      window.tb4cHardPatchActiveSlot = active;
      addClass(slot, 'tb4c-video-active');
    }

    function activate(slot) {
      if (!slot || !hasPlayable(slot)) return;
      if (active && active !== slot) {
        stop(active);
      }
      active = slot;
      window.tb4cHardPatchActiveSlot = slot;
      slot.tb4cHardUserStarted = true;
      slot.tb4cHardMiniClosed = false;
      slot.tb4cHardStartedAt = now();
      addClass(slot, 'tb4c-video-active');
      ensureBar(slot);
      applyNativeRatio(slot);
      prepareIframe(slot);
      schedule();
      window.setTimeout(schedule, 140);
      window.setTimeout(schedule, 360);
    }

    window.tb4cHardPatchActivateVideoSlot = function (slot) {
      var picked = pickSlot(slot) || slot;
      if (picked && hasPlayable(picked)) activate(picked);
    };

    function update() {
      raf = 0;
      if (!active || !active.tb4cHardUserStarted || active.tb4cHardMiniClosed) return;
      if (!active.tb4cHardMini && isOutOfReadingArea(active)) enter(active);
      /* Do not auto-restore when back in view. User asked: keep playing until stopped. */
    }

    function schedule() {
      if (raf) return;
      raf = window.requestAnimationFrame ? window.requestAnimationFrame(update) : window.setTimeout(update, 60);
    }

    Array.prototype.forEach.call(slots, function (slot) {
      var videos, iframe;
      if (!slot || slot.tb4cHardPatchReady || !hasPlayable(slot)) return;
      slot.tb4cHardPatchReady = true;
      ensureBar(slot);
      applyNativeRatio(slot);
      prepareIframe(slot);
      videos = slot.querySelectorAll ? slot.querySelectorAll('video') : [];
      Array.prototype.forEach.call(videos, function (video) {
        if (video.tb4cHardVideoReady) return;
        video.tb4cHardVideoReady = true;
        video.addEventListener('play', function () { activate(pickSlot(video) || slot); });
        video.addEventListener('playing', function () { activate(pickSlot(video) || slot); });
        video.addEventListener('pause', function () {
          var s = pickSlot(video) || slot;
          if (shouldIgnoreHardPause(s)) return;
          if (s && s.tb4cHardMini) restore(s, false);
          if (s) {
            s.tb4cHardUserStarted = false;
            removeClass(s, 'tb4c-video-active');
          }
          if (active === s) active = null;
          window.tb4cHardPatchActiveSlot = active;
        });
        video.addEventListener('ended', function () { stop(pickSlot(video) || slot); });
        video.addEventListener('loadedmetadata', function () { applyNativeRatio(pickSlot(video) || slot); });
      });
      iframe = slot.querySelector ? slot.querySelector('iframe') : null;
      if (iframe && !iframe.tb4cHardIframeReady) {
        iframe.tb4cHardIframeReady = true;
        iframe.addEventListener('pointerdown', function () { activate(pickSlot(iframe) || slot); }, { passive: true });
        iframe.addEventListener('touchstart', function () { activate(pickSlot(iframe) || slot); }, { passive: true });
      }
      slot.addEventListener('pointerdown', function (event) {
        if (isControlTarget(event.target)) return;
        slot.tb4cHardPointer = { x: event.clientX || 0, y: event.clientY || 0, time: now(), moved: false };
      }, { passive: true });
      slot.addEventListener('pointermove', function (event) {
        var p = slot.tb4cHardPointer;
        if (!p) return;
        if (Math.abs((event.clientX || 0) - p.x) > 10 || Math.abs((event.clientY || 0) - p.y) > 10) p.moved = true;
      }, { passive: true });
      slot.addEventListener('pointerup', function (event) {
        var p = slot.tb4cHardPointer;
        slot.tb4cHardPointer = null;
        if (!p || p.moved || now() - p.time > 1300 || isControlTarget(event.target)) return;
        activate(slot);
      }, { passive: true });
    });

    if (!window.tb4cHardPatchScrollReady) {
      window.tb4cHardPatchScrollReady = true;
      /* v4.2.101: disable hardpatch scroll/wheel/touchmove watchers; keep only resize. */
      window.addEventListener('resize', schedule, { passive: true });
      doc.addEventListener('click', function (event) {
        var btn = closest(event.target, '[data-tb4c-hard-mini-action], [data-tb4c-video-mini-action]');
        var slot, action;
        if (!btn) return;
        slot = pickSlot(btn);
        action = btn.getAttribute('data-tb4c-hard-mini-action') || btn.getAttribute('data-tb4c-video-mini-action');
        if (action === 'pause' || action === 'close') {
          event.preventDefault();
          event.stopPropagation();
          if (action === 'pause') stop(slot || active);
          else closeOnly(slot || active);
        }
      }, true);
      doc.addEventListener('click', function (event) {
        var slot;
        if (isControlTarget(event.target)) return;
        slot = pickSlot(event.target);
        if (slot && hasPlayable(slot)) activate(slot);
      }, true);
    }
  }




  function updateMemberCoverPreview(url) {
    var area = doc.querySelector('.tb4c-member-area-page');
    var nodes;
    if (!area) return;
    nodes = area.querySelectorAll('.tb4c-member-area-hero-bg, [data-tb4c-member-cover-preview]');
    Array.prototype.forEach.call(nodes, function (node) {
      if (url) node.style.setProperty('--tb4c-member-cover-image', 'url("' + String(url).replace(/"/g, '%22') + '")');
      else node.style.removeProperty('--tb4c-member-cover-image');
    });
  }


  function updateMemberAvatarPreview(url) {
    var area = doc.querySelector('.tb4c-member-area-page');
    var nodes;
    if (!area || !url) return;
    nodes = area.querySelectorAll('[data-tb4c-member-avatar-preview]');
    Array.prototype.forEach.call(nodes, function (node) {
      var img = node.querySelector ? node.querySelector('img') : null;
      var text = node.querySelector ? node.querySelector('span') : null;
      if (!img && doc.createElement) {
        img = doc.createElement('img');
        img.setAttribute('alt', '');
        img.setAttribute('data-tb4c-member-avatar-img', '');
        node.insertBefore(img, node.firstChild || null);
      }
      if (img) img.setAttribute('src', url);
      if (text && text.className !== 'tb4c-member-area-avatar-sync-badge') text.style.display = 'none';
    });
  }

  function initMemberAvatarPreview() {
    var input = doc.querySelector('[data-tb4c-member-avatar-input]');
    if (!input || input.tb4cBound) return;
    input.tb4cBound = true;
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      var reader;
      if (!file || !file.type || file.type.indexOf('image/') !== 0 || !window.FileReader) return;
      reader = new FileReader();
      reader.onload = function (event) {
        if (event && event.target && event.target.result) updateMemberAvatarPreview(event.target.result);
      };
      reader.readAsDataURL(file);
    });
  }

  function initMemberCoverPreview() {
    var input = doc.querySelector('[data-tb4c-member-cover-input]');
    if (!input || input.tb4cBound) return;
    input.tb4cBound = true;
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      var reader;
      if (!file || !file.type || file.type.indexOf('image/') !== 0 || !window.FileReader) return;
      reader = new FileReader();
      reader.onload = function (event) {
        if (event && event.target && event.target.result) updateMemberCoverPreview(event.target.result);
      };
      reader.readAsDataURL(file);
    });
  }

  function alignMemberAreaToViewportCenter() {
    var area = doc.querySelector('.tb4c-member-area-page');
    var container = area ? area.querySelector('.tb4c-member-area-container') : null;
    var viewportWidth = window.innerWidth || doc.documentElement.clientWidth || 0;
    var rect, currentCenter, targetCenter, delta, maxDelta;
    if (!area || !container || !viewportWidth) return;

    addClass(root, 'tb4c-v470-member-centerline');
    addClass(area, 'tb4c-member-area-v70');
    addClass(area, 'tb4c-member-area-v71');
    addClass(doc.body, 'tb4c-member-area-page-active');

    container.style.setProperty('--tb4c-member-center-delta', '0px');
    rect = container.getBoundingClientRect();
    currentCenter = rect.left + (rect.width / 2);
    targetCenter = viewportWidth / 2;
    delta = targetCenter - currentCenter;
    maxDelta = Math.max(0, (viewportWidth - Math.min(rect.width, viewportWidth)) / 2);

    if (rect.width >= viewportWidth - 24) {
      delta = 0;
    } else if (Math.abs(delta) > viewportWidth * 0.45) {
      delta = Math.max(-maxDelta, Math.min(maxDelta, delta));
    }

    if (Math.abs(delta) < 1) delta = 0;
    container.style.setProperty('--tb4c-member-center-delta', delta.toFixed(2) + 'px');
  }

  function scheduleMemberAreaCentering() {
    if (!doc.querySelector('.tb4c-member-area-page')) return;
    window.requestAnimationFrame(function () {
      alignMemberAreaToViewportCenter();
    });
  }



  function initMemberPremiumFlow() {
    var area = doc.querySelector('.tb4c-member-area-page');
    var inputs, links, buttons, i, flag;
    if (!area || !doc.body) return;

    addClass(area, 'tb4c-member-area-v82');
    addClass(area, 'tb4c-member-area-v83');
    addClass(area, 'tb4c-member-area-v84');
    addClass(area, 'tb4c-member-area-v86');
    addClass(area, 'tb4c-member-area-v87');
    addClass(root, 'tb4c-v483-member-feature-flow');
    addClass(root, 'tb4c-v484-member-post-footer-composer-clean');
  addClass(root, 'tb4c-v485-feed-standard-composer-hardline');
  addClass(root, 'tb4c-v486-feed-popup-clone');
  addClass(root, 'tb4c-v487-post-tag-clean');
  addClass(root, 'tb4c-v488-feed-clone-final');
  addClass(root, 'tb4c-v489-composer-tag-type-reel-fit');
  addClass(root, 'tb4c-v490-composer-feed-hard-reset');
    addClass(root, 'tb4c-v491-feed-ratio-copy');
    addClass(doc.body, 'tb4c-member-area-premium-flow');

    try { flag = window.sessionStorage && sessionStorage.getItem('tb4c_member_area_soft_switch'); } catch (e) { flag = ''; }
    if (!area.tb4cPremiumEnterDone) {
      area.tb4cPremiumEnterDone = true;
      addClass(area, flag ? 'is-soft-returning' : 'is-soft-entering');
      if (flag) {
        try { sessionStorage.removeItem('tb4c_member_area_soft_switch'); } catch (ignoreFlag) {}
      }
      window.setTimeout(function () { removeClass(area, 'is-soft-entering'); removeClass(area, 'is-soft-returning'); }, 620);
    }

    function showFeatureLockToast(link) {
      var toast = area.querySelector ? area.querySelector('[data-tb4c-member-feature-lock-toast]') : null;
      var label = link ? (link.getAttribute('data-tb4c-feature-label') || 'ฟีเจอร์นี้') : 'ฟีเจอร์นี้';
      var purchaseUrl = link ? (link.getAttribute('data-tb4c-purchase-url') || link.getAttribute('href') || '') : '';
      if (!toast && doc.createElement) {
        toast = doc.createElement('div');
        toast.className = 'tb4c-member-feature-lock-toast';
        toast.setAttribute('data-tb4c-member-feature-lock-toast', '');
        toast.setAttribute('aria-live', 'polite');
        area.insertBefore(toast, area.querySelector('.tb4c-member-area-layout') || null);
      }
      if (!toast) {
        if (purchaseUrl) window.location.href = purchaseUrl;
        return;
      }
      toast.innerHTML = '<strong>ฟีเจอร์ยังล็อกอยู่</strong><span>' + label + ' เป็นฟีเจอร์เสริมสำหรับสมาชิก เมื่อเปิดซื้อใช้งานแล้วระบบจะปลดล็อกให้อัตโนมัติ</span>' + (purchaseUrl ? '<a href="' + purchaseUrl + '">ดูแพ็กเกจ</a>' : '');
      toast.setAttribute('aria-hidden', 'false');
      addClass(toast, 'is-visible');
      window.clearTimeout(toast.tb4cHideTimer);
      toast.tb4cHideTimer = window.setTimeout(function () {
        removeClass(toast, 'is-visible');
        toast.setAttribute('aria-hidden', 'true');
      }, 4600);
    }

    links = area.querySelectorAll ? area.querySelectorAll('.tb4c-member-area-tabs a, .tb4c-member-area-panel-head a[href*="tb4c_area"], .tb4c-member-area-actions a[href*="tb4c_area"]') : [];
    for (i = 0; i < links.length; i++) {
      if (links[i].tb4cPremiumTabBound) continue;
      links[i].tb4cPremiumTabBound = true;
      links[i].addEventListener('click', function (event) {
        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || this.target === '_blank') return;
        if (this.getAttribute('data-tb4c-feature-locked') === '1' || hasClass(this, 'is-locked')) {
          event.preventDefault();
          showFeatureLockToast(this);
          return;
        }
        addClass(area, 'is-soft-switching');
        addClass(doc.body, 'tb4c-member-area-soft-switching');
        addClass(this, 'is-loading');
        try { if (window.sessionStorage) sessionStorage.setItem('tb4c_member_area_soft_switch', '1'); } catch (e) {}
      }, false);
    }

    buttons = area.querySelectorAll ? area.querySelectorAll('[data-tb4c-open-composer]') : [];
    for (i = 0; i < buttons.length; i++) {
      addClass(buttons[i], 'tb4c-member-composer-trigger');
      buttons[i].removeAttribute('data-tb4c-composer-source');
    }

    inputs = area.querySelectorAll ? area.querySelectorAll('.tb4c-member-file-picker input[type="file"]') : [];
    for (i = 0; i < inputs.length; i++) {
      if (inputs[i].tb4cPremiumFileBound) continue;
      inputs[i].tb4cPremiumFileBound = true;
      inputs[i].addEventListener('change', function () {
        var targetSelector = this.getAttribute('data-tb4c-file-name-target');
        var nameNode = targetSelector && area.querySelector ? area.querySelector(targetSelector) : null;
        var label = this.files && this.files[0] ? this.files[0].name : 'ยังไม่ได้เลือกรูป';
        if (!nameNode) nameNode = this.parentNode ? this.parentNode.querySelector('[data-tb4c-file-name]') : null;
        if (nameNode) nameNode.textContent = label;
        if (this.parentNode) {
          if (this.files && this.files[0]) addClass(this.parentNode, 'has-file');
          else removeClass(this.parentNode, 'has-file');
        }
      }, false);
    }
  }

  function initComposerV490Guard() {
    var modal = doc.getElementById('tb4cComposerModal');
    var form = doc.getElementById('tb4cCommunityForm');
    var contentRow, strip, tagRow, typeField, newTagField, oldMeta, help, insertAfter, parent;
    if (!modal || !form) return;
    addClass(root, 'tb4c-v490-composer-feed-hard-reset');
    addClass(root, 'tb4c-v491-feed-ratio-copy');
    addClass(root, 'tb4c-v492-feed-source-clone');
    addClass(root, 'tb4c-v493-feed-popup-rebuild');
    if (doc.body) {
      addClass(doc.body, 'tb4c-v490-composer-feed-hard-reset');
      addClass(doc.body, 'tb4c-v491-feed-ratio-copy');
      addClass(doc.body, 'tb4c-v493-feed-popup-rebuild');
      addClass(doc.body, 'tb4c-v494-feed-popup-top-lock');
    }
    addClass(form, 'tb4c-v490-composer-form');
    addClass(form, 'tb4c-v491-feed-ratio-form');
    addClass(form, 'tb4c-v493-feed-native-form');

    contentRow = form.querySelector('.tb4c-feed-standard-content-row') || form.querySelector('#tb4c-post-content');
    strip = form.querySelector('.tb4c-v490-redline-strip');
    tagRow = form.querySelector('.tb4c-form-tags-row');
    typeField = form.querySelector('.tb4c-form-type-field');
    newTagField = form.querySelector('.tb4c-form-newtag-field');
    oldMeta = form.querySelector('.tb4c-v489-redline-meta-row, .tb4c-feed-standard-type-newtag-grid');

    if (!strip) {
      strip = doc.createElement('div');
      strip.className = 'tb4c-form-row tb4c-v490-redline-strip tb4c-feed-standard-redline-strip';
      strip.setAttribute('aria-label', 'Tag ประเภท และ Tag สร้างใหม่');
      insertAfter = contentRow && contentRow.nodeType === 1 ? contentRow : null;
      parent = insertAfter && insertAfter.parentNode ? insertAfter.parentNode : form;
      if (insertAfter && insertAfter.nextSibling) parent.insertBefore(strip, insertAfter.nextSibling);
      else parent.appendChild(strip);
    }

    if (tagRow && tagRow.parentNode !== strip) {
      addClass(tagRow, 'tb4c-v490-field');
      addClass(tagRow, 'tb4c-v490-tag-field');
      strip.appendChild(tagRow);
    }
    if (typeField && typeField.parentNode !== strip) {
      addClass(typeField, 'tb4c-v490-field');
      addClass(typeField, 'tb4c-v490-type-field');
      strip.appendChild(typeField);
    }
    if (newTagField && newTagField.parentNode !== strip) {
      addClass(newTagField, 'tb4c-v490-field');
      addClass(newTagField, 'tb4c-v490-newtag-field');
      strip.appendChild(newTagField);
    }
    help = strip.querySelector('.tb4c-v490-strip-help');
    if (!help) {
      help = doc.createElement('small');
      help.className = 'tb4c-v490-strip-help';
      help.textContent = 'Tag จะแสดงใต้คำอธิบายโพสต์ ส่วนประเภทใช้จัดหมวดในระบบ ไม่เอาไปโชว์รกใต้โพสต์';
      strip.appendChild(help);
    }
    if (oldMeta && oldMeta !== strip && oldMeta.parentNode && !oldMeta.children.length) {
      oldMeta.parentNode.removeChild(oldMeta);
    }
  }

  function initMemberAreaThemeBridge() {
    var area = doc.querySelector('.tb4c-member-area-page');
    var entry, container, main, article;
    if (!area || !doc.body) return;

    addClass(doc.body, 'tb4c-member-area-page-active');
    addClass(doc.body, 'tb4c-member-area-theme-bridge');
    addClass(area, 'tb4c-member-area-theme-safe');

    entry = closest(area, '.entry-content');
    article = closest(area, '.tb4-page-article, article');
    container = closest(area, '.container');
    main = closest(area, '.tb4-page-main, main');

    if (entry) addClass(entry, 'tb4c-member-area-entry-host');
    if (article) addClass(article, 'tb4c-member-area-article-host');
    if (container) addClass(container, 'tb4c-member-area-container-host');
    if (main) addClass(main, 'tb4c-member-area-main-host');
    scheduleMemberAreaCentering();
    window.setTimeout(scheduleMemberAreaCentering, 80);
    window.setTimeout(scheduleMemberAreaCentering, 320);
    window.setTimeout(scheduleMemberAreaCentering, 900);
  }



  if (!window.tb4cMemberCenterResizeBound) {
    window.tb4cMemberCenterResizeBound = true;
    window.addEventListener('resize', scheduleMemberAreaCentering, { passive: true });
    window.addEventListener('orientationchange', function () { window.setTimeout(scheduleMemberAreaCentering, 120); });
    window.addEventListener('load', function () { window.setTimeout(scheduleMemberAreaCentering, 120); });
  }

  window.addEventListener('beforeunload', function () {
    var form = getComposerForm();
    if (form) saveComposerDraft(form, true);
  });

  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', function () { initMemberAreaThemeBridge(); initMemberPremiumFlow(); initMemberCoverPreview(); initMemberAvatarPreview(); initPopupComposerForceReady(); initComposerV490Guard(); initComposerOptInOnly(); initComposerInlineReturn(); initHorizontalRailButtons(); initReelExperience(); initVideoCovers(); initMiniVideoPlayer(); });
  else { initMemberAreaThemeBridge(); initMemberPremiumFlow(); initMemberCoverPreview(); initMemberAvatarPreview(); initPopupComposerForceReady(); initComposerV490Guard(); initComposerOptInOnly(); initComposerInlineReturn(); initHorizontalRailButtons(); initReelExperience(); initVideoCovers(); initMiniVideoPlayer(); }
  /* v4.2.101: removed delayed 250ms full re-init scan. */
  /* v4.2.101: removed delayed 1100ms full re-init scan. */

  /* v4.2.101: removed document-wide MiniVideo MutationObserver. New nodes initialize on direct interaction/page load only. */



  // v4.2.88: hard patch after delayed renders/cache while still inside the IIFE scope.
  tb4cNormalizePostFooters(doc);
  window.setTimeout(function () { tb4cNormalizePostFooters(doc); }, 120);
  /* v4.2.101: removed 650/1500ms delayed footer scans. */
  /* v4.2.101: removed footer document-wide MutationObserver. */

  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', function () { refreshAllCommentSubmitStates(doc); });
  } else {
    refreshAllCommentSubmitStates(doc);
  }

})();


/* v4.2.123: removed disabled legacy composer lean runtime. Canonical composer sizing is owned by assets/community-runtime.js and the v4.2.111 smart composer controller. */

/* v4.2.98 — Reel Desktop Next/Prev hard guard.
 * Adds visible left/right navigation for desktop and creates a safe fallback viewer
 * on pages such as member-area where Reel buttons exist but feed viewer markup is absent.
 */
(function () {
  'use strict';
  var doc = document;
  var root = doc.documentElement;
  if (!doc || !root) return;

  function addClass(el, name) {
    if (!el || !name) return;
    if (el.classList) el.classList.add(name);
    else if ((' ' + (el.className || '') + ' ').indexOf(' ' + name + ' ') < 0) el.className = (el.className ? el.className + ' ' : '') + name;
  }

  function removeClass(el, name) {
    if (!el || !name) return;
    if (el.classList) el.classList.remove(name);
    else el.className = (' ' + (el.className || '') + ' ').replace(' ' + name + ' ', ' ').replace(/^\s+|\s+$/g, '');
  }

  function hasClass(el, name) {
    return !!(el && (el.classList ? el.classList.contains(name) : ((' ' + (el.className || '') + ' ').indexOf(' ' + name + ' ') >= 0)));
  }

  function closest(node, selector) {
    while (node && node !== doc) {
      if (node.matches && node.matches(selector)) return node;
      node = node.parentNode;
    }
    return null;
  }

  function pxVar(name, fallback) {
    var value = '';
    var num;
    try { value = window.getComputedStyle(root).getPropertyValue(name) || ''; } catch (ignore) {}
    num = parseFloat(String(value).replace('px', '').trim());
    return isNaN(num) ? fallback : num;
  }

  function safeText(value, fallback) {
    value = String(value || '').replace(/\s+/g, ' ').trim();
    return value || fallback || '';
  }

  function createEl(tag, className, text) {
    var el = doc.createElement(tag);
    if (className) el.className = className;
    if (text !== undefined && text !== null) el.textContent = text;
    return el;
  }

  function getPostId(card) {
    return card ? (card.getAttribute('data-post-id') || card.getAttribute('data-tb4c-post-id') || '') : '';
  }

  function getCardTitle(card) {
    var titleEl = card ? card.querySelector('h1, h2, h3, .tb4c-post-title, .tb4c-post-inline-text h2') : null;
    var title = titleEl ? titleEl.textContent : '';
    return safeText(title, 'Thinkb4do Reel');
  }

  function getCardExcerpt(card) {
    var textEl = card ? card.querySelector('.tb4c-post-inline-text p, .tb4c-post-content p, .tb4c-card-copy, p') : null;
    return safeText(textEl ? textEl.textContent : '', 'เลื่อนดูโพสต์ถัดไป หรือใช้ปุ่มซ้าย/ขวา');
  }

  function getMediaFromCard(card) {
    var media = { type: '', src: '', poster: '' };
    var video = card ? card.querySelector('video[src], video source[src]') : null;
    var lightVideo = card ? card.querySelector('[data-tb4c-lightbox-type="video"][data-tb4c-lightbox-src]') : null;
    var lightImage = card ? card.querySelector('[data-tb4c-lightbox-type="image"][data-tb4c-lightbox-src]') : null;
    var img = card ? card.querySelector('.tb4c-post-media-slot img, .tb4c-thumb img, .tb4c-album-image img, img') : null;
    var iframe = card ? card.querySelector('.tb4c-post-video-slot iframe, iframe[src*="youtube"], iframe[src*="vimeo"]') : null;

    if (video) {
      media.type = 'video';
      media.src = video.getAttribute('src') || (video.querySelector('source') ? video.querySelector('source').getAttribute('src') : '');
      media.poster = video.getAttribute('poster') || '';
      if (media.src) return media;
    }
    if (lightVideo) {
      media.type = 'video';
      media.src = lightVideo.getAttribute('data-tb4c-lightbox-src') || '';
      media.poster = lightVideo.getAttribute('poster') || (img ? (img.currentSrc || img.src || '') : '');
      if (media.src) return media;
    }
    if (iframe) {
      media.type = 'embed';
      media.src = iframe.getAttribute('src') || '';
      if (media.src) return media;
    }
    if (lightImage) {
      media.type = 'image';
      media.src = lightImage.getAttribute('data-tb4c-lightbox-src') || '';
      if (media.src) return media;
    }
    if (img) {
      media.type = 'image';
      media.src = img.currentSrc || img.getAttribute('src') || '';
      if (media.src) return media;
    }
    return null;
  }

  function makeSlide(card, index) {
    var media = getMediaFromCard(card);
    var title = getCardTitle(card);
    var excerpt = getCardExcerpt(card);
    var postId = getPostId(card) || String(index + 1);
    var slide, phone, mediaWrap, shade, content, text, titleNode, excerptNode, side, playBtn;
    if (!media || !media.src) return null;

    slide = createEl('article', 'tb4c-reel-slide tb4c-v498-reel-fallback-slide');
    slide.setAttribute('data-tb4c-reel-post-id', postId);
    slide.setAttribute('data-tb4c-reel-index', String(index));

    phone = createEl('div', 'tb4c-reel-phone');
    mediaWrap = createEl('div', 'tb4c-reel-phone-media');

    if (media.type === 'video') {
      var video = doc.createElement('video');
      video.className = 'tb4c-reel-video';
      video.setAttribute('playsinline', '');
      video.setAttribute('controls', '');
      video.setAttribute('preload', 'metadata');
      video.src = media.src;
      if (media.poster) video.setAttribute('poster', media.poster);
      mediaWrap.appendChild(video);
    } else if (media.type === 'embed') {
      var embed = createEl('div', 'tb4c-reel-embed');
      var iframe = doc.createElement('iframe');
      iframe.src = media.src;
      iframe.setAttribute('allowfullscreen', 'allowfullscreen');
      iframe.setAttribute('loading', 'lazy');
      iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
      embed.appendChild(iframe);
      mediaWrap.appendChild(embed);
    } else {
      var image = doc.createElement('img');
      image.src = media.src;
      image.alt = title;
      image.loading = 'lazy';
      image.decoding = 'async';
      mediaWrap.appendChild(image);
    }

    shade = createEl('div', 'tb4c-reel-shade');
    content = createEl('div', 'tb4c-reel-content');
    text = createEl('div', 'tb4c-reel-text');
    titleNode = createEl('h3', '', title);
    excerptNode = createEl('p', '', excerpt);
    text.appendChild(titleNode);
    text.appendChild(excerptNode);
    content.appendChild(text);
    side = createEl('div', 'tb4c-reel-side');
    playBtn = createEl('button', '', 'เล่น/หยุด');
    playBtn.type = 'button';
    playBtn.setAttribute('data-tb4c-reel-toggle-play', '');
    side.appendChild(playBtn);

    mediaWrap.appendChild(shade);
    mediaWrap.appendChild(content);
    mediaWrap.appendChild(side);
    phone.appendChild(mediaWrap);
    slide.appendChild(phone);
    return slide;
  }

  function ensureViewer() {
    var viewer = doc.getElementById('tb4cReelViewer');
    var panel, topbar, brand, mark, labelWrap, close, stage;
    if (viewer) return viewer;
    if (!doc.body || !doc.querySelector('[data-tb4c-open-reels]')) return null;

    viewer = createEl('div', 'tb4c-reel-viewer tb4c-v498-reel-fallback-viewer');
    viewer.id = 'tb4cReelViewer';
    viewer.setAttribute('aria-hidden', 'true');
    viewer.innerHTML = '<div class="tb4c-reel-backdrop" data-tb4c-close-reels></div>';
    panel = createEl('div', 'tb4c-reel-panel');
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-modal', 'true');
    panel.setAttribute('aria-label', 'Thinkb4do Reels');

    topbar = createEl('div', 'tb4c-reel-topbar');
    brand = createEl('div', 'tb4c-reel-brand');
    mark = createEl('span', 'tb4c-reel-brand-mark', 'T');
    labelWrap = createEl('div');
    labelWrap.appendChild(createEl('strong', '', 'Thinkb4do Reels'));
    labelWrap.appendChild(createEl('small', '', 'ใช้ปุ่มซ้าย/ขวาเพื่อเลื่อนบน Desktop'));
    brand.appendChild(mark);
    brand.appendChild(labelWrap);
    close = createEl('button', 'tb4c-reel-close', '×');
    close.type = 'button';
    close.setAttribute('data-tb4c-close-reels', '');
    close.setAttribute('aria-label', 'ปิดรีล');
    topbar.appendChild(brand);
    topbar.appendChild(close);
    panel.appendChild(topbar);

    stage = createEl('div', 'tb4c-reel-stage');
    stage.setAttribute('data-tb4c-reel-stage', '');
    panel.appendChild(stage);
    viewer.appendChild(panel);
    doc.body.appendChild(viewer);
    return viewer;
  }

  function ensureNav(viewer) {
    var panel = viewer ? viewer.querySelector('.tb4c-reel-panel') : null;
    var stage = viewer ? viewer.querySelector('[data-tb4c-reel-stage]') : null;
    var prev = viewer ? viewer.querySelector('[data-tb4c-reel-prev]') : null;
    var next = viewer ? viewer.querySelector('[data-tb4c-reel-next]') : null;
    if (!viewer || !panel) return;
    if (!prev) {
      prev = createEl('button', 'tb4c-reel-nav tb4c-reel-nav-prev tb4c-reel-desk-nav', '‹');
      prev.type = 'button';
      prev.setAttribute('data-tb4c-reel-prev', '');
      prev.setAttribute('aria-label', 'Reel ก่อนหน้า');
      panel.insertBefore(prev, stage || panel.firstChild);
    } else {
      addClass(prev, 'tb4c-reel-desk-nav');
      prev.textContent = '‹';
    }
    if (!next) {
      next = createEl('button', 'tb4c-reel-nav tb4c-reel-nav-next tb4c-reel-desk-nav', '›');
      next.type = 'button';
      next.setAttribute('data-tb4c-reel-next', '');
      next.setAttribute('aria-label', 'Reel ถัดไป');
      panel.insertBefore(next, stage || null);
    } else {
      addClass(next, 'tb4c-reel-desk-nav');
      next.textContent = '›';
    }
  }

  function buildFallbackSlides(viewer) {
    var stage = viewer ? viewer.querySelector('[data-tb4c-reel-stage]') : null;
    var cards, added = 0;
    if (!stage) return;
    if (stage.querySelector('.tb4c-reel-slide:not(.tb4c-reel-empty-slide)')) return;
    stage.innerHTML = '';
    cards = doc.querySelectorAll('.tb4c-post-card[data-post-id], article[data-post-id], .tb4c-social-post-card[data-post-id]');
    Array.prototype.forEach.call(cards, function (card) {
      var slide = makeSlide(card, added);
      if (!slide) return;
      stage.appendChild(slide);
      added += 1;
    });
    if (!added) {
      var emptySlide = createEl('article', 'tb4c-reel-slide tb4c-reel-empty-slide');
      emptySlide.innerHTML = '<div class="tb4c-reel-phone"><div class="tb4c-reel-phone-media tb4c-reel-empty-card"><div class="tb4c-reel-shade"></div><div class="tb4c-reel-content"><div class="tb4c-reel-text"><h3>ยังไม่มี Reels</h3><p>เมื่อมีโพสต์รูปหรือวิดีโอ ระบบจะแสดงเป็น Reels ให้อัตโนมัติ</p></div></div></div></div>';
      stage.appendChild(emptySlide);
    }
  }

  function applyBounds(viewer) {
    var vv = window.visualViewport;
    var viewportHeight = vv && vv.height ? vv.height : (window.innerHeight || root.clientHeight || 780);
    var viewportWidth = vv && vv.width ? vv.width : (window.innerWidth || root.clientWidth || 1280);
    var adminH = pxVar('--tb4c-admin-h', 0);
    var headerH = pxVar('--tb4c-header-h', pxVar('--tb4-modern-header-height', 68));
    var mobile = window.matchMedia && window.matchMedia('(max-width: 760px)').matches;
    var bottom = mobile ? Math.max(pxVar('--tb4c-mobile-nav-h', 68), pxVar('--tb4c-safe-bottom', 0)) : 0;
    var top = Math.max(0, Math.round(adminH + headerH));
    var areaH = Math.max(340, Math.round(viewportHeight - top - bottom));
    var sideSpace = mobile ? 104 : 220;
    var maxStageW = Math.max(220, viewportWidth - sideSpace);
    var maxCardH = Math.max(260, Math.round(areaH - (mobile ? 86 : 78)));
    var cardH = Math.min(maxCardH, mobile ? 720 : 760, Math.round(maxStageW * 16 / 9));
    var cardW = Math.max(180, Math.round(cardH * 9 / 16));

    root.style.setProperty('--tb4c-reel-safe-top', top + 'px');
    root.style.setProperty('--tb4c-reel-safe-bottom', bottom + 'px');
    root.style.setProperty('--tb4c-reel-area-h', areaH + 'px');
    root.style.setProperty('--tb4c-reel-card-h', cardH + 'px');
    root.style.setProperty('--tb4c-reel-card-w', cardW + 'px');
    if (viewer && viewer.style) {
      viewer.style.setProperty('--tb4c-reel-area-h', areaH + 'px');
      viewer.style.setProperty('--tb4c-reel-card-h', cardH + 'px');
      viewer.style.setProperty('--tb4c-reel-card-w', cardW + 'px');
    }
  }

  function currentIndex(stage) {
    var slides = stage ? stage.querySelectorAll('.tb4c-reel-slide') : [];
    var rect, mid, bestIndex = 0, bestDistance = Number.MAX_VALUE;
    if (!stage || !slides.length) return 0;
    try {
      rect = stage.getBoundingClientRect();
      mid = rect.top + (rect.height / 2);
    } catch (ignore) {
      return 0;
    }
    Array.prototype.forEach.call(slides, function (slide, index) {
      var sRect = slide.getBoundingClientRect();
      var sMid = sRect.top + (sRect.height / 2);
      var dist = Math.abs(sMid - mid);
      if (dist < bestDistance) {
        bestDistance = dist;
        bestIndex = index;
      }
    });
    return bestIndex;
  }

  function scrollToIndex(stage, index, behavior) {
    var slides = stage ? stage.querySelectorAll('.tb4c-reel-slide') : [];
    if (!stage || !slides.length) return 0;
    index = Math.max(0, Math.min(index, slides.length - 1));
    try {
      slides[index].scrollIntoView({ block: 'start', behavior: behavior || 'smooth' });
    } catch (ignore) {
      stage.scrollTop = slides[index].offsetTop || 0;
    }
    updateNav(stage, index);
    return index;
  }

  function updateNav(stage, index) {
    var viewer = stage ? closest(stage, '.tb4c-reel-viewer') : doc.getElementById('tb4cReelViewer');
    var slides = stage ? stage.querySelectorAll('.tb4c-reel-slide') : [];
    var prev = viewer ? viewer.querySelector('[data-tb4c-reel-prev]') : null;
    var next = viewer ? viewer.querySelector('[data-tb4c-reel-next]') : null;
    if (typeof index !== 'number') index = currentIndex(stage);
    if (prev) prev.disabled = index <= 0;
    if (next) next.disabled = index >= slides.length - 1;
  }

  function openFallback(postId) {
    var viewer = ensureViewer();
    var stage, slides, index = 0;
    if (!viewer) return;
    ensureNav(viewer);
    buildFallbackSlides(viewer);
    applyBounds(viewer);
    stage = viewer.querySelector('[data-tb4c-reel-stage]');
    slides = stage ? stage.querySelectorAll('.tb4c-reel-slide') : [];
    Array.prototype.forEach.call(slides, function (slide, i) {
      if (String(slide.getAttribute('data-tb4c-reel-post-id') || '') === String(postId || '')) index = i;
    });
    addClass(viewer, 'is-open');
    viewer.setAttribute('aria-hidden', 'false');
    if (doc.body) addClass(doc.body, 'tb4c-reel-open');
    scrollToIndex(stage, index, 'auto');
    window.setTimeout(function () { updateNav(stage); }, 90);
  }

  function closeViewer() {
    var viewer = doc.getElementById('tb4cReelViewer');
    if (!viewer) return;
    Array.prototype.forEach.call(viewer.querySelectorAll('video'), function (video) {
      try { video.pause(); } catch (ignore) {}
    });
    removeClass(viewer, 'is-open');
    viewer.setAttribute('aria-hidden', 'true');
    if (doc.body) removeClass(doc.body, 'tb4c-reel-open');
  }

  function move(viewer, delta) {
    var stage = viewer ? viewer.querySelector('[data-tb4c-reel-stage]') : null;
    if (!stage) return;
    scrollToIndex(stage, currentIndex(stage) + delta, 'smooth');
    window.setTimeout(function () { updateNav(stage); }, 160);
  }

  function initReelDesktopNext() {
    var viewer = doc.getElementById('tb4cReelViewer');
    addClass(root, 'tb4c-v498-reel-desktop-next');
    if (doc.body) addClass(doc.body, 'tb4c-v498-reel-desktop-next');
    if (viewer) {
      ensureNav(viewer);
      applyBounds(viewer);
      updateNav(viewer.querySelector('[data-tb4c-reel-stage]'));
    } else if (doc.querySelector('[data-tb4c-open-reels]')) {
      /* v4.2.101: keep Reel fallback lazy. Do not build/scan all slides on page load. */
      viewer = null;
    }
  }

  if (!doc.tb4cReelDesktopNext498Bound) {
    doc.tb4cReelDesktopNext498Bound = true;
    doc.addEventListener('click', function (event) {
      var openBtn = closest(event.target, '[data-tb4c-open-reels]');
      var closeBtn = closest(event.target, '[data-tb4c-close-reels]');
      var prevBtn = closest(event.target, '[data-tb4c-reel-prev]');
      var nextBtn = closest(event.target, '[data-tb4c-reel-next]');
      var viewer = doc.getElementById('tb4cReelViewer');
      var createdByFallback = viewer && hasClass(viewer, 'tb4c-v498-reel-fallback-viewer');

      if (openBtn && (!viewer || createdByFallback)) {
        event.preventDefault();
        openFallback(openBtn.getAttribute('data-tb4c-reel-post-id') || '');
        return;
      }
      if (closeBtn && createdByFallback) {
        event.preventDefault();
        closeViewer();
        return;
      }
      if ((prevBtn || nextBtn) && createdByFallback) {
        event.preventDefault();
        move(viewer, prevBtn ? -1 : 1);
        return;
      }
    }, true);

    doc.addEventListener('keydown', function (event) {
      var viewer = doc.getElementById('tb4cReelViewer');
      if (!viewer || !hasClass(viewer, 'is-open') || !hasClass(viewer, 'tb4c-v498-reel-fallback-viewer')) return;
      if (event.key === 'Escape') {
        event.preventDefault();
        closeViewer();
      } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
        event.preventDefault();
        move(viewer, -1);
      } else if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
        event.preventDefault();
        move(viewer, 1);
      }
    });

    window.addEventListener('resize', function () {
      var viewer = doc.getElementById('tb4cReelViewer');
      applyBounds(viewer);
      updateNav(viewer ? viewer.querySelector('[data-tb4c-reel-stage]') : null);
    }, { passive: true });
    if (window.visualViewport && window.visualViewport.addEventListener) {
      window.visualViewport.addEventListener('resize', function () {
        var viewer = doc.getElementById('tb4cReelViewer');
        applyBounds(viewer);
        updateNav(viewer ? viewer.querySelector('[data-tb4c-reel-stage]') : null);
      });
    }
  }

  initReelDesktopNext();
  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', initReelDesktopNext);
  window.setTimeout(initReelDesktopNext, 120);
}());


/* v4.2.101 — Performance Hard Cut: Lightweight Reel/Nav Runtime
 * Replaces document-wide rescue observers with click/resize-only logic.
 */
(function () {
  'use strict';
  var doc = document;
  var root = doc.documentElement;
  var resizeTimer = 0;

  function closest(el, selector) {
    while (el && el !== doc) {
      if (el.matches && el.matches(selector)) return el;
      el = el.parentNode;
    }
    return null;
  }
  function addClass(el, name) { if (el && el.classList) el.classList.add(name); }
  function hasClass(el, name) { return !!(el && el.classList && el.classList.contains(name)); }
  function create(tag, cls, text) {
    var el = doc.createElement(tag);
    if (cls) el.className = cls;
    if (text) el.textContent = text;
    return el;
  }
  function pxVar(name, fallback) {
    var raw = '';
    try { raw = window.getComputedStyle(root).getPropertyValue(name) || ''; } catch (ignore) {}
    var n = parseFloat(String(raw).replace('px', '').trim());
    return isNaN(n) ? fallback : n;
  }
  function applyBounds(viewer) {
    var vv = window.visualViewport;
    var vh = vv && vv.height ? vv.height : (window.innerHeight || root.clientHeight || 780);
    var vw = vv && vv.width ? vv.width : (window.innerWidth || root.clientWidth || 1280);
    var mobile = window.matchMedia && window.matchMedia('(max-width: 760px)').matches;
    var top = Math.max(0, Math.round(pxVar('--tb4c-admin-h', 0) + pxVar('--tb4c-header-h', pxVar('--tb4-modern-header-height', 68))));
    var bottom = mobile ? Math.max(pxVar('--tb4c-mobile-nav-h', 68), pxVar('--tb4c-safe-bottom', 0)) : 0;
    var areaH = Math.max(320, Math.round(vh - top - bottom));
    var cardH = Math.min(mobile ? 700 : 740, Math.max(260, areaH - (mobile ? 82 : 72)), Math.round(Math.max(220, vw - (mobile ? 96 : 220)) * 16 / 9));
    var cardW = Math.max(178, Math.round(cardH * 9 / 16));
    root.style.setProperty('--tb4c-reel-safe-top', top + 'px');
    root.style.setProperty('--tb4c-reel-safe-bottom', bottom + 'px');
    root.style.setProperty('--tb4c-reel-area-h', areaH + 'px');
    root.style.setProperty('--tb4c-reel-card-h', cardH + 'px');
    root.style.setProperty('--tb4c-reel-card-w', cardW + 'px');
    if (viewer) {
      viewer.style.setProperty('--tb4c-reel-area-h', areaH + 'px');
      viewer.style.setProperty('--tb4c-reel-card-h', cardH + 'px');
      viewer.style.setProperty('--tb4c-reel-card-w', cardW + 'px');
    }
  }
  function stageOf(viewer) { return viewer ? viewer.querySelector('[data-tb4c-reel-stage], .tb4c-reel-stage') : null; }
  function slidesOf(stage) { return stage ? Array.prototype.slice.call(stage.querySelectorAll('.tb4c-reel-slide')) : []; }
  function currentIndex(stage) {
    var slides = slidesOf(stage);
    var rect, mid, best = 0, bestD = Number.MAX_VALUE;
    if (!stage || !slides.length) return 0;
    rect = stage.getBoundingClientRect();
    mid = rect.top + rect.height / 2;
    slides.forEach(function (slide, i) {
      var r = slide.getBoundingClientRect();
      var d = Math.abs((r.top + r.height / 2) - mid);
      if (d < bestD) { bestD = d; best = i; }
    });
    return best;
  }
  function updateNav(viewer, index) {
    var stage = stageOf(viewer);
    var slides = slidesOf(stage);
    var prev = viewer ? viewer.querySelector('[data-tb4c-reel-prev]') : null;
    var next = viewer ? viewer.querySelector('[data-tb4c-reel-next]') : null;
    if (typeof index !== 'number') index = currentIndex(stage);
    if (prev) prev.disabled = index <= 0 || slides.length <= 1;
    if (next) next.disabled = index >= slides.length - 1 || slides.length <= 1;
  }
  function ensureNav(viewer) {
    var panel, stage, prev, next;
    if (!viewer) return;
    panel = viewer.querySelector('.tb4c-reel-panel') || viewer;
    stage = stageOf(viewer);
    prev = viewer.querySelector('[data-tb4c-reel-prev]');
    next = viewer.querySelector('[data-tb4c-reel-next]');
    if (!prev) {
      prev = create('button', 'tb4c-reel-nav tb4c-reel-nav-prev tb4c-reel-desk-nav tb4c-v42101-lite-nav', '‹');
      prev.type = 'button'; prev.setAttribute('data-tb4c-reel-prev', ''); prev.setAttribute('aria-label', 'Reel ก่อนหน้า');
      panel.insertBefore(prev, stage || panel.firstChild);
    }
    if (!next) {
      next = create('button', 'tb4c-reel-nav tb4c-reel-nav-next tb4c-reel-desk-nav tb4c-v42101-lite-nav', '›');
      next.type = 'button'; next.setAttribute('data-tb4c-reel-next', ''); next.setAttribute('aria-label', 'Reel ถัดไป');
      panel.appendChild(next);
    }
    addClass(prev, 'tb4c-v42101-lite-nav'); addClass(next, 'tb4c-v42101-lite-nav');
    applyBounds(viewer); updateNav(viewer);
  }
  function move(viewer, delta) {
    var stage = stageOf(viewer);
    var slides = slidesOf(stage);
    var index;
    if (!stage || !slides.length) return;
    index = Math.max(0, Math.min(currentIndex(stage) + delta, slides.length - 1));
    try { slides[index].scrollIntoView({ block: 'start', behavior: 'smooth' }); }
    catch (ignore) { stage.scrollTop = slides[index].offsetTop || 0; }
    updateNav(viewer, index);
  }
  function whenViewerOpen(fn) {
    var viewer = doc.getElementById('tb4cReelViewer');
    if (!viewer || !hasClass(viewer, 'is-open')) return;
    fn(viewer);
  }

  addClass(root, 'tb4c-v42101-performance-hard-cut');
  if (doc.body) addClass(doc.body, 'tb4c-v42101-performance-hard-cut');
  else doc.addEventListener('DOMContentLoaded', function () { addClass(doc.body, 'tb4c-v42101-performance-hard-cut'); }, { once: true });

  doc.addEventListener('click', function (event) {
    var openBtn = closest(event.target, '[data-tb4c-open-reels]');
    var prevBtn = closest(event.target, '[data-tb4c-reel-prev]');
    var nextBtn = closest(event.target, '[data-tb4c-reel-next]');
    var viewer = doc.getElementById('tb4cReelViewer');
    if (openBtn) {
      window.setTimeout(function () { ensureNav(doc.getElementById('tb4cReelViewer')); }, 40);
      return;
    }
    if ((prevBtn || nextBtn) && viewer) {
      event.preventDefault();
      ensureNav(viewer);
      move(viewer, prevBtn ? -1 : 1);
    }
  }, true);
  doc.addEventListener('keydown', function (event) {
    whenViewerOpen(function (viewer) {
      if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') { event.preventDefault(); ensureNav(viewer); move(viewer, -1); }
      if (event.key === 'ArrowRight' || event.key === 'ArrowDown') { event.preventDefault(); ensureNav(viewer); move(viewer, 1); }
    });
  });
  window.addEventListener('resize', function () {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(function () { whenViewerOpen(function (viewer) { applyBounds(viewer); updateNav(viewer); }); }, 90);
  }, { passive: true });
  doc.addEventListener('DOMContentLoaded', function () { whenViewerOpen(ensureNav); }, { once: true });
}());


/* v4.2.102 — Reel rail side Next bars
 * Dedicated lightweight runtime for the horizontal Reel strip. No MutationObserver, no scroll scan.
 */
(function () {
  'use strict';
  var doc = document;
  var root = doc.documentElement;

  function addClass(el, name) { if (el && el.classList) el.classList.add(name); }
  function removeClass(el, name) { if (el && el.classList) el.classList.remove(name); }
  function closest(el, selector) {
    while (el && el !== doc) {
      if (el.matches && el.matches(selector)) return el;
      el = el.parentNode;
    }
    return null;
  }
  function makeButton(dir, targetId) {
    var btn = doc.createElement('button');
    btn.type = 'button';
    btn.className = 'tb4c-rail-nav ' + (dir < 0 ? 'is-prev' : 'is-next') + ' tb4c-v42102-rail-side-nav';
    btn.setAttribute('aria-label', dir < 0 ? 'เลื่อน Reel ไปทางซ้าย' : 'เลื่อน Reel ไปทางขวา');
    btn.setAttribute('data-tb4c-scroll-target', targetId);
    btn.setAttribute('data-tb4c-scroll-dir', String(dir));
    btn.innerHTML = '<span aria-hidden="true">' + (dir < 0 ? '‹' : '›') + '</span>';
    return btn;
  }
  function setDisabled(btn, disabled) {
    if (!btn) return;
    btn.removeAttribute('disabled');
    btn.setAttribute('aria-disabled', disabled ? 'true' : 'false');
    if (disabled) addClass(btn, 'is-disabled');
    else removeClass(btn, 'is-disabled');
  }
  function updateShell(shell) {
    var rail = shell ? shell.querySelector('.tb4c-reel-rail') : null;
    var prev = shell ? shell.querySelector('.tb4c-rail-nav.is-prev') : null;
    var next = shell ? shell.querySelector('.tb4c-rail-nav.is-next') : null;
    var maxLeft, left;
    if (!rail) return;
    maxLeft = Math.max(0, (rail.scrollWidth || 0) - (rail.clientWidth || 0) - 2);
    left = rail.scrollLeft || 0;
    setDisabled(prev, left <= 2 || maxLeft <= 2);
    setDisabled(next, maxLeft <= 2 || left >= maxLeft);
  }
  function prepareShell(shell, index) {
    var rail, id, prev, next;
    if (!shell) return;
    rail = shell.querySelector('.tb4c-reel-rail');
    if (!rail) return;
    id = rail.id || ('tb4cReelRailSideNext' + (index + 1));
    rail.id = id;
    prev = shell.querySelector('.tb4c-rail-nav.is-prev');
    next = shell.querySelector('.tb4c-rail-nav.is-next');
    if (!prev) shell.insertBefore(makeButton(-1, id), rail);
    if (!next) shell.appendChild(makeButton(1, id));
    prev = shell.querySelector('.tb4c-rail-nav.is-prev');
    next = shell.querySelector('.tb4c-rail-nav.is-next');
    if (prev) { prev.setAttribute('data-tb4c-scroll-target', id); prev.setAttribute('data-tb4c-scroll-dir', '-1'); }
    if (next) { next.setAttribute('data-tb4c-scroll-target', id); next.setAttribute('data-tb4c-scroll-dir', '1'); }
    if (!rail.tb4cV42102RailBound) {
      rail.tb4cV42102RailBound = true;
      rail.addEventListener('scroll', function () { updateShell(shell); }, { passive: true });
    }
    updateShell(shell);
    window.setTimeout(function () { updateShell(shell); }, 140);
  }
  function scrollRail(btn) {
    var shell = closest(btn, '.tb4c-reel-shell[data-tb4c-rail-shell]');
    var targetId = btn ? btn.getAttribute('data-tb4c-scroll-target') : '';
    var rail = targetId ? doc.getElementById(targetId) : (shell ? shell.querySelector('.tb4c-reel-rail') : null);
    var dir = parseInt(btn.getAttribute('data-tb4c-scroll-dir') || '0', 10);
    var amount;
    if (!rail || !dir) return;
    amount = Math.max(116, Math.round((rail.clientWidth || 300) * 0.72)) * (dir < 0 ? -1 : 1);
    try { rail.scrollBy({ left: amount, behavior: 'smooth' }); }
    catch (ignore) { rail.scrollLeft += amount; }
    if (shell) {
      window.setTimeout(function () { updateShell(shell); }, 80);
      window.setTimeout(function () { updateShell(shell); }, 340);
    }
  }
  function init() {
    var shells;
    addClass(root, 'tb4c-v42102-reel-rail-side-next');
    if (doc.body) addClass(doc.body, 'tb4c-v42102-reel-rail-side-next');
    shells = doc.querySelectorAll('.tb4c-reel-shell[data-tb4c-rail-shell]');
    Array.prototype.forEach.call(shells, prepareShell);
  }

  if (!doc.tb4cV42102ReelRailNextBound) {
    doc.tb4cV42102ReelRailNextBound = true;
    doc.addEventListener('click', function (event) {
      var btn = closest(event.target, '.tb4c-reel-shell[data-tb4c-rail-shell] > .tb4c-rail-nav[data-tb4c-scroll-dir]');
      if (!btn) return;
      event.preventDefault();
      event.stopPropagation();
      scrollRail(btn);
    }, true);
    window.addEventListener('resize', function () {
      window.clearTimeout(doc.tb4cV42102RailResizeTimer || 0);
      doc.tb4cV42102RailResizeTimer = window.setTimeout(init, 120);
    }, { passive: true });
  }
  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
  window.setTimeout(init, 160);
}());


/* v4.2.126: duplicate Smart Composer v4.2.111 position/controller block remains removed from legacy.js.
 * Canonical Composer open/close, panel tabs, upload status, and safe bounds are now handled by assets/community-runtime.js.
 */

/* Thinkb4do Community v4.2.112 — Composer Loading Stop + Latest Jump
 * Adds soft loading dots, turns submit into a Stop control while posting,
 * aborts the active AJAX request when Stop is clicked, and scrolls to the newest post after completion.
 */
(function () {
  'use strict';
  if (window.tb4cComposerLoadingStop42112Booted) return;
  window.tb4cComposerLoadingStop42112Booted = true;

  var doc = document;
  var root = doc.documentElement;
  var cfg = window.tb4cCommunity || {};
  var latestKey = 'tb4c_v42112_latest_post_jump';

  function addClass(el, c) { if (el && el.classList && !el.classList.contains(c)) el.classList.add(c); }
  function removeClass(el, c) { if (el && el.classList && el.classList.contains(c)) el.classList.remove(c); }
  function qsa(sel, base) { try { return Array.prototype.slice.call((base || doc).querySelectorAll(sel)); } catch (e) { return []; } }
  function closest(el, sel) { return el && el.closest ? el.closest(sel) : null; }
  function storageSet(key, value) { try { if (window.localStorage) window.localStorage.setItem(key, value); } catch (e) {} }
  function storageGet(key) { try { return window.localStorage ? window.localStorage.getItem(key) : null; } catch (e) { return null; } }
  function storageRemove(key) { try { if (window.localStorage) window.localStorage.removeItem(key); } catch (e) {} }

  function bootClass() {
    addClass(root, 'tb4c-v42112-loading-stop-latest');
    if (doc.body) addClass(doc.body, 'tb4c-v42112-loading-stop-latest');
  }
  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', bootClass, { once: true });
  else bootClass();

  function setStatus(form, message, type, html) {
    var status = form ? form.querySelector('[data-tb4c-form-status]') : null;
    if (!status) return;
    removeClass(status, 'is-success');
    removeClass(status, 'is-error');
    if (type) addClass(status, 'is-' + type);
    if (html) status.innerHTML = message || '';
    else status.textContent = message || '';
  }

  function dots(label) {
    return '<span class="tb4c-v42112-loading-copy">' + (label || 'กำลังโหลด') + '</span><span class="tb4c-v42112-soft-dots" aria-hidden="true"><i></i><i></i><i></i></span>';
  }

  function getSubmit(form) {
    return form ? form.querySelector('button[type="submit"]') : null;
  }

  function buildTitle(form) {
    var title = form ? form.querySelector('[name="title"]') : null;
    var content = form ? form.querySelector('[name="content"]') : null;
    var mediaUrl = form ? form.querySelector('[name="media_url"]') : null;
    var source = '';
    if (!title || String(title.value || '').trim()) return;
    source = String((content && content.value) || '').replace(/\s+/g, ' ').trim();
    if (!source && mediaUrl && String(mediaUrl.value || '').trim()) source = 'โพสต์สื่อใหม่';
    if (!source) source = 'โพสต์ใหม่';
    title.value = source.slice(0, 86);
  }

  function markPosting(form) {
    var btn = getSubmit(form);
    if (!form) return;
    form.setAttribute('data-tb4c-v42112-loading', '1');
    form.setAttribute('aria-busy', 'true');
    addClass(form, 'is-posting');
    addClass(form, 'tb4c-v42112-is-loading');
    if (btn) {
      if (!btn.getAttribute('data-tb4c-v42112-original-html')) btn.setAttribute('data-tb4c-v42112-original-html', btn.innerHTML || '');
      btn.disabled = false;
      btn.setAttribute('aria-label', 'หยุดการโหลด');
      btn.setAttribute('title', 'หยุดการโหลด');
      btn.setAttribute('data-tb4c-v42112-stop', '1');
      addClass(btn, 'tb4c-v42112-stop-submit');
      btn.innerHTML = '<span class="tb4c-v42112-stop-icon" aria-hidden="true"><i class="ph ph-stop-circle"></i><b>■</b></span><span class="tb4c-v42112-soft-dots" aria-hidden="true"><i></i><i></i><i></i></span>';
    }
    setStatus(form, dots('กำลังโพสต์'), '', true);
  }

  function unmarkPosting(form, restore) {
    var btn = getSubmit(form);
    if (!form) return;
    form.removeAttribute('data-tb4c-v42112-loading');
    form.removeAttribute('aria-busy');
    removeClass(form, 'is-posting');
    removeClass(form, 'tb4c-v42112-is-loading');
    form._tb4cV42112Request = null;
    if (btn) {
      btn.disabled = false;
      btn.removeAttribute('data-tb4c-v42112-stop');
      btn.removeAttribute('aria-label');
      btn.removeAttribute('title');
      removeClass(btn, 'tb4c-v42112-stop-submit');
      if (restore !== false) {
        btn.innerHTML = btn.getAttribute('data-tb4c-v42112-original-html') || '<span class="tb4c-submit-text"><i class="ph ph-paper-plane-tilt"></i> โพสต์</span><span class="tb4c-submit-loader" aria-hidden="true"><i></i><i></i><i></i></span>';
      }
    }
  }

  function abortablePost(formData, done, fail) {
    var ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
    var controller = (window.AbortController && window.fetch) ? new AbortController() : null;
    var xhr = null;
    var finished = false;
    function safeDone(json) { if (finished) return; finished = true; done(json); }
    function safeFail(err) { if (finished) return; finished = true; fail(err || new Error('เกิดข้อผิดพลาด')); }

    if (window.fetch) {
      fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
        signal: controller ? controller.signal : undefined
      }).then(function (res) {
        return res.json();
      }).then(function (json) {
        safeDone(json);
      }).catch(function (err) {
        if (controller && controller.signal && controller.signal.aborted) {
          safeFail(new Error('ยกเลิกการโหลดแล้ว'));
          return;
        }
        xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl, true);
        xhr.withCredentials = true;
        xhr.onreadystatechange = function () {
          var json;
          if (xhr.readyState !== 4) return;
          if (xhr.status >= 200 && xhr.status < 300) {
            try { json = JSON.parse(xhr.responseText); } catch (e) { json = null; }
            if (json) safeDone(json); else safeFail(new Error('Invalid JSON response'));
          } else {
            safeFail(new Error('HTTP ' + xhr.status));
          }
        };
        xhr.onerror = function () { safeFail(new Error('Network error')); };
        xhr.onabort = function () { safeFail(new Error('ยกเลิกการโหลดแล้ว')); };
        xhr.send(formData);
      });
      return {
        abort: function () {
          if (controller && !controller.signal.aborted) controller.abort();
          if (xhr && xhr.abort) { try { xhr.abort(); } catch (e) {} }
          safeFail(new Error('ยกเลิกการโหลดแล้ว'));
        }
      };
    }

    xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxUrl, true);
    xhr.withCredentials = true;
    xhr.onreadystatechange = function () {
      var json;
      if (xhr.readyState !== 4) return;
      if (xhr.status >= 200 && xhr.status < 300) {
        try { json = JSON.parse(xhr.responseText); } catch (e) { json = null; }
        if (json) safeDone(json); else safeFail(new Error('Invalid JSON response'));
      } else {
        safeFail(new Error('HTTP ' + xhr.status));
      }
    };
    xhr.onerror = function () { safeFail(new Error('Network error')); };
    xhr.onabort = function () { safeFail(new Error('ยกเลิกการโหลดแล้ว')); };
    xhr.send(formData);
    return { abort: function () { if (xhr && xhr.abort) xhr.abort(); safeFail(new Error('ยกเลิกการโหลดแล้ว')); } };
  }

  function rememberLatest(postId, url) {
    if (!postId) return;
    storageSet(latestKey, JSON.stringify({ id: String(postId), url: url || '', t: Date.now() }));
  }

  function scrollToLatest() {
    var raw = storageGet(latestKey);
    var data, selector, card, feed;
    if (!raw) return;
    try { data = JSON.parse(raw); } catch (e) { data = null; }
    if (!data || !data.id || (Date.now() - Number(data.t || 0)) > 120000) {
      storageRemove(latestKey);
      return;
    }
    selector = '[data-post-id="' + String(data.id).replace(/"/g, '') + '"]';
    card = doc.querySelector(selector);
    if (!card) {
      feed = doc.getElementById('tb4cFeedList') || doc.querySelector('.tb4c-feed-list,.tb4c-member-own-feed,.tb4c-member-area-feed');
      card = feed ? feed.querySelector('.tb4c-post-card,.tb4c-member-area-list-card,[data-post-id]') : null;
    }
    if (!card) return;
    try { card.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { card.scrollIntoView(true); }
    addClass(card, 'tb4c-v42112-latest-focus');
    window.setTimeout(function () { removeClass(card, 'tb4c-v42112-latest-focus'); storageRemove(latestKey); }, 2600);
  }

  function softCloseModal() {
    var modal = doc.getElementById('tb4cComposerModal') || doc.querySelector('.tb4c-modal[data-tb4c-stable-composer]');
    if (!modal) return;
    removeClass(modal, 'is-open');
    removeClass(modal, 'tb4c-composer-visible-now');
    modal.setAttribute('aria-hidden', 'true');
  }

  doc.addEventListener('click', function (event) {
    var btn = event.target && event.target.closest ? event.target.closest('button[type="submit"][data-tb4c-v42112-stop]') : null;
    var form;
    if (!btn) return;
    form = closest(btn, '#tb4cCommunityForm');
    if (!form || form.getAttribute('data-tb4c-v42112-loading') !== '1') return;
    event.preventDefault();
    event.stopImmediatePropagation();
    if (form._tb4cV42112Request && form._tb4cV42112Request.abort) form._tb4cV42112Request.abort();
    unmarkPosting(form);
    setStatus(form, 'ยกเลิกการโหลดแล้ว', 'error');
  }, true);

  doc.addEventListener('submit', function (event) {
    var form = closest(event.target, '#tb4cCommunityForm');
    var fd, request, submitBtn;
    if (!form) return;
    event.preventDefault();
    event.stopImmediatePropagation();

    if (form.getAttribute('data-tb4c-v42112-loading') === '1') return;

    if (!window.FormData) {
      setStatus(form, 'เบราว์เซอร์นี้เก่าเกินไปสำหรับการส่งฟอร์มแบบ AJAX', 'error');
      return;
    }
    if (cfg && cfg.isLoggedIn === false) {
      window.location.href = cfg.loginUrl || '/wp-login.php';
      return;
    }

    buildTitle(form);
    fd = new FormData(form);
    if (!fd.get('nonce')) fd.append('nonce', cfg.nonce || '');
    markPosting(form);
    submitBtn = getSubmit(form);
    if (submitBtn) submitBtn.disabled = false;

    request = abortablePost(fd, function (json) {
      var data = (json && json.data) || {};
      if (!json || !json.success) {
        unmarkPosting(form);
        setStatus(form, data.message || ((cfg.messages && cfg.messages.failed) || 'เกิดข้อผิดพลาด'), 'error');
        return;
      }
      rememberLatest(data.post_id, data.url);
      setStatus(form, (data.message || ((cfg.messages && cfg.messages.saved) || 'สำเร็จ')) + ' กำลังพาไปดูข้อความล่าสุด' + '<span class="tb4c-v42112-soft-dots" aria-hidden="true"><i></i><i></i><i></i></span>', 'success', true);
      unmarkPosting(form);
      form.reset();
      window.setTimeout(function () {
        if (data.status === 'publish' || data.status === 'updated') {
          window.location.reload();
        } else {
          softCloseModal();
          scrollToLatest();
        }
      }, 520);
    }, function (err) {
      unmarkPosting(form);
      setStatus(form, (err && err.message) || ((cfg.messages && cfg.messages.failed) || 'เกิดข้อผิดพลาด'), /ยกเลิก/.test((err && err.message) || '') ? '' : 'error');
    });
    form._tb4cV42112Request = request;
  }, true);

  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', function () { window.setTimeout(scrollToLatest, 420); }, { once: true });
  else window.setTimeout(scrollToLatest, 420);
}());

/* Thinkb4do Community v4.2.113 — Performance All Devices / Cross Browser Smooth Guard
 * ลดงานหนักจากแพตช์สะสม: lazy media, idle boot, throttled smart-composer repaint, และ low-memory class
 */
(function () {
  'use strict';
  var doc = document;
  var root = doc.documentElement;
  if (!doc || !root || window.tb4cPerformanceAll42113Booted) return;
  window.tb4cPerformanceAll42113Booted = true;

  function addClass(el, name) {
    if (!el || !name) return;
    if (el.classList) el.classList.add(name);
    else if ((' ' + (el.className || '') + ' ').indexOf(' ' + name + ' ') < 0) el.className = (el.className ? el.className + ' ' : '') + name;
  }

  function onReady(fn) {
    if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', fn, { once: true });
    else fn();
  }

  function runIdle(fn, timeout) {
    if (window.requestIdleCallback) return window.requestIdleCallback(fn, { timeout: timeout || 700 });
    return window.setTimeout(fn, 80);
  }

  function isCriticalMedia(el) {
    if (!el || !el.closest) return false;
    return !!el.closest('#tb4cComposerModal, .tb4c-modal.is-open, .tb4c-reel-viewer.is-open, .tb4c-video-mini-player, .tb4c-home-discovery-hero, [data-tb4c-critical-media]');
  }

  function markLazyMedia(scope) {
    var host = scope && scope.querySelectorAll ? scope : doc;
    var imgs = host.querySelectorAll ? host.querySelectorAll('img:not([data-tb4c-v42113-media-ready])') : [];
    var iframes = host.querySelectorAll ? host.querySelectorAll('iframe:not([data-tb4c-v42113-media-ready])') : [];
    var videos = host.querySelectorAll ? host.querySelectorAll('video:not([data-tb4c-v42113-media-ready])') : [];
    var i, el;

    for (i = 0; i < imgs.length; i++) {
      el = imgs[i];
      el.setAttribute('data-tb4c-v42113-media-ready', '1');
      if (!isCriticalMedia(el)) {
        if (!el.getAttribute('loading')) el.setAttribute('loading', 'lazy');
        if (!el.getAttribute('decoding')) el.setAttribute('decoding', 'async');
        if (!el.getAttribute('fetchpriority')) el.setAttribute('fetchpriority', 'low');
      }
    }

    for (i = 0; i < iframes.length; i++) {
      el = iframes[i];
      el.setAttribute('data-tb4c-v42113-media-ready', '1');
      if (!isCriticalMedia(el)) {
        el.setAttribute('loading', 'lazy');
        if (!el.getAttribute('title')) el.setAttribute('title', 'Thinkb4do embedded media');
      }
    }

    for (i = 0; i < videos.length; i++) {
      el = videos[i];
      el.setAttribute('data-tb4c-v42113-media-ready', '1');
      if (!el.getAttribute('preload') || (!isCriticalMedia(el) && el.getAttribute('preload') === 'auto')) el.setAttribute('preload', 'metadata');
      el.setAttribute('playsinline', '');
    }
  }

  function markDeviceClass() {
    var memory = navigator.deviceMemory || 0;
    var cores = navigator.hardwareConcurrency || 0;
    addClass(root, 'tb4c-v42113-performance-all');
    if (doc.body) addClass(doc.body, 'tb4c-v42113-performance-all');
    if ((memory && memory <= 4) || (cores && cores <= 4) || (window.matchMedia && window.matchMedia('(max-width: 760px)').matches)) {
      addClass(root, 'tb4c-v42113-low-memory');
      if (doc.body) addClass(doc.body, 'tb4c-v42113-low-memory');
    }
  }

  function throttleGlobal(name) {
    var original = window[name];
    var pending = 0;
    var lastArgs = null;
    if (typeof original !== 'function' || original.tb4cV42113Throttled) return;
    function wrapped() {
      lastArgs = arguments;
      if (pending) return;
      pending = (window.requestAnimationFrame || function (cb) { return window.setTimeout(cb, 16); })(function () {
        pending = 0;
        try { original.apply(window, lastArgs || []); } catch (ignore) {}
      });
    }
    wrapped.tb4cV42113Throttled = true;
    window[name] = wrapped;
  }

  function throttleSmartComposer() {
    throttleGlobal('tb4cApplyFeedNativeComposer497');
    throttleGlobal('tb4cApplyComposerLean42100');
    throttleGlobal('tb4cApplySmartComposer42110');
    throttleGlobal('tb4cApplySmartComposer42111');
  }

  function bindLightObserver() {
    var target = doc.getElementById('tb4cFeedList') || doc.querySelector('.tb4c-shell');
    var timer = 0;
    if (!target || !window.MutationObserver) return;
    if (target.tb4cV42113Observed) return;
    target.tb4cV42113Observed = true;
    new MutationObserver(function (mutations) {
      if (timer) return;
      timer = window.setTimeout(function () {
        timer = 0;
        var i;
        for (i = 0; i < mutations.length; i++) {
          if (mutations[i].addedNodes && mutations[i].addedNodes.length) {
            markLazyMedia(target);
            break;
          }
        }
      }, 120);
    }).observe(target, { childList: true, subtree: true });
  }

  markDeviceClass();
  onReady(function () {
    markDeviceClass();
    runIdle(function () {
      markLazyMedia(doc);
      throttleSmartComposer();
      bindLightObserver();
      addClass(root, 'tb4c-v42113-idle-ready');
      if (doc.body) addClass(doc.body, 'tb4c-v42113-idle-ready');
    }, 700);
  });

  if (window.addEventListener) {
    window.addEventListener('pageshow', function () { runIdle(function () { markLazyMedia(doc); throttleSmartComposer(); }, 500); }, { passive: true });
    window.addEventListener('orientationchange', function () { throttleSmartComposer(); }, { passive: true });
  }
}());


/* Thinkb4do Community v4.2.114 — Performance Anti-Lag Deep Guard
 * ลดอาการหน่วงบนมือถือ/เครื่องช้า: ปิด smooth-scroll ภายในชุมชน, ใส่ slow-device class ตั้งแต่ต้น, และลดงาน resize ซ้อนด้วย RAF
 */
(function () {
  'use strict';
  var doc = document;
  var root = doc.documentElement;
  if (!doc || !root || window.tb4cPerformanceAntiLag42114Booted) return;
  window.tb4cPerformanceAntiLag42114Booted = true;

  function addClass(el, name) {
    if (!el || !name) return;
    if (el.classList) el.classList.add(name);
    else if ((' ' + (el.className || '') + ' ').indexOf(' ' + name + ' ') < 0) el.className = (el.className ? el.className + ' ' : '') + name;
  }

  function matchesSlowDevice() {
    var memory = navigator.deviceMemory || 0;
    var cores = navigator.hardwareConcurrency || 0;
    var narrow = window.matchMedia && window.matchMedia('(max-width: 900px), (hover: none), (pointer: coarse), (prefers-reduced-motion: reduce)').matches;
    return !!(narrow || (memory && memory <= 4) || (cores && cores <= 4));
  }

  function applyDeviceClass() {
    addClass(root, 'tb4c-v42114-anti-lag');
    if (doc.body) addClass(doc.body, 'tb4c-v42114-anti-lag');
    if (matchesSlowDevice()) {
      addClass(root, 'tb4c-v42114-slow-device');
      if (doc.body) addClass(doc.body, 'tb4c-v42114-slow-device');
    }
  }

  function patchSmoothScrollForSlowDevices() {
    var proto = window.Element && window.Element.prototype;
    var original;
    if (!proto || !proto.scrollIntoView || proto.scrollIntoView.tb4cV42114Patched || !matchesSlowDevice()) return;
    original = proto.scrollIntoView;
    proto.scrollIntoView = function (arg) {
      var scoped = this && this.closest && this.closest('.tb4c-shell, #tb4c-community-app, .tb4c-modal, .tb4c-reel-viewer, .tb4c-member-area-shell, .tb4-home-feed-main');
      if (scoped && arg && typeof arg === 'object' && arg.behavior === 'smooth') {
        try { arg = Object.assign({}, arg, { behavior: 'auto' }); } catch (ignoreAssign) { arg.behavior = 'auto'; }
      }
      return original.call(this, arg);
    };
    proto.scrollIntoView.tb4cV42114Patched = true;
  }

  function onceReady(fn) {
    if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', fn, { once: true });
    else fn();
  }

  applyDeviceClass();
  patchSmoothScrollForSlowDevices();
  onceReady(function () {
    applyDeviceClass();
    patchSmoothScrollForSlowDevices();
  });
}());
