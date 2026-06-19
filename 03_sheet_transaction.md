# ชีท 03 · SQL Transaction ใน PHP

## จุดประสงค์
สั่ง **Transaction** (COMMIT / ROLLBACK) ผ่านโค้ด **PHP** เพื่อให้หลายคำสั่งที่ต้องไปด้วยกัน "สำเร็จทั้งหมด หรือยกเลิกทั้งหมด"

> Day 1 เราฝึก Transaction ด้วย SQL ล้วนใน phpMyAdmin (พิมพ์ `START TRANSACTION; ... COMMIT;` เอง)
> วันนี้เราเอาแนวคิดเดิมมาสั่งผ่าน PHP — ข้อดีคือ **ให้ `if` ในโค้ดตัดสินใจ commit/rollback อัตโนมัติ** แทนที่จะดูด้วยตา

---

## 1. ทบทวน: ทำไมต้องใช้ Transaction

สมมติ "โอนเงิน" จากบัญชี A ไป B ต้องทำ 2 ขั้น:
1. ลดเงินบัญชี A
2. เพิ่มเงินบัญชี B

ถ้าขั้น 1 สำเร็จแล้วโปรแกรมพังก่อนขั้น 2 → **เงินหายไปเฉยๆ**
Transaction บอกว่า "สองขั้นนี้ต้องสำเร็จคู่กัน ถ้าพังขั้นใดขั้นหนึ่ง ให้ย้อนกลับเหมือนไม่เคยทำ"

> ใช้ได้เฉพาะตารางชนิด **InnoDB** (ตาราง `products` / `accounts` / `orders` ของเราเป็น InnoDB อยู่แล้ว)

---

## 2. คำสั่ง Transaction เวอร์ชัน PHP (mysqli)

| SQL ล้วน (Day 1) | PHP (mysqli) |
| :-- | :-- |
| `START TRANSACTION;` | `mysqli_begin_transaction($conn);` |
| `COMMIT;` | `mysqli_commit($conn);` |
| `ROLLBACK;` | `mysqli_rollback($conn);` |

โครงสร้างมาตรฐานที่เราจะใช้ คือ **`try / catch`**:

```php
mysqli_begin_transaction($conn);          // เริ่ม (ปิด autocommit ชั่วคราว)
try {
    // ... ทำหลายคำสั่ง SQL ที่ต้องสำเร็จคู่กัน ...
    // ถ้าเงื่อนไขไม่ผ่าน ให้ throw เพื่อกระโดดไป catch
    if (เงื่อนไขผิด) {
        throw new Exception('เหตุผลที่ทำต่อไม่ได้');
    }
    mysqli_commit($conn);                 // ✅ ผ่านหมด → ยืนยันพร้อมกัน
} catch (Exception $e) {
    mysqli_rollback($conn);               // ❌ พลาด → ย้อนกลับทั้งหมด
    $error = $e->getMessage();
}
```

> **หัวใจ:** `throw` ที่ไหนก็ได้ในบล็อก `try` จะกระโดดมา `catch` ทันที → เราเลยเอา `mysqli_rollback()` ไว้ที่เดียวใน `catch` พอ ไม่ต้องเขียนซ้ำทุกจุด

---

## 3. ตัวอย่างที่ 1 — โอนเงิน (เช็คเงื่อนไขกลางทาง)

หัวใจคือ "ทำขั้นแรก → ดูผลกลางทาง → ค่อยตัดสินใจไปต่อหรือยกเลิก"

```php
mysqli_begin_transaction($conn);
try {
    // 1) ลดเงินต้นทาง
    mysqli_query($conn, "UPDATE accounts SET balance = balance - $amount WHERE id = $from");

    // 2) เช็คกลางทาง: ยอดห้ามติดลบ
    $res = mysqli_query($conn, "SELECT balance FROM accounts WHERE id = $from");
    $row = mysqli_fetch_assoc($res);
    if ($row['balance'] < 0) {
        throw new Exception('เงินไม่พอ');   // → กระโดดไป rollback
    }

    // 3) เพิ่มเงินปลายทาง
    mysqli_query($conn, "UPDATE accounts SET balance = balance + $amount WHERE id = $to");

    mysqli_commit($conn);                   // ✅ เงินพอ ยืนยันทั้งคู่
} catch (Exception $e) {
    mysqli_rollback($conn);                 // ❌ เงินไม่พอ ยอดกลับเป็นเดิม
}
```

เทียบกับ Day 1: เมื่อก่อนเราต้อง `SELECT` ดูยอดด้วยตาเองแล้วตัดสินใจพิมพ์ `ROLLBACK`
ตอนนี้ `if ($row['balance'] < 0)` ทำหน้าที่ "ดูแทนตาเรา" และเลือก rollback ให้อัตโนมัติ

---

## 4. ตัวอย่างที่ 2 — ขายของ: หักสต็อก + เปิดบิล (หลายตาราง)

งานขายจริงต้องทำ 2 ตารางให้สำเร็จคู่กัน: **ลดสต็อก** `products` + **เพิ่มบิล** `orders`
ถ้าหักสต็อกแล้วเปิดบิลพลาด → สต็อกหายแต่ไม่มีบิล (ข้อมูลเพี้ยน)

```php
mysqli_begin_transaction($conn);
try {
    // อ่านสต็อกปัจจุบันก่อน
    $res  = mysqli_query($conn, "SELECT price, stock FROM products WHERE id = $pid");
    $prod = mysqli_fetch_assoc($res);
    if ($prod['stock'] < $qty) {
        throw new Exception('สต็อกไม่พอ');
    }

    // 1) หักสต็อก   2) เปิดบิล
    mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $pid");
    mysqli_query($conn, "INSERT INTO orders (product_id, qty, total_price) VALUES (...)");

    mysqli_commit($conn);
} catch (Exception $e) {
    mysqli_rollback($conn);
}
```

> 📌 แพตเทิร์น "อ่านก่อน → เช็ค → เขียนหลายตาราง → commit/rollback" นี้
> คือสิ่งเดียวกับที่จะเจอใน **Day 3** (เพิ่มรายการผลิต = `INSERT` รายการย่อย + `UPDATE` ยอดรวมในใบงาน)

---

## 5. กับดักที่พบบ่อย

- **ลืม `mysqli_begin_transaction()`** → อยู่ในโหมด autocommit ทุกคำสั่งบันทึกทันที rollback ไม่มีผล
- **ตารางเป็น MyISAM** → Transaction ใช้ไม่ได้ (ต้อง `ENGINE=InnoDB`)
- **เขียน `commit` แต่ลืม `rollback` ใน catch** → พอ error ข้อมูลจะค้างครึ่งๆ กลางๆ
- **`throw` แล้วไม่มี `try/catch` ครอบ** → โปรแกรมตายโดยไม่ rollback

---

## เช็คความเข้าใจ
- [ ] บอกคู่คำสั่ง SQL ↔ PHP ได้ (`START TRANSACTION`↔`begin_transaction` ฯลฯ)
- [ ] เข้าใจว่า `throw` ในบล็อก `try` ทำให้กระโดดไป `rollback` ใน `catch`
- [ ] อธิบายได้ว่าทำไมต้องใช้ InnoDB
- [ ] เห็นความเชื่อมโยงกับโจทย์ Day 3 (หลายตารางต้องสำเร็จคู่กัน)

ไปต่อ: [04_lab_transaction.md](./04_lab_transaction.md)
