<?php
// checkout.php
require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? 1; // Tailored for GitHub

// 1. Get Cart Items
// GITHUB FORMAT: Using 'book' (singular)
$stmt = $pdo->prepare("SELECT c.quantity, b.price FROM cart c JOIN book b ON c.book_id = b.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($cart_items) == 0) {
    header("Location: index.php");
    exit();
}

$base_total = 0;
foreach ($cart_items as $item) {
    $base_total += $item['price'] * $item['quantity'];
}

$msg = "";
$msg_type = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['voucher_code'])) {
    $code = trim($_POST['voucher_code']);
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
    
    <!-- MAP LIBRARY -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        .payment-option { margin-bottom: 10px; display: flex; align-items: center; gap: 10px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; transition: 0.2s; }
        .payment-option:hover { background-color: #f9f9f9; border-color: #007bff; }
        .payment-option input[type="radio"] { transform: scale(1.5); }
        
        #map { height: 250px; width: 100%; border-radius: 5px; margin-bottom: 10px; border: 2px solid #ddd; z-index: 1; }
        #qr-code-container { text-align: center; margin-bottom: 15px; display: none; padding: 10px; background: #f9f9f9; border-radius: 5px; border: 1px dashed #ccc; }
        
        .form-input, .form-textarea, .voucher-input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .voucher-btn, .confirm-btn { padding: 10px 15px; cursor: pointer; border: none; border-radius: 4px; color: white; }
        .voucher-btn { background: #555; }
        .confirm-btn { background: #28a745; width: 100%; font-size: 1.1em; }
    </style>
</head>
<body>
    
    <?php include 'includes/header.php'; ?>

    <main>
        <h2 align='center' style="margin-top: 20px;">Secure Checkout</h2>
        <br>
        <?php if ($msg != ""): ?>
            <div class="message <?php echo $msg_type; ?>" style="text-align:center; padding: 10px; margin-bottom: 20px; background: <?php echo ($msg_type=='success')?'#d4edda':'#f8d7da'; ?>; color: <?php echo ($msg_type=='success')?'#155724':'#721c24'; ?>;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            <!-- Left Column -->
            <div class="order-summary">
                <h3>Order Summary</h3>
                <p class="order-item">Total Items: <?php echo count($cart_items); ?></p>
                <p class="order-item subtotal">Subtotal: $<?php echo number_format($base_total, 2); ?></p>
                <?php if ($discount > 0): ?>
                    <p class="order-item discount" style="color: green;">
                        Discount: -$<?php echo number_format($discount, 2); ?>
                    </p>
                <?php endif; ?>
                <h2 class="total-price" style="color: #28a745;">Total to Pay: $<?php echo number_format($final_total, 2); ?></h2>
                
                <div class="voucher-section" style="margin-top: 20px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <p class="form-label">Have a voucher?</p>
                    <form action="checkout.php" method="POST" class="voucher-form" style="display: flex; gap: 5px;">
                        <input type="text" name="voucher_code" placeholder="Enter Code" required class="voucher-input" style="margin-bottom:0;">
                        <button type="submit" class="voucher-btn">Apply</button>
                    </form>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="shipping-payment">
                <form action="place_order.php" method="POST">
                    
                    <h3><i class="fa-solid fa-truck"></i> Shipping Details</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Full Name:</label>
                        <input type="text" name="full_name" required class="form-input">
                    </div>

                    <!-- Email Input Removed - Will use default profile email -->
                    
                    <div class="form-group">
                        <label class="form-label">Shipping Address (Type to search or Click map):</label>
                        <div id="map"></div>
                        <textarea name="address" id="addressBox" required class="form-textarea" rows="3" placeholder="e.g. KLCC, Kuala Lumpur"></textarea>
                    </div>
                    
                    <hr style="margin: 20px 0;">

                    <div class="payment-section">
                        <h3><i class="fa-solid fa-credit-card"></i> Payment Method</h3>
                        
                        <div class="payment-option" onclick="selectPayment('credit')">
                            <input type="radio" id="credit_card" name="payment_method" value="Credit Card" checked>
                            <label for="credit_card" style="cursor: pointer; width: 100%;"><strong>Credit / Debit Card</strong></label>
                            <i class="fa-brands fa-cc-visa fa-2x"></i>
                        </div>

                        <div class="payment-option" onclick="selectPayment('bank')">
                            <input type="radio" id="online_banking" name="payment_method" value="Online Banking">
                            <label for="online_banking" style="cursor: pointer; width: 100%;"><strong>Online Banking (FPX)</strong></label>
                            <i class="fa-solid fa-building-columns fa-2x"></i>
                        </div>

                        <div class="payment-option" onclick="selectPayment('ewallet')">
                            <input type="radio" id="ewallet" name="payment_method" value="E-Wallet">
                            <label for="ewallet" style="cursor: pointer; width: 100%;"><strong>E-Wallet (Touch 'n Go)</strong></label>
                            <i class="fa-solid fa-mobile-screen-button fa-2x"></i>
                        </div>
                        
                        <div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #eee; border-radius: 5px;">
                            <div id="qr-code-container">
                                <p style="font-weight: bold; margin-bottom: 5px;">Scan to Pay:</p>
                                <img id="qr-code-img" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=PAY<?php echo $final_total; ?>" alt="QR Code">
                                <p style="font-size: 0.9em; color: #555;">Total: RM <?php echo number_format($final_total, 2); ?></p>
                            </div>

                            <label class="form-label" id="ref-label">Card Number:</label>
                            <input type="text" name="payment_ref" id="payment-input" 
                                   placeholder="e.g. 1234 5678 1234 5678" 
                                   required class="form-input"
                                   oninput="validateNumber(this)">
                        </div>
                        
                        <button type="submit" name="place_order" class="confirm-btn">Confirm & Pay</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>

    <script>
        var map = L.map('map').setView([3.1390, 101.6869], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
        var marker;

        let debounceTimer;
        document.getElementById('addressBox').addEventListener('input', function() {
            clearTimeout(debounceTimer);
            var query = this.value;
            debounceTimer = setTimeout(() => {
                if(query.length > 4) {
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
                    .then(r => r.json())
                    .then(data => {
                        if(data && data.length > 0) {
                            var lat = data[0].lat;
                            var lon = data[0].lon;
                            map.setView([lat, lon], 14);
                            if(marker) marker.setLatLng([lat, lon]);
                            else marker = L.marker([lat, lon]).addTo(map);
                        }
                    });
                }
            }, 1000);
        });

        map.on('click', function(e) {
            if (marker) marker.setLatLng(e.latlng);
            else marker = L.marker(e.latlng).addTo(map);
            document.getElementById('addressBox').value = "Locating...";
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                .then(r => r.json())
                .then(d => { document.getElementById('addressBox').value = d.display_name || ("Lat: " + e.latlng.lat); });
        });

        function selectPayment(type) {
            const label = document.getElementById('ref-label');
            const input = document.getElementById('payment-input');
            const qrBox = document.getElementById('qr-code-container');
            const radio = document.getElementById(type === 'credit' ? 'credit_card' : (type === 'bank' ? 'online_banking' : 'ewallet'));
            radio.checked = true;

            if (type === 'credit') {
                qrBox.style.display = 'none';
                label.innerText = "Card Number:";
                input.placeholder = "e.g. 1234 5678 1234 5678";
            } else if (type === 'bank') {
                qrBox.style.display = 'none';
                label.innerText = "Bank Account Number:";
                input.placeholder = "e.g. 1551 2345 6789";
            } else if (type === 'ewallet') {
                qrBox.style.display = 'block';
                label.innerText = "Enter Transaction ID:";
                input.placeholder = "e.g. TNG-2025...";
            }
        }

        function validateNumber(field) {
            field.value = field.value.replace(/[^0-9-]/g, '');
        }
    </script>
</body>
</html>