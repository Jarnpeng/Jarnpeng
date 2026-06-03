(function tb4cSingleInlineEdit4269(window, document) {
  'use strict';
  if (!window || !document || window.tb4cSingleInlineEdit4269Booted) return;
  window.tb4cSingleInlineEdit4269Booted = true;
  var cfg = window.tb4cCommunity || {};
  function closest(node, selector) { try { return node && node.closest ? node.closest(selector) : null; } catch (e) { return null; } }
  function qs(scope, selector) { try { return scope ? scope.querySelector(selector) : null; } catch (e) { return null; } }
  function setStatus(root, text, type) {
    var status = qs(root, '[data-tb4c-sp-post-status]');
    if (!status) return;
    status.classList.remove('is-error', 'is-success');
    if (type) status.classList.add(type === 'error' ? 'is-error' : 'is-success');
    status.textContent = text || '';
  }
  function getEditButton(root) { return qs(root, '[data-tb4c-sp-edit-post]'); }
  function syncEditFields(root, btn) {
    var title = qs(root, '[data-tb4c-sp-edit-title]');
    var content = qs(root, '[data-tb4c-sp-edit-content]');
    if (title) title.value = (btn && btn.getAttribute('data-post-title')) || (qs(root, '[data-tb4c-sp-post-title]') || {}).textContent || '';
    if (content) content.value = (btn && btn.getAttribute('data-post-content')) || (qs(root, '[data-tb4c-sp-post-content]') || {}).textContent || '';
  }
  function showEditor(root) {
    var box = qs(root, '[data-tb4c-sp-post-editbox]');
    var btn = getEditButton(root);
    if (!box) return;
    syncEditFields(root, btn);
    box.hidden = false;
    root.classList.add('tb4c-sp268-is-editing-post');
    setStatus(root, '', '');
    window.setTimeout(function () { var first = qs(root, '[data-tb4c-sp-edit-title]'); if (first && first.focus) first.focus(); }, 30);
  }
  function hideEditor(root) {
    var box = qs(root, '[data-tb4c-sp-post-editbox]');
    if (box) box.hidden = true;
    root.classList.remove('tb4c-sp268-is-editing-post');
    setStatus(root, '', '');
  }
  function postFormData(fd, done, fail) {
    var url = cfg.ajaxUrl || (window.ajaxurl || '/wp-admin/admin-ajax.php');
    if (!window.fetch) { if (fail) fail({ message: 'เบราว์เซอร์ไม่รองรับการบันทึกแบบนี้' }); return; }
    fetch(url, { method: 'POST', credentials: 'same-origin', body: fd })
      .then(function (res) { return res.json(); })
      .then(function (json) { if (done) done(json); })
      .catch(function (err) { if (fail) fail(err || { message: 'บันทึกไม่สำเร็จ' }); });
  }
  function savePost(root, trigger) {
    var btn = getEditButton(root);
    var titleInput = qs(root, '[data-tb4c-sp-edit-title]');
    var contentInput = qs(root, '[data-tb4c-sp-edit-content]');
    var postId = btn ? (btn.getAttribute('data-tb4c-sp-edit-post') || '') : '';
    var title = titleInput ? titleInput.value.trim() : '';
    var content = contentInput ? contentInput.value.trim() : '';
    if (!postId) { setStatus(root, 'ไม่พบโพสต์ที่ต้องการแก้ไข', 'error'); return; }
    if (!title && !content) { setStatus(root, 'กรุณาใส่หัวข้อหรือรายละเอียดก่อนบันทึก', 'error'); return; }
    var fd = new FormData();
    fd.append('action', 'tb4c_update_community_post');
    fd.append('nonce', cfg.nonce || '');
    fd.append('post_id', postId);
    fd.append('title', title || content.substring(0, 60) || 'โพสต์ใหม่');
    fd.append('content', content);
    fd.append('post_type', (btn && btn.getAttribute('data-post-type')) || 'discussion');
    fd.append('visibility', (btn && btn.getAttribute('data-post-visibility')) || 'public');
    fd.append('media_url', (btn && (btn.getAttribute('data-post-media-album') || btn.getAttribute('data-post-media-url'))) || '');
    fd.append('tags', (btn && btn.getAttribute('data-post-tags')) || '');
    fd.append('feeling', (btn && btn.getAttribute('data-post-feeling')) || '');
    fd.append('poll_question', (btn && btn.getAttribute('data-post-poll-question')) || '');
    fd.append('poll_options', (btn && btn.getAttribute('data-post-poll-options')) || '');
    if (trigger) trigger.disabled = true;
    setStatus(root, 'กำลังบันทึก...', '');
    postFormData(fd, function (json) {
      if (trigger) trigger.disabled = false;
      if (!json || !json.success) {
        setStatus(root, (json && json.data && json.data.message) || 'บันทึกไม่สำเร็จ', 'error');
        return;
      }
      var data = json.data || {};
      var titleEl = qs(root, '[data-tb4c-sp-post-title]');
      var contentEl = qs(root, '[data-tb4c-sp-post-content]');
      var newTitle = data.title || title;
      if (titleEl) titleEl.textContent = newTitle;
      if (contentEl) {
        if (data.content_html) contentEl.innerHTML = data.content_html;
        else contentEl.textContent = content;
      }
      if (btn) {
        btn.setAttribute('data-post-title', newTitle);
        btn.setAttribute('data-post-content', data.content || content);
      }
      setStatus(root, data.message || 'บันทึกการแก้ไขแล้ว', 'success');
      window.setTimeout(function () { hideEditor(root); }, 550);
    }, function (err) {
      if (trigger) trigger.disabled = false;
      setStatus(root, (err && err.message) || 'บันทึกไม่สำเร็จ', 'error');
    });
  }
  document.addEventListener('click', function (event) {
    var actionBtn = closest(event.target, '[data-tb4c-sp-post-action]');
    if (!actionBtn) return;
    var root = closest(actionBtn, '#tb4c-sp268-root');
    if (!root) return;
    var action = actionBtn.getAttribute('data-tb4c-sp-post-action') || '';
    if (action !== 'edit' && action !== 'save' && action !== 'cancel') return;
    event.preventDefault();
    if (event.stopPropagation) event.stopPropagation();
    if (action === 'edit') { showEditor(root); return; }
    if (action === 'cancel') { hideEditor(root); return; }
    if (action === 'save') { savePost(root, actionBtn); }
  }, true);
})(window, document);

