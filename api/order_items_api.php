<?php
include_once __DIR__ . '/../helper/db.php';

header('Content-Type: application/json');

$order_id = $_GET['order_id'] ?? '';

if (!$order_id) {
  echo json_encode([]);
  exit;
}

$stmt = $conn->prepare("
  SELECT oi.*, p.name, p.image 
  FROM order_items oi
  JOIN products p ON oi.product_id = p.id
  WHERE oi.order_id = ?
");

$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);
