<?php
// File: momo_return.php
session_start();
include_once __DIR__ . '/helper/db.php';
include_once __DIR__ . '/helper/functions.php';


$order_id = $_GET['orderId'] ?? '';
$result_code = $_GET['resultCode'] ?? '-1';

if (!$order_id || $result_code != '0') {
  $_SESSION['error'] = "Thanh toán không thành công hoặc bị huỷ.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

// Cập nhật trạng thái đơn hàng
$stmt = $conn->prepare("UPDATE orders SET order_status = 'chuẩn bị lấy hàng', payment_status = 'đã thanh toán' WHERE id = ? AND order_status = 'chưa thanh toán'");
$stmt->bind_param("s", $order_id);
$stmt->execute();

$_SESSION['success'] = "Thanh toán thành công! Đơn hàng đang được chuẩn bị.";
header("Location: /cuahangtaphoa/orders/my_orders.php");
exit;
