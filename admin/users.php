<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isAdmin()) header("Location: ../auth/login.php");

$users = $conn->query("SELECT * FROM users WHERE role='user'");

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin - Registered Users</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Favicon -->
<link rel="icon" href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/img/logo/logo.png" />

<!-- Bootstrap 4 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- RuangAdmin CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/css/dataTables.bootstrap4.min.css">



  <!-- ✅ Bootstrap CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- ✅ Font Awesome CDN -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <!-- ✅ RuangAdmin CSS CDN -->
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">
  <!-- Favicon -->
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

      <!-- Container Fluid-->
<div class="container-fluid" id="container-wrapper">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Registered Users</h1>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="./">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">User Management</li>
    </ol>
  </div>



  <div class="row">
    <div class="col-lg-12">
      <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary">Users Table</h6>
        </div>

       


        <div class="table-responsive p-3">
          <table class="table align-items-center table-flush" id="dataTable">
            <thead class="thead-light">
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Registered At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                  <td><?= $user['id']; ?></td>
                  <td><?= $user['name']; ?></td>
                  <td><?= $user['email']; ?></td>
                  <td><?= $user['created_at']; ?></td>
                  <td>
                    <!-- Edit Button -->
                    <button class="btn btn-sm btn-warning" data-toggle="modal"
                      data-target="#editModal<?= $user['id']; ?>">
                      <i class="fas fa-edit"></i>
                    </button>

                    <!-- Delete Button -->
                    <a href="delete-user.php?id=<?= $user['id']; ?>"
                      onclick="return confirm('Are you sure you want to delete this user?');"
                      class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
                    </a>
                  </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $user['id']; ?>" tabindex="-1" role="dialog"
                  aria-labelledby="editModalLabel<?= $user['id']; ?>" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <form action="edit-user.php" method="POST">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="editModalLabel<?= $user['id']; ?>">Edit User</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id" value="<?= $user['id']; ?>">
                          <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?= $user['name']; ?>" required>
                          </div>
                          <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?= $user['email']; ?>"
                              required>
                          </div>
                          <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control">
                              <option value="user" <?= $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                              <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                          <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!--- End Container Fluid-->
<!-- Script to auto-fill modal form -->
<script>
  document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editUserId').value = btn.dataset.id;
      document.getElementById('editUserName').value = btn.dataset.name;
      document.getElementById('editUserEmail').value = btn.dataset.email;
    });
  });
</script>

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
          <div class="container my-auto">
            <div class="copyright text-center my-auto">
              <span>&copy; <?= date('Y') ?> - developed by <b><a href="https://indrijunanda.gitlab.io/" target="_blank">indrijunanda</a></b></span>
            </div>
          </div>
        </footer>
      </div>
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery.easing/jquery.easing.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>
  <script src="vendor/jquery/jquery.min.js"></script>

  <!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery Easing -->
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- RuangAdmin JS -->
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/js/dataTables.bootstrap4.min.js"></script>

<!-- Initialize DataTables -->
<script>
  $(document).ready(function () {
    $('#dataTable').DataTable();
  });
</script>




</body>
</html>
