<!-- Modal thêm địa chỉ -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addAddressForm">
        <div class="modal-header">
          <h5 class="modal-title">📍 Thêm địa chỉ giao hàng</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Họ tên</label>
            <input name="full_name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Số điện thoại</label>
            <input name="phone" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Địa chỉ chi tiết</label>
            <textarea name="address" class="form-control" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Lưu địa chỉ</button>
        </div>
      </form>
    </div>
  </div>
</div>