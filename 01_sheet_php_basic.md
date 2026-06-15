# ชีท 01 · PHP พื้นฐาน + เชื่อมต่อฐานข้อมูล

## จุดประสงค์
เขียน PHP พื้นฐานได้ และใช้ PHP ดึงข้อมูลจาก MySQL มาแสดงบนหน้าเว็บ

---

## 1. PHP กับการรับ-ส่งข้อมูล (เรียก API คุยกับฐานข้อมูล)

PHP ทำงานฝั่ง **เซิร์ฟเวอร์ (Backend)** หน้าที่หลักของเราในคอร์สนี้คือให้ PHP เป็น "ตัวกลางของ API" — รับคำขอจากหน้าเว็บ แล้วไปคุยกับฐานข้อมูล MySQL แล้วส่งผลลัพธ์กลับไป

```
[ หน้าเว็บ / fetch ] --ส่ง request--> [ PHP (API) ] --query--> [ MySQL ]
        ^                                   |
        └─────────── ส่งผลกลับ (HTML/JSON) ──┘
```

หน้าเว็บส่งข้อมูลเข้ามาให้ PHP ได้ 2 ช่องทางหลัก เราจะ **เน้น `method="POST"` เป็นหลัก** และใช้ `GET` เสริม:

| ช่องทาง | ข้อมูลมากับ | เหมาะกับ |
| :-- | :-- | :-- |
| **`$_POST`** (เน้น) | การ submit ฟอร์ม `method="POST"` หรือ body ของ `fetch` | ส่งข้อมูล "เข้าไปทำงาน" เช่น เพิ่ม/แก้/ลบ/ล็อกอิน |
| **`$_GET`** | ค่าใน URL เช่น `?id=5` | อ่าน/ค้นหา/ส่ง id ผ่านลิงก์ |

> ทำไมเน้น POST: ค่าที่ส่งไม่โผล่ใน URL (ปลอดภัยกว่า) และเหมาะกับการ "เปลี่ยนแปลงข้อมูล" ในฐานข้อมูล ส่วน GET ใช้ตอนแค่ "อ่าน" หรือเปิดลิงก์

รับค่าที่ส่งมาแบบ **POST** :
```php
<?php
// ฟอร์มที่ตั้ง method="POST" จะส่งค่ามาที่ $_POST
$name  = $_POST['name']  ?? '';
$price = $_POST['price'] ?? 0;
// เดี๋ยวเอา $name, $price ไป INSERT ลงฐานข้อมูลด้วย mysqli (ดูข้อ 3)
?>
```

และรับค่าแบบ **GET** :
```php
<?php
$id = $_GET['id'] ?? null;   // เช่นเปิด list.php?id=3
?>
```

> ในคอร์สนี้เชื่อมฐานข้อมูลด้วยไดรเวอร์ **mysqli เท่านั้น** (ไม่ใช้ PDO)

---

## 2. ไวยากรณ์พื้นฐาน

ไฟล์ PHP ลงท้ายด้วย `.php` และโค้ด PHP อยู่ในแท็ก `<?php ... ?>`

```php
<?php
echo "สวัสดี Backend!";   // echo = พิมพ์ออกหน้าจอ (เหมือน console.log แต่ออกหน้าเว็บ)
?>
```

### 2.1 ตัวแปร (ขึ้นต้นด้วย `$`)
```php
<?php
$name = "สมชาย";
$age = 25;
$price = 19.50;
$isActive = true;

echo $name;          // สมชาย
echo "อายุ " . $age; // เชื่อมข้อความด้วยจุด (.)  → อายุ 25
?>
```

> ต่างจาก JS: เชื่อมข้อความใช้จุด `.` ไม่ใช่เครื่องหมายบวก `+`

### 2.2 Array (อาเรย์)
```php
<?php
// array แบบรายการ
$fruits = ["แอปเปิล", "กล้วย", "ส้ม"];
echo $fruits[0];   // แอปเปิล

// array แบบ key => value (เหมือน object ใน JS)
$person = ["name" => "สมชาย", "age" => 25];
echo $person["name"];  // สมชาย
?>
```

