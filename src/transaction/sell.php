<?php
// ============================================================
// sell.php — ขายสินค้า: หักสต็อก + เปิดบิล พร้อมกัน ด้วย Transaction (PHP)
// แนวคิด (master-detail แบบย่อ): 1 การขาย ต้องทำ 2 ตารางให้สำเร็จคู่กัน
//   1) UPDATE products  ลดสต็อก
//   2) INSERT orders     เปิดบิลใหม่
// ถ้าสต็อกไม่พอ → ROLLBACK ทั้งคู่ (กันขายเกินจำนวนที่มี)
// นี่คือแพตเทิร์นเดียวกับที่จะเจอใน Day 3 (INSERT detail + UPDATE header)
// ============================================================
require '../db.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)$_POST['product_id'];
    $qty = (int)$_POST['qty'];

    mysqli_begin_transaction($conn);
    try {
        if ($qty <= 0) throw new Exception('จำนวนต้องมากกว่า 0');

        // อ่านข้อมูลสินค้าก่อน (ราคา + สต็อกปัจจุบัน)
        $res  = mysqli_query($conn, "SELECT name, price, stock FROM products WHERE id = $pid");
        $prod = mysqli_fetch_assoc($res);
        if ($prod === null) throw new Exception('ไม่พบสินค้า');
        if ($prod['stock'] < $qty) throw new Exception('สต็อกไม่พอ (เหลือ ' . $prod['stock'] . ')');

        $total = $prod['price'] * $qty;

        // 1) หักสต็อก
        mysqli_query($conn,
            "UPDATE products SET stock = stock - $qty WHERE id = $pid");

        // 2) เปิดบิล
        $name = mysqli_real_escape_string($conn, $prod['name']);
        mysqli_query($conn,
            "INSERT INTO orders (product_id, product_name, qty, total_price)
             VALUES ($pid, '$name', $qty, $total)");

        mysqli_commit($conn);
        $msg = "✅ ขาย {$prod['name']} จำนวน $qty ชิ้น (รวม " . number_format($total, 2) . " บาท) สำเร็จ";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = '❌ ขายไม่สำเร็จ: ' . $e->getMessage();
    }
}

$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ขายสินค้า (Transaction)</title>
    <style>
        body { font-family: sans-serif; max-width: 560px; margin: 30px auto; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 6px; }
        button { margin-top: 15px; padding: 8px 16px; }
        .msg { padding: 10px; background: #f0f0f0; border-radius: 6px; }
    </style>
</head>
<body>
    <h1>🛒 ขายสินค้า (ฝึก Transaction หลายตาราง)</h1>

    <?php if ($msg): ?><p class="msg"><?= $msg ?></p><?php endif; ?>

    <table>
        <tr><th>id</th><th>สินค้า</th><th>ราคา</th><th>สต็อก</th></tr>
        <?php while ($p = mysqli_fetch_assoc($products)): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= $p['name'] ?></td>
            <td><?= number_format($p['price'], 2) ?></td>
            <td><?= $p['stock'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <form method="POST">
        <label>สินค้า (id)</label>
        <input type="number" name="product_id" value="1" required>

        <label>จำนวนที่ขาย</label>
        <input type="number" name="qty" value="3" required>

        <button type="submit">ขาย</button>
    </form>

    <p style="color:#666">
        ลองขายจำนวนปกติ (สำเร็จ) แล้วลองขายมากกว่าสต็อกที่มี (→ ROLLBACK สต็อกไม่ลด และไม่มีบิลค้าง)
    </p>
</body>
</html>
