<?php
// File: orders/cancel_request.php
session_start();
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../helper/functions.php';

if (!isset($_SESSION['user'])) {
  $_SESSION['error'] = "Vui lòng đăng nhập để huỷ đơn hàng.";
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

// Lấy đơn hàng để kiểm tra
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

// Chỉ cho phép huỷ nếu trạng thái là đang xử lý
if (!in_array($order['order_status'], ['chuẩn bị lấy hàng', 'đang giao'])) {
  $_SESSION['error'] = "Đơn hàng không thể huỷ ở trạng thái hiện tại.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

// Tùy theo phương thức thanh toán
// Nếu bank_transfer → insert vào bảng refund_requests
if ($order['payment_method'] === 'bank_transfer') {
  $full_name = $_POST['full_name'] ?? '';
  $bank_number = $_POST['bank_number'] ?? '';

  if (!$full_name || !$bank_number) {
    $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin hoàn tiền.";
    header("Location: /cuahangtaphoa/orders/my_orders.php");
    exit;
  }

  // Insert vào bảng refund_requests
  $insert_sql = "INSERT INTO refund_requests (order_id, full_name, bank_number, total_amount) 
                 VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($insert_sql);
  $stmt->bind_param("sssd", $order_id, $full_name, $bank_number, $order['total_amount']);
  $stmt->execute();

  // Cập nhật trạng thái đơn
  $update_sql = "UPDATE orders SET order_status = 'chưa hoàn tiền' WHERE id = ?";
  $stmt = $conn->prepare($update_sql);
  $stmt->bind_param("s", $order_id);
  $stmt->execute();

  $_SESSION['success'] = "Yêu cầu hoàn tiền đã được ghi nhận.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
} else {
  // COD → huỷ trực tiếp
  $new_status = 'đã huỷ';
  $update_sql = "UPDATE orders SET order_status = ? WHERE id = ?";
  $stmt = $conn->prepare($update_sql);
  if (!$stmt) {
    die("Lỗi prepare: " . $conn->error);
  }
  $stmt->bind_param("ss", $new_status, $order_id);
  $stmt->execute();
  $_SESSION['success'] = "Đơn hàng đã được huỷ thành công.";
}

header("Location: /cuahangtaphoa/orders/my_orders.php");
exit;
