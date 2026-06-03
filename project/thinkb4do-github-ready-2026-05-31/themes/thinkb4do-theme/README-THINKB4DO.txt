Thinkb4do Theme v1.4.6

สถานะ: Beta / กำลังพัฒนา

สิ่งที่ปรับในเวอร์ชันนี้:
1. เพิ่มระบบข้อความกำลังพัฒนาแบบแก้ไขได้จาก WordPress
   ไปที่ Appearance > Customize > Thinkb4do Settings > ข้อความกำลังพัฒนา

2. กระจายข้อความสถานะตามตำแหน่งสำคัญของเว็บแบบไม่ซ้ำกัน
   ครอบคลุมส่วนหัว, ปุ่มแจ้งเตือน, หน้าแรก, ระบบสินค้า, ชุมชน, หน้าไกด์, Sidebar, Footer และหน้าสมาชิกตัวอย่าง

3. เพิ่มหน้า Thinkb4do Guide ภายใน WordPress Admin
   ไปที่ Appearance > Thinkb4do Guide
   มีปุ่มลัดสำหรับเปิดหน้าแก้ข้อความกำลังพัฒนา

4. เพิ่มหน้า Template สำหรับไกด์หน้าเว็บ
   สร้าง Page ใหม่ แล้วเลือก Template: หน้าไกด์ระบบ Thinkb4do

5. เพิ่มตัวเลือก Sticky Header
   ไปที่ Appearance > Customize > Thinkb4do Settings > ส่วนหัว / Sticky Header
   เลือกได้ 3 แบบ:
   - ตรึงทุกหน้า
   - ตรึงเฉพาะหน้าแรก
   - ไม่ตรึงส่วนหัว

6. ปรับปุ่มแจ้งเตือนให้กดได้
   ตอนนี้เป็น popup ตัวอย่างสำหรับแจ้งสถานะระบบว่าเว็บไซต์อยู่ระหว่างพัฒนา

การตรวจเบื้องต้น:
- PHP lint ผ่านทุกไฟล์
- JavaScript syntax check ผ่าน
- โครงสร้างธีมพร้อมติดตั้งทดสอบใน WordPress

หมายเหตุ:
ปุ่มบางส่วนยังเป็น UI ตัวอย่าง เช่น ปุ่มกรองสินค้า ปุ่มดูทั้งหมดบางจุด ปุ่มโซเชียลเริ่มต้น และปุ่มดาวน์โหลดแอป ต้องเชื่อมระบบจริงภายหลัง


=== v1.4.4 Honest DBD Registration Display ===
เพิ่มส่วนแสดงข้อมูลจดทะเบียนพาณิชย์ / DBD สำหรับใช้กับเว็บ Thinkb4do อย่างจริงใจ

ตั้งค่าได้ที่:
Appearance → Customize → Thinkb4do Settings → ข้อมูลจดทะเบียน / DBD

สิ่งที่รองรับ:
- เลือกสถานะ: ไม่แสดง / กำลังเตรียมข้อมูล / อยู่ระหว่างยื่น / จดทะเบียนแล้ว
- กรอกชื่อผู้ประกอบการ, เลขทะเบียนจริง, วันที่จดทะเบียน, สำนักงานที่จดทะเบียน, เว็บไซต์
- ใส่ลิงก์ตรวจสอบ DBD/DBD Registered และรูปเครื่องหมายที่ได้รับจริงภายหลัง
- แสดงใน Footer และหน้าไกด์ระบบได้
- มี Shortcode: [thinkb4do_dbd_info]

หมายเหตุสำคัญ:
ห้ามกรอกเลขทะเบียนสมมติหรือแสดงเครื่องหมาย DBD เหมือนจดทะเบียนแล้ว หากยังไม่ได้รับอนุญาต/ยังไม่มีข้อมูลจริง ให้ใช้สถานะ "กำลังเตรียมข้อมูลจดทะเบียน" หรือ "อยู่ระหว่างยื่น/รอตรวจสอบ"


