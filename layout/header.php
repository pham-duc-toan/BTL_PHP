<?php
include_once __DIR__ . "/../helper/db.php"; // kết nối DB nếu cần
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
  <title>Shopee Clone</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <?php
  $toastTypes = ['success', 'error', 'info', 'warning'];
  foreach ($toastTypes as $type):
    if (isset($_SESSION[$type])):
      // Map kiểu sang Bootstrap class
      $class = match ($type) {
        'success' => 'bg-success text-white',
        'error'   => 'bg-danger text-white',
        'info'    => 'bg-info text-dark',
        'warning' => 'bg-warning text-dark',
      };
  ?>
      <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
        <div class="toast align-items-center <?= $class ?> border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              <?= $_SESSION[$type];
              unset($_SESSION[$type]); ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      </div>
  <?php endif;
  endforeach; ?>


  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="/cuahangtaphoa/index.php">🛍️ Shopee Clone</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <?php if ($user): ?>
            <?php if ($role === 'admin'): ?>
              <li class="nav-item"><a class="nav-link" href="admin_orders.php">📦 Quản lý đơn hàng</a></li>
            <?php else: ?>
              <?php include_once __DIR__ . '/components/cart_header.php'; ?>

              <li class="nav-item"><a class="nav-link" href="my_orders.php">📜 Đơn hàng của tôi</a></li>
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