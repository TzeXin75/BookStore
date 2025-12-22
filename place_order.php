<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

if (isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
    $_user = $_SESSION['user']; 
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $_user = ['username' => 'Member']; 
} else {
    header("Location: index.php"); exit();
}

if (!isset($_POST['place_order'])) { header("Location: cart.php"); exit(); }

$address = $_POST['address'];
$pay_method = $_POST['payment_method'];
$pay_ref = $_POST['payment_ref'] ?? 'N/A';

$stmt = $pdo->prepare("SELECT c.quantity, b.id, b.price FROM cart c JOIN book b ON c.id = b.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($cart_items) == 0) { header("Location: cart.php"); exit(); }

$total = 0;
foreach ($cart_items as $item) { $total += ($item['price'] * $item['quantity']); }
if (isset($_SESSION['discount_amount'])) { $total = max(0, $total - $_SESSION['discount_amount']); }

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, order_status) VALUES (?, ?, ?, 'Pending')");
    $stmt->execute([$user_id, $total, $address]);
    $order_id = $pdo->lastInsertId();

    $stmt_detail = $pdo->prepare("INSERT INTO order_details (order_id, id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    $stmt_stock = $pdo->prepare("UPDATE book SET stock = stock - ? WHERE id = ?");

    // FIXED: Directly processing cart results to ensure all books are saved
    foreach ($cart_items as $item) {
        $stmt_detail->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        $stmt_stock->execute([$item['quantity'], $item['id']]);
    }

    $stmt_pay = $pdo->prepare("INSERT INTO payments (order_id, payment_method, transaction_ref, amount, status) VALUES (?, ?, ?, ?, 'Success')");
    $stmt_pay->execute([$order_id, $pay_method, $pay_ref, $total]);

    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
    $pdo->commit();
    unset($_SESSION['discount_amount'], $_SESSION['voucher_code']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Success - Bookstore</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .fa-search::before { content: "🔍"; font-style: normal; }
        .fa-shopping-cart::before { content: "Cart"; font-family: sans-serif; font-size: 0.8em; }
        .fa-history::before { content: "Orders"; font-family: sans-serif; font-size: 0.8em; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main style="text-align: center; padding: 100px 20px; min-height: 60vh;">
        <h1 style="color: #28a745;">Payment Successful!</h1>
        <p>Order <strong>#<?= $order_id ?></strong> has been processed successfully.</p>
        <div style="margin-top: 40px;">
            <a href="my_orders.php" style="background: #3498db; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">My Orders</a>
            <a href="index.php" style="background: #95a5a6; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">Return Home</a>
        </div>
    </main>
    <div id="footer-placeholder"></div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        fetch('footer.html').then(r => r.text()).then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
        $(document).ready(function() { $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); }); });
    </script>
</body>
</html>
<?php
} catch (Exception $e) { $pdo->rollBack(); die("Error: " . $e->getMessage()); }