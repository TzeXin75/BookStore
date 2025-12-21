<?php
session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$cart_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if ($cart_id) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}

header("Location: cart.php");
exit(); 