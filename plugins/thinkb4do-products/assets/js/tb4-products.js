(function(){
    'use strict';
    function ready(fn){
        if(document.readyState==='loading'){
            document.addEventListener('DOMContentLoaded',fn,{once:true});
        }else{fn();}
    }
    function readStore(key){
        try{return JSON.parse(localStorage.getItem(key)||'[]');}
        catch(err){return [];}
    }
    function writeStore(key,items){
        try{localStorage.setItem(key,JSON.stringify(items));}
        catch(err){}
    }
    function unique(items){
        var map={};
        return items.filter(function(item){
            if(!item||!item.id||map[item.id]){return false;}
            map[item.id]=true;
            return true;
        });
    }
    function esc(str){
        return String(str||'').replace(/[&<>'"]/g,function(ch){
            return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[ch];
        });
    }
    ready(function(){
        document.querySelectorAll('[data-tb4-products-shell]').forEach(function(shell){
            var input=shell.querySelector('[data-tb4-product-search]');
            var grid=shell.querySelector('[data-tb4-products-grid]');
            var cards=[].slice.call(shell.querySelectorAll('.tb4-product-card'));
            var countNodes=[].slice.call(shell.querySelectorAll('[data-tb4-wishlist-count]'));
            var compareCountNodes=[].slice.call(shell.querySelectorAll('[data-tb4-compare-count]'));
            var savedList=shell.querySelector('[data-tb4-saved-list]');
            var compareMini=shell.querySelector('[data-tb4-compare-mini]');
            var compareDrawer=shell.querySelector('[data-tb4-compare-drawer]');
            var compareTable=shell.querySelector('[data-tb4-compare-table]');
            var saved=unique(readStore('tb4_products_saved'));
            var compared=unique(readStore('tb4_products_compare'));

            function syncSavedUI(){
                countNodes.forEach(function(node){node.textContent=String(saved.length);});
                shell.querySelectorAll('[data-tb4-wishlist]').forEach(function(btn){
                    var id=btn.getAttribute('data-tb4-wishlist');
                    var active=saved.some(function(item){return item.id===id;});
                    btn.classList.toggle('is-saved',active);
                    btn.textContent=active?'♥':'♡';
                });
                if(savedList){
                    if(!saved.length){
                        savedList.textContent='ยังไม่มีรายการที่บันทึก';
                    }else{
                        savedList.innerHTML=saved.slice(0,6).map(function(item){
                            return '<a href="'+ esc(item.url||'#') +'">'+ esc(item.title||'ผลิตภัณฑ์') +'</a>';
                        }).join('');
                    }
                }
            }

            function renderCompareTable(){
                if(compareMini){
                    if(!compared.length){
                        compareMini.textContent='ยังไม่มีสินค้าที่เลือกเปรียบเทียบ';
                    }else{
                        compareMini.innerHTML=compared.slice(0,4).map(function(item){
                            return '<a href="'+ esc(item.url||'#') +'">'+ esc(item.title||'ผลิตภัณฑ์') +'</a>';
                        }).join('');
                    }
                }
                if(compareTable){
                    if(!compared.length){
                        compareTable.innerHTML='<div class="tb4-products-empty"><h2>ยังไม่มีสินค้าในตารางเปรียบเทียบ</h2><p>กดปุ่ม ⇄ บนการ์ดสินค้าเพื่อเพิ่มเข้าตาราง</p></div>';
                    }else{
                        compareTable.innerHTML=compared.slice(0,4).map(function(item){
                            return '<article class="tb4-products-compare-item">'+
                                '<h4>'+esc(item.title)+'</h4>'+
                                '<p><span>ประเภท</span><strong>'+esc(item.type)+'</strong></p>'+
                                '<p><span>สถานะ</span><strong>'+esc(item.status)+'</strong></p>'+
                                '<p><span>คะแนน</span><strong>★ '+esc(item.rating)+'</strong></p>'+
                                '<p><span>License</span><strong>'+esc(item.license)+'</strong></p>'+
                                '<p><span>เหมาะสำหรับ</span><strong>'+esc(item.fit)+'</strong></p>'+
                                '<p><span>Trust</span><strong>'+esc(item.trust)+'%</strong></p>'+
                                '<p><span>ราคา/ข้อเสนอ</span><strong>'+esc(item.price)+'</strong></p>'+
                                '<a href="'+esc(item.url||'#')+'">ดูรายละเอียด</a>'+
                            '</article>';
                        }).join('');
                    }
                }
            }

            function syncCompareUI(openDrawer){
                compareCountNodes.forEach(function(node){node.textContent=String(compared.length);});
                shell.querySelectorAll('[data-tb4-compare]').forEach(function(btn){
                    var id=btn.getAttribute('data-tb4-compare');
                    var active=compared.some(function(item){return item.id===id;});
                    btn.classList.toggle('is-compared',active);
                    btn.textContent=active?'✓':'⇄';
                });
                renderCompareTable();
                if(openDrawer&&compareDrawer){compareDrawer.hidden=false;}
            }

            if(input){
                input.addEventListener('input',function(){
                    var q=(input.value||'').toLowerCase().trim();
                    cards.forEach(function(card){
                        var hay=(card.getAttribute('data-title')||'').toLowerCase();
                        card.hidden=!!q&&hay.indexOf(q)===-1;
                    });
                });
            }

            shell.addEventListener('click',function(e){
                var wish=e.target.closest('[data-tb4-wishlist]');
                if(wish){
                    e.preventDefault();
                    var card=wish.closest('.tb4-product-card');
                    var id=wish.getAttribute('data-tb4-wishlist');
                    var found=saved.findIndex(function(item){return item.id===id;});
                    if(found>=0){
                        saved.splice(found,1);
                    }else{
                        saved.push({
                            id:id,
                            title:card?card.getAttribute('data-saved-title'):'ผลิตภัณฑ์',
                            url:card?card.getAttribute('data-saved-url'):'#'
                        });
                    }
                    saved=unique(saved);
                    writeStore('tb4_products_saved',saved);
                    syncSavedUI();
                    return;
                }

                var compareBtn=e.target.closest('[data-tb4-compare]');
                if(compareBtn){
                    e.preventDefault();
                    var c=compareBtn.closest('.tb4-product-card');
                    var cid=compareBtn.getAttribute('data-tb4-compare');
                    var idx=compared.findIndex(function(item){return item.id===cid;});
                    if(idx>=0){
                        compared.splice(idx,1);
                    }else if(c){
                        compared.push({
                            id:cid,
                            title:c.getAttribute('data-compare-title')||'ผลิตภัณฑ์',
                            url:c.getAttribute('data-compare-url')||'#',
                            type:c.getAttribute('data-compare-type')||'-',
                            status:c.getAttribute('data-compare-status')||'-',
                            rating:c.getAttribute('data-compare-rating')||'-',
                            license:c.getAttribute('data-compare-license')||'-',
                            fit:c.getAttribute('data-compare-fit')||'-',
                            trust:c.getAttribute('data-compare-trust')||'-',
                            price:c.getAttribute('data-compare-price')||'ยังไม่แสดงราคา'
                        });
                    }
                    compared=unique(compared).slice(0,4);
                    writeStore('tb4_products_compare',compared);
                    syncCompareUI(true);
                    return;
                }

                var viewBtn=e.target.closest('[data-tb4-view]');
                if(viewBtn&&grid){
                    e.preventDefault();
                    var view=viewBtn.getAttribute('data-tb4-view');
                    grid.classList.toggle('is-list',view==='list');
                    shell.querySelectorAll('[data-tb4-view]').forEach(function(btn){btn.classList.remove('is-active');});
                    viewBtn.classList.add('is-active');
                    try{localStorage.setItem('tb4_products_view',view);}catch(err){}
                    return;
                }

                var scrollSaved=e.target.closest('[data-tb4-scroll-saved]');
                if(scrollSaved){
                    e.preventDefault();
                    var panel=shell.querySelector('#tb4-products-saved-panel');
                    if(panel){panel.scrollIntoView({behavior:'smooth',block:'center'});}
                    return;
                }

                var scrollCompare=e.target.closest('[data-tb4-scroll-compare]');
                if(scrollCompare){
                    e.preventDefault();
                    var comparePanel=shell.querySelector('#tb4-products-compare-panel');
                    if(comparePanel){comparePanel.scrollIntoView({behavior:'smooth',block:'center'});}
                    if(compareDrawer){compareDrawer.hidden=false;}
                    return;
                }

                if(e.target.closest('[data-tb4-compare-close]')){
                    e.preventDefault();
                    if(compareDrawer){compareDrawer.hidden=true;}
                    return;
                }

                if(e.target.closest('[data-tb4-compare-clear]')){
                    e.preventDefault();
                    compared=[];
                    writeStore('tb4_products_compare',compared);
                    syncCompareUI(false);
                    return;
                }

                var detailCard=e.target.closest('.tb4-product-card[data-tb4-detail-url]');
                if(detailCard && !e.target.closest('a,button,input,select,textarea,label,[role="button"],[data-tb4-no-card-click]')){
                    var detailUrl=detailCard.getAttribute('data-tb4-detail-url')||'';
                    if(detailUrl && detailUrl !== '#'){
                        detailCard.classList.add('is-card-clicked');
                        window.location.href=detailUrl;
                    }
                    return;
                }
            });

            shell.addEventListener('keydown',function(e){
                var activeCard=e.target.closest ? e.target.closest('.tb4-product-card[data-tb4-detail-url]') : null;
                if(activeCard && e.target===activeCard && (e.key==='Enter' || e.key===' ')){
                    e.preventDefault();
                    var detailUrl=activeCard.getAttribute('data-tb4-detail-url')||'';
                    if(detailUrl && detailUrl !== '#'){window.location.href=detailUrl;}
                }
            });

            document.addEventListener('keydown',function(e){
                if(e.key==='Escape'&&compareDrawer&&!compareDrawer.hidden){
                    compareDrawer.hidden=true;
                }
            });

            if(grid){
                try{
                    var view=localStorage.getItem('tb4_products_view')||'grid';
                    if(view==='list'){
                        grid.classList.add('is-list');
                        shell.querySelectorAll('[data-tb4-view]').forEach(function(btn){
                            btn.classList.toggle('is-active',btn.getAttribute('data-tb4-view')==='list');
                        });
                    }
                }catch(err){}
            }
            syncSavedUI();
            syncCompareUI(false);
        });
    });
})();


