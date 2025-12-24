<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

$order_id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $target_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    try {
        $pdo->beginTransaction();
        $stmt_status = $pdo->prepare("SELECT order_status FROM orders WHERE order_id = ?");
        $stmt_status->execute([$target_id]);
        $old_status = $stmt_status->fetchColumn();

        if ($new_status === 'Cancelled' && $old_status !== 'Cancelled') {
            $stmt_items = $pdo->prepare("SELECT id, quantity FROM order_details WHERE order_id = ?");
            $stmt_items->execute([$target_id]);
            $items = $stmt_items->fetchAll();

            $upd_stock = $pdo->prepare("UPDATE book SET stock = stock + ? WHERE id = ?");
            foreach ($items as $item) {
                $upd_stock->execute([$item['quantity'], $item['id']]);
            }
        }

        $update_stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $update_stmt->execute([$new_status, $target_id]);

        $pdo->commit();
        $_SESSION['status_msg'] = "Order status updated successfully.";
        // Change currency from $ to RM
        $_SESSION['status_msg'] = str_replace('$', 'RM', $_SESSION['status_msg']);
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['status_error'] = "Error: " . $e->getMessage();
    }
    header("Location: admin.php?page=order_details&id=" . $target_id);
    exit();
}

$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) { echo "Order not found."; return; }

$stmt = $pdo->prepare("SELECT od.*, b.title FROM order_details od JOIN book b ON od.id = b.id WHERE od.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<div class="container" style="max-width: 100%; padding: 10px;">
    <?php if(isset($_SESSION['status_msg'])): ?>
        <div style="background:#d4edda; color:#155724; padding:15px; border-radius:5px; margin-bottom:20px; border:1px solid #c3e6cb;">
            &#10003; <?= $_SESSION['status_msg']; unset($_SESSION['status_msg']); ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 25px;">
        <a href="admin.php?page=manage orders" style="text-decoration: none; color: #3498db; font-weight: bold;">
            &larr; Back to Orders
        </a>
        <h2 style="margin-top: 10px;">Management: Order #<?= $order['order_id'] ?></h2>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
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
                        <td style="padding: 12px; text-align: center;">RM<?= number_format($item['unit_price'], 2) ?></td>
                        <td style="padding: 12px; text-align: center;"><?= $item['quantity'] ?></td>
                        <td style="padding: 12px; text-align: right;">RM<?= number_format($item['unit_price'] * $item['quantity'], 2) ?>&nbsp;&nbsp;</td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                <h4>Shipping Address</h4>
                <p style="margin-top: 10px; color: #333; line-height: 1.6;">
                    <strong>Recipient:</strong> <?= htmlspecialchars($order['username']) ?><br>
                    <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                </p>
            </div>
        </div>

        <div>
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">
                <h4>Financial Summary</h4>
                <hr>
                <h3 style="display: flex; justify-content: space-between; color: #2c3e50;">
                    <span>Total Paid:</span> <span>RM<?= number_format($order['total_amount'], 2) ?></span>
                </h3>
            </div>

            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h4>Status Management</h4>
                <p>Current: <strong style="color: #e67e22;"><?= $order['order_status'] ?></strong></p>
                
                <form action="admin.php?page=order_details&id=<?= $order['order_id'] ?>" method="POST">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                    <select name="status" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">
                        <option value="Pending" <?= $order['order_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Shipped" <?= $order['order_status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="Completed" <?= $order['order_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled" <?= $order['order_status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" style="width: 100%; padding: 10px; background: #2c3e50; color: white; border: none; cursor: pointer; font-weight: bold;">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>