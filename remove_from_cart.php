<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_GET['id'])) {
    $book_id = $_GET['id'];
    $user_id = 1; // HARDCODED

    // Delete the specific item for this user
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$user_id, $book_id]);
}

header("Location: cart.php");
exit();
?>