<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Check user login
if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/db.php';


$user_id = $_SESSION['user']['id'];
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current = trim($_POST['current_password']);
    $new = trim($_POST['new_password']);
    $confirm = trim($_POST['confirm_password']);

    // Validation
    if (empty($current) || empty($new) || empty($confirm)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: change-password.php");
        exit();
    } elseif ($new !== $confirm) {
        $_SESSION['error_message'] = "New passwords do not match.";
        header("Location: change-password.php");
        exit();
    } elseif (strlen($new) < 6) {
        $_SESSION['error_message'] = "Password must be at least 6 characters.";
        header("Location: change-password.php");
        exit();
    } else {
        // Check current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashedPassword);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current, $hashedPassword)) {
            $_SESSION['error_message'] = "Current password is incorrect.";
            header("Location: change-password.php");
            exit();
        }

        // Update to new password
        $new_hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $new_hashed, $user_id);

        if ($update->execute()) {
            $_SESSION['success_message'] = "Password updated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to update password.";
        }

        $update->close();
        header("Location: change-password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Change Password</h4>
            </div>
            <div class="card-body">
                <!-- Display messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <form method="post" action="change-password.php">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Password</button>
                    <a href="account.php" class="btn btn-secondary">Back to Account</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS + jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
