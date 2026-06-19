# Lab 5 · สร้าง Record API

## เป้าหมาย
สร้างและทดสอบ API ที่จัดการข้อมูลสินค้าครบ CRUD ผ่าน HTTP method

## สิ่งที่ต้องเตรียม
1. ฐานข้อมูล `atsoft_day2` พร้อมตาราง `products` (จาก [`src/setup.sql`](./src/setup.sql))
2. คัดลอกไฟล์ [`src/api/record_api.php`](./src/api/record_api.php) ไปไว้ที่ `htdocs/atsoft/api/`
3. (แนะนำ) ติดตั้ง **Postman** ไว้ทดสอบ POST/PUT/DELETE

---

## ส่วนที่ 1: ทดสอบ API ที่ให้มา

**ข้อ 1.1 (GET ทั้งหมด)** เปิดในเบราว์เซอร์:
```
http://localhost/atsoft/api/record_api.php
```
 ควรเห็น JSON รายการสินค้าทั้งหมด `{ "success": true, "data": [...] }`

**ข้อ 1.2 (GET ตัวเดียว)** เปิด:
```
http://localhost/atsoft/api/record_api.php?id=1
```
 ควรเห็นสินค้า id=1 ตัวเดียว

**ข้อ 1.3 (POST เพิ่มสินค้า)** ใช้ Console ของเบราว์เซอร์ (กด F12 → Console) วาง:
```js
fetch('http://localhost/atsoft/api/record_api.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ name: 'เจลล้างมือ', category: 'อุปกรณ์', price: 39.0, stock: 80 })
}).then(r => r.json()).then(console.log);
```
 ควรได้ `{ success: true, id: ... }` แล้วลอง GET ทั้งหมดดูว่ามีของใหม่

**ข้อ 1.4 (DELETE)** ลบสินค้าที่เพิ่งเพิ่ม (แทน `<id>` ด้วยเลขจริง):
```js
fetch('http://localhost/atsoft/api/record_api.php?id=<id>', { method: 'DELETE' })
  .then(r => r.json()).then(console.log);
```

---

## ส่วนที่ 2: อ่านเข้าใจโค้ด
ตอบในใจ:
- `switch ($method)` ทำหน้าที่อะไร?
- ทำไม POST ใช้ `json_decode(file_get_contents('php://input'), true)`?
- `(int)$id` และ `mysqli_real_escape_string(...)` มีไว้ทำไม? (คำใบ้: ความปลอดภัย)
- ฟังก์ชัน `respond()` ช่วยให้โค้ดสั้นลงยังไง?

---

## ส่วนที่ 3: ต่อยอด

**ข้อ 3.1** เพิ่มความสามารถค้นหา: `GET ...?search=น้ำ` ให้คืนเฉพาะสินค้าที่ชื่อมีคำค้น
> ในเคส GET เพิ่มเงื่อนไข: ถ้ามี `$_GET['search']` ให้ใส่ `WHERE name LIKE '%...%'`
```php

```

**ข้อ 3.2** เพิ่ม endpoint สรุป: `GET ...?summary=1` คืน `{ total_products, total_value }`
> ใช้ `COUNT(*)` และ `SUM(price*stock)`
```php

```

**ข้อ 3.3** ทำให้ POST ตรวจสอบว่า `price` และ `stock` ต้องไม่ติดลบ ถ้าติดลบให้ตอบ 400 พร้อมข้อความ
```php

```

---

## โจทย์ท้าทาย
**ข้อ 4.1** สร้างหน้า HTML + JavaScript (`shop.html`) ที่ `fetch` ข้อมูลจาก API นี้มาแสดงเป็นการ์ดสินค้า
> นี่คือการเชื่อม Frontend (ที่คุณถนัด) เข้ากับ Backend (ที่เพิ่งเรียน) เข้าด้วยกัน!
```php

```

---

## เช็คความเข้าใจ
- [ ] เรียก API ด้วย GET/POST/DELETE ได้
- [ ] เข้าใจการแยกงานตาม HTTP method
- [ ] รับ-ส่งข้อมูลเป็น JSON ได้
- [ ] ต่อยอดเพิ่ม endpoint ค้นหา/สรุปได้
ไปต่อ: [07_sheet_login.md](./07_sheet_login.md)
