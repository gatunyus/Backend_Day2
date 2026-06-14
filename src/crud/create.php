<?php
// create.php — เพิ่มสินค้าใหม่ (หน้า CREATE ของ CRUD)
require '../db.php';

// ถ้าฟอร์มถูก submit มา (method = POST) ให้บันทึกลงฐานข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $category = $_POST['category'];
    $price    = $_POST['price'];
    $stock    = $_POST['stock'];

    $sql = "INSERT INTO products (name, category, price, stock)
            VALUES ('$name', '$category', $price, $stock)";

    if (mysqli_query($conn, $sql)) {
        header('Location: index.php');  // บันทึกเสร็จ กลับไปหน้ารายการ
        exit;
    } else {
        $error = mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มสินค้า</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 30px auto; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 6px; }
        button { margin-top: 15px; padding: 8px 16px; }
    </style>
</head>
<body>
    <h1>➕ เพิ่มสินค้า</h1>
    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>

    <form method="POST">
        <label>ชื่อสินค้า</label>
        <input type="text" name="name" required>

        <label>หมวดหมู่</label>
        <input type="text" name="category" required>

        <label>ราคา</label>
        <input type="number" step="0.01" name="price" required>

        <label>สต็อก</label>
        <input type="number" name="stock" required>

        <button type="submit">บันทึก</button>
        <a href="index.php">ยกเลิก</a>
    </form>
</body>
</html>
