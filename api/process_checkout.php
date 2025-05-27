<?php
// File: api/process_checkout.php
session_start();
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../helper/functions.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Bạn phải đăng nhập để đặt hàng.";
    header("Location: /cuahangtaphoa/cart/cart.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$address_id = $_POST['address_id'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$selected_ids = $_POST['selected_ids'] ?? '';

if (!$address_id || !$payment_method || !$selected_ids) {
    $_SESSION['error'] = "Thiếu thông tin đơn hàng.";
    header("Location: /cuahangtaphoa/cart/cart.php");
    exit;
}

$selected_ids_arr = explode(',', $selected_ids);
$id_list = implode("','", array_map('trim', $selected_ids_arr));

// Lấy thông tin sản phẩm từ cart
$sql = "SELECT ci.*, p.price FROM cart_items ci 
        JOIN products p ON ci.product_id = p.id 
        WHERE ci.id IN ('$id_list') AND ci.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

if (empty($items)) {
    $_SESSION['error'] = "Không tìm thấy sản phẩm trong giỏ hàng.";
    header("Location: /cuahangtaphoa/cart/cart.php");
    exit;
}

$order_id = generateId();
$status = ($payment_method === 'cod') ? 'chuẩn bị lấy hàng' : 'chưa thanh toán';
$order_date = date('Y-m-d H:i:s');
$payment_status = 'chưa thanh toán';

// Tạo đơn hàng (có thêm total_amount)
$sql = "INSERT INTO orders (
            id, user_id, address_id, order_date, 
            payment_method, payment_status, order_status, total_amount
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Lỗi prepare orders: " . $conn->error);
}

$stmt->bind_param(
    "sssssssd",
    $order_id,
    $user_id,
    $address_id,
    $order_date,
    $payment_method,
    $payment_status,
    $status,
    $total
);

if (!$stmt->execute()) {
    die("Lỗi execute orders: " . $stmt->error);
}

// Tạo chi tiết đơn hàng
$stmt_item = $conn->prepare("INSERT INTO order_items (id, order_id, product_id, size, quantity, price) 
                             VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt_item) {
    die("Lỗi prepare order_items: " . $conn->error);
}
foreach ($items as $item) {
    $item_id = generateId();
    $stmt_item->bind_param(
        "sssssd",
        $item_id,
        $order_id,
        $item['product_id'],
        $item['size'],
        $item['quantity'],
        $item['price']
    );
    $stmt_item->execute();
}

// Xoá sản phẩm khỏi giỏ hàng
$conn->query("DELETE FROM cart_items WHERE id IN ('$id_list') AND user_id = '$user_id'");

// Điều hướng sau khi đặt hàng
if ($payment_method === 'bank_transfer') {
    $_SESSION['success'] = "Đơn hàng đã được tạo. Vui lòng thanh toán qua MoMo.";
    header("Location: /cuahangtaphoa/momo_payment.php?order_id=$order_id");
} else {
    $_SESSION['success'] = "Đặt hàng thành công. Đơn hàng đang được chuẩn bị.";
    header("Location: /cuahangtaphoa/orders/my_orders.php");
}
exit;
