<?php
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../helper/functions.php';
session_start();

if (!isset($_SESSION['user'])) exit;

$id = generateId();
$user_id = $_SESSION['user']['id'];
$full_name = $_POST['full_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';

$stmt = $conn->prepare("INSERT INTO addresses (id, user_id, full_name, phone, address) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $id, $user_id, $full_name, $phone, $address);

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'new_id' => $id]);
} else {
  echo json_encode(['success' => false, 'error' => $stmt->error]);
}
