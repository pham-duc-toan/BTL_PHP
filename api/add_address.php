<?php
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../helper/functions.php';
session_start();

if (!isset($_SESSION['user'])) {
  echo json_encode(['success' => false, 'error' => "Session user chưa tồn tại."]);
  exit;
}

$user_id = $_SESSION['user']['id'];
$full_name = $_POST['full_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';

if (!$full_name || !$phone || !$address) {
  $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin.";
  echo json_encode(['success' => false, 'error' => $_SESSION['error']]);
  exit;
}
$id = generateId();
$stmt = $conn->prepare("INSERT INTO addresses (id,user_id, full_name, phone, address) VALUES (?, ?, ?, ?,?)");
$stmt->bind_param("sssss", $id, $user_id, $full_name, $phone, $address); // user_id là số nguyên

$success = $stmt->execute();

if ($success) {
  $_SESSION['success'] = "Đã thêm địa chỉ mới!";
  echo json_encode(['success' => true, 'new_id' => $conn->insert_id]);
} else {
  echo json_encode([
    'success' => false,
    'error' => "Không thể thêm địa chỉ. Chi tiết: " . $stmt->error
  ]);
}
