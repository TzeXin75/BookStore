<?php
//start session and link database 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

//Check if user is logged in by looking for their ID in the session
if (isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {

    //if no user id is found then will redirect to login page
    header("Location: login.php"); 
    exit(); 
}

//get the book id and quantity from post or get
$book_id = $_POST['id'] ?? $_GET['id'] ?? null;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($book_id) {
    try {
        //prevent data corruption if the system crashed midway
        $pdo->beginTransaction();

        //fetch book stock from database
        $stmt = $pdo->prepare("SELECT stock FROM book WHERE id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();

       // If book not found in database then redirect to index
        if (!$book) {
            header("Location: index.php");
            exit();
        }

        // Check if the specific book is already in thecurrent cart
        $check_cart = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND id = ?");
        $check_cart->execute([$user_id, $book_id]);
        $exists = $check_cart->fetch();

        //calculate current quantity in cart and how many user want to add
        $current_in_cart = $exists ? intval($exists['quantity']) : 0;
        $new_total = $current_in_cart + $quantity;

        // Prevent adding more items into cart than available stock in database
        if ($new_total > $book['stock']) {
            $_SESSION['error_msg'] = "Warning: Cannot add more. You have $current_in_cart in cart. Total would exceed stock limit of " . $book['stock'] . ".";
            $pdo->rollBack();
            header("Location: cart.php");
            exit();
        }

        // if the book already in cart then increase quantity
        if ($exists) {
            $update_cart = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $update_cart->execute([$new_total, $exists['cart_id']]);
        } else {
            // if book not in cart then create new record
            $insert_cart = $pdo->prepare("INSERT INTO cart (user_id, id, quantity) VALUES (?, ?, ?)");
            $insert_cart->execute([$user_id, $book_id, $quantity]);
        }

        //finalize transaction and save to database
        $pdo->commit();
    
    } catch (Exception $e) {
       //if any error occur then undo all changes to prevent bad data
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        die("System Error: " . $e->getMessage());
    }
}
//redirect user to cart after action is complete
header("Location: cart.php");
exit();