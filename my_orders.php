<?php
require_once 'config/db_connect.php';
include 'head.php';

if ($_SESSION['user_role'] !== 'member') {
    header("Location: login.php");
    exit();
}

// HARDCODED User ID (Change to $_SESSION['user_id'] later)
$user_id = 1; 

// Fetch orders for this specific user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>My Order History</h2>

<table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f2f2f2;">
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
                    <td>#<?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    
                    <!-- Status Column with Color -->
                    <td>
                        <?php 
                        $status = $order['order_status'];
                        $color = 'black';
                        if ($status == 'Pending') $color = 'orange';
                        if ($status == 'Shipped') $color = 'blue';
                        if ($status == 'Completed') $color = 'green';
                        if ($status == 'Cancelled') $color = 'red';
                        ?>
                        <span style="color: <?php echo $color; ?>; font-weight: bold;">
                            <?php echo $status; ?>
                        </span>
                    </td>
                    
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    
                    <!-- ACTION COLUMN (View + Cancel) -->
                    <td>
                        <!-- 1. View Details Button -->
                        <a href="order_details.php?id=<?php echo $order['order_id']; ?>" 
                           class="btn" 
                           style="padding: 5px 10px; font-size: 0.9em; margin-right: 5px;">
                           View Details
                        </a>

                        <!-- 2. Cancel Button (Only shows if status is Pending) -->
                        <?php if ($status == 'Pending'): ?>
                            <a href="cancel_order.php?id=<?php echo $order['order_id']; ?>" 
                               onclick="return confirm('Are you sure you want to cancel this order?');" 
                               style="color: white; background-color: #d9534f; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.9em;">
                               Cancel
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">You have no orders yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>