<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once 'config/db_connect.php';

// Allow any logged-in user (both members and regular customers)
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['user_id']; 
$total_price = 0;

$sql = "SELECT c.cart_id, c.quantity, c.id as book_id, b.id, b.title, b.price 
        FROM cart c 
        JOIN book b ON c.id = b.id 
        WHERE c.user_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <?php include 'head.php'; ?>
    
    <div class="cart-container" style="max-width: 1000px; margin: 3rem auto; padding: 20px; min-height: 60vh;">
        <h2 style="margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">Your Shopping Cart</h2>
        
        <?php if (count($cart_items) > 0): ?>
            <table class="cart-table" style="width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <thead>
                    <tr style="background: #2c3e50; color: white; text-align: left;">
                        <th style="padding: 15px;">Book Title</th>
                        <th style="padding: 15px;">Price</th>
                        <th style="padding: 15px;">Quantity</th>
                        <th style="padding: 15px;">Subtotal</th>
                        <th style="padding: 15px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total_price += $subtotal;
                    ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;"><?php echo htmlspecialchars($item['title']); ?></td>
                            <td style="padding: 15px;">$<?php echo number_format($item['price'], 2); ?></td>
                            <td style="padding: 15px; font-weight: bold;"><?php echo $item['quantity']; ?></td>
                            <td style="padding: 15px;">$<?php echo number_format($subtotal, 2); ?></td>
                            <td style="padding: 15px;">
                                <a href="remove_from_cart.php?id=<?php echo $item['cart_id']; ?>" style="color: #d9534f; text-decoration: none; font-weight: bold; font-size: 0.9em;"><i class="fa-solid fa-trash"></i> Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="cart-summary" style="margin-top: 30px; padding: 25px; background: #fdfdfd; border: 1px solid #eee; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; color: #28a745; font-size: 1.6rem;">Grand Total: $<?php echo number_format($total_price, 2); ?></h3>
                    <div>
                        <a href="index.php" style="padding: 12px 20px; text-decoration: none; color: #555; background: #eee; border-radius: 5px; margin-right: 10px; font-weight: 500;">Continue Shopping</a>
                        <a href="checkout.php" style="padding: 12px 25px; text-decoration: none; color: white; background: #28a745; border-radius: 5px; font-weight: bold; box-shadow: 0 3px 6px rgba(40,167,69,0.2);">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- FIXED: Better spacing to prevent button overlap -->
            <div class="empty-cart" style="text-align: center; padding: 80px 20px; background: #f9f9f9; border-radius: 10px; border: 2px dashed #ddd;">
                <i class="fa-solid fa-cart-shopping" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
                <p style="font-size: 1.3rem; color: #666; margin-bottom: 25px; display: block;">Your shopping cart is currently empty.</p>
                <a href="index.php" style="padding: 15px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Browse Our Books</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>