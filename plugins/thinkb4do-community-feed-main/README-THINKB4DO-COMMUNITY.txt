- v4.2.278: ปรับพื้นหลัง popup เป็นความเข้มมาตรฐานแบบ soft dim และแก้การ์ดภาพเดี่ยวให้สูงตามสัดส่วนภาพจริง ไม่บังคับ min-height/aspect-ratio เก่า
- v4.2.277: ปรับ popup รูปภาพพื้นหลังดำเต็มจอให้เบาลง ลดอาการหน่วง โดยยังอยู่ใต้ header/menu และยังคลิกภาพได้เหมือนเดิม
- v4.2.274: แก้กดภาพ Popup ไม่ติดทุกภาพด้วย hard delegation ระดับ window และเพิ่ม data trigger ให้ภาพจาก PHP โดยตรง พร้อมคงการลากเลื่อนหน้า
- v4.2.273: แก้กดดูภาพ Popup ไม่ขึ้น และแยกแตะภาพ/ลากเลื่อนหน้า เพื่อให้ลากผ่านภาพแล้ว scroll ได้ปกติ
- v4.2.272: เชื่อมคลิกอวาตาร์/ชื่อ user ไปหน้าสมาชิกผ่าน /member-area/?tb4c_user=ID โดย Feed Main ยังไม่สร้างหน้าสมาชิกเอง
อัปเดต v4.2.271 — Feed Main Only / Link Bridge
- ลบไฟล์หน้าแสดงความคิดเห็น/Single Post และ comments template ออกจากแพ็ก Feed Main
- หน้าสมาชิกยังไม่ถูกสร้าง ไม่ถูกแสดง และไม่ถูกโหลด asset จาก Feed Main
- คงลิงก์โพสต์/คอมเมนต์/สมาชิกไว้ผ่าน Link Bridge เพื่อให้ปลั๊กอินแยกหรือธีมรับช่วงต่อได้
- ปุ่มคอมเมนต์ยังชี้ไป permalink + #comments ไม่ตัดการเชื่อมต่อเดิม

Thinkb4do Community Feed Main v4.2.266
- ปรับหน้าฟีดให้ครบความกว้างแกนกลาง 760px ตามขีดแดง และบังคับทุกส่วนหลักให้กว้างเท่ากัน
- ปรับหน้าแสดงความคิดเห็น/Single Post ให้กว้างเท่าหน้าฟีด ทั้งโพสต์ รายละเอียด คอมเมนต์ และช่องพิมพ์ความคิดเห็น
- เอาเมนู/หน้าสมาชิกออกจาก Feed Main บนหน้าชุมชน
- คงปุ่ม Next ซ้าย/ขวาให้ Reel และตัวควบคุม Reel แบบไม่พึ่ง legacy controller

Thinkb4do Community Feed Main v4.2.242
- เพิ่ม Touch Scroll Safe เพื่อแก้จุดแตะแล้วเลื่อนไม่ได้บนมือถือ
- ปลด scroll lock ที่ค้าง และปิด overlay ที่ซ่อนอยู่ไม่ให้ดักการแตะ

Thinkb4do Community v4.2.144

อัปเดตนี้ปรับ UI/UX ให้สะอาด น่าใช้งาน และยังคงเบา โดยเน้น CSS patch เป็นหลัก ไม่เพิ่มไลบรารีหนัก และไม่เปลี่ยนระบบโพสต์/คอมเมนต์เดิม

สิ่งที่ปรับ:
- การ์ด ฟีด ปุ่ม เมนู และ Composer ดูเรียบขึ้น อ่านง่ายขึ้น
- ปุ่มบนมือถือแตะง่ายขึ้น และลดข้อความในปุ่มย่อยของ Composer เพื่อลดความรก
- เพิ่ม focus state สำหรับคีย์บอร์ด/การเข้าถึง
- คงระบบ runtime เดิมและโหลดเบาเหมือนเดิม


v4.2.144 — Social Network Light Layout
- ปรับ layout ให้เป็น social network มากขึ้น: sidebar ซ้าย, feed กลางเด่น, sidebar ขวาเบา
- แต่ง feed/post/composer/actions ให้สะอาดขึ้น ใช้ง่ายขึ้น และยังคงเบา
- ใช้ CSS-only patch ไม่เพิ่ม library ใหม่ และไม่แตะระบบโพสต์/คอมเมนต์หลัก

