<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../../helper/db.php';

// Hàm đếm tổng số lượng sản phẩm trong giỏ hàng
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

<!-- Giỏ hàng Shopee-style -->
<li id="header_cart" class="nav-item position-relative mx-2 me-4">
  <a class="nav-link position-relative" href="/cuahangtaphoa/cart/cart.php">
    <i class="bi bi-cart" style="font-size: 1.5rem;"></i>

    <?php if ($cartCount > 0): ?>
      <span class="position-absolute top-10 start-100 translate-middle-y badge rounded-pill bg-danger"
        style="font-size: 0.7rem; padding: 4px 6px; transform: translate(-40%, -40%);">
        <?= ($cartCount > 99) ? '99+' : $cartCount ?>
      </span>
    <?php endif; ?>
  </a>
</li>