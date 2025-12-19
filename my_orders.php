<?php
require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'member') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? 1; 

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 20px; min-height: 60vh;">
    <h2 style="margin-bottom: 20px;">My Order History</h2>

    <table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #ddd;">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th>Order ID</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td style="text-align: center;">#<?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                        
                        <td style="text-align: center;">
                            <?php 
                            $status = $order['order_status'];
                            $color = 'black';
                            if ($status == 'Pending') $color = 'orange';
                            if ($status == 'Shipped') $color = 'blue';
                            if ($status == 'Completed') $color = 'green';
                            if ($status == 'Cancelled') $color = 'red';
                            ?>
                            <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                        
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        
                        <td style="text-align: center;">
                            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" 
                               style="background: #3498db; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 0.85em; display: inline-block;">
                               View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px; color: #666;">
                        You have no orders yet.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>