v4.2.147 — Focus Social Polish
- ย้าย Composer ขึ้นก่อน Reels เพื่อให้เริ่มโพสต์/ใช้งานได้เร็วขึ้นแบบ social network
- เอาแถบฟิลเตอร์กลางและ panel สร้างโพสต์/กฎด้านซ้ายที่ซ้ำกับส่วนอื่นออก
- รวม sidebar ขวาเป็นกล่องแนะนำเดียว ลดการ์ดซ้ำและลดความแน่นของ layout
- ปรับขนาดการ์ด ฟีด Reels ปุ่ม และ mobile spacing ให้เบาและสวยขึ้นโดยใช้ CSS patch เป็นหลัก


v4.2.147 — Focus Social Polish
- ปรับ layout desktop ให้เป็น social network มากขึ้น: ซ้ายเป็น icon rail, กลางเป็น feed หลัก, ขวาเป็นกล่องแนะนำแบบเบา
- ลดส่วนซ้ำเพิ่มเติม: ตัด story chip สำหรับสร้างโพสต์ที่ซ้ำกับ composer และปุ่มลอยมือถือ
- ลดความสูง Reels/Story rail ให้ไม่แย่งพื้นที่ feed
- ปรับ composer ให้สั้น ใช้งานง่าย และเบาขึ้น
- จำกัด tag ที่แสดงบนการ์ดโพสต์ เพื่อลดความรก
- ปรับ action row บนมือถือให้ไม่ล้นและซ่อนข้อมูลรองที่ไม่จำเป็น


## v4.2.148 Beauty Lite UI/UX
- ตกแต่ง Social Network layout ให้สวยขึ้น แต่ยังเบา
- ลดน้ำหนักเงา/เอฟเฟกต์หนัก และไม่เพิ่มไลบรารีใหม่
- ปรับ Feed, Composer, Reels, Sidebar และ Mobile spacing ให้อ่านง่ายขึ้น
- เหมาะสำหรับติดตั้งทับเวอร์ชันเดิม


## v4.2.150 Airy Beauty Lite UI/UX
- ตกแต่ง UI/UX ให้สวยขึ้นแบบโปร่ง เบา และอ่านง่ายกว่าเดิม
- ลดน้ำหนักเงา/blur/filter และใช้ CSS-only patch เพื่อไม่เพิ่มภาระ JS
- ปรับ Composer, Feed Card, Reels, Sidebar และ Mobile Bottom Nav ให้ดูสะอาดขึ้น
- คงโครง Social Network: ซ้ายเมนู / กลางฟีด / ขวาแนะนำ โดยลดความแน่นของสายตา
- เหมาะสำหรับติดตั้งทับเวอร์ชันเดิม


== v4.2.150 Complete Beauty Lite + Smart Back ==
- ตกแต่ง UI/UX ให้ครบทุกพื้นผิวหลัก: Community, Feed, Composer, Post Card, Reels, Sidebar, Comment, Modal, Member Area, Single Post และ Home Feed
- คงแนว Social Network: เมนูซ้าย / Feed กลาง / แนะนำขวา พร้อมเวอร์ชันมือถือแบบแถบเลื่อน
- เพิ่มปุ่มย้อนกลับอัจฉริยะ เมื่อผู้ใช้คลิกเปลี่ยนหน้าภายในชุมชนหรือพื้นที่ที่เกี่ยวข้อง
- ใช้ CSS override + JS ขนาดเล็ก ไม่เพิ่มไลบรารีใหม่ ไม่เพิ่ม blur/filter หนัก

== v4.2.151 Complete Beauty Polish ==
- ตกแต่ง UI/UX ให้ครบทุกจุดหลักของ Community, Feed, Member Area, Single Post, Home Feed, Composer, Reels, Comments และ Mobile Nav
- คงความเบาด้วย CSS patch เป็นหลัก ไม่เพิ่มไลบรารี และเพิ่ม JS เพียงสำหรับ class marker
- ปรับ Layout แบบ Social Network ให้สวยขึ้น อ่านง่ายขึ้น และลดความรกบนมือถือ
- คงปุ่มย้อนกลับ Smart Back จาก v4.2.150 ไว้


V4.2.155 Google Plus Lite Rebuild
- รื้อหัวฟีดใหม่เป็น Thinkb4do Stream แบบ Google Plus Lite
- เปลี่ยนภาพรวมเป็นการ์ดขาว เส้นบาง เงาเบา ลด blur/filter
- คงโครง social network: เมนูซ้าย / ฟีดกลาง / แนะนำขวา
- เพิ่ม inline CSS เฉพาะหน้าเพื่อให้เห็นผลจริง แม้ cache เก่าบางส่วนยังอยู่
- คง Smart Back / ปุ่มย้อนกลับเดิม


