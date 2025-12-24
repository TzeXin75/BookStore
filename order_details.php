<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

if (isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: login.php"); exit(); 
}

$order_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) { die("Order not found."); }

$stmt = $pdo->prepare("SELECT od.*, b.title FROM order_details od JOIN book b ON od.id = b.id WHERE od.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach($items as $item) { $subtotal += ($item['unit_price'] * $item['quantity']); }
$discount = $subtotal - $order['total_amount'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Order Details - Bookstore</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container" style="max-width: 1100px; margin: 40px auto; padding: 20px; min-height: 60vh;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2>Order #<?= $order['order_id'] ?> Details</h2>
            <a href="generate_receipt.php?id=<?= $order['order_id'] ?>" style="padding: 10px 20px; background: #2c3e50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Download Receipt (PDF)</a>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <div style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; text-align: left; border-bottom: 2px solid #eee;">
                            <th style="padding: 15px;">Book Title</th>
                            <th style="padding: 15px;">Price</th>
                            <th style="padding: 15px;">Qty</th>
                            <th style="padding: 15px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;"><?= htmlspecialchars($item['title']) ?></td>
                            <td style="padding: 15px;">RM<?= number_format($item['unit_price'], 2) ?></td>
                            <td style="padding: 15px;"><?= $item['quantity'] ?></td>
                            <td style="padding: 15px;">RM<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="background: #fdfdfd; padding: 25px; border: 1px solid #ddd; border-radius: 8px; height: fit-content;">
                <h4 style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Order Summary</h4>
                <p style="margin-bottom: 8px;"><strong>Status:</strong> <span style="color: #28a745; font-weight: bold;"><?= $order['order_status'] ?></span></p>
                <p style="margin-bottom: 8px;"><strong>Date:</strong> <?= date('d M Y', strtotime($order['order_date'])) ?></p>
                <hr style="margin: 15px 0;">
                <p style="display: flex; justify-content: space-between; margin-bottom: 8px;"><span>Subtotal:</span> <span>RM<?= number_format($subtotal, 2) ?></span></p>
                <?php if ($discount > 0.01): ?>
                    <p style="display: flex; justify-content: space-between; color: #dc3545; margin-bottom: 8px;"><span>Discount:</span> <span>RM-<?= number_format($discount, 2) ?></span></p>
                <?php endif; ?>
                <h3 style="display: flex; justify-content: space-between; color: #2c3e50; margin-top: 15px;"><span>Grand Total:</span> <span>RM<?= number_format($order['total_amount'], 2) ?></span></h3>
                <hr style="margin: 20px 0;">
                <h4 style="margin-bottom: 10px;">Shipping Address:</h4>
                <p style="font-size: 0.95rem; color: #555; line-height: 1.5;"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
            </div>
        </div>
    </main>

    <div id="footer-placeholder"></div>
    <script>
        fetch('footer.html').then(r => r.text()).then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
        $(document).ready(function() { $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); }); });
    </script>
</body>
</html>