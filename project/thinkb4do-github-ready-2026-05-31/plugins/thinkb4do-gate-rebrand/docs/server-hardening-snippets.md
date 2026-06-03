# Thinkb4do Think Control - Server Hardening Notes

## เป้าหมาย

เพิ่มชั้นป้องกันฝั่งเซิร์ฟเวอร์สำหรับระบบหลังบ้านและไฟล์อัปโหลด โดยไม่แตะไฟล์หลักของระบบ

## แนวทางแนะนำ

- เปิด HTTPS ตลอดทั้งเว็บ
- ปิด directory listing
- บล็อกการรันไฟล์ script ในโฟลเดอร์อัปโหลด
- จำกัด IP สำหรับหน้าหลังบ้านถ้าทำได้
- เปิด 2FA สำหรับบัญชีผู้ดูแล
- ใช้ Cloudflare Firewall / Turnstile เพื่อกันบอท
- สำรองไฟล์และฐานข้อมูลก่อนแก้กฎเซิร์ฟเวอร์ทุกครั้ง

## ตัวอย่างแนวคิดกฎเซิร์ฟเวอร์

```text
Block script execution inside upload folders
Block direct access to sensitive config files
Allow only trusted IPs for the control area when possible
Rate limit repeated login attempts
```

## คำเตือน

ให้ทดสอบบน staging หรือช่วงเวลาที่มีผู้ใช้น้อยก่อนเสมอ เพราะกฎเซิร์ฟเวอร์ที่เข้มเกินไปอาจทำให้บางส่วนของเว็บใช้งานไม่ได้