(function(){
    'use strict';
    function ready(fn){
        if(document.readyState==='loading'){
            document.addEventListener('DOMContentLoaded',fn,{once:true});
        }else{fn();}
    }
    ready(function(){
        document.querySelectorAll('[data-tb4-product-detail]').forEach(function(root){
            var modal=root.querySelector('[data-tb4-gallery-modal]');
            var modalImg=modal?modal.querySelector('img'):null;
            root.addEventListener('click',function(e){
                var copy=e.target.closest('[data-tb4-copy-link]');
                if(copy){
                    e.preventDefault();
                    var url=copy.getAttribute('data-tb4-copy-link')||window.location.href;
                    var done=function(){
                        var old=copy.textContent;
                        copy.textContent='คัดลอกแล้ว';
                        setTimeout(function(){copy.textContent=old;},1400);
                    };
                    if(navigator.clipboard&&navigator.clipboard.writeText){
                        navigator.clipboard.writeText(url).then(done).catch(done);
                    }else{
                        window.prompt('คัดลอกลิงก์นี้',url);
                        done();
                    }
                    return;
                }
                var gallery=e.target.closest('[data-tb4-gallery-open]');
                if(gallery&&modal&&modalImg){
                    e.preventDefault();
                    modalImg.src=gallery.getAttribute('data-tb4-gallery-open')||'';
                    modal.hidden=false;
                    return;
                }
                if(e.target.closest('[data-tb4-gallery-close]') || (modal && e.target===modal)){
                    e.preventDefault();
                    if(modal){modal.hidden=true;}
                    if(modalImg){modalImg.src='';}
                }
            });
            document.addEventListener('keydown',function(e){
                if(e.key==='Escape'&&modal&&!modal.hidden){
                    modal.hidden=true;
                    if(modalImg){modalImg.src='';}
                }
            });
        });
    });
})();

