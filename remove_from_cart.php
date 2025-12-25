<?php
//start session and connect to database
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';


if (isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: login.php");
    exit();
}

$cart_id = $_GET['id'] ?? $_GET['cart_id'] ?? null;

if ($cart_id) {
    try {
        $pdo->beginTransaction();

        // Strict delete: must match both cart_id and the logged-in user
        $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        die("Error: " . $e->getMessage());
    }
}

    // After deletion (or if no cart_id provided) redirect back to cart page
    header("Location: cart.php");
    exit();