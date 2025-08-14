<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit();
}

$user = $_SESSION['user']; // Assuming 'user' contains ['name', 'email', 'role']
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Account Settings</title>
  <!-- Bootstrap & Font Awesome CDN -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <!-- ✅ Bootstrap 4.6 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ✅ Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

  <!-- ✅ RuangAdmin CSS -->
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">
</head>
<body>

<div id="wrapper">

  <?php include('partials/sidebar.php'); ?>

  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

      <?php include('partials/topbar.php'); ?>
<div class="container mt-5">
  <h2 class="mb-4">Account Settings</h2>
  
  <div class="card">
    <div class="card-body">
      <h5 class="card-title"><i class="fas fa-user-circle mr-2"></i>Your Profile</h5>
      
      <form>
        <div class="form-group">
          <label>Name</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
        </div>

        <div class="form-group">
          <label>Role</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['role']); ?>" readonly>
        </div>
      </form>

      <!-- Optional: Link to change password -->
      <a href="change-password.php" class="btn btn-primary mt-3">
        <i class="fas fa-key mr-1"></i> Change Password
      </a>
    </div>
  </div>
</div>

<!-- Footer (optional) -->
<?php include('includes/footer.php'); ?>

</body>
</html>
