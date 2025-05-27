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
            <?php
            $status = $row['order_status'];
            $orderId = $row['id'];

            if ($status === 'chưa hoàn tiền') {
              // Chỉ hiển thị nút "Hoàn tiền"
              echo '<button class="btn btn-sm btn-outline-info btn-refund-info" data-order-id="' . $orderId . '">Hoàn tiền</button>';
            } else {
              // Các trạng thái khác có thể có dropdown thay đổi trạng thái
              $options = [];

              if ($status === 'chuẩn bị lấy hàng') {
                $options = ['--chọn--', 'đang giao', 'đã huỷ'];
              } elseif ($status === 'đang giao') {
                $options = ['--chọn--', 'đã giao', 'đã huỷ'];
              }

              if (!empty($options)) {
            ?>
                <select class="form-select form-select-sm d-inline w-auto auto-submit-status mt-2"
                  data-order-id="<?= $orderId ?>">
                  <?php foreach ($options as $op): ?>
                    <option value="<?= $op ?>" <?= $op === '--chọn--' ? 'selected disabled' : '' ?>>
                      <?= ucfirst($op) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
            <?php
              }
            }
            ?>
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
          <input type="hidden" id="refund_order_id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="confirmRefundBtn">Đã hoàn tiền</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        </div>
      </div>
    </div>
  </div>

  <!-- end modal bank info -->
</div>
<?php include_once __DIR__ . '/../layout/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  $(document).ready(function() {
    // Gọi modal hoàn tiền
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
            $('#refund_order_id').val(orderId);
            $('#refund_full_name').text(data.data.full_name);
            $('#refund_bank_number').text(data.data.bank_number);
            $('#refund_total').text(Number(data.data.total_amount).toLocaleString());
            $('#refundInfoModal').modal('show');
          } else {
            alert(data.message);
          }
        },
        error: function() {
          location.reload();
        }
      });
    });

    // Bấm "Đã hoàn tiền"
    $('#confirmRefundBtn').on('click', function() {
      const orderId = $('#refund_order_id').val();

      $.post('/cuahangtaphoa/orders/update_status.php', {
        order_id: orderId,
        new_status: "đã hoàn tiền"
      }, function(res) {
        if (res.status === 'success') {
          location.reload();
        } else {
          location.reload();
        }
      }, 'json');
    });

    // Đổi trạng thái đơn hàng bằng dropdown
    $('.auto-submit-status').on('change', function() {
      const orderId = $(this).data('order-id');
      const newStatus = $(this).val();

      // Không gửi nếu vẫn chọn mặc định
      if (!newStatus || newStatus === '--chọn--') return;

      $.post('/cuahangtaphoa/orders/update_status.php', {
        order_id: orderId,
        new_status: newStatus
      }, function(res) {
        if (res.status === 'success') {
          location.reload();
        } else {

          location.reload();
        }
      }, 'json');
    });

  });
</script>