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

Thinkb4do Community Feed Main v4.2.236

หน้าที่หลัก:
- ปลั๊กอินเฉพาะหน้าฟีดหลัก /community/
- Shortcode: [thinkb4do_community] หรือ [thinkb4do_feed_main]
- ใช้ข้อมูลโพสต์เดิม post type: tb4_community_post
- มี Composer, Reels, ฟีดโพสต์, โหลดเพิ่มเติม AJAX

วิธีใช้:
1. ปิดปลั๊กอิน Thinkb4do Community ตัวรวมเดิมก่อน เพื่อลดการซ้อนกัน
2. ติดตั้งและเปิดใช้ปลั๊กอินนี้
3. ตรวจหน้า /community/ หรือใส่ shortcode [thinkb4do_community]

หมายเหตุ:
- ไม่ลบข้อมูลเดิม
- Sidebar ซ้าย/ขวายังคงเป็นปลั๊กอินแยกตามงานก่อนหน้า

อัปเดต v4.2.236:
- ตัดการเชื่อม /member-area/ ออกจาก Feed Main
- ไม่เพิ่มเมนูพื้นที่สมาชิกจากปลั๊กอินฟีดหลัก
- ไม่สร้าง/ซิงก์หน้าสมาชิกจากปุ่มซิงก์ของ Feed Main
- ไม่ลงทะเบียน block thinkb4do/member-area เพื่อไม่ซ้อนกับปลั๊กอินสมาชิก

อัปเดต v4.2.236:
- ตัดระบบ member/follow/profile ที่ยังค้างใน Feed Main ออกเพิ่ม
- ปิดการโหลด assets ของ Feed Main บนหน้า member-area/members/profile surfaces
- เอาตัวเลือก visibility เฉพาะสมาชิกออกจาก Composer
- Feed Main จะไม่ hook avatar/profile/follow AJAX อีกต่อไป

v4.2.270 เพิ่มเติม:
- หน้าแสดงความคิดเห็นถูกแยกเป็นปลั๊กอิน Thinkb4do Community Comment Page
- Feed Main ไม่ override single-community-post.php แล้ว
- เพื่อลด UI เพี้ยน ให้ใช้งานคู่กับ comment page plugin เวอร์ชัน 1.0.0 ขึ้นไป
