 <?php
session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

if (isset($_GET['id'])) {
    $book_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Check current stock
        $stmt = $pdo->prepare("SELECT stock FROM book WHERE id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();

        if (!$book || $book['stock'] <= 0) {
            die("Error: Out of stock.");
        }

        // 2. DEDUCT stock immediately
        $update_stock = $pdo->prepare("UPDATE book SET stock = stock - 1 WHERE id = ?");
        $update_stock->execute([$book_id]);

        // 3. Add to cart (using 'id' column as per your DB error)
        $check_cart = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND id = ?");
        $check_cart->execute([$user_id, $book_id]);
        $exists = $check_cart->fetch();

        if ($exists) {
    $update_cart = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?");
    $update_cart->execute([$exists['cart_id']]);
} else {
    $insert_cart = $pdo->prepare("INSERT INTO cart (user_id, id, quantity) VALUES (?, ?, 1)");
    $insert_cart->execute([$user_id, $book_id]);
}

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("System Error: " . $e->getMessage());
    }
}
header("Location: cart.php");
exit();