(function tb4ccpCommentPage(window, document) {
  'use strict';
  if (!window || !document || window.tb4ccpCommentPageBooted) return;
  window.tb4ccpCommentPageBooted = true;
  var cfg = window.tb4cCommunity || {};
  function closest(node, selector) { try { return node && node.closest ? node.closest(selector) : null; } catch (e) { return null; } }
  function qs(scope, selector) { try { return scope ? scope.querySelector(selector) : null; } catch (e) { return null; } }
  function qsa(scope, selector) { try { return scope ? Array.prototype.slice.call(scope.querySelectorAll(selector)) : []; } catch (e) { return []; } }
  function ajax(fd) {
    var url = cfg.ajaxUrl || (window.ajaxurl || '/wp-admin/admin-ajax.php');
    return fetch(url, { method: 'POST', credentials: 'same-origin', body: fd }).then(function (res) { return res.json(); });
  }
  function lockEvent(event) {
    if (!event) return;
    event.preventDefault();
    if (event.stopImmediatePropagation) event.stopImmediatePropagation();
    else if (event.stopPropagation) event.stopPropagation();
  }
  var mediaTapState = { active: false, x: 0, y: 0, moved: false, movedAt: 0, target: null };
  function eventPoint(event) {
    var src = (event && event.touches && event.touches[0]) || (event && event.changedTouches && event.changedTouches[0]) || event || {};
    return { x: Number(src.clientX || 0), y: Number(src.clientY || 0) };
  }
  function mediaSelector() {
    return '[data-tb4ccp-media-open],[data-tb4c-lightbox],.tb4c-comment-image-slot,.tb4ccp-post-image-open,.tb4c-sp268-media a[href],.tb4c-sp268-media button,.tb4c-sp268-media img';
  }
  function mediaTarget(node) {
    return closest(node, mediaSelector());
  }
  function isMediaTarget(node) {
    return !!mediaTarget(node);
  }
  function rememberMediaDown(event) {
    var target = mediaTarget(event.target);
    if (!target) return;
    var p = eventPoint(event);
    mediaTapState.active = true;
    mediaTapState.x = p.x;
    mediaTapState.y = p.y;
    mediaTapState.moved = false;
    mediaTapState.target = target;
  }
  function rememberMediaMove(event) {
    if (!mediaTapState.active) return;
    var p = eventPoint(event);
    var dx = Math.abs(p.x - mediaTapState.x);
    var dy = Math.abs(p.y - mediaTapState.y);
    if (dx > 4 || dy > 4) {
      mediaTapState.moved = true;
      mediaTapState.movedAt = Date.now();
      if (mediaTapState.target && mediaTapState.target.classList) mediaTapState.target.classList.add('tb4ccp-media-is-scrolling');
    }
  }
  function forgetMediaDown() {
    var target = mediaTapState.target;
    mediaTapState.active = false;
    mediaTapState.target = null;
    if (target && target.classList) {
      window.setTimeout(function () { target.classList.remove('tb4ccp-media-is-scrolling'); }, 120);
    }
  }
  function mediaClickShouldOpen() {
    if (mediaTapState.moved || (mediaTapState.movedAt && Date.now() - mediaTapState.movedAt < 1100)) {
      mediaTapState.moved = false;
      return false;
    }
    return true;
  }
  function markMediaImagesSafe(root) {
    qsa(root || document, '#tb4c-sp268-root .tb4c-sp268-media img,#tb4c-sp268-root .tb4c-comment-media-slot img,#tb4c-sp268-root .tb4c-sp268-media video,#tb4c-sp268-root .tb4c-comment-media-slot video').forEach(function (media) {
      media.draggable = false;
      media.setAttribute('draggable', 'false');
    });
  }
  function setText(el, text, ok) {
    if (!el) return;
    el.textContent = text || '';
    el.classList.toggle('is-success', !!ok);
    el.classList.toggle('is-error', ok === false);
  }
  function escapeHtml(text) {
    return String(text || '').replace(/[&<>'"]/g, function (ch) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[ch];
    });
  }
  function setCount(root, count) {
    qsa(root || document, '[data-tb4c-comment-count]').forEach(function (el) {
      el.textContent = String(count || 0) + ' ความคิดเห็น';
    });
    qsa(root || document, 'a[href="#comments"] span').forEach(function (el) {
      if (/^\d+$/.test((el.textContent || '').trim())) el.textContent = String(count || 0);
    });
  }
  function showListState(root) {
    var list = qs(root, '[data-tb4c-comment-list]');
    var empty = qs(root, '[data-tb4c-comments-empty]');
    var hasItems = list && list.children && list.children.length > 0;
    if (list) list.hidden = !hasItems;
    if (empty) empty.hidden = !!hasItems;
  }
  function getActionCommentId(btn) {
    return btn ? (btn.getAttribute('data-tb4ccp-target-comment') || btn.getAttribute('data-comment-id') || '') : '';
  }
  function dedupeCommentItems(root) {
    var seen = {};
    qsa(root || document, '.tb4c-comment-item[data-comment-id]').forEach(function (item) {
      var id = item.getAttribute('data-comment-id') || '';
      if (!id) return;
      if (seen[id]) item.remove();
      else seen[id] = true;
    });
    showListState(root || document);
  }
  function normalizeCommentActions(root) {
    root = root || document;
    qsa(root, '.tb4ccp-comment-action-row').forEach(function (row) {
      var item = closest(row, '.tb4c-comment-item');
      var isTopLevel = !!(item && item.classList && item.classList.contains('depth-0'));
      var copySeen = false;
      var replySeen = false;
      qsa(row, '.tb4ccp-copy-action').forEach(function (btn) {
        if (copySeen) btn.remove(); else copySeen = true;
      });
      qsa(row, '.tb4ccp-reply-action').forEach(function (btn) {
        if (!isTopLevel || replySeen) btn.remove(); else replySeen = true;
      });
    });
    qsa(root, '[data-tb4ccp-owner-tools]').forEach(function (row) {
      var editSeen = false;
      var deleteSeen = false;
      qsa(row, '.tb4ccp-safe-owner-edit,[data-tb4ccp-comment-action="edit"]').forEach(function (btn) {
        if (editSeen) btn.remove();
        else { editSeen = true; btn.setAttribute('aria-label', 'แก้ไขความคิดเห็น'); btn.setAttribute('title', 'แก้ไข'); }
      });
      qsa(row, '.tb4ccp-safe-owner-delete,[data-tb4ccp-comment-action="delete"]').forEach(function (btn) {
        if (deleteSeen) btn.remove();
        else { deleteSeen = true; btn.setAttribute('aria-label', 'ลบความคิดเห็น'); btn.setAttribute('title', 'ลบ'); }
      });
      row.hidden = false;
      row.removeAttribute('aria-hidden');
      row.style.display = 'flex';
      row.style.visibility = 'visible';
      row.style.opacity = '1';
      qsa(row, '.tb4ccp-safe-owner-btn').forEach(function (btn) {
        btn.hidden = false;
        btn.removeAttribute('aria-hidden');
        btn.style.display = 'grid';
        btn.style.visibility = 'visible';
        btn.style.opacity = '1';
      });
    });
  }
  function commentStatus(form) { return qs(form, '[data-tb4ccp-comment-status]'); }
  function updateTextareaHeight(textarea) {
    if (!textarea) return;
    textarea.style.height = '38px';
    textarea.style.height = Math.min(140, Math.max(38, textarea.scrollHeight)) + 'px';
  }
  function moveToForm(form) {
    if (form && form.scrollIntoView) form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    var textarea = qs(form, '[data-tb4ccp-comment-textarea]');
    if (textarea && textarea.focus) textarea.focus();
  }
  function setSending(form, isSending) {
    var submit = qs(form, 'button[type="submit"]');
    if (!submit) return;
    submit.disabled = !!isSending;
    submit.classList.toggle('is-sending', !!isSending);
    submit.setAttribute('aria-busy', isSending ? 'true' : 'false');
    submit.setAttribute('aria-label', isSending ? 'กำลังส่งความคิดเห็น' : 'ส่งความคิดเห็น');
  }
  function insertCommentHtml(root, html, commentId, parentId) {
    if (!html) return;
    if (commentId && qs(root, '.tb4c-comment-item[data-comment-id="' + commentId + '"]')) return;
    if (parentId) {
      var replyBox = qs(root, '[data-tb4c-replies-for="' + parentId + '"]');
      if (replyBox) replyBox.insertAdjacentHTML('beforeend', html);
      return;
    }
    var list = qs(root, '[data-tb4c-comment-list]');
    if (list) list.insertAdjacentHTML('afterbegin', html);
  }

  function ensureMediaViewer() {
    var existing = document.getElementById('tb4ccpMediaViewer');
    if (existing) return existing;
    var viewer = document.createElement('div');
    viewer.id = 'tb4ccpMediaViewer';
    viewer.className = 'tb4ccp-media-viewer';
    viewer.setAttribute('hidden', 'hidden');
    viewer.setAttribute('aria-hidden', 'true');
    viewer.innerHTML = '<div class="tb4ccp-media-backdrop" data-tb4ccp-media-close></div><div class="tb4ccp-media-dialog" role="dialog" aria-modal="true" aria-label="ดูรูปภาพ"><button type="button" class="tb4ccp-media-close" data-tb4ccp-media-close aria-label="ปิด">×</button><div class="tb4ccp-media-stage" data-tb4ccp-media-stage></div><p class="tb4ccp-media-caption" data-tb4ccp-media-caption></p></div>';
    document.body.appendChild(viewer);
    return viewer;
  }
  function openMediaViewer(src, type, title) {
    if (!src) return;
    var viewer = ensureMediaViewer();
    var stage = qs(viewer, '[data-tb4ccp-media-stage]');
    var caption = qs(viewer, '[data-tb4ccp-media-caption]');
    if (!stage) return;
    type = type || (/\.(mp4|webm|mov|m4v)(\?|#|$)/i.test(src) ? 'video' : 'image');
    stage.innerHTML = '';
    if (type === 'video') {
      var video = document.createElement('video');
      video.controls = true;
      video.playsInline = true;
      video.autoplay = true;
      video.src = src;
      stage.appendChild(video);
    } else {
      var img = document.createElement('img');
      img.src = src;
      img.alt = title || '';
      img.decoding = 'async';
      stage.appendChild(img);
    }
    if (caption) {
      caption.textContent = title || '';
      caption.hidden = !title;
    }
    viewer.hidden = false;
    viewer.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('tb4ccp-media-viewer-open');
    var closeBtn = qs(viewer, '[data-tb4ccp-media-close].tb4ccp-media-close');
    if (closeBtn && closeBtn.focus) window.setTimeout(function () { closeBtn.focus(); }, 20);
  }
  function closeMediaViewer() {
    var viewer = document.getElementById('tb4ccpMediaViewer');
    if (!viewer) return;
    var stage = qs(viewer, '[data-tb4ccp-media-stage]');
    if (stage) stage.innerHTML = '';
    viewer.hidden = true;
    viewer.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('tb4ccp-media-viewer-open');
  }
  function getMediaSource(trigger, eventTarget) {
    if (!trigger) return null;
    var img = (eventTarget && eventTarget.tagName && eventTarget.tagName.toLowerCase() === 'img') ? eventTarget : qs(trigger, 'img');
    var video = (eventTarget && eventTarget.tagName && eventTarget.tagName.toLowerCase() === 'video') ? eventTarget : qs(trigger, 'video');
    var src = trigger.getAttribute('data-tb4ccp-media-src') || trigger.getAttribute('data-tb4c-lightbox-src') || trigger.getAttribute('href') || '';
    if (!src && img) src = img.currentSrc || img.src || '';
    if (!src && video) src = video.currentSrc || video.src || '';
    var type = trigger.getAttribute('data-tb4ccp-media-type') || trigger.getAttribute('data-tb4c-lightbox-type') || (video ? 'video' : 'image');
    var title = trigger.getAttribute('data-tb4ccp-media-title') || trigger.getAttribute('data-tb4c-lightbox-title') || (img ? img.alt : '') || '';
    return src ? { src: src, type: type, title: title } : null;
  }

  var tb4ccpSupportsPassive = false;
  try {
    var opts = Object.defineProperty({}, 'passive', { get: function () { tb4ccpSupportsPassive = true; return true; } });
    window.addEventListener('tb4ccpPassiveTest', null, opts);
    window.removeEventListener('tb4ccpPassiveTest', null, opts);
  } catch (e) {}
  function mediaListenerOptions(passive) {
    return tb4ccpSupportsPassive ? { capture: true, passive: !!passive } : true;
  }
  document.addEventListener('pointerdown', rememberMediaDown, true);
  document.addEventListener('pointermove', rememberMediaMove, true);
  document.addEventListener('pointerup', forgetMediaDown, true);
  document.addEventListener('pointercancel', forgetMediaDown, true);
  document.addEventListener('touchstart', rememberMediaDown, mediaListenerOptions(true));
  document.addEventListener('touchmove', rememberMediaMove, mediaListenerOptions(true));
  document.addEventListener('touchend', forgetMediaDown, mediaListenerOptions(true));
  document.addEventListener('touchcancel', forgetMediaDown, mediaListenerOptions(true));

  document.addEventListener('click', function (event) {
    if (closest(event.target, '[data-tb4ccp-media-close]')) {
      lockEvent(event);
      closeMediaViewer();
      return;
    }
    if (closest(event.target, 'video')) return;
    var trigger = mediaTarget(event.target);
    if (!trigger) return;
    if (!closest(trigger, '#tb4c-sp268-root')) return;
    if (!mediaClickShouldOpen()) { lockEvent(event); return; }
    var media = getMediaSource(trigger, event.target);
    if (!media || !media.src) return;
    lockEvent(event);
    openMediaViewer(media.src, media.type, media.title);
  }, true);

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && document.documentElement.classList.contains('tb4ccp-media-viewer-open')) {
      lockEvent(event);
      closeMediaViewer();
    }
  }, true);

  document.addEventListener('DOMContentLoaded', function () {
    dedupeCommentItems(document);
    normalizeCommentActions(document);
    qsa(document, '[data-tb4ccp-comment-textarea]').forEach(updateTextareaHeight);
    markMediaImagesSafe(document);
  });

  document.addEventListener('input', function (event) {
    if (event.target && event.target.matches && event.target.matches('[data-tb4ccp-comment-textarea]')) updateTextareaHeight(event.target);
  }, true);


  document.addEventListener('keydown', function (event) {
    var textarea = event.target && event.target.matches && event.target.matches('[data-tb4ccp-comment-textarea]') ? event.target : null;
    if (!textarea) return;
    if (event.key !== 'Enter' || event.shiftKey || event.ctrlKey || event.altKey || event.metaKey || event.isComposing || event.keyCode === 229) return;
    var form = closest(textarea, '[data-tb4ccp-comment-form]');
    if (!form || form.getAttribute('data-tb4ccp-submitting') === '1') return;
    if (!textarea.value || !textarea.value.trim()) return;
    lockEvent(event);
    if (typeof form.requestSubmit === 'function') {
      form.requestSubmit();
      return;
    }
    var submitButton = qs(form, 'button[type="submit"]');
    if (submitButton && submitButton.click) submitButton.click();
  }, true);

  document.addEventListener('submit', function (event) {
    var form = closest(event.target, '[data-tb4ccp-comment-form]');
    if (!form) return;
    lockEvent(event);
    if (form.getAttribute('data-tb4ccp-submitting') === '1') return;
    var root = closest(form, '#tb4c-sp268-root') || document;
    var textarea = qs(form, '[data-tb4ccp-comment-textarea]');
    var status = commentStatus(form);
    var content = textarea ? textarea.value.trim() : '';
    if (!content) { setText(status, 'กรุณาเขียนความคิดเห็นก่อนส่ง', false); return; }
    form.setAttribute('data-tb4ccp-submitting', '1');
    var fd = new FormData(form);
    fd.set('nonce', cfg.nonce || fd.get('nonce') || '');
    setText(status, 'กำลังส่งความคิดเห็น...', null);
    setSending(form, true);
    ajax(fd).then(function (json) {
      form.removeAttribute('data-tb4ccp-submitting');
      setSending(form, false);
      if (!json || !json.success) {
        setText(status, (json && json.data && json.data.message) || 'ส่งความคิดเห็นไม่สำเร็จ', false);
        return;
      }
      var data = json.data || {};
      if (textarea) { textarea.value = ''; updateTextareaHeight(textarea); }
      var parentId = parseInt(data.parent_id || fd.get('parent_id') || 0, 10) || 0;
      insertCommentHtml(root, data.html || '', String(data.comment_id || ''), parentId);
      markMediaImagesSafe(root);
      dedupeCommentItems(root);
      normalizeCommentActions(root);
      setCount(root, data.count || 0);
      showListState(root);
      var parentInput = qs(form, '[data-tb4ccp-comment-parent]');
      if (parentInput) parentInput.value = '0';
      var replyContext = qs(form, '[data-tb4ccp-reply-context]');
      if (replyContext) replyContext.hidden = true;
      setText(status, data.message || 'ส่งความคิดเห็นแล้ว', true);
      window.setTimeout(function () { setText(status, '', null); }, 1400);
    }).catch(function () {
      form.removeAttribute('data-tb4ccp-submitting');
      setSending(form, false);
      setText(status, 'ส่งความคิดเห็นไม่สำเร็จ กรุณาลองใหม่', false);
    });
  }, true);

  document.addEventListener('click', function (event) {
    var copy = closest(event.target, '[data-tb4ccp-copy-link]');
    if (copy) {
      lockEvent(event);
      var link = copy.getAttribute('data-tb4ccp-copy-link') || copy.getAttribute('href') || window.location.href;
      if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(link);
      copy.classList.add('is-copied');
      window.setTimeout(function () { copy.classList.remove('is-copied'); }, 800);
      return;
    }
    var deletePost = closest(event.target, '[data-tb4ccp-delete-post]');
    if (deletePost) {
      lockEvent(event);
      if (deletePost.getAttribute('data-tb4ccp-busy') === '1') return;
      if (!window.confirm('ยืนยันลบโพสต์นี้?')) return;
      var fdPost = new FormData();
      fdPost.append('action', 'tb4c_delete_post');
      fdPost.append('nonce', cfg.nonce || '');
      fdPost.append('post_id', deletePost.getAttribute('data-tb4ccp-delete-post') || '0');
      deletePost.setAttribute('data-tb4ccp-busy', '1');
      deletePost.disabled = true;
      ajax(fdPost).then(function (json) {
        if (!json || !json.success) {
          deletePost.removeAttribute('data-tb4ccp-busy');
          deletePost.disabled = false;
          window.alert((json && json.data && json.data.message) || 'ลบโพสต์ไม่สำเร็จ');
          return;
        }
        window.location.href = (json.data && json.data.url) || (cfg.communityUrl || '/community/');
      }).catch(function () { deletePost.removeAttribute('data-tb4ccp-busy'); deletePost.disabled = false; window.alert('ลบโพสต์ไม่สำเร็จ'); });
      return;
    }
    var btn = closest(event.target, '[data-tb4ccp-comment-action]');
    if (!btn) return;
    lockEvent(event);
    var root = closest(btn, '#tb4c-sp268-root') || document;
    var form = qs(root, '[data-tb4ccp-comment-form]');
    var action = btn.getAttribute('data-tb4ccp-comment-action');
    var commentId = getActionCommentId(btn);
    if (action === 'reply') {
      if (!form) return;
      var parentInput = qs(form, '[data-tb4ccp-comment-parent]');
      var replyContext = qs(form, '[data-tb4ccp-reply-context]');
      var replyLabel = qs(form, '[data-tb4ccp-reply-label]');
      if (parentInput) parentInput.value = commentId;
      if (replyContext) replyContext.hidden = false;
      if (replyLabel) replyLabel.textContent = 'กำลังตอบกลับ ' + (btn.getAttribute('data-comment-author') || 'ความคิดเห็น');
      moveToForm(form);
      return;
    }
    if (action === 'cancel-reply') {
      if (!form) return;
      var parentInputCancel = qs(form, '[data-tb4ccp-comment-parent]');
      var replyContextCancel = qs(form, '[data-tb4ccp-reply-context]');
      if (parentInputCancel) parentInputCancel.value = '0';
      if (replyContextCancel) replyContextCancel.hidden = true;
      return;
    }
    if (action === 'edit') {
      var item = closest(btn, '.tb4c-comment-item[data-comment-id]');
      var textEl = qs(item, '[data-tb4c-comment-text]');
      var bubble = qs(item, '[data-tb4c-comment-bubble]');
      if (!item || !textEl || !bubble) return;
      var existing = qs(item, '[data-tb4ccp-comment-editbox]');
      if (existing) { existing.hidden = false; var tx0 = qs(existing, 'textarea'); if (tx0) tx0.focus(); return; }
      var raw = textEl.getAttribute('data-raw') || textEl.textContent || '';
      var box = document.createElement('div');
      box.className = 'tb4ccp-comment-editbox';
      box.setAttribute('data-tb4ccp-comment-editbox', '1');
      box.innerHTML = '<textarea rows="3" maxlength="1200">' + escapeHtml(raw) + '</textarea><div class="tb4ccp-comment-edit-actions"><button type="button" class="tb4ccp-save-comment">✓ บันทึก</button><button type="button" class="tb4ccp-cancel-comment">× ยกเลิก</button></div><p class="tb4ccp-comment-edit-status" aria-live="polite"></p>';
      bubble.appendChild(box);
      var tx = qs(box, 'textarea'); if (tx) tx.focus();
      return;
    }
    if (action === 'delete') {
      if (btn.getAttribute('data-tb4ccp-busy') === '1') return;
      if (!window.confirm('ยืนยันลบความคิดเห็นนี้?')) return;
      var itemDel = closest(btn, '.tb4c-comment-item[data-comment-id]');
      var fdDel = new FormData();
      fdDel.append('action', 'tb4c_delete_comment');
      fdDel.append('nonce', cfg.nonce || '');
      fdDel.append('comment_id', commentId);
      btn.setAttribute('data-tb4ccp-busy', '1');
      btn.disabled = true;
      ajax(fdDel).then(function (json) {
        if (!json || !json.success) {
          btn.removeAttribute('data-tb4ccp-busy');
          btn.disabled = false;
          window.alert((json && json.data && json.data.message) || 'ลบความคิดเห็นไม่สำเร็จ');
          return;
        }
        if (itemDel) itemDel.remove();
        setCount(root, json.data && json.data.count);
        showListState(root);
      }).catch(function () { btn.removeAttribute('data-tb4ccp-busy'); btn.disabled = false; window.alert('ลบความคิดเห็นไม่สำเร็จ'); });
    }
  }, true);

  document.addEventListener('click', function (event) {
    var cancel = closest(event.target, '.tb4ccp-cancel-comment');
    if (cancel) {
      lockEvent(event);
      var box = closest(cancel, '[data-tb4ccp-comment-editbox]');
      if (box) box.hidden = true;
      return;
    }
    var save = closest(event.target, '.tb4ccp-save-comment');
    if (!save) return;
    lockEvent(event);
    if (save.getAttribute('data-tb4ccp-busy') === '1') return;
    var item = closest(save, '.tb4c-comment-item[data-comment-id]');
    var box = closest(save, '[data-tb4ccp-comment-editbox]');
    var textarea = qs(box, 'textarea');
    var status = qs(box, '.tb4ccp-comment-edit-status');
    var content = textarea ? textarea.value.trim() : '';
    if (!item || !content) { setText(status, 'ความคิดเห็นต้องไม่ว่าง', false); return; }
    var fd = new FormData();
    fd.append('action', 'tb4c_update_comment');
    fd.append('nonce', cfg.nonce || '');
    fd.append('comment_id', item.getAttribute('data-comment-id') || '0');
    fd.append('content', content);
    save.setAttribute('data-tb4ccp-busy', '1');
    save.disabled = true;
    setText(status, 'กำลังบันทึก...', null);
    ajax(fd).then(function (json) {
      save.removeAttribute('data-tb4ccp-busy');
      save.disabled = false;
      if (!json || !json.success) {
        setText(status, (json && json.data && json.data.message) || 'บันทึกไม่สำเร็จ', false);
        return;
      }
      var data = json.data || {};
      var textEl = qs(item, '[data-tb4c-comment-text]');
      if (textEl) {
        textEl.setAttribute('data-raw', data.content || content);
        textEl.innerHTML = data.content_html || ('<p>' + escapeHtml(content) + '</p>');
      }
      var mediaOld = qs(item, '.tb4c-comment-media-slot');
      if (mediaOld) mediaOld.remove();
      if (data.media_html) {
        var bubble = qs(item, '[data-tb4c-comment-bubble]');
        if (bubble) { bubble.insertAdjacentHTML('beforeend', data.media_html); markMediaImagesSafe(item); }
      }
      setText(status, data.message || 'บันทึกแล้ว', true);
      window.setTimeout(function () { if (box) box.hidden = true; }, 500);
    }).catch(function () {
      save.removeAttribute('data-tb4ccp-busy');
      save.disabled = false;
      setText(status, 'บันทึกไม่สำเร็จ', false);
    });
  }, true);
})(window, document);
