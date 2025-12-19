<?php
require_once 'config/db_connect.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch ALL orders, ordered by newest first
$sql = "SELECT o.*, u.username 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: Order Management</title>
    <link rel="stylesheet" href="style.css"> <!-- Keep your existing global styles -->
    <style>
        .orders-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 1rem;
        }

        .page-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }

        .orders-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table thead {
            background-color: #2c3e50;
            color: white;
        }

        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .orders-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .orders-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .status-pending {
            color: #e67e22; /* Orange */
            font-weight: bold;
        }

        .status-completed {
            color: #27ae60; /* Green */
            font-weight: bold;
        }

        .status-other {
            color: #2c3e50;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .orders-table, .orders-table thead, .orders-table tbody, 
            .orders-table th, .orders-table td, .orders-table tr {
                display: block;
            }

            .orders-table thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            .orders-table tr {
                border: 1px solid #ccc;
                border-radius: 8px;
                margin-bottom: 15px;
                padding: 10px;
                background: #fff;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            .orders-table td {
                border: none;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }

            .orders-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 45%;
                font-weight: bold;
                text-align: left;
                color: #2c3e50;
            }
        }
    </style>
</head>
<body>

<div class="orders-container">
    <h2 class="page-title">Admin: Order Management</h2>

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
                            <td data-label="Order ID">#<?php echo $order['order_id']; ?></td>
                            <td data-label="Customer"><?php echo htmlspecialchars($order['username']); ?></td>
                            <td data-label="Date"><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></td>
                            
                            <td data-label="Status">
                                <?php 
                                $status = $order['order_status'];
                                $class = ($status == 'Pending') ? 'status-pending' : 
                                        (($status == 'Completed') ? 'status-completed' : 'status-other');
                                ?>
                                <span class="<?php echo $class; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                            
                            <td data-label="Total">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            
                            <td data-label="Action">
                                <a href="admin_order_details.php?id=<?php echo $order['order_id']; ?>" class="btn">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #7f8c8d;">
                            No orders found.
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