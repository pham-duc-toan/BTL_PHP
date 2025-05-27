<?php
session_start();
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../helper/functions.php';
include_once __DIR__ . '/../layout/header.php';
include_once __DIR__ . '/../components/session_toast.php';

if (!isset($_SESSION['user'])) {
  $_SESSION['error'] = "Vui lòng đăng nhập để xem đơn hàng.";
  header("Location: /cuahangtaphoa/auth/login.php");
  exit;
}

$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT o.*,  a.full_name, a.phone, a.address 
                        FROM orders o 
                        JOIN addresses a ON o.address_id = a.id 
                        WHERE o.user_id = ? 
                        ORDER BY o.order_date DESC");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-4">
  <h2>Đơn hàng của tôi</h2>
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>Mã đơn</th>
        <th>Ngày đặt</th>
        <th>Thông tin đặt hàng</th>
        <th>Tổng tiền</th>
        <th>Phương thức</th>
        <th>Trạng thái</th>
        <th width="200">Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows === 0): ?>
        <tr>
          <td colspan="7" class="text-center text-muted">Không có đơn hàng nào.</td>
        </tr>
      <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
            <td>
              Họ tên: <strong><?= $row['full_name'] ?></strong><br>
              Số điện thoại: <?= $row['phone'] ?><br>
              Địa chỉ: <span class="text-muted"><?= $row['address'] ?></span>
            </td>
            <td><?= number_format($row['total_amount'], 0, ',', '.') ?> đ</td>
            <td><?= $row['payment_method'] === 'cod' ? 'Thanh toán khi nhận' : 'Chuyển khoản' ?></td>
            <td><?= ucfirst($row['order_status']) ?></td>
            <td>
              <!-- Nút xem chi tiết -->
              <!-- Nút xem chi tiết -->
              <button class="btn btn-sm btn-info mb-1 view-details" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#orderDetailModal">
                Xem chi tiết
              </button>



              <?php if ($row['order_status'] === 'chưa thanh toán'): ?>
                <form method="POST" action="/cuahangtaphoa/momo_payment.php" class="d-inline">
                  <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-warning mb-1">Thanh toán ngay</button>
                </form>

              <?php elseif (in_array($row['order_status'], ['chuẩn bị lấy hàng', 'đang giao'])): ?>
                <?php if ($row['payment_method'] === 'bank_transfer'): ?>
                  <button type="button"
                    class="btn btn-sm btn-danger mb-1 btn-cancel-order"
                    data-order-id="<?= $row['id'] ?>">
                    Yêu cầu huỷ
                  </button>
                <?php endif; ?>

              <?php elseif ($row['order_status'] === 'yêu cầu huỷ'): ?>
                <span class="text-muted">Đã yêu cầu huỷ</span>
              <?php endif; ?>
            </td>
          </tr>


        <?php endwhile; ?>
      <?php endif; ?>
    </tbody>
  </table>
  <?php include __DIR__ . '/../components/order_detail_modal.php'; ?>
</div>


<!-- modal điền bank-->

<!-- Modal nhập thông tin hoàn tiền -->
<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="cancelForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Yêu cầu huỷ & hoàn tiền</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="order_id" id="cancel_order_id">
          <div class="mb-3">
            <label for="full_name">Họ tên người nhận</label>
            <input type="text" name="full_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="bank_number">Số tài khoản MOMO</label>
            <input type="text" name="bank_number" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Xác nhận huỷ</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- modal điền bank-->

<?php include_once __DIR__ . '/../layout/footer.php'; ?>

<!-- script chi tiet order -->
<script>
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
            <table class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th>Hình ảnh</th>
                  <th>Tên sản phẩm</th>
                  <th>Size</th>
                  <th>Số lượng</th>
                  <th>Giá</th>
                  <th>Thành tiền</th>
                </tr>
              </thead>
              <tbody>`;


        response.forEach(item => {
          html += `
            <tr>
              <td class="text-center">
              <img src="${item.image}" alt="${item.name}" width="60" class="img-thumbnail d-block mx-auto">
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
<!-- script bank -->
<script>
  // Mở modal và gán order_id
  $('.btn-cancel-order').on('click', function() {
    const orderId = $(this).data('order-id');
    $('#cancel_order_id').val(orderId);
    $('#cancelModal').modal('show');
  });

  // Gửi AJAX khi submit form modal
  $('#cancelForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
      url: '/cuahangtaphoa/orders/cancel_request.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(res) {
        if (res.status === 'success') {
          window.location.reload(); // Toast sẽ hiển thị nhờ session
        } else {
          alert(res.message);
        }
      },
      error: function() {
        alert("Có lỗi xảy ra, vui lòng thử lại!");
      }
    });
  });
</script>