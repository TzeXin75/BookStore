<?php
require_once 'config/db_connect.php';
include 'head.php';

session_start();

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


$order_id = $_GET['id'] ?? 0;

// 1. Handle Status Update Submission
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update_sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$new_status, $order_id]);
    
    echo "<p style='color:green; font-weight:bold;'>Status Updated to $new_status!</p>";
}

// 2. Fetch Order Info
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Fetch Items
$stmt_items = $pdo->prepare("SELECT od.*, b.title FROM order_details od JOIN book b ON od.book_id = b.id WHERE od.order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: Order Details #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="style.css"> <!-- Your global stylesheet -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .details-container {
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

        .details-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .info-section {
            margin-bottom: 2rem;
        }

        .info-section h3 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .info-section p {
            font-size: 1.1rem;
            color: #34495e;
            margin: 0.5rem 0;
        }

        .status-form {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #eee;
            margin-bottom: 2rem;
        }

        .status-form label {
            font-weight: bold;
            margin-right: 1rem;
            color: #2c3e50;
        }

        .status-form select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 1rem;
        }

        .btn {
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95rem;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .details-container {
                padding: 0 0.5rem;
            }
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            tr {
                border: 1px solid #ccc;
                border-radius: 8px;
                margin-bottom: 1rem;
                padding: 1rem;
            }
            td {
                border: none;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            td:before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 45%;
                font-weight: bold;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="details-container">
    <h2 class="page-title">Order Details #<?php echo $order['order_id']; ?></h2>

    <div class="details-card">
        <div class="info-section">
            <h3>Customer Information</h3>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
            <p><strong>Order Date:</strong> <?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
        </div>

        <?php if (isset($success_message)) echo $success_message; ?>

        <!-- Status Update Form -->
        <div class="status-form">
            <form method="POST">
                <label>Update Status:</label>
                <select name="status">
                    <option value="Pending" <?php if ($order['order_status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Paid" <?php if ($order['order_status'] == 'Paid') echo 'selected'; ?>>Paid</option>
                    <option value="Shipped" <?php if ($order['order_status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                    <option value="Completed" <?php if ($order['order_status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                    <option value="Cancelled" <?php if ($order['order_status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
                <button type="submit" name="update_status" class="btn">Update</button>
            </form>
        </div>

        <div class="info-section">
            <h3>Items Ordered</h3>
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td data-label="Book Title"><?php echo htmlspecialchars($item['title']); ?></td>
                            <td data-label="Quantity"><?php echo $item['quantity']; ?></td>
                            <td data-label="Unit Price">$<?php echo number_format($item['unit_price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>

        <a href="admin.php?page=manage%20orders"  class="back-link">← Back to Order List</a>
    </div>
</div>


</body>
</html>
