<?php
include_once __DIR__ . "/../helper/db.php";
if (session_status() == PHP_SESSION_NONE) session_start();

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? null;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GoShopOnline</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/cuahangtaphoa/assets/style.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }

    .navbar-brand {
      font-weight: 600;
      font-size: 1.5rem;
      color: #28a745 !important;
    }

    .navbar-nav .nav-link {
      font-weight: 500;
    }

    .main {
      min-height: calc(100vh - 256px);
    }
  </style>
</head>

<body>
  <?php include_once __DIR__ . '/../components/session_toast.php'; ?>

  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand" href="/cuahangtaphoa/<?= $role === 'admin' ? 'admin_dashboard.php' : 'index.php' ?>">
        🛍️ GoShopOnline
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav ms-auto align-items-center">
          <?php if ($user): ?>
            <?php if ($role === 'user'): ?>
              <?php include_once __DIR__ . '/components/cart_header.php'; ?>
            <?php endif; ?>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="/cuahangtaphoa/assets/avt.jpg" alt="Avatar" width="32" height="32" class="rounded-circle me-2">
                <?= htmlspecialchars($user['username']) ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="/cuahangtaphoa/account/profile.php"><i class="bi bi-person-circle me-2"></i> Tài khoản của tôi</a></li>
                <?php if ($role === 'user'): ?>
                  <li><a class="dropdown-item" href="/cuahangtaphoa/orders/my_orders.php"><i class="bi bi-receipt me-2"></i> Đơn mua</a></li>
                <?php endif; ?>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="/cuahangtaphoa/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Đăng xuất</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="/cuahangtaphoa/auth/login.php"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/cuahangtaphoa/auth/register.php"><i class="bi bi-person-plus"></i> Đăng ký</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>


  <div class="container py-4 main">