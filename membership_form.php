<?php
require_once '_base.php'; // Use require_once to prevent "redeclare" errors
$_title = 'User | Membership Upgrade';
require_once 'head.php';

// ----------------------------------------------------------------------------
auth('member', 'customer');

if (is_post()) {
    $email      = req('email');
    $username   = req('username');
    $user_phone = req('user_phone');
    $user_dob   = req('user_dob');
    $user_address = req('user_address');
    
    // Files
    $f_photo   = get_file('photo');
    $f_receipt = get_file('receipt'); // Get the receipt file

    // --- VALIDATION START ---

    // 1. Email (Check format only, assuming user is already logged in or this is update)
    if ($email == '') { $_err['email'] = 'Required'; }
    else if (strlen($email) > 100) { $_err['email'] = 'Maximum 100 characters'; }
    else if (!is_email($email)) { $_err['email'] = 'Invalid email'; }

    // 2. Username
    if ($username == '') { $_err['username'] = 'Required'; }
    else if (strlen($username) > 100) { $_err['username'] = 'Maximum 100 characters'; }

    // 3. Phone
    if ($user_phone == '') { $_err['user_phone'] = 'Required'; }
    else if (strlen($user_phone) > 20) { $_err['user_phone'] = 'Maximum 20 characters'; }

    // 4. Date of Birth (New Validation)
    if ($user_dob == '') { $_err['user_dob'] = 'Required'; }

    // 5. Address
    if ($user_address == '') { $_err['user_address'] = 'Required'; }
    else if (strlen($user_address) > 255) { $_err['user_address'] = 'Maximum 255 characters'; }

    // 6. Photo (Optional)
    if ($f_photo) {
        if (!str_starts_with($f_photo->type, 'image/')) { $_err['photo'] = 'Must be image'; }
        else if ($f_photo->size > 1 * 1024 * 1024) { $_err['photo'] = 'Maximum 1MB'; }
    }

    // --- DB OPERATION ---
    if (!$_err) {
        // Save Photo (Default if none uploaded)
        $photo = 'default.jpg';
        if ($f_photo) {
            $photo = save_photo($f_photo, 'photos'); 
        }

        // If no user is logged in, you might need to Redirect or Insert. 
        // Assuming user is logged in for an upgrade:
        // 2. GENERATE NEW MEMBER ID (M-001, M-002...)
        // Get the latest member_id from DB
        $sql_id = "SELECT member_id FROM users WHERE member_id LIKE 'M-%' ORDER BY CAST(SUBSTRING(member_id, 3) AS UNSIGNED) DESC LIMIT 1";
        $stm_id = $_db->query($sql_id);
        $lastId = $stm_id->fetchColumn();

        if ($lastId) {
            // Extract number (remove "M-") and add 1
            $num = (int)substr($lastId, 2) + 1;
        } else {
            // First member ever
            $num = 1;
        }
        $member_id = 'M-' . sprintf('%03d', $num); // Format as M-001


        // 3. UPDATE USER with new Role AND Member ID
        $current_user_id = $_SESSION['user']['user_id'] ?? 0;

        $sql = 'UPDATE users SET 
                    email = ?, 
                    username = ?,
                    user_phone = ?, 
                    user_address = ?, 
                    user_photo = ?, 
                    user_dob = ?, 
                    user_role = ?,
                    member_id = ?        
                WHERE user_id = ?'; 

        $stm = $_db->prepare($sql);
        
        $stm->execute([
            $email,
            $username,
            $user_phone, 
            $user_address, 
            $photo, 
            $user_dob,  
            'member',        
            $member_id,      /* <--- Pass the generated ID */
            $current_user_id 
        ]);

        // Update session
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['user_role'] = 'member';
            $_SESSION['user']['member_id'] = $member_id; // Store ID in session too
        }

        temp('info', "Upgrade successful! Your new Member ID is $member_id.");
        redirect('/');
    }
}
?>

