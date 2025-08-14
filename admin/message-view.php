<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isAdmin()) header("Location: ../auth/login.php");

if (!isset($_GET['id'])) {
    header("Location: messages.php");
    exit();
}

$messageId = intval($_GET['id']);

// Fetch the main message
$msg = $conn->query("SELECT * FROM messages WHERE id = $messageId")->fetch_assoc();
if (!$msg) {
    echo "<div class='alert alert-danger'>Message not found.</div>";
    exit();
}

// Mark as read
$conn->query("UPDATE messages SET is_read = 1 WHERE id = $messageId");

// Handle reply form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply = mysqli_real_escape_string($conn, $_POST['reply']);
    $adminId = $_SESSION['user']['id'];

    $stmt = $conn->prepare("INSERT INTO message_replies (message_id, admin_id, reply) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $messageId, $adminId, $reply);
    $stmt->execute();
    header("Location: message-view.php?id=$messageId");
    exit();
}

// Fetch all replies
$replies = $conn->query("SELECT * FROM message_replies WHERE message_id = $messageId ORDER BY created_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Message View</title>

    <!-- ✅ Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ✅ Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- ✅ RuangAdmin CSS -->
    <link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php include('partials/sidebar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('partials/topbar.php'); ?>

            <div class="container-fluid mt-4">
                <a href="messages.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Messages</a>

                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong>Subject:</strong> <?= htmlspecialchars($msg['subject'] ?? '') ?>
                    </div>
                    <div class="card-body">
                        <p><strong>From:</strong> <?= htmlspecialchars($msg['name'] ?? '') ?> (<?= htmlspecialchars($msg['email'] ?? '') ?>)</p>
                        <p><strong>Sent On:</strong> <?= date('d M Y, h:i A', strtotime($msg['created_at'] ?? 'now')) ?></p>
                        <hr>
                        <p><?= nl2br(htmlspecialchars($msg['message'] ?? '')) ?></p>
                    </div>
                </div>

                <!-- Replies -->
                <h5>Replies</h5>
                <?php if ($replies->num_rows > 0): ?>
                    <ul class="list-group mb-4">
                        <?php while ($r = $replies->fetch_assoc()): ?>
                            <li class="list-group-item">
                                <div class="text-muted small float-right"><?= date('d M Y, h:i A', strtotime($r['created_at'] ?? 'now')) ?></div>
                                <strong>Admin:</strong>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($r['reply'] ?? '')) ?></p>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-info">No replies yet.</div>
                <?php endif; ?>

                <!-- Reply Form -->
                <div class="card shadow">
                    <div class="card-body">
                        <h5>Write a Reply</h5>
                        <form method="POST">
                            <div class="form-group">
                                <textarea name="reply" rows="4" class="form-control" required placeholder="Write your reply..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-reply"></i> Send Reply</button>
                        </form>
                    </div>
                </div>

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

<!-- ✅ JS CDN -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>
</body>
</html>
