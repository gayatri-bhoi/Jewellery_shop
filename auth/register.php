<?php
require_once('../config/db.php');
session_start();

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = $_POST['role'];

    if ($password !== $confirm) {
        $error = "❌ Passwords do not match.";
    } elseif (!in_array($role, ['admin', 'user'])) {
        $error = "❌ Invalid role selected.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "❌ Email already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed, $role);
            if ($stmt->execute()) {
                $success = "✅ Registered successfully as <strong>$role</strong>!";
            } else {
                $error = "❌ Registration failed: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register | Jewellery Shop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CDN CSS Links -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin@master/css/ruang-admin.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-login">

<div class="container-login">
  <div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
      <div class="card shadow-sm my-5">
        <div class="card-body p-0">
          <div class="row">
            <div class="col-lg-12">
              <div class="login-form p-4">
                <div class="text-center">
                  <h1 class="h4 text-gray-900 mb-4">Register</h1>
                </div>

                <?php if (!empty($error)): ?>
                  <div class="alert alert-danger"><?= $error; ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                  <div class="alert alert-success"><?= $success; ?></div>
                <?php endif; ?>

                <form method="POST">
                  <div class="form-group mb-3">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter Full Name" required>
                  </div>
                  <div class="form-group mb-3">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter Email Address" required>
                  </div>
                  <div class="form-group mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                  </div>
                  <div class="form-group mb-3">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Repeat Password" required>
                  </div>
                  <div class="form-group mb-4">
                    <label>Role</label>
                    <select name="role" class="form-control" required>
                      <option value="user" selected>User</option>
                      <option value="admin">Admin</option>
                    </select>
                  </div>
                  <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>

                <hr>
                <div class="text-center">
                  <a class="font-weight-bold small" href="login.php">Already have an account? Login!</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CDN JS Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin@master/js/ruang-admin.min.js"></script>
</body>
</html>
