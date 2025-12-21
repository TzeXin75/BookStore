<?php
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

$book_id = $_POST['id'] ?? $_GET['id'] ?? null;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($book_id) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT stock FROM book WHERE id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();

        if (!$book) {
            header("Location: index.php");
            exit();
        }

        // Check current cart
        $check_cart = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND id = ?");
        $check_cart->execute([$user_id, $book_id]);
        $exists = $check_cart->fetch();

        $current_in_cart = $exists ? intval($exists['quantity']) : 0;
        $new_total = $current_in_cart + $quantity;

        // Cumulative Check for book and cart
        if ($new_total > $book['stock']) {
            $_SESSION['error_msg'] = "Warning: Cannot add more. You have $current_in_cart in cart. Total would exceed stock limit of " . $book['stock'] . ".";
            $pdo->rollBack();
            header("Location: cart.php");
            exit();
        }

        if ($exists) {
            $update_cart = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $update_cart->execute([$new_total, $exists['cart_id']]);
        } else {
            $insert_cart = $pdo->prepare("INSERT INTO cart (user_id, id, quantity) VALUES (?, ?, ?)");
            $insert_cart->execute([$user_id, $book_id, $quantity]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        die("System Error: " . $e->getMessage());
    }
}
header("Location: cart.php");
exit();