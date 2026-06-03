AiRA Studio Safe Repair 7.5.8.41

ไฟล์นี้ทำขึ้นเพื่อแก้ปัญหาเมื่อติดตั้ง/Activate AiRA Studio รุ่นเดิมไม่ได้ หรือมีปลั๊กอินเก่าค้างจนลบยาก

สิ่งที่ปรับแล้ว:
1) เปลี่ยนโฟลเดอร์เป็น aira-studio-safe-v75841 เพื่อไม่ชนกับโฟลเดอร์เดิม
2) เปลี่ยน class / slug / nonce / AJAX action prefix เพื่อเลี่ยงชนกับรุ่นเก่า
3) แก้ add_option ให้ไม่ autoload ข้อมูลก้อนใหญ่ ลดโอกาสแอดมินหน่วง
4) เพิ่ม uninstall cleanup ให้ล้าง option สำคัญครบขึ้น
5) ตรวจ PHP lint แล้ว ไม่พบ syntax error

วิธีติดตั้ง:
1) ไปที่ WordPress > Plugins > Add New > Upload Plugin
2) อัปโหลดไฟล์ ZIP นี้
3) กด Activate ที่ AiRA Studio Safe Repair

ถ้ารุ่นเก่ายังค้างและลบไม่ได้:
1) เข้า File Manager / FTP / Hosting Panel
2) ไปที่ wp-content/plugins/
3) เปลี่ยนชื่อโฟลเดอร์ปลั๊กอินเก่าที่เสีย เช่น aira-studio เป็น aira-studio-disabled
4) กลับเข้า WordPress > Plugins แล้วลบรายการเก่า
5) ใช้ AiRA Studio Safe Repair แทน

หมายเหตุ: ถ้ามี fatal error จากปลั๊กอินเก่าที่ยัง active อยู่ การอัปโหลดรุ่นใหม่นี้อาจยังไม่ปิดตัวเก่าให้เอง ต้องเปลี่ยนชื่อโฟลเดอร์เก่าผ่าน File Manager ก่อน
