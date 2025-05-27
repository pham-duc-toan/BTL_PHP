<?php
session_start();
header('Content-Type: application/json');
include_once __DIR__ . '/../helper/db.php';

$order_id = $_POST['order_id'] ?? '';

if (!$order_id) {
  echo json_encode(['status' => 'error', 'message' => 'Thiếu mã đơn hàng']);
  exit;
}

$stmt = $conn->prepare("SELECT full_name, bank_number, total_amount FROM refund_requests WHERE order_id = ?");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy yêu cầu hoàn tiền.']);
} else {
  $data = $result->fetch_assoc();
  echo json_encode(['status' => 'success', 'data' => $data]);
}
