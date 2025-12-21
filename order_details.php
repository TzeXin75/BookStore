<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

// Fetch Order Header
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) { die("Order not found."); }

// Fetch Items
$stmt = $pdo->prepare("SELECT od.*, b.title, b.images FROM order_details od JOIN book b ON od.id = b.id WHERE od.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach($items as $item) { $subtotal += ($item['unit_price'] * $item['quantity']); }
$discount = $subtotal - $order['total_amount'];

include 'includes/header.php';
?>

<div class="container" style="max-width: 1000px; margin: 40px auto; padding: 0 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Order #<?= $order['order_id'] ?> Details</h2>
        <a href="generate_receipt.php?id=<?= $order['order_id'] ?>" class="btn-primary" style="padding: 10px 20px; background: #2c3e50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
            <i class="fa fa-file-pdf"></i> Download Receipt
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <!-- Items Table -->
        <div style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; text-align: left; border-bottom: 2px solid #eee;">
                        <th style="padding: 15px;">Book</th>
                        <th style="padding: 15px;">Price</th>
                        <th style="padding: 15px;">Qty</th>
                        <th style="padding: 15px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;"><?= htmlspecialchars($item['title']) ?></td>
                        <td style="padding: 15px;">$<?= number_format($item['unit_price'], 2) ?></td>
                        <td style="padding: 15px;"><?= $item['quantity'] ?></td>
                        <td style="padding: 15px;">$<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Order Info Sidebar -->
        <div style="background: #fdfdfd; padding: 20px; border: 1px solid #ddd; border-radius: 8px; height: fit-content;">
            <h4 style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Order Summary</h4>
            <p><strong>Status:</strong> <span style="color: #28a745;"><?= $order['order_status'] ?></span></p>
            <p><strong>Date:</strong> <?= date('d M Y', strtotime($order['order_date'])) ?></p>
            <hr>
            <p style="display: flex; justify-content: space-between;"><span>Subtotal:</span> <span>$<?= number_format($subtotal, 2) ?></span></p>
            <?php if ($discount > 0.01): ?>
                <p style="display: flex; justify-content: space-between; color: red;"><span>Discount:</span> <span>-$<?= number_format($discount, 2) ?></span></p>
            <?php endif; ?>
            <h3 style="display: flex; justify-content: space-between; color: #28a745; margin-top: 10px;"><span>Total:</span> <span>$<?= number_format($order['total_amount'], 2) ?></span></h3>
            
            <hr>
            <h4 style="margin: 15px 0 5px 0;">Shipping Address:</h4>
            <p style="font-size: 0.9em; color: #666; line-height: 1.4;"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>