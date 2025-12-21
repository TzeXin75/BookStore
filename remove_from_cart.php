<?php
session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fixed: Check for both 'id' or 'cart_id' to be safe
$cart_id = $_GET['id'] ?? $_GET['cart_id'] ?? null;
$user_id = $_SESSION['user_id'];

if ($cart_id) {
    try {
        $pdo->beginTransaction();

        // Delete the item from the cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}

// Go back to the cart page
header("Location: cart.php");
exit();