<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
    $user_id = 1; // HARDCODED

    // 1. Verify ownership and status
    $check_sql = "SELECT order_status FROM orders WHERE order_id = ? AND user_id = ?";
    $stmt = $pdo->prepare($check_sql);
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if ($order && $order['order_status'] == 'Pending') {
        
        // 2. RESTORE STOCK
        // Get all items in this order
        $stmt_items = $pdo->prepare("SELECT book_id, quantity FROM order_details WHERE order_id = ?");
        $stmt_items->execute([$order_id]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        // Add quantity back to books table
        $update_stock = $pdo->prepare("UPDATE book SET stock = stock + ? WHERE book_id = ?");
        foreach ($items as $item) {
            $update_stock->execute([$item['quantity'], $item['book_id']]);
        }

        // 3. Set status to Cancelled
        $update_sql = "UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ?";
        $pdo->prepare($update_sql)->execute([$order_id]);
    }
}

header("Location: my_orders.php");
exit();
?>