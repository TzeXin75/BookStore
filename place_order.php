<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';


if (isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: index.php"); exit();
}

if (!isset($_POST['place_order'])) {
    header("Location: cart.php"); exit();
}

$address = $_POST['address'];
$pay_method = $_POST['payment_method'];
$pay_ref = $_POST['payment_ref'] ?? 'N/A';
$total_amount = 0;
$order_items = []; 


$stmt = $pdo->prepare("SELECT c.quantity, b.id, b.price FROM cart c JOIN book b ON c.id = b.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$db_cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($db_cart_items) == 0) { header("Location: cart.php"); exit(); }

foreach ($db_cart_items as $item) {
    $total_amount += ($item['price'] * $item['quantity']);
    $order_items[] = ['id' => $item['id'], 'price' => $item['price'], 'quantity' => $item['quantity']];
}

if (isset($_SESSION['discount_amount'])) {
    $total_amount = max(0, $total_amount - $_SESSION['discount_amount']);
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, order_status) VALUES (?, ?, ?, 'Pending')");
    $stmt->execute([$user_id, $total_amount, $address]);
    $order_id = $pdo->lastInsertId();

    $stmt_detail = $pdo->prepare("INSERT INTO order_details (order_id, id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    $stmt_stock = $pdo->prepare("UPDATE book SET stock = stock - ? WHERE id = ?");

    foreach ($order_items as $item) {
        $stmt_detail->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        $stmt_stock->execute([$item['quantity'], $item['id']]);
    }

    $stmt_pay = $pdo->prepare("INSERT INTO payments (order_id, payment_method, transaction_ref, amount, status) VALUES (?, ?, ?, ?, 'Success')");
    $stmt_pay->execute([$order_id, $pay_method, $pay_ref, $total_amount]);

    $clear_cart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear_cart->execute([$user_id]);

    $pdo->commit();

    unset($_SESSION['discount_amount']);
    unset($_SESSION['voucher_code']);

    include 'includes/header.php';
    ?>
    <div style="text-align: center; padding: 60px 20px; min-height: 50vh;">
        <i class="fa-solid fa-circle-check" style="font-size: 4rem; color: #28a745; margin-bottom: 20px;"></i>
        <h1 style="color: #2c3e50;">Payment Successful!</h1>
        <p>Your order <strong>#<?php echo $order_id; ?></strong> has been successfully processed.</p>
        <div style="margin-top: 35px;">
            <a href="my_orders.php" style="background: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin-right: 10px;">View My Orders</a>
            <a href="index.php" style="background: #95a5a6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;">Return to Store</a>
        </div>
    </div>
    <?php
    include 'includes/footer.php';

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    die("Error processing order: " . $e->getMessage());
}