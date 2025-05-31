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

$filter = $_GET['filter'] ?? 'all';
$sort_by = $_GET['sort_by'] ?? 'order_date';
$sort_dir = strtoupper($_GET['sort_dir'] ?? 'DESC');

$allowed_sort_by = ['order_date', 'total_amount', 'full_name'];
$allowed_sort_dir = ['ASC', 'DESC'];

$search_from = $_GET['search_from'] ?? '';
$search_to = $_GET['search_to'] ?? '';

if (!in_array($sort_by, $allowed_sort_by)) $sort_by = 'order_date';
if (!in_array($sort_dir, $allowed_sort_dir)) $sort_dir = 'DESC';

$statuses = [
  'all' => 'Tất cả',
  'chưa thanh toán' => 'Chưa thanh toán',
  'chuẩn bị lấy hàng' => 'Chuẩn bị lấy hàng',
  'đang giao' => 'Đang giao',
  'đã giao' => 'Đã giao',
  'chưa hoàn tiền' => 'Chưa hoàn tiền',
  'đã huỷ' => 'Đã huỷ',
  'đã hoàn tiền' => 'Đã hoàn tiền'
];

$user_id = $_SESSION['user']['id'];

// Chuẩn bị truy vấn
$sql = "SELECT o.*, a.full_name, a.phone, a.address
        FROM orders o 
        JOIN addresses a ON o.address_id = a.id
        WHERE o.user_id = ?";
$params = [$user_id];
$types = "s";

// Thêm điều kiện lọc trạng thái
if ($filter !== 'all') {
  $sql .= " AND o.order_status = ?";
  $params[] = $filter;
  $types .= "s";
}

// Lọc từ ngày
if (!empty($search_from)) {
  $sql .= " AND DATE(o.order_date) >= ?";
  $params[] = $search_from;
  $types .= "s";
}

// Lọc đến ngày
if (!empty($search_to)) {
  $sql .= " AND DATE(o.order_date) <= ?";
  $params[] = $search_to;
  $types .= "s";
}

$sql .= " ORDER BY $sort_by $sort_dir";

// Chuẩn bị và bind
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>


<div class="container py-4">
  <h2>Đơn hàng của tôi</h2>
  <ul class="nav nav-tabs mt-5 mb-3">
    <?php foreach ($statuses as $key => $label): ?>
      <li class="nav-item">
        <a class="nav-link <?= ($key === $filter) ? 'active' : '' ?>"
          href="?filter=<?= urlencode($key) ?>">
          <?= $label ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <form method="GET" class="row g-2 mb-3 justify-content-between align-items-end">

    <!-- Khối bên trái: Sort -->
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

    <!-- Khối bên phải: Tìm ngày -->
    <div class="col-md-6">
      <div class="row g-2 align-items-end">
        <div class="col">
          <label class="form-label">Từ ngày:</label>
          <input type="date" class="form-control" name="search_from"
            value="<?= htmlspecialchars($_GET['search_from'] ?? '') ?>">
        </div>
        <div class="col">
          <label class="form-label">Đến ngày:</label>
          <input type="date" class="form-control" name="search_to"
            value="<?= htmlspecialchars($_GET['search_to'] ?? '') ?>">
        </div>
      </div>
    </div>




    <div class="ms-2">
      <button class="btn btn-primary mt-4" type="submit">Áp dụng</button>
    </div>


    <!-- Hidden giữ filter cũ -->
    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
  </form>


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
                <button type="button"
                  class="btn btn-sm btn-secondary mb-1 btn-edit-address"
                  data-order-id="<?= $row['id'] ?>">
                  Thay đổi địa chỉ
                </button>
              <?php elseif (in_array($row['order_status'], ['chuẩn bị lấy hàng', 'đang giao'])): ?>
                <?php if ($row['payment_method'] === 'bank_transfer'): ?>
                  <button type="button"
                    class="btn btn-sm btn-danger mb-1 btn-cancel-order"
                    data-order-id="<?= $row['id'] ?>">
                    Yêu cầu huỷ
                  </button>
                <?php endif; ?>
                <?php if ($row['order_status'] === 'chuẩn bị lấy hàng'): ?>
                  <button type="button"
                    class="btn btn-sm btn-secondary mb-1 btn-edit-address"
                    data-order-id="<?= $row['id'] ?>">
                    Thay đổi địa chỉ
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
<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="cancelForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Yêu cầu huỷ & hoàn tiền</h5>

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
<!-- end modal điền bank-->
<!-- Modal thay đổi địa chỉ -->
<div class="modal fade" id="changeAddressModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="changeAddressForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🔄 Thay đổi địa chỉ giao hàng</h5>

      </div>
      <div class="modal-body">
        <input type="hidden" name="order_id" id="change_order_id">
        <div id="addressRadioList" class="list-group mb-3">
          <div class="text-muted">Đang tải danh sách địa chỉ...</div>
        </div>
        <div class="form-text">
          <a href="#" data-bs-toggle="modal" data-bs-target="#addAddressModal" onclick="setChangeAddressMode()">➕ Thêm địa chỉ mới</a>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Cập nhật</button>
      </div>
    </form>
  </div>
</div>

<?php include_once __DIR__ . '/../components/add_address_modal.php'; ?>
<?php include_once __DIR__ . '/../components/confirm_modal.php'; ?>
<?php include_once __DIR__ . '/../layout/footer.php'; ?>

