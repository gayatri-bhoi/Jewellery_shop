<?php
require_once('../config/db.php');
session_start();

// Delete logic
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    // Delete related entries to prevent FK constraint failure
    $conn->query("DELETE FROM cart WHERE product_id = $id");
    $conn->query("DELETE FROM wishlist WHERE product_id = $id");

    // Now delete the product
    if ($conn->query("DELETE FROM products WHERE id = $id")) {
        $_SESSION['success'] = "Product deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete product: " . $conn->error;
    }

    header("Location: products.php");
    exit;
}

// Fetch products
$products = $conn->query("SELECT products.*, categories.name AS category_name 
                          FROM products 
                          LEFT JOIN categories ON products.category_id = categories.id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>All Products | Admin Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Favicon & Styles -->
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/img/logo/logo.png" rel="icon">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
  <div id="wrapper">

    <?php include('partials/sidebar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include('partials/topbar.php'); ?>

        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0 text-gray-800">All Products</h1>
            <a href="add_product.php" class="btn btn-sm btn-success">
              <i class="fas fa-plus-circle"></i> Add Product
            </a>
          </div>

          <!-- ✅ Flash Messages -->
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

          <!-- ✅ Product Table -->
          <div class="row">
            <div class="col-lg-12 mb-4">
              <div class="card shadow">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Product List</h6>
                </div>

                <!-- ✅ Image Modal -->
                <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Product Image Preview</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body text-center">
                        <img id="modalImage" src="" class="img-fluid rounded shadow" style="max-height: 500px;" alt="Preview">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ✅ Product Table -->
                <div class="table-responsive p-3">
                  <table class="table table-bordered" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $products->fetch_assoc()): ?>
                        <tr>
                          <td><?= $row['id'] ?></td>
                          <td>
                            <?php
                              $imgSrc = filter_var($row['image'], FILTER_VALIDATE_URL)
                                  ? $row['image']
                                  : '../uploads/product-images/' . $row['image'];
                            ?>
                            <img src="<?= $imgSrc ?>" alt="Image" class="img-thumbnail"
                                 style="width:60px;height:60px; cursor:pointer;"
                                 data-toggle="modal" data-target="#imageModal"
                                 data-img="<?= $imgSrc ?>"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/60x60?text=No+Image';">
                          </td>
                          <td><?= htmlspecialchars($row['name']) ?></td>
                          <td>₹<?= $row['price'] ?></td>
                          <td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></td>

                          <td><?= $row['stock'] ?></td>
                          <td>
                            <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                              <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="products.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this product?');">
                              <i class="fas fa-trash-alt"></i> Delete
                            </a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- ✅ Footer -->
        <footer class="sticky-footer bg-white">
          <div class="container my-auto">
            <div class="text-center my-auto">
              <span>&copy; <?= date('Y') ?> - Jewellery Admin Panel</span>
            </div>
          </div>
        </footer>

      </div>
    </div>
  </div>

  <!-- ✅ Scroll to Top -->
  <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

  <!-- ✅ Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/js/dataTables.bootstrap4.min.js"></script>

  <!-- ✅ DataTables Init -->
  <script>
    $(document).ready(function () {
      $('#dataTable').DataTable();
    });

    // ✅ Image Modal Preview
    $('#imageModal').on('show.bs.modal', function (event) {
      var img = $(event.relatedTarget);
      var src = img.data('img');
      $('#modalImage').attr('src', src);
    });
  </script>

</body>
</html>
