<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isAdmin()) header("Location: ../auth/login.php");

$orders = $conn->query("SELECT o.*, u.name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- ✅ Bootstrap CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ✅ Font Awesome CDN -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

  <!-- ✅ Ruang Admin CSS (via CDN) -->
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
<h2>All Orders</h2>
<a href="dashboard.php">← Back to Admin Dashboard</a>
<table border="1" cellpadding="10">
    <tr>
        <th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Invoice</th>
    </tr>
    <?php while ($o = $orders->fetch_assoc()): ?>
        <tr>
            <td><?= $o['id'] ?></td>
            <td><?= $o['name'] ?> (<?= $o['email'] ?>)</td>
            <td>₹<?= $o['total_amount'] ?></td>
            <td><?= $o['status'] ?></td>
            <td><?= $o['created_at'] ?></td>
            <td><a href="../user/invoice.php?id=<?= $o['id'] ?>" target="_blank">PDF</a></td>
        </tr>
    <?php endwhile; ?>
</table>
