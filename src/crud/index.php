<?php
// index.php — แสดงรายการสินค้า + ปุ่มเพิ่ม/แก้/ลบ (หน้า READ ของ CRUD)
require '../db.php';

// รองรับการค้นหา (ไม่บังคับ) ผ่าน ?q=คำค้น
$q = isset($_GET['q']) ? $_GET['q'] : '';
if ($q !== '') {
    // หมายเหตุ: ต่อสตริงตรงๆ เพื่อความง่ายในบทเรียน (Day 2 ตอนหลังจะสอนวิธีปลอดภัย)
    $sql = "SELECT * FROM products WHERE name LIKE '%$q%' ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM products ORDER BY id DESC";
}
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการสินค้า</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 30px auto; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        a.btn { padding: 4px 10px; text-decoration: none; border-radius: 4px; }
        .add { background: #2e7d32; color: #fff; }
        .edit { background: #1565c0; color: #fff; }
        .del { background: #c62828; color: #fff; }
    </style>
</head>
<body>
    <h1>📦 รายการสินค้า</h1>

    <form method="GET">
        <input type="text" name="q" placeholder="ค้นหาชื่อสินค้า..." value="<?= htmlspecialchars($q) ?>">
        <button type="submit">ค้นหา</button>
        <a class="btn add" href="create.php">+ เพิ่มสินค้า</a>
    </form>
    <br>

    <table>
        <tr>
            <th>ID</th><th>ชื่อ</th><th>หมวด</th><th>ราคา</th><th>สต็อก</th><th>จัดการ</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= $row['price'] ?></td>
            <td><?= $row['stock'] ?></td>
            <td>
                <a class="btn edit" href="edit.php?id=<?= $row['id'] ?>">แก้ไข</a>
                <a class="btn del" href="delete.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('ลบสินค้านี้?')">ลบ</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
