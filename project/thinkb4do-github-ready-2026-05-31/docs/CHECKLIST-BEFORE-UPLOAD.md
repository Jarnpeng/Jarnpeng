# Checklist Before Upload to GitHub

## ก่อนอัปโหลด

- [ ] แตก ZIP ก่อนอัปโหลด ไม่ควรอัปทั้ง ZIP เป็นไฟล์เดียวใน repository หลัก
- [ ] ตรวจว่าโฟลเดอร์ `plugins/` และ `themes/` อยู่ครบ
- [ ] ใช้ Member เวอร์ชันล่าสุด v4.2.283 เท่านั้น
- [ ] ไม่ใส่ไฟล์สำรองซ้ำหลายเวอร์ชันถ้าไม่จำเป็น

## หลังอัปโหลด

- [ ] เปิด GitHub repo แล้วดูว่าไฟล์ PHP/CSS/JS อ่านได้ปกติ
- [ ] ให้ Codex อ่าน README ก่อนเริ่มแก้
- [ ] เวลาสั่งแก้หน้า Member ให้ระบุ path ของปลั๊กอิน Member ล่าสุด
- [ ] ทดสอบ Desktop / Tablet / Mobile หลังแก้ทุกครั้ง

## Prompt สั้นสำหรับ Codex

แก้เฉพาะหน้า Member ที่ path `plugins/thinkb4do-community-members-v4.2.283-feed-main-width-touch-fix/` โดยใช้ Feed Main ที่ path `plugins/thinkb4do-community-feed-main/` เป็นมาตรฐาน ห้ามรื้อ Header / Comment / Sidebar / Theme และห้ามทำระบบเดิมพัง
