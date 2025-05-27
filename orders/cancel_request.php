<?php
session_start();
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../helper/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
  echo json_encode(['status' => 'error', 'message' => 'Bạn phải đăng nhập.']);
  exit;
}

$order_id = $_POST['order_id'] ?? '';
$full_name = $_POST['full_name'] ?? '';
$bank_number = $_POST['bank_number'] ?? '';
$user_id = $_SESSION['user']['id'];

if (!$order_id) {
  echo json_encode(['status' => 'error', 'message' => 'Thiếu mã đơn hàng.']);
  exit;
}

// Lấy đơn hàng để kiểm tra
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ss", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng.']);
  exit;
}

$order = $result->fetch_assoc();

if (!in_array($order['order_status'], ['chuẩn bị lấy hàng', 'đang giao'])) {
  echo json_encode(['status' => 'error', 'message' => 'Không thể huỷ ở trạng thái hiện tại.']);
  exit;
}

// Xử lý theo phương thức thanh toán
if ($order['payment_method'] === 'bank_transfer') {
  if (!$full_name || !$bank_number) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin hoàn tiền.']);
    exit;
  }

  // Insert yêu cầu hoàn tiền
  $stmt = $conn->prepare("INSERT INTO refund_requests (id, order_id, full_name, bank_number, total_amount)
                          VALUES (?, ?, ?, ?, ?)");
  $refund_id = generateId(); // UUID
  $stmt->bind_param("ssssd", $refund_id, $order_id, $full_name, $bank_number, $order['total_amount']);
  $stmt->execute();

  // Cập nhật trạng thái đơn
  $stmt = $conn->prepare("UPDATE orders SET order_status = 'chưa hoàn tiền' WHERE id = ?");
  $stmt->bind_param("s", $order_id);
  $stmt->execute();

  echo json_encode(['status' => 'success', 'message' => 'Yêu cầu hoàn tiền đã được ghi nhận.']);
  exit;
} else {
  // COD → huỷ luôn
  $stmt = $conn->prepare("UPDATE orders SET order_status = 'đã huỷ' WHERE id = ?");
  $stmt->bind_param("s", $order_id);
  $stmt->execute();

  echo json_encode(['status' => 'success', 'message' => 'Đơn hàng đã được huỷ.']);
  exit;
}
