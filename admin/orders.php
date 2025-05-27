<?php
// File: admin_orders.php
session_start();
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../helper/functions.php';
include_once __DIR__ . '/../layout/header.php';

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
            <form method="POST" action="orders/update_status.php" class="d-inline me-2">
              <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
              <select name="new_status"
                class="form-select form-select-sm d-inline w-auto auto-submit-status"
                data-order-id="<?= $row['id'] ?>">

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

            </form>

            <?php if ($row['order_status'] === 'chưa hoàn tiền'): ?>
              <button class="btn btn-sm btn-outline-info btn-refund-info" data-order-id="<?= $row['id'] ?>">Thông tin hoàn tiền</button>
            <?php endif; ?>
          </td>

        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <!-- modal bank info -->

  <div class="modal fade" id="refundInfoModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Thông tin hoàn tiền</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p><strong>Họ tên người nhận:</strong> <span id="refund_full_name"></span></p>
          <p><strong>Số tài khoản:</strong> <span id="refund_bank_number"></span></p>
          <p><strong>Số tiền hoàn:</strong> <span id="refund_total"></span> đ</p>
        </div>
      </div>
    </div>
  </div>
  <!-- end modal bank info -->
</div>
<?php include_once __DIR__ . '/../layout/footer.php'; ?>
<!-- call thong tin bank -->
<script>
  $(document).ready(function() {
    $('.btn-refund-info').on('click', function() {
      const orderId = $(this).data('order-id');

      $.ajax({
        url: '/cuahangtaphoa/api/get_refund_info.php',
        method: 'POST',
        data: {
          order_id: orderId
        },
        success: function(data) {
          if (data.status === 'success') {
            $('#refund_full_name').text(data.data.full_name);
            $('#refund_bank_number').text(data.data.bank_number);
            $('#refund_total').text(Number(data.data.total_amount).toLocaleString());
            $('#refundInfoModal').modal('show');
          } else {
            alert(data.message);
          }
        },
        error: function() {
          alert("Không thể lấy thông tin hoàn tiền.");
        }
      });
    });
  });
</script>

<!-- doi trang thai -->
<script>
  $(document).ready(function() {
    $('.auto-submit-status').on('change', function() {
      const orderId = $(this).data('order-id');
      const newStatus = $(this).val();

      $.post('/cuahangtaphoa/orders/update_status.php', {
        order_id: orderId,
        new_status: newStatus
      }, function(res) {
        if (res.status === 'success') {
          location.reload(); // reload để thấy cập nhật
        } else {
          alert(res.message);
        }
      }, 'json');
    });
  });
</script>