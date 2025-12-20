<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $book_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Find how many were in the cart
        $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND id = ?");
        $stmt->execute([$user_id, $book_id]);
        $item = $stmt->fetch();

        if ($item) {
            // 2. RETURN stock to the library
            $return_stock = $pdo->prepare("UPDATE book SET stock = stock + ? WHERE id = ?");
            $return_stock->execute([$item['quantity'], $book_id]);

            // 3. Delete from cart
            $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND id = ?")
                ->execute([$user_id, $book_id]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}
header("Location: cart.php");
exit();