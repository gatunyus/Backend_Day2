<?php
// ============================================================
// record_api.php — ตัวอย่าง API จัดการสินค้าครบ CRUD ในไฟล์เดียว
// ทำงานตาม HTTP method:
//   GET    /record_api.php          -> รายการทั้งหมด
//   GET    /record_api.php?id=2     -> รายการเดียว
//   POST   /record_api.php          -> เพิ่ม (ส่ง JSON: name, category, price, stock)
//   PUT    /record_api.php?id=2     -> แก้ไข (ส่ง JSON)
//   DELETE /record_api.php?id=2     -> ลบ
// ============================================================

require '../db.php';
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

// ฟังก์ชันช่วยตอบกลับ JSON แล้วจบการทำงาน
function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// อ่าน JSON ที่ส่งเข้ามา (ใช้ตอน POST/PUT)
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {

    case 'GET':
        if ($id) {
            $res = mysqli_query($conn, "SELECT * FROM products WHERE id = " . (int)$id);
            $row = mysqli_fetch_assoc($res);
            if ($row) {
                respond(["success" => true, "data" => $row]);
            } else {
                respond(["success" => false, "message" => "ไม่พบสินค้า"], 404);
            }
        } else {
            $res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
            $list = [];
            while ($r = mysqli_fetch_assoc($res)) { $list[] = $r; }
            respond(["success" => true, "data" => $list]);
        }
        break;

    case 'POST':
        $name     = $input['name'] ?? '';
        $category = $input['category'] ?? '';
        $price    = $input['price'] ?? 0;
        $stock    = $input['stock'] ?? 0;

        if ($name === '') {
            respond(["success" => false, "message" => "ต้องระบุชื่อสินค้า"], 400);
        }

        $name = mysqli_real_escape_string($conn, $name);
        $category = mysqli_real_escape_string($conn, $category);
        $sql = "INSERT INTO products (name, category, price, stock)
                VALUES ('$name', '$category', " . (float)$price . ", " . (int)$stock . ")";
        if (mysqli_query($conn, $sql)) {
            respond(["success" => true, "id" => mysqli_insert_id($conn)], 201);
        } else {
            respond(["success" => false, "message" => mysqli_error($conn)], 500);
        }
        break;

    case 'PUT':
        if (!$id) respond(["success" => false, "message" => "ต้องระบุ id"], 400);
        $name     = mysqli_real_escape_string($conn, $input['name'] ?? '');
        $category = mysqli_real_escape_string($conn, $input['category'] ?? '');
        $price    = (float)($input['price'] ?? 0);
        $stock    = (int)($input['stock'] ?? 0);

        $sql = "UPDATE products
                SET name='$name', category='$category', price=$price, stock=$stock
                WHERE id=" . (int)$id;
        if (mysqli_query($conn, $sql)) {
            respond(["success" => true, "message" => "แก้ไขสำเร็จ"]);
        } else {
            respond(["success" => false, "message" => mysqli_error($conn)], 500);
        }
        break;

    case 'DELETE':
        if (!$id) respond(["success" => false, "message" => "ต้องระบุ id"], 400);
        if (mysqli_query($conn, "DELETE FROM products WHERE id=" . (int)$id)) {
            respond(["success" => true, "message" => "ลบสำเร็จ"]);
        } else {
            respond(["success" => false, "message" => mysqli_error($conn)], 500);
        }
        break;

    default:
        respond(["success" => false, "message" => "ไม่รองรับ method นี้"], 405);
}
