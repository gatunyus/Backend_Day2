<?php
// login.php — เข้าสู่ระบบ (เวอร์ชันเข้าใจง่าย)
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && $user['password'] === $password) {
        // ล็อกอินสำเร็จ → เก็บข้อมูลลง session
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "username หรือ password ไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head><meta charset="UTF-8"><title>เข้าสู่ระบบ</title>
<style>body{font-family:sans-serif;max-width:400px;margin:40px auto}input{width:100%;padding:8px;margin:6px 0}</style>
</head>
<body>
    <h1>🔑 เข้าสู่ระบบ</h1>
    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form method="POST">
        <input name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button>เข้าสู่ระบบ</button>
    </form>
    <a href="register.php">ยังไม่มีบัญชี? สมัครสมาชิก</a>
</body>
</html>
