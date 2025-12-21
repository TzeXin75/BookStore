<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

// Security: Verify Admin Role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php"); exit();
}

$order_id = $_GET['id'] ?? 0;

// --- TASK 2 FIX: Handle Status Update Logic inside this file ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $target_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    try {
        $pdo->beginTransaction();

        // 1. Get current status to check if we are CHANGING to Cancelled
        $stmt_status = $pdo->prepare("SELECT order_status FROM orders WHERE order_id = ?");
        $stmt_status->execute([$target_id]);
        $old_status = $stmt_status->fetchColumn();

        if ($new_status === 'Cancelled' && $old_status !== 'Cancelled') {
            // 2. Restore Stock if newly cancelled
            $stmt_items = $pdo->prepare("SELECT id, quantity FROM order_details WHERE order_id = ?");
            $stmt_items->execute([$target_id]);
            $items = $stmt_items->fetchAll();

            $upd_stock = $pdo->prepare("UPDATE book SET stock = stock + ? WHERE id = ?");
            foreach ($items as $item) {
                $upd_stock->execute([$item['quantity'], $item['id']]);
            }
        }

        // 3. Update the Order table
        $update_stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $update_stmt->execute([$new_status, $target_id]);

        $pdo->commit();
        $_SESSION['status_msg'] = "Order status updated to $new_status successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['status_error'] = "Error: " . $e->getMessage();
    }
    header("Location: admin_order_details.php?id=" . $target_id);
    exit();
}

// Fetch Order Header + User Info
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) { die("Order not found."); }

// Fetch Payment Info
$stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
$stmt->execute([$order_id]);
$payment = $stmt->fetch();

// Fetch Items (Matches table 'order_details' and column 'id')
$stmt = $pdo->prepare("SELECT od.*, b.title FROM order_details od JOIN book b ON od.id = b.id WHERE od.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach($items as $item) { $subtotal += ($item['unit_price'] * $item['quantity']); }
$discount = $subtotal - $order['total_amount'];

include 'includes/header.php';
?>

<div class="container" style="max-width: 1100px; margin: 40px auto; padding: 0 20px;">
    
    <!-- Success Message Alert -->
    <?php if(isset($_SESSION['status_msg'])): ?>
        <div style="background:#d4edda; color:#155724; padding:15px; border-radius:5px; margin-bottom:20px; border:1px solid #c3e6cb;">
            <i class="fa fa-check-circle"></i> <?= $_SESSION['status_msg']; unset($_SESSION['status_msg']); ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 25px;">
        <a href="admin_orders.php" style="text-decoration: none; color: #555;"><i class="fa fa-arrow-left"></i> Back to Orders</a>
        <h2 style="margin-top: 10px;">Management: Order #<?= $order['order_id'] ?></h2>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
        <!-- Left Column: Items and Shipping -->
        <div>
            <div style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="background: #34495e; color: white;">
                        <th style="padding: 12px; text-align: left;">Product</th>
                        <th style="padding: 12px;">Price</th>
                        <th style="padding: 12px;">Qty</th>
                        <th style="padding: 12px;">Total</th>
                    </tr>
                    <?php foreach ($items as $item): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;"><?= htmlspecialchars($item['title']) ?></td>
                        <td style="padding: 12px; text-align: center;">$<?= number_format($item['unit_price'], 2) ?></td>
                        <td style="padding: 12px; text-align: center;"><?= $item['quantity'] ?></td>
                        <td style="padding: 12px; text-align: right;">$<?= number_format($item['unit_price'] * $item['quantity'], 2) ?>&nbsp;&nbsp;</td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                <h4>Shipping Label Information</h4>
                <p style="margin-top: 10px; color: #333; line-height: 1.6;">
                    <strong>Recipient:</strong> <?= htmlspecialchars($order['username']) ?><br>
                    <strong>Address:</strong><br><?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                </p>
            </div>
        </div>

        <!-- Right Column: Summary and Status Control -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h4 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">Order Financials</h4>
                <p><strong>Payment:</strong> <?= $payment['payment_method'] ?? 'Unknown' ?></p>
                <p><strong>Reference:</strong> <?= $payment['transaction_ref'] ?? 'N/A' ?></p>
                <hr>
                <h3 style="display: flex; justify-content: space-between; color: #2c3e50;"><span>Total Paid:</span> <span>$<?= number_format($order['total_amount'], 2) ?></span></h3>
            </div>

            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h4>Status Management</h4>
                <p style="margin: 10px 0;">Current: <strong style="color: <?= ($order['order_status'] == 'Cancelled') ? 'red' : 'orange' ?>;"><?= $order['order_status'] ?></strong></p>
                
                <!-- Action points back to this same file -->
                <form action="admin_order_details.php?id=<?= $order['order_id'] ?>" method="POST">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                    <select name="status" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; margin-bottom: 10px;">
                        <option value="Pending" <?= $order['order_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Shipped" <?= $order['order_status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="Completed" <?= $order['order_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled" <?= $order['order_status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" style="width: 100%; padding: 10px; background: #2c3e50; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>