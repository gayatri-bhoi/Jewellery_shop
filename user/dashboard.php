<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isUser()) header("Location: ../auth/login.php");

$userId = $_SESSION['user']['id'];

// Handle Add to Wishlist
if (isset($_GET['add_wish'])) {
    $pid = intval($_GET['add_wish']);
    $check = $conn->query("SELECT * FROM wishlist WHERE user_id=$userId AND product_id=$pid");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO wishlist (user_id, product_id) VALUES ($userId, $pid)");
    }
    header("Location: dashboard.php");
    exit();
}

// Handle AJAX Add to Cart (MERGED HERE)
if (isset($_POST['add_cart']) && isset($_POST['qty'])) {
    $pid = intval($_POST['add_cart']);
    $qty = max(1, intval($_POST['qty']));
    $check = $conn->query("SELECT * FROM cart WHERE user_id=$userId AND product_id=$pid");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE cart SET quantity = quantity + $qty WHERE user_id=$userId AND product_id=$pid");
    } else {
        $conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($userId, $pid, $qty)");
    }
    echo json_encode(["status" => "success"]);
    exit();
}

// Fetch products
$products = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- ✅ Bootstrap 4.6 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ✅ Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

  <!-- ✅ RuangAdmin CSS -->
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">

  <style>
    .product-img {
      height: 200px;
      object-fit: cover;
    }
    .product-card {
      transition: 0.3s ease;
    }
    .product-card:hover {
      transform: scale(1.02);
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body id="page-top">

<div id="wrapper">
  <!-- Sidebar -->
  <?php include('partials/sidebar.php'); ?>

  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

      <!-- Topbar -->
      <?php include('partials/topbar.php'); ?>

      <div class="container-fluid mt-4">
        <h3 class="mb-4"><i class="fas fa-gem text-primary"></i> All Jewellery Products</h3>

        <div class="row">
          <?php while ($p = $products->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
              <div class="card product-card h-100 shadow">
                <img src="<?= htmlspecialchars($p['image']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($p['name']) ?>">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                  <p class="text-muted">₹<?= number_format($p['price'], 2) ?></p>
                  <p>
                    Stock:
                    <?= $p['stock'] > 0 ? '<span class="badge badge-success">In Stock</span>' : '<span class="badge badge-danger">Out of Stock</span>' ?>
                  </p>

                  <div id="action-<?= $p['id'] ?>">
                    <?php if ($p['stock'] > 0): ?>
                      <button class="btn btn-sm btn-primary btn-block" onclick="showQty(<?= $p['id'] ?>)">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                      </button>
                    <?php endif; ?>
                    <a href="?add_wish=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger btn-block mt-2">
                      <i class="fas fa-heart"></i> Add to Wishlist
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- JS CDN -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>

<script>
  function showQty(pid) {
    $('#action-' + pid).html(`
      <form onsubmit="return false;" class="cart-form" data-product="${pid}">
        <div class="d-flex align-items-center mb-2">
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeQty(this, -1)">
            <i class="fas fa-minus"></i>
          </button>
          <input type="text" name="qty" value="1" class="form-control form-control-sm mx-2 text-center" style="width: 50px;">
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeQty(this, 1)">
            <i class="fas fa-plus"></i>
          </button>
        </div>
        <button type="button" class="btn btn-sm btn-success btn-block" onclick="addToCart(this)">
          <i class="fas fa-check"></i> Confirm Add
        </button>
      </form>
    `);
  }

  function changeQty(btn, delta) {
    const input = btn.closest('.cart-form').querySelector('input[name="qty"]');
    let qty = parseInt(input.value) || 1;
    qty += delta;
    input.value = Math.max(1, qty);
  }

  function addToCart(btn) {
    const form = btn.closest('.cart-form');
    const pid = form.dataset.product;
    const qty = form.querySelector('input[name="qty"]').value;

    $.post('dashboard.php', { add_cart: pid, qty: qty }, function (res) {
      const result = JSON.parse(res);
      if (result.status === 'success') {
        $('#action-' + pid).html(`
          <a href="cart.php" class="btn btn-sm btn-success btn-block">
            <i class="fas fa-shopping-cart"></i> View Cart
          </a>
          <a href="?add_wish=${pid}" class="btn btn-sm btn-outline-danger btn-block mt-2">
            <i class="fas fa-heart"></i> Add to Wishlist
          </a>
        `);
      }
    });
  }
</script>

</body>
</html>