V1.4.4 Global Trust UI
- ปรับข้อมูล DBD จากกล่องใหญ่แบบราชการเป็น Trust Badge แบบสั้นบนหน้าแรกและ Footer
- เพิ่ม Template: Trust & Registration สำหรับแสดงรายละเอียดทางกฎหมาย/ความน่าเชื่อถือแยกหน้า
- เพิ่ม shortcode [thinkb4do_dbd_info layout="compact"] สำหรับวาง badge แบบสั้น
- ข้อมูลยังแก้ได้จาก Customizer และยังไม่ใส่เลขสมมติ


=== v1.4.5 Premium Brand UI ===
- ปรับหน้าแรกเป็นแนวแบรนด์พรีเมียม: Hero ใหญ่, mockup ระบบ, Product Suite 6 ช่อง, Timeline และ Newsletter
- เปลี่ยนส่วนความน่าเชื่อถือให้ไม่ใช้โลโก้ DBD/เครื่องหมายราชการก่อนจดทะเบียนหรือได้รับอนุมัติจริง
- เพิ่ม info mascot/illustration แทนรูปทีมงานจริง เพื่อไม่ทำให้เข้าใจผิดว่านั่นคือทีมจริง
- ปรับ Trust Center ให้ใช้ข้อความโปร่งใส ตรวจสอบได้ และกรอกข้อมูลจริงภายหลังจาก Customizer
- เมนู fallback ปรับให้สั้นขึ้น: หน้าแรก, ระบบ, ชุมชน, สมาชิก, บทความ, ความน่าเชื่อถือ
- ตรวจ PHP/JS ก่อนแพ็ก ZIP


=== v1.4.6 Logo Media Library ===
- เพิ่มระบบเลือกโลโก้หลักจาก WordPress Media Library
- ตั้งค่าได้ที่ Appearance → Customize → Thinkb4do Settings → โลโก้ / อัตลักษณ์แบรนด์
- รองรับการแสดงโลโก้อัตโนมัติใน Header และ Footer
- รองรับ fallback โลโก้ตัวอักษร T หากยังไม่ได้อัปโหลดโลโก้จริง
- เลือกได้ว่าจะแสดงชื่อเว็บข้างโลโก้หรือไม่
- เลือกได้ว่าจะแสดง Tagline ใต้โลโก้ใน Header/Footer หรือไม่
- เพิ่มคำอธิบายใน Thinkb4do Guide เพื่อให้ทีมงานรู้ว่าต้องตั้งค่าโลโก้จาก Media Library

วิธีใช้โลโก้:
1. ไปที่ Appearance → Customize
2. เปิด Thinkb4do Settings → โลโก้ / อัตลักษณ์แบรนด์
3. กดเลือกโลโก้หลักจาก Media Library
4. Publish เพื่อบันทึก
5. โลโก้จะแสดงบน Header และ Footer ทุกหน้าที่ใช้ธีมนี้

=== v1.4.8 Universal Compatibility ===
เพิ่มระบบรองรับการใช้งานหลายอุปกรณ์/หลายเบราว์เซอร์/หลายบริบท:
- เพิ่ม js/compat.js เป็น ES5 bootstrap สำหรับ no-js/js class, ตรวจ CSS Grid, Sticky, Fetch, Touch, OS, Reduced Motion และ Forced Colors
- เพิ่ม CSS fallback สำหรับกรณีไม่รองรับ CSS Grid, Sticky Header, Aspect Ratio และ Fetch
- เพิ่ม viewport-fit=cover และ safe-area inset สำหรับมือถือที่มีรอยบาก/ขอบโค้ง
- เพิ่ม prefers-reduced-motion, forced-colors, print CSS และ focus-visible ที่ชัดขึ้น
- เพิ่มส่วน Universal Compatibility บนหน้าแรก พร้อมแก้ข้อความจาก Customizer ได้
- เพิ่มหน้า Template: Universal Support / รองรับทุกระบบ
- เพิ่ม Shortcode: [thinkb4do_compatibility]
- เพิ่ม Customizer: Thinkb4do Settings → รองรับทุกอุปกรณ์ / Universal
- อัปเดต font stack เป็น Noto Sans Thai, Roboto, Kanit และ system fonts เพื่อรองรับภาษาไทย/สากลมากขึ้น

