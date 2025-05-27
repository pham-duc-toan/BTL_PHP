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
        <th>Chi tiết</th> <!-- mới -->
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
            <button class="btn btn-sm btn-info view-details" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#orderDetailModal">
              Xem
            </button>
          </td>

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
  <!-- Modal chi tiết đơn hàng -->
  <div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Chi tiết đơn hàng</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="order-detail-content">
          <p class="text-muted">Đang tải...</p>
        </div>
      </div>
    </div>
  </div>
  <!-- end Modal chi tiết đơn hàng -->

</div>
<?php include_once __DIR__ . '/../layout/footer.php'; ?>


<!-- script cot hanh dong  -->
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
<!-- script  cot chi tiet don hang  -->
<script>
  // Chi tiết đơn hàng
  $('.view-details').on('click', function() {
    const orderId = $(this).data('id');
    $('#order-detail-content').html('<p class="text-muted">Đang tải...</p>');

    $.ajax({
      url: '/cuahangtaphoa/api/order_items_api.php',
      method: 'GET',
      data: {
        order_id: orderId
      },
      dataType: 'json',
      success: function(response) {
        if (response.length === 0) {
          $('#order-detail-content').html('<p class="text-muted">Không có sản phẩm nào.</p>');
          return;
        }

        let html = `
        <table class="table table-bordered table-sm align-middle">
          <thead class="table-light">
            <tr>
              <th>Ảnh</th>
              <th>Tên sản phẩm</th>
              <th>Size</th>
              <th>Số lượng</th>
              <th>Đơn giá</th>
              <th>Thành tiền</th>
            </tr>
          </thead><tbody>`;

        response.forEach(item => {
          html += `
          <tr>
            <td class="text-center">
              <img src="${item.image}" width="60" class="img-thumbnail">
            </td>
            <td>${item.name}</td>
            <td>${item.size}</td>
            <td>${item.quantity}</td>
            <td>${Number(item.price).toLocaleString()} đ</td>
            <td>${(item.price * item.quantity).toLocaleString()} đ</td>
          </tr>`;
        });

        html += '</tbody></table>';
        $('#order-detail-content').html(html);
      },
      error: function() {
        $('#order-detail-content').html('<p class="text-danger">Không thể tải chi tiết đơn hàng.</p>');
      }
    });
  });
</script>