<?php
include_once __DIR__ . '/../helper/db.php';
include_once __DIR__ . '/../layout/header.php';
include_once __DIR__ . '/../helper/functions.php';

if (session_status() == PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user'])) {
  header("Location: /cuahangtaphoa/index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?") or die("Lỗi truy vấn: " . $conn->error);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($pass, $user['password'])) {
            // ✅ Gán thông tin user vào session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['name'],  // hoặc fullname nếu có
                'role' => $user['role']
            ];

            header("Location: /cuahangtaphoa/index.php");
            exit();
        } else {
            $error = "Sai mật khẩu!";
        }
    } else {
        $error = "Email không tồn tại!";
    }
}
?>

<h2 class="mb-4">Đăng nhập</h2>
<?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="post" class="card p-4 shadow-sm">
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input name="email" type="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Mật khẩu</label>
    <input name="password" type="password" class="form-control" required>
  </div>
  <button class="btn btn-success">Đăng nhập</button>
</form>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
