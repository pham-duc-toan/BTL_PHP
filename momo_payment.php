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

$order_id = $_POST['order_id'] ?? '';

$user_id = $_SESSION['user']['id'];

// Kiểm tra đơn hàng tồn tại, thuộc user, chưa thanh toán
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND payment_method = 'bank_transfer' AND order_status = 'chưa thanh toán'");
$stmt->bind_param("ss", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['error'] = "Không tìm thấy đơn hàng hợp lệ.";
  header("Location: /cuahangtaphoa/orders/my_orders.php");
  exit;
}

$order = $result->fetch_assoc();
$amount = (string)(int)$order['total_amount'];
$extraData = ""; // bắt buộc phải có

// MoMo (sandbox) config
$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
$accessKey  = "F8BBA842ECF85";
$secretKey  = "K951B6PE1waDMi640xX08PD3vg6EkVlz";
$partnerCode = "MOMO";

// Tạo orderId duy nhất cho MoMo
$timestamp = time();
$mo_order_id = $order_id . "_" . $timestamp;
$requestId = $mo_order_id;
$orderInfo = "Thanh toán đơn hàng $order_id";

$redirectUrl = "https://a258-1-55-100-205.ngrok-free.app/cuahangtaphoa/momo_return.php";
$ipnUrl = "https://a258-1-55-100-205.ngrok-free.app/cuahangtaphoa/momo_return.php";
$requestType = "captureWallet";

// Tạo chữ ký
$rawHash = "accessKey=$accessKey"
  . "&amount=$amount"
  . "&extraData=$extraData"
  . "&ipnUrl=$ipnUrl"
  . "&orderId=$mo_order_id"
  . "&orderInfo=$orderInfo"
  . "&partnerCode=$partnerCode"
  . "&redirectUrl=$redirectUrl"
  . "&requestId=$requestId"
  . "&requestType=$requestType";

$signature = hash_hmac("sha256", $rawHash, $secretKey);

// Gửi yêu cầu tới MoMo
$data = [
  'partnerCode' => $partnerCode,
  'accessKey' => $accessKey,
  'requestId' => $requestId,
  'amount' => $amount,
  'orderId' => $mo_order_id,
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

$response = curl_exec($ch);
curl_close($ch);

$res = json_decode($response, true);
if (isset($res['payUrl'])) {
  header('Location: ' . $res['payUrl']);
  exit;
} else {
  echo "Lỗi khi tạo yêu cầu thanh toán.<br>";
  echo "<pre>";
  var_dump($res);
  echo "</pre>";
}
