Thinkb4do Community Desktop Right Sidebar v4.9.8

สิ่งที่ปรับในเวอร์ชันนี้
- แก้ “กระแสโหวต” ให้ไม่ว่าง: ถ้าจับโพสต์ Poll/โหวตไม่ได้ ระบบจะดึงโพสต์ยอดนิยมจากฟีดมาแสดงแทนทันที
- ปรับ Live Scan ให้ไม่ถูกบล็อกโดย class/layout ของธีม เช่น menu/search/sidebar ที่ครอบทั้งหน้า
- ดึงลำดับความนิยมจาก reaction, comment, like, share, view และค่าความนิยมอื่น ๆ ที่มีในฟีด
- ถ้ามีคะแนนโหวตจริง จะแสดง + / - และจำนวนโหวต
- ถ้ายังไม่มีคะแนนโหวตจริง จะแสดงเป็น “ยอดนิยมจากฟีด” หรือ “จากฟีด”
- ไม่ดึงจากกล่องสร้างโพสต์ / popup composer / dialog
- โครงแสดงผล: เลขลำดับ / หัวข้อ+ชื่อย่อยซ้าย / แถบโหลดสีตรงกลาง / จำนวนด้านขวา
- หน้าเว็บไม่แสดงข้อความเกี่ยวกับระบบภายใน

หลังติดตั้ง: อัปโหลด ZIP ทับตัวเดิม แล้วเปิดหน้าชุมชนใหม่อีกครั้ง หากยังเห็นค่าเก่า ให้ล้าง cache ของเว็บ/เบราว์เซอร์

Changelog 4.9.8
- Guaranteed Vote Trend fallback from real feed/popular posts
- Relaxed over-strict DOM blockers
- Added server-side popular feed fallback
- Added client-side popular feed fallback
- ตรวจ PHP syntax ผ่าน
