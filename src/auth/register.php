<?php
// register.php — สมัครสมาชิก (เวอร์ชันเข้าใจง่าย: เก็บรหัสแบบ plain)
// 🔒 ดูเวอร์ชันปลอดภัย (hash + prepared statement) ได้ในโฟลเดอร์ answers/auth_secure/
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fullname = $_POST['full_name'];

    $sql = "INSERT INTO users (username, password, full_name)
            VALUES ('$username', '$password', '$fullname')";

    if (mysqli_query($conn, $sql)) {
        $success = true;
    } else {
        $error = mysqli_error($conn);  // เช่น username ซ้ำ (UNIQUE)
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head><meta charset="UTF-8"><title>สมัครสมาชิก</title>
<style>body{font-family:sans-serif;max-width:400px;margin:40px auto}input{width:100%;padding:8px;margin:6px 0}</style>
</head>
<body>
    <h1>📝 สมัครสมาชิก</h1>
    <?php if (!empty($success)): ?>
        <p style="color:green">สมัครสำเร็จ! <a href="login.php">ไปเข้าสู่ระบบ</a></p>
    <?php else: ?>
        <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form method="POST">
            <input name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input name="full_name" placeholder="ชื่อ-สกุล">
            <button>สมัครสมาชิก</button>
        </form>
        <a href="login.php">มีบัญชีแล้ว? เข้าสู่ระบบ</a>
    <?php endif; ?>
</body>
</html>
