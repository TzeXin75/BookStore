<?php
require_once 'config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php"); exit();
}

$order_id = $_GET['id'] ?? 0;
$msg = "";

if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $stmt_check = $pdo->prepare("SELECT order_status FROM orders WHERE order_id = ?");
    $stmt_check->execute([$order_id]);
    $old_status = $stmt_check->fetch()['order_status'];

    if ($new_status === 'Cancelled' && $old_status !== 'Cancelled') {
        try {
            $pdo->beginTransaction();
            $stmt_items = $pdo->prepare("SELECT id, quantity FROM order_details WHERE order_id = ?");
            $stmt_items->execute([$order_id]);
            foreach ($stmt_items->fetchAll() as $item) {
                $pdo->prepare("UPDATE book SET stock = stock + ? WHERE id = ?")->execute([$item['quantity'], $item['id']]);
            }
            $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ?")->execute([$order_id]);
            $pdo->commit();
            $msg = "<p style='color:red;'>Order Cancelled & Stock Restored.</p>";
        } catch (Exception $e) { $pdo->rollBack(); }
    } else {
        $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?")->execute([$new_status, $order_id]);
        $msg = "<p style='color:green;'>Status Updated!</p>";
    }
}

$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

$stmt_items = $pdo->prepare("SELECT od.*, b.title FROM order_details od JOIN book b ON od.id = b.id WHERE od.order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();

include 'header.php'; 
?>

<div style="display:flex; justify-content: space-between;">
    <h2>Manage Order #<?php echo $order['order_id']; ?></h2>
    <a href="admin_orders.php" style="margin-top:20px;">&larr; Back to List</a>
</div>
<?php echo $msg; ?>

<div style="background:#f9f9f9; padding:20px; border-radius:5px; margin-bottom:20px;">
    <strong>Customer:</strong> <?php echo $order['username']; ?> (<?php echo $order['email']; ?>)<br>
    <strong>Address:</strong> <?php echo $order['shipping_address']; ?>
</div>

<form method="POST" style="background:#eee; padding:15px; border-radius:5px; margin-bottom:20px;">
    <label>Update Status: </label>
    <select name="status">
        <option value="Pending" <?php if($order['order_status']=='Pending') echo 'selected'; ?>>Pending</option>
        <option value="Shipped" <?php if($order['order_status']=='Shipped') echo 'selected'; ?>>Shipped</option>
        <option value="Completed" <?php if($order['order_status']=='Completed') echo 'selected'; ?>>Completed</option>
        <option value="Cancelled" <?php if($order['order_status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
    </select>
    <button type="submit" name="update_status" class="btn">Save Change</button>
</form>

<table width="100%" border="1" style="border-collapse:collapse;">
    <tr style="background:#333; color:white;">
        <th style="padding:10px;">Item</th>
        <th>Qty</th>
        <th>Price</th>
    </tr>
    <?php foreach($items as $i): ?>
    <tr>
        <td style="padding:10px;"><?php echo $i['title']; ?></td>
        <td align="center"><?php echo $i['quantity']; ?></td>
        <td align="right">$<?php echo number_format($i['unit_price'], 2); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include 'footer.php'; ?>