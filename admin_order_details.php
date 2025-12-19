<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;

// 1. Handle Status Update Submission
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update_sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$new_status, $order_id]);
    
    echo "<p style='color:green; font-weight:bold;'>Status Updated to $new_status!</p>";
}

// 2. Fetch Order Info
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Fetch Items
$stmt_items = $pdo->prepare("SELECT od.*, b.title FROM order_details od JOIN book b ON od.book_id = b.id WHERE od.order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Manage Order #<?php echo $order['order_id']; ?></h2>
<p><strong>Customer:</strong> <?php echo $order['username']; ?> (<?php echo $order['email']; ?>)</p>
<p><strong>Address:</strong> <?php echo $order['shipping_address']; ?></p>

<!-- STATUS UPDATE FORM -->
<div style="background: #eee; padding: 15px; margin: 20px 0; border: 1px solid #ccc;">
    <form method="POST">
        <label><strong>Update Status:</strong></label>
        <select name="status">
            <option value="Pending" <?php if($order['order_status']=='Pending') echo 'selected'; ?>>Pending</option>
            <option value="Paid" <?php if($order['order_status']=='Paid') echo 'selected'; ?>>Paid</option>
            <option value="Shipped" <?php if($order['order_status']=='Shipped') echo 'selected'; ?>>Shipped</option>
            <option value="Completed" <?php if($order['order_status']=='Completed') echo 'selected'; ?>>Completed</option>
            <option value="Cancelled" <?php if($order['order_status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
        </select>
        <button type="submit" name="update_status" class="btn" style="padding: 5px 10px;">Update</button>
    </form>
</div>

<h3>Items Ordered</h3>
<table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr><th>Book</th><th>Qty</th><th>Price</th></tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo $item['title']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>$<?php echo $item['unit_price']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br>
<a href="admin_orders.php">Back to Order List</a>

<?php include 'includes/footer.php'; ?>