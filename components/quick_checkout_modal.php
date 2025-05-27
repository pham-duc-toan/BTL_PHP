<!-- Modal Mua ngay -->
<div class="modal fade" id="quickCheckoutModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="quickCheckoutForm">
      <div class="modal-header">
        <h5 class="modal-title">💰 Mua ngay</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="product_id" id="quickProductId">
        <input type="hidden" name="price" id="quickProductPrice">
        <input type="hidden" name="address_id" id="quickSelectedAddressId">

        <div class="mb-2">
          <label class="form-label">Sản phẩm</label>
          <input type="text" id="quickProductName" class="form-control" readonly>
        </div>

        <div class="mb-2">
          <label class="form-label">Chọn size</label>
          <select name="size" class="form-select" required>
            <option value="M">M</option>
            <option value="L">L</option>
            <option value="XL">XL</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Số lượng</label>
          <input type="number" name="quantity" value="1" min="1" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Địa chỉ giao hàng</label>
          <div id="quickAddressList" class="list-group"></div>
        </div>

        <div class="mb-3">
          <label class="form-label">Phương thức thanh toán</label>
          <select name="payment_method" class="form-select" required>
            <option value="cod">Thanh toán khi nhận hàng</option>
            <option value="bank_transfer">Ví MoMo</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Xác nhận mua</button>
      </div>
    </form>
  </div>
</div>