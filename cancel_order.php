<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

//check whther you are admin , if no then no access oh
$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

if (!$is_admin) {
    die("Unauthorized: Only admins can cancel orders.");
}

if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);

    try {
        $pdo->beginTransaction();

        // 1. Get current status
        $stmt = $pdo->prepare("SELECT order_status FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $status = $stmt->fetchColumn();

        // Only cancel if it's not already cancelled
        if ($status && $status !== 'Cancelled') {
            
            // 2. Restore Stock (Using your SQL names: table 'order_details', column 'id')
            $stmt_items = $pdo->prepare("SELECT id, quantity FROM order_details WHERE order_id = ?");
            $stmt_items->execute([$order_id]);
            $items = $stmt_items->fetchAll();

            $upd_stock = $pdo->prepare("UPDATE book SET stock = stock + ? WHERE id = ?");
            foreach ($items as $item) {
                $upd_stock->execute([$item['quantity'], $item['id']]);
            }

            // 3. Update Order Status
            $update_order = $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ?");
            $update_order->execute([$order_id]);

            $pdo->commit();
            $_SESSION['admin_msg'] = "Order #$order_id cancelled and stock restored.";
        } else {
            $pdo->rollBack();
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        die("Error: " . $e->getMessage());
    }
}

// Redirect back to the Archive list
header("Location: cancelled_order.php");
exit();