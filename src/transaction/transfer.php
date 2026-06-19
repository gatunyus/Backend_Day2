<?php
// ============================================================
// transfer.php — โอนเงินจากบัญชีหนึ่งไปอีกบัญชี ด้วย Transaction (PHP)
// แนวคิด: ต้องทำ 2 คำสั่งให้ "สำเร็จคู่กัน" — ลดเงินต้นทาง + เพิ่มเงินปลายทาง
//         ถ้าพลาด (เช่น เงินไม่พอ) ต้อง ROLLBACK ให้ยอดกลับเหมือนเดิม
// ============================================================
require '../db.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from   = (int)$_POST['from_id'];      // บัญชีต้นทาง
    $to     = (int)$_POST['to_id'];        // บัญชีปลายทาง
    $amount = (float)$_POST['amount'];      // จำนวนเงินที่โอน

    // ⭐ เริ่ม Transaction = ปิด autocommit ชั่วคราว
    mysqli_begin_transaction($conn);
    try {
        if ($amount <= 0)        throw new Exception('จำนวนเงินต้องมากกว่า 0');
        if ($from === $to)       throw new Exception('โอนเข้าบัญชีเดียวกันไม่ได้');

        // 1) ลดเงินบัญชีต้นทาง
        mysqli_query($conn,
            "UPDATE accounts SET balance = balance - $amount WHERE id = $from");

        // 2) เช็คกลางทาง: ยอดต้นทางห้ามติดลบ (เงินต้องพอ)
        $res = mysqli_query($conn, "SELECT balance FROM accounts WHERE id = $from");
        $row = mysqli_fetch_assoc($res);
        if ($row === null)        throw new Exception('ไม่พบบัญชีต้นทาง');
        if ($row['balance'] < 0)  throw new Exception('เงินในบัญชีต้นทางไม่พอ');

        // 3) เพิ่มเงินบัญชีปลายทาง
        $ok = mysqli_query($conn,
            "UPDATE accounts SET balance = balance + $amount WHERE id = $to");
        if (!$ok || mysqli_affected_rows($conn) === 0) {
            throw new Exception('ไม่พบบัญชีปลายทาง');
        }

        // ✅ ทุกขั้นผ่าน → ยืนยันทั้งหมดพร้อมกัน
        mysqli_commit($conn);
        $msg = "✅ โอนเงิน $amount บาท สำเร็จ";
    } catch (Exception $e) {
        // ❌ มีขั้นใดพลาด → ย้อนกลับทั้งหมด (ขั้น 1 ที่ทำไปแล้วก็ถูกยกเลิกด้วย)
        mysqli_rollback($conn);
        $msg = '❌ โอนไม่สำเร็จ: ' . $e->getMessage();
    }
}

// ดึงยอดล่าสุดมาแสดง
$accounts = mysqli_query($conn, "SELECT * FROM accounts ORDER BY id");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>โอนเงิน (Transaction)</title>
    <style>
        body { font-family: sans-serif; max-width: 520px; margin: 30px auto; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 6px; }
        button { margin-top: 15px; padding: 8px 16px; }
        .msg { padding: 10px; background: #f0f0f0; border-radius: 6px; }
    </style>
</head>
<body>
    <h1>🏦 โอนเงิน (ฝึก Transaction)</h1>

    <?php if ($msg): ?><p class="msg"><?= $msg ?></p><?php endif; ?>

    <table>
        <tr><th>id</th><th>เจ้าของบัญชี</th><th>ยอดเงิน</th></tr>
        <?php while ($a = mysqli_fetch_assoc($accounts)): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= $a['owner'] ?></td>
            <td><?= number_format($a['balance'], 2) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <form method="POST">
        <label>จากบัญชี (id)</label>
        <input type="number" name="from_id" value="1" required>

        <label>ไปบัญชี (id)</label>
        <input type="number" name="to_id" value="2" required>

        <label>จำนวนเงิน</label>
        <input type="number" step="0.01" name="amount" value="200" required>

        <button type="submit">โอนเงิน</button>
    </form>

    <p style="color:#666">
        ลองโอน 200 (สำเร็จ) แล้วลองโอน 9999 (เงินไม่พอ → ROLLBACK ยอดไม่เปลี่ยน)
    </p>
</body>
</html>
