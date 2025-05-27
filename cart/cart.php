<?php
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../layout/header.php';
include_once __DIR__ . '/../components/session_toast.php';
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
        const addressList = document.getElementById("addressList");
        addressList.innerHTML = "";

        if (data.length === 0) {
          addressList.innerHTML = '<div class="text-muted">Chưa có địa chỉ nào.</div>';
          return;
        }

        data.forEach(addr => {
          const wrapper = document.createElement("div");
          wrapper.className = "list-group-item d-flex justify-content-between align-items-start";

          wrapper.innerHTML = `
          <div class="form-check w-100">
            <input class="form-check-input" type="radio" name="address_option" value="${addr.id}" id="addr-${addr.id}">
            <label class="form-check-label" for="addr-${addr.id}">
              <strong>${addr.full_name}</strong> - ${addr.phone}<br>
              <span class="text-muted">${addr.address}</span>
            </label>
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger ms-2 btn-delete-address" data-id="${addr.id}">Xoá</button>
        `;

          addressList.appendChild(wrapper);
        });

        // Gán lại sự kiện cho nút xoá từng địa chỉ
        document.querySelectorAll(".btn-delete-address").forEach(btn => {
          btn.addEventListener("click", function() {
            const id = this.dataset.id;

            const form = document.getElementById("confirmForm");
            form.action = "/cuahangtaphoa/api/delete_address.php";
            document.getElementById("confirmDeleteId").value = id;
            document.querySelector("#confirmModal .modal-body").textContent =
              "Bạn có chắc chắn muốn gỡ địa chỉ này khỏi tài khoản?";

            // Đánh dấu là xoá địa chỉ
            form.dataset.type = "address";

            const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
            modal.show();
          });
        });



        // Gán sự kiện khi chọn radio
        document.querySelectorAll('input[name="address_option"]').forEach(radio => {
          radio.addEventListener("change", function() {
            document.getElementById("selectedAddressId").value = this.value;
          });
        });
      });
  }


  // Mở modal thanh toán
  document.getElementById('btnCheckout').addEventListener('click', () => {
    const checked = [...document.querySelectorAll(".item-check:checked")].map(cb => cb.value);
    if (checked.length === 0) {
      fetch('/cuahangtaphoa/components/generate_toast.php?type=error&msg=' + encodeURIComponent("Vui lòng chọn sản phẩm trong giỏ hàng!"))
        .then(res => res.text())
        .then(html => {
          document.body.insertAdjacentHTML('beforeend', html);
          const toastEl = document.querySelector('.toast');
          if (toastEl) new bootstrap.Toast(toastEl).show();
        });
      return;
    }
    document.getElementById("selectedCartItems").value = checked.join(',');
    loadAddresses();
    new bootstrap.Modal(document.getElementById("checkoutModal")).show();
  });
  //reload modal sau khi xác nhận xóa địa chỉ
  document.getElementById("confirmForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = e.target;
    const type = form.dataset.type; // address hoặc cart
    const formData = new FormData(form);

    fetch(form.action, {
        method: "POST",
        body: formData
      })
      .then(res => res.text()) // nếu PHP trả về redirect thì vẫn chạy
      .then(() => {
        // Đóng modal
        bootstrap.Modal.getInstance(document.getElementById("confirmModal")).hide();

        if (type === "address") {
          const checkoutModal = new bootstrap.Modal(document.getElementById("checkoutModal"));
          checkoutModal.show();
          loadAddresses();

          // Gọi lại session toast nếu có
          fetch('/cuahangtaphoa/components/session_toast.php')
            .then(res => res.text())
            .then(html => {
              document.body.insertAdjacentHTML('beforeend', html);
              const toastEl = document.querySelector('.toast');
              if (toastEl) new bootstrap.Toast(toastEl).show();
            });
        } else {
          // Trường hợp xoá cart item → reload giỏ hoặc cập nhật lại DOM
          location.reload(); // hoặc updateTotal() và xoá row
        }


      });

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
          this.reset();
          bootstrap.Modal.getInstance(document.getElementById("addAddressModal")).hide();

          const checkoutModal = new bootstrap.Modal(document.getElementById("checkoutModal"));
          checkoutModal.show();

          loadAddresses(data.new_id);

          // Hiện toast từ session (reload session toast nếu có)
          fetch('/cuahangtaphoa/components/session_toast.php')
            .then(res => res.text())
            .then(html => {
              document.body.insertAdjacentHTML('beforeend', html);
              const toast = new bootstrap.Toast(document.getElementById("toastSuccess"));
              toast.show();
            });
        } else {
          // Gán lỗi lên toastError
          document.getElementById("toastErrorMessage").textContent = data.error;
          new bootstrap.Toast(document.getElementById("toastError")).show();
        }
      });
  });



  // Xoá địa chỉ (nút ở dưới select)
  btnDeleteAddress.addEventListener("click", () => {
    const id = addressSelect.value;
    if (!id) return;

    const form = document.getElementById("confirmForm");
    form.action = "/cuahangtaphoa/api/delete_address.php";
    document.getElementById("confirmDeleteId").value = id;
    document.querySelector("#confirmModal .modal-body").textContent =
      "Bạn có chắc chắn muốn gỡ địa chỉ này khỏi tài khoản?";
    form.dataset.type = "address";

    new bootstrap.Modal(document.getElementById("confirmModal")).show();
  });
</script>
<!-- script cho button remove sản phẩm khỏi cart -->
<script>
  document.querySelectorAll(".btn-confirm-delete").forEach(btn => {
    btn.addEventListener("click", function() {
      const cartId = this.dataset.id;
      const form = document.getElementById("confirmForm");

      form.action = "remove_from_cart.php";
      document.getElementById("confirmDeleteId").value = cartId;
      document.querySelector("#confirmModal .modal-body").textContent =
        "Bạn có chắc chắn muốn xoá sản phẩm này khỏi giỏ hàng không?";

      const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
      modal.show();
    });
  });
</script>


<?php include_once __DIR__ . '/../layout/footer.php'; ?>