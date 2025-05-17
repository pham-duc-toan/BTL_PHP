<?php
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../layout/header.php';
include_once __DIR__ . '/../helper/functions.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = generateId();
  $name = $_POST['name'];
  $email = $_POST['email'];
  $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role = "user"; // luôn là user

  // Kiểm tra email đã tồn tại chưa
  $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    echo "<div class='alert alert-warning'>Email này đã được sử dụng!</div>";
  } else {
    $stmt = $conn->prepare("INSERT INTO users (id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $id, $name, $email, $pass, $role);

    if ($stmt->execute()) {
      echo "<div class='alert alert-success'>Đăng ký thành công! <a href='/cuahangtaphoa/auth/login.php'>Đăng nhập</a></div>";
    } else {
      echo "<div class='alert alert-danger'>Lỗi: {$stmt->error}</div>";
    }
  }
}

?>

<h2 class="mb-4">Đăng ký tài khoản</h2>
<form method="post" class="card p-4 shadow-sm">
  <div class="mb-3">
    <label class="form-label">Họ tên</label>
    <input name="name" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input name="email" type="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Mật khẩu</label>
    <input name="password" type="password" class="form-control" required>
  </div>
  <button class="btn btn-primary">Đăng ký</button>
</form>

<?php include_once __DIR__ . '/../layout/footer.php';  ?>