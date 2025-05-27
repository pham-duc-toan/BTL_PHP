<?php
// File: admin_orders.php
session_start();
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../helper/functions.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  $_SESSION['error'] = "Bạn không có quyền truy cập.";
  header("Location: index.php");
  exit;
}

$result = $conn->query("SELECT o.*, u.name AS user_name, a.address FROM orders o JOIN users u ON o.user_id = u.id JOIN addresses a ON o.address_id = a.id ORDER BY o.order_date DESC");
?>
<div class="container py-4">
  <h2>Quản lý đơn hàng</h2>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Mã đơn</th>
        <th>Khách hàng</th>
        <th>Địa chỉ</th>
        <th>Ngày đặt</th>
        <th>Thanh toán</th>
        <th>Trạng thái</th>
        <th>Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= $row['user_name'] ?></td>
          <td><?= $row['address'] ?></td>
          <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
          <td><?= $row['payment_method'] === 'cod' ? 'COD' : 'Chuyển khoản' ?></td>
          <td><?= $row['order_status'] ?></td>
          <td>
            <form method="POST" action="orders/update_status.php" class="d-inline">
              <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
              <select name="new_status" class="form-select form-select-sm d-inline w-auto">
                <?php
                $options = [];
                if ($row['order_status'] === 'chuẩn bị lấy hàng') $options = ['đang giao', 'đã huỷ'];
                elseif ($row['order_status'] === 'đang giao') $options = ['đã giao', 'đã huỷ'];
                elseif ($row['order_status'] === 'yêu cầu huỷ') $options = ['đã huỷ'];
                elseif ($row['order_status'] === 'chưa thanh toán') $options = ['chuẩn bị lấy hàng'];
                elseif ($row['order_status'] === 'chưa hoàn tiền') $options = ['đã hoàn tiền'];
                foreach ($options as $op): ?>
                  <option value="<?= $op ?>"><?= ucfirst($op) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-sm btn-primary">Cập nhật</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php include_once __DIR__ . '/layouts/footer.php'; ?>