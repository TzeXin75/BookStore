<?php
// Start session to access logged-in user data
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once 'config/db_connect.php';

// Security: Redirect to login if not a member
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'member') {
    header("Location: login.php");
    exit();
}

// FIX 1: Use the actual logged-in user ID
$user_id = $_SESSION['user_id']; 
$total_price = 0;

// FIX 2: Changed 'c.book_id' to 'c.id' to match your database structure
$sql = "SELECT c.quantity, b.id, b.title, b.price 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <?php include 'head.php'; ?>
    
    <div class="cart-container" style="max-width: 1000px; margin: 3rem auto; padding: 20px; min-height: 60vh;">
        <h2>Your Shopping Cart</h2>
        
        <?php if (count($cart_items) > 0): ?>
            <table class="cart-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #f4f4f4; text-align: left;">
                        <th style="padding: 12px;">Book Title</th>
                        <th style="padding: 12px;">Price</th>
                        <th style="padding: 12px;">Quantity</th>
                        <th style="padding: 12px;">Subtotal</th>
                        <th style="padding: 12px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total_price += $subtotal;
                    ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px;"><?php echo htmlspecialchars($item['title']); ?></td>
                            <td style="padding: 12px;">$<?php echo number_format($item['price'], 2); ?></td>
                            <td style="padding: 12px;"><?php echo $item['quantity']; ?></td>
                            <td style="padding: 12px;">$<?php echo number_format($subtotal, 2); ?></td>
                            <td style="padding: 12px;">
                                <a href="remove_from_cart.php?id=<?php echo $item['id']; ?>" style="color: #d9534f; text-decoration: none; font-weight: bold;">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="cart-summary" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 class="total-amount" style="margin: 0; font-size: 1.5rem; color: #28a745;">Total: $<?php echo number_format($total_price, 2); ?></h3>
                    </div>
                    <div class="cart-actions">
                        <a href="index.php" style="padding: 10px 20px; text-decoration: none; color: #555; background: #eee; border-radius: 4px; margin-right: 10px;">Continue Shopping</a>
                        <a href="checkout.php" style="padding: 10px 20px; text-decoration: none; color: white; background: #28a745; border-radius: 4px;">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart" style="text-align: center; padding: 50px;">
                <p style="font-size: 1.2rem; color: #666; margin-bottom: 1.5rem;">Your cart is empty.</p>
                <a href="index.php" style="padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px;">Browse Books</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div id="footer-placeholder"></div>
    <script>
    fetch('footer.html')
    .then(r => r.text())
    .then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
    </script>
</body>
</html>