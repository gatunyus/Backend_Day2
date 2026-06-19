# ชีท 05 · สร้าง API คืนข้อมูลเป็น JSON

## จุดประสงค์
เข้าใจว่า API คืออะไร และสร้าง API ด้วย PHP ที่ส่งข้อมูลกลับเป็น **JSON** ได้

---

## 1. API คืออะไร?

ตอนเรียน Frontend คุณอาจเคยใช้ `fetch()` ดึงข้อมูลจากที่ไหนสักแห่งมาแสดง — ที่ที่ส่งข้อมูลกลับมานั่นแหละคือ **API**

> **API** = ช่องทางให้โปรแกรมคุยกับโปรแกรม โดยรับคำขอ แล้วส่ง **ข้อมูล** กลับ (ไม่ใช่หน้า HTML สวยๆ แต่เป็นข้อมูลดิบให้โปรแกรมอื่นเอาไปใช้)

เปรียบเทียบ:
- หน้าเว็บ `index.php` → ส่ง **HTML** ให้คนดู
- API `api.php` → ส่ง **JSON** ให้โปรแกรม (เช่น JavaScript, มือถือ) เอาไปใช้ต่อ

ตัวอย่างข้อมูล JSON ที่ API ส่งกลับ:
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "น้ำดื่ม 600ml", "price": "7.00" },
    { "id": 2, "name": "น้ำโซดา 325ml", "price": "10.00" }
  ]
}
```

---

## 2. ทำให้ PHP ส่ง JSON

2 ขั้นตอนสำคัญ:
1. บอกเบราว์เซอร์ว่ากำลังส่ง JSON ด้วย `header()`
2. แปลง array ของ PHP เป็นข้อความ JSON ด้วย `json_encode()`

```php
<?php
require 'db.php';

header('Content-Type: application/json; charset=utf-8');  // บอกว่านี่คือ JSON

$result = mysqli_query($conn, "SELECT * FROM products");

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;   // เก็บแต่ละแถวเข้า array
}

// แปลง array → JSON แล้วพิมพ์ออกไป
echo json_encode([
    "success" => true,
    "data" => $products
], JSON_UNESCAPED_UNICODE);   // UNESCAPED_UNICODE = ให้ภาษาไทยอ่านออก ไม่กลายเป็น \u...
?>
```

เปิด <http://localhost/atsoft/api.php> จะเห็นข้อมูลเป็น JSON ล้วนๆ

> `JSON_UNESCAPED_UNICODE` สำคัญมากสำหรับภาษาไทย ไม่งั้นจะได้ `น้ำ` แทน "น้ำ"

---

## 3. รับพารามิเตอร์ 

### 3.1 รับค่าจาก URL ด้วย `$_GET`
```php
// api.php?id=2  →  คืนสินค้าตัวเดียว
$id = $_GET['id'] ?? null;
if ($id) {
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
    $product = mysqli_fetch_assoc($result);
    echo json_encode(["success" => true, "data" => $product], JSON_UNESCAPED_UNICODE);
}
```

### 3.2 รู้ว่าเป็น method อะไร (GET/POST/PUT/DELETE)
API ที่ดีจะแยกการทำงานตาม **HTTP method**:

| Method | ใช้ทำ | ตรงกับ CRUD |
| :-- | :-- | :-- |
| `GET` | อ่านข้อมูล | Read |
| `POST` | เพิ่มข้อมูล | Create |
| `PUT` | แก้ข้อมูล | Update |
| `DELETE` | ลบข้อมูล | Delete |

> **`PUT` คืออะไร?** เป็น HTTP method หนึ่งเหมือน GET/POST แต่ตกลงกันว่าใช้สำหรับ **แก้ไขข้อมูลที่มีอยู่แล้ว** (Update) เช่น `PUT /record_api.php?id=2` = แก้สินค้า id 2
> — ฟอร์ม HTML (`<form>`) ส่งได้แค่ `GET`/`POST` เท่านั้น ส่วน `PUT`/`DELETE` ต้องเรียกผ่านโค้ด เช่น `fetch()` ฝั่ง JavaScript หรือเครื่องมือทดสอบ API (Postman) — ดูวิธีเรียกในข้อ 4
>
> ทำไมต้องแยก `POST` (เพิ่ม) กับ `PUT` (แก้)? เพื่อให้ API อ่านง่ายและสื่อความหมายชัด ว่า request นี้ตั้งใจจะ "สร้างใหม่" หรือ "แก้ของเดิม" — ฝั่งเซิร์ฟเวอร์ดูจาก `$_SERVER['REQUEST_METHOD']` แล้วทำงานคนละแบบ

```php
$method = $_SERVER['REQUEST_METHOD'];   // ได้ "GET", "POST", ...

if ($method === 'GET') {
    // อ่านข้อมูล
} elseif ($method === 'POST') {
    // เพิ่มข้อมูล
}
```

### 3.3 รับข้อมูล JSON ที่ส่งเข้ามา (ตอน POST/PUT)
เวลา Frontend ส่ง JSON มา (เช่น `fetch` ด้วย `body: JSON.stringify(...)`) ฝั่ง PHP อ่านแบบนี้:
```php
$input = json_decode(file_get_contents('php://input'), true);
// $input จะเป็น array เช่น $input['name'], $input['price']
```

---

## 4. โครงสร้าง Response ที่ดี

ออกแบบให้ตอบกลับรูปแบบเดียวกันเสมอ จะได้ใช้ง่าย:

```php
// สำเร็จ
echo json_encode(["success" => true, "data" => $data], JSON_UNESCAPED_UNICODE);

// ผิดพลาด
http_response_code(400);   // ส่งรหัสสถานะ (400 = คำขอผิด, 404 = ไม่พบ, 500 = เซิร์ฟเวอร์พัง)
echo json_encode(["success" => false, "message" => "ไม่พบข้อมูล"], JSON_UNESCAPED_UNICODE);
```

---

## 5. ทดสอบ API อย่างไร

- **GET** ง่ายสุด: เปิด URL ในเบราว์เซอร์ได้เลย
- **POST/PUT/DELETE**: ใช้เครื่องมือทดสอบ เช่น
  - **Postman** (โปรแกรมยอดนิยม)
  - หรือเขียน `fetch()` ใน Console ของเบราว์เซอร์:
    ```js
    fetch('http://localhost/atsoft/api/record_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: 'สินค้าใหม่', category: 'ทดสอบ', price: 10, stock: 5 })
    }).then(r => r.json()).then(console.log);
    ```

> จุดเชื่อมกับ Frontend: นี่คือสิ่งที่ทำให้หน้าเว็บที่คุณเคยทำ "ดึงข้อมูลจริง" ได้!

---

## 6. สรุป
- API ส่ง **ข้อมูล (JSON)** ไม่ใช่หน้า HTML
- ใช้ `header('Content-Type: application/json')` + `json_encode(..., JSON_UNESCAPED_UNICODE)`
- แยกการทำงานตาม `$_SERVER['REQUEST_METHOD']`
- รับ JSON เข้ามาด้วย `json_decode(file_get_contents('php://input'), true)`
- ตอบกลับรูปแบบเดียวกันเสมอ: `{success, data}` หรือ `{success, message}`

ไปต่อ: [06_lab_api.md](./06_lab_api.md)
