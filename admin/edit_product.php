<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isAdmin()) header("Location: ../auth/login.php");

session_start();

$id = (int)$_GET['id'];
$product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
$categories = $conn->query("SELECT * FROM categories");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $price    = $_POST['price'];
    $stock    = $_POST['stock'];
    $desc     = trim($_POST['description']);
    $category = $_POST['category_id'];
    $image    = $product['image']; // fallback

    $image_uploaded = false;

    // ✅ 1. Handle uploaded image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = basename($_FILES['image']['name']);
        $target = "../uploads/product-images/" . $image;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_uploaded = true;
        } else {
            $_SESSION['error'] = "Failed to upload image.";
            header("Location: products.php?id=$id");
            exit;
        }
    }

    // ✅ 2. Handle image from URL (only if no file uploaded)
    if (!$image_uploaded && !empty($_POST['image_url'])) {
        $image = trim($_POST['image_url']);
    }

    // ✅ 3. Final validation: Must have image
    if (empty($image)) {
        $_SESSION['error'] = "Image is required (upload or provide URL).";
        header("Location: edit_product.php?id=$id");
        exit;
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, description=?, category_id=?, image=? WHERE id=?");
    $stmt->bind_param("sdisisi", $name, $price, $stock, $desc, $category, $image, $id);
    $stmt->execute();

    $_SESSION['success'] = "Product updated successfully.";
    header("Location: products.php?id=$id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <!-- RuangAdmin -->
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">

  <?php include('partials/sidebar.php'); ?>

  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

      <?php include('partials/topbar.php'); ?>

      <div class="container-fluid mt-4">

        <!-- ✅ Flash Message -->
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>

        <!-- ✅ Edit Product Form -->
        <div class="card shadow">
          <div class="card-header">
            <h4>Edit Product</h4>
          </div>
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">

              <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
              </div>

              <div class="form-group">
                <label>Price (₹)</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
              </div>

              <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required>
              </div>

              <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control" required>
                  <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($c['name']) ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>

              <div class="form-group">
                <label>Upload New Image</label>
                <input type="file" name="image" id="imageInput" class="form-control-file">
              </div>

              <div class="form-group">
                <label>Or Enter Image URL</label>
                <input type="url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
              </div>

              <div class="form-group">
                <label>Current Image Preview</label><br>
                <?php
                  $imgSrc = filter_var($product['image'], FILTER_VALIDATE_URL) 
                    ? $product['image'] 
                    : '../uploads/product-images/' . $product['image'];
                ?>
                <img id="previewImage" src="<?= $imgSrc ?>" class="img-thumbnail" style="width: 120px; height: 120px;" 
                     onerror="this.src='https://via.placeholder.com/120x120?text=No+Image';">
              </div>

              <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" class="form-control" required><?= htmlspecialchars($product['description']) ?></textarea>
              </div>

              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Product
              </button>
              <a href="products.php" class="btn btn-secondary">Cancel</a>
            </form>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- JS Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>

<!-- ✅ Live Image Preview -->
<script>
  document.getElementById('imageInput').addEventListener('change', function (event) {
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function (e) {
        document.getElementById('previewImage').src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });
</script>

</body>
</html>
