<?php
//start session and link databse
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

//check if user is logged in if not logged in the redirect to login page
if (isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: login.php"); exit();
}


//get total price and get items from cart 
$total_price = 0;
$stmt = $pdo->prepare("SELECT c.cart_id, c.quantity, b.id, b.title, b.price FROM cart c JOIN book b ON c.id = b.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Shopping Cart - Bookstore</title>
    <link rel="stylesheet" href="style.css" />
    <!-- Removed Font Awesome link for restricted code compliance -->
</head>
<body>
    <!--display header and nav links-->
    <?php include 'header.php'; ?>
    <main class="cart-container" style="max-width: 1000px; margin: 3rem auto; padding: 20px; min-height: 60vh;">
        <h2 style="margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">Your Shopping Cart</h2>

        <!--check if cart has items-->  
        <?php if (count($cart_items) > 0): ?>

        <!---display the items in cart--->
            <table class="cart-table" style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <thead>
                    <tr style="background: #2c3e50; color: white; text-align: left;"><th style="padding: 15px;">Book Title</th><th style="padding: 15px;">Price</th><th style="padding: 15px;">Qty</th><th style="padding: 15px;">Subtotal</th><th style="padding: 15px;">Action</th></tr></thead>
                <tbody>
                    <?php foreach ($cart_items as $item):
                        //calculate subtotal of each row and add to total price
                        $sub = $item['price'] * $item['quantity']; $total_price += $sub; ?>
                        <tr style="border-bottom: 1px solid #eee;"><td style="padding: 15px;"><?= htmlspecialchars($item['title']); ?></td><td style="padding: 15px;">RM<?= number_format($item['price'], 2); ?></td><td style="padding: 15px; font-weight: bold;"><?= $item['quantity']; ?></td><td style="padding: 15px;">RM<?= number_format($sub, 2); ?></td><td style="padding: 15px;"><a href="remove_from_cart.php?id=<?= $item['cart_id']; ?>" style="color: #d9534f; text-decoration: none; font-weight: bold;">[Remove]</a></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

                    <!--display final price, continue shopping, and checkout link-->
            <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="color: #28a745;">Grand Total: RM<?= number_format($total_price, 2); ?></h3>
                </div>
                <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
                    <a href="index.php" style="padding: 12px 25px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Continue Shopping</a>
                    <a href="checkout.php" style="padding: 12px 25px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Proceed to Checkout</a>
                </div>
            
        <?php else: ?>
        <!--display when cart is empty-->
            <div style="text-align: center; padding: 80px 20px; background: #f9f9f9; border-radius: 10px; border: 2px dashed #ddd;">
                <p style="font-size: 1.3rem; color: #666; margin-bottom: 25px;">Your shopping cart is empty.</p>
                <a href="index.php" style="padding: 15px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Browse Our Books</a>
            </div>
        <?php endif; ?>
    </main>

        <!--footer placeholder-->
    <div id="footer-placeholder"></div>
    

    <script>
    /*fetch and display global footer*/
    fetch('footer.html').then(r => r.text()).then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
    $(document).ready(function() { $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); }); });
    </script>
</body>
</html>