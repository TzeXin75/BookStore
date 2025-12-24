<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

if (isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
    $user_role = $_SESSION['user']['user_role'] ?? 'member';
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 'member';
} else {
    header("Location: login.php"); exit();
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Orders - Bookstore</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .orders-table td { font-size: 1.1rem; padding: 18px 15px !important; }
        .orders-table th { font-size: 1rem; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main style="max-width: 1200px; margin: 40px auto; padding: 20px; min-height: 60vh;">
        <h2 style="margin-bottom: 20px;">My Order History</h2>
        <table class="orders-table" border="1" cellpadding="10" style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #ddd;">
            <thead><tr style="background-color: #f8f9fa;"><th>Order ID</th><th>Date</th><th>Status</th><th>Total</th><th>Action</th></tr></thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td style="text-align: center;">#<?= $order['order_id']; ?></td>
                            <td><?= date('d M Y', strtotime($order['order_date'])); ?></td>
                            <td style="text-align: center;">
                                <?php 
                                $status = $order['order_status'];
                                $color = match($status) { 'Pending' => 'orange', 'Shipped' => 'blue', 'Completed' => 'green', 'Cancelled' => 'red', default => 'black' };
                                ?>
                                <span style="color: <?= $color; ?>; font-weight: bold;"><?= $status; ?></span>
                            </td>
                            <td>RM<?= number_format($order['total_amount'], 2); ?></td>
                            <td style="text-align: center;"><a href="order_details.php?id=<?= $order['order_id']; ?>" style="background: #3498db; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 0.9em;">View Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: #666;">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
    <div id="footer-placeholder"></div>
    <script>
    fetch('footer.html').then(r => r.text()).then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
    $(document).ready(function() { $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); }); });
    </script>
</body>
</html>