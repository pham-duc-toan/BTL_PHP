<?php
include_once __DIR__ . '/../helper/db.php';
session_start();

$user_id = $_SESSION['user']['id'];
$full_name = $_POST['full_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';

if (!$full_name || !$phone || !$address) {
  $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin.";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

$stmt = $conn->prepare("INSERT INTO addresses (user_id, full_name, phone, address) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $user_id, $full_name, $phone, $address);
$success = $stmt->execute();

if ($success) {
  $_SESSION['success'] = "Đã thêm địa chỉ mới!";
  echo json_encode(['success' => true, 'new_id' => $conn->insert_id]);
} else {
  echo json_encode(['success' => false, 'error' => "Không thể thêm địa chỉ."]);
}
