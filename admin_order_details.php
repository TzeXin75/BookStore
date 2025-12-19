<?php
require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;
$msg = "";
$msg_type = "";

if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    $stmt_check = $pdo->prepare("SELECT order_status FROM orders WHERE order_id = ?");
    $stmt_check->execute([$order_id]);
    $current_order = $stmt_check->fetch();
    $old_status = $current_order['order_status'] ?? '';

    if ($new_status === 'Cancelled' && $old_status !== 'Cancelled') {
        try {
            $pdo->beginTransaction();

            $stmt_items = $pdo->prepare("SELECT book_id, quantity FROM order_details WHERE order_id = ?");
            $stmt_items->execute([$order_id]);
            $restock_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            $update_stock = $pdo->prepare("UPDATE book SET stock_quantity = stock_quantity + ? WHERE id = ?");
            foreach ($restock_items as $item) {
                $update_stock->execute([$item['quantity'], $item['book_id']]);
            }

            $update_status = $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ?");
            $update_status->execute([$order_id]);

            $pdo->commit();
            $msg = "Order Cancelled. Stock has been restored to inventory.";
            $msg_type = "error"; 
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Error processing cancellation: " . $e->getMessage();
            $msg_type = "error";
        }
    } else {
        $update_sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([$new_status, $order_id]);
        $msg = "Status Updated to " . htmlspecialchars($new_status) . "!";
        $msg_type = "success";
    }
}

$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) { die("Order not found."); }

$stmt_items = $pdo->prepare("SELECT od.*, b.title FROM order_details od JOIN book b ON od.book_id = b.id WHERE od.order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<style>
    .admin-container { max-width: 1100px; margin: 3rem auto; padding: 0 1rem; }
    .admin-card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; border: 1px solid #ddd; }
    .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .back-btn { text-decoration: none; color: #555; font-weight: bold; padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .info-box { background: #f9f9f9; padding: 15px; border-radius: 5px; }
    .label { font-size: 0.8rem; color: #7f8c8d; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 5px; }
    .status-form { display: flex; gap: 10px; align-items: center; background: #ecf0f1; padding: 15px; border-radius: 5px; }
    select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-size: 1rem; }
    .update-btn { background: #3498db; color: white; border: none; padding: 9px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #2c3e50; color: white; padding: 12px; text-align: left; }
    td { padding: 12px; border-bottom: 1px solid #eee; }
    .total-row td { font-weight: bold; font-size: 1.1rem; border-top: 2px solid #ddd; }
    .msg { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; font-weight: bold; }
    .msg.success { background: #d4edda; color: #155724; }
    .msg.error { background: #f8d7da; color: #721c24; }
</style>

<div class="admin-container">
    <div class="header-row">
        <h2>Order Management #<?php echo $order['order_id']; ?></h2>
        <a href="admin_orders.php" class="back-btn">&larr; Back to Orders</a>
    </div>

    <?php if ($msg != ""): ?>
        <div class="msg <?php echo $msg_type; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="info-grid">
            <div class="info-box">
                <span class="label">Customer</span>
                <strong><?php echo htmlspecialchars($order['username']); ?></strong> (<?php echo htmlspecialchars($order['email']); ?>)
            </div>
            <div class="info-box">
                <span class="label">Shipping Address</span>
                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
            </div>
        </div>

        <div class="info-box">
            <span class="label">Status Control</span>
            <form method="POST" class="status-form">
                <select name="status">
                    <?php 
                    $statuses = ['Pending', 'Paid', 'Shipped', 'Completed', 'Cancelled'];
                    foreach($statuses as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo ($order['order_status'] == $s) ? 'selected' : ''; ?>>
                            <?php echo $s; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="update_status" class="update-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <h3>Items Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th style="text-align: center;">Qty</th>
                    <th>Price</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td style="text-align: right;">$<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Grand Total:</td>
                    <td style="text-align: right;">$<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>