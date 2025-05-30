<?php
include_once __DIR__ . '/../helper/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = trim($_POST['email'] ?? '');

  if (!$email) {
    echo json_encode(['exists' => false]);
    exit;
  }

  $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  echo json_encode(['exists' => $stmt->num_rows > 0]);
}