v4.2.156 — Google Plus Clean Rebuild
- รื้อ Community UI ใหม่ให้เป็น social stream แบบ Google Plus Lite มากขึ้น
- เปลี่ยน header เป็น Thinkb4do Community / Google Plus Lite พร้อม search และ circles
- ปรับ layout เป็นซ้าย Circles / กลาง Stream / ขวา Discover แบบเบา
- ลดเงา blur filter และ animation หนัก โดยใช้ card shadow ระดับ 1px
- ปรับ composer, post card, action row, comment preview, sidebar, form/modal/mobile ให้เข้าชุดเดียวกัน
- เพิ่ม class และ inline CSS v42156 ที่ template โดยตรง เพื่อให้เห็นผลชัดและลดปัญหา cache/selector เก่าทับ


Update v4.2.157: Social Size Lock - ล็อกขนาด layout/card/button/media/mobile ให้เป็น social feed ชัดขึ้นและเบาขึ้น.


## v4.2.163 — Left Menu Clean Lite
- เอา scrollbar เมนูซ้ายออก
- รวมเมนูซ้ายให้เหลือการ์ดเดียว ไม่ซ้ำกับหัวเมนูเดิม
- คง layout social network แบบเบา และไม่เพิ่มไลบรารีใหม่


[v4.2.165] Feed Size Lock Lite: แก้ขนาดฟีด/วิดีโอ/iframe/Reels ไม่ให้ล้น และซ่อน Reel viewer เมื่อยังไม่เปิดใช้งาน

[v4.2.166] Aligned Social UI Lite: รื้อชั้นตำแหน่งซ้าย/กลาง/ขวาให้ตรงแนวส่วนหัวมากขึ้น โหลด CSS ใหม่ท้ายสุด และยังคง Feed Size Lock จาก v4.2.165 ไว้.


## v4.2.169 — Header Edge Align Lite
- จัดเมนูซ้ายและขวาให้ตรงแนวส่วนหัวมากขึ้น โดยคงขนาด Feed และ Sidebar Transparent เดิม
- เพิ่ม `assets/community-header-edge-align-lite.css` โหลดท้ายสุด เพื่อทับตำแหน่งเก่าที่ซ้อนกัน

v4.2.186 — Better Desk/Phone UI + Icon Clean
- ปรับ UI/UX Desktop/Phone เพิ่มเติมโดยไม่ขยับ layout หลัก
- ลดไอคอนซ้ำด้วยการซ่อน fallback icon/emoji ที่ซ้อนกับ Phosphor icon
- เพิ่มไฟล์ assets/community-desk-phone-v2.css เป็น final layer โหลดท้ายสุด

== v4.2.187 Clean UI Reset ==
- ล้างเลเยอร์ UI ที่ซ้อนจากหลายรอบก่อนหน้าในเส้นทาง enqueue ให้เหลือ final visual layer หลักตัวเดียว: assets/community-clean-ui-reset.css
- ไม่โหลด CSS patch ชุด v4.2.164/165/184/185/186 อีก เพื่อลดการตีกันของตำแหน่ง ขนาด และไอคอน
- จัด Desktop เป็นซ้าย / Feed กลาง / ขวา ที่นิ่งและเบากว่าเดิม
- Phone ใช้เมนูแนวนอนด้านบนและ Feed เต็มความกว้าง อ่านง่ายขึ้น
- เอาพื้นหลัง/เส้น/เงาของ Sidebar ซ้ายและขวาออก
- ลดไอคอนซ้ำโดยซ่อน fallback icon และบังคับ icon renderer ให้เหลือชุดเดียว
- Feed กลางถูกจัดเป็น social card system: Composer, Reels, Post Card, Action Row, Comment Preview


== v4.2.188 Complete Decoration ==
- เก็บ UI/UX ครบทุกจุดใน final layer เดียว โดยไม่ขยับ layout ซ้าย/กลาง/ขวาใหม่
- ปรับ Desktop/Phone, Feed, Composer, Reels, Post Card, Sidebar, ปุ่ม, Tag, Comment Preview และ Reel Viewer
- ลดไอคอนซ้ำและคุมพื้นหลัง Sidebar ให้เบา


