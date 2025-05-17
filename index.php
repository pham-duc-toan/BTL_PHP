<?php
include "helper/db.php";
include "layout/header.php";
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}


// Phân trang
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Lấy tổng số sản phẩm
$total_result = $conn->query("SELECT COUNT(*) as total FROM products");
$total_row = $total_result->fetch_assoc();
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);

// Lấy danh sách sản phẩm phân trang
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT $start, $limit");
?>

<h2 class="mb-4">🛒 Danh sách sản phẩm</h2>


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
                <!-- Nút gọi modal -->
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sizeModal<?= $row['id'] ?>">🛒 Thêm vào giỏ</button>

                <!-- Modal chọn size và số lượng -->
                <div class="modal fade" id="sizeModal<?= $row['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <form action="add_to_cart.php" method="post" class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Chọn size và số lượng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <div class="mb-3">
                          <label for="size<?= $row['id'] ?>" class="form-label">Size</label>
                          <select name="size" id="size<?= $row['id'] ?>" class="form-select" required>
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


                <a href="checkout.php?product_id=<?= $row['id'] ?>&buy_now=1" class="btn btn-success">💰 Mua ngay</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <!-- PHÂN TRANG -->
  <nav aria-label="Page navigation">
    <ul class="pagination justify-content-center mt-4">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>

<?php else: ?>
  <div class="alert alert-warning text-center">
    Hiện chưa có sản phẩm nào được đăng bán. Vui lòng quay lại sau!
  </div>
<?php endif; ?>

<?php include "layout/footer.php"; ?>