<div class="page-break"></div>

### 2.3 เงื่อนไข if
```php
<?php
$score = 75;
if ($score >= 80) {
    echo "ดีมาก";
} elseif ($score >= 50) {
    echo "ผ่าน";
} else {
    echo "ไม่ผ่าน";
}
?>
```

### 2.4 วนซ้ำ (loop)
```php
<?php
// for
for ($i = 1; $i <= 3; $i++) {
    echo "รอบที่ $i <br>";
}

// foreach (วนใน array)
$fruits = ["แอปเปิล", "กล้วย", "ส้ม"];
foreach ($fruits as $fruit) {
    echo $fruit . "<br>";
}
?>
```

> `<br>` คือแท็ก HTML ขึ้นบรรทัดใหม่ — เพราะผลลัพธ์ออกไปแสดงในเบราว์เซอร์

### 2.5 ฟังก์ชัน
```php
<?php
function บวก($a, $b) {
    return $a + $b;
}
echo บวก(3, 5);   // 8
?>
```
<div class="page-break"></div>

### 2.6 แทรก PHP ใน HTML
```php
<!DOCTYPE html>
<html>
<body>
    <h1>รายชื่อผลไม้</h1>
    <ul>
    <?php
    $fruits = ["แอปเปิล", "กล้วย", "ส้ม"];
    foreach ($fruits as $fruit) {
        echo "<li>$fruit</li>";
    }
    ?>
    </ul>
</body>
</html>
```

---

## 3. เชื่อมต่อฐานข้อมูลด้วย mysqli

> เราจะใช้ **mysqli** แบบ procedural (เขียนเป็นฟังก์ชัน) เพราะเข้าใจง่ายสำหรับผู้เริ่มต้น

### 3.1 ขั้นตอน 4 ข้อ
1. **เชื่อมต่อ** (connect)
2. **ส่งคำสั่ง** SQL (query)
3. **อ่านผลลัพธ์** (fetch)
4. (ปิดการเชื่อมต่อ — PHP ปิดให้เองเมื่อจบสคริปต์)

### 3.2 ไฟล์เชื่อมต่อกลาง `db.php`
เราเตรียมไว้ให้แล้วใน [`src/db.php`](./src/db.php):

```php
<?php
$host = 'localhost';
$user = 'dba01';            // user แอดมินที่ grant สิทธิ์ไว้ใน Day 1
$pass = '202606';           // รหัสผ่านของ dba01
$dbname = 'atsoft_day2';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die('เชื่อมต่อไม่สำเร็จ: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');  // รองรับภาษาไทย
```
<div class="page-break"></div>

### 3.3 อ่านข้อมูล (SELECT) แล้วแสดงผล
สร้างไฟล์ `list.php`:

```php
<?php
require 'db.php';   // ได้ตัวแปร $conn

$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);   // ส่งคำสั่ง

echo "<h1>รายการสินค้า</h1>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>ชื่อ</th><th>ราคา</th><th>สต็อก</th></tr>";

// วนอ่านทีละแถว
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['price'] . "</td>";
    echo "<td>" . $row['stock'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
```

เปิด <http://localhost/atsoft/list.php> จะเห็นข้อมูลจากฐานข้อมูลออกมาเป็นตาราง

**อธิบายฟังก์ชันสำคัญ:**
| ฟังก์ชัน | หน้าที่ |
| :-- | :-- |
| `mysqli_connect(...)` | เชื่อมต่อฐานข้อมูล คืนค่า "connection" |
| `mysqli_query($conn, $sql)` | ส่งคำสั่ง SQL ไปรัน |
| `mysqli_fetch_assoc($result)` | ดึงผลลัพธ์ออกมาทีละแถว เป็น array (`$row['ชื่อคอลัมน์']`) |

