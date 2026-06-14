# ชีท 05 · ระบบ Login + Session

## จุดประสงค์
ทำระบบสมัครสมาชิก + เข้าสู่ระบบ + จำสถานะผู้ใช้ (Session) + ป้องกันหน้าที่ต้องล็อกอิน
แล้วเรียนรู้วิธี **ยกระดับความปลอดภัย** (prepared statement + hash) เป็นขั้นตอนเสริม

> เราจะทำเป็น **2 เวอร์ชัน**:
> 1. **เวอร์ชันเข้าใจง่าย** — เก็บรหัสผ่านแบบ plain text (เพื่อให้เห็น flow ชัดๆ ก่อน)
> 2. **เวอร์ชันปลอดภัย (Optional)** — เพิ่ม hash + prepared statement (ทำเมื่อเข้าใจ flow แล้ว)

---

## 1. Session คืออะไร?

HTTP เป็นโปรโตคอลที่ "ขี้ลืม" — แต่ละครั้งที่เปิดหน้าเว็บ เซิร์ฟเวอร์ไม่รู้ว่าเราเป็นใคร
**Session** คือวิธีที่เซิร์ฟเวอร์ "จำ" ผู้ใช้ไว้ระหว่างที่ยังใช้งานอยู่ (เช่น จำว่าล็อกอินแล้ว)

วิธีใช้ใน PHP:
```php
<?php
session_start();                       // เปิด session (ต้องเรียกบนสุดของไฟล์ ก่อน echo ใดๆ)
$_SESSION['user'] = 'somchai';         // เก็บข้อมูลลง session
echo $_SESSION['user'];                // อ่านกลับมาได้ในหน้าอื่นๆ
session_destroy();                     // ล้าง session (ตอน logout)
?>
```

---

## 2. ภาพรวม Flow ของระบบ Login

```
[สมัครสมาชิก register] → บันทึก username + password ลงตาราง users
[เข้าสู่ระบบ login]    → ตรวจว่า username/password ตรงกับในตารางไหม
        ├─ ตรง   → เก็บลง $_SESSION แล้วพาไปหน้า dashboard
        └─ ไม่ตรง → แจ้ง "รหัสผ่านไม่ถูกต้อง"
[หน้าที่ต้องล็อกอิน]   → เช็คว่ามี $_SESSION หรือยัง ถ้าไม่มีเด้งไป login
[ออกจากระบบ logout]   → ล้าง session
```

เราใช้ตาราง `users` (สร้างไว้แล้วใน `setup.sql`):
```sql
CREATE TABLE users (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  username  VARCHAR(50) NOT NULL UNIQUE,
  password  VARCHAR(255) NOT NULL,
  full_name VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## 3. เวอร์ชันเข้าใจง่าย (plain text ก่อน)

### 3.1 สมัครสมาชิก `register.php`
```php
<?php
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];     //  ยังเก็บแบบดิบ (จะแก้ทีหลัง)
    $fullname = $_POST['full_name'];

    $sql = "INSERT INTO users (username, password, full_name)
            VALUES ('$username', '$password', '$fullname')";

    if (mysqli_query($conn, $sql)) {
        echo "สมัครสำเร็จ! <a href='login.php'>ไปเข้าสู่ระบบ</a>";
    } else {
        echo "ผิดพลาด: " . mysqli_error($conn);
    }
}
?>
<form method="POST">
    Username: <input name="username" required><br>
    Password: <input type="password" name="password" required><br>
    ชื่อ-สกุล: <input name="full_name"><br>
    <button>สมัครสมาชิก</button>
</form>
```

### 3.2 เข้าสู่ระบบ `login.php`
```php
<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && $user['password'] === $password) {   // ตรวจรหัสผ่าน
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "username หรือ password ไม่ถูกต้อง";
    }
}
?>
<?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
<form method="POST">
    Username: <input name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <button>เข้าสู่ระบบ</button>
</form>
<a href="register.php">ยังไม่มีบัญชี? สมัครสมาชิก</a>
```

### 3.3 หน้าที่ต้องล็อกอิน `dashboard.php`
```php
<?php
session_start();

