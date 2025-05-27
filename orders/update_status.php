<?php
// File: orders/update_status.php
session_start();
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../helper/functions.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  $_SESSION['error'] = "Bạn không có quyền thực hiện thao tác này.";
  header("Location: ../admin_orders.php");
  exit;
}

$order_id = $_POST['order_id'] ?? '';
$new_status = $_POST['new_status'] ?? '';

if (!$order_id || !$new_status) {
  $_SESSION['error'] = "Thiếu thông tin cập nhật.";
  header("Location: ../admin_orders.php");
  exit;
}

$stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
$stmt->bind_param("ss", $new_status, $order_id);
$stmt->execute();

$_SESSION['success'] = "Đã cập nhật trạng thái đơn hàng.";
header("Location: ../admin_orders.php");
exit;
