<?php
session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['user_id'])) {
    header("Location: login.php"); 
    exit(); 
}

$book_id = $_POST['id'] ?? $_GET['id'] ?? null;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
$user_id = $_SESSION['user']['user_id'];

if ($book_id) {
    try {
        $pdo->beginTransaction();

        // Check if book exists and has enough stock (but don't deduct yet!)
        $stmt = $pdo->prepare("SELECT stock FROM book WHERE id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$book || $book['stock'] < $quantity) {
            die("Error: Requested quantity exceeds available stock.");
        }

        // Add to cart database
        $check_cart = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND id = ?");
        $check_cart->execute([$user_id, $book_id]);
        $exists = $check_cart->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $update_cart = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
            $update_cart->execute([$quantity, $exists['cart_id']]);
        } else {
            $insert_cart = $pdo->prepare("INSERT INTO cart (user_id, id, quantity) VALUES (?, ?, ?)");
            $insert_cart->execute([$user_id, $book_id, $quantity]);
        }

        $pdo->commit();
        header("Location: cart.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("System Error: " . $e->getMessage());
    }
} else {
    // No book_id provided
    header("Location: index.php");
    exit();
}