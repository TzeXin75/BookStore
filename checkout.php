<?php
require_once 'config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$user_id = $_SESSION['user_id'] ?? 1; 

// --- VOUCHER LOGIC ---
$msg = ""; $msg_type = ""; 
if (isset($_POST['remove_voucher'])) {
    unset($_SESSION['discount_amount']);
    unset($_SESSION['voucher_code']);
    $msg = "Voucher removed."; $msg_type = "success";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['voucher_code'])) {
    $code = trim($_POST['voucher_code']);
    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND status = 'Active' AND expiry_date >= CURDATE()");
    $stmt->execute([$code]);
    $voucher = $stmt->fetch();
    if ($voucher) {
        $_SESSION['discount_amount'] = $voucher['discount_amount'];
        $_SESSION['voucher_code'] = $code; 
        $msg = "Voucher Applied!"; $msg_type = "success";
    } else {
        $msg = "Invalid Code."; $msg_type = "error";
    }
}

// --- FETCH CART ---
$stmt = $pdo->prepare("SELECT c.quantity, b.price, b.title FROM cart c JOIN book b ON c.id = b.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($cart_items) == 0) { header("Location: index.php"); exit(); }

$base_total = 0;
foreach ($cart_items as $item) { $base_total += $item['price'] * $item['quantity']; }
$discount = $_SESSION['discount_amount'] ?? 0;
$final_total = max(0, $base_total - $discount);
$checkout_ref = "REF" . time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Bookstore</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .checkout-layout { max-width: 1100px; margin: 20px auto; display: flex; gap: 30px; padding: 0 20px; }
        .order-summary-box { flex: 1; background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; height: fit-content; }
        .form-section { flex: 1.5; background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        #map { height: 250px; width: 100%; border-radius: 5px; margin: 10px 0; border: 1px solid #ccc; z-index: 1; }
        .form-input, .form-textarea { width: 100%; padding: 12px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .payment-option { margin-bottom: 10px; display: flex; align-items: center; gap: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; }
        .confirm-btn { background: #28a745; color: white; padding: 15px; width: 100%; border: none; border-radius: 5px; font-size: 1.1em; cursor: pointer; font-weight: bold; }
        #qr-code-container { text-align: center; margin-bottom: 15px; display: none; padding: 15px; background: #f9f9f9; border: 1px solid #eee; border-radius: 8px; }
        .summary-item { display: flex; justify-content: space-between; font-size: 0.9em; margin-bottom: 8px; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="checkout-layout">
        <div class="order-summary-box">
            <h3>Order Summary</h3>
            <hr>
            <div style="margin: 15px 0;">
                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <span><?= htmlspecialchars($item['title']) ?> (x<?= $item['quantity'] ?>)</span>
                        <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr>
            <?php if ($discount > 0): ?>
                <div class="summary-item" style="color: green; font-weight: bold;">
                    <span>Discount (<?= $_SESSION['voucher_code'] ?>):</span> 
                    <span>-$<?= number_format($discount, 2) ?></span>
                </div>
                <form action="checkout.php" method="POST"><button type="submit" name="remove_voucher" style="background:none; border:none; color:red; cursor:pointer; font-size:0.8em; padding:0; margin-bottom:10px;">[Remove Voucher]</button></form>
            <?php endif; ?>
            <h2 style="color: #28a745; display: flex; justify-content: space-between;"><span>Total:</span> <span>$<?= number_format($final_total, 2) ?></span></h2>
            <form action="checkout.php" method="POST" style="display:flex; gap:5px; margin-top:15px;">
                <input type="text" name="voucher_code" class="form-input" style="margin:0;" placeholder="Promo Code">
                <button type="submit" style="background:#333; color:white; border:none; padding:0 10px; border-radius:4px; cursor:pointer;">Apply</button>
            </form>
        </div>

        <div class="form-section">
            <form action="place_order.php" method="POST" id="checkoutForm">
                <input type="hidden" name="checkout_ref" value="<?= $checkout_ref ?>">
                <!-- Hidden input to ensure place_order.php logic triggers when JS submits the form -->
                <input type="hidden" name="place_order" value="1">

                <h3>Shipping Details</h3>
                <input type="text" name="full_name" required class="form-input" placeholder="Recipient Name">
                <div id="map"></div>
                <textarea name="address" id="addressBox" required class="form-textarea" rows="3" placeholder="Address..."></textarea>

                <hr>
                <h3>Payment Method</h3>
                <div class="payment-option" onclick="selectPayment('credit')">
                    <input type="radio" id="credit_card" name="payment_method" value="Credit Card" checked>
                    <label for="credit_card" style="width:100%; cursor:pointer;"><strong>Credit Card</strong></label>
                </div>
                <div class="payment-option" onclick="selectPayment('bank')">
                    <input type="radio" id="online_banking" name="payment_method" value="Online Banking">
                    <label for="online_banking" style="width:100%; cursor:pointer;"><strong>Online Banking</strong></label>
                </div>
                <div class="payment-option" onclick="selectPayment('ewallet')">
                    <input type="radio" id="ewallet" name="payment_method" value="E-Wallet">
                    <label for="ewallet" style="width:100%; cursor:pointer;"><strong>E-Wallet (TNG)</strong></label>
                </div>

                <div id="payment-details-box" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top:10px;">
                    <div id="qr-code-container">
                        <a href="#" onclick="window.open('mobile_payment.php?ref=<?= $checkout_ref ?>','popup','width=400,height=600'); return false;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=SIMULATE" alt="QR Code">
                        </a>
                    </div>
                    <div id="standard-fields">
                        <label id="ref-label">Number</label>
                        <input type="text" name="payment_ref" id="payment-input" required class="form-input" placeholder="0000 0000 0000 0000" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        <div id="cvv-container"><label>CVV</label><input type="text" name="cvv" id="cvv-input" class="form-input" style="width:80px;" maxlength="3" oninput="this.value=this.value.replace(/[^0-9]/g,'')"></div>
                        <div id="bank-pass-container" style="display:none;"><label>Password</label><input type="password" name="bank_password" id="bank-pass-input" class="form-input"></div>
                    </div>
                </div>
                <button type="submit" name="place_order" id="confirmBtn" class="confirm-btn" style="margin-top:20px;">Confirm & Pay</button>
            </form>
        </div>
    </div>

<script>
    var map = L.map('map').setView([3.1390, 101.6869], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    var marker = L.marker([3.1390, 101.6869], {draggable: true}).addTo(map);

    function updateAddress(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(r => r.json())
            .then(d => { 
                if(d.display_name) document.getElementById('addressBox').value = d.display_name; 
            });
    }

    map.on('click', e => { 
        marker.setLatLng(e.latlng); 
        updateAddress(e.latlng.lat, e.latlng.lng); 
    });
    
    marker.on('dragend', e => {
        updateAddress(e.target.getLatLng().lat, e.target.getLatLng().lng);
    });
    let searchTimer;
    document.getElementById('addressBox').addEventListener('input', function() {
        const query = this.value;
        clearTimeout(searchTimer);
        
        searchTimer = setTimeout(() => {
            if(query.length > 5) {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        if(data.length > 0) {
                            const lat = data[0].lat;
                            const lon = data[0].lon;
                            map.setView([lat, lon], 16);
                            marker.setLatLng([lat, lon]);
                        }
                    })
                    .catch(err => console.error("Search failed:", err));
            }
        }, 1000); // 1 second delay after user stops typing
    });

    // --- 4. Payment UI Logic ---
    let pollInterval;
    function selectPayment(type) {
        const btn = document.getElementById('confirmBtn');
        const qrBox = document.getElementById('qr-code-container');
        const stdFields = document.getElementById('standard-fields');
        const cvvBox = document.getElementById('cvv-container');
        const bankBox = document.getElementById('bank-pass-container');
        const label = document.getElementById('ref-label');

        document.getElementById('credit_card').checked = (type === 'credit');
        document.getElementById('online_banking').checked = (type === 'bank');
        document.getElementById('ewallet').checked = (type === 'ewallet');
        
        qrBox.style.display="none"; stdFields.style.display="block"; cvvBox.style.display="none"; bankBox.style.display="none"; btn.style.display="block";
        clearInterval(pollInterval);

        if(type === 'credit') { label.innerText = "Card Number"; cvvBox.style.display = "block"; }
        else if(type === 'bank') { label.innerText = "Account Number"; bankBox.style.display = "block"; }
        else if(type === 'ewallet') { btn.style.display = "none"; stdFields.style.display = "none"; qrBox.style.display = "block"; startPolling(); }
    }

    function startPolling() {
        pollInterval = setInterval(() => {
            fetch(`check_payment_status.php?ref=<?= $checkout_ref ?>`)
            .then(r => r.json()).then(data => { if(data.status === 'success') document.getElementById('checkoutForm').submit(); });
        }, 2000);
    }

    document.getElementById('checkoutForm').onsubmit = function(e) {
        if(document.getElementById('credit_card').checked && !document.getElementById('cvv-input').value) { alert("CVV required"); return false; }
        if(document.getElementById('online_banking').checked && !document.getElementById('bank-pass-input').value) { alert("Password required"); return false; }
    };
</script>
</body>
</html>