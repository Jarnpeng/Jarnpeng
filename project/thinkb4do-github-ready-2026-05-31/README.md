# Thinkb4do GitHub Ready Package

ชุดไฟล์นี้เตรียมไว้สำหรับนำขึ้น GitHub ของโปรเจกต์ Thinkb4do โดยจัดเป็นโครงสร้างที่อ่านง่าย แยก Plugin และ Theme ชัดเจน

## โครงสร้างหลัก

```text
plugins/
  aira-studio-safe-v75841/
  aira-suite-export-plugin/
  thinkb4do-plugin-bug-inspector/
  thinkb4do-community-feed-main/
  thinkb4do-community-members-v4.2.283-feed-main-width-touch-fix/
  thinkb4do-community-comment-page/
  thinkb4do-header-menu/
  thinkb4do-community-left-sidebar-desktop/
  thinkb4do-community-right-sidebar-desktop/
  thinkb4do-discovery/
  thinkb4do-products/
  thinkb4do-gate-rebrand/

themes/
  thinkb4do-theme/

docs/
  PROJECT_STRUCTURE.md
  CHECKLIST-BEFORE-UPLOAD.md
```

## วิธีเอาขึ้น GitHub แบบง่าย

1. แตกไฟล์ ZIP นี้ก่อน
2. เข้า GitHub แล้วสร้าง Repository ใหม่ เช่น `thinkb4do-web-suite`
3. อัปโหลดไฟล์และโฟลเดอร์ทั้งหมดที่อยู่ข้างในโฟลเดอร์นี้ขึ้น GitHub
4. กด Commit changes
5. เวลาจะให้ Codex/AI ช่วยแก้ ให้ชี้ไปที่ repository นี้

## หมายเหตุสำคัญ

- ใช้ Member ตัวล่าสุด: `thinkb4do-community-members-v4.2.283-feed-main-width-touch-fix`
- ไม่รวม Member เวอร์ชันเก่าหลายตัว เพื่อป้องกันสับสนและแก้ผิดไฟล์
- โครงนี้ไม่รื้อระบบเดิม แยก plugin/theme ตามที่มีอยู่
- ก่อนอัปขึ้นเว็บจริง ควรทดสอบใน Local หรือ Staging ก่อน
