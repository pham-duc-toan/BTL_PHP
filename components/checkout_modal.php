<!-- Modal chọn phương thức thanh toán và địa chỉ -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="process_checkout.php" method="post" id="checkoutForm">
        <div class="modal-header">
          <h5 class="modal-title">🧾 Thanh toán</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Chọn địa chỉ giao hàng</label>
            <div id="addressList" class="list-group"></div>
            <input type="hidden" name="address_id" id="selectedAddressId">

            <div class="form-text">
              Chưa có địa chỉ? <a href="#" data-bs-toggle="modal" data-bs-target="#addAddressModal">Thêm địa chỉ mới</a>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Phương thức thanh toán</label>
            <select name="payment_method" class="form-select" required>
              <option value="cod">Thanh toán khi nhận hàng</option>
              <option value="momo">Ví MoMo</option>
              <option value="bank">Chuyển khoản ngân hàng</option>
            </select>
          </div>

          <input type="hidden" name="selected_ids" id="selectedCartItems">
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Xác nhận thanh toán</button>
        </div>
      </form>
    </div>
  </div>
</div>