<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['place_order'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch Cart Items (Note: JOIN uses 'c.id' to match your database structure)
$stmt = $pdo->prepare("SELECT c.quantity, b.id, b.title, b.price 
                       FROM cart c 
                       JOIN book b ON c.id = b.id 
                       WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$db_cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($db_cart_items) == 0) {
    header("Location: cart.php");
    exit();
}

$address = $_POST['address'];
$pay_method = $_POST['payment_method'];
$pay_ref = $_POST['payment_ref'];
$total_amount = 0;
$order_items = []; 

foreach ($db_cart_items as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $total_amount += $subtotal;
    
    $order_items[] = [
        'id' => $item['id'],
        'price' => $item['price'],
        'quantity' => $item['quantity']
    ];
}

if (isset($_SESSION['discount_amount'])) {
    $total_amount = max(0, $total_amount - $_SESSION['discount_amount']);
}

try {
    $pdo->beginTransaction();

    $sql_order = "INSERT INTO orders (user_id, total_amount, shipping_address, order_status) VALUES (?, ?, ?, 'Pending')";
    $stmt = $pdo->prepare($sql_order);
    $stmt->execute([$user_id, $total_amount, $address]);
    $order_id = $pdo->lastInsertId();

   $sql_detail = "INSERT INTO order_details (order_id, id, quantity, unit_price) VALUES (?, ?, ?, ?)";
    $stmt_detail = $pdo->prepare($sql_detail);

    foreach ($order_items as $item) {
        $stmt_detail->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        // Note: Stock update is handled at the Cart stage to support instant deduction
    }

    $sql_pay = "INSERT INTO payments (order_id, payment_method, transaction_ref, amount, status) VALUES (?, ?, ?, ?, 'Success')";
    $stmt_pay = $pdo->prepare($sql_pay);
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
        <p style="font-size: 1.1rem; color: #7f8c8d;">Your order <strong>#<?php echo $order_id; ?></strong> has been placed.</p>
        <div style="margin-top: 35px;">
            <a href="my_orders.php" style="background: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin-right: 10px;">View My Orders</a>
            <a href="index.php" style="background: #95a5a6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;">Return to Store</a>
        </div>
    </div>
    <?php
    include 'includes/footer.php';

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error processing order: " . $e->getMessage());
}