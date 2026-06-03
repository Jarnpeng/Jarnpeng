(function(){
  'use strict';
  if (window.__airaCleanSlate7575) { return; }
  window.__airaCleanSlate7575 = true;

  var VERSION = '7.5.8.39-real-evidence-answer-bridge';
  var w = window, d = document;
  var cfg = w.AiRASafe || {};
  var actions = cfg.actions || {};
  var state = {
    rooms: [], activeRoomId: '', messages: [], lastAnswer: '', lastCode: '', lastCodeBlocks: [], sending: false,
    recognition: null, listening: false, artifactTab: 'preview', booted: false, codePreviewUrl: '', currentImage: null, imageGenerating: false, imageLoadingMessage: null, contextProfile: null, pendingAttachments: [], promptProcessing: null, interestStats: null, interestSyncTimer: 0, performanceEvents: [], authorizedIdentity: null, deviceId: '', localUpdatedAt: 0
  };

  function byId(id){ return d.getElementById(id); }
  function all(sel, root){ return Array.prototype.slice.call((root || d).querySelectorAll(sel)); }
  function app(){ return byId('airaApp'); }
  function clean(s){ return String(s == null ? '' : s); }
  function trim(s){ return clean(s).replace(/^\s+|\s+$/g, ''); }
  function esc(s){ return clean(s).replace(/[&<>"']/g, function(ch){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]; }); }
  function now(){ try { return new Date().toLocaleTimeString('th-TH', {hour:'2-digit', minute:'2-digit'}); } catch(e){ return ''; } }
  function icon(name){
    var map = {
      copy:'<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>',
      edit:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>',
      audio:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5 6 9H3v6h3l5 4V5Z"></path><path d="M15.5 8.5a5 5 0 0 1 0 7"></path><path d="M18.5 5.5a9 9 0 0 1 0 13"></path></svg>',
      more:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>',
      save:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path><path d="M17 21v-8H7v8"></path><path d="M7 3v5h8"></path></svg>',
      download:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12"></path><path d="m7 10 5 5 5-5"></path><path d="M5 21h14"></path></svg>',
      preview:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
      code:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 9-4 3 4 3"></path><path d="m16 9 4 3-4 3"></path><path d="m14 5-4 14"></path></svg>',
      cancel:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>',
      image:'<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="2"></rect><circle cx="9" cy="10" r="1.5"></circle><path d="m7 17 4.2-4.2 2.8 2.8 1.6-1.6L20 18"></path></svg>',
      brain:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 3a4 4 0 0 0-4 4v1a4 4 0 0 0-2 3.5A4.5 4.5 0 0 0 7.5 16H8v1.5A3.5 3.5 0 0 0 11.5 21H12V3Z"></path><path d="M15 3a4 4 0 0 1 4 4v1a4 4 0 0 1 2 3.5A4.5 4.5 0 0 1 16.5 16H16v1.5A3.5 3.5 0 0 1 12.5 21H12V3Z"></path><path d="M8 8h4"></path><path d="M12 12h4"></path></svg>',
      file:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><path d="M14 2v6h6"></path><path d="M8 13h8"></path><path d="M8 17h5"></path></svg>',
      docs:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><path d="M14 2v6h6"></path><path d="M8 13h8"></path><path d="M8 17h5"></path></svg>',
      install:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v10"></path><path d="m7 9 5 5 5-5"></path><rect x="4" y="17" width="16" height="4" rx="1"></rect></svg>',
      upgrade:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-2.64-6.36"></path><path d="M21 3v6h-6"></path><path d="M12 8v5l3 2"></path></svg>',
      next:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"></path><path d="m13 6 6 6-6 6"></path></svg>',
      module:'<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><path d="M14 17h7"></path><path d="M17.5 13.5v7"></path></svg>',
      like:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path></svg>',
      performance:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5"></path><path d="M4 19h16"></path><path d="m7 15 3-3 3 2 5-7"></path><path d="M17 7h3v3"></path></svg>',
      bug:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 7V5a4 4 0 0 1 8 0v2"></path><rect x="6" y="7" width="12" height="14" rx="6"></rect><path d="M3 13h3"></path><path d="M18 13h3"></path><path d="M4 20l3-2"></path><path d="M20 20l-3-2"></path><path d="M4 7l3 2"></path><path d="M20 7l-3 2"></path><path d="M10 11h.01"></path><path d="M14 11h.01"></path></svg>',
      idcard:'<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><circle cx="9" cy="12" r="2"></circle><path d="M14 10h4"></path><path d="M14 14h4"></path><path d="M7 16c.6-1.2 3.4-1.2 4 0"></path></svg>',
      globe:'<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 0 20"></path><path d="M12 2a15.3 15.3 0 0 0 0 20"></path></svg>'
    };
    return map[name] || '';
  }
  function isTypingText(text){ return clean(text).indexOf('<span class="typing-dots"') === 0; }
  function messageRoleLabel(role){ return role === 'user' ? 'ผู้ถาม' : (role === 'assistant' ? 'ผู้ตอบ' : 'ระบบ'); }
  function normalizeChoiceToken(token){
    token = trim(token || '');
    var th = {'๑':'1','๒':'2','๓':'3','๔':'4','๕':'5','๖':'6','๗':'7','๘':'8','๙':'9','๐':'0'};
    token = token.replace(/[๐-๙]/g, function(ch){ return th[ch] || ch; });
    return token.toUpperCase();
  }
  function textWithoutCodeBlocks(text){ return clean(text).replace(/```[\s\S]*?```/g, '\n').replace(/\[\[AIRA_[\s\S]*?\]\]/g, '\n'); }
  function extractContinuationChoices(text){
    var source = textWithoutCodeBlocks(text);
    var lines = source.split(/\r?\n/);
    var out = [], seen = {};
    var contextHint = hasRe(/ตัวเลือก|เงื่อนไข|ลำดับ|เลือก|หัวข้อ|ขั้นตอน|option|choice|condition|select|next/iu, source);
    lines.forEach(function(line){
      line = trim(line.replace(/^[>\s]+/, ''));
      if(!line || line.length > 230) return;
      var m = line.match(/^(?:[-*•]\s*)?(?:ข้อ\s*)?([0-9]{1,2}|[๐-๙]{1,2}|[A-Ha-h])(?:[\).:\-]|\s+)\s*(.{2,180})$/u);
      if(!m) return;
      var num = normalizeChoiceToken(m[1]);
      var label = trim(m[2]).replace(/^[-–—:\s]+/, '');
      if(!label || /^https?:\/\//i.test(label) || /^(px|%|kb|mb|gb|ms)$/i.test(label)) return;
      if(/^(function|class|const|let|var|if|for|while|return|echo|add_action|wp_)/i.test(label)) return;
      if(seen[num]) return;
      seen[num] = 1;
      out.push({num:num, label:label.slice(0, 150)});
    });
    if(out.length < 2 && !contextHint) return [];
    return out.slice(0, 12);
  }
  function lastAssistantMessageWithChoices(){
    var room = activeRoom ? activeRoom() : null;
    var list = room && Array.isArray(room.messages) ? room.messages : (state.messages || []);
    for(var i=list.length-1;i>=0;i--){
      var msg = list[i] || {};
      if(msg.role !== 'assistant') continue;
      var choices = extractContinuationChoices(msg.text || '');
      if(choices.length){ return {index:i, message:msg, choices:choices}; }
    }
    return null;
  }
  function continuationPromptFromChoice(choice, messageText){
    choice = choice || {};
    return [
      'ต่อจากคำตอบก่อนหน้า ผมเลือกข้อ ' + (choice.num || '') + (choice.label ? ': ' + choice.label : ''),
      '',
      'ช่วยคุยต่อจากตัวเลือกนี้แบบต่อเนื่อง ไม่เริ่มบริบทใหม่ และพาไปขั้นตอนถัดไปที่ใช้งานได้จริง',
      'ถ้าเป็นงานระบบ/โค้ด ให้ตอบแบบ: สิ่งที่จะทำ → โครงสร้าง → โค้ด/ขั้นตอน → ตรวจบั๊ก → สถานะใช้งาน',
      '',
      '[บริบทคำตอบก่อนหน้า]',
      clean(messageText || '').slice(0, 8000)
    ].join('\n');
  }
  function continuationIconForLabel(label, iconName){
    if(iconName) return iconName;
    label = clean(label || '');
    if(/ตัวอย่าง|example|sample/i.test(label)) return 'docs';
    if(/เงื่อนไข|condition|ข้อ/i.test(label)) return 'module';
    if(/ใช้ต่อ|next|ต่อ/i.test(label)) return 'next';
    if(/answer|คำตอบ/i.test(label)) return 'brain';
    return 'brain';
  }
  function composerHasContinuation(){
    var input = byId('composerInput');
    return !!(input && input.getAttribute('data-continuation-prompt'));
  }
  function clearComposerContinuation(silent){
    var input = byId('composerInput');
    if(!input) return false;
    input.removeAttribute('data-continuation-prompt');
    input.removeAttribute('data-continuation-visible');
    input.removeAttribute('data-continuation-icon');
    input.removeAttribute('data-continuation-benefit');
    input.removeAttribute('data-continuation-number-only');
    input.removeAttribute('data-continuation-number');
    input.removeAttribute('data-continuation-chip-hidden');
    resizeInput(); updateComposerContext();
    if(!silent) toast('ล้างไอคอนบริบทใน Composer แล้ว', 'ok');
    return true;
  }
  function composerContinuationChipHtml(){
    var input = byId('composerInput');
    if(!input || !input.getAttribute('data-continuation-prompt')) return '';
    if(input.getAttribute('data-continuation-chip-hidden') === '1') return '';
    var label = input.getAttribute('data-continuation-visible') || 'คุยต่อ';
    var iconName = continuationIconForLabel(label, input.getAttribute('data-continuation-icon') || '');
    var benefit = input.getAttribute('data-continuation-benefit') || 'ระบบจะใช้บริบทคำตอบเดิมแบบซ่อน และผู้ใช้พิมพ์ข้อความเพิ่มต่อได้';
    return '<span class="composer-continuation-chip" data-role="composer-continuation" title="'+esc(benefit)+'">'+icon(iconName)+'<b>'+esc(label).slice(0,28)+'</b><small>+ พิมพ์ต่อได้</small><button type="button" data-action="composer-continuation-clear" aria-label="ล้างบริบทคุยต่อ">×</button></span>';
  }
  function setComposerContinuationPrompt(prompt, visible, note, iconName, benefit){
    var input = byId('composerInput');
    if(!input){ toast('ไม่พบ Composer', 'error'); return false; }
    input.setAttribute('data-continuation-prompt', clean(prompt || ''));
    input.setAttribute('data-continuation-visible', clean(visible || 'คุยต่อ'));
    input.setAttribute('data-continuation-icon', continuationIconForLabel(visible, iconName || ''));
    input.setAttribute('data-continuation-benefit', clean(benefit || 'ระบบจะคุยต่อจากคำตอบเดิมแบบซ่อน ผู้ใช้พิมพ์รายละเอียดเพิ่มในช่องนี้ได้'));
    input.removeAttribute('data-continuation-number-only');
    input.removeAttribute('data-continuation-number');
    input.removeAttribute('data-continuation-chip-hidden');
    input.placeholder = 'พิมพ์รายละเอียดเพิ่มต่อจากไอคอนได้เลย';
    try{ input.focus({preventScroll:true}); }catch(e){ input.focus(); }
    resizeInput(); updateComposerContext(); setComposerMenuOpen(false);
    toast(note || 'ใส่ไอคอนคุยต่อใน Composer แล้ว พิมพ์ต่อได้เลย', 'ok');
    return true;
  }
  function setComposerNumberContinuationPrompt(prompt, number, note, benefit){
    var input = byId('composerInput');
    number = normalizeChoiceToken(number || '');
    if(!input){ toast('ไม่พบ Composer', 'error'); return false; }
    if(!number){ toast('ไม่พบหมายเลขสำหรับคุยต่อ', 'warn'); return false; }
    input.setAttribute('data-continuation-prompt', clean(prompt || ''));
    input.setAttribute('data-continuation-visible', number);
    input.setAttribute('data-continuation-icon', 'next');
    input.setAttribute('data-continuation-benefit', clean(benefit || 'ระบบใช้เลขนี้คุยต่อจากคำตอบเดิมแบบซ่อน prompt ยาว'));
    input.setAttribute('data-continuation-number-only', '1');
    input.setAttribute('data-continuation-number', number);
    input.setAttribute('data-continuation-chip-hidden', '1');
    input.value = number;
    input.placeholder = 'พิมพ์ต่อหลังเลขนี้ได้ เช่น ' + number + ' ขอแบบละเอียด';
    try{ input.focus({preventScroll:true}); input.setSelectionRange(input.value.length, input.value.length); }catch(e){ try{ input.focus(); }catch(x){} }
    resizeInput(); updateComposerContext(); setComposerMenuOpen(false);
    toast(note || ('ใส่เลข ' + number + ' ใน Composer แล้ว พิมพ์ต่อได้เลย'), 'ok');
    return true;
  }
  function resolveTypedContinuationChoice(text){
    var raw = trim(text || '');
    var m = raw.match(/^(?:เลือก\s*)?(?:ข้อ\s*)?([0-9]{1,2}|[๐-๙]{1,2}|[A-Ha-h])$/iu);
    if(!m) return null;
    var found = lastAssistantMessageWithChoices();
    if(!found) return null;
    var token = normalizeChoiceToken(m[1]);
    var choice = (found.choices || []).filter(function(c){ return normalizeChoiceToken(c.num) === token; })[0];
    if(!choice) return null;
    return {
      prompt: continuationPromptFromChoice(choice, found.message.text || ''),
      visible: token
    };
  }
  function resolveTypedInsightNumber(text){
    var raw = trim(text || '');
    var m = raw.match(/^(?:เลือก\s*)?(?:ข้อ\s*)?([0-9]{1,2}|[๐-๙]{1,2})$/iu);
    if(!m) return null;
    var token = normalizeChoiceToken(m[1]);
    var room = activeRoom ? activeRoom() : null;
    var list = room && Array.isArray(room.messages) ? room.messages : (state.messages || []);
    for(var i=list.length-1;i>=0;i--){
      var msg = list[i] || {};
      if(msg.role !== 'assistant') continue;
      var answerText = msg.text || '';
      var choices = extractContinuationChoices(answerText);
      var items = extractAnswerInsightItems(answerText);
      var startNum = choices.length + 1;
      for(var j=0;j<items.length;j++){
        var num = String(startNum + j);
        if(normalizeChoiceToken(num) === token){
          return {prompt:answerInsightPrompt(items[j].key, answerText), visible:token, type:items[j].key};
        }
      }
      if(choices.length || items.length) break;
    }
    return null;
  }
  function createContinuationChoiceRow(raw){
    var choices = extractContinuationChoices(raw);
    if(!choices.length) return null;
    var row = d.createElement('div');
    row.className = 'message-choice-continuation';
    row.setAttribute('aria-label', 'ตัวเลือกสำหรับคุยต่อเนื่อง');
    var label = d.createElement('span');
    label.className = 'choice-continuation-label';
    label.textContent = 'คุยต่อด้วยตัวเลข';
    row.appendChild(label);
    choices.forEach(function(choice){
      var btn = d.createElement('button');
      btn.type = 'button';
      btn.className = 'choice-continuation-chip';
      btn.setAttribute('data-action', 'message-choice-compose');
      btn.setAttribute('data-choice-num', choice.num);
      btn.setAttribute('data-choice-label', choice.label);
      btn.setAttribute('aria-label', 'เลือกข้อ ' + choice.num + ' เพื่อคุยต่อ');
      btn.innerHTML = '<b>' + esc(choice.num) + '</b><span>' + esc(choice.label) + '</span>';
      row.appendChild(btn);
    });
    return row;
  }

  function answerInsightSignalText(text){
    return textWithoutCodeBlocks(text).replace(/https?:\/\/\S+/g, ' ').replace(/\s+/g, ' ').slice(0, 12000);
  }
  function extractAnswerInsightItems(text){
    var source = answerInsightSignalText(text);
    if(!trim(source) || source.length < 80) return [];
    var items = [];
    function add(key,label,iconKey,meaning,benefit,found){
      if(!found) return;
      if(items.some(function(it){ return it.key === key; })) return;
      items.push({key:key,label:label,iconKey:iconKey,meaning:meaning,benefit:benefit});
    }
    var hasCondition = hasRe(/(เงื่อนไข|ถ้า|หาก|กรณี|เมื่อ|ต้องมี|จำเป็น|ข้อกำหนด|condition|if\s|when\s|requirement|rule|constraint)/iu, source);
    var hasExample = hasRe(/(ตัวอย่าง|เช่น|ยกตัวอย่าง|sample|example|for example|case study|demo)/iu, source);
    var hasMeaning = hasRe(/(ความหมาย|หมายถึง|คือ|แปลว่า|อธิบาย|ศัพท์|นิยาม|purpose|meaning|definition|concept|command|คำสั่ง|ฟังก์ชัน|ปุ่ม)/iu, source);
    var hasTest = hasRe(/(วิธีใช้|ขั้นตอน|ทดสอบ|ตรวจ|ใช้งานจริง|ลอง|preview|qc|test|next step|use case)/iu, source);
    var hasNumbered = extractContinuationChoices(text).length > 0;
    add('meaning','ความหมาย','brain','ช่วยอธิบายศัพท์ ปุ่ม คำสั่ง หรือแนวคิดที่อยู่ในคำตอบก่อนหน้า', 'ทำให้ผู้ใช้เข้าใจว่าข้อความ/คำสั่งนั้นมีไว้เพื่ออะไรและใช้ต่ออย่างไร', hasMeaning || (hasCondition && hasExample));
    add('example','ตัวอย่าง','docs','ขอตัวอย่างที่จับต้องได้จากคำตอบก่อนหน้า', 'ช่วยให้ผู้ใช้เห็นภาพจริงจากสถานการณ์หรือ use case ไม่ใช่เข้าใจแค่ทฤษฎี', hasExample || hasNumbered);
    add('condition','เงื่อนไข','module','แยกเงื่อนไข ข้อจำกัด และสิ่งที่ต้องมีให้ชัด', 'ช่วยลดความสับสนก่อนตัดสินใจ กดปุ่ม ใช้โค้ด หรือทำขั้นตอนต่อ', hasCondition || hasNumbered);
    add('apply','ใช้ต่อ','next','แปลงคำตอบก่อนหน้าเป็นขั้นตอนถัดไปที่ลงมือทำได้', 'ช่วยให้ผู้ใช้ไม่หยุดที่การอ่าน แต่ไปต่อเป็นงานจริงหรือ prompt ต่อเนื่องได้', hasTest || hasNumbered || (hasCondition && hasMeaning));
    return items.slice(0, 4);
  }
  function answerInsightPrompt(type, messageText){
    var text = clean(messageText || '').slice(0, 10000);
    var titles = {
      meaning:'ช่วยอธิบายความหมายจากคำตอบก่อนหน้า',
      example:'ช่วยยกตัวอย่างจากคำตอบก่อนหน้า',
      condition:'ช่วยแยกเงื่อนไขจากคำตอบก่อนหน้า',
      apply:'ช่วยพาไปขั้นตอนถัดไปจากคำตอบก่อนหน้า'
    };
    var rule = {
      meaning:'อธิบายว่าแต่ละคำ/ปุ่ม/คำสั่ง/แนวคิด หมายถึงอะไร ช่วยผู้ใช้อย่างไร ใช้เมื่อไหร่ และควรระวังอะไร',
      example:'ยกตัวอย่างจริง 2-3 แบบ โดยแยกตัวอย่างง่าย → ตัวอย่างใช้งานจริง → ตัวอย่างต่อยอด พร้อมบอกผลลัพธ์ที่ควรเห็น',
      condition:'สรุปเงื่อนไขเป็นข้อ 1,2,3 แยกว่า ต้องมี / ควรมี / ยังไม่จำเป็น / เสี่ยง และบอกวิธีตรวจแต่ละข้อ',
      apply:'แปลงคำตอบเดิมเป็นขั้นตอนทำต่อทันที 1,2,3 พร้อมสิ่งที่ต้องกด/แก้/ทดสอบ และสรุปว่าสำเร็จหรือยัง'
    };
    return [
      titles[type] || titles.meaning,
      '',
      rule[type] || rule.meaning,
      'ตอบต่อจากบริบทเดิม ไม่เริ่มใหม่ ไม่เดาข้อมูลส่วนตัว และถ้าเกี่ยวกับระบบ/โค้ดให้บอกประโยชน์ต่อผู้ใช้งานจริงทุกจุด',
      '',
      '[คำตอบก่อนหน้า]',
      text
    ].join('\n');
  }
  function createAnswerInsightIconRow(raw){
    var items = extractAnswerInsightItems(raw);
    if(!items.length) return null;
    var choiceCount = extractContinuationChoices(raw).length;
    var row = d.createElement('div');
    row.className = 'message-answer-insights is-numbered';
    row.setAttribute('aria-label', 'หมายเลขรับรู้ความหมายต่อจากตัวเลือกใต้คำตอบ');
    var label = d.createElement('span');
    label.className = 'answer-insight-label';
    label.innerHTML = '<span>รับรู้ต่อด้วยเลข</span>';
    row.appendChild(label);
    items.forEach(function(it, idx){
      var num = String(choiceCount + idx + 1);
      var btn = d.createElement('button');
      btn.type = 'button';
      btn.className = 'answer-insight-chip answer-insight-number is-' + it.key;
      btn.setAttribute('data-action', 'message-answer-insight');
      btn.setAttribute('data-insight-type', it.key);
      btn.setAttribute('data-insight-number', num);
      btn.setAttribute('data-insight-meaning', it.meaning);
      btn.setAttribute('data-insight-benefit', it.benefit);
      btn.setAttribute('aria-label', 'เลือกหมายเลข ' + num + ' เพื่อ ' + it.label + ' — ' + it.meaning + ' — ' + it.benefit);
      btn.setAttribute('title', 'กดแล้วใส่เลข ' + num + ' เข้า Composer · ' + it.meaning + ' · ' + it.benefit);
      btn.innerHTML = '<b>' + esc(num) + '</b><span>' + esc(it.label) + '</span>';
      row.appendChild(btn);
    });
    return row;
  }
  function createUnifiedNumberContinuationRow(raw){
    var choices = extractContinuationChoices(raw);
    var insights = extractAnswerInsightItems(raw);
    if(!choices.length && !insights.length) return null;
    var row = d.createElement('div');
    row.className = 'message-choice-continuation is-unified-number-row';
    row.setAttribute('aria-label', 'คุยต่อด้วยตัวเลขชุดเดียวใต้คำตอบ');
    row.setAttribute('data-continuation-row', 'single-number-set');
    var label = d.createElement('span');
    label.className = 'choice-continuation-label';
    label.textContent = 'คุยต่อด้วยเลข';
    row.appendChild(label);
    var used = {};
    choices.forEach(function(choice){
      var num = normalizeChoiceToken(choice.num);
      if(!num || used[num]) return;
      used[num] = 1;
      var btn = d.createElement('button');
      btn.type = 'button';
      btn.className = 'choice-continuation-chip is-choice-number';
      btn.setAttribute('data-action', 'message-choice-compose');
      btn.setAttribute('data-choice-num', num);
      btn.setAttribute('data-choice-label', choice.label);
      btn.setAttribute('data-continuation-kind', 'answer-choice');
      btn.setAttribute('aria-label', 'เลือกข้อ ' + num + ' เพื่อคุยต่อ');
      btn.innerHTML = '<b>' + esc(num) + '</b><span>' + esc(choice.label) + '</span>';
      row.appendChild(btn);
    });
    var start = choices.length + 1;
    insights.forEach(function(it, idx){
      var num = normalizeChoiceToken(String(start + idx));
      while(used[num]){ start += 1; num = normalizeChoiceToken(String(start + idx)); }
      used[num] = 1;
      var btn = d.createElement('button');
      btn.type = 'button';
      btn.className = 'choice-continuation-chip answer-insight-chip answer-insight-number is-' + it.key;
      btn.setAttribute('data-action', 'message-answer-insight');
      btn.setAttribute('data-insight-type', it.key);
      btn.setAttribute('data-insight-number', num);
      btn.setAttribute('data-insight-meaning', it.meaning);
      btn.setAttribute('data-insight-benefit', it.benefit);
      btn.setAttribute('data-continuation-kind', 'answer-insight');
      btn.setAttribute('aria-label', 'เลือกหมายเลข ' + num + ' เพื่อ ' + it.label + ' — ' + it.meaning + ' — ' + it.benefit);
      btn.setAttribute('title', 'กดแล้วใส่เลข ' + num + ' เข้า Composer · ' + it.meaning + ' · ' + it.benefit);
      btn.innerHTML = '<b>' + esc(num) + '</b><span>' + esc(it.label) + '</span>';
      row.appendChild(btn);
    });
    return row.children.length > 1 ? row : null;
  }
  function enforceSingleNumberContinuationRows(root){
    if(!root || !root.querySelectorAll) return;
    all('.message-card', root).forEach(function(card){
      var rows = all('.message-choice-continuation, .message-answer-insights, .message-continuation-choices', card);
      if(rows.length <= 1) return;
      var keep = null;
      for(var i=rows.length-1;i>=0;i--){
        if(rows[i].classList && rows[i].classList.contains('is-unified-number-row')){ keep = rows[i]; break; }
      }
      if(!keep) keep = rows[rows.length-1];
      rows.forEach(function(row){ if(row !== keep && row.parentNode){ row.parentNode.removeChild(row); } });
    });
  }

  function promptProcessorInsightSeedText(ctx, userText, statusText){
    // v7.5.7.8: Prompt processor must stay clean. It never seeds numbered insight/meaning choices.
    // Insight numbers are created only from the final assistant answer after slide typing is complete.
    return statusText || buildPromptProcessingText(ctx || buildAllFormContext(userText || ''), userText || '');
  }
  function ensurePromptProcessorInsightRow(processor, statusText){
    // v7.5.7.8: no meaning/example/condition/continue numbers on Prompt processor.
    removePromptProcessorInsightRows(processor);
    return null;
  }
  function removePromptProcessorInsightRows(processor){
    var node = processor && processor.node ? processor.node : processor;
    if(!node || !node.querySelectorAll) return;
    all('.message-choice-continuation, .message-continuation-choices, .message-answer-insights, .answer-insight-chip, .choice-continuation-chip', node).forEach(function(el){
      if(el && el.parentNode){ el.parentNode.removeChild(el); }
    });
  }
  function removeAnswerContinuationRows(node){
    if(!node || !node.querySelectorAll) return;
    all('.message-choice-continuation, .message-continuation-choices, .message-answer-insights', node).forEach(function(el){
      if(el && el.parentNode){ el.parentNode.removeChild(el); }
    });
  }
  function appendPostSlideAnswerRows(node, raw){
    if(!node || !node.querySelector) return;
    if(node.classList && node.classList.contains('prompt-processing')){ removePromptProcessorInsightRows(node); return; }
    var card = node.querySelector('.message-card');
    if(!card) return;
    removeAnswerContinuationRows(node);
    var frag = d.createDocumentFragment();
    var unifiedRow = createUnifiedNumberContinuationRow(raw);
    if(unifiedRow){
      unifiedRow.setAttribute('data-ready-after','assistant-slide-done');
      unifiedRow.setAttribute('data-insight-source','assistant-answer-done');
      frag.appendChild(unifiedRow);
    }
    if(!frag.childNodes.length) return;
    var tools = card.querySelector('.message-tools');
    if(tools){ card.insertBefore(frag, tools); }
    else { card.appendChild(frag); }
    enforceSingleNumberContinuationRows(node);
  }

  function shouldCollapseMessage(role, text){ var t = clean(text); return role === 'user' && !isTypingText(t) && (t.length > 520 || t.split(/\n/).length > 7); }
  function roleFromNode(node){
    if(!node) return '';
    if(node.classList.contains('user')) return 'user';
    if(node.classList.contains('assistant')) return 'assistant';
    if(node.classList.contains('system')) return 'system';
    return '';
  }
  function countRe(re, text){ var m = clean(text).match(re); return m ? m.length : 0; }
  function hasRe(re, text){ return re.test(clean(text)); }
  function clamp(n, min, max){ n = Number(n) || 0; return Math.max(min, Math.min(max, n)); }
  function uniqueList(arr){ var out=[], seen={}; (arr||[]).forEach(function(x){ x=trim(x); if(x && !seen[x]){ seen[x]=1; out.push(x); } }); return out; }
  function detectLanguage(text){
    var th = countRe(/[ก-๙]/g, text), en = countRe(/[A-Za-z]/g, text), allc = Math.max(1, th + en);
    if(th/allc > 0.7) return 'thai';
    if(en/allc > 0.7) return 'english';
    if(th && en) return 'thai_english_mixed';
    return 'unknown';
  }
  function detectContentForms(text){
    var forms=[];
    if(hasRe(/```|<\/?[a-z][\s\S]*?>|function\s*\(|class\s+|add_action\s*\(|wp_ajax_|shortcode|\.php|\.js|\.css/iu, text)) forms.push('code');
    if(hasRe(/https?:\/\/|www\.|\.com\b|\.co\.th\b/iu, text)) forms.push('link_or_web');
    if(hasRe(/ภาพ|รูป|image|photo|camera|svg|png|jpg|jpeg|webp|gif/iu, text) || (state.pendingAttachments && state.pendingAttachments.length)) forms.push('image_visual');
    if(hasRe(/เสียง|ไมค์|พูด|voice|audio|tts|ฟัง/iu, text)) forms.push('voice_audio');
    if(hasRe(/ไฟล์|แนบ|zip|pdf|docx|xlsx|upload|download|บันทึก|ดาวน์โหลด/iu, text)) forms.push('file_artifact');
    if(hasRe(/wordpress|plugin|ปลั๊กอิน|wp-admin|admin bar|shortcode|elementor|gutenberg|woocommerce|theme|ธีม/iu, text)) forms.push('wordpress_system');
    if(hasRe(/react|vue|next\.?js|nuxt|node|express|python|django|flask|laravel|php|mysql|sql|database|firebase|supabase|api|rest|graphql|docker|linux|vps|server|android|ios|flutter|electron|desktop app|mobile app/iu, text)) forms.push('software_system');
    if(hasRe(/ui|ux|composer|popup|ป๊อบอัพ|responsive|mobile|desktop|tablet|layout|สี|ฟอนต์|ปุ่ม/iu, text)) forms.push('ui_ux');
    if(hasRe(/[0-9]+\s*(%|px|kb|mb|gb|ms|วิ|นาที|ชั่วโมง|บาท)?/iu, text)) forms.push('number_metric');
    if(!forms.length) forms.push('plain_text');
    return uniqueList(forms);
  }
  function detectIntent(text){
    var t = clean(text);
    if(hasRe(/แก้|ซ่อม|fix|bug|ผิด|ค้าง|ไม่ทำงาน|ไม่แสดง|ไม่ขึ้น|error|fail/iu, t)) return 'fix_debug';
    if(hasRe(/เพิ่ม|ใส่|สร้าง|ทำ|develop|generate|build|create|add/iu, t)) return 'build_add';
    if(hasRe(/วิเคราะห์|ตรวจ|เช็ก|audit|inspect|review|คำนวณ|ประเมิน/iu, t)) return 'analyze_calculate';
    if(hasRe(/อธิบาย|สอน|ทำยังไง|คืออะไร|why|how/iu, t)) return 'explain_teach';
    if(hasRe(/ต่อ|เอาเลย|ทำต่อ|รื้อต่อ/iu, t)) return 'continue_previous';
    return 'general_answer';
  }
  function detectTone(text){
    var t = clean(text), score = 0, flags=[];
    if(hasRe(/ด่วน|เร็ว|ทันที|เลย|ต้อง|ห้าม|ไม่เอา|เอาออก/iu, t)){ score += 28; flags.push('urgent_direct'); }
    if(hasRe(/ทำไม|ไม่ได้|ยังไม่|ค้าง|ผิดปกติ|เสีย|งง|งงมาก/iu, t)){ score += 24; flags.push('frustration_or_blocked'); }
    if(hasRe(/ดีไหม|ได้ไหม|ช่วย|ขอ/iu, t)){ score += 8; flags.push('polite_request'); }
    if(hasRe(/[!！]{1,}|\?{2,}/u, t)){ score += 8; flags.push('high_emphasis'); }
    return {urgency:clamp(score, 0, 100), flags:uniqueList(flags)};
  }
  function lastMessagesForContext(limit){
    var room = activeRoom ? activeRoom() : null;
    var list = room && Array.isArray(room.messages) ? room.messages : (state.messages || []);
    return list.slice(Math.max(0, list.length - (limit || 8))).map(function(m){
      return {role:m.role || '', text:clean(m.text || '').replace(/\s+/g,' ').slice(0,220)};
    });
  }
  function detectContinuity(text, recent){
    var t = clean(text), cont = hasRe(/^(ต่อ|เอาเลย|ทำต่อ|รื้อต่อ|แก้ต่อ|เพิ่มต่อ)\b|ต่อจาก|เหมือนเดิม|จากไฟล์|เวอร์ชันล่าสุด/iu, t);
    var hasRecent = Array.isArray(recent) && recent.length > 1;
    return cont ? 'high_continue_previous_context' : (hasRecent ? 'medium_use_recent_room_context' : 'low_new_topic');
  }
  function extractTopicHints(text, forms){
    var hints=[];
    if(forms.indexOf('wordpress_system') !== -1) hints.push('WordPress plugin/admin');
    if(forms.indexOf('ui_ux') !== -1) hints.push('UI/UX layout/composer/popup');
    if(forms.indexOf('code') !== -1) hints.push('code generation or code debugging');
    if(forms.indexOf('image_visual') !== -1) hints.push('image generation/visual result');
    if(forms.indexOf('voice_audio') !== -1) hints.push('voice/audio interaction');
    if(forms.indexOf('file_artifact') !== -1) hints.push('file/download/artifact flow');
    if(hasRe(/Thinkb4do|AiRA|Aira|ไอร่า|thinkb4do/iu, text)) hints.push('AiRA Studio / Thinkb4do project');
    return uniqueList(hints);
  }
  function scoreClarity(text, forms, intent){
    var len = trim(text).length, score = 48;
    if(len > 12) score += 16;
    if(len > 60) score += 10;
    if(forms.length > 1) score += 8;
    if(intent !== 'general_answer') score += 10;
    if(hasRe(/นี้|ตรงนี้|แบบนี้|มัน|มันไม่/iu, text) && len < 70) score -= 18;
    return clamp(score, 0, 100);
  }
  function buildAllFormContext(userText){
    var original = trim(userText);
    var recent = lastMessagesForContext(8);
    var forms = detectContentForms(original);
    var intent = detectIntent(original);
    var tone = detectTone(original);
    var language = detectLanguage(original);
    var continuity = detectContinuity(original, recent);
    var topics = extractTopicHints(original, forms);
    var clarity = scoreClarity(original, forms, intent);
    var complexity = clamp(18 + forms.length*12 + (topics.length*8) + (original.length > 180 ? 18 : 0) + (continuity.indexOf('high')===0 ? 14 : 0), 0, 100);
    var communicationScore = clamp(Math.round((clarity*0.32) + ((100-tone.urgency)*0.12) + ((100-complexity)*0.08) + 44), 0, 100);
    var responseStyle = [];
    var interpretation = buildInterpretationResult(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore});
    var questionReading = buildQuestionReadingResult(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore});
    var desiredOutput = resolveDesiredOutput(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore, interpretation:interpretation, questionReading:questionReading});
    var subtext = detectImplicitCommunicationNeeds(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore});
    var globalCommunication = buildGlobalCommunicationLens(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore, subtext:subtext});
    var codeMaster = buildCodeMasterProfile(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore});
    var universalCode = buildUniversalSystemCodeProfile(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore, codeMaster:codeMaster});
    var autoEvolutionCore = buildAutoEvolutionCoreProfile(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore, interpretation:interpretation, questionReading:questionReading, desiredOutput:desiredOutput, subtext:subtext, globalCommunication:globalCommunication, codeMaster:codeMaster, universalCode:universalCode});
    var thoughtPromptCompiler = buildThoughtPromptCompilerProfile(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore, interpretation:interpretation, questionReading:questionReading, desiredOutput:desiredOutput, subtext:subtext, globalCommunication:globalCommunication, codeMaster:codeMaster, universalCode:universalCode, autoEvolutionCore:autoEvolutionCore});
    var externalSystemBridge = buildExternalSystemBridgeProfile(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore, interpretation:interpretation, questionReading:questionReading, desiredOutput:desiredOutput, subtext:subtext, globalCommunication:globalCommunication, codeMaster:codeMaster, universalCode:universalCode, autoEvolutionCore:autoEvolutionCore, thoughtPromptCompiler:thoughtPromptCompiler});
    var communicationSkillEvolution = buildCommunicationSkillEvolutionProfile(original, {forms:forms, intent:intent, tone:tone, language:language, continuity:continuity, topics:topics, clarityScore:clarity, complexityScore:complexity, communicationScore:communicationScore, interpretation:interpretation, questionReading:questionReading, desiredOutput:desiredOutput, subtext:subtext, globalCommunication:globalCommunication, codeMaster:codeMaster, universalCode:universalCode, autoEvolutionCore:autoEvolutionCore, thoughtPromptCompiler:thoughtPromptCompiler, externalSystemBridge:externalSystemBridge});
    if(intent === 'fix_debug') responseStyle.push('เริ่มจากสาเหตุ แล้วระบุจุดแก้และวิธีทดสอบ');
    if(intent === 'build_add') responseStyle.push('บอกของเดิมยังอยู่ อะไรที่เพิ่ม และสถานะใช้งาน');
    if(intent === 'continue_previous') responseStyle.push('ยึดบริบทล่าสุด ไม่เริ่มใหม่โดยไม่จำเป็น');
    if(forms.indexOf('code') !== -1 || forms.indexOf('wordpress_system') !== -1) responseStyle.push('อธิบายโค้ด/ไฟล์แบบคนไม่เข้าใจโค้ดก็ทำตามได้');
    if(forms.indexOf('image_visual') !== -1) responseStyle.push('แยก prompt / preview / save-download flow ให้ชัดเจน');
    if(!responseStyle.length) responseStyle.push('ตอบเป็นลำดับ สั้นก่อน แล้วค่อยลงรายละเอียดที่จำเป็น');
    return {
      version:'context_reader_v7.3.5_global_communication_code_universe',
      language:language,
      forms:forms,
      intent:intent,
      toneFlags:tone.flags,
      urgencyScore:tone.urgency,
      continuity:continuity,
      topics:topics,
      clarityScore:clarity,
      complexityScore:complexity,
      communicationScore:communicationScore,
      responseStyle:uniqueList(responseStyle),
      interpretation:interpretation,
      questionReading:questionReading,
      desiredOutput:desiredOutput,
      subtext:subtext,
      globalCommunication:globalCommunication,
      codeMaster:codeMaster,
      universalCode:universalCode,
      autoEvolutionCore:autoEvolutionCore,
      thoughtPromptCompiler:thoughtPromptCompiler,
      externalSystemBridge:externalSystemBridge,
      communicationSkillEvolution:communicationSkillEvolution,
      recent:recent,
      privacy:'privacy-safe: do not infer or store gender, age, occupation, health, religion, politics, or other sensitive personal attributes unless explicitly provided by the user for this task',
      original:original
    };
  }


  function detectInterpretationMode(text){
    var t = clean(text);
    var overview = hasRe(/ตีความภาพรวม|ภาพรวม|มองรวม|สรุปรวม|อ่านรวม|overall|overview|big\s*picture|whole\s*context/iu, t);
    var shortMode = hasRe(/ตีความสั้น|สั้นๆ|สั้น ๆ|สรุปสั้น|ย่อให้|คำตอบสั้น|brief|concise|short\s*answer/iu, t);
    if(overview && shortMode) return 'overview_then_short';
    if(overview) return 'overview';
    if(shortMode) return 'short';
    return 'auto';
  }
  function labelInterpretationMode(mode){
    if(mode === 'overview') return 'ตีความภาพรวม';
    if(mode === 'short') return 'ตีความสั้น';
    if(mode === 'overview_then_short') return 'ภาพรวม + สั้น';
    return 'ตีความอัตโนมัติ';
  }
  function intentGoal(intent){
    var map = {
      fix_debug:'แก้ปัญหา/บั๊กให้ใช้งานได้จริง',
      build_add:'เพิ่มความสามารถหรือสร้างระบบใหม่โดยไม่ทำของเดิมหาย',
      analyze_calculate:'วิเคราะห์/คำนวณให้เห็นผลลัพธ์และเหตุผล',
      explain_teach:'อธิบายให้ง่ายและทำตามได้',
      continue_previous:'ต่อจากบริบทล่าสุดโดยไม่เริ่มใหม่',
      general_answer:'ตอบให้ตรงคำถามและใช้งานได้ทันที'
    };
    return map[intent] || map.general_answer;
  }
  function buildInterpretationResult(text, meta){
    meta = meta || {};
    var mode = detectInterpretationMode(text);
    var forms = meta.forms || [];
    var topics = meta.topics || [];
    var intent = meta.intent || detectIntent(text);
    var continuity = meta.continuity || 'low_new_topic';
    var target = topics.length ? topics.join(' + ') : (forms.length ? forms.join(' + ') : 'คำถามล่าสุด');
    var baseMeaning = 'ผู้ถามต้องการให้ AiRA ' + intentGoal(intent) + ' จากบริบทที่ให้มา';
    if(mode === 'overview') baseMeaning = 'ผู้ถามต้องการให้ AiRA อ่านภาพรวมทั้งหมดก่อน แล้วสรุปแกนปัญหา/เป้าหมาย/ผลลัพธ์ที่ควรทำให้เห็นทันที';
    if(mode === 'short') baseMeaning = 'ผู้ถามต้องการคำตอบที่ตีความแล้วแบบสั้น ตรงประเด็น เห็นผลลัพธ์เร็ว และไม่อธิบายยาวเกินจำเป็น';
    if(mode === 'overview_then_short') baseMeaning = 'ผู้ถามต้องการให้มองภาพรวมก่อน แล้วบีบผลลัพธ์ให้สั้นแบบใช้ต่อได้ทันที';
    if(mode === 'auto' && continuity.indexOf('high') === 0) baseMeaning = 'ผู้ถามกำลังสั่งต่อจากงานเดิม ต้องใช้บริบทล่าสุดเป็นฐานและตอบผลลัพธ์ตรงคำสั่งใหม่';
    var expected = 'แสดงผลลัพธ์ก่อน แล้วค่อยอธิบายเฉพาะจุดจำเป็น';
    if(forms.indexOf('wordpress_system') !== -1 || forms.indexOf('code') !== -1){ expected = 'สรุปผลที่ต้องแก้/เพิ่ม พร้อมไฟล์หรือจุดโค้ดที่เกี่ยวข้อง วิธีทดสอบ และสถานะใช้งาน'; }
    if(forms.indexOf('ui_ux') !== -1){ expected = 'สรุปภาพรวม UI/UX ที่ต้องเปลี่ยน ผลต่อผู้ใช้ และจุดที่ต้องทดสอบบนมือถือ/เดสก์ท็อป'; }
    if(forms.indexOf('image_visual') !== -1){ expected = 'สรุปผลลัพธ์ภาพ/preview/save flow ให้ชัดว่าผู้ใช้จะเห็นอะไรและกดอะไรต่อ'; }
    var confidence = clamp(Math.round(((meta.clarityScore || scoreClarity(text, forms, intent))*0.55) + (intent !== 'general_answer' ? 16 : 4) + (forms.length*5) + (continuity.indexOf('high')===0 ? 12 : 0)), 0, 100);
    var length = mode === 'short' ? 'short_first' : (mode === 'overview' ? 'overview_first' : 'balanced_gpt_style');
    return {mode:mode, label:labelInterpretationMode(mode), meaning:baseMeaning, target:target, expectedOutput:expected, actionFocus:intentGoal(intent), responseLength:length, confidence:confidence};
  }
  function questionSystemConnectors(meta){
    meta = meta || {};
    var forms = meta.forms || [];
    var intent = meta.intent || 'general_answer';
    var connectors = ['Core Chat', 'Context Reader', 'GPT Communication', 'Answer Router'];
    if(meta.continuity && meta.continuity.indexOf('high') === 0){ connectors.push('Room History'); connectors.push('Memory/Topic Continuity'); }
    if(forms.indexOf('wordpress_system') !== -1){ connectors.push('WordPress System'); connectors.push('Plugin/Module/Widget Map'); connectors.push('Settings/API Center'); }
    if(forms.indexOf('code') !== -1){ connectors.push('Code Renderer'); connectors.push('Artifact Files'); connectors.push('Download/Preview'); }
    if(forms.indexOf('image_visual') !== -1){ if(state.pendingAttachments && state.pendingAttachments.length){ connectors.push('Vision Attachment Reader'); connectors.push('OCR/Image Understanding'); } else { connectors.push('Image Generation'); connectors.push('Image Preview/Save'); } }
    if(forms.indexOf('voice_audio') !== -1){ connectors.push('Voice/Audio'); connectors.push('TTS/STT Flow'); }
    if(forms.indexOf('file_artifact') !== -1){ connectors.push('File Reader'); connectors.push('Artifact/Download'); }
    if(forms.indexOf('link_or_web') !== -1){ connectors.push('Web/Research Reader'); connectors.push('Link Preview'); }
    if(forms.indexOf('number_metric') !== -1){ connectors.push('Metric/Calculation Guard'); }
    if(intent === 'fix_debug'){ connectors.push('Bug Recovery'); connectors.push('System Health'); }
    if(intent === 'build_add'){ connectors.push('System Builder'); connectors.push('Feature Composer'); }
    if(intent === 'analyze_calculate'){ connectors.push('Analyzer'); connectors.push('QC/Score'); }
    return uniqueList(connectors);
  }
  function buildQuestionReadingResult(text, meta){
    meta = meta || {};
    var forms = meta.forms || detectContentForms(text);
    var intent = meta.intent || detectIntent(text);
    var continuity = meta.continuity || 'low_new_topic';
    var connectors = questionSystemConnectors({forms:forms, intent:intent, continuity:continuity});
    var route = 'normal_question_to_direct_answer';
    if(intent === 'fix_debug') route = 'normal_question_to_bug_fix_answer';
    else if(intent === 'build_add') route = 'normal_question_to_system_build_answer';
    else if(intent === 'continue_previous') route = 'normal_question_to_latest_context_answer';
    else if(intent === 'analyze_calculate') route = 'normal_question_to_analysis_answer';
    else if(intent === 'explain_teach') route = 'normal_question_to_simple_explanation';
    var answerPlan = 'อ่านคำถามปกติ → เชื่อมโมดูลที่เกี่ยวข้อง → ตอบผลลัพธ์ตรงก่อน → ขยายเฉพาะจุดจำเป็น';
    if(forms.indexOf('wordpress_system') !== -1 || forms.indexOf('code') !== -1){ answerPlan = 'อ่านคำถามปกติ → เชื่อม WordPress/Code/Artifact/API → สรุปจุดแก้หรือสิ่งที่สร้าง → ให้โค้ด/ไฟล์/วิธีทดสอบ'; }
    if(forms.indexOf('image_visual') !== -1){ answerPlan = (state.pendingAttachments && state.pendingAttachments.length) ? 'อ่านคำถามปกติ → แนบ live image เข้า Vision API → ตีความภาพ/OCR → ตอบจากสิ่งที่เห็นจริง' : 'อ่านคำถามปกติ → เชื่อม Image Prompt/Image API/Preview/Save → แสดงภาพหรือแนวทางสร้างภาพให้ใช้ได้จริง'; }
    if(forms.indexOf('voice_audio') !== -1){ answerPlan = 'อ่านคำถามปกติ → เชื่อม Voice/Audio/TTS/STT → ตอบวิธีใช้หรือแก้ระบบเสียงอย่างตรงจุด'; }
    var confidence = clamp(Math.round(((meta.clarityScore || scoreClarity(text, forms, intent))*0.45) + connectors.length*5 + (continuity.indexOf('high')===0 ? 14 : 8)), 0, 100);
    return {
      version:'question_reader_v7.2.0_auto_system_answer',
      normalTyping:'ผู้ใช้พิมพ์ปกติได้ ระบบอ่านคำถามและเชื่อมโมดูลที่เกี่ยวข้องให้อัตโนมัติก่อนตอบ',
      route:route,
      connectors:connectors,
      answerPlan:answerPlan,
      outputRule:'ตอบเป็นผลลัพธ์ที่ใช้ได้ทันที ไม่บังคับให้ผู้ใช้เลือกเมนูก่อน และไม่ลากระบบที่ไม่เกี่ยวข้องมาทำให้รก',
      confidence:confidence
    };
  }

  function resolveDesiredOutput(text, meta){
    meta = meta || {};
    var forms = meta.forms || detectContentForms(text);
    var intent = meta.intent || detectIntent(text);
    var attachmentCount = (state.pendingAttachments || []).length;
    var wantsVisible = hasRe(/ผลลัพธ์|ผลลัพ|แสดงผล|แสดง|ขึ้น|ไม่ขึ้น|ไม่แสดง|preview|result|output|เห็นจริง|ของจริง|หน้าจริง|เรนเดอร์|render/iu, text);
    var promptIssue = hasRe(/prompt|พรอมป์|ตีความ|เข้าใจ|สั่งระบบ|อ่านคำถาม|ประมวล/iu, text);
    var type = 'direct_answer';
    var label = 'คำตอบตรงตามคำถาม';
    var primaryResult = 'ตอบผลลัพธ์ที่ใช้ได้ทันที ไม่ค้างแค่สถานะหรือข้อความประมวลผล';
    var renderRule = 'เริ่มด้วยผลลัพธ์จริงก่อน แล้วค่อยอธิบายสั้น ๆ เฉพาะสิ่งจำเป็น';
    var fallback = 'ถ้า API ตอบไม่ครบ ให้สร้างคำตอบสำรองที่ระบุผลลัพธ์ที่ต้องแสดง วิธีทดสอบ และข้อจำกัดจริง';
    if(forms.indexOf('image_visual') !== -1){
      type = attachmentCount ? 'vision_image_interpretation' : 'image_generation_visible_card';
      label = attachmentCount ? 'ตีความภาพที่แนบจริง' : 'สร้าง/แสดงภาพในแชท';
      primaryResult = attachmentCount ? 'ภาพต้องขึ้นใน bubble ของผู้ถาม และคำตอบต้องอธิบายจากภาพที่ส่งเข้า Vision' : 'ต้องมีการ์ดภาพขึ้นในห้องแชท กดภาพแล้วเปิดป๊อบอัพและบันทึกได้';
      renderRule = 'ถ้ามีภาพ ให้แสดงภาพก่อน ถ้าเป็นการอ่านภาพให้ตอบจากสิ่งที่เห็นจริง ถ้าไม่มี Vision/API ให้บอกข้อจำกัดตรง ๆ';
    }
    if(forms.indexOf('code') !== -1){
      type = 'code_block_with_rendered_result';
      label = 'โค้ด + ผลลัพธ์จากโค้ด';
      primaryResult = 'ต้องมี code block แรก พร้อมปุ่ม Copy / Download / Result / ทดลองใช้ก่อนติดตั้ง และถ้าเป็น WordPress ต้องมี Sandbox ให้ลองกด/ดู UI ก่อนติดตั้ง';
      renderRule = 'แสดงโค้ดเป็น code fence ชัดเจน และถ้ามี HTML/CSS/JS ให้จัดกลุ่มเพื่อ Preview/Result ได้';
    }
    if(forms.indexOf('wordpress_system') !== -1){
      type = 'wordpress_patch_or_plugin_package';
      label = 'แพตช์/ระบบ WordPress ที่เห็นผลได้';
      primaryResult = 'ต้องสรุปไฟล์ที่แก้ จุดที่เพิ่ม วิธีติดตั้ง/ทดสอบ และสถานะว่าพร้อมใช้งานหรือยัง';
      renderRule = 'ห้ามตอบแค่แนวคิด ต้องระบุของเดิมยังอยู่ไหม สิ่งใหม่ที่เพิ่ม และวิธีเช็กผลบน WordPress จริง';
    }
    if(promptIssue && wantsVisible){
      type = 'prompt_to_visible_result_resolver';
      label = 'แปลง Prompt เป็นผลลัพธ์ที่ต้องขึ้นจริง';
      primaryResult = 'หลังประมวล Prompt ต้องรู้ว่าผู้ใช้ต้องการเห็น output อะไร และต้องสร้าง/แสดง output นั้น ไม่ใช่จบแค่ข้อความสถานะ';
      renderRule = 'บังคับขึ้นหัวข้อ “ผลลัพธ์ที่ต้องแสดง” ก่อนคำอธิบาย แล้วตามด้วยจุดแก้หรือสิ่งที่สร้าง';
    } else if(wantsVisible && type === 'direct_answer'){
      type = 'visible_answer_result';
      label = 'ผลลัพธ์ที่ต้องแสดงให้เห็น';
      primaryResult = 'ต้องแสดงคำตอบ/การ์ด/สรุปผลที่ผู้ใช้เห็นได้ทันที ไม่ปล่อยเป็นพื้นที่ว่าง';
      renderRule = 'ถ้าไม่มีไฟล์หรือภาพจริง ให้แสดงการ์ดสรุปผลพร้อมเหตุผลและ next check แบบสั้น';
    }
    var confidence = clamp(Math.round((meta.clarityScore || 50)*0.34 + (forms.length*9) + (wantsVisible ? 24 : 8) + (promptIssue ? 14 : 0) + (intent !== 'general_answer' ? 12 : 0)), 0, 100);
    var checks = [];
    if(type.indexOf('image') !== -1 || type.indexOf('vision') !== -1){ checks.push('image_visible_in_chat'); checks.push('click_popup_save'); }
    if(type.indexOf('code') !== -1){ checks.push('code_block_first'); checks.push('result_toggle'); }
    if(type.indexOf('wordpress') !== -1){ checks.push('wp_file_patch'); checks.push('install_test'); }
    if(!checks.length){ checks.push('direct_visible_answer'); }
    return {version:'result_resolver_v7.2.5', type:type, label:label, primaryResult:primaryResult, renderRule:renderRule, fallback:fallback, confidence:confidence, checks:uniqueList(checks)};
  }
  function formatDesiredOutputResolver(ctx){
    ctx = ctx || buildAllFormContext('');
    var r = ctx.desiredOutput || resolveDesiredOutput(ctx.original || '', ctx);
    return [
      '[AiRA Result Resolver v7.2.5]',
      'Output type: ' + r.type,
      'Label: ' + r.label,
      'Primary result that must be visible: ' + r.primaryResult,
      'Render rule: ' + r.renderRule,
      'Fallback rule: ' + r.fallback,
      'Visible checks: ' + (r.checks || []).join(', '),
      'Confidence: ' + r.confidence + '/100'
    ].join('\n');
  }
  function desiredOutputHtml(ctx){
    ctx = ctx || state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '');
    var r = ctx.desiredOutput || resolveDesiredOutput(ctx.original || '', ctx);
    var checks = (r.checks || []).map(function(x){ return '<span>'+esc(x.replace(/_/g,' '))+'</span>'; }).join('');
    return '<div class="desired-output-card"><div class="desired-output-head"><span>'+icon('preview')+'</span><div><b>Result Resolver</b><small>'+esc(r.label)+' · '+esc(r.confidence)+'/100</small></div></div>'+
      '<p><b>ผลลัพธ์ที่ต้องขึ้น</b><span>'+esc(r.primaryResult)+'</span></p>'+
      '<p><b>กฎการแสดงผล</b><span>'+esc(r.renderRule)+'</span></p>'+
      '<div class="desired-output-checks">'+checks+'</div></div>';
  }
  function replyAlreadyShowsResult(reply){
    return hasRe(/ผลลัพธ์|เสร็จแล้ว|แก้แล้ว|เพิ่มแล้ว|ไฟล์ใหม่|ดาวน์โหลด|Download|code block|```|ภาพ|Preview|Result|สถานะใช้งาน|วิธีทดสอบ|ของเดิมยังอยู่|สิ่งที่เพิ่ม/iu, reply);
  }
  function normalizeReplyWithDesiredOutput(reply, ctx){
    reply = clean(reply);
    ctx = ctx || buildAllFormContext('');
    var r = ctx.desiredOutput || resolveDesiredOutput(ctx.original || '', ctx);
    var mustShow = hasRe(/ผลลัพธ์|ผลลัพ|แสดง|ขึ้น|ไม่ขึ้น|ไม่แสดง|preview|result|output|ตีความ|prompt/iu, ctx.original || '') || (r.type && r.type !== 'direct_answer');
    if(!mustShow || replyAlreadyShowsResult(reply)){ return reply; }
    var prefix = 'ผลลัพธ์ที่ต้องแสดง: ' + r.primaryResult + '\n\nสถานะการตีความ: ' + r.label + ' · ความมั่นใจ ' + r.confidence + '/100\n\n';
    return prefix + reply;
  }

  function formatQuestionReaderBridge(ctx){
    ctx = ctx || buildAllFormContext('');
    var qr = ctx.questionReading || buildQuestionReadingResult(ctx.original || '', ctx);
    return [
      '[AiRA Auto Question Reader v7.2.0]',
      'Normal typing rule: ' + qr.normalTyping,
      'Route: ' + qr.route,
      'Connected systems: ' + (qr.connectors || []).join(' → '),
      'Answer plan: ' + qr.answerPlan,
      'Output rule: ' + qr.outputRule,
      'Confidence: ' + qr.confidence + '/100'
    ].join('\n');
  }
  function questionReaderHtml(ctx){
    ctx = ctx || state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '');
    var qr = ctx.questionReading || buildQuestionReadingResult(ctx.original || '', ctx);
    var connectors = (qr.connectors || []).slice(0, 8).map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('');
    return '<div class="question-reader-card"><div class="question-reader-head"><span>'+icon('brain')+'</span><div><b>อ่านคำถามอัตโนมัติ</b><small>'+esc(qr.route)+' · '+esc(qr.confidence)+'/100</small></div></div>'+
      '<p>'+esc(qr.normalTyping)+'</p><div class="question-reader-flow">'+connectors+'</div>'+
      '<p><b>แผนตอบ</b><span>'+esc(qr.answerPlan)+'</span></p></div>';
  }

  function formatInterpretationResult(ctx){
    ctx = ctx || buildAllFormContext('');
    var it = ctx.interpretation || buildInterpretationResult(ctx.original || '', ctx);
    return [
      '[AiRA Interpretation Result v7.2.0]',
      'Mode: ' + it.label + ' (' + it.mode + ')',
      'Meaning: ' + it.meaning,
      'Target: ' + it.target,
      'Expected output: ' + it.expectedOutput,
      'Action focus: ' + it.actionFocus,
      'Response length: ' + it.responseLength,
      'Confidence: ' + it.confidence + '/100'
    ].join('\n');
  }
  function interpretationResultHtml(ctx){
    ctx = ctx || state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '');
    var it = ctx.interpretation || buildInterpretationResult(ctx.original || '', ctx);
    return '<div class="interpretation-result-card"><div class="interpretation-head"><span>'+icon('brain')+'</span><div><b>ผลลัพธ์การตีความ</b><small>'+esc(it.label)+' · '+esc(it.confidence)+'/100</small></div></div>'+
      '<p><b>ความหมายที่ระบบอ่านได้</b><span>'+esc(it.meaning)+'</span></p>'+
      '<p><b>เป้าหมาย/บริบท</b><span>'+esc(it.target)+'</span></p>'+
      '<p><b>ผลลัพธ์ที่ควรแสดง</b><span>'+esc(it.expectedOutput)+'</span></p></div>';
  }
  function gptCommunicationProfile(ctx){
    ctx = ctx || buildAllFormContext('');
    var intent = ctx.intent || 'general_answer';
    var forms = ctx.forms || [];
    var profile = {
      mode:'direct_helpful',
      opening:'ตอบเข้าเรื่องทันที ไม่เกริ่นยาว',
      structure:'สรุปสั้นก่อน แล้วแยกข้อเฉพาะที่ทำได้จริง',
      tone:'อบอุ่น สุภาพ ตรงประเด็น คล้าย GPT แต่ไม่เยิ่นเย้อ',
      recovery:'ถ้าคำตอบเสี่ยงไม่ตรงโจทย์ ให้เรียบเรียงใหม่จากคำสั่งล่าสุด',
      avoid:'ไม่ตอบซ้ำ ไม่ลากเรื่องเก่าไม่เกี่ยวข้อง ไม่ถามกลับถ้าไม่จำเป็น'
    };
    if(intent === 'fix_debug'){
      profile.mode = 'debug_solver';
      profile.structure = 'ปัญหา → สาเหตุ → จุดที่แก้ → วิธีทดสอบ → สถานะใช้งาน';
    } else if(intent === 'build_add'){
      profile.mode = 'builder_update';
      profile.structure = 'ของเดิมยังอยู่ → สิ่งที่เพิ่ม → วิธีใช้ → ตรวจบั๊ก → ไฟล์/สถานะ';
    } else if(intent === 'continue_previous'){
      profile.mode = 'continue_context';
      profile.opening = 'ต่อจากบริบทล่าสุดทันที ไม่เริ่มอธิบายใหม่ทั้งหมด';
      profile.structure = 'ยึดไฟล์/เวอร์ชันล่าสุด → แก้เฉพาะคำสั่งใหม่ → สรุปผล';
    } else if(intent === 'explain_teach'){
      profile.mode = 'teacher_simple';
      profile.structure = 'คำตอบตรง → อธิบายง่าย → ตัวอย่างสั้น → ข้อควรระวัง';
    } else if(intent === 'analyze_calculate'){
      profile.mode = 'analyst';
      profile.structure = 'ผลประเมิน/ตัวเลขก่อน → เหตุผล → ข้อเสนอแก้ไข';
    }
    if(forms.indexOf('number_metric') !== -1){ profile.structure += ' · ถ้ามีตัวเลขให้คำนวณตรงและไม่แต่งคำจนค่าผิด'; }
    if(forms.indexOf('ui_ux') !== -1){ profile.structure += ' · สำหรับ UI/UX ให้ระบุผลต่อ mobile/desktop/browser'; }
    if(forms.indexOf('wordpress_system') !== -1 || forms.indexOf('code') !== -1){ profile.structure += ' · สำหรับโค้ดให้ระบุไฟล์/หน้าที่/วิธีทดสอบ'; }
    return profile;
  }
  function formatGPTCommunicationProfile(profile){
    profile = profile || gptCommunicationProfile();
    return [
      '[GPT-Like Communication Skill v7.2.0 + Auto Question/System Answer]',
      'Mode: ' + profile.mode,
      'Opening: ' + profile.opening,
      'Structure: ' + profile.structure,
      'Tone: ' + profile.tone,
      'Recovery: ' + profile.recovery,
      'Avoid: ' + profile.avoid
    ].join('\n');
  }


  function detectImplicitCommunicationNeeds(text, meta){
    meta = meta || {};
    var t = clean(text);
    var needs = [];
    var signals = [];
    if(hasRe(/ไม่ขึ้น|ไม่แสดง|ไม่ได้|ยังไม่|ไม่มา|ค้าง|ผิด|งง|ทำไม/iu, t)){ needs.push('ต้องการให้ผลลัพธ์ขึ้นจริง ไม่ใช่แค่อธิบาย'); signals.push('blocked_result'); }
    if(hasRe(/ต่อ|กลับมา|เอา.*กลับ|เหมือนเดิม|จากเดิม|ปรับไปเรื่อย|จนกว่าจะพอใจ/iu, t)){ needs.push('ต้องการความต่อเนื่องจากเวอร์ชันล่าสุด'); signals.push('continuity_need'); }
    if(hasRe(/สั้น|ก่อน|เร็ว|เลย|แค่|ปกติ/iu, t)){ needs.push('ต้องการคำตอบสั้นก่อนและทำงานทันที'); signals.push('short_fast_need'); }
    if(hasRe(/เก่ง|ฉลาด|เข้าใจ|ตีความ|จิตใต้สำนึก|สื่อสาร|คล้าย gpt/iu, t)){ needs.push('ต้องการให้ระบบอ่านเจตนาแฝงและสื่อสารเหมือนผู้ช่วยมืออาชีพ'); signals.push('communication_upgrade'); }
    if(hasRe(/โค้ด|code|plugin|wordpress|อัปเกรด|ติดตั้ง|module|component|theme|widget|block/iu, t)){ needs.push('ต้องการโค้ดที่ครบ ใช้ต่อได้ ติดตั้ง/อัปเกรดได้'); signals.push('code_deliverable_need'); }
    if(hasRe(/ภาพ|รูป|preview|result|ผลลัพธ์/iu, t)){ needs.push('ต้องการ output ที่มองเห็นได้จริงบนหน้าจอ'); signals.push('visible_output_need'); }
    if(!needs.length){ needs.push('ต้องการคำตอบตรงประเด็น ใช้งานได้ทันที'); signals.push('direct_help_need'); }
    var confidence = clamp(Math.round((meta.clarityScore || 50)*0.35 + signals.length*14 + ((meta.continuity||'').indexOf('high')===0 ? 14 : 4) + ((meta.intent||'') !== 'general_answer' ? 12 : 4)), 0, 100);
    var responseMove = 'ตอบผลลัพธ์ที่ใช้ได้ก่อน แล้วค่อยอธิบายสั้น ๆ';
    if(signals.indexOf('blocked_result') !== -1) responseMove = 'เริ่มจากสาเหตุที่ผลลัพธ์ไม่ขึ้น แล้วแก้ให้ output แสดงจริง';
    if(signals.indexOf('code_deliverable_need') !== -1) responseMove = 'ส่งโค้ด/ไฟล์เต็ม พร้อมวิธีทดสอบ ไม่ส่งแค่แนวคิด';
    return {
      version:'deep_communication_v7.3.5',
      label:'เจตนาแฝงแบบปลอดภัย',
      needs:uniqueList(needs),
      signals:uniqueList(signals),
      responseMove:responseMove,
      confidence:confidence,
      safety:'privacy-safe: this is not mind-reading and does not infer sensitive identity, health, age, gender, religion, politics, or private traits'
    };
  }
  function formatDeepCommunicationSkill(ctx){
    ctx = ctx || buildAllFormContext('');
    var s = ctx.subtext || detectImplicitCommunicationNeeds(ctx.original || '', ctx);
    return [
      '[AiRA Deep Communication Skill v7.3.5]',
      'Purpose: read privacy-safe implicit need/subtext behind short or repeated user wording before answering.',
      'Label: ' + s.label,
      'Detected signals: ' + (s.signals || []).join(', '),
      'Likely practical needs: ' + (s.needs || []).join(' | '),
      'Response move: ' + s.responseMove,
      'Confidence: ' + s.confidence + '/100',
      'Safety: ' + s.safety
    ].join('\n');
  }
  function deepCommunicationHtml(ctx){
    ctx = ctx || state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '');
    var s = ctx.subtext || detectImplicitCommunicationNeeds(ctx.original || '', ctx);
    var chips = (s.needs || []).slice(0,4).map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('');
    return '<div class="deep-communication-card"><div class="deep-communication-head"><span>'+icon('brain')+'</span><div><b>ทักษะสื่อสารลึก</b><small>'+esc(s.label)+' · '+esc(s.confidence)+'/100</small></div></div>'+ 
      '<p><b>สิ่งที่ผู้ถามน่าจะต้องการ</b><span>'+esc(s.responseMove)+'</span></p><div class="deep-communication-chips">'+chips+'</div>'+ 
      '<p class="deep-communication-safe">อ่านเฉพาะเจตนาเชิงงานจากข้อความ ไม่เดาข้อมูลส่วนตัวหรือข้อมูลอ่อนไหว</p></div>';
  }
  function buildGlobalCommunicationLens(text, meta){
    meta = meta || {};
    var t = clean(text);
    var layers = ['literal_meaning','task_intent','desired_output'];
    if(hasRe(/ทำไม|ไม่ได้|ยังไม่|ไม่ขึ้น|ไม่มา|ค้าง|ผิด/iu, t)) layers.push('friction_or_blocker');
    if(hasRe(/สั้น|เร็ว|เลย|ปกติ|แค่/iu, t)) layers.push('fast_clear_answer');
    if(hasRe(/โลก|ทุกการสื่อสาร|ทุกภาษา|ทุกอาชีพ|ทุกเพศ|ทุกวัย|มนุษยชาติ|global|world/iu, t)) layers.push('global_plain_language');
    if(hasRe(/ลูกค้า|ผู้ใช้|นักเรียน|นักศึกษา|ทีม|แอดมิน|developer|designer|manager|ผู้บริหาร/iu, t)) layers.push('audience_adaptation');
    if(hasRe(/โค้ด|ระบบ|plugin|wordpress|api|server/iu, t)) layers.push('technical_translation');
    var responsePolicy = [
      'อ่านคำพูดตรงตัวก่อน แล้วแปลงเป็นผลลัพธ์ที่ผู้ใช้ต้องเห็น',
      'ปรับภาษาให้เข้าใจง่ายสำหรับคนทั่วไป แต่เก็บรายละเอียดเทคนิคให้ทีมพัฒนาใช้ต่อได้',
      'ไม่เดาข้อมูลส่วนตัวหรือข้อมูลอ่อนไหว ถ้าต้องปรับตามกลุ่มคนให้ปรับจากบริบทงานเท่านั้น',
      'ถ้าข้อความสั้น/ไม่ครบ ให้ใช้บริบทล่าสุดอย่างปลอดภัยและตอบเป็นงานที่ทำต่อได้ทันที'
    ];
    var score = clamp(62 + layers.length*6 + ((meta.continuity||'').indexOf('high')===0 ? 8 : 0) + ((meta.intent||'') !== 'general_answer' ? 8 : 0), 0, 100);
    return {version:'global_communication_lens_v7.3.5', label:'Global Communication Lens', layers:uniqueList(layers), responsePolicy:responsePolicy, score:score, safety:'does not literally understand every person or infer subconscious/private traits; it uses safe communication cues and task context'};
  }
  function formatGlobalCommunicationSkill(ctx){
    ctx = ctx || buildAllFormContext('');
    var g = ctx.globalCommunication || buildGlobalCommunicationLens(ctx.original || '', ctx);
    return [
      '[AiRA Global Communication Lens v7.3.5]',
      'Purpose: communicate like a GPT-style universal helper across plain language, technical language, short commands, frustrated reports, and cross-role work contexts.',
      'Layers: ' + (g.layers || []).join(', '),
      'Response policy: ' + (g.responsePolicy || []).join(' | '),
      'Score: ' + g.score + '/100',
      'Safety: ' + g.safety
    ].join('\n');
  }
  function globalCommunicationHtml(ctx){
    ctx = ctx || state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '');
    var g = ctx.globalCommunication || buildGlobalCommunicationLens(ctx.original || '', ctx);
    var chips = (g.layers || []).slice(0,6).map(function(x){ return '<span>'+esc(x.replace(/_/g,' '))+'</span>'; }).join('');
    return '<div class="global-communication-card"><div class="global-communication-head"><span>'+icon('brain')+'</span><div><b>Global Communication</b><small>'+esc(g.score)+'/100 · เข้าใจรูปแบบการสื่อสารเชิงงาน</small></div></div>'+ 
      '<div class="global-communication-chips">'+chips+'</div><p>'+esc((g.responsePolicy||[]).slice(0,2).join(' · '))+'</p><p class="global-communication-safe">ปรับการสื่อสารจากบริบทงานเท่านั้น ไม่อ้างว่าอ่านใจหรือรู้ทุกคนจริง</p></div>';
  }

  function buildCodeMasterProfile(text, meta){
    meta = meta || {};
    var forms = meta.forms || detectContentForms(text);
    var isCode = forms.indexOf('code') !== -1 || forms.indexOf('wordpress_system') !== -1 || forms.indexOf('software_system') !== -1 || hasRe(/โค้ด|code|plugin|wordpress|php|js|css|html|api|module|component|widget|theme|block|react|node|python|sql|database|docker|vps|server|mobile|app/iu, text);
    var tracks = ['complete_output'];
    if(forms.indexOf('wordpress_system') !== -1 || hasRe(/wordpress|plugin|ปลั๊กอิน|wp-|shortcode|elementor|gutenberg/iu, text)) tracks.push('wordpress_security');
    if(hasRe(/ui|ux|responsive|composer|popup|mobile|desktop|layout|ปุ่ม/iu, text)) tracks.push('responsive_ui');
    if(hasRe(/ปุ่ม|คำสั่ง|ช่วยเหลือ|คุณค่า|human|value|benefit|accessibility|aria/iu, text)) tracks.push('human_value_guard');
    if(hasRe(/api|key|endpoint|provider|openai|gemini|claude|search|rest|graphql|webhook|oauth/iu, text)) tracks.push('api_integration');
    if(forms.indexOf('software_system') !== -1 || hasRe(/react|node|python|sql|database|docker|vps|server|linux|mobile|desktop|firebase|supabase/iu, text)) tracks.push('universal_system_architecture');
    if(hasRe(/บั๊ก|bug|error|ไม่ทำงาน|ไม่ขึ้น|ค้าง|แก้/iu, text)) tracks.push('debug_test');
    var checklist = [
      'ส่ง code block เต็ม ไม่ตัดกลาง',
      'ระบุไฟล์/หน้าที่/ความสัมพันธ์ระบบ',
      'รักษาของเดิมและเพิ่มเฉพาะสิ่งใหม่',
      'ตรวจ syntax/security/responsive/browser support',
      'เชื่อมความสัมพันธ์ระบบ: frontend/backend/database/API/auth/storage/deploy/log',
      'ให้วิธีติดตั้ง อัปเกรด ทดสอบ และ rollback แบบสั้น',
      'ทุกปุ่ม/คำสั่งต้องมีคุณค่าช่วยผู้ใช้จริง มี feedback และทดสอบได้'
    ];
    if(tracks.indexOf('wordpress_security') !== -1){ checklist.push('WordPress: capability, nonce, sanitize, escape, REST permission_callback, uninstall guard'); }
    if(tracks.indexOf('universal_system_architecture') !== -1){ checklist.push('Universal systems: file map, data flow, API contracts, environment variables, error handling, deployment notes'); }
    if(!isCode){ checklist = ['ถ้าไม่ใช่งานโค้ด ให้ตอบปกติ แต่พร้อมแปลงเป็นแผนโค้ดเมื่อผู้ใช้ขอ']; }
    var score = clamp(54 + tracks.length*10 + (isCode ? 18 : 0) + ((meta.intent||'') === 'fix_debug' ? 8 : 0), 0, 100);
    return {version:'code_master_v7.3.5', isCode:isCode, tracks:uniqueList(tracks), checklist:uniqueList(checklist), score:score};
  }
  function formatCodeMasterProfile(ctx){
    ctx = ctx || buildAllFormContext('');
    var c = ctx.codeMaster || buildCodeMasterProfile(ctx.original || '', ctx);
    return [
      '[AiRA Code Master Skill v7.3.5]',
      'Mode: ' + (c.isCode ? 'senior_code_builder' : 'standby_for_code_tasks'),
      'Tracks: ' + (c.tracks || []).join(', '),
      'Checklist: ' + (c.checklist || []).join(' | '),
      'Output rule: If the task is code/WordPress/upgrade/next, return complete updated code fences and keep Install/Upgrade/Next UI compatible.',
      'Quality score target: ' + c.score + '/100'
    ].join('\n');
  }
  function codeMasterHtml(ctx){
    ctx = ctx || state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '');
    var c = ctx.codeMaster || buildCodeMasterProfile(ctx.original || '', ctx);
    var chips = (c.tracks || []).map(function(x){ return '<span>'+esc(x.replace(/_/g,' '))+'</span>'; }).join('');
    return '<div class="code-master-card"><div class="code-master-head"><span>'+icon('code')+'</span><div><b>Code Master</b><small>'+esc(c.score)+'/100 · '+esc(c.isCode ? 'พร้อมเขียนโค้ดเต็ม' : 'standby')+'</small></div></div>'+ 
      '<div class="code-master-chips">'+chips+'</div><p>'+esc((c.checklist||[]).slice(0,3).join(' · '))+'</p></div>';
  }

  function buildUniversalSystemCodeProfile(text, meta){
    meta = meta || {};
    var forms = meta.forms || detectContentForms(text);
    var domains = [];
    if(forms.indexOf('wordpress_system') !== -1 || hasRe(/wordpress|plugin|elementor|gutenberg|woocommerce|theme/iu, text)) domains.push('WordPress');
    if(hasRe(/html|css|js|javascript|react|vue|next|frontend|ui|ux/iu, text)) domains.push('Frontend');
    if(hasRe(/php|node|express|python|django|flask|laravel|backend|server/iu, text)) domains.push('Backend');
    if(hasRe(/mysql|sql|database|db|firebase|supabase|storage/iu, text)) domains.push('Database');
    if(hasRe(/api|rest|graphql|webhook|oauth|key|endpoint/iu, text)) domains.push('API');
    if(hasRe(/linux|vps|docker|deploy|server|nginx|apache|cloudflare/iu, text)) domains.push('DevOps');
    if(hasRe(/android|ios|flutter|mobile|electron|desktop/iu, text)) domains.push('App');
    if(!domains.length) domains.push('General system');
    var rules = [
      'เริ่มจากผลลัพธ์ที่ต้องใช้งานได้จริง ไม่ใช่แค่แนวคิด',
      'เขียนโค้ดเต็มเมื่อขอสร้าง/แก้ ไม่ตัดกลาง และรักษาของเดิม',
      'ระบุไฟล์ หน้าที่ ความสัมพันธ์ และ flow ข้อมูลให้ชัด',
      'ตรวจ security, error handling, compatibility, performance และ rollback',
      'ถ้าความรู้ระบบใดต้องอัปเดต/ต้องต่อ API ให้บอกข้อจำกัดจริงและวิธีตรวจ'
    ];
    var score = clamp(64 + domains.length*5 + (((meta.codeMaster||{}).isCode) ? 15 : 0) + ((meta.intent||'') === 'fix_debug' ? 8 : 0), 0, 100);
    return {version:'universal_system_code_engine_v7.3.5', domains:uniqueList(domains), rules:rules, score:score};
  }
  function formatUniversalSystemCodeSkill(ctx){
    ctx = ctx || buildAllFormContext('');
    var u = ctx.universalCode || buildUniversalSystemCodeProfile(ctx.original || '', ctx);
    return [
      '[AiRA Universal System Code Engine v7.3.5]',
      'Purpose: improve code/system generation across WordPress, frontend, backend, database, API, DevOps, and app-style workflows when relevant.',
      'Domains: ' + (u.domains || []).join(', '),
      'Rules: ' + (u.rules || []).join(' | '),
      'Score: ' + u.score + '/100',
      'Limit: it cannot literally know every private/proprietary system unless the user provides specs or APIs; it should ask only when a missing detail blocks safe execution.'
    ].join('\n');
  }
  function universalCodeHtml(ctx){
    ctx = ctx || state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '');
    var u = ctx.universalCode || buildUniversalSystemCodeProfile(ctx.original || '', ctx);
    var chips = (u.domains || []).map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('');
    return '<div class="universal-code-card"><div class="universal-code-head"><span>'+icon('module')+'</span><div><b>Universal Code Engine</b><small>'+esc(u.score)+'/100 · เข้าใจความสัมพันธ์ระบบ</small></div></div><div class="universal-code-chips">'+chips+'</div><p>'+esc((u.rules||[]).slice(0,3).join(' · '))+'</p></div>';
  }

  function buildAutoEvolutionCoreProfile(text, meta){
    meta = meta || {};
    var forms = meta.forms || [];
    var intent = meta.intent || detectIntent(text || '');
    var continuity = meta.continuity || 'low_new_topic';
    var clarity = Number(meta.clarityScore || 0);
    var complexity = Number(meta.complexityScore || 0);
    var baseCommunication = Number(meta.communicationScore || 0);
    var globalScore = Number(((meta.globalCommunication || {}).score) || 0);
    var codeScore = Number(((meta.codeMaster || {}).score) || 0);
    var universalScore = Number(((meta.universalCode || {}).score) || 0);
    var isSystemWork = forms.indexOf('wordpress_system') !== -1 || forms.indexOf('code') !== -1 || forms.indexOf('file_artifact') !== -1;
    var isContinue = continuity.indexOf('high') === 0 || intent === 'continue_previous';
    var contentScore = clamp(Math.round(50 + clarity*0.28 + (isContinue ? 12 : 0) + (isSystemWork ? 10 : 0) + forms.length*3), 0, 100);
    var progressScore = clamp(Math.round(46 + (isContinue ? 20 : 8) + (intent === 'build_add' ? 14 : 0) + (intent === 'fix_debug' ? 12 : 0) + Math.min(16, forms.length*4)), 0, 100);
    var qualityScore = clamp(Math.round(52 + baseCommunication*0.22 + globalScore*0.18 + (isSystemWork ? 10 : 0) + (clarity > 65 ? 8 : 0)), 0, 100);
    var performanceScore = clamp(Math.round(72 - complexity*0.12 + (isContinue ? 6 : 0) + (isSystemWork ? 5 : 0)), 0, 100);
    var alignmentScore = clamp(Math.round(48 + baseCommunication*0.22 + progressScore*0.18 + ((meta.questionReading||{}).connectors||[]).length*4), 0, 100);
    var score = clamp(Math.round((contentScore*0.22) + (progressScore*0.2) + (qualityScore*0.24) + (performanceScore*0.16) + (alignmentScore*0.18)), 0, 100);
    var progressMode = isContinue ? 'continue_and_improve_existing_system' : (intent === 'fix_debug' ? 'repair_then_raise_quality' : (intent === 'build_add' ? 'add_then_integrate' : 'answer_then_improve'));
    var syncTargets = ['Context Reader','Answer Router','Prompt Processor','Result Resolver','Memory/Rooms context','Code Block context'];
    if(forms.indexOf('wordpress_system') !== -1) syncTargets.push('WordPress plugin/admin context');
    if(forms.indexOf('ui_ux') !== -1) syncTargets.push('UI/UX responsive context');
    if(forms.indexOf('voice_audio') !== -1) syncTargets.push('Voice/Audio context');
    if(forms.indexOf('image_visual') !== -1) syncTargets.push('Image/Preview context');
    return {
      version:'auto_evolution_core_v7.5.8.7_no_button_touch',
      score:score,
      contentScore:contentScore,
      progressScore:progressScore,
      qualityScore:qualityScore,
      performanceScore:performanceScore,
      alignmentScore:alignmentScore,
      progressMode:progressMode,
      syncTargets:uniqueList(syncTargets),
      rules:[
        'Do not mutate buttons, data-action attributes, action router, click/touch handlers, or existing UI controls in this evolution layer.',
        'Preserve existing systems first, then improve content direction, communication quality, answer usefulness, and performance automatically.',
        'Each response should advance beyond the previous round: current state, improvement added, remaining risk, next test.',
        'Quality lift: remove repetition, avoid unsupported claims, explain constraints honestly, and include a realistic verification path.',
        'Performance lift: keep prompts compact, route only relevant systems, and avoid duplicate UI/status text.'
      ],
      outputContract:[
        'เริ่มจากผลลัพธ์ที่ผู้ใช้ต้องใช้จริง',
        'เชื่อมกับของเดิมที่เกี่ยวข้องเท่านั้น',
        'บอกสิ่งที่พัฒนาขึ้นแบบวัดได้',
        'ระบุวิธีทดสอบหรือวิธีตรวจคุณภาพ',
        'ไม่เพิ่มปุ่มใหม่และไม่เปลี่ยนพฤติกรรมปุ่มเดิม'
      ]
    };
  }
  function formatAutoEvolutionCore(ctx){
    ctx = ctx || buildAllFormContext('');
    var e = ctx.autoEvolutionCore || buildAutoEvolutionCoreProfile(ctx.original || '', ctx);
    return [
      '[AiRA Auto Evolution Core v7.5.8.7 - no button touch]',
      'Purpose: automatically improve content progress, communication coherence, answer quality, and answer performance while preserving existing systems.',
      'Important lock: do not add, remove, rename, rebind, disable, or repair buttons/action router from this layer.',
      'Mode: ' + e.progressMode,
      'Scores: overall=' + e.score + '/100, content=' + e.contentScore + '/100, progress=' + e.progressScore + '/100, quality=' + e.qualityScore + '/100, performance=' + e.performanceScore + '/100, alignment=' + e.alignmentScore + '/100',
      'Sync targets: ' + (e.syncTargets || []).join(' > '),
      'Rules: ' + (e.rules || []).join(' | '),
      'Output contract: ' + (e.outputContract || []).join(' | ')
    ].join('\n');
  }
  function autoEvolutionCoreHtml(ctx){
    ctx = ctx || state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '');
    var e = ctx.autoEvolutionCore || buildAutoEvolutionCoreProfile(ctx.original || '', ctx);
    var chips = (e.syncTargets || []).slice(0,6).map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('');
    return '<div class="auto-evolution-card"><div class="auto-evolution-head"><span>'+icon('spark')+'</span><div><b>Auto Evolution Core</b><small>'+esc(e.score)+'/100 · พัฒนาเนื้อหา/การสื่อสาร/คุณภาพ/ประสิทธิภาพ โดยไม่แตะปุ่ม</small></div></div><div class="auto-evolution-grid"><p><b>Content</b><small>'+esc(e.contentScore)+'/100</small></p><p><b>Progress</b><small>'+esc(e.progressScore)+'/100</small></p><p><b>Quality</b><small>'+esc(e.qualityScore)+'/100</small></p><p><b>Performance</b><small>'+esc(e.performanceScore)+'/100</small></p></div><div class="auto-evolution-chips">'+chips+'</div><p>'+esc((e.outputContract||[]).slice(0,3).join(' · '))+'</p></div>';
  }

  function buildThoughtPromptCompilerProfile(text, meta){
    meta = meta || {};
    var forms = meta.forms || [];
    var intent = meta.intent || detectIntent(text || '');
    var connectors = (((meta.questionReading || {}).connectors) || []).slice(0,10);
    var shortText = trim(text || '').replace(/\s+/g,' ');
    var isLoose = shortText.length > 0 && shortText.length < 90 && !/[?？]|ครับ|ค่ะ|ช่วย|please|how|what|why/i.test(shortText);
    var commandScope = uniqueList(connectors.concat(['Context Reader','Prompt Processor','Result Resolver','Auto Evolution Core','Quality Guard','Performance Guard']));
    var autoIntent = intentGoal(intent);
    var score = clamp(Math.round(62 + (isLoose ? 10 : 5) + commandScope.length*3 + ((meta.continuity||'').indexOf('high')===0 ? 10 : 0) + ((meta.clarityScore||0)*0.08)), 0, 100);
    var compilerMode = isLoose ? 'loose_thought_to_structured_prompt' : 'message_to_structured_prompt';
    var promptGoal = 'แปลงข้อความที่ผู้ใช้พิมพ์ให้เป็น prompt ทำงานจริง โดยดึงระบบที่เกี่ยวข้องทั้งหมดมาประมวลผลอัตโนมัติ และยังยึดคำสั่งล่าสุดเป็นหลัก';
    if(forms.indexOf('ui_ux') !== -1) promptGoal = 'แปลงข้อความเป็น prompt แก้ UI/UX จริง โดยโฟกัสตำแหน่ง composer/header/keyboard/responsive และวิธีทดสอบ';
    if(forms.indexOf('wordpress_system') !== -1 || forms.indexOf('code') !== -1 || forms.indexOf('software_system') !== -1) promptGoal = 'แปลงข้อความเป็น prompt งานระบบ/โค้ด โดยดึงโครงสร้างไฟล์ ความสัมพันธ์ระบบ QC และสถานะใช้งานเข้ามาอัตโนมัติ';
    return {
      version:'thought_prompt_compiler_v7.5.8.10',
      mode:compilerMode,
      score:score,
      autoIntent:autoIntent,
      promptGoal:promptGoal,
      commandScope:commandScope,
      rules:[
        'User can type a rough thought, short phrase, complaint, or normal message; the system converts it to a structured working prompt automatically.',
        'Pull only relevant existing command systems into processing: context, prompt processor, result resolver, memory/rooms, code, WordPress, UI/UX, file, voice, image, API, debug, and quality/performance guards when relevant.',
        'Do not expose hidden reasoning; show the final useful answer/result, not the internal compiled prompt unless the user asks to export/debug it.',
        'Keep latest user text as the decision anchor; use previous room context only as support.',
        'Do not add, remove, rename, or rebind buttons from this compiler layer.'
      ]
    };
  }
  function formatThoughtPromptCompiler(ctx){
    ctx = ctx || buildAllFormContext('');
    var p = ctx.thoughtPromptCompiler || buildThoughtPromptCompilerProfile(ctx.original || '', ctx);
    return [
      '[AiRA Thought Prompt Compiler v7.5.8.10]',
      'Concept: แค่คิดและพิมพ์ข้อความ ระบบจะดึงคำสั่ง/ระบบที่เกี่ยวข้องทั้งหมดมาประมวลผล แล้วแปลงเป็น prompt ทำงานอัตโนมัติก่อนตอบ',
      'Mode: ' + p.mode,
      'Score: ' + p.score + '/100',
      'Auto intent: ' + p.autoIntent,
      'Prompt goal: ' + p.promptGoal,
      'Command scope: ' + (p.commandScope || []).join(' > '),
      'Rules: ' + (p.rules || []).join(' | ')
    ].join('\n');
  }
  function compileThoughtToAutomaticPrompt(userText, ctx){
    ctx = ctx || buildAllFormContext(userText || '');
    var p = ctx.thoughtPromptCompiler || buildThoughtPromptCompilerProfile(userText || '', ctx);
    var desired = ctx.desiredOutput || resolveDesiredOutput(userText || '', ctx);
    var it = ctx.interpretation || buildInterpretationResult(userText || '', ctx);
    return [
      '[Auto-Compiled Working Prompt v7.5.8.10]',
      'Original user thought/message: ' + trim(userText || ''),
      'Interpreted meaning: ' + (it.meaning || it.label || ctx.intent || 'auto'),
      'Working goal: ' + p.promptGoal,
      'Desired visible output: ' + (desired.primaryResult || desired.label || 'usable answer'),
      'Use systems: ' + (p.commandScope || []).join(' > '),
      'Passive command matrix: ' + (function(){ var m = collectSystemCommandMatrix(); return 'elements=' + m.totalElements + ', unique=' + m.uniqueActions + ', groups=' + Object.keys(m.groups||{}).map(function(k){return k+':' + m.groups[k];}).join(','); })(),
      'Stable rebase policy: keep 7.5.8.9 composer/thought-prompt behavior as baseline and avoid importing later unstable menu/composer behavior.',
      'Quality rules: preserve existing systems, answer latest request first, remove repetition, include real verification path, be honest about what is not tested.',
      'Performance rules: keep answer compact enough, route only relevant systems, do not duplicate UI/status text, do not ask unnecessary follow-up questions.',
      'Final answer must be user-facing Thai by default, clear, practical, and not reveal hidden chain-of-thought.'
    ].join('\n');
  }
  function thoughtPromptCompilerHtml(ctx){
    ctx = ctx || state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '');
    var p = ctx.thoughtPromptCompiler || buildThoughtPromptCompilerProfile(ctx.original || '', ctx);
    return '<div class="thought-prompt-card"><div class="thought-prompt-head"><span>'+icon('brain')+'</span><div><b>Thought → Prompt Compiler</b><small>'+esc(p.score)+'/100 · พิมพ์ข้อความธรรมดาแล้วระบบแปลงเป็น prompt อัตโนมัติ</small></div></div><div class="thought-prompt-grid"><p><b>Mode</b><small>'+esc(p.mode)+'</small></p><p><b>Intent</b><small>'+esc(p.autoIntent)+'</small></p><p><b>Systems</b><small>'+esc((p.commandScope||[]).length)+' connected</small></p></div><code>'+esc(p.promptGoal)+'</code></div>';
  }

  function collectSystemCommandMatrix(){
    var a = app();
    var els = all('[data-action]', a || d);
    var map = {};
    els.forEach(function(el){
      var action = trim(el.getAttribute('data-action') || '');
      if(!action) return;
      var label = trim((el.getAttribute('aria-label') || el.getAttribute('title') || el.textContent || '').replace(/\s+/g,' ')).slice(0,72);
      if(!map[action]){ map[action] = {action:action, count:0, labels:{}}; }
      map[action].count += 1;
      if(label){ map[action].labels[label] = true; }
    });
    var actions = Object.keys(map).sort();
    var groups = {composer:0, message:0, code:0, file:0, preview:0, api:0, memory:0, room:0, voice:0, debug:0, settings:0, other:0};
    actions.forEach(function(action){
      var g = 'other';
      if(/composer|quick-prompt|mode/.test(action)) g='composer';
      else if(/^message-|answer|choice/.test(action)) g='message';
      else if(/^code-|artifact|generated/.test(action)) g='code';
      else if(/file|attachment|download|upload/.test(action)) g='file';
      else if(/preview|image|structure|device/.test(action)) g='preview';
      else if(/api|connect|settings|unlock/.test(action)) g='api';
      else if(/memory|interest|identity/.test(action)) g='memory';
      else if(/room/.test(action)) g='room';
      else if(/voice|audio|tts|speech/.test(action)) g='voice';
      else if(/debug|bug|health|scan|export|performance/.test(action)) g='debug';
      else if(/setting|updates|workplace/.test(action)) g='settings';
      groups[g] = (groups[g] || 0) + 1;
    });
    var top = actions.slice(0,80).map(function(action){
      var labels = Object.keys(map[action].labels || {}).slice(0,2).join(' / ');
      return action + (labels ? ' = ' + labels : '');
    });
    return {
      version:'system_command_matrix_v7.5.8.10_stable_rebase',
      totalElements:els.length,
      uniqueActions:actions.length,
      groups:groups,
      actions:actions,
      top:top,
      rule:'read existing commands passively and use them as meaning hints; never add/remove/rebind buttons from this matrix layer'
    };
  }
  function formatSystemCommandMatrix(matrix){
    matrix = matrix || collectSystemCommandMatrix();
    var groupLine = Object.keys(matrix.groups || {}).map(function(k){ return k + '=' + matrix.groups[k]; }).join(', ');
    return [
      '[AiRA System Command Matrix v7.5.8.10 - passive]',
      'Purpose: แค่ผู้ใช้พิมพ์ข้อความ ระบบอ่านคำสั่งเดิมทั้งหมดแบบ passive แล้วเลือกคำสั่ง/ระบบที่เกี่ยวข้องมาช่วยแปลงเป็น prompt อัตโนมัติ',
      'Elements=' + matrix.totalElements + ', unique actions=' + matrix.uniqueActions,
      'Groups: ' + groupLine,
      'Rule: ' + matrix.rule,
      'Known actions sample: ' + (matrix.top || []).slice(0,32).join(' | ')
    ].join('\n');
  }
  function buildStableRebaseGuardProfile(ctx){
    ctx = ctx || buildAllFormContext('');
    var matrix = collectSystemCommandMatrix();
    var forms = ctx.forms || [];
    var risk = [];
    if(forms.indexOf('ui_ux') !== -1) risk.push('layout_regression');
    if(forms.indexOf('wordpress_system') !== -1 || forms.indexOf('code') !== -1) risk.push('code_regression');
    if((ctx.continuity || '').indexOf('high') === 0) risk.push('context_drift');
    if(!risk.length) risk.push('general_stability');
    return {
      version:'stable_rebase_guard_v7.5.8.10',
      base:'7.5.8.9-stable-composer-thought-prompt',
      branchPolicy:'พัฒนาต่อจากฐาน 7.5.8.9 เท่านั้น ไม่ดึงพฤติกรรมจาก 7.5.9.0/7.5.9.1 ที่ทำให้ composer/menu แย่ลง',
      preserve:['composer behavior from 7.5.8.9','Thought Prompt Compiler','Auto Evolution Core','single-number continuation row','existing buttons/action router','code block preview/download/package','Memory/Rooms/API Center'],
      add:['passive System Command Matrix','stable rebase instruction','automatic prompt conversion from rough thought','regression warning in exported report'],
      risk:risk,
      commandStats:{elements:matrix.totalElements, unique:matrix.uniqueActions}
    };
  }
  function formatStableRebaseGuard(ctx){
    var g = buildStableRebaseGuardProfile(ctx || state.contextProfile || buildAllFormContext(''));
    return [
      '[AiRA Stable Rebase Guard v7.5.8.10]',
      'Base: ' + g.base,
      'Policy: ' + g.branchPolicy,
      'Preserve: ' + g.preserve.join(' > '),
      'Add only: ' + g.add.join(' > '),
      'Regression risks to watch: ' + g.risk.join(', '),
      'Command inventory: elements=' + g.commandStats.elements + ', unique=' + g.commandStats.unique
    ].join('\n');
  }

  // v7.5.8.37 External System Bridge + Communication Skill Evolution
  function buildExternalSystemBridgeProfile(userText, ctx){
    ctx = ctx || buildAllFormContext(userText || '');
    var text = clean(userText || ctx.original || '');
    var matrix = collectSystemCommandMatrix();
    var needsExternal = hasRe(/(ระบบภายนอก|ภายนอก|external|api|endpoint|webhook|google|github|v0|openai|gemini|claude|openrouter|ค้นหา|เว็บ|link|url|ล่าสุด|ราคา|ข่าว|กฎหมาย|research|verify|source|เชื่อม|connect|sync)/iu, text);
    var channels = [];
    function add(name, ready, reason){ channels.push({name:name, ready:!!ready, reason:reason || ''}); }
    add('WordPress admin-ajax bridge', !!(actions && actions.sendChat), 'เชื่อม backend ภายใน WordPress เพื่อส่งคำถามและรับ connection evidence จาก server');
    add('Real API Center UI', !!(actions && actions.getApi && actions.saveApi && actions.testApi), 'เป็นหน้าตั้งค่า API เท่านั้น; ความพร้อมของ key/endpoint จริงต้องรอ server evidence');
    add('Google CSE / Web research', false, 'client ไม่เห็น key; ต้องรอ server evidence ว่ามี Google Search API key + CSE ID จริง');
    add('GitHub / public code reader', false, 'client ไม่ยืนยัน token/endpoint; ต้องรอ server evidence หรือ reader fetch ผลลัพธ์จริง');
    add('Custom endpoint / webhook', false, 'client ไม่เห็น secret; ต้องรอ server evidence ว่ามี endpoint/key จริง');
    add('v0 / UI generation connector', false, 'client ไม่เห็น v0 endpoint/key; ต้องรอ server evidence');
    var readyCount = channels.filter(function(c){ return c.ready; }).length;
    var missing = channels.filter(function(c){ return !c.ready; }).map(function(c){ return c.name; }).slice(0,5);
    var connected = channels.filter(function(c){ return c.ready; }).map(function(c){ return c.name; }).slice(0,6);
    var score = clamp(30 + readyCount*6 + (needsExternal ? 8 : 0) + Math.min(12, (matrix.groups.api || 0)), 0, 100);
    return {
      version:'external_system_bridge_v7.5.8.37',
      needsExternal:needsExternal,
      score:score,
      connected:connected,
      missing:missing,
      channels:channels,
      selectionRule:'Use external systems only when relevant and configured; otherwise answer from internal context and clearly state the missing key/endpoint without blocking the answer.',
      commandApiActions:matrix.groups.api || 0
    };
  }
  function formatExternalSystemBridge(profile){
    profile = profile || buildExternalSystemBridgeProfile('', state.contextProfile || buildAllFormContext(''));
    var channelLine = (profile.channels || []).map(function(c){ return c.name + '=' + (c.ready ? 'ready' : 'needs_config') + (c.reason ? ' (' + c.reason + ')' : ''); }).join(' | ');
    return [
      '[External System Bridge v7.5.8.37]',
      'Purpose: เชื่อมระบบภายนอกที่เกี่ยวข้องกับคำถามล่าสุดเข้ากับ Context Reader / Prompt Processor / Answer Selector โดยไม่ให้ระบบภายนอกกลบคำถามจริง',
      'Need external: ' + (profile.needsExternal ? 'yes' : 'no') + ' · Bridge score: ' + profile.score + '/100',
      'Connected now: ' + ((profile.connected || []).join(' > ') || 'internal-only fallback'),
      'Missing/config needed: ' + ((profile.missing || []).join(' > ') || 'none'),
      'API action hints found: ' + (profile.commandApiActions || 0),
      'Channel map: ' + channelLine,
      'Rule: ' + profile.selectionRule
    ].join('\n');
  }
  function externalSystemBridgeHtml(profile){
    profile = profile || buildExternalSystemBridgeProfile('', state.contextProfile || buildAllFormContext(''));
    var chips = (profile.channels || []).slice(0,5).map(function(c){ return '<span class="system-chip '+(c.ready?'ok':'warn')+'">'+esc(c.name)+' · '+(c.ready?'ready':'needs config')+'</span>'; }).join('');
    return '<div class="context-reader-subcard external-bridge-card"><b>'+icon('api')+' External Bridge</b><small>'+esc(profile.score)+'/100 · '+esc(profile.needsExternal ? 'ต้องเชื่อมระบบภายนอกที่เกี่ยวข้อง' : 'ใช้ internal context เป็นหลัก')+'</small><div class="context-chip-row">'+chips+'</div><p>กติกา: เชื่อมเฉพาะระบบที่เกี่ยวข้องและตั้งค่าแล้ว ถ้ายังไม่มี key/endpoint ให้ตอบจากบริบทภายในก่อนและบอกข้อจำกัดสั้น ๆ</p></div>';
  }
  function buildCommunicationSkillEvolutionProfile(userText, ctx){
    ctx = ctx || buildAllFormContext(userText || '');
    var text = clean(userText || ctx.original || '');
    var frustration = hasRe(/(ไม่ได้|ยังไม่|ผิด|ทำผิด|ห่วย|งง|ไม่ตรง|ไม่มา|ตอบไม่ได้|ค้าง)/iu, text) || (ctx.toneFlags || []).indexOf('frustration_or_blocked') !== -1;
    var systemWork = (ctx.forms || []).indexOf('code') !== -1 || (ctx.forms || []).indexOf('wordpress_system') !== -1 || (ctx.forms || []).indexOf('ui_ux') !== -1;
    var directness = frustration ? 'รับผิดสั้น ๆ → ชี้จุดแก้จริง → ไม่แก้ตัว → ส่งผลลัพธ์ที่ใช้งานได้' : 'ตอบผลลัพธ์ก่อน → เหตุผลสั้น → วิธีทำต่อ';
    var stages = ['Understand latest user message exactly','Connect relevant internal/external systems','Select the most useful answer candidate','Rewrite into human-readable Thai','Add test path/status only when useful'];
    var score = clamp(Math.round((ctx.communicationScore || 50) + (frustration ? 14 : 6) + (systemWork ? 8 : 0)), 0, 100);
    return {
      version:'communication_skill_evolution_v7.5.8.37',
      score:score,
      directness:directness,
      style: frustration ? 'calm_repair_mode' : (systemWork ? 'system_builder_clear_mode' : 'natural_helpful_mode'),
      stages:stages,
      rules:[
        'คำตอบต้องเลือกจากคำถามล่าสุด ไม่ปล่อย context ภายในกลบผู้ใช้',
        'ถ้ามีระบบภายนอกเกี่ยวข้อง ให้ตรวจ readiness ก่อนใช้ และไม่โกหกว่าเชื่อมแล้วถ้ายังไม่ได้ตั้งค่า',
        'ถ้าเป็นงานแก้ระบบ ให้บอกอาการ สาเหตุ จุดแก้ วิธีเช็ก และสถานะสั้น ๆ',
        'ถ้าคำถามง่าย ให้ตอบง่ายก่อน ไม่บังคับเข้าระบบยาว'
      ]
    };
  }
  function formatCommunicationSkillEvolution(profile){
    profile = profile || buildCommunicationSkillEvolutionProfile('', state.contextProfile || buildAllFormContext(''));
    return [
      '[Communication Skill Evolution v7.5.8.37]',
      'Purpose: พัฒนาทักษะการสื่อสารอัตโนมัติให้คำตอบตรงคำถามล่าสุด สั้นก่อน ชัดขึ้น และเชื่อมระบบที่เกี่ยวข้องอย่างไม่หลุดประเด็น',
      'Score: ' + profile.score + '/100 · Style: ' + profile.style,
      'Directness: ' + profile.directness,
      'Stages: ' + (profile.stages || []).join(' > '),
      'Rules: ' + (profile.rules || []).join(' | ')
    ].join('\n');
  }
  function communicationSkillEvolutionHtml(profile){
    profile = profile || buildCommunicationSkillEvolutionProfile('', state.contextProfile || buildAllFormContext(''));
    return '<div class="context-reader-subcard communication-evolution-card"><b>'+icon('brain')+' Communication Skill</b><small>'+esc(profile.score)+'/100 · '+esc(profile.style)+'</small><p>'+esc(profile.directness)+'</p></div>';
  }


  // v7.5.8.39 Real Relation Answer Pipeline
  // This is not a button system. It is an answer-selection pipeline that maps
  // internal context + external readiness + API/local candidates before rendering.
  function buildRealRelationPipeline(userText, ctx){
    ctx = ctx || buildAllFormContext(userText || '');
    var text = clean(userText || ctx.original || '');
    var matrix = collectSystemCommandMatrix();
    var external = ctx.externalSystemBridge || buildExternalSystemBridgeProfile(text, ctx);
    var communication = ctx.communicationSkillEvolution || buildCommunicationSkillEvolutionProfile(text, ctx);
    var direct = clientDirectAnswer(text);
    var systemIntent = hasRe(/(ระบบ|ปลั๊กอิน|wordpress|composer|prompt processor|processer|เมนู|popup|api|เชื่อม|สัมพันธ์|ภายใน|ภายนอก|external|internal|answer selector|context|คำตอบ|สื่อสาร|พัฒนา|แก้|ซ่อม|โค้ด|code|debug)/iu, text);
    var needsExternal = !!(external && external.needsExternal);
    var internal = [
      {name:'Latest User Message Lock', ready:!!text, role:'คำถามจริงล่าสุดต้องชนะ context ภายใน'},
      {name:'Prompt Processor', ready:true, role:'แสดงสถานะและเวลาประมวลผลเท่านั้น ห้ามบล็อกคำตอบ'},
      {name:'Context Reader', ready:!!ctx, role:'อ่าน intent/forms/continuity/urgency'},
      {name:'System Command Matrix', ready:(matrix.totalElements||0)>0, role:'อ่านคำสั่งเดิมแบบ passive เพื่อช่วย route'},
      {name:'Result Resolver', ready:true, role:'จัดรูปแบบคำตอบให้ตรง output'},
      {name:'Memory/Rooms Context', ready:!!(state.rooms && state.rooms.length), role:'ต่อเนื่องจากห้องและประเด็นเดิม'},
      {name:'Code/Preview Context', ready:!!(state.lastCode || (state.lastCodeBlocks && state.lastCodeBlocks.length)), role:'ใช้เมื่อคำถามเกี่ยวกับโค้ด/ระบบ/ไฟล์'}
    ];
    var externalCandidates = (external.channels || []).map(function(c){
      return {name:c.name, ready:!!c.ready, role:c.reason || 'external connector candidate'};
    });
    var readyExternal = externalCandidates.filter(function(c){ return c.ready; }).map(function(c){ return c.name; });
    var missingExternal = externalCandidates.filter(function(c){ return !c.ready; }).map(function(c){ return c.name; }).slice(0,6);
    var apiWait = direct && !systemIntent && !needsExternal ? 120 : (needsExternal || systemIntent ? 16000 : 6200);
    return {
      version:'real_relation_answer_pipeline_v7.5.8.39',
      latest:text.slice(0,520),
      intent:ctx.intent || 'normal_question',
      forms:(ctx.forms || []).slice(0,10),
      needsSystem:!!systemIntent,
      needsExternal:needsExternal,
      directAnswerAvailable:!!direct,
      answerWaitMs:apiWait,
      internal:internal,
      external:externalCandidates,
      readyExternal:readyExternal,
      missingExternal:missingExternal,
      commandInventory:{elements:matrix.totalElements||0, unique:matrix.uniqueActions||0, apiActions:matrix.groups ? (matrix.groups.api || 0) : 0},
      communicationSkill:{score:communication.score || 0, style:communication.style || '', directness:communication.directness || ''},
      selectionOrder: direct && !systemIntent && !needsExternal
        ? ['local_direct','processor_complete_visual','answer_visible','processing_time_card']
        : ['api_candidate_from_backend','internal_context_candidate','external_readiness_candidate','local_direct_when_exact','relationship_rescue'],
      rule:'ต้องรวมความสัมพันธ์จริงก่อนตอบ: ภายในอ่านบริบท/คำสั่ง/สถานะ → ภายนอกตรวจ readiness/keys/endpoints → คัดเลือก candidate ที่ไม่ว่างและตรงคำถามล่าสุด → ตอบแบบไม่เคลมว่าเชื่อมจริงถ้ายังไม่มี key หรือ endpoint'
    };
  }

  function formatRealRelationPipeline(profile){
    profile = profile || buildRealRelationPipeline('', state.contextProfile || buildAllFormContext(''));
    function lineItems(items){
      return (items || []).map(function(x){ return x.name + '=' + (x.ready ? 'ready' : 'not_ready') + (x.role ? ' (' + x.role + ')' : ''); }).join(' | ');
    }
    return [
      '[Real Internal/External Relationship Pipeline v7.5.8.39]',
      'Latest user message: ' + (profile.latest || '-'),
      'Intent/forms: ' + (profile.intent || '-') + ' / ' + ((profile.forms || []).join(', ') || '-'),
      'Need system: ' + (profile.needsSystem ? 'yes' : 'no') + ' · Need external: ' + (profile.needsExternal ? 'yes' : 'no'),
      'Answer wait: ' + (profile.answerWaitMs || 0) + 'ms · Direct answer: ' + (profile.directAnswerAvailable ? 'yes' : 'no'),
      'Internal relationship: ' + lineItems(profile.internal),
      'External relationship/readiness: ' + lineItems(profile.external),
      'Ready external: ' + ((profile.readyExternal || []).join(' > ') || 'none'),
      'Missing external: ' + ((profile.missingExternal || []).join(' > ') || 'none'),
      'Command inventory: elements=' + ((profile.commandInventory||{}).elements || 0) + ', unique=' + ((profile.commandInventory||{}).unique || 0) + ', apiActions=' + ((profile.commandInventory||{}).apiActions || 0),
      'Communication skill: ' + ((profile.communicationSkill||{}).score || 0) + '/100 · ' + ((profile.communicationSkill||{}).style || '-'),
      'Selection order: ' + ((profile.selectionOrder || []).join(' > ') || '-'),
      'Rule: ' + profile.rule
    ].join('\n');
  }

  function relationshipPipelineFallbackAnswer(userText, ctx, pipeline, reason){
    pipeline = pipeline || buildRealRelationPipeline(userText, ctx);
    var direct = clientDirectAnswer(userText);
    if(direct) return direct;
    var ready = (pipeline.readyExternal || []).join(', ') || 'ยังไม่มี external connector ที่พร้อมใช้จริง';
    var missing = (pipeline.missingExternal || []).slice(0,4).join(', ') || 'ไม่มีรายการที่ขาดจากการประเมินรอบนี้';
    var forms = (pipeline.forms || []).join(', ') || 'ทั่วไป';
    var lines = [];
    lines.push('ระบบกำลังคัดเลือกคำตอบจากภายใน/ภายนอกตามหลักฐานที่มีครับ');
    lines.push('');
    lines.push('ผลการอ่านคำถามล่าสุด: ' + (clean(userText || '').slice(0,360) || '-'));
    lines.push('- หมวด/ฟอร์มที่เกี่ยวข้อง: ' + forms);
    lines.push('- ระบบภายในที่ใช้: Latest User Message, Prompt Processor, Context Reader, System Command Matrix, Result Resolver');
    lines.push('- ระบบภายนอกที่พร้อมใช้จากฝั่ง client: ' + ready + ' (ต้องยืนยันซ้ำจาก server evidence ก่อนถือว่าเชื่อมจริง)');
    lines.push('- ระบบภายนอกที่ยังต้องตั้งค่า: ' + missing);
    lines.push('');
    lines.push('คำตอบที่คัดเลือกได้ตอนนี้:');
    lines.push('1. ถ้าคำถามต้องใช้ข้อมูลภายนอกจริง ระบบต้องรอ server evidence/API candidate ก่อน ไม่ใช้ client UI readiness เป็นหลักฐาน');
    lines.push('2. ถ้ายังไม่มี connector พร้อม ระบบจะตอบจากบริบทภายในและบอกข้อจำกัดตรง ๆ ไม่ตอบว่าค้นเว็บแล้ว');
    lines.push('3. ถ้าเป็นงานพัฒนา AiRA/WordPress ให้ใช้คำถามล่าสุดเป็นตัวตั้ง แล้วเลือกเฉพาะโมดูลที่เกี่ยวข้อง ไม่ดึงทุกระบบมาปนจนคำตอบหลุด');
    lines.push('4. ถ้า API หลักช้า/ล้มเหลว ระบบยังต้องส่งคำตอบสำรองที่อธิบายสถานะและขั้นตอนแก้ได้ ไม่ปล่อยว่าง');
    if(reason){ lines.push(''); lines.push('สถานะการคัดเลือก: ' + String(reason).slice(0,220)); }
    return lines.join('\n');
  }

  function boundedAnswerPromise(promise, ms, fallbackFactory){
    ms = Math.max(120, Number(ms || 4200));
    return new Promise(function(resolve){
      var settled = false;
      var timer = setTimeout(function(){
        if(settled) return;
        settled = true;
        try { resolve(fallbackFactory ? fallbackFactory('timeout_after_' + ms + 'ms') : null); }
        catch(e){ resolve(null); }
      }, ms);
      Promise.resolve(promise).then(function(value){
        if(settled) return;
        settled = true;
        clearTimeout(timer);
        resolve(value);
      }).catch(function(err){
        if(settled) return;
        settled = true;
        clearTimeout(timer);
        try { resolve(fallbackFactory ? fallbackFactory(err && err.message ? err.message : String(err)) : null); }
        catch(e2){ resolve(null); }
      });
    });
  }


  function formatCommunicationContext(ctx){
    ctx = ctx || buildAllFormContext('');
    var recent = (ctx.recent || []).map(function(m,i){ return (i+1) + '. ' + (m.role || 'message') + ': ' + m.text; }).join('\n');
    return [
      '[AiRA All-Form Context Reader v7.2.0 + Auto Question/System Answer]',
      'Purpose: calculate how to communicate with the asker from available safe context before answering.',
      formatInterpretationResult(ctx),
      formatQuestionReaderBridge(ctx),
      formatDesiredOutputResolver(ctx),
      formatDeepCommunicationSkill(ctx),
      formatGlobalCommunicationSkill(ctx),
      formatCodeMasterProfile(ctx),
      formatUniversalSystemCodeSkill(ctx),
      formatAutoEvolutionCore(ctx),
      formatThoughtPromptCompiler(ctx),
      formatExternalSystemBridge(ctx.externalSystemBridge || buildExternalSystemBridgeProfile(ctx.original || '', ctx)),
      formatCommunicationSkillEvolution(ctx.communicationSkillEvolution || buildCommunicationSkillEvolutionProfile(ctx.original || '', ctx)),
      formatSystemCommandMatrix(),
      formatStableRebaseGuard(ctx),
      'Language: ' + ctx.language,
      'Detected forms: ' + (ctx.forms || []).join(', '),
      'Intent: ' + ctx.intent,
      'Continuity: ' + ctx.continuity,
      'Topic hints: ' + ((ctx.topics || []).join(', ') || 'none'),
      'Tone flags: ' + ((ctx.toneFlags || []).join(', ') || 'normal'),
      'Scores: clarity=' + ctx.clarityScore + '/100, complexity=' + ctx.complexityScore + '/100, urgency=' + ctx.urgencyScore + '/100, communication=' + ctx.communicationScore + '/100',
      'Response style: ' + (ctx.responseStyle || []).join(' | '),
      'Privacy rule: ' + ctx.privacy,
      formatGPTCommunicationProfile(gptCommunicationProfile(ctx)),
      'Recent room context (safe snippets only):',
      recent || 'none'
    ].join('\n');
  }
  function buildHumanCommunicationMessage(userText, ctx, compiledPrompt){
    var original = trim(userText);
    ctx = ctx || buildAllFormContext(original);
    var autoCompiledPrompt = compiledPrompt || compileThoughtToAutomaticPrompt(original, ctx);
    return [
      formatCommunicationContext(ctx),
      '',
      '[Answer Contract]',
      formatGPTCommunicationProfile(gptCommunicationProfile(ctx)),
      formatQuestionReaderBridge(ctx),
      formatDesiredOutputResolver(ctx),
      'Result Resolver rule: หลังอ่าน prompt แล้วต้องแปลงเป็นผลลัพธ์ที่ผู้ใช้ควรเห็นจริง เช่น การ์ดภาพ, code block, rendered Result, ไฟล์/แพ็ก, หรือคำตอบสรุปที่ใช้ได้ทันที ห้ามจบแค่สถานะประมวลผล',
      'Deep Communication rule: อ่านเจตนาแฝงแบบปลอดภัยจากคำสั้น/คำบ่น/คำสั่งซ้ำ เช่น ผู้ใช้ต้องการให้ผลลัพธ์ขึ้นจริง ต้องการทำต่อจากของเดิม หรือต้องการคำตอบสั้นก่อน แต่ห้ามอ้างว่าอ่านใจ/จิตใต้สำนึกจริง และห้ามเดาข้อมูลส่วนตัวอ่อนไหว',
      'Global Communication rule: เข้าใจรูปแบบการสื่อสารให้กว้างขึ้นแบบ GPT ได้แก่ คำพูดสั้น คำบ่น คำสั่งไม่ครบ ภาษาเทคนิค ภาษาคนทั่วไป บริบทผู้ใช้หลายบทบาท และการสื่อสารข้ามภาษา/ข้ามอาชีพ โดยต้องปรับเป็นผลลัพธ์ที่เข้าใจง่าย ไม่เหมารวมและไม่เดาข้อมูลส่วนตัว',
      'Code Master rule: ถ้าเป็นงานโค้ด ให้ตอบแบบ senior engineer: โค้ดเต็ม, ไฟล์ชัด, module/component/theme/widget/block relationship, security, responsive, browser support, วิธีทดสอบ, สถานะพร้อมใช้ และรองรับ Install/Upgrade/Next loop',
      'Universal System Code rule: งานระบบต้องเชื่อม frontend/backend/database/API/auth/storage/deploy/logs ให้เป็นภาพเดียวกัน เลือกโครงสร้างไฟล์ที่เหมาะกับระบบนั้น ๆ และระบุข้อจำกัดถ้าขาดสเปก/API/สิทธิ์เข้าถึง',
      'External System Bridge v7.5.8.37 rule: ถ้าคำถามต้องใช้ข้อมูลหรือบริการภายนอก ให้เชื่อมผ่าน Real API Center / Google CSE / GitHub / Custom Endpoint เฉพาะเมื่อ key/endpoint พร้อม ถ้ายังไม่พร้อมให้ตอบจากข้อมูลภายในก่อนและบอกว่าอะไรยังขาด โดยห้ามปล่อยคำตอบว่าง',
      'Communication Skill Evolution v7.5.8.37 rule: พัฒนาทักษะการสื่อสารอัตโนมัติทุกครั้ง โดยเลือกคำตอบที่เข้าใจง่ายที่สุด สั้นก่อน ตรงคำถามล่าสุด ลดคำซ้ำ ไม่โยนภาระให้ผู้ใช้ และถ้าผู้ใช้บอกว่าผิดให้แก้ตรงจุดทันที',
      'Auto Evolution Core v7.5.8.7 rule: พัฒนาการเนื้อหา การสื่อสาร ความก้าวหน้า คุณภาพ และประสิทธิภาพต้องเพิ่มอัตโนมัติร่วมกับระบบเดิม โดยห้ามเพิ่ม/แก้/ลบ/ผูกใหม่ปุ่ม data-action หรือ action router ในชั้นนี้',
      'Thought Prompt Compiler v7.5.8.10 rule: ผู้ใช้แค่คิดและพิมพ์ข้อความธรรมดา ระบบต้องดึงคำสั่ง/ระบบที่เกี่ยวข้องทั้งหมดมาประมวลผลและแปลงเป็น prompt ทำงานอัตโนมัติ ก่อนส่งคำตอบจริง โดยไม่แสดง prompt ภายในให้ผู้ใช้เห็นถ้าไม่ได้ขอ',
      'Stable Rebase Guard v7.5.8.10 rule: พัฒนาต่อจากฐาน 7.5.8.9 เท่านั้น ห้ามดึงพฤติกรรม layout/menu จาก 7.5.9.0/7.5.9.1 ที่ทำให้ composer/menu แย่ลง และต้องรักษาปุ่ม/action router เดิมก่อนเพิ่มความสามารถใหม่',
      'Communication Quality Engine v7.5.7.0 rule: ทุกคำตอบต้องมีความชัดเจน อบอุ่น ตรงประเด็น มีผลลัพธ์ก่อนรายละเอียด มีขั้นตอนถัดไปที่ทำได้จริง และอธิบายศัพท์เทคนิคให้ง่ายเมื่อจำเป็น',
      'Deep Cognitive Communication Engine v7.5.7.1 rule: สื่อสารลึกแบบปลอดภัยโดยมองหลายมิติของโจทย์ ตีความเฉพาะเจตนาเชิงงานจากข้อความและบริบท ไม่อ้างว่าอ่านใจหรือวินิจฉัยผู้ใช้ และจัดคำตอบให้ช่วย Attention, Memory, Reasoning, Emotion Regulation, Creativity, Decision, Action และ Reflection ของผู้ใช้',
      'Button/Command Communication rule: ถ้าเกี่ยวกับปุ่ม คำสั่ง API หรือ code block ให้ระบุเสมอว่าปุ่ม/คำสั่งนั้นทำอะไร ช่วยผู้ใช้อย่างไร กดแล้วเห็น feedback อะไร ถ้าล้มเหลวบอกอย่างไร และทดสอบอย่างไร',
      'Quality honesty rule: ห้ามเคลมว่าเสร็จ/ใช้งานจริง/เชื่อมต่อสำเร็จ หากยังไม่ได้ทดสอบจริง ให้บอกสถานะจริง ข้อจำกัด และวิธีตรวจต่ออย่างสั้น ชัด และเป็นประโยชน์',
      'ถ้าคำตอบจาก API ไม่ได้แสดงผลลัพธ์ที่ต้องการ ให้เริ่มคำตอบด้วยหัวข้อ “ผลลัพธ์ที่ต้องแสดง” แล้วระบุ output ที่ต้องขึ้น วิธีทดสอบ และข้อจำกัดจริง',
      'Auto Question/System Answer rule: ผู้ใช้แค่พิมพ์ปกติ ระบบต้องอ่านคำถาม เชื่อมโมดูลที่เกี่ยวข้องทั้งหมดเท่าที่มีใน AiRA Studio แล้วตอบผลลัพธ์ทันที ไม่บังคับให้ผู้ใช้กดเมนูก่อน',
      '',
      '[Auto Prompt Converted From User Thought]',
      autoCompiledPrompt,
      '',
      'ถ้าคำถามแตะหลายระบบ ให้เชื่อมเฉพาะระบบที่เกี่ยวข้องจริง เช่น Context, Memory, WordPress, Code, Image, Voice, Web, Artifact, Settings/API, Debug และ File ตามรูปแบบคำถาม',
      'ตอบเพื่อช่วยเหลือมนุษย์อย่างสร้างสรรค์ ใช้ภาษาไทยที่เข้าใจง่าย อบอุ่น ตรงประเด็น และนำไปใช้ได้จริง',
      'เริ่มจากคำตอบที่ผู้ใช้ต้องใช้ทันที คล้าย GPT: สั้น ชัด ตรง แล้วค่อยขยายเฉพาะส่วนจำเป็น',
      'ใช้ผลลัพธ์การตีความด้านบนเป็นตัวนำคำตอบ: ถ้าผู้ใช้ต้องการภาพรวม ให้เริ่มด้วย “ผลลัพธ์ภาพรวม” 1-3 บรรทัด; ถ้าผู้ใช้ต้องการสั้น ให้เริ่มด้วย “สรุปสั้น” ไม่เกิน 3 บรรทัด',
      'แสดงผลลัพธ์ที่ตีความแล้วให้ผู้ใช้เห็นก่อนคำอธิบายยาว โดยไม่เปิดเผย chain-of-thought หรือเหตุผลภายใน',
      'อ่านบริบทจากข้อความล่าสุด ประวัติห้อง รูปแบบข้อมูล ลิงก์ โค้ด ไฟล์ ภาพ เสียง ตัวเลข และคำสั่งต่อเนื่องเท่าที่ผู้ใช้ให้มาเท่านั้น',
      'ปรับระดับคำอธิบายตามบริบทของคำถาม ไม่เหมารวม ไม่ตัดสิน และไม่อ้างว่ารู้ข้อมูลส่วนตัวถ้าผู้ใช้ไม่ได้บอก',
      'ถ้าเกี่ยวกับระบบ/โค้ด/WordPress ให้ทวนโจทย์สั้น ๆ ระบุของเดิมยังอยู่ไหม สิ่งใหม่ที่เพิ่ม จุดแก้ วิธีใช้ สถานะใช้งาน และขั้นตอนทดสอบ',
      'ถ้าบริบทไม่พอ ให้ทำ best effort จากข้อมูลที่มีและบอกข้อจำกัดสั้น ๆ โดยไม่ถามซ้ำถ้าไม่จำเป็น',
      'ก่อนส่งคำตอบ ให้ตัดข้อความซ้ำ คำตอบนอกประเด็น และจัดรูปแบบให้อ่านง่ายเหมือนผู้ช่วย GPT',
      '',
      '[User Message]',
      original
    ].join('\n');
  }
  function contextSummaryHtml(ctx){
    ctx = ctx || state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '');
    return '<div class="context-reader-card"><div class="context-reader-title">'+icon('brain')+'<b>Context Reader</b><span>'+esc(ctx.communicationScore)+'/100</span></div>'+
      interpretationResultHtml(ctx)+
      questionReaderHtml(ctx)+
      desiredOutputHtml(ctx)+
      deepCommunicationHtml(ctx)+
      globalCommunicationHtml(ctx)+
      codeMasterHtml(ctx)+
      universalCodeHtml(ctx)+
      autoEvolutionCoreHtml(ctx)+
      thoughtPromptCompilerHtml(ctx)+
      externalSystemBridgeHtml(ctx.externalSystemBridge || buildExternalSystemBridgeProfile(ctx.original || '', ctx))+
      communicationSkillEvolutionHtml(ctx.communicationSkillEvolution || buildCommunicationSkillEvolutionProfile(ctx.original || '', ctx))+
      '<div class="context-reader-grid"><p><b>Intent</b><small>'+esc(ctx.intent)+'</small></p><p><b>Forms</b><small>'+esc((ctx.forms||[]).join(', '))+'</small></p><p><b>Continuity</b><small>'+esc(ctx.continuity)+'</small></p><p><b>Language</b><small>'+esc(ctx.language)+'</small></p></div>'+
      '<div class="context-reader-meter"><span style="width:'+esc(ctx.communicationScore)+'%"></span></div>'+
      '<p class="context-reader-note">ระบบใช้เพื่อปรับวิธีตอบจากบริบทที่ผู้ใช้ให้มาเท่านั้น ไม่เดา/ไม่เก็บข้อมูลอ่อนไหว เช่น เพศ อายุ อาชีพ ศาสนา สุขภาพ หรือการเมือง</p>'+
      '<pre class="mono context-reader-pre">'+esc(formatCommunicationContext(ctx))+'</pre></div>';
  }
  function updateComposerContext(){
    var input = byId('composerInput'), box = byId('composerSmartTags');
    if(!input || !box) return;
    var text = trim(input.value || '');
    var pendingChip = composerContinuationChipHtml();
    if(!text && !pendingChip){ box.hidden = true; box.innerHTML = ''; return; }
    box.hidden = false;
    if(!text){
      box.innerHTML = pendingChip;
      return;
    }
    var ctx = buildAllFormContext(text); state.contextProfile = ctx;
    var it = ctx.interpretation || buildInterpretationResult(text, ctx);
    box.innerHTML = pendingChip +
      '<button type="button" data-action="context-scan">'+icon('brain')+' Context '+esc(ctx.communicationScore)+'/100</button>'+
      '<button type="button" data-action="interpret-preview">ผลลัพธ์: '+esc(it.label)+'</button>'+
      '<button type="button" data-action="question-reader-preview">อ่านคำถามอัตโนมัติ</button>'+
      '<button type="button" data-action="result-resolver-preview">ต้องแสดง: '+esc(((ctx.desiredOutput||{}).label || 'auto')).slice(0,40)+'</button>'+
      '<button type="button" data-action="deep-communication-preview">เจตนาแฝง: '+esc((((ctx.subtext||{}).signals||[])[0] || 'safe')).slice(0,32)+'</button>'+
      '<button type="button" data-action="global-communication-preview">สื่อสารโลก '+esc(((ctx.globalCommunication||{}).score || 0))+'/100</button>'+
      '<button type="button" data-action="communication-quality-preview">สื่อสารคุณภาพ '+esc(ctx.communicationScore || 0)+'/100</button>'+
      '<button type="button" data-action="code-master-preview">Code '+esc(((ctx.codeMaster||{}).score || 0))+'/100</button>'+
      '<button type="button" data-action="universal-code-preview">ระบบ '+esc(((ctx.universalCode||{}).score || 0))+'/100</button>'+
      '<span>'+esc(ctx.intent)+'</span><span>Prompt time</span><span>Result resolver</span><span>Deep talk</span><span>Global talk</span><span>Code universe</span><span>'+esc((ctx.forms||[]).slice(0,3).join(' · '))+'</span>'+
      (ctx.continuity.indexOf('high')===0 ? '<span>ต่อจากบริบทเดิม</span>' : '');
  }
  function px(n){ return Math.max(0, Math.round(Number(n)||0)) + 'px'; }
  function rectH(el, fallback){ var r = el && el.getBoundingClientRect ? el.getBoundingClientRect() : null; return Math.max(0, Math.round((r && r.height) || (el && el.offsetHeight) || fallback || 0)); }
  function rectW(el, fallback){ var r = el && el.getBoundingClientRect ? el.getBoundingClientRect() : null; return Math.max(0, Math.round((r && r.width) || (el && el.offsetWidth) || fallback || 0)); }
  function setHidden(el, yes){ if(!el) return; if(yes){ el.setAttribute('hidden','hidden'); } else { el.removeAttribute('hidden'); } }
  function hasHidden(el){ return !el || el.hasAttribute('hidden'); }

  function activeEditableInsideApp(){
    var a = app();
    var el = d.activeElement;
    if(!a || !el || !a.contains(el)) return false;
    var tag = (el.tagName || '').toUpperCase();
    return tag === 'TEXTAREA' || tag === 'INPUT' || el.isContentEditable;
  }
  function keyboardLikelyOpen(kb, vv){
    if(w.innerWidth > 900 || !activeEditableInsideApp()) return false;
    var layoutH = w.innerHeight || d.documentElement.clientHeight || 0;
    var visualH = vv && vv.height ? vv.height : layoutH;
    var lost = Math.max(0, Math.round(layoutH - visualH - ((vv && vv.offsetTop) || 0)));
    return kb > 70 || lost > 90 || (layoutH > 0 && visualH > 0 && visualH / layoutH < 0.82);
  }
  function syncKeyboardFusion(active){
    var a = app();
    d.documentElement.classList.toggle('aira-keyboard-fusion-on', !!active);
    if(d.body){ d.body.classList.toggle('aira-keyboard-fusion-on', !!active); }
    if(a){
      a.classList.toggle('keyboard-open', !!active);
      a.setAttribute('data-keyboard-fusion', active ? 'on' : 'off');
    }
    var dock = byId('airaComposerDock');
    if(dock){ dock.setAttribute('data-keyboard-fusion', active ? 'on' : 'off'); }
    if(!active){ resetComposerDockPosition(); }
  }

  function keyboardInset(){
    var vv = w.visualViewport;
    if(!vv) return 0;
    var layoutH = w.innerHeight || d.documentElement.clientHeight || vv.height || 0;
    return Math.max(0, Math.round(layoutH - (vv.height + vv.offsetTop)));
  }
  var fixedBottomProbeCache = {at:0, mode:'visual'};
  function detectFixedBottomMode(vv){
    // Mobile browsers disagree: some pin fixed bottom:0 to visualViewport, others to layoutViewport.
    // This one-pixel probe lets the composer stay on the keyboard edge instead of floating mid-screen.
    var nowMs = Date.now();
    if(fixedBottomProbeCache.mode && nowMs - fixedBottomProbeCache.at < 700){ return fixedBottomProbeCache.mode; }
    var layoutH = w.innerHeight || d.documentElement.clientHeight || 0;
    if(!vv || !layoutH){ fixedBottomProbeCache = {at:nowMs, mode:'visual'}; return 'visual'; }
    var probe = d.createElement('i');
    probe.setAttribute('aria-hidden','true');
    probe.style.cssText = 'position:fixed;left:0;right:auto;bottom:0;width:1px;height:1px;opacity:0;pointer-events:none;z-index:-1;contain:strict;';
    (d.body || d.documentElement).appendChild(probe);
    var r = probe.getBoundingClientRect ? probe.getBoundingClientRect() : {bottom:0};
    if(probe.parentNode){ probe.parentNode.removeChild(probe); }
    var b = Math.round(r && r.bottom ? r.bottom : 0);
    var visualH = Math.round(vv.height || 0);
    var mode = Math.abs(b - visualH) <= 6 ? 'visual' : (Math.abs(b - layoutH) <= 8 ? 'layout' : 'visual');
    fixedBottomProbeCache = {at:nowMs, mode:mode};
    return mode;
  }
  function composerFixedBottom(kb, vv){
    kb = Math.max(0, Math.round(kb || 0));
    if(!vv || kb < 30) return 0;
    return detectFixedBottomMode(vv) === 'layout' ? kb : 0;
  }
  function composerAnchorMetrics(composerHeight){
    var vv = w.visualViewport;
    var layoutH = w.innerHeight || d.documentElement.clientHeight || 720;
    var visualTop = Math.max(0, Math.round((vv && vv.offsetTop) || 0));
    var visualH = Math.max(320, Math.round((vv && vv.height) || layoutH));
    var visualBottom = Math.max(visualTop + 120, Math.round(visualTop + visualH));
    var h = Math.max(52, Math.round(composerHeight || 104));
    var top = Math.max(0, Math.round(visualBottom - h));
    var rawBottomInset = Math.max(0, Math.round(layoutH - visualBottom));
    var bottom = composerFixedBottom(rawBottomInset, vv);
    return {top:top, bottom:bottom, visualBottom:visualBottom, rawBottomInset:rawBottomInset, fixedMode:(vv ? detectFixedBottomMode(vv) : 'visual')};
  }
  function lockComposerToKeyboardEdge(){
    var root = d.documentElement;
    var dock = byId('airaComposerDock');
    var metrics = composerAnchorMetrics(rectH(dock, w.innerWidth <= 782 ? 104 : 112));
    root.style.setProperty('--aira-composer-anchor-top', px(metrics.top));
    root.style.setProperty('--aira-visual-bottom', px(metrics.visualBottom));
    root.style.setProperty('--aira-keyboard-lock-bottom', px(metrics.bottom));
    root.style.setProperty('--aira-composer-fixed-bottom', px(metrics.bottom));
    root.style.setProperty('--aira-keyboard-anchor-mode', metrics.fixedMode || 'visual');
    if(dock){
      if(app() && app().classList && app().classList.contains('keyboard-open')){
        dock.setAttribute('data-composer-fixed','keyboard');
        dock.style.position = 'fixed';
        dock.style.left = '0px';
        dock.style.right = '0px';
        dock.style.top = 'auto';
        dock.style.bottom = px(metrics.bottom);
        dock.style.transform = 'translate3d(0,0,0)';
      } else {
        resetComposerDockPosition();
      }
    }
    return metrics;
  }
  function resetComposerDockPosition(){
    var root = d.documentElement;
    var dock = byId('airaComposerDock');
    root.style.setProperty('--aira-keyboard-lock-bottom', '0px');
    root.style.setProperty('--aira-composer-fixed-bottom', '0px');
    root.style.setProperty('--aira-keyboard-anchor-mode', 'closed');
    if(dock){
      dock.removeAttribute('data-composer-fixed');
      dock.style.position = '';
      dock.style.left = '';
      dock.style.right = '';
      dock.style.top = '';
      dock.style.bottom = '';
      dock.style.width = '';
      dock.style.maxHeight = '';
      dock.style.transform = '';
      dock.style.willChange = '';
    }
  }
  function adminBarMetrics(){
    var fallback = w.innerWidth <= 782 ? 46 : 32;
    var bar = byId('wpadminbar');
    if(!bar){ return {top:0,bottom:0,height:0,visible:false}; }
    var cs = w.getComputedStyle ? w.getComputedStyle(bar) : null;
    if(cs && (cs.display === 'none' || cs.visibility === 'hidden')){ return {top:0,bottom:0,height:0,visible:false}; }
    var r = bar.getBoundingClientRect ? bar.getBoundingClientRect() : null;
    var h = Math.max(0, Math.round((r && r.height) || bar.offsetHeight || fallback));
    var top = Math.max(0, Math.round((r && r.top) || 0));
    var bottom = Math.max(0, Math.round((r && r.bottom) || (top + h)));
    if(h && bottom < h){ bottom = h; }
    if(!h && fallback){ h = fallback; bottom = fallback; }
    return {top:top,bottom:bottom,height:h,visible:true};
  }
  function applyAdminBarHeaderLock(a, metrics, visualH){
    var shellTop = Math.max(0, metrics && typeof metrics.bottom === 'number' ? metrics.bottom : 0);
    var usableH = Math.max(320, Math.round((visualH || w.innerHeight || 720) - shellTop));
    var root = d.documentElement;
    root.style.setProperty('--aira-adminbar-top', px(metrics.top || 0));
    root.style.setProperty('--aira-adminbar-bottom', px(shellTop));
    root.style.setProperty('--aira-shell-top', px(shellTop));
    root.classList.toggle('aira-adminbar-lock-on', !!(metrics && metrics.visible));
    if(a){
      a.setAttribute('data-adminbar-lock', (metrics && metrics.visible) ? 'on' : 'off');
      a.style.top = px(shellTop);
      a.style.height = px(usableH);
      a.style.maxHeight = px(usableH);
    }
    var topbar = a && a.querySelector ? a.querySelector('.aira-topbar') : null;
    if(topbar){ topbar.setAttribute('data-adminbar-attached', (metrics && metrics.visible) ? 'true' : 'false'); }
  }

  function measure(){
    var root = d.documentElement;
    var a = app();
    var adminMetrics = adminBarMetrics();
    var adminbar = adminMetrics.height;
    var menu = (w.innerWidth <= 960) ? 0 : rectW(byId('adminmenuwrap') || byId('adminmenu'), d.body && d.body.classList && d.body.classList.contains('folded') ? 36 : 160);
    var vv = w.visualViewport;
    var layoutH = w.innerHeight || d.documentElement.clientHeight || 720;
    var visualTop = Math.max(0, Math.round((vv && vv.offsetTop) || 0));
    var visualH = Math.max(320, Math.round((vv && vv.height) || layoutH));
    var kb = keyboardInset();
    var keyboardOpen = keyboardLikelyOpen(kb, vv);
    var header = rectH((a || d).querySelector ? (a || d).querySelector('.aira-topbar') : null, w.innerWidth <= 782 ? 54 : 58);
    var composerDock = byId('airaComposerDock');
    var composerCore = rectH((a || d).querySelector ? (a || d).querySelector('.composer') : null, w.innerWidth <= 782 ? 52 : 56);
    var composer = rectH(composerDock, w.innerWidth <= 782 ? 104 : 112);
    var anchor = composerAnchorMetrics(composer || (w.innerWidth <= 782 ? 104 : 112));
    root.style.setProperty('--aira-adminbar', px(adminbar));
    root.style.setProperty('--aira-adminmenu', px(menu));
    syncMenuBackdropEdge();
    root.style.setProperty('--aira-vh', px(visualH));
    root.style.setProperty('--aira-visual-top', px(visualTop));
    root.style.setProperty('--aira-visual-height', px(visualH));
    root.style.setProperty('--aira-layout-height', px(layoutH));
    root.style.setProperty('--aira-keyboard-bottom', px(kb));
    root.style.setProperty('--aira-composer-anchor-top', px(anchor.top));
    root.style.setProperty('--aira-visual-bottom', px(anchor.visualBottom));
    root.style.setProperty('--aira-keyboard-lock-bottom', px(anchor.bottom));
    root.style.setProperty('--aira-header', px(header || (w.innerWidth <= 782 ? 54 : 58)));
    root.style.setProperty('--aira-composer-core', px(composerCore || (w.innerWidth <= 782 ? 52 : 56)));
    root.style.setProperty('--aira-composer', px(composer || (w.innerWidth <= 782 ? 104 : 112)));
    applyAdminBarHeaderLock(a, adminMetrics, visualH);
    syncKeyboardFusion(keyboardOpen);
    syncComposerMenuState();
    if(keyboardOpen){
      lockComposerToKeyboardEdge();
      scrollBottom(false);
    } else {
      resetComposerDockPosition();
    }
  }
  var measureTimer = 0;
  function scheduleMeasure(){ clearTimeout(measureTimer); measureTimer = setTimeout(measure, 30); }
  function keyboardAnchorBurst(){
    [0,40,90,160,260,420,650,900].forEach(function(ms){
      setTimeout(function(){ measure(); if(app() && app().classList.contains('keyboard-open')){ lockComposerToKeyboardEdge(); } }, ms);
    });
  }
  function ensureComposerBackdrop(){
    var a = app(); if(!a) return null;
    var backdrop = byId('composerMenuBackdrop');
    if(!backdrop){
      backdrop = d.createElement('button');
      backdrop.type = 'button';
      backdrop.id = 'composerMenuBackdrop';
      backdrop.className = 'composer-menu-backdrop';
      backdrop.setAttribute('data-action','composer-menu-close');
      backdrop.setAttribute('aria-label','ปิดเมนูทั้งหมด');
      backdrop.hidden = true;
      a.appendChild(backdrop);
    }
    return backdrop;
  }
  function isDesktopComposerViewport(){
    return (w.innerWidth || d.documentElement.clientWidth || 0) > 900;
  }
  function wordpressSidebarEdge(){
    if(!isDesktopComposerViewport()) return 0;
    var fallback = (d.body && d.body.classList && d.body.classList.contains('folded')) ? 36 : 160;
    var nodes = [byId('adminmenuwrap'), byId('adminmenuback'), byId('adminmenu')].filter(Boolean);
    var edge = 0;
    nodes.forEach(function(el){
      var r = el.getBoundingClientRect ? el.getBoundingClientRect() : null;
      if(r && r.width > 0){ edge = Math.max(edge, Math.round(r.right)); }
    });
    if(!edge){ edge = fallback; }
    return Math.max(0, edge);
  }

  function fixedScopeLeftBase(){
    var a = app();
    if(!a || !isDesktopComposerViewport()) return 0;
    var probe = d.createElement('i');
    probe.setAttribute('aria-hidden','true');
    probe.style.cssText = 'position:fixed;left:0;top:0;width:1px;height:1px;opacity:0;pointer-events:none;z-index:-1;';
    a.appendChild(probe);
    var pr = probe.getBoundingClientRect ? probe.getBoundingClientRect() : null;
    var ar = a.getBoundingClientRect ? a.getBoundingClientRect() : null;
    if(probe.parentNode){ probe.parentNode.removeChild(probe); }
    var left = pr ? Math.round(pr.left) : 0;
    var appLeft = ar ? Math.round(ar.left) : 0;
    // Some WP/admin CSS creates a containing block for fixed children inside #airaApp.
    // If so, CSS left:0 already starts at the AiRA/sidebar edge.
    if(Math.abs(left - appLeft) <= 2){ return appLeft; }
    return 0;
  }

  function syncMenuBackdropEdge(){
    var root = d.documentElement;
    if(!root) return;
    if(!isDesktopComposerViewport()){
      root.style.setProperty('--aira-menu-backdrop-left', '0px');
      return;
    }
    var sidebarEdge = wordpressSidebarEdge();
    var fixedBase = fixedScopeLeftBase();
    var left = Math.max(0, sidebarEdge - fixedBase);
    root.style.setProperty('--aira-menu-backdrop-left', px(left));
  }


  function syncDesktopComposerMenuPosition(){
    syncMenuBackdropEdge();
    var root = d.documentElement;
    var a = app();
    var menu = byId('composerMenu');
    var dock = byId('airaComposerDock');
    if(!root || !a || !menu || !dock || hasHidden(menu) || !isDesktopComposerViewport()) return;
    var vpW = w.innerWidth || d.documentElement.clientWidth || 1200;
    var vpH = w.innerHeight || d.documentElement.clientHeight || 720;
    var ar = a.getBoundingClientRect ? a.getBoundingClientRect() : null;
    var dockRect = dock.getBoundingClientRect ? dock.getBoundingClientRect() : null;
    // v7.5.8.22: lock the popup's LEFT EDGE to the real Composer/chat box.
    // WordPress admin pages can make fixed children inside #airaApp behave as if they are scoped
    // to #airaApp, not to the viewport. Therefore we subtract fixedScopeLeftBase() from the
    // measured viewport coordinate. This removes the extra right shift seen on desktop.
    var composerBox = a.querySelector ? a.querySelector('#airaComposerDock .composer') : null;
    var readableBox = composerBox || (a.querySelector ? a.querySelector('.aira-message .message-card, .welcome') : null) || dock;
    var rr = readableBox && readableBox.getBoundingClientRect ? readableBox.getBoundingClientRect() : dockRect;
    if(!rr || !rr.width){ return; }
    var fixedBase = fixedScopeLeftBase();
    var scopeWidth = fixedBase ? Math.max(320, Math.round(((ar && ar.right) || vpW) - fixedBase)) : vpW;
    var width = Math.max(320, Math.min(Math.round(rr.width), 920, scopeWidth - 28));
    var left = Math.round(rr.left - fixedBase);
    var minLeft = 14;
    var maxLeft = Math.max(minLeft, scopeWidth - width - 14);
    left = Math.max(minLeft, Math.min(maxLeft, left));
    var verticalRect = (dockRect && dockRect.width) ? dockRect : rr;
    var bottom = Math.max(14, Math.round(vpH - verticalRect.top + 10));
    var availableAbove = Math.max(220, Math.round(verticalRect.top - (adminBarMetrics().bottom || 0) - 22));
    root.style.setProperty('--aira-desktop-menu-left', px(left));
    root.style.setProperty('--aira-desktop-menu-bottom', px(bottom));
    root.style.setProperty('--aira-desktop-menu-width', px(width));
    root.style.setProperty('--aira-desktop-menu-max-height', px(Math.min(620, availableAbove)));
  }

  function handleComposerMenuWheelLock(ev){
    var menu = byId('composerMenu');
    if(!menu || hasHidden(menu) || !isDesktopComposerViewport()) return;
    var target = ev.target;
    if(!target || !target.closest || !target.closest('#composerMenu')) return;
    var delta = ev.deltaY || 0;
    var max = Math.max(0, menu.scrollHeight - menu.clientHeight);
    var top = menu.scrollTop <= 0;
    var bottom = menu.scrollTop >= max - 1;
    if((delta < 0 && top) || (delta > 0 && bottom)){
      ev.preventDefault();
    }
    ev.stopPropagation();
  }

  function syncComposerMenuState(){
    var a = app();
    var menu = byId('composerMenu');
    var backdrop = ensureComposerBackdrop();
    var open = !!(menu && !hasHidden(menu));
    if(a){
      a.classList.toggle('composer-menu-open', open);
      a.classList.toggle('desktop-menu-scroll-lock', open && isDesktopComposerViewport());
      a.setAttribute('data-plus-menu', open ? 'popup-open' : 'closed');
    }
    if(menu){ menu.setAttribute('aria-expanded', open ? 'true' : 'false'); }
    if(backdrop){ setHidden(backdrop, !open); }
    if(open){ syncDesktopComposerMenuPosition(); }
  }
  function setComposerMenuOpen(open){
    var menu = byId('composerMenu');
    if(!menu) return;
    setHidden(menu, !open);
    syncComposerMenuState();
    scheduleMeasure();
    if(open){
      closeFloatingExcept('composerMenu');
      syncDesktopComposerMenuPosition();
      if(!isDesktopComposerViewport()){ scrollBottom(false); }
    }
  }

  function toast(msg, type){
    var box = byId('toasts'); if(!box) return;
    var t = d.createElement('div'); t.className = 'toast' + (type ? ' is-' + type : ''); t.textContent = clean(msg);
    box.appendChild(t);
    setTimeout(function(){ t.style.opacity = '0'; }, 2600);
    setTimeout(function(){ if(t.parentNode){ t.parentNode.removeChild(t); } }, 3200);
  }



  function purgeLegacyDOMAssets(){
    var keepNeedles = ['aira-studio-clean-slate.css','aira-studio-clean-slate.js','aira-studio-clean-slate-style','aira-studio-clean-slate-runtime'];
    function keep(node){
      var id = node.id || '';
      var src = node.getAttribute('src') || node.getAttribute('href') || '';
      var text = id + ' ' + src;
      if(text.indexOf('aira-studio') === -1 && text.indexOf('thinkb4do') === -1 && text.indexOf('aira') === -1){ return true; }
      for(var i=0;i<keepNeedles.length;i++){ if(text.indexOf(keepNeedles[i]) !== -1){ return true; } }
      return false;
    }
    all('link[id*="aira"],link[href*="aira-studio"],style[id*="aira"],script[id*="aira"],script[src*="aira-studio"]').forEach(function(node){
      if(!keep(node) && node.parentNode){ node.parentNode.removeChild(node); }
    });
  }

  function stripHtmlForError(text){
    text = clean(text || '');
    return text.replace(/<script[\s\S]*?<\/script>/gi, ' ')
      .replace(/<style[\s\S]*?<\/style>/gi, ' ')
      .replace(/<[^>]+>/g, ' ')
      .replace(/&nbsp;/g, ' ')
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .replace(/&amp;/g, '&')
      .replace(/\s+/g, ' ')
      .trim();
  }
  function ajaxFriendlyError(actionKey, xhr, text, json, reason){
    var status = xhr && xhr.status ? Number(xhr.status) : 0;
    var plain = stripHtmlForError(text || '');
    var serverMsg = json && json.data && json.data.message ? clean(json.data.message) : '';
    var raw = (serverMsg || plain || reason || '').slice(0, 260);
    var lower = (raw + ' ' + text).toLowerCase();
    var isChat = String(actionKey || '').toLowerCase().indexOf('sendchat') !== -1 || String(actionKey || '').toLowerCase().indexOf('send_chat') !== -1;
    var msg = raw || ('HTTP ' + status);
    if(reason === 'client_timeout' || status === 504 || /gateway\s*time|gateway\s*timeout|time-?out|timed\s*out/.test(lower)){
      msg = 'คำตอบใช้เวลานานเกินจนเซิร์ฟเวอร์ตัดการเชื่อมต่อ (504/timeout). ระบบไม่ได้พัง แต่คำสั่งหรือ code context ยาวเกินช่วงเวลาที่ server รอได้';
      if(isChat){ msg += ' · AiRA จะเตรียมคำสั่งแบบย่อ/Retry Slim ให้ส่งต่อได้'; }
    } else if(status === 502 || status === 503 || status === 524){
      msg = 'เซิร์ฟเวอร์/API ปลายทางไม่พร้อมชั่วคราว (HTTP ' + status + ') · ลองส่งใหม่แบบสั้นลงหรือทดสอบ API Center';
    } else if(status === 403){
      msg = 'สิทธิ์หรือ nonce ไม่ผ่าน (HTTP 403) · ให้รีเฟรชหน้าและตรวจสิทธิ์ผู้ดูแลระบบ';
    } else if(status === 0){
      msg = 'เชื่อมต่อเครือข่ายไม่สำเร็จหรือคำขอถูกยกเลิก · ตรวจอินเทอร์เน็ต/แคช แล้วลองใหม่';
    } else if(serverMsg){
      msg = serverMsg;
    } else {
      msg = 'เชื่อมต่อไม่สำเร็จ (HTTP ' + status + ') · ' + (plain ? plain.slice(0, 160) : 'ไม่มีรายละเอียดจากเซิร์ฟเวอร์');
    }
    var err = new Error(msg);
    err.status = status;
    err.raw = raw;
    err.actionKey = actionKey;
    err.isTimeout = (reason === 'client_timeout' || status === 504 || /timeout|time-out|timed out/i.test(msg + ' ' + raw));
    return err;
  }
  function ajax(actionKey, payload, fileMode){
    return new Promise(function(resolve, reject){
      var actionName = actions[actionKey] || actionKey;
      var fd = fileMode && payload instanceof FormData ? payload : new FormData();
      if(!(fileMode && payload instanceof FormData)){
        payload = payload || {};
        Object.keys(payload).forEach(function(k){ if(payload[k] !== undefined && payload[k] !== null){ fd.append(k, payload[k]); } });
      }
      fd.append('action', actionName);
      fd.append('nonce', cfg.nonce || '');
      var xhr = new XMLHttpRequest();
      xhr.open('POST', cfg.ajaxUrl || ajaxurl, true);
      xhr.withCredentials = true;
      // v7.5.6.1: stop before nginx/browser shows raw 504 HTML, then show a useful recovery path.
      xhr.timeout = (/sendChat|send_chat/i.test(String(actionKey) + ' ' + String(actionName))) ? 52000 : 36000;
      xhr.onreadystatechange = function(){
        if(xhr.readyState !== 4) return;
        var text = xhr.responseText || '';
        var json = null;
        try { json = JSON.parse(text); } catch(e) {}
        if(xhr.status >= 200 && xhr.status < 300 && json){ resolve(json); }
        else { reject(ajaxFriendlyError(actionKey, xhr, text, json)); }
      };
      xhr.onerror = function(){ reject(ajaxFriendlyError(actionKey, xhr, '', null, 'network_error')); };
      xhr.ontimeout = function(){ reject(ajaxFriendlyError(actionKey, xhr, '', null, 'client_timeout')); };
      xhr.send(fd);
    });
  }

  function legacyStorageKey(){ return 'aira_v700_rooms_u' + clean(cfg.userId || '0'); }
  function getAuthorizedId(){
    var id = state.authorizedIdentity && state.authorizedIdentity.authorizedId ? state.authorizedIdentity.authorizedId : (cfg.authorizedIdentity && cfg.authorizedIdentity.authorizedId ? cfg.authorizedIdentity.authorizedId : '');
    return clean(id || ('wpuser_' + clean(cfg.userId || '0'))).replace(/[^a-zA-Z0-9_\-]/g, '_');
  }
  function storageKey(){ return 'aira_v744_rooms_auth_' + getAuthorizedId(); }
  function interestStorageKey(){ return 'aira_v744_interest_auth_' + getAuthorizedId(); }
  function performanceStorageKey(){ return 'aira_v744_performance_auth_' + getAuthorizedId(); }
  function deviceStorageKey(){ return 'aira_v744_device_id_' + clean(cfg.userId || '0'); }
  function getDeviceId(){
    if(state.deviceId) return state.deviceId;
    try {
      var id = localStorage.getItem(deviceStorageKey());
      if(!id){ id = 'dev_' + Date.now() + '_' + Math.random().toString(36).slice(2,10); localStorage.setItem(deviceStorageKey(), id); }
      state.deviceId = id;
    } catch(e){ state.deviceId = 'dev_session_' + Date.now(); }
    return state.deviceId;
  }
  function messageTopicForStats(text){
    text = clean(text);
    if(hasRe(/wordpress|plugin|ปลั๊กอิน|wp-admin|shortcode|gutenberg|elementor/iu, text)) return 'WordPress/Plugin';
    if(hasRe(/ui|ux|composer|preview|ปุ่ม|หน้าจริง|layout|responsive|สี|ฟอนต์/iu, text)) return 'UI/UX';
    if(hasRe(/```|function\s*\(|class\s+|api|rest|ajax|php|js|css|html/iu, text)) return 'Code/System';
    if(hasRe(/เสียง|voice|audio|ไมค์|พูด|ฟัง/iu, text)) return 'Voice/Audio';
    if(hasRe(/ภาพ|image|photo|รูป|กล้อง|preview/iu, text)) return 'Image/Preview';
    return 'Communication';
  }
  function messageSnippet(text){ return trim(clean(text).replace(/```[\s\S]*?```/g,'[code block]').replace(/\s+/g,' ')).slice(0,180); }
  function buildInterestStats(){
    var stats = {total:0,user:0,assistant:0,topics:{},samples:[],updatedAt:Date.now(),version:VERSION};
    (state.rooms||[]).forEach(function(room){
      (room.messages||[]).forEach(function(m, i){
        if(!m || !m.interesting) return;
        var role = m.role || 'assistant';
        var topic = m.interestTopic || messageTopicForStats(m.text || '');
        stats.total += 1;
        if(role === 'user') stats.user += 1;
        if(role === 'assistant') stats.assistant += 1;
        stats.topics[topic] = (stats.topics[topic] || 0) + 1;
        stats.samples.push({role:role, topic:topic, snippet:messageSnippet(m.text || ''), time:m.interestUpdatedAt || m.time || 0, room:room.title || 'แชทใหม่', index:i});
      });
    });
    stats.samples.sort(function(a,b){ return (b.time||0)-(a.time||0); });
    stats.samples = stats.samples.slice(0,80);
    state.interestStats = stats;
    return stats;
  }
  function saveInterestStats(){
    var stats = buildInterestStats();
    try { localStorage.setItem(interestStorageKey(), JSON.stringify(stats)); } catch(e) {}
    if(state.interestSyncTimer) clearTimeout(state.interestSyncTimer);
    state.interestSyncTimer = w.setTimeout(function(){
      if(actions.saveInterestStats){ ajax('saveInterestStats', {snapshot: JSON.stringify(stats)}).catch(function(){}); }
    }, 500);
    return stats;
  }
  function loadInterestStats(){
    try {
      var raw = localStorage.getItem(interestStorageKey());
      if(raw){ state.interestStats = JSON.parse(raw); }
    } catch(e) {}
    buildInterestStats();
  }
  function interestStatsReportText(){
    var stats = buildInterestStats();
    var topics = Object.keys(stats.topics||{}).sort(function(a,b){return stats.topics[b]-stats.topics[a];});
    return [
      'AiRA Communication Interest Stats',
      'Version: ' + VERSION,
      'Generated: ' + (new Date()).toISOString(),
      'Total liked messages: ' + stats.total,
      'User liked: ' + stats.user,
      'Assistant liked: ' + stats.assistant,
      '',
      'Top topics:',
      topics.length ? topics.map(function(t){ return '- ' + t + ': ' + stats.topics[t]; }).join('\n') : '- none',
      '',
      'Recent liked samples:',
      stats.samples.length ? stats.samples.slice(0,30).map(function(s,i){ return (i+1)+') ['+s.role+' · '+s.topic+'] '+s.snippet; }).join('\n') : '- none'
    ].join('\n');
  }
  function interestStatsHtml(){
    var stats = buildInterestStats();
    var topics = Object.keys(stats.topics||{}).sort(function(a,b){return stats.topics[b]-stats.topics[a];});
    var topicHtml = topics.length ? topics.slice(0,8).map(function(t){ return '<span class="interest-pill"><b>'+esc(t)+'</b><em>'+esc(stats.topics[t])+'</em></span>'; }).join('') : '<span class="muted">ยังไม่มีหัวข้อที่ถูกใจ</span>';
    var rows = stats.samples.length ? stats.samples.slice(0,12).map(function(s){ return '<div class="interest-row"><b>'+esc(s.role)+' · '+esc(s.topic)+'</b><span>'+esc(s.snippet || '-')+'</span></div>'; }).join('') : '<div class="interest-empty">กดไอคอนถูกใจใต้ข้อความที่สื่อสารดี/น่าสนใจ เพื่อเก็บสถิติ</div>';
    return '<section class="interest-stats"><div class="interest-score"><div><b>'+esc(stats.total)+'</b><span>ข้อความถูกใจทั้งหมด</span></div><div><b>'+esc(stats.user)+'</b><span>ฝั่งผู้ถาม</span></div><div><b>'+esc(stats.assistant)+'</b><span>ฝั่งผู้ตอบ</span></div></div><h3>หัวข้อที่น่าสนใจ</h3><div class="interest-pills">'+topicHtml+'</div><h3>ตัวอย่างล่าสุด</h3><div class="interest-list">'+rows+'</div><p class="panel-actions"><button type="button" data-action="interest-export">Export Interest TXT</button><button type="button" data-action="open-settings">Settings | โดย Thinkb4do | ดูรายละเอียด</button></p></section>';
  }
  function loadPerformanceEvents(){
    try {
      var raw = localStorage.getItem(performanceStorageKey());
      state.performanceEvents = raw ? JSON.parse(raw) : [];
      if(!Array.isArray(state.performanceEvents)) state.performanceEvents = [];
    } catch(e){ state.performanceEvents = []; }
  }
  function savePerformanceEvents(){
    try { localStorage.setItem(performanceStorageKey(), JSON.stringify((state.performanceEvents || []).slice(-120))); } catch(e) {}
  }
  function recordPerformanceEvent(ev){
    ev = ev || {};
    ev.time = ev.time || Date.now();
    ev.version = VERSION;
    state.performanceEvents = Array.isArray(state.performanceEvents) ? state.performanceEvents : [];
    state.performanceEvents.push(ev);
    if(state.performanceEvents.length > 120){ state.performanceEvents = state.performanceEvents.slice(-120); }
    savePerformanceEvents();
  }
  function countMessagePattern(re){
    var n = 0;
    (state.rooms || []).forEach(function(room){
      (room.messages || []).forEach(function(m){ if(re.test(clean(m && m.text || ''))) n += 1; });
    });
    return n;
  }
  function buildPerformanceImpactStats(){
    var rooms = state.rooms || [], events = Array.isArray(state.performanceEvents) ? state.performanceEvents : [];
    var totalMessages = 0, userMessages = 0, assistantMessages = 0, liked = 0, codeBlocks = 0, newest = 0;
    rooms.forEach(function(room){
      (room.messages || []).forEach(function(m){
        totalMessages += 1;
        if((m.role || '') === 'user') userMessages += 1;
        if((m.role || '') === 'assistant') assistantMessages += 1;
        if(m.interesting) liked += 1;
        codeBlocks += extractCodeBlocks(m.text || '').length;
        newest = Math.max(newest, Number(m.time || 0));
      });
    });
    var nextLoops = countMessagePattern(/\bnext\b|ปรับต่อ|แก้ต่อ|อัปเกรด code block|Live QC/iu);
    var bugFixes = countMessagePattern(/แก้บั๊ก|debug|fix|ผิดปกติ|ไม่ทำงาน|ค้าง|error|fail/iu);
    var previews = countMessagePattern(/preview|หน้าจริง|sandbox|ลองใช้ก่อนติดตั้ง|pre-install/iu);
    var durations = events.filter(function(e){ return e && e.type === 'chat_success' && Number(e.ms) > 0; }).map(function(e){ return Number(e.ms); });
    var avgMs = durations.length ? Math.round(durations.reduce(function(a,b){return a+b;},0) / durations.length) : 0;
    var success = events.filter(function(e){ return e && e.type === 'chat_success'; }).length;
    var errors = events.filter(function(e){ return e && e.type === 'chat_error'; }).length;
    var successRate = (success + errors) ? Math.round(success * 100 / (success + errors)) : 100;
    var interestRatio = totalMessages ? Math.round(liked * 100 / totalMessages) : 0;
    var speedScore = avgMs ? clamp(100 - Math.round(avgMs / 150), 50, 98) : 82;
    var loopScore = clamp(50 + nextLoops * 6 + previews * 5 + bugFixes * 3, 50, 98);
    var buildScore = clamp(55 + Math.min(25, codeBlocks * 5) + Math.min(14, previews * 4) + Math.min(8, nextLoops * 2), 55, 99);
    var communicationScore = clamp(60 + Math.min(18, liked * 3) + Math.min(12, interestRatio) + Math.min(8, userMessages ? Math.round(assistantMessages * 8 / Math.max(1,userMessages)) : 0), 60, 99);
    var impactScore = Math.round((speedScore * .22) + (loopScore * .24) + (buildScore * .27) + (communicationScore * .27));
    var estimatedMinutesSaved = Math.max(0, Math.round((nextLoops * 4) + (previews * 3) + (bugFixes * 5) + (codeBlocks * 6)));
    return {version:VERSION, rooms:rooms.length, totalMessages:totalMessages, userMessages:userMessages, assistantMessages:assistantMessages, liked:liked, codeBlocks:codeBlocks, nextLoops:nextLoops, bugFixes:bugFixes, previews:previews, avgMs:avgMs, successRate:successRate, speedScore:speedScore, loopScore:loopScore, buildScore:buildScore, communicationScore:communicationScore, impactScore:impactScore, estimatedMinutesSaved:estimatedMinutesSaved, newest:newest, updatedAt:Date.now()};
  }
  function efficiencyCircleHtml(label, percent, note, cls){
    percent = clamp(percent, 0, 100);
    return '<span class="efficiency-circle '+esc(cls||'')+'" style="--p:'+esc(percent)+'"><i><b>'+esc(percent)+'%</b></i><small>'+esc(label)+'</small><em>'+esc(note||'')+'</em></span>';
  }
  function performanceImpactHtml(){
    var st = buildPerformanceImpactStats();
    var circles = [
      efficiencyCircleHtml('Impact', st.impactScore, 'คุณค่ารวม', 'is-impact'),
      efficiencyCircleHtml('Speed', st.speedScore, st.avgMs ? (st.avgMs+' ms เฉลี่ย') : 'พร้อมวัด', 'is-speed'),
      efficiencyCircleHtml('Build', st.buildScore, st.codeBlocks+' code block', 'is-build'),
      efficiencyCircleHtml('Next Loop', st.loopScore, st.nextLoops+' รอบพัฒนา', 'is-loop'),
      efficiencyCircleHtml('Communication', st.communicationScore, st.liked+' ถูกใจ', 'is-comm'),
      efficiencyCircleHtml('Success', st.successRate, 'คำตอบสำเร็จ', 'is-success')
    ].join('');
    var rows = [
      ['ข้อความทั้งหมด', st.totalMessages], ['Code block', st.codeBlocks], ['Preview/หน้าจริง', st.previews], ['Fix/Debug', st.bugFixes], ['Next loop', st.nextLoops], ['ประหยัดเวลาโดยประมาณ', st.estimatedMinutesSaved + ' นาที']
    ].map(function(r){ return '<div class="efficiency-row"><b>'+esc(r[0])+'</b><span>'+esc(r[1])+'</span></div>'; }).join('');
    return '<section class="efficiency-stats"><div class="efficiency-hero"><div><b>AiRA Performance & Silent Impact</b><span>เพิ่มประสิทธิภาพการทำงานแบบเห็นค่า % จริงจากห้องแชท โค้ด Preview Debug และ Next Loop</span></div><button type="button" data-action="performance-export">Export Performance TXT</button></div><div class="efficiency-rings">'+circles+'</div><div class="efficiency-grid">'+rows+'</div><div class="efficiency-mission"><b>Brand mission</b><p>ระบบจะไม่เคลมว่าเป็นสินค้า GPT/OpenAI อย่างเป็นทางการ แต่จะทำงานให้รู้สึกระดับ GPT: ช่วยลดขั้นตอน แก้บั๊กสด แสดงหน้าจริงก่อนส่งมอบ และเก็บสัญญาณว่าสื่อสารแบบไหนช่วยคนได้มากขึ้น</p><p>เป้าหมายคือให้ Thinkb4do / AiRA เป็นแบรนด์ที่คนใช้แล้วรู้สึกว่า AI ช่วยงาน ช่วยเวลา และช่วยโลกได้จริงจากคุณภาพของระบบเอง</p></div><p class="panel-actions"><button type="button" data-action="performance-next">Next เพิ่มประสิทธิภาพต่อ</button><button type="button" data-action="open-settings">Settings | โดย Thinkb4do | ดูรายละเอียด</button></p></section>';
  }
  function performanceImpactReportText(){
    var st = buildPerformanceImpactStats();
    return [
      'AiRA Performance & Silent Impact Report',
      'Version: ' + VERSION,
      'Generated: ' + (new Date()).toISOString(),
      'Impact score: ' + st.impactScore + '/100',
      'Speed score: ' + st.speedScore + '/100',
      'Build score: ' + st.buildScore + '/100',
      'Next loop score: ' + st.loopScore + '/100',
      'Communication score: ' + st.communicationScore + '/100',
      'Success rate: ' + st.successRate + '%',
      '',
      'Evidence:',
      '- Total messages: ' + st.totalMessages,
      '- Code blocks: ' + st.codeBlocks,
      '- Preview/real-screen mentions: ' + st.previews,
      '- Fix/debug mentions: ' + st.bugFixes,
      '- Next loops: ' + st.nextLoops,
      '- Liked communication signals: ' + st.liked,
      '- Estimated time saved: ' + st.estimatedMinutesSaved + ' minutes',
      '',
      'Brand rule: do not claim official GPT/OpenAI ownership or endorsement. Let GPT-grade quality, reliability, preview-before-handoff, and human benefit communicate the value.'
    ].join('\n');
  }

  function authorizedIdentityHtml(){
    var id = state.authorizedIdentity || cfg.authorizedIdentity || {};
    var status = id && id.authorized ? 'เชื่อมแล้ว' : 'ยังไม่เชื่อม';
    var score = id && id.authorized ? 96 : 35;
    var rows = [
      ['Authorized ID', id.authorizedId || 'ยังไม่มี'],
      ['สิทธิ์', id.capability || 'ต้องล็อกอิน/มีสิทธิ์'],
      ['Sync mode', id.syncMode || 'none'],
      ['อุปกรณ์ที่เห็น', id.deviceCount || 0],
      ['Device ปัจจุบัน', getDeviceId()],
      ['อัปเดตล่าสุด', id.lastSeenAt || '-']
    ].map(function(r){ return '<div class="identity-row"><b>'+esc(r[0])+'</b><span>'+esc(r[1])+'</span></div>'; }).join('');
    return '<section class="identity-stats"><div class="identity-hero"><div>'+efficiencyCircleHtml('ID Sync', score, status, 'is-identity')+'</div><div><b>Authorized ID Sync</b><span>ใช้ ID ผู้มีสิทธิ์ผูกห้องแชท/Memory/สถิติไว้กับบัญชี WordPress ที่มีสิทธิ์ แม้เปลี่ยนมือถือ แท็บเล็ต หรือคอมพิวเตอร์ก็โหลดข้อมูลชุดเดียวกันได้เมื่อเข้าสู่ระบบเดิม</span></div></div><div class="identity-grid">'+rows+'</div><div class="identity-note"><b>หลักความปลอดภัย</b><p>Authorized ID ไม่ใช่รหัสผ่านและไม่เปิดให้ใครใช้งานแทนได้ การอ่าน/บันทึกข้อมูลยังต้องผ่าน WordPress login, nonce และ capability ของผู้ใช้เสมอ</p></div><p class="panel-actions"><button type="button" data-action="identity-sync-now">Sync ID ตอนนี้</button><button type="button" data-action="identity-export">Export ID Status TXT</button><button type="button" data-action="identity-rotate">เปลี่ยน Authorized ID</button></p></section>';
  }
  function authorizedIdentityReportText(){
    var id = state.authorizedIdentity || cfg.authorizedIdentity || {};
    return [
      'AiRA Authorized ID Sync Report',
      'Version: ' + VERSION,
      'Generated: ' + (new Date()).toISOString(),
      'Authorized: ' + (!!id.authorized),
      'Authorized ID: ' + (id.authorizedId || 'none'),
      'Owner WP User ID: ' + (id.ownerUserId || cfg.userId || '0'),
      'Capability: ' + (id.capability || 'none'),
      'Sync mode: ' + (id.syncMode || 'none'),
      'Device count: ' + (id.deviceCount || 0),
      'Current device ID: ' + getDeviceId(),
      'Last seen: ' + (id.lastSeenAt || '-'),
      '',
      'Security contract:',
      '- Cross-device data is linked by Authorized ID + WordPress user meta.',
      '- Reading/saving still requires WordPress login, nonce, and required capability.',
      '- Authorized ID is an identifier, not a password or public login key.',
      '- Rotating the ID changes future localStorage namespace but keeps server-side rooms for the same WP user.'
    ].join('\n');
  }
  function renderIdentityPanelIfOpen(){
    var body = byId('workplaceBody'), title = byId('workplaceTitle');
    if(body && title && /identity/i.test(title.textContent || '')) body.innerHTML = authorizedIdentityHtml();
  }
  function loadAuthorizedIdentity(cb){
    state.authorizedIdentity = state.authorizedIdentity || cfg.authorizedIdentity || null;
    getDeviceId();
    if(!actions.getAuthorizedIdentity){ if(cb) cb(); return; }
    ajax('getAuthorizedIdentity', {device_id:getDeviceId()}).then(function(res){
      var data = res && res.data && res.data.authorized_identity ? res.data.authorized_identity : null;
      if(data){
        state.authorizedIdentity = data;
        if(app()){ app().setAttribute('data-authorized-id', data.authorizedId || 'none'); app().setAttribute('data-authorized-sync', data.authorized ? 'on' : 'off'); }
        renderIdentityPanelIfOpen();
      }
      if(cb) cb();
    }).catch(function(){ if(cb) cb(); });
  }

  function saveLocal(){
    try { state.localUpdatedAt = Date.now(); localStorage.setItem(storageKey(), JSON.stringify({rooms:state.rooms, activeRoomId:state.activeRoomId, authorizedId:getAuthorizedId(), deviceId:getDeviceId(), updatedAt:state.localUpdatedAt})); } catch(e) {}
  }
  function loadLocal(){
    try {
      var raw = localStorage.getItem(storageKey()) || localStorage.getItem(legacyStorageKey());
      if(!raw) return;
      var data = JSON.parse(raw);
      if(data && data.rooms && data.rooms.length){ state.rooms = data.rooms; state.activeRoomId = data.activeRoomId || data.rooms[0].id; state.localUpdatedAt = Number(data.updatedAt || 0) || 0; }
    } catch(e) {}
  }
  function activeRoom(){
    if(!state.rooms.length){ createRoom('แชทใหม่', false); }
    var room = null;
    state.rooms.forEach(function(r){ if(r.id === state.activeRoomId) room = r; });
    if(!room){ room = state.rooms[0]; state.activeRoomId = room.id; }
    return room;
  }
  function createRoom(title, render){
    var room = { id:'r_' + Date.now() + '_' + Math.floor(Math.random()*999), title:title || 'แชทใหม่', messages:[], updatedAt:Date.now() };
    state.rooms.unshift(room); state.activeRoomId = room.id; state.messages = room.messages; saveLocal();
    if(render !== false){ renderChat(); renderRooms(); toast('สร้างห้องใหม่แล้ว','ok'); }
    return room;
  }

  function chatLog(){ return byId('chatLog'); }
  function nearBottom(el){ return !el || (el.scrollHeight - el.scrollTop - el.clientHeight < 140); }
  function ensureScrollLatestButton(){
    var a = app(); if(!a) return null;
    var btn = byId('scrollLatestBtn');
    if(!btn){
      btn = d.createElement('button');
      btn.type = 'button';
      btn.id = 'scrollLatestBtn';
      btn.className = 'scroll-latest-btn';
      btn.setAttribute('data-action','scroll-latest');
      btn.setAttribute('aria-label','เลื่อนลงคำถามล่าสุด');
      btn.hidden = true;
      btn.innerHTML = '<span aria-hidden="true">↓</span><b>ล่าสุด</b>';
      var panel = a.querySelector('.chat-panel') || a;
      panel.appendChild(btn);
    }
    return btn;
  }
  function updateScrollLatestButton(){
    var el = chatLog();
    var btn = ensureScrollLatestButton();
    if(!btn || !el) return;
    var shouldShow = !nearBottom(el) && el.scrollHeight > el.clientHeight + 180;
    setHidden(btn, !shouldShow);
    btn.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
  }
  function scrollBottom(force){
    var el = chatLog(); if(!el) return;
    if(force || nearBottom(el)){
      try{ el.scrollTo({top:el.scrollHeight, behavior: force ? 'smooth' : 'auto'}); }
      catch(e){ el.scrollTop = el.scrollHeight; }
    }
    setTimeout(updateScrollLatestButton, 20);
  }
  function scrollToLatest(){
    var el = chatLog(); if(!el) return;
    try{ el.scrollTo({top:el.scrollHeight, behavior:'smooth'}); }
    catch(e){ el.scrollTop = el.scrollHeight; }
    setTimeout(updateScrollLatestButton, 80);
    setTimeout(updateScrollLatestButton, 260);
  }
  function clearWelcome(){ var el = chatLog(); if(!el) return; var welcome = el.querySelector('.welcome'); if(welcome) welcome.parentNode.removeChild(welcome); }
  function messageEl(role, text, meta, index, opts){
    role = role || 'assistant'; opts = opts || {};
    var raw = clean(text);
    var typing = isTypingText(raw);
    var collapsed = opts.forceCollapsed != null ? !!opts.forceCollapsed : shouldCollapseMessage(role, raw);
    var wrap = d.createElement('div');
    wrap.className = 'aira-message ' + role + (collapsed ? ' is-collapsed' : '') + (typing ? ' is-typing' : '');
    // v7.5.7.8: transient/no-save message text is kept for tools, but Prompt processor rows are stripped later.
    // Prompt-processing cards are not saved into room.messages, so click handlers must not depend only on message index.
    wrap.__airaMessageText = raw;
    if(index !== undefined && index !== null){ wrap.setAttribute('data-message-index', String(index)); }

    var inner = d.createElement('div'); inner.className = 'message-card';
    var head = d.createElement('div'); head.className = 'message-head';
    var label = d.createElement('span'); label.className = 'role-label'; label.textContent = messageRoleLabel(role);
    var m = d.createElement('span'); m.className = 'message-meta'; m.textContent = meta || '';
    head.appendChild(label); if(meta){ head.appendChild(m); }
    inner.appendChild(head);

    var bubble = d.createElement('div'); bubble.className = 'bubble';
    if(typing){ bubble.innerHTML = raw; }
    else { renderMessageContent(bubble, raw, role); }
    inner.appendChild(bubble);
    if(!typing && role === 'assistant'){
      var unifiedRow = createUnifiedNumberContinuationRow(raw);
      if(unifiedRow){ inner.appendChild(unifiedRow); }
      enforceSingleNumberContinuationRows(inner);
    }

    if(!typing && role !== 'system'){
      var tools = d.createElement('div'); tools.className = 'message-tools';
      var copy = d.createElement('button'); copy.type='button'; copy.className='message-tool'; copy.setAttribute('data-action','message-copy'); copy.setAttribute('aria-label','คัดลอกข้อความ' + messageRoleLabel(role)); copy.innerHTML=icon('copy') + '<span>คัดลอก</span>'; tools.appendChild(copy);
      var msgData = (index !== undefined && index !== null && activeRoom && activeRoom().messages) ? (activeRoom().messages[index] || {}) : {};
      var liked = !!msgData.interesting;
      var like = d.createElement('button'); like.type='button'; like.className='message-tool message-like' + (liked ? ' is-liked' : ''); like.setAttribute('data-action','message-like'); like.setAttribute('aria-label','ถูกใจข้อความนี้เพื่อเก็บสถิติการสื่อสาร'); like.setAttribute('aria-pressed', liked ? 'true' : 'false'); like.innerHTML=icon('like') + '<span>' + (liked ? 'ถูกใจแล้ว' : 'ถูกใจ') + '</span>'; tools.appendChild(like);
      if(role === 'user'){
        var userEdit = d.createElement('button'); userEdit.type='button'; userEdit.className='message-tool'; userEdit.setAttribute('data-action','message-edit'); userEdit.setAttribute('aria-label','แก้ไขข้อความผู้ถาม'); userEdit.innerHTML=icon('edit') + '<span>แก้ไข</span>'; tools.appendChild(userEdit);
        if(shouldCollapseMessage(role, raw)){
          var more = d.createElement('button'); more.type='button'; more.className='message-tool show-more'; more.setAttribute('data-action','message-show-more'); more.setAttribute('aria-label','แสดงคำถามทั้งหมด'); more.innerHTML=icon('more') + '<span>Show more</span>'; tools.appendChild(more);
        }
      }
      if(role === 'assistant'){
        var answer = d.createElement('button'); answer.type='button'; answer.className='message-tool message-answer-continue'; answer.setAttribute('data-action','message-answer-continue'); answer.setAttribute('aria-label','คุยต่อจากคำตอบนี้'); answer.innerHTML=icon('next') + '<span>Answer</span>'; tools.appendChild(answer);
        var audio = d.createElement('button'); audio.type='button'; audio.className='message-tool'; audio.setAttribute('data-action','message-audio'); audio.setAttribute('aria-label','เล่นเสียงข้อความผู้ตอบ'); audio.innerHTML=icon('audio') + '<span>เล่นเสียง</span>'; tools.appendChild(audio);
        var edit = d.createElement('button'); edit.type='button'; edit.className='message-tool'; edit.setAttribute('data-action','message-edit'); edit.setAttribute('aria-label','แก้ไขข้อความผู้ตอบ'); edit.innerHTML=icon('edit') + '<span>แก้ไข</span>'; tools.appendChild(edit);
      }
      inner.appendChild(tools);
    }
    wrap.appendChild(inner);
    return wrap;
  }
  function addMessage(role, text, meta, noSave){
    clearWelcome();
    var el = chatLog(); if(!el) return null;
    var index = null;
    if(!noSave){
      var room = activeRoom();
      room.messages.push({role:role, text:clean(text), meta:meta || '', time:Date.now()});
      index = room.messages.length - 1;
      room.updatedAt = Date.now(); state.messages = room.messages; saveLocal();
    }
    var node = messageEl(role, text, meta || now(), index);
    el.appendChild(node);
    scrollBottom(true);
    return node;
  }

  function prefersReducedMotion(){
    try { return !!(w.matchMedia && w.matchMedia('(prefers-reduced-motion: reduce)').matches); }
    catch(e){ return false; }
  }
  function splitGraphemes(text){
    text = clean(text);
    try {
      if(w.Intl && Intl.Segmenter){
        var seg = new Intl.Segmenter('th', {granularity:'grapheme'});
        return Array.from(seg.segment(text), function(part){ return part.segment; });
      }
    } catch(e) {}
    return Array.from(text);
  }
  function slideTypingHasCodeFence(text){
    return /```/.test(clean(text));
  }
  function slideTypingIsThai(text){ return /[\u0E00-\u0E7F]/.test(clean(text || '')); }
  function slideTypingHasRenderableMarker(text){ return /\[\[(AIRA_IMAGE_CARD|AIRA_VISIBLE_IMAGE_CARD)\]\]/.test(clean(text || '')); }
  function slideTypingChunkSize(total, fullText, visible){
    total = Number(total) || 0;
    fullText = clean(fullText || '');
    visible = clean(visible || '');
    var hasCode = slideTypingHasCodeFence(fullText);
    var thai = slideTypingIsThai(fullText);
    // v7.5.6.4: smoother GPT-like cadence. Code stays responsive; prose breathes in smaller human-readable groups.
    if(hasCode){
      if(total > 16000) return 120;
      if(total > 9000) return 86;
      if(total > 5200) return 54;
      if(total > 2400) return 28;
      if(total > 900) return 13;
      return 6;
    }
    if(total > 9000) return 48;
    if(total > 5200) return 31;
    if(total > 2400) return 18;
    if(total > 980) return 9;
    if(total > 420) return thai ? 4 : 5;
    if(total > 180) return thai ? 3 : 2;
    return thai ? 2 : 1;
  }
  function slideTypingPauseMs(visible, fullText){
    visible = clean(visible); fullText = clean(fullText || '');
    var tail = visible.slice(-42);
    if(!tail) return 0;
    if(/```[a-z0-9_-]*\s*$/i.test(tail)) return 230;
    if(/```\s*$/.test(tail)) return 190;
    if(/\n\s*\n$/.test(tail)) return 210;
    if(/[.!?。！？]\s*$/.test(tail)) return 132;
    if(/[\:：]\s*$/.test(tail)) return 104;
    if(/[;；]\s*$/.test(tail)) return 78;
    if(/[,，、]\s*$/.test(tail)) return 46;
    if(/\)\s*$/.test(tail)) return 30;
    if(/\n$/.test(tail)) return 72;
    if(/(ครับ|ค่ะ|นะครับ|นะคะ|ดังนี้|ต่อไป|สรุป|เรียบร้อย|สำเร็จ)\s*$/.test(tail)) return 86;
    if(slideTypingHasCodeFence(fullText) && /[{};]\s*$/.test(tail)) return 18;
    return 0;
  }
  function slideTypingDelay(visible, total, chunk, fullText){
    visible = clean(visible); total = Number(total) || 0; chunk = Number(chunk) || 1;
    var hasCode = slideTypingHasCodeFence(fullText || visible);
    var base = hasCode
      ? (total > 9000 ? 7 : (total > 4200 ? 9 : (total > 1600 ? 11 : 14)))
      : (total > 9000 ? 12 : (total > 4200 ? 15 : (total > 1600 ? 19 : (total > 520 ? 25 : 31))));
    var breathing = slideTypingPauseMs(visible, fullText || visible);
    var ease = Math.min(16, Math.max(0, chunk - 2));
    return base + breathing + ease;
  }
  function shouldSlideTypeAssistant(text){
    var t = clean(text);
    if(!t || prefersReducedMotion()) return false;
    if(t.indexOf('[[AIRA_IMAGE_CARD]]') !== -1) return false;
    return true;
  }
  function animateAssistantText(node, fullText, done){
    if(!node){ if(typeof done === 'function') done(); return; }
    var bubble = node.querySelector('.bubble');
    if(!bubble){ if(typeof done === 'function') done(); return; }
    var text = clean(fullText);
    if(!shouldSlideTypeAssistant(text)){
      renderMessageContent(bubble, text, 'assistant');
      if(typeof done === 'function') done();
      return;
    }
    var parts = splitGraphemes(text);
    var total = parts.length;
    var chunk = slideTypingChunkSize(total, text, '');
    var i = 0, timer = 0, raf = 0, lastScroll = 0, lastPaint = '';
    var richDuringTyping = slideTypingHasRenderableMarker(text);
    node.classList.add('is-slide-typing');
    node.setAttribute('data-slide-typing','on');
    node.setAttribute('data-typing-cadence','gpt-breathing-7564');
    function appendCaret(){
      var caret = d.createElement('span');
      caret.className = 'aira-slide-caret';
      caret.setAttribute('aria-hidden','true');
      bubble.appendChild(caret);
    }
    function paintNow(finalPass){
      // v7.5.8.24: hard gate protection. Prompt Processor cards must not be repainted
      // by a late requestAnimationFrame after they have been marked complete, otherwise
      // the answer can appear while the processor still visually says "กำลังประมวล".
      if(node && node.classList && node.classList.contains('prompt-processing') && node.getAttribute('data-prompt-processing') === 'complete'){ return; }
      var visible = finalPass ? text : parts.slice(0, i).join('');
      if(!finalPass && visible === lastPaint) return;
      lastPaint = visible;
      if(finalPass || richDuringTyping){
        renderMessageContent(bubble, visible, 'assistant');
      } else {
        // During typing, keep rendering plain text to avoid expensive markdown/code parsing every few milliseconds.
        // Final pass renders full rich content/code block once, making the slide smoother on mobile.
        bubble.textContent = visible;
      }
      if(!finalPass) appendCaret();
      var nowMs = Date.now();
      if(nowMs - lastScroll > 130){
        lastScroll = nowMs;
        try { if(w.requestAnimationFrame){ w.requestAnimationFrame(function(){ scrollBottom(false); }); } else { scrollBottom(false); } } catch(e){ scrollBottom(false); }
      }
    }
    function paint(finalPass){
      if(raf && w.cancelAnimationFrame){ w.cancelAnimationFrame(raf); raf = 0; }
      if(w.requestAnimationFrame){ raf = w.requestAnimationFrame(function(){ paintNow(finalPass); }); }
      else { paintNow(finalPass); }
    }
    function finish(){
      paintNow(true);
      node.classList.remove('is-slide-typing');
      node.setAttribute('data-slide-typing','done');
      node.removeAttribute('data-typing-cadence');
      scheduleMeasure(); scrollBottom(false);
      if(typeof done === 'function') done();
    }
    function step(){
      var currentVisible = parts.slice(0, i).join('');
      chunk = slideTypingChunkSize(total, text, currentVisible);
      i = Math.min(total, i + chunk);
      if(i >= total){ finish(); return; }
      paint(false);
      var visibleForDelay = parts.slice(0, i).join('');
      timer = w.setTimeout(step, Math.max(10, slideTypingDelay(visibleForDelay, total, chunk, text)));
      node.__airaStopSlideTyping = function(){ clearTimeout(timer); if(raf && w.cancelAnimationFrame) w.cancelAnimationFrame(raf); i = total; finish(); };
    }
    bubble.textContent = '';
    paint(false);
    timer = w.setTimeout(step, 30);
  }
  function addAssistantMessageWithSlide(text, meta, afterDone){
    var node = addMessage('assistant', text, meta || now());
    if(node){
      removeAnswerContinuationRows(node);
      node.setAttribute('data-answer-insights','waiting-for-slide-done');
      var bubble = node.querySelector('.bubble');
      if(bubble){ bubble.textContent = ''; }
      animateAssistantText(node, text, function(){
        appendPostSlideAnswerRows(node, text);
        node.setAttribute('data-answer-insights','ready-after-slide-done');
        updateScrollLatestButton();
        if(typeof afterDone === 'function'){
          try { afterDone(node); } catch(e){}
        }
      });
    } else if(typeof afterDone === 'function'){
      try { afterDone(null); } catch(e2){}
    }
    return node;
  }

  function promptProcessorConnectorText(ctx){
    var qr = ctx && ctx.questionReading ? ctx.questionReading : null;
    var connectors = qr && Array.isArray(qr.connectors) ? qr.connectors : [];
    if(!connectors.length && ctx && Array.isArray(ctx.forms)){
      connectors = ctx.forms.map(function(f){ return f.replace(/_/g,' '); }).slice(0,4);
    }
    return connectors.length ? connectors.slice(0,5).join(' → ') : 'Context Reader → GPT Communication → Answer Renderer';
  }
  function buildPromptProcessingText(ctx, userText){
    ctx = ctx || buildAllFormContext(userText || '');
    var it = ctx.interpretation || buildInterpretationResult(userText || '', ctx);
    var forms = (ctx.forms || []).slice(0,4).join(', ') || 'plain_text';
    var desired = ctx.desiredOutput || resolveDesiredOutput(userText || '', ctx);
    var shortPrompt = trim(userText || '').replace(/\s+/g,' ');
    if(shortPrompt.length > 120){ shortPrompt = shortPrompt.slice(0,117) + '...'; }
    return [
      'กำลังประมวล Prompt...',
      'อ่านความคิด/ข้อความ: ' + (shortPrompt || 'คำถามว่าง'),
      'แปลงเป็น Prompt อัตโนมัติ: ดึงคำสั่งและระบบที่เกี่ยวข้องมาประมวลผล',
      'ตีความ: ' + (it.label || ctx.intent || 'auto') + ' · ความมั่นใจ ' + (it.confidence || ctx.communicationScore || 0) + '/100',
      'รูปแบบข้อมูล: ' + forms,
      'ผลลัพธ์ที่ต้องขึ้น: ' + desired.primaryResult,
      'เจตนาแฝง: ' + (((ctx.subtext||{}).needs||[]).slice(0,2).join(' | ') || 'ตอบตรงประเด็น'),
      'Code Skill: ' + (((ctx.codeMaster||{}).isCode) ? 'ตรวจโค้ดแบบ senior + ส่งโค้ดเต็ม' : 'standby'),
      'เชื่อมระบบ: ' + promptProcessorConnectorText(ctx),
      'ระบบภายนอก: ' + (((ctx.externalSystemBridge||{}).needsExternal) ? (((ctx.externalSystemBridge||{}).connected||[]).join(' > ') || 'ต้องตั้งค่า key/endpoint') : 'ไม่จำเป็นในคำถามนี้'),
      'ทักษะสื่อสาร: ' + (((ctx.communicationSkillEvolution||{}).style) || 'natural_helpful_mode'),
      'เตรียมคำตอบจริงให้ตรงประเด็นและบังคับให้มี output ที่ผู้ใช้เห็นได้...'
    ].join('\n');
  }
  function formatProcessingDuration(ms){
    ms = Math.max(0, Number(ms) || 0);
    if(ms < 1000){ return Math.round(ms) + ' มิลลิวินาที'; }
    var sec = ms / 1000;
    var fixed = sec < 10 ? sec.toFixed(2) : (sec < 60 ? sec.toFixed(1) : sec.toFixed(0));
    return fixed.replace(/\.00$/, '').replace(/\.0$/, '') + ' วินาที';
  }
  function buildPromptProcessingDoneText(ctx, userText, elapsedMs, status){
    ctx = ctx || buildAllFormContext(userText || '');
    var it = ctx.interpretation || buildInterpretationResult(userText || '', ctx);
    var elapsed = formatProcessingDuration(elapsedMs);
    var connector = promptProcessorConnectorText(ctx);
    var desired = ctx.desiredOutput || resolveDesiredOutput(userText || '', ctx);
    var label = status === 'error' ? 'Prompt Processor เสร็จแล้ว — แต่คำตอบจริงเชื่อมต่อไม่สำเร็จ' : 'Prompt Processor เสร็จแล้ว — พร้อมแสดงคำตอบจริง';
    return [
      label,
      'เวลาในการประมวลผล: ' + elapsed,
      'ตีความ: ' + (it.label || ctx.intent || 'auto') + ' · ความมั่นใจ ' + (it.confidence || ctx.communicationScore || 0) + '/100',
      'ผลลัพธ์ที่ต้องแสดง: ' + desired.primaryResult,
      'ทักษะสื่อสาร: ' + (((ctx.communicationSkillEvolution||{}).directness) || ((ctx.subtext||{}).responseMove) || 'ตอบผลลัพธ์ก่อน'),
      'เชื่อมระบบ: ' + connector,
      'External Bridge: ' + (((ctx.externalSystemBridge||{}).needsExternal) ? (((ctx.externalSystemBridge||{}).connected||[]).join(' > ') || 'needs config') : 'internal context'),
      status === 'error' ? 'สถานะ: ต้องตรวจ API/เครือข่าย แล้วลองส่งใหม่' : 'สถานะ: พร้อมแสดงคำตอบจริง'
    ].join('\n');
  }
  function ensurePromptProcessingDots(node){
    if(!node || !node.querySelector) return;
    all('.processor-inline-dots', node).forEach(function(el){ if(el && el.parentNode){ el.parentNode.removeChild(el); } });
    var card = node.querySelector('.message-card');
    if(!card) return;
    var existing = all('.aira-processing-dots', card);
    if(existing.length){
      existing.slice(1).forEach(function(el){ if(el && el.parentNode){ el.parentNode.removeChild(el); } });
      return;
    }
    var dots = d.createElement('div');
    dots.className = 'aira-processing-dots';
    dots.setAttribute('aria-hidden','true');
    dots.innerHTML = '<span></span><span></span><span></span>';
    var tools = card.querySelector('.message-tools');
    if(tools){ card.insertBefore(dots, tools); }
    else { card.appendChild(dots); }
  }


  function buildPromptReadingSteps(ctx, userText){
    ctx = ctx || buildAllFormContext(userText || '');
    var forms = Array.isArray(ctx.forms) ? ctx.forms : [];
    var steps = [
      'กำลังอ่านคำถามและจับเจตนาหลัก',
      'เชื่อมบริบทเดิมกับคำถามล่าสุด',
      'ตรวจว่ามีเงื่อนไข ตัวเลือก หรือลำดับ 1 2 3 หรือไม่',
      'จัดลำดับคำตอบให้ต่อเนื่องและไม่หลุดประเด็น'
    ];
    if(forms.indexOf('code') !== -1 || forms.indexOf('wordpress_system') !== -1 || forms.indexOf('software_system') !== -1){
      steps.push('อ่านโครงสร้างโค้ดและความสัมพันธ์ของระบบ');
      steps.push('ตรวจ flow: Preview → QC → Package → Install Gate');
    }
    if(forms.indexOf('image_visual') !== -1){ steps.push('เตรียมอ่านภาพและสรุปสิ่งที่เห็นจริง'); }
    if(forms.indexOf('voice_audio') !== -1){ steps.push('เตรียมวิเคราะห์เสียงและจังหวะการตอบกลับ'); }
    steps.push('เรียบเรียงคำตอบให้สั้นพออ่านง่าย แต่ครบจุดที่ต้องทำ');
    steps.push('รอคำตอบจริงจาก API แล้วจะแสดงทันที');
    return steps;
  }
  function renderProcessingLoopBubble(bubble, steps, index, ctx){
    if(!bubble) return;
    steps = steps && steps.length ? steps : ['กำลังอ่านข้อมูล'];
    var current = steps[index % steps.length];
    var next = steps[(index + 1) % steps.length];
    var score = ctx && ctx.communicationScore ? clamp(ctx.communicationScore, 0, 100) : 72;
    bubble.innerHTML = '<div class="aira-reading-processor" data-step="'+esc(String(index % steps.length + 1))+'">' +
      '<div class="processor-kicker"><b>AiRA Processor</b><span>reading</span></div>' +
      '<div class="processor-slide-text"><span>'+esc(current)+'</span></div>' +
      '<div class="processor-subline">ต่อไป: '+esc(next)+'</div>' +
      '<div class="processor-meter" aria-hidden="true"><i style="width:'+esc(String(score))+'%"></i></div>' +
      '</div>';
  }
  function startPromptProcessingLoop(processor){
    if(!processor || !processor.node || processor.loopStarted) return;
    processor.loopStarted = true;
    var steps = buildPromptReadingSteps(processor.ctx, processor.userText);
    var idx = 0;
    var node = processor.node;
    node.setAttribute('data-processing-loop','on');
    function tick(){
      if(!processor || !processor.node || state.promptProcessing !== processor || node.getAttribute('data-prompt-processing') === 'complete'){ return; }
      renderProcessingLoopBubble(node.querySelector('.bubble'), steps, idx, processor.ctx);
      ensurePromptProcessingDots(node);
      scrollBottom(false);
      idx += 1;
      processor.loopTimer = w.setTimeout(tick, 1180 + ((idx % 3) * 160));
    }
    processor.stopLoop = function(){
      if(processor.loopTimer){ clearTimeout(processor.loopTimer); processor.loopTimer = 0; }
      if(node && node.setAttribute){ node.setAttribute('data-processing-loop','off'); }
    };
    tick();
  }

  function finalizePromptProcessing(processor, status){
    processor = processor || state.promptProcessing;
    if(!processor || !processor.node){ return 0; }
    var elapsedMs = Date.now() - (processor.startedAt || Date.now());
    state.lastPromptProcessingDuration = elapsedMs;
    if(processor.stopLoop){ processor.stopLoop(); }
    if(processor.loopTimer){ clearTimeout(processor.loopTimer); processor.loopTimer = 0; }
    var text = buildPromptProcessingDoneText(processor.ctx, processor.userText, elapsedMs, status || 'success');
    processor.node.classList.remove('is-slide-typing');
    processor.node.setAttribute('data-prompt-processing','complete');
    processor.node.setAttribute('data-processing-ms', String(Math.round(elapsedMs)));
    var meta = processor.node.querySelector('.message-meta');
    if(meta){ meta.textContent = 'ประมวลผลแล้ว ' + formatProcessingDuration(elapsedMs); }
    var bubble = processor.node.querySelector('.bubble');
    if(bubble){ bubble.textContent = text; }
    // v7.5.7.8: keep Prompt processor clean permanently; no numbered insight row on this card.
    removePromptProcessorInsightRows(processor);
    scheduleMeasure();
    scrollBottom(false);
    if(state.promptProcessing === processor){ state.promptProcessing = null; }
    return elapsedMs;
  }
  // v7.5.8.24 Prompt Processor Hard Answer Gate
  // The answer may be received from API early, but it is not allowed to appear until
  // the Prompt Processor card has visibly changed from reading/processing to completed.
  function waitMs(ms){
    ms = Math.max(0, Number(ms) || 0);
    return new Promise(function(resolve){ w.setTimeout(resolve, ms); });
  }
  function nextPaint(){
    return new Promise(function(resolve){
      if(w.requestAnimationFrame){ w.requestAnimationFrame(function(){ w.requestAnimationFrame(resolve); }); }
      else { w.setTimeout(resolve, 32); }
    });
  }
  function setPromptAnswerGateState(stateName){
    var app = appRoot();
    if(app && app.setAttribute){ app.setAttribute('data-prompt-answer-gate', stateName || 'idle'); }
  }
  function forcePromptProcessorCompleteVisible(processor, status){
    if(!processor || !processor.node){ return 0; }
    var elapsedMs = Date.now() - (processor.startedAt || Date.now());
    var text = buildPromptProcessingDoneText(processor.ctx, processor.userText, elapsedMs, status || 'success');
    // v7.5.8.32 Reliable Release: stop the old slide-typing processor before
    // writing the completed state, otherwise a late animation callback can repaint
    // the card back to "reading" and the answer looks like it never arrives.
    try {
      processor.node.setAttribute('data-prompt-processing','complete-locking');
      if(typeof processor.node.__airaStopSlideTyping === 'function'){
        processor.node.__airaStopSlideTyping();
        processor.node.__airaStopSlideTyping = null;
      }
    } catch(stopTypingErr){}
    if(processor.stopLoop){ processor.stopLoop(); }
    if(processor.loopTimer){ clearTimeout(processor.loopTimer); processor.loopTimer = 0; }
    processor.completed = true;
    processor.node.classList.remove('is-slide-typing');
    processor.node.classList.add('is-processor-complete');
    processor.node.setAttribute('data-prompt-processing','complete');
    processor.node.setAttribute('data-answer-gate','processor-complete-visible');
    processor.node.setAttribute('data-processing-ms', String(Math.round(elapsedMs)));
    var meta = processor.node.querySelector('.message-meta');
    if(meta){ meta.textContent = 'ประมวลผลเสร็จแล้ว ' + formatProcessingDuration(elapsedMs); }
    var bubble = processor.node.querySelector('.bubble');
    if(bubble){
      bubble.textContent = text;
      bubble.setAttribute('data-processor-visible-state','complete');
    }
    removePromptProcessorInsightRows(processor);
    scheduleMeasure();
    scrollBottom(false);
    if(state.promptProcessing === processor){ state.promptProcessing = null; }
    return elapsedMs;
  }
  function processorStillLooksBusy(processor){
    if(!processor || !processor.node){ return false; }
    var bubble = processor.node.querySelector('.bubble');
    var text = bubble ? (bubble.textContent || '') : '';
    if(processor.node.getAttribute('data-prompt-processing') !== 'complete'){ return true; }
    return /กำลังประมวล\s*Prompt|กำลังอ่านข้อมูล|reading|รอคำตอบจริงจาก API/i.test(text);
  }
  function completePromptProcessorGate(processor, status){
    if(!processor || !processor.node){ setPromptAnswerGateState('no-processor'); return Promise.resolve(0); }
    var minMs = prefersReducedMotion() ? 350 : 2100;
    var afterDonePause = prefersReducedMotion() ? 120 : 560;
    var initialDone = processor.done || Promise.resolve();
    setPromptAnswerGateState('locked-waiting-processor');
    if(processor.node && processor.node.setAttribute){
      processor.node.setAttribute('data-answer-gate','locked-waiting-processor');
    }
    return initialDone.catch(function(){ return true; }).then(function(){
      var elapsedBefore = Date.now() - (processor.startedAt || Date.now());
      return waitMs(Math.max(0, minMs - elapsedBefore));
    }).then(function(){
      var elapsed = forcePromptProcessorCompleteVisible(processor, status || 'success');
      return nextPaint().then(function(){ return elapsed; });
    }).then(function(elapsed){
      if(processorStillLooksBusy(processor)){
        elapsed = forcePromptProcessorCompleteVisible(processor, status || 'success');
      }
      if(processor && processor.node){ processor.node.setAttribute('data-answer-gate','answer-release-after-visible-complete'); }
      return waitMs(afterDonePause).then(function(){
        setPromptAnswerGateState('answer-released');
        return elapsed;
      });
    });
  }

  // v7.5.8.31 Prompt Processor Complete-Before-Answer Gate
  // API may finish early, but the answer bubble must not be appended until the
  // Prompt Processor card has visibly changed to the completed state. This gate
  // is strict about visual order but still has a short failsafe so replies are
  // never blocked forever.
  function promptProcessorVisualCompleteBeforeAnswer(processor, status){
    if(!processor || !processor.node){
      setPromptAnswerGateState('no-processor-answer-release');
      return Promise.resolve(0);
    }
    var started = processor.startedAt || Date.now();
    var minReadingMs = prefersReducedMotion() ? 160 : 720;
    var completeVisibleMs = prefersReducedMotion() ? 90 : 260;
    setPromptAnswerGateState('locked-until-processor-complete-v75832');
    try { processor.node.setAttribute('data-answer-gate','locked-until-processor-complete-v75832'); } catch(e0){}

    // Wait briefly for the processor card to appear, but never let this wait block
    // the real answer. The completed card is forced below even if the typing loop
    // or requestAnimationFrame is delayed by the browser.
    var initial = Promise.race([
      (processor.done || Promise.resolve()).catch(function(){ return true; }),
      waitMs(prefersReducedMotion() ? 360 : 900)
    ]);

    return initial.then(function(){
      var elapsedBefore = Date.now() - started;
      return waitMs(Math.max(0, minReadingMs - elapsedBefore));
    }).then(function(){
      var elapsed = forcePromptProcessorCompleteVisible(processor, status || 'success');
      try { processor.node.setAttribute('data-answer-gate','processor-complete-visible-v75832'); } catch(e1){}
      return Promise.race([nextPaint(), waitMs(96)]).then(function(){ return elapsed; });
    }).then(function(elapsed){
      // The answer is now allowed to render. This wait only gives the user a short
      // visual confirmation; it is intentionally small so AiRA does not feel stuck.
      return waitMs(completeVisibleMs).then(function(){ return elapsed; });
    }).then(function(elapsed){
      try { processor.node.setAttribute('data-answer-gate','answer-release-allowed-v75832'); } catch(e2){}
      setPromptAnswerGateState('answer-release-allowed-v75832');
      return elapsed;
    }).catch(function(){
      var elapsed = 0;
      try { elapsed = forcePromptProcessorCompleteVisible(processor, status || 'success'); } catch(e3){}
      try {
        if(processor && processor.node){ processor.node.setAttribute('data-answer-gate','answer-release-failsafe-v75832'); }
        setPromptAnswerGateState('answer-release-failsafe-v75832');
      } catch(e4){}
      return waitMs(prefersReducedMotion() ? 40 : 120).then(function(){ return elapsed; });
    });
  }

  // v7.5.8.36 Prompt Processor Deterministic Release Gate
  // Keep the visual order requested by the user, but never depend on the live
  // processor typing promise. Older versions waited for processor.done/animation
  // and some browsers left the real answer blocked. This gate forces the
  // completed processor state, lets the browser paint it, then ALWAYS resolves.
  function safeCompletePromptProcessorGate(processor, status){
    status = status || 'success';
    setPromptAnswerGateState('guaranteed-release-start-v75833');
    return new Promise(function(resolve){
      var settled = false;
      function release(elapsed){
        if(settled) return;
        settled = true;
        try { setPromptAnswerGateState('answer-release-guaranteed-v75833'); } catch(e0){}
        resolve(Number(elapsed) || 0);
      }
      // Absolute failsafe: even if DOM paint, animation cleanup, or an old
      // callback fails, the answer must appear.
      var hardTimer = w.setTimeout(function(){ release(0); }, prefersReducedMotion() ? 420 : 880);
      try {
        if(!processor || !processor.node){
          clearTimeout(hardTimer);
          release(0);
          return;
        }
        try { processor.node.setAttribute('data-answer-gate','guaranteed-release-v75833'); } catch(e1){}
        var elapsed = forcePromptProcessorCompleteVisible(processor, status);
        // Make the completed processor visible before rendering answer.
        var paintPromise = (w.requestAnimationFrame)
          ? new Promise(function(done){
              w.requestAnimationFrame(function(){
                w.requestAnimationFrame(function(){ done(); });
              });
            })
          : waitMs(48);
        paintPromise.then(function(){
          return waitMs(prefersReducedMotion() ? 40 : 180);
        }).then(function(){
          clearTimeout(hardTimer);
          release(elapsed);
        }).catch(function(){
          clearTimeout(hardTimer);
          release(elapsed);
        });
      } catch(err){
        try { clearTimeout(hardTimer); } catch(e2){}
        release(0);
      }
    });
  }

  function looksLikeUnusableReplyText(text){
    text = trim(String(text || ''));
    if(!text) return true;
    return /(ไม่มีข้อความตอบกลับจาก API|ยังไม่มีข้อมูล\/API|ไม่มี API ที่เชื่อมสำเร็จ|ไม่พบคำตอบ|หาคำตอบไม่ได้|empty reply|not_configured|unknown_error)/iu.test(text);
  }

  function clientDirectAnswer(text){
    var raw = trim(String(text || ''));
    if(!raw) return '';
    var compact = raw.replace(/\s+/g, '');
    var m = compact.match(/^(-?\d+(?:\.\d+)?)([+\-*x×\/÷])(-?\d+(?:\.\d+)?)=?$/u);
    if(m){
      var a = Number(m[1]), b = Number(m[3]), op = m[2], ans = null;
      if((op === '/' || op === '÷') && Math.abs(b) < 1e-12) return 'หารด้วยศูนย์ไม่ได้ครับ';
      if(op === '+') ans = a + b;
      else if(op === '-') ans = a - b;
      else if(op === '*' || op === 'x' || op === '×') ans = a * b;
      else if(op === '/' || op === '÷') ans = a / b;
      if(ans !== null && isFinite(ans)) return Math.abs(ans - Math.round(ans)) < 1e-10 ? String(Math.round(ans)) : String(Number(ans.toFixed(8)));
    }
    if(/^(สวัสดี|หวัดดี|hello|hi)$/iu.test(raw)) return 'สวัสดีครับ ให้ AiRA ช่วยเรื่องอะไรต่อได้เลย';
    if(/(เมืองหลวงของไทย|เมืองหลวงประเทศไทย|capital of thailand)/iu.test(raw)) return 'กรุงเทพมหานครครับ';
    return '';
  }

  function buildClientAnswerFallback(userText, ctx, errorText){
    var direct = clientDirectAnswer(userText);
    if(direct) return direct;
    var forms = ctx && ctx.forms && ctx.forms.length ? ctx.forms.join(', ') : 'ทั่วไป';
    var issue = trim(String(userText || '')).slice(0, 420);
    var lines = [];
    lines.push('AiRA รับคำถามแล้วครับ');
    lines.push('');
    lines.push('สิ่งที่ระบบตีความได้:');
    lines.push('- คำถามล่าสุด: ' + (issue || 'ไม่มีข้อความ'));
    lines.push('- กลุ่มงานที่เกี่ยวข้อง: ' + forms);
    lines.push('');
    lines.push('คำตอบเบื้องต้น:');
    lines.push('1. ระบบจะยึดคำถามล่าสุดก่อน context ภายในทั้งหมด');
    lines.push('2. ถ้า API หลักไม่ตอบ ระบบจะใช้ local fallback เพื่อไม่ให้คำตอบว่าง');
    lines.push('3. ถ้าเป็นงานแก้ระบบ ให้ตรวจตามลำดับ: Console error → admin-ajax response → provider/API readiness → UI state ที่ค้าง');
    if(errorText){ lines.push(''); lines.push('สถานะ technical: ' + String(errorText).slice(0, 220)); }
    return lines.join('\n');
  }

  function resolveUsableReply(data, userText, ctx){
    data = data || {};
    var reply = data.reply || data.message || data.content || '';
    if(looksLikeUnusableReplyText(reply)){
      var direct = clientDirectAnswer(userText);
      if(direct) return direct;
      if(!reply){ return buildClientAnswerFallback(userText, ctx, 'empty_api_reply'); }
    }
    return reply || buildClientAnswerFallback(userText, ctx, 'missing_reply_field');
  }

  // v7.5.8.36 System Relationship Answer Selector
  // Connects the latest user message, prompt processor, context reader, command
  // matrix, local direct answer, API reply, and rescue fallback into one answer
  // selection path. The selector never controls buttons and never blocks render;
  // it only chooses the best available reply package before Visible Commit.
  function buildAnswerRelationshipContext(userText, ctx){
    ctx = ctx || {};
    var connectors = [];
    try { connectors = ((ctx.questionReading || {}).connectors || []).slice(0, 12); } catch(e0){ connectors = []; }
    var forms = (ctx.forms || []).slice ? (ctx.forms || []).slice(0, 10) : [];
    var direct = clientDirectAnswer(userText);
    var hasCodeIntent = /(?:code|plugin|wordpress|composer|popup|menu|api|debug|ระบบ|ปลั๊กอิน|โค้ด|แก้|พัฒนา)/iu.test(String(userText || ''));
    return {
      version:'7.5.8.36',
      latestUserMessage: trim(String(userText || '')).slice(0, 520),
      intent: ctx.intent || 'normal_question',
      forms: forms,
      connectors: connectors.length ? connectors : ['Latest User Message','Prompt Processor','Context Reader','Answer Router','Result Resolver'],
      selectionPriority: direct ? 'local_direct_answer_first' : (hasCodeIntent ? 'api_or_system_answer_then_rescue' : 'api_answer_then_local_rescue'),
      requiredBehavior:[
        'answer_latest_user_message_first',
        'ignore_internal_context_when_it_conflicts',
        'select_non_empty_usable_answer',
        'never_block_answer_render',
        'keep_prompt_processor_as_visual_status_only'
      ],
      directAnswerAvailable: !!direct
    };
  }

  function formatAnswerRelationshipSelector(rel){
    rel = rel || {};
    var lines = [];
    lines.push('System Relationship Answer Selector v7.5.8.36');
    lines.push('Latest user message: ' + (rel.latestUserMessage || '-'));
    lines.push('Intent: ' + (rel.intent || '-'));
    lines.push('Forms: ' + ((rel.forms || []).join(', ') || '-'));
    lines.push('Connected systems: ' + ((rel.connectors || []).join(' > ') || '-'));
    lines.push('Selection priority: ' + (rel.selectionPriority || '-'));
    lines.push('Direct answer available: ' + (rel.directAnswerAvailable ? 'yes' : 'no'));
    lines.push('Rules: ' + ((rel.requiredBehavior || []).join(' | ') || '-'));
    return lines.join('\n');
  }

  function scoreAnswerPackage(pkg, userText, ctx){
    pkg = pkg || {};
    var reply = clean(pkg.reply || '');
    var score = 0;
    var source = String(pkg.source || '');
    if(reply) score += 30;
    if(!looksLikeUnusableReplyText(reply)) score += 25;
    if(source === 'local_direct') score += 42;
    if(source === 'api') score += 36;
    if(source === 'relationship_pipeline_rescue') score += 18;
    if(source === 'local_rescue' || source === 'ajax_error' || source === 'race_failed') score += 12;
    if(pkg.error) score -= 18;
    var direct = clientDirectAnswer(userText);
    if(direct && trim(reply) === trim(direct)) score += 80;
    var latest = trim(String(userText || '')).replace(/\s+/g, ' ');
    if(latest && reply && reply.indexOf(latest.slice(0, Math.min(60, latest.length))) >= 0) score += 4;
    if(reply.length > 12) score += 6;
    if(reply.length > 1200 && !/code|โค้ด|plugin|wordpress|ระบบ|พัฒนา/iu.test(String(userText || ''))) score -= 8;
    return score;
  }

  function chooseRelationshipAnswerPackage(candidates, userText, ctx){
    candidates = (candidates || []).filter(Boolean);
    if(!candidates.length){
      return {reply: buildClientAnswerFallback(userText, ctx, 'relationship_selector_no_candidate'), data:{provider:'Relationship Answer Rescue'}, source:'relationship_rescue', error:null};
    }
    var best = null, bestScore = -9999;
    candidates.forEach(function(pkg){
      var score = scoreAnswerPackage(pkg, userText, ctx);
      try { pkg.selection_score = score; pkg.selection_engine = 'relationship_answer_selector_v75836'; } catch(e0){}
      if(score > bestScore){ best = pkg; bestScore = score; }
    });
    if(!best || looksLikeUnusableReplyText(best.reply)){
      best = {reply: buildClientAnswerFallback(userText, ctx, 'relationship_selector_unusable_best'), data:{provider:'Relationship Answer Rescue'}, source:'relationship_rescue', error:null, selection_score:bestScore};
    }
    try {
      best.data = best.data || {};
      if(!best.data.provider){ best.data.provider = 'Relationship Selector · ' + (best.source || 'selected'); }
      best.data.selection_score = bestScore;
    } catch(e1){}
    return best;
  }


  function needsConnectionEvidenceReport(userText, data, pkg){
    var text = String(userText || '');
    if(/(local|ภายใน|ภายนอก|external|internal|api|endpoint|เชื่อม|connect|sync|แหล่ง|source|provider|คัดเลือกคำตอบ|สัมพันธ์ระบบ|ไม่ได้เชื่อม|เหมือนไม่ได้)/iu.test(text)) return true;
    data = data || {};
    if(data && data.answer_source_line && /(api|external|ภายนอก|เชื่อม|source|provider|ระบบ)/iu.test(text)) return true;
    if(pkg && /api|fallback|rescue|local/i.test(String(pkg.source || ''))) return /(ระบบ|api|external|ภายนอก|เชื่อม|prompt|processor|answer|คำตอบ)/iu.test(text);
    return false;
  }

  function formatConnectionEvidenceReport(data, pipeline, pkg){
    data = data || {};
    pipeline = pipeline || {};
    var ev = data.connection_evidence || {};
    var provider = (data.provider || (pkg && pkg.data && pkg.data.provider) || (pkg && pkg.source) || 'unknown');
    var mode = data.mode || '';
    var connected = (ev.connected || []).slice(0,4).join(' · ') || 'ไม่มี provider ภายนอกที่ server ยืนยันว่าพร้อมใช้';
    var partial = (ev.partial || []).slice(0,3).join(' · ');
    var missing = (ev.missing || []).slice(0,4).join(' · ') || 'none';
    var route = (ev.route_order || pipeline.selectionOrder || []).slice(0,6).join(' > ') || '-';
    var sourceLine = data.answer_source_line || '';
    var selectedType = (/local|fallback|rescue|guard/i.test(String(provider)) || /local|fallback|rescue/i.test(String(pkg && pkg.source || ''))) ? 'Internal / Local fallback' : 'External API provider';
    var lines = [];
    lines.push('');
    lines.push('---');
    lines.push('**แหล่งคำตอบที่ใช้จริง**');
    lines.push('- คำตอบที่เลือก: ' + selectedType + ' · provider: ' + String(provider || 'unknown') + (mode ? ' · mode: ' + mode : ''));
    if(sourceLine) lines.push('- Source line: ' + sourceLine);
    lines.push('- ภายในที่เชื่อม: Latest message + Prompt Processor + Context Reader + System Command Matrix + Result Resolver');
    lines.push('- ภายนอกที่ server ยืนยัน: ' + connected);
    if(partial) lines.push('- ภายนอกแบบ partial/limited: ' + partial);
    lines.push('- ภายนอกที่ยังขาด/ต้องตั้งค่า: ' + missing);
    lines.push('- ลำดับคัดเลือก: ' + route);
    lines.push('- กติกา: ถ้าไม่มี provider/API candidate จริง ระบบต้องบอกว่าใช้ internal/local fallback ไม่อ้างว่าเชื่อมภายนอกแล้ว');
    return lines.join('\n');
  }

  function attachConnectionEvidenceToReply(reply, data, pipeline, pkg, userText){
    reply = clean(reply || '');
    if(!needsConnectionEvidenceReport(userText, data, pkg)) return reply;
    if(/\*\*แหล่งคำตอบที่ใช้จริง\*\*/.test(reply)) return reply;
    return reply + formatConnectionEvidenceReport(data || {}, pipeline || {}, pkg || {});
  }

  function removePromptProcessing(processor){
    processor = processor || state.promptProcessing;
    if(processor && processor.stopLoop){ processor.stopLoop(); }
    if(processor && processor.loopTimer){ clearTimeout(processor.loopTimer); processor.loopTimer = 0; }
    if(processor && processor.node && processor.node.parentNode){
      processor.node.parentNode.removeChild(processor.node);
    }
    if(state.promptProcessing === processor){ state.promptProcessing = null; }
  }


  // v7.5.8.30 Prompt Processor Time Card Restored
  // The full live processor card is temporary. After the real answer is visible,
  // keep a clean Processing Time report card (not a tiny chip) so the user can
  // verify elapsed time, interpretation, output target, communication skill,
  // system connectors, and final status.
  function promptProcessorElapsedMs(processor){
    if(processor && processor.node){
      var attr = Number(processor.node.getAttribute('data-processing-ms') || 0);
      if(attr > 0){ return attr; }
    }
    var started = processor && processor.startedAt ? processor.startedAt : Date.now();
    return Math.max(0, Date.now() - started);
  }
  function escapeProcessorTimeText(text){
    return String(text == null ? '' : text).replace(/[&<>"']/g, function(ch){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch] || ch;
    });
  }
  function processorTimeCardLine(label, value){
    return '<div class="processor-time-card-line"><span class="processor-time-card-label">' +
      escapeProcessorTimeText(label) + '</span><span class="processor-time-card-value">' +
      escapeProcessorTimeText(value || '-') + '</span></div>';
  }
  function buildProcessorTimeCardHtml(processor, elapsedMs, status){
    processor = processor || {};
    var ctx = processor.ctx || buildAllFormContext(processor.userText || '');
    var it = ctx.interpretation || buildInterpretationResult(processor.userText || '', ctx);
    var desired = ctx.desiredOutput || resolveDesiredOutput(processor.userText || '', ctx);
    var connector = promptProcessorConnectorText(ctx);
    var elapsed = formatProcessingDuration(elapsedMs);
    var stateText = status === 'error' ? 'คำตอบสำรองถูกแสดงแล้ว' : 'พร้อมแสดงคำตอบจริง';
    var communication = (((ctx.subtext||{}).responseMove) || 'ตอบผลลัพธ์ที่ใช้ได้ก่อน แล้วค่อยอธิบายสั้น ๆ');
    return '<div class="processor-time-card-inner">' +
      '<div class="processor-time-card-head"><span class="processor-time-card-badge">Processing Time</span>' +
      '<span class="processor-time-card-title">ประมวลผล Prompt เสร็จแล้ว</span></div>' +
      processorTimeCardLine('เวลาในการประมวลผล', elapsed) +
      processorTimeCardLine('ตีความ', (it.label || ctx.intent || 'auto') + ' · ความมั่นใจ ' + (it.confidence || ctx.communicationScore || 0) + '/100') +
      processorTimeCardLine('ผลลัพธ์ที่ต้องแสดง', desired.primaryResult) +
      processorTimeCardLine('ทักษะสื่อสาร', communication) +
      processorTimeCardLine('เชื่อมระบบ', connector) +
      processorTimeCardLine('สถานะ', stateText) +
      '</div>';
  }
  function keepPromptProcessingTimeAfterAnswer(processor, reason){
    if(!processor || !processor.node){ return false; }
    var node = processor.node;
    if(!node.parentNode){ return false; }
    if(node.getAttribute('data-prompt-time-kept') === '1'){ return false; }
    var elapsedMs = promptProcessorElapsedMs(processor);
    try { if(processor.stopLoop){ processor.stopLoop(); } } catch(e){}
    try { if(processor.loopTimer){ clearTimeout(processor.loopTimer); processor.loopTimer = 0; } } catch(e2){}
    node.classList.remove('is-slide-typing','is-auto-dismissing');
    node.classList.add('processor-time-kept');
    node.removeAttribute('aria-hidden');
    node.setAttribute('data-prompt-processing','time-kept');
    node.setAttribute('data-prompt-time-kept','1');
    node.setAttribute('data-dismiss-reason', reason || 'answer-visible-time-kept');
    node.setAttribute('data-processing-ms', String(Math.round(elapsedMs)));
    node.style.maxHeight = '';
    node.style.opacity = '';
    node.style.transform = '';
    node.style.marginTop = '';
    node.style.marginBottom = '';
    node.style.overflow = '';
    var meta = node.querySelector('.message-meta');
    var durationText = formatProcessingDuration(elapsedMs);
    if(meta){ meta.textContent = 'ประมวลผลแล้ว ' + durationText + ' · เวลาในการประมวลผล'; }
    var bubble = node.querySelector('.bubble');
    if(bubble){
      bubble.innerHTML = buildProcessorTimeCardHtml(processor, elapsedMs, (reason === 'answer-error-time-kept' ? 'error' : 'success'));
      bubble.setAttribute('data-processor-visible-state','time-card-kept');
    }
    all('.aira-processing-dots, .processor-inline-dots, .message-choice-continuation, .message-continuation-choices, .message-answer-insights', node).forEach(function(el){
      if(el && el.parentNode){ el.parentNode.removeChild(el); }
    });
    if(state.promptProcessing === processor){ state.promptProcessing = null; }
    try { setPromptAnswerGateState('answer-released-processor-time-kept'); } catch(e3){}
    try { scheduleMeasure(); scrollBottom(false); } catch(e4){}
    return true;
  }
  // Backward-compatible name used by the existing send flow. It no longer removes
  // the processor node; it collapses the full card into the time receipt only.
  function dismissPromptProcessingAfterAnswer(processor, reason){
    return keepPromptProcessingTimeAfterAnswer(processor, reason || 'answer-visible-time-kept');
  }


  function addPromptProcessingSlide(ctx, userText){
    removePromptProcessing();
    var text = buildPromptProcessingText(ctx, userText);
    var node = addMessage('assistant', text, 'กำลังอ่านข้อมูล', true);
    var resolved = false, resolveFn = function(){};
    var done = new Promise(function(resolve){ resolveFn = resolve; });
    var processor = {node:node, text:text, done:done, startedAt:Date.now(), ctx:ctx, userText:userText || '', loopTimer:0, loopStarted:false, stopLoop:null};
    var fallback = w.setTimeout(function(){
      if(!resolved){
        resolved = true;
        startPromptProcessingLoop(processor);
        resolveFn();
      }
    }, prefersReducedMotion() ? 0 : 1650);
    if(node){
      node.classList.add('prompt-processing');
      node.setAttribute('data-prompt-processing','on');
      node.setAttribute('data-no-insight-numbers','prompt-processor');
      ensurePromptProcessingDots(node);
      removePromptProcessorInsightRows(node);
      var bubble = node.querySelector('.bubble');
      if(bubble){ bubble.textContent = ''; }
      animateAssistantText(node, text, function(){
        // v7.5.8.32: if the answer gate already forced the processor to complete,
        // do not let this older animation callback revert it to reading.
        var currentProcessorState = node.getAttribute('data-prompt-processing') || '';
        if(/complete|time-kept|complete-locking/i.test(currentProcessorState)){
          if(!resolved){ resolved = true; clearTimeout(fallback); resolveFn(); }
          return;
        }
        node.setAttribute('data-prompt-processing','reading');
        removePromptProcessorInsightRows(node);
        startPromptProcessingLoop(processor);
        if(!resolved){ resolved = true; clearTimeout(fallback); resolveFn(); }
      });
    } else {
      if(!resolved){ resolved = true; clearTimeout(fallback); resolveFn(); }
    }
    state.promptProcessing = processor;
    return state.promptProcessing;
  }


  function renderChat(){
    var el = chatLog(); if(!el) return;
    el.innerHTML = '';
    var room = activeRoom(); state.messages = room.messages || [];
    if(!state.messages.length){
      el.innerHTML = '<div class="welcome"><div class="welcome-mark">A</div><h1>วันนี้ให้ AiRA ช่วยอะไร?</h1><p>Clean Slate · Vision Reader · Auto Question · GPT Answer</p><div class="welcome-chips"><button type="button" data-action="quick-prompt" data-prompt="ช่วยตรวจและแก้ UI/UX WordPress admin ให้คล้าย GPT แบบไม่ซ้อน">ตรวจ UI/UX</button><button type="button" data-action="quick-prompt" data-prompt="ช่วยเขียนโค้ดปลั๊กอิน WordPress พร้อมอธิบายไฟล์และวิธีติดตั้ง">สร้าง Plugin</button><button type="button" data-action="quick-prompt" data-prompt="ช่วยสรุปปัญหาระบบและจัดลำดับสิ่งที่ควรแก้ก่อน">สรุปปัญหาระบบ</button></div></div>';
      return;
    }
    state.messages.forEach(function(m, i){ el.appendChild(messageEl(m.role, m.text, m.meta || '', i, {forceCollapsed: !!m.collapsed})); });
    updateAllCodeRealityPanels(el);
    scrollBottom(true);
  }

  function resizeInput(){
    var input = byId('composerInput'); if(!input) return;
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, w.innerWidth <= 782 ? 132 : 160) + 'px';
    var send = byId('sendBtn'); if(send) send.disabled = (trim(input.value) === '' && !composerHasContinuation()) || state.sending;
    scheduleMeasure();
  }

  function normalizeCodeLang(lang){
    lang = trim(lang || '').toLowerCase().replace(/[^a-z0-9_+#.-]/g,'');
    if(!lang) return 'text';
    if(lang === 'js') return 'javascript';
    if(lang === 'ts') return 'typescript';
    if(lang === 'md') return 'markdown';
    if(lang === 'htm') return 'html';
    if(lang === 'svg+xml') return 'svg';
    return lang.slice(0,32);
  }
  function extensionForLang(lang){
    lang = normalizeCodeLang(lang);
    var map = {javascript:'js',typescript:'ts',html:'html',css:'css',php:'php',json:'json',markdown:'md',xml:'xml',svg:'svg',python:'py',bash:'sh',shell:'sh',sql:'sql',yaml:'yml',yml:'yml',txt:'txt',text:'txt'};
    return map[lang] || (lang.length <= 8 ? lang : 'txt');
  }
  function mimeForLang(lang){
    lang = normalizeCodeLang(lang);
    var map = {html:'text/html;charset=utf-8',css:'text/css;charset=utf-8',javascript:'text/javascript;charset=utf-8',typescript:'text/plain;charset=utf-8',php:'text/plain;charset=utf-8',json:'application/json;charset=utf-8',markdown:'text/markdown;charset=utf-8',svg:'image/svg+xml;charset=utf-8',xml:'application/xml;charset=utf-8'};
    return map[lang] || 'text/plain;charset=utf-8';
  }
  function extractCodeBlocks(text){
    var source = clean(text), blocks = [], re = /```\s*([a-zA-Z0-9_+#.-]*)\s*\n([\s\S]*?)```/g, m;
    while((m = re.exec(source)) !== null){
      blocks.push({lang:normalizeCodeLang(m[1] || 'text'), code:clean(m[2]).replace(/\n$/,'')});
      if(blocks.length > 24) break;
    }
    return blocks;
  }
  function extractCode(text){
    var blocks = extractCodeBlocks(text);
    return blocks.length ? blocks[0].code : '';
  }
  function downloadNameForCode(lang, index){
    return 'aira-code-' + String((index || 0) + 1) + '.' + extensionForLang(lang);
  }
  function looksPreviewable(lang, code){
    lang = normalizeCodeLang(lang);
    code = clean(code);
    return lang === 'html' || lang === 'svg' || lang === 'css' || lang === 'javascript' || lang === 'markdown' || lang === 'php' || /<html[\s>]|<!doctype\s+html|<body[\s>]|<div[\s>]|<section[\s>]|<svg[\s>]|<\?php|add_action\s*\(|add_shortcode\s*\(/i.test(code);
  }
  function isPhpLikeCode(lang, code){
    lang = normalizeCodeLang(lang); code = clean(code);
    return lang === 'php' || /<\?php|add_action\s*\(|add_shortcode\s*\(|Plugin\s+Name\s*:/i.test(code);
  }
  function codeByteSizeLabel(code){
    var bytes = 0;
    try { bytes = new Blob([clean(code)]).size; } catch(e){ bytes = clean(code).length; }
    if(bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    if(bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return bytes + ' B';
  }
  function detectCodeParts(lang, code){
    lang = normalizeCodeLang(lang); code = clean(code);
    var parts = [];
    function add(key, label, reason){ parts.push({key:key, label:label, reason:reason || ''}); }
    if(isPhpLikeCode(lang, code)) add('plugin','WordPress Plugin','มี Plugin Header / PHP runtime');
    if(/add_(?:menu|submenu)_page\s*\(/i.test(code)) add('module','Admin Module','มีหน้าเมนูในแอดมิน');
    if(/add_shortcode\s*\(/i.test(code)) add('component','Shortcode Component','เรียกใช้งานในหน้า/โพสต์ได้');
    if(/register_widget\s*\(|extends\s+WP_Widget/i.test(code)) add('widget','Widget','มีโครงสร้าง Widget');
    if(/register_block_type\s*\(|block\.json|wp\.blocks|Gutenberg/i.test(code)) add('block','Gutenberg Block','รองรับ Block Editor');
    if(/Elementor\\Widget_Base|elementor\/widgets|class\s+.*Widget.*extends/i.test(code)) add('elementor','Elementor Widget','รองรับ Elementor');
    if(/wp_ajax_|admin-ajax\.php|add_action\s*\(\s*["']wp_ajax_/i.test(code)) add('ajax','AJAX Handler','มี action สำหรับ admin-ajax');
    if(/register_rest_route\s*\(/i.test(code)) add('rest','REST API','มี endpoint REST');
    if(/wp_enqueue_(?:script|style)\s*\(/i.test(code)) add('assets','Assets','มี CSS/JS ที่ต้อง enqueue');
    if(/theme|functions\.php|after_setup_theme|add_theme_support/i.test(code)) add('theme','Theme Related','เกี่ยวข้องกับธีม/functions.php');
    if(/nonce|check_ajax_referer|current_user_can|sanitize_|esc_html|esc_attr|wp_kses/i.test(code)) add('security','Security','มี nonce/capability/sanitize/escape');
    if(!parts.length){
      if(lang === 'html' || /<[^>]+>/i.test(code)) add('component','HTML Component','ส่วนแสดงผลหน้าเว็บ');
      else if(lang === 'css') add('theme','Style / Theme CSS','ส่วนตกแต่งหน้าตา');
      else if(lang === 'javascript') add('module','JavaScript Module','ส่วนพฤติกรรม/interaction');
      else add('file','Code File','ไฟล์โค้ดทั่วไป');
    }
    var seen = {}, out = [];
    parts.forEach(function(p){ if(!seen[p.key]){ seen[p.key]=1; out.push(p); } });
    return out;
  }
  function codeRelationshipSentence(parts, isWp){
    var labels = (parts || []).map(function(p){ return p.label; });
    if(isWp){
      return 'ความสัมพันธ์ระบบ: Plugin → Module/Component → Widget/Block/Theme → Assets → AJAX/REST → Security';
    }
    return 'ความสัมพันธ์ไฟล์: ' + (labels.length ? labels.join(' → ') : 'Code File');
  }
  function codeVersionLineText(lang, code, parts, isWpRelated, index){
    lang = normalizeCodeLang(lang);
    code = clean(code);
    var detectedVersion = isPhpLikeCode(lang, code) ? (headerValueFromPhp(code, 'Version') || '-') : '-';
    var pluginName = isPhpLikeCode(lang, code) ? (headerValueFromPhp(code, 'Plugin Name') || '') : '';
    var type = isWpRelated ? (pluginName ? 'WordPress Plugin' : 'WordPress Related') : ((parts && parts[0] && parts[0].label) || 'Code File');
    var summaryParts = (parts || []).map(function(p){ return p.label; }).slice(0, 4).join(' / ');
    var ready = isWordPressInstallableCode(lang, code) ? 'Preview / Debug / Install / Upgrade ได้' : (looksPreviewable(lang, code) ? 'Result / Debug / Upgrade ได้' : 'Copy / Download / Debug ได้');
    return 'Version: ' + detectedVersion + ' · ' + type + ' · ' + ready + (summaryParts ? ' · ' + summaryParts : '') + ' · ' + downloadNameForCode(lang, index || 0);
  }
  function isWordPressInstallableCode(lang, code){
    return isPhpLikeCode(lang, code) && /<\?php/i.test(code) && /Plugin\s+Name\s*:/i.test(code);
  }
  function authorizedPreviewAccess(){
    var identity = cfg.authorizedIdentity || (state && state.authorizedIdentity) || {};
    var access = cfg.previewAccess || {};
    var authorized = !!cfg.canManage && (access.canPreview !== false) && (identity.authorized !== false);
    return {
      authorized: authorized,
      authorizedOnly: access.authorizedOnly !== false,
      capability: access.capability || (identity.capability || 'manage_options'),
      policy: access.policy || (identity.policy || 'same_wordpress_user_with_required_capability'),
      userId: access.userId || cfg.userId || identity.userId || 0,
      fingerprint: identity.fingerprint || '',
      authorizedId: identity.authorizedId || ''
    };
  }
  function requireAuthorizedPreviewAccess(){
    var access = authorizedPreviewAccess();
    if(!access.authorized){
      toast('Preview Sandbox เปิดได้เฉพาะผู้มีสิทธิ์: ' + access.capability, 'error');
      return false;
    }
    return true;
  }
  function safeStyle(code){ return clean(code).replace(/<\/style/gi,'<\\/style'); }
  function safeScript(code){ return clean(code).replace(/<\/script/gi,'<\\/script'); }
  function previewBaseStyle(){
    return '<style>html,body{margin:0;min-height:100%;font:15px/1.55 system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#111827;background:#f7f7f5;-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility}body{padding:18px}.preview-wrap{max-width:920px;margin:auto}.preview-card{border:1px solid #D6DDD8;border-radius:22px;background:#fff;box-shadow:0 18px 48px rgba(17,24,39,.14);padding:22px;color:#111827}.preview-card h1{margin:0 0 8px;font-size:24px;color:#111827}.preview-card p{margin:0 0 14px;color:#374151}.preview-card small,.muted,.safe,.file span,.actual-widget span{color:#374151!important}button,.btn{border:1px solid #14532D;border-radius:999px;background:#1E6B45;color:#fff;padding:10px 16px;font:inherit;font-weight:800}button:hover,.btn:hover{background:#14532D;color:#fff}.box{margin-top:14px;border-radius:18px;background:#F0FDF4;color:#14532D;border:1px solid #86B89C;padding:16px}pre,.api-console,.live-console{white-space:pre-wrap;overflow:auto;background:#0B1220!important;color:#E5E7EB!important;border-radius:16px}a{color:#14532D}*:focus-visible{outline:3px solid rgba(249,115,22,.55);outline-offset:2px}.badge.orange,.live-step.warn b{background:#FFF1E8!important;color:#7C2D12!important;border-color:#FDBA74!important}.badge.green,.pill,.live-step b{background:#E9F8EF!important;color:#14532D!important;border-color:#86B89C!important}.badge.ok{background:#111827!important;color:#fff!important}.screen,.card,.hero,.authorized-preview-dock,.safe{color:#111827!important}.screen p,.card p,.hero p,.authorized-preview-dock small,.safe{color:#374151!important}</style>';
  }
  function defaultResultHtml(css, js, label){
    var script = js ? '<script>const __airaOut=document.getElementById("airaConsole");const __oldLog=console.log;console.log=(...a)=>{if(__airaOut){__airaOut.textContent += ( __airaOut.textContent ? "\\n" : "" ) + a.map(String).join(" ");}__oldLog.apply(console,a)};try{'+safeScript(js)+'\n}catch(e){if(__airaOut){__airaOut.textContent="JavaScript Error: "+e.message}}<\/script>' : '';
    return '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'+previewBaseStyle()+'<style>'+safeStyle(css || '')+'</style></head><body><main class="preview-wrap"><section class="preview-card"><h1>'+esc(label || 'Rendered Result')+'</h1><p>ผลลัพธ์นี้แสดงจากโค้ดที่ AiRA สร้างไว้ใน sandbox</p><button type="button">Sample Button</button><div class="box">พื้นที่ตัวอย่างสำหรับ CSS/JS</div><pre id="airaConsole" aria-live="polite"></pre></section></main>'+script+'</body></html>';
  }
  function headerValueFromPhp(code, label){
    code = clean(code); label = String(label || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    var re = new RegExp(label + '\\s*:\\s*([^\\r\\n*]+)', 'i');
    var m = code.match(re);
    return m ? trim(m[1].replace(/\*\/$/, '')) : '';
  }
  function collectRegexMatches(code, re, limit){
    var out = [], m; code = clean(code); limit = limit || 10;
    while((m = re.exec(code)) !== null){
      if(m[1] && out.indexOf(m[1]) === -1) out.push(m[1]);
      if(out.length >= limit) break;
    }
    return out;
  }
  function extractPhpMixedHtml(code){
    code = clean(code);
    var outside = code.replace(/<\?php[\s\S]*?\?>/gi, ' ');
    var htmlParts = '', m;
    var stringHtmlRe = /(?:echo|print|return)\s*(?:wp_kses_post\(|wp_kses\(|__\(|esc_html__?\()?(["'])([\s\S]*?)\1\)?\s*;/gi;
    while((m = stringHtmlRe.exec(code)) !== null){
      if(/[<][a-z][\s\S]*[>]/i.test(m[2])) htmlParts += '\n' + m[2];
      if(htmlParts.length > 16000) break;
    }
    var heredocRe = /<<<["']?([A-Z0-9_]+)["']?\s*\n([\s\S]*?)\n\1\s*;/gi;
    while((m = heredocRe.exec(code)) !== null){
      if(/[<][a-z][\s\S]*[>]/i.test(m[2])) htmlParts += '\n' + m[2];
      if(htmlParts.length > 20000) break;
    }
    var html = trim(outside + '\n' + htmlParts);
    return /<[a-z][\s\S]*>/i.test(html) ? html : '';
  }
  function sanitizeRenderedHtml(html){
    html = clean(html);
    html = html.replace(/<script[\s\S]*?<\/script>/gi, '');
    html = html.replace(/<iframe[\s\S]*?<\/iframe>/gi, '');
    html = html.replace(/<object[\s\S]*?<\/object>/gi, '');
    html = html.replace(/<embed[\s\S]*?>/gi, '');
    html = html.replace(/\son[a-z]+\s*=\s*(["'])[\s\S]*?\1/gi, '');
    html = html.replace(/\s(href|src)\s*=\s*(["'])\s*javascript:[\s\S]*?\2/gi, ' $1="#"');
    return html;
  }
  function buildActualCodePreviewHtml(info, mixedHtml){
    info = info || {};
    var actual = sanitizeRenderedHtml(mixedHtml || '');
    if(actual){
      return '<div class="actual-code-preview"><div class="actual-code-head"><b>หน้าจริงจากการเขียนโค้ด</b><span>ดึงจาก HTML / echo / return / heredoc ใน code block โดยตรง</span></div><div class="actual-code-stage">'+actual+'</div></div>';
    }
    var name = info.name || 'AiRA Generated Plugin';
    var shortcode = info.shortcodes && info.shortcodes.length ? '['+info.shortcodes[0]+']' : '['+asciiSlug(name, 'aira_generated').replace(/-/g,'_')+']';
    return '<div class="actual-code-preview generated"><div class="actual-code-head"><b>หน้าจริงจากโครงสร้างโค้ด</b><span>ยังไม่พบ HTML ตรง ๆ จึงประกอบหน้าจาก Plugin Header, shortcode, menu, REST/AJAX และ component ที่ตรวจพบ</span></div><div class="actual-generated-screen"><header><b>'+esc(name)+'</b><small>'+esc(info.desc || 'Generated by AiRA Studio')+'</small></header><main><div class="actual-widget"><span>Shortcode</span><strong>'+esc(shortcode)+'</strong></div><div class="actual-widget"><span>Version</span><strong>'+esc(info.version || '1.0.0')+'</strong></div><button type="button">Primary Action</button></main></div></div>';
  }
  function phpWordPressResultHtml(code, label){
    code = clean(code);
    var mixedHtml = extractPhpMixedHtml(code);
    var name = headerValueFromPhp(code, 'Plugin Name') || (label || 'WordPress / PHP Result');
    var desc = headerValueFromPhp(code, 'Description') || 'โค้ดนี้เป็น PHP/WordPress ระบบจึงสร้าง Sandbox ให้ลองใช้งานตรงนี้ก่อนติดตั้งจริง';
    var version = headerValueFromPhp(code, 'Version') || '-';
    var author = headerValueFromPhp(code, 'Author') || '-';
    var shortcodes = collectRegexMatches(code, /add_shortcode\s*\(\s*["']([^"']+)["']/gi, 8);
    var actions = collectRegexMatches(code, /add_action\s*\(\s*["']([^"']+)["']/gi, 10);
    var filters = collectRegexMatches(code, /add_filter\s*\(\s*["']([^"']+)["']/gi, 8);
    var menus = collectRegexMatches(code, /add_(?:menu|submenu)_page\s*\(\s*["']([^"']+)["']/gi, 6);
    var rest = collectRegexMatches(code, /register_rest_route\s*\(\s*["']([^"']+)["']/gi, 6);
    var ajax = collectRegexMatches(code, /wp_ajax_(?:nopriv_)?([a-z0-9_\-]+)/gi, 8);
    var hasRuntimeParts = shortcodes.length || actions.length || filters.length || menus.length || rest.length || ajax.length;
    function pillList(items, empty){
      if(!items || !items.length) return '<span class="muted">'+esc(empty || 'ไม่พบ')+'</span>';
      return items.map(function(x){ return '<span class="pill">'+esc(x)+'</span>'; }).join('');
    }
    function optionList(items, prefix){
      if(!items || !items.length) return '<option value="">'+esc(prefix || 'ไม่พบรายการ')+'</option>';
      return items.map(function(x){ return '<option value="'+esc(x)+'">'+esc(x)+'</option>'; }).join('');
    }
    var shortcodeDemo = shortcodes.length ? '[' + shortcodes[0] + ']' : '[aira_demo]';
    var menuTitle = menus.length ? menus[0] : name;
    var safeMixed = buildActualCodePreviewHtml({name:name, desc:desc, version:version, shortcodes:shortcodes}, mixedHtml);
    var html = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'+previewBaseStyle()+'<style>' +
      'body{background:#f6f8f6;color:#111}.wp-preview{max-width:980px;margin:auto}.wp-hero{display:flex;gap:14px;align-items:flex-start;border:1px solid rgba(30,107,69,.16);background:linear-gradient(180deg,#fff,#f8fbf9);border-radius:24px;padding:18px;box-shadow:0 18px 46px rgba(17,24,39,.10)}.wp-icon{width:48px;height:48px;border-radius:16px;background:#1E6B45;color:#fff;display:grid;place-items:center;font-weight:800;font-size:22px;flex:0 0 auto}.wp-hero h1{font-size:23px;margin:0 0 6px}.wp-hero p{margin:0;color:rgba(17,17,17,.68)}.status{margin-top:13px;display:flex;flex-wrap:wrap;gap:8px}.badge{border:1px solid rgba(30,107,69,.18);background:#ecf8f0;color:#1E6B45;border-radius:999px;padding:7px 10px;font-size:12px;font-weight:800}.badge.warn{border-color:rgba(249,115,22,.25);background:#fff4eb;color:#b45309}.badge.ok{background:#111;color:#fff;border-color:#111}.sandbox-tabs{display:flex;flex-wrap:wrap;gap:8px;margin:14px 0}.sandbox-tabs button{background:#fff;color:#111;border:1px solid rgba(17,17,17,.12);box-shadow:0 6px 18px rgba(17,24,39,.06)}.sandbox-tabs button.active{background:#1E6B45;color:#fff;border-color:#1E6B45}.sandbox-screen{display:none;border:1px solid rgba(17,17,17,.10);background:#fff;border-radius:22px;padding:16px;box-shadow:0 12px 32px rgba(17,24,39,.07)}.sandbox-screen.active{display:block}.sandbox-screen h2{font-size:16px;margin:0 0 10px}.admin-shell{border:1px solid rgba(17,17,17,.10);border-radius:18px;overflow:hidden;background:#f7f7f5}.admin-bar{background:#1d2327;color:#fff;padding:10px 12px;font-weight:800}.admin-content{padding:16px}.field{display:grid;gap:5px;margin:10px 0}.field input,.field select{border:1px solid rgba(17,17,17,.14);border-radius:12px;padding:10px;background:#fff}.btn-row{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}.btn-row button{background:#1E6B45;color:#fff;border:1px solid #14532D;font-weight:900;box-shadow:0 6px 16px rgba(30,107,69,.18)}.btn-row button.secondary{background:#F97316;color:#fff;border-color:#9A3412}.btn-row button[disabled],button[disabled],.button.disabled,[aria-disabled=true]{opacity:1!important;background:#E5E7EB!important;color:#374151!important;border-color:#94A3B8!important}.notice{margin-top:12px;border-radius:14px;background:#ecfdf3;border:1px solid #86B89C;padding:10px;color:#14532D;font-weight:700}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:14px}.card{border:1px solid rgba(17,17,17,.10);background:#fff;border-radius:18px;padding:14px}.card h3{font-size:14px;margin:0 0 9px}.meta{display:grid;gap:6px}.row{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid rgba(17,17,17,.06);padding:7px 0}.row:last-child{border-bottom:0}.muted{color:rgba(17,17,17,.58)}.pills{display:flex;flex-wrap:wrap;gap:7px}.pill{display:inline-flex;border:1px solid rgba(17,17,17,.10);background:#f3f5f4;border-radius:999px;padding:6px 9px;font-size:12px}.shortcode-box{border:1px dashed rgba(30,107,69,.32);border-radius:18px;padding:14px;background:#fbfffc}.shortcode-output{margin-top:12px;border-radius:16px;background:#111;color:#fff;padding:14px}.rendered-html{border:1px solid rgba(17,17,17,.10);border-radius:18px;padding:14px;background:#fff;overflow:auto}.empty-ui{display:grid;gap:6px;border:1px dashed rgba(249,115,22,.35);background:#fff7ed;color:#9a3412;border-radius:16px;padding:14px}.safe-note{margin-top:14px;border:1px dashed rgba(17,17,17,.18);background:#fff;border-radius:18px;padding:12px;color:rgba(17,17,17,.66);font-size:13px}.safe-note b{display:block;color:#111;margin-bottom:4px}@media(max-width:680px){body{padding:12px}.wp-hero{padding:15px;border-radius:20px}.grid{grid-template-columns:1fr}.wp-hero h1{font-size:20px}.wp-icon{width:42px;height:42px;border-radius:14px}.sandbox-screen{padding:13px}.admin-content{padding:13px}}' +
      '</style></head><body><main class="wp-preview"><section class="wp-hero"><div class="wp-icon">W</div><div><h1>'+esc(name)+'</h1><p>'+esc(desc)+'</p><div class="status"><span class="badge ok">ใช้ตรงนี้ก่อนติดตั้ง</span><span class="badge">Pre-install Sandbox</span><span class="badge warn">ไม่เขียนฐานข้อมูล / ไม่ Activate</span></div></div></section>'+
      '<div class="sandbox-tabs"><button type="button" class="active" data-tab="admin">Admin Preview</button><button type="button" data-tab="shortcode">Shortcode</button><button type="button" data-tab="html">Rendered HTML</button><button type="button" data-tab="map">System Map</button></div>'+
      '<section class="sandbox-screen active" data-screen="admin"><h2>ทดลองหน้าแอดมินก่อนติดตั้ง</h2><div class="admin-shell"><div class="admin-bar">WordPress Admin · '+esc(menuTitle)+'</div><div class="admin-content"><p class="muted">นี่คือพื้นที่ทดลองใช้จากโค้ดก่อนติดตั้งจริง ใช้สำหรับเช็ก layout, ปุ่ม, form, module และ flow หลัก</p><label class="field"><span>Plugin</span><input value="'+esc(name)+'" readonly></label><label class="field"><span>Mode</span><select id="sandboxMode"><option>Preview</option><option>Debug</option><option>Production Ready</option></select></label><div class="btn-row"><button type="button" id="sandboxSave">บันทึกตัวอย่าง</button><button type="button" class="secondary" id="sandboxRun">ทดสอบ Flow</button></div><div class="notice" id="sandboxNotice">พร้อมทดลองก่อนติดตั้ง · ยังไม่มีการเขียนไฟล์หรือเปิดใช้งานปลั๊กอิน</div></div></div></section>'+
      '<section class="sandbox-screen" data-screen="shortcode"><h2>ทดลอง Shortcode / Component</h2><div class="shortcode-box"><div><b>Shortcode ที่ตรวจพบ</b><div class="pills">'+pillList(shortcodes, 'ยังไม่พบ shortcode ในโค้ดนี้')+'</div></div><p class="muted">ตัวอย่างเรียกใช้งาน: <b>'+esc(shortcodeDemo)+'</b></p><div class="shortcode-output"><b>'+esc(name)+'</b><br><span>ผลลัพธ์ตัวอย่างของ component ก่อนติดตั้งจริง</span></div></div></section>'+
      '<section class="sandbox-screen" data-screen="html"><h2>HTML/UI ที่ดึงจากโค้ด</h2><div class="rendered-html">'+safeMixed+'</div></section>'+
      '<section class="sandbox-screen" data-screen="map"><h2>System Map จากโค้ด</h2><div class="grid"><div class="card"><h3>Plugin Info</h3><div class="meta"><div class="row"><span class="muted">File</span><b>'+esc(label || 'aira-code.php')+'</b></div><div class="row"><span class="muted">Version</span><b>'+esc(version)+'</b></div><div class="row"><span class="muted">Author</span><b>'+esc(author)+'</b></div></div></div><div class="card"><h3>Hooks</h3><div class="pills">'+pillList(actions.concat(filters), hasRuntimeParts ? '' : 'ยังไม่พบ hook/shortcode/menu')+'</div></div><div class="card"><h3>Shortcode / AJAX / REST</h3><div class="pills">'+pillList(shortcodes.concat(ajax).concat(rest), 'ไม่พบ shortcode/ajax/rest route')+'</div></div><div class="card"><h3>Admin Menu</h3><div class="pills">'+pillList(menus, 'ไม่พบเมนูแอดมินในโค้ดนี้')+'</div></div></div></section>'+
      '<section class="safe-note"><b>ขอบเขตการทดลองก่อนติดตั้ง</b>Sandbox นี้ทำให้ลอง flow และหน้าตาจากโค้ดได้ในที่นี้ก่อนติดตั้งจริง แต่จะไม่ execute PHP ดิบ ไม่เขียนฐานข้อมูล และไม่เรียก hook จริง เพื่อป้องกันโค้ดอันตราย ถ้าต้องการ runtime จริง 100% ให้กดติดตั้ง/อัปเกรดหลังตรวจแล้ว</section></main>'+
      '<script>(function(){var q=function(s,r){return (r||document).querySelector(s)},qa=function(s,r){return Array.prototype.slice.call((r||document).querySelectorAll(s))};qa(".sandbox-tabs button").forEach(function(b){b.addEventListener("click",function(){qa(".sandbox-tabs button").forEach(function(x){x.classList.remove("active")});qa(".sandbox-screen").forEach(function(x){x.classList.remove("active")});b.classList.add("active");var screen=q("[data-screen="+JSON.stringify(b.getAttribute("data-tab"))+"]");if(screen)screen.classList.add("active")})});var n=q("#sandboxNotice"), save=q("#sandboxSave"), run=q("#sandboxRun");if(save)save.onclick=function(){if(n)n.textContent="บันทึกตัวอย่างสำเร็จใน Sandbox · ยังไม่เขียนฐานข้อมูลจริง"};if(run)run.onclick=function(){if(n)n.textContent="ทดสอบ Flow สำเร็จ · พร้อมตรวจต่อหรือกดติดตั้งหลังพอใจ"};})();<\/script></body></html>';
    return html;
  }

  function injectAssetsIntoHtml(html, css, js){
    html = clean(html);
    var headAssets = '<meta name="viewport" content="width=device-width,initial-scale=1">' + previewBaseStyle() + (css ? '<style data-aira-combined-css>'+safeStyle(css)+'</style>' : '');
    var bodyAssets = js ? '<script data-aira-combined-js>try{'+safeScript(js)+'\n}catch(e){document.body.insertAdjacentHTML("beforeend","<pre style=\\"white-space:pre-wrap;color:#b91c1c;padding:12px\\">JavaScript Error: "+String(e.message)+"</pre>")}<\/script>' : '';
    if(!/<html[\s>]/i.test(html)) html = '<!doctype html><html><head><meta charset="utf-8"></head><body>' + html + '</body></html>';
    if(/<head[\s>]/i.test(html)) html = html.replace(/<head([^>]*)>/i, '<head$1>' + headAssets);
    else html = html.replace(/<html([^>]*)>/i, '<html$1><head>' + headAssets + '</head>');
    if(/<\/body>/i.test(html)) html = html.replace(/<\/body>/i, bodyAssets + '</body>');
    else html += bodyAssets;
    return html;
  }
  function previewSrcDoc(lang, code){
    lang = normalizeCodeLang(lang); code = clean(code);
    if(isPhpLikeCode(lang, code)) return phpWordPressResultHtmlRealerV740(code, downloadNameForCode(lang, 0));
    if(lang === 'html' || /<html[\s>]|<!doctype\s+html|<body[\s>]|<div[\s>]|<section[\s>]/i.test(code)) return injectAssetsIntoHtml(code, '', '');
    if(lang === 'svg' || /<svg[\s>]/i.test(code)) return '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'+previewBaseStyle()+'<body style="display:grid;place-items:center;min-height:100vh;padding:18px">' + code + '</body>';
    if(lang === 'css') return defaultResultHtml(code, '', 'CSS Result');
    if(lang === 'javascript') return defaultResultHtml('', code, 'JavaScript Result');
    if(lang === 'markdown') return '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'+previewBaseStyle()+'<body><main class="preview-wrap"><section class="preview-card"><pre>'+esc(code)+'</pre></section></main></body>';
    return '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'+previewBaseStyle()+'<body><main class="preview-wrap"><section class="preview-card"><h1>Result Preview</h1><p>โค้ดนี้ยังไม่มี HTML/CSS/JS หรือ WordPress runtime ที่แสดงผลใน browser ได้โดยตรง</p><div class="box">กด Copy หรือ Download เพื่อนำโค้ดไปทดสอบในสภาพแวดล้อมจริง</div></section></main></body>';
  }
  function previewSrcDocFromBlocks(blocks, focusIndex){
    blocks = Array.isArray(blocks) ? blocks : [];
    var normalized = blocks.map(function(b){ return {lang:normalizeCodeLang(b.lang || 'text'), code:clean(b.code || '')}; }).filter(function(b){ return b.code; });
    if(!normalized.length) return previewSrcDoc('text','');
    if(normalized.length === 1) return previewSrcDoc(normalized[0].lang, normalized[0].code);
    var focus = normalized[Math.max(0, Math.min(normalized.length - 1, Number(focusIndex)||0))] || normalized[0];
    var phpBlock = normalized.find(function(b){ return isPhpLikeCode(b.lang, b.code); });
    if(phpBlock && !normalized.some(function(b){ return b.lang === 'html' || /<html[\s>]|<!doctype\s+html|<body[\s>]|<div[\s>]|<section[\s>]/i.test(b.code); })){
      return phpWordPressResultHtmlRealerV740(phpBlock.code, downloadNameForCode(phpBlock.lang, Math.max(0, Number(focusIndex)||0)));
    }
    var css = '', js = '', html = '', svg = '';
    normalized.forEach(function(b){
      if(b.lang === 'css') css += '\n' + b.code;
      else if(b.lang === 'javascript') js += '\n' + b.code;
      else if((b.lang === 'html' || /<html[\s>]|<!doctype\s+html|<body[\s>]|<div[\s>]|<section[\s>]/i.test(b.code)) && !html) html = b.code;
      else if((b.lang === 'svg' || /<svg[\s>]/i.test(b.code)) && !svg) svg = b.code;
    });
    if(html) return injectAssetsIntoHtml(html, css, js);
    if(svg && !js) return previewSrcDoc('svg', svg);
    if(css || js) return defaultResultHtml(css, js, 'Combined Code Result');
    return previewSrcDoc(focus.lang, focus.code);
  }
  function blocksCanRenderResult(blocks, focusLang, focusCode){
    blocks = Array.isArray(blocks) && blocks.length ? blocks : [{lang:focusLang, code:focusCode}];
    return blocks.some(function(b){ return looksPreviewable(b.lang, b.code); });
  }
  function codeActionMeaningProfile(action, label){
    action = action || ''; label = label || action || 'Action';
    var profiles = {
      'code-copy':{
        meaning:'คัดลอกโค้ดที่สร้างขึ้นอย่างครบถ้วน',
        benefit:'ผู้ใช้ส่งต่อ ตรวจ แก้ หรือสำรองโค้ดได้ทันทีโดยไม่ต้องพิมพ์ซ้ำ',
        helps:'ผู้ใช้ทั่วไป นักพัฒนา และทีมที่ต้องรับงานต่อ',
        feedback:'คัดลอกแล้ว / แจ้งหากคัดลอกไม่ได้',
        test:'กดแล้ว clipboard ต้องมีโค้ดเต็มและไม่มีข้อความขาด'
      },
      'code-download':{
        meaning:'ดาวน์โหลดโค้ดเป็นไฟล์ที่นำไปใช้ต่อได้',
        benefit:'ลดการสูญหายของงาน เก็บเวอร์ชัน และส่งต่อไฟล์จริงให้ทีมได้',
        helps:'ผู้ใช้ที่ไม่ถนัดคัดลอกโค้ดยาว ๆ และผู้ดูแลระบบ',
        feedback:'สร้างไฟล์แล้ว / แจ้งชื่อไฟล์ที่ดาวน์โหลด',
        test:'เปิดไฟล์ที่โหลดแล้วต้องเห็นโค้ดตรงกับ code block'
      },
      'code-preview':{
        meaning:'ดูผลลัพธ์ของโค้ดก่อนนำไปติดตั้งหรือใช้งานจริง',
        benefit:'เห็น UI/flow ก่อน ลดโอกาสทำเว็บจริงเสียหรือเสียเวลาติดตั้งซ้ำ',
        helps:'ผู้ใช้ที่ต้องตัดสินใจก่อนติดตั้งและนักออกแบบ UI',
        feedback:'เปิด/ปิด preview พร้อมสถานะชัดเจน',
        test:'กดแล้ว iframe หรือ sandbox ต้องแสดงผลและย้อนกลับมาดูโค้ดได้'
      },
      'code-wp-preinstall':{
        meaning:'ทดลองระบบ WordPress ใน Sandbox ก่อนติดตั้งจริง',
        benefit:'ทดสอบหน้า ปุ่ม ฟอร์ม และ flow โดยไม่เขียนฐานข้อมูลหรือ activate plugin',
        helps:'เจ้าของเว็บ ผู้ดูแล WordPress และทีมทดสอบ',
        feedback:'แจ้งว่าเป็น Pre-install Sandbox และไม่กระทบเว็บไซต์จริง',
        test:'ทดสอบ action หลักแล้วต้องไม่มีการเขียนไฟล์/ฐานข้อมูลจริง'
      },
      'code-live-debug':{
        meaning:'ตรวจโค้ดและบั๊กแบบใช้งานจริงมากขึ้น',
        benefit:'หา error, จุดเสี่ยง, ปุ่มไม่มี handler, security gap และส่วนที่ยังไม่เชื่อม',
        helps:'ผู้ใช้ที่ไม่รู้โค้ดและนักพัฒนาที่ต้องแก้เร็ว',
        feedback:'แสดงคะแนน/รายการบั๊ก/จุดที่ต้องแก้',
        test:'รายงานต้องระบุปัญหา สาเหตุ และวิธีแก้ ไม่ใช่แค่บอกว่ามีบั๊ก'
      },
      'code-system-builder':{
        meaning:'แปลง code block ให้เป็นระบบที่มีไฟล์และโครงสร้างชัดเจน',
        benefit:'ผู้ใช้เห็นว่าแต่ละไฟล์/โมดูลทำอะไร ลดความสับสนก่อนนำไปพัฒนาต่อ',
        helps:'ผู้ใช้ที่ไม่ถนัดระบบไฟล์และทีมพัฒนา',
        feedback:'แสดง system map, file role และลำดับการทำงาน',
        test:'ต้องบอกไฟล์หลัก ไฟล์ย่อย การเชื่อมต่อ และหน้าที่แต่ละส่วนได้'
      },
      'code-performance-boost':{
        meaning:'ตรวจและยกระดับประสิทธิภาพของ code block ทั้งด้านโครงสร้าง ปุ่ม flow และ runtime',
        benefit:'ช่วยให้ระบบที่สร้างเร็วขึ้น ลื่นขึ้น ลดโค้ดซ้ำ ลดปุ่มหลอก ลด error และทำงานจริงได้มั่นคงกว่าเดิม',
        helps:'ผู้ใช้ทั่วไป เจ้าของเว็บ นักพัฒนา และผู้ใช้ปลายทางที่ต้องการระบบเร็ว ไม่ค้าง และกดแล้วเห็นผล',
        feedback:'แสดง Performance %, คอขวด สิ่งที่ต้องแก้ และส่งคำสั่งปรับโค้ดเต็มแบบรักษาของเดิม',
        test:'ต้องทดสอบโหลดหน้า, ปุ่มทุกปุ่ม, error state, mobile keyboard, preview, API retry, memory/cache และ accessibility'
      },
      'code-build-success':{
        meaning:'สั่งให้ระบบพัฒนา/ตรวจ/แก้ซ้ำจนเข้าใกล้ใช้งานจริง',
        benefit:'ผลักงานจากคำแนะนำไปสู่โค้ดเต็มที่ Preview, QC, Debug และ Package ได้',
        helps:'ผู้ใช้ที่ต้องการงานสำเร็จ ไม่ใช่แค่แนวคิด',
        feedback:'แสดง Success %, Phase, Missing และคำสั่งแก้ต่อ',
        test:'กดแล้วต้องเติม prompt ที่บังคับส่งโค้ดเต็มและแผนทดสอบ'
      },
      'code-expand-capabilities':{
        meaning:'ขยาย code block ให้คิดเป็นระบบกว้างขึ้นและสร้างฟังก์ชันได้หลายประเภท',
        benefit:'ช่วยเปลี่ยนโค้ดหนึ่งชิ้นให้เป็นระบบที่มี Dashboard/User/API/Data/Security/Preview/QC/Docs ได้ตามเป้าหมายผู้ใช้',
        helps:'ผู้ใช้ที่ต้องการต่อยอดจากโค้ดเดิมให้เป็นผลิตภัณฑ์จริงโดยไม่หลุดประโยชน์ของงาน',
        feedback:'แสดง Capability %, ฟังก์ชันที่พบ, ฟังก์ชันที่ควรเติม และส่งคำสั่งขยายต่อได้',
        test:'กดแล้วต้องสร้าง prompt ขยายฟังก์ชันจากโค้ดเดิม โดยยังรักษาของเดิมและมีแผนทดสอบทุก module'
      },
      'code-universal-platform':{
        meaning:'ยกระดับ code block ให้คิดและสร้างระบบได้หลายแพลตฟอร์มตามมาตรฐานโลก',
        benefit:'ช่วยให้โค้ดรองรับงานหลากหลาย เช่น เกม ธีมเว็บ โปรแกรมช่วยงาน แอป PWA WordPress API dashboard และเครื่องมือ media โดยยังมีความปลอดภัย ประสิทธิภาพ และทดสอบได้จริง',
        helps:'เจ้าของระบบ นักพัฒนา ผู้ใช้ทั่วไป ทีมออกแบบ และผู้ใช้ปลายทางทุกอุปกรณ์',
        feedback:'แสดง Universal Platform %, platform ที่รองรับ, ช่องว่างมาตรฐานโลก และส่งคำสั่งขยายเป็นระบบ cross-platform ต่อได้',
        test:'กดแล้วต้องสร้าง prompt ที่บังคับให้มี Platform Target Matrix, Device/Browser Matrix, Input/Output Contract, Performance Budget, Security/Privacy, Accessibility, QC และโค้ดเต็มเมื่อทำได้'
      },
      'code-beyond-limit':{
        meaning:'เปิดโหมด Beyond-Limit Value Engine เพื่อค้นหาศักยภาพที่ซ่อนอยู่ใน code block แล้วเปลี่ยนเป็นระบบที่เหนือความคาดหมายอย่างปลอดภัย',
        benefit:'ช่วยให้ code block ไม่หยุดที่ฟังก์ชันพื้นฐาน แต่ค้นหา extension point, automation, hidden workflow, performance energy, offline/edge use, AI/API connector, test loop และผลกระทบต่อผู้ใช้ที่ยังมองไม่เห็น',
        helps:'ผู้ใช้ทั่วไป เจ้าของระบบ นักพัฒนา ผู้สร้างเกม/เว็บ/แอป/โปรแกรมช่วยงาน และผู้ใช้ปลายทางที่ต้องการระบบที่มีคุณค่ามากกว่าที่คาดไว้',
        feedback:'แสดง Beyond Value %, Hidden Potential, Energy Sources, Unexpected Capabilities, Safe Limits และส่งคำสั่งยกระดับเป็นระบบเต็มที่ยังเคารพความปลอดภัย/กฎหมาย/ความเป็นส่วนตัว',
        test:'กดแล้วต้องสร้าง prompt ที่บังคับให้มี Hidden Potential Map, Unexpected Capability Matrix, Safe Innovation Guard, Performance Energy Plan, Human Benefit Loop, QC/Test Matrix และโค้ดเต็มเมื่อทำได้จริง'
      },
      'code-system-connection':{
        meaning:'เชื่อมความสัมพันธ์ทั้งระบบของ code block ตั้งแต่ปุ่ม คำสั่ง ฟอร์ม API state data flow security feedback preview debug package และคู่มือ',
        benefit:'ช่วยให้ระบบที่สร้างไม่เป็นชิ้นส่วนแยกกัน แต่ทุกปุ่มมี handler ทุกคำสั่งมีผลลัพธ์ ทุกโมดูลรู้หน้าที่ และผู้ใช้เห็น feedback/ทางแก้เมื่อเกิดปัญหา',
        helps:'ผู้ใช้ทั่วไป เจ้าของเว็บ นักพัฒนา ทีมทดสอบ และผู้ใช้ปลายทางที่ต้องการระบบที่กดแล้วทำงานจริงต่อเนื่องทั้งระบบ',
        feedback:'แสดง Connection %, Relationship Map, Broken Links, Button/Command Contract, Data Flow, API Flow, QC Path และส่งคำสั่งเชื่อมระบบต่อได้',
        test:'กดแล้วต้องสร้าง prompt ที่บังคับให้ตรวจทุกปุ่ม ทุก data-action ทุก event listener ทุก fetch/API ทุก state/storage ทุก permission ทุก error และทุก output ว่าเชื่อมกันจริง'
      },
      'code-studio-nexus':{
        meaning:'เชื่อม AiRA Studio ทั้งระบบให้ทุกปุ่ม ทุกคำสั่ง ทุก API ทุก state และทุก panel ทำงานสัมพันธ์กันเป็น product เดียว',
        benefit:'ช่วยลดปุ่มค้าง คำสั่งลอย API ไม่ตอบสนอง handler ซ้ำ และ flow ที่หลุดกัน ทำให้ระบบลื่นขึ้น แม่นขึ้น และส่งต่อเป็นงานจริงได้มากขึ้น',
        helps:'เจ้าของเว็บ ผู้ดูแล WordPress นักพัฒนา ทีมทดสอบ และผู้ใช้ปลายทางที่ต้องการ AiRA Studio ที่กดแล้วทำงานจริงทุกส่วน',
        feedback:'แสดง Nexus %, Action/API/State/Performance/Safety Matrix, จุดเชื่อมที่ยังขาด และส่งคำสั่งปรับทั้ง AiRA Studio ต่อได้',
        test:'กดแล้วต้องตรวจ real action registry, buttons, handlers, API endpoints, state sync, composer, chat, preview, debug, package, mobile keyboard, browser fallback และ security/QC ทั้งระบบ'
      },
      'code-communication-quality':{
        meaning:'ยกระดับทักษะการสื่อสารของ AiRA Studio ให้ตอบชัด เข้าใจง่าย มีจังหวะ มีบริบท และพาผู้ใช้ไปถึงผลลัพธ์จริง',
        benefit:'ช่วยลดคำตอบกำกวม ลดการวนซ้ำ ลดปุ่ม/คำสั่งที่สื่อสารไม่ชัด และทำให้ทุก feedback, error, API status, guide และ code explanation มีประโยชน์ต่อผู้ใช้จริง',
        helps:'ผู้ใช้ทั่วไป คนไม่เข้าใจโค้ด นักพัฒนา เจ้าของเว็บ ทีมทดสอบ และผู้ใช้ปลายทางที่ต้องการคำตอบคุณภาพสูงและทำตามได้ทันที',
        feedback:'แสดง Communication Quality %, tone/clarity/actionability/error recovery matrix และส่งคำสั่งปรับทั้ง Studio ให้สื่อสารดีขึ้นได้',
        test:'กดแล้วต้องตรวจทุกข้อความ ปุ่ม คำสั่ง API error success state loading empty state คู่มือ และคำอธิบายโค้ดว่าชัดเจน มีประโยชน์ และนำไปใช้จริงได้'
      },
      'code-deep-cognitive-communication':{
        meaning:'ยกระดับการสื่อสารลึกของ AiRA Studio ให้มองโจทย์หลายมิติ ตอบตรงเจตนาเชิงงาน และช่วยจัดระบบความคิดของผู้ใช้โดยไม่อ้างว่าอ่านใจจริง',
        benefit:'ช่วยให้คำตอบไม่หลุดความคิดหลักของผู้ใช้ ลดความสับสน แยกเป้าหมาย/ข้อจำกัด/ความเสี่ยง/ขั้นตอนถัดไป และพัฒนาการคิดของผู้ใช้ผ่านโครงตอบที่เข้าใจง่าย',
        helps:'ผู้ใช้ทั่วไป เจ้าของระบบ คนไม่เข้าใจโค้ด นักพัฒนา ทีมออกแบบ ทีมทดสอบ และผู้ใช้ที่ต้องการให้ AiRA ช่วยคิดเป็นระบบมากขึ้น',
        feedback:'แสดง Deep Cognitive Communication %, มิติการสื่อสาร, cognitive support map, safe intent guard และส่งคำสั่งพัฒนาคำตอบ/ปุ่ม/API ให้ตรงความต้องการมากขึ้น',
        test:'กดแล้วต้องตรวจว่าคำตอบมีผลลัพธ์ก่อนรายละเอียด, สรุปเจตนา, หลายมิติ, next action, error copy, memory hook, decision support, reflection และไม่เดาข้อมูลส่วนตัวอ่อนไหว'
      },
      'code-command-fusion':{
        meaning:'ประยุกต์ทุกคำสั่งในระบบให้ทำงานร่วมกันเป็นความสามารถอัจฉริยะ ไม่ปล่อยให้สี ปุ่ม API ไฟล์ หรือคำสั่งใดเป็นชิ้นส่วนลอย',
        benefit:'ช่วยให้ผู้ใช้เปลี่ยนสัญญาณเล็ก ๆ เช่น สี พาเลตต์ ตัวอย่าง ปุ่ม หรือคำสั่ง ให้กลายเป็นไฟล์ตัวอย่าง ธีม ระบบ เกม แอป โปรแกรมช่วยงาน หรือ workflow ที่ใช้ต่อได้จริง',
        helps:'ผู้ใช้ทั่วไป เจ้าของระบบ นักออกแบบ นักพัฒนา ทีมทดสอบ และผู้ใช้ปลายทางที่ต้องการให้ AiRA Studio ประยุกต์ทุกความสามารถเข้าด้วยกันอย่างมีคุณค่า',
        feedback:'แสดง Fusion %, Command/Color/File/API Relationship Map, ตัวอย่างสิ่งที่สร้างต่อได้ และส่งคำสั่งให้ AiRA สร้างโค้ดเต็ม/ไฟล์ตัวอย่าง/ระบบที่เชื่อมกันจริง',
        test:'กดแล้วต้องสร้าง prompt ที่บังคับให้ตรวจทุก data-action, handler, API, palette/color, file output, preview, QC, accessibility, privacy, rollback และ human value ว่าประยุกต์ร่วมกันได้จริง'
      },
      'code-dev-master':{
        meaning:'ยกระดับ code block ให้ทำหน้าที่เป็นหัวหน้าพัฒนาระบบแบบครบวงจร',
        benefit:'ช่วยเปลี่ยนโค้ดจากชิ้นส่วนให้เป็นระบบที่มีสถาปัตยกรรม ฟีเจอร์ ความปลอดภัย QC คู่มือ และคุณค่าต่อผู้ใช้ชัดเจนกว่าคำตอบทั่วไป',
        helps:'ผู้ใช้ที่ต้องการสร้างระบบจริง ทีมพัฒนา ผู้ดูแลเว็บ และคนที่ไม่ถนัดโค้ด',
        feedback:'แสดง Dev Master %, มิติที่ผ่าน/ยังขาด และส่งคำสั่งสร้างโค้ดเต็มระดับ production-ready ต่อได้',
        test:'กดแล้วต้องสร้าง prompt ที่บังคับให้รักษาของเดิม เติมโค้ดเต็ม แยกไฟล์/หน้าที่ ตรวจทุกปุ่ม และสรุปวิธีทดสอบใช้งานจริง'
      },
      'code-humanity-value':{
        meaning:'ตรวจและยกระดับ code block ให้ทุกปุ่ม ทุกคำสั่ง และทุกฟังก์ชันผลิตคุณค่าที่ช่วยมนุษย์จริง',
        benefit:'เปลี่ยนการสร้างระบบจากแค่ทำงานได้ ให้เป็นระบบที่แก้ปัญหาคนจริง ลดความเสี่ยง ใช้ง่าย ปลอดภัย เป็นธรรม และส่งต่อความรู้ได้',
        helps:'ผู้ใช้งานปลายทาง เจ้าของระบบ ทีมพัฒนา ผู้ดูแลเว็บ และคนที่ได้รับผลจากระบบนั้น',
        feedback:'แสดง Humanity Value %, แผนที่ผู้ได้รับประโยชน์ ความเสี่ยงที่ลดลง และส่งคำสั่งยกระดับคุณค่าใน Composer ได้',
        test:'กดแล้วต้องสร้าง prompt ที่บังคับให้มี Humanity Impact Map, Button Contract, Command Benefit, Safety/Ethics guard และวิธีทดสอบผลประโยชน์จริง'
      },
      'code-humanity-mission':{
        meaning:'แปลง code block ให้เป็นภารกิจสร้างระบบที่ช่วยคนจริงทีละปัญหา พร้อมวัดผลกระทบได้',
        benefit:'ช่วยให้ผู้ใช้สร้างระบบที่ไม่ได้มีแค่ฟีเจอร์ แต่มีเป้าหมายผู้ได้รับประโยชน์ วิธีทดสอบผลลัพธ์ ความปลอดภัย และแผนต่อยอดที่รับผิดชอบ',
        helps:'ผู้ใช้ปลายทาง เจ้าของระบบ ชุมชน ธุรกิจเล็ก ทีมพัฒนา และคนที่ได้รับประโยชน์จากระบบ',
        feedback:'แสดง Mission %, Problem → Human Value → Build → Test → Impact และส่งคำสั่งพัฒนาต่อแบบไม่หลุดคุณค่า',
        test:'กดแล้วต้องสร้าง prompt ที่บังคับให้มี Real Problem, Beneficiary, Minimal Useful System, Safety Gate, Impact Metric, Test Path และโค้ดเต็มเมื่อทำได้'
      },
      'code-package-download':{
        meaning:'แพ็กไฟล์ระบบเป็น ZIP หรือไฟล์ที่ส่งต่อได้',
        benefit:'ช่วยนำงานไปติดตั้ง ทดสอบ สำรอง หรือส่งต่อได้เป็นรูปธรรม',
        helps:'เจ้าของเว็บและนักพัฒนาที่ต้องรับไฟล์จริง',
        feedback:'แจ้งชื่อแพ็ก สถานะ และข้อจำกัดก่อนดาวน์โหลด',
        test:'ZIP ต้องเปิดได้และมีไฟล์ที่จำเป็นครบตามโครงสร้าง'
      },
      'code-wp-upgrade':{
        meaning:'อัปเกรดโค้ดหรือปลั๊กอินโดยรักษาของเดิมที่มีประโยชน์',
        benefit:'ลดความเสี่ยงฟังก์ชันเดิมหาย และพัฒนาต่อเป็นเวอร์ชันใหม่ได้',
        helps:'ผู้ใช้ที่มีระบบเดิมและต้องการต่อยอดอย่างปลอดภัย',
        feedback:'บอกสิ่งที่เพิ่ม สิ่งที่คงไว้ และจุดที่ต้องทดสอบหลังอัปเกรด',
        test:'ต้องมี version note, rollback path หรือคำเตือนก่อนทำ action เสี่ยง'
      },
      'code-wp-install':{
        meaning:'ติดตั้งเป็นปลั๊กอิน WordPress ผ่านขั้นตอนที่มีสิทธิ์และยืนยัน',
        benefit:'ทำให้ระบบใช้งานจริงได้โดยยังคุมความเสี่ยงเรื่องสิทธิ์ ความปลอดภัย และ backup',
        helps:'ผู้ดูแลเว็บ WordPress',
        feedback:'แสดง confirmation, capability, nonce, backup และผลติดตั้ง',
        test:'ผู้ไม่มีสิทธิ์ต้องติดตั้งไม่ได้ และต้องมีข้อความแจ้งชัดเจน'
      },
      'code-file-toggle':{
        meaning:'เปิดดูไฟล์และความสัมพันธ์ของโค้ดใน code block',
        benefit:'ช่วยเข้าใจว่าโค้ดนี้เป็นส่วนไหนของระบบก่อนตัดสินใจแก้หรือติดตั้ง',
        helps:'ผู้ใช้ที่ต้องการรู้ภาพรวมแบบไม่อ่านโค้ดยาว',
        feedback:'เปิด/ปิดแผงไฟล์พร้อมสถานะ aria-expanded',
        test:'กดแล้วแผงต้องแสดงชื่อไฟล์ ขนาด ภาษา และชิ้นส่วนระบบ'
      },
      'code-file-view':{
        meaning:'กลับไปดู source code อย่างโปร่งใส',
        benefit:'ผู้ใช้ตรวจสอบสิ่งที่ระบบสร้างได้ ไม่ถูกบังคับเชื่อ preview อย่างเดียว',
        helps:'ผู้ใช้ นักพัฒนา และคนตรวจงาน',
        feedback:'สลับกลับโหมดดูโค้ด',
        test:'ต้องแสดง source code ได้ครบหลังเคยเปิด preview'
      },
      'code-success-copy':{
        meaning:'คัดลอกรายงาน QC/Success ของ code block',
        benefit:'ใช้ส่งต่อให้ทีม แปะใน issue หรือเก็บเป็นหลักฐานว่าตรวจอะไรแล้ว',
        helps:'ทีมพัฒนาและผู้จ้างงาน',
        feedback:'คัดลอกรายงานสำเร็จ',
        test:'รายงานต้องมีคะแนน Phase Missing และ Value Guard'
      },
      'code-success-compose':{
        meaning:'นำคำสั่งแก้ต่อเข้า Composer อัตโนมัติ',
        benefit:'ลดการพิมพ์ซ้ำและพาระบบไปต่อด้วยบริบทที่ถูกต้อง',
        helps:'ผู้ใช้มือถือและผู้ใช้ที่ไม่ถนัดเขียน prompt',
        feedback:'ใส่คำสั่งใน Composer แล้วและ focus ช่องพิมพ์',
        test:'Composer ต้องมี prompt แก้ต่อครบและแก้ไขข้อความได้'
      },
      'code-success-send':{
        meaning:'ส่งคำสั่งให้แก้ต่อจนสำเร็จ',
        benefit:'ช่วยเดินงานต่อทันทีเมื่อยังมีส่วนขาด ไม่ปล่อยให้ผู้ใช้หลงขั้นตอน',
        helps:'ผู้ใช้ที่ต้องการผลลัพธ์ใช้งานจริงเร็วขึ้น',
        feedback:'ส่งคำสั่งแล้ว / แจ้งหากส่งไม่ได้',
        test:'ต้อง trigger flow ส่งข้อความจริงหรือ fallback เป็นการเติม Composer'
      }
    };
    var profile = profiles[action] || {
      meaning:'คำสั่งนี้ต้องมีหน้าที่ชัดเจน ไม่ใช่ปุ่มตกแต่ง',
      benefit:'ช่วยผู้ใช้ทำงานต่อได้จริง ลดเวลา ลดความสับสน หรือเพิ่มความปลอดภัย',
      helps:'ผู้ใช้งานระบบ',
      feedback:'ต้องมีข้อความตอบกลับหลังคลิก',
      test:'ต้องมี handler, aria-label, keyboard/touch support และ fallback เมื่อทำไม่ได้'
    };
    profile.action = action; profile.label = label;
    profile.value = profile.benefit;
    return profile;
  }
  function codeActionHumanValue(action, label){
    return codeActionMeaningProfile(action, label).benefit;
  }
  function attachHumanValueToButton(btn, action, label){
    if(!btn) return null;
    var profile = codeActionMeaningProfile(action || btn.getAttribute('data-action') || '', label || (btn.textContent || '').trim());
    var value = profile.benefit;
    btn.setAttribute('data-human-value', value);
    btn.setAttribute('data-command-purpose', value);
    btn.setAttribute('data-command-benefit', value);
    btn.setAttribute('data-command-meaning', profile.meaning);
    btn.setAttribute('data-user-benefit', profile.benefit);
    btn.setAttribute('data-helped-user', profile.helps);
    btn.setAttribute('data-feedback-promise', profile.feedback);
    btn.setAttribute('data-value-test', profile.test);
    btn.setAttribute('title', 'ความหมาย: ' + profile.meaning + ' · ประโยชน์: ' + profile.benefit);
    if(!btn.getAttribute('aria-label')){
      btn.setAttribute('aria-label', (profile.label || action || 'action') + ' — ' + profile.meaning + ' — ' + profile.benefit);
    }
    return profile;
  }
  function addCommandBenefit(items, key, label, meaning, benefit, found, meta){
    items = Array.isArray(items) ? items : [];
    if(!found) return items;
    if(items.some(function(it){ return it && it.key === key; })) return items;
    meta = meta || {};
    items.push({
      key:key,
      label:label,
      meaning:meaning,
      benefit:benefit,
      value:benefit,
      helps:meta.helps || 'ผู้ใช้งานและผู้ดูแลระบบ',
      feedback:meta.feedback || 'ต้องมีผลลัพธ์หรือสถานะให้ผู้ใช้เห็นหลังคำสั่งทำงาน',
      test:meta.test || 'ทดสอบว่าคำสั่งทำงานจริง มี error handling และไม่ทำลายข้อมูลเดิม',
      risk:meta.risk || 'ลดความเสี่ยงจากระบบค้าง ข้อมูลผิด หรือปุ่มใช้งานไม่ได้'
    });
    return items;
  }
  function detectButtonMeaningFromCode(code){
    code = clean(code || '');
    var items = [];
    var textOnly = function(v){ return String(v || '').replace(/<[^>]+>/g,' ').replace(/&nbsp;/g,' ').replace(/&amp;/g,'&').replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&quot;/g,'\"').replace(/&#39;/g,"'"); };
    var re = /<button\b([^>]*)>([\s\S]*?)<\/button>/gi, m, n = 0;
    while((m = re.exec(code)) && n < 8){
      var attrs = m[1] || '', inner = textOnly(m[2] || '').replace(/\s+/g,' ').trim();
      var action = (/data-action=["']([^"']+)/i.exec(attrs) || [,''])[1];
      var aria = (/aria-label=["']([^"']+)/i.exec(attrs) || [,''])[1];
      var text = inner || aria || action || 'Button';
      items.push({
        key:'html-button-'+n,
        label:'<button> ' + text.slice(0,36),
        meaning:'ปุ่มในโค้ดที่ผู้ใช้จะกดเพื่อสั่งงานจริง' + (action ? ' ('+action+')' : ''),
        benefit:'ต้องพาผู้ใช้ไปถึงผลลัพธ์ที่ตั้งใจ เช่น บันทึก ทดลอง ส่ง ตรวจ หรือเปิดข้อมูล โดยมี feedback ชัดเจน',
        value:'ต้องพาผู้ใช้ไปถึงผลลัพธ์ที่ตั้งใจ เช่น บันทึก ทดลอง ส่ง ตรวจ หรือเปิดข้อมูล โดยมี feedback ชัดเจน',
        helps:'ผู้ใช้ปลายทางที่กดปุ่มนี้',
        feedback:'หลังคลิกควรเห็นสถานะ เช่น สำเร็จ กำลังทำงาน หรือสาเหตุที่ทำไม่ได้',
        test:'กดปุ่มด้วย mouse/touch/keyboard แล้วต้องเรียก handler หรือ disabled อย่างซื่อสัตย์',
        risk:'ป้องกันปุ่มลอย ปุ่มหลอก และปุ่มไม่มี handler'
      });
      n++;
    }
    return items;
  }
  function detectCodeCommandBenefits(lang, code){
    lang = normalizeCodeLang(lang || 'text'); code = clean(code || '');
    var items = [];
    addCommandBenefit(items, 'wp-hook', 'add_action / add_filter', 'ผูกฟังก์ชันเข้าจังหวะทำงานของ WordPress', 'ช่วยต่อยอดระบบโดยไม่แก้ core ทำให้ดูแลและอัปเกรดปลอดภัยกว่า', /\badd_(action|filter)\s*\(/.test(code), {feedback:'ฟังก์ชันทำงานเมื่อถึง hook ที่กำหนด', test:'เปิดหน้าที่เกี่ยวข้องแล้วตรวจว่า callback ถูกเรียกและไม่มี warning'});
    addCommandBenefit(items, 'wp-shortcode', 'add_shortcode', 'สร้างคำสั่งสั้นสำหรับฝังฟีเจอร์ในหน้าเว็บ', 'ให้ผู้ใช้เรียกฟีเจอร์ผ่าน shortcode ได้ง่ายใน Gutenberg/Elementor/หน้าเว็บ', /\badd_shortcode\s*\(/.test(code), {helps:'แอดมินและผู้สร้างหน้าเว็บ', feedback:'เมื่อใส่ shortcode ต้องเห็น component จริง', test:'วาง shortcode ในหน้าแล้ว preview ทั้ง desktop/mobile'});
    addCommandBenefit(items, 'wp-rest', 'register_rest_route', 'สร้าง endpoint ให้ frontend/backend คุยกัน', 'ช่วยให้ระบบรับส่งข้อมูลอย่างมีโครงสร้างและควบคุมสิทธิ์ได้', /\bregister_rest_route\s*\(/.test(code), {feedback:'ต้องคืน response/status ชัดเจน', test:'ยิง endpoint ด้วยสิทธิ์ถูก/ผิดและตรวจ HTTP status'});
    addCommandBenefit(items, 'wp-ajax', 'wp_ajax / admin-ajax', 'สร้างช่องทางประมวลผลคำสั่งแบบไม่รีเฟรชหน้า', 'ช่วยให้ผู้ใช้กดปุ่มแล้วเห็นผลทันที เช่น บันทึก ทดสอบ API หรือดึงข้อมูล', /wp_ajax_|admin-ajax\.php|\bajaxurl\b/i.test(code), {feedback:'ต้องแสดง loading/success/error', test:'ทดสอบ nonce, payload และ response เมื่อ server error'});
    addCommandBenefit(items, 'security-nonce', 'nonce / permission', 'ตรวจว่าคำสั่งมาจากผู้ใช้ที่ถูกต้องและมีสิทธิ์', 'ป้องกันคำสั่งปลอมและลดความเสี่ยงการติดตั้ง/บันทึก/ลบโดยไม่ได้รับอนุญาต', /wp_(create|verify)_nonce|check_ajax_referer|permission_callback|current_user_can/.test(code), {helps:'เจ้าของเว็บและข้อมูลของผู้ใช้', feedback:'หากสิทธิ์ไม่พอต้องแจ้งปฏิเสธอย่างชัดเจน', test:'ทดสอบ user ไม่มีสิทธิ์, nonce หมดอายุ และ nonce ถูกต้อง'});
    addCommandBenefit(items, 'sanitize-escape', 'sanitize / escape', 'ทำความสะอาดข้อมูลเข้าและป้องกันการแสดงผลอันตราย', 'ลดความเสี่ยง XSS, ข้อมูลสกปรก และข้อความผิดรูปก่อนบันทึกหรือแสดงผล', /\bsanitize_|\besc_(html|attr|url|textarea|js)|wp_kses/.test(code), {feedback:'ข้อมูลที่แสดงควรปลอดภัยและไม่ทำให้ layout เสีย', test:'ใส่ script/html แปลก ๆ แล้วต้องไม่ execute'});
    addCommandBenefit(items, 'settings-state', 'get_option / update_option', 'อ่าน/บันทึกค่าระบบที่จำเป็น', 'ช่วยจำค่าที่ใช้บ่อย เช่น API/setting/status เพื่อให้ผู้ใช้ไม่ต้องกรอกซ้ำ', /\b(get|update|add|delete)_option\s*\(/.test(code), {feedback:'หลังบันทึกต้องมี status render ว่าสำเร็จ/ล้มเหลว', test:'refresh หน้าแล้วค่าที่จำเป็นยังอยู่และไม่เก็บข้อมูลซ้ำ'});
    addCommandBenefit(items, 'enqueue-assets', 'wp_enqueue', 'โหลด CSS/JS ผ่านระบบ WordPress', 'ลดการชนกับธีม/ปลั๊กอินอื่นและโหลดเฉพาะที่จำเป็น', /\bwp_enqueue_(script|style)\s*\(/.test(code), {feedback:'UI/JS ต้องโหลดเฉพาะหน้าที่เกี่ยวข้อง', test:'ดู Network/Console ว่าไฟล์โหลดถูกและไม่มี 404'});
    addCommandBenefit(items, 'js-events', 'addEventListener', 'ผูกการกระทำกับปุ่ม/ฟอร์ม/คีย์บอร์ด', 'ทำให้ UI ตอบสนองจริง ไม่ใช่แค่หน้าตาสวยแต่กดไม่ได้', /\.addEventListener\s*\(/.test(code), {feedback:'หลัง event ต้องมี loading/result/error', test:'กดทุกปุ่มและทดสอบ Enter/Escape/Touch'});
    addCommandBenefit(items, 'js-fetch', 'fetch / ajax', 'เรียก API หรือ backend เพื่อรับส่งข้อมูล', 'เชื่อมระบบจริงพร้อมรับผลลัพธ์และ error กลับมาให้ผู้ใช้เข้าใจ', /\bfetch\s*\(|XMLHttpRequest|\.ajax\s*\(/.test(code), {feedback:'ต้องแสดง HTTP status/response/error', test:'ทดสอบ success, 400/403/500 และ network offline'});
    addCommandBenefit(items, 'dom-query', 'querySelector / DOM', 'เลือกองค์ประกอบเพื่ออัปเดตหน้าเว็บ', 'ช่วยแสดงสถานะ ผลลัพธ์ และข้อความผิดพลาดตรงจุดที่ผู้ใช้มองเห็น', /querySelector(All)?\s*\(|getElementById\s*\(/.test(code), {feedback:'ถ้า element หายต้อง fallback ไม่ค้าง', test:'ลบ element จำลองแล้ว console ต้องไม่ error รุนแรง'});
    addCommandBenefit(items, 'storage', 'localStorage / sessionStorage', 'เก็บสถานะชั่วคราวในเครื่องผู้ใช้', 'ช่วยให้ผู้ใช้ทำงานต่อได้ ไม่ต้องเริ่มใหม่ทุกครั้ง และลดการกรอกซ้ำ', /\b(localStorage|sessionStorage)\b/.test(code), {feedback:'แจ้งเมื่อบันทึกสถานะแล้ว', test:'refresh/reopen แล้ว state ที่ควรจำยังอยู่ และมีปุ่มล้างเมื่อจำเป็น'});
    addCommandBenefit(items, 'timing-motion', 'setTimeout / requestAnimationFrame', 'ควบคุมจังหวะ animation/typing/feedback', 'ทำให้การตอบสนองนุ่ม อ่านง่าย และไม่กระชากสายตาผู้ใช้', /\b(setTimeout|setInterval|requestAnimationFrame)\s*\(/.test(code), {feedback:'animation ควรมีสถานะกำลังประมวลผลและหยุดเมื่อจบ', test:'ทดสอบมือถือช้า ๆ แล้วไม่หน่วงหรือค้าง'});
    addCommandBenefit(items, 'error-guard', 'try / catch', 'จับข้อผิดพลาดไม่ให้ระบบค้างเงียบ', 'ช่วยแจ้งสาเหตุและทางแก้ให้ผู้ใช้ แทนปล่อยให้หน้าเสียหรือกดไม่ได้', /\btry\s*\{|\bcatch\s*\(/.test(code), {feedback:'ต้องมี error message ที่อ่านรู้เรื่อง', test:'จำลอง error แล้วระบบยังใช้งานต่อได้'});
    addCommandBenefit(items, 'html-form', '<form/input/textarea>', 'รับข้อมูลจากผู้ใช้เป็นขั้นตอน', 'ช่วยเปลี่ยนความต้องการของผู้ใช้เป็นข้อมูลที่ระบบนำไปประมวลผลได้', /<(form|input|textarea|select)[\s>]/i.test(code), {feedback:'ต้องมี validation และข้อความหลังส่ง', test:'กรอกว่าง/ผิด/ถูกแล้วต้องแจ้งชัดเจน'});
    addCommandBenefit(items, 'a11y', 'aria / role / focus', 'เพิ่มความหมายให้ UI สำหรับคีย์บอร์ดและ screen reader', 'ช่วยให้คนใช้งานได้กว้างขึ้น รวมถึงผู้ใช้มือถือและผู้ที่ต้องใช้เทคโนโลยีช่วยเหลือ', /\baria-|\brole=|:focus|focus-visible/.test(code), {feedback:'focus/label ต้องชัดเจน', test:'Tab ผ่านปุ่มได้ครบและ screen reader อ่านชื่อปุ่มได้'});
    addCommandBenefit(items, 'responsive', '@media / responsive', 'ปรับ layout ให้เหมาะกับหน้าจอต่าง ๆ', 'ทำให้ระบบอ่านง่าย กดได้จริง และไม่ซ้อนทับบนมือถือ/แท็บเล็ต/เดสก์ท็อป', /@media|clamp\(|minmax\(|viewport|responsive/i.test(code), {feedback:'องค์ประกอบไม่ล้นจอและ composer/header ไม่ทับเนื้อหา', test:'ทดสอบ 360px, 768px, desktop และ browser หลัก'});
    addCommandBenefit(items, 'api-status', 'status / endpoint / API', 'แสดงสถานะการเชื่อมต่อและผลจากบริการภายนอก', 'ช่วยให้ผู้ใช้รู้ว่าระบบเชื่อมสำเร็จ/ล้มเหลวเพราะอะไรและแก้ตรงไหน', /\b(endpoint|api|status|http|response|error)\b/i.test(code), {feedback:'ต้องมี status code, endpoint, เวลาทดสอบ และ error log ที่อ่านง่าย', test:'ทดสอบ API key ถูก/ผิด quota หมด และ network fail'});
    detectButtonMeaningFromCode(code).forEach(function(it){ items.push(it); });
    if(!items.length){
      items.push({key:'code-purpose', label:'คำสั่งในโค้ด', meaning:'ทุกบรรทัดควรมีหน้าที่ที่อธิบายได้', benefit:'ช่วยผู้ใช้ผ่านลำดับ รับข้อมูล → ประมวลผล → แสดง feedback → ลดความเสี่ยง → ทดสอบได้', value:'ช่วยผู้ใช้ผ่านลำดับ รับข้อมูล → ประมวลผล → แสดง feedback → ลดความเสี่ยง → ทดสอบได้', helps:'ผู้ใช้งานระบบ', feedback:'ต้องมีผลลัพธ์ที่มองเห็นหรือรายงานได้', test:'อ่านแต่ละคำสั่งแล้วต้องตอบได้ว่าทำเพื่อใครและทดสอบอย่างไร', risk:'ลดโค้ดที่เขียนไว้เฉย ๆ โดยไม่มีประโยชน์'});
    }
    return items.slice(0, 16);
  }
  function commandBenefitMapText(items){
    items = Array.isArray(items) ? items.filter(Boolean) : [];
    return items.map(function(it){ return (it.label || it.key || 'Command') + ' = ความหมาย: ' + (it.meaning || '-') + ' / ประโยชน์: ' + (it.benefit || it.value || 'ช่วยให้งานเดินต่อได้จริง') + ' / ทดสอบ: ' + (it.test || '-'); }).join(' | ');
  }
  function addCapability(items, key, label, meaning, benefit, present, extra){
    if(!present) return;
    extra = extra || {};
    items.push({
      key:key,
      label:label,
      meaning:meaning,
      benefit:benefit,
      helps:extra.helps || 'ผู้ใช้งานระบบและทีมพัฒนา',
      feedback:extra.feedback || 'ต้องมีสถานะหลังใช้งาน เช่น สำเร็จ/กำลังทำ/ผิดพลาด',
      test:extra.test || 'ต้องทดสอบได้จาก Preview, ปุ่ม, form, API/mock หรือ checklist',
      risk:extra.risk || 'ลดการสร้างฟังก์ชันลอยที่กดไม่ได้หรือไม่มีประโยชน์จริง'
    });
  }
  function baseCapabilityCatalog(){
    return [
      {key:'dashboard',label:'Dashboard/Admin',meaning:'หน้าจัดการระบบ แก้ค่า ดูสถานะ และควบคุมงาน',benefit:'ช่วยเจ้าของระบบรู้ว่าอะไรเปิดใช้งานอยู่และแก้ไขได้โดยไม่แตะโค้ด'},
      {key:'user-ui',label:'User UI',meaning:'หน้าใช้งานสำหรับผู้ใช้จริง',benefit:'ทำให้ระบบไม่ใช่แค่ backend แต่มีหน้าที่คนทั่วไปกดใช้ได้'},
      {key:'forms',label:'Forms/Input',meaning:'รับข้อมูลจากผู้ใช้เป็นลำดับ',benefit:'เปลี่ยนความต้องการให้เป็นข้อมูลที่ระบบประมวลผลต่อได้'},
      {key:'api',label:'API/REST/AJAX',meaning:'ช่องทางรับส่งข้อมูลระหว่างหน้าเว็บกับ backend/บริการภายนอก',benefit:'ทำให้ระบบเชื่อมข้อมูลจริง ทดสอบสถานะ และต่อยอดกับหลายบริการได้'},
      {key:'data-storage',label:'Data/Storage',meaning:'เก็บข้อมูล สถานะ ประวัติ หรือค่าตั้งค่า',benefit:'ช่วยให้ผู้ใช้ทำงานต่อเนื่อง ไม่ต้องกรอกซ้ำ และตรวจย้อนหลังได้'},
      {key:'auth-permission',label:'Permission/Auth',meaning:'กำหนดสิทธิ์ผู้ใช้และป้องกันการเข้าถึงผิดคน',benefit:'ช่วยรักษาความปลอดภัยของเว็บและข้อมูลผู้ใช้'},
      {key:'security',label:'Security Guard',meaning:'nonce, sanitize, escape, permission และ guard สำหรับ action เสี่ยง',benefit:'ลดความเสี่ยงเว็บเสีย ข้อมูลรั่ว หรือถูกสั่งงานผิดสิทธิ์'},
      {key:'preview-qc',label:'Preview/QC',meaning:'แสดงตัวอย่าง ตรวจบั๊ก และบอกความพร้อมก่อนใช้งานจริง',benefit:'ช่วยตัดสินใจก่อนติดตั้ง ลดการแก้ซ้ำและลดความเสียหายกับเว็บจริง'},
      {key:'responsive-a11y',label:'Responsive/A11y',meaning:'รองรับมือถือ แท็บเล็ต เดสก์ท็อป keyboard/touch/screen reader',benefit:'ทำให้คนใช้ได้กว้างขึ้นและไม่ติดปัญหาจอเล็กหรือปุ่มกดไม่ได้'},
      {key:'automation',label:'Automation/Scheduler',meaning:'งานอัตโนมัติ เช่น cron, queue, retry, workflow',benefit:'ลดงานมือและช่วยให้ระบบทำงานต่อเนื่องตามเวลา/เงื่อนไข'},
      {key:'media',label:'Media/Image/Audio',meaning:'จัดการภาพ ไฟล์ เสียง วิดีโอ หรือ preview สื่อ',benefit:'ช่วยสร้างเครื่องมือคอนเทนต์และงานสื่อที่ผู้ใช้ใช้ต่อได้จริง'},
      {key:'export-import',label:'Export/Import',meaning:'นำข้อมูลเข้า/ออกเป็นไฟล์หรือรายงาน',benefit:'ช่วยสำรอง ส่งต่อ ตรวจงาน และย้ายข้อมูลได้ง่าย'},
      {key:'search-filter',label:'Search/Filter',meaning:'ค้นหา กรอง และจัดลำดับข้อมูล',benefit:'ช่วยผู้ใช้หาสิ่งที่ต้องการเร็วขึ้นในระบบที่ข้อมูลเยอะ'},
      {key:'analytics-chart',label:'Analytics/Chart',meaning:'สรุปตัวเลข กราฟ สถานะ และคะแนน',benefit:'ช่วยตัดสินใจจากข้อมูล ไม่ใช่เดา และเห็นทิศทางของระบบ'},
      {key:'notifications',label:'Notifications',meaning:'แจ้งเตือนผลลัพธ์ ความผิดพลาด หรือสิ่งที่ต้องทำต่อ',benefit:'ช่วยไม่ให้ผู้ใช้พลาดขั้นตอนสำคัญ'},
      {key:'gutenberg',label:'Gutenberg Block',meaning:'บล็อกสำหรับ WordPress editor',benefit:'ให้ผู้ใช้วางระบบลงหน้าเว็บได้ง่ายผ่าน block editor'},
      {key:'elementor',label:'Elementor Widget',meaning:'วิดเจ็ตสำหรับ Elementor',benefit:'ให้ผู้ใช้ลากวางระบบบนเว็บได้ง่ายโดยไม่เขียนโค้ด'},
      {key:'shortcode',label:'Shortcode',meaning:'รหัสสั้นสำหรับฝังระบบในหน้าเว็บ',benefit:'ช่วยติดตั้งหน้าผู้ใช้จริงได้เร็วและรองรับเว็บเดิม'},
      {key:'docs-guide',label:'Docs/Guide',meaning:'คู่มือ วิธีใช้ และคำอธิบายสำหรับคนไม่เข้าใจโค้ด',benefit:'ช่วยให้ระบบถูกใช้งานจริง ไม่ใช่ส่งมอบแล้วใช้งานไม่เป็น'},
      {key:'rollback',label:'Backup/Rollback',meaning:'แผนย้อนกลับและสำรองก่อน action เสี่ยง',benefit:'ลดความกลัวและลดความเสียหายเมื่ออัปเกรดหรือติดตั้ง'},
      {key:'game-engine',label:'Game/Interactive Engine',meaning:'ระบบเกมหรือ interactive simulation พร้อม loop/input/score/state',benefit:'ช่วยสร้างเกม เครื่องมือฝึก หรือสื่อการเรียนรู้ที่เล่น/ทดลองได้จริง'},
      {key:'web-theme',label:'Web Theme/Design System',meaning:'ระบบธีม สี ฟอนต์ layout component และ pattern UI',benefit:'ช่วยสร้างเว็บหรือธีมที่ดูมืออาชีพและดูแลแบรนด์ได้'},
      {key:'pwa-app',label:'PWA/Mobile App Ready',meaning:'โครงแอปที่รองรับ manifest/service worker/offline/app-like UX',benefit:'ช่วยต่อยอดเว็บเป็นแอปใช้งานจริงบนมือถือได้ง่ายขึ้น'},
      {key:'desktop-utility',label:'Desktop/Utility Program',meaning:'โปรแกรมช่วยงาน เครื่องมือคำนวณ แปลงไฟล์ จัดการงาน หรือ automation',benefit:'ช่วยลดเวลางานซ้ำและสร้างเครื่องมือใช้จริงในองค์กร/ชีวิตประจำวัน'},
      {key:'cross-browser-device',label:'Cross-browser/Device Matrix',meaning:'รองรับ Chrome/Safari/Firefox/Edge, iOS/Android/Desktop และขนาดจอหลัก',benefit:'ลดปัญหาระบบใช้ได้เฉพาะเครื่องผู้พัฒนา'},
      {key:'deployment-package',label:'Deploy/Package/Handoff',meaning:'วิธี build/package/deploy/version/rollback และส่งต่อไฟล์',benefit:'ช่วยให้ระบบจาก code block ถูกนำไปใช้จริงและดูแลต่อได้'},
      {key:'world-standard-qc',label:'Global Standard QC',meaning:'ชุดทดสอบมาตรฐานด้าน performance, accessibility, security, responsive, browser, data และ UX',benefit:'ช่วยให้คุณภาพใกล้มาตรฐานสากลมากขึ้นก่อนส่งมอบ'}
    ];
  }
  function detectCodeSystemCapabilities(lang, code){
    lang = normalizeCodeLang(lang || 'text'); code = clean(code || '');
    var items = [];
    addCapability(items,'dashboard','Dashboard/Admin','มีหน้า/ส่วนจัดการระบบ','ช่วยให้เจ้าของระบบควบคุมและตั้งค่าได้จากหน้าเดียว',/(add_menu_page|add_submenu_page|admin_menu|settings_fields|register_setting|dashboard|admin page|wp-admin)/i.test(code),{test:'เปิดเมนู admin แล้วตั้งค่า/บันทึก/ดูสถานะได้'});
    addCapability(items,'user-ui','User UI','มีหน้าหรือ component ให้ผู้ใช้กดใช้งาน','ทำให้ระบบนำไปใช้กับผู้ใช้จริง ไม่ใช่แค่โค้ดหลังบ้าน',/(<main|<section|<button|<form|className=|render\(|return\s*\(|shortcode|frontend|public)/i.test(code),{test:'Preview ต้องเห็นหน้าที่ผู้ใช้เข้าใจและกดได้'});
    addCapability(items,'forms','Forms/Input','มีช่องกรอกหรือ form รับข้อมูล','ช่วยรับโจทย์/ข้อมูลจากผู้ใช้และส่งต่อให้ระบบทำงาน',/(<form|<input|<textarea|<select|FormData|onSubmit|submit|settings_fields)/i.test(code),{test:'กรอกว่าง/ผิด/ถูกแล้วต้องมี validation และ feedback'});
    addCapability(items,'api','API/REST/AJAX','มีระบบเชื่อมต่อ API หรือ backend','ช่วยให้ระบบรับส่งข้อมูลจริง เชื่อมบริการภายนอก และแสดง error ได้',/(register_rest_route|wp_ajax_|fetch\s*\(|XMLHttpRequest|\.ajax\s*\(|endpoint|api_key|api key|HTTP status)/i.test(code),{test:'ทดสอบ success, 400/403/500, network fail และ key ผิด'});
    addCapability(items,'data-storage','Data/Storage','มีการบันทึกค่าหรือข้อมูล','ช่วยเก็บ history/settings/state เพื่อลดการทำซ้ำ',/(update_option|get_option|add_option|delete_option|localStorage|sessionStorage|indexedDB|wpdb|database|save|persist)/i.test(code),{test:'บันทึกแล้ว refresh ยังอยู่ และมีปุ่ม/วิธีล้างเมื่อจำเป็น'});
    addCapability(items,'auth-permission','Permission/Auth','มีการตรวจสิทธิ์และตัวตน','ช่วยให้คนที่ไม่มีสิทธิ์ทำ action สำคัญไม่ได้',/(current_user_can|permission_callback|is_user_logged_in|capability|nonce|auth|role|manage_options)/i.test(code),{test:'ผู้ไม่มีสิทธิ์ต้องถูกปฏิเสธด้วยข้อความอ่านง่าย'});
    addCapability(items,'security','Security Guard','มีการ sanitize/escape/nonce/error guard','ช่วยลดความเสี่ยงข้อมูลสกปรก XSS CSRF และ action ผิดสิทธิ์',/(sanitize_|esc_html|esc_attr|esc_url|wp_kses|check_ajax_referer|wp_verify_nonce|wp_create_nonce|try\s*\{|catch\s*\()/i.test(code),{test:'input อันตรายต้องถูกกรอง output ต้อง escape และ error ไม่ทำให้หน้าค้าง'});
    addCapability(items,'preview-qc','Preview/QC','มี preview/debug/test/status','ช่วยตรวจความพร้อมก่อนนำไปใช้จริงและลดการแก้ซ้ำ',/(preview|sandbox|debug|test|QC|quality|status|score|inspector|health)/i.test(code),{test:'ต้องมีรายงานชัดเจนว่าผ่าน/ไม่ผ่าน และต้องแก้อะไร'});
    addCapability(items,'responsive-a11y','Responsive/A11y','รองรับหลายจอและการเข้าถึง','ช่วยให้กดง่าย อ่านง่าย และใช้ได้กับ keyboard/touch/screen reader',/(@media|clamp\(|minmax\(|responsive|aria-|role=|focus-visible|tabindex|viewport)/i.test(code),{test:'ทดสอบ 360px, 768px, desktop, Tab, Enter, touch'});
    addCapability(items,'automation','Automation/Scheduler','มีงานอัตโนมัติหรือ workflow','ช่วยลดงานซ้ำและให้ระบบเดินงานตามเวลา/เงื่อนไข',/(wp_schedule_event|cron|setInterval|setTimeout|queue|retry|workflow|automation|scheduler)/i.test(code),{test:'ต้องหยุด/เริ่ม/จัดการ retry ได้และไม่รันซ้ำผิดปกติ'});
    addCapability(items,'media','Media/Image/Audio','มีการจัดการไฟล์ ภาพ เสียง หรือวิดีโอ','ช่วยสร้างระบบคอนเทนต์/สื่อที่ใช้งานได้จริง',/(media|image|audio|video|canvas|upload|attachment|FileReader|Blob|download|mime)/i.test(code),{test:'ไฟล์ผิดชนิด/ใหญ่เกิน/โหลดไม่สำเร็จต้องแจ้งชัดเจน'});
    addCapability(items,'export-import','Export/Import','มีการส่งออก/นำเข้าข้อมูล','ช่วยสำรอง ส่งต่อ และตรวจงานเป็นไฟล์จริง',/(download|export|import|CSV|JSON|TXT|zip|Blob|FileReader|parse)/i.test(code),{test:'ไฟล์ที่ได้เปิดได้ ข้อมูลครบ และไม่มีข้อมูลอ่อนไหวเกินจำเป็น'});
    addCapability(items,'search-filter','Search/Filter','มีค้นหา กรอง หรือจัดลำดับ','ช่วยให้ผู้ใช้หาข้อมูลเร็วในระบบที่เริ่มใหญ่',/(search|filter|sort|query|find|lookup|pagination|pageSize)/i.test(code),{test:'ค้นหาไม่พบ/พบหลายรายการ/ข้อมูลเยอะแล้วไม่ค้าง'});
    addCapability(items,'analytics-chart','Analytics/Chart','มีตัวเลข กราฟ คะแนน หรือสถิติ','ช่วยเห็นทิศทาง ประสิทธิภาพ และคุณภาพของระบบ',/(chart|graph|analytics|stats|metric|score|percent|%|dashboard|performance)/i.test(code),{test:'ตัวเลขต้องคำนวณถูกและอธิบายได้ว่าใช้ตัดสินใจอะไร'});
    addCapability(items,'notifications','Notifications','มี toast/alert/status/message','ช่วยให้ผู้ใช้รู้ว่ากดแล้วเกิดอะไรขึ้น',/(toast|notice|notification|alert|status|message|aria-live|feedback)/i.test(code),{test:'ทุก action สำคัญต้องมีข้อความ success/error/loading'});
    addCapability(items,'gutenberg','Gutenberg Block','รองรับ Gutenberg block','ช่วยฝังระบบลง WordPress editor ได้ง่าย',/(registerBlockType|block\.json|wp\.blocks|Gutenberg|enqueue_block_editor_assets)/i.test(code),{test:'เพิ่ม block ใน editor แล้ว preview หน้าเว็บต้องตรง'});
    addCapability(items,'elementor','Elementor Widget','รองรับ Elementor widget','ช่วยให้ผู้ใช้ลากวางระบบได้โดยไม่เขียนโค้ด',/(Elementor|Widget_Base|elementor\/widgets|register_widget_type|Controls_Manager)/i.test(code),{test:'ลากวิดเจ็ตได้ ตั้งค่าได้ และ frontend แสดงถูก'});
    addCapability(items,'shortcode','Shortcode','มี shortcode สำหรับฝังระบบ','ช่วยนำระบบไปแสดงในหน้าเว็บได้เร็วและเข้ากับเว็บเดิม',/(add_shortcode|\[[a-z0-9_\-]+\])/i.test(code),{test:'วาง shortcode แล้ว render ถูกและไม่ชนกับ theme'});
    addCapability(items,'docs-guide','Docs/Guide','มีคู่มือ วิธีใช้ หรือคำอธิบาย','ช่วยให้คนไม่เข้าใจโค้ดใช้งานระบบได้จริง',/(readme|docs|manual|usage|วิธีใช้|คู่มือ|install|step\s*1|ขั้นตอน)/i.test(code),{test:'ผู้ใช้ทั่วไปอ่านแล้วติดตั้ง/ใช้งาน/แก้ปัญหาเบื้องต้นได้'});
    addCapability(items,'rollback','Backup/Rollback','มีแผน backup/rollback/cleanup','ช่วยลดความเสี่ยงเมื่ออัปเกรด ติดตั้ง หรือลบระบบ',/(backup|rollback|restore|uninstall|register_uninstall_hook|delete_option|cleanup|migration)/i.test(code),{test:'ก่อน action เสี่ยงต้องมี confirmation และแผนย้อนกลับ'});
    addCapability(items,'game-engine','Game/Interactive Engine','มี loop/input/canvas/score/physics สำหรับเกมหรือ interactive tool','ช่วยสร้างเกม สื่อฝึก หรือ simulation ที่เล่นและทดสอบได้จริง',/(canvas|getContext\(|requestAnimationFrame|gameLoop|sprite|collision|score|level|keyboard|touch|pointer|physics|phaser|three\.js|webgl)/i.test(code),{test:'ทดสอบ fps, resize, keyboard/touch, pause/restart, memory และมือถือ'});
    addCapability(items,'web-theme','Web Theme/Design System','มีระบบธีม สี ฟอนต์ layout component หรือ template','ช่วยสร้างเว็บ/ธีมให้สวยเป็นระบบ แก้ต่อได้ และตรงแบรนด์',/(theme|template|style\.css|functions\.php|wp_head|wp_footer|body_class|:root|--[a-z0-9\-]+|palette|typography|design token|component library)/i.test(code),{test:'ทดสอบ contrast, responsive, light/dark, font fallback และ browser หลัก'});
    addCapability(items,'pwa-app','PWA/Mobile App Ready','มีโครงแอป manifest/service worker/offline/navigation','ช่วยต่อยอดเป็นแอปเว็บที่ใช้บนมือถือได้เหมือนโปรแกรมจริงมากขึ้น',/(manifest\.json|serviceWorker|service-worker|PWA|offline|cache\.|caches\.|app shell|install prompt|beforeinstallprompt)/i.test(code),{test:'ทดสอบ installability, offline fallback, mobile viewport และ storage cleanup'});
    addCapability(items,'desktop-utility','Desktop/Utility Program','มี logic สำหรับโปรแกรมช่วยงาน เช่น คำนวณ แปลงไฟล์ จัดการ task หรือ automation','ช่วยลดเวลางานซ้ำและสร้างเครื่องมือใช้งานจริงในทีม/ธุรกิจ',/(calculator|converter|task|todo|workflow|automation|file manager|csv|pdf|report|utility|tool|electron|tauri|desktop)/i.test(code),{test:'ทดสอบ input หลายแบบ, export, error, undo และข้อมูลจำนวนมาก'});
    addCapability(items,'cross-browser-device','Cross-browser/Device Matrix','มี fallback/feature detection และรองรับหลาย browser/device','ช่วยลดปัญหาใช้ได้เฉพาะเครื่องเดียวหรือ browser เดียว',/(CSS\.supports|matchMedia|visualViewport|navigator\.|PointerEvent|touchstart|polyfill|fallback|graceful|cross-browser|iOS|Android|Safari|Firefox|Edge|Chrome)/i.test(code),{test:'ทดสอบ Chrome, Safari, Firefox, Edge, Android, iOS, 360/768/1024/desktop'});
    addCapability(items,'deployment-package','Deploy/Package/Handoff','มีขั้นตอน build/deploy/package/version/handoff','ช่วยนำระบบไปใช้จริง ส่งต่อ และย้อนกลับได้เมื่อผิดพลาด',/(build|deploy|package|bundle|version|release|rollback|zip|Dockerfile|composer\.json|package\.json|vite|webpack|CI|CD|handoff)/i.test(code),{test:'ทดสอบ build/package เปิดไฟล์ได้ dependency ชัด และมี rollback'});
    addCapability(items,'world-standard-qc','Global Standard QC','มี test matrix หรือ checklist คุณภาพมาตรฐานโลก','ช่วยตรวจ performance, security, accessibility, responsive, browser, data และ UX ก่อนส่งมอบ',/(test matrix|unit test|integration|e2e|playwright|jest|phpunit|lighthouse|WCAG|Core Web Vitals|performance budget|security checklist|QC)/i.test(code),{test:'ต้องมีรายการผ่าน/ไม่ผ่านและวิธีแก้เมื่อไม่ผ่าน'});
    var found = {}; items.forEach(function(it){ found[it.key] = 1; });
    var suggestions = baseCapabilityCatalog().filter(function(it){ return !found[it.key]; }).slice(0, 8).map(function(it){
      return {key:it.key,label:it.label,meaning:it.meaning,benefit:it.benefit,helps:'ผู้ใช้งานระบบ',feedback:'ถ้าเติม module นี้ต้องมี UI/status/test ชัดเจน',test:'ให้รอบถัดไปสร้างเป็น module จริงหรือระบุอย่างซื่อสัตย์ว่ายังเป็น placeholder'};
    });
    var score = clamp(Math.round((items.length / 28) * 100), 0, 100);
    if(items.length >= 8) score = clamp(score + 8, 0, 100);
    if(items.length >= 12) score = clamp(score + 8, 0, 100);
    var status = score >= 76 ? 'expansive_system_ready' : (score >= 45 ? 'expandable_with_core_modules' : 'needs_more_system_functions');
    return {active:items.slice(0, 18), suggestions:suggestions, score:score, status:status, totalFound:items.length};
  }
  function capabilityMapText(profile){
    profile = profile || {}; var active = Array.isArray(profile.active) ? profile.active : []; var suggestions = Array.isArray(profile.suggestions) ? profile.suggestions : [];
    return 'Capability ' + (profile.score || 0) + '/100 · Active: ' + (active.map(function(it){return it.label;}).join(', ') || 'none') + ' · Next: ' + (suggestions.map(function(it){return it.label;}).join(', ') || 'none');
  }
  function codeCapabilityEngineHtml(profile){
    profile = profile || {}; var active = Array.isArray(profile.active) ? profile.active : []; var suggestions = Array.isArray(profile.suggestions) ? profile.suggestions : [];
    var activeHtml = active.slice(0, 10).map(function(it){
      return '<li><b>'+esc(it.label || it.key)+'</b><span>'+esc(it.meaning || '-')+'</span><small>ประโยชน์: '+esc(it.benefit || '-')+'</small></li>';
    }).join('') || '<li><b>เริ่มขยายระบบ</b><span>ยังพบฟังก์ชันหลักไม่มาก</span><small>กด “ขยายฟังก์ชัน” เพื่อให้ AiRA เติม module ที่จำเป็นและมีประโยชน์จริง</small></li>';
    var suggestionHtml = suggestions.slice(0, 8).map(function(it){
      return '<li><b>'+esc(it.label || it.key)+'</b><span>'+esc(it.meaning || '-')+'</span><small>ควรเติมเมื่อช่วยเป้าหมายผู้ใช้: '+esc(it.benefit || '-')+'</small></li>';
    }).join('');
    return '<div class="capability-engine-head"><div><b>'+icon('module')+'<span>Unlimited System Capability Engine</span></b><small>ขยาย code block ให้คิดเป็นระบบได้หลากหลายขึ้น: Dashboard/User/API/Data/Security/Preview/QC/Docs โดยยังรักษาคุณค่าและความปลอดภัย</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="capability-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="capability-columns"><div><b class="capability-section-title">ฟังก์ชันที่พบแล้ว</b><ul>'+activeHtml+'</ul></div><div><b class="capability-section-title">ฟังก์ชันที่ควรต่อยอด</b><ul>'+suggestionHtml+'</ul></div></div>'+ 
      '<div class="capability-rules"><span>สร้างได้หลายแนว</span><span>ต้องมีประโยชน์จริง</span><span>ไม่ตัดของเดิม</span><span>มี Preview/QC</span><span>มีสิทธิ์/ความปลอดภัย</span><span>สอนวิธีใช้</span></div>';
  }
  function capabilityExpansionPromptFromCard(card, extra){
    var data = codeDataFromCard(card); var lang = normalizeCodeLang(data.lang || 'text'); var code = data.code || '';
    var profile = detectCodeSystemCapabilities(lang, code);
    var current = (profile.active || []).map(function(it){ return it.label + ': ' + it.benefit; }).join(' | ') || 'ยังมีฟังก์ชันหลักน้อย';
    var next = (profile.suggestions || []).map(function(it){ return it.label + ': ' + it.benefit; }).join(' | ') || 'เติมตามเป้าหมายผู้ใช้';
    var name = headerValueFromPhp(code, 'Plugin Name') || downloadNameForCode(lang, data.index || 0);
    return [
      'ขยาย code block นี้ให้สร้างระบบได้หลากหลายฟังก์ชันมากขึ้นแบบ Unlimited System Capability Engine',
      '',
      '[แนวคิดหลัก]',
      '- ขยายได้กว้างด้านไอเดียและฟังก์ชัน แต่ต้องปลอดภัย ถูกกฎหมาย ไม่ละเมิดสิทธิ์ และช่วยผู้ใช้จริง',
      '- ทุกฟังก์ชันต้องตอบได้ว่า ช่วยใคร → มีประโยชน์อะไร → กดแล้วเห็น feedback อะไร → ทดสอบอย่างไร',
      '- ห้ามมีปุ่มลอย ปุ่มหลอก ฟังก์ชันหลอก หรือคำสั่งที่ไม่มี handler/fallback',
      '',
      '[Current System]',
      '- Name/File: ' + name,
      '- Language: ' + lang,
      '- Capability score: ' + (profile.score || 0) + '/100',
      '- Active capabilities: ' + current,
      '- Recommended expansion: ' + next,
      '- User extra: ' + (trim(extra || '') || 'เพิ่มความสามารถให้สร้างระบบได้หลากหลายฟังก์ชันมากขึ้น'),
      '',
      '[ต้องเติมเมื่อเหมาะสม]',
      '1) Dashboard/Admin สำหรับตั้งค่า ดูสถานะ และควบคุมระบบ',
      '2) User UI สำหรับผู้ใช้จริง พร้อม responsive ทุกอุปกรณ์',
      '3) Forms/Input + validation + feedback',
      '4) API/REST/AJAX หรือ mock connector พร้อมสถานะเชื่อมต่อ',
      '5) Data/Storage/History/Settings โดยไม่เก็บซ้ำและไม่เก็บเกินจำเป็น',
      '6) Permission/Nonce/Sanitize/Escape/Error Guard',
      '7) Preview/QC/Debug/Success %/Capability %',
      '8) Export/Import/Report/Package เมื่อเกี่ยวข้อง',
      '9) Search/Filter/Analytics/Chart/Notifications เมื่อช่วยงานจริง',
      '10) Elementor/Gutenberg/Shortcode สำหรับ WordPress เมื่อเกี่ยวข้อง',
      '11) Docs/คู่มือใช้งานสำหรับคนไม่เข้าใจโค้ด',
      '12) Backup/Rollback/Confirmation สำหรับ action เสี่ยง',
      '',
      '[Return Format]',
      'ทวนโจทย์สั้น ๆ → สิ่งที่คงไว้จากของเดิม → สิ่งใหม่ที่เพิ่ม → Capability Map → Human Value Map → โค้ดเต็มใน fenced code block → วิธีทดสอบทุกปุ่ม/ทุกฟังก์ชัน → สถานะใช้งานได้แล้วหรือยัง + Success %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }
  function runCodeCapabilityExpansionFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับขยายฟังก์ชัน','error'); return; }
    var panel = card.querySelector('.aira-code-capability-engine');
    if(panel){ panel.innerHTML = codeCapabilityEngineHtml(detectCodeSystemCapabilities(data.lang, data.code)); panel.hidden = false; }
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = capabilityExpansionPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่งไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่งขยายฟังก์ชันใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-expand-capabilities"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Capability Engine ส่งคำสั่งแล้ว · กำลังขยายฟังก์ชันให้เป็นระบบหลากหลายขึ้น', 'is-next-sent');
    submitPrompt(capabilityExpansionPromptFromCard(card, 'กดจากปุ่มขยายฟังก์ชัน'), 'ขยายฟังก์ชัน code block ให้เป็นระบบหลากหลายขึ้น · Capability ' + (detectCodeSystemCapabilities(data.lang, data.code).score || 0) + '/100', {mode:'code_capability_expand', source:'codeblock-unlimited-system-capability-7559'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }


  function codeUniversalPlatformProfile(lang, code){
    lang = normalizeCodeLang(lang || 'text');
    code = clean(code || '');
    var cap = detectCodeSystemCapabilities(lang, code);
    var commands = detectCodeCommandBenefits(lang, code);
    var audit = codeStaticAudit({lang:lang, code:code});
    var reality = codeRealityProfile({lang:lang, code:code});
    var dims = [];
    function dim(key, label, pass, weight, meaning, benefit, fix){
      dims.push({key:key,label:label,pass:!!pass,weight:weight||10,meaning:meaning,benefit:benefit,fix:fix||''});
    }
    var hasGame = /(canvas|getContext\(|requestAnimationFrame|gameLoop|sprite|collision|score|level|keyboard|touch|pointer|physics|webgl|three\.js|phaser)/i.test(code);
    var hasTheme = /(theme|template|style\.css|functions\.php|wp_head|wp_footer|body_class|:root|--[a-z0-9\-]+|palette|typography|design token|component library|dark mode|light mode)/i.test(code);
    var hasUtility = /(calculator|converter|task|todo|workflow|automation|csv|pdf|report|utility|tool|export|import|file|download|transform|generator)/i.test(code);
    var hasApp = /(component|router|route|state|store|screen|navigation|manifest\.json|serviceWorker|PWA|offline|app shell|mobile app|desktop|electron|tauri)/i.test(code);
    var hasWebCore = /(<main|<section|<form|<button|fetch\(|addEventListener|querySelector|@media|responsive|aria-|role=|CSS|HTML|JavaScript|frontend|backend)/i.test(code);
    var hasWp = /(Plugin\s+Name\s*:|add_action|add_shortcode|register_rest_route|wp_ajax_|Gutenberg|Elementor|shortcode|wp_enqueue|admin_menu)/i.test(code);
    var hasBackend = /(api|endpoint|server|database|wpdb|sql|REST|AJAX|webhook|auth|permission|nonce|request|response|HTTP)/i.test(code);
    var hasData = /(database|storage|localStorage|sessionStorage|indexedDB|get_option|update_option|save|history|schema|model|state|cache)/i.test(code);
    var hasDeviceBrowser = /(@media|viewport|clamp\(|minmax\(|responsive|visualViewport|matchMedia|CSS\.supports|navigator\.|PointerEvent|touchstart|fallback|polyfill|Chrome|Safari|Firefox|Edge|iOS|Android)/i.test(code);
    var hasA11y = /(aria-|role=|tabindex|focus-visible|keyboard|screen reader|WCAG|contrast|label|alt=)/i.test(code);
    var hasPerformance = /(performance|requestAnimationFrame|debounce|throttle|lazy|cache|AbortController|timeout|retry|fps|Core Web Vitals|content-visibility|virtual|memo)/i.test(code);
    var hasSecurity = (audit.score || 0) >= 68 && /(sanitize|escape|nonce|permission|capability|auth|validate|csrf|xss|try\s*\{|catch\s*\()/i.test(code);
    var hasTesting = /(test|QC|preview|debug|inspector|report|score|lighthouse|playwright|jest|phpunit|unit|integration|e2e|checklist|test matrix)/i.test(code);
    var hasPackage = /(download|zip|package|bundle|build|deploy|version|release|rollback|install|README|docs|manual|handoff|Dockerfile|package\.json|composer\.json)/i.test(code);
    var hasValue = /(human value|benefit|ประโยชน์|ผู้ใช้|ช่วย|feedback|ทดสอบ|accessibility|privacy|consent|impact)/i.test(code) || commands.length >= 4;
    dim('web-core','Web / UI Core', hasWebCore, 9, 'มีแกนหน้าเว็บ/อินเทอร์แอ็กชันที่ผู้ใช้กดเห็นผล', 'รองรับเว็บและระบบหน้าบ้านที่ใช้จริง', 'เพิ่ม HTML/CSS/JS, form, event, feedback และ preview');
    dim('wordpress','WordPress / CMS Ready', hasWp, 7, 'รองรับปลั๊กอิน/ธีม/shortcode/block/widget เมื่อเกี่ยวข้อง', 'ต่อยอดกับเว็บ Thinkb4do/WordPress ได้โดยไม่ทิ้งมาตรฐาน', 'เพิ่ม plugin header, hooks, REST/AJAX, shortcode, Gutenberg/Elementor');
    dim('game','Game / Interactive', hasGame, 8, 'รองรับเกม สื่อเล่นได้ หรือ simulation', 'สร้างระบบฝึก เล่น ทดลอง หรือเรียนรู้แบบ interactive ได้', 'เพิ่ม canvas/game loop/input/score/pause/responsive');
    dim('theme','Theme / Design System', hasTheme, 8, 'รองรับธีมเว็บ สี ฟอนต์ component และแบรนด์', 'ทำให้ระบบดูมืออาชีพและแก้ต่อได้', 'เพิ่ม design tokens, contrast, theme preview, font fallback');
    dim('utility','Work Utility / Program', hasUtility, 8, 'รองรับโปรแกรมช่วยงาน/เครื่องมือจัดการงาน', 'ลดเวลางานซ้ำและช่วยผู้ใช้ทั่วไปทำงานง่ายขึ้น', 'เพิ่ม input/output, export/import, report, validation');
    dim('app','PWA / App Structure', hasApp, 8, 'รองรับโครงแอปหลายหน้า state navigation และ offline เมื่อเหมาะสม', 'ต่อยอดเป็นแอปพลิเคชันได้จริงขึ้น', 'เพิ่ม app shell, route/state, manifest/service worker/fallback');
    dim('backend','API / Backend / Connector', hasBackend, 9, 'มีทางเชื่อม backend/API/auth/request/response', 'เชื่อมระบบจริงและแสดงสถานะ/error ได้', 'เพิ่ม endpoint contract, HTTP status, retry, timeout, auth');
    dim('data','Data / Storage Contract', hasData, 8, 'มีแผนข้อมูล state storage schema หรือ history', 'ทำงานต่อเนื่อง ตรวจย้อนหลัง และไม่กรอกซ้ำ', 'เพิ่ม schema, validation, cleanup, backup/export');
    dim('device-browser','All Devices / Browsers', hasDeviceBrowser, 10, 'คิดเรื่องมือถือ แท็บเล็ต เดสก์ท็อป และ browser หลัก', 'ลดปัญหาจอซ้อน ปุ่มกดไม่ได้ และใช้ได้เฉพาะเครื่องเดียว', 'เพิ่ม test matrix: 360/768/1024/desktop + Chrome/Safari/Firefox/Edge/iOS/Android');
    dim('a11y','Accessibility / Inclusive UX', hasA11y, 7, 'มี aria/focus/keyboard/contrast หรือแนวทางเข้าถึงง่าย', 'ไม่ทิ้งผู้ใช้ที่ใช้อุปกรณ์และความสามารถต่างกัน', 'เพิ่ม aria-label, focus-visible, keyboard path, contrast check');
    dim('performance','Performance Budget', hasPerformance, 8, 'มีแนวคิดลดหน่วง cache lazy timeout retry หรือ fps', 'ทำให้ระบบเร็ว ลื่น และไม่กินทรัพยากรเกินจำเป็น', 'เพิ่ม debounce/throttle, cache, AbortController, loading budget, lazy render');
    dim('security','Security / Privacy', hasSecurity, 9, 'มีความปลอดภัย สิทธิ์ validation และ error guard', 'ปกป้องเว็บ ข้อมูล และความเชื่อใจของผู้ใช้', 'เพิ่ม sanitize/escape/nonce/permission/privacy/consent/try-catch');
    dim('testing','QC / Test Matrix', hasTesting, 8, 'มีวิธีทดสอบหลายมิติและรายงานผ่าน/ไม่ผ่าน', 'ทำให้รู้ว่าส่งมอบได้จริงหรือยัง', 'เพิ่ม preview, unit/e2e/manual checklist, browser/device/API tests');
    dim('package','Package / Deploy / Handoff', hasPackage, 6, 'มีแผน build/deploy/download/docs/rollback', 'ช่วยนำระบบไปติดตั้ง ส่งต่อ และดูแลต่อได้', 'เพิ่ม package, version, install steps, rollback, README');
    dim('human-value','Human Value', hasValue, 7, 'ทุกฟังก์ชันต้องอธิบายคุณค่าต่อผู้ใช้หรือผู้อื่น', 'ผลิตระบบที่ช่วยคนจริง ไม่ใช่แค่โชว์ฟีเจอร์', 'เพิ่ม Human Value Map, Button Contract, Command Benefit Map');
    var total = dims.reduce(function(a,d){return a + (d.weight||0);}, 0);
    var got = dims.reduce(function(a,d){return a + (d.pass ? (d.weight||0) : 0);}, 0);
    var score = clamp(Math.round((got / Math.max(1,total)) * 100), 0, 100);
    score = clamp(Math.round(score * 0.68 + (cap.score || 0) * 0.14 + (audit.score || 0) * 0.10 + (reality.realness || 0) * 0.08), 0, 100);
    var domains = [];
    if(hasGame) domains.push('Game'); if(hasTheme) domains.push('Theme'); if(hasUtility) domains.push('Utility'); if(hasApp) domains.push('App/PWA'); if(hasWp) domains.push('WordPress'); if(hasBackend) domains.push('API/Backend'); if(hasData) domains.push('Data'); if(hasWebCore) domains.push('Web');
    var missing = dims.filter(function(d){return !d.pass;}).map(function(d){return d.label;});
    var strengths = dims.filter(function(d){return d.pass;}).map(function(d){return d.label;});
    var level = score >= 88 ? 'world_standard_multi_platform_ready' : (score >= 70 ? 'cross_platform_builder_ready' : (score >= 48 ? 'platform_expansion_needed' : 'single_snippet_needs_platform_map'));
    return {score:score, level:level, dims:dims, missing:missing, strengths:strengths, domains:domains, capability:cap, audit:audit, reality:reality, commands:commands};
  }

  function codeUniversalPlatformHtml(profile){
    profile = profile || {};
    var dims = Array.isArray(profile.dims) ? profile.dims : [];
    var domainHtml = (profile.domains && profile.domains.length ? profile.domains : ['General System']).map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('');
    var dimHtml = dims.slice(0, 15).map(function(d){
      return '<li class="'+(d.pass?'is-pass':'is-fix')+'"><b>'+esc(d.label)+'</b><span>'+esc(d.benefit || d.meaning || '-')+'</span><small>'+esc(d.pass ? 'พร้อมต่อยอด/ทดสอบได้' : (d.fix || 'ควรเติมให้ครบมาตรฐาน'))+'</small></li>';
    }).join('');
    return '<div class="universal-platform-head"><div><b>'+icon('globe')+'<span>Codeblock Universal Platform Builder</span></b><small>รองรับระบบหลายแนว: เกม ธีมเว็บ โปรแกรมช่วยงาน แอป PWA WordPress API Data และมาตรฐานทุกอุปกรณ์/บราวเซอร์</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="universal-platform-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="universal-platform-domains">'+domainHtml+'</div>'+ 
      '<ul class="universal-platform-dims">'+dimHtml+'</ul>'+ 
      '<div class="universal-platform-rules"><span>Game</span><span>Theme</span><span>Web/App/PWA</span><span>Utility</span><span>API/Data</span><span>All devices</span><span>All browsers</span><span>QC/Deploy</span></div>';
  }

  function universalPlatformPromptFromCard(card, reason){
    var data = codeDataFromCard(card); var lang = normalizeCodeLang(data.lang || 'text'); var code = data.code || '';
    var profile = codeUniversalPlatformProfile(lang, code);
    var cap = detectCodeSystemCapabilities(lang, code);
    var domains = (profile.domains || []).join(', ') || 'ยังต้องกำหนด platform target';
    var missing = (profile.missing || []).slice(0, 10).join(', ') || 'เติมตามเป้าหมายผู้ใช้';
    var active = (cap.active || []).map(function(it){ return it.label + ': ' + it.benefit; }).join(' | ') || 'ยังมีฟังก์ชันหลักน้อย';
    var name = headerValueFromPhp(code, 'Plugin Name') || downloadNameForCode(lang, data.index || 0);
    return [
      'พัฒนา code block นี้ให้เป็น Codeblock Universal Platform Builder v7.5.6.6 เพื่อสร้างระบบได้หลากหลายตามมาตรฐานโลก',
      '',
      '[เป้าหมายหลัก]',
      '- รองรับระบบหลายประเภทเมื่อเหมาะสม: เกม, ออกแบบธีมเว็บ, web app, โปรแกรมช่วยงาน, PWA/mobile app, desktop utility, WordPress plugin/theme/block/widget, API/backend, data/storage, media tool, analytics/chart และ automation',
      '- ต้องรองรับทุกอุปกรณ์ ทุกบราวเซอร์ ทุกระบบเท่าที่ทำได้จริง พร้อม fallback ที่ซื่อสัตย์เมื่อบาง platform ต้องใช้ environment เพิ่ม',
      '- ทุกฟังก์ชันต้องมีประโยชน์ต่อผู้ใช้จริง ไม่สร้างปุ่มหลอก ไม่สร้าง placeholder ที่กดไม่ได้ และไม่ตัดของเดิมที่มีคุณค่า',
      '- ใช้มาตรฐานสากล: responsive, accessibility/WCAG, security/privacy, validation, error handling, performance budget, test matrix, versioning, rollback และ documentation',
      '',
      '[Current Scan]',
      '- Name/File: ' + name,
      '- Language: ' + lang,
      '- Universal Platform score: ' + (profile.score || 0) + '/100 · ' + (profile.level || '-'),
      '- Detected domains: ' + domains,
      '- Active capabilities: ' + active,
      '- Missing standards: ' + missing,
      '- Reason: ' + (trim(reason || '') || 'กดจากปุ่มรองรับทุกระบบ'),
      '',
      '[ต้องสร้าง/เติมเป็นโครงที่ใช้งานจริง]',
      '1) Platform Target Matrix: Web / WordPress / Theme / Game / App / Utility / API / Data ระบุว่าอันไหนทำจริง อันไหนเป็น optional',
      '2) Device + Browser Matrix: 360px mobile, tablet, desktop, Chrome, Safari, Firefox, Edge, Android, iOS พร้อม fallback',
      '3) Input/Output Contract: ผู้ใช้กรอกอะไร ระบบคืนอะไร error แจ้งอะไร',
      '4) UI/UX Contract: ปุ่มทุกปุ่มมี handler, loading/success/error, aria-label, touch/keyboard support',
      '5) Security/Privacy: permission, sanitize/escape, nonce/auth เมื่อเกี่ยวข้อง, ไม่เก็บข้อมูลเกินจำเป็น',
      '6) Performance Budget: ลดโหลดซ้ำ, lazy render/cache/debounce/timeout/retry เมื่อเหมาะสม',
      '7) QC/Test Matrix: วิธีทดสอบทุก platform, browser, device, API, data, accessibility, performance',
      '8) Package/Handoff: ชื่อไฟล์อังกฤษสั้น, วิธีติดตั้ง, วิธี rollback, สถานะใช้งานได้แล้วหรือยัง',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → ของเดิมยังอยู่ → สิ่งที่เพิ่ม → Platform Target Matrix → Device/Browser Matrix → Button/Command Value → โค้ดเต็มใน fenced code block → วิธีทดสอบ → สถานะ Success/Quality/Universal %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }

  function runCodeUniversalPlatformFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับรองรับทุกระบบ','error'); return; }
    var profile = codeUniversalPlatformProfile(data.lang, data.code);
    var panel = card.querySelector('.aira-code-universal-platform');
    if(panel){ panel.innerHTML = codeUniversalPlatformHtml(profile); panel.hidden = false; }
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = universalPlatformPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่งรองรับทุกระบบไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่งรองรับทุกระบบใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-universal-platform"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Universal Platform Builder ส่งคำสั่งแล้ว · กำลังยกระดับให้รองรับเกม ธีม เว็บ แอป โปรแกรมช่วยงาน และมาตรฐานทุกอุปกรณ์/บราวเซอร์', 'is-next-sent');
    submitPrompt(universalPlatformPromptFromCard(card, 'กดจากปุ่มรองรับทุกระบบ'), 'Codeblock Universal Platform Builder · ' + (profile.score || 0) + '/100', {mode:'code_universal_platform', source:'codeblock-universal-platform-builder-7566'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }


  function codeBeyondLimitProfile(lang, code){
    lang = normalizeCodeLang(lang || 'text');
    code = clean(code || '');
    var cap = detectCodeSystemCapabilities(lang, code);
    var universal = codeUniversalPlatformProfile(lang, code);
    var quality = codeQualityProfile(lang, code, null, cap, detectCodeCommandBenefits(lang, code));
    var perf = codePerformanceProfile(lang, code, null, cap);
    var audit = codeStaticAudit({lang:lang, code:code});
    var reality = codeRealityProfile({lang:lang, code:code});
    var commands = detectCodeCommandBenefits(lang, code);
    var dims = [];
    function dim(key,label,pass,weight,meaning,benefit,fix){ dims.push({key:key,label:label,pass:!!pass,weight:weight||10,meaning:meaning||'',benefit:benefit||'',fix:fix||''}); }
    var hasExtension = /(hook|filter|event|plugin|module|component|widget|block|shortcode|extension|middleware|provider|adapter|registry|customEvent|dispatchEvent|data-action|add_action|register_rest_route)/i.test(code);
    var hasAutomation = /(automation|workflow|queue|scheduler|cron|batch|pipeline|process|retry|loop|state machine|orchestrator|agent|builder|generate|sync)/i.test(code);
    var hasAdaptive = /(mode|strategy|router|detect|classify|profile|score|recommend|suggest|context|memory|learning|behavior|adaptive|smart|intent)/i.test(code);
    var hasEnergy = /(cache|memo|lazy|virtual|worker|serviceWorker|indexedDB|localStorage|requestAnimationFrame|debounce|throttle|AbortController|stream|chunk|offline|edge|cdn|performance|fps|Core Web Vitals)/i.test(code);
    var hasPrototype = /(sandbox|preview|mock|fixture|demo|try|simulate|test harness|playground|storybook|iframe|canvas|svg)/i.test(code);
    var hasSafety = /(nonce|permission|capability|sanitize|escape|validate|privacy|consent|rollback|backup|rate limit|quota|audit|log|try\s*\{|catch\s*\()/i.test(code) && !/(eval\s*\(|shell_exec\s*\(|passthru\s*\(|proc_open\s*\(|popen\s*\(|base64_decode\s*\()/i.test(code);
    var hasKnowledge = /(docs|manual|readme|guide|explain|meaning|benefit|human value|command benefit|help|คู่มือ|วิธีใช้|ประโยชน์|ความหมาย)/i.test(code);
    var hasInteroperability = /(api|connector|webhook|REST|AJAX|fetch|GraphQL|JSON|CSV|import|export|sync|OpenAI|Gemini|Claude|v0|GitHub|Google|endpoint)/i.test(code);
    var hasCreativeSystem = /(game|theme|design system|animation|media|audio|video|image|chart|dashboard|canvas|webgl|simulation|tuner|editor|builder|application|PWA|desktop|mobile)/i.test(code);
    var hasImpact = /(impact|metric|score|percent|quality|performance|success|value|benefit|ช่วย|ลดเวลา|ลดความผิดพลาด|user|feedback|QC|test matrix)/i.test(code);
    dim('hidden-extension','Hidden Extension Points', hasExtension, 11, 'มีจุดต่อยอดหรือ hook ที่เปลี่ยนโค้ดจากชิ้นส่วนให้เป็นระบบขยายได้', 'ผู้ใช้สามารถเพิ่มฟีเจอร์ใหม่โดยไม่ทำลายของเดิม', 'เพิ่ม registry, data-action contract, hook/event และ module boundary');
    dim('unexpected-automation','Unexpected Automation', hasAutomation, 10, 'มีวงจรอัตโนมัติที่ช่วยทำงานซ้ำแทนผู้ใช้', 'ลดแรงงานซ้ำและพางานไปต่อโดยไม่หยุดกลางทาง', 'เพิ่ม workflow queue, retry, scheduler, state machine หรือ build loop ที่ปลอดภัย');
    dim('adaptive-intelligence','Adaptive Intelligence', hasAdaptive, 10, 'ระบบอ่านบริบท/โหมด/คะแนนเพื่อปรับการทำงานตามสถานการณ์', 'ช่วยให้ผลลัพธ์เหมาะกับผู้ใช้ งาน และอุปกรณ์มากขึ้น', 'เพิ่ม intent router, scoring, recommendation และ fallback');
    dim('performance-energy','Performance Energy Source', hasEnergy, 12, 'ใช้พลังงานที่มองไม่เห็น: cache, lazy, worker, offline, stream หรือ render ที่ประหยัด', 'ทำให้ระบบเร็ว ลื่น ใช้ทรัพยากรน้อย และรองรับเครื่องอ่อนกว่าเดิม', 'เพิ่ม cache budget, lazy render, AbortController, worker/service worker หรือ offline fallback เมื่อเหมาะสม');
    dim('prototype-lab','Prototype / Sandbox Lab', hasPrototype || (reality.previewReady||false), 9, 'มีพื้นที่ทดลองของจริงก่อนติดตั้งหรือส่งมอบ', 'ผู้ใช้เห็นผลก่อนเสี่ยงกับระบบจริง', 'เพิ่ม sandbox, preview, fixture, mock API หรือ click test');
    dim('safe-innovation','Safe Innovation Guard', hasSafety && (audit.score||0) >= 62, 13, 'ความคิดไร้ขีดจำกัดต้องอยู่บนความปลอดภัย สิทธิ์ และความซื่อสัตย์', 'ป้องกันการสร้างระบบที่อันตราย ละเมิดข้อมูล หรือปุ่มหลอก', 'เพิ่ม permission, privacy, rollback, validation, rate limit และตัดคำสั่งเสี่ยง');
    dim('knowledge-transfer','Knowledge Transfer', hasKnowledge || commands.length >= 5, 8, 'ระบบอธิบายความหมายและประโยชน์ของปุ่ม/คำสั่ง', 'ผู้ใช้ที่ไม่ถนัดโค้ดเข้าใจและดูแลต่อได้', 'เพิ่มคู่มือ, command benefit map และคำอธิบายภาษาคน');
    dim('interoperability','Universal Interoperability', hasInteroperability, 10, 'เชื่อม API/ข้อมูล/ระบบภายนอกได้แบบมี contract', 'ต่อยอดกับโลกจริงได้มากกว่าโค้ดในกล่องเดียว', 'เพิ่ม connector layer, endpoint contract, auth, timeout, retry และ status');
    dim('creative-system','Creative System Spectrum', hasCreativeSystem, 8, 'รองรับงานที่หลากหลายเกินคาด เช่น เกม ธีม แอป media dashboard simulation', 'เปิดทางให้ code block สร้างผลิตภัณฑ์หลายรูปแบบจากฐานเดียว', 'เพิ่ม platform matrix และ module สำหรับเกม/ธีม/app/media เมื่อช่วยโจทย์จริง');
    dim('measurable-impact','Measurable Human Impact', hasImpact, 9, 'สิ่งที่เหนือคาดต้องวัดได้ว่าช่วยคนอย่างไร', 'ทำให้คุณค่าไม่ใช่คำสวย แต่มี Success/Quality/Impact % และวิธีทดสอบ', 'เพิ่ม impact metric, QC report, before/after และ user benefit loop');
    var total = dims.reduce(function(a,d){return a+(d.weight||0);},0);
    var got = dims.reduce(function(a,d){return a+(d.pass?(d.weight||0):0);},0);
    var base = Math.round((got/Math.max(1,total))*100);
    var score = clamp(Math.round(base * 0.48 + (universal.score||0) * 0.18 + (quality.score||0) * 0.13 + (perf.score||0) * 0.11 + (cap.score||0) * 0.10),0,100);
    var missing = dims.filter(function(d){return !d.pass;}).map(function(d){return d.label;});
    var strengths = dims.filter(function(d){return d.pass;}).map(function(d){return d.label;});
    var energySources = [];
    if(hasEnergy) energySources.push('render/cache/offline energy');
    if(hasExtension) energySources.push('extension-point energy');
    if(hasAutomation) energySources.push('automation-loop energy');
    if(hasAdaptive) energySources.push('adaptive-context energy');
    if(hasPrototype) energySources.push('preview-lab energy');
    if(hasInteroperability) energySources.push('connector/API energy');
    if(!energySources.length) energySources = ['latent idea energy: ยังมีศักยภาพซ่อนอยู่ ให้เติม module, feedback, test และ value loop'];
    var level = score >= 90 ? 'beyond_expected_ready' : (score >= 74 ? 'unexpected_capability_ready' : (score >= 55 ? 'hidden_potential_found' : 'needs_beyond_limit_design'));
    return {score:score, level:level, dims:dims, missing:missing, strengths:strengths, energySources:energySources, cap:cap, universal:universal, quality:quality, performance:perf, audit:audit, reality:reality};
  }
  function codeBeyondLimitHtml(profile){
    profile = profile || {};
    var dims = Array.isArray(profile.dims) ? profile.dims : [];
    var dimHtml = dims.slice(0, 10).map(function(d){
      return '<li class="'+(d.pass?'is-pass':'is-fix')+'"><b>'+esc(d.label || d.key)+'</b><span>'+esc(d.benefit || d.meaning || '')+'</span><small>'+esc(d.pass ? 'พบพลังงาน/ศักยภาพที่ใช้ต่อยอดได้' : ('ควรปลดล็อก: ' + (d.fix || '-')))+'</small></li>';
    }).join('');
    var energy = (profile.energySources || []).slice(0, 6).map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('');
    var missing = (profile.missing || []).slice(0, 6).join(', ') || 'ผ่านแกนเหนือความคาดหมายหลักแล้ว';
    return '<div class="beyond-limit-head"><div><b>'+icon('performance')+'<span>Beyond-Limit Value Engine</span></b><small>ค้นหาศักยภาพที่ซ่อนอยู่ใน code block แล้วเปลี่ยนเป็นระบบที่เหนือความคาดหมาย แต่ยังปลอดภัย ซื่อสัตย์ และช่วยผู้ใช้จริง</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="beyond-limit-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="beyond-limit-energy">'+energy+'</div>'+ 
      '<div class="beyond-limit-summary"><p><b>Hidden Potential:</b> '+esc((profile.strengths || []).slice(0,5).join(', ') || 'ยังต้องปลดล็อกศักยภาพหลัก')+'</p><p><b>Next Unlock:</b> '+esc(missing)+'</p></div>'+ 
      '<ul class="beyond-limit-dims">'+dimHtml+'</ul>'+ 
      '<div class="beyond-limit-rules"><span>ไร้ขีดจำกัดด้านความพยายาม</span><span>ปลอดภัยก่อนเสมอ</span><span>พลังงานจาก cache/automation/context</span><span>ไม่สร้างปุ่มหลอก</span><span>วัดผลได้</span><span>ช่วยมนุษย์จริง</span></div>';
  }
  function beyondLimitPromptFromCard(card, extra){
    var data = codeDataFromCard(card); var lang = normalizeCodeLang(data.lang || 'text'); var code = data.code || '';
    var profile = codeBeyondLimitProfile(lang, code);
    var name = headerValueFromPhp(code, 'Plugin Name') || downloadNameForCode(lang, data.index || 0);
    var missing = (profile.missing || []).slice(0, 10).join(', ') || 'ต่อยอดตามเป้าหมายผู้ใช้';
    var energy = (profile.energySources || []).join(', ');
    return [
      'พัฒนา code block นี้ให้เป็น Beyond-Limit Value Engine v7.5.6.7: เพิ่มประสิทธิภาพและความสามารถที่เหนือความคาดหมาย โดยค้นหา “พลังงานที่มองไม่เห็น” จากโค้ดเดิม เช่น extension point, cache, automation loop, adaptive context, preview lab, connector/API, hidden workflow และ human-value loop',
      '',
      '[ขอบเขตความหมายของคำว่าไร้ขีดจำกัด]',
      '- หมายถึงไร้ขีดจำกัดด้านความพยายาม การต่อยอด ความคิดสร้างสรรค์ และการค้นหาศักยภาพที่ซ่อนอยู่',
      '- ไม่หมายถึงการ bypass ความปลอดภัย กฎหมาย ลิขสิทธิ์ ความเป็นส่วนตัว สิทธิ์ผู้ใช้ หรือข้อจำกัดของ environment/API จริง',
      '- ถ้าทำจริงไม่ได้ใน environment นี้ ให้สร้างโครงรองรับ + fallback + วิธีทดสอบอย่างซื่อสัตย์',
      '',
      '[Current Scan]',
      '- Name/File: ' + name,
      '- Language: ' + lang,
      '- Beyond Value score: ' + (profile.score || 0) + '/100 · ' + (profile.level || '-'),
      '- Energy sources found: ' + energy,
      '- Need unlock: ' + missing,
      '- User extra: ' + (trim(extra || '') || 'เพิ่มความสามารถที่ไร้ขีดจำกัดและเหนือความคาดหมาย แต่มีคุณค่าต่อผู้ใช้งานจริง'),
      '',
      '[ต้องปลดล็อกในคำตอบใหม่]',
      '1) Hidden Potential Map: โค้ดนี้มีพลังงาน/ศักยภาพซ่อนอยู่ตรงไหน เช่น hook, event, module, data, cache, context, UI state, API connector',
      '2) Unexpected Capability Matrix: เพิ่มฟังก์ชันที่คาดไม่ถึงแต่ช่วยผู้ใช้จริง เช่น auto-debug, self-QC, sandbox, offline mode, adaptive wizard, test generator, impact report, rollback, preview-before-install',
      '3) Performance Energy Plan: ใช้ cache/lazy/debounce/AbortController/worker/offline/streaming เมื่อเหมาะสม เพื่อให้เร็วและลื่นขึ้นโดยไม่กินเครื่อง',
      '4) Safe Innovation Guard: ห้าม bypass security, ห้ามละเมิด privacy/copyright/law, ห้ามปุ่มหลอก, ห้ามคำสั่งเสี่ยง, ต้องมี permission/validation/rollback',
      '5) Human Benefit Loop: ทุกฟังก์ชันต้องตอบว่า ช่วยใคร → ประโยชน์อะไร → feedback อะไร → ทดสอบอย่างไร → ลดความเสี่ยงอะไร',
      '6) Universal Delivery: รองรับทุกอุปกรณ์/บราวเซอร์/แพลตฟอร์มเท่าที่ทำได้จริง พร้อม fallback ที่ซื่อสัตย์',
      '7) ส่งโค้ดเต็ม runnable เมื่อแก้ได้ โดยรักษาของเดิมที่มีประโยชน์ไว้ทั้งหมด',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → Hidden Potential Map → Unexpected Capability Matrix → Performance Energy Plan → Safe Innovation Guard → Human Benefit Loop → โค้ดเต็ม → QC/Test Matrix → สถานะ Beyond/Quality/Performance/Success %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }
  function runCodeBeyondLimitFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับ Beyond-Limit','error'); return; }
    var profile = codeBeyondLimitProfile(data.lang, data.code);
    var panel = card.querySelector('.aira-code-beyond-limit');
    if(panel){ panel.innerHTML = codeBeyondLimitHtml(profile); panel.hidden = false; }
    card.setAttribute('data-beyond-limit-score', String(profile.score || 0));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = beyondLimitPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่ง Beyond-Limit ไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่ง Beyond-Limit ใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-beyond-limit"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Beyond-Limit Value Engine ส่งคำสั่งแล้ว · กำลังปลดล็อกศักยภาพซ่อนเร้นให้เหนือความคาดหมายอย่างปลอดภัย', 'is-next-sent');
    submitPrompt(beyondLimitPromptFromCard(card, 'กดจากปุ่มเหนือคาดหมาย'), 'Codeblock Beyond-Limit Value Engine · ' + (profile.score || 0) + '/100', {mode:'code_beyond_limit', source:'codeblock-beyond-limit-value-engine-7567'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }

  function codeSystemConnectionProfile(lang, code){
    lang = normalizeCodeLang(lang || 'text');
    code = clean(code || '');
    var lower = code.toLowerCase();
    var dims = [];
    function has(re){ return re.test(code); }
    function dim(key, label, pass, weight, meaning, benefit, fix){
      dims.push({key:key,label:label,pass:!!pass,weight:weight||10,meaning:meaning,benefit:benefit,fix:fix||''});
    }
    var actionMatches = code.match(/data-action\s*=\s*["'][^"']+["']/gi) || [];
    var buttonMatches = code.match(/<button\b|createElement\(["']button["']\)|addComfortBottomButton\(/gi) || [];
    var listenerMatches = code.match(/addEventListener\s*\(|onclick\s*=|closest\s*\(\s*["']\[data-action\]|case\s+["']|if\s*\([^)]*action\s*===/gi) || [];
    var fetchMatches = code.match(/fetch\s*\(|axios\.|XMLHttpRequest|\$\.ajax|wp_remote_|ajax\s*\(/gi) || [];
    var stateMatches = code.match(/localStorage|sessionStorage|setState\s*\(|useState\s*\(|state\.|data-|dataset\.|FormData|serialize|JSON\.(parse|stringify)/gi) || [];
    var securityMatches = code.match(/nonce|capability|permission|sanitize|esc_|wp_verify_nonce|current_user_can|csrf|validate|auth|token|rate.?limit/gi) || [];
    var feedbackMatches = code.match(/toast\s*\(|notice|alert\s*\(|aria-live|status|loading|disabled|is-loading|progress|spinner|feedback|error|catch\s*\(/gi) || [];
    var qcMatches = code.match(/test|qc|preview|debug|rollback|fallback|try\s*\{|catch\s*\(|timeout|AbortController|retry/gi) || [];
    var outputMatches = code.match(/download|copy|export|render|appendChild|innerHTML|textContent|response|return|iframe|preview|package|zip/gi) || [];
    var moduleMatches = code.match(/function\s+\w+|class\s+\w+|const\s+\w+\s*=|let\s+\w+\s*=|var\s+\w+\s*=|add_action|add_shortcode|register_rest_route|register_block_type|component|module/gi) || [];
    var responsiveMatches = code.match(/@media|matchMedia|resize|visualViewport|viewport|responsive|grid|flex|container|clamp\(/gi) || [];
    var docsMatches = code.match(/README|docs|คู่มือ|วิธีใช้|usage|manual|comment|\/\*|\/\/|description|help|tooltip|aria-label/gi) || [];
    var actionNames = uniqueList(actionMatches.map(function(x){ return (x.match(/["']([^"']+)["']/)||[])[1] || x; })).slice(0,16);
    dim('button-handler','ปุ่ม ↔ handler', buttonMatches.length && listenerMatches.length && (!actionMatches.length || listenerMatches.length), 13, 'ทุกปุ่มหรือ data-action ต้องมีตัวรับคำสั่งที่ชัดเจน', 'ลดปัญหาปุ่มกดไม่ได้หรือปุ่มหลอก', 'เชื่อม data-action ทุกตัวเข้ากับ event bridge และมี feedback หลังคลิก');
    dim('command-output','คำสั่ง ↔ ผลลัพธ์', moduleMatches.length && outputMatches.length, 11, 'คำสั่ง/ฟังก์ชันต้องมี output หรือผลลัพธ์ที่ผู้ใช้มองเห็น', 'ทำให้โค้ดไม่เป็นแค่ logic ภายใน แต่ช่วยงานจริง', 'ระบุ input/output contract และจุดแสดงผลของแต่ละ command');
    dim('api-flow','Frontend ↔ API/Backend', fetchMatches.length || has(/register_rest_route|admin-ajax|wp_ajax|endpoint|route/i), 11, 'ส่วนหน้าต้องรู้ว่าจะคุยกับ endpoint ไหนและ backend ต้องตอบอะไร', 'เชื่อมระบบภายนอก ฐานข้อมูล และบริการจริงได้มั่นคง', 'เพิ่ม endpoint contract, timeout, retry, error shape และ fallback');
    dim('state-data','State/Data ↔ UI', stateMatches.length && (buttonMatches.length || outputMatches.length), 10, 'ข้อมูลที่ผู้ใช้กรอกหรือระบบเก็บต้องไหลกลับไปแสดงผล/บันทึกได้', 'ลดข้อมูลหาย ลดกรอกซ้ำ และทำให้ระบบต่อเนื่องข้ามหน้าจอ', 'ทำ data flow map: input → validate → store → render → export');
    dim('security-permission','Security ↔ Permission', securityMatches.length, 11, 'ทุกคำสั่งที่กระทบข้อมูล/ระบบต้องมีสิทธิ์และการทำความสะอาดข้อมูล', 'ปกป้องผู้ใช้ เว็บ และข้อมูลส่วนตัว', 'เพิ่ม nonce/capability/sanitize/escape/validation/rate limit ตามแพลตฟอร์ม');
    dim('feedback-recovery','Feedback ↔ Recovery', feedbackMatches.length && qcMatches.length, 11, 'ทุก action ต้องบอกสถานะ สำเร็จ/ล้มเหลว/กำลังทำ และมีทางแก้', 'ผู้ใช้ไม่หลงทางเมื่อระบบช้า error หรือ API ล้มเหลว', 'เพิ่ม loading, success, error, retry, timeout, fallback และข้อความภาษาคน');
    dim('qc-preview','Preview/Debug ↔ Delivery', qcMatches.length && outputMatches.length, 9, 'ก่อนส่งมอบต้องมี preview/debug/QC/export/package ที่ทดสอบได้', 'ลดการติดตั้งผิด ลดแก้ซ้ำ และส่งงานต่อได้', 'เพิ่ม test matrix, preview sandbox, debug report, package checklist');
    dim('responsive-access','Responsive ↔ Accessibility', responsiveMatches.length && has(/aria-|role=|label|keyboard|focus|tabindex/i), 8, 'ระบบต้องใช้งานได้บนมือถือ คีย์บอร์ด screen reader และหลายบราวเซอร์', 'ช่วยผู้ใช้หลากหลายอุปกรณ์และความสามารถเข้าถึงระบบได้จริง', 'เพิ่ม responsive matrix, focus state, aria-label, keyboard path, browser fallback');
    dim('module-relations','Module ↔ Role Map', moduleMatches.length >= 3, 8, 'แต่ละ function/module/file ต้องมีหน้าที่และความสัมพันธ์กันชัดเจน', 'ดูแลต่อได้ง่าย ไม่ซ้ำซ้อน และต่อยอดเป็นระบบใหญ่ได้', 'ทำ Module Relationship Map และแยก responsibilities');
    dim('docs-handoff','Docs ↔ Handoff', docsMatches.length, 8, 'ระบบต้องมีคำอธิบาย วิธีใช้ และขั้นตอนทดสอบ', 'คนไม่เข้าใจโค้ดก็ใช้งาน/ส่งต่อ/ซ่อมต่อได้', 'เพิ่ม README, usage guide, setup, QC checklist และ rollback note');
    var total = dims.reduce(function(n,d){ return n + (d.weight||0); }, 0) || 1;
    var got = dims.reduce(function(n,d){ return n + (d.pass ? (d.weight||0) : 0); }, 0);
    var score = clamp(Math.round(got * 100 / total), 0, 100);
    var passed = dims.filter(function(d){ return d.pass; }).map(function(d){ return d.label; });
    var missing = dims.filter(function(d){ return !d.pass; }).map(function(d){ return d.fix || d.label; });
    var relations = [];
    if(actionNames.length) relations.push('Actions: ' + actionNames.join(', '));
    if(buttonMatches.length) relations.push('Buttons: ' + buttonMatches.length + ' จุด');
    if(listenerMatches.length) relations.push('Handlers: ' + listenerMatches.length + ' จุด');
    if(fetchMatches.length) relations.push('API calls: ' + fetchMatches.length + ' จุด');
    if(stateMatches.length) relations.push('State/Data: ' + stateMatches.length + ' จุด');
    if(securityMatches.length) relations.push('Security: ' + securityMatches.length + ' จุด');
    if(feedbackMatches.length) relations.push('Feedback: ' + feedbackMatches.length + ' จุด');
    return {
      score: score,
      level: score >= 86 ? 'connected-system-ready' : (score >= 68 ? 'strong-but-needs-wiring' : (score >= 45 ? 'partial-connections' : 'fragmented-code-block')),
      dims: dims,
      passed: passed,
      missing: missing,
      relations: relations.length ? relations : ['ยังไม่พบความสัมพันธ์ระบบชัดเจน'],
      actionNames: actionNames
    };
  }
  function codeSystemConnectionHtml(profile){
    profile = profile || {};
    var dims = profile.dims || [];
    var missing = (profile.missing || []).slice(0,6);
    var rel = (profile.relations || []).slice(0,7);
    return '<div class="system-connection-head"><div><b>'+icon('module')+'<span>System Connection Engine</span></b><small>เชื่อมความสัมพันธ์ทั้งระบบ: ปุ่ม ↔ handler ↔ คำสั่ง ↔ API ↔ state/data ↔ feedback ↔ QC ↔ ผู้ใช้</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="system-connection-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="system-connection-relations">'+rel.map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('')+'</div>'+ 
      '<div class="system-connection-grid">'+dims.map(function(d){ return '<div class="connection-dim '+(d.pass?'is-pass':'is-missing')+'"><b>'+esc(d.label)+'</b><small>'+esc(d.pass ? d.benefit : d.fix)+'</small></div>'; }).join('')+'</div>'+ 
      '<div class="system-connection-missing"><b>จุดที่ควรเชื่อมต่อเพิ่ม</b><ul>'+missing.map(function(x){ return '<li>'+esc(x)+'</li>'; }).join('')+'</ul></div>';
  }
  function systemConnectionPromptFromCard(card, extra){
    var data = codeDataFromCard(card);
    var code = clean(data.code || '');
    var lang = normalizeCodeLang(data.lang || 'text');
    var profile = codeSystemConnectionProfile(lang, code);
    var cap = detectCodeSystemCapabilities(lang, code);
    var commandItems = detectCodeCommandBenefits(lang, code);
    var connection = (profile.relations || []).join(' | ') || '-';
    var missing = (profile.missing || []).join(' | ') || 'เชื่อมความสัมพันธ์ระบบให้แน่นขึ้น';
    var actions = (profile.actionNames || []).join(', ') || 'ไม่พบ data-action ชัดเจน';
    return [
      'พัฒนา code block นี้ให้เป็น System Connection Engine v7.5.6.8: เพิ่มการเชื่อมต่อ เพิ่มความสามารถ เพิ่มประสิทธิภาพ และเชื่อมความสัมพันธ์ทั้งระบบ ทุกปุ่ม ทุกคำสั่ง ทุกฟังก์ชัน ทุกข้อมูล ให้ทำงานต่อเนื่องกันจริง',
      '',
      '[เป้าหมายหลัก]',
      '- ไม่สร้างปุ่มหลอก ไม่สร้างคำสั่งลอย ไม่ปล่อย module แยกกันแบบไม่เชื่อม',
      '- ทุกปุ่มต้องมี handler, feedback, error state, disabled/loading state, aria-label และวิธีทดสอบ',
      '- ทุกคำสั่งสำคัญต้องมี input/output contract, validation, permission, fallback และประโยชน์ผู้ใช้',
      '- เชื่อม Frontend ↔ Backend/API ↔ State/Data ↔ Preview/QC ↔ Package/Deploy ↔ Docs/Handoff',
      '- เพิ่มประสิทธิภาพโดยลดงานซ้ำ ใช้ lazy/debounce/cache/timeout/retry เมื่อเหมาะสม และไม่ทำให้ระบบหนักเกินจำเป็น',
      '',
      '[Current Scan]',
      '- Name/File: ' + downloadNameForCode(lang, data.index || 0),
      '- Language: ' + lang,
      '- Connection score: ' + (profile.score || 0) + '/100 · ' + (profile.level || '-'),
      '- System capability: ' + (cap.score || 0) + '/100',
      '- Actions found: ' + actions,
      '- Relationships found: ' + connection,
      '- Need connect: ' + missing,
      '- Command benefit map: ' + (commandBenefitMapText(commandItems) || '-'),
      '- User extra: ' + (trim(extra || '') || 'เชื่อมต่อความสัมพันธ์ทั้งระบบ ทุกปุ่ม ทุกคำสั่ง และเพิ่มประสิทธิภาพ'),
      '',
      '[ต้องทำในคำตอบใหม่]',
      '1) System Relationship Map: ระบุ Button → Handler → Command → Data/API → UI Feedback → QC/Test → User Benefit',
      '2) Button Contract: ทุกปุ่มต้องบอกหน้าที่ handler feedback error retry aria-label และ test path',
      '3) Command Contract: ทุกคำสั่ง/ฟังก์ชันสำคัญต้องบอก input output validation permission fallback และประโยชน์',
      '4) Data/API Flow: เชื่อม input, state, storage, API, response, render, export/package ให้เห็นลำดับจริง',
      '5) Performance Connection: ลด handler ซ้ำ ลด render ซ้ำ ใช้ delegation/lazy/debounce/cache/AbortController/timeout/retry ตามเหมาะสม',
      '6) Safety Connection: เชื่อม nonce/capability/sanitize/escape/privacy/log/rollback ทุกจุดที่เสี่ยง',
      '7) Cross-System Matrix: รองรับมือถือ แท็บเล็ต เดสก์ท็อป เบราว์เซอร์หลัก WordPress/เว็บ/API/app ตามชนิดโค้ด พร้อม fallback',
      '8) ส่งโค้ดเต็ม runnable เมื่อแก้ได้ โดยรักษาของเดิมที่มีประโยชน์ไว้ทั้งหมด และระบุจุดที่แก้/เพิ่มอย่างชัดเจน',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → System Relationship Map → Button Contract → Command Contract → Data/API Flow → Performance/Safety Connection → โค้ดเต็ม → QC/Test Matrix → สถานะ Connection/Quality/Performance/Success %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }
  function runCodeSystemConnectionFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับเชื่อมความสัมพันธ์ระบบ','error'); return; }
    var profile = codeSystemConnectionProfile(data.lang, data.code);
    var panel = card.querySelector('.aira-code-system-connection');
    if(panel){ panel.innerHTML = codeSystemConnectionHtml(profile); panel.hidden = false; }
    card.setAttribute('data-system-connection-score', String(profile.score || 0));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = systemConnectionPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่งเชื่อมความสัมพันธ์ระบบไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่งเชื่อมความสัมพันธ์ระบบใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-system-connection"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'System Connection Engine ส่งคำสั่งแล้ว · กำลังเชื่อมทุกปุ่ม ทุกคำสั่ง API state feedback QC และเอกสารให้เป็นระบบเดียว', 'is-next-sent');
    submitPrompt(systemConnectionPromptFromCard(card, 'กดจากปุ่มเชื่อมระบบ'), 'Codeblock System Connection Engine · ' + (profile.score || 0) + '/100', {mode:'code_system_connection', source:'codeblock-system-connection-engine-7568'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }


  function codeStudioNexusProfile(lang, code){
    lang = normalizeCodeLang(lang || 'text');
    code = clean(code || '');
    var connection = codeSystemConnectionProfile(lang, code);
    var perf = codePerformanceProfile(lang, code, null, detectCodeSystemCapabilities(lang, code));
    var quality = codeQualityProfile(lang, code, null, detectCodeSystemCapabilities(lang, code), detectCodeCommandBenefits(lang, code));
    var universal = codeUniversalPlatformProfile(lang, code);
    var audit = codeStaticAudit({lang:lang, code:code});
    var dims = [];
    function has(re){ return re.test(code); }
    function dim(key,label,pass,weight,meaning,benefit,fix){ dims.push({key:key,label:label,pass:!!pass,weight:weight||10,meaning:meaning||'',benefit:benefit||'',fix:fix||''}); }
    var actionMatches = collectRegexMatches(code, /data-action\s*=\s*["']([^"']+)["']/gi, 80);
    var handlerMatches = collectRegexMatches(code, /(?:addEventListener|onclick|handle[A-Z][A-Za-z0-9_]*|run[A-Z][A-Za-z0-9_]*|case\s+["']([^"']+)["'])/gi, 80);
    var apiMatches = collectRegexMatches(code, /(?:fetch\s*\(|wp_remote_(?:get|post)|admin-ajax\.php|register_rest_route|REST|endpoint|ajax\(|axios\.|api[_-]?key|cfg\.actions|AiRASafe|webhook|OpenAI|Gemini|Claude|Google|v0|GitHub)/gi, 80);
    var stateMatches = collectRegexMatches(code, /(?:localStorage|sessionStorage|indexedDB|state\.|rooms|messages|sync|memory|authorizedIdentity|deviceId|setAttribute\(|dataset|data-)/gi, 80);
    var uiMatches = collectRegexMatches(code, /(?:composer|chatLog|adminbar|visualViewport|keyboard|preview|modal|popup|panel|toolbar|toast|aria-|role=|focus|scroll)/gi, 80);
    var perfMatches = collectRegexMatches(code, /(?:requestAnimationFrame|debounce|throttle|AbortController|timeout|retry|cache|lazy|content-visibility|passive:true|setTimeout|performance|virtual|worker)/gi, 80);
    var securityMatches = collectRegexMatches(code, /(?:nonce|capability|permission|sanitize|escape|esc_|wp_kses|current_user_can|validate|privacy|consent|rate.?limit|rollback|backup|audit|log)/gi, 80);
    var deliveryMatches = collectRegexMatches(code, /(?:preview|debug|package|download|export|zip|docs|readme|handoff|install|upgrade|rollback|QC|test matrix)/gi, 80);
    var commandMatches = collectRegexMatches(code, /(?:prompt|command|router|intent|mode|submitPrompt|system prompt|instruction|data-command|command benefit|Button Contract|Command Contract)/gi, 80);
    var allButtonCount = (code.match(/<button\b|createElement\(['"]button['"]\)|data-action/gi) || []).length;
    var scoreActionRatio = actionMatches.length ? Math.min(100, Math.round((handlerMatches.length / Math.max(1, actionMatches.length)) * 100)) : 0;
    dim('studio-shell','AiRA Studio Shell ↔ UX Runtime', uiMatches.length >= 5, 9, 'Composer, chat, preview, panel และ viewport ต้องอยู่ใน flow เดียวกัน', 'ลดอาการปุ่มซ้อน composer จม scroll หลุด และทำให้ผู้ใช้คุมงานได้ต่อเนื่อง', 'เชื่อม header/chat/composer/preview/modal ผ่าน layout contract และ keyboard-safe viewport');
    dim('action-registry','Every Button ↔ Action Registry', actionMatches.length && scoreActionRatio >= 70, 12, 'ทุก data-action ต้องมี handler หรือ fallback ที่ตั้งใจไว้', 'ลดปุ่มกดไม่ได้ ปุ่มหลอก และคำสั่งลอย', 'ทำ Action Registry กลาง: action → handler → feedback → error → test path');
    dim('command-router','Every Command ↔ Intent Router', commandMatches.length >= 5, 10, 'ทุกคำสั่งต้องถูกส่งเข้า router เดียวกันและรู้โหมดงาน', 'ลดคำตอบหลุดคำสั่งและช่วยให้คำสั่งต่อเนื่องทำงานได้แม่นขึ้น', 'เพิ่ม Command Contract, mode/source, prompt digest และ latest-command anchor');
    dim('api-mesh','Every API ↔ Connector Mesh', apiMatches.length >= 4, 11, 'ทุก API ต้องมี endpoint, auth/key, timeout, retry, response shape และ status', 'ลด API ค้าง 504/403/ไม่ตอบ และทำให้ผู้ใช้รู้ว่าต้องแก้ตรงไหน', 'เพิ่ม connector layer, Test Connection, endpoint map, HTTP status, retry/fallback และ log ภาษาคน');
    dim('state-sync','State/Data ↔ UI Sync', stateMatches.length >= 8, 10, 'ข้อมูลทุกส่วนต้องไหลจาก input → validate → state/storage/API → render → export', 'ลดข้อมูลหาย ห้องแชท/Memory ไม่ตรง และการ render ไม่อัปเดต', 'เพิ่ม state contract, versioned storage, sync status, conflict guard และ render after save');
    dim('performance-network','Performance ↔ Runtime Network', perfMatches.length >= 6, 10, 'ประสิทธิภาพต้องเชื่อมทั้ง rendering, API, event, preview และ code block', 'ทำให้ระบบลื่นขึ้นบนมือถือ/คอมเก่า ลดกระตุกและลด timeout', 'ใช้ event delegation, content-visibility, lazy panel, AbortController, debounce/throttle และ prompt slimming');
    dim('safety-permission','Safety ↔ Permission Chain', securityMatches.length >= 5 && (audit.score||0) >= 55, 11, 'ทุกจุดที่แตะข้อมูลหรือระบบต้องผ่านสิทธิ์และการป้องกัน', 'ปกป้องเว็บ ผู้ใช้ API key และข้อมูลส่วนตัว', 'เชื่อม nonce/capability/sanitize/escape/privacy/rollback/rate-limit ทุก endpoint');
    dim('feedback-recovery','Feedback ↔ Recovery Loop', has(/toast|feedback|success|error|warn|retry|timeout|fallback|loading|disabled|is-loading/i), 9, 'ทุก action ต้องบอกกำลังทำ สำเร็จ ล้มเหลว และมีทางไปต่อ', 'ผู้ใช้ไม่ค้าง ไม่เดาเอง และรู้ว่ากดใหม่/แก้ค่าอะไร', 'เพิ่ม loading/disabled/success/error/retry/copy report/recovery prompt ทุกปุ่มสำคัญ');
    dim('delivery-qc','Preview/Debug/Package ↔ QC Delivery', deliveryMatches.length >= 7, 9, 'ระบบต้องมีทางลอง ตรวจ ส่งออก และ rollback ก่อนใช้งานจริง', 'ลดความเสี่ยงก่อนติดตั้งและช่วยส่งต่อทีมได้', 'เพิ่ม Preview Sandbox, Debug Report, Package Checklist, Export Bug All, rollback note และคู่มือ');
    dim('global-standard','Global Standard ↔ Cross Platform', universal.score >= 55 || has(/@media|responsive|browser|device|PWA|serviceWorker|accessibility|aria|keyboard|Android|iOS|Chrome|Safari|Firefox/i), 9, 'ต้องรองรับอุปกรณ์ บราวเซอร์ และรูปแบบระบบหลากหลายตามมาตรฐานโลก', 'ช่วยผู้ใช้หลายกลุ่มเข้าถึงและใช้งานได้จริง', 'เพิ่ม device/browser matrix, accessibility path, fallback และ performance budget');
    var total = dims.reduce(function(a,d){ return a+(d.weight||0); },0) || 1;
    var got = dims.reduce(function(a,d){ return a+(d.pass ? (d.weight||0) : 0); },0);
    var base = Math.round(got * 100 / total);
    var score = clamp(Math.round(base*0.46 + (connection.score||0)*0.18 + (quality.score||0)*0.12 + (perf.score||0)*0.14 + (universal.score||0)*0.10),0,100);
    var missing = dims.filter(function(d){ return !d.pass; }).map(function(d){ return d.fix || d.label; });
    var passed = dims.filter(function(d){ return d.pass; }).map(function(d){ return d.label; });
    var matrix = [];
    matrix.push('Buttons/Actions: ' + (actionMatches.length || allButtonCount || 0) + ' action points · handler ratio ' + scoreActionRatio + '%');
    matrix.push('Commands: ' + commandMatches.length + ' command/router signals');
    matrix.push('APIs: ' + apiMatches.length + ' connector signals');
    matrix.push('State/Data: ' + stateMatches.length + ' sync signals');
    matrix.push('Performance: ' + perfMatches.length + ' runtime signals');
    matrix.push('Security: ' + securityMatches.length + ' safety signals');
    matrix.push('Delivery/QC: ' + deliveryMatches.length + ' handoff signals');
    return {score:score, level:score>=88?'nexus-ready':(score>=70?'strong-nexus-needs-tightening':(score>=48?'partial-studio-network':'fragmented-studio-system')), dims:dims, missing:missing, passed:passed, matrix:matrix, actionRatio:scoreActionRatio, actionNames:actionMatches.slice(0,20), apiSignals:apiMatches.slice(0,16), commandSignals:commandMatches.slice(0,16), connection:connection, performance:perf, quality:quality, universal:universal};
  }
  function codeStudioNexusHtml(profile){
    profile = profile || {};
    var dims = profile.dims || [];
    var matrix = (profile.matrix || []).slice(0,7);
    var missing = (profile.missing || []).slice(0,7);
    return '<div class="studio-nexus-head"><div><b>'+icon('brain')+'<span>AiRA Studio Nexus Engine</span></b><small>เชื่อมทั้ง Studio: ปุ่ม ↔ คำสั่ง ↔ API ↔ state ↔ composer/chat ↔ preview/debug/package ↔ feedback/QC</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="studio-nexus-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="studio-nexus-matrix">'+matrix.map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('')+'</div>'+ 
      '<div class="studio-nexus-grid">'+dims.map(function(d){ return '<div class="nexus-dim '+(d.pass?'is-pass':'is-missing')+'"><b>'+esc(d.label)+'</b><small>'+esc(d.pass ? d.benefit : d.fix)+'</small></div>'; }).join('')+'</div>'+ 
      '<div class="studio-nexus-missing"><b>จุดที่ควรเชื่อมต่อให้แน่นขึ้น</b><ul>'+missing.map(function(x){ return '<li>'+esc(x)+'</li>'; }).join('')+'</ul></div>';
  }
  function studioNexusPromptFromCard(card, extra){
    var data = codeDataFromCard(card);
    var code = clean(data.code || '');
    var lang = normalizeCodeLang(data.lang || 'text');
    var profile = codeStudioNexusProfile(lang, code);
    var connection = codeSystemConnectionProfile(lang, code);
    var missing = (profile.missing || []).join(' | ') || 'เชื่อมต่อความสัมพันธ์ทั้งระบบให้ครบ';
    var matrix = (profile.matrix || []).join(' | ');
    var actionNames = (profile.actionNames || []).join(', ') || 'ยังไม่พบ action registry ชัดเจน';
    var apiSignals = (profile.apiSignals || []).join(', ') || 'ยังไม่พบ API/connector signal ชัดเจน';
    return [
      'พัฒนา AiRA Studio / code block นี้ให้เป็น AiRA Studio Nexus Engine v7.5.6.9: เพิ่มประสิทธิภาพ เชื่อมต่อความสัมพันธ์ทุกระบบ ทุกคำสั่ง ทุกปุ่ม ทุก API ทุก state ทุก panel และทุก feedback ให้ทำงานร่วมกันจริงแบบไม่เป็นชิ้นส่วนลอย',
      '',
      '[เป้าหมายหลัก]',
      '- มองทั้งระบบเป็น network เดียว: Header/Admin bar → Chat Log → Composer → Code block → Preview → Debug → Package → API Center → Memory/Rooms → Settings → Docs/Handoff',
      '- ทุกปุ่มต้องมี Action Registry กลาง: label, purpose, handler, input, output, loading, success, error, retry, permission, aria-label, test path',
      '- ทุกคำสั่งต้องมี Command Router: intent, mode, source, latest-command anchor, prompt digest, fallback และ human-value output',
      '- ทุก API ต้องมี Connector Contract: endpoint, method, auth/key source, request shape, response shape, timeout, retry, error mapping, status render, log และ Test Connection',
      '- ทุก state/data ต้องมี Sync Contract: input → validate → state/storage/API → render → export/package → rollback',
      '- เพิ่มประสิทธิภาพด้วย event delegation, lazy panel, content-visibility, requestAnimationFrame, debounce/throttle, AbortController, prompt slimming, cache budget และไม่ render หนักซ้ำ',
      '- ปลอดภัย: nonce/capability/sanitize/escape/privacy/rate-limit/rollback ห้าม bypass กฎหมาย ความเป็นส่วนตัว หรือข้อจำกัดจริง',
      '',
      '[Current Nexus Scan]',
      '- Name/File: ' + downloadNameForCode(lang, data.index || 0),
      '- Language: ' + lang,
      '- Nexus score: ' + (profile.score || 0) + '/100 · ' + (profile.level || '-'),
      '- Connection score: ' + (connection.score || 0) + '/100',
      '- Matrix: ' + matrix,
      '- Actions: ' + actionNames,
      '- API signals: ' + apiSignals,
      '- Need connect: ' + missing,
      '- User extra: ' + (trim(extra || '') || 'เพิ่มประสิทธิภาพและเชื่อมความสัมพันธ์ทั้ง AiRA Studio ทุกปุ่ม ทุกคำสั่ง ทุก API'),
      '',
      '[ต้องทำในคำตอบใหม่]',
      '1) Studio Relationship Map: แสดงความสัมพันธ์ Header/Chat/Composer/Code/Preview/Debug/Package/API/Memory/Settings/Docs',
      '2) Universal Action Registry: ตาราง action → ปุ่ม → handler → feedback → error/retry → permission → test path',
      '3) Command Router Contract: ระบุวิธีจับ intent, mode, source, latest command, context digest และ fallback',
      '4) API Connector Mesh: ระบุ endpoint, method, auth/key, timeout, retry, response shape, error mapping, status render และ test connection',
      '5) State/Data Sync: แสดง input → validation → state/local/server → render → export/rollback',
      '6) Performance Plan: ลด handler ซ้ำ ลด render หนัก ใช้ lazy/content-visibility/debounce/AbortController/cache/requestAnimationFrame ตามเหมาะสม',
      '7) Safety/QC Matrix: nonce, capability, sanitize, escape, privacy, rate limit, rollback, accessibility, responsive, browser/device test',
      '8) ส่งโค้ดเต็ม runnable เมื่อแก้ได้ โดยรักษาของเดิมที่มีประโยชน์ไว้ทั้งหมด และระบุสิ่งที่เพิ่ม/แก้ชัดเจน',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → ของเดิมยังอยู่ → สิ่งที่เพิ่ม → Studio Relationship Map → Action Registry → Command Router → API Connector Mesh → State/Data Sync → Performance/Safety/QC → โค้ดเต็ม → วิธีทดสอบ → Nexus/Connection/Quality/Performance %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }
  function runCodeStudioNexusFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับเชื่อม AiRA Studio','error'); return; }
    var profile = codeStudioNexusProfile(data.lang, data.code);
    var panel = card.querySelector('.aira-code-studio-nexus');
    if(panel){ panel.innerHTML = codeStudioNexusHtml(profile); panel.hidden = false; }
    card.setAttribute('data-studio-nexus-score', String(profile.score || 0));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = studioNexusPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่งเชื่อม AiRA Studio ไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่งเชื่อม AiRA Studio ใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-studio-nexus"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'AiRA Studio Nexus ส่งคำสั่งแล้ว · กำลังเชื่อมทุกระบบ ทุกปุ่ม ทุกคำสั่ง ทุก API และเพิ่มประสิทธิภาพทั้ง Studio', 'is-next-sent');
    submitPrompt(studioNexusPromptFromCard(card, 'กดจากปุ่ม AiRA Nexus'), 'AiRA Studio Nexus Engine · ' + (profile.score || 0) + '/100', {mode:'code_studio_nexus', source:'aira-studio-nexus-engine-7569'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }



  function codeCommunicationSkillProfile(lang, code){
    lang = normalizeCodeLang(lang || 'text');
    code = clean(code || '');
    var nexus = codeStudioNexusProfile(lang, code);
    var quality = codeQualityProfile(lang, code, null, detectCodeSystemCapabilities(lang, code), detectCodeCommandBenefits(lang, code));
    var dims = [];
    function dim(key, label, pass, weight, meaning, benefit, fix){
      dims.push({key:key,label:label,pass:!!pass,weight:weight||10,meaning:meaning,benefit:benefit,fix:fix||''});
    }
    var textSignals = (code.match(/toast|notice|alert|message|status|loading|success|error|empty|placeholder|label|title|description|tooltip|help|guide|docs|manual|aria-live/gi) || []);
    var buttonSignals = (code.match(/data-action|aria-label|<button\b|createElement\(["']button["']\)|onclick|addEventListener/gi) || []);
    var recoverySignals = (code.match(/try\s*\{|catch\s*\(|fallback|retry|timeout|AbortController|error mapping|response shape|isTimeout|finally/gi) || []);
    var guideSignals = (code.match(/step|ขั้นตอน|วิธีใช้|คู่มือ|guide|onboarding|wizard|readme|docs|instruction|manual|walkthrough/gi) || []);
    var claritySignals = (code.match(/summary|สรุป|return format|context|intent|route|resolver|contract|profile|score|percent|matrix|map/gi) || []);
    var accessibilitySignals = (code.match(/aria-|role=|focus|keyboard|tabindex|screen reader|WCAG|contrast|reduced-motion|prefers-reduced-motion/gi) || []);
    var continuitySignals = (code.match(/history|memory|room|continuation|latest|contextProfile|state\.|localStorage|sync|anchor|composer/gi) || []);
    var humanValueSignals = (code.match(/human value|benefit|ช่วย|ประโยชน์|ผู้ใช้|humanity|impact|mission|privacy|consent|permission|safe/gi) || []);
    var multiLanguageSignals = (code.match(/i18n|locale|language|translation|translate|ภาษา|ไทย|English|__\(|_e\(|textdomain|Noto Sans Thai/gi) || []);
    var performanceSignals = (code.match(/requestAnimationFrame|debounce|throttle|content-visibility|lazy|cache|performance|smooth|typing|slide|cadence/gi) || []);
    dim('clear-feedback','Feedback ชัดเจน', textSignals.length >= 8, 12, 'ทุก action ควรบอกสถานะ กำลังทำ สำเร็จ ล้มเหลว และขั้นต่อไป', 'ผู้ใช้ไม่งงว่ากดแล้วเกิดอะไร ลดการกดซ้ำและลดความไม่มั่นใจ', 'เพิ่มข้อความ loading/success/error/empty state ที่อ่านง่ายและมีทางแก้');
    dim('button-language','ภาษาปุ่มมีความหมาย', buttonSignals.length >= 6 && /aria-label|data-human-value|data-command-purpose/i.test(code), 11, 'ปุ่มต้องบอกหน้าที่และประโยชน์ ไม่ใช่มีไว้ให้ดูเยอะ', 'ช่วยให้ผู้ใช้เลือกปุ่มถูกและรู้ว่ากดแล้วได้อะไร', 'เพิ่ม aria-label, purpose, benefit, handler และ test path ให้ทุกปุ่ม');
    dim('error-recovery-copy','Error Recovery อ่านรู้เรื่อง', recoverySignals.length >= 4, 11, 'ข้อความ error ต้องแปลเป็นภาษาคนและมี Retry/Fallback', 'ผู้ใช้แก้ปัญหาต่อได้ ไม่เห็น raw error หรือ HTML 504 แบบน่ากลัว', 'เพิ่ม error mapping, timeout, retry slim, fallback และคำแนะนำสั้น ๆ');
    dim('step-guidance','คู่มือ/ขั้นตอนชัด', guideSignals.length >= 3, 10, 'งานซับซ้อนต้องแตกเป็นขั้นตอน 1,2,3', 'คนไม่เข้าใจโค้ดทำตามได้ ลดการถามซ้ำ', 'เพิ่ม quick guide, usage steps, test steps และ handoff note');
    dim('answer-structure','โครงคำตอบแม่น', claritySignals.length >= 6, 10, 'คำตอบควรมีสรุปก่อน รายละเอียดเท่าที่จำเป็น และผลลัพธ์ที่ใช้ได้', 'ทำให้ AiRA ตอบเหมือนผู้ช่วยมืออาชีพ ไม่วน ไม่ยาวเกิน', 'เพิ่ม answer contract: summary → action → code/result → test → status');
    dim('inclusive-access','เข้าถึงได้หลายกลุ่ม', accessibilitySignals.length >= 4, 10, 'การสื่อสารควรรองรับผู้ใช้มือถือ คีย์บอร์ด screen reader และคนใหม่', 'ทำให้ระบบใช้ได้จริงกว้างขึ้นและลดอุปสรรค', 'เพิ่ม aria, focus, keyboard, contrast และ reduced motion');
    dim('context-continuity','คุยต่อเนื่องได้', continuitySignals.length >= 5, 10, 'ระบบต้องจำบริบทงานล่าสุดอย่างปลอดภัยและต่อคำสั่งได้', 'ผู้ใช้ไม่ต้องอธิบายซ้ำ ช่วยให้พัฒนาเป็นขั้นตอนได้ต่อเนื่อง', 'เพิ่ม latest command anchor, context digest, room/memory sync และ continuation prompt');
    dim('human-tone','น้ำเสียงมีคุณค่า', humanValueSignals.length >= 5, 9, 'คำตอบต้องช่วยคนจริง อบอุ่น ตรง และไม่ทำร้ายผู้ใช้', 'เพิ่มความไว้วางใจและทำให้ระบบช่วยเหลือมนุษย์ได้จริง', 'เพิ่ม Human Benefit, privacy, consent, safe limitation และ no dark pattern');
    dim('multi-language','รองรับภาษา/บริบทหลายแบบ', multiLanguageSignals.length >= 2, 8, 'ควรรับภาษาไทย อังกฤษ ศัพท์เทคนิค และภาษาคนทั่วไป', 'ช่วยผู้ใช้หลายอาชีพ หลายระดับความรู้ และลดการเข้าใจผิด', 'เพิ่ม language intent, i18n/textdomain และคำอธิบายศัพท์เทคนิคง่าย ๆ');
    dim('smooth-delivery','ส่งคำตอบลื่น', performanceSignals.length >= 4, 9, 'จังหวะตอบต้องไม่กระตุก ไม่ render หนักซ้ำ และไม่ทำให้ผู้ใช้รอนานโดยไม่รู้สถานะ', 'ทำให้ประสบการณ์ใกล้ GPT มากขึ้น โดยเฉพาะบนมือถือ', 'ใช้ requestAnimationFrame, cadence, prompt slimming, lazy render และ progress copy');
    var total = dims.reduce(function(a,d){ return a+(d.weight||0); },0) || 1;
    var got = dims.reduce(function(a,d){ return a+(d.pass ? (d.weight||0) : 0); },0);
    var base = Math.round(got * 100 / total);
    var score = clamp(Math.round(base*0.58 + (nexus.score||0)*0.18 + (quality.score||0)*0.14 + Math.min(100, textSignals.length*4)*0.10),0,100);
    var missing = dims.filter(function(d){ return !d.pass; }).map(function(d){ return d.fix || d.label; });
    var passed = dims.filter(function(d){ return d.pass; }).map(function(d){ return d.label; });
    var matrix = [
      'Feedback copy: ' + textSignals.length,
      'Button language: ' + buttonSignals.length,
      'Recovery signals: ' + recoverySignals.length,
      'Guide signals: ' + guideSignals.length,
      'Continuity: ' + continuitySignals.length,
      'Accessibility: ' + accessibilitySignals.length,
      'Human value: ' + humanValueSignals.length
    ];
    return {score:score, level:score>=88?'communication-master':(score>=70?'strong-communication-needs-polish':(score>=48?'basic-communication-engine':'unclear-communication-flow')), dims:dims, missing:missing, passed:passed, matrix:matrix, nexus:nexus, quality:quality};
  }
  function codeCommunicationSkillHtml(profile){
    profile = profile || {};
    var dims = profile.dims || [];
    var matrix = (profile.matrix || []).slice(0,7);
    var missing = (profile.missing || []).slice(0,7);
    return '<div class="communication-engine-head"><div><b>'+icon('brain')+'<span>Communication Quality Engine</span></b><small>ยกระดับทักษะสื่อสารของ AiRA: ชัด อบอุ่น ตรงประเด็น มี feedback, error recovery, guide, context และคุณค่าผู้ใช้</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="communication-engine-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="communication-engine-matrix">'+matrix.map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('')+'</div>'+ 
      '<div class="communication-engine-grid">'+dims.map(function(d){ return '<div class="communication-dim '+(d.pass?'is-pass':'is-missing')+'"><b>'+esc(d.label)+'</b><small>'+esc(d.pass ? d.benefit : d.fix)+'</small></div>'; }).join('')+'</div>'+ 
      '<div class="communication-engine-missing"><b>จุดที่ควรทำให้สื่อสารดีขึ้น</b><ul>'+missing.map(function(x){ return '<li>'+esc(x)+'</li>'; }).join('')+'</ul></div>';
  }
  function communicationQualityPromptFromCard(card, extra){
    var data = codeDataFromCard(card);
    var code = clean(data.code || '');
    var lang = normalizeCodeLang(data.lang || 'text');
    var profile = codeCommunicationSkillProfile(lang, code);
    var missing = (profile.missing || []).join(' | ') || 'ยกระดับการสื่อสารทั้งระบบให้ชัดขึ้น';
    var matrix = (profile.matrix || []).join(' | ');
    return [
      'พัฒนา AiRA Studio / code block นี้ให้เป็น Communication Quality Engine v7.5.7.0: เพิ่มทักษะการสื่อสารให้มีประสิทธิภาพ คุณภาพ เข้าใจง่าย และช่วยผู้ใช้ทำงานสำเร็จจริง',
      '',
      '[เป้าหมายหลัก]',
      '- ทุกคำตอบต้องเริ่มจากผลลัพธ์ที่ผู้ใช้ต้องใช้จริงก่อน แล้วค่อยอธิบายเฉพาะส่วนจำเป็น',
      '- ทุกปุ่มต้องมีภาษา/label/purpose/benefit/handler/feedback/error/retry/test path ชัดเจน',
      '- ทุกคำสั่งต้องแปลเป็นภาษาคน: ทำอะไร ช่วยใคร ถ้าผิดพลาดบอกอย่างไร และทดสอบอย่างไร',
      '- ทุก API/error/status ต้องไม่โยน raw technical text ใส่ผู้ใช้โดยตรง ต้องมีคำอธิบาย + ทางแก้ + retry/fallback',
      '- รองรับคนไม่เข้าใจโค้ด: มีขั้นตอน 1,2,3 มีคู่มือสั้น มีคำอธิบายศัพท์เทคนิค และบอกสถานะใช้งานได้จริงหรือยัง',
      '- สื่อสารอย่างอบอุ่น ตรงประเด็น ไม่โอ้อวด ไม่เดาข้อมูลส่วนตัว ไม่สร้างปุ่มหลอก และไม่บอกว่าเสร็จถ้ายังทำไม่ได้จริง',
      '',
      '[Current Communication Scan]',
      '- Name/File: ' + downloadNameForCode(lang, data.index || 0),
      '- Language: ' + lang,
      '- Communication score: ' + (profile.score || 0) + '/100 · ' + (profile.level || '-'),
      '- Matrix: ' + matrix,
      '- Need improve: ' + missing,
      '- User extra: ' + (trim(extra || '') || 'เพิ่มทักษะการสื่อสารให้ AiRA Studio ทั้งระบบ'),
      '',
      '[ต้องทำในคำตอบใหม่]',
      '1) Communication Map: แสดงวิธีสื่อสารกับผู้ใช้ใน Header/Chat/Composer/Code/Preview/Debug/API/Settings/Docs',
      '2) Button Communication Contract: action → label → purpose → user benefit → loading/success/error copy → retry → aria-label → test path',
      '3) Command Meaning Map: คำสั่งสำคัญ → ความหมายภาษาคน → input/output → risk → test',
      '4) API/Error Copy Map: timeout/403/500/empty response/raw HTML → ข้อความที่ผู้ใช้เข้าใจ + next action',
      '5) Answer Cadence/Structure: สรุปก่อน → สิ่งที่ทำ → โค้ด/ผลลัพธ์ → วิธีทดสอบ → สถานะ %',
      '6) Inclusive UX: ภาษาไทยเข้าใจง่าย, technical term explained, aria/focus/keyboard/mobile, no dark pattern',
      '7) ส่งโค้ดเต็ม runnable เมื่อแก้ได้ โดยรักษาของเดิมที่มีประโยชน์ไว้ทั้งหมด',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → ของเดิมยังอยู่ → สิ่งที่เพิ่ม → Communication Map → Button Contract → Command/API Copy Map → โค้ดเต็ม → วิธีทดสอบ → Communication/Quality/Performance/Nexus %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }
  function runCodeCommunicationQualityFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับยกระดับการสื่อสาร','error'); return; }
    var profile = codeCommunicationSkillProfile(data.lang, data.code);
    var panel = card.querySelector('.aira-code-communication-engine');
    if(panel){ panel.innerHTML = codeCommunicationSkillHtml(profile); panel.hidden = false; }
    card.setAttribute('data-communication-quality-score', String(profile.score || 0));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = communicationQualityPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่ง Communication Quality ไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่ง Communication Quality ใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-communication-quality"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Communication Quality ส่งคำสั่งแล้ว · กำลังปรับคำตอบ ปุ่ม คำสั่ง API และคู่มือให้ชัด อบอุ่น และใช้ได้จริง', 'is-next-sent');
    submitPrompt(communicationQualityPromptFromCard(card, 'กดจากปุ่มสื่อสารคุณภาพ'), 'Communication Quality Engine · ' + (profile.score || 0) + '/100', {mode:'code_communication_quality', source:'aira-studio-communication-quality-7570'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }




  function codeDeepCognitiveCommunicationProfile(lang, code){
    lang = normalizeCodeLang(lang || 'text');
    code = clean(code || '');
    var communication = codeCommunicationSkillProfile(lang, code);
    var nexus = codeStudioNexusProfile(lang, code);
    var dims = [];
    function has(re){ return re.test(code); }
    function dim(key,label,pass,weight,meaning,benefit,fix){ dims.push({key:key,label:label,pass:!!pass,weight:weight||10,meaning:meaning||'',benefit:benefit||'',fix:fix||''}); }
    var intentSignals = collectRegexMatches(code, /(?:intent|goal|purpose|เจตนา|เป้าหมาย|ต้องการ|brief|interpret|resolveDesiredOutput|questionReading|contextReader|latest command)/gi, 80);
    var contextSignals = collectRegexMatches(code, /(?:context|memory|room|topic|history|previous|continuity|command_context|full_context|deep|global|multi.?dimensional|matrix|map)/gi, 80);
    var empathySignals = collectRegexMatches(code, /(?:tone|warm|human|help|ช่วย|ผู้ใช้|benefit|value|feedback|friction|confusion|error copy|plain language|เข้าใจง่าย)/gi, 80);
    var decisionSignals = collectRegexMatches(code, /(?:next step|action|decision|priorit|choose|option|ข้อดี|ข้อเสีย|risk|trade.?off|recommend|สรุป|ขั้นตอน|test path)/gi, 80);
    var learningSignals = collectRegexMatches(code, /(?:explain|meaning|teach|guide|manual|docs|why|reason|ศัพท์|learning|brain|cognitive|memory hook|reflection|review)/gi, 80);
    var safetySignals = collectRegexMatches(code, /(?:safe|privacy|consent|sensitive|ไม่เดา|ไม่อ้าง|permission|security|guard|sanitize|escape|capability|nonce|ethical)/gi, 80);
    var claritySignals = collectRegexMatches(code, /(?:result first|summary|short|concise|direct|clear|loading|success|error|retry|aria-live|status|cadence|typing|smooth)/gi, 80);
    var allButtonCount = (code.match(/<button\b|data-action|createElement\(['"]button['"]\)/gi) || []).length;
    var buttonCopySignals = collectRegexMatches(code, /(?:aria-label|data-human-value|data-command-purpose|data-command-benefit|feedback|success|error|retry|loading|disabled|purpose|benefit)/gi, 120);
    dim('intent-alignment','Intent Alignment / ตรงความคิดเชิงงาน', intentSignals.length >= 5 || has(/desiredOutput|questionReading|contextProfile/i), 12, 'อ่านเจตนาเชิงงานจากคำถามล่าสุดและบริบท ไม่ใช่เดาใจส่วนตัว', 'ช่วยให้คำตอบตรงสิ่งที่ผู้ใช้กำลังคิดจะทำจริงมากขึ้น', 'เพิ่ม intent map, latest-command anchor, resolved output และ safe clarification');
    dim('multi-dimensional-view','Multi-dimensional Context Map', contextSignals.length >= 8, 11, 'มองหลายมิติ: บริบทเดิม เป้าหมาย ข้อจำกัด ระบบที่เกี่ยวข้อง และผู้ได้รับผลกระทบ', 'ลดการตอบแค่มุมเดียวและช่วยเชื่อมระบบทั้งหมดให้แม่นขึ้น', 'เพิ่ม context matrix: user goal, system state, risk, API, UI, value, next action');
    dim('human-empathy','Human Benefit + Emotional Work Tone', empathySignals.length >= 8, 9, 'สื่อสารด้วยน้ำเสียงช่วยเหลือ ลดความเครียด และไม่ทำให้ผู้ใช้หลงทาง', 'ผู้ใช้รู้สึกว่าระบบช่วยจริง ไม่ใช่โยนภาระให้คิดเอง', 'เพิ่ม warm direct copy, feedback copy, plain-language guidance และ recovery tone');
    dim('cognitive-structure','Cognitive Response Structure', learningSignals.length >= 6 && claritySignals.length >= 5, 12, 'โครงคำตอบช่วยสมองทำงานเป็นลำดับ: สนใจ → จำ → เหตุผล → ตัดสินใจ → ลงมือ → ทบทวน', 'ช่วยให้ผู้ใช้เข้าใจเร็ว จำง่าย และลงมือทำต่อได้', 'เพิ่ม Result first, chunking, numbered steps, memory hook, reason, action, reflection');
    dim('decision-support','Decision + Next Action Support', decisionSignals.length >= 6, 10, 'ช่วยเลือกทางต่อโดยแยกข้อดี/ข้อเสีย ความเสี่ยง และขั้นตอนถัดไป', 'ลดการวนซ้ำและช่วยให้ผู้ใช้ตัดสินใจเร็วขึ้น', 'เพิ่ม option matrix, risk/benefit, one recommended next step และ test path');
    dim('safe-intent-guard','Safe Intent Guard', safetySignals.length >= 5, 11, 'ไม่อ้างว่าอ่านใจ ไม่วินิจฉัย ไม่เดาข้อมูลอ่อนไหว และเคารพ privacy/permission', 'ทำให้การสื่อสารลึกขึ้นโดยยังปลอดภัยและน่าเชื่อถือ', 'เพิ่ม guard ข้อจำกัดชัดเจน และแปลเป็นเจตนาเชิงงานเท่านั้น');
    dim('button-command-thinking','Button/Command Thought Contract', allButtonCount === 0 || buttonCopySignals.length >= Math.min(12, Math.max(4, allButtonCount)), 10, 'ทุกปุ่ม/คำสั่งต้องสื่อสารเหตุผลและผลต่อผู้ใช้ ไม่ใช่มีไว้โชว์', 'ทำให้ระบบกดแล้วเข้าใจทันทีว่าช่วยอะไรและจะเกิดอะไรต่อ', 'เพิ่ม action → thought goal → benefit → feedback → error → test path');
    dim('deep-system-relationship','Deep System Relationship', (nexus.score || 0) >= 55 || has(/relationship|nexus|connection|router|registry|connector|sync/i), 10, 'คำตอบต้องเชื่อมระบบ ปุ่ม API state preview debug และคู่มือเข้าด้วยกัน', 'ลดคำสั่งลอยและทำให้ AiRA Studio ตอบเหมือนเข้าใจระบบเดียวกัน', 'เพิ่ม Relationship Map และ Universal Action Registry');
    dim('smooth-cadence','Smooth Answer Cadence', has(/requestAnimationFrame|cadence|typing|slide|smooth|pause|punctuation|aria-live|status/i), 7, 'จังหวะคำตอบต้องนุ่ม มีพัก และไม่กระตุกบนมือถือ', 'ช่วยให้ผู้ใช้ติดตามความคิดของคำตอบได้สบายขึ้น', 'ใช้ cadence controller, pause after punctuation, lazy code render และ reduced motion');
    dim('reflection-loop','Reflection / Learning Loop', has(/reflection|review|learn|memory hook|topic|interest|feedback|like|stats|improve|iterate|retry/i), 8, 'ระบบควรช่วยผู้ใช้ทบทวนว่าเข้าใจอะไรและควรต่อยอดอย่างไร', 'พัฒนาคุณภาพการคิดของผู้ใช้และระบบในรอบถัดไป', 'เพิ่ม brief recap, what changed, next iteration และ memory/topic hook');
    var total = dims.reduce(function(a,d){ return a + (d.weight || 0); }, 0) || 1;
    var pass = dims.reduce(function(a,d){ return a + (d.pass ? (d.weight || 0) : 0); }, 0);
    var score = clamp(Math.round(pass * 100 / total), 0, 100);
    score = clamp(Math.round(score * 0.55 + (communication.score || 0) * 0.25 + (nexus.score || 0) * 0.20), 0, 100);
    var missing = dims.filter(function(d){ return !d.pass; }).map(function(d){ return d.fix || d.label; });
    var matrix = [
      'Intent signals: ' + intentSignals.length,
      'Context dimensions: ' + contextSignals.length,
      'Empathy/value: ' + empathySignals.length,
      'Decision support: ' + decisionSignals.length,
      'Learning/cognitive: ' + learningSignals.length,
      'Safe guard: ' + safetySignals.length,
      'Buttons/actions: ' + allButtonCount
    ];
    var level = score >= 90 ? 'deep-cognitive-master' : (score >= 74 ? 'multi-dimensional-communicator' : (score >= 55 ? 'needs-deeper-thinking-map' : 'surface-level-communication'));
    return {score:score, level:level, dims:dims, missing:missing, matrix:matrix, communication:communication, nexus:nexus};
  }
  function codeDeepCognitiveCommunicationHtml(profile){
    profile = profile || {};
    var dims = profile.dims || [];
    var matrix = (profile.matrix || []).slice(0,7);
    var missing = (profile.missing || []).slice(0,7);
    return '<div class="deep-cognitive-head"><div><b>'+icon('brain')+'<span>Deep Cognitive Communication Engine</span></b><small>มองหลายมิติ ตอบตรงเจตนาเชิงงาน และช่วยจัดระบบความคิด: attention, memory, reasoning, emotion, creativity, decision, action, reflection</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="deep-cognitive-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="deep-cognitive-matrix">'+matrix.map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('')+'</div>'+ 
      '<div class="deep-cognitive-grid">'+dims.map(function(d){ return '<div class="deep-cognitive-dim '+(d.pass?'is-pass':'is-missing')+'"><b>'+esc(d.label)+'</b><small>'+esc(d.pass ? d.benefit : d.fix)+'</small></div>'; }).join('')+'</div>'+ 
      '<div class="deep-cognitive-safe"><b>Safe Intent Guard</b><span>ระบบนี้อ่านเจตนาเชิงงานจากข้อความและบริบทเท่านั้น ไม่อ้างว่าอ่านใจจริง ไม่วินิจฉัย และไม่เดาข้อมูลส่วนตัวอ่อนไหว</span></div>'+ 
      '<div class="deep-cognitive-missing"><b>จุดที่ควรเติมเพื่อสื่อสารลึกขึ้น</b><ul>'+missing.map(function(x){ return '<li>'+esc(x)+'</li>'; }).join('')+'</ul></div>';
  }
  function deepCognitiveCommunicationPromptFromCard(card, extra){
    var data = codeDataFromCard(card);
    var code = clean(data.code || '');
    var lang = normalizeCodeLang(data.lang || 'text');
    var profile = codeDeepCognitiveCommunicationProfile(lang, code);
    var missing = (profile.missing || []).join(' | ') || 'เพิ่มการสื่อสารลึกและโครงช่วยคิดให้ครบถ้วน';
    var matrix = (profile.matrix || []).join(' | ');
    return [
      'พัฒนา AiRA Studio / code block นี้ให้เป็น Deep Cognitive Communication Engine v7.5.7.1: เพิ่มทักษะสื่อสารลึก มองหลายมิติ ตอบตรงเจตนาเชิงงาน และช่วยให้ผู้ใช้คิดเป็นระบบมากขึ้น',
      '',
      '[ขอบเขตความปลอดภัยสำคัญ]',
      '- ห้ามอ้างว่าอ่านใจจริง ห้ามวินิจฉัยสมอง/จิตใจ/สุขภาพ และห้ามเดาข้อมูลส่วนตัวหรือข้อมูลอ่อนไหว',
      '- ให้ตีความเฉพาะเจตนาเชิงงานจากข้อความล่าสุด บริบทระบบ และสิ่งที่ผู้ใช้ต้องการทำจริง',
      '- ถ้าข้อมูลไม่พอ ให้เลือก assumption ที่ปลอดภัยที่สุดและบอกว่าตรวจได้อย่างไร',
      '',
      '[เป้าหมายหลัก]',
      '- มองโจทย์หลายมิติ: เป้าหมาย, ปัญหา, ข้อจำกัด, ความเร่งด่วน, บริบทเดิม, ระบบที่เกี่ยวข้อง, API, ปุ่ม, ความเสี่ยง, คุณค่าผู้ใช้, ขั้นตอนต่อไป',
      '- โครงคำตอบต้องช่วยสมองผู้ใช้ทำงานครบวงจร: Attention → Memory → Reasoning → Emotion Regulation → Creativity → Decision → Action → Reflection',
      '- ตอบผลลัพธ์ที่ผู้ใช้ต้องการก่อน แล้วค่อยอธิบายเหตุผล/ขั้นตอน/ทดสอบ/ความเสี่ยงแบบสั้นแต่ครบ',
      '- ทุกปุ่ม ทุกคำสั่ง ทุก API ทุก feedback ต้องมี Thought Contract: ทำเพื่ออะไร ช่วยใคร กดแล้วเกิดอะไร ถ้าพลาดแก้อย่างไร ทดสอบอย่างไร',
      '- เพิ่ม Learning Loop: สรุปสิ่งที่เข้าใจ, สิ่งที่เปลี่ยนจากเดิม, จุดที่ยังไม่จบ, และขั้นตอนถัดไปที่ชัดเจน',
      '',
      '[Current Deep Cognitive Scan]',
      '- Name/File: ' + downloadNameForCode(lang, data.index || 0),
      '- Language: ' + lang,
      '- Deep Cognitive Communication score: ' + (profile.score || 0) + '/100 · ' + (profile.level || '-'),
      '- Matrix: ' + matrix,
      '- Need improve: ' + missing,
      '- User extra: ' + (trim(extra || '') || 'เพิ่มการสื่อสารลึก มองหลายมิติ และช่วยพัฒนาการคิดของผู้ใช้'),
      '',
      '[ต้องทำในคำตอบใหม่]',
      '1) Deep Intent Map: latest user thought/work intent → what they likely need now → safe assumption → blocked/unknown items',
      '2) Multi-dimensional Context Map: goal, pain, constraint, urgency, existing system, UI/API/code relationship, risk, human value, next action',
      '3) Cognitive Response Map: Attention hook, memory anchor, reasoning chain summary, emotional reassurance, creative options, decision, action steps, reflection',
      '4) Button/Command Thought Contract: action → purpose → user benefit → loading/success/error copy → retry → aria-label → test path',
      '5) API/Error Understanding Map: endpoint/status/error/raw response → user-friendly meaning → next fix → retry/fallback',
      '6) Learning/Brain Support UX: numbered steps, plain Thai, glossary, short recap, progress %, and no unnecessary clutter',
      '7) ส่งโค้ดเต็ม runnable เมื่อแก้ได้ โดยรักษาของเดิมที่มีประโยชน์ไว้ทั้งหมด',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → ของเดิมยังอยู่ → สิ่งที่เพิ่ม → Deep Intent Map → Multi-dimensional Map → Cognitive Response Map → Button/API Thought Contract → โค้ดเต็ม → วิธีทดสอบ → Deep Cognitive/Communication/Quality/Performance/Nexus %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }
  function runCodeDeepCognitiveCommunicationFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับยกระดับการสื่อสารลึก','error'); return; }
    var profile = codeDeepCognitiveCommunicationProfile(data.lang, data.code);
    var panel = card.querySelector('.aira-code-deep-cognitive-engine');
    if(panel){ panel.innerHTML = codeDeepCognitiveCommunicationHtml(profile); panel.hidden = false; }
    card.setAttribute('data-deep-cognitive-score', String(profile.score || 0));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = deepCognitiveCommunicationPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่ง Deep Cognitive Communication ไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่งสื่อสารลึกใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-deep-cognitive-communication"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Deep Cognitive Communication ส่งคำสั่งแล้ว · กำลังมองหลายมิติ จัดโครงความคิด และทำให้คำตอบตรงเจตนาเชิงงานมากขึ้น', 'is-next-sent');
    submitPrompt(deepCognitiveCommunicationPromptFromCard(card, 'กดจากปุ่มสื่อสารลึก'), 'Deep Cognitive Communication Engine · ' + (profile.score || 0) + '/100', {mode:'code_deep_cognitive_communication', source:'aira-studio-deep-cognitive-communication-7571'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }


  function codeCommandFusionProfile(lang, code){
    lang = normalizeCodeLang(lang || 'text');
    code = clean(code || '');
    var nexus = codeStudioNexusProfile(lang, code);
    var connection = codeSystemConnectionProfile(lang, code);
    var universal = codeUniversalPlatformProfile(lang, code);
    var quality = codeQualityProfile(lang, code, null, detectCodeSystemCapabilities(lang, code), detectCodeCommandBenefits(lang, code));
    var dims = [];
    function dim(key, label, pass, weight, meaning, benefit, fix){
      dims.push({key:key,label:label,pass:!!pass,weight:weight||10,meaning:meaning,benefit:benefit,fix:fix||''});
    }
    var actionsFound = (code.match(/data-action=["'][^"']+|addEventListener\s*\(|onclick\s*=|handleAction|command router|action registry/gi) || []);
    var handlerFound = (code.match(/function\s+[A-Za-z0-9_]+\s*\(|=>\s*\{|case\s+["']|if\s*\(\s*action\s*===|switch\s*\(/gi) || []);
    var apiFound = (code.match(/fetch\s*\(|admin-ajax\.php|wp_ajax_|register_rest_route|REST|endpoint|api|webhook|response|payload|HTTP|timeout|retry/gi) || []);
    var colorFound = (code.match(/#[0-9a-fA-F]{3,8}\b|rgb\(|hsl\(|linear-gradient|palette|color|สี|theme|brand|background|foreground|accent/gi) || []);
    var fileFound = (code.match(/download\(|Blob\(|File\(|zip|filename|mime|export|import|template|sample|ตัวอย่าง|preview|iframe|sandbox|svg|canvas/gi) || []);
    var systemFound = (code.match(/game|เกม|theme|ธีม|app|แอป|program|โปรแกรม|pwa|plugin|wordpress|dashboard|widget|gutenberg|elementor|shortcode|module|component|service|database|storage/gi) || []);
    var valueFound = (code.match(/benefit|human value|ประโยชน์|ช่วย|ผู้ใช้|feedback|test|QC|quality|accessibility|privacy|permission|safe|rollback/gi) || []);
    var outputFound = (code.match(/render|preview|download|copy|package|export|result|status|toast|notice|success|error|loading|progress|score|percent|รายงาน/gi) || []);
    var commandBenefits = detectCodeCommandBenefits(lang, code);
    dim('action-inventory','Command / Button Inventory', actionsFound.length >= 4, 12, 'ต้องรู้ว่ามีคำสั่งและปุ่มใดอยู่ในระบบ', 'ทำให้ทุกปุ่มไม่ลอยและตรวจได้ว่ากดแล้วเกิดอะไร', 'เพิ่ม Action Registry พร้อม purpose, handler, feedback และ test path');
    dim('handler-binding','Handler Binding', handlerFound.length >= 4 && actionsFound.length > 0, 12, 'ทุก action ต้องมี handler ที่เชื่อมผลลัพธ์จริง', 'ลดปุ่มกดไม่ได้และลด UI หลอก', 'เชื่อม data-action → function → state/API → result/error');
    dim('api-connector','API / Connector Fusion', apiFound.length >= 3 || (nexus.score||0) >= 60, 11, 'คำสั่งที่ต้องใช้ข้อมูลต้องเชื่อม API/endpoint/retry/error shape', 'ทำให้ระบบคุยกับบริการจริงได้และผู้ใช้เห็นสถานะ', 'เพิ่ม Connector Contract: endpoint, method, auth, timeout, retry, response mapping');
    dim('color-to-file','Color → File / Theme Example', colorFound.length >= 2 && fileFound.length >= 2, 11, 'สี/พาเลตต์ควรแปลงเป็นตัวอย่างไฟล์ ธีม หรือ preview ได้', 'ผู้ใช้เห็นภาพจริงและนำ palette ไปใช้ต่อได้ทันที', 'เพิ่ม palette extractor + sample SVG/CSS/theme file + preview/download');
    dim('multi-output','Reusable Output Engine', fileFound.length >= 4, 10, 'คำสั่งควรผลิตผลลัพธ์ที่จับต้องได้ เช่น preview, file, zip, report', 'ลดคำตอบที่เป็นแค่แนวคิดและเพิ่มงานที่ส่งต่อได้', 'เพิ่ม output contract: preview/copy/download/export/package');
    dim('cross-domain','Cross-domain System Use', systemFound.length >= 4 || (universal.score||0) >= 55, 10, 'คำสั่งเดียวควรต่อยอดได้หลายแนว เช่น เกม ธีมเว็บ แอป โปรแกรมช่วยงาน', 'ทำให้ Code block เป็นตัวสร้างระบบที่หลากหลายตามโจทย์ผู้ใช้', 'เพิ่ม Domain Adapter Matrix: web/theme/game/app/utility/wp/API/data');
    dim('relationship-map','Relationship Map', (connection.score||0) >= 55 || (nexus.score||0) >= 55, 10, 'ทุกคำสั่งต้องสัมพันธ์กับ UI/state/API/security/QC/docs', 'ระบบทำงานเป็น product เดียว ไม่เป็นชุดปุ่มแยกกัน', 'เพิ่ม command relationship graph และ state sync contract');
    dim('human-value','Human Value Contract', valueFound.length >= 6 || commandBenefits.length >= 4, 10, 'ทุกคำสั่งต้องตอบว่าช่วยใครและลดความเสี่ยงอะไร', 'ทำให้การสร้างระบบมีคุณค่า ไม่ใช่เพิ่มฟีเจอร์เพื่อความเยอะ', 'เพิ่ม Human Value Map + risk guard + test of benefit');
    dim('visible-feedback','Visible Feedback / Status', outputFound.length >= 6, 8, 'ทุก action ต้องมี loading/success/error/progress/status ที่อ่านรู้เรื่อง', 'ผู้ใช้ไม่หลงทางและรู้ว่าระบบกำลังทำอะไร', 'เพิ่ม status renderer + toast + retry copy + no-empty-result guard');
    dim('quality-qc','Quality / QC Fusion', (quality.score||0) >= 60 || /test|QC|debug|lint|validate|sanitize|escape|nonce|permission/i.test(code), 8, 'ทุกการประยุกต์ต้องมี QC ความปลอดภัย และ rollback', 'ทำให้ระบบที่สร้างน่าเชื่อถือและแก้ต่อได้', 'เพิ่ม test matrix, permission, sanitize/escape, rollback และ docs handoff');
    var total = dims.reduce(function(a,d){ return a+(d.weight||0); },0) || 1;
    var got = dims.reduce(function(a,d){ return a+(d.pass ? (d.weight||0) : 0); },0);
    var base = Math.round(got * 100 / total);
    var score = clamp(Math.round(base*0.42 + (connection.score||0)*0.16 + (nexus.score||0)*0.15 + (universal.score||0)*0.12 + (quality.score||0)*0.10 + Math.min(100, commandBenefits.length*8)*0.05),0,100);
    var missing = dims.filter(function(d){ return !d.pass; }).map(function(d){ return d.fix || d.label; });
    var matrix = [
      'Actions: ' + actionsFound.length,
      'Handlers: ' + handlerFound.length,
      'API: ' + apiFound.length,
      'Colors: ' + colorFound.length,
      'Files/Preview: ' + fileFound.length,
      'Domains: ' + systemFound.length,
      'Value/QC: ' + valueFound.length
    ];
    var examples = [];
    if(colorFound.length){ examples.push('Palette → sample-theme.css / sample-card.svg / preview token'); }
    if(systemFound.length){ examples.push('Command → web/theme/game/app/utility adapter'); }
    if(apiFound.length){ examples.push('API → connector status + retry + error mapping'); }
    if(actionsFound.length){ examples.push('Button → handler + feedback + test path'); }
    if(fileFound.length){ examples.push('Output → copy/download/preview/package/report'); }
    if(!examples.length){ examples.push('Text command → useful file/example/system blueprint'); }
    return {score:score, level:score>=88?'fusion-intelligence-ready':(score>=70?'strong-command-fusion':(score>=48?'partial-fusion-needs-links':'commands-still-fragmented')), dims:dims, missing:missing, matrix:matrix, examples:examples, nexus:nexus, connection:connection, universal:universal, quality:quality};
  }
  function codeCommandFusionHtml(profile){
    profile = profile || {};
    var dims = profile.dims || [];
    var matrix = (profile.matrix || []).slice(0,7);
    var missing = (profile.missing || []).slice(0,7);
    var examples = (profile.examples || []).slice(0,5);
    return '<div class="command-fusion-head"><div><b>'+icon('brain')+'<span>Command Fusion Intelligence Engine</span></b><small>เอาทุกคำสั่ง สี ปุ่ม API ไฟล์ preview และ QC มาประยุกต์ร่วมกันให้เกิดระบบ/ไฟล์ตัวอย่างที่ใช้ต่อได้จริง</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="command-fusion-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="command-fusion-matrix">'+matrix.map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('')+'</div>'+ 
      '<div class="command-fusion-examples"><b>ตัวอย่างการประยุกต์</b>'+examples.map(function(x){ return '<span>'+esc(x)+'</span>'; }).join('')+'</div>'+ 
      '<div class="command-fusion-grid">'+dims.map(function(d){ return '<div class="command-fusion-dim '+(d.pass?'is-pass':'is-missing')+'"><b>'+esc(d.label)+'</b><small>'+esc(d.pass ? d.benefit : d.fix)+'</small></div>'; }).join('')+'</div>'+ 
      '<div class="command-fusion-missing"><b>จุดที่ควรเชื่อมให้ฉลาดขึ้น</b><ul>'+missing.map(function(x){ return '<li>'+esc(x)+'</li>'; }).join('')+'</ul></div>';
  }
  function commandFusionPromptFromCard(card, extra){
    var data = codeDataFromCard(card);
    var lang = normalizeCodeLang(data.lang || 'text');
    var code = clean(data.code || '');
    var profile = codeCommandFusionProfile(lang, code);
    var missing = (profile.missing || []).join(' | ') || 'เพิ่มการประยุกต์ทุกคำสั่งให้เชื่อมกันเป็นระบบเดียว';
    var matrix = (profile.matrix || []).join(' | ');
    var examples = (profile.examples || []).join(' | ');
    return [
      'พัฒนา AiRA Studio / code block นี้ให้เป็น Command Fusion Intelligence Engine v7.5.7.4: เอาทุกคำสั่งในระบบมาประยุกต์ใช้งานร่วมกันอย่างอัจฉริยะ และผลิตผลลัพธ์ที่ผู้ใช้ใช้ต่อได้จริง',
      '',
      '[เป้าหมายหลัก]',
      '- ทุกปุ่ม ทุก data-action ทุกคำสั่ง ทุก API ทุก state ทุก preview ทุก file output ต้องเชื่อม Relationship Map เดียวกัน',
      '- ถ้าพบสี/พาเลตต์/brand tone ให้แปลงเป็นตัวอย่างไฟล์ที่จับต้องได้ เช่น sample-theme.css, sample-card.svg, design-token.json, preview HTML หรือ theme snippet',
      '- ถ้าพบคำสั่งสร้างระบบ ให้ประยุกต์เป็น adapter ได้หลายแบบ เช่น เกม, ธีมเว็บ, Web App, โปรแกรมช่วยงาน, PWA, WordPress plugin/block/widget, API/backend, dashboard, data tool',
      '- ทุกคำสั่งต้องมี purpose, input, output, handler, feedback, error, retry, permission, QC และ human value',
      '- ห้ามสร้างปุ่มหลอก ห้ามเคลมว่าเชื่อมต่อสำเร็จถ้ายังไม่ได้ทดสอบจริง ห้าม bypass ความปลอดภัย/ความเป็นส่วนตัว/กฎหมาย',
      '',
      '[Current Fusion Scan]',
      '- Name/File: ' + downloadNameForCode(lang, data.index || 0),
      '- Language: ' + lang,
      '- Fusion score: ' + (profile.score || 0) + '/100 · ' + (profile.level || '-'),
      '- Matrix: ' + matrix,
      '- Example routes: ' + examples,
      '- Need improve: ' + missing,
      '- User extra: ' + (trim(extra || '') || 'ประยุกต์ทุกคำสั่งให้เป็นความสามารถที่อัจฉริยะและสร้างคุณค่าจริง'),
      '',
      '[ต้องทำในคำตอบใหม่]',
      '1) Command Fusion Map: ปุ่ม/คำสั่ง/API/state/file/preview/QC ทั้งหมดสัมพันธ์กันอย่างไร',
      '2) Color-to-File Example: ถ้ามีสีหรือ brand tone ให้สร้างไฟล์ตัวอย่าง/preview/design token ที่ใช้ต่อได้',
      '3) Domain Adapter Matrix: ระบบนี้ต่อยอดเป็นเกม เว็บ ธีม แอป โปรแกรมช่วยงาน WordPress/API/Data ได้ส่วนไหนบ้าง',
      '4) Button + Command Contract: ทุกปุ่มต้องมี handler, loading, success, error, retry, aria-label, test path, human value',
      '5) API + Data Contract: endpoint/method/auth/payload/response/error/timeout/retry/cache/security',
      '6) Output Contract: copy/download/preview/package/report และชื่อไฟล์อังกฤษสั้น',
      '7) QC Matrix: responsive, browser/device, accessibility, security, privacy, rollback, performance',
      '8) ส่งโค้ดเต็ม runnable เมื่อแก้ได้ โดยรักษาของเดิมที่มีประโยชน์ไว้ทั้งหมด',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → ของเดิมยังอยู่ → สิ่งที่เพิ่ม → Command Fusion Map → Color/File Example → Domain Adapter Matrix → Button/API Contract → โค้ดเต็ม → วิธีทดสอบ → Fusion/Quality/Performance/Nexus %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }
  function runCodeCommandFusionFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับประยุกต์คำสั่ง','error'); return; }
    var profile = codeCommandFusionProfile(data.lang, data.code);
    var panel = card.querySelector('.aira-code-command-fusion');
    if(panel){ panel.innerHTML = codeCommandFusionHtml(profile); panel.hidden = false; }
    card.setAttribute('data-command-fusion-score', String(profile.score || 0));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = commandFusionPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่ง Command Fusion ไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่งประยุกต์อัจฉริยะใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-command-fusion"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Command Fusion ส่งคำสั่งแล้ว · กำลังประยุกต์ทุกคำสั่ง สี ปุ่ม API และไฟล์ตัวอย่างให้เชื่อมกันเป็นระบบอัจฉริยะ', 'is-next-sent');
    submitPrompt(commandFusionPromptFromCard(card, 'กดจากปุ่มประยุกต์อัจฉริยะ'), 'Command Fusion Intelligence Engine · ' + (profile.score || 0) + '/100', {mode:'code_command_fusion', source:'aira-studio-command-fusion-7574'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }

  function codeDevMasterProfile(lang, code){
    lang = normalizeCodeLang(lang || 'text');
    code = clean(code || '');
    var cap = detectCodeSystemCapabilities(lang, code);
    var commands = detectCodeCommandBenefits(lang, code);
    var audit = codeStaticAudit({lang:lang, code:code});
    var reality = codeRealityProfile({lang:lang, code:code});
    var parts = detectCodeParts(lang, code);
    var dims = [];
    function dim(key, label, pass, weight, meaning, benefit, fix){
      dims.push({key:key,label:label,pass:!!pass,weight:weight||10,meaning:meaning,benefit:benefit,fix:fix||''});
    }
    var hasArchitecture = /class\s+|function\s+|const\s+|let\s+|var\s+|module|component|controller|service|repository|schema|interface|namespace|add_action|register_rest_route/i.test(code) && parts.length >= 1;
    var hasStateFlow = /(state|store|option|meta|database|history|memory|settings|localStorage|sessionStorage|get_option|update_option|save|load|sync)/i.test(code);
    var hasApiFlow = /(fetch\s*\(|XMLHttpRequest|admin-ajax\.php|wp_ajax_|register_rest_route|endpoint|api|webhook|request|response|HTTP)/i.test(code);
    var hasUiFeedback = /(toast|notice|alert|status|loading|success|error|aria-live|disabled|spinner|progress|percent|feedback|message)/i.test(code);
    var hasPreviewQc = /(preview|sandbox|debug|test|QC|quality|score|health|inspector|report)/i.test(code);
    var hasValue = commands.length >= 3 || /(benefit|human value|ประโยชน์|ผู้ใช้|ช่วย|feedback|ทดสอบ)/i.test(code);
    var hasDocs = /(readme|docs|manual|usage|install|วิธีใช้|คู่มือ|ขั้นตอน|step\s*1)/i.test(code);
    var hasResponsive = /(@media|clamp\(|minmax\(|viewport|responsive|aria-|role=|focus-visible|tabindex)/i.test(code);
    var hasSafety = (audit.score || 0) >= 70 && !/(eval\s*\(|shell_exec\s*\(|passthru\s*\(|proc_open\s*\()/i.test(code);
    var isCompleteOutput = code.length > 900 && (parts.length >= 2 || cap.totalFound >= 4 || /Plugin\s+Name\s*:|<!doctype|<html|export\s+default|class\s+/i.test(code));
    dim('architecture','Architecture / File Roles', hasArchitecture, 12, 'มีโครงสร้างระบบและบทบาทไฟล์/โมดูลที่อ่านต่อได้', 'ช่วยให้พัฒนาต่อแบบไม่หลงทางและไม่ทับของเดิม', 'เพิ่ม file map, module roles, class/function responsibilities');
    dim('state-data','State / Data Flow', hasStateFlow, 10, 'มีที่มาที่ไปของข้อมูล สถานะ หรือ settings', 'ช่วยให้ผู้ใช้ทำงานต่อเนื่อง ไม่กรอกซ้ำ และตรวจย้อนหลังได้', 'เพิ่ม state/store/options/history + validation');
    dim('api-integration','API / Connector Flow', hasApiFlow, 10, 'มีช่องทางเชื่อม frontend/backend/API หรือ mock connector', 'ทำให้ระบบต่อกับบริการจริงและเห็น status/error ได้', 'เพิ่ม endpoint, payload, HTTP status, timeout, retry/error');
    dim('ui-feedback','UI Feedback / Button Contract', hasUiFeedback, 12, 'ทุก action สำคัญมี loading/success/error ให้ผู้ใช้เห็น', 'ลดปุ่มกดไม่ได้และลดความสับสนหลังคลิก', 'เพิ่ม toast/status/disabled/loading/error guard ทุกปุ่ม');
    dim('security-qc','Security / Permission / QC', hasSafety, 14, 'มีสิทธิ์ nonce sanitize escape และไม่ใช้คำสั่งเสี่ยง', 'ลดความเสี่ยงเว็บเสีย ข้อมูลรั่ว หรือใช้งานผิดสิทธิ์', 'เพิ่ม nonce/capability/sanitize/escape/permission_callback');
    dim('preview-test','Preview / Test / Debug', hasPreviewQc || reality.previewReady, 10, 'มีระบบทดลอง ดูตัวอย่าง หรือรายงานตรวจบั๊ก', 'ช่วยให้เห็นก่อนติดตั้งและลดการแก้ซ้ำ', 'เพิ่ม Preview/QC/Debug/Report/Success %');
    dim('responsive-a11y','Responsive / Accessibility', hasResponsive, 8, 'รองรับหลายขนาดจอและการเข้าถึง', 'ช่วยให้คนใช้ได้จริงบนมือถือ แท็บเล็ต เดสก์ท็อป และ keyboard', 'เพิ่ม @media, focus-visible, aria-label, touch target');
    dim('human-value','Human Value Map', hasValue, 10, 'ปุ่มและคำสั่งมีความหมาย/ประโยชน์ต่อผู้ใช้', 'ทำให้ระบบผลิตเพื่อช่วยคนจริง ไม่ใช่มีฟังก์ชันลอย', 'เพิ่ม Human Value Map + Command Benefit Map');
    dim('docs-handoff','Docs / Handoff', hasDocs, 6, 'มีคู่มือ วิธีติดตั้ง ใช้งาน และส่งต่อ', 'ช่วยให้คนไม่เข้าใจโค้ดใช้งานระบบได้จริง', 'เพิ่มคู่มือ install/use/test/rollback');
    dim('complete-code','Complete Runnable Code', isCompleteOutput, 8, 'โค้ดมีขนาดและองค์ประกอบพอเป็นระบบที่นำไปใช้ต่อได้', 'ลดคำตอบที่เป็นแค่แผนหรือ snippet กระจัดกระจาย', 'ส่งโค้ดเต็มใน fenced code block พร้อมไฟล์/ชื่อที่ชัดเจน');
    var total = dims.reduce(function(a,d){return a + (d.weight||0);}, 0);
    var got = dims.reduce(function(a,d){return a + (d.pass ? (d.weight||0) : 0);}, 0);
    var score = clamp(Math.round((got / Math.max(1,total)) * 100), 0, 100);
    score = clamp(Math.round(score * 0.62 + (cap.score || 0) * 0.18 + (audit.score || 0) * 0.12 + (reality.realness || 0) * 0.08), 0, 100);
    var missing = dims.filter(function(d){return !d.pass;}).map(function(d){return d.label;});
    var strengths = dims.filter(function(d){return d.pass;}).map(function(d){return d.label;});
    var level = score >= 88 ? 'dev_master_ready' : (score >= 70 ? 'senior_system_ready' : (score >= 48 ? 'builder_expand_needed' : 'snippet_needs_system_design'));
    return {score:score, level:level, dims:dims, missing:missing, strengths:strengths, capability:cap, audit:audit, reality:reality, commands:commands, parts:parts};
  }

  function codeDevMasterEngineHtml(profile){
    profile = profile || {};
    var dims = Array.isArray(profile.dims) ? profile.dims : [];
    var cards = dims.slice(0, 10).map(function(it){
      return '<li class="'+(it.pass?'is-pass':'is-fix')+'"><b>'+esc(it.label || it.key)+'</b><span>'+esc(it.meaning || '')+'</span><small>'+esc(it.pass ? (it.benefit || 'พร้อมใช้ต่อ') : ('ควรเติม: ' + (it.fix || '-')))+'</small></li>';
    }).join('');
    var missing = (profile.missing || []).slice(0, 6).join(', ') || 'ผ่านระดับหลักแล้ว';
    var strengths = (profile.strengths || []).slice(0, 6).join(', ') || 'รอวิเคราะห์จาก code block';
    return '<div class="dev-master-head"><div><b>'+icon('performance')+'<span>Codeblock Dev Master Engine</span></b><small>ยกระดับ code block ให้ทำหน้าที่พัฒนาระบบจริง: architecture, API/data, security, QC, button value, docs และ runnable code</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="dev-master-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="dev-master-summary"><p><b>จุดแข็ง:</b> '+esc(strengths)+'</p><p><b>ต้องเติม:</b> '+esc(missing)+'</p></div>'+ 
      '<ul class="dev-master-dims">'+cards+'</ul>'+ 
      '<div class="dev-master-rules"><span>โค้ดเต็ม</span><span>ปุ่มต้องมีประโยชน์</span><span>Preview/QC ก่อนใช้จริง</span><span>รักษาของเดิม</span><span>คู่มือสำหรับผู้ใช้</span></div>';
  }

  function devMasterPromptFromCard(card, extra){
    var data = codeDataFromCard(card); var lang = normalizeCodeLang(data.lang || 'text'); var code = data.code || '';
    var profile = codeDevMasterProfile(lang, code);
    var capText = capabilityMapText(profile.capability || {});
    var commandText = commandBenefitMapText(profile.commands || []);
    var name = headerValueFromPhp(code, 'Plugin Name') || downloadNameForCode(lang, data.index || 0);
    return [
      'พัฒนา code block นี้ให้เป็นระบบระดับ Codeblock Dev Master Engine ให้ละเอียด ครบ และใช้งานจริงมากกว่าคำตอบโค้ดทั่วไป',
      '',
      '[เป้าหมายคุณภาพ]',
      '- ทำงานเหมือนทีมพัฒนาระบบครบชุด: Product Architect + Senior Developer + UX Engineer + Security + QA + Documentation',
      '- ตั้งมาตรฐานสูงกว่าคำตอบ assistant ทั่วไป เช่น Claude-style artifact ที่ดี แต่ต้องเน้นโค้ดเต็ม ใช้งานจริง ตรวจได้ และติดตั้ง/ต่อยอดได้',
      '- ห้ามตอบแค่แผน ถ้าแก้/เติมโค้ดได้ ให้ส่งโค้ดเต็มใน fenced code block ทันที',
      '- ห้ามลบของเดิมที่มีประโยชน์ ให้บอกชัดเจนว่าของเดิมยังอยู่และสิ่งใหม่เพิ่มอะไร',
      '- ทุกปุ่ม ทุกคำสั่ง ทุกฟังก์ชันต้องมีประโยชน์ต่อผู้ใช้จริง มี feedback มี error handling และทดสอบได้',
      '- เพิ่ม Humanity Value Builder: ระบบที่สร้างต้องช่วยผู้ใช้และผู้อื่นจริง เคารพความปลอดภัย ความเป็นส่วนตัว การเข้าถึงง่าย และไม่สร้างปุ่ม/คำสั่งที่หลอกหรือไม่มีประโยชน์',
      '',
      '[Current Dev Master Scan]',
      '- Name/File: ' + name,
      '- Language: ' + lang,
      '- Dev Master score: ' + (profile.score || 0) + '/100 · ' + (profile.level || '-'),
      '- Missing: ' + ((profile.missing || []).join(', ') || 'none'),
      '- Capability: ' + capText,
      '- Command Benefit: ' + commandText,
      '- User extra: ' + (trim(extra || '') || 'ยกระดับ code block ให้สร้างระบบได้จริงและมีคุณค่าต่อผู้ใช้'),
      '',
      '[ต้องทำในคำตอบใหม่]',
      '1) ทวนโจทย์สั้น ๆ และระบุเป้าหมายผู้ใช้',
      '2) สรุปของเดิมยังอยู่ + สิ่งใหม่ที่เพิ่ม',
      '3) วาง Architecture / File Map / Module Role / Data Flow / API Flow',
      '4) ทำ Button Contract: ปุ่มแต่ละปุ่มช่วยอะไร กดแล้วเกิดอะไร ถ้าพลาดแจ้งอย่างไร',
      '5) ทำ Command Benefit Map: คำสั่งสำคัญแต่ละชุดช่วยผู้ใช้อย่างไร',
      '6) เติม Security: permission, nonce, sanitize, escape, validation, error guard, no unsafe command',
      '7) เติม UX: loading, success, error, disabled, aria-label, focus, responsive ทุกอุปกรณ์',
      '8) เติม Preview/QC/Debug/Success % และวิธีทดสอบทุกปุ่ม/ทุกฟังก์ชัน',
      '9) เติมคู่มือใช้งานสำหรับคนไม่เข้าใจโค้ด พร้อมขั้นตอน 1,2,3',
      '10) ส่งโค้ดเต็ม runnable ใน fenced code block พร้อมชื่อไฟล์ชัดเจน',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → Dev Master Upgrade Map → Human Value/Button Contract → Command Benefit Map → โค้ดเต็ม → วิธีติดตั้ง/ใช้งาน → วิธีทดสอบ → สถานะใช้งานได้แล้วหรือยัง + Success %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }

  function runCodeDevMasterFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับ Dev Master','error'); return; }
    var profile = codeDevMasterProfile(data.lang, data.code);
    var panel = card.querySelector('.aira-code-dev-master-engine');
    if(panel){ panel.innerHTML = codeDevMasterEngineHtml(profile); panel.hidden = false; }
    card.setAttribute('data-dev-master-score', String(profile.score || 0));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = devMasterPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่ง Dev Master ไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่ง Dev Master ใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-dev-master"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Dev Master Engine ส่งคำสั่งแล้ว · กำลังยกระดับเป็นระบบที่ครบกว่า code answer ทั่วไป', 'is-next-sent');
    submitPrompt(devMasterPromptFromCard(card, 'กดจากปุ่ม Dev Master'), 'Dev Master code block · ' + (profile.score || 0) + '/100', {mode:'code_dev_master', source:'codeblock-dev-master-engine-7560'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }

  function codeHumanityDimension(key, label, pass, weight, meaning, benefit, fix){
    return {key:key, label:label, pass:!!pass, weight:weight||10, meaning:meaning||'', benefit:benefit||'', fix:fix||''};
  }
  function codeHumanityValueProfile(lang, code, buttonItems, commandItems, capabilityProfile, devMasterProfile){
    lang = normalizeCodeLang(lang || 'text'); code = clean(code || '');
    buttonItems = Array.isArray(buttonItems) ? buttonItems.filter(Boolean) : [];
    commandItems = Array.isArray(commandItems) ? commandItems.filter(Boolean) : [];
    capabilityProfile = capabilityProfile || detectCodeSystemCapabilities(lang, code);
    devMasterProfile = devMasterProfile || codeDevMasterProfile(lang, code);
    var lower = code.toLowerCase();
    var isWp = isPhpLikeCode(lang, code) || /wordpress|wp_|add_action|register_rest_route|shortcode|gutenberg|elementor/i.test(code);
    var hasUi = /<button|addEventListener|onclick|submit|form|input|select|textarea|aria-|role=|className|render|shortcode|admin_menu/i.test(code);
    var hasFeedback = /toast|notice|alert|success|error|loading|status|message|feedback|disabled|aria-live|wp_send_json_(success|error)/i.test(code);
    var hasSafety = /nonce|permission_callback|current_user_can|sanitize_|esc_html|esc_attr|esc_url|wp_kses|try\s*\{|catch\s*\(|validate|rollback|backup/i.test(code);
    var hasPrivacy = /privacy|consent|permission|capability|mask|secret|token|api key|api_key|nonce|personal|delete_option|clear|cleanup/i.test(code);
    var hasAccess = /aria-|role=|focus|keyboard|tabindex|contrast|@media|prefers-reduced-motion|responsive|mobile|viewport|screen-reader/i.test(code);
    var hasDocs = /README|คู่มือ|วิธีใช้|install|usage|docs|help|คำอธิบาย|Settings \| โดย Thinkb4do|ดูรายละเอียด/i.test(code);
    var hasTesting = /test|qc|debug|preview|sandbox|health|check|audit|report|log|console\.error/i.test(code);
    var hasHumanWords = /human|value|benefit|ช่วย|ผู้ใช้|ผู้ใช้งาน|ประโยชน์|มนุษย์|ปลอดภัย|เข้าใจง่าย|ลดความเสี่ยง|feedback/i.test(code);
    var hasDanger = /(eval\s*\(|shell_exec\s*\(|passthru\s*\(|proc_open\s*\(|popen\s*\(|base64_decode\s*\(|document\.write\s*\(|innerHTML\s*=\s*[^;]*(?:request|location|hash))/i.test(code);
    var dims = [];
    dims.push(codeHumanityDimension('real_problem','แก้ปัญหาคนจริง', code.length > 80 && (hasUi || isWp || /function|class|const|def\s+/i.test(code)), 12, 'ระบบต้องมีเป้าหมายใช้งานจริง ไม่ใช่โค้ดวางโชว์', 'ช่วยให้ผู้ใช้ได้ผลลัพธ์ที่จับต้องได้จาก code block', 'ระบุเป้าหมายผู้ใช้และ flow หลัก'));
    dims.push(codeHumanityDimension('button_value','ปุ่มมีคุณค่า', buttonItems.length >= 4 && buttonItems.every(function(it){return !!(it.benefit && it.meaning);}), 11, 'ปุ่มต้องรู้ว่าช่วยใคร กดแล้วเกิดอะไร', 'ลดปุ่มหลอก ปุ่มลอย และลดความสับสนของผู้ใช้', 'เพิ่ม Button Contract ให้ทุกปุ่ม'));
    dims.push(codeHumanityDimension('command_value','คำสั่งมีประโยชน์', commandItems.length >= 3 || hasHumanWords, 11, 'คำสั่งในโค้ดต้องมีเหตุผลและผลลัพธ์', 'ผู้ใช้และทีมพัฒนาเข้าใจว่าคำสั่งนั้นช่วยงานอะไร', 'เพิ่ม Command Benefit Map'));
    dims.push(codeHumanityDimension('feedback','มี feedback หลังใช้งาน', hasFeedback, 10, 'ผู้ใช้ต้องรู้ว่ากำลังโหลด สำเร็จ หรือผิดพลาด', 'ลดความกังวลและลดการกดซ้ำที่ทำให้ระบบพัง', 'เพิ่ม loading/success/error/status'));
    dims.push(codeHumanityDimension('safety','ปลอดภัยและลดความเสี่ยง', hasSafety && !hasDanger, 13, 'ระบบต้องป้องกันความเสียหายก่อนช่วยคน', 'ปกป้องเว็บ ข้อมูล และความเชื่อใจของผู้ใช้', 'เพิ่ม nonce/capability/sanitize/escape/rollback และลบคำสั่งเสี่ยง'));
    dims.push(codeHumanityDimension('privacy','เคารพข้อมูลผู้ใช้', hasPrivacy || !/(fetch\(|api|ajax|rest|localStorage|camera|microphone|upload|file|form)/i.test(code), 9, 'เมื่อแตะข้อมูล/API/ไฟล์ ต้องคิดเรื่องสิทธิ์และความลับ', 'ลดการรั่วไหลของข้อมูลและทำให้ระบบน่าเชื่อถือ', 'เพิ่ม consent, masking, permission และ cleanup'));
    dims.push(codeHumanityDimension('accessibility','เข้าถึงง่ายทุกคน', hasAccess, 9, 'คนใช้มือถือ คีย์บอร์ด หรือหน้าจอเล็กต้องใช้งานได้', 'ช่วยให้ระบบไม่ทิ้งผู้ใช้ที่อุปกรณ์/ความถนัดต่างกัน', 'เพิ่ม aria, focus, contrast, responsive'));
    dims.push(codeHumanityDimension('learning','ถ่ายทอดความเข้าใจ', hasDocs || hasHumanWords, 8, 'ระบบควรอธิบายให้คนไม่เข้าใจโค้ดใช้ได้', 'ช่วยให้ผู้ใช้เรียนรู้และส่งต่องานได้', 'เพิ่มคู่มือ ขั้นตอน 1,2,3 และคำอธิบาย'));
    dims.push(codeHumanityDimension('testable','ทดสอบคุณค่าได้', hasTesting || (devMasterProfile.score||0) >= 70, 9, 'สิ่งที่ช่วยคนต้องตรวจได้ ไม่ใช่คำสวย', 'ทำให้รู้ว่าระบบพร้อมใช้หรือยังและควรแก้อะไร', 'เพิ่ม QC/preview/test report'));
    dims.push(codeHumanityDimension('sustainable','ต่อยอดได้ไม่ทำลายของเดิม', (capabilityProfile.score||0) >= 45 || /version|module|class|component|backup|rollback|uninstall|cleanup/i.test(code), 8, 'ระบบควรดูแลต่อได้ ไม่สร้างภาระระยะยาว', 'ช่วยเจ้าของระบบประหยัดเวลาและลดต้นทุนในอนาคต', 'เพิ่ม version/module/rollback/cleanup'));
    var total = dims.reduce(function(s,d){return s+d.weight;},0);
    var pass = dims.reduce(function(s,d){return s+(d.pass?d.weight:0);},0);
    var score = clamp(Math.round((pass/Math.max(1,total))*100),0,100);
    var level = score >= 90 ? 'humanity_ready' : (score >= 74 ? 'high_value_builder' : (score >= 55 ? 'value_needs_guard' : 'needs_human_value_design'));
    var missing = dims.filter(function(d){return !d.pass;}).map(function(d){return d.label;});
    var strengths = dims.filter(function(d){return d.pass;}).map(function(d){return d.label;});
    var beneficiaries = ['ผู้ใช้งานปลายทาง'];
    if(isWp) beneficiaries.push('ผู้ดูแล WordPress');
    if(hasUi) beneficiaries.push('คนที่ใช้งานหน้าเว็บ/มือถือ');
    if(hasTesting) beneficiaries.push('ทีมตรวจบั๊ก/ผู้รับงานต่อ');
    if(hasDocs) beneficiaries.push('คนไม่ถนัดโค้ด');
    return {score:score, level:level, dims:dims, missing:missing, strengths:strengths, beneficiaries:beneficiaries, hasDanger:hasDanger, capability:capabilityProfile, devMaster:devMasterProfile, commands:commandItems, buttons:buttonItems};
  }
  function humanityValueMapText(profile){
    profile = profile || {};
    return [
      'Humanity Value: ' + (profile.score || 0) + '/100 · ' + (profile.level || '-'),
      'Helps: ' + ((profile.beneficiaries || []).join(', ') || 'ผู้ใช้งาน'),
      'Strengths: ' + ((profile.strengths || []).join(', ') || 'none'),
      'Missing: ' + ((profile.missing || []).join(', ') || 'none'),
      'Rule: every system must solve a real human problem, be safe, explain feedback, protect privacy, support accessibility, and be testable.'
    ].join('\n');
  }
  function codeHumanityValueEngineHtml(profile){
    profile = profile || {};
    var dims = Array.isArray(profile.dims) ? profile.dims : [];
    var cards = dims.slice(0, 10).map(function(it){
      return '<li class="'+(it.pass?'is-pass':'is-fix')+'"><b>'+esc(it.label || it.key)+'</b><span>'+esc(it.meaning || '')+'</span><small>'+esc(it.pass ? (it.benefit || 'มีคุณค่าแล้ว') : ('ควรเติม: ' + (it.fix || '-')))+'</small></li>';
    }).join('');
    var helps = (profile.beneficiaries || []).slice(0,5).join(', ') || 'ผู้ใช้งาน';
    var missing = (profile.missing || []).slice(0,6).join(', ') || 'ผ่านแกนคุณค่าหลักแล้ว';
    return '<div class="humanity-value-head"><div><b>'+icon('heart')+'<span>Humanity Value Builder</span></b><small>code block ต้องสร้างระบบที่ช่วยคนจริง: แก้ปัญหา ปลอดภัย เข้าใจง่าย มี feedback เคารพข้อมูล และทดสอบคุณค่าได้</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="humanity-value-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="humanity-value-summary"><p><b>ช่วยใคร:</b> '+esc(helps)+'</p><p><b>ต้องเติม:</b> '+esc(missing)+'</p></div>'+ 
      '<ul class="humanity-value-dims">'+cards+'</ul>'+ 
      '<div class="humanity-value-rules"><span>แก้ปัญหาคนจริง</span><span>ไม่ทำร้าย/ไม่หลอกผู้ใช้</span><span>ปกป้องข้อมูล</span><span>เข้าถึงง่าย</span><span>อธิบายได้</span><span>ทดสอบได้</span></div>';
  }
  function humanityValuePromptFromCard(card, extra){
    var data = codeDataFromCard(card); var lang = normalizeCodeLang(data.lang || 'text'); var code = data.code || '';
    var commandItems = detectCodeCommandBenefits(lang, code);
    var cap = detectCodeSystemCapabilities(lang, code);
    var dev = codeDevMasterProfile(lang, code);
    var buttonItems = [];
    if(card){ all('[data-action^="code-"]', card).forEach(function(btn){ buttonItems.push(codeActionMeaningProfile(btn.getAttribute('data-action') || '', (btn.textContent || '').trim())); }); }
    var profile = codeHumanityValueProfile(lang, code, buttonItems, commandItems, cap, dev);
    var name = headerValueFromPhp(code, 'Plugin Name') || downloadNameForCode(lang, data.index || 0);
    return [
      'ยกระดับ code block นี้ให้เป็นระบบที่สร้างคุณค่าให้มนุษยชาติแบบใช้งานจริง ไม่ใช่แค่โค้ดที่รันได้',
      '',
      '[Humanity Value Goal]',
      '- เป้าหมาย: ผลิตระบบที่ช่วยผู้ใช้และผู้อื่นจริง แก้ปัญหาจริง ลดความเสี่ยง ลดความสับสน ประหยัดเวลา และถ่ายทอดความรู้ได้',
      '- ทำให้ทุกปุ่ม ทุกคำสั่ง ทุกฟังก์ชันมีเหตุผลด้านประโยชน์มนุษย์ ไม่ใช่แค่มีไว้ให้ดูครบ',
      '- ความมุ่งมั่นไร้ขีดจำกัดด้านการช่วยเหลือ แต่ต้องมีขอบเขตความปลอดภัย กฎหมาย ลิขสิทธิ์ ความเป็นส่วนตัว และไม่ทำร้ายผู้ใช้',
      '',
      '[Current Humanity Value Scan]',
      '- Name/File: ' + name,
      '- Language: ' + lang,
      '- Humanity Value score: ' + (profile.score || 0) + '/100 · ' + (profile.level || '-'),
      '- Helps: ' + ((profile.beneficiaries || []).join(', ') || 'ผู้ใช้งาน'),
      '- Missing: ' + ((profile.missing || []).join(', ') || 'none'),
      '- Capability: ' + capabilityMapText(cap),
      '- Command Benefit: ' + commandBenefitMapText(commandItems),
      '- User extra: ' + (trim(extra || '') || 'ให้ระบบทุกอย่างมีคุณค่าต่อผู้ใช้งานและมนุษยชาติ'),
      '',
      '[ต้องทำในคำตอบใหม่]',
      '1) ทวนโจทย์สั้น ๆ ว่าระบบนี้ช่วยใครและช่วยเรื่องอะไรจริง',
      '2) ทำ Humanity Impact Map: ผู้ได้รับประโยชน์ → ปัญหาที่แก้ → ผลลัพธ์ที่ผู้ใช้เห็น → ความเสี่ยงที่ลดลง → วิธีทดสอบคุณค่า',
      '3) ทำ Button Value Contract: ทุกปุ่มต้องระบุ ความหมาย ประโยชน์ feedback error fallback และ test path',
      '4) ทำ Command Benefit Map: คำสั่งสำคัญทุกชุดต้องระบุว่าช่วยมนุษย์/ผู้ใช้งานอย่างไร',
      '5) เพิ่ม Safety/Ethics Guard: privacy, permission, consent, no dark pattern, no fake button, no unsafe command, copyright-safe',
      '6) เพิ่ม Accessibility/Inclusive UX: aria, focus, keyboard/touch, contrast, responsive, ภาษาง่ายสำหรับคนไม่เข้าใจโค้ด',
      '7) เพิ่ม Feedback/Recovery: loading, success, error, retry, rollback, backup, clear status',
      '8) ส่งโค้ดเต็ม runnable ใน fenced code block โดยรักษาของเดิมที่มีประโยชน์ไว้',
      '9) สรุปวิธีติดตั้ง/ใช้งาน/ทดสอบแบบ 1,2,3 และบอกว่าใช้งานได้แล้วหรือยัง',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → Humanity Impact Map → Button Value Contract → Command Benefit Map → Safety/Ethics Guard → โค้ดเต็ม → วิธีทดสอบคุณค่าจริง → สถานะ Humanity Value % + Success %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }
  function runCodeHumanityValueFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับ Humanity Value','error'); return; }
    var buttonItems = [];
    all('[data-action^="code-"]', card).forEach(function(btn){ buttonItems.push(codeActionMeaningProfile(btn.getAttribute('data-action') || '', (btn.textContent || '').trim())); });
    var profile = codeHumanityValueProfile(data.lang, data.code, buttonItems, detectCodeCommandBenefits(data.lang, data.code), detectCodeSystemCapabilities(data.lang, data.code), codeDevMasterProfile(data.lang, data.code));
    var panel = card.querySelector('.aira-code-humanity-value-engine');
    if(panel){ panel.innerHTML = codeHumanityValueEngineHtml(profile); panel.hidden = false; }
    card.setAttribute('data-humanity-value-score', String(profile.score || 0));
    card.setAttribute('data-humanity-value-map', humanityValueMapText(profile));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = humanityValuePromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่ง Humanity Value ไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่ง Humanity Value ใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-humanity-value"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Humanity Value Builder ส่งคำสั่งแล้ว · กำลังยกระดับระบบให้ช่วยผู้ใช้และผู้อื่นจริง', 'is-next-sent');
    submitPrompt(humanityValuePromptFromCard(card, 'กดจากปุ่ม Humanity Value'), 'Humanity Value code block · ' + (profile.score || 0) + '/100', {mode:'code_humanity_value', source:'codeblock-humanity-value-builder-7562'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }

  function codeHumanityMissionProfile(lang, code, humanityProfile, capabilityProfile, devMasterProfile){
    lang = normalizeCodeLang(lang || 'text'); code = clean(code || '');
    humanityProfile = humanityProfile || codeHumanityValueProfile(lang, code, [], detectCodeCommandBenefits(lang, code), capabilityProfile, devMasterProfile);
    capabilityProfile = capabilityProfile || detectCodeSystemCapabilities(lang, code);
    devMasterProfile = devMasterProfile || codeDevMasterProfile(lang, code);
    var lower = code.toLowerCase();
    var hasRealTarget = /ผู้ใช้|ผู้ใช้งาน|help|benefit|human|value|problem|pain|need|goal|ช่วย|ประโยชน์|ปัญหา|เป้าหมาย/i.test(code);
    var hasMinimalSystem = /function|class|const|let|add_action|register_rest_route|add_shortcode|<button|<form|fetch|api|endpoint|render|preview|dashboard|shortcode/i.test(code);
    var hasImpactMetric = /score|percent|%|metric|measure|impact|report|qc|test|audit|quality|ประเมิน|คะแนน|วัดผล|รายงาน/i.test(code);
    var hasSafetyGate = /nonce|capability|permission|sanitize|escape|privacy|consent|safe|rollback|backup|validate|current_user_can|ไม่เก็บ|สิทธิ์/i.test(code) && !/(eval\s*\(|shell_exec\s*\(|passthru\s*\(|proc_open\s*\(|popen\s*\()/i.test(code);
    var hasAccessibleUse = /aria-|role=|focus|keyboard|touch|responsive|@media|mobile|viewport|contrast|เข้าใจง่าย|คู่มือ|วิธีใช้/i.test(code);
    var hasFeedback = /toast|notice|status|loading|success|error|feedback|aria-live|wp_send_json_(success|error)|แจ้ง|สำเร็จ|ผิดพลาด/i.test(code);
    var hasLoop = /preview|debug|test|qc|success|package|upgrade|rollback|retry|fix|ตรวจ|ทดสอบ|แก้/i.test(code);
    var hasHandoff = /README|docs|manual|install|usage|version|changelog|คู่มือ|ติดตั้ง|ใช้งาน|handoff/i.test(code);
    var dims = [];
    function dim(key,label,pass,weight,why,fix){ dims.push({key:key,label:label,pass:!!pass,weight:weight||10,why:why||'',fix:fix||''}); }
    dim('real-problem','Real Problem', hasRealTarget, 14, 'ระบบต้องเริ่มจากปัญหาคนจริง ไม่ใช่ฟีเจอร์ลอย', 'ระบุว่าช่วยใคร ปัญหาอะไร และผลลัพธ์ที่เขาจะเห็น');
    dim('minimal-useful-system','Minimal Useful System', hasMinimalSystem, 14, 'ต้องมีแกนระบบที่ใช้ได้จริงอย่างน้อยหนึ่ง flow', 'เพิ่ม UI/action/backend/mock/preview ที่ผู้ใช้ทดลองได้');
    dim('impact-metric','Impact Metric', hasImpactMetric, 12, 'สิ่งที่ช่วยมนุษย์ต้องวัดได้ ไม่ใช่คำสวย', 'เพิ่มตัวชี้วัด เช่น ลดเวลา ลด error เพิ่มความเข้าใจ Success % หรือ QC %');
    dim('safety-gate','Safety Gate', hasSafetyGate, 15, 'ช่วยคนจริงต้องไม่ทำร้ายข้อมูล เว็บ หรือสิทธิ์ผู้ใช้', 'เพิ่ม permission, nonce, sanitize, escape, privacy, rollback และตัดคำสั่งเสี่ยง');
    dim('inclusive-access','Inclusive Access', hasAccessibleUse, 10, 'คนทั่วไป มือถือ คีย์บอร์ด และผู้ใช้ที่ไม่ถนัดโค้ดต้องใช้งานได้', 'เพิ่ม responsive, aria, focus, contrast และคู่มือภาษาง่าย');
    dim('visible-feedback','Visible Feedback', hasFeedback, 10, 'ผู้ใช้ต้องรู้ว่ากดแล้วเกิดอะไร สำเร็จหรือผิดพลาด', 'เพิ่ม loading/success/error/retry/status render');
    dim('build-test-loop','Build/Test Loop', hasLoop, 11, 'ระบบต้องทดลอง แก้ และตรวจได้ก่อนส่งมอบ', 'เพิ่ม Preview/QC/Debug/Test/Retry/Package flow');
    dim('handoff-learning','Handoff/Learning', hasHandoff, 8, 'ระบบควรส่งต่อและสอนผู้ใช้ได้ ไม่ทำให้พึ่งคนเดียวตลอด', 'เพิ่ม README/วิธีใช้/วิธีทดสอบ/เวอร์ชัน/ขั้นตอน 1,2,3');
    dim('humanity-value','Humanity Value Core', (humanityProfile.score||0) >= 55, 12, 'ต้องผ่านแกนคุณค่ามนุษย์เดิม', 'เพิ่ม Humanity Impact Map และ Button/Command Benefit Map');
    var total = dims.reduce(function(a,d){return a+(d.weight||0);},0);
    var got = dims.reduce(function(a,d){return a+(d.pass?(d.weight||0):0);},0);
    var score = clamp(Math.round((got/Math.max(1,total))*100),0,100);
    score = clamp(Math.round(score * 0.55 + (humanityProfile.score||0) * 0.25 + (capabilityProfile.score||0) * 0.12 + (devMasterProfile.score||0) * 0.08),0,100);
    var missing = dims.filter(function(d){return !d.pass;}).map(function(d){return d.label;});
    var pass = dims.filter(function(d){return d.pass;}).map(function(d){return d.label;});
    var level = score >= 90 ? 'mission_ready' : (score >= 74 ? 'impact_builder_ready' : (score >= 55 ? 'mission_needs_value_loop' : 'needs_real_problem_design'));
    var beneficiaries = (humanityProfile.beneficiaries || []).slice(0,5);
    if(!beneficiaries.length) beneficiaries = ['ผู้ใช้งานจริง','เจ้าของระบบ','ทีมพัฒนา'];
    return {score:score, level:level, dims:dims, missing:missing, pass:pass, beneficiaries:beneficiaries, humanity:humanityProfile, capability:capabilityProfile, devMaster:devMasterProfile};
  }
  function codeHumanityMissionHtml(profile){
    profile = profile || {};
    var dims = Array.isArray(profile.dims) ? profile.dims : [];
    var steps = [
      ['Problem','เข้าใจปัญหาคนจริง'],
      ['Value','รู้ว่าใครได้ประโยชน์'],
      ['Build','สร้างระบบที่ใช้ได้'],
      ['Test','ตรวจความปลอดภัย/ผลลัพธ์'],
      ['Impact','วัดว่าช่วยได้จริงไหม']
    ].map(function(step){
      var found = dims.some(function(d){ return d.pass && ((step[0] === 'Problem' && d.key === 'real-problem') || (step[0] === 'Value' && d.key === 'humanity-value') || (step[0] === 'Build' && d.key === 'minimal-useful-system') || (step[0] === 'Test' && (d.key === 'build-test-loop' || d.key === 'safety-gate')) || (step[0] === 'Impact' && d.key === 'impact-metric')); });
      return '<span class="'+(found?'is-pass':'is-wait')+'"><b>'+esc(step[0])+'</b><small>'+esc(step[1])+'</small></span>';
    }).join('');
    var missing = (profile.missing || []).slice(0,5).join(', ') || 'ผ่านแกนภารกิจหลักแล้ว';
    var helps = (profile.beneficiaries || []).slice(0,5).join(', ') || 'ผู้ใช้งานจริง';
    var list = dims.slice(0,9).map(function(d){ return '<li class="'+(d.pass?'is-pass':'is-fix')+'"><b>'+esc(d.label)+'</b><span>'+esc(d.why || '')+'</span><small>'+esc(d.pass ? 'ผ่าน' : ('ควรเติม: ' + (d.fix || '-')))+'</small></li>'; }).join('');
    return '<div class="humanity-mission-head"><div><b>'+icon('heart')+'<span>Thinkb4do Humanity System Builder</span></b><small>เปลี่ยน code block ให้เป็นภารกิจสร้างระบบที่ช่วยคนจริงทีละปัญหา แล้ววัดผลได้จริง</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="humanity-mission-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="humanity-mission-flow">'+steps+'</div>'+ 
      '<div class="humanity-mission-summary"><p><b>ช่วยใคร:</b> '+esc(helps)+'</p><p><b>ต้องเติม:</b> '+esc(missing)+'</p></div>'+ 
      '<ul class="humanity-mission-dims">'+list+'</ul>'+ 
      '<div class="humanity-mission-rules"><span>เริ่มจากปัญหาจริง</span><span>สร้างขั้นต่ำที่ใช้ได้</span><span>ปลอดภัยก่อน</span><span>ลองก่อนส่งมอบ</span><span>วัดผลกระทบ</span></div>';
  }
  function humanityMissionPromptFromCard(card, extra){
    var data = codeDataFromCard(card); var lang = normalizeCodeLang(data.lang || 'text'); var code = data.code || '';
    var commands = detectCodeCommandBenefits(lang, code);
    var cap = detectCodeSystemCapabilities(lang, code);
    var dev = codeDevMasterProfile(lang, code);
    var buttonItems = [];
    if(card){ all('[data-action^="code-"]', card).forEach(function(btn){ buttonItems.push(codeActionMeaningProfile(btn.getAttribute('data-action') || '', (btn.textContent || '').trim())); }); }
    var humanity = codeHumanityValueProfile(lang, code, buttonItems, commands, cap, dev);
    var mission = codeHumanityMissionProfile(lang, code, humanity, cap, dev);
    var name = headerValueFromPhp(code, 'Plugin Name') || downloadNameForCode(lang, data.index || 0);
    return [
      'พัฒนา code block นี้ต่อให้เป็น Thinkb4do Humanity System Builder: ระบบที่ช่วยมนุษยชาติจริงแบบเริ่มจากปัญหาคนจริงทีละข้อ แล้วสร้าง/ทดสอบ/วัดผลได้',
      '',
      '[Mission Scan]',
      '- Name/File: ' + name,
      '- Language: ' + lang,
      '- Mission score: ' + (mission.score || 0) + '/100 · ' + (mission.level || '-'),
      '- Helps: ' + ((mission.beneficiaries || []).join(', ') || 'ผู้ใช้งานจริง'),
      '- Missing: ' + ((mission.missing || []).join(', ') || 'none'),
      '- Humanity Value: ' + (humanity.score || 0) + '/100',
      '- Capability: ' + capabilityMapText(cap),
      '- Command Benefit: ' + commandBenefitMapText(commands),
      '- User extra: ' + (trim(extra || '') || 'สร้างระบบที่มีคุณค่าจริงต่อผู้ใช้และผู้อื่น'),
      '',
      '[กติกาพัฒนา]',
      '1) เริ่มจาก Real Problem: ระบุช่วยใคร ปัญหาอะไร ผลลัพธ์จริงคืออะไร',
      '2) สร้าง Minimal Useful System: ฟีเจอร์ขั้นต่ำที่กด/ลอง/ใช้ได้จริง ไม่ใช่แค่แผน',
      '3) ทุกปุ่มต้องมี Button Value Contract: ความหมาย → ประโยชน์ → feedback → error fallback → test path',
      '4) ทุกคำสั่งสำคัญต้องมี Command Benefit Map: คำสั่งนี้ช่วยผู้ใช้อย่างไร ลดความเสี่ยงอะไร',
      '5) ต้องมี Safety Gate: permission, privacy, consent เมื่อเกี่ยวข้อง, sanitize/escape, no fake button, no unsafe command, copyright-safe',
      '6) ต้องมี Inclusive UX: responsive, aria, focus, touch/keyboard, ภาษาง่ายสำหรับคนไม่เข้าใจโค้ด',
      '7) ต้องมี Impact Metric: วัดได้ เช่น ลดเวลา ลด error เพิ่มความเข้าใจ Success/QC/Impact %',
      '8) ถ้าแก้โค้ดได้ ให้ส่งโค้ดเต็ม runnable ใน fenced code block โดยรักษาของเดิมที่มีประโยชน์',
      '9) สรุปวิธีติดตั้ง/ใช้งาน/ทดสอบแบบ 1,2,3 และบอกว่าใช้งานได้แล้วหรือยัง',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → Real Problem → Humanity Impact Map → Minimal Useful System → Button/Command Value → Safety Gate → Impact Metric → โค้ดเต็ม → วิธีทดสอบ → สถานะ Success/Impact %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }
  function runCodeHumanityMissionFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับ Humanity Mission','error'); return; }
    var commands = detectCodeCommandBenefits(data.lang, data.code);
    var cap = detectCodeSystemCapabilities(data.lang, data.code);
    var dev = codeDevMasterProfile(data.lang, data.code);
    var humanity = codeHumanityValueProfile(data.lang, data.code, [], commands, cap, dev);
    var profile = codeHumanityMissionProfile(data.lang, data.code, humanity, cap, dev);
    var panel = card.querySelector('.aira-code-humanity-mission');
    if(panel){ panel.innerHTML = codeHumanityMissionHtml(profile); panel.hidden = false; }
    card.setAttribute('data-humanity-mission-score', String(profile.score || 0));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = humanityMissionPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่งภารกิจช่วยโลกไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่ง Humanity Mission ใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-humanity-mission"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Humanity Mission ส่งคำสั่งแล้ว · กำลังพัฒนาให้เป็นระบบที่ช่วยคนจริงและวัดผลได้', 'is-next-sent');
    submitPrompt(humanityMissionPromptFromCard(card, 'กดจากปุ่มภารกิจช่วยโลก'), 'Humanity Mission code block · ' + (profile.score || 0) + '/100', {mode:'code_humanity_mission', source:'thinkb4do-humanity-system-builder-7563'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }

  function codeHumanValueGuardHtml(items, commandItems){
    items = Array.isArray(items) ? items.filter(Boolean) : [];
    commandItems = Array.isArray(commandItems) ? commandItems.filter(Boolean) : [];
    var shown = items.slice(0, 8).map(function(it){
      return '<li><b>'+esc(it.label || it.action || 'Action')+'</b><span><strong>ความหมาย:</strong> '+esc(it.meaning || 'ปุ่มนี้ต้องมีหน้าที่ชัดเจน')+'</span><span><strong>ประโยชน์:</strong> '+esc(it.benefit || it.value || '')+'</span><small>ช่วย: '+esc(it.helps || 'ผู้ใช้งาน')+' · Feedback: '+esc(it.feedback || 'ต้องเห็นผลหลังคลิก')+' · Test: '+esc(it.test || 'ต้องกดใช้งานได้จริง')+'</small></li>';
    }).join('');
    if(!shown){ shown = '<li><b>Action</b><span><strong>ความหมาย:</strong> ทุกคำสั่งต้องมีหน้าที่ชัดเจน</span><span><strong>ประโยชน์:</strong> ช่วยผู้ใช้ทำงานต่อได้จริง มีผลลัพธ์และ feedback ชัดเจน</span><small>Test: ต้องมี handler และ fallback</small></li>'; }
    var commands = commandItems.slice(0, 12).map(function(it){
      return '<li data-command-benefit-key="'+esc(it.key || '')+'"><b>'+esc(it.label || it.key || 'Command')+'</b><span><strong>ความหมาย:</strong> '+esc(it.meaning || 'คำสั่งนี้ต้องมีหน้าที่ในระบบ')+'</span><span><strong>ประโยชน์:</strong> '+esc(it.benefit || it.value || '')+'</span><small>ช่วย: '+esc(it.helps || 'ผู้ใช้งาน')+' · Feedback: '+esc(it.feedback || 'ต้องมีผลลัพธ์')+' · Test: '+esc(it.test || 'ต้องทดสอบได้')+'</small></li>';
    }).join('');
    if(!commands){ commands = '<li><b>Command</b><span><strong>ความหมาย:</strong> ทุกคำสั่งต้องตอบได้ว่าทำอะไร</span><span><strong>ประโยชน์:</strong> ช่วยงานจริง ลดความเสี่ยง มี feedback และทดสอบได้</span><small>Test: ตรวจ handler/error/responsive/accessibility</small></li>'; }
    return '<div class="value-guard-head"><div><b>'+icon('heart')+'<span>Meaning + Human Value Engine</span></b><small>code block จะพยายามอ่านความหมายของทุกปุ่มและทุกคำสั่ง เพื่อให้สิ่งที่ผลิตมีคุณค่าต่อผู้ใช้จริง</small></div><span>VALUE</span></div>'+ 
      '<p class="value-guard-summary">หลักคิด: “ปุ่มนี้ช่วยใคร?” “คำสั่งนี้ทำไปเพื่ออะไร?” “ผู้ใช้เห็นประโยชน์อะไร?” “ลดความเสี่ยงอะไร?” “ทดสอบได้อย่างไร?”</p>'+ 
      '<b class="value-section-title">ความหมายและประโยชน์ของปุ่ม</b><ul class="value-guard-list is-meaning-map">'+shown+'</ul>'+ 
      '<b class="value-section-title">ความหมายและประโยชน์ของคำสั่งในโค้ด</b><ul class="command-benefit-list is-meaning-map">'+commands+'</ul>'+ 
      '<div class="value-guard-rules"><span>ทุกปุ่มมีเป้าหมาย</span><span>ทุกคำสั่งมีเหตุผล</span><span>ผู้ใช้เห็น feedback</span><span>ลดความเสี่ยงก่อนติดตั้ง</span><span>ทดสอบได้จริง</span><span>ผลิตเพื่อสร้างคุณค่า</span></div>';
  }

  function codeQualityProfile(lang, code, audit, capabilityProfile, commandItems){
    lang = normalizeCodeLang(lang || 'text'); code = clean(code || '');
    audit = audit || codeStaticAudit({lang:lang, code:code, index:0});
    capabilityProfile = capabilityProfile || detectCodeSystemCapabilities(lang, code);
    commandItems = Array.isArray(commandItems) ? commandItems : detectCodeCommandBenefits(lang, code);
    var isWp = isPhpLikeCode(lang, code) || /add_action|register_rest_route|wp_ajax_|shortcode|Plugin\s+Name/i.test(code);
    var hasHeader = !isWp || /Plugin\s+Name\s*:|Description\s*:|Version\s*:/i.test(code);
    var hasStructure = /(class\s+[A-Z][A-Za-z0-9_]+|function\s+[a-zA-Z0-9_]+|const\s+[a-zA-Z0-9_]+|module|namespace|component|render|init)/i.test(code);
    var hasSeparation = (code.match(/function\s+[a-zA-Z0-9_]+|class\s+[A-Z][A-Za-z0-9_]+|const\s+[a-zA-Z0-9_]+\s*=\s*(?:function|\()/g) || []).length >= 2 || /assets\/|includes\/|admin\/|public\//i.test(code);
    var hasDataFlow = /state|payload|schema|validate|FormData|JSON\.parse|JSON\.stringify|wp_send_json|return\s+\{|fetch\(/i.test(code);
    var hasValidation = /validate|required|is_array|is_string|Number\.|parseInt|sanitize_|filter_var|wp_verify_nonce|check_ajax_referer/i.test(code);
    var hasSecurity = !isWp || (/nonce|check_ajax_referer|wp_verify_nonce|current_user_can|permission_callback/i.test(code) && /sanitize_|esc_html|esc_attr|esc_url|wp_kses|textContent|createTextNode/i.test(code));
    var hasEscaping = /esc_html|esc_attr|esc_url|wp_kses|htmlspecialchars|textContent|createTextNode|innerText/i.test(code);
    var hasError = /try\s*\{|catch\s*\(|\.catch\s*\(|finally\s*\(|wp_send_json_error|throw new Error|console\.error/i.test(code);
    var hasFeedback = /toast|notice|status|loading|success|error|disabled|aria-busy|spinner|progress|feedback/i.test(code);
    var hasA11y = /aria-|role=|label|focus|tabindex|keydown|keyup|screen-reader|sr-only/i.test(code);
    var hasResponsive = /@media|clamp\(|minmax\(|grid-template|100dvh|visualViewport|responsive|viewport/i.test(code);
    var hasTests = /test|QC|quality|lint|assert|debug|preview|sandbox|health|report/i.test(code);
    var hasDocs = /\/\*\*|\/\/|README|คู่มือ|วิธีใช้|step|ขั้นตอน|docs|description/i.test(code);
    var hasNaming = /[a-z]+[A-Z][a-zA-Z]+|[a-z]+_[a-z]+|Aira|Thinkb4do|Handler|Controller|Service|Repository|Store|Panel/i.test(code);
    var hasButtons = /<button\b|data-action=|addEventListener\s*\(\s*['"]click|onclick\s*=/i.test(code);
    var hasButtonContract = !hasButtons || (/data-action=|aria-label|disabled|loading|status|toast|addEventListener\s*\(\s*['"]click/i.test(code) && /feedback|success|error|try|catch|status|toast/i.test(code));
    var hasDbCare = !/(insert|update|delete|wpdb|localStorage|sessionStorage|fetch\(|api|ajax|rest|upload|file)/i.test(code) || /permission|nonce|backup|rollback|cleanup|sanitize|validate/i.test(code);
    var dangerous = /eval\s*\(|new Function\s*\(|document\.write\s*\(|innerHTML\s*=\s*[^;]*(?:user|input|payload|response|data)|DROP\s+TABLE|chmod\s*\(|shell_exec|exec\s*\(|passthru/i.test(code);
    var dims = [];
    function dim(key,label,pass,weight,fix){ dims.push({key:key,label:label,pass:!!pass,weight:weight,fix:fix}); }
    dim('structure','โครงสร้างอ่านง่าย/แยกหน้าที่', hasHeader && hasStructure && hasSeparation, 13, 'เพิ่มโครงสร้างไฟล์/คลาส/ฟังก์ชัน และระบุหน้าที่แต่ละส่วนให้ชัด');
    dim('data-flow','Data Flow/State ชัดเจน', hasDataFlow && hasValidation, 10, 'ระบุ input → validate → process → output และ schema ของข้อมูล');
    dim('security','Security / Permission / Escape', hasSecurity && hasEscaping && !dangerous, 15, 'เพิ่ม nonce/capability/permission_callback/sanitize/escape และลบคำสั่งเสี่ยง');
    dim('error','Error Guard + Fallback', hasError, 10, 'เพิ่ม try/catch, .catch, wp_send_json_error, fallback และข้อความ error ที่ผู้ใช้เข้าใจ');
    dim('button','Button Contract ทำงานจริง', hasButtonContract, 10, 'ทุกปุ่มต้องมี data-action/handler/loading/success/error/aria-label');
    dim('ux','Responsive + Accessibility', hasResponsive && hasA11y, 10, 'เพิ่ม responsive, keyboard/focus, aria, contrast และ touch target');
    dim('maintain','Maintainability', hasNaming && hasDocs && hasSeparation, 10, 'ตั้งชื่อสื่อความหมาย ใส่ comment เฉพาะจุดสำคัญ และลดโค้ดซ้ำ');
    dim('test','QC/Test/Preview', hasTests, 9, 'เพิ่ม test path, preview, debug report, health check หรือ QC checklist');
    dim('value','Command Benefit Map', (commandItems||[]).length >= 3 || /benefit|value|ช่วย|ประโยชน์|human/i.test(code), 8, 'อธิบายว่าคำสั่งสำคัญช่วยผู้ใช้อย่างไรและทดสอบอย่างไร');
    dim('safe-change','อัปเกรดไม่ทำลายของเดิม', hasDbCare && /version|backup|rollback|migration|compat|deprecat|cleanup|uninstall|preserve|ของเดิม/i.test(code), 5, 'เพิ่ม versioning, backup/rollback, migration และรายการของเดิมที่ต้องคงไว้');
    var total = dims.reduce(function(s,d){return s+d.weight;},0) || 1;
    var pass = dims.reduce(function(s,d){return s+(d.pass?d.weight:0);},0);
    var auditBonus = Math.min(8, Math.round(((audit && audit.score) || 0) / 14));
    var capabilityBonus = Math.min(7, Math.round(((capabilityProfile && capabilityProfile.score) || 0) / 15));
    var score = clamp(Math.round((pass/total)*85) + auditBonus + capabilityBonus - (dangerous ? 18 : 0), 0, 100);
    var missing = dims.filter(function(d){return !d.pass;}).map(function(d){return d.fix;}).slice(0,7);
    var strengths = dims.filter(function(d){return d.pass;}).map(function(d){return d.label;}).slice(0,7);
    return {score:score, status:score>=86?'production_quality_ready':(score>=68?'quality_good_refine_next':'needs_quality_upgrade'), dims:dims, missing:missing, strengths:strengths, flags:{structure:hasStructure&&hasSeparation, security:hasSecurity&&hasEscaping&&!dangerous, error:hasError, button:hasButtonContract, ux:hasResponsive&&hasA11y, test:hasTests, maintain:hasNaming&&hasDocs, data:hasDataFlow&&hasValidation}, dangerous:dangerous};
  }
  function codeQualityEngineHtml(profile){
    profile = profile || {score:0,dims:[],missing:[],strengths:[],flags:{}};
    var flags = profile.flags || {};
    function chip(ok,label){ return '<span class="'+(ok?'is-pass':'is-fix')+'"><b>'+esc(label)+'</b><small>'+(ok?'ผ่าน':'ต้องเติม')+'</small></span>'; }
    var dims = (profile.dims || []).map(function(d){ return '<li class="'+(d.pass?'is-pass':'is-fix')+'"><b>'+esc(d.label)+'</b><span>'+esc(d.pass?'พร้อม':'ควรปรับ')+' · '+esc(d.weight || 0)+'%</span></li>'; }).join('');
    var missing = (profile.missing && profile.missing.length ? profile.missing : ['พร้อมระดับพื้นฐาน ให้ทดสอบจริงก่อนส่งมอบ']).map(function(x){return '<li>'+esc(x)+'</li>';}).join('');
    var strengths = (profile.strengths && profile.strengths.length ? profile.strengths : ['ยังควรยกระดับคุณภาพโค้ดในรอบถัดไป']).map(function(x){return '<li>'+esc(x)+'</li>';}).join('');
    return '<div class="quality-engine-head"><div><b>'+icon('docs')+'<span>Codeblock Code Quality Engine</span></b><small>ยกระดับคุณภาพการเขียนโค้ด: โครงสร้าง ชื่อฟังก์ชัน security error guard ปุ่มจริง docs test และการส่งมอบที่ดูแลต่อได้</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="quality-engine-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="quality-engine-flags">'+chip(flags.structure,'Structure')+chip(flags.security,'Security')+chip(flags.error,'Error')+chip(flags.button,'Buttons')+chip(flags.ux,'UX/A11y')+chip(flags.test,'QC/Test')+chip(flags.maintain,'Maintain')+chip(flags.data,'Data Flow')+'</div>'+ 
      '<ul class="quality-engine-dims">'+dims+'</ul>'+ 
      '<div class="quality-engine-grid"><section><b>จุดแข็ง</b><ul>'+strengths+'</ul></section><section><b>ควรยกระดับ</b><ul>'+missing+'</ul></section></div>'+ 
      '<small class="quality-engine-note">ปุ่ม “คุณภาพโค้ด” จะสั่งให้ AiRA ปรับโค้ดให้เป็น production-ready มากขึ้น โดยคงของเดิมที่มีประโยชน์ไว้และส่งโค้ดเต็มเมื่อแก้ได้</small>';
  }
  function qualityPromptFromCard(card, reason){
    var data = codeDataFromCard(card); var audit = codeStaticAudit(data); var cap = detectCodeSystemCapabilities(data.lang, data.code); var commands = detectCodeCommandBenefits(data.lang, data.code); var quality = codeQualityProfile(data.lang, data.code, audit, cap, commands);
    return [
      'ยกระดับคุณภาพการเขียนโค้ดของ code block นี้ให้เป็นระบบที่ดีขึ้น ใช้งานจริง ดูแลต่อได้ และมีประโยชน์ต่อผู้ใช้',
      '',
      'เหตุผล: ' + (reason || 'กดจากปุ่มคุณภาพโค้ด'),
      'Code Quality ปัจจุบัน: ' + (quality.score || 0) + '/100 · ' + (quality.status || '-'),
      'QC ปัจจุบัน: ' + (audit.score || 0) + '/100 · ' + (audit.status || '-'),
      'Capability: ' + ((cap && cap.score) || 0) + '/100',
      '',
      'ข้อกำหนดบังคับ:',
      '1) ของเดิมยังอยู่: คงฟังก์ชัน ปุ่ม hook shortcode REST/API และ flow ที่มีประโยชน์ ห้ามลบโดยไม่บอกเหตุผล',
      '2) ปรับคุณภาพโค้ดจริง: แยกหน้าที่เป็น module/function/class ให้ชัด ตั้งชื่ออ่านเข้าใจ ลดโค้ดซ้ำ และลด side effect',
      '3) ปุ่มทุกปุ่มต้องมี Button Contract: label, purpose, data-action/handler, loading, success/error feedback, aria-label และวิธีทดสอบ',
      '4) Security/Privacy: เพิ่ม nonce, capability, permission_callback, sanitize, escape, validation, consent/masking เมื่อเกี่ยวกับข้อมูลผู้ใช้',
      '5) Reliability: เพิ่ม try/catch, .catch, timeout/retry, fallback, no raw HTML error, log ที่ปลอดภัย และสถานะที่ผู้ใช้เข้าใจ',
      '6) UX/A11y/Responsive: รองรับมือถือ แป้นพิมพ์ focus contrast touch target screen reader และไม่ให้ composer/header ทับกันถ้าเกี่ยวข้อง',
      '7) Documentation: ใส่คำอธิบายส่วนสำคัญพอดี ไม่รก พร้อมคู่มือใช้งาน 1,2,3 สำหรับคนไม่เข้าใจโค้ด',
      '8) Testing/QC: ใส่ checklist ทดสอบจริง, expected result, error case, rollback/backup เมื่อเป็น WordPress plugin',
      '9) Human Value + Command Benefit Map: อธิบายว่าคำสั่งสำคัญและปุ่มสำคัญช่วยผู้ใช้อย่างไร',
      '10) ส่งคำตอบเป็น: สรุปสิ่งที่ยกระดับ → โครงสร้างไฟล์/หน้าที่ → โค้ดเต็ม runnable ใน fenced code block → วิธีติดตั้ง/ทดสอบ → คะแนนหลังปรับ',
      '',
      'จุดที่ควรยกระดับจากการสแกน:',
      (quality.missing || []).map(function(x){ return '- ' + x; }).join('\n') || '- ตรวจเชิงลึกและยกระดับคุณภาพรวม',
      '',
      'โค้ดต้นทาง:',
      '```' + data.lang,
      shortCodePreviewForPrompt(data.code),
      '```'
    ].join('\n');
  }
  function runCodeQualityBoostFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card); if(!data.code){ toast('ไม่พบโค้ดสำหรับยกระดับคุณภาพ','error'); return; }
    var audit = codeStaticAudit(data); var cap = detectCodeSystemCapabilities(data.lang, data.code); var commands = detectCodeCommandBenefits(data.lang, data.code);
    var quality = codeQualityProfile(data.lang, data.code, audit, cap, commands);
    var panel = card.querySelector('.aira-code-quality-engine');
    if(panel){ panel.innerHTML = codeQualityEngineHtml(quality); panel.hidden = false; }
    card.setAttribute('data-quality-score', String(quality.score || 0));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = qualityPromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่งคุณภาพโค้ดไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่งคุณภาพโค้ดใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-quality-boost"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Code Quality Engine ส่งคำสั่งแล้ว · กำลังยกระดับโค้ดให้สะอาด ปลอดภัย ทดสอบได้ และดูแลต่อได้', 'is-next-sent');
    submitPrompt(qualityPromptFromCard(card, 'กดจากปุ่มคุณภาพโค้ด'), 'Codeblock Code Quality Engine · ' + (quality.score || 0) + '/100', {mode:'code_quality_boost', source:'codeblock-quality-engine-7565'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }

  function codePerformanceProfile(lang, code, audit, capabilityProfile){
    lang = normalizeCodeLang(lang || 'text'); code = clean(code || '');
    audit = audit || codeStaticAudit({lang:lang, code:code, index:0});
    capabilityProfile = capabilityProfile || detectCodeSystemCapabilities(lang, code);
    var len = code.length;
    var buttonCount = (code.match(/<button\b|data-action=|addEventListener\s*\(\s*['"]click|onclick\s*=/gi) || []).length;
    var duplicateActions = 0;
    var actionMap = {};
    (code.match(/data-action\s*=\s*['"][^'"]+['"]/gi) || []).forEach(function(raw){ var k=raw.replace(/^data-action\s*=\s*/i,'').replace(/['"]/g,''); actionMap[k]=(actionMap[k]||0)+1; });
    Object.keys(actionMap).forEach(function(k){ if(actionMap[k] > 1) duplicateActions += actionMap[k]-1; });
    var hasDebounce = /debounce|throttle|requestAnimationFrame|cancelAnimationFrame|AbortController|clearTimeout|clearInterval/i.test(code);
    var hasErrorGuard = /try\s*\{|catch\s*\(|\.catch\s*\(|wp_send_json_error|throw new Error|finally\s*\(/i.test(code);
    var hasLoading = /loading|disabled|aria-busy|spinner|processing|status|toast|notice|feedback/i.test(code);
    var hasCache = /localStorage|sessionStorage|transient|cache|get_option|memo|indexedDB/i.test(code);
    var hasA11y = /aria-|role=|focus-visible|tabindex|keyboard|keydown|keyup/i.test(code);
    var hasResponsive = /@media|clamp\(|minmax\(|viewport|100dvh|visualViewport|responsive/i.test(code);
    var hasRetry = /retry|timeout|AbortController|setTimeout|HTTP\s*status|504|429|rate/i.test(code);
    var hasSanitize = /sanitize_|esc_html|esc_attr|esc_url|wp_kses|textContent|createTextNode/i.test(code);
    var hasTests = /test|QC|quality|debug|health|lint|assert|preview|sandbox/i.test(code);
    var heavyPenalty = len > 90000 ? 16 : (len > 45000 ? 10 : (len > 18000 ? 5 : 0));
    var dupPenalty = Math.min(10, duplicateActions * 2);
    var score = 40;
    score += hasDebounce ? 9 : 0;
    score += hasErrorGuard ? 10 : 0;
    score += hasLoading ? 8 : 0;
    score += hasCache ? 6 : 0;
    score += hasA11y ? 7 : 0;
    score += hasResponsive ? 7 : 0;
    score += hasRetry ? 7 : 0;
    score += hasSanitize ? 8 : 0;
    score += hasTests ? 8 : 0;
    score += Math.min(10, Math.round(((capabilityProfile && capabilityProfile.score) || 0) / 12));
    score += Math.min(6, Math.round(((audit && audit.score) || 0) / 18));
    score = clamp(score - heavyPenalty - dupPenalty, 0, 100);
    var bottlenecks = [];
    if(!hasDebounce) bottlenecks.push('ยังไม่เห็น debounce/throttle/requestAnimationFrame/AbortController สำหรับลดงานซ้ำ');
    if(!hasErrorGuard) bottlenecks.push('ยังต้องเพิ่ม try/catch หรือ error fallback ให้ไม่พังเงียบ');
    if(!hasLoading) bottlenecks.push('ปุ่มควรมี loading/disabled/status หลังคลิก');
    if(!hasA11y) bottlenecks.push('ควรเพิ่ม aria/focus/keyboard support');
    if(!hasResponsive) bottlenecks.push('ควรเพิ่ม responsive/mobile viewport guard');
    if(!hasRetry) bottlenecks.push('ควรมี timeout/retry/HTTP status handling');
    if(duplicateActions) bottlenecks.push('พบ data-action ซ้ำประมาณ '+duplicateActions+' จุด ต้องรวม handler ให้ชัด');
    if(len > 45000) bottlenecks.push('โค้ดยาวมาก ควรแยกไฟล์/lazy render เพื่อลดหน่วง');
    if(!hasTests) bottlenecks.push('ควรเพิ่ม Preview/QC/test path ก่อนใช้งานจริง');
    var wins = [];
    if(hasDebounce) wins.push('มีตัวช่วยลดงานซ้ำ/จังหวะ render');
    if(hasErrorGuard) wins.push('มี error guard');
    if(hasLoading) wins.push('มี feedback ระหว่างทำงาน');
    if(hasResponsive) wins.push('รองรับหลายขนาดหน้าจอ');
    if(hasSanitize) wins.push('มีแนวทางลดความเสี่ยงข้อมูล/HTML');
    return {score:score, status:score>=82?'performance_ready':(score>=62?'usable_optimize_next':'needs_performance_pass'), length:len, buttons:buttonCount, duplicateActions:duplicateActions, bottlenecks:bottlenecks.slice(0,8), wins:wins.slice(0,6), flags:{debounce:hasDebounce,error:hasErrorGuard,loading:hasLoading,cache:hasCache,a11y:hasA11y,responsive:hasResponsive,retry:hasRetry,sanitize:hasSanitize,tests:hasTests}};
  }
  function codePerformanceEngineHtml(profile){
    profile = profile || {score:0,bottlenecks:[],wins:[],flags:{}};
    var flags = profile.flags || {};
    function chip(ok, label){ return '<span class="'+(ok?'is-pass':'is-fix')+'"><b>'+esc(label)+'</b><small>'+(ok?'พร้อม':'ควรเติม')+'</small></span>'; }
    var bottlenecks = (profile.bottlenecks && profile.bottlenecks.length ? profile.bottlenecks : ['พร้อมระดับพื้นฐาน แต่ควรทดสอบบนมือถือและเว็บจริง']).map(function(x){ return '<li>'+esc(x)+'</li>'; }).join('');
    var wins = (profile.wins && profile.wins.length ? profile.wins : ['ยังควรเติมจุดเด่นด้านประสิทธิภาพในการอัปเกรดรอบถัดไป']).map(function(x){ return '<li>'+esc(x)+'</li>'; }).join('');
    return '<div class="performance-engine-head"><div><b>'+icon('performance')+'<span>Codeblock Performance Builder</span></b><small>ตรวจความลื่น ความเร็ว ปุ่มจริง feedback และคอขวด เพื่อให้ระบบที่สร้างใช้งานดีขึ้น</small></div><span>'+esc(profile.score || 0)+'/100</span></div>'+ 
      '<div class="performance-engine-meter"><i style="width:'+esc(profile.score || 0)+'%"></i></div>'+ 
      '<div class="performance-engine-flags">'+chip(flags.debounce,'ลดงานซ้ำ')+chip(flags.error,'Error Guard')+chip(flags.loading,'Feedback')+chip(flags.responsive,'Responsive')+chip(flags.retry,'Retry/Timeout')+chip(flags.tests,'QC/Test')+'</div>'+ 
      '<div class="performance-engine-grid"><section><b>จุดดี</b><ul>'+wins+'</ul></section><section><b>ควรปรับ</b><ul>'+bottlenecks+'</ul></section></div>'+ 
      '<small class="performance-engine-note">ปุ่ม “เพิ่มประสิทธิภาพ” จะส่งคำสั่งให้รักษาของเดิม แล้วปรับโค้ดให้เร็วขึ้น ลื่นขึ้น ปุ่มไม่หลอก มี handler/error/retry/QC ครบขึ้น</small>';
  }
  function performancePromptFromCard(card, reason){
    var data = codeDataFromCard(card); var audit = codeStaticAudit(data); var cap = detectCodeSystemCapabilities(data.lang, data.code); var perf = codePerformanceProfile(data.lang, data.code, audit, cap);
    return [
      'เพิ่มประสิทธิภาพ code block นี้ให้ทำงานได้ดีขึ้น ลื่นขึ้น และใช้ได้จริงมากขึ้น',
      '',
      'เหตุผล: ' + (reason || 'กดจากปุ่มเพิ่มประสิทธิภาพ'),
      'Performance ปัจจุบัน: ' + (perf.score || 0) + '/100 · ' + (perf.status || '-'),
      'QC ปัจจุบัน: ' + (audit.score || 0) + '/100 · ' + (audit.status || '-'),
      'Capability: ' + ((cap && cap.score) || 0) + '/100',
      '',
      'ข้อกำหนดบังคับ:',
      '1) ของเดิมยังอยู่: ห้ามลบฟังก์ชัน/ปุ่มที่มีประโยชน์ ต้องบอกว่าคงอะไรไว้',
      '2) ปรับ performance จริง: ลดโค้ดซ้ำ รวม handler ที่ซ้ำ ใช้ lazy render/debounce/throttle/requestAnimationFrame/AbortController เมื่อเหมาะสม',
      '3) ปุ่มทุกปุ่มต้องมี handler, loading/disabled/status, success/error feedback และ aria-label',
      '4) เพิ่ม timeout/retry/error guard สำหรับ API/fetch/action สำคัญ และไม่แสดง raw HTML error ในแชท',
      '5) ตรวจ mobile keyboard/responsive/focus/contrast/touch target',
      '6) ตรวจ Security: nonce/capability/sanitize/escape/permission_callback เมื่อเกี่ยวกับ WordPress',
      '7) ใส่ Human Value Map และ Command Benefit Map ว่าแต่ละคำสั่งช่วยผู้ใช้อย่างไร',
      '8) ส่งโค้ดเต็ม runnable ใน fenced code block เมื่อแก้ได้ พร้อมวิธีทดสอบ 1,2,3 และคะแนนหลังปรับ',
      '',
      'คอขวดที่ตรวจพบ:',
      (perf.bottlenecks || []).map(function(x){ return '- ' + x; }).join('\n') || '- ยังไม่พบคอขวดชัดเจน ให้ตรวจเชิงลึกเพิ่ม',
      '',
      'โค้ดต้นทาง:',
      '```' + data.lang,
      shortCodePreviewForPrompt(data.code),
      '```'
    ].join('\n');
  }
  function runCodePerformanceBoostFromCard(card){
    if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card); if(!data.code){ toast('ไม่พบโค้ดสำหรับเพิ่มประสิทธิภาพ','error'); return; }
    var panel = card.querySelector('.aira-code-performance-engine');
    var perf = codePerformanceProfile(data.lang, data.code);
    if(panel){ panel.innerHTML = codePerformanceEngineHtml(perf); panel.hidden = false; }
    card.setAttribute('data-performance-score', String(perf.score || 0));
    if(state.sending){
      var input = byId('composerInput');
      if(input){ input.value = performancePromptFromCard(card, 'ระบบกำลังตอบอยู่ จึงใส่คำสั่งเพิ่มประสิทธิภาพไว้ให้ส่งต่อ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); }
      toast('ระบบกำลังตอบอยู่ ใส่คำสั่งเพิ่มประสิทธิภาพใน Composer แล้ว','warn');
      return;
    }
    var btns = all('[data-action="code-performance-boost"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Performance Builder ส่งคำสั่งแล้ว · กำลังปรับให้เร็ว ลื่น ปุ่มจริง และทดสอบได้มากขึ้น', 'is-next-sent');
    submitPrompt(performancePromptFromCard(card, 'กดจากปุ่มเพิ่มประสิทธิภาพ'), 'Codeblock Performance Builder · ' + (perf.score || 0) + '/100', {mode:'code_performance_boost', source:'codeblock-performance-builder-7564'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }

  function createCodeBlock(lang, code, index, allBlocks){
    lang = normalizeCodeLang(lang);
    var contextBlocks = Array.isArray(allBlocks) && allBlocks.length ? allBlocks : [{lang:lang, code:code}];
    var canPreview = blocksCanRenderResult(contextBlocks, lang, code);
    var parts = detectCodeParts(lang, code);
    var installable = isWordPressInstallableCode(lang, code);
    var isWpRelated = isPhpLikeCode(lang, code) || parts.some(function(p){ return ['plugin','widget','block','elementor','ajax','rest','security','theme'].indexOf(p.key) !== -1; });
    var humanValueItems = [];
    var commandBenefitItems = detectCodeCommandBenefits(lang, code);
    var capabilityProfile = detectCodeSystemCapabilities(lang, code);
    var card = d.createElement('figure');
    card.className = 'aira-code-card is-preview-first is-rendered-result is-codeblock-comfort is-human-value-guarded is-humanity-value-builder is-humanity-mission-builder is-performance-builder is-code-quality-builder is-beyond-limit-builder is-system-connection-builder is-studio-nexus-builder is-communication-quality-builder is-deep-cognitive-builder is-command-fusion-builder';
    card.setAttribute('data-code-lang', lang);
    card.setAttribute('data-code-index', String(index || 0));
    card.setAttribute('data-preview-mode', 'code');
    card.__airaPreviewBlocks = contextBlocks;
    card.setAttribute('data-command-benefit-map', commandBenefitMapText(commandBenefitItems));
    card.setAttribute('data-system-capability-map', capabilityMapText(capabilityProfile));
    card.setAttribute('data-capability-score', String(capabilityProfile.score || 0));

    var head = d.createElement('figcaption'); head.className = 'aira-code-head';
    var title = d.createElement('b');
    title.innerHTML = icon('code') + '<span>Code · ' + esc(downloadNameForCode(lang, index || 0)) + '</span>';

    var actions = d.createElement('div'); actions.className = 'aira-code-actions';
    var copy = d.createElement('button'); copy.type='button'; copy.className='message-tool'; copy.setAttribute('data-action','code-copy'); copy.innerHTML=icon('copy') + '<span>คัดลอก</span>'; humanValueItems.push(attachHumanValueToButton(copy, 'code-copy', 'คัดลอก')); actions.appendChild(copy);
    var dl = d.createElement('button'); dl.type='button'; dl.className='message-tool'; dl.setAttribute('data-action','code-download'); dl.innerHTML=icon('download') + '<span>โหลด</span>'; humanValueItems.push(attachHumanValueToButton(dl, 'code-download', 'โหลด')); actions.appendChild(dl);
    var toggle = d.createElement('button'); toggle.type='button'; toggle.className='message-tool'; toggle.setAttribute('data-action','code-preview'); toggle.setAttribute('aria-pressed','false'); toggle.innerHTML=icon('preview') + '<span>Preview</span>'; humanValueItems.push(attachHumanValueToButton(toggle, 'code-preview', 'Preview')); actions.appendChild(toggle);
    head.appendChild(title); head.appendChild(actions);

    var versionLine = d.createElement('div');
    versionLine.className = 'aira-code-version-line';
    versionLine.textContent = codeVersionLineText(lang, code, parts, isWpRelated, index || 0);

    var realityPanel = createCodeRealityPanel(lang, code, index || 0, contextBlocks);

    var stage = d.createElement('div'); stage.className = 'aira-code-stage';
    var pre = d.createElement('pre'); pre.className = 'aira-code-pre';
    var c = d.createElement('code'); c.className = 'language-' + lang; c.textContent = clean(code);
    pre.appendChild(c);

    var inline = d.createElement('div'); inline.className = 'aira-code-inline-preview';
    var note = isPhpLikeCode(lang, code) ? 'PHP/WordPress แสดงเป็น Sandbox ให้ลองใช้ก่อนติดตั้งจริง' : (canPreview ? 'แสดงผลลัพธ์จริงจากโค้ดที่เกี่ยวข้อง' : 'ภาษานี้ไม่มี runtime ใน browser จึงแสดงผลลัพธ์แบบสรุป ไม่โชว์ source ซ้ำ');
    inline.innerHTML = '<div class="inline-preview-head"><div><b>'+esc(downloadNameForCode(lang, index || 0))+'</b><small>'+note+'</small></div></div><div class="inline-preview-body"><iframe title="AiRA rendered code result" sandbox="allow-scripts"></iframe></div>';

    inline.hidden = true;
    stage.appendChild(pre); stage.appendChild(inline);

    var bottomActions = d.createElement('div');
    bottomActions.className = 'aira-code-bottom-actions is-comfort-toolbar';
    function addComfortBottomButton(cls, action, label, iconKey, aria){
      var b = d.createElement('button');
      b.type = 'button';
      b.className = 'aira-code-bottom-btn ' + cls;
      b.setAttribute('data-action', action);
      if(aria){ b.setAttribute('aria-label', aria); }
      b.innerHTML = icon(iconKey) + '<span>' + esc(label) + '</span>';
      humanValueItems.push(attachHumanValueToButton(b, action, label));
      bottomActions.appendChild(b);
      return b;
    }
    // v7.4.5: ลดปุ่มซ้ำใน code block ให้เหลือเฉพาะชุดทำงานหลักใต้โค้ด
    // Copy / Download / Preview อยู่ด้านบนแล้ว จึงไม่สร้างซ้ำด้านล่างอีก
    if(isWpRelated){
      addComfortBottomButton('is-preinstall', 'code-wp-preinstall', 'ลองก่อนติดตั้ง', 'preview', 'เปิด Preview/Sandbox ก่อนติดตั้ง');
    }
    addComfortBottomButton('is-live-debug', 'code-live-debug', isWpRelated ? 'Debug สด' : 'ตรวจโค้ด', 'bug', 'ตรวจบั๊กสดจาก code block นี้');
    addComfortBottomButton('is-performance-boost', 'code-performance-boost', 'เพิ่มประสิทธิภาพ', 'performance', 'ตรวจและปรับ code block ให้เร็วขึ้น ลื่นขึ้น ปุ่มทำงานจริง และมี feedback/QC ชัดเจน');
    addComfortBottomButton('is-code-quality-boost', 'code-quality-boost', 'คุณภาพโค้ด', 'docs', 'ยกระดับคุณภาพการเขียนโค้ดให้สะอาด ปลอดภัย ทดสอบได้ ดูแลต่อได้ และมีประโยชน์ต่อผู้ใช้');
    addComfortBottomButton('is-system-builder', 'code-system-builder', 'สร้างระบบ', 'module', 'เปิด System Builder จาก code block นี้');
    addComfortBottomButton('is-capability-expand', 'code-expand-capabilities', 'ขยายฟังก์ชัน', 'module', 'ขยาย code block ให้สร้างระบบได้หลากหลายฟังก์ชันมากขึ้นโดยคงประโยชน์ผู้ใช้');
    addComfortBottomButton('is-universal-platform', 'code-universal-platform', 'รองรับทุกระบบ', 'globe', 'ยกระดับ code block ให้รองรับเกม ธีมเว็บ โปรแกรมช่วยงาน แอป PWA WordPress API และมาตรฐานทุกอุปกรณ์/บราวเซอร์');
    addComfortBottomButton('is-beyond-limit', 'code-beyond-limit', 'เหนือคาดหมาย', 'performance', 'ปลดล็อกศักยภาพที่ซ่อนอยู่ของ code block ให้สร้างระบบเหนือความคาดหมายอย่างปลอดภัยและมีคุณค่าต่อผู้ใช้');
    addComfortBottomButton('is-system-connection', 'code-system-connection', 'เชื่อมระบบ', 'module', 'เชื่อมความสัมพันธ์ทั้งระบบ ทุกปุ่ม ทุกคำสั่ง API state feedback QC และเอกสาร ให้ทำงานต่อเนื่องกันจริง');
    addComfortBottomButton('is-studio-nexus', 'code-studio-nexus', 'AiRA Nexus', 'brain', 'เชื่อม AiRA Studio ทั้งระบบ ทุกปุ่ม ทุกคำสั่ง ทุก API ทุก state ทุก panel และเพิ่มประสิทธิภาพให้ทำงานสัมพันธ์กันจริง');
    addComfortBottomButton('is-communication-quality', 'code-communication-quality', 'สื่อสารคุณภาพ', 'brain', 'ยกระดับทักษะการสื่อสารของ AiRA Studio ให้ชัด อบอุ่น ตรงประเด็น มี feedback/error/guide และช่วยผู้ใช้ทำงานสำเร็จจริง');
    addComfortBottomButton('is-deep-cognitive', 'code-deep-cognitive-communication', 'สื่อสารลึก', 'brain', 'มองหลายมิติ ตอบตรงเจตนาเชิงงาน และช่วยจัดระบบความคิดของผู้ใช้แบบปลอดภัย ไม่อ้างว่าอ่านใจจริง');
    addComfortBottomButton('is-command-fusion', 'code-command-fusion', 'ประยุกต์อัจฉริยะ', 'brain', 'เอาทุกคำสั่ง สี ปุ่ม API ไฟล์ และ preview มาประยุกต์ร่วมกันเป็นระบบ/ไฟล์ตัวอย่างที่มีคุณค่าและใช้ต่อได้จริง');
    addComfortBottomButton('is-humanity-value', 'code-humanity-value', 'คุณค่ามนุษย์', 'heart', 'ยกระดับ code block ให้ทุกปุ่ม ทุกคำสั่ง และทุกฟังก์ชันสร้างคุณค่าต่อผู้ใช้และผู้อื่นจริง');
    addComfortBottomButton('is-humanity-mission', 'code-humanity-mission', 'ภารกิจช่วยโลก', 'heart', 'แปลง code block ให้เป็นภารกิจสร้างระบบที่ช่วยคนจริงและวัดผลกระทบได้');
    addComfortBottomButton('is-dev-master', 'code-dev-master', 'Dev Master', 'performance', 'ยกระดับ code block ให้ทำหน้าที่พัฒนาระบบเต็มรูปแบบพร้อมคุณค่า QC และโค้ดที่ใช้ได้จริง');
    addComfortBottomButton('is-success-loop', 'code-build-success', 'ทำจนสำเร็จ', 'performance', 'ให้ AiRA ตรวจ วางแผน แก้ และส่ง code block ใหม่จนระบบพร้อมใช้งาน');
    if(isWpRelated){
      addComfortBottomButton('is-package', 'code-package-download', 'โหลดแพ็ก', 'download', 'โหลดแพ็ก ZIP จาก code block นี้');
    }
    addComfortBottomButton('is-upgrade', 'code-wp-upgrade', 'อัปเกรด', 'upgrade', installable ? 'อัปเกรดปลั๊กอิน WordPress จาก code block นี้' : 'อัปเกรดโค้ดนี้ต่อในแชท');

    var fileRow = d.createElement('div'); fileRow.className = 'aira-code-file-row';
    var fileName = downloadNameForCode(lang, index || 0);
    card.setAttribute('data-wp-related', isWpRelated ? '1' : '0');
    card.setAttribute('data-wp-installable', installable ? '1' : '0');
    var fileBtn = d.createElement('button'); fileBtn.type='button'; fileBtn.className='aira-code-file-chip'; fileBtn.setAttribute('data-action','code-file-toggle'); fileBtn.setAttribute('aria-expanded','false');
    fileBtn.innerHTML = icon('file') + '<span class="file-title">'+esc(fileName)+'</span><small>'+esc(codeByteSizeLabel(code))+' · '+esc(normalizeCodeLang(lang))+'</small>';
    humanValueItems.push(attachHumanValueToButton(fileBtn, 'code-file-toggle', 'ไฟล์/ความสัมพันธ์'));
    fileRow.appendChild(fileBtn);
    if(installable){
      var install = d.createElement('button'); install.type='button'; install.className='aira-code-wp-install'; install.setAttribute('data-action','code-wp-install'); install.setAttribute('aria-label','ติดตั้งเป็นปลั๊กอิน WordPress'); install.innerHTML = icon('install') + '<span>ติดตั้ง Plugin</span>'; humanValueItems.push(attachHumanValueToButton(install, 'code-wp-install', 'ติดตั้ง Plugin')); fileRow.appendChild(install);
    }
    if(isWpRelated){
      var installHint = d.createElement('span'); installHint.className='aira-code-wp-hint'; installHint.innerHTML = icon('module') + '<span>WP พร้อม Preview</span>'; fileRow.appendChild(installHint);
    }
    // v7.5.5.5: Success Loop อยู่ใน code block เพื่อพาโค้ดไปจนสำเร็จจริง ไม่ใช่หยุดแค่แผน
    var nextRow = null;
    var successStatus = d.createElement('div');
    successStatus.className = 'aira-code-success-status aira-code-next-status';
    successStatus.textContent = 'Success Loop พร้อม: ตรวจ → สร้าง → Preview/QC → แก้ซ้ำจนพร้อมใช้งาน';
    var qualityProfile = codeQualityProfile(lang, code, null, capabilityProfile, commandBenefitItems);
    card.setAttribute('data-quality-score', String(qualityProfile.score || 0));
    var qualityPanel = d.createElement('div');
    qualityPanel.className = 'aira-code-quality-engine';
    qualityPanel.innerHTML = codeQualityEngineHtml(qualityProfile);
    var performanceProfile = codePerformanceProfile(lang, code, null, capabilityProfile);
    card.setAttribute('data-performance-score', String(performanceProfile.score || 0));
    var performancePanel = d.createElement('div');
    performancePanel.className = 'aira-code-performance-engine';
    performancePanel.innerHTML = codePerformanceEngineHtml(performanceProfile);
    var capabilityPanel = d.createElement('div');
    capabilityPanel.className = 'aira-code-capability-engine';
    capabilityPanel.innerHTML = codeCapabilityEngineHtml(capabilityProfile);
    var universalPlatformProfile = codeUniversalPlatformProfile(lang, code);
    card.setAttribute('data-universal-platform-score', String(universalPlatformProfile.score || 0));
    var universalPlatformPanel = d.createElement('div');
    universalPlatformPanel.className = 'aira-code-universal-platform';
    universalPlatformPanel.innerHTML = codeUniversalPlatformHtml(universalPlatformProfile);
    var beyondLimitProfile = codeBeyondLimitProfile(lang, code);
    card.setAttribute('data-beyond-limit-score', String(beyondLimitProfile.score || 0));
    var beyondLimitPanel = d.createElement('div');
    beyondLimitPanel.className = 'aira-code-beyond-limit';
    beyondLimitPanel.innerHTML = codeBeyondLimitHtml(beyondLimitProfile);
    var systemConnectionProfile = codeSystemConnectionProfile(lang, code);
    card.setAttribute('data-system-connection-score', String(systemConnectionProfile.score || 0));
    var systemConnectionPanel = d.createElement('div');
    systemConnectionPanel.className = 'aira-code-system-connection';
    systemConnectionPanel.innerHTML = codeSystemConnectionHtml(systemConnectionProfile);
    var studioNexusProfile = codeStudioNexusProfile(lang, code);
    card.setAttribute('data-studio-nexus-score', String(studioNexusProfile.score || 0));
    var studioNexusPanel = d.createElement('div');
    studioNexusPanel.className = 'aira-code-studio-nexus';
    studioNexusPanel.innerHTML = codeStudioNexusHtml(studioNexusProfile);
    var communicationQualityProfile = codeCommunicationSkillProfile(lang, code);
    card.setAttribute('data-communication-quality-score', String(communicationQualityProfile.score || 0));
    var communicationQualityPanel = d.createElement('div');
    communicationQualityPanel.className = 'aira-code-communication-engine';
    communicationQualityPanel.innerHTML = codeCommunicationSkillHtml(communicationQualityProfile);
    var deepCognitiveProfile = codeDeepCognitiveCommunicationProfile(lang, code);
    card.setAttribute('data-deep-cognitive-score', String(deepCognitiveProfile.score || 0));
    var deepCognitivePanel = d.createElement('div');
    deepCognitivePanel.className = 'aira-code-deep-cognitive-engine';
    deepCognitivePanel.innerHTML = codeDeepCognitiveCommunicationHtml(deepCognitiveProfile);
    var commandFusionProfile = codeCommandFusionProfile(lang, code);
    card.setAttribute('data-command-fusion-score', String(commandFusionProfile.score || 0));
    var commandFusionPanel = d.createElement('div');
    commandFusionPanel.className = 'aira-code-command-fusion';
    commandFusionPanel.innerHTML = codeCommandFusionHtml(commandFusionProfile);
    var devMasterProfile = codeDevMasterProfile(lang, code);
    card.setAttribute('data-dev-master-score', String(devMasterProfile.score || 0));
    var devMasterPanel = d.createElement('div');
    devMasterPanel.className = 'aira-code-dev-master-engine';
    devMasterPanel.innerHTML = codeDevMasterEngineHtml(devMasterProfile);
    var humanityProfile = codeHumanityValueProfile(lang, code, humanValueItems, commandBenefitItems, capabilityProfile, devMasterProfile);
    card.setAttribute('data-humanity-value-score', String(humanityProfile.score || 0));
    card.setAttribute('data-humanity-value-map', humanityValueMapText(humanityProfile));
    var humanityPanel = d.createElement('div');
    humanityPanel.className = 'aira-code-humanity-value-engine';
    humanityPanel.innerHTML = codeHumanityValueEngineHtml(humanityProfile);
    var missionProfile = codeHumanityMissionProfile(lang, code, humanityProfile, capabilityProfile, devMasterProfile);
    card.setAttribute('data-humanity-mission-score', String(missionProfile.score || 0));
    var missionPanel = d.createElement('div');
    missionPanel.className = 'aira-code-humanity-mission';
    missionPanel.innerHTML = codeHumanityMissionHtml(missionProfile);
    var valueGuard = d.createElement('div');
    valueGuard.className = 'aira-code-value-guard';
    valueGuard.innerHTML = codeHumanValueGuardHtml(humanValueItems, commandBenefitItems);
    var filePanel = d.createElement('div'); filePanel.className = 'aira-code-file-panel'; filePanel.hidden = true;
    var partHtml = parts.map(function(p){ return '<span class="code-part-chip" data-part="'+esc(p.key)+'"><b>'+esc(p.label)+'</b><small>'+esc(p.reason || '')+'</small></span>'; }).join('');
    filePanel.innerHTML = '<div class="code-file-panel-head"><div><b>'+esc(fileName)+'</b><small>'+esc(codeRelationshipSentence(parts, isWpRelated))+'</small></div><button type="button" class="message-tool" data-action="code-file-view">'+icon('code')+'<span>ดูโค้ด</span></button></div><div class="code-part-grid">'+partHtml+'</div>'+(installable ? '<p class="wp-install-note">ไฟล์นี้มี Plugin Header จึงสามารถกดติดตั้งหรืออัปเกรดเป็น WordPress Plugin ได้ ระบบจะถามยืนยันก่อน บันทึก backup ก่อนอัปเกรด และยังไม่เปิดใช้งานอัตโนมัติเพื่อความปลอดภัย</p>' : '<p class="wp-install-note muted">ถ้าต้องการติดตั้งเป็น Plugin ต้องมีไฟล์ PHP หลักที่มี Plugin Name ก่อน ส่วน module/component/theme/widget ควรแพ็กเป็นปลั๊กอินหรือธีมให้ครบก่อนใช้งานจริง</p>');

    var systemPanel = d.createElement('div'); systemPanel.className = 'aira-system-builder-panel'; systemPanel.hidden = true;
    var successPanel = d.createElement('div'); successPanel.className = 'aira-code-success-panel'; successPanel.hidden = true;
    card.appendChild(head); card.appendChild(versionLine); card.appendChild(realityPanel); card.appendChild(stage); card.appendChild(bottomActions); card.appendChild(successStatus); card.appendChild(qualityPanel); card.appendChild(performancePanel); card.appendChild(capabilityPanel); card.appendChild(universalPlatformPanel); card.appendChild(beyondLimitPanel); card.appendChild(systemConnectionPanel); card.appendChild(studioNexusPanel); card.appendChild(communicationQualityPanel); card.appendChild(deepCognitivePanel); card.appendChild(commandFusionPanel); card.appendChild(humanityPanel); card.appendChild(missionPanel); card.appendChild(devMasterPanel); card.appendChild(valueGuard); card.appendChild(fileRow); card.appendChild(filePanel); card.appendChild(systemPanel); card.appendChild(successPanel);
    if(!canPreview){ card.classList.add('is-code-only'); }
    if(isWpRelated){ card.classList.add('is-wp-related'); }
    renderCodeRealityPanel(card);
    return card;
  }
  function imageCardMarker(payload){
    try { return '[[AIRA_IMAGE_CARD]]\n' + JSON.stringify(payload || {}) + '\n[[/AIRA_IMAGE_CARD]]'; }
    catch(e){ return ''; }
  }
  function visibleImageMarker(payload){
    try { return '[[AIRA_VISIBLE_IMAGE_CARD]]\n' + JSON.stringify(payload || {}) + '\n[[/AIRA_VISIBLE_IMAGE_CARD]]'; }
    catch(e){ return ''; }
  }
  function parseImageCardPayload(raw){
    try { var obj = JSON.parse(clean(raw)); return obj && typeof obj === 'object' ? obj : null; }
    catch(e){ return null; }
  }
  function createGeneratedImageCard(payload){
    payload = payload || {};
    var url = payload.url || payload.image_url || payload.previewUrl || payload.preview_url || payload.downloadUrl || payload.download_url || '';
    var downloadUrl = payload.downloadUrl || payload.download_url || payload.previewUrl || payload.preview_url || url;
    var prompt = payload.prompt || payload.name || '';
    var mode = payload.mode || 'image';
    var real = !!(payload.realImage || payload.real_image || /^openai_images_api_real/.test(mode));
    var isAttachment = mode === 'vision_attachment' || payload.kind === 'attached_image';
    var label = payload.label || (isAttachment ? 'ภาพที่แนบแล้ว' : (real ? 'ภาพจริงจาก API' : 'ภาพตัวอย่างสำรอง'));
    var message = payload.message || (isAttachment ? 'ภาพนี้จะแสดงในแชทและส่งเข้า Vision เพื่ออ่านบริบท' : (real ? 'แตะภาพเพื่อเปิดพรีวิวและบันทึกภาพ' : 'ยังไม่ใช่ภาพจริงจาก API · แตะเพื่อดูรายละเอียดและบันทึก preview'));
    var debug = Array.isArray(payload.apiDebug || payload.api_debug) ? (payload.apiDebug || payload.api_debug).join(' | ') : (payload.apiDebug || payload.api_debug || '');
    var card = d.createElement('button');
    card.type = 'button';
    card.className = 'aira-generated-image-card' + (isAttachment ? ' is-attachment-image' : '') + (real ? ' is-real-image' : ' is-fallback-image') + (!url ? ' is-image-missing' : '');
    card.setAttribute('data-action','generated-image-open');
    card.setAttribute('data-real-image', real ? '1' : '0');
    card.setAttribute('aria-label', isAttachment ? 'เปิดภาพแนบเพื่อดูขนาดใหญ่และบันทึก' : (real ? 'เปิดภาพจริงเพื่อบันทึก' : 'เปิดภาพตัวอย่างสำรองเพื่อดูรายละเอียด'));
    card.__airaImageData = {url:url, downloadUrl:downloadUrl, prompt:prompt, mode:mode, message:message, saved:payload.saved || null, kind:payload.kind || '', realImage:real, apiDebug:debug};
    card.innerHTML = '<div class="generated-image-thumb">'+
      (url ? '<img src="'+esc(url)+'" alt="'+esc(prompt ? (label+': '+prompt) : label)+'" loading="lazy" onerror="this.style.display=&quot;none&quot;;this.parentNode.classList.add(&quot;is-image-load-error&quot;);">' : '<span class="generated-image-empty">ยังไม่มี URL ภาพ</span>')+
      '</div><div class="generated-image-copy"><b>'+icon('image')+'<span>'+esc(label)+'</span></b><small>'+esc(message)+'</small>'+ 
      (prompt ? '<p>'+esc(prompt)+'</p>' : '')+
      (debug && !real ? '<em class="generated-image-debug">'+esc(String(debug).slice(0,180))+'</em>' : '')+
      '<span class="generated-image-tap">แตะภาพเพื่อเปิดป๊อบอัพ · บันทึก/ดาวน์โหลดได้</span></div>';
    return card;
  }
  function renderCodeAndTextSegment(container, source, allBlocks, codeOffset){
    var re = /```\s*([a-zA-Z0-9_+#.-]*)\s*\n([\s\S]*?)```/g, last = 0, match, count = 0;
    source = clean(source);
    while((match = re.exec(source)) !== null){
      if(match.index > last){ container.appendChild(d.createTextNode(source.slice(last, match.index))); }
      container.appendChild(createCodeBlock(match[1] || 'text', clean(match[2]).replace(/\n$/,''), (codeOffset || 0) + count, allBlocks));
      count++;
      last = re.lastIndex;
      if(count > 24) break;
    }
    if(last < source.length){ container.appendChild(d.createTextNode(source.slice(last))); }
    return count;
  }
  function renderMessageContent(container, text, role){
    var source = clean(text), imageRe = /\[\[(AIRA_IMAGE_CARD|AIRA_VISIBLE_IMAGE_CARD)\]\]\s*([\s\S]*?)\s*\[\[\/\1\]\]/g, last = 0, match, codeCount = 0;
    var allBlocks = extractCodeBlocks(source);
    container.textContent = '';
    while((match = imageRe.exec(source)) !== null){
      if(match.index > last){ codeCount += renderCodeAndTextSegment(container, source.slice(last, match.index), allBlocks, codeCount); }
      var payload = parseImageCardPayload(match[2]);
      if(payload){ container.appendChild(createGeneratedImageCard(payload)); }
      else { container.appendChild(d.createTextNode(source.slice(match.index, imageRe.lastIndex))); }
      last = imageRe.lastIndex;
    }
    if(last < source.length){ codeCount += renderCodeAndTextSegment(container, source.slice(last), allBlocks, codeCount); }
    if(codeCount === 0 && !source){ container.appendChild(d.createTextNode('')); }
  }
  function codeCardFromButton(btn){ return btn && btn.closest ? btn.closest('.aira-code-card') : null; }
  function codeDataFromCard(card){
    var code = card && card.querySelector ? card.querySelector('code') : null;
    var lang = card ? (card.getAttribute('data-code-lang') || 'text') : 'text';
    var index = Number(card && card.getAttribute('data-code-index')) || 0;
    return {lang:normalizeCodeLang(lang), code:code ? code.textContent : '', index:index};
  }

  function codeStaticAudit(data){
    data = data || {}; var code = clean(data.code || ''), lang = normalizeCodeLang(data.lang || 'text');
    var isWp = isPhpLikeCode(lang, code) || /wordpress|wp_|add_action|add_shortcode|register_rest_route|wp_ajax_|Plugin\s+Name\s*:/i.test(code);
    var checks = [];
    function add(key,label,pass,weight,fix){ checks.push({key:key,label:label,pass:!!pass,weight:weight||8,fix:fix||''}); }
    add('not_empty','มีโค้ดให้ตรวจ', !!code, 10, 'ต้องมี code block ก่อน');
    add('wp_header','Plugin Header', !isWp || /Plugin\s+Name\s*:/i.test(code), 12, 'เพิ่ม Plugin Name / Description / Version / Author');
    add('nonce','Nonce / CSRF', !isWp || /nonce|check_ajax_referer|wp_verify_nonce|wp_create_nonce/i.test(code), 10, 'เพิ่ม nonce สำหรับ form/AJAX');
    add('capability','Capability', !isWp || /current_user_can|manage_options|edit_posts|permission_callback/i.test(code), 10, 'ตรวจสิทธิ์ผู้ใช้ก่อนทำงาน');
    add('sanitize_escape','Sanitize/Escape', !isWp || /sanitize_|wp_kses|esc_html|esc_attr|esc_url|absint|sanitize_text_field/i.test(code), 12, 'sanitize input และ escape output');
    add('uninstall','Cleanup/Uninstall', !isWp || /register_uninstall_hook|uninstall\.php|delete_option\s*\(/i.test(code), 6, 'เพิ่มแผน cleanup ตอน uninstall');
    add('risky','ไม่มีคำสั่งเสี่ยงสูง', !/(eval\s*\(|base64_decode\s*\(|shell_exec\s*\(|passthru\s*\(|proc_open\s*\(|popen\s*\()/i.test(code), 18, 'ลบ eval/base64/shell/proc ก่อนติดตั้ง');
    var total = checks.reduce(function(s,c){return s+c.weight;},0), pass = checks.reduce(function(s,c){return s+(c.pass?c.weight:0);},0);
    var hooks = collectRegexMatches(code, /add_action\s*\(\s*["']([^"']+)["']/gi, 12);
    var shortcodes = collectRegexMatches(code, /add_shortcode\s*\(\s*["']([^"']+)["']/gi, 8);
    var rest = collectRegexMatches(code, /register_rest_route\s*\(\s*["']([^"']+)["']/gi, 8);
    var ajax = collectRegexMatches(code, /wp_ajax_(?:nopriv_)?([a-z0-9_\-]+)/gi, 10);
    var score = clamp(Math.round((pass / Math.max(1,total)) * 100), 0, 100);
    var missing = checks.filter(function(c){return !c.pass;}).map(function(c){return c.label;});
    return {mode:'client_live_qc', lang:lang, isWp:isWp, score:score, checks:checks, missing:missing, hooks:hooks, shortcodes:shortcodes, rest:rest, ajax:ajax, size:codeByteSizeLabel(code), status:(score>=82?'ready_for_staging':(score>=62?'needs_review':'needs_fix_before_install'))};
  }

  function codeRealityProfile(data){
    data = data || {};
    var code = clean(data.code || ''), lang = normalizeCodeLang(data.lang || 'text');
    var parts = detectCodeParts(lang, code);
    var audit = codeStaticAudit(data);
    var previewReady = blocksCanRenderResult((data.card && data.card.__airaPreviewBlocks) || [{lang:lang, code:code}], lang, code) || looksPreviewable(lang, code);
    var installable = isWordPressInstallableCode(lang, code);
    var hasButtons = /<button|class=["'][^"']*button|type=["']submit|add_(?:menu|submenu)_page|add_shortcode|register_rest_route|wp_ajax_/i.test(code);
    var hasForms = /<form|<input|<textarea|<select|settings_fields|register_setting|wp_nonce_field/i.test(code);
    var hasApi = /fetch\s*\(|XMLHttpRequest|register_rest_route|wp_ajax_|admin-ajax\.php|endpoint|api/i.test(code);
    var hasDocs = /readme|docs|manual|คู่มือ|วิธีใช้|usage|install/i.test(code);
    var packageReady = installable || code.length > 200;
    var installGateReady = !installable || (audit.score >= 70 && /Plugin\s+Name\s*:/i.test(code));
    var clickReady = previewReady && (hasButtons || hasForms || /<a\s/i.test(code));
    var realness = clamp(Math.round((audit.score || 0) * 0.55) + (previewReady?10:0) + (clickReady?8:0) + (packageReady?8:0) + (installGateReady?9:0) + (hasApi?4:0) + (hasDocs?3:0), 0, 100);
    return {lang:lang, parts:parts, audit:audit, previewReady:previewReady, clickReady:clickReady, packageReady:packageReady, installGateReady:installGateReady, hasButtons:hasButtons, hasForms:hasForms, hasApi:hasApi, hasDocs:hasDocs, installable:installable, realness:realness, size:codeByteSizeLabel(code)};
  }
  function stepLabelDone(card, key, fallback){
    if(!card) return !!fallback;
    if(key === 'preview') return card.getAttribute('data-real-preview-seen') === '1' || card.classList.contains('is-previewing') || !!fallback;
    if(key === 'click') return card.getAttribute('data-interactive-preview-tested') === '1' || !!fallback;
    if(key === 'qc') return !!card.getAttribute('data-live-debug-score') || !!fallback;
    if(key === 'package') return card.getAttribute('data-package-downloaded') === '1' || !!fallback;
    if(key === 'gate') return card.getAttribute('data-preinstall-token') || card.getAttribute('data-upgrade-state') || !!fallback;
    return !!fallback;
  }
  function createCodeRealityPanel(lang, code, index, contextBlocks){
    var panel = d.createElement('div');
    panel.className = 'aira-code-reality-panel';
    panel.setAttribute('data-reality-panel','1');
    panel.__airaRealitySeed = {lang:normalizeCodeLang(lang), code:clean(code), index:index || 0, contextBlocks:contextBlocks || []};
    return panel;
  }
  function renderCodeRealityPanel(card){
    if(!card || !card.querySelector) return;
    var panel = card.querySelector('.aira-code-reality-panel');
    if(!panel) return;
    var data = codeDataFromCard(card); data.card = card;
    var profile = codeRealityProfile(data);
    var steps = [
      {key:'preview', label:'Preview', done:stepLabelDone(card,'preview',profile.previewReady), hint:profile.previewReady?'พร้อมดูหน้าจริง':'ต้องมี HTML/SVG/UI หรือ WP sandbox'},
      {key:'click', label:'Click Test', done:stepLabelDone(card,'click',false), hint:profile.clickReady?'รอทดลองกด':'ไม่มีปุ่ม/ฟอร์มให้กด'},
      {key:'qc', label:'QC', done:stepLabelDone(card,'qc',profile.audit.score>=82), hint:(profile.audit.score||0)+'/100'},
      {key:'package', label:'Package', done:stepLabelDone(card,'package',profile.packageReady && !profile.installable), hint:profile.packageReady?'แพ็กได้':'โค้ดสั้น/ยังไม่พร้อม'},
      {key:'gate', label:'Install Gate', done:stepLabelDone(card,'gate',profile.installGateReady && !profile.installable), hint:profile.installable?'ต้องผ่าน Preview+Click':'ไม่ใช่ plugin install'}
    ];
    var chips = steps.map(function(st){
      return '<span class="reality-step '+(st.done?'is-done':'is-wait')+'" data-step="'+esc(st.key)+'"><b>'+esc(st.label)+'</b><small>'+esc(st.done?'พร้อม':st.hint)+'</small></span>';
    }).join('');
    var parts = (profile.parts||[]).map(function(p){ return p.label; }).slice(0,3).join(' / ') || 'Code File';
    panel.innerHTML = '<div class="reality-head"><b>'+icon('preview')+'<span>Real Code Workflow</span></b><em>'+esc(profile.realness)+'/100</em></div><div class="reality-steps">'+chips+'</div><small class="reality-note">'+esc(profile.size)+' · '+esc(parts)+' · ก่อนติดตั้งจริงให้ผ่าน Preview + Click Test + QC</small>';
  }
  function updateAllCodeRealityPanels(root){
    all('.aira-code-card', root || d).forEach(function(card){ renderCodeRealityPanel(card); });
  }
  function auditReportText(audit, server){
    audit = audit || {}; server = server || null;
    var lines = ['AiRA Live Dev Pre-install QC','Version: '+VERSION,'Generated: '+(new Date()).toISOString(),'','Client score: '+(audit.score||0)+'/100','Client status: '+(audit.status||'-'),'Language: '+(audit.lang||'-'),'WordPress related: '+(audit.isWp?'yes':'no'),'Size: '+(audit.size||'-'),'Missing: '+((audit.missing||[]).join(', ') || 'none'),'','Checks:'];
    (audit.checks||[]).forEach(function(c){ lines.push('- '+(c.pass?'PASS':'FIX')+' · '+c.label+(c.fix?' · '+c.fix:'')); });
    lines.push('','Detected:','- Hooks: '+((audit.hooks||[]).join(', ') || '-'),'- Shortcodes: '+((audit.shortcodes||[]).join(', ') || '-'),'- REST: '+((audit.rest||[]).join(', ') || '-'),'- AJAX: '+((audit.ajax||[]).join(', ') || '-'));
    if(server){ lines.push('','Server preinstall check:','- Score: '+(server.score||0)+'/100','- Status: '+(server.status||'-'),'- Installable: '+(server.installable?'yes':'no'),'- Mode: '+(server.mode||'static')); if(server.missing){ lines.push('- Missing: '+(server.missing.join(', ') || 'none')); } }
    lines.push('','Scope: safe pre-install static/runtime simulation only; no PHP execution, no DB write, no plugin activation.');
    return lines.join('\n');
  }
  function ensureLiveDebugPanel(card){
    if(!card || !card.querySelector) return null;
    var panel = card.querySelector('.aira-code-live-debug');
    if(!panel){
      panel = d.createElement('div'); panel.className = 'aira-code-live-debug'; panel.hidden = true;
      var next = card.querySelector('.aira-code-next-row');
      if(next && next.parentNode){ next.parentNode.insertBefore(panel, next.nextSibling); }
      else { card.appendChild(panel); }
    }
    return panel;
  }
  function liveCheckPercent(c){
    c = c || {};
    if(c.pass) return 100;
    var base = c.key === 'risky' ? 18 : 42;
    if(c.key === 'not_empty') base = 5;
    if(c.key === 'wp_header') base = 30;
    return clamp(base + Math.round((c.weight || 8) * 1.8), 5, 74);
  }
  function progressCircleHtml(label, percent, key, tone){
    percent = clamp(percent, 0, 100);
    var cls = tone === 'ok' ? ' is-ok' : (tone === 'warn' ? ' is-warn' : '');
    return '<span class="live-progress-circle'+cls+'" data-live-progress="'+esc(percent)+'" data-live-key="'+esc(key||'')+'" style="--p:0"><i><b>0%</b></i><small>'+esc(label||'QC')+'</small></span>';
  }
  function animateLiveProgressPanel(panel){
    if(!panel) return;
    var circles = all('.live-progress-circle', panel);
    circles.forEach(function(circle, i){
      var target = clamp(parseInt(circle.getAttribute('data-live-progress') || '0', 10), 0, 100);
      var label = circle.querySelector('i b');
      var start = null;
      var duration = 520 + (i * 95);
      function step(ts){
        if(!start) start = ts;
        var t = clamp((ts - start) / duration, 0, 1);
        var eased = 1 - Math.pow(1 - t, 3);
        var value = Math.round(target * eased);
        circle.style.setProperty('--p', String(value));
        if(label) label.textContent = value + '%';
        if(t < 1) w.requestAnimationFrame(step);
      }
      w.setTimeout(function(){ w.requestAnimationFrame(step); }, i * 70);
    });
  }
  function liveProgressRingsHtml(audit, server, fixing){
    audit = audit || {};
    var checks = audit.checks || [];
    var rings = [progressCircleHtml(fixing ? 'แก้จากรายงาน' : 'รวม', audit.score || 0, 'overall', (audit.score||0) >= 82 ? 'ok' : 'warn')];
    checks.forEach(function(c){ rings.push(progressCircleHtml(c.label, liveCheckPercent(c), c.key, c.pass ? 'ok' : 'warn')); });
    if(server){ rings.push(progressCircleHtml('Server', server.score || 0, 'server', (server.score||0) >= 82 ? 'ok' : 'warn')); }
    return '<div class="live-progress-rings" aria-label="Live QC progress">'+rings.join('')+'</div>';
  }
  function startLiveFixProgress(card, audit){
    var panel = ensureLiveDebugPanel(card); if(!panel) return;
    card.classList.add('is-live-fixing');
    var flow = panel.querySelector('.live-debug-flow');
    if(!flow){
      flow = d.createElement('div');
      flow.className = 'live-debug-flow';
      panel.appendChild(flow);
    }
    flow.innerHTML = '<b>กำลังแก้บั๊กสด</b><span>วิเคราะห์ → แก้ → ตรวจซ้ำ → ส่ง code block ใหม่</span><em><i></i><i></i><i></i></em>';
    animateLiveProgressPanel(panel);
    setCodeNextStatus(card, 'กำลังจัดทำรายงานแก้ไข · วงกลม % กำลังประเมิน', 'is-next-sent');
  }
  function ensureRealPreviewBeforeInstall(card, mode){
    if(!card) return false;
    var seen = card.getAttribute('data-real-preview-seen') === '1' && card.classList.contains('is-previewing');
    var clicked = card.getAttribute('data-interactive-preview-tested') === '1';
    if(seen && clicked) return true;
    runPreinstallSandboxFromCard(card);
    card.setAttribute('data-install-gate', mode || 'install');
    if(seen && !clicked){
      setCodeNextStatus(card, 'เปิด Preview แล้ว · กรุณากด “ทดสอบปุ่มทั้งหมด” หรือกดปุ่มจริงใน Sandbox ก่อน '+(mode === 'upgrade' ? 'อัปเกรด' : 'ติดตั้ง'), 'is-next-sent');
      toast('ต้องทดลองกดใน Preview ก่อนติดตั้ง เพื่อลดการปรับซ้ำ', 'warn');
      return false;
    }
    setCodeNextStatus(card, 'เปิดหน้าจริง/Pre-install Preview แล้ว · ทดลองกดระบบใน Sandbox ก่อน แล้วกด '+(mode === 'upgrade' ? 'อัปเกรด' : 'ติดตั้ง')+' อีกครั้ง', 'is-next-sent');
    toast('เปิด Preview ให้ทดลองกดก่อนติดตั้งแล้ว', 'warn');
    return false;
  }
  function preinstallGatePayloadFromCard(card){
    return {
      strict_gate: '1',
      preinstall_token: (card && card.getAttribute ? (card.getAttribute('data-preinstall-token') || '') : ''),
      preinstall_code_hash: (card && card.getAttribute ? (card.getAttribute('data-preinstall-code-hash') || '') : '')
    };
  }
  function rememberServerPreinstallGate(card, server){
    if(!card || !server) return;
    if(server.gate_token){ card.setAttribute('data-preinstall-token', String(server.gate_token)); }
    if(server.code_hash){ card.setAttribute('data-preinstall-code-hash', String(server.code_hash)); }
    if(server.gate_expires_in){ card.setAttribute('data-preinstall-gate-expires-in', String(server.gate_expires_in)); }
    if(server.runtime_guard){ card.setAttribute('data-runtime-write-guard', 'ready'); }
  }

  function renderLiveDebugPanel(card, audit, server, opts){
    opts = opts || {};
    var panel = ensureLiveDebugPanel(card); if(!panel) return;
    var missing = (audit.missing||[]).join(', ') || 'ไม่พบจุดขาดหลัก';
    var serverLine = server ? ('Server: '+(server.score||0)+'/100 · '+(server.status||'-')) : 'Server: กำลังตรวจบน WordPress...';
    var checks = (audit.checks||[]).map(function(c){ return '<span class="live-check '+(c.pass?'pass':'fix')+'"><b>'+(c.pass?'PASS':'FIX')+'</b><small>'+esc(c.label)+'</small><em>'+liveCheckPercent(c)+'%</em></span>'; }).join('');
    var fixing = !!opts.fixing || (card && card.classList && card.classList.contains('is-live-fixing'));
    panel.hidden = false;
    panel.__airaReportText = auditReportText(audit, server);
    panel.innerHTML = '<div class="live-debug-head"><div><b>'+icon('bug')+'<span>Live Dev QC ก่อนติดตั้ง</span></b><small>Client: '+esc(audit.score)+'/100 · '+esc(audit.status)+' · '+esc(serverLine)+'</small></div><div class="live-debug-actions"><button type="button" data-action="code-live-copy-report">Copy Report</button></div></div>'+liveProgressRingsHtml(audit, server, fixing)+'<div class="live-debug-meter"><i style="width:'+esc(audit.score)+'%"></i></div><p><b>ต้องดู/เติม:</b> '+esc(missing)+'</p><div class="live-check-grid">'+checks+'</div><small class="live-debug-note">ตรวจได้ก่อนติดตั้งจริง: UI/flow/static QC/package/server capability โดยไม่ execute PHP และไม่เขียนฐานข้อมูล · หน้าจริง/Preview ต้องขึ้นก่อนติดตั้งหรือส่งมอบ</small>';
    card.setAttribute('data-live-debug-score', String(audit.score||0));
    animateLiveProgressPanel(panel);
    scheduleMeasure();
  }
  function updateLiveDebugPanelServer(card, audit, server){ renderLiveDebugPanel(card, audit, server); }
  function runCodeLiveDebugFromCard(card, opts){
    opts = opts || {}; if(!card) return null;
    var data = codeDataFromCard(card); if(!data.code){ toast('ไม่พบโค้ดสำหรับตรวจบั๊กสด','error'); return null; }
    var audit = codeStaticAudit(data); renderLiveDebugPanel(card, audit, null);
    if(card){ card.setAttribute('data-live-debug-score', String(audit.score || 0)); renderCodeRealityPanel(card); }
    if(actions.preinstallCheck && isPhpLikeCode(data.lang, data.code)){
      ajax('preinstallCheck', {code:data.code, interactive:(card && card.getAttribute('data-interactive-preview-tested') === '1') ? '1' : ''}).then(function(res){ var server = res && res.data ? res.data : null; rememberServerPreinstallGate(card, server); updateLiveDebugPanelServer(card, audit, server); renderCodeRealityPanel(card); }).catch(function(err){ var panel=ensureLiveDebugPanel(card); if(panel){ panel.__airaReportText = auditReportText(audit, {status:'server_check_failed', score:audit.score, missing:[(err&&err.message)||'server check failed']}); } renderCodeRealityPanel(card); });
    }
    if(!opts.silent) toast('ตรวจ Live Dev QC แล้ว: '+audit.score+'/100', audit.score>=82?'ok':'warn');
    return audit;
  }
  function sendCodeLiveFixFromCard(card, brief){
    if(!card) return; clearStaleSending(); if(state.sending){ toast('ระบบกำลังตอบอยู่ รอให้จบก่อนส่งแก้สด','warn'); return; }
    var data = codeDataFromCard(card); if(!data.code){ toast('ไม่พบโค้ดสำหรับแก้สด','error'); return; }
    var audit = runCodeLiveDebugFromCard(card, {silent:true}) || codeStaticAudit(data);
    renderLiveDebugPanel(card, audit, null, {fixing:true});
    startLiveFixProgress(card, audit);
    var prompt = ['จัดทำคำสั่งแก้ไขจาก AiRA Live Dev Pre-install QC แล้วส่ง code block ใหม่ที่ใช้ต่อได้ทันที','','[Live QC]',auditReportText(audit),'','[Instruction]',brief || 'แก้จุดที่ขาดทั้งหมด เพิ่มความปลอดภัย ลดบั๊ก UI/API/WordPress และคงชุดปุ่มหลักแบบไม่ซ้ำ: Preview / Debug / โหลดแพ็ก / อัปเกรด ให้ทำงานต่อ โดยเอา Next/Fix สดออกจาก UI พร้อมแสดงวงกลมเปอร์เซ็นต์ QC และเปิดหน้าจริง/Preview ก่อนติดตั้งหรือส่งมอบ', 'ตอบสรุปสั้น 1 บรรทัดก่อน แล้วตามด้วยโค้ดเต็มใน fenced code block เท่านั้นถ้าเป็นงานโค้ด','','[Current Code]','```'+normalizeCodeLang(data.lang),data.code,'```'].join('\n');
    setCodeNextStatus(card, 'ส่งรายงาน Live QC แล้ว · รอ code block ใหม่', 'is-next-sent');
    submitPrompt(prompt, 'Live QC Report · '+downloadNameForCode(data.lang, data.index), {mode:'live_dev_fix', source:'preinstall-live-debug-740'});
  }
  function copyLiveDebugReport(btn){
    var card = codeCardFromButton(btn), panel = card && card.querySelector ? card.querySelector('.aira-code-live-debug') : null;
    if(!panel || !panel.__airaReportText){ runCodeLiveDebugFromCard(card, {silent:true}); panel = card && card.querySelector ? card.querySelector('.aira-code-live-debug') : null; }
    copyText((panel && panel.__airaReportText) || 'No live debug report').then(function(){ toast('คัดลอก Live QC Report แล้ว','ok'); }).catch(function(){ toast('คัดลอก report ไม่สำเร็จ','error'); });
  }
  function handleSandboxMessage(ev){
    var msg = ev && ev.data ? ev.data : null;
    if(!msg || msg.source !== 'aira-preinstall-sandbox-v750' && msg.source !== 'aira-preinstall-sandbox-v747' && msg.source !== 'aira-preinstall-sandbox-v746' && msg.source !== 'aira-preinstall-sandbox-v742' && msg.source !== 'aira-preinstall-sandbox-v741' && msg.source !== 'aira-preinstall-sandbox-v740' && msg.source !== 'aira-preinstall-sandbox-v739') return;
    var cards = all('.aira-code-card.is-preinstall-sandbox'); var card = cards[cards.length - 1] || null;
    if(!card) return;
    if(msg.action === 'sandbox-debug' || msg.action === 'sandbox-run' || msg.action === 'sandbox-api' || msg.action === 'sandbox-save' || msg.action === 'sandbox-click' || msg.action === 'sandbox-fill' || msg.action === 'sandbox-test-all' || msg.action === 'sandbox-reset'){
      if(msg.action === 'sandbox-reset'){
        card.removeAttribute('data-interactive-preview-tested');
        renderCodeRealityPanel(card);
        setCodeNextStatus(card, 'รีเซ็ต Click Test แล้ว · ทดลองกดใน Preview อีกครั้งก่อนติดตั้ง', 'is-next-sent');
        toast('รีเซ็ตผลทดสอบ Preview แล้ว', 'warn');
        return;
      }
      card.setAttribute('data-real-preview-seen','1');
      card.setAttribute('data-preview-before-install','1');
      card.setAttribute('data-interactive-preview-tested','1');
      runCodeLiveDebugFromCard(card, {silent:true});
      renderCodeRealityPanel(card);
      var count = msg.extra && typeof msg.extra.count !== 'undefined' ? (' · '+msg.extra.count+' จุด') : '';
      setCodeNextStatus(card, 'Interactive Pre-install Test ผ่านแล้ว'+count+' · พร้อมไปขั้น Package/Install/Upgrade', 'is-next-sent');
      toast('Sandbox ทดลองกดจริงแล้ว ส่งสถานะกลับ Live QC แล้ว', 'ok');
      return;
    }
    if(msg.action === 'sandbox-next'){
      var brief = trim((msg.extra && msg.extra.brief) || 'ปรับปรุงจาก Preview');
      if(!brief){ toast('พิมพ์ข้อความสั้น ๆ ใน Preview ก่อนกด Next', 'warn'); return; }
      brief = brief.slice(0, 180);
      card.setAttribute('data-real-preview-seen','1');
      card.setAttribute('data-preview-before-install','1');
      var nextInput = card.querySelector ? card.querySelector('.aira-code-next-input') : null;
      if(nextInput){ nextInput.value = brief; }
      setCodeNextStatus(card, 'Preview Next · ส่งคำสั่งปรับปรุงทันที: ' + brief, 'is-next-sent');
      sendCodeNextFromCard(card);
      return;
    }
    if(msg.action === 'sandbox-fix'){
      sendCodeLiveFixFromCard(card, (msg.extra && msg.extra.brief) || 'แก้บั๊กต่อจาก Pre-install Sandbox');
    }
  }
  function toggleCodeFilePanel(card, forceOpen){
    if(!card) return;
    var panel = card.querySelector('.aira-code-file-panel');
    var btn = card.querySelector('[data-action="code-file-toggle"]');
    if(!panel) return;
    var open = typeof forceOpen === 'boolean' ? forceOpen : panel.hidden;
    panel.hidden = !open;
    card.classList.toggle('is-file-expanded', open);
    if(btn){ btn.setAttribute('aria-expanded', open ? 'true' : 'false'); }
    var bottomFile = card.querySelector('.aira-code-bottom-actions [data-action="code-file-toggle"]');
    if(bottomFile){ bottomFile.setAttribute('aria-expanded', open ? 'true' : 'false'); bottomFile.classList.toggle('is-active', open); }
    scheduleMeasure();
  }
  function codeDigestForGateway(code){
    code = clean(code || '');
    var lines = code.split(/\n/);
    var hooks = collectRegexMatches(code, /add_action\s*\(\s*["']([^"']+)["']/gi, 10).join(', ') || '-';
    var shortcodes = collectRegexMatches(code, /add_shortcode\s*\(\s*["']([^"']+)["']/gi, 8).join(', ') || '-';
    var rest = collectRegexMatches(code, /register_rest_route\s*\(\s*["']([^"']+)["']/gi, 8).join(', ') || '-';
    var buttons = collectRegexMatches(code, /<button[^>]*>([\s\S]{0,80}?)<\/button>/gi, 8).map(function(x){return stripHtmlForError(x).slice(0,50);}).join(', ') || '-';
    return [
      '/* AiRA Gateway Guard Digest',
      'Original lines: ' + lines.length + ' · Original chars: ' + code.length,
      'Plugin: ' + (headerValueFromPhp(code, 'Plugin Name') || '-'),
      'Version: ' + (headerValueFromPhp(code, 'Version') || '-'),
      'Hooks: ' + hooks,
      'Shortcodes: ' + shortcodes,
      'REST: ' + rest,
      'Buttons: ' + buttons,
      'Note: โค้ดถูกย่อเฉพาะตอนส่งให้ AI เพื่อกัน 504; code block ต้นฉบับในหน้าแชทยังอยู่ครบสำหรับ Copy/Download/Preview/Upgrade.',
      '*/'
    ].join('\n');
  }
  function shortCodePreviewForPrompt(code){
    code = clean(code);
    if(code.length <= 9500) return code;
    return code.slice(0, 6200) + '\n\n' + codeDigestForGateway(code) + '\n\n/* ...ตัดกลางไฟล์เพื่อป้องกัน 504 Gateway Timeout... */\n\n' + code.slice(-2800);
  }
  function compactPromptForGateway(text){
    text = clean(text || '');
    var originalLen = text.length;
    var compacted = false;
    var out = text.replace(/```\s*([a-zA-Z0-9_+#.-]*)\s*\n([\s\S]*?)```/g, function(all, lang, code){
      code = clean(code || '');
      var next = shortCodePreviewForPrompt(code);
      if(next.length !== code.length){ compacted = true; }
      return '```' + normalizeCodeLang(lang || 'text') + '\n' + next + '\n```';
    });
    if(out.length > 22000){
      compacted = true;
      out = out.slice(0, 14500) + '\n\n[AiRA Gateway Guard: ตัดกลาง prompt เพื่อไม่ให้ admin-ajax/nginx timeout แต่ยังรักษาเป้าหมายล่าสุดและโค้ดส่วนต้น/ท้ายไว้]\n\n' + out.slice(-6500);
    }
    if(compacted){
      out = '[AiRA Gateway Guard v7.5.6.1]\nคำสั่งนี้ถูกย่ออัตโนมัติเพื่อป้องกัน HTTP 504 แต่ต้องตอบให้ตรงเป้าหมายเดิม และถ้าไม่เห็นโค้ดบางส่วนให้รักษาโครงเดิมไว้ ห้ามเดาทำลายของเดิม\nOriginal chars: ' + originalLen + ' → Sent chars: ' + out.length + '\n\n' + out;
    }
    return {text:out, compacted:compacted, originalLength:originalLen, sentLength:out.length};
  }
  function timeoutRecoveryPrompt(originalText, err){
    originalText = clean(originalText || '');
    var compacted = compactPromptForGateway(originalText);
    var base = compacted.text || originalText;
    if(base.length > 12000){ base = base.slice(0, 7600) + '\n\n[Retry Slim: ตัดกลางคำสั่งเพื่อให้ตอบทันเวลา]\n\n' + base.slice(-3600); }
    return [
      'Retry Slim หลังเจอ 504/timeout: โปรดตอบแบบเร็วและใช้งานได้จริง',
      '',
      'กติกา:',
      '1) ไม่ต้องประมวลผลยาว 60 วินาที ให้สรุปและส่งโค้ด/แพตช์ที่จำเป็นที่สุดก่อน',
      '2) หากเป็น code block ให้แก้เฉพาะจุดสำคัญที่ทำให้ระบบเดินต่อได้จริง',
      '3) ทุกปุ่ม/คำสั่งต้องมีประโยชน์ต่อผู้ใช้ มี feedback และทดสอบได้',
      '4) สรุป: แก้ตรงไหน → วิธีเช็ก → เหลืออะไร',
      '',
      '[Error]',
      clean((err && err.message) || err || 'timeout').slice(0, 300),
      '',
      '[Original Request Compact]',
      base
    ].join('\n');
  }
  function codeNextIteration(card){
    var current = parseInt((card && card.getAttribute && card.getAttribute('data-next-iteration')) || '0', 10);
    return isNaN(current) ? 0 : current;
  }
  function setCodeNextStatus(card, text, cls){
    if(!card || !card.querySelector) return;
    var status = card.querySelector('.aira-code-next-status');
    if(status){ status.textContent = text || ''; }
    card.classList.remove('is-next-sent','is-next-satisfied');
    if(cls){ card.classList.add(cls); }
  }
  function codeNextPromptFromCard(card, brief, iteration){
    var data = codeDataFromCard(card);
    var code = data.code || '';
    var lang = data.lang || 'text';
    var name = headerValueFromPhp(code, 'Plugin Name') || downloadNameForCode(lang, data.index);
    var version = headerValueFromPhp(code, 'Version') || '-';
    var parts = detectCodeParts(lang, code);
    var partLabels = parts.map(function(p){ return p.label; }).join(', ') || 'Code File';
    var versionLine = card && card.querySelector ? clean((card.querySelector('.aira-code-version-line') || {}).textContent || '') : '';
    var upgradeState = card && card.getAttribute && card.getAttribute('data-upgrade-state') ? card.getAttribute('data-upgrade-state') : 'ready';
    var loopRound = iteration || (codeNextIteration(card) + 1);
    return [
      'Next Loop รอบที่ ' + loopRound + ': ' + (trim(brief) || 'ปรับต่อจาก code block นี้'),
      '',
      '[AiRA Code Next Loop Context]',
      '- File: ' + downloadNameForCode(lang, data.index),
      '- Plugin/Module: ' + name,
      '- Version: ' + version,
      '- Upgrade state: ' + upgradeState,
      '- Version line: ' + (versionLine || 'ไม่มี'),
      '- Related parts: ' + partLabels,
      '- User short instruction: ' + (trim(brief) || 'ปรับต่อ'),
      '',
      'คำสั่งระบบสำหรับรอบนี้:',
      '1) ใช้โค้ดด้านล่างเป็นบริบทหลัก แล้วปรับ/พัฒนาต่อจากคำสั่งสั้น ๆ ของผู้ใช้ทันที',
      '2) ตอบเป็นผลลัพธ์ที่นำไปใช้ต่อได้จริง ไม่ตอบแค่สถานะ ไม่ถามกลับถ้าแก้ต่อได้',
      '3) ทำแบบ Build Until Success: ตรวจของเดิม → เติมส่วนที่ขาด → แก้บั๊ก → ทดสอบ Preview/QC → ส่ง code block ใหม่ที่รันต่อได้',
      '4) ถ้าเกี่ยวกับ WordPress/Plugin ให้ส่งโค้ดเต็มที่อัปเดตแล้วใน fenced code block ภาษา php เพื่อให้ปุ่ม Install/Upgrade/Next ทำงานต่อได้',
      '5) เพิ่มหรือรักษา Plugin Header และอัปเดต Version/คำอธิบาย 1 บรรทัดด้านบนให้ชัดเจน',
      '6) ห้ามหยุดที่คำว่า “ควรทำ/แผน”; ถ้ารู้ว่าต้องเพิ่มอะไร ให้เพิ่มในโค้ดจริงทันที ถ้าติดข้อจำกัดจริงให้บอกเฉพาะจุดที่ต้องมี credential/server เท่านั้น',
      '7) สรุปสั้น ๆ ว่าปรับอะไร, ของเดิมยังอยู่ไหม, ทดสอบอย่างไร, และเหลืออะไรถ้ายังไม่จบ',
      '8) หลังคำตอบนี้ ผู้ใช้จะกด Next/ทำจนสำเร็จต่อได้ ดังนั้นให้เตรียมผลลัพธ์เป็นรอบถัดไปได้ทันที',
      '',
      '```' + normalizeCodeLang(lang),
      shortCodePreviewForPrompt(code),
      '```'
    ].join('\n');
  }
  function sendCodeNextFromCard(card){
    clearStaleSending(); if(state.sending){ toast('ระบบกำลังตอบอยู่ รอให้จบก่อนกด Next', 'warn'); return; }
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับ Next', 'error'); return; }
    var input = card && card.querySelector ? card.querySelector('.aira-code-next-input') : null;
    var brief = trim(input ? input.value : '');
    if(!brief){
      if(input){ input.focus({preventScroll:true}); }
      toast('พิมพ์ข้อความสั้น ๆ ก่อนกด Next', 'warn');
      return;
    }
    var name = headerValueFromPhp(data.code, 'Plugin Name') || downloadNameForCode(data.lang, data.index);
    var version = headerValueFromPhp(data.code, 'Version') || '-';
    var iteration = codeNextIteration(card) + 1;
    card.setAttribute('data-next-iteration', String(iteration));
    card.setAttribute('data-next-last-brief', brief);
    var prompt = codeNextPromptFromCard(card, brief, iteration);
    var visible = 'Next รอบที่ ' + iteration + ': ' + brief + '\n\n[Code block: ' + name + ' · Version ' + version + ' · ' + downloadNameForCode(data.lang, data.index) + ']';
    var nextBtns = all('[data-action="code-wp-next"]', card);
    nextBtns.forEach(function(nextBtn){ nextBtn.disabled = true; nextBtn.classList.add('is-loading'); nextBtn.innerHTML = icon('next') + '<span>กำลังปรับ...</span>'; });
    setCodeNextStatus(card, 'รอบที่ ' + iteration + ' · ส่งคำสั่งปรับต่อแล้ว: ' + brief, 'is-next-sent');
    toast('ส่ง Next รอบที่ ' + iteration + ' ให้ AiRA ปรับต่อแล้ว', 'ok');
    sendChat(prompt, visible);
    w.setTimeout(function(){
      nextBtns.forEach(function(nextBtn){ nextBtn.disabled = false; nextBtn.classList.remove('is-loading'); nextBtn.innerHTML = icon('next') + '<span>Next ต่อ</span>'; });
      if(input){ input.value = ''; input.placeholder = 'พิมพ์สิ่งที่อยากปรับต่อ แล้วกด Next อีกครั้ง'; }
    }, 900);
  }
  function markCodeNextSatisfied(card){
    if(!card){ return; }
    var iteration = codeNextIteration(card);
    setCodeNextStatus(card, 'พอใจแล้ว · จบรอบปรับต่อที่ ' + iteration + ' · ยังสามารถพิมพ์แล้วกด Next ต่อได้ถ้าต้องการ', 'is-next-satisfied');
    var input = card.querySelector ? card.querySelector('.aira-code-next-input') : null;
    if(input){ input.placeholder = 'ถ้าต้องการปรับเพิ่ม ให้พิมพ์สั้น ๆ แล้วกด Next ต่อ'; }
    toast('บันทึกสถานะว่าพอใจแล้ว', 'ok');
  }

  function installWordPressCodeFromCard(card){
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับติดตั้ง','error'); return; }
    if(!isWordPressInstallableCode(data.lang, data.code)){
      toast('โค้ดนี้ยังไม่ใช่ปลั๊กอิน WordPress ที่ติดตั้งได้ ต้องมี <?php และ Plugin Name','warn');
      toggleCodeFilePanel(card, true);
      return;
    }
    if(!cfg.canManage){ toast('ต้องมีสิทธิ์ผู้ดูแลระบบก่อนติดตั้งปลั๊กอิน','error'); return; }
    if(!ensureRealPreviewBeforeInstall(card, 'install')) return;
    var ok = w.confirm('ติดตั้งโค้ดนี้เป็น WordPress Plugin?\n\nระบบจะติดตั้งไฟล์เป็นปลั๊กอิน แต่จะไม่เปิดใช้งานอัตโนมัติ กรุณาตรวจโค้ดก่อนใช้งานจริง');
    if(!ok) return;
    var installBtn = card.querySelector('[data-action="code-wp-install"]');
    if(installBtn){ installBtn.disabled = true; installBtn.classList.add('is-loading'); installBtn.innerHTML = icon('install') + '<span>กำลังติดตั้ง...</span>'; }
    var gatePayload = preinstallGatePayloadFromCard(card);
    ajax('installGeneratedPlugin', Object.assign({code:data.code}, gatePayload)).then(function(res){
      if(!res || res.success === false){ throw new Error((res && res.data && res.data.message) || 'install_failed'); }
      var msg = (res && res.data && res.data.message) || 'ติดตั้งปลั๊กอินสำเร็จ';
      toast(msg, 'ok');
      card.setAttribute('data-upgrade-state','installed');
      renderCodeRealityPanel(card);
      var nextInput = card.querySelector('.aira-code-next-input');
      if(nextInput){ nextInput.placeholder = 'พิมพ์สั้น ๆ เช่น ตรวจหลังติดตั้งต่อ แล้วกด Next'; }
      var nextRow = card.querySelector('.aira-code-next-row');
      if(nextRow){ nextRow.classList.add('is-ready'); }
      toggleCodeFilePanel(card, true);
      var panel = card.querySelector('.aira-code-file-panel');
      if(panel && res && res.data && res.data.plugins_url){
        var note = d.createElement('p'); note.className = 'wp-install-result'; note.innerHTML = '<b>ติดตั้งแล้ว</b><span>ไปที่หน้า Plugins เพื่อตรวจและกด Activate เอง: '+esc(res.data.plugins_url)+'</span>';
        panel.appendChild(note);
      }
    }).catch(function(err){
      toast((err && err.message) ? err.message : 'ติดตั้งปลั๊กอินไม่สำเร็จ', 'error');
      toggleCodeFilePanel(card, true);
    }).finally(function(){
      if(installBtn){ installBtn.disabled = false; installBtn.classList.remove('is-loading'); installBtn.innerHTML = icon('install') + '<span>ติดตั้ง Plugin</span>'; }
    });
  }
  function upgradeCodeInChatFromCard(card){
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับอัปเกรด','error'); return; }
    clearStaleSending(); if(state.sending){ toast('ระบบกำลังตอบอยู่ รอให้จบก่อนกดอัปเกรด', 'warn'); return; }
    var name = headerValueFromPhp(data.code, 'Plugin Name') || downloadNameForCode(data.lang, data.index);
    var version = headerValueFromPhp(data.code, 'Version') || '-';
    var isWp = isPhpLikeCode(data.lang, data.code) || /wordpress|wp_|add_action|add_shortcode|register_block_type|elementor/i.test(data.code);
    var prompt = [
      'อัปเกรด code block นี้ให้ดีขึ้นและส่งผลลัพธ์ที่ใช้ต่อได้ทันที',
      '',
      '[AiRA Code Upgrade Context]',
      '- File: ' + name,
      '- Version: ' + version,
      '- Language: ' + normalizeCodeLang(data.lang),
      '- WordPress related: ' + (isWp ? 'yes' : 'no'),
      '- Source file: ' + downloadNameForCode(data.lang, data.index),
      '',
      '[Code]',
      '```' + normalizeCodeLang(data.lang),
      data.code,
      '```',
      '',
      '[Instruction]',
      isWp ? 'ปรับให้เป็น WordPress plugin/module ที่ติดตั้งหรืออัปเกรดได้จริง ถ้ายังไม่มี Plugin Header ให้เพิ่ม Plugin Name, Description, Version, Author ให้ครบ และตอบเป็นโค้ด PHP เต็มใน fenced code block' : 'ปรับปรุงโค้ดให้สมบูรณ์ขึ้น แก้บั๊ก เพิ่มความปลอดภัย ความยืดหยุ่น และตอบกลับเป็นโค้ดเต็มใน fenced code block',
      'ใช้แนวทาง Build Until Success: อย่าหยุดที่แผน ให้เติมโค้ดจริง แก้ส่วนที่ตรวจพบว่าขาด และทดสอบ logic ด้วยตัวเองก่อนตอบ',
      'ให้มีสรุปสั้น ๆ ด้านบน 1 บรรทัดว่าอัปเกรดอะไรและเวอร์ชันอะไร เพื่อให้ปุ่มใต้ code block รอบถัดไปยังทำงานต่อได้'
    ].join('\n');
    var visible = 'อัปเกรด code block: ' + name + ' · Version ' + version;
    var upBtns = all('[data-action="code-wp-upgrade"]', card);
    upBtns.forEach(function(upBtn){ upBtn.disabled = true; upBtn.classList.add('is-loading'); upBtn.innerHTML = icon('upgrade') + '<span>กำลังส่ง...</span>'; });
    setCodeNextStatus(card, 'ส่งคำสั่งอัปเกรดผ่านแชทแล้ว · รอผลลัพธ์ code block ใหม่', 'is-next-sent');
    submitPrompt(prompt, visible, {mode:'code_upgrade', source:'code-block-upgrade-733'}).finally(function(){
      upBtns.forEach(function(upBtn){ upBtn.disabled = false; upBtn.classList.remove('is-loading'); upBtn.innerHTML = icon('upgrade') + '<span>อัปเกรด</span>'; });
    });
  }

  function upgradeWordPressCodeFromCard(card){
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับอัปเกรด','error'); return; }
    if(!isWordPressInstallableCode(data.lang, data.code)){
      upgradeCodeInChatFromCard(card);
      return;
    }
    if(!cfg.canManage){ toast('ต้องมีสิทธิ์ผู้ดูแลระบบก่อนอัปเกรดปลั๊กอิน','error'); return; }
    if(!ensureRealPreviewBeforeInstall(card, 'upgrade')) return;
    var version = headerValueFromPhp(data.code, 'Version') || '-';
    var name = headerValueFromPhp(data.code, 'Plugin Name') || downloadNameForCode(data.lang, data.index);
    var ok = w.confirm('อัปเกรด Plugin จากโค้ดนี้?\n\n' + name + '\nVersion: ' + version + '\n\nระบบจะค้นหา plugin เดิมจาก Plugin Name แล้วอัปเดตไฟล์หลักแบบปลอดภัย หากไม่พบจะสร้างแพ็กอัปเกรดใหม่ และจะไม่ Activate อัตโนมัติ');
    if(!ok) return;
    var upBtns = all('[data-action="code-wp-upgrade"]', card);
    upBtns.forEach(function(upBtn){ upBtn.disabled = true; upBtn.classList.add('is-loading'); upBtn.innerHTML = icon('upgrade') + '<span>กำลังอัปเกรด...</span>'; });
    var gatePayload = preinstallGatePayloadFromCard(card);
    ajax('upgradeGeneratedPlugin', Object.assign({code:data.code}, gatePayload)).then(function(res){
      if(!res || res.success === false){ throw new Error((res && res.data && res.data.message) || 'upgrade_failed'); }
      var info = (res && res.data) || {};
      var msg = info.message || 'อัปเกรดปลั๊กอินสำเร็จ พร้อมตอบคำถามต่อไปได้ตามปกติ';
      toast(msg, 'ok');
      card.setAttribute('data-upgrade-state','upgraded');
      renderCodeRealityPanel(card);
      var nextInput = card.querySelector('.aira-code-next-input');
      if(nextInput){ nextInput.placeholder = 'พิมพ์สั้น ๆ เช่น ตรวจบั๊กหลังอัปเกรดต่อ แล้วกด Next'; }
      var nextRow = card.querySelector('.aira-code-next-row');
      if(nextRow){ nextRow.classList.add('is-ready'); }
      toggleCodeFilePanel(card, true);
      var panel = card.querySelector('.aira-code-file-panel');
      if(panel){
        var note = d.createElement('p');
        note.className = 'wp-install-result wp-upgrade-result';
        note.innerHTML = '<b>อัปเกรดแล้ว</b><span>'+esc(info.plugin_name || name)+' · Version '+esc(info.old_version || '-')+' → '+esc(info.version || version || '-')+' · พร้อมตอบคำถามต่อไปได้ตามปกติ</span>'+(info.backup_created ? '<small>Backup: '+esc(info.backup_created)+'</small>' : '')+'<small>พิมพ์ข้อความสั้น ๆ ในช่อง Next ใต้ไฟล์ แล้วกด Next เพื่อให้ระบบทำงานต่อจากโค้ดนี้</small>';
        panel.appendChild(note);
      }
      var versionLine = card.querySelector('.aira-code-version-line');
      if(versionLine){ versionLine.textContent = 'Version: ' + (info.version || version || '-') + ' · WordPress Plugin · อัปเกรดแล้ว · พร้อมตอบคำถามต่อไปได้ตามปกติ · ' + downloadNameForCode(data.lang, data.index); }
      var input = byId('composerInput');
      if(input && input.focus){ input.focus({preventScroll:true}); }
      scheduleMeasure();
    }).catch(function(err){
      toast((err && err.message) ? err.message : 'อัปเกรดปลั๊กอินไม่สำเร็จ', 'error');
      toggleCodeFilePanel(card, true);
    }).finally(function(){
      upBtns.forEach(function(upBtn){ upBtn.disabled = false; upBtn.classList.remove('is-loading'); upBtn.innerHTML = icon('upgrade') + '<span>อัปเกรด</span>'; });
    });
  }


  function asciiSlug(text, fallback){
    text = clean(text || '').toLowerCase();
    text = text.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 48);
    return text || (fallback || 'aira-generated-plugin');
  }
  function wordpressPackageInfoV740(code, lang, index){
    code = clean(code); lang = normalizeCodeLang(lang);
    var name = headerValueFromPhp(code, 'Plugin Name') || 'AiRA Generated Plugin';
    var desc = headerValueFromPhp(code, 'Description') || 'Generated by AiRA Studio';
    var version = headerValueFromPhp(code, 'Version') || '1.0.0';
    var author = headerValueFromPhp(code, 'Author') || 'Thinkb4do';
    var slug = asciiSlug(name, 'aira-generated-plugin');
    var main = slug + '/' + slug + '.php';
    var shortcodes = collectRegexMatches(code, /add_shortcode\s*\(\s*["']([^"']+)["']/gi, 8);
    var actions = collectRegexMatches(code, /add_action\s*\(\s*["']([^"']+)["']/gi, 12);
    var filters = collectRegexMatches(code, /add_filter\s*\(\s*["']([^"']+)["']/gi, 8);
    var menus = collectRegexMatches(code, /add_(?:menu|submenu)_page\s*\(\s*["']([^"']+)["']/gi, 6);
    var rest = collectRegexMatches(code, /register_rest_route\s*\(\s*["']([^"']+)["']/gi, 8);
    var ajax = collectRegexMatches(code, /wp_ajax_(?:nopriv_)?([a-z0-9_\-]+)/gi, 10);
    var parts = detectCodeParts(lang, code);
    var hasHeader = /Plugin\s+Name\s*:/i.test(code);
    var hasNonce = /nonce|check_ajax_referer|wp_verify_nonce|wp_create_nonce/i.test(code);
    var hasCapability = /current_user_can|manage_options|edit_posts|permission_callback/i.test(code);
    var hasSanitize = /sanitize_|wp_kses|esc_html|esc_attr|esc_url|absint|sanitize_text_field/i.test(code);
    var hasUninstall = /register_uninstall_hook|uninstall\.php|delete_option\s*\(/i.test(code);
    var score = 42 + (hasHeader?14:0) + (version?8:0) + (hasCapability?10:0) + (hasNonce?10:0) + (hasSanitize?10:0) + (hasUninstall?6:0) + (shortcodes.length||menus.length||rest.length||ajax.length?8:0);
    score = clamp(score, 0, 100);
    var missing = [];
    if(!hasHeader) missing.push('Plugin Header');
    if(!hasCapability) missing.push('Capability/permission check');
    if(!hasNonce) missing.push('Nonce verification');
    if(!hasSanitize) missing.push('Sanitize/Escape');
    if(!hasUninstall) missing.push('Uninstall/Cleanup');
    var files = [
      {path: main, content: code},
      {path: slug + '/readme.txt', content: '=== ' + name + ' ===\nContributors: thinkb4do\nTags: aira, generated, wordpress\nRequires at least: 6.0\nTested up to: current\nRequires PHP: 7.4\nStable tag: ' + version + '\n\n== Description ==\n' + desc + '\n\n== Pre-install Preview ==\nตรวจใน AiRA Studio Sandbox ก่อนติดตั้งแล้ว แต่ยังควรตรวจบน staging site ก่อนเปิดใช้จริง\n'},
      {path: slug + '/docs/preinstall-report.txt', content: 'AiRA Studio Pre-install Report\nPlugin: ' + name + '\nVersion: ' + version + '\nAuthor: ' + author + '\nShortcodes: ' + (shortcodes.join(', ') || '-') + '\nActions: ' + (actions.join(', ') || '-') + '\nFilters: ' + (filters.join(', ') || '-') + '\nREST: ' + (rest.join(', ') || '-') + '\nAJAX: ' + (ajax.join(', ') || '-') + '\nSecurity: nonce=' + (hasNonce?'yes':'no') + ', capability=' + (hasCapability?'yes':'no') + ', sanitize/escape=' + (hasSanitize?'yes':'no') + '\n'},
      {path: slug + '/docs/live-dev-qc.txt', content: 'AiRA Studio Live Dev QC\nVersion: ' + VERSION + '\nMode: preinstall live sandbox + static/server check\nScope: no PHP execution, no DB write, no plugin activation before user confirmation.\nReadiness: ' + score + '/100\nMissing: ' + (missing.join(', ') || 'none') + '\n'},
      {path: slug + '/manifest.json', content: JSON.stringify({name:name, slug:slug, version:version, author:author, main_file:main, shortcodes:shortcodes, actions:actions, filters:filters, rest:rest, ajax:ajax, parts:parts.map(function(p){return p.label;}), generated_by:'AiRA Studio v7.4.7'}, null, 2)}
    ];
    if(/wp_enqueue_style|\.css|assets\/css/i.test(code)) files.push({path:slug+'/assets/css/admin.css', content:'/* AiRA package placeholder: add generated CSS here when the plugin references assets. */\n'});
    if(/wp_enqueue_script|\.js|assets\/js/i.test(code)) files.push({path:slug+'/assets/js/admin.js', content:'/* AiRA package placeholder: add generated JS here when the plugin references assets. */\n'});
    if(!hasUninstall) files.push({path:slug+'/uninstall.php', content:'<?php\nif (!defined(\'WP_UNINSTALL_PLUGIN\')) { exit; }\n// AiRA placeholder: add delete_option/delete_transient cleanup here after review.\n'});
    return {name:name, desc:desc, version:version, author:author, slug:slug, main:main, shortcodes:shortcodes, actions:actions, filters:filters, menus:menus, rest:rest, ajax:ajax, parts:parts, files:files, score:score, missing:missing};
  }
  function readPhpParenArgs(code, openIndex){
    code = clean(code); var depth = 1, quote = '', escNext = false, out = '';
    for(var i = openIndex + 1; i < code.length; i++){
      var ch = code.charAt(i), prev = code.charAt(i-1);
      if(quote){ out += ch; if(escNext){ escNext=false; continue; } if(ch === '\\'){ escNext=true; continue; } if(ch === quote && prev !== '\\'){ quote=''; } continue; }
      if(ch === '"' || ch === "'"){ quote = ch; out += ch; continue; }
      if(ch === '('){ depth++; out += ch; continue; }
      if(ch === ')'){ depth--; if(depth === 0){ return {args:out, end:i}; } out += ch; continue; }
      out += ch;
    }
    return {args:out, end:code.length};
  }
  function splitPhpArgs(src){
    src = clean(src); var out=[], buf='', quote='', escNext=false, paren=0, bracket=0, brace=0;
    for(var i=0;i<src.length;i++){
      var ch=src.charAt(i), prev=src.charAt(i-1);
      if(quote){ buf += ch; if(escNext){ escNext=false; continue; } if(ch === '\\'){ escNext=true; continue; } if(ch === quote && prev !== '\\'){ quote=''; } continue; }
      if(ch === '"' || ch === "'"){ quote=ch; buf+=ch; continue; }
      if(ch === '('){ paren++; buf+=ch; continue; }
      if(ch === ')'){ paren=Math.max(0,paren-1); buf+=ch; continue; }
      if(ch === '['){ bracket++; buf+=ch; continue; }
      if(ch === ']'){ bracket=Math.max(0,bracket-1); buf+=ch; continue; }
      if(ch === '{'){ brace++; buf+=ch; continue; }
      if(ch === '}'){ brace=Math.max(0,brace-1); buf+=ch; continue; }
      if(ch === ',' && !paren && !bracket && !brace){ out.push(trim(buf)); buf=''; continue; }
      buf+=ch;
    }
    if(trim(buf) || src){ out.push(trim(buf)); }
    return out;
  }
  function phpArgStringValue(arg){
    arg = trim(arg || '');
    var m = arg.match(/^[\s]*(?:__|_e|esc_html__|esc_attr__)?\s*\(\s*(["'])([\s\S]*?)\1/i) || arg.match(/^[\s]*(["'])([\s\S]*?)\1/);
    if(m){ return trim(String(m[2] || '').replace(/\\'/g,"'").replace(/\\"/g,'"')); }
    var mm = arg.match(/["']([^"']{1,90})["']/);
    return mm ? trim(mm[1]) : trim(arg.replace(/\s+/g,' ').slice(0,90));
  }
  function phpCallableNameFromArg(arg){
    arg = trim(arg || '');
    var m = arg.match(/(?:array\s*\(|\[)[\s\S]*?,\s*(["'])([A-Za-z_][A-Za-z0-9_]*)\1/i);
    if(m) return m[2];
    m = arg.match(/(["'])([A-Za-z_][A-Za-z0-9_]*(?:::[A-Za-z_][A-Za-z0-9_]*)?)\1/);
    if(m){ return m[2].split('::').pop(); }
    m = arg.match(/([A-Za-z_][A-Za-z0-9_]*)\s*$/);
    return m ? m[1] : '';
  }
  function findPhpCalls(code, callNameRe){
    code = clean(code); var calls=[], re = callNameRe, m;
    while((m = re.exec(code)) !== null){
      var open = code.indexOf('(', m.index);
      if(open < 0) continue;
      var read = readPhpParenArgs(code, open);
      calls.push({name:m[1] || m[0], args:read.args, index:m.index, end:read.end});
      re.lastIndex = Math.max(read.end + 1, re.lastIndex + 1);
      if(calls.length > 18) break;
    }
    return calls;
  }
  function collectAdminScreenDefinitions(code){
    code = clean(code); var out=[];
    findPhpCalls(code, /\b(add_menu_page|add_submenu_page)\s*\(/gi).forEach(function(call){
      var args = splitPhpArgs(call.args), sub = /submenu/i.test(call.name);
      var pageTitle = phpArgStringValue(args[sub ? 1 : 0] || ''), menuTitle = phpArgStringValue(args[sub ? 2 : 1] || pageTitle), cap = phpArgStringValue(args[sub ? 3 : 2] || ''), slug = phpArgStringValue(args[sub ? 4 : 3] || ''), callback = phpCallableNameFromArg(args[sub ? 5 : 4] || '');
      out.push({type:sub?'submenu':'menu', pageTitle:pageTitle || menuTitle, menuTitle:menuTitle || pageTitle, capability:cap, slug:slug, callback:callback});
    });
    var seen={}, cleanOut=[];
    out.forEach(function(x){ var key=(x.slug||x.menuTitle||x.callback||'admin')+'|'+(x.callback||''); if(!seen[key]){ seen[key]=1; cleanOut.push(x); } });
    return cleanOut;
  }
  function readBraceBody(code, openIndex){
    code = clean(code); var depth=1, quote='', escNext=false, out='';
    for(var i=openIndex+1;i<code.length;i++){
      var ch=code.charAt(i), prev=code.charAt(i-1);
      if(quote){ out += ch; if(escNext){ escNext=false; continue; } if(ch === '\\'){ escNext=true; continue; } if(ch === quote && prev !== '\\'){ quote=''; } continue; }
      if(ch === '"' || ch === "'"){ quote=ch; out+=ch; continue; }
      if(ch === '{'){ depth++; out+=ch; continue; }
      if(ch === '}'){ depth--; if(depth===0) return out; out+=ch; continue; }
      out+=ch;
    }
    return out;
  }
  function extractPhpFunctionBody(code, fnName){
    code = clean(code); fnName = trim(fnName || ''); if(!fnName) return '';
    var safe = fnName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    var re = new RegExp('(?:public|protected|private|static|final|\\s)*function\\s+' + safe + '\\s*\\([^)]*\\)\\s*\\{', 'i');
    var m = re.exec(code); if(!m) return '';
    var open = code.indexOf('{', m.index); if(open < 0) return '';
    return readBraceBody(code, open);
  }
  function extractAdminOutputHtml(code, callback){
    var body = extractPhpFunctionBody(code, callback);
    if(!body) return '';
    var html = extractPhpMixedHtml(body);
    if(!html && /<[a-z][\s\S]*>/i.test(body)){
      html = body.replace(/<\?php|\?>/gi, ' ');
    }
    html = trim(html);
    if(html.length > 26000) html = html.slice(0,26000) + '<div class="mock-empty">Preview ถูกตัดให้สั้นลงเพื่อความปลอดภัย</div>';
    return html;
  }
  function buildAdminRealChromeHtml(info, admin, adminHtml, fallbackHtml){
    info = info || {}; admin = admin || {};
    var body = adminHtml || fallbackHtml || '';
    var rendered = body ? sanitizeRenderedHtml(body) : '';
    if(!rendered || !/<[a-z][\s\S]*>/i.test(rendered)){
      rendered = '<div class="mock-empty"><b>ยังไม่พบ HTML ของหน้าแอดมินโดยตรง</b><span>ระบบจึงสร้างโครงหน้าแอดมินจาก Plugin Header, Menu, Slug, Capability และ Callback ที่ตรวจพบ เพื่อให้เห็นภาพก่อนติดตั้งจริง</span></div>'+
        '<div class="actual-generated-screen"><header><b>'+esc(admin.menuTitle || info.name || 'Admin Page')+'</b><small>Settings | โดย Thinkb4do | ดูรายละเอียด</small></header><main><div class="actual-widget"><span>Slug</span><strong>'+esc(admin.slug || info.slug || '-')+'</strong></div><div class="actual-widget"><span>Capability</span><strong>'+esc(admin.capability || 'manage_options')+'</strong></div><button type="button">Save Settings</button></main></div>';
    }
    return '<div class="admin-real-note"><b>หากติดตั้งจริง แอดมินจะเห็นหน้าแนวนี้</b><span>แสดงจาก callback: '+esc(admin.callback || '-')+' · menu: '+esc(admin.menuTitle || info.name || '-')+'</span></div>'+
      '<div class="wp-admin-real-frame"><div class="wp-admin-real-bar"><span>WordPress Admin</span><b>'+esc(admin.menuTitle || info.name || 'Admin Page')+'</b></div><div class="wp-admin-real-body"><aside class="wp-admin-real-menu"><span>Dashboard</span><span class="active">'+esc(admin.menuTitle || info.name || 'AiRA')+'</span><span>Settings</span><span>Tools</span></aside><section class="wp-admin-real-content" id="adminRealContent">'+rendered+'</section></div></div><div class="notice" id="adminPreviewNotice">Preview นี้อ่านจากโค้ดจริงก่อนติดตั้ง · คลิกปุ่มในหน้านี้เพื่อจำลอง flow และส่งต่อให้ AiRA ปรับได้</div>';
  }
  function phpWordPressResultHtmlRealerV740(code, label){
    code = clean(code);
    var info = wordpressPackageInfoV740(code, 'php', 0);
    var mixedHtml = extractPhpMixedHtml(code);
    var adminScreens = collectAdminScreenDefinitions(code);
    var admin = adminScreens[0] || {type:'menu', pageTitle:info.name, menuTitle:(info.menus[0] || info.name), capability:'manage_options', slug:info.slug, callback:''};
    var adminHtml = admin.callback ? extractAdminOutputHtml(code, admin.callback) : '';
    var shortcodeDemo = info.shortcodes.length ? '[' + info.shortcodes[0] + ']' : '[' + info.slug.replace(/-/g, '_') + ']';
    var safeMixed = buildActualCodePreviewHtml(info, mixedHtml);
    var adminReal = buildAdminRealChromeHtml(info, admin, adminHtml, mixedHtml);
    function pillList(items, empty){ if(!items || !items.length) return '<span class="muted">'+esc(empty || 'ไม่พบ')+'</span>'; return items.map(function(x){ return '<span class="pill">'+esc(x)+'</span>'; }).join(''); }
    function partList(){ return (info.parts||[]).map(function(p){ return '<span class="part"><b>'+esc(p.label)+'</b><small>'+esc(p.reason||'')+'</small></span>'; }).join(''); }
    function fileRows(){ return info.files.map(function(f){ return '<div class="file"><span>'+esc(f.path)+'</span><b>'+esc(codeByteSizeLabel(f.content||''))+'</b></div>'; }).join(''); }
    var previewAccess = authorizedPreviewAccess();
    var payload = JSON.stringify({name:info.name, slug:info.slug, version:info.version, score:info.score, missing:info.missing, shortcodes:info.shortcodes, rest:info.rest, ajax:info.ajax, adminScreens:adminScreens, adminCallback:admin.callback || '', files:info.files.map(function(f){return f.path;}), previewAccess:previewAccess}).replace(/<\//g,'<\\/');
    var style = 'body{background:#f5f7f5;color:#111;padding:14px;font:14px/1.5 system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.wp-preview{max-width:1100px;margin:auto}.hero{display:grid;grid-template-columns:auto 1fr;gap:14px;border:1px solid rgba(30,107,69,.18);background:linear-gradient(180deg,#fff,#f8fbf9);border-radius:26px;padding:18px;box-shadow:0 18px 46px rgba(17,24,39,.10)}.logo{width:50px;height:50px;border-radius:17px;background:#1E6B45;color:#fff;display:grid;place-items:center;font-weight:900;font-size:23px}.hero h1{font-size:22px;margin:0 0 5px}.hero p{margin:0;color:rgba(17,17,17,.64)}.badges,.pills,.parts{display:flex;flex-wrap:wrap;gap:7px}.badges{margin-top:12px}.badge,.pill{display:inline-flex;align-items:center;border-radius:999px;border:1px solid rgba(17,17,17,.10);background:#fff;padding:6px 9px;font-size:12px;font-weight:800}.badge.ok{background:#111;color:#fff}.badge.green{background:#ecfdf3;color:#166534;border-color:rgba(22,101,52,.18)}.badge.orange{background:#fff7ed;color:#9a3412;border-color:rgba(249,115,22,.22)}.meter{height:9px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-top:9px}.meter i{display:block;height:100%;width:'+String(info.score)+'%;background:#1E6B45}.tabs{display:flex;flex-wrap:wrap;gap:8px;margin:14px 0}.tabs button{background:#fff;color:#111;border:1px solid rgba(17,17,17,.12);box-shadow:0 8px 20px rgba(17,24,39,.06);border-radius:999px;padding:9px 13px;font-weight:900}.tabs button.active{background:#1E6B45;color:#fff;border-color:#1E6B45}.screen{display:none;border:1px solid rgba(17,17,17,.10);background:#fff;border-radius:24px;padding:16px;box-shadow:0 12px 32px rgba(17,24,39,.07)}.screen.active{display:block}.screen h2{font-size:16px;margin:0 0 10px}.authorized-preview-dock,.preview-next-dock{position:sticky;top:0;z-index:6;margin:12px 0;border:1px solid rgba(30,107,69,.18);background:rgba(255,255,255,.95);backdrop-filter:blur(10px);border-radius:22px;padding:12px;box-shadow:0 14px 34px rgba(17,24,39,.10);display:grid;gap:8px}.authorized-preview-dock b,.preview-next-dock b{display:block;font-size:14px;color:#111}.authorized-preview-dock small,.preview-next-dock small{display:block;color:#374151;font-size:12px}.authorized-preview-meta{display:flex;flex-wrap:wrap;gap:7px}.authorized-preview-meta span{border:1px solid rgba(30,107,69,.20);background:#ecfdf3;color:#14532d;border-radius:999px;padding:6px 9px;font-size:11.5px;font-weight:900}.preview-next-form{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:8px;align-items:center}.preview-next-form input{min-height:40px;border:1px solid rgba(17,17,17,.12);border-radius:999px;background:#f8faf9;padding:9px 13px;font:inherit;color:#111;outline:none}.preview-next-form input:focus{border-color:rgba(30,107,69,.45);box-shadow:0 0 0 3px rgba(30,107,69,.10)}.preview-next-form button{min-height:40px;border:0;border-radius:999px;background:#1E6B45;color:#fff;padding:9px 13px;font-weight:900}.preview-next-form button.secondary{background:#111;color:#fff}.preview-next-status{font-size:11.5px;color:#166534;min-height:16px}.admin-real-note{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;border:1px solid rgba(30,107,69,.18);background:#f0fdf4;border-radius:18px;padding:12px;margin-bottom:12px}.admin-real-note b{display:block;color:#14532d}.admin-real-note span{font-size:12px;color:rgba(17,17,17,.65)}.wp-admin-real-frame{border:1px solid rgba(17,17,17,.12);border-radius:20px;overflow:hidden;background:#f6f7f7}.wp-admin-real-bar{min-height:42px;background:#1d2327;color:#fff;display:flex;justify-content:space-between;gap:12px;align-items:center;padding:0 14px;font-weight:900}.wp-admin-real-body{display:grid;grid-template-columns:180px minmax(0,1fr);min-height:340px}.wp-admin-real-menu{background:#111827;color:#e5e7eb;padding:12px;display:grid;gap:8px;align-content:start}.wp-admin-real-menu span{border-radius:10px;padding:8px;background:rgba(255,255,255,.06)}.wp-admin-real-menu span.active{background:#1E6B45;color:#fff}.wp-admin-real-content{background:#fff;padding:18px;overflow:auto}.wp-admin-real-content h1,.wp-admin-real-content h2{margin-top:0}.wp-admin-real-content input,.wp-admin-real-content select,.wp-admin-real-content textarea{max-width:100%;border:1px solid rgba(17,17,17,.16);border-radius:10px;padding:9px}.wp-admin-real-content button,.wp-admin-real-content .button,.wp-admin-real-content input[type=submit]{display:inline-flex;align-items:center;border:0;border-radius:999px;background:#1E6B45;color:#fff;padding:9px 13px;text-decoration:none;font-weight:800}.notice{margin-top:12px;border-radius:14px;background:#ecfdf3;border:1px solid rgba(30,107,69,.18);padding:10px;color:#166534}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.card{border:1px solid rgba(17,17,17,.10);background:#fff;border-radius:18px;padding:14px}.part{border:1px solid rgba(17,17,17,.10);border-radius:16px;padding:10px;background:#f8faf9;display:grid;gap:3px}.part small,.muted{color:rgba(17,17,17,.58)}.front{border:1px dashed rgba(30,107,69,.26);border-radius:18px;background:#fbfffc;padding:16px}.front-output{margin-top:12px;border-radius:18px;background:#111;color:#fff;padding:16px}.rendered-html{border:1px solid rgba(17,17,17,.10);border-radius:18px;padding:14px;background:#fff;overflow:auto}.actual-code-preview{border:1px solid rgba(30,107,69,.16);background:#fff;border-radius:20px;padding:12px;display:grid;gap:12px}.actual-code-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}.actual-code-head b{font-size:14px}.actual-code-head span{font-size:12px;color:rgba(17,17,17,.58)}.actual-code-stage{border:1px dashed rgba(30,107,69,.22);border-radius:16px;padding:14px;background:#fbfffc;overflow:auto}.actual-generated-screen{border:1px solid rgba(17,17,17,.10);border-radius:18px;overflow:hidden;background:#f8faf9;margin-top:10px}.actual-generated-screen header{display:grid;gap:3px;background:#1E6B45;color:#fff;padding:14px}.actual-generated-screen header small{opacity:.86}.actual-generated-screen main{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;padding:14px}.actual-widget{border:1px solid rgba(17,17,17,.10);background:#fff;border-radius:14px;padding:10px;display:grid;gap:3px}.actual-widget span{font-size:12px;color:rgba(17,17,17,.58)}.mock-empty{display:grid;gap:6px;border:1px dashed rgba(249,115,22,.36);background:#fff7ed;color:#9a3412;border-radius:16px;padding:14px}.api-console,.live-console{border-radius:18px;background:#111;color:#e5e7eb;padding:14px;white-space:pre-wrap;overflow:auto;min-height:122px}.file{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid rgba(17,17,17,.06);padding:8px 0}.file:last-child{border-bottom:0}.ready-list{display:grid;gap:8px}.safe{margin-top:14px;border:1px dashed rgba(17,17,17,.18);background:#fff;border-radius:18px;padding:12px;color:rgba(17,17,17,.66);font-size:13px}.safe b{display:block;color:#111;margin-bottom:4px}.live-strip{display:grid;gap:8px}.live-step{display:flex;justify-content:space-between;gap:12px;border:1px solid rgba(17,17,17,.10);border-radius:14px;padding:10px;background:#f8faf9}.live-step b{color:#1E6B45}.live-step.warn b{color:#9a3412}@media(max-width:720px){body{padding:10px}.hero{grid-template-columns:1fr}.authorized-preview-dock,.preview-next-dock{position:relative;border-radius:18px}.preview-next-form{grid-template-columns:1fr}.preview-next-form button{width:100%}.wp-admin-real-body{grid-template-columns:1fr}.wp-admin-real-menu{grid-template-columns:repeat(2,minmax(0,1fr))}.grid{grid-template-columns:1fr}.screen{padding:13px;border-radius:20px}.admin-real-note{display:grid}.actual-generated-screen main{grid-template-columns:1fr}}';
    var script = '<script>(function(){var payload='+payload+';var q=function(s,r){return (r||document).querySelector(s)},qa=function(s,r){return Array.prototype.slice.call((r||document).querySelectorAll(s))};function post(action,extra){try{parent.postMessage({source:"aira-preinstall-sandbox-v750",action:action,payload:payload,extra:extra||{}} ,"*")}catch(e){}}function log(text){var c=q("#liveConsole");if(c)c.textContent=text}qa(".tabs button").forEach(function(b){b.addEventListener("click",function(){qa(".tabs button").forEach(function(x){x.classList.remove("active")});qa(".screen").forEach(function(x){x.classList.remove("active")});b.classList.add("active");var screen=q("[data-screen="+JSON.stringify(b.getAttribute("data-tab"))+"]");if(screen)screen.classList.add("active")})});var n=q("#adminPreviewNotice"),api=q("#apiTest"),consoleBox=q("#apiConsole"),debug=q("#liveDebug"),adminReal=q("#adminRealContent");if(adminReal){adminReal.addEventListener("click",function(e){var t=e.target&&e.target.closest?e.target.closest("button,a,input[type=submit]"):null;if(!t)return;if(e&&e.preventDefault)e.preventDefault();if(n)n.textContent="จำลองการกดปุ่มในหน้าแอดมินจริงแล้ว · ส่งสถานะกลับ Live QC ได้ · ไม่มีการเขียนฐานข้อมูล";post("sandbox-run",{area:"admin-real",label:(t.textContent||t.value||"admin action").trim().slice(0,80)})})}if(api)api.onclick=function(){if(consoleBox)consoleBox.textContent="Status: simulated success\nHTTP: 200\nPermission: authorized preview only\nCapability: "+((payload.previewAccess&&payload.previewAccess.capability)||"manage_options")+"\nResponse: { ok: true, source: authorized preinstall sandbox }";post("sandbox-api")};if(debug)debug.onclick=function(){var missing=(payload.missing&&payload.missing.length)?payload.missing.join(", "):"ไม่พบจุดขาดหลัก";log("Authorized Live QC: "+payload.score+"/100\nPlugin: "+payload.name+"\nAdmin callback: "+(payload.adminCallback||"-")+"\nVersion: "+payload.version+"\nCapability: "+((payload.previewAccess&&payload.previewAccess.capability)||"manage_options")+"\nMissing: "+missing+"\nScope: ทดลองใช้งานก่อนติดตั้ง เฉพาะผู้มีสิทธิ์ ไม่ execute PHP ไม่เขียน DB");post("sandbox-debug")};})();<\/script>';
    var clickLabScript = "<script>(function(){\nvar payload=" + payload + ";\nvar q=function(s,r){return (r||document).querySelector(s)};\nvar qa=function(s,r){return Array.prototype.slice.call((r||document).querySelectorAll(s))};\nfunction post(action,extra){try{parent.postMessage({source:\"aira-preinstall-sandbox-v750\",action:action,payload:payload,extra:extra||{}} ,\"*\")}catch(e){}}\nfunction addStyle(){if(q(\"#airaInteractivePreviewPatch\"))return;var st=document.createElement(\"style\");st.id=\"airaInteractivePreviewPatch\";st.textContent=\".wp-admin-real-content{max-width:100%!important;overflow:auto!important}.wp-admin-real-content *{box-sizing:border-box!important;max-width:100%!important}.wp-admin-real-content .wrap,.wp-admin-real-content form,.wp-admin-real-content table{max-width:100%!important;overflow:auto!important}.wp-admin-real-content button,.wp-admin-real-content .button,.wp-admin-real-content input[type=submit],.wp-admin-real-content a.button{width:auto!important;min-width:0!important;max-width:100%!important;min-height:42px!important;margin:4px 6px 4px 0!important;padding:9px 13px!important;white-space:normal!important;overflow-wrap:anywhere!important;line-height:1.25!important;border-radius:999px!important;background:#1E6B45!important;color:#fff!important;border:1px solid #14532D!important;text-decoration:none!important;box-shadow:0 5px 14px rgba(15,23,42,.12)!important;font-weight:900!important;opacity:1!important;text-shadow:none!important}.wp-admin-real-content button[disabled],.wp-admin-real-content .button.disabled,.wp-admin-real-content [aria-disabled=true],.wp-admin-real-content input[disabled]{opacity:1!important;background:#E5E7EB!important;color:#374151!important;border-color:#94A3B8!important;box-shadow:none!important}.wp-admin-real-content button.secondary,.wp-admin-real-content .button-secondary{background:#F97316!important;color:#fff!important;border-color:#9A3412!important}.wp-admin-real-content input,.wp-admin-real-content select,.wp-admin-real-content textarea{width:auto!important;max-width:100%!important;background:#fff!important;color:#111827!important;border:1px solid #CBD5D1!important}.wp-admin-real-content h1,.wp-admin-real-content h2,.wp-admin-real-content h3{color:#111827!important;line-height:1.25!important}.wp-admin-real-content p,.wp-admin-real-content small,.wp-admin-real-content label,.wp-admin-real-content td,.wp-admin-real-content th{color:#374151!important}.aira-preview-click-target{outline:3px solid rgba(249,115,22,.72)!important;outline-offset:2px!important}.aira-tested-click{box-shadow:0 0 0 4px rgba(30,107,69,.18)!important}.preinstall-click-lab{margin-top:12px;border:1px solid #86B89C;background:linear-gradient(180deg,#F0FDF4,#FFFFFF);border-radius:18px;padding:12px;display:grid;gap:9px;color:#111827}.preinstall-click-lab b{color:#14532D}.preinstall-click-lab small{color:#374151}.preinstall-click-actions{display:flex;flex-wrap:wrap;gap:8px}.preinstall-click-actions button{border:1px solid #14532D;border-radius:999px;background:#1E6B45;color:#fff;padding:9px 13px;font-weight:900}.preinstall-click-actions button.secondary{background:#111827;border-color:#111827}.preinstall-click-result{border:1px solid #D6DDD8;background:#fff;border-radius:14px;padding:9px;color:#111827;font-size:12px;white-space:pre-wrap}\";document.head.appendChild(st)}\nfunction targets(){return qa(\"#adminRealContent button,#adminRealContent a,#adminRealContent input,#adminRealContent select,#adminRealContent textarea,#adminRealContent [role=button],#adminRealContent .button\").filter(function(el){return el&&el.offsetParent!==null&&!el.disabled&&el.type!==\"hidden\"})}\nfunction label(el){return ((el.textContent||el.value||el.getAttribute(\"aria-label\")||el.getAttribute(\"name\")||el.tagName||\"item\")+\"\").trim().replace(/\\s+/g,\" \").slice(0,70)}\nfunction result(text){var r=q(\"#clickLabResult\");if(r)r.textContent=text}\nfunction buildLab(){var note=q(\"#adminPreviewNotice\");if(!note||q(\"#preinstallClickLab\"))return;var lab=document.createElement(\"div\");lab.id=\"preinstallClickLab\";lab.className=\"preinstall-click-lab\";lab.innerHTML=\"<div><b>Interactive Pre-install Test</b><small> \u0e17\u0e14\u0e25\u0e2d\u0e07\u0e01\u0e14\u0e23\u0e30\u0e1a\u0e1a\u0e17\u0e35\u0e48\u0e2a\u0e23\u0e49\u0e32\u0e07\u0e44\u0e14\u0e49\u0e08\u0e23\u0e34\u0e07\u0e43\u0e19 Sandbox \u0e01\u0e48\u0e2d\u0e19\u0e15\u0e34\u0e14\u0e15\u0e31\u0e49\u0e07 \u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e25\u0e14\u0e01\u0e32\u0e23\u0e41\u0e01\u0e49\u0e0b\u0e49\u0e33</small></div><div class='preinstall-click-actions'><button type='button' id='sandboxTestAll'>\u0e17\u0e14\u0e2a\u0e2d\u0e1a\u0e1b\u0e38\u0e48\u0e21\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14</button><button type='button' id='sandboxFill'>\u0e40\u0e15\u0e34\u0e21\u0e02\u0e49\u0e2d\u0e21\u0e39\u0e25\u0e08\u0e33\u0e25\u0e2d\u0e07</button><button type='button' class='secondary' id='sandboxReset'>\u0e23\u0e35\u0e40\u0e0b\u0e47\u0e15\u0e1c\u0e25\u0e17\u0e14\u0e2a\u0e2d\u0e1a</button></div><div class='preinstall-click-result' id='clickLabResult'>\u0e1e\u0e23\u0e49\u0e2d\u0e21\u0e17\u0e14\u0e2a\u0e2d\u0e1a \u00b7 \u0e1e\u0e1a\u0e2d\u0e07\u0e04\u0e4c\u0e1b\u0e23\u0e30\u0e01\u0e2d\u0e1a\u0e17\u0e35\u0e48\u0e01\u0e14/\u0e01\u0e23\u0e2d\u0e01\u0e44\u0e14\u0e49: \"+targets().length+\" \u0e08\u0e38\u0e14</div>\";note.insertAdjacentElement(\"afterend\",lab)}\nfunction mark(el,i,total){if(!el)return;el.classList.add(\"aira-preview-click-target\",\"aira-tested-click\");try{el.scrollIntoView({block:\"center\",inline:\"nearest\",behavior:\"smooth\"})}catch(e){}result(\"\u0e01\u0e33\u0e25\u0e31\u0e07\u0e17\u0e14\u0e2a\u0e2d\u0e1a \"+(i+1)+\"/\"+total+\": \"+label(el));setTimeout(function(){el.classList.remove(\"aira-preview-click-target\")},450)}\nfunction fillSample(){var fields=qa(\"#adminRealContent input,#adminRealContent textarea,#adminRealContent select\").filter(function(el){return !el.readOnly&&!el.disabled&&el.type!==\"hidden\"});fields.forEach(function(el,i){if(el.tagName===\"SELECT\"){if(el.options&&el.options.length>1)el.selectedIndex=1}else if(el.type===\"checkbox\"||el.type===\"radio\"){el.checked=true}else{el.value=(el.type===\"email\"?\"preview@example.com\":\"\u0e17\u0e14\u0e2a\u0e2d\u0e1a\u0e01\u0e48\u0e2d\u0e19\u0e15\u0e34\u0e14\u0e15\u0e31\u0e49\u0e07 \"+(i+1))}try{el.dispatchEvent(new Event(\"input\",{bubbles:true}));el.dispatchEvent(new Event(\"change\",{bubbles:true}))}catch(e){}});result(\"\u0e40\u0e15\u0e34\u0e21\u0e02\u0e49\u0e2d\u0e21\u0e39\u0e25\u0e08\u0e33\u0e25\u0e2d\u0e07\u0e41\u0e25\u0e49\u0e27 \"+fields.length+\" \u0e0a\u0e48\u0e2d\u0e07 \u00b7 \u0e22\u0e31\u0e07\u0e44\u0e21\u0e48\u0e40\u0e02\u0e35\u0e22\u0e19\u0e10\u0e32\u0e19\u0e02\u0e49\u0e2d\u0e21\u0e39\u0e25\");post(\"sandbox-fill\",{count:fields.length})}\nfunction testAll(){var list=targets();if(!list.length){result(\"\u0e44\u0e21\u0e48\u0e1e\u0e1a\u0e1b\u0e38\u0e48\u0e21\u0e2b\u0e23\u0e37\u0e2d\u0e1f\u0e2d\u0e23\u0e4c\u0e21\u0e43\u0e2b\u0e49\u0e17\u0e14\u0e2a\u0e2d\u0e1a\");post(\"sandbox-test-all\",{count:0});return}var i=0;function step(){var el=list[i];mark(el,i,list.length);if(el.tagName===\"A\"||el.type===\"submit\"||el.tagName===\"BUTTON\"||el.classList.contains(\"button\")||el.getAttribute(\"role\")===\"button\"){try{el.dispatchEvent(new MouseEvent(\"click\",{bubbles:true,cancelable:true,view:window}))}catch(e){}}i++;if(i<list.length){setTimeout(step,180)}else{setTimeout(function(){result(\"Click Test \u0e1c\u0e48\u0e32\u0e19 \"+list.length+\" \u0e08\u0e38\u0e14 \u00b7 Preview \u0e1e\u0e23\u0e49\u0e2d\u0e21\u0e43\u0e0a\u0e49\u0e40\u0e1b\u0e47\u0e19\u0e14\u0e48\u0e32\u0e19\u0e01\u0e48\u0e2d\u0e19\u0e15\u0e34\u0e14\u0e15\u0e31\u0e49\u0e07\");post(\"sandbox-test-all\",{count:list.length,passed:true})},220)}}step()}\nfunction reset(){qa(\".aira-tested-click,.aira-preview-click-target\").forEach(function(el){el.classList.remove(\"aira-tested-click\",\"aira-preview-click-target\")});result(\"\u0e23\u0e35\u0e40\u0e0b\u0e47\u0e15\u0e1c\u0e25\u0e17\u0e14\u0e2a\u0e2d\u0e1a\u0e41\u0e25\u0e49\u0e27 \u00b7 \u0e01\u0e14\u0e17\u0e14\u0e2a\u0e2d\u0e1a\u0e2d\u0e35\u0e01\u0e04\u0e23\u0e31\u0e49\u0e07\u0e01\u0e48\u0e2d\u0e19\u0e15\u0e34\u0e14\u0e15\u0e31\u0e49\u0e07\");post(\"sandbox-reset\")}\ndocument.addEventListener(\"click\",function(e){var t=e.target&&e.target.closest?e.target.closest(\"#adminRealContent button,#adminRealContent a,#adminRealContent input[type=submit],#adminRealContent [role=button],#adminRealContent .button\"):null;if(!t)return;if(e.preventDefault)e.preventDefault();t.classList.add(\"aira-tested-click\");result(\"\u0e01\u0e14\u0e08\u0e23\u0e34\u0e07\u0e43\u0e19 Preview: \"+label(t)+\" \u00b7 \u0e2a\u0e48\u0e07\u0e2a\u0e16\u0e32\u0e19\u0e30\u0e01\u0e25\u0e31\u0e1a Live QC \u0e41\u0e25\u0e49\u0e27\");post(\"sandbox-click\",{label:label(t),count:1})},true);\naddStyle();buildLab();var f=q(\"#sandboxFill\"),ta=q(\"#sandboxTestAll\"),rs=q(\"#sandboxReset\");if(f)f.onclick=fillSample;if(ta)ta.onclick=testAll;if(rs)rs.onclick=reset;\n})();</script>";
    return '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'+previewBaseStyle()+'<style>'+style+'</style></head><body><main class="wp-preview"><section class="hero"><div class="logo">A</div><div><h1>'+esc(info.name)+'</h1><p>'+esc(info.desc)+'</p><div class="badges"><span class="badge ok">Admin Real Preview</span><span class="badge green">เห็นก่อนติดตั้ง</span><span class="badge orange">ยังไม่ execute PHP / ไม่เขียน DB</span></div><div class="meter"><i></i></div></div></section><section class="authorized-preview-dock" aria-label="Authorized Preview Gate"><div><b>Authorized Preview · เฉพาะผู้มีสิทธิ์ก่อนติดตั้ง</b><small>เปิดให้ผู้ใช้ WordPress ที่ผ่าน capability เท่านั้นเห็นหน้าจริงจากโค้ดก่อนส่งติดตั้ง/อัปเกรด · ใช้งานจำลองได้แต่ไม่ execute PHP และไม่เขียนฐานข้อมูล</small></div><div class="authorized-preview-meta"><span>Policy: '+esc(previewAccess.policy)+'</span><span>Capability: '+esc(previewAccess.capability)+'</span><span>User ID: '+esc(previewAccess.userId || '-')+'</span><span>Fingerprint: '+esc(previewAccess.fingerprint || 'local')+'</span></div></section><div class="tabs"><button type="button" class="active" data-tab="admin-real">Admin จริง</button><button type="button" data-tab="front">Frontend</button><button type="button" data-tab="builder">HTML/Module</button><button type="button" data-tab="api">REST/AJAX</button><button type="button" data-tab="package">Package</button><button type="button" data-tab="live">Live Debug</button><button type="button" data-tab="qa">QC</button></div><section class="screen active" data-screen="admin-real"><h2>หน้าที่แอดมินจะเห็นหากติดตั้งจริง</h2>'+adminReal+'</section><section class="screen" data-screen="front"><h2>ทดลอง Shortcode / Frontend</h2><div class="front"><b>Shortcode ที่ตรวจพบ</b><div class="pills">'+pillList(info.shortcodes, 'ยังไม่พบ shortcode ในโค้ดนี้')+'</div><p class="muted">ตัวอย่างเรียกใช้งาน: <b>'+esc(shortcodeDemo)+'</b></p><div class="front-output"><b>'+esc(info.name)+'</b><br><span>นี่คือผลลัพธ์ฝั่งหน้าเว็บที่ประกอบจากโค้ด/shortcode ก่อนติดตั้งจริง</span></div></div></section><section class="screen" data-screen="builder"><h2>HTML / Module / Widget / Block จากโค้ด</h2><div class="parts">'+partList()+'</div><div class="rendered-html" style="margin-top:12px">'+safeMixed+'</div></section><section class="screen" data-screen="api"><h2>REST / AJAX Tester จำลอง</h2><div class="grid"><div class="card"><h3>REST Routes</h3><div class="pills">'+pillList(info.rest, 'ไม่พบ REST route')+'</div></div><div class="card"><h3>AJAX Actions</h3><div class="pills">'+pillList(info.ajax, 'ไม่พบ AJAX action')+'</div></div></div><pre class="api-console" id="apiConsole">Status: ready\nHTTP: simulated 200\nEndpoint: admin-ajax.php / wp-json\nResult: ยังไม่เรียก server จริงก่อนติดตั้ง</pre><div class="btn-row"><button type="button" id="apiTest">Test Simulated API</button></div></section><section class="screen" data-screen="package"><h2>ตรวจแพ็กก่อนโหลด / ก่อนติดตั้ง</h2><div class="grid"><div class="card"><h3>Package Manifest</h3><div class="file"><span>Slug</span><b>'+esc(info.slug)+'</b></div><div class="file"><span>Main file</span><b>'+esc(info.main)+'</b></div><div class="file"><span>Version</span><b>'+esc(info.version)+'</b></div></div><div class="card"><h3>Files ที่จะอยู่ใน ZIP</h3>'+fileRows()+'</div></div><p class="safe"><b>ก่อนโหลดแพ็ก</b>แพ็ก ZIP จะรวมไฟล์หลัก readme manifest รายงาน pre-install และ live debug note ให้ตรวจต่อได้</p></section><section class="screen" data-screen="live"><h2>Live Debug ก่อนติดตั้ง</h2><div class="live-strip"><div class="live-step"><span>Client Sandbox QC</span><b>'+esc(info.score)+'/100</b></div><div class="live-step warn"><span>ต้องเติม/ตรวจ</span><b>'+esc(info.missing.join(', ') || 'พร้อมระดับพื้นฐาน')+'</b></div><div class="live-step"><span>Runtime</span><b>simulate only · no DB write</b></div></div><pre class="live-console" id="liveConsole">กด “ตรวจ Live QC” เพื่อจำลอง QC และส่งสถานะกลับไปที่ code block ด้านนอก</pre><div class="btn-row"><button type="button" id="liveDebug">ตรวจ Live QC</button></div></section><section class="screen" data-screen="qa"><h2>QC ก่อนติดตั้งจริง</h2><ul class="ready-list"><li>Readiness: <b>'+esc(info.score)+'/100</b></li><li>หน้าแอดมินที่ตรวจพบ: <b>'+esc((adminScreens.length || 0) + ' หน้า')+'</b></li><li>Callback หลัก: <b>'+esc(admin.callback || '-')+'</b></li><li>สิ่งที่ควรเติม: <b>'+esc(info.missing.join(', ') || 'พร้อมระดับพื้นฐาน')+'</b></li><li>แนะนำ: ทดสอบบน staging site ก่อนกด Activate</li></ul></section><section class="safe"><b>ขอบเขตความจริงก่อนติดตั้ง</b>Preview นี้อ่านโครงสร้างและ HTML จากโค้ดจริงเพื่อให้เห็นหน้าที่แอดมินน่าจะเห็นหลังติดตั้ง และส่งสถานะ Live QC กลับไปที่ code block ได้ แต่ยังไม่ execute PHP ดิบ ไม่เขียนฐานข้อมูล ไม่เรียก hook จริง และไม่ Activate ปลั๊กอินอัตโนมัติ เพื่อความปลอดภัย</section></main>'+script+clickLabScript+'</body></html>';
  }
  var __airaCrcTable = null;
  function crc32Bytes(bytes){
    if(!__airaCrcTable){
      __airaCrcTable = []; for(var n=0;n<256;n++){ var c=n; for(var k=0;k<8;k++){ c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1); } __airaCrcTable[n]=c>>>0; }
    }
    var crc = 0 ^ -1; for(var i=0;i<bytes.length;i++){ crc = (crc >>> 8) ^ __airaCrcTable[(crc ^ bytes[i]) & 0xFF]; }
    return (crc ^ -1) >>> 0;
  }
  function u16(n){ return [n & 255, (n >>> 8) & 255]; }
  function u32(n){ return [n & 255, (n >>> 8) & 255, (n >>> 16) & 255, (n >>> 24) & 255]; }
  function concatUint8(parts){ var len=0; parts.forEach(function(p){ len += p.length; }); var out=new Uint8Array(len), off=0; parts.forEach(function(p){ out.set(p, off); off += p.length; }); return out; }
  function strBytes(s){ try { return new TextEncoder().encode(String(s||'')); } catch(e){ var arr=[]; s=String(s||''); for(var i=0;i<s.length;i++){ arr.push(s.charCodeAt(i)&255); } return new Uint8Array(arr); } }
  function bytesFromArray(a){ return new Uint8Array(a); }
  function makeZipBlob(files){
    var localParts=[], centralParts=[], offset=0;
    var now = new Date(); var dosTime = ((now.getHours() & 31) << 11) | ((now.getMinutes() & 63) << 5) | ((Math.floor(now.getSeconds()/2)) & 31); var dosDate = (((now.getFullYear()-1980) & 127) << 9) | (((now.getMonth()+1) & 15) << 5) | (now.getDate() & 31);
    files.forEach(function(f){
      var nameB=strBytes(f.path), dataB=strBytes(f.content||''), crc=crc32Bytes(dataB), size=dataB.length;
      var local=bytesFromArray([].concat(u32(0x04034b50),u16(20),u16(0),u16(0),u16(dosTime),u16(dosDate),u32(crc),u32(size),u32(size),u16(nameB.length),u16(0)));
      localParts.push(local,nameB,dataB);
      var central=bytesFromArray([].concat(u32(0x02014b50),u16(20),u16(20),u16(0),u16(0),u16(dosTime),u16(dosDate),u32(crc),u32(size),u32(size),u16(nameB.length),u16(0),u16(0),u16(0),u16(0),u32(0),u32(offset)));
      centralParts.push(central,nameB);
      offset += local.length + nameB.length + dataB.length;
    });
    var centralSize = centralParts.reduce(function(s,p){return s+p.length;},0);
    var end=bytesFromArray([].concat(u32(0x06054b50),u16(0),u16(0),u16(files.length),u16(files.length),u32(centralSize),u32(offset),u16(0)));
    return new Blob([concatUint8(localParts.concat(centralParts).concat([end]))], {type:'application/zip'});
  }
  function downloadBlob(filename, blob){
    var a=d.createElement('a'); a.href=URL.createObjectURL(blob); a.download=filename; d.body.appendChild(a); a.click(); setTimeout(function(){ URL.revokeObjectURL(a.href); if(a.parentNode) a.parentNode.removeChild(a); }, 400);
  }
  function downloadCodePackageFromCard(card){
    var data = codeDataFromCard(card); if(!data.code){ toast('ไม่พบโค้ดสำหรับโหลดแพ็ก','error'); return; }
    if(card && (card.getAttribute('data-real-preview-seen') !== '1' || card.getAttribute('data-interactive-preview-tested') !== '1')){ runPreinstallSandboxFromCard(card); setCodeNextStatus(card, 'เปิด Preview ก่อนส่งมอบแพ็กแล้ว · ทดลองกดปุ่ม/ฟอร์มใน Sandbox ก่อน แล้วกดโหลดแพ็กอีกครั้ง', 'is-next-sent'); toast('ต้องทดลองกดใน Preview ก่อนโหลดแพ็ก เพื่อลดการปรับซ้ำ', 'warn'); return; }
    var isWp = isPhpLikeCode(data.lang, data.code) || /wordpress|wp_|add_action|add_shortcode|register_block_type|elementor/i.test(data.code);
    var info = isWp ? wordpressPackageInfoV740(data.code, data.lang, data.index) : null;
    var baseName = info ? info.slug : ('aira-code-package-' + String((data.index||0)+1));
    var files = info ? info.files : [{path:baseName + '/' + downloadNameForCode(data.lang, data.index), content:data.code},{path:baseName + '/README.txt', content:'AiRA Studio code package\nLanguage: '+data.lang+'\nUse Result/Preview before using this package.\n'}];
    files.push({path:baseName + '/docs/package-qc.txt', content:'AiRA Studio Package QC\nGenerated: ' + new Date().toISOString() + '\nPre-install checked in chat UI: yes\nReal preview before delivery/install: yes\nInstall automatically: no\nActivate automatically: no\n'});
    var blob = makeZipBlob(files);
    downloadBlob(baseName + '.zip', blob);
    if(card){ card.setAttribute('data-package-downloaded','1'); var line=card.querySelector('.aira-code-version-line'); if(line){ line.textContent = clean(line.textContent) + ' · โหลดแพ็ก ZIP แล้ว'; } renderCodeRealityPanel(card); }
    toast('โหลดแพ็ก ZIP แล้ว: ' + baseName + '.zip', 'ok');
  }


  function systemBuilderProjectType(data){
    data = data || {}; var code = clean(data.code || ''), lang = normalizeCodeLang(data.lang || 'text');
    var lower = code.toLowerCase();
    if(isPhpLikeCode(lang, code) || /wordpress|wp_|add_action|add_shortcode|elementor|gutenberg|register_block_type/.test(lower)) return 'WordPress Plugin / Module';
    if(/react|jsx|tsx|useState|export default|tailwind/.test(lower) || lang === 'javascript' || lang === 'typescript') return 'Web App / UI Component';
    if(/express|fastify|django|flask|laravel|api|rest|graphql|endpoint|server/.test(lower)) return 'API / Backend System';
    if(/flutter|dart|android|ios|swift|kotlin|mobile/.test(lower)) return 'Mobile App';
    if(/html|css|<main|<section|<div/.test(lower) || lang === 'html' || lang === 'css') return 'Frontend Page / Landing System';
    return 'General Code System';
  }
  function systemBuilderReadinessScore(data, audit){
    data = data || {}; audit = audit || codeStaticAudit(data);
    var score = Number(audit.score || 0);
    if(clean(data.code).length > 900) score += 8;
    if(blocksCanRenderResult((data.card && data.card.__airaPreviewBlocks) || [{lang:data.lang, code:data.code}], data.lang, data.code)) score += 6;
    if(/readme|docs|manual|คู่มือ|วิธีใช้/i.test(data.code)) score += 4;
    return clamp(score, 0, 100);
  }
  function systemBuilderFilePlan(data, parts, isWp){
    data = data || {}; parts = parts || detectCodeParts(data.lang, data.code); var type = systemBuilderProjectType(data);
    if(isWp){
      var info = wordpressPackageInfoV740(data.code, data.lang, data.index || 0);
      return info.files.map(function(f){ return f.path; });
    }
    var base = asciiSlug(type.replace(/\s*\/\s*/g,'-'), 'aira-system');
    var files = [base + '/' + downloadNameForCode(data.lang || 'text', data.index || 0), base + '/README.md', base + '/docs/usage-guide.md', base + '/docs/qc-checklist.txt', base + '/manifest.json'];
    if(parts.some(function(p){ return p.key === 'component' || p.key === 'ui'; })) files.push(base + '/components/GeneratedComponent.txt');
    if(parts.some(function(p){ return p.key === 'api' || p.key === 'rest' || p.key === 'ajax'; })) files.push(base + '/api/contract.json');
    return uniqueList(files);
  }
  function systemBuilderReportFromCard(card){
    var data = codeDataFromCard(card); data.card = card;
    var code = clean(data.code || ''), lang = normalizeCodeLang(data.lang || 'text'), parts = detectCodeParts(lang, code);
    var isWp = isPhpLikeCode(lang, code) || /wordpress|wp_|add_action|add_shortcode|register_block_type|elementor|gutenberg/i.test(code);
    var audit = codeStaticAudit(data);
    var score = systemBuilderReadinessScore(data, audit);
    var type = systemBuilderProjectType(data);
    var filePlan = systemBuilderFilePlan(data, parts, isWp);
    var missing = audit.missing && audit.missing.length ? audit.missing : ['เพิ่มเอกสารใช้งานจริง', 'ทดสอบ flow บนอุปกรณ์จริง', 'เชื่อม API/ฐานข้อมูลเมื่อมี key และ server จริง'];
    return [
      'AiRA Code Block System Builder Report',
      'Version: ' + VERSION,
      'Generated: ' + new Date().toISOString(),
      'Project Type: ' + type,
      'Source File: ' + downloadNameForCode(lang, data.index || 0),
      'Readiness: ' + score + '/100',
      '',
      '1) ของเดิมยังอยู่',
      '- คง code block เดิม: Copy / Download / Preview / Debug / Package / Upgrade',
      '- คงโค้ดต้นฉบับใน block นี้ ไม่เขียนทับโดยอัตโนมัติ',
      '- คงระบบ Preview ก่อนติดตั้ง และ Live QC ก่อนส่งมอบ',
      '',
      '2) สิ่งที่เพิ่ม',
      '- System Builder Layer: แปลง code block นี้เป็นแผนสร้างระบบที่ต่อยอดได้',
      '- Chat Feature Prompt: เตรียมคำสั่งให้ AiRA คุยต่อจาก code block นี้ได้ใน Composer',
      '- App Builder Plan: แยก Dashboard / User / API / Data / Security / Files',
      '- Package/File Status: สรุปรายการไฟล์ที่ควรมีในระบบ',
      '',
      '3) วิธีใช้',
      '- กด “สร้างระบบ” ใต้ code block',
      '- ตรวจรายงาน 5 ส่วน: ของเดิม / สิ่งเพิ่ม / วิธีใช้ / ตรวจบั๊ก / ไฟล์สถานะ',
      '- กด “ใส่ Composer” เพื่อให้ AiRA สร้างหรืออัปเกรดระบบต่อจาก code block เดิม',
      '- กด “เปิด Preview+QC” ก่อนติดตั้งหรือโหลดแพ็กจริง',
      '',
      '4) ตรวจบั๊ก',
      '- Client QC: ' + (audit.score || 0) + '/100 · ' + (audit.status || '-'),
      '- Missing: ' + (missing.join(', ') || '-'),
      '- ต้องทดสอบ responsive, browser, buttons, forms, preview, package และ install/upgrade gate ก่อนใช้งานจริง',
      '',
      '5) ไฟล์/สถานะ',
      filePlan.map(function(f){ return '- ' + f; }).join('\n'),
      '',
      'Next Prompt',
      systemBuilderComposerPrompt(data, audit, filePlan, type)
    ].join('\n');
  }
  function systemBuilderComposerPrompt(data, audit, filePlan, type){
    data = data || {}; audit = audit || codeStaticAudit(data); filePlan = filePlan || [];
    var lang = normalizeCodeLang(data.lang || 'text');
    return [
      'ต่อยอดจาก code block นี้เป็นระบบที่ช่วยสร้างระบบต่าง ๆ ให้ใช้งานจริง',
      '',
      'ข้อกำหนดหลัก:',
      '1. ของเดิมยังอยู่: ห้ามลบฟังก์ชันเดิม และต้องบอกชัดว่าของเดิมยังอยู่ส่วนไหน',
      '2. สิ่งที่เพิ่ม: เพิ่มฟีเจอร์แชทสำหรับคุยสร้างระบบ และระบบสร้างแอพ/ปลั๊กอิน/เว็บ/โมดูลตามโจทย์',
      '3. วิธีใช้: ต้องมีคู่มือใช้งานแบบคนไม่เข้าใจโค้ดก็ทำตามได้',
      '4. ตรวจบั๊ก: ต้องตรวจ security, responsive, browser, button, form, API, file/package, install/upgrade flow',
      '5. ไฟล์/สถานะ: ต้องแสดงโครงสร้างไฟล์ สถานะ readiness %, QC %, และบอกว่าสามารถใช้งานได้แล้วหรือยัง',
      '',
      'ชนิดระบบที่ตรวจพบ: ' + type,
      'QC ปัจจุบัน: ' + (audit.score || 0) + '/100 · ' + (audit.status || '-'),
      'ไฟล์ที่ควรมี:',
      (filePlan || []).map(function(f){ return '- ' + f; }).join('\n') || '- ยังไม่มี file plan',
      '',
      'ให้ตอบเป็น: ทวนโจทย์ → โครงระบบ → โครงสร้างไฟล์ → โค้ดเต็มใน fenced code block → วิธีติดตั้ง/ใช้งาน → ตรวจบั๊ก → สถานะจบหรือยัง',
      '',
      'โค้ดต้นทาง:',
      '```' + lang,
      clean(data.code || '').slice(0, 60000),
      '```'
    ].join('\n');
  }
  function ensureSystemBuilderPanel(card){
    if(!card) return null;
    var panel = card.querySelector('.aira-system-builder-panel');
    if(!panel){ panel = d.createElement('div'); panel.className = 'aira-system-builder-panel'; panel.hidden = true; card.appendChild(panel); }
    return panel;
  }
  function renderSystemBuilderPanel(card){
    var panel = ensureSystemBuilderPanel(card); if(!panel) return;
    var data = codeDataFromCard(card); data.card = card;
    if(!data.code){ panel.innerHTML = '<div class="system-builder-empty">ไม่พบโค้ดสำหรับสร้างระบบ</div>'; return; }
    var parts = detectCodeParts(data.lang, data.code);
    var isWp = isPhpLikeCode(data.lang, data.code) || /wordpress|wp_|add_action|add_shortcode|register_block_type|elementor|gutenberg/i.test(data.code);
    var audit = codeStaticAudit(data);
    var score = systemBuilderReadinessScore(data, audit);
    var type = systemBuilderProjectType(data);
    var files = systemBuilderFilePlan(data, parts, isWp);
    var reality = codeRealityProfile(data);
    var missing = audit.missing && audit.missing.length ? audit.missing : ['พร้อมระดับพื้นฐาน แต่ควรทดสอบบน staging ก่อนใช้งานจริง'];
    panel.__airaSystemBuilderReport = systemBuilderReportFromCard(card);
    panel.innerHTML = '<div class="system-builder-head"><div><b>'+icon('module')+'<span>System Builder จาก Code Block</span></b><small>เปลี่ยนโค้ดนี้เป็นระบบ/แอพ/ปลั๊กอินที่ต่อยอดได้ โดยไม่ลบของเดิม</small></div><span class="system-builder-score">'+esc(score)+'/100</span></div>'+
      '<div class="system-builder-grid">'+
      '<section><b>1. ของเดิมยังอยู่</b><p>คง code block เดิม, Preview, Debug, Package และ Upgrade flow ไว้ครบ ไม่เขียนทับโค้ดอัตโนมัติ</p></section>'+
      '<section><b>2. สิ่งที่เพิ่ม</b><p>เพิ่ม System Builder Layer สำหรับคุยต่อ, วาง app builder, แยกไฟล์ และส่ง prompt เข้า Composer</p></section>'+
      '<section><b>3. วิธีใช้</b><p>กดสร้างระบบ → ตรวจรายงาน → ใส่ Composer → ให้ AiRA สร้าง/อัปเกรดต่อ → Preview+QC ก่อนติดตั้ง</p></section>'+
      '<section><b>4. ตรวจบั๊ก</b><p>Client QC '+esc(audit.score || 0)+'/100 · '+esc(audit.status || '-')+' · ต้องดู: '+esc(missing.join(', '))+'</p></section>'+
      '</div>'+
      '<div class="system-builder-reality"><b>Realness '+esc(reality.realness)+'/100</b><span>Preview: '+esc(reality.previewReady?'พร้อม':'ต้องเพิ่ม')+'</span><span>Click: '+esc(reality.clickReady?'มีจุดกด/ฟอร์ม':'ยังไม่มี')+'</span><span>Package: '+esc(reality.packageReady?'พร้อมแพ็ก':'ยังไม่พร้อม')+'</span></div>'+
      '<div class="system-builder-files"><b>5. ไฟล์/สถานะ · '+esc(type)+'</b><div>'+files.map(function(f){ return '<span>'+esc(f)+'</span>'; }).join('')+'</div></div>'+
      '<div class="system-builder-actions"><button type="button" data-action="code-system-builder-compose">ใส่ Composer</button><button type="button" data-action="code-system-builder-preview">เปิด Preview+QC</button><button type="button" data-action="code-system-builder-copy">Copy Report</button><button type="button" data-action="code-system-builder-download">Download TXT</button></div>'+
      '<small class="system-builder-note">Settings | โดย Thinkb4do | ดูรายละเอียด · สถานะ: พร้อมต่อยอดจาก code block นี้โดยยังไม่ติดตั้ง/ไม่เขียนฐานข้อมูล</small>';
  }
  function toggleSystemBuilderPanel(card, force){
    var panel = ensureSystemBuilderPanel(card); if(!panel) return;
    renderSystemBuilderPanel(card);
    var show = typeof force === 'boolean' ? force : !!panel.hidden;
    panel.hidden = !show;
    if(show){ card.setAttribute('data-system-builder-open','1'); toast('เปิด System Builder จาก code block แล้ว', 'ok'); }
    else { card.setAttribute('data-system-builder-open','0'); }
    scheduleMeasure();
  }
  function copySystemBuilderReport(btn){
    var card = codeCardFromButton(btn), panel = ensureSystemBuilderPanel(card); if(!panel || !panel.__airaSystemBuilderReport){ renderSystemBuilderPanel(card); panel = ensureSystemBuilderPanel(card); }
    copyText((panel && panel.__airaSystemBuilderReport) || systemBuilderReportFromCard(card)).then(function(){ toast('คัดลอก System Builder Report แล้ว','ok'); }).catch(function(){ toast('คัดลอก report ไม่สำเร็จ','error'); });
  }
  function downloadSystemBuilderReport(btn){
    var card = codeCardFromButton(btn), data = codeDataFromCard(card), report = systemBuilderReportFromCard(card);
    download('aira-system-builder-' + String((data.index || 0) + 1) + '.txt', report, 'text/plain;charset=utf-8');
    if(card) card.setAttribute('data-system-builder-downloaded','1');
    toast('ดาวน์โหลด System Builder TXT แล้ว', 'ok');
  }
  function composeSystemBuilderPrompt(btn){
    var card = codeCardFromButton(btn), data = codeDataFromCard(card); data.card = card;
    var audit = codeStaticAudit(data), type = systemBuilderProjectType(data), files = systemBuilderFilePlan(data, detectCodeParts(data.lang, data.code), isPhpLikeCode(data.lang, data.code));
    var input = byId('composerInput'); if(!input){ toast('ไม่พบ Composer','error'); return; }
    input.value = systemBuilderComposerPrompt(data, audit, files, type);
    input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); setComposerMenuOpen(false);
    toast('ใส่คำสั่งสร้างระบบต่อจาก code block ใน Composer แล้ว', 'ok');
  }
  function previewSystemBuilderFlow(btn){
    var card = codeCardFromButton(btn); if(!card) return;
    toggleSystemBuilderPanel(card, true);
    runPreinstallSandboxFromCard(card);
    runCodeLiveDebugFromCard(card, {silent:false});
    card.setAttribute('data-system-builder-preview-qc','1');
  }

  function successLoopProfile(card, audit){
    var data = codeDataFromCard(card);
    audit = audit || codeStaticAudit(data);
    var reality = codeRealityProfile(data);
    var isWp = isPhpLikeCode(data.lang, data.code) || /wordpress|wp_|add_action|add_shortcode|register_rest_route|wp_ajax_|Plugin\s+Name\s*:/i.test(data.code || '');
    var previewSeen = card && card.getAttribute && card.getAttribute('data-real-preview-seen') === '1';
    var clicked = card && card.getAttribute && card.getAttribute('data-interactive-preview-tested') === '1';
    var packageReady = !!(reality.packageReady || isWordPressInstallableCode(data.lang, data.code));
    var score = Math.round(((audit.score || 0) * 0.48) + ((reality.realness || 0) * 0.34) + (previewSeen ? 8 : 0) + (clicked ? 6 : 0) + (packageReady ? 4 : 0));
    score = clamp(score, 0, 100);
    var missing = [];
    (audit.missing || []).forEach(function(x){ if(missing.indexOf(x) === -1) missing.push(x); });
    if(!reality.previewReady) missing.push('Preview/Result ที่เห็นผลจริง');
    if(isWp && !packageReady) missing.push('WordPress package/installable structure');
    if(isWp && !clicked) missing.push('ทดลองกดใน Pre-install Sandbox');
    if(!reality.clickReady) missing.push('ปุ่ม/ฟอร์ม/flow ให้ทดสอบได้จริง');
    var phase = score >= 92 && (!missing.length || (isWp && clicked)) ? 'ready_to_package' : (score >= 76 ? 'fix_and_verify' : 'build_missing_parts');
    return {data:data, audit:audit, reality:reality, score:score, isWp:isWp, previewSeen:previewSeen, clicked:clicked, packageReady:packageReady, missing:missing.slice(0,10), phase:phase};
  }
  function successPhaseChip(label, ok){ return '<span class="success-phase '+(ok?'ok':'todo')+'"><b>'+(ok?'✓':'•')+'</b><small>'+esc(label)+'</small></span>'; }
  function ensureSuccessLoopPanel(card){
    if(!card) return null;
    var panel = card.querySelector('.aira-code-success-panel');
    if(!panel){ panel = d.createElement('div'); panel.className = 'aira-code-success-panel'; panel.hidden = true; card.appendChild(panel); }
    return panel;
  }
  function successLoopReportText(profile){
    profile = profile || {}; var data = profile.data || {};
    return [
      'AiRA Code Block Success Loop',
      'Version: ' + VERSION,
      'File: ' + downloadNameForCode(data.lang || 'text', data.index || 0),
      'Score: ' + (profile.score || 0) + '/100',
      'Phase: ' + (profile.phase || '-'),
      'Preview seen: ' + (profile.previewSeen ? 'yes' : 'no'),
      'Interactive tested: ' + (profile.clicked ? 'yes' : 'no'),
      'Package ready: ' + (profile.packageReady ? 'yes' : 'no'),
      'Realness: ' + ((profile.reality && profile.reality.realness) || 0) + '/100',
      'Client QC: ' + ((profile.audit && profile.audit.score) || 0) + '/100',
      'Missing: ' + ((profile.missing || []).join(', ') || 'none'),
      '',
      'Human Value Guard:',
      '- Every button/command must help a real user complete a task, reduce risk, save time, or understand the system better.',
      '- Every code command should have a visible or documented benefit: who it helps, what it does, what feedback appears, what risk it reduces, and how to test it.',
      '- Every action needs a handler/fallback, clear feedback, accessibility label, and a test path.',
      '- Risky actions need preview/confirmation and must preserve useful existing features.',
      '',
      'Loop rule: Build → Preview → Debug → Fix → Rebuild → Package. Do not stop at a plan when code can be produced.'
    ].join('\n');
  }
  function renderSuccessLoopPanel(card, profile){
    var panel = ensureSuccessLoopPanel(card); if(!panel) return;
    profile = profile || successLoopProfile(card);
    panel.hidden = false;
    panel.__airaSuccessLoopReport = successLoopReportText(profile);
    var missing = (profile.missing || []).length ? profile.missing.join(', ') : 'ไม่พบจุดขาดหลัก';
    var phases = [
      successPhaseChip('อ่านโค้ดเดิม', !!(profile.data && profile.data.code)),
      successPhaseChip('Preview/QC', !!profile.previewSeen),
      successPhaseChip('คลิกทดสอบ', !!profile.clicked || !profile.isWp),
      successPhaseChip('แก้ส่วนขาด', (profile.audit && profile.audit.score >= 82)),
      successPhaseChip('Package/Upgrade', !!profile.packageReady)
    ].join('');
    panel.innerHTML = '<div class="success-loop-head"><div><b>'+icon('performance')+'<span>Build Until Success</span></b><small>โหมดนี้บังคับให้ code block เดินงานต่อ พร้อม Human Value Guard: ทุกปุ่ม/คำสั่งต้องช่วยผู้ใช้จริง มี feedback และทดสอบได้</small></div><span>'+esc(profile.score)+'/100</span></div>'+ 
      '<div class="success-loop-meter"><i style="width:'+esc(profile.score)+'%"></i></div>'+ 
      '<div class="success-loop-phases">'+phases+'</div>'+ 
      '<p><b>สถานะ:</b> '+esc(profile.phase)+' · <b>ต้องเติม:</b> '+esc(missing)+'</p>'+ 
      '<div class="success-loop-actions"><button type="button" data-action="code-success-copy">Copy Success Report</button><button type="button" data-action="code-success-compose">ใส่ Composer</button><button type="button" data-action="code-success-send">ส่งแก้จนสำเร็จ</button></div>'+ 
      '<small>Settings | โดย Thinkb4do | ดูรายละเอียด · ระบบไม่ execute PHP/ไม่เขียนฐานข้อมูลก่อนยืนยัน และจะบังคับให้รอบถัดไปส่งโค้ดเต็มพร้อม Button/Command Value Map</small>';
    setCodeNextStatus(card, 'Success Loop ' + profile.score + '/100 · ' + (profile.phase || 'checking'), profile.score >= 92 ? 'is-next-satisfied' : 'is-next-sent');
    scheduleMeasure();
  }
  function successLoopPromptFromProfile(profile, extra){
    profile = profile || {}; var data = profile.data || {};
    var lang = normalizeCodeLang(data.lang || 'text');
    var name = headerValueFromPhp(data.code || '', 'Plugin Name') || downloadNameForCode(lang, data.index || 0);
    return [
      'ทำ code block นี้ให้เป็นระบบที่สำเร็จจริงแบบ Build Until Success',
      '',
      '[Goal]',
      'พัฒนาโค้ดด้านล่างต่อให้ใช้งานได้จริงขึ้นจนถึงระดับพร้อม Preview/QC/Package/Upgrade โดยไม่หยุดที่แผน',
      '',
      '[Current Status]',
      '- File/System: ' + name,
      '- Language: ' + lang,
      '- Success score: ' + (profile.score || 0) + '/100',
      '- Phase: ' + (profile.phase || '-'),
      '- Client QC: ' + ((profile.audit && profile.audit.score) || 0) + '/100',
      '- Realness: ' + ((profile.reality && profile.reality.realness) || 0) + '/100',
      '- Missing: ' + ((profile.missing || []).join(', ') || 'none'),
      '- User extra: ' + (trim(extra || '') || 'มุ่งมั่นทำระบบจนสำเร็จ'),
      '',
      '[Hard Rules]',
      '1) ของเดิมต้องอยู่ครบ ห้ามตัดฟังก์ชันเดิมที่มีประโยชน์',
      '2) ห้ามตอบแค่แผน ถ้าแก้โค้ดได้ให้แก้และส่ง code block เต็มทันที',
      '3) เติมสิ่งที่ทำให้ระบบทำงานจริง: frontend/backend/API/data/security/state/error handling/responsive/test/preview/package ตามชนิดโค้ด',
      '4) ถ้าเป็น WordPress ต้องมี Plugin Header, capability, nonce, sanitize/escape, REST/AJAX permission_callback, uninstall/cleanup, admin/user UI และรองรับ Elementor/Gutenberg เมื่อเกี่ยวข้อง',
      '5) ต้องมีขั้นตอนทดสอบ: ปุ่ม, form, preview, responsive, browser, API/mock, install/upgrade gate',
      '6) Human Value Guard: ทุกปุ่ม/คำสั่งต้องช่วยผู้ใช้หรือผู้อื่นจริง ๆ ระบุว่า ช่วยใคร → ทำอะไรจริง → feedback หลังคลิก → ป้องกันความเสี่ยงอะไร → ทดสอบอย่างไร',
      '6.1) Code Command Benefit: ทุกคำสั่งสำคัญในโค้ดต้องมีประโยชน์ชัดเจน ไม่ใช่เขียนไว้เฉย ๆ เช่น event/API/save/render/security/responsive/error ต้องบอกว่าช่วยผู้ใช้อย่างไรและทดสอบอย่างไร',
      '6.2) Unlimited System Capability: ขยายฟังก์ชันได้หลากหลายเมื่อช่วยงานจริง เช่น Dashboard/User UI/API/Data/Security/Preview/QC/Export/Search/Chart/Docs/Elementor/Gutenberg/Shortcode แต่ต้องปลอดภัย ถูกกฎหมาย และไม่ทำลายของเดิม',
      '6.3) Humanity Value Builder: ทุกระบบที่ผลิตต้องระบุ Humanity Impact Map ว่าช่วยใคร แก้ปัญหาอะไร ลดความเสี่ยงอะไร เคารพข้อมูล/สิทธิ์อย่างไร และทดสอบคุณค่านั้นได้อย่างไร',
      '7) ห้ามมีปุ่มลอย ปุ่มซ้ำ ปุ่มตกแต่ง ปุ่มไม่มี handler หรือคำสั่งที่ไม่พาผู้ใช้ไปถึงเป้าหมาย ถ้ายังทำงานจริงไม่ได้ต้องใส่ disabled/coming soon อย่างซื่อสัตย์',
      '8) ปุ่มเสี่ยง เช่น install/upgrade/delete/send ต้องมี preview/confirmation/permission/rollback และไม่ทำลายของเดิมที่มีประโยชน์',
      '9) ต้องรองรับ accessibility: aria-label, focus state, keyboard/touch, readable contrast และข้อความไทยชัดเจนสำหรับคนไม่เข้าใจโค้ด',
      '10) สรุปท้ายว่าใช้งานได้แล้วหรือยัง เหลืออะไร และรอบถัดไปควรแก้อะไร',
      '11) ถ้าต้องใช้ API key/server credential ให้ใส่ช่องตั้งค่า/placeholder/สถานะเชื่อมต่อ แต่อย่าใส่ key จริง',
      '',
      '[Return Format]',
      'สรุป 1 บรรทัด → สิ่งที่เพิ่ม → Capability Map → Human Value Map ของปุ่ม → Command Benefit Map ของคำสั่งในโค้ด → โค้ดเต็มใน fenced code block → วิธีทดสอบ → สถานะ Success %',
      '',
      '[Current Code]',
      '```' + lang,
      shortCodePreviewForPrompt(data.code || ''),
      '```'
    ].join('\n');
  }
  function composeSuccessLoopPrompt(btn){
    var card = codeCardFromButton(btn), profile = successLoopProfile(card, codeStaticAudit(codeDataFromCard(card)));
    renderSuccessLoopPanel(card, profile);
    var input = byId('composerInput'); if(!input){ toast('ไม่พบ Composer','error'); return; }
    input.value = successLoopPromptFromProfile(profile, 'ใส่จากปุ่ม Build Until Success');
    input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); setComposerMenuOpen(false);
    toast('ใส่คำสั่งทำจนสำเร็จใน Composer แล้ว', 'ok');
  }
  function copySuccessLoopReport(btn){
    var card = codeCardFromButton(btn), panel = ensureSuccessLoopPanel(card);
    if(!panel || !panel.__airaSuccessLoopReport){ renderSuccessLoopPanel(card, successLoopProfile(card)); panel = ensureSuccessLoopPanel(card); }
    copyText((panel && panel.__airaSuccessLoopReport) || '').then(function(){ toast('คัดลอก Success Loop Report แล้ว','ok'); }).catch(function(){ toast('คัดลอก report ไม่สำเร็จ','error'); });
  }
  function sendSuccessLoopPrompt(btn){
    var card = codeCardFromButton(btn); runCodeBuildSuccessFromCard(card, {forceSend:true});
  }
  function runCodeBuildSuccessFromCard(card, opts){
    opts = opts || {}; if(!card) return;
    clearStaleSending();
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับทำจนสำเร็จ','error'); return; }
    toggleSystemBuilderPanel(card, true);
    if(blocksCanRenderResult(card.__airaPreviewBlocks || [{lang:data.lang, code:data.code}], data.lang, data.code) || isPhpLikeCode(data.lang, data.code)){
      runPreinstallSandboxFromCard(card);
    } else {
      runCodeLiveDebugFromCard(card, {silent:true});
    }
    var audit = codeStaticAudit(data);
    var profile = successLoopProfile(card, audit);
    renderSuccessLoopPanel(card, profile);
    if(state.sending){ toast('ระบบกำลังตอบอยู่ รอให้จบก่อนส่ง Success Loop', 'warn'); return; }
    var prompt = successLoopPromptFromProfile(profile, opts.extra || 'ทำต่อจนสำเร็จ ใช้งานได้จริงขึ้น');
    var visible = 'ทำ code block จนสำเร็จ: ' + (headerValueFromPhp(data.code, 'Plugin Name') || downloadNameForCode(data.lang, data.index)) + ' · Success ' + profile.score + '/100';
    var btns = all('[data-action="code-build-success"], [data-action="code-success-send"]', card);
    btns.forEach(function(b){ b.disabled = true; b.classList.add('is-loading'); });
    setCodeNextStatus(card, 'Success Loop ส่งคำสั่งแล้ว · กำลังให้ AiRA แก้/เติม/ทดสอบเพื่อส่ง code block ใหม่', 'is-next-sent');
    submitPrompt(prompt, visible, {mode:'code_success_loop', source:'code-block-command-value-typing-cadence-7557'}).finally(function(){
      btns.forEach(function(b){ b.disabled = false; b.classList.remove('is-loading'); });
    });
  }

  function runPreinstallSandboxFromCard(card){
    if(!card) return;
    var data = codeDataFromCard(card);
    if(!data.code){ toast('ไม่พบโค้ดสำหรับทดลองใช้ก่อนติดตั้ง','error'); return; }
    if(isPhpLikeCode(data.lang, data.code) && !requireAuthorizedPreviewAccess()) return;
    setInlineCodePreview(card, true);
    card.classList.add('is-preinstall-sandbox');
    card.setAttribute('data-real-preview-seen','1');
    card.setAttribute('data-preview-before-install','1');
    renderCodeRealityPanel(card);
    var inline = card.querySelector('.aira-code-inline-preview');
    var label = inline && inline.querySelector('.inline-preview-head small');
    if(label){ label.textContent = isPhpLikeCode(data.lang, data.code) ? 'Authorized Interactive Preview · ทดลองกดได้ก่อนติดตั้งจริง' : 'ทดลอง Result จากโค้ดนี้ก่อนนำไปใช้จริง'; }
    var versionLine = card.querySelector('.aira-code-version-line');
    if(versionLine && isPhpLikeCode(data.lang, data.code)){
      versionLine.textContent = codeVersionLineText(data.lang, data.code, detectCodeParts(data.lang, data.code), true, data.index) + ' · ทดลองใช้ก่อนติดตั้งแล้ว';
    }
    runCodeLiveDebugFromCard(card, {silent:true});
    toast(isPhpLikeCode(data.lang, data.code) ? 'เปิด Authorized Interactive Preview + Live QC ก่อนติดตั้งแล้ว' : 'เปิด Result + Live Debug ให้ทดลองก่อนนำไปใช้แล้ว', 'ok');
  }

  function setInlineCodePreview(card, show){
    if(!card) return;
    var data = codeDataFromCard(card); if(!data.code){ toast('ไม่พบโค้ดสำหรับ Preview','warn'); return; }
    var pre = card.querySelector('.aira-code-pre');
    var inline = card.querySelector('.aira-code-inline-preview');
    var frame = inline && inline.querySelector ? inline.querySelector('iframe') : null;
    var btn = card.querySelector('[data-action="code-preview"]');
    var title = card.querySelector('.aira-code-head b span');
    show = !!show;
    if(show && isPhpLikeCode(data.lang, data.code)){
      if(!requireAuthorizedPreviewAccess()) return;
      card.classList.add('is-preinstall-sandbox');
      card.setAttribute('data-real-preview-seen','1');
      card.setAttribute('data-preview-before-install','1');
      var ipLabel = inline && inline.querySelector ? inline.querySelector('.inline-preview-head small') : null;
      if(ipLabel){ ipLabel.textContent = 'Authorized Preview · เฉพาะผู้มีสิทธิ์ · ตรวจจริงก่อนติดตั้ง'; }
      if(!card.getAttribute('data-live-debug-score')){ runCodeLiveDebugFromCard(card, {silent:true}); }
    }
    card.classList.toggle('is-previewing', show);
    card.setAttribute('data-preview-mode', show ? 'result' : 'code');
    setHidden(pre, show);
    setHidden(inline, !show);
    if(frame){ frame.srcdoc = show ? previewSrcDocFromBlocks(card.__airaPreviewBlocks || [{lang:data.lang, code:data.code}], data.index) : ''; }
    state.previewCode = data;
    if(btn){ btn.setAttribute('aria-pressed', show ? 'true' : 'false'); btn.innerHTML = icon(show ? 'code' : 'preview') + '<span>' + (show ? 'Code' : 'Result') + '</span>'; }
    if(title){ title.textContent = (show ? 'ผลลัพธ์จากโค้ด · ' : 'Code · ') + downloadNameForCode(data.lang, data.index); }
    renderCodeRealityPanel(card);
    scheduleMeasure();
  }
  function openCodePreviewFromCard(card){
    if(!card) return;
    setInlineCodePreview(card, !card.classList.contains('is-previewing'));
    toast(card.classList.contains('is-previewing') ? 'แสดงผลลัพธ์ที่เรนเดอร์จากโค้ดแล้ว' : 'เปิดดูโค้ดต้นฉบับแล้ว', 'ok');
  }
  function closeCodePreview(card){
    card = card || (d.activeElement && d.activeElement.closest ? d.activeElement.closest('.aira-code-card') : null);
    setInlineCodePreview(card, true);
  }
  function ensureCodePreviewPanel(){
    // v7.1.3 keeps code/result inline inside each code card. This shim prevents old runtime calls from breaking boot.
    return true;
  }

  function attachmentTray(){ return byId('attachmentTray'); }
  function compactFileName(name){
    name = clean(name || 'image');
    if(name.length > 34){ name = name.slice(0,18) + '…' + name.slice(-10); }
    return name;
  }
  function renderAttachmentTray(){
    var tray = attachmentTray(); if(!tray) return;
    var items = state.pendingAttachments || [];
    tray.innerHTML = '';
    setHidden(tray, !items.length);
    if(!items.length) return;
    items.forEach(function(a, i){
      var chip = d.createElement('div'); chip.className = 'attachment-chip'; chip.setAttribute('data-attachment-index', String(i));
      var thumb = d.createElement('button'); thumb.type = 'button'; thumb.className = 'attachment-thumb'; thumb.setAttribute('data-action','attachment-preview'); thumb.setAttribute('data-attachment-index', String(i));
      var thumbSrc = (a && a.data_url && /^data:image\//i.test(a.data_url)) ? a.data_url : (a && (a.preview_url || a.download_url) ? (a.preview_url || a.download_url) : '');
      if(thumbSrc){ thumb.innerHTML = '<img src="'+esc(thumbSrc)+'" alt="'+esc(a.name || 'attached image')+'">'; }
      else { thumb.innerHTML = icon('docs'); }
      var meta = d.createElement('div'); meta.className = 'attachment-meta'; meta.innerHTML = '<b>'+esc(compactFileName(a.name || 'attached-image'))+'</b><small>'+esc((a.mime || 'image') + ' · พร้อมส่งเข้า Vision')+'</small>';
      var remove = d.createElement('button'); remove.type='button'; remove.className='attachment-remove'; remove.setAttribute('data-action','attachment-remove'); remove.setAttribute('data-attachment-index', String(i)); remove.setAttribute('aria-label','ลบไฟล์แนบ'); remove.textContent='×';
      chip.appendChild(thumb); chip.appendChild(meta); chip.appendChild(remove); tray.appendChild(chip);
    });
    scheduleMeasure();
  }
  function clearPendingAttachments(){ state.pendingAttachments = []; renderAttachmentTray(); }
  function pendingAttachmentSummary(){
    var items = state.pendingAttachments || [];
    if(!items.length) return '';
    return '\n\n[Live Vision Attachments: ' + items.map(function(a,i){ return (i+1)+'. '+(a.name||'image')+' ('+(a.mime||'image')+')'; }).join(' | ') + ']';
  }
  function pendingAttachmentVisibleCards(items){
    items = Array.isArray(items) ? items : (state.pendingAttachments || []);
    if(!items.length) return '';
    return items.map(function(a){
      a = a || {};
      var visibleUrl = a.preview_url || a.download_url || '';
      if(!visibleUrl && a.data_url && clean(a.data_url).length < 900000){ visibleUrl = a.data_url; }
      return visibleImageMarker({
        url: visibleUrl,
        downloadUrl: a.download_url || a.preview_url || visibleUrl,
        prompt: a.name || 'attached image',
        mode: 'vision_attachment',
        kind: 'attached_image',
        label: 'ภาพที่แนบแล้ว',
        message: visibleUrl ? 'ภาพขึ้นในแชทแล้ว · ระบบจะส่งภาพนี้เข้า Vision เพื่ออ่านบริบท' : 'ภาพถูกแนบแล้ว แต่ไฟล์ใหญ่เกินกว่าจะฝัง preview ใน local message',
        saved: a.saved || null
      });
    }).join('\n');
  }
  function openPendingAttachment(index){
    index = Number(index)||0;
    var a = (state.pendingAttachments || [])[index];
    if(!a || !a.data_url){ toast('ไม่พบภาพแนบ', 'warn'); return; }
    fillImagePreviewPanel({url:(a.preview_url || a.download_url || a.data_url), downloadUrl:(a.download_url || a.preview_url || a.data_url), prompt:a.name || 'attached image', mode:'vision_attachment', message:'ภาพแนบสำหรับตีความ/OCR'}, true);
  }
  function clearStaleSending(){
    if(state.sending && state.sendingSince && (Date.now() - state.sendingSince > 9000)){
      state.sending = false;
      state.sendingSince = 0;
      resizeInput();
      toast('ปลดสถานะกำลังตอบที่ค้างแล้ว ลองกดปุ่มอีกครั้งได้', 'warn');
      return true;
    }
    return false;
  }

  function sendChat(overrideText, visibleText){
    clearStaleSending();
    if(state.sending) return Promise.resolve(false);
    var input = byId('composerInput'); if(!input) return Promise.resolve(false);
    var hasOverride = overrideText !== undefined && overrideText !== null;
    var text = trim(hasOverride ? overrideText : input.value);
    var attachments = hasOverride ? [] : (state.pendingAttachments || []).slice(0,3);
    var composerHiddenPrompt = (!hasOverride && !attachments.length) ? (input.getAttribute('data-continuation-prompt') || '') : '';
    var composerVisible = (!hasOverride && !attachments.length) ? (input.getAttribute('data-continuation-visible') || '') : '';
    if(composerHiddenPrompt){
      var typedExtra = trim(input.value || '');
      var numberOnly = input.getAttribute('data-continuation-number-only') === '1';
      var continuationNumber = normalizeChoiceToken(input.getAttribute('data-continuation-number') || composerVisible || '');
      var typedForPrompt = typedExtra;
      if(numberOnly && continuationNumber){
        if(normalizeChoiceToken(typedForPrompt) === continuationNumber){ typedForPrompt = ''; }
        else {
          var safeNum = continuationNumber.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
          typedForPrompt = trim(typedForPrompt.replace(new RegExp('^(?:เลือก\\s*)?(?:ข้อ\\s*)?' + safeNum + '\\s*[:.\\-–—]?\\s*', 'iu'), ''));
        }
      }
      text = composerHiddenPrompt + (typedForPrompt ? ('\n\n[ข้อความที่ผู้ใช้พิมพ์เพิ่มใน Composer]\n' + typedForPrompt) : '');
      visibleText = numberOnly ? (typedExtra || continuationNumber || composerVisible || 'คุยต่อ') : trim((composerVisible || visibleText || 'คุยต่อ') + (typedExtra ? ': ' + typedExtra : ''));
    }
    else {
      var typedContinuation = (!hasOverride && !attachments.length) ? resolveTypedContinuationChoice(text) : null;
      if(!typedContinuation){ typedContinuation = (!hasOverride && !attachments.length) ? resolveTypedInsightNumber(text) : null; }
      if(typedContinuation){ text = typedContinuation.prompt; visibleText = typedContinuation.visible; }
    }
    if(!text && attachments.length){ text = 'ช่วยตีความภาพนี้ อ่านข้อความในภาพ และสรุปสิ่งสำคัญให้เข้าใจง่าย'; }
    if(!text){ resizeInput(); return Promise.resolve(false); }
    state.sending = true; state.sendingSince = Date.now(); resizeInput();
    if(composerVisible){ input.removeAttribute('data-continuation-visible'); }
    if(composerHiddenPrompt){ clearComposerContinuation(true); }
    var displayText = trim(visibleText || composerVisible || text);
    addMessage('user', displayText + (hasOverride ? '' : (pendingAttachmentSummary() + pendingAttachmentVisibleCards(attachments))), now());
    if(!hasOverride || trim(input.value)){ input.value = ''; }
    resizeInput();
    var startedAt = Date.now();
    var communicationContext = buildAllFormContext(text);
    var answerRelationshipContext = buildAnswerRelationshipContext(text, communicationContext);
    var relationPipelineContext = buildRealRelationPipeline(text, communicationContext);
    var externalSystemBridgeContext = formatExternalSystemBridge(communicationContext.externalSystemBridge || buildExternalSystemBridgeProfile(text, communicationContext));
    var communicationSkillEvolutionContext = formatCommunicationSkillEvolution(communicationContext.communicationSkillEvolution || buildCommunicationSkillEvolutionProfile(text, communicationContext));
    var realRelationPipelineContext = formatRealRelationPipeline(relationPipelineContext);
    state.contextProfile = communicationContext;
    updateComposerContext();
    var processor = addPromptProcessingSlide(communicationContext, text);
    var gatewayPayload = compactPromptForGateway(text);
    if(gatewayPayload.compacted){
      recordPerformanceEvent({type:'gateway_prompt_compacted', ms:0, intent:communicationContext.intent || '', forms:(communicationContext.forms||[]).join(','), original:gatewayPayload.originalLength, sent:gatewayPayload.sentLength});
    }
    var aiRequestText = gatewayPayload.text || text;
    var autoCompiledPrompt = compileThoughtToAutomaticPrompt(aiRequestText, communicationContext);
    var guidedMessage = buildHumanCommunicationMessage(aiRequestText, communicationContext, autoCompiledPrompt);
    var sendChatPayload = {message:guidedMessage, original_message:text, gateway_guard: gatewayPayload.compacted ? 'compact_prompt_v7561' : '', gateway_original_chars: gatewayPayload.originalLength || text.length, gateway_sent_chars: gatewayPayload.sentLength || aiRequestText.length, attachments: attachments.length ? JSON.stringify(attachments) : '', communication_profile:'vision_attachment_reader_v721_prompt_processor_time_v724_result_resolver_v725_global_communication_code_universe_v735_communication_quality_v7570_thought_prompt_compiler_v7589_answer_visible_commit_v75836_external_bridge_v75837_real_relation_pipeline_v75838', human_profile_behavior_context:formatCommunicationContext(communicationContext), question_reader_context_720:formatQuestionReaderBridge(communicationContext), desired_output_context_725:formatDesiredOutputResolver(communicationContext), deep_communication_context_734:formatDeepCommunicationSkill(communicationContext), global_communication_context_735:formatGlobalCommunicationSkill(communicationContext), code_master_context_734:formatCodeMasterProfile(communicationContext), universal_code_context_735:formatUniversalSystemCodeSkill(communicationContext), auto_system_answer_context_720:'Auto system answer enabled: normal typing routes to relevant AiRA modules before answering. Connected systems=' + (((communicationContext.questionReading||{}).connectors||[]).join(' > ')), prompt_processing_context_724: buildPromptProcessingText(communicationContext, text), auto_evolution_core_context_7587:formatAutoEvolutionCore(communicationContext), thought_prompt_compiler_context_7589:formatThoughtPromptCompiler(communicationContext), system_command_matrix_context_75810:formatSystemCommandMatrix(), stable_rebase_guard_context_75810:formatStableRebaseGuard(communicationContext), answer_relationship_selector_context_75836:formatAnswerRelationshipSelector(answerRelationshipContext), external_system_bridge_context_75837:externalSystemBridgeContext, communication_skill_evolution_context_75837:communicationSkillEvolutionContext, relationship_connector_context_75838:realRelationPipelineContext, auto_compiled_prompt_7589:autoCompiledPrompt, question_behavior_context:'Question context score: '+communicationContext.communicationScore+'/100 | intent='+communicationContext.intent+' | forms='+(communicationContext.forms||[]).join(',')};

    function makeReplyPackage(reply, data, source, err){
      return {reply: clean(reply || '') || buildClientAnswerFallback(text, communicationContext, source || 'empty_reply'), data: data || {}, source: source || 'unknown', error: err || null};
    }
    function renderReplyPackage(pkg){
      // v7.5.8.36 Answer Visible Commit:
      // Prompt Processor may complete visually first, but it must never block
      // simple or normal answers from appearing again.
      if(renderReplyPackage.__committed){ return Promise.resolve(false); }
      renderReplyPackage.__committed = true;
      pkg = pkg || makeReplyPackage('', {}, 'empty_package');
      var reply = clean(pkg.reply) || buildClientAnswerFallback(text, communicationContext, pkg.source || 'missing_reply');
      try { reply = attachConnectionEvidenceToReply(reply, pkg.data || {}, relationPipelineContext || {}, pkg, text); } catch(evidenceErr){}
      var status = pkg.error ? 'error' : 'success';
      try { forcePromptProcessorCompleteVisible(processor, status); }
      catch(forceErr){ try { finalizePromptProcessing(processor, status); } catch(finalizeErr){} }
      return nextPaint().catch(function(){ return true; }).then(function(){
        try {
          if(pkg.error && pkg.error.isTimeout){
            var retry = timeoutRecoveryPrompt(text, pkg.error);
            setComposerContinuationPrompt(retry, 'ส่งใหม่แบบเร็วหลัง 504/timeout', 'เจอ timeout จึงเตรียม Retry Slim ใน Composer แล้ว');
            reply += '\n\nระบบเตรียมคำสั่ง Retry Slim ไว้ใน Composer แล้ว ให้กดส่งอีกครั้งเพื่อให้ AiRA ตอบแบบสั้นลงและไม่ชน 504';
          }
          state.lastAnswer = reply;
          state.lastCodeBlocks = extractCodeBlocks(reply);
          state.lastCode = state.lastCodeBlocks.length ? state.lastCodeBlocks[0].code : '';
          recordPerformanceEvent({type:pkg.error ? 'chat_error_visible_commit' : 'chat_reply_visible_commit', ms:Date.now()-startedAt, intent:communicationContext.intent || '', forms:(communicationContext.forms||[]).join(','), codeBlocks:state.lastCodeBlocks.length, source:pkg.source || ''});
          var metaText = pkg.data && pkg.data.provider ? ('Provider: ' + pkg.data.provider) : (pkg.source === 'local_direct' ? 'Local Direct Answer' : (pkg.source === 'local_rescue' ? 'Answer Rescue' : now()));
          var answerNode = addMessage('assistant', reply, metaText);
          if(answerNode){
            try { removeAnswerContinuationRows(answerNode); } catch(r0){}
            try { appendPostSlideAnswerRows(answerNode, reply); } catch(r1){}
            answerNode.setAttribute('data-answer-renderer','real-relation-pipeline-v75838');
            answerNode.setAttribute('data-answer-insights','ready-after-visible-commit');
          }
          dismissPromptProcessingAfterAnswer(processor, pkg.error ? 'answer-error-time-kept' : 'answer-visible-commit-finished');
          updateArtifact(reply);
          clearPendingAttachments();
          scheduleMeasure();
          scrollBottom(false);
          if(pkg.error){ toast(pkg.error && pkg.error.isTimeout ? 'เตรียม Retry Slim แล้ว' : 'ใช้ Answer Rescue ตอบแทนคำตอบที่ค้าง', 'warn'); }
          return true;
        } catch(renderErr){
          try {
            var safeReply = reply || buildClientAnswerFallback(text, communicationContext, 'visible_commit_failed:' + (renderErr && renderErr.message ? renderErr.message : renderErr));
            addMessage('assistant', safeReply, 'Answer Rescue');
            dismissPromptProcessingAfterAnswer(processor, 'answer-rescue-visible-commit-error');
            updateArtifact(safeReply);
            scheduleMeasure();
            scrollBottom(false);
          } catch(finalErr){}
          return false;
        }
      });
    }


    var apiReplyPromise = ajax('sendChat', sendChatPayload).then(function(res){
      var data = res && res.data ? res.data : {};
      var reply = resolveUsableReply(data, text, communicationContext);
      try { reply = normalizeReplyWithDesiredOutput(reply, communicationContext); }
      catch(normalizeErr){ reply = buildClientAnswerFallback(text, communicationContext, 'normalize_failed:' + (normalizeErr && normalizeErr.message ? normalizeErr.message : normalizeErr)); }
      return makeReplyPackage(reply, data, 'api', null);
    }).catch(function(err){
      var msg = buildClientAnswerFallback(text, communicationContext, err && err.message ? err.message : String(err));
      return makeReplyPackage(msg, {provider:'Answer Rescue'}, 'ajax_error', err);
    });

    // v7.5.8.39 Real Relation Answer Pipeline:
    // Simple direct answers are released quickly. System/external questions wait for
    // the backend/API candidate long enough to be meaningful, then fall back with an
    // explicit internal/external readiness answer instead of a blank or fake external claim.
    var directAnswer = clientDirectAnswer(text);
    var directPackage = directAnswer ? makeReplyPackage(directAnswer, {provider:'Local Direct Answer · Real Relation Pipeline'}, 'local_direct', null) : null;
    var directFirst = !!(directAnswer && !relationPipelineContext.needsSystem && !relationPipelineContext.needsExternal);
    var fallbackFactory = function(reason){
      var msg = directAnswer || relationshipPipelineFallbackAnswer(text, communicationContext, relationPipelineContext, reason || 'bounded_fallback');
      return makeReplyPackage(msg, {provider: directAnswer ? 'Local Direct Answer · Real Relation Pipeline' : 'Relationship Pipeline Rescue', relation_pipeline:relationPipelineContext}, directAnswer ? 'local_direct' : 'relationship_pipeline_rescue', null);
    };
    var answerSelectionPromise = directFirst
      ? waitMs(prefersReducedMotion() ? 30 : 90).then(function(){ return directPackage || fallbackFactory('direct_first_empty'); })
      : boundedAnswerPromise(apiReplyPromise, relationPipelineContext.answerWaitMs || 4200, fallbackFactory);

    return answerSelectionPromise.then(function(pkg){
      var selected = chooseRelationshipAnswerPackage([pkg, directPackage].filter(Boolean), text, communicationContext);
      if(!selected || looksLikeUnusableReplyText(selected.reply)){
        selected = fallbackFactory('selector_returned_unusable');
      }
      return renderReplyPackage(selected);
    }).catch(function(err){
      var rescuePkg = fallbackFactory(err && err.message ? err.message : String(err));
      return renderReplyPackage(chooseRelationshipAnswerPackage([rescuePkg, directPackage].filter(Boolean), text, communicationContext));
    }).finally(function(){ state.sending = false; state.sendingSince = 0; resizeInput(); syncServer(); });

  }


  function submitPrompt(prompt, visibleText, meta){
    meta = meta || {};
    clearStaleSending();
    if(state.sending){
      toast('ระบบกำลังตอบอยู่ รอให้จบก่อน หรือกดใหม่หลังสถานะค้างถูกปลด', 'warn');
      return Promise.resolve(false);
    }
    return sendChat(prompt, visibleText || prompt);
  }

  function updateArtifact(text){
    var body = byId('artifactBody'); if(!body) return;
    var blocks = extractCodeBlocks(text);
    state.lastCodeBlocks = blocks; state.lastCode = blocks.length ? blocks[0].code : (state.lastCode || '');
    body.textContent = '';
    if(state.artifactTab === 'code'){
      if(blocks.length){ blocks.forEach(function(b,i){ body.appendChild(createCodeBlock(b.lang, b.code, i, blocks)); }); }
      else { body.textContent = clean(text || 'ยังไม่มี code block'); }
      return;
    }
    if(state.artifactTab === 'files'){
      if(blocks.length){
        var list = d.createElement('div'); list.className = 'artifact-file-list';
        blocks.forEach(function(b,i){ var row=d.createElement('button'); row.type='button'; row.setAttribute('data-action','artifact-file-download'); row.setAttribute('data-code-index',String(i)); row.textContent = downloadNameForCode(b.lang, i); list.appendChild(row); });
        body.appendChild(list);
      } else { body.textContent = 'ยังไม่มีไฟล์จาก code block'; }
      return;
    }
    renderMessageContent(body, clean(text || 'Artifact จะเปิดเมื่อ AiRA สร้างไฟล์ โค้ด หรือเอกสาร'), 'assistant');
  }
  function download(filename, text, type){
    var blob = new Blob([clean(text)], {type:type || 'text/plain;charset=utf-8'});
    var a = d.createElement('a'); a.href = URL.createObjectURL(blob); a.download = filename; d.body.appendChild(a); a.click();
    setTimeout(function(){ URL.revokeObjectURL(a.href); if(a.parentNode) a.parentNode.removeChild(a); }, 200);
  }

  function renderRooms(){
    var list = byId('roomsList'); if(!list) return;
    if(!state.rooms.length) createRoom('แชทใหม่', false);
    list.innerHTML = state.rooms.map(function(r){
      var count = (r.messages || []).length;
      return '<div class="room-item" data-room-id="'+esc(r.id)+'"><div><b>'+esc(r.title || 'แชทใหม่')+'</b><small>'+count+' ข้อความ</small></div><div><button type="button" data-action="room-open" data-room-id="'+esc(r.id)+'">เปิด</button><button type="button" data-action="room-delete" data-room-id="'+esc(r.id)+'">ลบ</button></div></div>';
    }).join('');
  }
  function syncServer(){
    if(!actions.saveUserSync) return;
    ajax('saveUserSync', {payload: JSON.stringify({rooms:state.rooms, activeRoomId:state.activeRoomId, authorizedId:getAuthorizedId(), deviceId:getDeviceId(), authorizedIdentity:state.authorizedIdentity || cfg.authorizedIdentity || null, memory:{enabled:true,summary:'',updated:''}})}).then(function(res){ var ai=res && res.data ? (res.data.authorized_identity || (res.data.user_sync && res.data.user_sync.authorizedIdentity)) : null; if(ai){ state.authorizedIdentity=ai; renderIdentityPanelIfOpen(); } }).catch(function(){});
  }
  function loadServer(){
    if(!actions.getUserSync) return;
    ajax('getUserSync', {}).then(function(res){
      var data = res && res.data && res.data.user_sync ? res.data.user_sync : null;
      if(data && data.authorizedIdentity){ state.authorizedIdentity = data.authorizedIdentity; }
      var serverNewer = data && data.updatedAt && (!state.rooms.length || (Number(data.updatedAt) * 1000) > (Number(state.localUpdatedAt || 0) + 1500));
      if(data && data.rooms && data.rooms.length && (!state.rooms.length || serverNewer)){
        state.rooms = data.rooms; state.activeRoomId = data.activeRoomId || data.rooms[0].id; saveLocal(); loadInterestStats(); renderChat(); renderRooms();
      }
      renderIdentityPanelIfOpen();
    }).catch(function(){});
    if(actions.getInterestStats){ ajax('getInterestStats', {}).then(function(res){ var server = res && res.data ? res.data.interest_stats : null; if(server && !state.interestStats){ state.interestStats = server; } }).catch(function(){}); }
  }

  function openPanel(id){ var el = byId(id); setHidden(el, false); }
  function closePanel(id){ var el = byId(id); setHidden(el, true); }
  function closeFloatingExcept(id){ ['workplacePop','roomsDrawer','settings','updateCenterPop','linkPreviewPop','imagePreviewPop','structurePreviewPop'].forEach(function(x){ if(x !== id) closePanel(x); }); }
  function toggleArtifact(){ var a = app(); if(!a) return; a.classList.toggle('artifact-open'); scheduleMeasure(); }

  function reportText(kind){
    measure();
    var root = getComputedStyle(d.documentElement);
    var a = app();
    var actionEls = all('[data-action]', a);
    var ids = ['airaApp','chatLog','airaComposerDock','composerInput','sendBtn','composerMenu','roomsDrawer','artifact','settings'].map(function(id){ return id + '=' + (!!byId(id)); });
    var visible = ['workplacePop','roomsDrawer','settings','updateCenterPop','linkPreviewPop','imagePreviewPop','structurePreviewPop','composerMenu'].map(function(id){ var el=byId(id); return id + '=' + (el && !hasHidden(el) ? 'open' : 'closed'); });
    return [
      'AiRA Studio Clean Slate Result Resolver + Prompt Processing Report',
      'Version: ' + VERSION,
      'Authorized ID Sync: ' + (getAuthorizedId() || 'none') + ' · device=' + getDeviceId(),
      'Kind: ' + (kind || 'bug-all'),
      'Generated: ' + (new Date()).toISOString(),
      'Settings | โดย Thinkb4do | ดูรายละเอียด',
      '',
      'Layout contract:',
      '- Header: magnet-locked to the bottom edge of the WordPress admin bar in every viewport/keyboard/orientation state',
      '- Composer: bottom row inside chat panel, not fixed overlay',
      '- Chat scroll: #chatLog only, native auto scroll',
      '- Legacy AiRA assets: dequeued server-side and purged client-side on this page',
      '- Menu restore: full menu groups restored inside one popup panel above the composer',
      '- Plus menu behavior: opens as a popup above composer; composer remains fixed to the bottom/keyboard',
      '- Message tools: copy + like/interest stats for user/assistant, user inline edit, user show-more/show-less for long questions, assistant inline edit',
      '- Inline answer audio player: clicking assistant audio shows a player under that answer and pushes content down, not overlay',
      '- Code block tools: every fenced code block shows raw code first with Copy/Download/Preview/File/ลองก่อนติดตั้ง; WordPress plugin code shows Authorized Pre-install Sandbox, Debug, Package, Install and Upgrade only; duplicate Next/Fix controls are removed',
      '- Communication Context Reader: reads safe signals from text/code/link/image/voice/file/history/number formats and calculates language, intent, continuity, urgency, complexity, clarity, communication score and interpretation result before sending',
      '- Auto Question Reader v7.2.0: normal typing automatically connects relevant AiRA systems before answering, without forcing menu selection',
      '- GPT Interpretation Skill: routes answers into overview/short/auto interpretation result before direct_helpful/debug_solver/builder_update/continue_context/teacher_simple/analyst structures',
      '- Prompt Processing Time + Result Resolver: after safe prompt-processing, the same card shows elapsed time plus the exact visible result target before the real answer appears',
      '- Assistant Slide Typing: replies reveal with smoother GPT-like breathing cadence v7.5.6.6, punctuation/paragraph pauses, requestAnimationFrame batching, cheaper plain-text during typing, and final rich render',
      '- Codeblock Performance Builder: every code block scores speed/feedback/retry/QC/button handler readiness and can send a performance-upgrade prompt while preserving useful existing features',
      '- Codeblock Code Quality Engine v7.5.6.5: every code block scores structure/security/error/button-contract/maintainability/testing/data-flow quality and can send a production-quality upgrade prompt',
      '- Codeblock Universal Platform Builder v7.5.6.7: every code block can be expanded across games, web themes, utilities, apps, PWA, WordPress, API/backend, data/storage, responsive/browser/device support, deployment/package, QC, and global-standard handoff',
      '- Codeblock Beyond-Limit Value Engine v7.5.6.7: discovers hidden extension points, performance energy, automation loops, adaptive context, preview labs and safe unexpected capabilities so code blocks can build systems beyond ordinary expectations',
      '- Codeblock System Connection Engine v7.5.6.8: connects every button, command, handler, API, state/data flow, security, feedback, preview/debug, package and documentation into one relationship-aware system so code blocks become coherent products, not isolated snippets',
      '- AiRA Studio Nexus Engine v7.5.6.9: connects the whole Studio shell, composer, chat, code block, preview, debug, package, API center, rooms/memory, settings, commands and buttons into one performance-aware relationship network',
      '- AiRA Studio Communication Quality Engine v7.5.7.0: upgrades response clarity, tone, button/command meaning, API/error copy, user guidance, technical explanation, continuation context, accessibility language and truthful status reporting across the whole Studio',
      '- Auto Evolution Core v7.5.8.7: improves content progress, communication coherence, answer quality and answer performance automatically while leaving all buttons/data-action/action router untouched',
      '- Thought Prompt Compiler v7.5.8.10: converts rough thought/normal typing into a working prompt using relevant existing AiRA systems',
      '- System Command Matrix v7.5.8.10: passively reads every existing data-action as command inventory and uses it as prompt routing hints without rebinding buttons',
      '- Stable Rebase Guard v7.5.8.11: continues from 7.5.8.9 baseline and avoids unstable later composer/menu behavior',
      '- Composer Return Bottom Lock v7.5.8.11: when mobile keyboard closes, clears fixed inline styles and returns composer to the final bottom row immediately',
      '- Command Fusion Intelligence Engine v7.5.7.4: fuses every button, command, palette/color signal, API, state, preview and file output into reusable system capabilities such as sample theme files, design tokens, previews, adapters and QC maps',
      '',
      'CSS variables:',
      '--aira-adminbar=' + root.getPropertyValue('--aira-adminbar').trim(),
      '--aira-adminbar-bottom=' + root.getPropertyValue('--aira-adminbar-bottom').trim(),
      '--aira-shell-top=' + root.getPropertyValue('--aira-shell-top').trim(),
      '--aira-adminmenu=' + root.getPropertyValue('--aira-adminmenu').trim(),
      '--aira-vh=' + root.getPropertyValue('--aira-vh').trim(),
      '--aira-header=' + root.getPropertyValue('--aira-header').trim(),
      '--aira-composer=' + root.getPropertyValue('--aira-composer').trim(),
      '',
      'Required IDs:', ids.join(' | '),
      'Floating panels:', visible.join(' | '),
      'Detected code-first blocks in latest answer: ' + (state.lastCodeBlocks ? state.lastCodeBlocks.length : 0),
      'Context reader score: ' + ((state.contextProfile && state.contextProfile.communicationScore) || 'none'),
      'Answer render mode: prompt-processing slide first, completed processing-time + visible result target second, then assistant slide typing for normalized replies; instant render for image cards/reduced motion',
      'Auto question reader: ' + (state.contextProfile && state.contextProfile.questionReading ? state.contextProfile.questionReading.route + ' / ' + state.contextProfile.questionReading.connectors.join(' > ') : 'waiting for user input'),
      'Context reader intent/forms: ' + (state.contextProfile ? (state.contextProfile.intent + ' / ' + state.contextProfile.forms.join(',')) : 'none'),
      'data-action elements: ' + actionEls.length,
      'Unique data-actions: ' + Object.keys(actionEls.reduce(function(o,el){o[el.getAttribute('data-action')||'']=1; return o;},{})).length,
      '',
      'QC result:',
      '- UI layer duplication risk: reduced by clean slate render + legacy asset purge',
      '- Scroll jitter risk: reduced by removing active legacy runtime patches',
      '- Cross-device readiness: responsive grid + visualViewport measurement + mobile keyboard fusion lock + admin bar edge lock',
      '- Keyboard fusion: composer docks directly above mobile keyboard; plus menu opens as a popup above the dock',
      '- Runtime state: data-keyboard-fusion=' + ((app() && app().getAttribute('data-keyboard-fusion')) || 'off'),
      '- Current status: ready for WordPress live test after browser cache clear'
    ].join('\n');
  }

  function openWorkplace(tab){
    closeFloatingExcept('workplacePop'); openPanel('workplacePop');
    var title=byId('workplaceTitle'), sub=byId('workplaceSub'), body=byId('workplaceBody');
    if(title) title.textContent = tab ? ('Workplace · ' + tab) : 'Workplace';
    if(sub) sub.textContent = tab === 'context' || tab === 'behavior' || tab === 'interpret' || tab === 'question' || tab === 'result' ? 'อ่านบริบท ตีความ และบังคับผลลัพธ์ที่ต้องแสดงแบบ GPT โดยไม่เดาข้อมูลอ่อนไหว' : 'Clean Slate runtime: เปิดเฉพาะ panel เดียว ไม่ซ้อนกับ Composer';
    if(body){
      if(tab === 'interest'){
        if(sub) sub.textContent = 'เก็บสถิติข้อความที่สื่อสารดี น่าสนใจ หรือควรนำไปพัฒนาต่อจากปุ่มถูกใจใต้แชท';
        body.innerHTML = interestStatsHtml();
      } else if(tab === 'performance'){
        if(sub) sub.textContent = 'วัดประสิทธิภาพการทำงานจากแชทจริง โค้ด Preview Debug และ Next Loop พร้อม Mission ของแบรนด์แบบไม่เคลมเกินจริง';
        body.innerHTML = performanceImpactHtml();
      } else if(tab === 'identity'){
        if(sub) sub.textContent = 'เชื่อมห้องแชท Memory และสถิติด้วย Authorized ID ผู้มีสิทธิ์ เพื่อใช้ต่อได้แม้เปลี่ยนอุปกรณ์';
        body.innerHTML = authorizedIdentityHtml();
        loadAuthorizedIdentity();
      } else if(tab === 'context' || tab === 'behavior' || tab === 'interpret' || tab === 'question' || tab === 'result'){
        body.innerHTML = contextSummaryHtml(state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || '')) + '<p class="panel-actions"><button type="button" data-action="context-export">Export Context TXT</button><button type="button" data-action="open-settings">เปิด Real API Center</button></p>';
      } else {
        body.innerHTML = '<p><b>ระบบยังรักษาปุ่มเดิมไว้</b></p><p>แท็บนี้ใช้สำหรับรวมเครื่องมือ ' + esc(tab || 'overview') + ' โดยไม่สร้าง UI ซ้อนเพิ่ม</p><p><button type="button" data-action="open-settings">เปิด Real API Center</button> <button type="button" data-action="bug-all-export">Export Bug All TXT</button></p>';
      }
    }
  }
  function openUpdateCenter(){
    closeFloatingExcept('updateCenterPop'); openPanel('updateCenterPop');
    var body=byId('updateCenterBody'); if(body){ body.textContent = reportText('update-center'); }
  }
  function openLinkPreview(){ closeFloatingExcept('linkPreviewPop'); openPanel('linkPreviewPop'); }
  function openImagePreview(){ closeFloatingExcept('imagePreviewPop'); openPanel('imagePreviewPop'); }
  function openStructurePreview(){ closeFloatingExcept('structurePreviewPop'); openPanel('structurePreviewPop'); }

  function startVoice(){
    var bar = byId('composerVoiceBar'); setHidden(bar, false); if(bar) bar.classList.add('is-listening');
    var status = byId('composerVoiceStatus'), transcript = byId('composerVoiceTranscript');
    if(status) status.textContent = 'กำลังฟังเสียง';
    var SR = w.SpeechRecognition || w.webkitSpeechRecognition;
    if(!SR){ if(transcript) transcript.textContent = 'เบราว์เซอร์นี้ยังไม่รองรับ SpeechRecognition'; toast('เบราว์เซอร์นี้ยังไม่รองรับ SpeechRecognition','warn'); return; }
    try{
      if(state.recognition){ state.recognition.stop(); }
      var rec = new SR(); state.recognition = rec; state.listening = true;
      rec.lang = 'th-TH'; rec.interimResults = true; rec.continuous = false;
      rec.onresult = function(ev){
        var text=''; for(var i=ev.resultIndex;i<ev.results.length;i++){ text += ev.results[i][0].transcript; }
        if(transcript) transcript.textContent = text;
        var input=byId('composerInput'); if(input){ input.value = trim((input.value ? input.value + ' ' : '') + text); resizeInput(); }
      };
      rec.onerror = function(e){ toast('Voice error: ' + (e.error || 'unknown'), 'warn'); };
      rec.onend = function(){ state.listening=false; if(bar) bar.classList.remove('is-listening'); if(status) status.textContent='Voice พร้อม'; };
      rec.start();
    } catch(e){ toast('เปิดไมค์ไม่สำเร็จ: ' + e.message, 'error'); }
  }
  function stopVoice(){ try{ if(state.recognition) state.recognition.stop(); }catch(e){} state.listening=false; var bar=byId('composerVoiceBar'); if(bar) bar.classList.remove('is-listening'); }
  function closeVoice(){ stopVoice(); setHidden(byId('composerVoiceBar'), true); }

  function playAudio(){
    var text = state.lastAnswer || ''; if(!text){ toast('ยังไม่มีคำตอบล่าสุดสำหรับอ่านเสียง','warn'); return; }
    var dock = byId('composerAudioDock'); setHidden(dock,false); var audio=byId('composerAnswerAudio'); var status=byId('composerAudioStatus');
    if(status) status.textContent='กำลังสร้างเสียง...';
    ajax('ttsVoice', {text:text, lang:'th'}).then(function(res){
      var data=res && res.data ? res.data : {};
      if(data.audio && audio){ audio.src='data:'+(data.mime||'audio/mpeg')+';base64,'+data.audio; audio.hidden=false; audio.play().catch(function(){}); }
      if(status) status.textContent='พร้อมเล่น';
    }).catch(function(err){
      if(status) status.textContent='ใช้เสียงจากเบราว์เซอร์แทน';
      try{ var u=new SpeechSynthesisUtterance(text); u.lang='th-TH'; speechSynthesis.cancel(); speechSynthesis.speak(u); }catch(e){ toast('สร้างเสียงไม่สำเร็จ: '+(err.message||err),'error'); }
    });
  }
  function stopAudio(){ try{ var audio=byId('composerAnswerAudio'); if(audio){ audio.pause(); audio.currentTime=0; } if(w.speechSynthesis) speechSynthesis.cancel(); }catch(e){} }

  function messageNodeFromButton(btn){ return btn && btn.closest ? btn.closest('.aira-message') : null; }
  function messageIndexFromNode(node){ var n = Number(node && node.getAttribute('data-message-index')); return isFinite(n) ? n : -1; }
  function messageDataFromNode(node){
    var i = messageIndexFromNode(node);
    var room = activeRoom();
    var msg = (room.messages || [])[i] || null;
    if(!msg && node){
      var bubble = node.querySelector ? node.querySelector('.bubble') : null;
      var fallbackText = clean(node.__airaMessageText || (bubble ? (bubble.innerText || bubble.textContent || '') : ''));
      if(fallbackText){ msg = {role:roleFromNode(node) || 'assistant', text:fallbackText, meta:'transient', time:Date.now(), transient:true}; }
    }
    return {index:i, room:room, message:msg};
  }
  function copyText(text){
    text = clean(text);
    if(navigator.clipboard && navigator.clipboard.writeText){ return navigator.clipboard.writeText(text); }
    return new Promise(function(resolve, reject){
      try{ var ta=d.createElement('textarea'); ta.value=text; ta.setAttribute('readonly','readonly'); ta.style.position='fixed'; ta.style.left='-9999px'; d.body.appendChild(ta); ta.select(); d.execCommand('copy'); d.body.removeChild(ta); resolve(); }catch(e){ reject(e); }
    });
  }
  function copyMessage(btn){
    var info = messageDataFromNode(messageNodeFromButton(btn));
    var text = info.message ? info.message.text : '';
    copyText(text).then(function(){ toast('คัดลอกข้อความแล้ว','ok'); }).catch(function(){ toast('คัดลอกไม่สำเร็จ','error'); });
  }
  function composeFromAssistantAnswer(btn){
    var node = messageNodeFromButton(btn);
    var info = messageDataFromNode(node);
    var text = info.message ? (info.message.text || '') : '';
    if(!text){ toast('ไม่พบคำตอบสำหรับคุยต่อ', 'warn'); return; }
    var choices = extractContinuationChoices(text);
    var prompt = [
      'Answer',
      '',
      'คุยต่อจากคำตอบก่อนหน้าแบบต่อเนื่อง โดยเชื่อมบริบทเดิมและพาไปขั้นตอนถัดไปที่ใช้งานได้จริง',
      choices.length ? 'ถ้ามีตัวเลือก ให้ผมเลือกด้วยตัวเลข เช่น ' + choices.map(function(c){ return c.num; }).join(', ') : 'ถ้ามีตัวเลือก/เงื่อนไข ให้สรุปเป็นลำดับ 1, 2, 3 เพื่อให้เลือกคุยต่อได้',
      '',
      '[คำตอบก่อนหน้า]',
      clean(text).slice(0, 8000)
    ].join('\n');
    setComposerContinuationPrompt(prompt, 'Answer', 'ใส่ไอคอน Answer ใน Composer แล้ว พิมพ์ต่อได้เลย', 'brain', 'คุยต่อจากคำตอบเดิมโดยไม่แสดง prompt ยาวในช่องพิมพ์');
  }
  function composeFromMessageChoice(btn){
    var node = messageNodeFromButton(btn);
    var info = messageDataFromNode(node);
    var text = info.message ? (info.message.text || '') : '';
    var choice = {num:btn.getAttribute('data-choice-num') || '', label:btn.getAttribute('data-choice-label') || ''};
    if(!text || !choice.num){ toast('ไม่พบตัวเลือกสำหรับคุยต่อ', 'warn'); return; }
    var prompt = continuationPromptFromChoice(choice, text);
    setComposerNumberContinuationPrompt(prompt, choice.num, 'ใส่เลข ' + choice.num + ' ใน Composer แล้ว พิมพ์ต่อได้เลย', 'เลือกตัวเลือกจากคำตอบเดิมแบบซ่อน prompt และให้ผู้ใช้พิมพ์รายละเอียดเพิ่มได้');
  }

  function composeFromAnswerInsight(btn){
    var node = messageNodeFromButton(btn);
    var info = messageDataFromNode(node);
    var text = info.message ? (info.message.text || '') : '';
    var type = btn ? (btn.getAttribute('data-insight-type') || 'meaning') : 'meaning';
    if(!text){ toast('ไม่พบคำตอบสำหรับรับรู้ความหมาย', 'warn'); return; }
    var num = btn ? (btn.getAttribute('data-insight-number') || '') : '';
    var labelMap = {meaning:'อธิบายความหมาย', example:'ขอตัวอย่าง', condition:'แยกเงื่อนไข', apply:'ใช้ต่อ'};
    if(!num){
      var choices = extractContinuationChoices(text);
      var items = extractAnswerInsightItems(text);
      var idx = items.map(function(x){ return x.key; }).indexOf(type);
      num = String(Math.max(0, choices.length) + Math.max(0, idx) + 1);
    }
    setComposerNumberContinuationPrompt(answerInsightPrompt(type, text), num, 'ใส่เลข ' + num + ' ใน Composer แล้ว พิมพ์ต่อได้เลย', (labelMap[type] || 'รับรู้ความหมาย') + 'จากคำตอบเดิมแบบซ่อน prompt ยาว และเปิดให้ผู้ใช้พิมพ์คำถามเพิ่มได้');
  }
  function toggleMessageInterest(btn){
    var node = messageNodeFromButton(btn);
    var info = messageDataFromNode(node);
    if(!info.message || info.index < 0){ toast('ข้อความนี้ยังไม่พร้อมเก็บสถิติ', 'warn'); return; }
    var next = !info.message.interesting;
    info.message.interesting = next;
    info.message.interestUpdatedAt = Date.now();
    info.message.interestTopic = messageTopicForStats(info.message.text || '');
    if(node){ node.classList.toggle('is-interesting', next); }
    if(btn){
      btn.classList.toggle('is-liked', next);
      btn.setAttribute('aria-pressed', next ? 'true' : 'false');
      var span = btn.querySelector('span'); if(span) span.textContent = next ? 'ถูกใจแล้ว' : 'ถูกใจ';
    }
    saveLocal();
    var stats = saveInterestStats();
    var body = byId('workplaceBody'), title = byId('workplaceTitle');
    if(body && title && /interest/i.test(title.textContent || '')){ body.innerHTML = interestStatsHtml(); }
    toast(next ? ('เก็บสถิติข้อความน่าสนใจแล้ว · รวม ' + stats.total + ' ข้อความ') : ('ยกเลิกถูกใจแล้ว · เหลือ ' + stats.total + ' ข้อความ'), next ? 'ok' : 'warn');
  }
  function toggleMessageMore(btn){
    var node = messageNodeFromButton(btn); if(!node) return;
    node.classList.toggle('is-collapsed');
    var span = btn.querySelector('span');
    var opened = !node.classList.contains('is-collapsed');
    if(span) span.textContent = opened ? 'Show less' : 'Show more';
    var role = roleFromNode(node);
    btn.setAttribute('aria-label', opened ? (role === 'user' ? 'ย่อคำถาม' : 'ย่อคำตอบ') : (role === 'user' ? 'แสดงคำถามทั้งหมด' : 'แสดงคำตอบทั้งหมด'));
    btn.setAttribute('aria-expanded', opened ? 'true' : 'false');
    scrollBottom(false);
  }
  function stopOtherMessageAudio(currentNode){
    all('.message-audio-player').forEach(function(player){
      var node = player.closest('.aira-message');
      if(currentNode && node === currentNode) return;
      var audio = player.querySelector('audio');
      if(audio){ try{ audio.pause(); }catch(e){} }
      player.classList.remove('is-playing','is-loading');
      var status = player.querySelector('.message-audio-status');
      if(status) status.textContent = 'หยุดชั่วคราว';
    });
  }
  function ensureMessageAudioPlayer(node){
    if(!node) return null;
    var card = node.querySelector('.message-card');
    if(!card) return null;
    var player = card.querySelector('.message-audio-player');
    if(player) return player;
    player = d.createElement('div');
    player.className = 'message-audio-player';
    player.setAttribute('aria-live','polite');
    player.innerHTML = ''+
      '<div class="message-audio-top">'+
        '<span class="media-dot" aria-hidden="true"></span>'+
        '<div class="message-audio-copy"><b>เครื่องเล่นเสียงคำตอบนี้</b><small class="message-audio-status">พร้อมสร้างเสียง</small></div>'+
        '<button type="button" class="message-tool message-audio-close" data-action="message-audio-close" aria-label="ปิดเครื่องเล่นเสียง">×</button>'+
      '</div>'+
      '<audio class="message-audio-element" controls preload="none"></audio>'+
      '<div class="message-audio-actions">'+
        '<button type="button" class="message-tool" data-action="message-audio-stop">หยุดเสียง</button>'+
      '</div>';
    var tools = card.querySelector('.message-tools');
    if(tools && tools.nextSibling){ card.insertBefore(player, tools.nextSibling); }
    else { card.appendChild(player); }
    return player;
  }
  function setMessageAudioStatus(player, text, mode){
    if(!player) return;
    player.classList.toggle('is-loading', mode === 'loading');
    player.classList.toggle('is-playing', mode === 'playing');
    player.classList.toggle('is-fallback', mode === 'fallback');
    var status = player.querySelector('.message-audio-status');
    if(status) status.textContent = clean(text || '');
  }
  function stopMessageAudio(btn){
    var node = messageNodeFromButton(btn); if(!node) return;
    var player = node.querySelector('.message-audio-player');
    if(!player) return;
    var audio = player.querySelector('audio');
    try{ if(audio){ audio.pause(); audio.currentTime = 0; } if(w.speechSynthesis) speechSynthesis.cancel(); }catch(e){}
    setMessageAudioStatus(player, 'หยุดแล้ว', '');
  }
  function closeMessageAudio(btn){
    var node = messageNodeFromButton(btn); if(!node) return;
    stopMessageAudio(btn);
    var player = node.querySelector('.message-audio-player');
    if(player && player.parentNode){ player.parentNode.removeChild(player); }
    scheduleMeasure(); scrollBottom(false);
  }
  function playMessageAudio(btn){
    var node = messageNodeFromButton(btn);
    var info = messageDataFromNode(node);
    var text = info.message ? info.message.text : '';
    if(!node || !text){ toast('ไม่พบข้อความสำหรับเล่นเสียง','warn'); return; }
    state.lastAnswer = text;
    stopOtherMessageAudio(node);
    var player = ensureMessageAudioPlayer(node);
    if(!player){ toast('เปิดเครื่องเล่นเสียงไม่สำเร็จ','error'); return; }
    var audioEl = player.querySelector('audio');
    setMessageAudioStatus(player, 'กำลังสร้างเสียง... เครื่องเล่นจะดันพื้นที่ลงใต้คำตอบนี้', 'loading');
    if(audioEl){ audioEl.removeAttribute('hidden'); audioEl.controls = true; }
    var old = btn.innerHTML; btn.disabled = true; btn.innerHTML = icon('audio') + '<span>กำลังสร้าง</span>';
    scheduleMeasure(); scrollBottom(false);
    ajax('ttsVoice', {text:text, lang:'th'}).then(function(res){
      var data=res && res.data ? res.data : {};
      if(data.audio && audioEl){
        audioEl.src='data:'+(data.mime||'audio/mpeg')+';base64,'+data.audio;
        setMessageAudioStatus(player, 'พร้อมเล่นเสียงจาก API', 'playing');
        audioEl.play().catch(function(){ setMessageAudioStatus(player, 'แตะปุ่ม Play บนเครื่องเล่นเพื่อฟัง', ''); });
      } else { throw new Error('no_audio'); }
    }).catch(function(){
      setMessageAudioStatus(player, 'ใช้เสียงจากเบราว์เซอร์แทน — ปุ่มหยุดใช้งานได้', 'fallback');
      try{
        var u=new SpeechSynthesisUtterance(text); u.lang='th-TH';
        u.onend=function(){ setMessageAudioStatus(player, 'อ่านเสียงจบแล้ว', ''); };
        speechSynthesis.cancel(); speechSynthesis.speak(u);
      }catch(e){ toast('เล่นเสียงไม่สำเร็จ','error'); setMessageAudioStatus(player, 'เล่นเสียงไม่สำเร็จ', ''); }
    }).finally(function(){ btn.disabled=false; btn.innerHTML=old; scheduleMeasure(); scrollBottom(false); });
  }
  function editMessage(btn){
    var node = messageNodeFromButton(btn); var info = messageDataFromNode(node);
    var role = roleFromNode(node);
    if(!node || !info.message || role === 'system') return;
    if(node.classList.contains('is-editing')) return;
    node.classList.remove('is-collapsed'); node.classList.add('is-editing');
    var card = node.querySelector('.message-card'); var bubble = node.querySelector('.bubble'); var tools = node.querySelector('.message-tools');
    if(!card || !bubble || !tools) return;
    var original = clean(info.message.text);
    var editor = d.createElement('div'); editor.className = 'message-editor';
    var ta = d.createElement('textarea'); ta.value = original; ta.setAttribute('aria-label', role === 'user' ? 'แก้ไขข้อความผู้ถาม' : 'แก้ไขข้อความผู้ตอบ');
    var hint = d.createElement('small'); hint.className = 'message-editor-hint'; hint.textContent = role === 'user' ? 'แก้ไขข้อความผู้ถามในประวัติแชทนี้ — ถ้าต้องการให้ AiRA ตอบใหม่ ให้กดส่งคำถามอีกครั้ง' : 'แก้ไขข้อความผู้ตอบในประวัติแชทนี้';
    var actions = d.createElement('div'); actions.className = 'message-editor-actions';
    var save = d.createElement('button'); save.type='button'; save.className='message-tool is-primary'; save.setAttribute('data-action','message-edit-save'); save.innerHTML=icon('save') + '<span>บันทึก</span>';
    var cancel = d.createElement('button'); cancel.type='button'; cancel.className='message-tool'; cancel.setAttribute('data-action','message-edit-cancel'); cancel.innerHTML=icon('cancel') + '<span>ยกเลิก</span>';
    actions.appendChild(save); actions.appendChild(cancel); editor.appendChild(ta); editor.appendChild(hint); editor.appendChild(actions);
    bubble.hidden = true; tools.hidden = true; card.appendChild(editor); ta.focus({preventScroll:true});
  }
  function saveMessageEdit(btn){
    var node = messageNodeFromButton(btn); var info = messageDataFromNode(node); if(!node || !info.message) return;
    var role = roleFromNode(node);
    var ta = node.querySelector('.message-editor textarea'); var text = ta ? trim(ta.value) : '';
    if(!text){ toast('ข้อความว่างไม่ได้','warn'); return; }
    info.message.text = text; info.message.editedAt = Date.now(); info.room.updatedAt = Date.now(); state.messages = info.room.messages;
    if(role === 'assistant') state.lastAnswer = text;
    saveLocal(); renderChat(); toast(role === 'user' ? 'แก้ไขข้อความผู้ถามแล้ว' : 'แก้ไขข้อความผู้ตอบแล้ว','ok'); syncServer();
  }
  function cancelMessageEdit(btn){
    var node = messageNodeFromButton(btn); if(!node) return;
    var editor = node.querySelector('.message-editor'); if(editor && editor.parentNode) editor.parentNode.removeChild(editor);
    var bubble = node.querySelector('.bubble'); var tools = node.querySelector('.message-tools'); if(bubble) bubble.hidden=false; if(tools) tools.hidden=false; node.classList.remove('is-editing');
  }

  function imagePromptSeed(){
    return 'สร้างภาพสไตล์ Thinkb4do/AiRA: อธิบายภาพ, mood, แสง, สีเขียว-ขาว-ส้ม-ดำอย่างพอดี, มุมกล้อง, รายละเอียดสำคัญ, ขนาด square 1:1';
  }
  function imagePromptFromComposer(){
    var input = byId('composerInput');
    var text = input ? trim(input.value || '') : '';
    text = text.replace(/^สร้างภาพตามคำอธิบายนี้\s*[:：]?\s*/i, '');
    text = text.replace(/^สร้างภาพจริง\s*[:：]?\s*/i, '');
    return trim(text);
  }
  function imageFilenameFromPrompt(prompt, url){
    var ext = 'png';
    if(/^data:image\/svg\+xml/i.test(url || '')) ext = 'svg';
    else if(/\.svg(?:\?|$)/i.test(url || '')) ext = 'svg';
    else if(/\.webp(?:\?|$)/i.test(url || '')) ext = 'webp';
    else if(/\.jpe?g(?:\?|$)/i.test(url || '')) ext = 'jpg';
    var slug = trim(prompt || 'image').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'').slice(0,34);
    if(!slug) slug = 'image';
    return 'aira-image-' + slug + '.' + ext;
  }
  function setImagePanelLoading(prompt){
    closePanel('imagePreviewPop');
    state.imageLoadingMessage = addMessage('system', 'กำลังสร้างภาพจาก Prompt: ' + prompt, 'image generating', true);
    toast('กำลังสร้างภาพ', 'ok');
  }
  function fillImagePreviewPanel(payload, openIt){
    payload = payload || {};
    var url = payload.url || payload.image_url || payload.downloadUrl || payload.download_url || '';
    var downloadUrl = payload.downloadUrl || payload.download_url || url;
    var prompt = payload.prompt || imagePromptFromComposer();
    var real = !!(payload.realImage || payload.real_image || /^openai_images_api_real/.test(payload.mode || ''));
    var apiDebug = payload.apiDebug || payload.api_debug || '';
    state.currentImage = {url:url, downloadUrl:downloadUrl, prompt:prompt, mode:payload.mode || '', message:payload.message || '', saved:payload.saved || null, realImage:real, apiDebug:apiDebug};
    var pop = byId('imagePreviewPop'), img = byId('imagePreviewImg'), title = byId('imagePreviewTitle'), meta = byId('imagePreviewMeta'), note = byId('imagePreviewNote');
    if(pop){ pop.classList.remove('is-loading'); pop.classList.remove('is-image-load-error'); }
    if(title) title.textContent = real ? 'ภาพจริงจาก API' : 'ภาพตัวอย่างสำรอง';
    if(meta) meta.textContent = (payload.mode || 'image') + (real ? ' · real image' : ' · not real API image') + (payload.saved && payload.saved.stored ? ' · saved to uploads' : '');
    if(img){ if(url){ img.style.display='block'; img.src = url; } else { img.removeAttribute('src'); img.style.display='none'; } img.alt = prompt ? ('AiRA image: ' + prompt) : 'AiRA image'; img.onerror=function(){ this.style.display='none'; if(pop) pop.classList.add('is-image-load-error'); }; }
    if(note){
      note.innerHTML = '<b>Prompt</b><span>' + esc(prompt || '-') + '</span><small>' + esc(payload.message || 'แตะปุ่มบันทึก/ดาวน์โหลดภาพเพื่อเก็บไฟล์') + '</small>' + (apiDebug && !real ? '<small class="image-debug-note">Debug: '+esc(String(apiDebug).slice(0,260))+'</small>' : '');
    }
    if(openIt){ closeFloatingExcept('imagePreviewPop'); openPanel('imagePreviewPop'); }
  }
  function imagePayloadFromButton(btn){
    var card = btn && btn.closest ? btn.closest('.aira-generated-image-card') : null;
    var payload = card && card.__airaImageData ? card.__airaImageData : null;
    if(!payload){ payload = state.currentImage || {}; }
    return payload || {};
  }
  function openGeneratedImage(btn){
    var payload = imagePayloadFromButton(btn);
    if(!payload.url && !payload.downloadUrl){ toast('ไม่พบภาพสำหรับเปิด', 'warn'); return; }
    fillImagePreviewPanel(payload, true);
  }
  function showGeneratedImage(data){
    data = data || {};
    var url = data.image_url || data.preview_url || data.download_url || '';
    var prompt = data.prompt || imagePromptFromComposer();
    var payload = {url:url, downloadUrl:data.download_url || data.preview_url || url, prompt:prompt, mode:data.mode || 'image', realImage:!!data.real_image, message:data.message || (data.real_image ? 'สร้างภาพจริงเสร็จแล้ว' : 'แสดงภาพตัวอย่างสำรอง เพราะ Image API ยังไม่คืนภาพจริง'), saved:data.saved || null, apiDebug:data.api_debug || []};
    fillImagePreviewPanel(payload, false);
    if(state.imageLoadingMessage && state.imageLoadingMessage.parentNode){ state.imageLoadingMessage.parentNode.removeChild(state.imageLoadingMessage); }
    state.imageLoadingMessage = null;
    addMessage('assistant', imageCardMarker(payload), data.mode || now());
    toast(data.real_image ? 'สร้างภาพจริงเสร็จแล้ว · แตะภาพเพื่อบันทึก' : 'ภาพจริงยังไม่ขึ้น · แสดง preview พร้อมเหตุผลให้แล้ว', data.real_image ? 'ok' : 'warn');
  }
  function generateImageFromComposer(btn){
    if(state.imageGenerating){ toast('กำลังสร้างภาพอยู่', 'warn'); return; }
    var input = byId('composerInput');
    var prompt = imagePromptFromComposer();
    if(!prompt){
      if(input){ input.value = imagePromptSeed(); input.focus({preventScroll:true}); resizeInput(); }
      toast('ใส่รายละเอียดภาพก่อน แล้วกดสร้างภาพอีกครั้ง', 'warn');
      return;
    }
    state.imageGenerating = true;
    if(btn){ btn.disabled = true; btn.setAttribute('aria-busy','true'); }
    setImagePanelLoading(prompt);
    ajax('generateImage', {prompt: prompt}).then(function(res){
      var data = res && res.data ? res.data : {};
      showGeneratedImage(data);
    }).catch(function(err){
      var msg = 'สร้างภาพไม่สำเร็จ: ' + (err && err.message ? err.message : err);
      closePanel('imagePreviewPop');
      if(state.imageLoadingMessage && state.imageLoadingMessage.parentNode){ state.imageLoadingMessage.parentNode.removeChild(state.imageLoadingMessage); }
      state.imageLoadingMessage = null;
      addMessage('system', msg, 'image error');
      toast(msg, 'error');
    }).finally(function(){
      state.imageGenerating = false;
      if(btn){ btn.disabled = false; btn.removeAttribute('aria-busy'); }
      scheduleMeasure();
    });
  }
  function downloadCurrentImage(){
    var img = state.currentImage || {};
    var url = img.downloadUrl || img.url || (byId('imagePreviewImg') ? byId('imagePreviewImg').src : '');
    if(!url){ toast('ยังไม่มีภาพให้ดาวน์โหลด', 'warn'); return; }
    var a = d.createElement('a');
    a.href = url;
    a.download = imageFilenameFromPrompt(img.prompt || 'image', url);
    a.rel = 'noopener';
    d.body.appendChild(a); a.click();
    setTimeout(function(){ if(a.parentNode) a.parentNode.removeChild(a); }, 200);
  }
  function openCurrentImage(){
    var img = state.currentImage || {};
    if(!img.url && byId('imagePreviewImg')) img.url = byId('imagePreviewImg').src || '';
    if(!img.url){ toast('ยังไม่มีภาพให้เปิด', 'warn'); return; }
    fillImagePreviewPanel(img, true);
  }
  function imagePromptHelper(){
    var input = byId('composerInput');
    var seed = 'สร้างภาพจริง: ภาพแนว modern minimal AI assistant ของ AiRA ผู้หญิงอัจฉริยะ โทน Thinkb4do เขียว ขาว ส้ม ดำ แสงนุ่ม รายละเอียดคมชัด ใช้เป็นภาพประกอบเว็บไซต์ ไม่ใส่ตัวหนังสือ ไม่ใส่โลโก้หน่วยงานราชการ';
    if(input){ input.value = seed; input.focus({preventScroll:true}); resizeInput(); }
    toast('ใส่ Prompt ภาพตัวอย่างแล้ว', 'ok');
  }

  function uploadFiles(files){
    if(!files || !files.length) return;
    Array.prototype.slice.call(files).forEach(function(file){
      var fd = new FormData(); fd.append('file', file);
      ajax('uploadFile', fd, true).then(function(res){
        var data=res && res.data ? res.data : {};
        var input=byId('composerInput');
        if(data.is_image && data.attachment){
          state.pendingAttachments = state.pendingAttachments || [];
          if(state.pendingAttachments.length >= 3){ state.pendingAttachments.shift(); }
          state.pendingAttachments.push(data.attachment);
          renderAttachmentTray(); updateComposerContext();
          if(input && !trim(input.value || '')){ input.value = 'ช่วยตีความภาพนี้ อ่านข้อความในภาพ และสรุปสิ่งสำคัญ'; resizeInput(); }
          toast('แนบภาพแล้ว · ภาพจะแสดงในแชทเมื่อกดส่ง', 'ok');
        } else {
          toast('แนบไฟล์แล้ว: ' + (data.display || file.name), 'ok');
          if(input){ input.value = trim(input.value + '\n' + (data.prompt || ('[attached: ' + (data.name || file.name) + ']'))); resizeInput(); updateComposerContext(); }
        }
        setComposerMenuOpen(false);
      }).catch(function(err){ toast('อัปโหลดไม่สำเร็จ: ' + (err.message || err), 'error'); });
    });
  }


  function handleCodeBlockActionBridge(ev){
    var btn = ev.target && ev.target.closest ? ev.target.closest('[data-action]') : null;
    if(!btn || !btn.closest || !btn.closest('#airaApp')) return;
    var action = btn.getAttribute('data-action') || '';
    if(action.indexOf('code-') !== 0) return;
    var handled = true;
    var card = codeCardFromButton(btn);
    try {
      if(action === 'code-copy'){
        var cd = codeDataFromCard(card); copyText(cd.code).then(function(){ toast('คัดลอกโค้ดแล้ว','ok'); }).catch(function(){ toast('คัดลอกโค้ดไม่สำเร็จ','error'); });
      } else if(action === 'code-download'){
        var dd = codeDataFromCard(card); download(downloadNameForCode(dd.lang, dd.index), dd.code, mimeForLang(dd.lang));
      } else if(action === 'code-file-toggle'){
        toggleCodeFilePanel(card);
      } else if(action === 'code-wp-preinstall'){
        runPreinstallSandboxFromCard(card);
      } else if(action === 'code-live-debug'){
        runCodeLiveDebugFromCard(card);
      } else if(action === 'code-system-builder'){
        toggleSystemBuilderPanel(card);
      } else if(action === 'code-quality-boost'){
        runCodeQualityBoostFromCard(card);
      } else if(action === 'code-performance-boost'){
        runCodePerformanceBoostFromCard(card);
      } else if(action === 'code-system-builder-compose'){
        composeSystemBuilderPrompt(btn);
      } else if(action === 'code-system-builder-preview'){
        previewSystemBuilderFlow(btn);
      } else if(action === 'code-system-builder-copy'){
        copySystemBuilderReport(btn);
      } else if(action === 'code-system-builder-download'){
        downloadSystemBuilderReport(btn);
      } else if(action === 'code-build-success'){
        runCodeBuildSuccessFromCard(card);
      } else if(action === 'code-dev-master'){
        runCodeDevMasterFromCard(card);
      } else if(action === 'code-humanity-value'){
        runCodeHumanityValueFromCard(card);
      } else if(action === 'code-humanity-mission'){
        runCodeHumanityMissionFromCard(card);
      } else if(action === 'code-expand-capabilities'){
        runCodeCapabilityExpansionFromCard(card);
      } else if(action === 'code-universal-platform'){
        runCodeUniversalPlatformFromCard(card);
      } else if(action === 'code-beyond-limit'){
        runCodeBeyondLimitFromCard(card);
      } else if(action === 'code-system-connection'){
        runCodeSystemConnectionFromCard(card);
      } else if(action === 'code-studio-nexus'){
        runCodeStudioNexusFromCard(card);
      } else if(action === 'code-communication-quality'){
        runCodeCommunicationQualityFromCard(card);
      } else if(action === 'code-deep-cognitive-communication'){
        runCodeDeepCognitiveCommunicationFromCard(card);
      } else if(action === 'code-command-fusion'){
        runCodeCommandFusionFromCard(card);
      } else if(action === 'code-success-compose'){
        composeSuccessLoopPrompt(btn);
      } else if(action === 'code-success-copy'){
        copySuccessLoopReport(btn);
      } else if(action === 'code-success-send'){
        sendSuccessLoopPrompt(btn);
      } else if(action === 'code-live-fix'){
        sendCodeLiveFixFromCard(card);
      } else if(action === 'code-live-copy-report'){
        copyLiveDebugReport(btn);
      } else if(action === 'code-package-download'){
        downloadCodePackageFromCard(card);
      } else if(action === 'code-file-view'){
        setInlineCodePreview(card, false); toggleCodeFilePanel(card, true);
      } else if(action === 'code-wp-install'){
        installWordPressCodeFromCard(card);
      } else if(action === 'code-wp-upgrade'){
        upgradeWordPressCodeFromCard(card);
      } else if(action === 'code-wp-next'){
        sendCodeNextFromCard(card);
      } else if(action === 'code-wp-next-done'){
        markCodeNextSatisfied(card);
      } else if(action === 'code-preview'){
        openCodePreviewFromCard(card);
      } else if(action === 'code-preview-close'){
        closeCodePreview(card);
      } else if(action === 'code-preview-copy'){
        var pc = state.previewCode || {}; copyText(pc.code || '').then(function(){ toast('คัดลอกโค้ดจาก Preview แล้ว','ok'); }).catch(function(){ toast('คัดลอกไม่สำเร็จ','error'); });
      } else if(action === 'code-preview-download'){
        var pd = state.previewCode || {}; download(downloadNameForCode(pd.lang || 'text', pd.index || 0), pd.code || '', mimeForLang(pd.lang || 'text'));
      } else {
        handled = false;
      }
    } catch(e){
      console.error('[AiRA code action bridge]', e);
      toast('ปุ่ม Code Block ขัดข้อง: ' + ((e && e.message) ? e.message : 'unknown error'), 'error');
      var input = byId('composerInput');
      if(input){ input.value = trim(input.value + '\n[แก้ปุ่ม Code Block] ' + action + ' ไม่ทำงาน: ' + ((e && e.message) ? e.message : 'unknown')); resizeInput(); updateComposerContext(); }
    }
    if(handled){
      ev.preventDefault();
      ev.stopPropagation();
      if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
    }
  }

  function handleAction(ev){
    var btn = ev.target && ev.target.closest ? ev.target.closest('[data-action]') : null;
    if(!btn || !btn.closest('#airaApp')) return;
    var action = btn.getAttribute('data-action') || '';
    if(action){ ev.preventDefault(); }
    var input = byId('composerInput');
    if(action === 'send-chat'){ sendChat(); return; }
    if(action === 'composer-continuation-clear'){ clearComposerContinuation(false); return; }
    if(action === 'composer-menu'){ var menu=byId('composerMenu'); setComposerMenuOpen(hasHidden(menu)); return; }
    if(action === 'composer-menu-close'){ setComposerMenuOpen(false); return; }
    if(action === 'scroll-latest'){ scrollToLatest(); return; }
    if(action === 'quick-prompt'){ if(input){ input.value = btn.getAttribute('data-prompt') || btn.textContent || ''; input.focus({preventScroll:true}); resizeInput(); } return; }
    if(action === 'new-chat'){ createRoom('แชทใหม่', true); return; }
    if(action === 'open-rooms'){ closeFloatingExcept('roomsDrawer'); openPanel('roomsDrawer'); renderRooms(); return; }
    if(action === 'rooms-close'){ closePanel('roomsDrawer'); return; }
    if(action === 'room-new'){ createRoom('แชทใหม่', true); renderRooms(); return; }
    if(action === 'room-title-now'){ var r=activeRoom(); var first=(r.messages||[]).filter(function(m){return m.role==='user';})[0]; r.title = first ? first.text.slice(0,34) : 'แชทใหม่'; saveLocal(); renderRooms(); toast('ตั้งหัวข้อแล้ว','ok'); return; }
    if(action === 'room-open'){ state.activeRoomId=btn.getAttribute('data-room-id') || state.activeRoomId; saveLocal(); renderChat(); closePanel('roomsDrawer'); return; }
    if(action === 'room-delete'){ var id=btn.getAttribute('data-room-id'); state.rooms=state.rooms.filter(function(r){return r.id!==id;}); if(state.activeRoomId===id) state.activeRoomId=(state.rooms[0]&&state.rooms[0].id)||''; if(!state.rooms.length) createRoom('แชทใหม่', false); saveLocal(); renderRooms(); renderChat(); return; }
    if(action === 'open-settings'){ w.location.href = cfg.nativeSettingsUrl || '#'; return; }
    if(action === 'close-settings'){ closePanel('settings'); return; }
    if(action === 'toggle-artifact'){ toggleArtifact(); updateArtifact(state.lastAnswer); return; }
    if(action === 'artifact-tab'){ state.artifactTab=btn.getAttribute('data-tab')||'preview'; all('.artifact-tabs button').forEach(function(b){b.classList.toggle('is-active', b===btn);}); updateArtifact(state.lastAnswer); return; }
    if(action === 'preview-everything'){ var a=app(); if(a && !a.classList.contains('artifact-open')) a.classList.add('artifact-open'); state.artifactTab='preview'; all('.artifact-tabs button').forEach(function(b){b.classList.toggle('is-active', b.getAttribute('data-tab')==='preview');}); updateArtifact(state.lastAnswer || 'Preview จะทำงานเมื่อมีคำตอบหรือ code block'); return; }
    if(action === 'download-artifact'){ download('aira-answer.md', state.lastAnswer || 'No answer yet', 'text/markdown;charset=utf-8'); return; }
    if(action === 'download-code'){ download('aira-code.txt', state.lastCode || state.lastAnswer || 'No code yet'); return; }
    if(action === 'voice-open' || action === 'voice-talk'){ startVoice(); return; }
    if(action === 'voice-stop'){ stopVoice(); return; }
    if(action === 'voice-close'){ closeVoice(); return; }
    if(action === 'audio-toggle'){ playAudio(); return; }
    if(action === 'audio-stop'){ stopAudio(); return; }
    if(action === 'audio-close'){ stopAudio(); setHidden(byId('composerAudioDock'), true); return; }
    if(action === 'upload-file' || action === 'photo-to-text' || action === 'speech-image-text'){ var fi=byId('fileInput'); if(fi) fi.click(); return; }
    if(action === 'attachment-remove'){ var ri=Number(btn.getAttribute('data-attachment-index'))||0; (state.pendingAttachments||[]).splice(ri,1); renderAttachmentTray(); updateComposerContext(); return; }
    if(action === 'attachment-preview'){ openPendingAttachment(btn.getAttribute('data-attachment-index')); return; }
    if(action === 'workplace'){ openWorkplace(btn.getAttribute('data-tab') || 'overview'); return; }
    if(action === 'interpret-preview'){ openWorkplace('interpret'); return; }
    if(action === 'question-reader-preview'){ openWorkplace('question'); return; }
    if(action === 'result-resolver-preview'){ openWorkplace('result'); return; }
    if(action === 'interpret-mode'){
      var mode = btn.getAttribute('data-mode') || 'overview';
      if(input){
        var current = trim(input.value || '');
        var prefix = mode === 'short' ? 'ตีความสั้น แล้วแสดงผลลัพธ์แบบ GPT:\n' : 'ตีความภาพรวม แล้วแสดงผลลัพธ์แบบ GPT:\n';
        input.value = current ? (prefix + current.replace(/^ตีความ(ภาพรวม|สั้น)[\s\S]*?:\s*/iu,'')) : prefix;
        input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); setComposerMenuOpen(false);
        toast(mode === 'short' ? 'เปิดโหมดตีความสั้นแล้ว' : 'เปิดโหมดตีความภาพรวมแล้ว','ok');
      }
      return;
    }
    if(action === 'context-scan'){ openWorkplace('context'); return; }
    if(action === 'context-export'){ var cx = state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || ''); download('aira-context-reader.txt', formatCommunicationContext(cx)); return; }
      if(action === 'deep-communication-preview'){ var dc = state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || ''); toast(((dc.subtext||{}).responseMove || 'ตอบผลลัพธ์ก่อน'), 'ok'); return; }
      if(action === 'global-communication-preview'){ var gc = state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || ''); toast('Global Communication '+(((gc.globalCommunication||{}).score)||0)+'/100 · '+((((gc.globalCommunication||{}).layers)||[]).join(', ') || 'safe'), 'ok'); return; }
      if(action === 'communication-quality-preview'){ var cq = state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || ''); toast('Communication Quality '+((cq.communicationScore)||0)+'/100 · ตอบผลลัพธ์ก่อน รายละเอียดเท่าที่จำเป็น และมีขั้นตอนถัดไป', 'ok'); return; }
      if(action === 'code-master-preview'){ var cm = state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || ''); toast('Code Master '+(((cm.codeMaster||{}).score)||0)+'/100 · '+((((cm.codeMaster||{}).tracks)||[]).join(', ') || 'standby'), 'ok'); return; }
      if(action === 'universal-code-preview'){ var uc = state.contextProfile || buildAllFormContext((byId('composerInput') && byId('composerInput').value) || ''); toast('Universal Code '+(((uc.universalCode||{}).score)||0)+'/100 · '+((((uc.universalCode||{}).domains)||[]).join(', ') || 'General system'), 'ok'); return; }
    if(action === 'workplace-close'){ closePanel('workplacePop'); return; }
    if(action === 'updates-open'){ openUpdateCenter(); return; }
    if(action === 'updates-close'){ closePanel('updateCenterPop'); return; }
    if(action === 'updates-mark-all' || action === 'updates-clear-read'){ toast('อัปเดตสถานะในหน้าจอแล้ว','ok'); return; }
    if(action === 'bug-all-scan' || action === 'page-health-scan' || action === 'cross-device-scan' || action === 'visual-ui-scan' || action === 'smoothness-scan' || action === 'inspector-version-scan'){ openUpdateCenter(); return; }
    if(action === 'bug-all-export' || action === 'self-bug-export' || action === 'page-health-export' || action === 'cross-device-export' || action === 'visual-ui-export' || action === 'smoothness-export' || action === 'inspector-version-export'){ download('aira-clean-rebuild-report.txt', reportText(action)); return; }
    if(action === 'link-preview-close'){ closePanel('linkPreviewPop'); return; }
    if(action === 'image-preview-close'){ closePanel('imagePreviewPop'); return; }
    if(action === 'structure-preview-close'){ closePanel('structurePreviewPop'); return; }
    if(action === 'link-preview-use' || action === 'structure-preview-use'){ if(input){ input.value = trim(input.value + '\n' + (byId('linkPreviewBody') ? byId('linkPreviewBody').textContent : '')); resizeInput(); } return; }
    if(action === 'link-preview-open'){ toast('เปิดแท็บใหม่เมื่อมี URLจริง','warn'); return; }
    if(action === 'image-preview-open'){ openCurrentImage(); return; }
    if(action === 'generated-image-open'){ openGeneratedImage(btn); return; }
    if(action === 'image-prompt-helper'){ imagePromptHelper(); return; }
    if(action === 'generate-image'){ generateImageFromComposer(btn); return; }
    if(action === 'create-one-click'){ if(input){ input.value = 'สร้างระบบ/โปรเจกต์ใหม่แบบครบชุด: ทวนโจทย์ วางโครงระบบ แยก Dashboard/User/API/Data/Security/Files ทำ preview และสรุปวิธีใช้งาน'; input.focus({preventScroll:true}); resizeInput(); } return; }
    if(action === 'mode'){ var mode=btn.getAttribute('data-mode')||'full'; if(input){ input.value=trim(input.value + '\n[mode: '+mode+']'); resizeInput(); } toast('ตั้งโหมด: '+mode,'ok'); return; }
    if(action === 'device-preview'){ all('[data-action="device-preview"]').forEach(function(b){b.classList.toggle('is-active', b===btn);}); toast('Preview: '+(btn.getAttribute('data-device')||''),'ok'); return; }
    if(action === 'mini-browser-fetch' || action === 'web-structure-import' || action === 'web-code-import' || action === 'github-import' || action === 'reader-fetch'){ openLinkPreview(); return; }
    if(action === 'structure-preview-download'){ download('aira-structure-blueprint.txt', byId('structurePreviewBody') ? byId('structurePreviewBody').textContent : ''); return; }
    if(action === 'identity-export'){ download('aira-authorized-id-sync-report.txt', authorizedIdentityReportText()); return; }
    if(action === 'identity-sync-now'){ loadAuthorizedIdentity(function(){ syncServer(); toast('Sync Authorized ID แล้ว','ok'); }); return; }
    if(action === 'identity-rotate'){ if(!actions.rotateAuthorizedIdentity){ toast('ยังไม่มี endpoint เปลี่ยน Authorized ID','warn'); return; } if(!w.confirm('เปลี่ยน Authorized ID ใช่ไหม? ข้อมูลบนเซิร์ฟเวอร์ของบัญชีนี้ยังอยู่ แต่ localStorage จะใช้ namespace ใหม่')) return; ajax('rotateAuthorizedIdentity', {device_id:getDeviceId()}).then(function(res){ var id=res && res.data ? res.data.authorized_identity : null; if(id){ state.authorizedIdentity=id; saveLocal(); renderIdentityPanelIfOpen(); toast('เปลี่ยน Authorized ID แล้ว','ok'); } }).catch(function(){ toast('เปลี่ยน Authorized ID ไม่สำเร็จ','warn'); }); return; }
    if(action === 'interest-export'){ download('aira-communication-interest-stats.txt', interestStatsReportText()); return; }
    if(action === 'performance-export'){ download('aira-performance-impact-report.txt', performanceImpactReportText()); return; }
    if(action === 'performance-next'){ if(input){ input.value = trim(input.value + '\nเพิ่มประสิทธิภาพ AiRA ต่อ: ลดขั้นตอนที่ซ้ำ แก้บั๊กสดให้แม่นขึ้น แสดง Preview หน้าจริงก่อนส่งมอบ เก็บสถิติการสื่อสารที่ช่วยคนได้มากขึ้น และคงแบรนด์ให้ดูเป็นมืออาชีพโดยไม่เคลมว่าเป็นสินค้า GPT/OpenAI อย่างเป็นทางการ'); input.focus({preventScroll:true}); resizeInput(); updateComposerContext(); } toast('ใส่คำสั่ง Next เพิ่มประสิทธิภาพใน Composer แล้ว','ok'); return; }
    if(action === 'image-preview-download'){ downloadCurrentImage(); return; }
    if(action === 'code-copy'){ var cd=codeDataFromCard(codeCardFromButton(btn)); copyText(cd.code).then(function(){ toast('คัดลอกโค้ดแล้ว','ok'); }).catch(function(){ toast('คัดลอกโค้ดไม่สำเร็จ','error'); }); return; }
    if(action === 'code-download'){ var dd=codeDataFromCard(codeCardFromButton(btn)); download(downloadNameForCode(dd.lang, dd.index), dd.code, mimeForLang(dd.lang)); return; }
    if(action === 'code-file-toggle'){ toggleCodeFilePanel(codeCardFromButton(btn)); return; }
    if(action === 'code-wp-preinstall'){ runPreinstallSandboxFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-live-debug'){ runCodeLiveDebugFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-system-builder'){ toggleSystemBuilderPanel(codeCardFromButton(btn)); return; }
    if(action === 'code-performance-boost'){ runCodePerformanceBoostFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-system-builder-compose'){ composeSystemBuilderPrompt(btn); return; }
    if(action === 'code-system-builder-preview'){ previewSystemBuilderFlow(btn); return; }
    if(action === 'code-system-builder-copy'){ copySystemBuilderReport(btn); return; }
    if(action === 'code-system-builder-download'){ downloadSystemBuilderReport(btn); return; }
    if(action === 'code-build-success'){ runCodeBuildSuccessFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-dev-master'){ runCodeDevMasterFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-humanity-value'){ runCodeHumanityValueFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-humanity-mission'){ runCodeHumanityMissionFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-expand-capabilities'){ runCodeCapabilityExpansionFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-universal-platform'){ runCodeUniversalPlatformFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-beyond-limit'){ runCodeBeyondLimitFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-system-connection'){ runCodeSystemConnectionFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-studio-nexus'){ runCodeStudioNexusFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-communication-quality'){ runCodeCommunicationQualityFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-deep-cognitive-communication'){ runCodeDeepCognitiveCommunicationFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-success-compose'){ composeSuccessLoopPrompt(btn); return; }
    if(action === 'code-success-copy'){ copySuccessLoopReport(btn); return; }
    if(action === 'code-success-send'){ sendSuccessLoopPrompt(btn); return; }
    if(action === 'code-live-fix'){ sendCodeLiveFixFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-live-copy-report'){ copyLiveDebugReport(btn); return; }
    if(action === 'code-package-download'){ downloadCodePackageFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-file-view'){ var viewCard=codeCardFromButton(btn); setInlineCodePreview(viewCard, false); toggleCodeFilePanel(viewCard, true); return; }
    if(action === 'code-wp-install'){ installWordPressCodeFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-wp-upgrade'){ upgradeWordPressCodeFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-wp-next'){ sendCodeNextFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-wp-next-done'){ markCodeNextSatisfied(codeCardFromButton(btn)); return; }
    if(action === 'code-preview'){ openCodePreviewFromCard(codeCardFromButton(btn)); return; }
    if(action === 'code-preview-close'){ closeCodePreview(codeCardFromButton(btn)); return; }
    if(action === 'code-preview-copy'){ var pc=state.previewCode || {}; copyText(pc.code || '').then(function(){ toast('คัดลอกโค้ดจาก Preview แล้ว','ok'); }).catch(function(){ toast('คัดลอกไม่สำเร็จ','error'); }); return; }
    if(action === 'code-preview-download'){ var pd=state.previewCode || {}; download(downloadNameForCode(pd.lang || 'text', pd.index || 0), pd.code || '', mimeForLang(pd.lang || 'text')); return; }
    if(action === 'artifact-file-download'){ var ai=Number(btn.getAttribute('data-code-index'))||0; var ab=(state.lastCodeBlocks||[])[ai]; if(ab){ download(downloadNameForCode(ab.lang, ai), ab.code, mimeForLang(ab.lang)); } return; }
    if(action === 'message-answer-continue'){ composeFromAssistantAnswer(btn); return; }
    if(action === 'message-choice-compose'){ composeFromMessageChoice(btn); return; }
    if(action === 'message-answer-insight'){ composeFromAnswerInsight(btn); return; }
    if(action === 'message-copy'){ copyMessage(btn); return; }
    if(action === 'message-like'){ toggleMessageInterest(btn); return; }
    if(action === 'message-show-more'){ toggleMessageMore(btn); return; }
    if(action === 'message-audio'){ playMessageAudio(btn); return; }
    if(action === 'message-audio-stop'){ stopMessageAudio(btn); return; }
    if(action === 'message-audio-close'){ closeMessageAudio(btn); return; }
    if(action === 'message-edit'){ editMessage(btn); return; }
    if(action === 'message-edit-save'){ saveMessageEdit(btn); return; }
    if(action === 'message-edit-cancel'){ cancelMessageEdit(btn); return; }
    // Safe fallback: keep all existing buttons useful without invoking old stacked UI.
    if(input){ input.value = trim(input.value + '\n[' + action + '] ' + trim(btn.textContent)); input.focus({preventScroll:true}); resizeInput(); }
    toast('ส่งคำสั่งเข้าช่องพิมพ์แล้ว: ' + action, 'ok');
  }

  function bind(){
    d.addEventListener('click', handleCodeBlockActionBridge, true);
    d.addEventListener('click', handleAction, false);
    d.addEventListener('wheel', handleComposerMenuWheelLock, {passive:false, capture:true});
    w.addEventListener('message', handleSandboxMessage, false);
    d.addEventListener('keydown', function(ev){
      var input=byId('composerInput');
      if(ev.target === input && ev.key === 'Enter' && !ev.shiftKey){ ev.preventDefault(); sendChat(); }
      if(ev.key === 'Escape'){ closeFloatingExcept(''); setComposerMenuOpen(false); }
    }, false);
    var input=byId('composerInput');
    if(input){ input.addEventListener('input', function(){ resizeInput(); updateComposerContext(); }, false); input.addEventListener('focus', function(){ scheduleMeasure(); keyboardAnchorBurst(); setTimeout(function(){ measure(); scrollBottom(false); }, 70); setTimeout(function(){ measure(); scrollBottom(false); }, 220); setTimeout(function(){ measure(); scrollBottom(false); }, 520); setTimeout(function(){ measure(); scrollBottom(false); }, 860); }, false); input.addEventListener('blur', function(){ setTimeout(scheduleMeasure, 80); setTimeout(function(){ measure(); syncKeyboardFusion(false); resetComposerDockPosition(); }, 220); setTimeout(function(){ measure(); syncKeyboardFusion(false); resetComposerDockPosition(); }, 520); }, false); }
    var fi=byId('fileInput'); if(fi){ fi.addEventListener('change', function(){ uploadFiles(fi.files); fi.value=''; }, false); } renderAttachmentTray();
    ['resize','orientationchange','scroll','pageshow'].forEach(function(ev){ w.addEventListener(ev, scheduleMeasure, {passive:true}); });
    ['scroll','touchstart','touchmove','pointerdown','focusin','focusout'].forEach(function(ev){ d.addEventListener(ev, scheduleMeasure, {passive:true}); });
    if(w.visualViewport){ w.visualViewport.addEventListener('resize', function(){ scheduleMeasure(); keyboardAnchorBurst(); }, {passive:true}); w.visualViewport.addEventListener('scroll', function(){ scheduleMeasure(); keyboardAnchorBurst(); }, {passive:true}); }
    setTimeout(measure, 120); setTimeout(measure, 420); setTimeout(measure, 900);
    var log=chatLog(); if(log){ log.addEventListener('scroll', updateScrollLatestButton, {passive:true}); }
    d.addEventListener('pointerdown', function(ev){
      var a = app(); var menu = byId('composerMenu');
      if(!a || !menu || hasHidden(menu)) return;
      var t = ev.target;
      if(t && t.closest && (t.closest('#composerMenu') || t.closest('[data-action="composer-menu"]') || t.closest('#composerMenuBackdrop'))){ return; }
      if(t && a.contains(t)){ setComposerMenuOpen(false); }
    }, true);
  }

  function boot(){
    var a=app(); if(!a) return;
    purgeLegacyDOMAssets();
    a.setAttribute('data-clean-runtime', VERSION);
    d.documentElement.classList.add('aira-clean-slate-ready');
    state.authorizedIdentity = cfg.authorizedIdentity || null; getDeviceId(); loadLocal(); loadInterestStats(); loadPerformanceEvents(); if(!state.rooms.length) createRoom('แชทใหม่', false);
    renderChat(); renderRooms(); ensureScrollLatestButton(); ensureComposerBackdrop(); ensureCodePreviewPanel(); bind(); syncComposerMenuState(); measure(); resizeInput(); updateComposerContext(); updateScrollLatestButton(); loadAuthorizedIdentity(function(){ loadServer(); });
    setTimeout(measure, 120); setTimeout(measure, 450);
    state.booted = true;
  }
  if(d.readyState === 'loading'){ d.addEventListener('DOMContentLoaded', boot, {once:true}); } else { boot(); }
  w.AiRACleanSlate744 = {version:VERSION, measure:measure, resizeInput:resizeInput, report:reportText, identity:authorizedIdentityReportText, state:state, syncComposerMenuState:syncComposerMenuState, lockAdminBarHeader:measure};
})();
