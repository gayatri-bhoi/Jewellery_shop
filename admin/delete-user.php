<?php
require_once('../config/db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM users WHERE id = $id");
}

header("Location: dashboard.php");
?>
