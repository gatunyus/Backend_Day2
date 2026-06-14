<?php
// logout.php — ออกจากระบบ
session_start();
session_destroy();              // ล้างข้อมูล session ทั้งหมด
header('Location: login.php');  // กลับไปหน้าเข้าสู่ระบบ
exit;
