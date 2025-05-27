<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "../helper/db.php";
include "../helper/functions.php";
// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: /cuahangtaphoa/auth/login.php");
    exit;
}

// Lấy dữ liệu từ form
$user_id    = $_SESSION['user']['id'];
$product_id = $_POST['product_id'] ?? '';
$size       = $_POST['size'] ?? 'M';
$quantity   = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

// Nếu dữ liệu không hợp lệ
if ($product_id <= 0 || !in_array($size, ['M', 'L', 'XL'])) {
    $_SESSION['error'] = "Dữ liệu không hợp lệ!";
    header("Location: /cuahangtaphoa/index.php");
    exit;
}



$stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=? AND size=?");
$stmt->bind_param("iis", $user_id, $product_id, $size);
$stmt->execute();
$result = $stmt->get_result();



if ($row = $result->fetch_assoc()) {
    // Tăng số lượng nếu đã có
    $new_qty = $row['quantity'] + $quantity;

    $update = $conn->prepare("UPDATE cart_items SET quantity=? WHERE id=?");
    $update->bind_param("ii", $new_qty, $row['id']);
    $update->execute();
} else {
    $cart_id = generateId();
    // Thêm mới nếu chưa có
    $stmt = $conn->prepare("INSERT INTO cart_items (id, user_id, product_id, size, quantity)
                        VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $cart_id, $user_id, $product_id, $size, $quantity);
    $stmt->execute();
}


$_SESSION['success'] = "Thêm vào giỏ hàng thành công!";
if (!empty($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: /cuahangtaphoa/index.php");
}

exit;
