<?php
include_once __DIR__ . '/../helper/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['_method'] === 'DELETE') {
  $user_id = $_SESSION['user']['id'];
  $id = $_POST['id'];

  $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ss", $id, $user_id);
  $success = $stmt->execute();

  if ($success) {
    $_SESSION['success'] = "Đã xoá địa chỉ!";
  } else {
    $_SESSION['error'] = "Không thể xoá địa chỉ.";
  }
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
