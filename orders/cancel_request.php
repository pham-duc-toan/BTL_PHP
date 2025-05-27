<?php
// File: orders/cancel_request.php
session_start();
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../helper/functions.php';


if (!isset($_SESSION['user'])) {
  $_SESSION['error'] = "Bạn phải đăng nhập để huỷ đơn hàng.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

$order_id = $_POST['order_id'] ?? '';
$bank_info = $_POST['bank_info'] ?? null;
$user_id = $_SESSION['user']['id'];

if (!$order_id) {
  $_SESSION['error'] = "Thiếu mã đơn hàng.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

// Kiểm tra đơn có thuộc user không và trạng thái hợp lệ
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['error'] = "Không tìm thấy đơn hàng.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

$order = $result->fetch_assoc();
if (!in_array($order['order_status'], ['chuẩn bị lấy hàng', 'đang giao'])) {
  $_SESSION['error'] = "Đơn hàng không thể huỷ ở trạng thái hiện tại.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

$new_status = ($order['payment_method'] === 'bank_transfer') ? 'chưa hoàn tiền' : 'yêu cầu huỷ';

if ($new_status === 'chưa hoàn tiền' && !$bank_info) {
  $_SESSION['error'] = "Vui lòng nhập STK để hoàn tiền.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

$update_sql = "UPDATE orders SET order_status = ?, refund_info = ? WHERE id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("sss", $new_status, $bank_info, $order_id);
$stmt->execute();

$_SESSION['success'] = "Yêu cầu huỷ đã được gửi.";
header("Location: /cuahangtaphoa/orders/my_orders.php");
exit;