// ด่านป้องกัน: ถ้ายังไม่ล็อกอิน เด้งกลับไป login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<h1>ยินดีต้อนรับ <?= htmlspecialchars($_SESSION['username']) ?> </h1>
<p>นี่คือหน้าที่เห็นได้เฉพาะคนที่ล็อกอินแล้ว</p>
<a href="logout.php">ออกจากระบบ</a>
```

### 3.4 ออกจากระบบ `logout.php`
```php
<?php
session_start();
session_destroy();          // ล้างข้อมูล session ทั้งหมด
header('Location: login.php');
exit;
```

 แค่นี้ก็ได้ระบบ Login ที่ทำงานได้แล้ว! ลองสมัคร → เข้าระบบ → เข้า dashboard → ออกจากระบบ

---

## 4. เวอร์ชันปลอดภัย (Optional — ทำเมื่อเข้าใจ flow ข้างบนแล้ว)

เวอร์ชันข้างบนมี **2 ช่องโหว่ใหญ่** ในงานจริง:
1. **เก็บรหัสผ่านแบบ plain text** — ถ้าฐานข้อมูลรั่ว รหัสผ่านโดนเห็นหมด
2. **SQL Injection** — เอา `$_POST` ต่อใน SQL ตรงๆ ผู้ไม่หวังดีอาจป้อนคำสั่งอันตราย

### 4.1 ปัญหา SQL Injection คืออะไร
ถ้าผู้ใช้กรอก username เป็น:
```
' OR '1'='1
```
SQL จะกลายเป็น `... WHERE username = '' OR '1'='1'` ซึ่งเป็นจริงเสมอ → เข้าระบบได้โดยไม่ต้องรู้รหัส!

**ทางแก้: Prepared Statement** — แยก "คำสั่ง" ออกจาก "ข้อมูล" ฐานข้อมูลจะไม่ตีความข้อมูลเป็นคำสั่ง

```php
// แทนการต่อสตริง ใช้ ? เป็นช่องเสียบค่า
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);   // "s" = string
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
```

### 4.2 การ Hash รหัสผ่าน

**Hash** = แปลงรหัสผ่านเป็นข้อความสุ่มที่ย้อนกลับไม่ได้ เก็บแต่ค่าที่ hash แล้ว
เวลาตรวจ ก็ hash รหัสที่ผู้ใช้กรอก แล้วเทียบกับที่เก็บไว้

**ตัวอย่างด้วย SHA-256** (ตามที่โจทย์ระบุ):
```php
// ตอนสมัคร: hash ก่อนเก็บ
$hashed = hash('sha256', $password);
// เก็บ $hashed ลงฐานข้อมูลแทน $password

// ตอนล็อกอิน: hash รหัสที่กรอก แล้วเทียบ
$inputHashed = hash('sha256', $password);
if ($user['password'] === $inputHashed) {
    // รหัสถูกต้อง
}
```

ทดสอบดูได้ว่า `hash('sha256', '1234')` จะได้ข้อความยาว 64 ตัวอักษรเสมอ และเหมือนเดิมทุกครั้ง

> **หมายเหตุสำคัญสำหรับงานจริง:**
> SHA-256 เร็วเกินไป จึงถูกเดา (brute-force) ได้ง่ายถ้าใช้กับรหัสผ่าน
> ในระบบจริงควรใช้ **`password_hash()`** ของ PHP (ใช้ bcrypt + ใส่ salt อัตโนมัติ):
> ```php
> $hashed = password_hash($password, PASSWORD_DEFAULT);   // ตอนสมัคร
> if (password_verify($password, $user['password'])) { ... } // ตอนตรวจ
> ```
> ในคอร์สนี้เราฝึก SHA-256 เพื่อ "เข้าใจหลักการ hash" ก่อน แล้วค่อยรู้ว่าของจริงใช้ `password_hash()`

---

## 5. สรุป
- `session_start()` + `$_SESSION` ใช้จำว่าใครล็อกอินอยู่
- Flow: register → login (เช็ครหัส) → เก็บ session → ป้องกันหน้า → logout
- เริ่มจากเวอร์ชันเข้าใจง่ายก่อน แล้วยกระดับด้วย **prepared statement** (กัน SQL Injection) และ **hash** (กันรหัสรั่ว)
- งานจริงใช้ `password_hash()` / `password_verify()`

ไปต่อ: [06_lab_login.md](./06_lab_login.md)
