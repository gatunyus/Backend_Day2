<?php
// dashboard.php — หน้าที่เห็นได้เฉพาะคนที่ล็อกอินแล้ว
session_start();

// 🛡️ ด่านป้องกัน: ถ้ายังไม่ล็อกอิน เด้งกลับไปหน้า login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head><meta charset="UTF-8"><title>Dashboard</title>
<style>body{font-family:sans-serif;max-width:600px;margin:40px auto}</style>
</head>
<body>
    <h1>ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']) ?> 🎉</h1>
    <p>นี่คือหน้าที่เห็นได้เฉพาะผู้ที่เข้าสู่ระบบแล้วเท่านั้น</p>
    <p>user_id ของคุณคือ: <?= (int)$_SESSION['user_id'] ?></p>
    <a href="logout.php">ออกจากระบบ</a>
</body>
</html>