<!-- script chi tiet order -->
<script>
  document.querySelectorAll('.view-details').forEach(btn => {
    btn.addEventListener('click', function() {
      const orderId = this.dataset.id;
      const contentEl = document.getElementById('order-detail-content');
      contentEl.innerHTML = '<p class="text-muted">Đang tải...</p>';

      fetch(`/cuahangtaphoa/api/order_items_api.php?order_id=${encodeURIComponent(orderId)}`)
        .then(res => res.json())
        .then(response => {
          if (response.length === 0) {
            contentEl.innerHTML = '<p class="text-muted">Không có sản phẩm nào.</p>';
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
          contentEl.innerHTML = html;
        })
        .catch(() => {
          contentEl.innerHTML = '<p class="text-danger">Không thể tải chi tiết đơn hàng.</p>';
        });
    });
  });
</script>

<!-- script bank -->
<script>
  document.querySelectorAll('.btn-cancel-order').forEach(btn => {
    btn.addEventListener('click', () => {
      const orderId = btn.dataset.orderId;
      document.getElementById('cancel_order_id').value = orderId;
      const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
      modal.show();
    });
  });


  document.getElementById('cancelForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/cuahangtaphoa/orders/cancel_request.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success') {
          window.location.reload(); // toast hiển thị do dùng session
        } else {
          alert(res.message);
        }
      })
      .catch(() => {
        alert("Có lỗi xảy ra, vui lòng thử lại!");
      });
  });
</script>

<!-- script change address  -->
<script>
  let currentAddressOrderId = null;

  function setChangeAddressMode() {
    currentAddressContext = 'change';
  }

  function loadAddressRadioList(selected = null) {
    const wrapper = document.getElementById("addressRadioList");
    wrapper.innerHTML = `<div class="text-muted">Đang tải...</div>`;

    fetch('/cuahangtaphoa/api/address_api.php')
      .then(res => res.json())
      .then(data => {
        wrapper.innerHTML = "";
        if (data.length === 0) {
          wrapper.innerHTML = `<div class="text-danger">Chưa có địa chỉ nào.</div>`;
          return;
        }

        data.forEach(addr => {
          const label = document.createElement("label");
          label.className = "list-group-item d-flex justify-content-between align-items-center";
          label.innerHTML = `
            <div class="form-check">
              <input class="form-check-input" type="radio" name="new_address_id" value="${addr.id}" ${selected == addr.id ? 'checked' : ''}>
              <label class="form-check-label">
                <strong>${addr.full_name}</strong> - ${addr.phone}<br>
                <span class="text-muted">${addr.address}</span>
              </label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-address" data-id="${addr.id}">xóa</button>
          `;
          wrapper.appendChild(label);

          label.querySelector('.btn-delete-address').addEventListener("click", function() {
            const form = document.getElementById("confirmForm");
            form.action = "/cuahangtaphoa/api/delete_address.php";
            document.getElementById("confirmDeleteId").value = addr.id;
            document.querySelector("#confirmModal .modal-body").textContent = "Bạn có chắc chắn muốn xoá địa chỉ này?";
            form.dataset.type = "change";

            const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
            modal.show();
          });
        });
      });
  }
  // Xử lý thêm địa chỉ từ modal
  document.getElementById("addAddressForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/cuahangtaphoa/api/add_address.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          this.reset();
          const addModalEl = document.getElementById("addAddressModal");
          const addModal = bootstrap.Modal.getInstance(addModalEl);
          addModal.hide();

          // Chờ modal đóng xong rồi mới mở lại modal chọn địa chỉ
          addModalEl.addEventListener('hidden.bs.modal', function handleHidden() {
            addModalEl.removeEventListener('hidden.bs.modal', handleHidden);

            // Chỉ cần mở lại changeAddressModal và load địa chỉ
            const modal = new bootstrap.Modal(document.getElementById("changeAddressModal"));
            modal.show();
            loadAddressRadioList(data.new_id);
          });

          // Hiện toast nếu có
          fetch('/cuahangtaphoa/components/session_toast.php')
            .then(res => res.text())
            .then(html => {
              document.body.insertAdjacentHTML('beforeend', html);
              const toast = new bootstrap.Toast(document.getElementById("toastSuccess"));
              toast.show();
            });

        } else {
          document.getElementById("toastErrorMessage").textContent = data.error;
          new bootstrap.Toast(document.getElementById("toastError")).show();
        }
      });
  });


  // Khi bấm "Thay đổi địa chỉ"
  document.querySelectorAll(".btn-edit-address").forEach(btn => {
    btn.addEventListener("click", () => {
      currentAddressOrderId = btn.dataset.orderId;
      document.getElementById("change_order_id").value = currentAddressOrderId;
      loadAddressRadioList();
      new bootstrap.Modal(document.getElementById("changeAddressModal")).show();
    });
  });

  // Gửi form thay đổi địa chỉ
  document.getElementById("changeAddressForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("/cuahangtaphoa/api/update_order_address.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert(data.message || "Không thể cập nhật.");
        }
      });
  });

  // Sau khi xoá địa chỉ xong từ confirmModal
  document.getElementById("confirmForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(this.action, {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        bootstrap.Modal.getInstance(document.getElementById("confirmModal")).hide();

        if (this.dataset.type === 'change') {
          loadAddressRadioList();
          const modal = new bootstrap.Modal(document.getElementById("changeAddressModal"));
          modal.show();
        }
      });
  });
</script>