<?php
include_once __DIR__ . '/../helper/db.php';
session_start();
if (!isset($_SESSION['user'])) exit;

$user_id = $_SESSION['user']['id'];
$result = $conn->query("SELECT * FROM addresses WHERE user_id = '$user_id' ORDER BY created_at DESC");

$addresses = [];
while ($row = $result->fetch_assoc()) {
  $addresses[] = $row;
}

header('Content-Type: application/json');
echo json_encode($addresses);
