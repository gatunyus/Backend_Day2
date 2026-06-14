<?php
// delete.php — ลบสินค้า (หน้า DELETE ของ CRUD)
require '../db.php';

$id = $_GET['id'] ?? null;   // รับ id ที่จะลบจาก URL (delete.php?id=3)
if (!$id) { die('ไม่พบ id'); }

$sql = "DELETE FROM products WHERE id=$id";

if (mysqli_query($conn, $sql)) {
    header('Location: index.php');   // ลบเสร็จกลับหน้ารายการ
    exit;
} else {
    echo "ลบไม่สำเร็จ: " . mysqli_error($conn);
}
