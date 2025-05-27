<?php
session_start();
include "../helper/db.php";

if (!isset($_SESSION['user'])) {
  header("Location: /cuahangtaphoa/auth/login.php");
  exit;
}

$user_id = $_SESSION['user']['id'];
$product_id = $_POST['product_id'];
$price = $_POST['price'];
$quantity = $_POST['quantity'];
$size = $_POST['size'];
$address_id = $_POST['address_id'];
$payment_method = $_POST['payment_method'];
$total = $price * $quantity;

$conn->begin_transaction();
try {
  // Tạo đơn hàng
  $stmt = $conn->prepare("INSERT INTO orders (user_id, address_id, payment_method, total_amount, order_status) VALUES (?, ?, ?, ?, 'chuẩn bị lấy hàng')");
  $stmt->bind_param("iisd", $user_id, $address_id, $payment_method, $total);
  $stmt->execute();
  $order_id = $stmt->insert_id;

  // Thêm chi tiết
  $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, size, price) VALUES (?, ?, ?, ?, ?)");
  $stmt2->bind_param("iiisd", $order_id, $product_id, $quantity, $size, $price);
  $stmt2->execute();

  $conn->commit();
  $_SESSION['success'] = "Đặt hàng thành công!";
  header("Location: /cuahangtaphoa/user_orders.php");
} catch (Exception $e) {
  $conn->rollback();
  $_SESSION['error'] = "Có lỗi xảy ra khi đặt hàng!";
  header("Location: /cuahangtaphoa/index.php");
}
