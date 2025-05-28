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

// Bắt đầu giao dịch
$conn->begin_transaction();
try {
  $order_id = generateId();
  $stmt = $conn->prepare("INSERT INTO orders (id,user_id, address_id, payment_method, total_amount, order_status) VALUES (?,?, ?, ?, ?, 'chuẩn bị lấy hàng')");
  $stmt->bind_param("ssssd", $order_id, $user_id, $address_id, $payment_method, $total); // Sửa 'iisd' thành 'issd'



  $stmt->execute();
  if ($stmt->affected_rows === 0) {
    throw new Exception("Tạo đơn hàng thất bại.");
  }


  // Thêm chi tiết đơn hàng
  $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, size, price) VALUES (?, ?, ?, ?, ?)");
  $stmt2->bind_param("ssisd", $order_id, $product_id, $quantity, $size, $price);
  $stmt2->execute();
  if ($stmt2->affected_rows === 0) {
    throw new Exception("Thêm chi tiết đơn hàng thất bại.");
  }

  $conn->commit();
  echo json_encode(['success' => true]);
} catch (Exception $e) {
  $conn->rollback();
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
