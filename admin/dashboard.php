<?php
include '../config/db.php';

// Total Stock
$productResult = $conn->query("SELECT SUM(stock) as total_stock FROM products");
$totalStock = $productResult->fetch_assoc()['total_stock'] ?? 0;

// Total Sold
$soldResult = $conn->query("SELECT SUM(quantity) as total_sold FROM order_items");
$totalSold = $soldResult->fetch_assoc()['total_sold'] ?? 0;

// Monthly Revenue
$revenueResult = $conn->query("SELECT MONTH(created_at) as month, SUM(total_amount) as total FROM orders GROUP BY MONTH(created_at)");
$monthlyRevenue = array_fill(1, 12, 0);
while ($row = $revenueResult->fetch_assoc()) {
    $monthlyRevenue[(int)$row['month']] = (float)$row['total'];
}

// Total Users
$userResult = $conn->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $userResult->fetch_assoc()['total_users'] ?? 0;

// Unread Messages
$messageResult = $conn->query("SELECT COUNT(*) as unread FROM messages WHERE is_read = 0");
$unreadMessages = $messageResult->fetch_assoc()['unread'] ?? 0;

// User roles (Pie Chart)
$roleResult = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$roles = [];
$roleCounts = [];
while ($row = $roleResult->fetch_assoc()) {
    $roles[] = ucfirst($row['role']);
    $roleCounts[] = (int)$row['count'];
}

// Top 5 selling products (Bar Chart)
$barResult = $conn->query("SELECT p.name, SUM(oi.quantity) as qty 
  FROM order_items oi 
  JOIN products p ON oi.product_id = p.id 
  GROUP BY p.name 
  ORDER BY qty DESC 
  LIMIT 5");
$topProducts = [];
$topQuantities = [];
while ($row = $barResult->fetch_assoc()) {
    $topProducts[] = $row['name'];
    $topQuantities[] = (int)$row['qty'];
}

// Latest Messages


$messageQuery = $conn->query("
  SELECT messages.*, users.name, users.email
  FROM messages
  LEFT JOIN users ON messages.user_id = users.id
  ORDER BY messages.created_at DESC
  LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div id="wrapper">
  <?php include('partials/sidebar.php'); ?>
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include('partials/topbar.php'); ?>
      <div class="container mt-4">

       <div class="row mb-4">
  <!-- Stock Card -->
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Stock</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($totalStock ?? '0') ?></div>
            <div class="mt-2 mb-0 text-muted text-xs">
              <span class="text-info mr-2"><i class="fas fa-boxes"></i> Updated Today</span>
            </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-box fa-2x text-primary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sold Card -->
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">Items Sold</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($totalSold ?? '0') ?></div>
            <div class="mt-2 mb-0 text-muted text-xs">
              <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> Live Data</span>
            </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-shopping-cart fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Users Card -->
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Users</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($totalUsers ?? '0') ?></div>
            <div class="mt-2 mb-0 text-muted text-xs">
              <span class="text-primary mr-2"><i class="fas fa-users"></i> Registered</span>
            </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-user fa-2x text-info"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Unread Messages Card -->
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">Unread Messages</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($unreadMessages ?? '0') ?></div>
            <div class="mt-2 mb-0 text-muted text-xs">
              <span class="text-danger mr-2"><i class="fas fa-envelope"></i> Needs Reply</span>
            </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-comments fa-2x text-warning"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


        <!-- Charts & Messages -->
        <div class="row">
          <div class="col-md-8">
            <div class="card mb-4">
              <div class="card-header">Monthly Revenue</div>
              <div class="card-body">
                <canvas id="areaChart" data-chart='<?= json_encode(array_values($monthlyRevenue)) ?>'></canvas>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card mb-4">
              <div class="card-header">User Roles</div>
              <div class="card-body">
                <canvas id="pieChart" data-labels='<?= json_encode($roles) ?>' data-values='<?= json_encode($roleCounts) ?>' style="max-width: 300px;"></canvas>
              </div>
            </div>
            <div class="card">
              <div class="card-header">Top Products</div>
              <div class="card-body">
                <canvas id="barChart" data-labels='<?= json_encode($topProducts) ?>' data-values='<?= json_encode($topQuantities) ?>' style="max-width: 300px;"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Enhanced Messages Table -->
     <div class="col-xl-8 col-lg-7">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="fas fa-envelope"></i> Recent Messages</span>
      <a href="messages.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover table-sm">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Subject</th>
              <th>Status</th>
              <th>Received</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($messageQuery && $messageQuery->num_rows > 0): ?>
              <?php $i = 1; while ($msg = $messageQuery->fetch_assoc()): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($msg['name'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($msg['email'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($msg['subject'] ?? '') ?></td>
                  <td>
                    <?= $msg['is_read']
                      ? '<span class="badge badge-success">Read</span>'
                      : '<span class="badge badge-warning">Unread</span>' ?>
                  </td>
                  <td><?= date('d M Y, H:i', strtotime($msg['created_at'])) ?></td>
                  <td>
                    <a href="message-view.php?id=<?= $msg['id'] ?>" class="btn btn-sm btn-info">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="messages.php?delete=<?= $msg['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this message?')">
                      <i class="fas fa-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted">No recent messages found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const areaCtx = document.getElementById('areaChart');
  const areaData = JSON.parse(areaCtx.getAttribute('data-chart'));
  new Chart(areaCtx, {
    type: 'line',
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      datasets: [{
        label: "Revenue",
        data: areaData,
        backgroundColor: "rgba(78, 115, 223, 0.2)",
        borderColor: "rgba(78, 115, 223, 1)",
        borderWidth: 2,
        fill: true,
      }]
    }
  });

  const pieCtx = document.getElementById('pieChart');
  const pieLabels = JSON.parse(pieCtx.getAttribute('data-labels'));
  const pieValues = JSON.parse(pieCtx.getAttribute('data-values'));
  new Chart(pieCtx, {
    type: 'pie',
    data: {
      labels: pieLabels,
      datasets: [{
        data: pieValues,
        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
      }]
    }
  });

  const barCtx = document.getElementById('barChart');
  const barLabels = JSON.parse(barCtx.getAttribute('data-labels'));
  const barValues = JSON.parse(barCtx.getAttribute('data-values'));
  new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: barLabels,
      datasets: [{
        label: "Units Sold",
        data: barValues,
        backgroundColor: '#f6c23e',
        borderColor: '#e0a800',
        borderWidth: 1
      }]
    }
  });
});
</script>
</body>
</html>
