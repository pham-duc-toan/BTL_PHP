<?php
include "helper/db.php";
include "layout/header.php";
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Lấy từ khóa tìm kiếm nếu có
$keyword = $_GET['q'] ?? '';
$keyword_sql = '%' . $conn->real_escape_string($keyword) . '%';

// Phân trang
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Lấy tổng số sản phẩm (theo từ khóa nếu có)
if (!empty($keyword)) {
  $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE name LIKE ?");
  $stmt->bind_param("s", $keyword_sql);
  $stmt->execute();
  $total_row = $stmt->get_result()->fetch_assoc();
} else {
  $total_result = $conn->query("SELECT COUNT(*) as total FROM products");
  $total_row = $total_result->fetch_assoc();
}
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);

// Lấy danh sách sản phẩm theo trang và từ khóa nếu có
if (!empty($keyword)) {
  $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY created_at DESC LIMIT ?, ?");
  $stmt->bind_param("sii", $keyword_sql, $start, $limit);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT $start, $limit");
}
?>

<div class="container mt-4">
  <h2 class="mb-4">🛒 Danh sách sản phẩm</h2>

  <?php if (!empty($keyword)): ?>
    <div class="alert alert-info">
      🔍 Đang tìm với từ khóa: <strong><?= htmlspecialchars($keyword) ?></strong>
    </div>
  <?php endif; ?>

  <?php if ($result->num_rows > 0): ?>
    <div class="row">
      <?php while ($row = $result->fetch_assoc()):
        $price = $row['price'];
        $discount = $row['discount_percent'];
        $final_price = $price * (100 - $discount) / 100;
      ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <?php if ($row['image']): ?>
              <div class="product-image-container">
                <img src="<?= $row['image'] ?>" class="product-image">
              </div>
            <?php endif; ?>
            <div class="card-body d-flex flex-column justify-content-between">
              <div>
                <h5 class="card-title"><?= $row['name'] ?></h5>
                <p class="card-text"><?= $row['description'] ?></p>
                <p>
                  <?php if ($discount > 0): ?>
                    <span class="text-muted text-decoration-line-through"><?= number_format($price) ?>đ</span>
                    <span class="text-danger fw-bold"><?= number_format($final_price) ?>đ</span>
                  <?php else: ?>
                    <span class="fw-bold"><?= number_format($price) ?>đ</span>
                  <?php endif; ?>
                </p>
              </div>
              <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'): ?>
                <div class="d-grid gap-2">
                  <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sizeModal<?= $row['id'] ?>">🛒 Thêm vào giỏ</button>

                  <!-- Modal chọn size -->
                  <div class="modal fade" id="sizeModal<?= $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                      <form action="/cuahangtaphoa/api/add_to_cart.php" method="post" class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Chọn size và số lượng</h5>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                          <div class="mb-3">
                            <label class="form-label">Size</label>
                            <select name="size" class="form-select" required>
                              <option value="M">M</option>
                              <option value="L">L</option>
                              <option value="XL">XL</option>
                            </select>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Số lượng</label>
                            <input type="number" name="quantity" value="1" min="1" class="form-control" required>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-primary">Thêm vào giỏ</button>
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        </div>
                      </form>
                    </div>
                  </div>

                  <button class="btn btn-success btn-buy-now"
                    data-product-id="<?= $row['id'] ?>"
                    data-product-name="<?= htmlspecialchars($row['name']) ?>"
                    data-price="<?= $final_price ?>">
                    <i class="bi bi-cash-coin me-1"></i> Mua ngay
                  </button>

                  <?php include __DIR__ . '/components/quick_checkout_modal.php'; ?>
                  <?php include_once __DIR__ . '/components/add_address_modal.php'; ?>
                  <?php include_once __DIR__ . '/components/confirm_modal.php'; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- PHÂN TRANG -->
    <?php
    $query_string = !empty($keyword) ? '&q=' . urlencode($keyword) : '';
    ?>
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center mt-4">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i . $query_string ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>

  <?php else: ?>
    <div class="alert alert-warning text-center">
      Không tìm thấy sản phẩm nào khớp với từ khóa "<strong><?= htmlspecialchars($keyword) ?></strong>". Vui lòng thử lại.
    </div>
  <?php endif; ?>
</div>

<?php include "layout/footer.php"; ?>

