<?php
include_once __DIR__ . '/../helper/db.php';
session_start();

if (!isset($_SESSION['user'])) exit;

// Giả lập method DELETE (RESTful override)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['_method'] === 'DELETE') {
  $cart_id = $_POST['id'] ?? '';

  if ($cart_id) {
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ss", $cart_id, $_SESSION['user']['id']);
    if ($stmt->execute()) {
      $_SESSION['success'] = "🗑️ Đã xoá sản phẩm khỏi giỏ hàng.";
    } else {
      $_SESSION['error'] = "❌ Xoá thất bại.";
    }
  }
}

header("Location: cart.php");
exit;
