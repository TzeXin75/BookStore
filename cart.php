<?php
require_once 'config/db_connect.php';
require_once 'db.php';

$user_id = 1; // HARDCODED
$total_price = 0;

// 1. Fetch Cart Items from Database (JOIN with Books to get title/price)
//PUT BACK THE IMAGE LINK HERE , i removed it since i dont have the images
$sql = "SELECT c.quantity, b.id, b.title, b.price 
        FROM cart c 
        JOIN book b ON c.book_id = b.id 
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
    
    <div class="cart-container">
        <h2>Your Shopping Cart</h2>
        
        <?php if (count($cart_items) > 0): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total_price += $subtotal;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <a href="remove_from_cart.php?id=<?php echo $item['id']; ?>" class="remove-link">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="cart-summary">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 class="total-amount">Total: $<?php echo number_format($total_price, 2); ?></h3>
                    </div>
                    <div class="cart-actions">
                        <a href="index.php" class="btn-secondary">Continue Shopping</a>
                        <a href="checkout.php" class="btn-primary">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <p style="font-size: 1.2rem; color: #666; margin-bottom: 1.5rem;">Your cart is empty.</p>
                <a href="index.php" class="btn-primary">Browse Books</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div id="footer-placeholder"></div>
    <script>
    fetch('footer.html')
    .then(r => r.text())
    .then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
