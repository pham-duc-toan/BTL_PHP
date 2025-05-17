<?php
include_once __DIR__ . '/../helper/db.php';
session_start();

if (!isset($_SESSION['user'])) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['_method'] === 'DELETE') {
  $id = $_POST['id'] ?? '';
  $user_id = $_SESSION['user']['id'];

  if ($id) {
    $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ss", $id, $user_id);
    if ($stmt->execute()) {
      $_SESSION['success'] = "🗑️ Đã xoá địa chỉ thành công.";
    } else {
      $_SESSION['error'] = "❌ Không thể xoá địa chỉ.";
    }
  }
}

header("Location: ../cart/cart.php");
exit;
