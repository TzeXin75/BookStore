<?php
require_once 'config/db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = 1; // HARDCODED

// 1. Get Cart Items from DB
$stmt = $pdo->prepare("SELECT c.quantity, b.price FROM cart c JOIN book b ON c.book_id = b.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Redirect if empty
if (count($cart_items) == 0) {
    header("Location: index.php");
    exit();
}

// 2. Calculate Base Total
$base_total = 0;
foreach ($cart_items as $item) {
    $base_total += $item['price'] * $item['quantity'];
}

// 3. Handle Voucher Form Submission
$msg = "";
$msg_type = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['voucher_code'])) {
    $code = trim($_POST['voucher_code']);
    
    // Check if voucher exists
    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND status = 'Active' AND expiry_date >= CURDATE()");
    $stmt->execute([$code]);
    $voucher = $stmt->fetch();

    if ($voucher) {
        $_SESSION['discount_amount'] = $voucher['discount_amount'];
        $_SESSION['voucher_code'] = $code; 
        $msg = "Voucher Applied Successfully!";
        $msg_type = "success";
    } else {
        $msg = "Invalid or Expired Voucher Code.";
        $msg_type = "error";
    }
}

// 4. Calculate Final Total
$discount = isset($_SESSION['discount_amount']) ? $_SESSION['discount_amount'] : 0;
$final_total = $base_total - $discount;
if ($final_total < 0) $final_total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Bookstore</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

</head>
<body>
    
    <main>
        <h2 align = 'center'>Checkout</h2>
        <br>
        <?php if ($msg != ""): ?>
            <div class="message <?php echo $msg_type; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            <!-- Left Column: Order Summary -->
            <div class="order-summary">
                <h3>Order Summary</h3>
                <p class="order-item">Total Items: <?php echo count($cart_items); ?></p>
                <p class="order-item subtotal">Subtotal: $<?php echo number_format($base_total, 2); ?></p>
                <?php if ($discount > 0): ?>
                    <p class="order-item discount">
                        Discount: -$<?php echo number_format($discount, 2); ?>
                    </p>
                <?php endif; ?>
                
                <div class="total-section">
                    <h2 class="total-price">Total to Pay: $<?php echo number_format($final_total, 2); ?></h2>
                </div>
                
                <div class="voucher-section">
                    <p class="form-label">Have a voucher?</p>
                    <form action="checkout.php" method="POST" class="voucher-form">
                        <input type="text" name="voucher_code" placeholder="Enter Code" required class="voucher-input">
                        <button type="submit" class="voucher-btn">Apply</button>
                    </form>
                </div>
            </div>
            
            <!-- Right Column: Shipping & Payment -->
            <div class="shipping-payment">
                <form action="place_order.php" method="POST">
                    <h3>Shipping Details</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Full Name:</label>
                        <input type="text" name="full_name" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Shipping Address:</label>
                        <textarea name="address" required class="form-textarea"></textarea>
                    </div>
                    
                    <div class="payment-section">
                        <h3>Payment Details</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Payment Method:</label>
                            <div class="payment-method">
                                <input type="radio" id="credit_card" name="payment_method" value="Credit Card" checked>
                                <label for="credit_card">Credit Card</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Card / Account Number:</label>
                            <input type="text" name="payment_ref" placeholder="0000-0000-0000-0000" required class="form-input">
                        </div>
                        
                        <button type="submit" name="place_order" class="confirm-btn">
                            Confirm & Pay
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
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