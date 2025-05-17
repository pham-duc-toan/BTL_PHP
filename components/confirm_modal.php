<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="" id="confirmForm">
        <input type="hidden" name="_method" value="DELETE">

        <input type="hidden" name="id" id="confirmDeleteId">

        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Xác nhận xoá</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          Bạn có chắc chắn muốn xoá sản phẩm này khỏi giỏ hàng không?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
          <button type="submit" class="btn btn-danger">Xoá</button>
        </div>
      </form>
    </div>
  </div>
</div>