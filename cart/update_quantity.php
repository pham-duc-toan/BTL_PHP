<?php
include_once __DIR__ . '/../helper/db.php';
session_start();

if (!isset($_SESSION['user'])) exit;

$id = $_POST['id'] ?? '';
$qty = max(1, (int) ($_POST['quantity'] ?? 1));

$stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("iss", $qty, $id, $_SESSION['user']['id']);
$stmt->execute();
