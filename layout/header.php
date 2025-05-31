<?php
include_once __DIR__ . "/../helper/db.php"; // Kết nối DB nếu cần
if (session_status() == PHP_SESSION_NONE) session_start();

// Kiểm tra session user
$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? null;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TapHoaOnline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/cuahangtaphoa/assets/style.css">
</head>

<body>
  <?php include_once __DIR__ . '/../components/session_toast.php'; ?>


  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="/cuahangtaphoa/index.php">🛍️ TapHoaOnline</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <?php if ($user): ?>
            <?php if ($role === 'admin'): ?>
              <li class="nav-item"><a class="nav-link" href="/cuahangtaphoa/admin/orders.php">📦 Quản lý đơn hàng</a></li>
            <?php else: ?>
              <?php include_once __DIR__ . '/components/cart_header.php'; ?>
              <li class="nav-item"><a class="nav-link" href="/cuahangtaphoa/orders/my_orders.php">📜 Đơn hàng của tôi</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="/cuahangtaphoa/auth/logout.php">🚪 Đăng xuất</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/cuahangtaphoa/auth/login.php">Đăng nhập</a></li>
            <li class="nav-item"><a class="nav-link" href="/cuahangtaphoa/auth/register.php">Đăng ký</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
  <div class="container mt-4">