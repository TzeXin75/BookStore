<?php
require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch only Cancelled orders
$sql = "SELECT o.*, u.username 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_status = 'Cancelled' 
        ORDER BY o.order_date DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .orders-container { max-width: 1200px; margin: 1rem auto; padding: 0 1rem; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .back-btn { background-color: #34495e; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    .orders-card { background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    thead { background-color: #c0392b; color: white; }
    th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
    .btn-manage { background-color: #3498db; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 0.9rem; }
</style>

<div class="orders-container">
    <div class="page-header">
        <h2>Cancelled Order Archive</h2>
        <a href="admin.php?page=manage orders" class="back-btn">&larr; Back to Active Orders</a>
        <h2 class="page-title">Cancelled Orders Archive</h2>
        <a href="admin.php?page=manage orders" class="btn-back">&larr; Back to Active Orders</a>
    </div>

    <div class="orders-card">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></td>
                            <td><span style="color: #c0392b; font-weight: bold;">Cancelled</span></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <a href="admin.php?page=order_details&id=<?php echo $order['order_id']; ?>" class="btn-manage">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 40px; color: #7f8c8d;">No cancelled orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>