<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isUser()) header("Location: ../auth/login.php");

$userId = $_SESSION['user']['id'];

// Move to cart
if (isset($_GET['move'])) {
    $pid = $_GET['move'];

    // Check if already in cart
    $check = $conn->query("SELECT * FROM cart WHERE user_id=$userId AND product_id=$pid");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($userId, $pid, 1)");
    }

    // Remove from wishlist
    $conn->query("DELETE FROM wishlist WHERE user_id=$userId AND product_id=$pid");

    header("Location: wishlist.php");
    exit();
}

// Remove from wishlist
if (isset($_GET['remove'])) {
    $pid = $_GET['remove'];
    $conn->query("DELETE FROM wishlist WHERE user_id=$userId AND product_id=$pid");
    header("Location: wishlist.php");
    exit();
}

// Get wishlist items
$query = "SELECT w.*, p.name, p.price, p.image 
          FROM wishlist w 
          JOIN products p ON w.product_id = p.id 
          WHERE w.user_id = $userId";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Wishlist</title>

  <!-- ✅ Bootstrap 4.6 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- ✅ Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <!-- ✅ RuangAdmin -->
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">

  <style>
    .card-img-top {
        height: 180px;
        object-fit: cover;
    }
  </style>
</head>
<body id="page-top">

<div id="wrapper">
  <?php include('partials/sidebar.php'); ?>

  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include('partials/topbar.php'); ?>

      <div class="container-fluid mt-4">
        <h2 class="h4 mb-4"><i class="fas fa-heart text-danger"></i> My Wishlist</h2>

        <?php if ($result->num_rows > 0): ?>
          <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
              <div class="col-md-4 mb-4">
                <div class="card h-100 shadow">
                  <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                    <p class="card-text text-success font-weight-bold">₹<?= number_format($row['price'], 2) ?></p>
                    <div class="mt-auto">
                      <a href="?move=<?= $row['product_id'] ?>" class="btn btn-primary bg-gradient-primary">
                        <i class="fas fa-cart-plus"></i> Move to Cart
                      </a>
                      <a href="?remove=<?= $row['product_id'] ?>" class="btn btn-danger btn-sm"
                         onclick="return confirm('Remove from wishlist?')">
                        <i class="fas fa-trash"></i> Remove
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Your wishlist is empty.
          </div>
        <?php endif; ?>

        <a href="dashboard.php" class="btn btn-secondary mt-3">
          <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Scroll to Top -->
<a class="scroll-to-top rounded" href="#page-top">
  <i class="fas fa-angle-up"></i>
</a>

<!-- ✅ Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>
</body>
</html>
