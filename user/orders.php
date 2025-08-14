<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isUser()) header("Location: ../auth/login.php");

$userId = $_SESSION['user']['id'];
$orders = $conn->query("SELECT * FROM orders WHERE user_id=$userId ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>

  <!-- ✅ Bootstrap 4.6 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ✅ Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

  <!-- ✅ RuangAdmin CSS -->
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">

  <!-- ✅ DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
  <?php include('partials/sidebar.php'); ?>

  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include('partials/topbar.php'); ?>

      <div class="container-fluid mt-4">
        <h2 class="h4 mb-4"><i class="fas fa-box-open"></i> My Orders</h2>

        <?php if ($orders->num_rows > 0): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-striped" id="ordersTable">
              <thead class="thead-light">
                <tr>
                  <th>Order ID</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Order Date</th>
                  <th>Invoice</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($o = $orders->fetch_assoc()): ?>
                  <tr>
                    <td>#<?= $o['id'] ?></td>
                    <td>₹<?= number_format($o['total_amount'], 2) ?></td>
                    <td>
                      <span class="badge badge-<?= $o['status'] === 'Completed' ? 'success' : ($o['status'] === 'Pending' ? 'warning' : 'secondary') ?>">
                        <?= $o['status'] ?>
                      </span>
                    </td>
                    <td><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
                    <td>
                      <a href="invoice.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-sm btn-info">
                        <i class="fas fa-file-pdf"></i> PDF
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You haven't placed any orders yet.
          </div>
        <?php endif; ?>

        <a href="dashboard.php" class="btn btn-secondary mt-3">
          <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
      </div>
    </div>

    <footer class="sticky-footer bg-white">
      <div class="container my-auto text-center">
        <span>© <?= date('Y') ?> Jewellery Shop</span>
      </div>
    </footer>
  </div>
</div>

<!-- ✅ Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>

<!-- ✅ DataTables JS -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap4.min.js"></script>

<script>
  $(document).ready(function () {
    $('#ordersTable').DataTable({
      "order": [[ 0, "desc" ]],
      "columnDefs": [{ "orderable": false, "targets": 4 }]
    });
  });
</script>

</body>
</html>
