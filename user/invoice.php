<?php
require_once('../config/db.php');
require_once('lib/dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$orderId = $_GET['id'];
$order = $conn->query("SELECT * FROM orders WHERE id=$orderId")->fetch_assoc();
$user = $conn->query("SELECT * FROM users WHERE id={$order['user_id']}")->fetch_assoc();
$items = $conn->query("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE order_id=$orderId");

$html = "<h2>Invoice - Order #{$order['id']}</h2>";
$html .= "<p><strong>Customer:</strong> {$user['name']} ({$user['email']})</p>";
$html .= "<p><strong>Date:</strong> {$order['created_at']}</p><hr>";
$html .= "<table border='1' cellpadding='10' width='100%'>
<tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr>";

$total = 0;
while ($i = $items->fetch_assoc()) {
    $lineTotal = $i['price'] * $i['quantity'];
    $total += $lineTotal;
    $html .= "<tr>
        <td>{$i['name']}</td>
        <td>₹{$i['price']}</td>
        <td>{$i['quantity']}</td>
        <td>₹{$lineTotal}</td>
    </tr>";
}
$html .= "<tr><td colspan='3'><strong>Grand Total</strong></td><td><strong>₹{$total}</strong></td></tr>";
$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Invoice_Order_{$orderId}.pdf", ["Attachment" => false]);
exit();