[v4.2.192] Clean UI Rebuild: รื้อ UI/UX ใหม่ให้เบา ใช้เลเยอร์เดียว คุม Desktop/Phone, Feed, Composer, Reels, Post Card และ Comment Preview ให้เป็น Social Network ที่อ่านง่ายขึ้น.

[v4.2.194] UI/UX Calm Social Rebuild: รื้อเลเยอร์ UI อีกครั้งให้โล่งขึ้น ใช้สีแบรนด์ Think Control (#1E6B45 / #F97316) คุม Desktop เป็น 3 คอลัมน์นิ่งขึ้น มือถือเป็นฟีดกลางอ่านง่ายขึ้น ลดข้อความเทคนิคบนหน้าผู้ใช้ และจัดปุ่ม/แท็ก/คอมเมนต์ไม่ให้ทับกัน.

[v4.2.195] UI/UX Clean Position Lock: รื้อผิว UI ให้สะอาดขึ้นโดยคงตำแหน่งซ้าย/กลาง/ขวาเดิมก่อน ลดสถิติ/ข้อความรอง/ปุ่มรองที่ซ้ำ ปรับการ์ด ฟีด Composer Reels และปุ่มใต้โพสต์ให้เรียบขึ้น พร้อมล็อก grid desktop 220/700/260 และ mobile order เดิม.

[v4.2.198] Cleaner Light Position Lock: รื้อ UI/UX ให้สะอาดและเบากว่าเดิม โดยยังคงตำแหน่งซ้าย/กลาง/ขวาเดิมก่อน ลดพื้นหลังไล่สีหนัก เงาเข้ม สถิติบนหัวฟีด ปุ่มรองซ้ำ ปุ่มติดตามบนการ์ด และทำ Composer/Reels/Post Card ให้ compact ขึ้น พร้อม cache bust version ใหม่ 4.2.198.
[v4.2.198] Cleaner Light Position Lock: รื้อ UI/UX ให้สะอาดและเบากว่าเดิม โดยยังคงตำแหน่งซ้าย/กลาง/ขวาเดิมก่อน ลดเงา กรอบ ระยะที่แน่น ปุ่ม/ชิป/สถิติซ้ำ และทำ Header / Composer / Reels / Post Card / Right Rail ให้เบาขึ้น พร้อม cache bust version ใหม่ 4.2.198.


[v4.2.198] Deep Clean Position Lock: ตัดส่วนซ้ำใน DOM, ลดชิป/ปุ่ม/สถิติซ้ำ, คง layout ซ้าย-กลาง-ขวาเดิม.


[v4.2.200] Essential Focus Position Lock: removed lower mobile menu, cleaned duplicate feed/sidebar DOM, kept left/center/right positions stable.

[v4.2.200] Essential Focus Position Lock: removed non-essential duplicate UI first, removed bottom menu/floating create, simplified composer/reels/right rail, kept left-center-right positions stable.


[v4.2.201] Core Focus Clean: รื้อส่วนซ้ำ/ไม่จำเป็นออกเพิ่มอีกขั้น เอาเมนูส่วนล่างออกต่อเนื่อง เน้นให้ส่วนสำคัญชัดขึ้น (สร้างโพสต์ / รีลเด่น / โพสต์ล่าสุด / หัวข้อสำคัญ) โดยยังคงตำแหน่งซ้าย-กลาง-ขวาเดิมไว้ก่อน.


[v4.2.202] Essential Member Comment Clean: รื้อ UI/UX เพิ่มอีกชั้น ตัดเมนูล่างต่อเนื่อง ลดหน้าแสดงความคิดเห็นให้เหลือ count + trigger + popup และลดหน้าสมาชิกให้เหลือ hero, stats, tabs สำคัญ, posts/comments/saved/settings โดยถอด sidebar สมาชิกที่ซ้ำออก.

[v4.2.203] Reel Viewer Position Fix: แก้ส่วน Thinkb4do Reels ที่หลุดมาแสดงในหน้าเป็น inline, ซ่อน viewer จนกว่าจะกดเปิด, จัดปุ่ม X / ก่อนหน้า / ถัดไป ให้อยู่ใน modal และไม่เบียดฟีดหลักบนมือถือ.

[v4.2.204] Video Card Placement Fix: แก้ปุ่ม “เล่นวิดีโอ” และปุ่ม “หยุด/ปิด” ที่หลุดลงมาใต้ YouTube ให้กลับไปอยู่ในตำแหน่งถูกต้อง โดยปุ่มเล่นเป็น overlay กลางวิดีโอ และ mini-player controls จะแสดงเฉพาะตอนเป็นวิดีโอเล็กเท่านั้น.

[v4.2.206] External Video Cover Pull: ดึงปกวิดีโอจากแหล่งภายนอกจริง เช่น YouTube/Vimeo ให้แสดงบนการ์ดวิดีโอ แก้กรณี oEmbed/iframe แสดงเป็นปกสีเทา โดยใช้ JS เสริมค้นหา video id จาก iframe/srcdoc/data-src และตั้งปกให้ทันทีบนหน้าเว็บ.

## v4.2.207 — Video Cover Refresh + Inline Owner Tools
- พัฒนาระบบดึงปกวิดีโอใหม่ให้แม่นขึ้นจาก YouTube/Vimeo/oEmbed/Gutenberg/iframe และข้อมูล meta ที่เกี่ยวข้อง
- เพิ่มการแสดงปกด้วย `<img>` พร้อม JS fallback เลือกภาพถัดไปเมื่อปก HD ใช้ไม่ได้หรือเป็น placeholder
- ปรับการ์ดวิดีโอให้คงปุ่มเล่นกลางภาพและปุ่ม “เล่นวิดีโอ” ตำแหน่งเดิมแบบไม่ซ้อน
- จัดไอคอนแก้ไขและไอคอนลบให้อยู่บรรทัดเดียวกับชื่อผู้ใช้บนการ์ดโพสต์
- ตรวจ PHP/JS/CSS แล้ว พร้อมติดตั้งทับเวอร์ชันก่อนหน้า


## v4.2.210
- เพิ่มความกว้างการ์ดและวิดีโอให้ใกล้ Instagram feed มากขึ้น
- เพิ่ม Type/Tag strip ใต้รายละเอียด ก่อนภาพหรือวิดีโอ
- ปรับ action row ให้เบาและสะอาดขึ้นตามโทน Thinkb4do


อัปเดต 4.2.212: เอาขอบภาพ/วิดีโอให้คมขึ้น และปรับ Featured Reels ให้ขนาดพอดีคล้าย Instagram มากขึ้น

อัปเดต 4.2.213: บังคับเอาขอบโค้งของภาพ/วิดีโอที่แสดงในฟีดออกให้เป็นมุมคมทั้งหมด

อัปเดต 4.2.214: จัดหน้าแสดงความคิดเห็นให้เข้ากับ mood & tone ของ feed และเหลือปุ่มกลับฟีดเพียงปุ่มเดียว

อัปเดต 4.2.215: บังคับเอาขอบโค้งของภาพ/วิดีโอในฟีดและหน้าแสดงความคิดเห็นออกให้เป็นมุมคม

อัปเดต 4.2.216: เพิ่มความกว้างการ์ดฟีด/คอมเมนต์ให้ใช้พื้นที่จอมากขึ้นบนมือถือ

อัปเดต 4.2.217: เพิ่มความกว้างการ์ดให้ใกล้ภาพอ้างอิงมากขึ้น ทั้ง feed และหน้าแสดงความคิดเห็น

อัปเดต 4.2.218: ขยายพื้นที่การ์ดด้านในให้กว้างขึ้น โดยเฉพาะหน้าแสดงความคิดเห็นและ media ด้านใน

อัปเดต 4.2.219: ขยายรูป/วิดีโอให้เต็มการ์ด และเพิ่มความกว้างการ์ดด้านในอีกเล็กน้อย

อัปเดต 4.2.220: พัฒนาหน้าแสดงความคิดเห็นตาม reference ให้กว้างขึ้น สะอาดขึ้น รูป/วิดีโอเต็มขึ้น และเหลือปุ่มกลับฟีดเดียว

อัปเดต 4.2.228: จัดโปรไฟล์ผู้ตอบให้อยู่ตำแหน่ง user, ย้ายแก้ไข/ลบไปแถวชื่อ และเอาเส้นจุดล่างช่องคอมเมนต์ออก

อัปเดต 4.2.230: ย้ายปุ่มแก้ไข/ลบของผู้โพสต์ไปขวาของแถว user และเอาเงาการ์ดออก

[v4.2.231] Main Community No Sidebar: เอา Sidebar ซ้าย/ขวาออกจากปลั๊กอินชุมชนหลัก เหลือ feed กลางเท่านั้น และเตรียมให้ใช้ปลั๊กอิน Sidebar แยกแทน


---
Update v4.2.262: Clean single real-media video controller. Removed stacked mini-video engines and legacy mini initialization.
