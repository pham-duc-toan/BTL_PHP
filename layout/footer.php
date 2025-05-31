  </div> <!-- end .main-content -->
  </div> <!-- end .main-layout -->



  <footer class="bg-success text-white mt-5">
    <div class="container py-4">
      <div class="row">
        <div class="col-md-4">
          <h5>GoShopOnline</h5>
          <p>Chuyên bán quần áo chất lượng với giá tốt nhất.</p>
        </div>
        <div class="col-md-4">
          <h5>Liên kết nhanh</h5>
          <ul class="list-unstyled">
            <li><a href="/cuahangtaphoa/index.php" class="text-white text-decoration-none">Trang chủ</a></li>
            <li><a href="#" class="text-white text-decoration-none">Sản phẩm</a></li>
            <li><a href="#" class="text-white text-decoration-none">Liên hệ</a></li>
          </ul>
        </div>
        <div class="col-md-4">
          <h5>Kết nối với chúng tôi</h5>
          <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
          <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
          <a href="#" class="text-white"><i class="bi bi-telephone"></i> 1900 6868</a>
        </div>
      </div>
    </div>
    <div class="text-center py-2 bg-dark">
      &copy; <?= date("Y") ?> GoShopOnline. All rights reserved.
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const toastEls = document.querySelectorAll('.toast');
      toastEls.forEach(el => {
        new bootstrap.Toast(el, {
          delay: 3000
        }).show();
      });
    });
  </script>
  </body>

  </html>