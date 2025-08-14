<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isAdmin()) header("Location: ../auth/login.php");

$categories = $conn->query("SELECT * FROM categories");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc = $_POST['description'];
    $category = $_POST['category_id'];

    $image = '';

    // Handle offline image upload
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "../uploads/product-images/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }
    // Fallback to online image URL
    else if (!empty($_POST['image_url'])) {
        $image = $_POST['image_url'];
    }

    $stmt = $conn->prepare("INSERT INTO products (name, price, stock, description, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdisis", $name, $price, $stock, $desc, $category, $image);
    $stmt->execute();
    header("Location: products.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Product - Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- ✅ Bootstrap CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ✅ Font Awesome CDN -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

  <!-- ✅ Ruang Admin CSS (via CDN) -->
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">

  <link rel="icon" href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/img/logo/logo.png" />
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
          <div class="card shadow">
            <div class="card-header">
              <h4 class="mb-0">Add New Product</h4>
            </div>
            <div class="card-body">
              <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                  <label>Product Name</label>
                  <input type="text" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Price (₹)</label>
                  <input type="number" step="0.01" name="price" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Stock Quantity</label>
                  <input type="number" name="stock" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Category</label>
                  <select name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php while ($c = $categories->fetch_assoc()): ?>
                      <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label>Upload Image (Offline)</label>
                  <input type="file" name="image" class="form-control-file">
                </div>

                <div class="form-group">
                  <label>Or Image URL (Online)</label>
                  <input type="url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                </div>

                <div class="form-group">
                  <label>Description</label>
                  <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Product</button>
              </form>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>

  <!-- JS Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>
</body>
</html>
