<?php
require_once 'config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) { die("Order not found."); }

// Using 'od.id' based on previous database error
$stmt_items = $pdo->prepare("SELECT od.*, b.title FROM order_details od JOIN book b ON od.id = b.id WHERE od.order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

include 'head.php'; 
?>

<div style="max-width: 700px; margin: 50px auto; font-family: sans-serif;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border: 1px solid #eee;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin:0;">Order #<?php echo $order['order_id']; ?></h2>
            <a href="generate_receipt.php?id=<?php echo $order['order_id']; ?>" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Download Receipt (PDF)</a>
        </div>

        <div style="margin-bottom: 30px; color: #555;">
            <p><strong>Status:</strong> <span style="color: #007bff; font-weight: bold;"><?php echo $order['order_status']; ?></span></p>
            <p><strong>Date:</strong> <?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
            <p><strong>Shipping Address:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="text-align: left; padding: 12px; border-bottom: 2px solid #eee;">Book Title</th>
                    <th style="text-align: center; padding: 12px; border-bottom: 2px solid #eee;">Qty</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($item['title']); ?></td>
                    <td style="text-align: center; padding: 12px; border-bottom: 1px solid #eee;"><?php echo $item['quantity']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; text-align: right;">
            <p style="font-size: 1.2rem; font-weight: bold;">Grand Total: <span style="color: #28a745;">$<?php echo number_format($order['total_amount'], 2); ?></span></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>