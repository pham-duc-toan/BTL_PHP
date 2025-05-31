<?php
session_start();
include "helper/db.php";
include "layout/header.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  $_SESSION['error'] = "Bạn không có quyền truy cập.";
  header("Location: index.php");
  exit;
}

// Tổng số đơn
$totalOrders = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'];

// Tổng doanh thu (chỉ tính đơn đã giao)
$totalRevenue = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE order_status = 'đã giao'")->fetch_assoc()['total'] ?? 0;

// Đếm theo từng trạng thái
$allStatus = ['chưa thanh toán', 'chuẩn bị lấy hàng', 'đang giao', 'đã giao', 'đã huỷ', 'chưa hoàn tiền', 'đã hoàn tiền'];
$statusStats = [];
foreach ($allStatus as $status) {
  $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM orders WHERE order_status = ?");
  $stmt->bind_param("s", $status);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $statusStats[$status] = $row['total'];
}

// Doanh thu theo tháng
$monthlyRevenue = $conn->query("
    SELECT MONTH(order_date) as month, SUM(total_amount) as total
    FROM orders
    WHERE order_status = 'đã giao'
    GROUP BY MONTH(order_date)
    ORDER BY month ASC
")->fetch_all(MYSQLI_ASSOC);

// Dữ liệu biểu đồ
$statusLabels = array_keys($statusStats);
$statusCounts = array_values($statusStats);
?>

<div class="container py-4">
  <h2 class="mb-4">📊 Thống kê đơn hàng</h2>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card text-bg-success">
        <div class="card-body">
          <h5 class="card-title">Tổng số đơn hàng</h5>
          <p class="card-text fs-3"><?= $totalOrders ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card text-bg-primary">
        <div class="card-body">
          <h5 class="card-title">Tổng doanh thu</h5>
          <p class="card-text fs-3"><?= number_format($totalRevenue, 0, ',', '.') ?> đ</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Biểu đồ -->
  <div class="row mb-4">
    <!-- Biểu đồ tròn: loại đơn -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">🧁 Tỷ lệ đơn hàng theo trạng thái</div>
        <div class="card-body">
          <canvas id="orderStatusPie" style="min-height: 300px;"></canvas>
        </div>
      </div>
    </div>

    <!-- Biểu đồ cột: doanh thu theo tháng -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">📈 Doanh thu theo tháng</div>
        <div class="card-body">
          <canvas id="revenueChart" style="min-height: 300px;"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "layout/footer.php"; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Biểu đồ tròn - các loại đơn hàng
  const orderStatusPie = new Chart(document.getElementById('orderStatusPie'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($statusLabels) ?>,
      datasets: [{
        data: <?= json_encode($statusCounts) ?>,
        backgroundColor: [
          '#0d6efd', '#198754', '#ffc107', '#dc3545',
          '#6c757d', '#6610f2', '#fd7e14'
        ]
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const value = context.parsed;
              const percent = (value / total * 100).toFixed(1) + '%';
              return `${context.label}: ${value} đơn (${percent})`;
            }
          }
        }
      }
    }
  });

  // Biểu đồ cột - doanh thu theo tháng
  const revenueChart = new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_map(fn($m) => "Tháng " . $m['month'], $monthlyRevenue)) ?>,
      datasets: [{
        label: 'Doanh thu (VNĐ)',
        data: <?= json_encode(array_column($monthlyRevenue, 'total')) ?>,
        backgroundColor: '#198754'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: value => value.toLocaleString() + ' đ'
          }
        }
      }
    }
  });
</script>