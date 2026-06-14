-- ============================================================
-- ฐานข้อมูลสำหรับ Day 2 (PHP)
-- วิธีใช้: phpMyAdmin > แท็บ SQL > วางทั้งหมด > Go
-- ============================================================

CREATE DATABASE IF NOT EXISTS atsoft_day2
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE atsoft_day2;

-- ตารางสินค้า (ใช้ใน Lab 3 CRUD และ Lab 4 API)
DROP TABLE IF EXISTS products;
CREATE TABLE products (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  name     VARCHAR(100)  NOT NULL,
  category VARCHAR(50)   NOT NULL,
  price    DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock    INT           NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO products (name, category, price, stock) VALUES
  ('น้ำดื่ม 600ml',  'เครื่องดื่ม', 7.00, 500),
  ('น้ำโซดา 325ml',  'เครื่องดื่ม', 10.00, 300),
  ('ถุงมือผ้า',      'อุปกรณ์',    18.00, 120),
  ('หมวกนิรภัย',     'อุปกรณ์',   250.00,  25);

-- ตารางผู้ใช้ (ใช้ใน Lab 5 Login)
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(50)  NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,         -- เก็บรหัสผ่าน (เริ่มแบบ plain ก่อน แล้วค่อยเปลี่ยนเป็น hash)
  full_name  VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SELECT 'setup เสร็จแล้ว!' AS message;
