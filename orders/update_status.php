<?php
session_start();
include_once __DIR__ . '/../helper/db.php';

header('Content-Type: application/json');

if ($_SESSION['user']['role'] !== 'admin') {
  echo json_encode(['status' => 'error', 'message' => 'Không có quyền.']);
  exit;
}

$order_id = $_POST['order_id'] ?? '';
$new_status = $_POST['new_status'] ?? '';

if (!$order_id || !$new_status) {
  echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin.']);
  exit;
}

$stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
$stmt->bind_param("ss", $new_status, $order_id);
$stmt->execute();

echo json_encode(['status' => 'success', 'message' => 'Đã cập nhật trạng thái.']);
