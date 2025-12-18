<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$order_id = $_GET['id'];

// 1. Fetch Order Header Info
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

// 2. Fetch Order Items
$sql_items = "SELECT od.*, b.title
              FROM order_details od 
              JOIN book b ON od.book_id = b.id 
              WHERE od.order_id = ?";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Order Details #<?php echo $order['order_id']; ?></h2>
<p><strong>Date:</strong> <?php echo $order['order_date']; ?></p>
<p><strong>Status:</strong> <?php echo $order['order_status']; ?></p>
<p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>

<h3 style="margin-top: 30px;">Items Ordered</h3>
<table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background-color: #ddd;">
            <th>Book Title</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['title']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                <td>$<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3 style="text-align: right;">Grand Total: $<?php echo number_format($order['total_amount'], 2); ?></h3>

<div style="margin-top: 20px;">
    <a href="generate_receipt.php?id=<?php echo $order_id; ?>" target="_blank" class="btn" style="background-color: #17a2b8;">
        📄 Download PDF Receipt
    </a>
</div>
<a href="my_orders.php" class="btn" style="background-color: #666;">Back to History</a>

<?php include 'includes/footer.php'; ?>