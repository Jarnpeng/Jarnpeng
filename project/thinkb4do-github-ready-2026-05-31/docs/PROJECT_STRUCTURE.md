# Project Structure

## plugins
โฟลเดอร์ปลั๊กอินทั้งหมดของ Thinkb4do / AiRA ที่ใช้แยกส่วนระบบ เช่น Feed Main, Member, Header, Comment, Sidebar, Products และ AiRA Studio

## themes
โฟลเดอร์ธีม Thinkb4do สำหรับ WordPress

## จุดที่ควรโฟกัสตอนให้ Codex แก้

### หน้า Member
ใช้ปลั๊กอิน:

```text
plugins/thinkb4do-community-members-v4.2.283-feed-main-width-touch-fix/
```

ไฟล์สำคัญ:

```text
templates/member-area-content.php
assets/member-responsive-standard.css
thinkb4do-community-members.php
```

### Feed Main
ใช้ปลั๊กอิน:

```text
plugins/thinkb4do-community-feed-main/
```

ใช้เป็นมาตรฐานเทียบ layout, card, spacing และ behavior ของ Member

### Header
ใช้ปลั๊กอิน:

```text
plugins/thinkb4do-header-menu/
```

### Comment
ใช้ปลั๊กอิน:

```text
plugins/thinkb4do-community-comment-page/
```

### Sidebar
ใช้ปลั๊กอิน:

```text
plugins/thinkb4do-community-left-sidebar-desktop/
plugins/thinkb4do-community-right-sidebar-desktop/
```
