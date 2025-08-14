<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isAdmin()) header("Location: ../auth/login.php");

// Mark as read
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $conn->query("UPDATE messages SET is_read = 1 WHERE id = $id");
    header("Location: messages.php");
    exit();
}

// Delete message
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM messages WHERE id = $id");
    header("Location: messages.php");
    exit();
}

// Fetch messages with user name & email
$messages = $conn->query("
    SELECT messages.*, users.name, users.email 
    FROM messages 
    LEFT JOIN users ON messages.user_id = users.id 
    ORDER BY messages.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Messages</title>

  <!-- ✅ Bootstrap & RuangAdmin CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">
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
        <h1 class="h4 mb-4">📨 User Messages</h1>

        <?php if ($messages->num_rows > 0): ?>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead class="thead-light">
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Subject</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php while ($row = $messages->fetch_assoc()): ?>
                <tr class="<?= $row['is_read'] ? '' : 'table-warning' ?>">
                  <td><?= htmlspecialchars($row['name'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($row['subject'] ?? '') ?></td>
                  <td>
                    <?php if ($row['is_read']): ?>
                      <span class="badge badge-success">Read</span>
                    <?php else: ?>
                      <span class="badge badge-warning">Unread</span>
                    <?php endif; ?>
                  </td>
                  <td><?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></td>
                  <td>
                    <a href="message-view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">
                      <i class="fas fa-eye"></i> View
                    </a>
                    <?php if (!$row['is_read']): ?>
                      <a href="?mark_read=<?= $row['id'] ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-check"></i> Mark Read
                      </a>
                    <?php endif; ?>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this message?');">
                      <i class="fas fa-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info">No messages found.</div>
        <?php endif; ?>
      </div>

    </div>

    <!-- Footer -->
    <footer class="bg-white sticky-footer">
      <div class="container my-auto text-center">
        <span>© <?= date('Y') ?> Jewellery Shop Admin</span>
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
