<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isUser()) header("Location: ../auth/login.php");

$userId = $_SESSION['user']['id'];

// Update quantity
if (isset($_POST['update_qty'])) {
    $pid = $_POST['product_id'];
    $newQty = max(1, intval($_POST['quantity']));
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $newQty, $userId, $pid);
    $stmt->execute();
    header("Location: cart.php");
    exit();
}

// Remove from cart
if (isset($_GET['remove'])) {
    $pid = $_GET['remove'];
    $conn->query("DELETE FROM cart WHERE user_id=$userId AND product_id=$pid");
    header("Location: cart.php");
    exit();
}

// Get cart items
$query = "SELECT c.*, p.name, p.price, p.image 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = $userId";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Cart</title>

  <!-- ✅ Bootstrap & RuangAdmin -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">

  <style>
    .cart-card img {
      height: 150px;
      object-fit: cover;
    }
    .cart-card {
      transition: transform 0.2s ease;
    }
    .cart-card:hover {
      transform: scale(1.02);
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
        <h2 class="h4 mb-4"><i class="fas fa-shopping-cart"></i> My Cart</h2>

        <?php if ($result->num_rows > 0): ?>
          <div class="row">
            <?php 
            $grandTotal = 0;
            while ($row = $result->fetch_assoc()): 
              $subtotal = $row['price'] * $row['quantity'];
              $grandTotal += $subtotal;
            ?>
              <div class="col-md-4 mb-4">
                <div class="card cart-card h-100 shadow">
                  <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                    <p class="card-text text-success font-weight-bold">₹<?= number_format($row['price'], 2) ?> each</p>

                    <form method="POST" class="d-flex align-items-center mb-3">
                      <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                      <button type="submit" name="update_qty" class="btn btn-sm btn-outline-secondary"
                              onclick="this.form.quantity.value = Math.max(1, parseInt(this.form.quantity.value) - 1)">
                        <i class="fas fa-minus"></i>
                      </button>

                      <input type="text" name="quantity" value="<?= $row['quantity'] ?>" 
                             class="form-control form-control-sm mx-2 text-center" style="width: 50px;" readonly>

                      <button type="submit" name="update_qty" class="btn btn-sm btn-outline-secondary"
                              onclick="this.form.quantity.value = parseInt(this.form.quantity.value) + 1">
                        <i class="fas fa-plus"></i>
                      </button>
                    </form>

                    <p class="mb-1">Subtotal: <span class="text-primary font-weight-bold">₹<?= number_format($subtotal, 2) ?></span></p>

                    <a href="?remove=<?= $row['product_id'] ?>" 
                       onclick="return confirm('Remove this item?')" 
                       class="btn btn-sm btn-danger mt-auto">
                      <i class="fas fa-trash-alt"></i> Remove
                    </a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>

          <!-- Grand Total -->
          <div class="text-right mt-4">
            <h5>Grand Total: <span class="text-success">₹<?= number_format($grandTotal, 2) ?></span></h5>
            <a href="checkout.php" class="btn btn-primary bg-gradient-primary"><i class="fas fa-credit-card"></i> Proceed to Checkout</a>
            <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
          </div>
        <?php else: ?>
          <div class="alert alert-info">Your cart is empty.</div>
          <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
        <?php endif; ?>
      </div>
    </div>

    <footer class="sticky-footer bg-white">
      <div class="container my-auto">
        <div class="text-center my-auto">
          <span>© <?= date('Y') ?> Jewellery Shop</span>
        </div>
      </div>
    </footer>
  </div>
</div>

<!-- ✅ Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>
</body>
</html>
