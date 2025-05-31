<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../helper/db.php';

$keyword = $_GET['q'] ?? '';
$keyword = "%$keyword%";

$stmt = $conn->prepare("SELECT id, name, image FROM products WHERE name LIKE ? LIMIT 10");
$stmt->bind_param("s", $keyword);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {

  $data[] = $row;
}

echo json_encode($data);