หมายเหตุ: เป้าหมายคือรองรับเบราว์เซอร์หลักรุ่นปัจจุบันและ fallback สำหรับอุปกรณ์/เบราว์เซอร์เก่า ไม่ได้หมายความว่าฟีเจอร์ขั้นสูงทุกอย่างจะทำงานเหมือนกัน 100% ในเบราว์เซอร์เก่ามาก


=== v1.4.8 Mobile Compatibility Hotfix ===
- แก้ปัญหาแจ้งเตือนเบราว์เซอร์เก่าแบบผิดพลาดบนมือถือ/Chrome/WebView รุ่นใหม่
- ปรับ Header เมื่อเข้าใช้งานจากมือถือและมี WordPress Admin Bar
- ลดความสูงแถบข้อความกำลังพัฒนาบนจอแคบ
- เพิ่ม guard ป้องกัน layout ล้นแนวนอนบน Android WebView/Chrome
- ปรับ hero mobile ให้ปุ่มและข้อความไม่ถูกบีบหรือดูเหมือนโดนตัด


=== v1.4.9 Sticky Scroll Safe ===
- แก้อาการ Sticky Header ทับ/ลอยผิดตำแหน่งตอนเลื่อนบนมือถือ
- คำนวณระยะ top ของ WordPress Admin Bar จากตำแหน่งจริง ไม่ใช้ค่าตายตัว
- เพิ่ม fixed fallback เฉพาะมือถือเมื่อ sticky ของ Android/WebView เพี้ยน
- ลดขนาด Header ตอนเลื่อนและปรับ Hero mobile ไม่ให้หัวข้อถูกตัด
- หลีกเลี่ยง overflow-x บน body/html ที่ทำให้ sticky เพี้ยนในบางเบราว์เซอร์


== v1.5.1 Actual Device Responsive Fix ==
- แก้กรณีมือถือ/แท็บเล็ตเปิดโหมด Desktop site แล้วหน้าเว็บย่อเล็กเหมือนเดสก์ท็อป
- เพิ่มคลาส tb4-actual-mobile จากขนาดหน้าจอจริง ไม่อิง CSS viewport อย่างเดียว
- บังคับ header, hero, grid, timeline, footer ให้ใช้ layout มือถือเมื่ออุปกรณ์จริงเป็นมือถือ
- เพิ่ม syncStickyOffset ใน main.js เพื่อแก้ runtime error และคำนวณ admin bar/header height จริง
- ปรับ WordPress admin bar ให้ใช้งานง่ายขึ้นบนมือถือจริง


=== v1.5.1 404 Mobile Safe ===
- ปรับหน้า 404 ใหม่ให้ไม่ล้นบนมือถือ
- ลดขนาดการ์ดเมนูลัดและบังคับ grid ให้ปลอดภัยบนจอเล็ก
- ปรับช่องค้นหาและปุ่มให้เรียง 1 คอลัมน์บนมือถือ
- เพิ่ม CSS สำหรับอุปกรณ์จริงเมื่อเปิด Desktop site บนมือถือ

v1.6.9 Force Fixed Header Lock
- ส่วนหัวธีมตรึงแบบ fixed ระหว่างเลื่อนหน้า
- แถบสถานะกำลังพัฒนาตรึงใต้ส่วนหัว
- เพิ่มการชดเชยพื้นที่ด้านบนเพื่อไม่ให้เนื้อหาถูกทับ


[v1.9.5] เพิ่ม WordPress Native Edit Assist: เมนู 🛠 แก้ไขหน้า, Dock WP แก้หน้า, ปุ่มแก้หน้า WP ใน Live Edit โดยไม่ใช้ Gutenberg
