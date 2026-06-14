# Day 2 — PHP เชื่อมฐานข้อมูล / API / Login

วันที่สองเราจะให้ **PHP** มาเป็นตัวกลางคุยกับฐานข้อมูลที่สร้างไว้เมื่อวาน
แล้วต่อยอดเป็น **API** และ **ระบบ Login**

> คุณเคยทำ Frontend มาแล้ว วันนี้คือการเข้าใจว่า "ฝั่งเซิร์ฟเวอร์" ทำงานอย่างไร และส่งข้อมูลให้ Frontend ได้อย่างไร

---

## จุดประสงค์การเรียนรู้
เมื่อจบ Day 2 คุณจะสามารถ:
- เขียน PHP พื้นฐาน (ตัวแปร, array, if, loop, function)
- เชื่อม PHP เข้ากับ MySQL ด้วย **mysqli** และแสดงข้อมูลออกหน้าเว็บ
- ทำหน้าเว็บ **CRUD** ครบ (แสดง/เพิ่ม/แก้/ลบ)
- สร้าง **API** ที่คืนข้อมูลเป็น **JSON**
- ทำระบบ **Login + Session** และรู้วิธียกระดับความปลอดภัย (prepared statement + hash)

---

## เตรียมตัวก่อนเริ่ม
1. เปิด XAMPP → Start **Apache** และ **MySQL**
2. สร้างโฟลเดอร์โปรเจกต์ใน `htdocs` เช่น `htdocs/atsoft/`
3. คัดลอกไฟล์จากโฟลเดอร์ [`src/`](./src/) ไปไว้ใน `htdocs/atsoft/`
4. วันนี้เราจะใช้ฐานข้อมูลใหม่ชื่อ `atsoft_day2` — สร้างได้จากไฟล์ [`src/setup.sql`](./src/setup.sql)
5. เปิดทดสอบผ่าน <http://localhost/atsoft/ไฟล์.php>

---

## สารบัญ Day 2 (ทำตามลำดับ)

| ลำดับ | ไฟล์ | ประเภท | เนื้อหา |
| :-- | :-- | :-- | :-- |
| 1 | [01_sheet_php_basic.md](./01_sheet_php_basic.md) | ชีท | PHP พื้นฐาน + เชื่อม DB ด้วย mysqli |
| 2 | [02_lab_php_crud.md](./02_lab_php_crud.md) | Lab 3 | ทำหน้าเว็บ CRUD |
| 3 | [03_sheet_api.md](./03_sheet_api.md) | ชีท | สร้าง API คืน JSON |
| 4 | [04_lab_api.md](./04_lab_api.md) | Lab 4 | ทำ Record API |
| 5 | [05_sheet_login.md](./05_sheet_login.md) | ชีท | Session + Login (+ hash แบบ optional) |
| 6 | [06_lab_login.md](./06_lab_login.md) | Lab 5 | ทำระบบ Register/Login |

 ไฟล์ประกอบ:
- [`src/`](./src/) — โค้ดตัวอย่าง/เริ่มต้น (db.php, setup.sql, crud/, api/, auth/)
- [`answers/`](./answers/) — เฉลยโค้ดเต็มแต่ละ Lab (+ เวอร์ชัน hash)

---

เริ่มที่ [01_sheet_php_basic.md](./01_sheet_php_basic.md)
