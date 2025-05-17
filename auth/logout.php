<?php
// Bắt đầu session nếu chưa có
if (session_status() == PHP_SESSION_NONE) session_start();

// Xóa toàn bộ dữ liệu session
session_unset();
session_destroy();

// Quay về trang đăng nhập
header("Location: /cuahangtaphoa/auth/login.php");
exit();
