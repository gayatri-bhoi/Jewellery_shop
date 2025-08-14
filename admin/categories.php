<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isAdmin()) header("Location: ../auth/login.php");

$message = "";

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $message = "Category added successfully.";
        } else {
            $message = "Error adding category.";
        }
        $stmt->close();
    }
}

// Delete category
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Check if ID exists before deleting
    $check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: categories.php?deleted=1");
            exit();
        } else {
            $message = "Delete failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Category not found.";
    }
    $check->close();
}

$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Categories</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">
  <?php include('partials/sidebar.php'); ?>
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include('partials/topbar.php'); ?>

      <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800">Manage Categories</h1>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Categories</li>
          </ol>
        </div>

        <?php if (!empty($message)): ?>
          <div class="alert alert-info alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
          <div class="alert alert-success alert-dismissible fade show">
            Category deleted successfully.
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>

        <!-- Add Category Form -->
        <div class="row mb-4">
          <div class="col-md-6">
            <form method="POST" class="form-inline">
              <input type="text" name="name" class="form-control mr-2" placeholder="New Category" required>
              <button type="submit" class="btn btn-primary">Add Category</button>
            </form>
          </div>
        </div>

        <!-- Category Table -->
        <div class="row">
          <div class="col-lg-12">
            <div class="card mb-4">
              <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Category List</h6>
              </div>
              <div class="table-responsive p-3">
                <table class="table table-bordered" id="categoryTable">
                  <thead class="thead-light">
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php while ($c = $categories->fetch_assoc()): ?>
                    <tr>
                      <td><?= $c['id'] ?></td>
                      <td><?= htmlspecialchars($c['name']) ?></td>
                      <td>
                        <a href="categories.php?delete=<?= $c['id'] ?>"
                           onclick="return confirm('Are you sure you want to delete this category?')"
                           class="btn btn-sm btn-danger">
                          <i class="fas fa-trash"></i>
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
    </div>

    <!-- Footer -->
    <footer class="sticky-footer bg-white">
      <div class="container my-auto">
        <div class="copyright text-center my-auto">
          <span>© <?= date('Y') ?> - Developed by <a href="https://indrijunanda.gitlab.io/">indrijunanda</a></span>
        </div>
      </div>
    </footer>
  </div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
  <i class="fas fa-angle-up"></i>
</a>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(document).ready(function () {
    $('#categoryTable').DataTable();
  });
</script>
</body>
</html>
