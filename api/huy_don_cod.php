<?php
// File: xuly/huy_don_cod.php
session_start();
require_once __DIR__ . '/../helper/db.php';

if (!isset($_SESSION['user'])) {
  $_SESSION['error'] = "Bạn cần đăng nhập để tiếp tục.";
  header("Location: /cuahangtaphoa/login.php");
  exit;
}

$order_id = $_POST['order_id'] ?? '';
$user_id = $_SESSION['user']['id'];

if (!$order_id) {
  $_SESSION['error'] = "Thiếu mã đơn hàng.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

// Kiểm tra đơn hàng hợp lệ
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND payment_method = 'cod' AND order_status IN ('chuẩn bị lấy hàng', 'đang giao')");
$stmt->bind_param("ss", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['error'] = "Không tìm thấy đơn hàng hợp lệ để huỷ.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

// Cập nhật trạng thái
$update = $conn->prepare("UPDATE orders SET order_status = 'đã huỷ' WHERE id = ?");
$update->bind_param("s", $order_id);
$update->execute();

$_SESSION['success'] = "Đã huỷ đơn hàng thành công.";
header("Location: /cuahangtaphoa/orders/my_orders.php");
exit;
