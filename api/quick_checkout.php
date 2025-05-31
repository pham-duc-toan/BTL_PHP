<?php
session_start();
header('Content-Type: application/json');
include "../helper/db.php";
include "../helper/functions.php";
// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
  echo json_encode(['success' => false, 'error' => 'Bạn chưa đăng nhập.']);
  exit;
}

// Nhận dữ liệu từ form
$user_id = $_SESSION['user']['id'];
$product_id = $_POST['product_id'] ?? null;
$price = $_POST['price'] ?? null;
$quantity = $_POST['quantity'] ?? null;
$size = $_POST['size'] ?? null;
$address_id = $_POST['address_id'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;

if (!$product_id || !$price || !$quantity || !$size || !$address_id || !$payment_method) {
  echo json_encode(['success' => false, 'error' => 'Thiếu dữ liệu cần thiết.']);
  exit;
}

// Tính tổng tiền
$total = $price * $quantity;
// Xác định trạng thái đơn theo phương thức thanh toán
if ($payment_method === 'bank_transfer') {
  $order_status = 'chưa thanh toán';
} else {
  $order_status = 'chuẩn bị lấy hàng';
}

// Bắt đầu giao dịch
$conn->begin_transaction();
try {
  // Bắt đầu transaction
  $conn->begin_transaction();

  // Tạo đơn hàng
  $order_id = generateId();  // Tạo ID cho đơn
  $stmt = $conn->prepare("INSERT INTO orders (id, user_id, address_id, payment_method, total_amount, order_status)
                        VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssds", $order_id, $user_id, $address_id, $payment_method, $total, $order_status);

  $stmt->execute();
  if ($stmt->affected_rows === 0) {
    throw new Exception("Tạo đơn hàng thất bại.");
  }

  // Thêm chi tiết đơn hàng (chỉ 1 sản phẩm)
  $order_item_id = generateId(); // ID riêng cho order_items
  $stmt2 = $conn->prepare("INSERT INTO order_items (id, order_id, product_id, quantity, size, price)
                           VALUES (?, ?, ?, ?, ?, ?)");
  $stmt2->bind_param("sssisd", $order_item_id, $order_id, $product_id, $quantity, $size, $price);
  $stmt2->execute();
  if ($stmt2->affected_rows === 0) {
    throw new Exception("Thêm chi tiết đơn hàng thất bại.");
  }

  // Thành công
  $conn->commit();
  echo json_encode(['success' => true]);
} catch (Exception $e) {
  $conn->rollback();
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
