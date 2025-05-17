<?php
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../layout/header.php';

if (!isset($_SESSION['user'])) {
  header("Location: /cuahangtaphoa/auth/login.php");
  exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("SELECT ci.id as cart_id, ci.quantity, ci.size, 
                               p.name, p.price, p.discount_percent, p.image
                        FROM cart_items ci
                        JOIN products p ON ci.product_id = p.id
                        WHERE ci.user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!-- thông báo lỗi -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
  <div id="toastError" class="toast text-bg-danger" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastErrorMessage">Đây là lỗi</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<h2 class="mb-4">🛒 Giỏ hàng của bạn</h2>

<?php if ($result->num_rows > 0): ?>
  <form action="checkout.php" method="post" id="cartForm">
    <table class="table table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th><input type="checkbox" id="checkAll"></th>
          <th>Ảnh</th>
          <th>Sản phẩm</th>
          <th>Size</th>
          <th>Số lượng</th>
          <th>Giá</th>
          <th>Thành tiền</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()):
          $final_price = $row['price'] * (100 - $row['discount_percent']) / 100;
        ?>
          <tr data-price="<?= $final_price ?>" data-cart-id="<?= $row['cart_id'] ?>">
            <td>
              <input type="checkbox" name="selected_ids[]" value="<?= $row['cart_id'] ?>" class="item-check">
            </td>
            <td style="width:100px">
              <?php if ($row['image']): ?>
                <img src="<?= $row['image'] ?>" alt="img" class="img-fluid rounded">
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= $row['size'] ?></td>
            <td style="width:100px">
              <input type="number" value="<?= $row['quantity'] ?>" min="1"
                class="form-control quantity-input" data-id="<?= $row['cart_id'] ?>">
            </td>
            <td><?= number_format($final_price, 0, ',', '.') ?>đ</td>
            <td class="item-subtotal"><?= number_format($final_price * $row['quantity'], 0, ',', '.') ?>đ</td>
            <td>
              <button type="button" class="btn btn-danger btn-confirm-delete"
                data-id="<?= $row['cart_id'] ?>">
                Xoá
              </button>


            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="fw-bold fs-5">
        🧾 Tổng cộng: <span id="totalAmount">0đ</span>
      </div>
      <button class="btn btn-success" type="button" id="btnCheckout">Thanh toán sản phẩm đã chọn</button>
    </div>
  </form>

<?php else: ?>
  <div class="alert alert-info">Giỏ hàng của bạn đang trống.</div>
<?php endif; ?>
<!-- script cho chọn sản phẩm thanh toán -->
<script>
  function formatCurrency(number) {
    return number.toLocaleString('vi-VN') + 'đ';
  }

  function updateTotal() {
    let total = 0;
    document.querySelectorAll(".item-check:checked").forEach(cb => {
      const row = cb.closest("tr");
      const price = parseFloat(row.dataset.price);
      const quantity = parseInt(row.querySelector(".quantity-input").value);
      const subtotal = price * quantity;

      row.querySelector(".item-subtotal").textContent = formatCurrency(subtotal);
      total += subtotal;
    });
    document.getElementById("totalAmount").textContent = formatCurrency(total);
  }

  document.querySelectorAll(".item-check").forEach(cb =>
    cb.addEventListener("change", updateTotal)
  );

  document.querySelectorAll(".quantity-input").forEach(input => {
    input.addEventListener("input", function() {
      const quantity = this.value;
      const id = this.dataset.id;

      fetch('update_quantity.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${id}&quantity=${quantity}`
      });

      updateTotal();
    });
  });

  document.querySelectorAll(".btn-remove").forEach(btn => {
    btn.addEventListener("click", function() {
      const id = this.dataset.id;
      const row = this.closest("tr");

      fetch('remove_from_cart.php?id=' + id)
        .then(() => row.remove())
        .then(updateTotal);
    });

  });

  document.getElementById("checkAll").addEventListener("change", function() {
    const checkboxes = document.querySelectorAll(".item-check");
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateTotal();
  });

  updateTotal();
</script>

<?php include_once __DIR__ . '/../components/checkout_modal.php'; ?>
<?php include_once __DIR__ . '/../components/add_address_modal.php'; ?>
<?php include_once __DIR__ . '/../components/confirm_modal.php'; ?>

<!-- script modal địa chỉ -->
<script>
  const addressSelect = document.getElementById("addressSelect");
  const btnDeleteAddress = document.getElementById("btnDeleteAddress");

  // Load danh sách địa chỉ
  function loadAddresses(selected = null) {
    fetch('/cuahangtaphoa/api/address_api.php')
      .then(res => res.json())
      .then(data => {
        addressSelect.innerHTML = "";
        if (data.length === 0) {
          let opt = new Option("Chưa có địa chỉ", "", false, false);
          opt.disabled = true;
          opt.selected = true;
          addressSelect.append(opt);
        } else {
          data.forEach(addr => {
            const opt = new Option(
              `${addr.full_name} - ${addr.phone} (${addr.address})`,
              addr.id,
              false,
              addr.id === selected
            );
            addressSelect.append(opt);
          });
        }
      });
  }

  // Mở modal thanh toán
  document.getElementById('btnCheckout').addEventListener('click', () => {
    const checked = [...document.querySelectorAll(".item-check:checked")].map(cb => cb.value);
    if (checked.length === 0) {
      document.getElementById("toastErrorMessage").textContent = "Vui lòng chọn sản phẩm trong giỏ hàng!";
      new bootstrap.Toast(document.getElementById("toastError")).show();
      return;
    }


    document.getElementById("selectedCartItems").value = checked.join(',');
    loadAddresses();
    new bootstrap.Modal(document.getElementById("checkoutModal")).show();
  });

  // Gửi form thêm địa chỉ
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
          alert("Đã thêm địa chỉ!");
          this.reset();
          bootstrap.Modal.getInstance(document.getElementById("addAddressModal")).hide();
          loadAddresses(data.new_id); // Giữ modal checkout, reload địa chỉ
        } else {
          alert("Thêm thất bại: " + data.error);
        }
      });
  });

  // Xoá địa chỉ
  btnDeleteAddress.addEventListener("click", () => {
    const id = addressSelect.value;
    if (!id || !confirm("Bạn chắc chắn muốn xoá địa chỉ này?")) return;

    fetch('/cuahangtaphoa/api/delete_address.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${id}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("Đã xoá địa chỉ!");
          loadAddresses();
        } else {
          alert("Không thể xoá địa chỉ.");
        }
      });
  });
</script>
<!-- script cho button remove sản phẩm khỏi cart -->
<script>
  document.querySelectorAll(".btn-confirm-delete").forEach(btn => {
    btn.addEventListener("click", function() {
      const cartId = this.dataset.id;
      const form = document.getElementById("confirmForm");

      // Action luôn là remove_from_cart.php
      form.action = "remove_from_cart.php";

      // Gán cart ID vào hidden input
      document.getElementById("confirmDeleteId").value = cartId;

      // Hiện modal
      const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
      modal.show();
    });
  });
</script>


<?php include_once __DIR__ . '/../layout/footer.php'; ?>