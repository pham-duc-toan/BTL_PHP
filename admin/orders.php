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

$status_filter = $_GET['filter'] ?? 'all';
$sort_by = $_GET['sort_by'] ?? 'order_date';
$sort_dir = strtoupper($_GET['sort_dir'] ?? 'DESC');
$search_from = $_GET['search_from'] ?? '';
$search_to = $_GET['search_to'] ?? '';

// Xác định danh sách cột sắp xếp hợp lệ
$allowed_sort = ['order_date', 'total_amount', 'full_name'];
$allowed_dir = ['ASC', 'DESC'];
if (!in_array($sort_by, $allowed_sort)) $sort_by = 'order_date';
if (!in_array($sort_dir, $allowed_dir)) $sort_dir = 'DESC';

// Xây dựng câu truy vấn
$sql = "SELECT o.*, u.name AS user_name, a.full_name, a.phone, a.address 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN addresses a ON o.address_id = a.id 
        WHERE 1=1";

// Lọc theo trạng thái
if ($status_filter !== 'all') {
  $sql .= " AND o.order_status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Lọc theo ngày
if (!empty($search_from)) {
  $sql .= " AND DATE(o.order_date) >= '" . $conn->real_escape_string($search_from) . "'";
}
if (!empty($search_to)) {
  $sql .= " AND DATE(o.order_date) <= '" . $conn->real_escape_string($search_to) . "'";
}

// Sắp xếp
$sql .= " ORDER BY $sort_by $sort_dir";

// Thực thi truy vấn
$result = $conn->query($sql);
?>
<div class="container py-4">
  <h2>Quản lý đơn hàng</h2>

  <!-- Tabs trạng thái -->
  <ul class="nav nav-tabs mb-3">
    <?php
    $tabs = ['all' => 'Tất cả', 'chưa thanh toán' => 'Chưa thanh toán', 'chuẩn bị lấy hàng' => 'Chuẩn bị lấy hàng', 'đang giao' => 'Đang giao', 'đã giao' => 'Đã giao', 'đã huỷ' => 'Đã huỷ', 'chưa hoàn tiền' => 'Chưa hoàn tiền', 'đã hoàn tiền' => 'Đã hoàn tiền'];
    foreach ($tabs as $key => $label): ?>
      <li class="nav-item">
        <a class="nav-link <?= $status_filter == $key ? 'active' : '' ?>"
          href="?filter=<?= $key ?>&sort_by=<?= $sort_by ?>&sort_dir=<?= $sort_dir ?>&search_date=<?= $search_date ?>">
          <?= $label ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <!-- Form sort + search -->
  <form method="GET" class="row g-2 mb-3 justify-content-between align-items-end">
    <input type="hidden" name="filter" value="<?= htmlspecialchars($status_filter) ?>">

    <div class="col-md-6 d-flex flex-wrap gap-2 align-items-end">
      <div>
        <label class="form-label">Sắp xếp theo:</label>
        <select class="form-select" name="sort_by">
          <option value="order_date" <?= $sort_by == 'order_date' ? 'selected' : '' ?>>Ngày đặt</option>
          <option value="total_amount" <?= $sort_by == 'total_amount' ? 'selected' : '' ?>>Tổng tiền</option>
          <option value="full_name" <?= $sort_by == 'full_name' ? 'selected' : '' ?>>Tên người nhận</option>
        </select>
      </div>

      <div>
        <label class="form-label">Thứ tự:</label>
        <select class="form-select" name="sort_dir">
          <option value="ASC" <?= $sort_dir == 'ASC' ? 'selected' : '' ?>>Tăng dần</option>
          <option value="DESC" <?= $sort_dir == 'DESC' ? 'selected' : '' ?>>Giảm dần</option>
        </select>
      </div>
    </div>

    <div class="col-md-6 d-flex justify-content-end align-items-end">
      <div class="col">
        <label class="form-label">Từ ngày:</label>
        <input type="date" class="form-control" name="search_from"
          value="<?= htmlspecialchars($search_from) ?>">
      </div>
      <div class="col">
        <label class="form-label">Đến ngày:</label>
        <input type="date" class="form-control" name="search_to"
          value="<?= htmlspecialchars($search_to) ?>">
      </div>
      <div class="ms-2">
        <button class="btn btn-primary mt-4" type="submit">Áp dụng</button>
      </div>
    </div>
  </form>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Mã đơn</th>
        <th>Tổng tiền</th>
        <th>Thông tin đặt hàng</th>
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
          <td><?= $row['total_amount'] ?></td>
          <td>
            Họ tên: <strong><?= $row['full_name'] ?></strong><br>
            Số điện thoại: <?= $row['phone'] ?><br>
            Địa chỉ: <span class="text-muted"><?= $row['address'] ?></span>
          </td>

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
  document.addEventListener('DOMContentLoaded', function() {
    // Gọi modal hoàn tiền
    document.querySelectorAll('.btn-refund-info').forEach(btn => {
      btn.addEventListener('click', () => {
        const orderId = btn.dataset.orderId;

        const formData = new FormData();
        formData.append('order_id', orderId);

        fetch('/cuahangtaphoa/api/get_refund_info.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              document.getElementById('refund_order_id').value = orderId;
              document.getElementById('refund_full_name').textContent = data.data.full_name;
              document.getElementById('refund_bank_number').textContent = data.data.bank_number;
              document.getElementById('refund_total').textContent = Number(data.data.total_amount).toLocaleString();

              new bootstrap.Modal(document.getElementById('refundInfoModal')).show();
            } else {
              alert(data.message);
            }
          })
          .catch(() => {
            location.reload();
          });
      });
    });

    // Bấm "Đã hoàn tiền"
    document.getElementById('confirmRefundBtn').addEventListener('click', () => {
      const orderId = document.getElementById('refund_order_id').value;

      const formData = new FormData();
      formData.append('order_id', orderId);
      formData.append('new_status', 'đã hoàn tiền');

      fetch('/cuahangtaphoa/orders/update_status.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(res => {
          location.reload();
        })
        .catch(() => {
          location.reload();
        });
    });

    // Đổi trạng thái đơn hàng bằng dropdown
    document.querySelectorAll('.auto-submit-status').forEach(select => {
      select.addEventListener('change', () => {
        const newStatus = select.value;
        if (!newStatus || newStatus === '--chọn--') return;

        const orderId = select.dataset.orderId;

        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('new_status', newStatus);

        fetch('/cuahangtaphoa/orders/update_status.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(res => {
            location.reload();
          })
          .catch(() => {
            location.reload();
          });
      });
    });
  });
</script>

<!-- script  cot chi tiet don hang  -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.view-details').forEach(button => {
      button.addEventListener('click', function() {
        const orderId = this.dataset.id;
        const detailContent = document.getElementById('order-detail-content');
        detailContent.innerHTML = '<p class="text-muted">Đang tải...</p>';

        fetch(`/cuahangtaphoa/api/order_items_api.php?order_id=${encodeURIComponent(orderId)}`)
          .then(res => res.json())
          .then(response => {
            if (response.length === 0) {
              detailContent.innerHTML = '<p class="text-muted">Không có sản phẩm nào.</p>';
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
            detailContent.innerHTML = html;
          })
          .catch(() => {
            detailContent.innerHTML = '<p class="text-danger">Không thể tải chi tiết đơn hàng.</p>';
          });
      });
    });
  });
</script>