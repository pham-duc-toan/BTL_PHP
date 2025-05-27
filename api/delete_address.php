<?php
include_once __DIR__ . '/../helper/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
  http_response_code(401); // Unauthorized
  echo json_encode([
    'success' => false,
    'message' => 'Chưa đăng nhập. Vui lòng đăng nhập lại.'
  ]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['_method'] === 'DELETE') {
  $user_id = $_SESSION['user']['id'];
  $id = $_POST['id'];

  // ❗ Cập nhật user_id = NULL thay vì xoá
  $stmt = $conn->prepare("UPDATE addresses SET user_id = NULL WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ss", $id, $user_id);

  if (!$stmt->execute()) {
    echo json_encode([
      'success' => false,
      'message' => 'Không thể xoá địa chỉ. Chi tiết: ' . $stmt->error
    ]);
    exit;
  }

  echo json_encode([
    'success' => true,
    'message' => 'Địa chỉ đã được gỡ khỏi tài khoản.'
  ]);
  exit;
}

// Trường hợp không hợp lệ
http_response_code(400);
echo json_encode([
  'success' => false,
  'message' => 'Yêu cầu không hợp lệ.'
]);
