=== Thinkb4do Plugin Bug Inspector ===
Contributors: thinkb4do
Tags: debug, plugin scanner, quality assurance, export, security, relationship, xray
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.4.0
License: GPLv2 or later

Thinkb4do Plugin Bug Inspector ตรวจบั๊กปลั๊กอิน WordPress, ตรวจความสัมพันธ์ภายในระบบ, อ่านเป้าหมายของระบบ, ทำ System X-Ray, ตรวจคุณภาพตามมาตรฐาน, ตรวจ Browser/Device/System Compatibility และ export รายงานเป็นไฟล์ text ได้

== Description ==

ระบบนี้ถูกออกแบบสำหรับเจ้าของเว็บและนักพัฒนาที่ต้องการตรวจปลั๊กอินก่อนใช้งานจริง โดยเน้น Safe Static Mode คืออ่านไฟล์และวิเคราะห์โครงสร้าง ไม่รันโค้ดของปลั๊กอินเป้าหมาย

ฟีเจอร์หลัก:
* Scan ปลั๊กอินที่ติดตั้งอยู่แล้ว
* Upload ZIP เพื่อตรวจปลั๊กอินก่อนติดตั้งจริง
* ตรวจ PHP/JS/CSS เบื้องต้น
* ตรวจความสัมพันธ์ include/require, class, function, shortcode, AJAX, REST, asset
* Deep Detail Scan ตรวจ security data-flow, database, remote API, asset และ UI/UX risk
* System Goal Reader อ่านว่า “ระบบนี้เกิดมาเพื่ออะไร”
* System X-Ray Analyzer ไล่ entry point → module → dependency → data-flow
* Goal vs Code Gap ตรวจว่าระบบที่มีอยู่ตรงกับเป้าหมายไหม
* Quality Standard Audit ตรวจ header, security gate, data handling, output escaping, PHP/WordPress compatibility, Browser/Device/System compatibility, maintainability และ release gate
* Export รายงานเป็น .txt และ Copy Report

== Installation ==

1. ไปที่ Plugins > Add New > Upload Plugin
2. อัปโหลดไฟล์ thinkb4do-plugin-bug-inspector-v1.3.0.zip
3. Activate Plugin
4. เปิดเมนู Bug Inspector
5. เลือกปลั๊กอินหรืออัปโหลด ZIP แล้วกด Scan Plugin
6. Export TXT เพื่อดาวน์โหลดรายงาน

== Safety ==

ค่าเริ่มต้น:
* Safe Static Mode: ON
* Mask API Key Before Export: ON
* Deep X-Ray Mode: ON

ระบบนี้ไม่ส่งโค้ดออกไป API ภายนอกอัตโนมัติ

== Changelog ==

= 1.3.0 =
* เพิ่ม Quality Standard Checker
* เพิ่ม Release Gate สำหรับตัดสินก่อนใช้งานจริง
* เพิ่มคะแนน Quality Standard และ Release Gate ใน Dashboard/Report
* ซ่อมรายงาน System Closer ซ้ำ และลดการตรวจ TODO ซ้ำใน scanner

= 1.2.0 =
* เพิ่ม System Goal Reader
* เพิ่ม System X-Ray Analyzer
* เพิ่ม Goal vs Code Gap
* เพิ่ม Feature Signals และ Topology/Entry Points ในรายงาน
* เพิ่มคะแนน Goal Reader, Feature Alignment และ System X-Ray

= 1.1.0 =
* เพิ่ม Deep Detail Scan
* เพิ่ม File Inventory และ Fix Queue

= 1.0.0 =
* เวอร์ชันแรก: ตรวจบั๊ก, ตรวจความสัมพันธ์, export TXT

= 1.4.0 =
* เพิ่ม Browser/Device/System Compatibility Audit
* เพิ่ม fallback สำหรับ Clipboard API และ mobile viewport
* ปรับ CSS ให้รองรับ responsive, safe-area, reduced motion, forced colors, print และ long text/table overflow
* เพิ่ม Compatibility Matrix บนหน้าแอดมินและรายงาน TXT
