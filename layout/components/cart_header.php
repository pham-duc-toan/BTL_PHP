<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../../helper/db.php';

// Hàm đếm tổng số lượng sản phẩm trong giỏ hàng của user
function getCartItemCount($userId, $conn)
{
  $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
  $stmt->bind_param("s", $userId);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  return $result['total'] ?? 0;
}

$cartCount = 0;
$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? null;

if ($user && $role !== 'admin') {
  $cartCount = getCartItemCount($user['id'], $conn);
}
?>

<li class="nav-item">
  <a class="nav-link" href="/cuahangtaphoa/cart/cart.php">
    🛒 Giỏ hàng
    <?php if ($cartCount > 0): ?>
      <span class="badge bg-danger"><?= ($cartCount > 99) ? '99+' : $cartCount ?></span>
    <?php endif; ?>
  </a>
</li>