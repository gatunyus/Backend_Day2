<?php
// edit.php — แก้ไขสินค้า (หน้า UPDATE ของ CRUD)
require '../db.php';

$id = $_GET['id'] ?? null;   // รับ id ที่จะแก้จาก URL  (edit.php?id=3)
if (!$id) { die('ไม่พบ id'); }

// ถ้า submit ฟอร์มมา → อัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $category = $_POST['category'];
    $price    = $_POST['price'];
    $stock    = $_POST['stock'];

    $sql = "UPDATE products
            SET name='$name', category='$category', price=$price, stock=$stock
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header('Location: index.php');
        exit;
    } else {
        $error = mysqli_error($conn);
    }
}

// ดึงข้อมูลเดิมมาเติมในฟอร์ม
$result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
$row = mysqli_fetch_assoc($result);
if (!$row) { die('ไม่พบสินค้านี้'); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 30px auto; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 6px; }
        button { margin-top: 15px; padding: 8px 16px; }
    </style>
</head>
<body>
    <h1>✏️ แก้ไขสินค้า #<?= $row['id'] ?></h1>
    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>

    <form method="POST">
        <label>ชื่อสินค้า</label>
        <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>

        <label>หมวดหมู่</label>
        <input type="text" name="category" value="<?= htmlspecialchars($row['category']) ?>" required>

        <label>ราคา</label>
        <input type="number" step="0.01" name="price" value="<?= $row['price'] ?>" required>

        <label>สต็อก</label>
        <input type="number" name="stock" value="<?= $row['stock'] ?>" required>

        <button type="submit">บันทึกการแก้ไข</button>
        <a href="index.php">ยกเลิก</a>
    </form>
</body>
</html>
