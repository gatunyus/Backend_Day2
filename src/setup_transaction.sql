-- ============================================================
-- ตารางเสริมสำหรับ Lab 4 (Transaction ใน PHP)
-- วิธีใช้: phpMyAdmin > เลือกฐานข้อมูล atsoft_day2 > แท็บ SQL > วางทั้งหมด > Go
-- หมายเหตุ: ไฟล์นี้ "เพิ่ม" ตารางใหม่ ไม่ลบของเดิม (products/users ยังอยู่ครบ)
-- ============================================================

USE atsoft_day2;

-- ---------- ตารางบัญชี (ใช้ฝึกโจทย์ "โอนเงิน") ----------
-- ต่อเนื่องจาก Day 1 ที่เคยฝึก Transaction โอนเงินด้วย SQL ล้วน
-- คราวนี้เราจะสั่ง Transaction เดียวกันนี้ผ่านโค้ด PHP แทน
DROP TABLE IF EXISTS accounts;
CREATE TABLE accounts (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  owner   VARCHAR(100)  NOT NULL,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;   -- ⭐ ต้องเป็น InnoDB ถึงจะใช้ Transaction ได้

INSERT INTO accounts (owner, balance) VALUES
  ('บัญชี A (สมชาย)', 1000.00),
  ('บัญชี B (มานี)',   500.00);

-- ---------- ตารางคำสั่งซื้อ (ใช้ฝึกโจทย์ "หักสต็อก + เปิดบิล") ----------
-- ใช้คู่กับตาราง products เดิม: 1 การขาย = หักสต็อก products + เพิ่มแถว orders
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  product_id   INT           NOT NULL,
  product_name VARCHAR(100)  NOT NULL,
  qty          INT           NOT NULL,
  total_price  DECIMAL(10,2) NOT NULL,
  order_date   DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SELECT 'setup_transaction เสร็จแล้ว! (เพิ่มตาราง accounts + orders)' AS message;