<!-- script modal quick checkout  -->
<script>
  let currentAddressContext = "checkout";

  function setAddAddressMode(mode) {
    currentAddressContext = mode;
  }

  document.addEventListener("DOMContentLoaded", function() {
    // Khi bấm nút Mua ngay
    document.querySelectorAll('.btn-buy-now').forEach(button => {
      button.addEventListener('click', function() {
        const id = this.dataset.productId;
        const name = this.dataset.productName;
        const price = this.dataset.price;

        document.getElementById('quickProductId').value = id;
        document.getElementById('quickProductName').value = name;
        document.getElementById('quickProductPrice').value = price;

        setAddAddressMode('quick');
        loadQuickAddresses();

        const modal = new bootstrap.Modal(document.getElementById("quickCheckoutModal"));
        modal.show();
      });
    });

    // Gửi form mua ngay
    const quickForm = document.getElementById('quickCheckoutForm');
    if (quickForm) {
      quickForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(quickForm);

        fetch('/cuahangtaphoa/api/quick_checkout.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              window.location.href = '/cuahangtaphoa/orders/my_orders.php';
            } else {
              alert(data.error || "Đã xảy ra lỗi, vui lòng thử lại.");
            }
          })
          .catch((err) => {
            console.error("Lỗi fetch:", err);
            alert("Không thể kết nối máy chủ.");
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
            bootstrap.Modal.getInstance(document.getElementById("addAddressModal")).hide();

            // Mở lại modal quickCheckout
            const quickModal = new bootstrap.Modal(document.getElementById("quickCheckoutModal"));
            quickModal.show();

            loadQuickAddresses(data.new_id); // Tự động chọn địa chỉ vừa thêm

            // Hiện toast (nếu có)
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

  });
  //xoa dia chi
  document.getElementById("confirmForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = e.target;
    console.log("action:", form.action);

    const type = form.dataset.type;
    const formData = new FormData(form);

    fetch(form.action, {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert(data.message || "Xoá thất bại!");
          return;
        }

        // ✅ Đóng modal xác nhận
        bootstrap.Modal.getInstance(document.getElementById("confirmModal")).hide();

        if (type === "quick") {
          const quickModal = new bootstrap.Modal(document.getElementById("quickCheckoutModal"));
          quickModal.show();
          loadQuickAddresses();
        } else {
          const checkoutModal = new bootstrap.Modal(document.getElementById("checkoutModal"));
          checkoutModal.show();
          loadAddresses();
        }


      })
      .catch(err => {
        console.error("Lỗi xoá địa chỉ:", err);
        alert("Lỗi trong quá trình xoá. Vui lòng thử lại.");
      });
  });

  // Tải địa chỉ cho modal Mua ngay
  function loadQuickAddresses(selected = null) {
    const addressList = document.getElementById("quickAddressList");


    fetch('/cuahangtaphoa/api/address_api.php')
      .then(res => res.json())
      .then(data => {
        addressList.innerHTML = "";

        if (data.length === 0) {
          addressList.innerHTML = `
          <div class="text-danger">Chưa có địa chỉ nào. Hãy thêm mới trước khi mua.</div>`;
          return;
        }

        data.forEach(addr => {
          const wrapper = document.createElement("label");
          wrapper.className = "list-group-item";
          wrapper.innerHTML = `
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <input type="radio" name="address_option" value="${addr.id}" class="form-check-input me-1" required id="addr-${addr.id}">
              <label for="addr-${addr.id}">
                ${addr.full_name} - ${addr.phone} - ${addr.address}
              </label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-address" data-id="${addr.id}">Xoá</button>
          </div>
        `;
          addressList.appendChild(wrapper);

          // Gán sự kiện chọn địa chỉ
          const radio = wrapper.querySelector('input[name="address_option"]');
          radio.addEventListener("change", function() {
            document.getElementById("quickSelectedAddressId").value = this.value;
          });

          // Nếu là địa chỉ mới thêm thì auto chọn
          if (selected && addr.id === selected) {
            radio.checked = true;
            document.getElementById("quickSelectedAddressId").value = addr.id;
          }

          // Gán sự kiện xóa bằng confirm modal
          wrapper.querySelector(".btn-delete-address").addEventListener("click", function() {
            const id = this.dataset.id;

            const form = document.getElementById("confirmForm");
            form.action = "/cuahangtaphoa/api/delete_address.php";
            document.getElementById("confirmDeleteId").value = id;
            document.querySelector("#confirmModal .modal-body").textContent =
              "Bạn có chắc chắn muốn gỡ địa chỉ này khỏi tài khoản?";
            form.dataset.type = "quick"; // ❗ Đây là điểm QUAN TRỌNG phải có

            const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
            modal.show();
          });

        });
      });
  }
</script>