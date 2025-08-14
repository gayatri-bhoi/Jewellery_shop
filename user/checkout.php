<?php
require_once('../middleware.php');
require_once('../config/db.php');
checkLogin();
if (!isUser()) header("Location: ../auth/login.php");

$userId = $_SESSION['user']['id'];

// Get cart items
$cartItems = $conn->query("SELECT c.*, p.name, p.price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $userId");

$totalAmount = 0;
$orderItems = [];
$stockErrors = [];

while ($row = $cartItems->fetch_assoc()) {
    $pid = $row['product_id'];
    $qty = $row['quantity'];
    $price = $row['price'];
    $stock = $row['stock'];
    $name = $row['name'];

    if ($stock < $qty) {
        $stockErrors[] = "❌ <strong>$name</strong> has only $stock left in stock.";
        continue;
    }

    $subtotal = $qty * $price;
    $totalAmount += $subtotal;

    $orderItems[] = [
        'product_id' => $pid,
        'quantity' => $qty,
        'price' => $price,
        'name' => $name,
        'subtotal' => $subtotal
    ];
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($stockErrors)) {
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $stmt->bind_param("id", $userId, $totalAmount);
    $stmt->execute();
    $orderId = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

    foreach ($orderItems as $item) {
        $stmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
        $stmt->execute();

        $conn->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['product_id']}");
    }

    $conn->query("DELETE FROM cart WHERE user_id = $userId");

    echo "<!DOCTYPE html><html lang='en'><head>
    <title>Order Placed</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head><body class='container mt-5'>
    <div class='alert alert-success'>
        <h4>✅ Order placed successfully!</h4>
        <p>🧾 Order ID: <strong>$orderId</strong></p>
        <p>💰 Total Paid: ₹<strong>" . number_format($totalAmount, 2) . "</strong></p>
        <a href='dashboard.php' class='btn btn-primary mt-3'>Back to Dashboard</a>
    </div>
    </body></html>";
    exit();
}
?>