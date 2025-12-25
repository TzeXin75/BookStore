<?php
//start session and connect to database
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

// Ensure this script is reached via the checkout POST
if (!isset($_POST['place_order'])) { header("Location: cart.php"); exit(); }

$address = $_POST['address'];
$full_name = $_POST['full_name'];
$pay_method = $_POST['payment_method'];
$pay_ref = $_POST['payment_ref'] ?? 'N/A';

// Load cart items for this user
$stmt = $pdo->prepare("SELECT c.quantity, b.id, b.price FROM cart c JOIN book b ON c.id = b.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($cart_items) == 0) { header("Location: cart.php"); exit(); }

// Calculate total and apply any active discount stored in session
$total = 0;
foreach ($cart_items as $item) { $total += ($item['price'] * $item['quantity']); }
if (isset($_SESSION['discount_amount'])) { $total = max(0, $total - $_SESSION['discount_amount']); }

// Begin database transaction to safely create order, details and payment record
// All DB changes are committed together; on failure we roll back.

try {
    $pdo->beginTransaction();
    // Insert core order row (Pending by default)
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, order_status) VALUES (?, ?, ?, 'Pending')");
    $stmt->execute([$user_id, $total, $address]);
    $order_id = $pdo->lastInsertId();

    // Prepare statements for inserting order details and decrementing stock
    $stmt_detail = $pdo->prepare("INSERT INTO order_details (order_id, id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    $stmt_stock = $pdo->prepare("UPDATE book SET stock = stock - ? WHERE id = ?");

    // Insert each cart item into order_details and update stock accordingly
    foreach ($cart_items as $item) {
        $stmt_detail->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        $stmt_stock->execute([$item['quantity'], $item['id']]);
    }

    // Record payment; this example marks payment as Success and stores a simple amount
    $stmt_pay = $pdo->prepare("INSERT INTO payments (order_id, payment_method, transaction_ref, amount, status) VALUES (?, ?, ?, ?, 'Success')");
    // Change currency from $ to RM for display/storage consistency
    $stmt_pay->execute([$order_id, $pay_method, $pay_ref, str_replace('$', 'RM', $total)]);

    // If the user redeemed reward points, deduct them from the user's account now
    if (!empty($_SESSION['redeemed_points'])) {
        $redeem = (int)$_SESSION['redeemed_points'];
        $stmt_deduct = $pdo->prepare("UPDATE users SET reward_points = GREATEST(COALESCE(reward_points,0) - ?, 0) WHERE user_id = ?");
        $stmt_deduct->execute([$redeem, $user_id]);
        unset($_SESSION['redeemed_points']);
    }

    // Award reward points for this purchase: simple rule = 1 point per whole currency unit spent
    $points_awarded = (int)floor($total);
    if ($points_awarded > 0) {
        $stmt_points = $pdo->prepare("UPDATE users SET reward_points = COALESCE(reward_points,0) + ? WHERE user_id = ?");
        $stmt_points->execute([$points_awarded, $user_id]);
    }

    // Refresh user's reward points in session (best-effort)
    $stmt_get = $pdo->prepare("SELECT COALESCE(reward_points,0) FROM users WHERE user_id = ?");
    $stmt_get->execute([$user_id]);
    $new_points = $stmt_get->fetchColumn();
    if ($new_points !== false) {
        $_SESSION['user']['reward_points'] = (int)$new_points;
    }

    // Clear the user's cart now that order is placed
    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
    $pdo->commit();
    unset($_SESSION['discount_amount'], $_SESSION['voucher_code']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Success - SIX SEVEN BS</title>
    <link rel="stylesheet" href="style.css" />
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <main style="text-align: center; padding: 100px 20px; min-height: 60vh;">
        <h1 style="color: #28a745;">Payment Successful!</h1>
        <p>Order <strong>#<?= $order_id ?></strong> has been processed successfully.</p>
        <?php if (!empty($points_awarded) || isset($new_points)): ?>
            <p style="margin-top:12px;">You earned <strong><?= intval($points_awarded ?? 0) ?></strong> reward points. Your balance: <strong><?= intval($new_points ?? 0) ?></strong></p>
        <?php endif; ?>
        <div style="margin-top: 40px;">
            <a href="my_orders.php" style="background: #3498db; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">My Orders</a>
            <a href="index.php" style="background: #95a5a6; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">Return Home</a>
        </div>
    </main>
    <div id="footer-placeholder"></div>
    <script>
        fetch('footer.html').then(r => r.text()).then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
        $(document).ready(function() { $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); }); });
    </script>
</body>
</html>
<?php
} catch (Exception $e) { $pdo->rollBack(); die("Error: " . $e->getMessage()); }