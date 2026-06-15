# Lab 5 · ระบบ Register / Login

## เป้าหมาย
ทำระบบสมาชิกครบวงจร: สมัคร → เข้าสู่ระบบ → หน้าที่ต้องล็อกอิน → ออกจากระบบ
แล้ว (ถ้าพร้อม) ยกระดับด้วย hash + prepared statement

## สิ่งที่ต้องเตรียม
1. ฐานข้อมูล `atsoft_day2` มีตาราง `users` แล้ว (จาก [`src/setup.sql`](./src/setup.sql))
2. คัดลอกไฟล์ใน [`src/auth/`](./src/auth/) ไปไว้ที่ `htdocs/atsoft/auth/`
3. เปิด <http://localhost/atsoft/auth/register.php>

---

## ส่วนที่ 1: ทำให้ระบบทำงาน

**ข้อ 1.1** สมัครสมาชิก 1 บัญชี (เช่น username `test`, password `1234`)

**ข้อ 1.2** ไปที่ phpMyAdmin → ตาราง `users` → Browse
> สังเกต: รหัสผ่านถูกเก็บเป็น `1234` ตรงๆ — นี่คือสิ่งที่ **ไม่ปลอดภัย** ที่เราจะแก้ในส่วนที่ 3

**ข้อ 1.3** เข้าสู่ระบบด้วยบัญชีที่สมัคร → ควรเข้าหน้า `dashboard.php` ได้

**ข้อ 1.4** ทดสอบความปลอดภัยของ session:
- ขณะยังไม่ล็อกอิน ลองเปิด `dashboard.php` ตรงๆ → ควรถูกเด้งไป `login.php`
- ล็อกอินแล้วกด "ออกจากระบบ" → แล้วลองเปิด `dashboard.php` อีกครั้ง → ควรถูกเด้งออกอีก

---

## ส่วนที่ 2: เข้าใจ Flow
- `session_start()` ทำไมต้องอยู่บรรทัดบนสุด (ก่อน HTML)?
- `$_SESSION['user_id']` ถูกตั้งค่าตอนไหน และถูกเช็คที่ไหน?
- `session_destroy()` ทำอะไร?
- ทำไม `dashboard.php` ต้องมี "ด่านป้องกัน" ตอนต้นไฟล์?

---

## ส่วนที่ 3: ยกระดับความปลอดภัย 

> ทำส่วนนี้เมื่อเข้าใจ flow ส่วนที่ 1 แล้ว

**ข้อ 3.1 (Hash รหัสผ่าน)** แก้ `register.php` ให้ hash รหัสก่อนเก็บ:
```php
$hashed = hash('sha256', $password);
// แล้วเก็บ $hashed แทน $password
```
และแก้ `login.php` ให้ hash รหัสที่กรอกก่อนเทียบ:
```php
$inputHashed = hash('sha256', $password);
if ($user && $user['password'] === $inputHashed) { ... }
```
> บัญชีเก่า (plain) จะล็อกอินไม่ได้แล้ว — ให้สมัครใหม่ หรือ `TRUNCATE TABLE users;` ก่อน
```php
// register.php (ตอนบันทึกข้อมูล)
$hashed = hash('sha256', $password);
$sql = "INSERT INTO users (username, password, full_name)
        VALUES ('$username', '$hashed', '$fullname')";

// login.php (ตอนตรวจสอบรหัสผ่าน)
$inputHashed = hash('sha256', $password);
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
if ($user && $user['password'] === $inputHashed) {
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    header('Location: dashboard.php');
    exit;
}
```

**ข้อ 3.2 (ตรวจผลใน DB)** สมัครบัญชีใหม่ แล้วดูในตาราง `users` อีกครั้ง
> ตอนนี้รหัสผ่านควรเป็นข้อความสุ่มยาว 64 ตัวอักษร อ่านไม่ออกแล้ว

**ข้อ 3.3 (กัน SQL Injection)** เปลี่ยน `login.php` ให้ใช้ **prepared statement**:
```php
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
```

```php
// login.php ฉบับสมบูรณ์ที่ใช้ Prepared Statement และ Hash ป้องกัน SQL Injection
$inputHashed = hash('sha256', $password);

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && hash_equals($user['password'], $inputHashed)) {
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    header('Location: dashboard.php');
    exit;
} else {
    $error = "username หรือ password ไม่ถูกต้อง";
}
```

**ข้อ 3.4 (ทดสอบช่องโหว่)** *ก่อน* แก้เป็น prepared statement ลองกรอก username:
```
' OR '1'='1
```
สังเกตว่าเวอร์ชันเก่าอาจมีพฤติกรรมผิดปกติ แต่เวอร์ชัน prepared statement จะปลอดภัย

---

## โจทย์ท้าทาย
**ข้อ 4.1** เพิ่มคอลัมน์ `role` (เช่น `admin` / `user`) ในตาราง users แล้วทำให้ dashboard แสดงเมนูต่างกันตาม role
> `ALTER TABLE users ADD role VARCHAR(20) DEFAULT 'user';` แล้วเก็บ role ลง session ด้วย
```php
// 1. เพิ่มคอลัมน์ในฐานข้อมูล (รัน SQL ใน phpMyAdmin)
// ALTER TABLE users ADD role VARCHAR(20) NOT NULL DEFAULT 'user';

// 2. ใน login.php (ตอนเช็คผ่านแล้ว) ให้เก็บ role ลง session
$_SESSION['role'] = $user['role'];

// 3. ใน dashboard.php (แสดงเมนูและข้อมูลตามระดับสิทธิ์)
if ($_SESSION['role'] === 'admin') {
    echo '<a href="manage_users.php">⚙️ จัดการผู้ใช้ (เฉพาะแอดมิน)</a>';
} else {
    echo '<p>คุณเป็นผู้ใช้ทั่วไป</p>';
}
```

---

## เช็คความเข้าใจ
- [ ] ทำระบบ register/login/logout ที่ทำงานได้
- [ ] เข้าใจการใช้ session ป้องกันหน้า
- [ ] hash รหัสผ่านด้วย SHA-256 ได้ และเข้าใจว่าทำไมต้อง hash
- [ ] ใช้ prepared statement กัน SQL Injection ได้
- [ ] รู้ว่างานจริงควรใช้ `password_hash()`


