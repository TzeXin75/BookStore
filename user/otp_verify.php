<?php
include '../_base.php';

// Redirect if no pending registration
if (!isset($_SESSION['pending_registration']) || !isset($_SESSION['otp'])) {
    redirect('register.php');
}

$pending = $_SESSION['pending_registration'];

// Check expiry
if (time() > $pending['expires'] || time() > $_SESSION['otp_expires']) {
    unset($_SESSION['pending_registration']);
    unset($_SESSION['otp']);
    unset($_SESSION['otp_expires']);
    temp('info', 'Session expired. Please register again.');
    redirect('register.php');
}

// Handle Resend OTP
if (is_post() && isset($_POST['resend'])) {
    // Cooldown (60 seconds)
    $last_resend = $_SESSION['last_resend'] ?? 0;
    if (time() - $last_resend < 60) {
        temp('info', 'Please wait 60 seconds before resending.');
    } else {
        $otp = random_int(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expires'] = time() + 900;
        $_SESSION['last_resend'] = time();

        // Send new OTP
        $mail = get_mail();
        $mail->addAddress($pending['email']);
        $mail->isHTML(true);
        $mail->Subject = 'Your New BookStore Registration OTP';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <h2 style='color: #667eea;'>New Verification Code</h2>
                <p>Your new one-time verification code is:</p>
                <h1 style='font-size: 2.5rem; letter-spacing: 10px; text-align: center; color: #667eea;'>$otp</h1>
                <p>It expires in 15 minutes.</p>
                <hr>
                <p style='color: #666; font-size: 0.9em;'>BookStore Team</p>
            </div>
        ";
        $mail->send();

        temp('info', 'New OTP sent! Check your email.');
    }
    redirect('otp_verify.php');
}

// Handle OTP submission
$_err = [];
if (is_post() && !isset($_POST['resend'])) {
    $otp_input = req('otp');

    if (!$otp_input) {
        $_err['otp'] = 'Required';
    } elseif ($otp_input != $_SESSION['otp']) {
        $_err['otp'] = 'Invalid OTP';
    }

    if (!$_err) {
        // Insert user to DB
        $stm = $_db->prepare('
            INSERT INTO users (email, user_password, username, user_photo, user_role, user_phone, user_address, user_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ');
        $stm->execute([
            $pending['email'],
            $pending['hashed_password'],
            $pending['name'],
            $pending['photo'],
            $pending['role'],
            $pending['phone'],
            $pending['address']
        ]);

        // Clean session
        unset($_SESSION['pending_registration']);
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expires']);
        unset($_SESSION['last_resend']);

        temp('info', 'Registration successful! You can now log in.');
        redirect('/login.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - BookStore</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <?php include '../head.php'; ?>

    <div class="main-content">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <h1>Verify Your Email</h1>
                    <p>We sent a 6-digit code to <?= htmlspecialchars($pending['email']) ?></p>
                </div>

                <form method="post" class="register-form">
                    <div class="form-group">
                        <label for="otp">Enter Verification Code</label>
                        <input type="text" id="otp" name="otp" maxlength="6" placeholder="123456" required autocomplete="off">
                        <?php if ($_err['otp'] ?? false): ?>
                            <span class="error-msg"><?= $_err['otp'] ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="register-submit-btn">Verify & Complete Registration</button>
                </form>

                <!-- Resend OTP -->
                <form method="post" class="resend-form" style="text-align:center; margin-top:20px;">
                    <button type="submit" name="resend" class="resend-btn" id="resendBtn">Resend Code</button>
                </form>

                <?php 
                $last_resend = $_SESSION['last_resend'] ?? 0;
                $cooldown_left = 60 - (time() - $last_resend);
                if ($cooldown_left > 0): ?>
                    <p class="cooldown" style="text-align:center; color:#dc3545; font-size:0.9rem; margin-top:10px;">
                        Please wait <?= $cooldown_left ?> seconds
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="footer-placeholder"></div>

    <script>
    fetch('../footer.html')
        .then(r => r.text())
        .then(data => { document.getElementById('footer-placeholder').innerHTML = data; });

    // Cooldown timer
    <?php if ($cooldown_left > 0): ?>
    let seconds = <?= $cooldown_left ?>;
    const resendBtn = document.getElementById('resendBtn');
    resendBtn.disabled = true;

    const interval = setInterval(() => {
        seconds--;
        document.querySelector('.cooldown').textContent = `Please wait ${seconds} seconds`;

        if (seconds <= 0) {
            clearInterval(interval);
            resendBtn.disabled = false;
            document.querySelector('.cooldown').style.display = 'none';
        }
    }, 1000);
    <?php endif; ?>
    </script>
</body>
</html>