<style>
    .registration-wrapper {
        max-width: 700px;
        margin: 40px auto;
        background: #ffffff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        font-family: 'Segoe UI', sans-serif;
    }
    .vip-banner {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        color: white;
        padding: 25px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 30px;
    }
    .reg-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .full-width { grid-column: span 2; }
    
    .form-group { display: flex; flex-direction: column; margin-bottom: 5px; }
    .form-group label { font-weight: 600; color: #333; margin-bottom: 5px; }
    .form-control { padding: 12px; border: 1px solid #ddd; border-radius: 6px; width: 100%; box-sizing: border-box; }
    
    .photo-upload-container { text-align: center; margin-bottom: 20px; }
    .upload-label img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); cursor: pointer; }
    input[type="file"]#file-upload { display: none !important; }
    
    .payment-section {
        background: #f9f9f9;
        border: 2px dashed #28a745;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        margin-top: 20px;
    }

    .btn { flex: 1; padding: 12px; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; color: white; width: 100%; }
    .btn-primary { background-color: #28a745; margin-top: 15px; }
    .btn-primary:hover { background-color: #218838; }
    .err { color: red; font-size: 0.85em; margin-top: 4px; display: block; }
</style>

<div class="registration-wrapper">
    
    <div class="vip-banner">
        <h2>Join Our VIP Membership</h2>
        <p>Unlock <strong>10% OFF</strong> & Free Shipping forever!</p>
        <div style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 15px; display: inline-block; margin-top: 10px; font-weight: bold;">
            Price: RM 10.00 / Lifetime
        </div>
    </div>

    <form method="post" class="form reg-form" enctype="multipart/form-data">
        
        <div class="full-width photo-upload-container">
            <label for="file-upload" class="upload-label">
                <img id="photo-preview" src="photos/default.jpg" alt="Profile Photo">
                <div style="font-size: 12px; color: #666; margin-top: 5px;">Tap to upload photo</div>
            </label>
            <input type="file" id="file-upload" name="photo" accept="image/*" onchange="previewPhoto(this)">
            <?= err('photo') ?>
        </div>

        <div class="form-group">
            <label>Full Name</label>
            <?= html_text('username', 'class="form-control" maxlength="100" placeholder="e.g. Ali Bin Abu"') ?>
            <?= err('username') ?>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <?= html_text('user_phone', 'class="form-control" maxlength="20" placeholder="e.g. 012-3456789"') ?>
            <?= err('user_phone') ?>
        </div>

        <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="user_dob" class="form-control" value="<?= isset($user_dob) ? $user_dob : '' ?>">
            <?= err('user_dob') ?>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <?= html_text('email', 'class="form-control" maxlength="100" placeholder="name@email.com"') ?>
            <?= err('email') ?>
        </div>

        <div class="form-group full-width">
            <label>Shipping Address</label>
            <textarea name="user_address" class="form-control" rows="3" placeholder="Enter full address"><?= isset($user_address) ? encode($user_address) : '' ?></textarea>
            <?= err('user_address') ?>
        </div>

        <div class="full-width payment-section">
            <h3 style="margin-top: 0; color: #28a745;">Step 1: Scan to Pay RM 10.00</h3>
            
            <div style="background: white; padding: 10px; display: inline-block; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <img src="photos/payment_qr.jpeg" alt="DuitNow QR" style="width: 150px; height: 150px; object-fit: contain;">
                <div style="font-size: 12px; font-weight: bold; color: #555;">DuitNow / TnG</div>
            </div>

            <hr style="border: 0; border-top: 1px dashed #ccc; margin: 20px 0;">

            <h3 style="margin-top: 0; color: #333;">Step 2: Upload Receipt</h3>
            <div style="text-align: left; max-width: 400px; margin: 0 auto;">
                <input type="file" name="receipt" class="form-control" accept="image/*">
                <small style="color: #666;">Screenshot of successful transfer</small>
                <?= err('receipt') ?>
            </div>
        </div>

        <div class="full-width">
            <button type="submit" class="btn btn-primary">Pay & Register VIP</button>
        </div>

    </form>
</div>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('photo-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'footer.php'; ?>