### 3.4 เพิ่มข้อมูล (INSERT) จาก PHP
```php
<?php
require 'db.php';

$name = "ปากกาแดง";
$category = "อุปกรณ์";
$price = 12.00;
$stock = 50;

$sql = "INSERT INTO products (name, category, price, stock)
        VALUES ('$name', '$category', $price, $stock)";

if (mysqli_query($conn, $sql)) {
    echo "เพิ่มสินค้าสำเร็จ!";
} else {
    echo "ผิดพลาด: " . mysqli_error($conn);
}
?>
```

> **หมายเหตุความปลอดภัย:** การเอาตัวแปรมาต่อใน SQL ตรงๆ แบบนี้ **เสี่ยงต่อการโจมตี SQL Injection** เราเขียนแบบนี้ก่อนเพื่อให้เข้าใจง่าย แล้วจะเรียน **prepared statement** ที่ปลอดภัยกว่าในชีท Login (ข้อ 05)

---

## 4. รับค่าจากฟอร์ม (GET / POST)

HTML ฟอร์มส่งข้อมูลมาให้ PHP ผ่าน 2 ช่องทาง:
- `$_GET` — ค่ามากับ URL (เช่น `?id=5`) เหมาะกับการค้นหา/ลิงก์
- `$_POST` — ค่ามากับการ submit ฟอร์ม เหมาะกับการเพิ่ม/แก้ข้อมูล

**แบบ POST** — ค่าจะถูกส่งแนบไปกับ body ของ request (ไม่โชว์ใน URL) เหมาะกับการเพิ่ม/แก้/ลบข้อมูล หรือข้อมูลอ่อนไหวเช่นรหัสผ่าน

```html
<!-- ฟอร์ม -->
<form method="POST" action="save.php">
    ชื่อ: <input type="text" name="name">
    <button type="submit">บันทึก</button>
</form>
```

```php
<!-- save.php -->
<?php
$name = $_POST['name'];   // รับค่าที่กรอกมา
echo "ได้รับชื่อ: " . $name;
?>
```

**แบบ GET** — ค่าจะติดไปกับ URL ให้เห็น (เช่น `search.php?keyword=ปากกา`) เหมาะกับการค้นหา/ลิงก์ที่อยากบุ๊กมาร์กหรือส่งต่อได้

```html
<!-- ฟอร์มค้นหา (method="GET") -->
<form method="GET" action="search.php">
    ค้นหา: <input type="text" name="keyword">
    <button type="submit">ค้นหา</button>
</form>
```

```php
<!-- search.php -->
<?php
$keyword = $_GET['keyword'] ?? '';   // รับค่าจาก URL (?keyword=...) ; ?? '' กันค่าไม่มี
echo "กำลังค้นหา: " . $keyword;
?>
```

> นอกจากฟอร์มแล้ว GET ยังรับค่าจากลิงก์ตรงๆ ได้ เช่นเปิด `list.php?id=3` แล้วอ่านด้วย `$_GET['id']`
>
> **เลือกใช้ตัวไหน?** อ่าน/ค้นหา/ลิงก์ → `GET` (ค่าโชว์ใน URL บุ๊กมาร์กได้) · เพิ่ม/แก้/ลบ หรือข้อมูลอ่อนไหวเช่นรหัสผ่าน → `POST` (ไม่โชว์ใน URL)

---
<div class="page-break"></div>

## 5. สรุป
- PHP รันฝั่งเซิร์ฟเวอร์ สร้าง HTML ส่งให้เบราว์เซอร์
- เชื่อม DB ด้วย `mysqli_connect` → `mysqli_query` → `mysqli_fetch_assoc`
- ใช้ `require 'db.php'` เพื่อไม่ต้องเขียนโค้ดเชื่อมซ้ำ
- รับค่าจากผู้ใช้ด้วย `$_GET` / `$_POST`

ไปต่อ: [02_lab_php_crud.md](./02_lab_php_crud.md)
