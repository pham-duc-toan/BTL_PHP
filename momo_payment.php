<?php
// File: momo_payment.php
session_start();
include_once __DIR__ . '/helper/db.php';
include_once __DIR__ . '/helper/functions.php';

if (!isset($_SESSION['user'])) {
  $_SESSION['error'] = "Bạn phải đăng nhập để thanh toán.";
  header("Location: login.php");
  exit;
}

$order_id = $_GET['order_id'] ?? '';
$user_id = $_SESSION['user']['id'];

// Kiểm tra đơn hàng thuộc user và chưa thanh toán
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND order_status = 'chưa thanh toán'");
$stmt->bind_param("ss", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['error'] = "Không tìm thấy đơn hợp lệ.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

$order = $result->fetch_assoc();
$total = (int)$order['total_amount'];

// Thông tin tích hợp MoMo (sandbox)
$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = "MOMOBKUN20180529";
$accessKey = "klm05TvNBzhg7h7j";
$secretKey = "qH6sJbtvN8jK2t3xM3s3K1d9S3m1FQ9h";
$orderInfo = "Thanh toán đơn hàng $order_id";
$redirectUrl = "https://2261-42-116-243-148.ngrok-free.app/BTL_PHP/momo_return.php";
$ipnUrl = "https://2261-42-116-243-148.ngrok-free.app/BTL_PHP/momo_return.php";
$requestId = time() . "";
$requestType = "captureWallet";
$extraData = "";

// Tạo chữ ký
$rawHash = "accessKey=$accessKey&amount=$total&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$order_id&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
$signature = hash_hmac("sha256", $rawHash, $secretKey);

$data = [
  'partnerCode' => $partnerCode,
  'accessKey' => $accessKey,
  'requestId' => $requestId,
  'amount' => "$total",
  'orderId' => $order_id,
  'orderInfo' => $orderInfo,
  'redirectUrl' => $redirectUrl,
  'ipnUrl' => $ipnUrl,
  'extraData' => $extraData,
  'requestType' => $requestType,
  'signature' => $signature,
  'lang' => 'vi'
];

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$result = curl_exec($ch);
curl_close($ch);

$response = json_decode($result, true);
if (isset($response['payUrl'])) {
  header('Location: ' . $response['payUrl']);
  exit;
} else {
  echo "Lỗi khi tạo yêu cầu thanh toán.";
  var_dump($response);
}
