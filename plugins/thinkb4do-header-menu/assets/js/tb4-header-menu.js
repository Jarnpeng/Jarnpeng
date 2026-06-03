(function(){
  'use strict';
  var doc = document;
  var body = doc.body;

  function byId(id){ return doc.getElementById(id); }
  function isOpen(el){ return el && !el.hasAttribute('hidden'); }
  function openPanel(panel, trigger){
    if(!panel) return;
    syncHeaderAttachedPanels();
    panel.removeAttribute('hidden');
    panel.setAttribute('aria-hidden','false');
    panel.classList.add('is-open');
    panel.style.setProperty('display','block','important');
    panel.style.setProperty('visibility','visible','important');
    panel.style.setProperty('opacity','1','important');
    panel.style.setProperty('pointer-events','auto','important');
    if(trigger){
      trigger.setAttribute('aria-expanded','true');
      trigger.classList.add('is-open');
    }
  }
  function closePanel(panel, trigger){
    if(!panel) return;
    panel.setAttribute('hidden','hidden');
    panel.setAttribute('aria-hidden','true');
    panel.classList.remove('is-open');
    panel.style.setProperty('display','none','important');
    panel.style.removeProperty('visibility');
    panel.style.removeProperty('opacity');
    panel.style.removeProperty('pointer-events');
    if(trigger){
      trigger.setAttribute('aria-expanded','false');
      trigger.classList.remove('is-open');
    }
  }
  function togglePanel(panel, trigger){
    if(isOpen(panel)){ closePanel(panel, trigger); }
    else { closeAll(panel); openPanel(panel, trigger); }
  }
  function closeAll(except){
    var pairs = [
      ['tb4NotificationPanel','tb4NotificationTrigger'],
      ['tb4MessagePanel','tb4MessageTrigger'],
      ['userMenuDropdown','userMenuTrigger'],
      ['tb4AppGridPanel','tb4AppGridTrigger'],
      ['tb4MobileHeaderSearchPanel','tb4MobileSearchTrigger'],
      ['mobileMenuOverlay','mobileMenuToggle']
    ];
    pairs.forEach(function(pair){
      var p = byId(pair[0]);
      if(p && p !== except){
        closePanel(p, byId(pair[1]));
        if(pair[0] === 'mobileMenuOverlay') body.classList.remove('tb4hm-lock-scroll');
      }
    });
    var mobileBtn = byId('mobileMenuToggle');
    if(mobileBtn && byId('mobileMenuOverlay') !== except) mobileBtn.classList.remove('is-open');
    var results = byId('searchResults');
    if(results && results !== except){ results.setAttribute('hidden','hidden'); }
  }



  function getViewportSize(){
    var vv = window.visualViewport;
    return {
      width: Math.max(320, Math.floor(vv && vv.width ? vv.width : window.innerWidth || doc.documentElement.clientWidth || 0)),
      height: Math.max(320, Math.floor(vv && vv.height ? vv.height : window.innerHeight || doc.documentElement.clientHeight || 0))
    };
  }

  function syncAppGridPanelPosition(){
    var panel = byId('tb4AppGridPanel');
    var trigger = byId('tb4AppGridTrigger') || doc.querySelector('.tb4-app-grid-trigger');
    if(!panel || !trigger || !doc.documentElement) return;

    var viewport = getViewportSize();
    var rect = trigger.getBoundingClientRect();
    if(!rect || !isFinite(rect.right)) return;

    var sideGap = viewport.width <= 520 ? 10 : 14;
    var preferredWidth = viewport.width <= 520 ? viewport.width - (sideGap * 2) : Math.min(420, viewport.width - (sideGap * 2));
    var width = Math.max(280, Math.floor(preferredWidth));

    // Desktop: align the panel's right edge with the 9-dot trigger, then clamp inside the viewport.
    var right = Math.round(viewport.width - rect.right);
    if(!isFinite(right)) right = sideGap;
    right = Math.max(sideGap, right);

    var left = viewport.width - right - width;
    if(left < sideGap){
      right = Math.max(sideGap, viewport.width - sideGap - width);
      left = viewport.width - right - width;
    }

    doc.documentElement.style.setProperty('--tb4hm-appgrid-right-px', Math.round(right) + 'px');
    doc.documentElement.style.setProperty('--tb4hm-appgrid-width-px', Math.round(width) + 'px');
  }

  function syncHeaderAttachedPanels(){
    var header = byId('site-header');
    if(!header || !doc.documentElement) return;
    var rect = header.getBoundingClientRect();
    if(!rect || !isFinite(rect.bottom)) return;
    var bottom = Math.max(0, Math.ceil(rect.bottom));
    doc.documentElement.style.setProperty('--tb4hm-header-bottom-px', bottom + 'px');
    syncAppGridPanelPosition();
  }

  function bindToggle(triggerId, panelId){
    var trigger = byId(triggerId);
    var panel = byId(panelId);
    if(!trigger || !panel || trigger.dataset.tb4hmBound === '1') return;
    trigger.dataset.tb4hmBound = '1';

    function activate(e){
      if(e){
        e.preventDefault();
        e.stopPropagation();
      }
      trigger.dataset.tb4hmLastTouch = String(Date.now());
      togglePanel(panel, trigger);
      if(panelId === 'tb4MobileHeaderSearchPanel'){
        setTimeout(function(){ var input = byId('tb4MobileHeaderSearchInput'); if(input) input.focus(); }, 30);
      }
    }

    // มือถือบางรุ่นแตะครั้งแรกเป็น hover/focus ทำให้ panel ไม่ขึ้น จึงเปิดตั้งแต่ pointerdown/touchstart
    if(window.PointerEvent){
      trigger.addEventListener('pointerdown', function(e){
        if(e.pointerType === 'touch' || e.pointerType === 'pen'){ activate(e); }
      }, {passive:false});
    }else{
      trigger.addEventListener('touchstart', activate, {passive:false});
    }

    trigger.addEventListener('click', function(e){
      var lastTouch = Number(trigger.dataset.tb4hmLastTouch || 0);
      if(lastTouch && Date.now() - lastTouch < 700){
        e.preventDefault();
        e.stopPropagation();
        return;
      }
      activate(e);
    });
  }


  function initAppGridToggle(){
    var panel = byId('tb4AppGridPanel');
    if(!panel) return;
    var triggers = doc.querySelectorAll('#tb4AppGridTrigger, .tb4-app-grid-trigger');
    triggers.forEach(function(trigger){
      if(!trigger || trigger.dataset.tb4hmAppGridBound === '1') return;
      trigger.dataset.tb4hmAppGridBound = '1';

      function stopEvent(e){
        if(!e) return;
        e.preventDefault();
        e.stopPropagation();
        if(typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
      }

      function lockVisual(){
        trigger.classList.add('tb4hm-appgrid-static-lock');
        trigger.style.setProperty('transform','none','important');
        trigger.style.setProperty('transition','none','important');
      }

      function activate(e){
        stopEvent(e);
        lockVisual();
        syncHeaderAttachedPanels();
        syncAppGridPanelPosition();
        var now = Date.now();
        var last = Number(trigger.dataset.tb4hmAppGridLastActivate || 0);
        if(last && now - last < 360) return;
        trigger.dataset.tb4hmAppGridLastActivate = String(now);
        togglePanel(panel, trigger);
      }

      // ใช้ pointerup/touchend เพื่อลดอาการปุ่มกระตุก และกัน ghost click ที่ทำให้เปิดแล้วปิดทันที
      if(window.PointerEvent){
        trigger.addEventListener('pointerup', function(e){
          if(e.pointerType === 'touch' || e.pointerType === 'pen') activate(e);
        }, {passive:false, capture:true});
      }
      trigger.addEventListener('touchend', activate, {passive:false, capture:true});
      trigger.addEventListener('click', function(e){
        var last = Number(trigger.dataset.tb4hmAppGridLastActivate || 0);
        if(last && Date.now() - last < 520){
          stopEvent(e);
          return;
        }
        activate(e);
      }, true);
      trigger.addEventListener('keydown', function(e){
        if(e.key === 'Enter' || e.key === ' '){ activate(e); }
      });
    });
  }

  function initExclusiveHeaderMenus(){
    if(doc.documentElement && doc.documentElement.dataset.tb4hmExclusiveMenus === '1') return;
    if(doc.documentElement) doc.documentElement.dataset.tb4hmExclusiveMenus = '1';

    function closeAppGridForOtherTrigger(e){
      var target = e.target;
      if(!target || !target.closest) return;
      var trigger = target.closest('#tb4NotificationTrigger,#tb4MessageTrigger,#userMenuTrigger,#tb4MobileSearchTrigger,#mobileMenuToggle,.lang-btn,#site-header .btn');
      if(!trigger) return;
      if(trigger.id === 'tb4AppGridTrigger' || trigger.closest('.tb4-app-grid-trigger')) return;
      closePanel(byId('tb4AppGridPanel'), byId('tb4AppGridTrigger'));
    }

    // เมื่อแตะปุ่มเมนูอื่น ให้ปิดเมนู 9 ช่องทันที เพื่อไม่ค้าง/ซ้อนกับปุ่มอื่นบนมือถือ
    doc.addEventListener('pointerdown', closeAppGridForOtherTrigger, true);
    doc.addEventListener('click', closeAppGridForOtherTrigger, true);
  }

  function initMobileMenu(){
    var btn = byId('mobileMenuToggle');
    var panel = byId('mobileMenuOverlay');
    if(!btn || !panel || btn.dataset.tb4hmBound === '1') return;
    btn.dataset.tb4hmBound = '1';
    btn.addEventListener('click', function(e){
      e.preventDefault(); e.stopPropagation();
      if(isOpen(panel)){
        closePanel(panel, btn);
        btn.classList.remove('is-open');
        body.classList.remove('tb4hm-lock-scroll');
      }else{
        closeAll(panel);
        openPanel(panel, btn);
        btn.classList.add('is-open');
        body.classList.add('tb4hm-lock-scroll');
      }
    });
  }

  function initCloseButtons(){
    var appClose = byId('tb4AppGridClose');
    if(appClose && appClose.dataset.tb4hmBound !== '1'){
      appClose.dataset.tb4hmBound = '1';
      appClose.addEventListener('click', function(){ closePanel(byId('tb4AppGridPanel'), byId('tb4AppGridTrigger')); });
    }
    doc.querySelectorAll('.tb4-mobile-header-search-close').forEach(function(btn){
      if(btn.dataset.tb4hmBound === '1') return;
      btn.dataset.tb4hmBound = '1';
      btn.addEventListener('click', function(){ closePanel(byId('tb4MobileHeaderSearchPanel'), byId('tb4MobileSearchTrigger')); });
    });
  }

  function initKeyboard(){
    doc.addEventListener('keydown', function(e){
      if(e.key === 'Escape'){
        closeAll();
        var mobile = byId('mobileMenuOverlay');
        var mobileBtn = byId('mobileMenuToggle');
        closePanel(mobile, mobileBtn);
        if(mobileBtn) mobileBtn.classList.remove('is-open');
        body.classList.remove('tb4hm-lock-scroll');
      }
      var isCtrlK = (e.ctrlKey || e.metaKey) && String(e.key).toLowerCase() === 'k';
      if(isCtrlK){
        var input = byId('headerSearchInput') || byId('tb4MobileHeaderSearchInput');
        if(input){ e.preventDefault(); input.focus(); }
      }
    });
  }

  function initOutsideClose(){
    doc.addEventListener('click', function(e){
      var keep = e.target.closest('#site-header,#tb4AppGridPanel,#tb4MobileHeaderSearchPanel,#mobileMenuOverlay,#searchResults');
      if(!keep){ closeAll(); }
    });
  }



  function initHeaderAttachedPanelSync(){
    syncHeaderAttachedPanels();
    if(window.visualViewport){
      window.visualViewport.addEventListener('resize', syncHeaderAttachedPanels, {passive:true});
      window.visualViewport.addEventListener('scroll', syncHeaderAttachedPanels, {passive:true});
    }
    window.addEventListener('resize', syncHeaderAttachedPanels, {passive:true});
    window.addEventListener('orientationchange', function(){ setTimeout(syncHeaderAttachedPanels, 80); }, {passive:true});
    doc.addEventListener('scroll', syncHeaderAttachedPanels, {passive:true, capture:true});
  }

  function initSearchGuard(){
    doc.querySelectorAll('.tb4-header-search-form').forEach(function(form){
      if(form.dataset.tb4hmBound === '1') return;
      form.dataset.tb4hmBound = '1';
      form.addEventListener('submit', function(e){
        var input = form.querySelector('input[type="search"]');
        if(input && input.value.trim().length === 0){
          e.preventDefault();
          input.focus();
        }
      });
    });
  }

  function initAvatarLoadState(){
    doc.querySelectorAll('.tb4-user-avatar-img').forEach(function(img){
      var wrap = img.closest('.tb4-user-avatar');
      if(!wrap) return;
      function showFallback(){
        wrap.classList.remove('is-loaded');
        img.removeAttribute('src');
        img.setAttribute('aria-hidden','true');
      }
      function showImage(){
        if(img.naturalWidth > 1 && img.naturalHeight > 1){
          wrap.classList.add('is-loaded');
          img.removeAttribute('aria-hidden');
        }else{
          showFallback();
        }
      }
      if(img.complete){
        if(img.naturalWidth > 1){ showImage(); }
        else if(img.getAttribute('src')){ showFallback(); }
      }
      if(img.dataset.tb4hmAvatarBound === '1') return;
      img.dataset.tb4hmAvatarBound = '1';
      img.addEventListener('load', showImage);
      img.addEventListener('error', showFallback);
    });
  }

  function init(){
    if(doc.documentElement) doc.documentElement.classList.remove('no-js');
    initHeaderAttachedPanelSync();
    bindToggle('tb4NotificationTrigger','tb4NotificationPanel');
    bindToggle('tb4MessageTrigger','tb4MessagePanel');
    bindToggle('userMenuTrigger','userMenuDropdown');
    initAppGridToggle();
    initExclusiveHeaderMenus();
    bindToggle('tb4MobileSearchTrigger','tb4MobileHeaderSearchPanel');
    initMobileMenu();
    initCloseButtons();
    initSearchGuard();
    initAvatarLoadState();
    initKeyboard();
    initOutsideClose();
  }

  if(doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', init);
  else init();
})();
