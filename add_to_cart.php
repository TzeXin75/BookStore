<?php

session_start();
require_once 'config/db_connect.php';

if ($_SESSION['user_role'] !== 'member') {
    header("Location: login.php");
    exit();
}


if (isset($_GET['id'])) {
    $book_id = $_GET['id'];
    $user_id = 1; // HARDCODED (Change to $_SESSION['user_id'] later)

    // 1. Check if this book is already in the DB cart for this user
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$user_id, $book_id]);
    $existing_item = $stmt->fetch();

    if ($existing_item) {
        // 2. If yes, UPDATE quantity (+1)
        $update = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?");
        $update->execute([$existing_item['cart_id']]);
    } else {
        // 3. If no, INSERT new row
        $insert = $pdo->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, 1)");
        $insert->execute([$user_id, $book_id]);
    }
}

//redirect
header("Location: cart.php");
exit();
?>