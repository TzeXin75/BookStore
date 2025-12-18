<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';
ini_set('display_errors', 1); error_reporting(E_ALL);

// Check if form was submitted
if (!isset($_POST['place_order'])) {
    header("Location: index.php");
    exit();
}

$user_id = 1; // HARDCODED

// 1. Fetch Cart Items FROM DATABASE
$stmt = $pdo->prepare("SELECT c.quantity, b.* FROM cart c JOIN book b ON c.book_id = b.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$db_cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($db_cart_items) == 0) {
     die("Error: Your cart is empty.");
}

$full_name = $_POST['full_name'];
$address = $_POST['address'];
$pay_method = $_POST['payment_method'];
$pay_ref = $_POST['payment_ref'];
$total_amount = 0;
$cart_items = []; 

// 2. Process Items & Check Stock
foreach ($db_cart_items as $book) {
    $qty = $book['quantity'];
    
    if ($book['stock'] < $qty) {
        die("Error: Not enough stock for " . $book['title']);
    }
    $total_amount += $book['price'] * $qty;
    
    $cart_items[] = [
        'id' => $book['id'],
        'price' => $book['price'],
        'quantity' => $qty
    ];
}

// 3. Apply Voucher
if (isset($_SESSION['discount_amount'])) {
    $total_amount = $total_amount - $_SESSION['discount_amount'];
    if ($total_amount < 0) $total_amount = 0;
}

try {
    $pdo->beginTransaction();

    // Insert Order
    $sql_order = "INSERT INTO orders (user_id, total_amount, shipping_address, order_status) VALUES (?, ?, ?, 'Pending')";
    $stmt = $pdo->prepare($sql_order);
    $stmt->execute([$user_id, $total_amount, $address]);
    $order_id = $pdo->lastInsertId();

    // Insert Details & Update Stock
    $sql_detail = "INSERT INTO order_details (order_id, book_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
    $sql_stock  = "UPDATE book SET stock = stock - ? WHERE id = ?";
    $stmt_detail = $pdo->prepare($sql_detail);
    $stmt_stock  = $pdo->prepare($sql_stock);

    foreach ($cart_items as $item) {
        $stmt_detail->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        $stmt_stock->execute([$item['quantity'], $item['id']]);
    }

    // Insert Payment
    $sql_pay = "INSERT INTO payments (order_id, payment_method, transaction_ref, amount, status) VALUES (?, ?, ?, ?, 'Success')";
    $stmt_pay = $pdo->prepare($sql_pay);
    $stmt_pay->execute([$order_id, $pay_method, $pay_ref, $total_amount]);

    // 4. CLEAR THE DATABASE CART (Crucial Step)
    $clear_cart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear_cart->execute([$user_id]);

    $pdo->commit();

    // Clean Session (Vouchers)
    if (isset($_SESSION['discount_amount'])) unset($_SESSION['discount_amount']);
    if (isset($_SESSION['voucher_code'])) unset($_SESSION['voucher_code']);

    // Display Success
    include 'includes/header.php';
    ?>
    <div style="text-align: center; padding: 50px;">
        <h1 style="color: #28a745;">Order Placed Successfully!</h1>
        <p>Your Order ID is: <strong>#<?php echo $order_id; ?></strong></p>
        <div style="margin-top: 30px;">
            <a href="my_orders.php" class="btn" style="background-color: #007bff; margin-right: 10px;">View My Orders</a>
            <a href="index.php" class="btn" style="background-color: #6c757d;">Continue Shopping</a>
        </div>
    </div>
    <?php
    include 'includes/footer.php';

} catch (Exception $e) {
    $pdo->rollBack();
    die("Failed to place order: " . $e->getMessage());
}
?>