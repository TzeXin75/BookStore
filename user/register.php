<?php
include '../_base.php';

// ----------------------------------------------------------------------------
$_err = [];
if (is_post()) {
    $email    = req('email');
    $password = req('password');
    $confirm  = req('confirm');
    $name     = req('name');
    $phone    = req('phone');
    $address  = req('address');
    $f = get_file('photo');

    // Validate: email
    if (!$email) {
        $_err['email'] = 'Required';
    }
    else if (strlen($email) > 100) {
        $_err['email'] = 'Maximum 100 characters';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }
    else if (!is_unique($email, 'users', 'email')) {
        $_err['email'] = 'Duplicated';
    }

    // Validate: password
    if (!$password) {
        $_err['password'] = 'Required';
    }
    else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }

    // Validate: confirm
    if (!$confirm) {
        $_err['confirm'] = 'Required';
    }
    else if ($confirm != $password) {
        $_err['confirm'] = 'Not matched';
    }

    // Validate: name
    if (!$name) {
        $_err['name'] = 'Required';
    }
    else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum 100 characters';
    }

    // Validate: phone
    if (!$phone) {
        $_err['phone'] = 'Required';
    }
    else if (strlen($phone) > 20) {
        $_err['phone'] = 'Maximum 20 characters';
    }
    else if (!preg_match('/^[0-9\-\+\(\)\s]+$/', $phone)) {
        $_err['phone'] = 'Invalid phone number';
    }
    else if (!is_unique($phone, 'users', 'user_phone')) {
        $_err['phone'] = 'Duplicated';
    }

    // Validate: address
    if (!$address) {
        $_err['address'] = 'Required';
    }
    else if (strlen($address) > 255) {
        $_err['address'] = 'Maximum 255 characters';
    }

    // Validate: photo (optional)
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Must be image';
        }
        else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Maximum 1MB';
        }
    }
    // DB operation
    if (!$_err) { 
        if ($f) {
            $photo = save_photo($f, '../photos');
        }

        // Insert user as inactive
        $stm = $_db->prepare('
            INSERT INTO users (email, user_password, username, user_photo, user_role, user_phone, user_address, user_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0)
        ');
        $hashedPassword = sha1($password);
        $stm->execute([$email, $hashedPassword, $name, $photo, 'member', $phone, $address]);
        $user_id = $_db->lastInsertId();

        // Generate 6-digit OTP
        $otp = random_int(100000, 999999);

        // Save OTP in session (with user data for verification)
        $_SESSION['pending_user'] = [
            'user_id' => $user_id,
            'email'   => $email,
            'otp'     => $otp,
            'expires' => time() + 900 // 15 minutes
        ];

        // Send OTP email
        $mail = get_mail();
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Your SIX SEVEN BS Verification Code';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <h2 style='color: #667eea;'>Hello $name!</h2>
                <p>Your verification code is:</p>
                <h1 style='font-size: 2.5rem; letter-spacing: 10px; text-align: center; color: #667eea;'>$otp</h1>
                <p>This code expires in 15 minutes.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <hr>
                <p style='color: #666; font-size: 0.9em;'>SIX SEVEN BS Team</p>
            </div>
        ";
        $mail->send();

        temp('info', 'Please check your email for the verification code.');
        redirect('otp_verify.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SIX SEVEN BS</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <?php include '../head.php'; ?>

    <div class="main-content">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <h1>Create Your Account</h1>
                    <p>Join SIX SEVEN BS and explore endless stories!</p>
                </div>

                <form method="post" class="register-form" enctype="multipart/form-data">
                    <div class="register-form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" maxlength="100" 
                               placeholder="Enter your email" required
                               value="<?= encode($_POST['email'] ?? '') ?>">
                        <?php if ($_err['email'] ?? false): ?>
                            <span class="error-msg"><?= $_err['email'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="register-form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" maxlength="100" 
                               placeholder="At least 5 characters" required>
                        <?php if ($_err['password'] ?? false): ?>
                            <span class="error-msg"><?= $_err['password'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="register-form-group">
                        <label for="confirm">Confirm Password</label>
                        <input type="password" id="confirm" name="confirm" maxlength="100" 
                               placeholder="Re-enter password" required>
                        <?php if ($_err['confirm'] ?? false): ?>
                            <span class="error-msg"><?= $_err['confirm'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="register-form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" maxlength="100" 
                               placeholder="Your full name" required
                               value="<?= encode($_POST['name'] ?? '') ?>">
                        <?php if ($_err['name'] ?? false): ?>
                            <span class="error-msg"><?= $_err['name'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="register-form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" maxlength="20" 
                               placeholder="e.g., 0123456789" required
                               value="<?= encode($_POST['phone'] ?? '') ?>">
                        <?php if ($_err['phone'] ?? false): ?>
                            <span class="error-msg"><?= $_err['phone'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="register-form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" maxlength="255" 
                                  placeholder="Enter your full address" required><?= encode($_POST['address'] ?? '') ?></textarea>
                        <?php if ($_err['address'] ?? false): ?>
                            <span class="error-msg"><?= $_err['address'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- PHOTO UPLOAD WITH PREVIEW -->
                    <div class="register-form-group">
                        <label for="photo">Profile Photo</label>
                        <div class="file-upload-wrapper" id="uploadWrapper">
                            <input type="file" id="photo" name="photo" accept="image/*">
                            <span class="file-upload-label" id="uploadLabel">
                                <i class="fas fa-upload"></i>
                                Upload Photo (Max 1MB)
                                <br>
                                [Optional]
                            </span>
                        </div>
                        <p class="file-hint">JPG, PNG, GIF - 1MB max</p>

                        <!-- Preview Area -->
                        <div id="previewContainer" style="display: none; margin-top: 15px; text-align: center;">
                            <img id="photoPreview" src="" alt="Photo Preview" 
                                 style="max-width: 200px; max-height: 200px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <p style="color: #28a745; font-weight: bold; margin-top: 10px;">
                                <i class="fas fa-check-circle"></i> Upload successful!
                            </p>
                            <button type="button" id="removePhoto" style="margin-top: 8px; color: #dc3545; background: none; border: none; font-size: 0.9rem; cursor: pointer;">
                                <i class="fas fa-trash"></i> Remove photo
                            </button>
                        </div>

                        <?php if ($_err['photo'] ?? false): ?>
                            <span class="error-msg"><?= $_err['photo'] ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="register-submit-btn">Register</button>

                    <div class="register-login-link">
                        Already have an account? <a href="../login.php">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="footer-placeholder"></div>

    <script>
    // Load footer
    fetch('../footer.html')
        .then(r => r.text())
        .then(data => { document.getElementById('footer-placeholder').innerHTML = data; });

    // Photo preview and success feedback
    document.getElementById('photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewContainer = document.getElementById('previewContainer');
        const photoPreview = document.getElementById('photoPreview');
        const uploadLabel = document.getElementById('uploadLabel');
        const uploadWrapper = document.getElementById('uploadWrapper');

        if (file) {
            // Client-side size validation
            if (file.size > 1 * 1024 * 1024) {
                alert('Photo must be less than 1MB');
                e.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                photoPreview.src = event.target.result;
                previewContainer.style.display = 'block';

                // Success styling
                uploadWrapper.style.borderColor = '#28a745';
                uploadWrapper.style.backgroundColor = '#f0fff4';
                uploadLabel.innerHTML = '<i class="fas fa-check"></i> Photo selected';
                uploadLabel.style.color = '#28a745';
            };
            reader.readAsDataURL(file);
        } else {
            resetUpload();
        }
    });

    // Remove photo
    document.getElementById('removePhoto').addEventListener('click', function() {
        document.getElementById('photo').value = '';
        resetUpload();
    });

    function resetUpload() {
        const previewContainer = document.getElementById('previewContainer');
        const uploadWrapper = document.getElementById('uploadWrapper');
        const uploadLabel = document.getElementById('uploadLabel');

        previewContainer.style.display = 'none';
        uploadWrapper.style.borderColor = '#ccc';
        uploadWrapper.style.backgroundColor = '#f8f9fa';
        uploadLabel.innerHTML = '<i class="fas fa-upload"></i> Upload Photo (Max 1MB)';
        uploadLabel.style.color = '#667eea';
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>