(function(){
  'use strict';
  function ready(fn){
    if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',fn,{once:true});}
    else{fn();}
  }
  function esc(str){
    return String(str||'').replace(/[&<>"']/g,function(ch){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch];});
  }
  function parseApp(card){
    if(!card){return null;}
    try{return JSON.parse(card.getAttribute('data-app')||'{}');}
    catch(err){return null;}
  }
  function setStatus(root,message,type){
    var node=root.querySelector('[data-tb4-app-order-status]');
    if(!node){return;}
    node.classList.remove('is-error','is-loading');
    if(type){node.classList.add(type);}
    node.textContent=message||'';
  }
  ready(function(){
    document.querySelectorAll('[data-tb4-app-store]').forEach(function(root){
      var currentApp=null;
      var win=root.querySelector('[data-tb4-app-window]');
      var title=root.querySelector('[data-tb4-app-window-title]');
      var heading=root.querySelector('[data-tb4-app-window-heading]');
      var desc=root.querySelector('[data-tb4-app-window-desc]');
      var status=root.querySelector('[data-tb4-app-window-status]');
      var icon=root.querySelector('[data-tb4-app-window-icon]');
      var note=root.querySelector('[data-tb4-app-note]');
      var detail=root.querySelector('[data-tb4-app-detail]');
      var preview=root.querySelector('[data-tb4-app-preview]');
      function openApp(id){
        var card=null;
        root.querySelectorAll('[data-tb4-app-card]').forEach(function(item){
          if(item.getAttribute('data-tb4-app-card')===String(id)){card=item;}
        });
        var app=parseApp(card);
        if(!app){return;}
        currentApp=app;
        root.querySelectorAll('[data-tb4-open-app]').forEach(function(btn){btn.classList.toggle('is-active',btn.getAttribute('data-tb4-open-app')===String(id));});
        if(title){title.textContent=app.title||'Thinkb4do App';}
        if(heading){heading.textContent=app.title||'Thinkb4do App';}
        if(desc){desc.textContent=app.subtitle||app.desc||'แอพเพิ่มประสิทธิภาพการใช้งาน';}
        if(status){status.textContent=app.statusLabel||'พร้อมใช้';}
        if(icon){
          icon.style.setProperty('--tb4-app-accent',app.accent||'#1E6B45');
          icon.innerHTML=app.image?'<img src="'+esc(app.image)+'" alt="">':esc(String(app.icon||'T').slice(0,3));
        }
        if(note){note.textContent=app.installedLabel||'พร้อมเปิดใช้งานในพื้นที่สมาชิก';}
        if(detail){detail.href=app.detailUrl||'#';}
        if(preview){
          if(app.runMode==='iframe' && app.runUrl && app.runUrl !== '#'){
            preview.innerHTML='<iframe title="'+esc(app.title||'App')+'" src="'+esc(app.runUrl)+'" loading="lazy" style="position:relative;width:100%;height:360px;border:0;border-radius:20px;background:#fff;"></iframe>';
          }else{
            preview.innerHTML='<div class="tb4-app-preview-screen"><strong>'+esc(app.title||'Live App Area')+'</strong><span>'+esc(app.subtitle||'คลิกปุ่มเปิดแอพเพื่อเริ่มทำงาน')+'</span></div>';
          }
        }
        if(win){win.scrollIntoView({behavior:'smooth',block:'nearest'});}
        setStatus(root,'','');
      }
      root.addEventListener('click',function(e){
        var opener=e.target.closest('[data-tb4-open-app]');
        if(opener){e.preventDefault();openApp(opener.getAttribute('data-tb4-open-app'));return;}
        var run=e.target.closest('[data-tb4-app-run]');
        if(run){
          e.preventDefault();
          if(!currentApp){return;}
          if(currentApp.runUrl && currentApp.runUrl !== '#'){
            if(currentApp.runMode==='iframe'){
              openApp(currentApp.id);
              setStatus(root,'เปิดหน้าตัวอย่างแอพในพื้นที่นี้แล้ว','');
            }else{
              window.open(currentApp.runUrl,'_blank','noopener');
              setStatus(root,'เปิดแอพ/หน้าติดตั้งในแท็บใหม่แล้ว','');
            }
          }else{
            setStatus(root,'แอพนี้ยังรอข้อมูลสำหรับเปิดใช้งาน','is-error');
          }
          return;
        }
        var checkout=e.target.closest('[data-tb4-app-checkout]');
        if(checkout){
          e.preventDefault();
          if(!currentApp){return;}
          if(!window.TB4ProductsApp || !TB4ProductsApp.isLoggedIn){
            setStatus(root,'กรุณาเข้าสู่ระบบก่อนซื้อหรือเปิดใช้งานแอพ','is-error');
            if(window.TB4ProductsApp && TB4ProductsApp.loginUrl){window.location.href=TB4ProductsApp.loginUrl;}
            return;
          }
          if(!currentApp.id || String(currentApp.id).indexOf('demo-')===0){
            setStatus(root,'รายการตัวอย่างยังไม่เปิดขายจริง ให้เพิ่มสินค้าในระบบก่อน','is-error');
            return;
          }
          setStatus(root,'กำลังสร้างรายการและเตรียมส่งบิลทางอีเมล','is-loading');
          var body=new FormData();
          body.append('action','tb4_products_app_order');
          body.append('nonce',TB4ProductsApp.nonce||'');
          body.append('productId',currentApp.id);
          fetch(TB4ProductsApp.ajaxUrl,{method:'POST',credentials:'same-origin',body:body})
            .then(function(res){return res.json();})
            .then(function(json){
              if(!json || !json.success){throw new Error((json&&json.data&&json.data.message)||'สร้างรายการไม่สำเร็จ');}
              setStatus(root,(json.data&&json.data.message)||'สร้างรายการสำเร็จ','');
              if(json.data && json.data.checkoutUrl){window.open(json.data.checkoutUrl,'_blank','noopener');}
              else if(json.data && json.data.autoInstall && json.data.runUrl){window.open(json.data.runUrl,'_blank','noopener');}
            })
            .catch(function(err){setStatus(root,err.message||'สร้างรายการไม่สำเร็จ','is-error');});
        }
      });
      var first=root.querySelector('[data-tb4-app-card]');
      if(first){currentApp=parseApp(first);}
    });
  });
})();
