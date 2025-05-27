<?php
session_start();
include_once __DIR__ . '/../helper/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
  echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập']);
  exit;
}

$order_id = $_POST['order_id'] ?? '';
$new_address_id = $_POST['new_address_id'] ?? '';

if (!$order_id || !$new_address_id) {
  echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
  exit;
}

$stmt = $conn->prepare("UPDATE orders SET address_id = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sss", $new_address_id, $order_id, $_SESSION['user']['id']);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
