<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

// check if you are admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// get all cancelled orders from database
$sql = "SELECT o.*, u.username 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_status = 'Cancelled' 
        ORDER BY o.order_date DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: Cancelled Orders Archive</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .orders-container { max-width: 1200px; margin: 3rem auto; padding: 0 1rem; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-title { font-size: 1.8rem; color: #2c3e50; font-weight: bold; margin: 0; }
        .btn-back { background-color: #7f8c8d; color: white; padding: 10px 18px; text-decoration: none; border-radius: 5px; font-weight: bold; transition: 0.3s; }
        .btn-back:hover { background-color: #2c3e50; }
        .orders-card { background: #fff; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; }
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table th, .orders-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .orders-table th { background-color: #95a5a6; color: white; text-transform: uppercase; font-size: 0.85rem; }
        .status-cancelled { color: #c0392b; font-weight: bold; }
        .btn-details { display: inline-block; padding: 6px 14px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem; }
        .btn-details:hover { background-color: #2980b9; }
    </style>
</head>
<body>

<div class="orders-container">
    <?php if(isset($_SESSION['admin_msg'])): ?>
        <div style="background:#d4edda; color:#155724; padding:15px; border-radius:5px; margin-bottom:20px; border:1px solid #c3e6cb;">
            <?= $_SESSION['admin_msg']; unset($_SESSION['admin_msg']); ?>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <h2 class="page-title">Cancelled Orders Archive</h2>
        <a href="admin.php?page=manage orders" class="btn-back">&larr; Back to Active Orders</a>
    </div>

    <div class="orders-card">
        <table class="orders-table">
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
                            <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></td>
                            <td><span class="status-cancelled">Cancelled</span></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <a href="admin_order_details.php?id=<?php echo $order['order_id']; ?>" class="btn-details">Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #7f8c8d;">
                            No cancelled orders found in the archive.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>